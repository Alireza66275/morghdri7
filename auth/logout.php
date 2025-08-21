<?php
require_once __DIR__ . "/../config/init.php";

// حذف همه داده‌های سشن
session_unset();
session_destroy();

// هدایت به صفحه ورود
header("Location: login.php");
exit;
