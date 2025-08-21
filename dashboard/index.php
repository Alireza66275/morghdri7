<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <title>داشبورد</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">
    <h2>خوش آمدید، نقش شما: <?= $_SESSION["role"] ?></h2>
    <ul>
        <li><a href="../farms/list.php">مدیریت فارم‌ها</a></li>
        <li><a href="../reports/add.php">ثبت گزارش روزانه</a></li>
        <li><a href="../users/list.php">مدیریت کاربران</a></li>
        <li><a href="../auth/logout.php">خروج</a></li>
    </ul>
</body>
</html>
