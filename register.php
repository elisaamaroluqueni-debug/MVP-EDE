<?php require_once __DIR__ . '/includes/auth.php'; require_once __DIR__ . '/includes/header.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $n = trim($_POST['name'] ?? ''); $em = strtolower(trim($_POST['email'] ?? '')); $ph = trim($_POST['phone'] ?? '');
    $pw = $_POST['password'] ?? ''; $pw2 = $_POST['password2'] ?? '';
    if ($n === '' || !filter_var($em, FILTER_VALIDATE_EMAIL) || $ph === '') flash('Preencha nome, e-mail válido e telefone.', 'danger');
    elseif (strlen($pw) < 8 || $pw !== $pw2) flash('A palavra-passe deve ter 8+ caracteres e coincidir com a confirmação.', 'danger');
    else {
        $s = db()->prepare('SELECT id FROM users WHERE email=?'); $s->execute([$em]);
        if ($s->fetch()) flash('Este e-mail já está registado.', 'danger');
        else {
            db()->prepare('INSERT INTO users(name,email,phone,password_hash) VALUES(?,?,?,?)')->execute([$n, $em, $ph, password_hash($pw, PASSWORD_DEFAULT)]);
            session_regenerate_id(true);
            $_SESSION['user'] = ['id' => (int)db()->lastInsertId(), 'name' => $n, 'role' => 'user'];
            go('/user/financial-profile.php');
        }
    }
}
head('Registo'); ?>
<div class="card card-kpi mx-auto" style="max-width:480px"><div class="card-body">
<h4>Criar conta</h4><form method="post"><?= csrf() ?>
<input class="form-control mb-2" name="name" placeholder="Nome completo" required>
<input class="form-control mb-2" type="email" name="email" placeholder="E-mail" required>
<input class="form-control mb-2" name="phone" placeholder="Telefone" required>
<input class="form-control mb-2" type="password" name="password" placeholder="Palavra-passe (mín. 8)" required>
<input class="form-control mb-3" type="password" name="password2" placeholder="Confirmar palavra-passe" required>
<button class="btn btn-primary w-100">Registar</button></form></div></div>
<?php foot();
