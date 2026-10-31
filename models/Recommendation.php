<?php
function recommend(string $risk, float $capital, string $goal, string $horizon): array {
    $pct = ['conservador' => [55, 5, 40], 'moderado' => [35, 25, 40], 'agressivo' => [15, 45, 40]][$risk];
    $names = ['Imóveis', 'Criptomoedas', 'Acções'];
    $risks = ['Baixa liquidez e dependência do mercado local.', 'Volatilidade elevada; possibilidade de perda significativa.', 'Oscilações de mercado e risco específico de cada empresa.'];
    $rows = [];
    foreach ($pct as $i => $p) $rows[] = ['name' => $names[$i], 'pct' => $p, 'value' => round($capital * $p / 100, 2), 'risk' => $risks[$i]];
    $why = ['conservador' => 'Perfil conservador: maior peso em activos tendencialmente mais estáveis e exposição mínima a criptomoedas.',
            'moderado' => 'Perfil moderado: equilíbrio entre estabilidade e crescimento, com exposição relevante a criptomoedas.',
            'agressivo' => 'Perfil agressivo: maior exposição a activos voláteis na procura de crescimento, aceitando maior risco de perda.'][$risk];
    $g = ['preservacao' => 'O objectivo de preservação de capital recomenda cautela.', 'crescimento' => 'O objectivo de crescimento aceita mais oscilação.', 'rendimento' => 'O objectivo de rendimento a longo prazo favorece activos que geram retorno recorrente.'][$goal];
    $h = ['curto' => 'Com prazo até 1 ano, há pouco tempo para recuperar de quedas.', 'medio' => 'Com prazo de 1 a 5 anos, há margem moderada para recuperar de oscilações.', 'longo' => 'Com prazo superior a 5 anos, é possível suportar melhor a volatilidade.'][$horizon];
    return ['rows' => $rows, 'text' => "$why $g $h Regras demonstrativas, não constituem aconselhamento financeiro."];
}
