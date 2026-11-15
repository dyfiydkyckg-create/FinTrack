<?php
declare(strict_types=1);
require_once __DIR__.'/config/database.php';
require_once __DIR__.'/includes/functions.php';
$type = in_array($_GET['type']??'', ['income','expense'], true) ? $_GET['type'] : '';
$search = trim((string)($_GET['q']??''));
$sql = "SELECT t.*,c.name category_name,c.icon category_icon FROM transactions t LEFT JOIN categories c ON c.id=t.category_id WHERE 1=1";
$params=[];
if ($type!=='') {$sql.=" AND t.type=?";$params[]=$type;}
if ($search!=='') {$sql.=" AND (t.title LIKE ? OR c.name LIKE ?)";$params[]="%{$search}%";$params[]="%{$search}%";}
$sql.=" ORDER BY t.transaction_date DESC,t.id DESC LIMIT 200";
$stmt=$pdo->prepare($sql);$stmt->execute($params);$items=$stmt->fetchAll();
$pageTitle='تراکنش‌ها';$activePage='transactions';require __DIR__.'/includes/header.php';
?>
<section class="panel table-panel"><div class="panel-heading"><div><h3>تاریخچه تراکنش‌ها</h3><p><?= count($items) ?> مورد در فهرست فعلی</p></div><a class="button button-primary" href="transaction-create.php">＋ ثبت تراکنش</a></div>
<form class="filter-bar" method="get"><input name="q" value="<?= e($search) ?>" placeholder="جست‌وجو در عنوان یا دسته‌بندی..."><select name="type"><option value="">همه تراکنش‌ها</option><option value="income" <?= $type==='income'?'selected':'' ?>>فقط درآمدها</option><option value="expense" <?= $type==='expense'?'selected':'' ?>>فقط هزینه‌ها</option></select><button class="button button-dark">اعمال فیلتر</button><a class="button button-ghost" href="transactions.php">پاک‌کردن</a></form>
<?php if (!$items): ?><div class="empty-state"><span>⌕</span><strong>تراکنشی پیدا نشد</strong><p>فیلترها را تغییر دهید یا یک تراکنش جدید ثبت کنید.</p></div><?php else: ?><div class="table-wrap"><table><thead><tr><th>عنوان</th><th>نوع</th><th>دسته‌بندی</th><th>تاریخ</th><th>مبلغ</th><th>عملیات</th></tr></thead><tbody><?php foreach($items as $item): ?><tr><td><strong><?= e($item['title']) ?></strong><?php if($item['note']): ?><small class="cell-note"><?= e($item['note']) ?></small><?php endif; ?></td><td><span class="pill <?= $item['type']==='income'?'pill-income':'pill-expense' ?>"><?= $item['type']==='income'?'درآمد':'هزینه' ?></span></td><td><?= e(($item['category_icon']??'•').' '.($item['category_name']??'بدون دسته')) ?></td><td><?= e($item['transaction_date']) ?></td><td class="<?= $item['type']==='income'?'amount-income':'amount-expense' ?>"><?= $item['type']==='income'?'+':'−' ?> <?= money($item['amount']) ?></td><td><form method="post" action="transaction-delete.php" onsubmit="return confirm('از حذف این تراکنش مطمئن هستید؟')"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)$item['id'] ?>"><button class="icon-button danger" aria-label="حذف تراکنش">⌫</button></form></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
</section>
<?php require __DIR__.'/includes/footer.php'; ?>
