<?php
session_start();
require_once("../config/db.php");

// فقط ادمین اجازه دارد
if ($_SESSION["role"] != "admin") {
    die("دسترسی غیرمجاز ❌");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $username = $_POST["username"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $role = $_POST["role"];

    $stmt = $pdo->prepare("INSERT INTO users (name, username, password, role) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $username, $password, $role]);

    header("Location: list.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <title>افزودن کاربر</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">
    <h2>افزودن کاربر جدید</h2>
    <form method="post">
        <input type="text" name="name" placeholder="نام کامل" class="form-control mb-2" required>
        <input type="text" name="username" placeholder="نام کاربری" class="form-control mb-2" required>
        <input type="password" name="password" placeholder="رمز عبور" class="form-control mb-2" required>
        <select name="role" class="form-control mb-2">
            <option value="modir_amol">مدیر عامل</option>
            <option value="modir_farm">مدیر فارم</option>
            <option value="modir_fanni">مدیر فنی</option>
            <option value="kargar">کارگر</option>
            <option value="admin">ادمین</option>
        </select>
        <button type="submit" class="btn btn-success">ثبت</button>
    </form>
    <a href="list.php" class="btn btn-secondary mt-3">بازگشت</a>
</body>
</html>
