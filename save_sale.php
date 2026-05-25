<?php
include 'db.php';
include 'auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

$c_name = mysqli_real_escape_string($conn, $_POST['c_name'] ?? '');
$c_phone = mysqli_real_escape_string($conn, $_POST['c_phone'] ?? '');
$c_cnic = mysqli_real_escape_string($conn, $_POST['c_cnic'] ?? '');

if ($c_name === '' || $c_phone === '' || $c_cnic === '') {
    die("Customer details missing.");
}

$allowed_methods = ['Cash', 'JazzCash', 'EasyPaisa', 'Raast QR', 'Bank Transfer', 'Udhaar'];
$payment_method = $_POST['payment_method'] ?? 'Cash';
if (!in_array($payment_method, $allowed_methods, true)) {
    $payment_method = 'Cash';
}

$due_date = !empty($_POST['due_date']) ? mysqli_real_escape_string($conn, $_POST['due_date']) : null;
$sale_notes = !empty($_POST['sale_notes']) ? mysqli_real_escape_string($conn, $_POST['sale_notes']) : null;

$posted_paid = isset($_POST['paid_amount']) ? (float)$_POST['paid_amount'] : 0;
if ($posted_paid < 0) {
    $posted_paid = 0;
}

$prices = $_POST['price'] ?? [];
$devices = $_POST['device'] ?? [];
$imeis = $_POST['imei'] ?? [];

if (count($prices) === 0 || count($prices) !== count($devices) || count($devices) !== count($imeis)) {
    die("Sale items invalid.");
}

$total_amount = 0;
foreach ($prices as $p) {
    $total_amount += (float)$p;
}

if ($posted_paid > $total_amount) {
    $posted_paid = $total_amount;
}

if ($payment_method === 'Udhaar' && $posted_paid <= 0) {
    $posted_paid = 0;
}

$balance_amount = $total_amount - $posted_paid;
if ($balance_amount < 0) {
    $balance_amount = 0;
}

$customer_id = 0;
$existing_front = "";
$existing_back = "";

$check_cust = $conn->query("SELECT id, cnic_front, cnic_back FROM customers WHERE cnic = '$c_cnic' OR phone = '$c_phone' LIMIT 1");
if ($check_cust && $check_cust->num_rows > 0) {
    $cust_data = $check_cust->fetch_assoc();
    $customer_id = (int)$cust_data['id'];
    $existing_front = $cust_data['cnic_front'];
    $existing_back = $cust_data['cnic_back'];
    $conn->query("UPDATE customers SET name='$c_name', cnic='$c_cnic', phone='$c_phone' WHERE id=$customer_id");
} else {
    $conn->query("INSERT INTO customers (name, phone, cnic) VALUES ('$c_name', '$c_phone', '$c_cnic')");
    $customer_id = (int)$conn->insert_id;
}

$target_dir = "uploads/sales_proof/";
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0777, true);
}

$final_front = $existing_front;
$final_back = $existing_back;

if (!empty($_FILES['buyer_front']['name'])) {
    $path = $target_dir . time() . "_F_" . basename($_FILES['buyer_front']['name']);
    move_uploaded_file($_FILES['buyer_front']['tmp_name'], $path);
    $final_front = $path;
}
if (!empty($_FILES['buyer_back']['name'])) {
    $path = $target_dir . time() . "_B_" . basename($_FILES['buyer_back']['name']);
    move_uploaded_file($_FILES['buyer_back']['tmp_name'], $path);
    $final_back = $path;
}

$conn->query("UPDATE customers SET cnic_front='$final_front', cnic_back='$final_back' WHERE id=$customer_id");

$conn->begin_transaction();

try {
    $due_sql = $due_date ? "'$due_date'" : "NULL";
    $note_sql = $sale_notes ? "'$sale_notes'" : "NULL";

    $sql_sale = "INSERT INTO sales (customer_id, customer_name, customer_phone, customer_cnic, buyer_front, buyer_back, total_amount, payment_method, paid_amount, balance_amount, sale_notes)
                 VALUES ('$customer_id', '$c_name', '$c_phone', '$c_cnic', '$final_front', '$final_back', '$total_amount', '$payment_method', '$posted_paid', '$balance_amount', $note_sql)";

    if (!$conn->query($sql_sale)) {
        throw new Exception("Sale save failed: " . $conn->error);
    }

    $sale_id = (int)$conn->insert_id;

    for ($i = 0; $i < count($devices); $i++) {
        $d_name = mysqli_real_escape_string($conn, $devices[$i]);
        $d_imei = mysqli_real_escape_string($conn, $imeis[$i]);
        $d_price = (float)$prices[$i];

        $cp_query = $conn->query("SELECT cost_price FROM products WHERE imei_number = '$d_imei' LIMIT 1");
        $cost_price = 0;
        if ($cp_query && $cp_query->num_rows > 0) {
            $cost_price = (float)$cp_query->fetch_assoc()['cost_price'];
        }

        if (!$conn->query("INSERT INTO sale_items (sale_id, product_id, device_name, imei_number, price, cost
                          ) VALUES ('$sale_id', 0, '$d_name', '$d_imei', '$d_price', '$cost_price')")) {
            throw new Exception("Sale item save failed: " . $conn->error);
        }

        if (!$conn->query("UPDATE products SET stock_qty = IF(stock_qty > 0, stock_qty - 1, 0) WHERE imei_number = '$d_imei'")) {
            throw new Exception("Stock update failed: " . $conn->error);
        }
    }

    if ($balance_amount > 0) {
        $status = ($posted_paid > 0) ? 'Partial' : 'Unpaid';
        $ledger_sql = "INSERT INTO udhaar_ledgers (customer_id, sale_id, customer_name, customer_phone, total_amount, paid_amount, balance_amount, status, due_date, notes)
                       VALUES ('$customer_id', '$sale_id', '$c_name', '$c_phone', '$total_amount', '$posted_paid', '$balance_amount', '$status', $due_sql, $note_sql)";
        if (!$conn->query($ledger_sql)) {
            throw new Exception("Khata ledger save failed: " . $conn->error);
        }
    }

    $conn->commit();
    header("Location: invoice.php?sale_id=" . $sale_id);
    exit;
} catch (Throwable $e) {
    $conn->rollback();
    echo "Error: " . $e->getMessage();
}
?>
