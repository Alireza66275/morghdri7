<?php
session_start();
require_once("../config/db.php");

if ($_SESSION["role"] == "modir_farm") {
    $stmt = $pdo->prepare("
        SELECT s.*, f.name AS farm_name 
        FROM saloons s 
        JOIN farms f ON s.farm_id=f.id
        WHERE f.manager_id=?
    ");
    $stmt->execute([$_SESSION["user_id"]]);
} else {
    // ادمین یا مدیر عامل → همه سالن‌ها
    $stmt = $pdo->query("
        SELECT s.*, f.name AS farm_name 
        FROM saloons s 
        JOIN farms f ON s.farm_id=f.id
    ");
}


$saloons = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <title>مدیریت سالن‌ها</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">
    <h2>لیست سالن‌ها</h2>
    <a href="add.php" class="btn btn-success mb-3">افزودن سالن</a>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>شناسه</th>
                <th>نام سالن</th>
                <th>فارم</th>
                <th>عملیات</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($saloons as $s): ?>
            <tr>
                <td><?= $s["id"] ?></td>
                <td><?= $s["name"] ?></td>
                <td><?= $s["farm_name"] ?></td>
                <td>
                    <a href="edit.php?id=<?= $s["id"] ?>" class="btn btn-primary btn-sm">ویرایش</a>
                    <a href="delete.php?id=<?= $s["id"] ?>" class="btn btn-danger btn-sm" onclick="return confirm('مطمئنی؟')">حذف</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <a href="../dashboard/index.php" class="btn btn-secondary">بازگشت</a>
</body>
</html>
