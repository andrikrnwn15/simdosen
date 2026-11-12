<?php
// Autentikasi dan RBAC (Role-Based Access Control)
require_once __DIR__.'/koneksi.php';

const PERMISSIONS = [
  'admin'    => ['dashboard', 'dosen.read', 'dosen.create', 'dosen.update', 'dosen.delete', 'master.manage', 'users.manage', 'laporan.read'],
  'operator' => ['dashboard', 'dosen.read', 'dosen.create', 'dosen.update', 'master.manage', 'laporan.read'],
  'pimpinan' => ['dashboard', 'dosen.read', 'laporan.read'],
];

function current_user(): ?array { return $_SESSION['user'] ?? null; }
function role_can(string $role, string $perm): bool { return in_array($perm, PERMISSIONS[$role] ?? [], true); }
function can(string $perm): bool { $u = current_user(); return $u !== null && role_can($u['role'], $perm); }

function require_login(): void {
  if (!current_user()) { flash('warning', 'Silakan login terlebih dahulu.'); redirect('login.php'); }
}
function require_perm(string $perm): void {
  require_login();
  if (!can($perm)) {
    http_response_code(403);
    exit('<!DOCTYPE html><meta charset="utf-8"><div style="font-family:sans-serif;text-align:center;margin-top:15vh"><h1>403</h1><p>Anda tidak memiliki hak akses ke halaman ini.</p><a href="dashboard.php">Kembali ke Dashboard</a></div>');
  }
}
function login_user(string $username, string $password): bool {
  $st = db()->prepare('SELECT u.id, u.username, u.nama, u.password, r.nama AS role FROM users u JOIN roles r ON r.id = u.role_id WHERE u.username = ?');
  $st->execute([$username]);
  $u = $st->fetch();
  if ($u && password_verify($password, $u['password'])) {
    session_regenerate_id(true);
    $_SESSION['user'] = ['id' => $u['id'], 'username' => $u['username'], 'nama' => $u['nama'], 'role' => $u['role']];
    return true;
  }
  return false;
}
