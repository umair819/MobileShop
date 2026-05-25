<?php
include 'db.php';

header('Content-Type: application/json');

if(isset($_POST['action']) && $_POST['action'] == 'find_customer') {
    $type = $_POST['type']; // 'name', 'phone', or 'cnic'
    $val = mysqli_real_escape_string($conn, $_POST['val']);
    
    $col = ($type == 'phone') ? 'phone' : (($type == 'cnic') ? 'cnic' : 'name');
    
    // Search Query
    if($type == 'name'){
        $sql = "SELECT * FROM customers WHERE name LIKE '%$val%' LIMIT 1";
    } else {
        $sql = "SELECT * FROM customers WHERE $col = '$val' LIMIT 1";
    }

    $res = $conn->query($sql);

    if($res->num_rows > 0){
        $row = $res->fetch_assoc();
        
        // Check if images exist (True/False bhejenge taake frontend samajh sake)
        $has_front = !empty($row['cnic_front']);
        $has_back = !empty($row['cnic_back']);

        echo json_encode([
            'status' => 'found', 
            'data' => $row,
            'has_front' => $has_front,
            'has_back' => $has_back
        ]);
    } else {
        echo json_encode(['status' => 'new']);
    }
}
?>