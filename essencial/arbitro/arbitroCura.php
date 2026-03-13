<?php

/**
 * @property int    $jogadorDaVez
 * @property int[]  $pocoesDisponiveisJogador1
 * @property int[]  $pocoesDisponiveisJogador2
 * @property int[]  $pocaosJaUsadasJogador1
 * @property int[]  $pocaosJaUsadasJogador2
 *
 * @method array{0: object, 1: int[]} resolverJogadorEPocoesDoTurno()
 */
trait ArbitroCura
{
  abstract protected function tentativaEErro(callable $fn, string $mensagem, string $erro, bool $validar, int $max, array $proibidos = []): mixed;
  /**
   * Gerencia o uso de poção do jogador da vez.
   *
   * @throws \Exception Se a poção escolhida for inválida ou indisponível
   */
  public function curar(): void
  {
    [$jogador, $pocoesDisponiveis] = $this->resolverJogadorEPocoesDoTurno();
    $pocoesFiltradas = array_filter($pocoesDisponiveis);

    if (empty($pocoesFiltradas)) {
      echo "Nenhuma poção disponível.\n";
      return;
    }

    $this->exibirPocoesDisponiveis($pocoesFiltradas);

    $nivelEscolhido = (int) $this->tentativaEErro(
      readline(...),
      "Digite o nível da poção: ",
      "[curar] ERRO: POÇÃO NÃO DISPONIVEL.",
      true,
      3,
      $this->pocaosJaUsadas()
    );

    if (!in_array($nivelEscolhido, $pocoesFiltradas))
      throw new \Exception("[Arbitro] Poção inválida ou indisponível: $nivelEscolhido");

    $this->registrarPocaoUsada($nivelEscolhido);
    $jogador->usarPocao($nivelEscolhido - 1, ArbitroConstantes::VALORES_DE_CURA);
    $this->consumirPocao($nivelEscolhido - 1);
  }

  /**
   * Exibe as poções disponíveis com seus valores de cura.
   *
   * @param array $pocoesFiltradas Array com os níveis de poção disponíveis
   */
  private function exibirPocoesDisponiveis(array $pocoesFiltradas): void
  {
    echo "\nPOÇÕES DISPONÍVEIS\n";
    foreach ($pocoesFiltradas as $nivel) {
      $valor = ArbitroConstantes::VALORES_DE_CURA[$nivel - 1];
      echo "$nivel: Poção nível $nivel [VALOR DE CURA: $valor]\n";
    }
  }

  /**
   * Marca a poção do índice dado como consumida para o jogador da vez.
   *
   * @param int $indice Índice da poção (nivel - 1)
   */
  private function consumirPocao(int $indice): void
  {
    if ($this->jogadorDaVez === 1)
      $this->pocoesDisponiveisJogador1[$indice] = 0;
    else
      $this->pocoesDisponiveisJogador2[$indice] = 0;
  }

  /**
   * Retorna as poções já usadas pelo jogador da vez.
   *
   * @return array
   */
  public function pocaosJaUsadas(): array
  {
    return $this->jogadorDaVez === 1
      ? $this->pocaosJaUsadasJogador1
      : $this->pocaosJaUsadasJogador2;
  }

  /**
   * Registra uma poção como usada pelo jogador da vez.
   *
   * @param int $nivel Nível da poção usada (1, 2 ou 3)
   */
  private function registrarPocaoUsada(int $nivel): void
  {
    if ($this->jogadorDaVez === 1)
      $this->pocaosJaUsadasJogador1[] = $nivel;
    else
      $this->pocaosJaUsadasJogador2[] = $nivel;
  }
}