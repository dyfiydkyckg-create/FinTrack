<?php
declare(strict_types=1);
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$month = current_month();
$summaryStmt = $pdo->prepare("SELECT
 COALESCE(SUM(CASE WHEN type='income' THEN amount ELSE 0 END),0) income,
 COALESCE(SUM(CASE WHEN type='expense' THEN amount ELSE 0 END),0) expense
 FROM transactions WHERE DATE_FORMAT(transaction_date,'%Y-%m') = ?");
$summaryStmt->execute([$month]);
$summary = $summaryStmt->fetch();
$balance = (float)$summary['income'] - (float)$summary['expense'];

$recent = $pdo->query("SELECT t.*, c.name category_name, c.icon category_icon
 FROM transactions t LEFT JOIN categories c ON c.id=t.category_id
 ORDER BY t.transaction_date DESC, t.id DESC LIMIT 7")->fetchAll();

$chartStmt = $pdo->query("SELECT DATE_FORMAT(transaction_date,'%Y-%m') month,
 SUM(CASE WHEN type='income' THEN amount ELSE 0 END) income,
 SUM(CASE WHEN type='expense' THEN amount ELSE 0 END) expense
 FROM transactions WHERE transaction_date >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH)
 GROUP BY DATE_FORMAT(transaction_date,'%Y-%m') ORDER BY month");
$chartRows = $chartStmt->fetchAll();
$maxChart = 1;
foreach ($chartRows as $row) $maxChart = max($maxChart, (float)$row['income'], (float)$row['expense']);

$pageTitle = 'داشبورد'; $activePage = 'dashboard';
require __DIR__ . '/includes/header.php';
?>
<section class="welcome-row"><div><p class="muted">نمای کلی وضعیت مالی در ماه جاری</p><h2>سلام! به فین‌ترک خوش آمدید 👋</h2><p class="muted">با یک نگاه، درآمدها و هزینه‌های خود را دنبال کنید.</p></div><span class="date-chip"><?= e($month) ?></span></section>
<section class="stats-grid">
 <article class="stat-card"><div class="stat-top"><span>موجودی خالص این ماه</span><span class="stat-icon purple">◈</span></div><strong class="stat-value"><?= money($balance) ?></strong><span class="stat-foot">درآمد منهای هزینه</span></article>
 <article class="stat-card"><div class="stat-top"><span>کل درآمد این ماه</span><span class="stat-icon green">↙</span></div><strong class="stat-value"><?= money($summary['income']) ?></strong><span class="stat-foot positive">مجموع دریافتی‌ها</span></article>
 <article class="stat-card"><div class="stat-top"><span>کل هزینه این ماه</span><span class="stat-icon orange">↗</span></div><strong class="stat-value"><?= money($summary['expense']) ?></strong><span class="stat-foot">مجموع پرداختی‌ها</span></article>
</section>
<section class="content-grid">
 <article class="panel chart-panel"><div class="panel-heading"><div><h3>روند درآمد و هزینه</h3><p>نمای مقایسه‌ای شش ماه اخیر</p></div><span class="legend"><i class="income-dot"></i> درآمد <i class="expense-dot"></i> هزینه</span></div>
 <?php if (!$chartRows): ?><div class="empty-state compact"><span>▥</span><strong>هنوز داده‌ای برای نمایش نداریم</strong><p>با ثبت چند تراکنش، نمودار این بخش ساخته می‌شود.</p></div><?php else: ?>
 <div class="bar-chart"><?php foreach ($chartRows as $row): ?><div class="bar-group"><div class="bars"><span class="bar income-bar" style="height:<?= max(3, (float)$row['income']/$maxChart*100) ?>%" title="درآمد: <?= e(money($row['income'])) ?>"></span><span class="bar expense-bar" style="height:<?= max(3, (float)$row['expense']/$maxChart*100) ?>%" title="هزینه: <?= e(money($row['expense'])) ?>"></span></div><small><?= e(substr($row['month'],5,2)) ?></small></div><?php endforeach; ?></div><?php endif; ?>
 </article>
 <article class="panel quick-panel"><div class="panel-heading"><div><h3>شروع سریع</h3><p>عملیات پرکاربرد</p></div></div><a class="quick-link" href="transaction-create.php?type=income"><span class="quick-icon green">＋</span><span><strong>ثبت درآمد</strong><small>حقوق، هدیه و سایر دریافتی‌ها</small></span><b>←</b></a><a class="quick-link" href="transaction-create.php?type=expense"><span class="quick-icon orange">－</span><span><strong>ثبت هزینه</strong><small>خرید، قبض و پرداخت‌ها</small></span><b>←</b></a><a class="quick-link" href="budgets.php"><span class="quick-icon purple">◷</span><span><strong>تنظیم بودجه</strong><small>برای هزینه‌های ماهانه</small></span><b>←</b></a></article>
</section>
<section class="panel table-panel"><div class="panel-heading"><div><h3>آخرین تراکنش‌ها</h3><p>جدیدترین فعالیت‌های مالی ثبت‌شده</p></div><a class="text-link" href="transactions.php">مشاهده همه ←</a></div>
 <?php if (!$recent): ?><div class="empty-state"><span>⇄</span><strong>هنوز تراکنشی ثبت نشده است</strong><p>اولین درآمد یا هزینه خود را ثبت کنید تا داشبورد شما شکل بگیرد.</p><a class="button button-primary" href="transaction-create.php">＋ ثبت اولین تراکنش</a></div><?php else: ?>
 <div class="table-wrap"><table><thead><tr><th>عنوان</th><th>دسته‌بندی</th><th>تاریخ</th><th>مبلغ</th></tr></thead><tbody><?php foreach ($recent as $item): ?><tr><td><strong><?= e($item['title']) ?></strong></td><td><?= e(($item['category_icon'] ?? '•').' '.($item['category_name'] ?? 'بدون دسته')) ?></td><td><?= e($item['transaction_date']) ?></td><td class="<?= $item['type']==='income'?'amount-income':'amount-expense' ?>"><?= $item['type']==='income'?'+':'−' ?> <?= money($item['amount']) ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
