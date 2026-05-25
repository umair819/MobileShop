<?php
include 'db.php';
include 'auth.php';

// --- 1. ROLE FIX (THE MAGIC LINE) ---
// Session se role uthao, agar nahi hai to 'Admin' maan lo
$role_raw = isset($_SESSION['role']) ? $_SESSION['role'] : 'Admin';

// Zabardasti 'Admin' (Bara A) bana do agar spelling 'admin' (chota a) hai
if(strtolower($role_raw) == 'admin') { 
    $role = 'Admin'; 
} else {
    $role = 'Staff';
}
// ------------------------------------

// --- 2. HANDLE REQUESTS ---

// DELETE (Admin Only)
if (isset($_GET['del_prod'])) { 
    if($role != 'Admin') die("Access Denied: Sirf Admin delete kar sakta hai.");
    $conn->query("DELETE FROM products WHERE id=" . $_GET['del_prod']); 
    header("Location: dashboard.php?tab=inventory"); exit; 
}
if (isset($_GET['del_cust'])) { 
    if($role != 'Admin') die("Access Denied");
    $conn->query("DELETE FROM customers WHERE id=" . $_GET['del_cust']); 
    header("Location: dashboard.php?tab=customers"); exit; 
}
if (isset($_GET['del_user'])) { 
    if($role != 'Admin') die("Access Denied");
    $conn->query("DELETE FROM users WHERE id=" . $_GET['del_user']); 
    header("Location: dashboard.php?tab=users"); exit; 
}

// SAVE SETTINGS (Admin Only)
if (isset($_POST['save_settings'])) {
    if($role != 'Admin') die("Access Denied");
    $s_name = mysqli_real_escape_string($conn, $_POST['shop_name']);
    $printer = $_POST['printer_type']; 
    $contact = $_POST['shop_contact']; 
    $address = mysqli_real_escape_string($conn, $_POST['shop_address']);
    
    $check = $conn->query("SELECT id FROM settings WHERE id=1");
    if($check->num_rows > 0){ 
        $conn->query("UPDATE settings SET shop_name='$s_name', printer_type='$printer', shop_contact='$contact', shop_address='$address' WHERE id=1"); 
    } else { 
        $conn->query("INSERT INTO settings (id, shop_name, printer_type, shop_contact, shop_address) VALUES (1, '$s_name', '$printer', '$contact', '$address')"); 
    }
    
    if(!empty($_POST['new_password'])){
        $new_pass = $_POST['new_password'];
        $conn->query("UPDATE users SET password='$new_pass' WHERE username='admin'");
    }
    header("Location: dashboard.php?tab=settings"); exit;
}

// SAVE USER (Admin Only)
if (isset($_POST['save_user'])) {
    if($role != 'Admin') die("Access Denied");
    $u_name = mysqli_real_escape_string($conn, $_POST['username']);
    $u_pass = $_POST['password'];
    $u_role = $_POST['role'];
    
    $chk = $conn->query("SELECT * FROM users WHERE username='$u_name'");
    if($chk->num_rows > 0){ $conn->query("UPDATE users SET password='$u_pass', role='$u_role' WHERE username='$u_name'"); } 
    else { $conn->query("INSERT INTO users (username, password, role) VALUES ('$u_name', '$u_pass', '$u_role')"); }
    header("Location: dashboard.php?tab=users"); exit;
}

// SAVE PRODUCT
if (isset($_POST['save_product'])) {
    $id = $_POST['prod_id']; $type = $_POST['type']; 
    $brand = mysqli_real_escape_string($conn, $_POST['brand']); $model = mysqli_real_escape_string($conn, $_POST['model']); $imei = mysqli_real_escape_string($conn, $_POST['imei']);
    $storage = mysqli_real_escape_string($conn, $_POST['storage']); $color = mysqli_real_escape_string($conn, $_POST['color']);
    $qty = (int)$_POST['qty']; $price = (int)$_POST['price']; $cost = isset($_POST['cost']) ? (int)$_POST['cost'] : 0;
    $pta = $_POST['pta']; $cond = $_POST['condition']; $bh = $_POST['bh']; $sim = $_POST['sim']; $kit = $_POST['kit']; $unlock = $_POST['unlock'];
    $vendor = ($type == 'New') ? mysqli_real_escape_string($conn, $_POST['vendor_name']) : NULL;
    $vendor_phone = ($type == 'New' && !empty($_POST['vendor_phone'])) ? mysqli_real_escape_string($conn, $_POST['vendor_phone']) : NULL;
    $reorder_level = isset($_POST['reorder_level']) ? (int)$_POST['reorder_level'] : 1;
    if($reorder_level < 1) { $reorder_level = 1; }
    $s_name = ($type == 'Used') ? $_POST['seller_name'] : NULL; $s_phone = ($type == 'Used') ? $_POST['seller_phone'] : NULL; $s_cnic = ($type == 'Used') ? $_POST['seller_cnic'] : NULL;

    $target_dir = "uploads/"; if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
    $main_thumb_sql = ""; 
    if(!empty($_FILES['main_pic']['name'])){ $path = $target_dir.time()."_THUMB_".basename($_FILES['main_pic']['name']); move_uploaded_file($_FILES['main_pic']['tmp_name'], $path); $main_thumb_sql = ", device_image='$path'"; }
    
    if (!empty($id)) {
        $sql = "UPDATE products SET brand='$brand', model='$model', type='$type', imei_number='$imei', sale_price='$price', cost_price='$cost', stock_qty='$qty', reorder_level='$reorder_level', storage='$storage', color='$color', pta_status='$pta', condition_status='$cond', battery_health='$bh', sim_type='$sim', accessories='$kit', is_unlocked='$unlock', vendor_name='$vendor', vendor_phone='$vendor_phone', seller_name='$s_name', seller_phone='$s_phone', seller_cnic='$s_cnic' $main_thumb_sql WHERE id=$id";
        try { $conn->query($sql); } catch (mysqli_sql_exception $e) { echo "<script>alert('Error: IMEI exists!'); window.location.href='dashboard.php?tab=inventory';</script>"; exit; }
    } else {
        $check = $conn->query("SELECT id, stock_qty, type FROM products WHERE imei_number='$imei' LIMIT 1");
        if($check->num_rows > 0) {
            $exist = $check->fetch_assoc();
            if($type == 'New' && $exist['type'] == 'New'){ $new_qty = $exist['stock_qty'] + $qty; $eid = $exist['id']; $conn->query("UPDATE products SET stock_qty='$new_qty', sale_price='$price', cost_price='$cost', reorder_level='$reorder_level', vendor_name='$vendor', vendor_phone='$vendor_phone' WHERE id=$eid"); } 
            else { echo "<script>alert('Error: IMEI Registered!'); window.location.href='dashboard.php?tab=inventory';</script>"; exit; }
        } else {
            $thumb = !empty($path) ? $path : "uploads/default.png";
            $sql = "INSERT INTO products (brand, model, type, imei_number, sale_price, cost_price, stock_qty, reorder_level, storage, color, pta_status, condition_status, battery_health, sim_type, accessories, is_unlocked, vendor_name, vendor_phone, seller_name, seller_phone, seller_cnic, device_image) VALUES ('$brand', '$model', '$type', '$imei', '$price', '$cost', '$qty', '$reorder_level', '$storage', '$color', '$pta', '$cond', '$bh', '$sim', '$kit', '$unlock', '$vendor', '$vendor_phone', '$s_name', '$s_phone', '$s_cnic', '$thumb')";
            try { $conn->query($sql); } catch (mysqli_sql_exception $e) { echo "<script>alert('Error: Database Error!'); window.location.href='dashboard.php?tab=inventory';</script>"; exit; }
        }
    }
    header("Location: dashboard.php?tab=inventory"); exit;
}

// APPROVAL / RETURN
if (isset($_POST['send_approval'])) {
    $pid = $_POST['app_prod_id']; $holder = mysqli_real_escape_string($conn, $_POST['holder_name']); $contact = mysqli_real_escape_string($conn, $_POST['holder_contact']);
    $res = $conn->query("SELECT * FROM products WHERE id=$pid");
    if($res->num_rows > 0){
        $row = $res->fetch_assoc(); $current_qty = (int)$row['stock_qty'];
        if ($current_qty > 1) {
            $new_qty = $current_qty - 1; $conn->query("UPDATE products SET stock_qty=$new_qty WHERE id=$pid");
            $brand=$row['brand']; $model=$row['model']; $type=$row['type']; $imei=$row['imei_number']; $price=$row['sale_price']; $cost=$row['cost_price']; $storage=$row['storage']; $color=$row['color']; $pta=$row['pta_status']; $cond=$row['condition_status']; $bh=$row['battery_health']; $sim=$row['sim_type']; $kit=$row['accessories']; $unlock=$row['is_unlocked']; $dev_img=$row['device_image'];
            $sql_new = "INSERT INTO products (brand, model, type, imei_number, sale_price, cost_price, stock_qty, storage, color, pta_status, condition_status, battery_health, sim_type, accessories, is_unlocked, device_image, availability_status, approval_holder, approval_contact) VALUES ('$brand', '$model', '$type', '$imei', '$price', '$cost', 1, '$storage', '$color', '$pta', '$cond', '$bh', '$sim', '$kit', '$unlock', '$dev_img', 'On Approval', '$holder', '$contact')";
            if($conn->query($sql_new)){ $new_id = $conn->insert_id; $conn->query("INSERT INTO approval_history (product_id, holder_name, holder_contact, status) VALUES ('$new_id', '$holder', '$contact', 'Pending')"); }
        } else {
            $conn->query("UPDATE products SET availability_status='On Approval', approval_holder='$holder', approval_contact='$contact' WHERE id=$pid");
            $conn->query("INSERT INTO approval_history (product_id, holder_name, holder_contact, status) VALUES ('$pid', '$holder', '$contact', 'Pending')");
        }
    }
    header("Location: dashboard.php?tab=inventory"); exit;
}
if (isset($_GET['return_stock'])) { $pid = $_GET['return_stock']; $conn->query("UPDATE products SET availability_status='Available', approval_holder=NULL, approval_contact=NULL WHERE id=$pid"); $conn->query("UPDATE approval_history SET date_returned=NOW(), status='Returned' WHERE product_id=$pid AND status='Pending'"); header("Location: dashboard.php?tab=inventory"); exit; }

// SAVE CUSTOMER
if (isset($_POST['save_customer'])) {
    $id = $_POST['cust_id']; $name = mysqli_real_escape_string($conn, $_POST['name']); $phone = mysqli_real_escape_string($conn, $_POST['phone']); $cnic = mysqli_real_escape_string($conn, $_POST['cnic']); $source = $_POST['source'] ?? '';
    $target_dir = "uploads/customers/"; if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
    $sql_img = "";
    if(!empty($_FILES['profile_pic']['name'])){ $p_path = $target_dir.time()."_DP_".basename($_FILES['profile_pic']['name']); move_uploaded_file($_FILES['profile_pic']['tmp_name'], $p_path); $sql_img .= ", profile_pic='$p_path'"; }
    if(!empty($_FILES['cnic_front']['name'])){ $cf_path = $target_dir.time()."_CF_".basename($_FILES['cnic_front']['name']); move_uploaded_file($_FILES['cnic_front']['tmp_name'], $cf_path); $sql_img .= ", cnic_front='$cf_path'"; }
    if(!empty($_FILES['cnic_back']['name'])){ $cb_path = $target_dir.time()."_CB_".basename($_FILES['cnic_back']['name']); move_uploaded_file($_FILES['cnic_back']['tmp_name'], $cb_path); $sql_img .= ", cnic_back='$cb_path'"; }

    if(!empty($id)){ $conn->query("UPDATE customers SET name='$name', phone='$phone', cnic='$cnic' $sql_img WHERE id=$id"); } 
    else { 
        $conn->query("INSERT INTO customers (name, phone, cnic) VALUES ('$name', '$phone', '$cnic')"); $new_id = $conn->insert_id;
        if($sql_img != "") { $sql_img_clean = substr($sql_img, 1); $conn->query("UPDATE customers SET $sql_img_clean WHERE id=$new_id"); }
    }
    if($source == 'approval') { header("Location: dashboard.php?tab=inventory"); } else { header("Location: dashboard.php?tab=customers"); } exit;
}

$tab = isset($_GET['tab']) ? $_GET['tab'] : 'sales';
$stock = $conn->query("SELECT * FROM products ORDER BY id DESC");
$cust = $conn->query("SELECT * FROM customers ORDER BY name ASC");
$all_cust = $conn->query("SELECT * FROM customers ORDER BY name ASC");
$setting = $conn->query("SELECT * FROM settings WHERE id=1")->fetch_assoc();

// REPORTS LOGIC
if($tab == 'sales'){
    $sales = $conn->query("SELECT * FROM sales ORDER BY sale_date DESC LIMIT 50");
    $today_sale = $conn->query("SELECT SUM(total_amount) as total FROM sales WHERE DATE(sale_date) = CURDATE()")->fetch_assoc()['total'];
    $today_sale = $today_sale ? $today_sale : 0;

    $today_received = $conn->query("SELECT SUM(paid_amount) as total FROM sales WHERE DATE(sale_date) = CURDATE()")->fetch_assoc()['total'];
    $today_received = $today_received ? $today_received : 0;

    $today_credit = $conn->query("SELECT SUM(balance_amount) as total FROM sales WHERE DATE(sale_date) = CURDATE()")->fetch_assoc()['total'];
    $today_credit = $today_credit ? $today_credit : 0;

    // Profit Calculation (Admin Only)
    if($role == 'Admin') {
        $today_profit_q = $conn->query("SELECT SUM(price - cost) as profit FROM sale_items WHERE sale_id IN (SELECT id FROM sales WHERE DATE(sale_date) = CURDATE())");
        $today_profit = $today_profit_q->fetch_assoc()['profit'];
        $today_profit = $today_profit ? $today_profit : 0;
    }
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root { --bg-dark: #090c10; --glass: rgba(22, 27, 34, 0.6); --neon: #58a6ff; --border: #30363d; }
        body { background: var(--bg-dark); color: white; font-family: 'Poppins', sans-serif; display: flex; height: 100vh; overflow: hidden; }
        .sidebar { width: 250px; background: #0d1117; border-right: 1px solid var(--border); display: flex; flex-direction: column; padding: 20px; }
        .nav-link { color: #8b949e; padding: 12px; border-radius: 8px; margin-bottom: 5px; transition: 0.3s; }
        .nav-link:hover, .nav-link.active { background: #161b22; color: var(--neon); font-weight: 600; }
        .content { flex-grow: 1; overflow-y: auto; padding: 30px; background: radial-gradient(circle at top right, rgba(88,166,255,0.05), transparent); }
        .glass-card { background: #161b22; border: 1px solid var(--border); border-radius: 12px; padding: 20px; margin-bottom: 20px; }
        .table-dark { --bs-table-bg: transparent; }
        .table>:not(caption)>*>* { border-bottom-color: var(--border); color: #c9d1d9; vertical-align: middle; }
        .form-control-dark, .form-select-dark { background: #0d1117; border: 1px solid var(--border); color: white; }
        .form-control-dark:focus { border-color: var(--neon); box-shadow: none; }
        .modal-content { background-color: #161b22; border: 1px solid var(--border-color); color: white; }
        .btn-close { filter: invert(1); }
        .cust-card { background: #161b22; border: 1px solid var(--border); border-radius: 12px; padding: 25px 10px; text-align: center; transition: 0.2s; cursor: pointer; position: relative; display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; min-height: 220px; }
        .cust-card:hover { transform: translateY(-3px); border-color: var(--neon); box-shadow: 0 5px 15px rgba(88,166,255,0.1); }
        .cust-img { width: 70px; height: 70px; border-radius: 50%; object-fit: cover; border: 2px solid var(--border); margin-bottom: 15px; }
        .cust-actions { position: absolute; top: 8px; right: 8px; display: flex; gap: 5px; z-index: 5; }
        .action-btn { width: 26px; height: 26px; border-radius: 50%; font-size: 11px; display: flex; align-items: center; justify-content: center; background: rgba(0,0,0,0.6); color: white; border: 1px solid #444; transition: 0.2s; }
        .action-btn:hover { background: var(--neon); border-color: var(--neon); }
        .action-btn.del:hover { background: #da3633; border-color: #da3633; }
        .info-box { background: #161b22; padding: 20px; border-radius: 10px; border: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    </style>
</head>
<body>

<div class="sidebar">
    <h4 class="fw-bold mb-5 text-white"><i class="fa fa-bolt text-primary me-2"></i>AdminPanel</h4>
    <div class="mb-3 px-2 small text-muted text-uppercase">Role: <span class="text-white fw-bold"><?php echo $role; ?></span></div>
    
    <a href="?tab=sales" class="nav-link <?php echo ($tab=='sales')?'active':''; ?>"><i class="fa fa-chart-line me-2"></i>Sales History</a>
    <a href="?tab=inventory" class="nav-link <?php echo ($tab=='inventory')?'active':''; ?>"><i class="fa fa-boxes me-2"></i>Inventory</a>
    <a href="?tab=customers" class="nav-link <?php echo ($tab=='customers')?'active':''; ?>"><i class="fa fa-users me-2"></i>Customers</a>
    <a href="khata.php" class="nav-link"><i class="fa fa-book me-2"></i>Udhaar Khata</a>
    <a href="reports.php" class="nav-link"><i class="fa fa-chart-column me-2"></i>Reports</a>
    
    <?php if($role == 'Admin'): ?>
        <a href="?tab=users" class="nav-link <?php echo ($tab=='users')?'active':''; ?>"><i class="fa fa-user-shield me-2"></i>Users & Team</a>
        <a href="?tab=settings" class="nav-link <?php echo ($tab=='settings')?'active':''; ?>"><i class="fa fa-cog me-2"></i>Settings</a>
    <?php endif; ?>

    <a href="logout.php" class="nav-link text-danger mt-auto"><i class="fa fa-power-off me-2"></i>Logout</a>
    <a href="index.php" class="nav-link border-top border-secondary pt-3"><i class="fa fa-arrow-left me-2"></i>Back to POS</a>
</div>

<div class="content">
    
    <?php if($tab == 'sales'): ?>
    <div class="row">
        <div class="col-md-3"><div class="info-box border-success"><div><div class="text-muted small text-uppercase">Today's Sale</div><h2 class="fw-bold text-success m-0">Rs. <?php echo number_format($today_sale); ?></h2></div><div class="bg-success bg-opacity-25 p-3 rounded-circle text-success"><i class="fa fa-wallet fs-4"></i></div></div></div>
        <div class="col-md-3"><div class="info-box border-info"><div><div class="text-muted small text-uppercase">Received</div><h2 class="fw-bold text-info m-0">Rs. <?php echo number_format($today_received); ?></h2></div><div class="bg-info bg-opacity-25 p-3 rounded-circle text-info"><i class="fa fa-money-bill fs-4"></i></div></div></div>
        <div class="col-md-3"><div class="info-box border-warning"><div><div class="text-muted small text-uppercase">Credit</div><h2 class="fw-bold text-warning m-0">Rs. <?php echo number_format($today_credit); ?></h2></div><div class="bg-warning bg-opacity-25 p-3 rounded-circle text-warning"><i class="fa fa-book fs-4"></i></div></div></div>
        
        <?php if($role == 'Admin'): ?>
        <div class="col-md-3"><div class="info-box border-secondary"><div><div class="text-muted small text-uppercase">Today's Profit</div><h2 class="fw-bold text-light m-0">Rs. <?php echo number_format($today_profit); ?></h2></div><div class="bg-secondary bg-opacity-25 p-3 rounded-circle text-light"><i class="fa fa-chart-pie fs-4"></i></div></div></div>
        <?php endif; ?>
    </div>
    
    <div class="glass-card"><h5 class="mb-3 text-white">Recent Sales</h5><div class="table-responsive"><table class="table table-dark table-hover"><thead><tr><th>Inv #</th><th>Date</th><th>Customer</th><th>Amount</th><th>Paid</th><th>Due</th><th>Method</th><th class="text-end">Action</th></tr></thead><tbody><?php while($sale = $sales->fetch_assoc()): ?><tr><td>#<?php echo $sale['id']; ?></td><td><?php echo date('d M, h:i A', strtotime($sale['sale_date'])); ?></td><td><?php echo $sale['customer_name']; ?></td><td class="fw-bold text-success">Rs. <?php echo number_format($sale['total_amount']); ?></td><td class="text-info">Rs. <?php echo number_format($sale['paid_amount']); ?></td><td class="text-warning">Rs. <?php echo number_format($sale['balance_amount']); ?></td><td><?php echo htmlspecialchars($sale['payment_method']); ?></td><td class="text-end"><a href="invoice.php?sale_id=<?php echo $sale['id']; ?>" target="_blank" class="btn btn-sm btn-primary rounded-pill px-3">Print</a></td></tr><?php endwhile; ?></tbody></table></div></div>
    <?php endif; ?>

    <?php if($tab == 'inventory'): ?>
    <div class="d-flex justify-content-between align-items-center mb-4"><h3 class="fw-bold">Product Inventory</h3><button class="btn btn-primary rounded-pill px-4" onclick="openProductModal(null, 'New')">Add Stock</button></div>
    <div class="glass-card"><div class="table-responsive"><table class="table table-dark table-hover"><thead><tr><th>Device</th><th>Qty</th><th>Reorder</th><th>Price</th><th>Supplier</th><th class="text-end">Action</th></tr></thead><tbody>
        <?php while($row = $stock->fetch_assoc()): 
            $row_json = htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8');
            $supplierPhone = preg_replace('/[^0-9]/', '', (string)$row['vendor_phone']);
            if (substr($supplierPhone, 0, 1) === '0') { $supplierPhone = '92' . substr($supplierPhone, 1); }
            $reorderMsg = urlencode('Salam, ' . $row['model'] . ' ka stock low hai. Reorder required.');
            $reorderLink = !empty($supplierPhone) ? ('https://wa.me/' . $supplierPhone . '?text=' . $reorderMsg) : '';
            $is_low_stock = ((int)$row['stock_qty'] <= (int)$row['reorder_level']);
        ?>
        <tr style="<?php echo ($row['availability_status']=='On Approval') ? 'background: rgba(255, 193, 7, 0.05);' : ''; ?>">
            <td><?php echo $row['model']; ?></td>
            <td><?php if($is_low_stock): ?><span class="badge bg-danger"><?php echo $row['stock_qty']; ?></span><?php else: ?><?php echo $row['stock_qty']; ?><?php endif; ?></td>
            <td><?php echo (int)$row['reorder_level']; ?></td>
            <td>Rs. <?php echo number_format($row['sale_price']); ?></td>
            <td><div><?php echo $row['vendor_name'] ? htmlspecialchars($row['vendor_name']) : '-'; ?></div><small class="text-muted"><?php echo $row['vendor_phone'] ? htmlspecialchars($row['vendor_phone']) : 'No phone'; ?></small></td>
            <td class="text-end">
                <?php if($row['availability_status'] == 'Available'): ?>
                    <?php if(!empty($reorderLink) && $is_low_stock): ?><a href="<?php echo $reorderLink; ?>" target="_blank" class="btn btn-sm btn-outline-success me-1" title="Reorder"><i class="fab fa-whatsapp"></i></a><?php endif; ?>
                    <button class="btn btn-sm btn-outline-warning me-1" onclick='openApprovalModal(<?php echo $row['id']; ?>, "<?php echo $row['model']; ?>")'><i class="fa fa-handshake"></i></button>
                    <button class="btn btn-sm btn-outline-info me-1" data-product='<?php echo $row_json; ?>' onclick="editProduct(this)"><i class="fa fa-pencil"></i></button>
                    <?php if($role == 'Admin'): ?><a href="?del_prod=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete?')"><i class="fa fa-trash"></i></a><?php endif; ?>
                <?php else: ?>
                    <a href="?return_stock=<?php echo $row['id']; ?>" class="btn btn-sm btn-success me-1" onclick="return confirm('Return to stock?')"><i class="fa fa-undo me-1"></i> Return</a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody></table></div></div>
    <?php endif; ?>

    <?php if($tab == 'customers'): ?>
    <div class="d-flex justify-content-between align-items-center mb-4"><div><h3 class="fw-bold m-0">Customers</h3></div><div class="d-flex gap-2"><input type="text" id="custSearchInput" class="form-control form-control-dark" placeholder="Search..." style="width: 200px;"><button class="btn btn-primary rounded-pill px-4" onclick="openCustModal(null, 'customer')">Add</button></div></div>
    <div class="row g-3" id="custGrid"><?php while($c = $cust->fetch_assoc()): $json_c = htmlspecialchars(json_encode($c), ENT_QUOTES, 'UTF-8'); ?><div class="col-xl-2 col-lg-2 col-md-3 col-6 cust-item"><div class="cust-card" onclick='viewCustomer(<?php echo $json_c; ?>)'><div class="cust-actions" onclick="event.stopPropagation()"><button class="action-btn bg-dark" title="Edit" onclick='openCustModal(<?php echo $json_c; ?>)'><i class="fa fa-pencil"></i></button><?php if($role == 'Admin'): ?><a href="?del_cust=<?php echo $c['id']; ?>" class="action-btn del" onclick="return confirm('Delete?')"><i class="fa fa-trash"></i></a><?php endif; ?></div><img src="<?php echo !empty($c['profile_pic'])?$c['profile_pic']:'https://cdn-icons-png.flaticon.com/512/149/149071.png'; ?>" class="cust-img"><div class="fw-bold text-white text-truncate w-100"><?php echo $c['name']; ?></div><div class="text-warning small"><?php echo $c['phone']; ?></div></div></div><?php endwhile; ?></div>
    <?php endif; ?>

    <?php if($tab == 'users' && $role == 'Admin'): ?>
    <div class="d-flex justify-content-between align-items-center mb-4"><h3 class="fw-bold">Users Management</h3><button class="btn btn-success rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#userModal">New User</button></div>
    <div class="row"><?php $users = $conn->query("SELECT * FROM users"); while($u = $users->fetch_assoc()): ?><div class="col-md-3"><div class="glass-card text-center"><div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:50px; height:50px;"><i class="fa fa-user text-white fs-4"></i></div><h5 class="fw-bold mb-1"><?php echo $u['username']; ?></h5><span class="badge <?php echo ($u['role']=='Admin')?'bg-danger':'bg-info'; ?> mb-3"><?php echo $u['role']; ?></span><div class="d-flex justify-content-center gap-2"><button class="btn btn-sm btn-dark w-100" onclick="editUser('<?php echo $u['username']; ?>', '<?php echo $u['role']; ?>')">Change Pass</button><?php if($u['username'] != 'admin'): ?><a href="?del_user=<?php echo $u['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete User?')"><i class="fa fa-trash"></i></a><?php endif; ?></div></div></div><?php endwhile; ?></div>
    <?php endif; ?>

    <?php if($tab == 'settings' && $role == 'Admin'): ?>
    <div class="row"><div class="col-md-6"><div class="glass-card"><form method="POST"><h5 class="mb-4 text-warning">Shop Settings</h5><div class="mb-3"><label>Shop Name</label><input type="text" name="shop_name" class="form-control form-control-dark" value="<?php echo $setting['shop_name']; ?>"></div><div class="mb-3"><label>Printer</label><select name="printer_type" class="form-select form-select-dark"><option value="A4" <?php echo ($setting['printer_type']=='A4')?'selected':''; ?>>A4</option><option value="Thermal" <?php echo ($setting['printer_type']=='Thermal')?'selected':''; ?>>Thermal</option></select></div><div class="mb-3"><label>Contact</label><input type="text" name="shop_contact" class="form-control form-control-dark" value="<?php echo $setting['shop_contact']; ?>"></div><div class="mb-3"><label>Address</label><textarea name="shop_address" class="form-control form-control-dark"><?php echo $setting['shop_address']; ?></textarea></div><hr class="border-secondary"><div class="mb-3"><label>New Password (Admin)</label><input type="password" name="new_password" class="form-control form-control-dark" placeholder="Optional"></div><button type="submit" name="save_settings" class="btn btn-success w-100 fw-bold">Save</button></form></div></div></div>
    <?php endif; ?>

</div>

<div class="modal fade" id="userModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header border-secondary"><h5 class="modal-title">Manage User</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><div class="modal-body"><form method="POST"><div class="mb-3"><label>Username</label><input type="text" name="username" id="u_name" class="form-control form-control-dark" required></div><div class="mb-3"><label>New Password</label><input type="text" name="password" class="form-control form-control-dark" placeholder="Enter new password" required></div><div class="mb-3"><label>Role</label><select name="role" id="u_role" class="form-select form-select-dark"><option>Staff</option><option>Admin</option></select></div><button type="submit" name="save_user" class="btn btn-success w-100">Save User</button></form></div></div></div></div>

<div class="modal fade" id="productModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header border-secondary"><h5 class="modal-title" id="prodModalTitle">Product</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><div class="modal-body"><form method="POST" enctype="multipart/form-data"><input type="hidden" name="prod_id" id="prod_id"><div class="mb-3"><label class="small">Type</label><select name="type" id="type_selector" class="form-select form-select-dark" onchange="toggleFields()"><option value="New">New</option><option value="Used">Used</option></select></div><div class="row g-2 mb-3"><div class="col"><input name="brand" id="brand" class="form-control form-control-dark" placeholder="Brand" required></div><div class="col"><input name="model" id="model" class="form-control form-control-dark" placeholder="Model" required></div><div class="col"><input name="storage" id="storage" class="form-control form-control-dark" placeholder="Storage"></div></div><div class="row g-2 mb-3"><div class="col"><input name="price" id="price" class="form-control form-control-dark" placeholder="Sale Price"></div><div class="col"><input name="cost" id="cost" class="form-control form-control-dark" placeholder="Cost"></div><div class="col"><input name="qty" id="qty" class="form-control form-control-dark" value="1"></div><div class="col"><input name="imei" id="imei" class="form-control form-control-dark" placeholder="IMEI"></div></div><div class="row g-2 mb-3"><div class="col"><input name="color" id="color" class="form-control form-control-dark" placeholder="Color"></div><div class="col"><input name="bh" id="bh" class="form-control form-control-dark" placeholder="BH"></div><div class="col"><select name="pta" id="pta" class="form-select form-select-dark"><option>Approved</option><option>Non-PTA</option></select></div><div class="col"><select name="condition" id="condition" class="form-select form-select-dark"><option>10/10</option><option>9/10</option></select></div></div><div class="row g-2 mb-3"><div class="col"><select name="sim" id="sim" class="form-select form-select-dark"><option>Physical + eSIM</option></select></div><div class="col"><select name="kit" id="kit" class="form-select form-select-dark"><option>Complete Box</option></select></div><div class="col"><select name="unlock" id="unlock" class="form-select form-select-dark"><option value="Yes">Yes</option></select></div></div><hr class="border-secondary"><div id="new_fields"><div class="row g-2 mb-3"><div class="col"><input name="vendor_name" id="vendor_name" class="form-control form-control-dark" placeholder="Vendor"></div><div class="col"><input name="vendor_phone" id="vendor_phone" class="form-control form-control-dark" placeholder="Supplier Phone"></div><div class="col"><input type="number" min="1" name="reorder_level" id="reorder_level" class="form-control form-control-dark" placeholder="Reorder Level" value="1"></div></div><div class="mb-3"><input type="file" name="main_pic" class="form-control form-control-dark"></div></div><div id="used_fields" style="display:none;"><div class="row g-2 mb-3"><div class="col"><input name="seller_name" id="seller_name" class="form-control form-control-dark form-control-sm" placeholder="Seller"></div><div class="col"><input name="seller_phone" id="seller_phone" class="form-control form-control-dark form-control-sm" placeholder="Phone"></div><div class="col"><input name="seller_cnic" id="seller_cnic" class="form-control form-control-dark form-control-sm" placeholder="CNIC"></div></div><div class="p-2 border rounded bg-dark"><input type="file" name="main_pic" class="form-control form-control-dark form-control-sm mb-2"><input type="file" name="gallery_pics[]" class="form-control form-control-dark form-control-sm" multiple></div></div><button type="submit" name="save_product" class="btn btn-primary w-100 mt-4">Save</button></form></div></div></div></div>

<div class="modal fade" id="custModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header border-secondary"><h5 class="modal-title">Edit / Add Customer</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><div class="modal-body"><form method="POST" enctype="multipart/form-data"><input type="hidden" name="cust_id" id="cust_id"><input type="hidden" name="source" id="cust_source" value=""><div class="text-center mb-4"><img id="prev_dp" src="https://cdn-icons-png.flaticon.com/512/149/149071.png" style="width:80px; height:80px; border-radius:50%; object-fit:cover; border:3px solid #30363d;"><div class="mt-2"><label class="btn btn-sm btn-outline-primary py-0" for="dp_upload" style="font-size:12px;">Change</label><input type="file" name="profile_pic" id="dp_upload" hidden></div></div><div class="mb-3"><label class="small text-muted">Name</label><input type="text" name="name" id="c_name" class="form-control form-control-dark" required></div><div class="row g-2 mb-3"><div class="col"><label class="small text-muted">Phone</label><input type="text" name="phone" id="c_phone" class="form-control form-control-dark" required></div><div class="col"><label class="small text-muted">CNIC</label><input type="text" name="cnic" id="c_cnic" class="form-control form-control-dark" required></div></div><div class="p-3 bg-black rounded border border-secondary mb-3 bg-opacity-25"><h6 class="text-warning small mb-3">Upload CNIC Photos</h6><div class="row g-2"><div class="col-6"><label class="small text-muted d-block mb-1">Front</label><input type="file" name="cnic_front" class="form-control form-control-dark form-control-sm"></div><div class="col-6"><label class="small text-muted d-block mb-1">Back</label><input type="file" name="cnic_back" class="form-control form-control-dark form-control-sm"></div></div></div><button type="submit" name="save_customer" class="btn btn-primary w-100">Save Changes</button></form></div></div></div></div>

<div class="modal fade" id="viewCustModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content" style="background: #161b22; border: 1px solid #30363d;"><div class="modal-body text-center p-4"><button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal"></button><img id="view_dp" src="" style="width:100px; height:100px; border-radius:50%; object-fit:cover; border:4px solid var(--neon); margin-bottom:15px;"><h3 class="fw-bold text-white mb-1" id="view_name">Name</h3><div class="text-warning fs-5 mb-1" id="view_phone">Phone</div><div class="badge bg-dark border border-secondary font-monospace fs-6 mb-4" id="view_cnic">CNIC</div><div class="row g-3"><div class="col-6"><div class="p-2 border border-secondary rounded bg-black"><small class="text-muted d-block mb-2">CNIC FRONT</small><img id="view_cnic_f" src="" class="img-fluid rounded" style="height:80px; object-fit:cover; cursor:pointer" onclick="viewImage(this.src)"></div></div><div class="col-6"><div class="p-2 border border-secondary rounded bg-black"><small class="text-muted d-block mb-2">CNIC BACK</small><img id="view_cnic_b" src="" class="img-fluid rounded" style="height:80px; object-fit:cover; cursor:pointer" onclick="viewImage(this.src)"></div></div></div><button onclick="shareCustomer()" class="btn btn-success w-100 fw-bold mt-4 shadow"><i class="fab fa-whatsapp me-2"></i> Share Details & CNIC</button></div></div></div></div>

<div class="modal fade" id="imagePreviewModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content bg-black border-secondary"><div class="modal-body text-center p-0 position-relative"><button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal"></button><img id="full_image" src="" style="max-width:100%; max-height:80vh;"></div><div class="modal-footer bg-dark border-top border-secondary justify-content-center"><a id="download_btn" href="#" download class="btn btn-primary"><i class="fa fa-download me-2"></i> Download Image</a></div></div></div></div>

<div class="modal fade" id="approvalModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-body"><form method="POST"><input type="hidden" name="app_prod_id" id="app_prod_id"><h5 class="text-warning mb-3">Approval / Hold</h5><div class="input-group mb-2"><input list="approval_cust_list" id="approval_search" name="holder_name" class="form-control form-control-dark" placeholder="Search Customer..." required><button type="button" class="btn btn-dark border-secondary" onclick="openCustModal(null, 'approval')"><i class="fa fa-plus"></i></button></div><datalist id="approval_cust_list"><?php $all_cust->data_seek(0); while($c = $all_cust->fetch_assoc()): ?><option value="<?php echo $c['name']; ?>"><?php echo $c['phone']; ?></option><?php endwhile; ?></datalist><input type="text" id="app_contact" name="holder_contact" class="form-control form-control-dark mb-3" placeholder="Contact Number"><button type="submit" name="send_approval" class="btn btn-warning w-100 fw-bold">Confirm Hold</button></form></div></div></div></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function editUser(name, role) { document.getElementById('u_name').value = name; document.getElementById('u_role').value = role; new bootstrap.Modal(document.getElementById('userModal')).show(); }
    function editProduct(btn) { let data = JSON.parse(btn.getAttribute('data-product')); openProductModal(data); }
    function openProductModal(data = null, type = 'New') {
        let modal = new bootstrap.Modal(document.getElementById('productModal')); let form = document.querySelector('#productModal form');
        if (data) { document.getElementById('prodModalTitle').innerText = "Edit Product"; document.getElementById('prod_id').value = data.id; if(document.getElementById('brand')) document.getElementById('brand').value = data.brand; if(document.getElementById('model')) document.getElementById('model').value = data.model; if(document.getElementById('imei')) document.getElementById('imei').value = data.imei_number; if(document.getElementById('storage')) document.getElementById('storage').value = data.storage; if(document.getElementById('price')) document.getElementById('price').value = data.sale_price; if(document.getElementById('cost')) document.getElementById('cost').value = data.cost_price; if(document.getElementById('qty')) document.getElementById('qty').value = data.stock_qty; if(document.getElementById('color')) document.getElementById('color').value = data.color; if(document.getElementById('pta')) document.getElementById('pta').value = data.pta_status; if(document.getElementById('condition')) document.getElementById('condition').value = data.condition_status; if(document.getElementById('bh')) document.getElementById('bh').value = data.battery_health; if(document.getElementById('sim')) document.getElementById('sim').value = data.sim_type; if(document.getElementById('kit')) document.getElementById('kit').value = data.accessories; if(document.getElementById('unlock')) document.getElementById('unlock').value = data.is_unlocked; document.getElementById('type_selector').value = data.type; toggleFields(); if(data.type === 'New'){ document.getElementById('vendor_name').value = data.vendor_name || ''; document.getElementById('vendor_phone').value = data.vendor_phone || ''; document.getElementById('reorder_level').value = data.reorder_level || 1; } else { document.getElementById('seller_name').value = data.seller_name; document.getElementById('seller_phone').value = data.seller_phone; document.getElementById('seller_cnic').value = data.seller_cnic; } } 
        else { document.getElementById('prodModalTitle').innerText = "Add Stock"; form.reset(); document.getElementById('prod_id').value = ""; document.getElementById('type_selector').value = type; toggleFields(); }
        modal.show();
    }
    function toggleFields() { let type = document.getElementById('type_selector').value; if(type === 'New') { document.getElementById('new_fields').style.display = 'block'; document.getElementById('used_fields').style.display = 'none'; } else { document.getElementById('new_fields').style.display = 'none'; document.getElementById('used_fields').style.display = 'block'; } }
    let custShareData = null;
    function viewCustomer(data) { custShareData = data; let def = "https://cdn-icons-png.flaticon.com/512/149/149071.png"; let cnic_ph = "https://via.placeholder.com/200x100?text=No+Image"; document.getElementById('view_dp').src = data.profile_pic || def; document.getElementById('view_name').innerText = data.name; document.getElementById('view_phone').innerText = data.phone; document.getElementById('view_cnic').innerText = data.cnic; document.getElementById('view_cnic_f').src = data.cnic_front || cnic_ph; document.getElementById('view_cnic_b').src = data.cnic_back || cnic_ph; new bootstrap.Modal(document.getElementById('viewCustModal')).show(); }
    function viewImage(src) { if(src.includes("No+Image")) return; document.getElementById('full_image').src = src; document.getElementById('download_btn').href = src; new bootstrap.Modal(document.getElementById('imagePreviewModal')).show(); }
    function openCustModal(data = null, source = '') { document.getElementById('cust_source').value = source; let defaultImg = "https://cdn-icons-png.flaticon.com/512/149/149071.png"; if(data){ document.getElementById('cust_id').value = data.id; document.getElementById('c_name').value = data.name; document.getElementById('c_phone').value = data.phone; document.getElementById('c_cnic').value = data.cnic; document.getElementById('prev_dp').src = data.profile_pic || defaultImg; } else { document.querySelector('#custModal form').reset(); document.getElementById('cust_id').value = ""; document.getElementById('cust_source').value = source; document.getElementById('prev_dp').src = defaultImg; } new bootstrap.Modal(document.getElementById('custModal')).show(); }
    function openApprovalModal(id, model) { document.getElementById('app_prod_id').value = id; new bootstrap.Modal(document.getElementById('approvalModal')).show(); }
    if(document.getElementById('custSearchInput')){ document.getElementById('custSearchInput').addEventListener('keyup', function() { let filter = this.value.toLowerCase(); document.querySelectorAll('.cust-item').forEach(card => { let name = card.innerText.toLowerCase(); card.style.display = name.includes(filter) ? 'block' : 'none'; }); }); }
    if(document.getElementById('approval_search')){ document.getElementById('approval_search').addEventListener('change', function(){ let val = this.value; let formData = new FormData(); formData.append('action', 'find_customer'); formData.append('type', 'name'); formData.append('val', val); fetch('api_handler.php', { method: 'POST', body: formData }).then(res => res.json()).then(d => { if(d.status === 'found') { document.getElementById('app_contact').value = d.data.phone; } }); }); }
    async function shareCustomer() { if (!custShareData) return; const text = `*Customer Profile*\n👤 Name: ${custShareData.name}\n📞 Phone: ${custShareData.phone}\n🆔 CNIC: ${custShareData.cnic}`; try { let filesArray = []; if (custShareData.cnic_front) { const res1 = await fetch(custShareData.cnic_front); const blob1 = await res1.blob(); filesArray.push(new File([blob1], "cnic_front.jpg", { type: blob1.type })); } if (custShareData.cnic_back) { const res2 = await fetch(custShareData.cnic_back); const blob2 = await res2.blob(); filesArray.push(new File([blob2], "cnic_back.jpg", { type: blob2.type })); } if (navigator.share && filesArray.length > 0) { await navigator.share({ title: custShareData.name, text: text, files: filesArray }); } else { window.open(`https://wa.me/?text=${encodeURIComponent(text)}`); } } catch (error) { window.open(`https://wa.me/?text=${encodeURIComponent(text)}`); } }
</script>
</body>
</html>





