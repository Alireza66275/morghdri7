<?php
require_once("../config/db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name     = $_POST["name"];
    $username = $_POST["username"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT); // هش کردن رمز
    $role     = "admin"; // فقط ادمین

    try {
        $stmt = $pdo->prepare("INSERT INTO users (name, username, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $username, $password, $role]);
        $success = "ادمین با موفقیت ایجاد شد ✅";
    } catch (Exception $e) {
        $error = "خطا در ایجاد کاربر: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <title>ایجاد ادمین اولیه</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">
    <h2>ایجاد کاربر ادمین اولیه</h2>
    <form method="post">
        <input type="text" name="name" placeholder="نام کامل" class="form-control mb-2" required>
        <input type="text" name="username" placeholder="نام کاربری" class="form-control mb-2" required>
        <input type="password" name="password" placeholder="رمز عبور" class="form-control mb-2" required>
        <button type="submit" class="btn btn-success">ایجاد ادمین</button>
    </form>

    <?php if (isset($success)) echo "<div class='alert alert-success mt-3'>$success</div>"; ?>
    <?php if (isset($error)) echo "<div class='alert alert-danger mt-3'>$error</div>"; ?>
</body>
</html>
