<?php
session_start();
include 'db.php';

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: index.php"); exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['username'] = $row['username'];
        $_SESSION['role'] = $row['role']; // Role Save kar liya
        
        header("Location: index.php");
        exit;
    } else {
        $error = "Ghalat Username ya Password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>Login | POS System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #090c10; color: white; font-family: sans-serif; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { background: #161b22; border: 1px solid #30363d; padding: 40px; width: 350px; border-radius: 15px; text-align: center; }
        .form-control-dark { background: #0d1117; border: 1px solid #30363d; color: white; margin-bottom: 15px; }
        .btn-login { background: #58a6ff; border: none; color: white; width: 100%; padding: 10px; border-radius: 5px; font-weight: bold; }
    </style>
</head>
<body>
<div class="login-card">
    <i class="fa fa-user-circle fa-3x text-primary mb-3"></i>
    <h4 class="mb-4">POS Login</h4>
    <?php if($error): ?><div class="alert alert-danger p-2 small"><?php echo $error; ?></div><?php endif; ?>
    <form method="POST">
        <input type="text" name="username" class="form-control form-control-dark" placeholder="Username" required>
        <input type="password" name="password" class="form-control form-control-dark" placeholder="Password" required>
        <button type="submit" class="btn btn-login">LOGIN</button>
    </form>
</div>
</body>
</html>