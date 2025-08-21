<?php
session_start();
require_once("../config/db.php");

// فقط مدیر عامل یا ادمین می‌تونه فارم بسازه
if ($_SESSION["role"] != "modir_amol" && $_SESSION["role"] != "admin") {
    die("دسترسی غیرمجاز ❌");
}

// گرفتن لیست مدیران فارم که هنوز فارم ندارن
$users = $pdo->query("
    SELECT u.id, u.username 
    FROM users u
    WHERE u.role='modir_farm'
    AND u.id NOT IN (SELECT manager_id FROM farms)
")->fetchAll();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $manager_id = $_POST["manager_id"];

    // درج فارم جدید
    $stmt = $pdo->prepare("INSERT INTO farms (name, manager_id) VALUES (?, ?)");
    $stmt->execute([$name, $manager_id]);

    header("Location: list.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <title>افزودن فارم</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">
    <h2>افزودن فارم</h2>
    <form method="post">
        <input type="text" name="name" class="form-control mb-2" placeholder="نام فارم" required>

        <select name="manager_id" class="form-control mb-2" required>
            <option value="">انتخاب مدیر فارم</option>
            <?php foreach ($users as $u): ?>
                <option value="<?= $u['id'] ?>"><?= $u['username'] ?></option>
            <?php endforeach; ?>
        </select>

        <button type="submit" class="btn btn-success">ثبت فارم</button>
    </form>
    <a href="list.php" class="btn btn-secondary mt-3">بازگشت</a>
</body>
</html>
