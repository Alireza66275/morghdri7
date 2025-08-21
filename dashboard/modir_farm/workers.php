<?php
require_once __DIR__ . "/../../config/init.php";

// بررسی ورود کاربر
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'modir_farm') {
    header("Location: ../../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// گرفتن farm_id مدیر فارم
$stmt = $conn->prepare("SELECT id FROM farms WHERE manager_id=? LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$farm = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$farm) {
    die("⚠️ هیچ فارم برای شما تعریف نشده است.");
}
$farm_id = $farm['id'];

$message = "";

// 🟢 افزودن یا ویرایش کارگر
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $worker_id = $_POST['worker_id'] ?? null;

    // چک یکتا بودن یوزرنیم
    if ($worker_id) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username=? AND id!=? LIMIT 1");
        $stmt->bind_param("si", $username, $worker_id);
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username=? LIMIT 1");
        $stmt->bind_param("s", $username);
    }
    $stmt->execute();
    $exists = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($exists) {
        $message = "❌ نام کاربری <b>$username</b> قبلاً ثبت شده است.";
    } else {
        if ($worker_id) {
            // ویرایش
            if (!empty($password)) {
                $hashed = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $conn->prepare("UPDATE users SET name=?, username=?, password=? WHERE id=? AND farm_id=? AND role='worker'");
                $stmt->bind_param("sssii", $name, $username, $hashed, $worker_id, $farm_id);
            } else {
                $stmt = $conn->prepare("UPDATE users SET name=?, username=? WHERE id=? AND farm_id=? AND role='worker'");
                $stmt->bind_param("ssii", $name, $username, $worker_id, $farm_id);
            }
            $stmt->execute();
            $stmt->close();
            $message = "✅ اطلاعات کارگر ویرایش شد.";
        } else {
            // افزودن
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("INSERT INTO users (name, username, password, role, farm_id) VALUES (?, ?, ?, 'worker', ?)");
            $stmt->bind_param("sssi", $name, $username, $hashed, $farm_id);
            $stmt->execute();
            $stmt->close();
            $message = "✅ کارگر جدید اضافه شد.";
        }

        // رفرش برای نمایش لیست جدید
        $_SESSION['msg'] = $message;
        header("Location: workers.php");
        exit;
    }
}

// نمایش پیام بعد از رفرش
if (isset($_SESSION['msg'])) {
    $message = $_SESSION['msg'];
    unset($_SESSION['msg']);
}

// اگر درخواست ویرایش آمد
$edit_worker = null;
if (isset($_GET['edit'])) {
    $wid = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT id, name, username FROM users WHERE id=? AND farm_id=? AND role='worker'");
    $stmt->bind_param("ii", $wid, $farm_id);
    $stmt->execute();
    $edit_worker = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// گرفتن لیست کارگرها (همیشه به‌روز)
$stmt = $conn->prepare("SELECT id, name, username FROM users WHERE farm_id=? AND role='worker' ORDER BY id DESC");
$stmt->bind_param("i", $farm_id);
$stmt->execute();
$workers = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <title>مدیریت کارگرها</title>
    <style>
        body { font-family: Tahoma, sans-serif; direction: rtl; margin: 20px; }
        .message { padding: 10px; margin-bottom: 15px; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        form { margin-bottom: 20px; padding: 10px; border: 1px solid #ccc; width: 350px; }
        input { margin: 5px 0; padding: 5px; width: 100%; }
        button { padding: 5px 10px; }
        table { border-collapse: collapse; width: 60%; margin-top: 15px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
        th { background: #f2f2f2; }
    </style>
</head>
<body>
    <h2>👷 مدیریت کارگرهای فارم</h2>

    <?php if ($message): ?>
        <div class="message <?= strpos($message, "❌") !== false ? 'error' : 'success' ?>">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <h3><?= $edit_worker ? "✏️ ویرایش کارگر" : "➕ افزودن کارگر جدید" ?></h3>
    <form method="post">
        <input type="hidden" name="worker_id" value="<?= $edit_worker['id'] ?? '' ?>">
        <label>نام:</label>
        <input type="text" name="name" value="<?= htmlspecialchars($edit_worker['name'] ?? '') ?>" required>
        <label>نام کاربری:</label>
        <input type="text" name="username" value="<?= htmlspecialchars($edit_worker['username'] ?? '') ?>" required>
        <label>رمز عبور <?= $edit_worker ? "(اختیاری)" : "" ?>:</label>
        <input type="password" name="password" <?= $edit_worker ? "" : "required" ?>>
        <button type="submit"><?= $edit_worker ? "ذخیره تغییرات" : "افزودن کارگر" ?></button>
    </form>

    <h3>📋 لیست کارگرها</h3>
    <table>
        <tr>
            <th>نام</th>
            <th>نام کاربری</th>
            <th>عملیات</th>
        </tr>
        <?php while ($row = $workers->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td><a href="workers.php?edit=<?= $row['id'] ?>">✏️ ویرایش</a></td>
            </tr>
        <?php endwhile; ?>
    </table>

    <p><a href="index.php">⬅ بازگشت به داشبورد</a></p>
</body>
</html>
