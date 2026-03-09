<?php

/**
 * @property int $vida
 */
trait Acao
{
  public function usarPocao(int $nivel): void
  {
    $curasPorNivel = [10, 30, 50];
    $totalNiveis   = count($curasPorNivel);

    if (!is_numeric($nivel))
      throw new InvalidArgumentException("[Acao][usarPocao] ERRO: valor não numérico: $nivel");

    $cura = ($nivel >= 0 && $nivel < $totalNiveis) ? $curasPorNivel[$nivel] : 0;

    if (isset($this->vida)) $this->vida += $cura;
  }

  public function causarDano(int $dano): void
  {
    if (!is_numeric($dano))
      throw new InvalidArgumentException("[Acao][receberDano] ERRO: valor não numérico: $dano");

    if (isset($this->vida)) $this->vida -= $dano;
  }
}