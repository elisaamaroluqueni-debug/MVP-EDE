<?php require_once __DIR__ . '/../includes/auth.php'; require_once __DIR__ . '/../includes/header.php';
require_login(); $uid = user()['id']; $pdo = db();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $pdo->prepare("UPDATE investments SET status='cancelado' WHERE id=? AND user_id=? AND status IN ('simulado','activo')")->execute([(int)$_POST['cancel'], $uid]);
    flash('Investimento cancelado; o saldo foi reposto.'); go('/user/dashboard.php');
}
$fp = $pdo->prepare('SELECT * FROM financial_profiles WHERE user_id=?'); $fp->execute([$uid]); $fp = $fp->fetch();
$q = $pdo->prepare("SELECT i.*, a.name asset, a.category, b.name broker FROM investments i JOIN assets a ON a.id=i.asset_id JOIN brokers b ON b.id=i.broker_id WHERE i.user_id=? ORDER BY i.created_at DESC"); $q->execute([$uid]); $rows = $q->fetchAll();
$inv = 0; $net = 0; $cur = 0; $n = 0; $byCat = []; $byBr = [];
foreach ($rows as $r) { if ($r['status'] === 'cancelado') continue;
    $n++; $inv += $r['gross']; $net += $r['net']; $cur += $r['current_value'];
    $byCat[$r['category']] = ($byCat[$r['category']] ?? 0) + $r['net']; $byBr[$r['broker']] = ($byBr[$r['broker']] ?? 0) + $r['net']; }
$cap = $fp['capital'] ?? 0; $pl = $cur - $net; $rent = $net > 0 ? $pl / $net * 100 : 0;
head('Visão geral');
if (!$fp) echo '<div class="alert alert-info">Comece por <a href="' . BASE . '/user/financial-profile.php">preencher o perfil financeiro</a>.</div>';
$k = ['Total de poupanças' => kz($fp['savings'] ?? 0), 'Capital disponibilizado' => kz($cap), 'Capital investido' => kz($inv), 'Saldo disponível' => kz($cap - $inv),
      'Valor actual estimado' => kz($cur), 'Lucro/prejuízo estimado' => kz($pl) . ' (' . number_format($rent, 2, ',', '.') . '%)', 'Nº de investimentos' => $n]; ?>
<div class="row g-3 mb-4"><?php foreach ($k as $l => $v): ?><div class="col-md-4 col-xl-3"><div class="card card-kpi"><div class="card-body"><div class="small text-muted"><?= $l ?></div><div class="kpi"><?= $v ?></div></div></div></div><?php endforeach; ?></div>
<?php if ($n): ?><div class="row g-3 mb-4"><div class="col-md-6"><div class="card card-kpi p-3"><canvas id="c1"></canvas></div></div><div class="col-md-6"><div class="card card-kpi p-3"><canvas id="c2"></canvas></div></div></div>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>const lab={imobiliario:'Imóveis',cripto:'Criptomoedas',accoes:'Acções'},bc=<?= json_encode($byCat) ?>,bb=<?= json_encode($byBr) ?>;
new Chart(c1,{type:'doughnut',data:{labels:Object.keys(bc).map(k=>lab[k]),datasets:[{data:Object.values(bc)}]},options:{plugins:{title:{display:true,text:'Por categoria (valor líquido)'}}}});
new Chart(c2,{type:'bar',data:{labels:Object.keys(bb),datasets:[{label:'Kz',data:Object.values(bb)}]},options:{plugins:{title:{display:true,text:'Por corretora (valor líquido)'}}}});</script><?php endif; ?>
<h5>Histórico de operações</h5>
<table class="table bg-white"><tr><th>Data</th><th>Activo</th><th>Corretora</th><th>Bruto</th><th>Taxa</th><th>Líquido</th><th>Valor actual</th><th>Estado</th><th></th></tr>
<?php if (!$rows): ?><tr><td colspan="9" class="text-center text-muted">Ainda sem investimentos.</td></tr><?php endif; foreach ($rows as $r): ?>
<tr><td><?= e($r['created_at']) ?></td><td><?= e($r['asset']) ?></td><td><?= e($r['broker']) ?></td><td><?= kz($r['gross']) ?></td><td><?= kz($r['fee']) ?></td><td><?= kz($r['net']) ?></td><td><?= kz($r['current_value']) ?></td><td><?= e($r['status']) ?></td>
<td><?php if (in_array($r['status'], ['simulado', 'activo'])): ?><form method="post"><?= csrf() ?><input type="hidden" name="cancel" value="<?= $r['id'] ?>"><button class="btn btn-sm btn-outline-danger">Cancelar</button></form><?php endif; ?></td></tr><?php endforeach; ?></table>
<?php foot();
