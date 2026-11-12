<?php
require_once __DIR__.'/includes/auth.php';
require_perm('master.manage');
$t = $_GET['t'] ?? array_key_first(MASTER);
if (!isset(MASTER[$t])) $t = array_key_first(MASTER);   // whitelist nama tabel
$judul = 'Master Data';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  $aksi = $_POST['aksi'] ?? '';
  $nama = trim($_POST['nama'] ?? '');
  $rid = (int)($_POST['id'] ?? 0);
  try {
    if ($aksi === 'tambah' && $nama !== '') {
      db()->prepare("INSERT INTO $t (nama) VALUES (?)")->execute([$nama]); flash('success', 'Data ditambahkan.');
    } elseif ($aksi === 'ubah' && $nama !== '') {
      db()->prepare("UPDATE $t SET nama = ? WHERE id = ?")->execute([$nama, $rid]); flash('success', 'Data diperbarui.');
    } elseif ($aksi === 'hapus') {
      db()->prepare("DELETE FROM $t WHERE id = ?")->execute([$rid]); flash('success', 'Data dihapus.');
    } else {
      flash('danger', 'Nama tidak boleh kosong.');
    }
  } catch (PDOException $e) {
    flash('danger', 'Data tidak dapat dihapus karena masih dipakai oleh data dosen.');
  }
  redirect('master.php?t='.$t);
}
$rows = db()->query("SELECT m.id, m.nama FROM $t m ORDER BY m.id")->fetchAll();
require __DIR__.'/includes/header.php';
?>
<h3 class="mb-3">Master Data</h3>
<ul class="nav nav-pills mb-3">
  <?php foreach (MASTER as $key => $lb): ?>
  <li class="nav-item"><a class="nav-link <?= $key === $t ? 'active bg-success' : 'text-success' ?>" href="master.php?t=<?= $key ?>"><?= h($lb) ?></a></li>
  <?php endforeach; ?>
</ul>
<div class="card shadow-sm" style="max-width:720px"><div class="card-header fw-semibold"><?= h(MASTER[$t]) ?></div><div class="card-body">
  <form method="post" class="d-flex gap-2 mb-3"><?= csrf_field() ?>
    <input type="hidden" name="aksi" value="tambah">
    <input class="form-control" name="nama" placeholder="Tambah <?= h(MASTER[$t]) ?> baru" required>
    <button class="btn btn-success">Tambah</button>
  </form>
  <table class="table table-bordered align-middle mb-0">
    <?php foreach ($rows as $r): ?>
    <tr><td style="width:50px"><?= $r['id'] ?></td><td>
      <form method="post" class="d-flex gap-2"><?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= $r['id'] ?>">
        <input class="form-control form-control-sm" name="nama" value="<?= h($r['nama']) ?>">
        <button class="btn btn-sm btn-outline-primary" name="aksi" value="ubah">Simpan</button>
        <button class="btn btn-sm btn-outline-danger" name="aksi" value="hapus" onclick="return confirm('Hapus data ini?')">Hapus</button>
      </form></td></tr>
    <?php endforeach; ?>
  </table>
</div></div>
<?php require __DIR__.'/includes/footer.php'; ?>
