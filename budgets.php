<?php
declare(strict_types=1);
require_once __DIR__.'/config/database.php';
require_once __DIR__.'/includes/functions.php';
$month=(string)($_GET['month']??current_month());
if (!preg_match('/^\d{4}-\d{2}$/',$month)) $month=current_month();
$errors=[];
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();$month=(string)($_POST['month']??current_month());$category=(int)($_POST['category_id']??0);$amount=filter_var($_POST['amount']??null,FILTER_VALIDATE_FLOAT);
 if(!preg_match('/^\d{4}-\d{2}$/',$month))$errors[]='ماه انتخاب‌شده معتبر نیست.';
 if(!$category)$errors[]='دسته‌بندی را انتخاب کنید.';
 if($amount===false||$amount<=0)$errors[]='مبلغ بودجه باید بیشتر از صفر باشد.';
 if(!$errors){
  $check=$pdo->prepare("SELECT id FROM categories WHERE id=? AND type='expense'");$check->execute([$category]);
  if(!$check->fetch())$errors[]='دسته‌بندی هزینه معتبر نیست.';
 }
 if(!$errors){$stmt=$pdo->prepare("INSERT INTO budgets(category_id,month,amount) VALUES(?,?,?) ON DUPLICATE KEY UPDATE amount=VALUES(amount)");$stmt->execute([$category,$month,$amount]);flash('بودجه با موفقیت ذخیره شد.');redirect('budgets.php?month='.urlencode($month));}
}
$stmt=$pdo->prepare("SELECT b.*,c.name,c.icon,COALESCE(SUM(t.amount),0) spent FROM budgets b JOIN categories c ON c.id=b.category_id LEFT JOIN transactions t ON t.category_id=b.category_id AND t.type='expense' AND DATE_FORMAT(t.transaction_date,'%Y-%m')=b.month WHERE b.month=? GROUP BY b.id ORDER BY c.name");$stmt->execute([$month]);$budgets=$stmt->fetchAll();
$categories=$pdo->query("SELECT id,name,icon FROM categories WHERE type='expense' ORDER BY name")->fetchAll();
$pageTitle='بودجه‌بندی';$activePage='budgets';require __DIR__.'/includes/header.php';
?>
<section class="content-grid budget-layout"><article class="panel form-panel"><div class="panel-heading"><div><h3>تعریف بودجه</h3><p>برای هر دسته هزینه سقف ماهانه مشخص کنید.</p></div></div><?php if($errors): ?><div class="alert alert-error"><?php foreach($errors as $error): ?><div><?= e($error) ?></div><?php endforeach; ?></div><?php endif; ?><form method="post" class="form-grid"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><div class="field full"><label>ماه بودجه</label><input type="month" name="month" value="<?= e($month) ?>" required></div><div class="field full"><label>دسته‌بندی هزینه</label><select name="category_id" required><option value="">انتخاب دسته‌بندی</option><?php foreach($categories as $cat): ?><option value="<?= (int)$cat['id'] ?>"><?= e($cat['icon'].' '.$cat['name']) ?></option><?php endforeach; ?></select></div><div class="field full"><label>سقف بودجه (<?= e(getenv('APP_CURRENCY')?:'تومان') ?>)</label><input type="number" name="amount" min="1" step="1" required placeholder="مثلاً ۵۰۰۰۰۰۰"></div><div class="field full"><button class="button button-primary">ذخیره بودجه</button></div></form></article>
<article class="panel budget-panel"><div class="panel-heading"><div><h3>وضعیت بودجه‌ها</h3><p>ماه <?= e($month) ?></p></div><form method="get"><input type="month" name="month" value="<?= e($month) ?>" onchange="this.form.submit()"></form></div><?php if(!$budgets): ?><div class="empty-state compact"><span>◷</span><strong>بودجه‌ای برای این ماه ندارید</strong><p>از فرم کنار صفحه اولین بودجه را تعریف کنید.</p></div><?php else: foreach($budgets as $budget): $percent=$budget['amount']>0?min(100,(float)$budget['spent']/(float)$budget['amount']*100):0; ?><div class="budget-item"><div class="budget-title"><strong><?= e($budget['icon'].' '.$budget['name']) ?></strong><span><?= number_format($percent,0) ?>٪</span></div><div class="progress"><span style="width:<?= $percent ?>%"></span></div><div class="budget-numbers"><span>مصرف‌شده: <?= money($budget['spent']) ?></span><span>بودجه: <?= money($budget['amount']) ?></span></div><?php if((float)$budget['spent']>(float)$budget['amount']): ?><small class="over-budget">از سقف بودجه عبور کرده‌اید.</small><?php endif; ?></div><?php endforeach; endif; ?></article></section>
<?php require __DIR__.'/includes/footer.php'; ?>
