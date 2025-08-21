<?php
require_once __DIR__ . "/../../config/init.php";

// بررسی ورود کاربر
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'modir_farm') {
    header("Location: ../../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$name = $_SESSION['name'];

// پیدا کردن فارم مربوط به این مدیر فارم
$stmt = $conn->prepare("
    SELECT f.id, f.name 
    FROM farms f 
    WHERE f.manager_id = ?
    LIMIT 1
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$farm = $result->fetch_assoc();
$stmt->close();

?>
<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <title>داشبورد مدیر فارم</title>
</head>
<body>
    <h2>👨‍🌾 خوش آمدید <?= htmlspecialchars($name) ?></h2>

    <?php if ($farm): ?>
        <h3>✅ فارم شما: <?= htmlspecialchars($farm['name']) ?></h3>

        <ul>
            <li><a href="saloons.php?farm_id=<?= $farm['id'] ?>">مدیریت سالن‌ها</a></li>
            <li><a href="workers.php?farm_id=<?= $farm['id'] ?>">مدیریت کارگرها</a></li>
            <li><a href="doras.php?farm_id=<?= $farm['id'] ?>">مدیریت دوره‌ها</a></li>
        </ul>

    <?php else: ?>
        <p>⚠️ هنوز هیچ فارم برای شما ثبت نشده است. لطفاً با مدیر عامل تماس بگیرید.</p>
    <?php endif; ?>

    <p><a href="../../auth/logout.php">🚪 خروج</a></p>
</body>
</html>
