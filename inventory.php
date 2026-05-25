<?php
include 'db.php';
include 'auth.php'; // Security ON

// Agar Stock Add kiya jaye
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $brand = $_POST['brand'];
    $model = $_POST['model'];
    $cost = $_POST['cost'];
    $sale = $_POST['sale'];
    $qty = $_POST['qty'];

    $sql = "INSERT INTO products (brand, model, cost_price, sale_price, stock_qty) 
            VALUES ('$brand', '$model', '$cost', '$sale', '$qty')";
    
    if($conn->query($sql)){
        $msg = "✅ Stock Added Successfully!";
    } else {
        $msg = "❌ Error: " . $conn->error;
    }
}

// Stock Delete Logic
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM products WHERE id=$id");
    header("Location: inventory.php");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Inventory - <?php echo $shop_name; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f4f6f9; }
        .sidebar { height: 100vh; background: #343a40; color: white; position: fixed; width: 220px; padding-top: 20px; }
        .sidebar a { color: #cfd2d6; text-decoration: none; display: block; padding: 12px 20px; }
        .sidebar a:hover { background: #495057; color: white; }
        .main-content { margin-left: 220px; padding: 30px; }
    </style>
</head>
<body>

<div class="sidebar">
    <h4 class="text-center text-white mb-4">Inventory</h4>
    <a href="index.php"><i class="fa fa-home me-2"></i> Dashboard</a>
    <a href="inventory.php" class="bg-primary text-white"><i class="fa fa-box me-2"></i> Stock Manager</a>
    <a href="settings.php"><i class="fa fa-cog me-2"></i> Settings</a>
    <a href="logout.php" class="text-danger mt-5"><i class="fa fa-power-off me-2"></i> Logout</a>
</div>

<div class="main-content">
    <h2 class="mb-4">Stock Management</h2>

    <?php if(isset($msg)) { echo "<div class='alert alert-info'>$msg</div>"; } ?>

    <div class="card p-4 shadow-sm mb-4">
        <h5 class="mb-3">Add New Mobile</h5>
        <form method="POST" class="row g-3">
            <div class="col-md-3">
                <input type="text" name="brand" class="form-control" placeholder="Brand (e.g Samsung)" required>
            </div>
            <div class="col-md-3">
                <input type="text" name="model" class="form-control" placeholder="Model Name" required>
            </div>
            <div class="col-md-2">
                <input type="number" name="cost" class="form-control" placeholder="Cost Price" required>
            </div>
            <div class="col-md-2">
                <input type="number" name="sale" class="form-control" placeholder="Sale Price" required>
            </div>
            <div class="col-md-2">
                <input type="number" name="qty" class="form-control" placeholder="Quantity" required>
            </div>
            <div class="col-12 text-end">
                <button type="submit" class="btn btn-success"><i class="fa fa-plus"></i> Add to Stock</button>
            </div>
        </form>
    </div>

    <div class="card p-4 shadow-sm">
        <h5 class="mb-3">Available Stock</h5>
        <table class="table table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Brand</th>
                    <th>Model</th>
                    <th>Cost (Purchase)</th>
                    <th>Sale Price</th>
                    <th>Qty</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $res = $conn->query("SELECT * FROM products ORDER BY id DESC");
                while ($row = $res->fetch_assoc()) {
                    // Logic to show Low Stock Warning
                    $stock_alert = ($row['stock_qty'] < 2) ? "text-danger fw-bold" : "";
                ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['brand']; ?></td>
                    <td><?php echo $row['model']; ?></td>
                    <td><?php echo number_format($row['cost_price']); ?></td>
                    <td><?php echo number_format($row['sale_price']); ?></td>
                    <td class="<?php echo $stock_alert; ?>"><?php echo $row['stock_qty']; ?></td>
                    <td>
                        <a href="inventory.php?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')"><i class="fa fa-trash"></i></a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>