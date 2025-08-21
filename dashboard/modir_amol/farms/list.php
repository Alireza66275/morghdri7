<?php
session_start();
require_once("../config/db.php");

// فقط مدیرعامل و ادمین
if ($_SESSION["role"] != "modir_amol" && $_SESSION["role"] != "admin") {
    die("دسترسی غیرمجاز ❌");
}

$stmt = $pdo->query("
    SELECT farms.*, users.name AS manager_name 
    FROM farms 
    LEFT JOIN users ON farms.modir_farm_id = users.id
    ORDER BY farms.id DESC
");
$farms = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <title>مدیریت فارم‌ها</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">
    <h2>لیست فارم‌ها</h2>
    <a href="add.php" class="btn btn-success mb-3">افزودن فارم جدید</a>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>شناسه</th>
                <th>نام فارم</th>
                <th>مدیر فارم</th>
                <th>عملیات</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($farms as $farm): ?>
            <tr>
                <td><?= $farm["id"] ?></td>
                <td><?= $farm["name"] ?></td>
                <td><?= $farm["manager_name"] ?: "ندارد" ?></td>
                <td>
                    <a href="edit.php?id=<?= $farm["id"] ?>" class="btn btn-primary btn-sm">ویرایش</a>
                    <a href="delete.php?id=<?= $farm["id"] ?>" class="btn btn-danger btn-sm" onclick="return confirm('مطمئنی؟')">حذف</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <a href="../dashboard/index.php" class="btn btn-secondary">بازگشت</a>
</body>
</html>
