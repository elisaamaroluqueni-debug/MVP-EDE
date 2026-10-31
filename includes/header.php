<?php
function head($t) {
    $u = user(); $b = BASE; ?>
<!doctype html><html lang="pt"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1"><title><?= e($t) ?> · InvestTrack</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="<?= $b ?>/assets/css/style.css" rel="stylesheet"></head><body>
<nav class="navbar navbar-dark it-nav px-3">
<a class="navbar-brand fw-bold" href="<?= $b ?>/"><i class="bi bi-graph-up-arrow"></i> InvestTrack</a>
<div class="ms-auto text-white small">
<?php if ($u): ?><?= e($u['name']) ?> · <a class="text-white" href="<?= $b ?>/logout.php">Sair</a>
<?php else: ?><a class="text-white me-3" href="<?= $b ?>/login.php">Entrar</a><a class="btn btn-sm btn-light" href="<?= $b ?>/register.php">Criar conta</a><?php endif; ?>
</div></nav><div class="d-flex">
<?php if ($u): ?><aside class="it-side p-3"><div class="nav flex-column">
<a class="nav-link" href="<?= $b ?>/user/dashboard.php"><i class="bi bi-speedometer2"></i> Visão geral</a>
<a class="nav-link" href="<?= $b ?>/user/financial-profile.php"><i class="bi bi-person-vcard"></i> Perfil financeiro e recomendação</a>
<a class="nav-link" href="<?= $b ?>/user/assets.php?cat=imobiliario"><i class="bi bi-building"></i> Imóveis</a>
<a class="nav-link" href="<?= $b ?>/user/assets.php?cat=cripto"><i class="bi bi-currency-bitcoin"></i> Criptomoedas</a>
<a class="nav-link" href="<?= $b ?>/user/assets.php?cat=accoes"><i class="bi bi-bar-chart"></i> Acções</a>
<?php if ($u['role'] === 'admin'): ?><a class="nav-link" href="<?= $b ?>/admin/dashboard.php"><i class="bi bi-shield-lock"></i> Administração</a><?php endif; ?>
</div></aside><?php endif; ?>
<main class="flex-fill p-4">
<?php if (!empty($_SESSION['flash'])): [$m, $ty] = $_SESSION['flash']; unset($_SESSION['flash']); ?>
<div class="alert alert-<?= e($ty) ?>"><?= e($m) ?></div><?php endif; }
function foot() { ?>
<hr><p class="text-muted small">InvestTrack é uma simulação. Todos os dados são fictícios; não são movimentados valores reais nem prometida rentabilidade.</p>
</main></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE ?>/assets/js/app.js"></script></body></html>
<?php }
