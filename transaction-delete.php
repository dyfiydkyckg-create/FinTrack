<?php
declare(strict_types=1);
require_once __DIR__.'/config/database.php';
require_once __DIR__.'/includes/functions.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('transactions.php');
verify_csrf();
$id=filter_var($_POST['id']??null,FILTER_VALIDATE_INT);
if (!$id) {flash('شناسه تراکنش معتبر نیست.','error');redirect('transactions.php');}
$stmt=$pdo->prepare("DELETE FROM transactions WHERE id=?");
$stmt->execute([$id]);
flash($stmt->rowCount() ? 'تراکنش حذف شد.' : 'تراکنش موردنظر پیدا نشد.',$stmt->rowCount()?'success':'error');
redirect('transactions.php');
