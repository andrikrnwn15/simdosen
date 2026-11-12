<?php
require_once __DIR__.'/includes/auth.php';
$user = current_user();
$total  = (int)db()->query('SELECT COUNT(*) FROM dosen')->fetchColumn();
$aktif  = (int)db()->query("SELECT COUNT(*) FROM dosen d JOIN status_aktivitas s ON s.id = d.status_aktivitas_id WHERE s.nama = 'Aktif'")->fetchColumn();
$serdos = (int)db()->query("SELECT COUNT(*) FROM dosen WHERE status_serdos = 'Sudah'")->fetchColumn();
$prodi  = (int)db()->query('SELECT COUNT(*) FROM penempatan')->fetchColumn();
$terbaru = array_slice(cari_dosen(), 0, 6);
?><!DOCTYPE html>
<html lang="id"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>SIMDOSEN - Sistem Informasi Dosen UM Bengkulu</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>.hero{background:linear-gradient(135deg,#0b5d3b,#198754);color:#fff;padding:90px 0}</style>
</head><body>
<nav class="navbar navbar-dark bg-success"><div class="container">
  <a class="navbar-brand fw-bold" href="index.php">SIMDOSEN UMB</a>
  <div>
    <a class="btn btn-sm btn-outline-light me-1" href="api/dosen.php" target="_blank">API</a>
    <?php if ($user): ?><a class="btn btn-sm btn-warning" href="dashboard.php">Dashboard</a>
    <?php else: ?><a class="btn btn-sm btn-warning" href="login.php">Login</a><?php endif; ?>
  </div>
</div></nav>

<section class="hero text-center"><div class="container">
  <h1 class="display-5 fw-bold">Sistem Informasi Dosen</h1>
  <p class="lead mb-4">Universitas Muhammadiyah Bengkulu &middot; Fakultas Teknik</p>
  <p class="mb-4">Kelola data dosen, master data, dan laporan dalam satu aplikasi, lengkap dengan Web Service (API).</p>
  <a class="btn btn-warning btn-lg me-2" href="<?= $user ? 'dashboard.php' : 'login.php' ?>"><?= $user ? 'Buka Dashboard' : 'Masuk ke Sistem' ?></a>
  <a class="btn btn-outline-light btn-lg" href="#api">Dokumentasi API</a>
</div></section>

<section class="py-5 bg-light"><div class="container"><div class="row g-3 text-center">
  <?php foreach ([['Total Dosen', $total], ['Dosen Aktif', $aktif], ['Sudah Serdos', $serdos], ['Penempatan / Prodi', $prodi]] as [$lb, $n]): ?>
  <div class="col-6 col-md-3"><div class="card shadow-sm"><div class="card-body">
    <div class="display-6 fw-bold text-success"><?= $n ?></div><div class="text-secondary"><?= h($lb) ?></div>
  </div></div></div>
  <?php endforeach; ?>
</div></div></section>

<section class="py-5"><div class="container">
  <h3 class="mb-3">Daftar Dosen</h3>
  <div class="table-responsive"><table class="table table-striped table-bordered">
    <thead class="table-success"><tr><th>Nama</th><th>Jabatan Fungsional</th><th>Pendidikan</th><th>Rumpun Ilmu</th><th>Status</th><th>Penempatan</th></tr></thead>
    <?php foreach ($terbaru as $r): ?>
    <tr><td><?= h($r['nama']) ?></td><td><?= h($r['jabatan']) ?></td><td><?= h($r['pendidikan']) ?></td><td><?= h($r['rumpun']) ?></td><td><?= h($r['status_aktivitas']) ?></td><td><?= h($r['penempatan']) ?></td></tr>
    <?php endforeach; ?>
  </table></div>
  <p class="text-secondary small">Data lengkap dan pengelolaan tersedia setelah login.</p>
</div></section>

<section class="py-5 bg-light" id="api"><div class="container">
  <h3 class="mb-3">Web Service (REST API)</h3>
  <div class="table-responsive"><table class="table table-bordered bg-white">
    <thead class="table-success"><tr><th>Method</th><th>Endpoint</th><th>Keterangan</th><th>Akses</th></tr></thead>
    <tr><td>POST</td><td><code>api/login.php</code></td><td>Dapatkan token (body JSON: username, password)</td><td>Publik</td></tr>
    <tr><td>GET</td><td><code>api/dosen.php</code></td><td>Daftar dosen (filter: q, penempatan_id, status_aktivitas_id)</td><td>Publik</td></tr>
    <tr><td>GET</td><td><code>api/dosen.php?id=1</code></td><td>Detail satu dosen</td><td>Publik</td></tr>
    <tr><td>POST</td><td><code>api/dosen.php</code></td><td>Tambah dosen (JSON)</td><td>Token: admin, operator</td></tr>
    <tr><td>PUT</td><td><code>api/dosen.php?id=1</code></td><td>Ubah dosen (JSON)</td><td>Token: admin, operator</td></tr>
    <tr><td>DELETE</td><td><code>api/dosen.php?id=1</code></td><td>Hapus dosen</td><td>Token: admin</td></tr>
  </table></div>
  <p class="small text-secondary">Kirim token pada header <code>Authorization: Bearer TOKEN</code>.</p>
</div></section>

<footer class="text-center text-secondary small py-3">Tugas Praktikum 2345 &middot; Andri Kurniawan &middot; 2355201147</footer>
</body></html>
