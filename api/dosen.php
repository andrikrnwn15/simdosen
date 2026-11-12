<?php
// REST API Dosen
// GET    api/dosen.php[?id=1&q=&penempatan_id=&status_aktivitas_id=]   publik
// POST   api/dosen.php            token (admin, operator)
// PUT    api/dosen.php?id=1       token (admin, operator)
// DELETE api/dosen.php?id=1       token (admin)
require_once __DIR__.'/../includes/auth.php';
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Token');

function out($d, int $c = 200) { http_response_code($c); echo json_encode($d, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT); exit; }

function api_user(): ?array {
  $hdr = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
  if (!$hdr && function_exists('getallheaders')) foreach (getallheaders() as $k => $v) if (strtolower($k) === 'authorization') $hdr = $v;
  $token = '';
  if (preg_match('/Bearer\s+(\S+)/i', $hdr, $m)) $token = $m[1];
  elseif (!empty($_SERVER['HTTP_X_API_TOKEN'])) $token = $_SERVER['HTTP_X_API_TOKEN'];
  elseif (!empty($_GET['token'])) $token = $_GET['token'];
  if ($token !== '') {
    $st = db()->prepare('SELECT u.id, u.username, u.nama, r.nama AS role FROM users u JOIN roles r ON r.id = u.role_id WHERE u.api_token = ?');
    $st->execute([$token]);
    return $st->fetch() ?: null;
  }
  return current_user();
}
function wajib(string $perm): void {
  $u = api_user();
  if (!$u) out(['status' => 'error', 'message' => 'Token tidak valid atau belum login'], 401);
  if (!role_can($u['role'], $perm)) out(['status' => 'error', 'message' => 'Role '.$u['role'].' tidak berhak melakukan aksi ini'], 403);
}

$method = $_SERVER['REQUEST_METHOD'];
$id = (int)($_GET['id'] ?? 0);
if ($method === 'OPTIONS') out(null, 204);
$body = json_decode(file_get_contents('php://input'), true) ?: [];

try {
  switch ($method) {
    case 'GET':
      if ($id) {
        $st = db()->prepare(dosen_select().' WHERE d.id = ?'); $st->execute([$id]);
        $r = $st->fetch();
        $r ? out(['status' => 'ok', 'data' => $r]) : out(['status' => 'error', 'message' => 'Data tidak ditemukan'], 404);
      }
      $rows = cari_dosen(['q' => trim($_GET['q'] ?? ''), 'penempatan_id' => $_GET['penempatan_id'] ?? 0, 'status_aktivitas_id' => $_GET['status_aktivitas_id'] ?? 0]);
      out(['status' => 'ok', 'total' => count($rows), 'data' => $rows]);

    case 'POST':
      wajib('dosen.create');
      if (empty($body['nama']) || empty($body['nomor_registrasi'])) out(['status' => 'error', 'message' => 'nama dan nomor_registrasi wajib diisi'], 422);
      $vals = [];
      foreach (dosen_cols() as $c) $vals[$c] = ($body[$c] ?? null) === '' ? null : ($body[$c] ?? null);
      if (!in_array($vals['status_serdos'], ['Sudah', 'Belum'], true)) $vals['status_serdos'] = 'Belum';
      $cols = implode(', ', dosen_cols());
      $ph = implode(', ', array_map(fn($c) => ":$c", dosen_cols()));
      db()->prepare("INSERT INTO dosen ($cols) VALUES ($ph)")->execute($vals);
      out(['status' => 'ok', 'id' => (int)db()->lastInsertId()], 201);

    case 'PUT':
      wajib('dosen.update');
      if (!$id) out(['status' => 'error', 'message' => 'id wajib'], 400);
      $set = []; $vals = ['id' => $id];
      foreach (dosen_cols() as $c) if (array_key_exists($c, $body)) { $set[] = "$c = :$c"; $vals[$c] = $body[$c] === '' ? null : $body[$c]; }
      if (!$set) out(['status' => 'error', 'message' => 'Tidak ada field untuk diubah'], 422);
      db()->prepare('UPDATE dosen SET '.implode(', ', $set).' WHERE id = :id')->execute($vals);
      out(['status' => 'ok', 'message' => 'Data diperbarui']);

    case 'DELETE':
      wajib('dosen.delete');
      if (!$id) out(['status' => 'error', 'message' => 'id wajib'], 400);
      $st = db()->prepare('DELETE FROM dosen WHERE id = ?'); $st->execute([$id]);
      $st->rowCount() ? out(['status' => 'ok', 'message' => 'Data dihapus']) : out(['status' => 'error', 'message' => 'Data tidak ditemukan'], 404);

    default:
      out(['status' => 'error', 'message' => 'Method tidak didukung'], 405);
  }
} catch (PDOException $e) {
  out(['status' => 'error', 'message' => $e->getCode() == 23000 ? 'Data duplikat atau referensi tidak valid' : 'Kesalahan database'], 409);
}
