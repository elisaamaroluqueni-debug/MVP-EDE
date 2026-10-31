<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
session_start();
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function kz($v) { return number_format((float)$v, 2, ',', '.') . ' Kz'; }
function csrf() {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return '<input type="hidden" name="csrf" value="' . $_SESSION['csrf'] . '">';
}
function check_csrf() {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) { http_response_code(400); die('Pedido inválido (CSRF).'); }
}
function user() { return $_SESSION['user'] ?? null; }
function require_login() { if (!user()) { header('Location: ' . BASE . '/login.php'); exit; } }
function require_admin() {
    require_login();
    if (user()['role'] !== 'admin') { http_response_code(403); die('Acesso negado.'); }
}
function flash($m, $t = 'success') { $_SESSION['flash'] = [$m, $t]; }
function go($url) { header('Location: ' . BASE . $url); exit; }
