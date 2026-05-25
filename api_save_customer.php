<?php
// api_save_customer.php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $cnic = $_POST['cnic'];

    // Check agar pehle se hai
    $check = $conn->query("SELECT * FROM customers WHERE cnic = '$cnic'");
    if ($check->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "Customer with this CNIC already exists!"]);
        exit;
    }

    $sql = "INSERT INTO customers (name, phone, cnic) VALUES ('$name', '$phone', '$cnic')";
    if ($conn->query($sql)) {
        echo json_encode(["status" => "success", "id" => $conn->insert_id, "name" => $name]);
    } else {
        echo json_encode(["status" => "error", "message" => "Database Error"]);
    }
}
?>