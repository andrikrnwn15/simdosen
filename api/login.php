<?php
// POST api/login.php  body JSON: {"username":"admin","password":"admin123"}  -> token
require_once __DIR__.'/../includes/auth.php';
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
function out($d, int $c = 200) { http_response_code($c); echo json_encode($d, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT); exit; }
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') out(null, 204);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') out(['status' => 'error', 'message' => 'Gunakan method POST'], 405);
$b = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$st = db()->prepare('SELECT u.id, u.username, u.nama, u.password, r.nama AS role FROM users u JOIN roles r ON r.id = u.role_id WHERE u.username = ?');
$st->execute([trim($b['username'] ?? '')]);
$u = $st->fetch();
if (!$u || !password_verify($b['password'] ?? '', $u['password'])) out(['status' => 'error', 'message' => 'Username atau password salah'], 401);
$token = bin2hex(random_bytes(24));
db()->prepare('UPDATE users SET api_token = ? WHERE id = ?')->execute([$token, $u['id']]);
out(['status' => 'ok', 'token' => $token, 'role' => $u['role'], 'nama' => $u['nama']]);
