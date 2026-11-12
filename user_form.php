<?php
require_once __DIR__.'/includes/auth.php';
require_perm('users.manage');
$id = (int)($_GET['id'] ?? 0);
$d = ['username' => '', 'nama' => '', 'role_id' => ''];
if ($id) {
  $st = db()->prepare('SELECT id, username, nama, role_id FROM users WHERE id = ?'); $st->execute([$id]);
  $d = $st->fetch();
  if (!$d) { flash('danger', 'Pengguna tidak ditemukan.'); redirect('users.php'); }
}
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  $d = ['username' => trim($_POST['username'] ?? ''), 'nama' => trim($_POST['nama'] ?? ''), 'role_id' => (int)($_POST['role_id'] ?? 0)];
  $pass = $_POST['password'] ?? '';
  if ($d['username'] === '' || $d['nama'] === '' || !$d['role_id']) $err = 'Username, nama, dan role wajib diisi.';
  elseif (!$id && strlen($pass) < 6) $err = 'Password minimal 6 karakter.';
  elseif ($id && $pass !== '' && strlen($pass) < 6) $err = 'Password baru minimal 6 karakter.';
  else {
    try {
      if ($id) {
        db()->prepare('UPDATE users SET username = ?, nama = ?, role_id = ? WHERE id = ?')->execute([$d['username'], $d['nama'], $d['role_id'], $id]);
        if ($pass !== '') db()->prepare('UPDATE users SET password = ? WHERE id = ?')->execute([password_hash($pass, PASSWORD_DEFAULT), $id]);
      } else {
        db()->prepare('INSERT INTO users (username, password, nama, role_id) VALUES (?, ?, ?, ?)')->execute([$d['username'], password_hash($pass, PASSWORD_DEFAULT), $d['nama'], $d['role_id']]);
      }
      flash('success', 'Pengguna disimpan.'); redirect('users.php');
    } catch (PDOException $e) {
      $err = $e->getCode() == 23000 ? 'Username sudah digunakan.' : 'Gagal menyimpan: '.$e->getMessage();
    }
  }
}
$roles = db()->query('SELECT id, nama, keterangan FROM roles ORDER BY id')->fetchAll();
$judul = $id ? 'Edit Pengguna' : 'Tambah Pengguna';
require __DIR__.'/includes/header.php';
?>
<h3><?= h($judul) ?></h3>
<?php if ($err): ?><div class="alert alert-danger"><?= h($err) ?></div><?php endif; ?>
<form method="post" class="card card-body shadow-sm" style="max-width:560px"><?= csrf_field() ?>
  <label class="form-label">Username<input class="form-control" name="username" value="<?= h($d['username']) ?>" required></label>
  <label class="form-label">Nama<input class="form-control" name="nama" value="<?= h($d['nama']) ?>" required></label>
  <label class="form-label">Role<select class="form-select" name="role_id" required><option value="">- pilih -</option>
    <?php foreach ($roles as $r): ?><option value="<?= $r['id'] ?>" <?= $d['role_id'] == $r['id'] ? 'selected' : '' ?>><?= h($r['nama']) ?> - <?= h($r['keterangan']) ?></option><?php endforeach; ?></select></label>
  <label class="form-label">Password <?= $id ? '(kosongkan jika tidak diubah)' : '' ?><input type="password" class="form-control" name="password" <?= $id ? '' : 'required' ?>></label>
  <div><button class="btn btn-success">Simpan</button> <a class="btn btn-link" href="users.php">Batal</a></div>
</form>
<?php require __DIR__.'/includes/footer.php'; ?>
