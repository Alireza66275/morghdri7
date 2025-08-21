<?php
session_start();
require_once("../config/db.php");

// فقط ادمین
if ($_SESSION["role"] != "admin") {
    die("دسترسی غیرمجاز ❌");
}

$id = $_GET["id"] ?? 0;

// جلوگیری از حذف خودش
if ($_SESSION["user_id"] == $id) {
    die("شما نمی‌توانید خودتان را حذف کنید ❌");
}

$stmt = $pdo->prepare("DELETE FROM users WHERE id=?");
$stmt->execute([$id]);

header("Location: list.php");
exit;
