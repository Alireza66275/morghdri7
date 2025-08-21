<?php
session_start();
require_once("../config/db.php");

// فقط مدیر فارم می‌تونه دوره شروع کنه
if ($_SESSION["role"] != "modir_farm") {
    die("دسترسی غیرمجاز ❌");
}

// گرفتن سالن‌های مربوط به فارم این مدیر
$stmt = $pdo->prepare("
    SELECT s.*, f.name AS farm_name 
    FROM saloons s 
    JOIN farms f ON s.farm_id=f.id
    WHERE f.manager_id=?
");
$stmt->execute([$_SESSION["user_id"]]);
$saloons = $stmt->fetchAll();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $saloon_id = $_POST["saloon_id"];
    $start_date = $_POST["start_date"];
    $total_chicks = $_POST["total_chicks"];

    // پیدا کردن farm_id بر اساس saloon_id
    $q = $pdo->prepare("SELECT farm_id FROM saloons WHERE id=?");
    $q->execute([$saloon_id]);
    $farm = $q->fetch();

    if (!$farm) {
        $error = "سالن معتبر یافت نشد ❌";
    } else {
        $farm_id = $farm["farm_id"];

        // بررسی اینکه سالن دوره فعال نداشته باشه
        $check = $pdo->prepare("SELECT * FROM doras WHERE saloon_id=? AND status='active'");
        $check->execute([$saloon_id]);

        if ($check->rowCount() > 0) {
            $error = "این سالن در حال حاضر دوره فعال دارد ❌";
        } else {
            // ثبت دوره
            $stmt = $pdo->prepare("INSERT INTO doras (farm_id, saloon_id, start_date, total_chicks) VALUES (?, ?, ?, ?)");
            $stmt->execute([$farm_id, $saloon_id, $start_date, $total_chicks]);
            header("Location: list.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <title>شروع دوره جدید</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">
    <h2>شروع دوره جدید</h2>
    <form method="post">
        <select name="saloon_id" class="form-control mb-2" required>
            <option value="">انتخاب سالن</option>
            <?php foreach ($saloons as $s): ?>
                <option value="<?= $s['id'] ?>"> <?= $s['farm_name'] ?> → <?= $s['name'] ?> </option>
            <?php endforeach; ?>
        </select>

        <input type="date" name="start_date" class="form-control mb-2" required>
        <input type="number" name="total_chicks" placeholder="تعداد جوجه" class="form-control mb-2" required>

        <button type="submit" class="btn btn-success">ثبت دوره</button>
    </form>

    <?php if (isset($error)) echo "<div class='alert alert-danger mt-3'>$error</div>"; ?>
    <a href="list.php" class="btn btn-secondary mt-3">بازگشت</a>
</body>
</html>
