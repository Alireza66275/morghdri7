<?php
session_start();
require_once("../config/db.php");

if ($_SESSION["role"] != "modir_amol" && $_SESSION["role"] != "admin") {
    die("دسترسی غیرمجاز ❌");
}

$id = $_GET["id"] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM farms WHERE id=?");
$stmt->execute([$id]);
$farm = $stmt->fetch();

if (!$farm) die("فارم یافت نشد ❌");

// مدیران فارم
$managers = $pdo->query("SELECT id, name FROM users WHERE role='modir_farm'")->fetchAll();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $manager = $_POST["manager"];

    $stmt = $pdo->prepare("UPDATE farms SET name=?, modir_farm_id=? WHERE id=?");
    $stmt->execute([$name, $manager, $id]);

    header("Location: list.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <title>ویرایش فارم</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">
    <h2>ویرایش فارم</h2>
    <form method="post">
        <input type="text" name="name" value="<?= $farm['name'] ?>" class="form-control mb-2" required>
        <select name="manager" class="form-control mb-2" required>
            <?php foreach ($managers as $m): ?>
                <option value="<?= $m['id'] ?>" <?= $farm['modir_farm_id']==$m['id']?"selected":"" ?>><?= $m['name'] ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary">ذخیره</button>
    </form>
    <a href="list.php" class="btn btn-secondary mt-3">بازگشت</a>
</body>
</html>
