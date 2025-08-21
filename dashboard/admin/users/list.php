<?php
session_start();
require_once("../config/db.php");

// فقط ادمین اجازه دسترسی دارد
if ($_SESSION["role"] != "admin") {
    die("دسترسی غیرمجاز ❌");
}

$stmt = $pdo->query("SELECT * FROM users ORDER BY id DESC");
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <title>مدیریت کاربران</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">
    <h2>لیست کاربران</h2>
    <a href="add.php" class="btn btn-success mb-3">افزودن کاربر جدید</a>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>شناسه</th>
                <th>نام</th>
                <th>نام کاربری</th>
                <th>نقش</th>
                <th>عملیات</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
            <tr>
                <td><?= $user["id"] ?></td>
                <td><?= $user["name"] ?></td>
                <td><?= $user["username"] ?></td>
                <td><?= $user["role"] ?></td>
                <td>
                    <a href="edit.php?id=<?= $user["id"] ?>" class="btn btn-primary btn-sm">ویرایش</a>
                    <a href="delete.php?id=<?= $user["id"] ?>" class="btn btn-danger btn-sm" onclick="return confirm('آیا مطمئن هستید؟')">حذف</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <a href="../dashboard/index.php" class="btn btn-secondary">بازگشت</a>
</body>
</html>
