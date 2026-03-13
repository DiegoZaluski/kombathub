<?php
require_once "./essencial/combatentes.php";
require_once "./seletorDeCombatentes.php";
require_once "./essencial/arbitro.php";

$seletor = new SeletorDeCombatentes();

[$combatente1, $combatente2] = $seletor->escolherCombatentes();

$arbitro = new Arbitro($combatente1, $combatente2,
  $seletor->nomeCombatente1, $seletor->nomeCombatente2);

while (!$arbitro->batalhaEncerrada()) {
  $arbitro->exibirEstadoDaBatalha();
  $arbitro->executarAcaoDoTurno();
  $arbitro->avancarTurno();
}

$arbitro->exibirEstadoDaBatalha();
$arbitro->exibirVencedor();

