<?php require_once __DIR__ . '/../includes/auth.php'; require_once __DIR__ . '/../includes/header.php';
require_login();
$cats = ['imobiliario' => 'Imóveis', 'cripto' => 'Criptomoedas', 'accoes' => 'Acções'];
$cat = $_GET['cat'] ?? 'imobiliario'; if (!isset($cats[$cat])) $cat = 'imobiliario';
$uid = user()['id']; $pdo = db();
$fp = $pdo->prepare('SELECT * FROM financial_profiles WHERE user_id=?'); $fp->execute([$uid]); $fp = $fp->fetch();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $aid = (int)($_POST['asset_id'] ?? 0); $bid = (int)($_POST['broker_id'] ?? 0);
    $gross = round((float)str_replace(',', '.', $_POST['amount'] ?? 0), 2);
    try {
        $a = $pdo->prepare('SELECT * FROM assets WHERE id=? AND active=1'); $a->execute([$aid]); $a = $a->fetch();
        $br = $pdo->prepare('SELECT * FROM brokers WHERE id=? AND active=1'); $br->execute([$bid]); $br = $br->fetch();
        if (!$fp) throw new Exception('Preencha primeiro o perfil financeiro.');
        if (!$a || !$br || $a['category'] !== $br['category']) throw new Exception('Activo ou corretora inválidos.');
        if ($gross <= 0) throw new Exception('Introduza um montante positivo.');
        if ($gross < $br['min_investment']) throw new Exception('Abaixo do mínimo da corretora (' . kz($br['min_investment']) . ').');
        if ($gross < $a['min_amount']) throw new Exception('Abaixo do mínimo do activo (' . kz($a['min_amount']) . '). Escolha outro activo ou aumente o montante.');
        $pdo->beginTransaction();
        $l = $pdo->prepare('SELECT capital FROM financial_profiles WHERE user_id=? FOR UPDATE'); $l->execute([$uid]); $capital = (float)$l->fetchColumn();
        $s = $pdo->prepare("SELECT COALESCE(SUM(gross),0) FROM investments WHERE user_id=? AND status<>'cancelado'"); $s->execute([$uid]); $inv = (float)$s->fetchColumn();
        if ($gross > round($capital - $inv, 2)) throw new Exception('Saldo insuficiente: disponível ' . kz($capital - $inv) . '.');
        $fee = round($gross * $br['fee_percent'] / 100, 2); $net = round($gross - $fee, 2);
        $qty = $a['ref_price'] > 0 ? round($net / $a['ref_price'], 8) : null;
        $pdo->prepare("INSERT INTO investments(user_id,asset_id,broker_id,gross,fee,net,quantity,current_value,status) VALUES(?,?,?,?,?,?,?,?, 'simulado')")->execute([$uid, $aid, $bid, $gross, $fee, $net, $qty, $net]);
        $pdo->commit();
        flash('Investimento simulado registado. Taxa: ' . kz($fee) . ' · Líquido: ' . kz($net)); go('/user/dashboard.php');
    } catch (Exception $x) { if ($pdo->inTransaction()) $pdo->rollBack(); flash($x->getMessage(), 'danger'); }
}
$sql = 'SELECT * FROM assets WHERE active=1 AND category=?'; $par = [$cat];
$maxp = trim($_GET['max_price'] ?? ''); $loc = trim($_GET['loc'] ?? '');
if ($maxp !== '' && is_numeric($maxp)) { $sql .= ' AND ref_price<=?'; $par[] = $maxp; }
if ($loc !== '' && $cat === 'imobiliario') { $sql .= ' AND location LIKE ?'; $par[] = "%$loc%"; }
$as = $pdo->prepare($sql . ' ORDER BY name'); $as->execute($par); $as = $as->fetchAll();
$bs = $pdo->prepare('SELECT * FROM brokers WHERE active=1 AND category=? ORDER BY fee_percent'); $bs->execute([$cat]); $bs = $bs->fetchAll();
$bal = null;
if ($fp) { $s = $pdo->prepare("SELECT COALESCE(SUM(gross),0) FROM investments WHERE user_id=? AND status<>'cancelado'"); $s->execute([$uid]); $bal = $fp['capital'] - $s->fetchColumn(); }
head($cats[$cat]); ?>
<h3><?= $cats[$cat] ?></h3>
<div class="alert alert-warning py-2 small">Dados fictícios e demonstrativos, não são cotações reais. <?= $bal !== null ? 'Saldo disponível: <b>' . kz($bal) . '</b>' : 'Preencha o perfil financeiro para investir.' ?></div>
<h6>Corretoras disponíveis (comparação)</h6>
<table class="table table-sm bg-white mb-4"><tr><th>Corretora</th><th>Taxa</th><th>Mínimo</th><th>Descrição</th></tr>
<?php foreach ($bs as $b): ?><tr><td><?= e($b['name']) ?></td><td><?= e($b['fee_percent']) ?>%</td><td><?= kz($b['min_investment']) ?></td><td class="small"><?= e($b['description']) ?></td></tr><?php endforeach; ?></table>
<form class="row g-2 mb-3" method="get"><input type="hidden" name="cat" value="<?= e($cat) ?>">
<div class="col-auto"><input class="form-control" name="max_price" placeholder="Preço máximo" value="<?= e($maxp) ?>"></div>
<?php if ($cat === 'imobiliario'): ?><div class="col-auto"><input class="form-control" name="loc" placeholder="Localização" value="<?= e($loc) ?>"></div><?php endif; ?>
<div class="col-auto"><button class="btn btn-outline-primary">Filtrar</button></div></form>
<div class="row g-3"><?php if (!$as): ?><p>Sem resultados.</p><?php endif; foreach ($as as $a): ?>
<div class="col-lg-4"><div class="card card-kpi h-100"><div class="card-body">
<h5><?= e($a['name']) ?> <?= $a['symbol'] ? '<small class="text-muted">' . e($a['symbol']) . '</small>' : '' ?></h5>
<p class="small"><?= e($a['description']) ?></p>
<?php if ($a['location']): ?><p class="small mb-1"><i class="bi bi-geo-alt"></i> <?= e($a['location']) ?></p><?php endif; ?>
<p class="mb-1">Preço de referência: <b><?= kz($a['ref_price']) ?></b> · Variação demo: <?= e($a['variation']) ?>%</p>
<p class="small">Mínimo de aplicação: <?= kz($a['min_amount']) ?></p>
<form method="post" class="invest"><?= csrf() ?><input type="hidden" name="asset_id" value="<?= $a['id'] ?>">
<select name="broker_id" class="form-select mb-2"><?php foreach ($bs as $b): ?><option value="<?= $b['id'] ?>" data-fee="<?= e($b['fee_percent']) ?>"><?= e($b['name']) ?> (<?= e($b['fee_percent']) ?>%)</option><?php endforeach; ?></select>
<input name="amount" class="form-control mb-1" placeholder="Montante (Kz)" required>
<div class="calc small text-muted mb-2"></div><button class="btn btn-primary btn-sm w-100">Confirmar simulação</button></form>
</div></div></div><?php endforeach; ?></div>
<?php foot();
