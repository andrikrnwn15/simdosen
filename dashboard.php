<?php
require_once __DIR__.'/includes/auth.php';
require_perm('dashboard');
$judul = 'Dashboard';
function sebaran(string $tabel, string $fk): array {
  return db()->query("SELECT m.nama, COUNT(d.id) AS jml FROM $tabel m LEFT JOIN dosen d ON d.$fk = m.id GROUP BY m.id, m.nama ORDER BY jml DESC, m.id")->fetchAll();
}
function kartu_sebaran(string $judul, array $rows, int $total): void {
  echo '<div class="card shadow-sm h-100"><div class="card-header fw-semibold">'.h($judul).'</div><div class="card-body">';
  foreach ($rows as $r) {
    $pct = $total ? round($r['jml'] / $total * 100) : 0;
    echo '<div class="small d-flex justify-content-between"><span>'.h($r['nama']).'</span><span>'.(int)$r['jml'].'</span></div>';
    echo '<div class="progress mb-2" style="height:8px"><div class="progress-bar bg-success" style="width:'.$pct.'%"></div></div>';
  }
  echo '</div></div>';
}
$total  = (int)db()->query('SELECT COUNT(*) FROM dosen')->fetchColumn();
$aktif  = (int)db()->query("SELECT COUNT(*) FROM dosen d JOIN status_aktivitas s ON s.id = d.status_aktivitas_id WHERE s.nama = 'Aktif'")->fetchColumn();
$serdos = (int)db()->query("SELECT COUNT(*) FROM dosen WHERE status_serdos = 'Sudah'")->fetchColumn();
$s3     = (int)db()->query("SELECT COUNT(*) FROM dosen d JOIN pendidikan p ON p.id = d.pendidikan_id WHERE p.nama = 'S3'")->fetchColumn();
require __DIR__.'/includes/header.php';
?>
<h3 class="mb-3">Dashboard</h3>
<div class="row g-3 mb-4">
  <?php foreach ([['Total Dosen', $total, 'success'], ['Dosen Aktif', $aktif, 'primary'], ['Sudah Sertifikasi', $serdos, 'warning'], ['Berpendidikan S3', $s3, 'info']] as [$lb, $n, $c]): ?>
  <div class="col-6 col-lg-3"><div class="card border-<?= $c ?> shadow-sm"><div class="card-body">
    <div class="text-secondary small"><?= h($lb) ?></div><div class="display-6 fw-bold"><?= $n ?></div>
  </div></div></div>
  <?php endforeach; ?>
</div>
<div class="row g-3">
  <div class="col-md-4"><?php kartu_sebaran('Jabatan Fungsional', sebaran('jabatan_fungsional', 'jabatan_id'), $total); ?></div>
  <div class="col-md-4"><?php kartu_sebaran('Pendidikan Terakhir', sebaran('pendidikan', 'pendidikan_id'), $total); ?></div>
  <div class="col-md-4"><?php kartu_sebaran('Status Aktivitas', sebaran('status_aktivitas', 'status_aktivitas_id'), $total); ?></div>
</div>
<?php require __DIR__.'/includes/footer.php'; ?>
