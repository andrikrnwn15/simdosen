<?php
require_once __DIR__.'/includes/auth.php';
$id = (int)($_GET['id'] ?? 0);
require_perm($id ? 'dosen.update' : 'dosen.create');
$d = array_fill_keys(dosen_cols(), '');
$d['status_serdos'] = 'Belum';
if ($id) {
  $st = db()->prepare('SELECT * FROM dosen WHERE id = ?');
  $st->execute([$id]);
  $d = $st->fetch();
  if (!$d) { flash('danger', 'Data dosen tidak ditemukan.'); redirect('dosen.php'); }
}
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  $data = [];
  foreach (dosen_cols() as $c) {
    $v = trim($_POST[$c] ?? '');
    $data[$c] = substr($c, -3) === '_id' ? ($v === '' ? null : (int)$v) : $v;
  }
  if (!in_array($data['status_serdos'], ['Sudah', 'Belum'], true)) $data['status_serdos'] = 'Belum';
  $d = $data + $d;
  if ($data['nama'] === '' || $data['nomor_registrasi'] === '') {
    $err = 'Nama dan Nomor Registrasi wajib diisi.';
  } else {
    try {
      if ($id) {
        $set = implode(', ', array_map(fn($c) => "$c = :$c", dosen_cols()));
        db()->prepare("UPDATE dosen SET $set WHERE id = :id")->execute($data + ['id' => $id]);
        flash('success', 'Data dosen diperbarui.');
      } else {
        $cols = implode(', ', dosen_cols());
        $ph = implode(', ', array_map(fn($c) => ":$c", dosen_cols()));
        db()->prepare("INSERT INTO dosen ($cols) VALUES ($ph)")->execute($data);
        flash('success', 'Data dosen ditambahkan.');
      }
      redirect('dosen.php');
    } catch (PDOException $e) {
      $err = $e->getCode() == 23000 ? 'Nomor Registrasi sudah digunakan.' : 'Gagal menyimpan: '.$e->getMessage();
    }
  }
}
$opt = ref_options();
$judul = $id ? 'Edit Dosen' : 'Tambah Dosen';
require __DIR__.'/includes/header.php';
?>
<h3><?= h($judul) ?></h3>
<?php if ($err): ?><div class="alert alert-danger"><?= h($err) ?></div><?php endif; ?>
<form method="post" class="card card-body shadow-sm"><?= csrf_field() ?>
<div class="row g-3">
  <div class="col-md-4"><label class="form-label">Nomor Registrasi *<input class="form-control" name="nomor_registrasi" value="<?= h($d['nomor_registrasi']) ?>" required></label></div>
  <div class="col-md-4"><label class="form-label">NUPTK<input class="form-control" name="nuptk" value="<?= h($d['nuptk']) ?>"></label></div>
  <div class="col-md-4"><label class="form-label">NIP<input class="form-control" name="nip" value="<?= h($d['nip']) ?>"></label></div>
  <div class="col-md-8"><label class="form-label">Nama *<input class="form-control" name="nama" value="<?= h($d['nama']) ?>" required></label></div>
  <div class="col-md-4"><label class="form-label">Status Serdos<select class="form-select" name="status_serdos">
    <?php foreach (['Sudah', 'Belum'] as $s): ?><option <?= $d['status_serdos'] === $s ? 'selected' : '' ?>><?= $s ?></option><?php endforeach; ?></select></label></div>
  <?php foreach (REFS as $col => [$tbl, $label]): ?>
  <div class="col-md-4"><label class="form-label"><?= h($label) ?><select class="form-select" name="<?= $col ?>">
    <option value="">- pilih -</option>
    <?php foreach ($opt[$col] as $o): ?><option value="<?= $o['id'] ?>" <?= (string)$d[$col] === (string)$o['id'] ? 'selected' : '' ?>><?= h($o['nama']) ?></option><?php endforeach; ?>
  </select></label></div>
  <?php endforeach; ?>
</div>
<div class="mt-3"><button class="btn btn-success">Simpan</button> <a class="btn btn-link" href="dosen.php">Batal</a></div>
</form>
<?php require __DIR__.'/includes/footer.php'; ?>
