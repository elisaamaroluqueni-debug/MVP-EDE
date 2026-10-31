<?php require_once __DIR__ . '/includes/auth.php'; require_once __DIR__ . '/includes/header.php'; head('Início'); ?>
<div class="text-center py-5"><h1 class="display-5 fw-bold" style="color:#0b3d5c"><i class="bi bi-graph-up-arrow"></i> InvestTrack</h1>
<p class="lead">Simule como distribuir as suas poupanças entre imóveis, criptomoedas e acções, e compare corretoras.</p>
<a class="btn btn-primary btn-lg me-2" href="<?= BASE ?>/register.php">Criar conta</a><a class="btn btn-outline-primary btn-lg" href="<?= BASE ?>/login.php">Entrar</a></div>
<div class="row g-3 text-center">
<?php foreach ([['building', 'Imóveis', 'Oportunidades imobiliárias fictícias.'], ['currency-bitcoin', 'Criptomoedas', 'Activos digitais com dados demonstrativos.'], ['bar-chart', 'Acções', 'Empresas fictícias para simulação.']] as $c): ?>
<div class="col-md-4"><div class="card card-kpi p-4"><i class="bi bi-<?= $c[0] ?> fs-1 text-primary"></i><h5><?= $c[1] ?></h5><p class="mb-0"><?= $c[2] ?></p></div></div>
<?php endforeach; ?></div>
<h4 class="mt-5">Como funciona</h4><ol><li>Crie conta e indique poupanças, capital e perfil de risco.</li><li>Receba uma proposta de distribuição.</li><li>Escolha um activo e uma corretora, compare taxas.</li><li>Confirme a simulação e acompanhe a carteira.</li></ol>
<?php foot();
