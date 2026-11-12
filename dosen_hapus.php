<?php
require_once __DIR__.'/includes/auth.php';
require_perm('dosen.delete');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  db()->prepare('DELETE FROM dosen WHERE id = ?')->execute([(int)($_POST['id'] ?? 0)]);
  flash('success', 'Data dosen dihapus.');
}
redirect('dosen.php');
