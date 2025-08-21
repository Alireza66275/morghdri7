<?php
session_start();
require_once("../config/db.php");

// فقط ادمین دسترسی دارد
if ($_SESSION["role"] != "admin") {
    die("دسترسی غیرمجاز ❌");
}

$id = $_GET["id"] ?? 0;

// دریافت اطلاعات کاربر
$stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    die("کاربر یافت نشد ❌");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $username = $_POST["username"];
    $role = $_POST["role"];

    // اگر رمز جدید وارد شد → بروزرسانی رمز
    if (!empty($_POST["password"])) {
        $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET name=?, username=?, password=?, role=? WHERE id=?");
        $stmt->execute([$name, $username, $password, $role, $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET name=?, username=?, role=? WHERE id=?");
        $stmt->execute([$name, $username, $role, $id]);
    }

    header("Location: list.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <title>ویرایش کاربر</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">
    <h2>ویرایش کاربر</h2>
    <form method="post">
        <input type="text" name="name" value="<?= $user['name'] ?>" class="form-control mb-2" required>
        <input type="text" name="username" value="<?= $user['username'] ?>" class="form-control mb-2" required>
        <input type="password" name="password" placeholder="رمز جدید (اختیاری)" class="form-control mb-2">
        <select name="role" class="form-control mb-2">
            <option value="modir_amol" <?= $user["role"]=="modir_amol"?"selected":"" ?>>مدیر عامل</option>
            <option value="modir_farm" <?= $user["role"]=="modir_farm"?"selected":"" ?>>مدیر فارم</option>
            <option value="modir_fanni" <?= $user["role"]=="modir_fanni"?"selected":"" ?>>مدیر فنی</option>
            <option value="kargar" <?= $user["role"]=="kargar"?"selected":"" ?>>کارگر</option>
            <option value="admin" <?= $user["role"]=="admin"?"selected":"" ?>>ادمین</option>
        </select>
        <button type="submit" class="btn btn-primary">ذخیره تغییرات</button>
    </form>
    <a href="list.php" class="btn btn-secondary mt-3">بازگشت</a>
</body>
</html>
