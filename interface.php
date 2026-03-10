<?php
require_once "./src/combatentes.php";
require_once "./seletorDeCombatentes.php";
require_once "./arbitro.php";

$seletor = new SeletorDeCombatentes();

[$combatente1, $combatente2] = $seletor->escolherCombatentes();

$arbitro = new Arbitro($combatente1, $combatente2,
  $seletor->nomeCombatente1, $seletor->nomeCombatente2);
  
while (!$arbitro->batalhaEncerrada()) {
  $arbitro->exibirEstadoDaBatalha();
  try {
  $arbitro->executarAcaoDoTurno();
  $arbitro->avancarTurno();
  
  } catch (Exception $e) {
    echo "\nERRO: OPÇÃO DIGITADA INVALIDA.\n";
  }
}

$arbitro->exibirEstadoDaBatalha();
$arbitro->exibirVencedor();

