<?php
declare(strict_types=1);
require_once __DIR__.'/config/database.php';
require_once __DIR__.'/includes/functions.php';
$id=filter_var($_GET['id']??$_POST['id']??null,FILTER_VALIDATE_INT);
if(!$id){flash('شناسه تراکنش معتبر نیست.','error');redirect('transactions.php');}
$find=$pdo->prepare("SELECT * FROM transactions WHERE id=?");$find->execute([$id]);$transaction=$find->fetch();
if(!$transaction){flash('تراکنش موردنظر پیدا نشد.','error');redirect('transactions.php');}
$errors=[];
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();
 $type=($_POST['type']??'')==='income'?'income':'expense';
 $title=trim((string)($_POST['title']??''));
 $amount=filter_var($_POST['amount']??null,FILTER_VALIDATE_FLOAT);
 $date=(string)($_POST['transaction_date']??'');
 $category=filter_var($_POST['category_id']??null,FILTER_VALIDATE_INT);
 $note=trim((string)($_POST['note']??''));
 if($title===''||mb_strlen($title)>160)$errors[]='عنوان را وارد کنید (حداکثر ۱۶۰ نویسه).';
 if($amount===false||$amount<=0)$errors[]='مبلغ باید عددی بزرگ‌تر از صفر باشد.';
 if(!valid_date($date))$errors[]='تاریخ واردشده معتبر نیست.';
 if(!$category)$errors[]='یک دسته‌بندی انتخاب کنید.';
 if(!$errors){$check=$pdo->prepare("SELECT id FROM categories WHERE id=? AND type=?");$check->execute([$category,$type]);if(!$check->fetch())$errors[]='دسته‌بندی انتخاب‌شده معتبر نیست.';}
 if(!$errors){$update=$pdo->prepare("UPDATE transactions SET type=?,category_id=?,title=?,amount=?,transaction_date=?,note=? WHERE id=?");$update->execute([$type,$category,$title,$amount,$date,$note?:null,$id]);flash('تراکنش با موفقیت ویرایش شد.');redirect('transactions.php');}
 $transaction=array_merge($transaction,['type'=>$type,'title'=>$title,'amount'=>$amount,'transaction_date'=>$date,'category_id'=>$category,'note'=>$note]);
}
if(isset($_GET['type']) && in_array($_GET['type'],['income','expense'],true)) $transaction['type']=$_GET['type'];
$categories=$pdo->prepare("SELECT id,name,icon FROM categories WHERE type=? ORDER BY name");$categories->execute([$transaction['type']]);$categories=$categories->fetchAll();
$pageTitle='ویرایش تراکنش';$activePage='transactions';require __DIR__.'/includes/header.php';
?>
<section class="panel form-panel"><div class="panel-heading"><div><h3>ویرایش اطلاعات تراکنش</h3><p>اطلاعات موردنظر را اصلاح و ذخیره کنید.</p></div></div>
<?php if($errors): ?><div class="alert alert-error"><?php foreach($errors as $error): ?><div><?= e($error) ?></div><?php endforeach; ?></div><?php endif; ?>
<form method="post" class="form-grid"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)$id ?>">
<div class="field full"><label>نوع تراکنش</label><div class="type-switch"><label><input type="radio" name="type" value="expense" <?= $transaction['type']==='expense'?'checked':'' ?> onchange="window.location='edit.php?id=<?= (int)$id ?>&type=expense'"> هزینه</label><label><input type="radio" name="type" value="income" <?= $transaction['type']==='income'?'checked':'' ?> onchange="window.location='edit.php?id=<?= (int)$id ?>&type=income'"> درآمد</label></div></div>
<div class="field full"><label for="title">عنوان تراکنش</label><input id="title" name="title" maxlength="160" required value="<?= e($transaction['title']) ?>"></div>
<div class="field"><label for="amount">مبلغ (<?= e(getenv('APP_CURRENCY')?:'تومان') ?>)</label><input id="amount" name="amount" type="number" min="1" step="1" required value="<?= e($transaction['amount']) ?>"></div>
<div class="field"><label for="transaction_date">تاریخ</label><input id="transaction_date" name="transaction_date" type="date" required value="<?= e($transaction['transaction_date']) ?>"></div>
<div class="field full"><label for="category_id">دسته‌بندی</label><select id="category_id" name="category_id" required><?php foreach($categories as $cat): ?><option value="<?= (int)$cat['id'] ?>" <?= (int)$transaction['category_id']===(int)$cat['id']?'selected':'' ?>><?= e($cat['icon'].' '.$cat['name']) ?></option><?php endforeach; ?></select></div>
<div class="field full"><label for="note">یادداشت (اختیاری)</label><textarea id="note" name="note" rows="3"><?= e($transaction['note']??'') ?></textarea></div>
<div class="form-actions full"><a class="button button-ghost" href="transactions.php">انصراف</a><button class="button button-primary">ذخیره تغییرات</button></div></form></section>
<?php require __DIR__.'/includes/footer.php'; ?>
