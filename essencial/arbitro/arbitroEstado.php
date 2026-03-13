<?php

/**
 * @property object      $jogador1
 * @property object      $jogador2
 * @property string|null $nomeCombatente1
 * @property string|null $nomeCombatente2
 * @property bool[]      $especiaisDisponiveisJogador1
 * @property bool[]      $especiaisDisponiveisJogador2
 */
trait ArbitroEstado
{
  /**
   * Retorna status da barra de super especial de cada jogador.
   *
   * @return array{0: int|string, 1: int|string}
   */
  public function disponivelSuperEspecial(): array
  {
    return [
      $this->statusSuperEspecial($this->jogador1, $this->especiaisDisponiveisJogador1),
      $this->statusSuperEspecial($this->jogador2, $this->especiaisDisponiveisJogador2),
    ];
  }

  /**
   * @param object $jogador             Instância do jogador
   * @param bool[] $especiaisDisponiveis Array de booleans dos especiais do jogador
   *
   * @return int|string
   */
  private function statusSuperEspecial(object $jogador, array $especiaisDisponiveis): int|string
  {
    if ($jogador->barraDeEspecial !== 10)
      return $jogador->barraDeEspecial;

    if ($especiaisDisponiveis[2] && $jogador->vida <= 100)
      return "[DISPONIVEL]";

    if (!$especiaisDisponiveis[2] && $jogador->vida > 100)
      return "[ESPERANDO O DESESPERO🗡️💥]";

    return "[INDISPONIVEL]";
  }

  /**
   * Retorna true se a batalha acabou (algum jogador com vida <= 0).
   */
  public function batalhaEncerrada(): bool
  {
    return $this->jogador1->vida <= 0 || $this->jogador2->vida <= 0;
  }

  /**
   * Exibe o nome do vencedor ou empate.
   */
  public function exibirVencedor(): void
  {
    if ($this->jogador1->vida <= 0 && $this->jogador2->vida <= 0) {
      echo "Empate! Ambos os combatentes caíram.\n";
      return;
    }

    $vencedor = $this->jogador1->vida > 0 ? $this->nomeCombatente1 : $this->nomeCombatente2;
    echo "Vencedor: $vencedor!\n";
  }
}