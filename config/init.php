<?php
// مسیر ریشه پروژه
define("BASE_PATH", dirname(__DIR__));

// اتصال به دیتابیس
require_once BASE_PATH . "/config/db.php";

// شروع سشن (فقط یک بار)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
