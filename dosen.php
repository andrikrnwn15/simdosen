<?php
require_once __DIR__.'/includes/auth.php';
require_perm('dosen.read');
$judul = 'Data Dosen';
$f = ['q' => trim($_GET['q'] ?? ''), 'penempatan_id' => (int)($_GET['penempatan_id'] ?? 0), 'status_aktivitas_id' => (int)($_GET['status_aktivitas_id'] ?? 0)];
$rows = cari_dosen($f);
$pen = db()->query('SELECT id, nama FROM penempatan ORDER BY nama')->fetchAll();
$sta = db()->query('SELECT id, nama FROM status_aktivitas ORDER BY id')->fetchAll();
require __DIR__.'/includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3 class="mb-0">Data Dosen</h3>
  <?php if (can('dosen.create')): ?><a class="btn btn-success" href="dosen_form.php">+ Tambah Dosen</a><?php endif; ?>
</div>
<form method="get" class="row g-2 mb-3">
  <div class="col-md-4"><input class="form-control" name="q" value="<?= h($f['q']) ?>" placeholder="Cari nama / NIP / NUPTK / No. registrasi"></div>
  <div class="col-md-3"><select class="form-select" name="penempatan_id"><option value="0">Semua penempatan</option>
    <?php foreach ($pen as $p): ?><option value="<?= $p['id'] ?>" <?= $f['penempatan_id'] == $p['id'] ? 'selected' : '' ?>><?= h($p['nama']) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-3"><select class="form-select" name="status_aktivitas_id"><option value="0">Semua status aktivitas</option>
    <?php foreach ($sta as $s): ?><option value="<?= $s['id'] ?>" <?= $f['status_aktivitas_id'] == $s['id'] ? 'selected' : '' ?>><?= h($s['nama']) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-2"><button class="btn btn-outline-success w-100">Filter</button></div>
</form>
<p class="text-secondary"><?= count($rows) ?> data dosen</p>
<div class="table-responsive bg-white"><table class="table table-bordered table-sm tbl align-middle">
<thead><tr><th>No</th><?php foreach (KOLOM as $lb): ?><th><?= h($lb) ?></th><?php endforeach; ?><?php if (can('dosen.update') || can('dosen.delete')): ?><th>Aksi</th><?php endif; ?></tr></thead>
<?php foreach ($rows as $i => $r): ?>
<tr><td><?= $i + 1 ?></td>
<?php foreach (KOLOM as $k => $_): ?><td><?= h($r[$k]) ?></td><?php endforeach; ?>
<?php if (can('dosen.update') || can('dosen.delete')): ?>
<td class="d-flex gap-1">
  <?php if (can('dosen.update')): ?><a class="btn btn-sm btn-outline-primary" href="dosen_form.php?id=<?= $r['id'] ?>">Edit</a><?php endif; ?>
  <?php if (can('dosen.delete')): ?>
  <form method="post" action="dosen_hapus.php" onsubmit="return confirm('Hapus data dosen ini?')"><?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= $r['id'] ?>"><button class="btn btn-sm btn-outline-danger">Hapus</button></form>
  <?php endif; ?>
</td><?php endif; ?>
</tr>
<?php endforeach; ?>
</table></div>
<?php require __DIR__.'/includes/footer.php'; ?>
