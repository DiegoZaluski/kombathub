<?php

/**
 * @property array $ataques
 */
trait Memorizar
{
  static array $poderesSalvos = [];

  public function memorizarPoder(string $poder): void
  {
    if (isset($this->ataques[$poder]))
      self::$poderesSalvos[$poder] = $this->ataques[$poder];
  }

  public function esquecerPoder(string $poder): void
  {
    if (!isset($this->ataques[$poder]))
      throw new InvalidArgumentException("[Memorizar][esquecerPoder] ERRO: poder nao encontrado: $poder");

    if (isset(self::$poderesSalvos[$poder]))
      unset(self::$poderesSalvos[$poder]);
  }
}