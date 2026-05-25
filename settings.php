<?php
include 'db.php';
include 'auth.php'; // Sirf Admin access kare

$msg = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['shop_name'];
    $phone = $_POST['shop_phone'];
    $theme = $_POST['theme_mode']; // New Setting

    $sql = "UPDATE shop_settings SET shop_name='$name', shop_phone='$phone', theme_mode='$theme' WHERE id=1";
    if ($conn->query($sql)) {
        $msg = "✅ Settings Updated!";
        // Refresh data
        $shop = $conn->query("SELECT * FROM shop_settings WHERE id=1")->fetch_assoc();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow border-0">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">⚙️ Software Settings</h5>
                </div>
                <div class="card-body">
                    <?php if($msg) echo "<div class='alert alert-success'>$msg</div>"; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label>Shop Name</label>
                            <input type="text" name="shop_name" class="form-control" value="<?php echo $shop['shop_name']; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label>Phone Number</label>
                            <input type="text" name="shop_phone" class="form-control" value="<?php echo $shop['shop_phone']; ?>">
                        </div>

                        <hr>
                        
                        <div class="mb-3">
                            <label class="fw-bold">Display Theme</label>
                            <select name="theme_mode" class="form-select bg-warning text-dark fw-bold">
                                <option value="dark" <?php if($shop['theme_mode']=='dark') echo 'selected'; ?>>🌑 Always Dark (Midnight)</option>
                                <option value="light" <?php if($shop['theme_mode']=='light') echo 'selected'; ?>>☀️ Always Light</option>
                                <option value="auto" <?php if($shop['theme_mode']=='auto') echo 'selected'; ?>>🌗 Auto (Day/Night Cycle)</option>
                            </select>
                            <small class="text-muted">Auto mode will switch to Dark at 6:00 PM.</small>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Save Changes</button>
                        <a href="index.php" class="btn btn-outline-secondary w-100 mt-2">Back to POS</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>