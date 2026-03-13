<?php 
/**
 * @property int   jogadorDaVez
 * @property int   contadorRodadas
 * @property int   nomeCombatente1
 * @property int   nomeCombatente2
 */

  trait Painel {
    private function painel(array $vivoOuMorto, array $super) {
      [$vivoOuMorto1, $vivoOuMorto2] = $vivoOuMorto;
      [$super1, $super2] = $super;
      if(is_numeric($super1)) $super1 *= 10;
      if(is_numeric($super2)) $super2 *= 10;

      echo "\033[s";
      echo "\033[H";
      echo "\033[2K" . "───────────────────────────────────────────────────\n";
      echo "\033[2K" . "Rodada {$this->contadorRodadas} | Vez do Jogador {$this->jogadorDaVez}\n";
      echo "\033[2K" . "Jogador 1 [{$this->nomeCombatente1}] HP: $vivoOuMorto1 super: $super1\n";
      echo "\033[2K" . "Jogador 2 [{$this->nomeCombatente2}] HP: $vivoOuMorto2 super: $super2\n";
      echo "\033[2K" . "───────────────────────────────────────────────────\n";
      echo "\033[u";
    }
  }
