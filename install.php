<?php
// install.php - Fixed Date Issue

$servername = "localhost";
$username = "root";
$password = ""; // Laragon default

// 1. Connect to Server
$conn = new mysqli($servername, $username, $password);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 2. Create Database
$sql = "CREATE DATABASE IF NOT EXISTS universal_mobile_shop";
if ($conn->query($sql) === TRUE) {
    echo "✅ Database checked/created.<br>";
} else {
    die("Error creating database: " . $conn->error);
}

// 3. Select Database
$conn->select_db("universal_mobile_shop");

// 4. Create Settings Table
$table1 = "CREATE TABLE IF NOT EXISTS shop_settings (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    shop_name VARCHAR(100) DEFAULT 'Mobile Shop',
    shop_phone VARCHAR(50) DEFAULT '0300-0000000',
    address TEXT,
    currency VARCHAR(10) DEFAULT 'Rs.'
)";

if ($conn->query($table1) === TRUE) {
    echo "✅ Settings Table checked.<br>";
} else {
    echo "❌ Error creating Settings table: " . $conn->error . "<br>";
}

// 5. Default Settings (Only if empty)
$check = $conn->query("SELECT * FROM shop_settings");
if ($check->num_rows == 0) {
    $conn->query("INSERT INTO shop_settings (shop_name, shop_phone) VALUES ('My Mobile Shop', '0300-1234567')");
    echo "✅ Default settings inserted.<br>";
}

// 6. Create Sales Table (FIXED HERE: TIMESTAMP use kiya hai)
$table2 = "CREATE TABLE IF NOT EXISTS sales (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(100),
    customer_phone VARCHAR(50),
    device_name VARCHAR(100),
    imei_number VARCHAR(100),
    price DECIMAL(10,2),
    sale_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($table2) === TRUE) {
    echo "✅ Sales Table created successfully.<br>";
} else {
    echo "❌ Error creating Sales table: " . $conn->error . "<br>";
}

echo "<br><hr><h3>🎉 MUBARAK HO! Installation Complete.</h3>";
echo "<a href='index.php' style='font-size:20px; font-weight:bold;'>Click here to Open Dashboard</a>";

$conn->close();
?>