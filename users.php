<?php
require_once __DIR__.'/includes/auth.php';
require_perm('users.manage');
$judul = 'Pengguna';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  $uid = (int)($_POST['id'] ?? 0);
  if ($uid === (int)current_user()['id']) flash('danger', 'Anda tidak dapat menghapus akun sendiri.');
  else { db()->prepare('DELETE FROM users WHERE id = ?')->execute([$uid]); flash('success', 'Pengguna dihapus.'); }
  redirect('users.php');
}
$rows = db()->query('SELECT u.id, u.username, u.nama, r.nama AS role FROM users u JOIN roles r ON r.id = u.role_id ORDER BY u.id')->fetchAll();
require __DIR__.'/includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3 class="mb-0">Pengguna & Hak Akses</h3><a class="btn btn-success" href="user_form.php">+ Tambah Pengguna</a>
</div>
<div class="table-responsive bg-white"><table class="table table-bordered align-middle">
<thead class="table-success"><tr><th>No</th><th>Username</th><th>Nama</th><th>Role</th><th>Aksi</th></tr></thead>
<?php foreach ($rows as $i => $r): ?>
<tr><td><?= $i + 1 ?></td><td><?= h($r['username']) ?></td><td><?= h($r['nama']) ?></td><td><span class="badge text-bg-secondary"><?= h($r['role']) ?></span></td>
<td class="d-flex gap-1"><a class="btn btn-sm btn-outline-primary" href="user_form.php?id=<?= $r['id'] ?>">Edit</a>
<form method="post" onsubmit="return confirm('Hapus pengguna ini?')"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $r['id'] ?>"><button class="btn btn-sm btn-outline-danger">Hapus</button></form></td></tr>
<?php endforeach; ?>
</table></div>
<?php require __DIR__.'/includes/footer.php'; ?>
