<?php
require_once __DIR__ . "/../../config/init.php";

// فقط مدیر فارم
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'modir_farm') {
    header("Location: ../../auth/login.php"); exit;
}

// یافتن فارم مدیر
$manager_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT id, name FROM farms WHERE manager_id = ? LIMIT 1");
$stmt->bind_param("i", $manager_id);
$stmt->execute();
$farm = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$farm) { die("⚠️ برای شما هیچ فارمی ثبت نشده است."); }
$farm_id = (int)$farm['id'];

$msg = "";

// افزودن سالن
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action']) && $_POST['action']==='add') {
    $name = trim($_POST['name'] ?? "");
    if ($name !== "") {
        $stmt = $conn->prepare("INSERT INTO saloons (farm_id, name) VALUES (?, ?)");
        $stmt->bind_param("is", $farm_id, $name);
        if ($stmt->execute()) $msg = "✅ سالن جدید افزوده شد.";
        else $msg = "❌ خطا در افزودن سالن: ".$stmt->error;
        $stmt->close();
    } else { $msg = "⚠️ نام سالن را وارد کنید."; }
}

// ویرایش سالن
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action']) && $_POST['action']==='edit') {
    $id   = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? "");
    if ($id && $name!=="") {
        $stmt = $conn->prepare("UPDATE saloons SET name=? WHERE id=? AND farm_id=?");
        $stmt->bind_param("sii", $name, $id, $farm_id);
        if ($stmt->execute()) $msg = "✅ تغییرات ذخیره شد.";
        else $msg = "❌ خطا در ویرایش: ".$stmt->error;
        $stmt->close();
    } else { $msg = "⚠️ داده‌های ویرایش نامعتبر است."; }
}

// حذف سالن
if (isset($_GET['delete_id'])) {
    $del_id = (int)$_GET['delete_id'];

    // اگر دوره باز دارد، حذف نشود
    $chk = $conn->prepare("SELECT COUNT(*) c FROM doras WHERE saloon_id=? AND farm_id=? AND (end_date IS NULL)");
    $chk->bind_param("ii", $del_id, $farm_id);
    $chk->execute();
    $c = $chk->get_result()->fetch_assoc()['c'];
    $chk->close();

    if ($c > 0) {
        $msg = "⚠️ این سالن دورهٔ باز دارد و قابل حذف نیست.";
    } else {
        $stmt = $conn->prepare("DELETE FROM saloons WHERE id=? AND farm_id=?");
        $stmt->bind_param("ii", $del_id, $farm_id);
        if ($stmt->execute() && $stmt->affected_rows) $msg = "🗑️ سالن حذف شد.";
        else $msg = "❌ حذف انجام نشد.";
        $stmt->close();
    }
}

// اگر در حالت ویرایش هستیم، سالن را بگیریم
$edit_item = null;
if (isset($_GET['edit_id'])) {
    $eid = (int)$_GET['edit_id'];
    $stmt = $conn->prepare("SELECT id, name FROM saloons WHERE id=? AND farm_id=?");
    $stmt->bind_param("ii", $eid, $farm_id);
    $stmt->execute();
    $edit_item = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// لیست سالن‌ها
$stmt = $conn->prepare("SELECT id, name FROM saloons WHERE farm_id=? ORDER BY id DESC");
$stmt->bind_param("i", $farm_id);
$stmt->execute();
$saloons = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!doctype html>
<html lang="fa">
<head><meta charset="utf-8"><title>مدیریت سالن‌ها</title></head>
<body>
<h2>🏠 مدیریت سالن‌های «<?= htmlspecialchars($farm['name']) ?>»</h2>

<?php if($msg): ?><p><?= htmlspecialchars($msg) ?></p><?php endif; ?>

<?php if ($edit_item): ?>
<!-- فرم ویرایش -->
<h3>✏️ ویرایش سالن</h3>
<form method="post">
    <input type="hidden" name="action" value="edit">
    <input type="hidden" name="id" value="<?= (int)$edit_item['id'] ?>">
    <label>نام سالن:</label>
    <input type="text" name="name" value="<?= htmlspecialchars($edit_item['name']) ?>" required>
    <button type="submit">ذخیره</button>
    <a href="saloons.php">انصراف</a>
</form>
<hr>
<?php else: ?>
<!-- فرم افزودن -->
<h3>➕ افزودن سالن جدید</h3>
<form method="post">
    <input type="hidden" name="action" value="add">
    <label>نام سالن:</label>
    <input type="text" name="name" required>
    <button type="submit">افزودن</button>
</form>
<hr>
<?php endif; ?>

<h3>لیست سالن‌ها</h3>
<table border="1" cellpadding="6" cellspacing="0">
<tr><th>#</th><th>نام سالن</th><th>عملیات</th></tr>
<?php foreach($saloons as $s): ?>
<tr>
  <td><?= (int)$s['id'] ?></td>
  <td><?= htmlspecialchars($s['name']) ?></td>
  <td>
    <a href="saloons.php?edit_id=<?= (int)$s['id'] ?>">ویرایش</a> |
    <a href="saloons.php?delete_id=<?= (int)$s['id'] ?>" onclick="return confirm('حذف شود؟')">حذف</a>
  </td>
</tr>
<?php endforeach; ?>
</table>

<p><a href="index.php">⬅️ بازگشت به داشبورد</a></p>
</body>
</html>
