<?php
$u = current_user();
$halaman = basename($_SERVER['PHP_SELF']);
function nav_item(string $file, string $label, string $perm, string $halaman, array $alias = []): void {
  if (!can($perm)) return;
  $aktif = $halaman === $file || in_array($halaman, $alias, true);
  echo '<li class="nav-item"><a class="nav-link'.($aktif ? ' active fw-semibold' : '').'" href="'.$file.'">'.$label.'</a></li>';
}
?><!DOCTYPE html>
<html lang="id"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= h($judul ?? 'SIMDOSEN') ?> - SIMDOSEN UM Bengkulu</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>.tbl th{white-space:nowrap;background:#e8f1ec}.tbl td{white-space:nowrap;font-size:.85rem}</style>
</head><body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-success d-print-none">
<div class="container-fluid px-4">
  <a class="navbar-brand fw-bold" href="dashboard.php">SIMDOSEN UMB</a>
  <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
  <div class="collapse navbar-collapse" id="nav">
    <ul class="navbar-nav me-auto">
      <?php
      nav_item('dashboard.php', 'Dashboard', 'dashboard', $halaman);
      nav_item('dosen.php', 'Data Dosen', 'dosen.read', $halaman, ['dosen_form.php']);
      nav_item('master.php', 'Master Data', 'master.manage', $halaman);
      nav_item('laporan.php', 'Laporan', 'laporan.read', $halaman);
      nav_item('users.php', 'Pengguna', 'users.manage', $halaman, ['user_form.php']);
      ?>
    </ul>
    <span class="navbar-text me-3"><?= h($u['nama'] ?? '') ?> <span class="badge bg-warning text-dark"><?= h($u['role'] ?? '') ?></span></span>
    <a class="btn btn-sm btn-outline-light" href="logout.php">Logout</a>
  </div>
</div></nav>
<main class="container-fluid px-4 py-4">
<?php if (!empty($_SESSION['flash'])): [$ft, $fm] = $_SESSION['flash']; unset($_SESSION['flash']); ?>
  <div class="alert alert-<?= h($ft) ?> d-print-none"><?= h($fm) ?></div>
<?php endif; ?>
