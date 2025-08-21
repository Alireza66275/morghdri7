<?php
session_start();
require_once("../config/db.php");

if ($_SESSION["role"] != "modir_amol" && $_SESSION["role"] != "admin") {
    die("دسترسی غیرمجاز ❌");
}

$id = $_GET["id"] ?? 0;

$stmt = $pdo->prepare("DELETE FROM farms WHERE id=?");
$stmt->execute([$id]);

header("Location: list.php");
exit;
