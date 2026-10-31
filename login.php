<?php require_once __DIR__ . '/includes/auth.php'; require_once __DIR__ . '/includes/header.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $s = db()->prepare('SELECT * FROM users WHERE email=?'); $s->execute([strtolower(trim($_POST['email'] ?? ''))]);
    $u = $s->fetch();
    if ($u && password_verify($_POST['password'] ?? '', $u['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = ['id' => (int)$u['id'], 'name' => $u['name'], 'role' => $u['role']];
        go($u['role'] === 'admin' ? '/admin/dashboard.php' : '/user/dashboard.php');
    }
    flash('Credenciais inválidas.', 'danger');
}
head('Entrar'); ?>
<div class="card card-kpi mx-auto" style="max-width:420px"><div class="card-body">
<h4>Entrar</h4><form method="post"><?= csrf() ?>
<input class="form-control mb-2" type="email" name="email" placeholder="E-mail" required>
<input class="form-control mb-3" type="password" name="password" placeholder="Palavra-passe" required>
<button class="btn btn-primary w-100">Entrar</button></form></div></div>
<?php foot();
