<?php
require_once __DIR__ . "/../config/init.php";

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            if (password_verify($password, $user['password'])) {
                // ذخیره اطلاعات سشن
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['name'] = $user['name'];

                // هدایت به داشبورد متناسب با نقش
                switch ($user['role']) {
                    case 'admin':
                        header("Location: ../dashboard/admin/index.php");
                        break;
                    case 'modir_amol':
                        header("Location: ../dashboard/modir_amol/index.php");
                        break;
                    case 'modir_farm':
                        header("Location: ../dashboard/modir_farm/index.php");
                        break;
                    case 'modir_fanni':
                        header("Location: ../dashboard/modir_fanni/index.php");
                        break;
                    case 'worker':
                        header("Location: ../dashboard/worker/index.php");
                        break;
                    default:
                        $message = "❌ نقش کاربر ناشناخته است.";
                }
                exit;
            } else {
                $message = "⚠️ رمز عبور اشتباه است.";
            }
        } else {
            $message = "⚠️ نام کاربری یافت نشد.";
        }
    } else {
        $message = "⚠️ لطفاً نام کاربری و رمز عبور را وارد کنید.";
    }
}
?>

<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <title>ورود به سیستم</title>
</head>
<body>
    <h2>🔑 ورود به سیستم</h2>

    <?php if ($message): ?>
        <p><?= $message ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <label>نام کاربری:</label><br>
        <input type="text" name="username" required><br><br>

        <label>رمز عبور:</label><br>
        <input type="password" name="password" required><br><br>

        <button type="submit">ورود</button>
    </form>
</body>
</html>
