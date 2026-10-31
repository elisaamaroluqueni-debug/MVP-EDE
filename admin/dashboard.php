<?php require_once __DIR__ . '/../includes/auth.php'; require_once __DIR__ . '/../includes/header.php';
require_admin(); $pdo = db();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf(); $a = $_POST['action'] ?? '';
    if ($a === 'broker') $pdo->prepare('UPDATE brokers SET fee_percent=?, min_investment=?, active=? WHERE id=?')->execute([max(0, (float)$_POST['fee']), max(0, (float)$_POST['min']), isset($_POST['active']) ? 1 : 0, (int)$_POST['id']]);
    elseif ($a === 'toggle') $pdo->prepare('UPDATE assets SET active=1-active WHERE id=?')->execute([(int)$_POST['id']]);
    elseif ($a === 'asset' && in_array($_POST['category'], ['imobiliario', 'cripto', 'accoes']) && trim($_POST['name']) !== '')
        $pdo->prepare('INSERT INTO assets(category,name,symbol,description,location,ref_price,variation,min_amount) VALUES(?,?,?,?,?,?,?,?)')->execute([$_POST['category'], trim($_POST['name']), trim($_POST['symbol']) ?: null, trim($_POST['description']), trim($_POST['location']) ?: null, (float)$_POST['ref_price'], (float)$_POST['variation'], (float)$_POST['min_amount']]);
    elseif ($a === 'inv' && in_array($_POST['status'], ['simulado', 'activo', 'concluido', 'cancelado']))
        $pdo->prepare('UPDATE investments SET current_value=?, status=? WHERE id=?')->execute([max(0, (float)$_POST['value']), $_POST['status'], (int)$_POST['id']]);
    flash('Alteração guardada.'); go('/admin/dashboard.php');
}
$st = $pdo->query("SELECT (SELECT COUNT(*) FROM users WHERE role='user') u, (SELECT COUNT(*) FROM investments WHERE status<>'cancelado') i, (SELECT COALESCE(SUM(gross),0) FROM investments WHERE status<>'cancelado') v")->fetch();
head('Administração'); ?>
<h3>Painel administrativo</h3>
<div class="row g-3 mb-4"><?php foreach (['Utilizadores' => $st['u'], 'Investimentos' => $st['i'], 'Volume simulado' => kz($st['v'])] as $l => $v): ?><div class="col-md-4"><div class="card card-kpi p-3"><div class="small text-muted"><?= $l ?></div><div class="kpi"><?= $v ?></div></div></div><?php endforeach; ?></div>
<h5>Corretoras (taxas e mínimos)</h5><table class="table bg-white table-sm"><?php foreach ($pdo->query('SELECT * FROM brokers ORDER BY category,name') as $b): ?>
<tr><form method="post"><?= csrf() ?><input type="hidden" name="action" value="broker"><input type="hidden" name="id" value="<?= $b['id'] ?>">
<td><?= e($b['name']) ?> <small class="text-muted">(<?= e($b['category']) ?>)</small></td><td><input name="fee" class="form-control form-control-sm" value="<?= e($b['fee_percent']) ?>"></td><td><input name="min" class="form-control form-control-sm" value="<?= e($b['min_investment']) ?>"></td>
<td><input type="checkbox" name="active" <?= $b['active'] ? 'checked' : '' ?>> activa</td><td><button class="btn btn-sm btn-primary">Guardar</button></td></form></tr><?php endforeach; ?></table>
<h5>Activos</h5><table class="table bg-white table-sm"><?php foreach ($pdo->query('SELECT * FROM assets ORDER BY category,name') as $a): ?>
<tr><td><?= e($a['name']) ?></td><td><?= e($a['category']) ?></td><td><?= kz($a['ref_price']) ?></td><td><?= $a['active'] ? 'activo' : 'inactivo' ?></td>
<td><form method="post"><?= csrf() ?><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= $a['id'] ?>"><button class="btn btn-sm btn-outline-secondary">Activar/desactivar</button></form></td></tr><?php endforeach; ?></table>
<form method="post" class="row g-2 mb-4"><?= csrf() ?><input type="hidden" name="action" value="asset">
<div class="col-md-2"><select name="category" class="form-select"><option value="imobiliario">Imóvel</option><option value="cripto">Cripto</option><option value="accoes">Acção</option></select></div>
<div class="col-md-2"><input name="name" class="form-control" placeholder="Nome" required></div><div class="col-md-1"><input name="symbol" class="form-control" placeholder="Símbolo"></div>
<div class="col-md-2"><input name="description" class="form-control" placeholder="Descrição"></div><div class="col-md-2"><input name="location" class="form-control" placeholder="Localização"></div>
<div class="col-md-1"><input name="ref_price" class="form-control" placeholder="Preço" required></div><div class="col-md-1"><input name="variation" class="form-control" placeholder="Var.%" value="0"></div>
<div class="col-md-1"><input name="min_amount" class="form-control" placeholder="Mín." required></div><div class="col-12"><button class="btn btn-primary btn-sm">Adicionar activo</button></div></form>
<h5>Investimentos simulados (actualizar valor actual demonstrativo e estado)</h5><table class="table bg-white table-sm">
<?php foreach ($pdo->query('SELECT i.*, u.name uname, a.name aname, b.name bname FROM investments i JOIN users u ON u.id=i.user_id JOIN assets a ON a.id=i.asset_id JOIN brokers b ON b.id=i.broker_id ORDER BY i.created_at DESC LIMIT 100') as $i): ?>
<tr><form method="post"><?= csrf() ?><input type="hidden" name="action" value="inv"><input type="hidden" name="id" value="<?= $i['id'] ?>">
<td><?= e($i['uname']) ?></td><td><?= e($i['aname']) ?> · <?= e($i['bname']) ?></td><td><?= kz($i['net']) ?></td><td><input name="value" class="form-control form-control-sm" value="<?= e($i['current_value']) ?>"></td>
<td><select name="status" class="form-select form-select-sm"><?php foreach (['simulado', 'activo', 'concluido', 'cancelado'] as $s): ?><option <?= $i['status'] === $s ? 'selected' : '' ?>><?= $s ?></option><?php endforeach; ?></select></td><td><button class="btn btn-sm btn-primary">Guardar</button></td></form></tr><?php endforeach; ?></table>
<h5>Utilizadores</h5><table class="table bg-white table-sm"><?php foreach ($pdo->query("SELECT name,email,phone,created_at FROM users WHERE role='user' ORDER BY created_at DESC") as $u): ?>
<tr><td><?= e($u['name']) ?></td><td><?= e($u['email']) ?></td><td><?= e($u['phone']) ?></td><td><?= e($u['created_at']) ?></td></tr><?php endforeach; ?></table>
<?php foot();
