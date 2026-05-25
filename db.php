<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "universal_mobile_shop";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// --- DUKAAN DOST SAFE MIGRATIONS ---
function dd_column_exists($conn, $database, $table, $column) {
    $table = $conn->real_escape_string($table);
    $column = $conn->real_escape_string($column);
    $database = $conn->real_escape_string($database);

    $sql = "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA='$database' AND TABLE_NAME='$table' AND COLUMN_NAME='$column' LIMIT 1";
    $res = $conn->query($sql);
    return ($res && $res->num_rows > 0);
}

function dd_add_column_if_missing($conn, $database, $table, $column, $definition) {
    if (!dd_column_exists($conn, $database, $table, $column)) {
        $conn->query("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
    }
}

// Sales table extensions for payment tracking
dd_add_column_if_missing($conn, $dbname, "sales", "payment_method", "VARCHAR(30) NOT NULL DEFAULT 'Cash' AFTER total_amount");
dd_add_column_if_missing($conn, $dbname, "sales", "paid_amount", "DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER payment_method");
dd_add_column_if_missing($conn, $dbname, "sales", "balance_amount", "DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER paid_amount");
dd_add_column_if_missing($conn, $dbname, "sales", "sale_notes", "VARCHAR(255) NULL AFTER balance_amount");

// Products table extensions for low stock + supplier quick order
dd_add_column_if_missing($conn, $dbname, "products", "reorder_level", "INT(11) NOT NULL DEFAULT 1 AFTER stock_qty");
dd_add_column_if_missing($conn, $dbname, "products", "vendor_phone", "VARCHAR(30) NULL AFTER vendor_name");

// Udhaar ledger tables
$conn->query("CREATE TABLE IF NOT EXISTS udhaar_ledgers (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    customer_id INT(11) NULL,
    sale_id INT(11) NULL,
    customer_name VARCHAR(120) NOT NULL,
    customer_phone VARCHAR(30) NOT NULL,
    total_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    paid_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    balance_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    status VARCHAR(20) NOT NULL DEFAULT 'Unpaid',
    due_date DATE NULL,
    notes VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

$conn->query("CREATE TABLE IF NOT EXISTS khata_payments (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    ledger_id INT(11) NOT NULL,
    amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    payment_method VARCHAR(30) NOT NULL DEFAULT 'Cash',
    notes VARCHAR(255) NULL,
    paid_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ledger_id (ledger_id)
)");

// --- GLOBAL SETTINGS FETCH ---
$settings_res = $conn->query("SELECT * FROM settings WHERE id=1");
$global_settings = ($settings_res && $settings_res->num_rows > 0) ? $settings_res->fetch_assoc() : [];

// Agar database khali ho to default values
$shop_name = isset($global_settings['shop_name']) ? $global_settings['shop_name'] : "Universal Mobile Shop";
$shop_contact = isset($global_settings['shop_contact']) ? $global_settings['shop_contact'] : "0300-1234567";
$shop_address = isset($global_settings['shop_address']) ? $global_settings['shop_address'] : "Karachi, Pakistan";
?>
