<?php

/**
 * @property object $jogador1
 * @property object $jogador2
 * @property int    $jogadorDaVez
 * @property int    $contadorRodadas
 * @property bool[] $especiaisDisponiveisJogador1
 * @property bool[] $especiaisDisponiveisJogador2
 * @property bool   $especialUnicoJogador1Disponivel
 * @property bool   $especialUnicoJogador2Disponivel
 *
 * @method array{0: object, 1: object} resolverAtacanteEDefensor()
 */
trait ArbitroTurno
{
  /**
   * Avança para o próximo turno: verifica morte súbita, troca jogador e libera especiais.
   */
  public function avancarTurno(): void
  {
    $this->mortePorTurno();
    $this->morteSubita();
    $this->jogadorDaVez = $this->jogadorDaVez === 1 ? 2 : 1;
    $this->contadorRodadas++;
    $this->liberarEspeciaisPorRodada();
  }

  /**
   * Libera especiais periódicos e o especial único baseado na rodada atual.
   */
  public function liberarEspeciaisPorRodada(): void
  {
    $rodada = $this->contadorRodadas % ArbitroConstantes::MAX_RODADAS_ESPECIAL;

    match ($rodada) {
      ArbitroConstantes::RODADA_LIBERA_ESPECIAL_1 => $this->liberarEspecialNoIndice(0),
      ArbitroConstantes::RODADA_LIBERA_ESPECIAL_2 => $this->liberarEspecialNoIndice(1),
      default                        => null,
    };

    $this->liberarEspecialUnico();
  }

  /**
   * Ativa o especial de índice dado para ambos os jogadores.
   *
   * @param int $indice Índice do especial (0 ou 1)
   */
  private function liberarEspecialNoIndice(int $indice): void
  {
    $this->especiaisDisponiveisJogador1[$indice] = true;
    $this->especiaisDisponiveisJogador2[$indice] = true;
  }

  /**
   * Tenta liberar o especial único (índice 2) para o jogador da vez,
   * caso ele esteja em desespero (vida <= 100) e barra cheia.
   */
  private function liberarEspecialUnico(): void
  {
    [$jogador, $_] = $this->resolverAtacanteEDefensor();

    if ($this->jogadorDaVez === 1) {
      $this->podeUsarEspecialUnico($this->especialUnicoJogador1Disponivel, $jogador, 1);
    } else {
      $this->podeUsarEspecialUnico($this->especialUnicoJogador2Disponivel, $jogador, 2);
    }
  }

  /**
   * Libera o especial único do jogador se as condições forem atendidas.
   *
   * @param bool   $especialDisponivel Flag se o especial único ainda está disponível para uso
   * @param object $jogador            Instância do jogador
   * @param int    $numJogador         Número do jogador (1 ou 2)
   */
  private function podeUsarEspecialUnico(bool $especialDisponivel, object $jogador, int $numJogador): void
  {
    if (!$especialDisponivel || $jogador->vida > 100 || $jogador->barraDeEspecial !== 10)
      return;

    if ($numJogador === 1) {
      if ($this->especiaisDisponiveisJogador1[2]) return;
      $this->especiaisDisponiveisJogador1[2] = true;
      $this->especialUnicoJogador1Disponivel = false;
      return;
    }

    if ($this->especiaisDisponiveisJogador2[2]) return;
    $this->especiaisDisponiveisJogador2[2] = true;
    $this->especialUnicoJogador2Disponivel = false;
  }

  /**
   * Mata jogadores que abusaram de poções (vida >= 300).
   */
  private function morteSubita(): void
  {
    $v1 = $this->jogador1->vida;
    $v2 = $this->jogador2->vida;
    $mensagem = "A ganância leva o homem à destruição.\n";

    if ($v1 >= 300 && $v2 >= 300) {
      echo "[AMBOS SE ENVENENARAM COM EXCESSO DE POÇÃO]\n" . $mensagem;
      return;
    }

    if ($v1 >= 300) {
      echo $mensagem;
      $this->jogador1->vida = 0;
    }

    if ($v2 >= 300) {
      echo $mensagem;
      $this->jogador2->vida = 0;
    }
  }
  private function mortePorTurno() {
    if($this->contadorRodadas > 99) {
      $this->jogador1->vida = 0;
      $this->jogador2->vida = 0;
    }
  }
}