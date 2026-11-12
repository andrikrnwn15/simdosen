<?php
require_once __DIR__.'/includes/auth.php';
if (current_user()) redirect('dashboard.php');
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  if (login_user(trim($_POST['username'] ?? ''), $_POST['password'] ?? '')) redirect('dashboard.php');
  $err = 'Username atau password salah.';
}
?><!DOCTYPE html>
<html lang="id"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login - SIMDOSEN</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="bg-light"><div class="container" style="max-width:420px;margin-top:10vh">
  <div class="text-center mb-3"><h3 class="text-success fw-bold">SIMDOSEN UMB</h3><div class="text-secondary">Masuk ke sistem</div></div>
  <?php if (!empty($_SESSION['flash'])): [$ft, $fm] = $_SESSION['flash']; unset($_SESSION['flash']); ?><div class="alert alert-<?= h($ft) ?>"><?= h($fm) ?></div><?php endif; ?>
  <?php if ($err): ?><div class="alert alert-danger"><?= h($err) ?></div><?php endif; ?>
  <form method="post" class="card card-body shadow-sm">
    <?= csrf_field() ?>
    <label class="form-label">Username<input class="form-control" name="username" required autofocus></label>
    <label class="form-label">Password<input type="password" class="form-control" name="password" required></label>
    <button class="btn btn-success">Login</button>
  </form>
  <div class="small text-secondary mt-3 text-center">Akun demo: admin / admin123 &middot; operator / operator123 &middot; pimpinan / pimpinan123</div>
  <div class="text-center mt-2"><a href="index.php">&larr; Kembali ke beranda</a></div>
</div></body></html>
