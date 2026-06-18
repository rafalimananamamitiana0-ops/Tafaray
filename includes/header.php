<?php
$current = basename($_SERVER['PHP_SELF']);
?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= isset($pageTitle) ? e($pageTitle) . ' — ' : '' ?>TAFARAY-SENI</title>
<link rel="stylesheet" href="assets/style.css">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body>
<aside class="sidebar">
  <div class="brand">
    <div class="logo">TS</div>
    <div>
      <div class="brand-title">TAFARAY</div>
      <div class="brand-sub">SENI · 2026</div>
    </div>
  </div>
  <nav>
    <a href="index.php" class="<?= $current==='index.php'?'active':'' ?>"><span>◐</span> Tableau de bord</a>
    <a href="etudiants.php" class="<?= $current==='etudiants.php'?'active':'' ?>"><span>◇</span> Étudiants</a>
    <a href="droits.php" class="<?= $current==='droits.php'?'active':'' ?>"><span>◈</span> Droits</a>
    <a href="qr_all.php" class="<?= $current==='qr_all.php'?'active':'' ?>"><span>▦</span> Tous les QR (PDF)</a>
  </nav>
  <div class="sidebar-foot">
    <a class="btn btn-ghost btn-block" href="logout.php" style="margin-top:10px;text-align:center;">
     Se déconnecter
</a>
    <div class="muted">Gestion réception<br>des novices</div>
  </div>
</aside>
<main class="main">
  <header class="topbar">
    <div>
      <div class="eyebrow">TAFARAY-SENI</div>
      <h1><?= e($pageTitle ?? 'Tableau de bord') ?></h1>
    </div>
    <div class="topbar-actions">
      <?php if (!empty($topbarActions)) echo $topbarActions; ?>
    </div>
  </header>
  <section class="content">
