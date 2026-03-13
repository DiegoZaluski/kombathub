<?php

/**
 * @property object $jogador1
 * @property object $jogador2
 * @property int    $jogadorDaVez
 * @property bool[] $especiaisDisponiveisJogador1
 * @property bool[] $especiaisDisponiveisJogador2
 *
 * @method array{0: object, 1: object} resolverAtacanteEDefensor()
 * @method array{0: bool[], 1: string[]} resolverEspeciaisDoAtacante()
 */
trait ArbitroAtaque
{
  abstract protected function tentativaEErro(callable $fn, string $mensagem, string $erro, bool $validar, int $max, array $proibidos = []): mixed;
  /**
   * Solicita e executa o ataque do jogador da vez.
   */
  public function atacar(): void
  {
    [$atacante, $defensor]                         = $this->resolverAtacanteEDefensor();
    [$especiaisDisponiveis, $nomesEspeciais]       = $this->resolverEspeciaisDoAtacante();
    [$naoPermitidosEspeciais, $permitidosEspeciais] = $this->exibirOpcoesDeAtaque($atacante, $especiaisDisponiveis, $nomesEspeciais);

    $limiteOpcoes = $this->calcularLimiteOpcoes($permitidosEspeciais);

    $escolha = (int) $this->tentativaEErro(
      readline(...),
      "Escolha um ataque: ",
      "ERRO: OPÇÃO DE ATAQUE INVALIDO.",
      true,
      $limiteOpcoes,
      $naoPermitidosEspeciais
    ) - 1;

    $ataquesBasicos = array_keys($atacante->ataquesBasicos);
    $totalBasicos   = count($ataquesBasicos);

    if ($escolha < $totalBasicos) {
      $this->executarAtaqueBasico($atacante, $defensor, $ataquesBasicos, $escolha);
      return;
    }

    $indiceEspecial = $escolha - $totalBasicos;
    $this->executarAtaqueEspecial($atacante, $defensor, $nomesEspeciais, $especiaisDisponiveis, $indiceEspecial);
  }

  /**
   * Ativa a postura defensiva do jogador da vez.
   */
  public function defender(): void
  {
    $jogador = $this->jogadorDaVez === 1 ? $this->jogador1 : $this->jogador2;
    $jogador->ativarDefesa();
    echo "Jogador {$this->jogadorDaVez} está em postura defensiva!\n";
  }

  /**
   * Calcula o número máximo de opções válidas para o readline.
   *
   * @param array $permitidosEspeciais Índices dos especiais permitidos
   *
   * @return int
   */
  private function calcularLimiteOpcoes(array $permitidosEspeciais): int
  {
    if (empty($permitidosEspeciais)) return 3;
    return (int) max($permitidosEspeciais);
  }

  /**
   * Executa um ataque básico, incrementando barra especial se necessário.
   *
   * @param object $atacante      Instância do atacante
   * @param object $defensor      Instância do defensor
   * @param array  $ataquesBasicos Lista de nomes dos ataques básicos
   * @param int    $escolha       Índice do ataque escolhido (0-based)
   */
  private function executarAtaqueBasico(object $atacante, object $defensor, array $ataquesBasicos, int $escolha): void
  {
    if ($escolha === 1 || $escolha === 2)
      $atacante->barraDeEspecial++;

    $nomeAtaque = $ataquesBasicos[$escolha];
    $defensor->causarDano($atacante->ataquesTotal[$nomeAtaque], "[atacar] ERRO: VALOR INVALIDO");
  }

  /**
   * Executa um ataque especial após validar disponibilidade.
   *
   * @param object $atacante             Instância do atacante
   * @param object $defensor             Instância do defensor
   * @param array  $nomesEspeciais       Lista de nomes dos especiais
   * @param array  $especiaisDisponiveis Array de booleans de disponibilidade
   * @param int    $indice               Índice do especial (0-based após os básicos)
   *
   * @throws \Exception Se o especial for inválido ou indisponível
   */
  private function executarAtaqueEspecial(object $atacante, object $defensor, array $nomesEspeciais, array $especiaisDisponiveis, int $indice): void
  {
    if (!isset($nomesEspeciais[$indice]) || !$especiaisDisponiveis[$indice])
      throw new \Exception("[Arbitro] Ataque especial inválido ou indisponível.");

    $defensor->causarDano($atacante->ataquesTotal[$nomesEspeciais[$indice]]);
    $this->consumirEspecial($indice);
  }

  /**
   * Exibe as opções de ataque e retorna índices proibidos/permitidos dos especiais.
   *
   * @param object $atacante             Instância do atacante
   * @param array  $especiaisDisponiveis Array de booleans de disponibilidade
   * @param array  $nomesEspeciais       Lista de nomes dos especiais
   *
   * @return array{0: array|null, 1: array|null}
   */
  private function exibirOpcoesDeAtaque(object $atacante, array $especiaisDisponiveis, array $nomesEspeciais): array
  {
    echo "\nATAQUES BÁSICOS\n";
    foreach (array_keys($atacante->ataquesBasicos) as $i => $nome) {
      $dano = $atacante->ataquesBasicos[$nome];
      echo ($i + 1) . ": $nome [DANO: $dano]\n";
    }

    $totalBasicos     = count($atacante->ataquesBasicos);
    $indicesPermitidos = [];

    foreach ($nomesEspeciais as $i => $nome) {
      if (!$especiaisDisponiveis[$i]) continue;

      $dano       = $atacante->ataquesTotal[$nome];
      $exibicao   = $i + $totalBasicos + 1;
      $indicesPermitidos[] = $exibicao;

      echo "$exibicao: [ESPECIAL] $nome [DANO: $dano]\n";
    }

    if (empty($indicesPermitidos)) return [[], []];

    $naoPermitidos = array_diff(ArbitroConstantes::ESPECIAS_POSSIVEIS_INDICES, $indicesPermitidos);
    $permitidos    = array_intersect(ArbitroConstantes::ESPECIAS_POSSIVEIS_INDICES, $indicesPermitidos);

    return [$naoPermitidos, $permitidos];
  }

  /**
   * Marca o especial do índice dado como consumido para o jogador da vez.
   *
   * @param int $indice Índice do especial consumido
   */
  private function consumirEspecial(int $indice): void
  {
    if ($this->jogadorDaVez === 1)
      $this->especiaisDisponiveisJogador1[$indice] = false;
    else
      $this->especiaisDisponiveisJogador2[$indice] = false;
  }
}