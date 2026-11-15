<?php
declare(strict_types=1);
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
$type = ($_GET['type'] ?? $_POST['type'] ?? 'expense') === 'income' ? 'income' : 'expense';
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 verify_csrf();
 $type = ($_POST['type'] ?? '') === 'income' ? 'income' : 'expense';
 $title = trim((string)($_POST['title'] ?? ''));
 $amount = filter_var($_POST['amount'] ?? null, FILTER_VALIDATE_FLOAT);
 $date = (string)($_POST['transaction_date'] ?? '');
 $category = filter_var($_POST['category_id'] ?? null, FILTER_VALIDATE_INT);
 $note = trim((string)($_POST['note'] ?? ''));
 if ($title === '' || mb_strlen($title) > 160) $errors[] = 'عنوان را وارد کنید (حداکثر ۱۶۰ نویسه).';
 if ($amount === false || $amount <= 0) $errors[] = 'مبلغ باید عددی بزرگ‌تر از صفر باشد.';
 if (!valid_date($date)) $errors[] = 'تاریخ واردشده معتبر نیست.';
 if (!$category) $errors[] = 'یک دسته‌بندی انتخاب کنید.';
 if (!$errors) {
  $check = $pdo->prepare("SELECT id FROM categories WHERE id=? AND type=?");
  $check->execute([$category,$type]);
  if (!$check->fetch()) $errors[] = 'دسته‌بندی انتخاب‌شده معتبر نیست.';
 }
 if (!$errors) {
  $stmt = $pdo->prepare("INSERT INTO transactions (type,category_id,title,amount,transaction_date,note) VALUES (?,?,?,?,?,?)");
  $stmt->execute([$type,$category,$title,$amount,$date,$note ?: null]);
  flash('تراکنش با موفقیت ثبت شد.'); redirect('transactions.php');
 }
}
$categories = $pdo->prepare("SELECT id,name,icon FROM categories WHERE type=? ORDER BY name");
$categories->execute([$type]); $categories = $categories->fetchAll();
$pageTitle='ثبت تراکنش'; $activePage='transactions';
require __DIR__.'/includes/header.php';
?>
<section class="form-panel panel"><div class="panel-heading"><div><h3>اطلاعات تراکنش</h3><p>جزئیات درآمد یا هزینه جدید را وارد کنید.</p></div></div>
<?php if ($errors): ?><div class="alert alert-error"><?php foreach($errors as $error): ?><div><?= e($error) ?></div><?php endforeach; ?></div><?php endif; ?>
<form method="post" class="form-grid">
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<div class="field full"><label>نوع تراکنش</label><div class="type-switch"><label><input type="radio" name="type" value="expense" <?= $type==='expense'?'checked':'' ?> onchange="this.form.submit()"> هزینه</label><label><input type="radio" name="type" value="income" <?= $type==='income'?'checked':'' ?> onchange="this.form.submit()"> درآمد</label></div></div>
<div class="field full"><label for="title">عنوان تراکنش</label><input id="title" name="title" maxlength="160" required placeholder="مثلاً خرید هفتگی یا حقوق ماهانه" value="<?= e($_POST['title']??'') ?>"></div>
<div class="field"><label for="amount">مبلغ (<?= e(getenv('APP_CURRENCY') ?: 'تومان') ?>)</label><input id="amount" name="amount" type="number" min="1" step="1" required placeholder="مثلاً ۲۵۰۰۰۰" value="<?= e($_POST['amount']??'') ?>"></div>
<div class="field"><label for="transaction_date">تاریخ</label><input id="transaction_date" name="transaction_date" type="date" required value="<?= e($_POST['transaction_date']??date('Y-m-d')) ?>"></div>
<div class="field full"><label for="category_id">دسته‌بندی</label><select id="category_id" name="category_id" required><option value="">انتخاب دسته‌بندی</option><?php foreach($categories as $cat): ?><option value="<?= (int)$cat['id'] ?>" <?= (string)($_POST['category_id']??'')===(string)$cat['id']?'selected':'' ?>><?= e($cat['icon'].' '.$cat['name']) ?></option><?php endforeach; ?></select></div>
<div class="field full"><label for="note">یادداشت (اختیاری)</label><textarea id="note" name="note" rows="3" placeholder="توضیحات بیشتر..."><?= e($_POST['note']??'') ?></textarea></div>
<div class="form-actions full"><a class="button button-ghost" href="transactions.php">انصراف</a><button class="button button-primary" type="submit">ذخیره تراکنش</button></div>
</form></section>
<?php require __DIR__.'/includes/footer.php'; ?>
