<?php
require_once __DIR__ . "/../../config/init.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'modir_farm') {
    header("Location: ../../auth/login.php"); exit;
}

// فارم مدیر
$manager_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT id, name FROM farms WHERE manager_id=? LIMIT 1");
$stmt->bind_param("i", $manager_id);
$stmt->execute();
$farm = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$farm) { die("⚠️ برای شما هیچ فارمی ثبت نشده است."); }
$farm_id = (int)$farm['id'];

$msg = "";

// بستن دوره
if (isset($_GET['close_id'])) {
    $cid = (int)$_GET['close_id'];
    $stmt = $conn->prepare("UPDATE doras SET end_date=CURDATE() WHERE id=? AND farm_id=? AND end_date IS NULL");
    $stmt->bind_param("ii", $cid, $farm_id);
    if ($stmt->execute() && $stmt->affected_rows) $msg = "✅ دوره بسته شد.";
    else $msg = "⚠️ دوره قبلاً بسته شده یا یافت نشد.";
    $stmt->close();
}

// سالن‌ها
$stmt = $conn->prepare("SELECT id, name FROM saloons WHERE farm_id=? ORDER BY id");
$stmt->bind_param("i", $farm_id);
$stmt->execute();
$saloons = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// شروع دوره جدید
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action']) && $_POST['action']==='start') {
    $start_date = $_POST['start_date'] ?? "";
    if ($start_date==="") { $msg = "⚠️ تاریخ شروع دوره را وارد کنید."; }
    else {
        $chicks = $_POST['chicks'] ?? [];
        $started = 0;
        foreach ($chicks as $sid => $qty) {
            $sid = (int)$sid;
            $qty = (int)$qty;
            if ($qty <= 0) continue;

            // اگر سالن مربوط به همین فارم نباشد، رد شود
            $ok = $conn->prepare("SELECT COUNT(*) c FROM saloons WHERE id=? AND farm_id=?");
            $ok->bind_param("ii", $sid, $farm_id);
            $ok->execute();
            $c = $ok->get_result()->fetch_assoc()['c'];
            $ok->close();
            if (!$c) continue;

            // اگر دوره باز دارد، شروع جدید نزن
            $chk = $conn->prepare("SELECT COUNT(*) c FROM doras WHERE saloon_id=? AND farm_id=? AND end_date IS NULL");
            $chk->bind_param("ii", $sid, $farm_id);
            $chk->execute();
            $hasOpen = $chk->get_result()->fetch_assoc()['c'] > 0;
            $chk->close();
            if ($hasOpen) continue;

            $ins = $conn->prepare("INSERT INTO doras (farm_id, saloon_id, start_date, total_chicks) VALUES (?, ?, ?, ?)");
            $ins->bind_param("iisi", $farm_id, $sid, $start_date, $qty);
            if ($ins->execute()) $started++;
            $ins->close();
        }
        $msg = $started ? "✅ {$started} دوره شروع شد." : "⚠️ دوره‌ای شروع نشد (ممکن است سالن دوره باز داشته باشد یا تعداد جوجه صفر باشد).";
    }
}

// لیست دوره‌ها
$stmt = $conn->prepare("
    SELECT d.id, s.name AS saloon_name, d.start_date, d.end_date, d.total_chicks
    FROM doras d
    JOIN saloons s ON d.saloon_id = s.id
    WHERE d.farm_id = ?
    ORDER BY d.id DESC
");
$stmt->bind_param("i", $farm_id);
$stmt->execute();
$doras = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!doctype html>
<html lang="fa">
<head><meta charset="utf-8"><title>مدیریت دوره‌ها</title></head>
<body>
<h2>🔄 مدیریت دوره‌ها – «<?= htmlspecialchars($farm['name']) ?>»</h2>

<?php if($msg): ?><p><?= htmlspecialchars($msg) ?></p><?php endif; ?>

<h3>شروع دوره جدید</h3>
<?php if (empty($saloons)): ?>
<p>⚠️ هنوز سالنی تعریف نکرده‌اید. ابتدا <a href="saloons.php">سالن بسازید</a>.</p>
<?php else: ?>
<form method="post">
    <input type="hidden" name="action" value="start">
    <label>تاریخ شروع:</label>
    <input type="date" name="start_date" required>
    <table border="1" cellpadding="6" cellspacing="0" style="margin-top:8px;">
        <tr><th>سالن</th><th>تعداد جوجه</th></tr>
        <?php foreach($saloons as $s): ?>
        <tr>
            <td><?= htmlspecialchars($s['name']) ?></td>
            <td><input type="number" name="chicks[<?= (int)$s['id'] ?>]" value="0" min="0"></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <button type="submit">شروع دوره</button>
</form>
<?php endif; ?>

<hr>
<h3>لیست دوره‌ها</h3>
<table border="1" cellpadding="6" cellspacing="0">
<tr><th>#</th><th>سالن</th><th>تاریخ شروع</th><th>تاریخ پایان</th><th>تعداد جوجه</th><th>وضعیت</th><th>عملیات</th></tr>
<?php foreach($doras as $d): ?>
<tr>
  <td><?= (int)$d['id'] ?></td>
  <td><?= htmlspecialchars($d['saloon_name']) ?></td>
  <td><?= htmlspecialchars($d['start_date']) ?></td>
  <td><?= htmlspecialchars($d['end_date'] ?? '-') ?></td>
  <td><?= (int)$d['total_chicks'] ?></td>
  <td><?= $d['end_date'] ? 'بسته' : 'باز' ?></td>
  <td>
    <?php if (!$d['end_date']): ?>
    <a href="doras.php?close_id=<?= (int)$d['id'] ?>" onclick="return confirm('دوره بسته شود؟')">بستن دوره</a>
    <?php else: ?>-<?php endif; ?>
  </td>
</tr>
<?php endforeach; ?>
</table>

<p><a href="index.php">⬅️ بازگشت به داشبورد</a></p>
</body>
</html>
