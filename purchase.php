<?php
// purchase.php (Updated for Multiple Images & Detailed Specs)
include 'db.php';
include 'auth.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Text Data
    $seller_name = $_POST['seller_name'];
    $s_cnic = $_POST['seller_cnic'];
    $s_phone = $_POST['seller_phone'];
    
    // Check Customer (Auto Add)
    $check_cust = $conn->query("SELECT * FROM customers WHERE cnic = '$s_cnic'");
    if($check_cust->num_rows == 0){
        $conn->query("INSERT INTO customers (name, phone, cnic) VALUES ('$seller_name', '$s_phone', '$s_cnic')");
    }

    // Phone Details
    $brand = $_POST['brand'];
    $device = $_POST['device_name'];
    $imei = $_POST['imei'];
    $price = $_POST['price'];
    
    // New Specs
    $pta = $_POST['pta_status'];
    $ram = $_POST['ram'];
    $storage = $_POST['storage'];
    $cond = $_POST['condition'];
    $bh = $_POST['battery_health'];  // NEW
    $color = $_POST['color'];        // NEW
    $sim = $_POST['sim_type'];       // NEW
    $kit = $_POST['accessories'];    // NEW
    $unlock = $_POST['is_unlocked']; // NEW

    // 2. Image Upload Function (Single Image - CNIC)
    function uploadSingle($fileInputName) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
        if(isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] == 0){
            $fileName = time() . "_" . basename($_FILES[$fileInputName]["name"]);
            move_uploaded_file($_FILES[$fileInputName]["tmp_name"], $target_dir . $fileName);
            return $target_dir . $fileName;
        }
        return "uploads/default.png";
    }
    
    $cf = uploadSingle("cnic_front");
    $cb = uploadSingle("cnic_back");

    // 3. MULTIPLE Device Images Upload Logic
    $device_imgs_arr = [];
    $main_thumb = "uploads/default_phone.png"; // Pehli pic thumbnail hogi

    if(isset($_FILES['device_pics'])){
        $total_files = count($_FILES['device_pics']['name']);
        
        for($i=0; $i<$total_files; $i++) {
            if($_FILES['device_pics']['error'][$i] == 0){
                $tmpName = $_FILES['device_pics']['tmp_name'][$i];
                $name = time()."_DEV_".$i."_".basename($_FILES['device_pics']['name'][$i]);
                $target = "uploads/" . $name;
                
                if(move_uploaded_file($tmpName, $target)){
                    $device_imgs_arr[] = $target;
                    // Pehli image ko main thumbnail bana lo
                    if($i == 0) $main_thumb = $target;
                }
            }
        }
    }
    // Array ko string banao (img1.jpg,img2.jpg)
    $all_images_str = implode(",", $device_imgs_arr);

    // 4. Save to Purchases (History)
    $sql_purchase = "INSERT INTO purchases (seller_name, seller_cnic, seller_phone, device_name, imei_number, purchase_price, cnic_front, cnic_back, device_images, battery_health, color) 
                     VALUES ('$seller_name', '$s_cnic', '$s_phone', '$device', '$imei', '$price', '$cf', '$cb', '$all_images_str', '$bh', '$color')";
    $conn->query($sql_purchase);

    // 5. Save to Stock (Products)
    $sale_est = $price + 5000;
    
    $sql_stock = "INSERT INTO products (brand, model, cost_price, sale_price, stock_qty, type, imei_number, ram, storage, pta_status, condition_status, device_image, device_images, battery_health, color, sim_type, accessories, is_unlocked) 
                  VALUES ('$brand', '$device', '$price', '$sale_est', 1, 'Used', '$imei', '$ram', '$storage', '$pta', '$cond', '$main_thumb', '$all_images_str', '$bh', '$color', '$sim', '$kit', '$unlock')";

    if ($conn->query($sql_stock) === TRUE) {
        echo "<script>alert('✅ Phone with Full Specs Added!'); window.location.href='index.php';</script>";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>