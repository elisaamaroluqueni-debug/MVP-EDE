<?php require_once __DIR__ . '/../includes/auth.php'; require_once __DIR__ . '/../includes/header.php'; require_once __DIR__ . '/../models/Recommendation.php';
require_login(); $uid = user()['id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $sav = round((float)str_replace(',', '.', $_POST['savings'] ?? 0), 2); $cap = round((float)str_replace(',', '.', $_POST['capital'] ?? 0), 2);
    $goal = $_POST['goal'] ?? ''; $hor = $_POST['horizon'] ?? ''; $risk = $_POST['risk'] ?? '';
    if ($sav <= 0 || $cap <= 0 || $cap > $sav) flash('O capital a investir deve ser positivo e não superior às poupanças.', 'danger');
    elseif (!in_array($goal, ['preservacao', 'crescimento', 'rendimento']) || !in_array($hor, ['curto', 'medio', 'longo']) || !in_array($risk, ['conservador', 'moderado', 'agressivo'])) flash('Valores inválidos.', 'danger');
    else {
        db()->prepare('INSERT INTO financial_profiles(user_id,savings,capital,goal,horizon,risk) VALUES(?,?,?,?,?,?) ON DUPLICATE KEY UPDATE savings=VALUES(savings),capital=VALUES(capital),goal=VALUES(goal),horizon=VALUES(horizon),risk=VALUES(risk)')->execute([$uid, $sav, $cap, $goal, $hor, $risk]);
        flash('Perfil financeiro guardado.'); go('/user/financial-profile.php');
    }
}
$s = db()->prepare('SELECT * FROM financial_profiles WHERE user_id=?'); $s->execute([$uid]); $p = $s->fetch();
head('Perfil financeiro');
function sel($name, $opts, $cur) { echo "<select class='form-select mb-2' name='$name'>"; foreach ($opts as $k => $v) echo "<option value='$k'" . ($cur === $k ? ' selected' : '') . ">$v</option>"; echo '</select>'; } ?>
<div class="row g-4"><div class="col-lg-5"><div class="card card-kpi"><div class="card-body"><h5>Perfil financeiro</h5>
<form method="post"><?= csrf() ?>
<label>Total de poupanças (Kz)</label><input class="form-control mb-2" name="savings" value="<?= e($p['savings'] ?? '') ?>" required>
<label>Capital a investir (Kz)</label><input class="form-control mb-2" name="capital" value="<?= e($p['capital'] ?? '') ?>" required>
<label>Objectivo</label><?php sel('goal', ['preservacao' => 'Preservação de capital', 'crescimento' => 'Crescimento do património', 'rendimento' => 'Rendimento a longo prazo'], $p['goal'] ?? ''); ?>
<label>Horizonte</label><?php sel('horizon', ['curto' => 'Curto (até 1 ano)', 'medio' => 'Médio (1 a 5 anos)', 'longo' => 'Longo (+5 anos)'], $p['horizon'] ?? ''); ?>
<label>Perfil de risco</label><?php sel('risk', ['conservador' => 'Conservador', 'moderado' => 'Moderado', 'agressivo' => 'Agressivo'], $p['risk'] ?? ''); ?>
<button class="btn btn-primary w-100 mt-2">Guardar</button></form></div></div></div>
<div class="col-lg-7"><?php if ($p): $r = recommend($p['risk'], (float)$p['capital'], $p['goal'], $p['horizon']); ?>
<div class="card card-kpi"><div class="card-body"><h5>Proposta de distribuição</h5>
<table class="table"><tr><th>Categoria</th><th>%</th><th>Valor</th><th>Principais riscos</th></tr>
<?php foreach ($r['rows'] as $x): ?><tr><td><?= e($x['name']) ?></td><td><?= $x['pct'] ?>%</td><td><?= kz($x['value']) ?></td><td class="small"><?= e($x['risk']) ?></td></tr><?php endforeach; ?></table>
<p><?= e($r['text']) ?></p>
<a class="btn btn-outline-primary btn-sm" href="<?= BASE ?>/user/assets.php?cat=imobiliario">Consultar oportunidades</a></div></div>
<?php else: ?><div class="alert alert-info">Preencha o perfil para ver a proposta.</div><?php endif; ?></div></div>
<?php foot();
