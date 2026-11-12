<?php
require_once __DIR__.'/includes/auth.php';
require_perm('laporan.read');
$judul = 'Laporan Dosen';
$f = ['penempatan_id' => (int)($_GET['penempatan_id'] ?? 0), 'status_aktivitas_id' => (int)($_GET['status_aktivitas_id'] ?? 0), 'pendidikan_id' => (int)($_GET['pendidikan_id'] ?? 0)];
$rows = cari_dosen($f);
$pen = db()->query('SELECT id, nama FROM penempatan ORDER BY nama')->fetchAll();
$sta = db()->query('SELECT id, nama FROM status_aktivitas ORDER BY id')->fetchAll();
$pdd = db()->query('SELECT id, nama FROM pendidikan ORDER BY id')->fetchAll();
function nama_dari(array $list, int $id, string $semua): string { foreach ($list as $x) if ($x['id'] == $id) return $x['nama']; return $semua; }
$rekapPdd = array_count_values(array_map(fn($r) => $r['pendidikan'] ?? '-', $rows));
$rekapJab = array_count_values(array_map(fn($r) => $r['jabatan'] ?? '-', $rows));
$qs = http_build_query(array_filter($f));
require __DIR__.'/includes/header.php';
?>
<style>@media print{@page{size:landscape;margin:10mm}body{background:#fff!important}.tbl td,.tbl th{font-size:9px!important;padding:2px 4px!important}}</style>
<form method="get" class="row g-2 mb-3 d-print-none">
  <div class="col-md-3"><select class="form-select" name="penempatan_id"><option value="0">Semua penempatan</option>
    <?php foreach ($pen as $p): ?><option value="<?= $p['id'] ?>" <?= $f['penempatan_id'] == $p['id'] ? 'selected' : '' ?>><?= h($p['nama']) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-3"><select class="form-select" name="status_aktivitas_id"><option value="0">Semua status aktivitas</option>
    <?php foreach ($sta as $s): ?><option value="<?= $s['id'] ?>" <?= $f['status_aktivitas_id'] == $s['id'] ? 'selected' : '' ?>><?= h($s['nama']) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-2"><select class="form-select" name="pendidikan_id"><option value="0">Semua pendidikan</option>
    <?php foreach ($pdd as $s): ?><option value="<?= $s['id'] ?>" <?= $f['pendidikan_id'] == $s['id'] ? 'selected' : '' ?>><?= h($s['nama']) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-4 d-flex gap-2">
    <button class="btn btn-outline-success">Tampilkan</button>
    <button type="button" class="btn btn-success" onclick="window.print()">Cetak / PDF</button>
    <a class="btn btn-outline-secondary" href="laporan_csv.php?<?= h($qs) ?>">Export CSV</a>
  </div>
</form>

<div class="bg-white p-3 border">
  <h5 class="fw-bold">Daftar Dosen Aktif Universitas</h5>
  <div class="small mb-3">
    Universitas : Universitas Muhammadiyah Bengkulu<br>
    Fakultas : Teknik<br>
    Penempatan : <?= h(nama_dari($pen, $f['penempatan_id'], 'Semua')) ?><br>
    Status Aktivitas : <?= h(nama_dari($sta, $f['status_aktivitas_id'], 'Semua')) ?> &middot; Tanggal cetak : <?= date('d-m-Y') ?>
  </div>
  <div class="table-responsive"><table class="table table-bordered table-sm tbl">
    <thead><tr><th>No</th><?php foreach (KOLOM as $lb): ?><th><?= h($lb) ?></th><?php endforeach; ?></tr></thead>
    <?php foreach ($rows as $i => $r): ?>
    <tr><td><?= $i + 1 ?></td><?php foreach (KOLOM as $k => $_): ?><td><?= h($r[$k]) ?></td><?php endforeach; ?></tr>
    <?php endforeach; ?>
    <?php if (!$rows): ?><tr><td colspan="<?= count(KOLOM) + 1 ?>" class="text-center text-secondary">Tidak ada data.</td></tr><?php endif; ?>
  </table></div>
  <div class="row mt-3 small">
    <div class="col-md-4"><strong>Total dosen: <?= count($rows) ?></strong></div>
    <div class="col-md-4"><strong>Rekap pendidikan</strong><br><?php foreach ($rekapPdd as $k => $v): ?><?= h($k) ?>: <?= $v ?><br><?php endforeach; ?></div>
    <div class="col-md-4"><strong>Rekap jabatan fungsional</strong><br><?php foreach ($rekapJab as $k => $v): ?><?= h($k ?: '-') ?>: <?= $v ?><br><?php endforeach; ?></div>
  </div>
</div>
<?php require __DIR__.'/includes/footer.php'; ?>
