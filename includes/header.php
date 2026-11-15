<?php
require_once __DIR__ . '/functions.php';
$pageTitle = $pageTitle ?? 'داشبورد';
$activePage = $activePage ?? '';
$flashMessage = get_flash();
?>
<!doctype html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#101827">
  <title><?= e($pageTitle) ?> | فین‌ترک</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app-shell">
  <aside class="sidebar" id="sidebar">
    <a class="brand" href="index.php"><span class="brand-mark">ف</span><span>فین‌ترک<small>مدیریت مالی شخصی</small></span></a>
    <div class="nav-caption">فضای کاری</div>
    <nav class="main-nav">
      <a class="<?= $activePage === 'dashboard' ? 'active' : '' ?>" href="index.php"><span>▦</span> داشبورد</a>
      <a class="<?= $activePage === 'transactions' ? 'active' : '' ?>" href="transactions.php"><span>⇄</span> تراکنش‌ها</a>
      <a class="<?= $activePage === 'budgets' ? 'active' : '' ?>" href="budgets.php"><span>◷</span> بودجه‌بندی</a>
      <a class="<?= $activePage === 'reports' ? 'active' : '' ?>" href="reports.php"><span>▥</span> گزارش‌ها</a>
    </nav>
    <div class="sidebar-bottom"><div class="help-card"><span>✦</span><strong>مدیریت آگاهانه</strong><p>با ثبت منظم تراکنش‌ها، تصویر روشن‌تری از وضعیت مالی خود داشته باشید.</p></div><span class="version">نسخه ۱.۰.۰</span></div>
  </aside>
  <main class="main-content">
    <header class="topbar"><button class="icon-button menu-toggle" id="menuToggle" aria-label="باز کردن منو">☰</button><div><span class="eyebrow">فضای مالی شما</span><h1><?= e($pageTitle) ?></h1></div><a class="button button-primary top-action" href="transaction-create.php">＋ ثبت تراکنش جدید</a></header>
    <?php if ($flashMessage): ?><div class="alert alert-<?= e($flashMessage['type']) ?>"><?= e($flashMessage['message']) ?></div><?php endif; ?>
    <div class="page-body">
