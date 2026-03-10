<?php
/**
 * @property int $vida
 */
trait Acao 
{
  public function usarPocao(int $nivel): void {
    $curasPorNivel = [10, 30, 50];
    $nivelValido   = $nivel >= 0 && $nivel < count($curasPorNivel);
    $cura          = $nivelValido ? $curasPorNivel[$nivel] : 0;

    if (isset($this->vida))
      $this->vida += $cura;
  }

  public function causarDano(int $dano): void {
    if (isset($this->vida))
      $this->vida -= $dano;
  }

  public function ativarDefesa():void {
    $this->vida +=2;
  }
}