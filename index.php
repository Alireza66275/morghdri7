<?php
require_once __DIR__ . "/config/init.php";

// اگر لاگین کرده بود → هدایت به داشبورد
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    switch ($_SESSION['role']) {
        case 'admin':
            header("Location: dashboard/admin/index.php");
            break;
        case 'modir_amol':
            header("Location: dashboard/modir_amol/index.php");
            break;
        case 'modir_farm':
            header("Location: dashboard/modir_farm/index.php");
            break;
        case 'modir_fanni':
            header("Location: dashboard/modir_fanni/index.php");
            break;
        case 'worker':
            header("Location: dashboard/worker/index.php");
            break;
    }
    exit;
}

// اگر لاگین نکرده بود → بره به login
header("Location: auth/login.php");
exit;
