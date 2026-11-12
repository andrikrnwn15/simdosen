<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!file_exists(__DIR__.'/../config.local.php')) {
  exit('config.local.php belum dibuat. Salin dari config.local.example.php lalu isi kredensial database.');
}
require_once __DIR__.'/../config.local.php';

// Tabel master (referensi) yang bisa dikelola
const MASTER = [
  'jabatan_fungsional' => 'Jabatan Fungsional',
  'kepangkatan'        => 'Kepangkatan',
  'pendidikan'         => 'Pendidikan Terakhir',
  'rumpun_ilmu'        => 'Rumpun Ilmu',
  'status_pegawai'     => 'Status Pegawai',
  'ikatan_kerja'       => 'Ikatan Kerja',
  'status_aktivitas'   => 'Status Aktivitas',
  'penempatan'         => 'Penempatan',
];
// Kolom FK pada tabel dosen => [tabel master, label]
const REFS = [
  'jabatan_id'          => ['jabatan_fungsional', 'Jabatan Fungsional'],
  'pangkat_id'          => ['kepangkatan', 'Kepangkatan'],
  'pendidikan_id'       => ['pendidikan', 'Pendidikan Terakhir'],
  'rumpun_id'           => ['rumpun_ilmu', 'Rumpun Ilmu'],
  'status_pegawai_id'   => ['status_pegawai', 'Status Pegawai'],
  'ikatan_kerja_id'     => ['ikatan_kerja', 'Ikatan Kerja'],
  'status_aktivitas_id' => ['status_aktivitas', 'Status Aktivitas'],
  'penempatan_id'       => ['penempatan', 'Penempatan'],
];
// Kolom tampilan tabel dan laporan
const KOLOM = [
  'nomor_registrasi' => 'Nomor Registrasi', 'nuptk' => 'NUPTK', 'nama' => 'Nama', 'nip' => 'NIP',
  'jabatan' => 'Jabatan Fungsional', 'pangkat' => 'Kepangkatan', 'pendidikan' => 'Pendidikan Terakhir',
  'rumpun' => 'Rumpun Ilmu', 'status_serdos' => 'Status Serdos', 'status_pegawai' => 'Status Pegawai',
  'ikatan_kerja' => 'Ikatan Kerja', 'status_aktivitas' => 'Status Aktivitas', 'penempatan' => 'Penempatan',
];

function db(): PDO {
  static $pdo = null;
  if (!$pdo) {
    try {
      $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4', DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      ]);
    } catch (PDOException $e) {
      http_response_code(500);
      header('Content-Type: text/plain; charset=utf-8');
      exit('Koneksi database gagal: '.$e->getMessage());
    }
  }
  return $pdo;
}
function h($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function flash(string $type, string $msg): void { $_SESSION['flash'] = [$type, $msg]; }
function redirect(string $url): void { header('Location: '.$url); exit; }

function csrf_token(): string {
  if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
  return $_SESSION['csrf'];
}
function csrf_field(): string { return '<input type="hidden" name="_csrf" value="'.csrf_token().'">'; }
function csrf_check(): void {
  if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['_csrf'] ?? '')) {
    http_response_code(419);
    exit('Token keamanan tidak valid. Muat ulang halaman lalu coba lagi.');
  }
}

function dosen_select(): string {
  return "SELECT d.*, jf.nama AS jabatan, kp.nama AS pangkat, pd.nama AS pendidikan, ri.nama AS rumpun,
    sp.nama AS status_pegawai, ik.nama AS ikatan_kerja, sa.nama AS status_aktivitas, pn.nama AS penempatan
    FROM dosen d
    LEFT JOIN jabatan_fungsional jf ON jf.id = d.jabatan_id
    LEFT JOIN kepangkatan kp ON kp.id = d.pangkat_id
    LEFT JOIN pendidikan pd ON pd.id = d.pendidikan_id
    LEFT JOIN rumpun_ilmu ri ON ri.id = d.rumpun_id
    LEFT JOIN status_pegawai sp ON sp.id = d.status_pegawai_id
    LEFT JOIN ikatan_kerja ik ON ik.id = d.ikatan_kerja_id
    LEFT JOIN status_aktivitas sa ON sa.id = d.status_aktivitas_id
    LEFT JOIN penempatan pn ON pn.id = d.penempatan_id";
}
function cari_dosen(array $f = []): array {
  $w = []; $p = [];
  if (!empty($f['q'])) {
    $w[] = '(d.nama LIKE :q OR d.nip LIKE :q OR d.nuptk LIKE :q OR d.nomor_registrasi LIKE :q)';
    $p[':q'] = '%'.$f['q'].'%';
  }
  foreach (['penempatan_id', 'status_aktivitas_id', 'pendidikan_id', 'jabatan_id'] as $k) {
    if (!empty($f[$k])) { $w[] = "d.$k = :$k"; $p[":$k"] = (int)$f[$k]; }
  }
  $sql = dosen_select().($w ? ' WHERE '.implode(' AND ', $w) : '').' ORDER BY d.nama';
  $st = db()->prepare($sql);
  $st->execute($p);
  return $st->fetchAll();
}
function dosen_cols(): array {
  return array_merge(['nomor_registrasi', 'nuptk', 'nama', 'nip', 'status_serdos'], array_keys(REFS));
}
function ref_options(): array {
  $o = [];
  foreach (REFS as $col => [$tbl, $label]) $o[$col] = db()->query("SELECT id, nama FROM $tbl ORDER BY id")->fetchAll();
  return $o;
}
