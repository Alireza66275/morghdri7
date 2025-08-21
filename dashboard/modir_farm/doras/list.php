<?php
session_start();
require_once("../config/db.php");

if ($_SESSION["role"] != "modir_fanni" && $_SESSION["role"] != "admin") {
    die("دسترسی غیرمجاز ❌");
}

$stmt = $pdo->query("
    SELECT d.*, f.name AS farm_name, s.name AS saloon_name
    FROM doras d
    JOIN farms f ON d.farm_id=f.id
    JOIN saloons s ON d.saloon_id=s.id
    ORDER BY d.id DESC
");
$doras = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <title>مدیریت دوره‌ها</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">
    <h2>لیست دوره‌ها</h2>
    <a href="add.php" class="btn btn-success mb-3">شروع دوره جدید</a>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>شناسه</th>
                <th>فارم</th>
                <th>سالن</th>
                <th>تاریخ شروع</th>
                <th>تعداد جوجه</th>
                <th>وضعیت</th>
                <th>عملیات</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($doras as $d): ?>
            <tr>
                <td><?= $d["id"] ?></td>
                <td><?= $d["farm_name"] ?></td>
                <td><?= $d["saloon_name"] ?></td>
                <td><?= $d["start_date"] ?></td>
                <td><?= $d["total_chicks"] ?></td>
                <td>
                    <?= $d["status"]=="active" ? "فعال 🟢" : "بسته 🔴" ?>
                </td>
                <td>
                    <?php if ($d["status"]=="active"): ?>
                        <a href="close.php?id=<?= $d['id'] ?>" class="btn btn-warning btn-sm">بستن دوره</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <a href="../dashboard/index.php" class="btn btn-secondary">بازگشت</a>
</body>
</html>
