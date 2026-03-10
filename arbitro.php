<?php

class Arbitro
{
  const POCAO_NIVEL_1 = 1;
  const POCAO_NIVEL_2 = 2;
  const POCAO_NIVEL_3 = 3;

  const RODADA_LIBERA_ESPECIAL_1  = 2;
  const RODADA_LIBERA_ESPECIAL_2  = 3;
  const RODADA_LIBERA_POCAO_1     = 2;
  const RODADA_LIBERA_POCAO_2     = 4;
  const RODADA_LIBERA_POCAO_3     = 6;
  const MAX_RODADAS_ESPECIAL      = 4;
  const MAX_RODADAS_POCAO         = 7;

  public $jogador1;
  public $jogador2;
  public string|null $nomeCombatente1 = null;
  public string|null $nomeCombatente2 = null;

  public int $jogadorDaVez = 1;
  private int $contadorRodadas = 0;

  public array $especiaisDisponiveisJogador1 = [false, false, false];
  public array $especiaisDisponiveisJogador2 = [false, false, false];

  public array $pocoesDisponiveisJogador1 = [self::POCAO_NIVEL_1, self::POCAO_NIVEL_2, self::POCAO_NIVEL_3];
  public array $pocoesDisponiveisJogador2 = [self::POCAO_NIVEL_1, self::POCAO_NIVEL_2, self::POCAO_NIVEL_3];

  private bool $especialUnicoJogador1Disponivel = true;
  private bool $especialUnicoJogador2Disponivel = true;

  public function __construct($jogador1, $jogador2, string $nomeCombatente1, string $nomeCombatente2)
  {
    $this->jogador1 = $jogador1;
    $this->jogador2 = $jogador2;
    $this->nomeCombatente1 = $nomeCombatente1;
    $this->nomeCombatente2 = $nomeCombatente2;
  }

  // ─── Controle de turno ───────────────────────────────────────────────────

  public function avancarTurno(): void
  {
    $this->jogadorDaVez = $this->jogadorDaVez === 1 ? 2 : 1;
    $this->contadorRodadas++;
    $this->liberarEspeciaisPorRodada();
    $this->liberarPocoes();
  }

  public function exibirEstadoDaBatalha(): void
  {
    echo "\033[s";
    echo "\033[H";
    echo "\033[2K" . "───────────────────────────────────────────────────\n";
    echo "\033[2K" . "Rodada {$this->contadorRodadas} | Vez do Jogador {$this->jogadorDaVez}\n";
    echo "\033[2K" . "Jogador 1 [{$this->nomeCombatente1}] HP: {$this->jogador1->vida}\n";
    echo "\033[2K" . "Jogador 2 [{$this->nomeCombatente2}] HP: {$this->jogador2->vida}\n";
    echo "\033[2K" . "───────────────────────────────────────────────────\n";
    echo "\033[u";
  }

  public function batalhaEncerrada(): bool
  {
    return $this->jogador1->vida <= 0 || $this->jogador2->vida <= 0;
  }

  public function exibirVencedor(): void
  {
    if ($this->jogador1->vida <= 0 && $this->jogador2->vida <= 0) {
      echo "Empate! Ambos os combatentes caíram.\n";
      return;
    }

    $vencedor = $this->jogador1->vida > 0 ? $this->nomeCombatente1 : $this->nomeCombatente2;
    echo "Vencedor: $vencedor!\n";
  }

  // ─── Ações do turno ──────────────────────────────────────────────────────

  public function executarAcaoDoTurno(): void
  {
    echo "\nO que deseja fazer?\n";
    echo "1: Atacar\n";
    echo "2: Usar poção\n";
    echo "3: Defender (pular turno com redução de dano)\n";

    $acao = (int) readline("Escolha uma ação: ");

    match ($acao) {
      1 => $this->atacar(),
      2 => $this->curar(),
      3 => $this->defender(),
      default => throw new \Exception("[Arbitro] Ação inválida: $acao")
    };

    echo "\033[6;1H"; //MOVE
    echo "\033[J"; // LIMPA
  }

  public function atacar(): void
  {
    [$atacante, $defensor] = $this->resolverAtacanteEDefensor();
    [$especiaisDisponiveis, $nomesEspeciais] = $this->resolverEspeciaisDoAtacante();

    $this->exibirOpcoesDeAtaque($atacante, $especiaisDisponiveis, $nomesEspeciais);

    $escolha = (int) readline("Escolha um ataque: ");
    $ataquesBasicos = array_keys($atacante->ataquesBasicos);
    $totalBasicos   = count($ataquesBasicos);

    if ($escolha < $totalBasicos) {
      $nomeAtaque = $ataquesBasicos[$escolha];
      $defensor->causarDano($atacante->ataquesTotal[$nomeAtaque]);
      return;
    }

    $indiceEspecial = $escolha - $totalBasicos;

    if (!isset($nomesEspeciais[$indiceEspecial]) || !$especiaisDisponiveis[$indiceEspecial])
      throw new \Exception("[Arbitro] Ataque especial inválido ou indisponível.");

    $nomeAtaque = $nomesEspeciais[$indiceEspecial];
    $defensor->causarDano($atacante->ataquesTotal[$nomeAtaque]);
    $this->consumirEspecial($indiceEspecial);
  }

  private function exibirOpcoesDeAtaque($atacante, array $especiaisDisponiveis, array $nomesEspeciais): void
  {
    echo "\nATAQUES BÁSICOS\n";
    foreach (array_keys($atacante->ataquesBasicos) as $i => $nome)
      echo "$i: $nome\n";

    $totalBasicos = count($atacante->ataquesBasicos);
    foreach ($nomesEspeciais as $i => $nome) {
      if (!$especiaisDisponiveis[$i]) continue;
      $exibicao = $i + $totalBasicos;
      echo "$exibicao: [ESPECIAL] $nome\n";
    }
  }

  public function defender(): void
  {
    $jogador = $this->jogadorDaVez === 1 ? $this->jogador1 : $this->jogador2;
    $jogador->ativarDefesa();
    echo "Jogador {$this->jogadorDaVez} está em postura defensiva!\n";
  }

  public function curar(): void
  {
    [$jogador, $pocoesDisponiveis] = $this->resolverJogadorEPocoesDoTurno();
    $pocoesFiltradas = array_filter($pocoesDisponiveis);

    if (empty($pocoesFiltradas)) {
      echo "Nenhuma poção disponível.\n";
      return;
    }

    $this->exibirPocoesDisponiveis($pocoesFiltradas);

    $nivelEscolhido = (int) readline("Digite o nível da poção: ");

    if (!in_array($nivelEscolhido, $pocoesFiltradas))
      throw new \Exception("[Arbitro] Poção inválida ou indisponível: $nivelEscolhido");

    $indice = $nivelEscolhido - 1;
    $jogador->usarPocao($indice);
    $this->consumirPocao($indice);
  }

  private function exibirPocoesDisponiveis(array $pocoesFiltradas): void
  {
    echo "\nPOÇÕES DISPONÍVEIS\n";
    foreach ($pocoesFiltradas as $nivel)
      echo "$nivel: Poção nível $nivel\n";
  }

  // ─── Liberação de recursos por rodada ────────────────────────────────────

  public function liberarEspeciaisPorRodada(): void
  {
    $rodada = $this->contadorRodadas % self::MAX_RODADAS_ESPECIAL;

    match ($rodada) {
      self::RODADA_LIBERA_ESPECIAL_1 => $this->liberarEspecialNoIndice(0),
      self::RODADA_LIBERA_ESPECIAL_2 => $this->liberarEspecialNoIndice(1),
      default                        => null
    };

    $this->liberarEspecialDeDesespero();
  }

  private function liberarEspecialNoIndice(int $indice): void
  {
    $this->especiaisDisponiveisJogador1[$indice] = true;
    $this->especiaisDisponiveisJogador2[$indice] = true;
  }

  private function liberarEspecialDeDesespero(): void
  {
    if ($this->especialUnicoJogador1Disponivel && $this->jogador1->vida <= 100) {
      $this->especiaisDisponiveisJogador1[2]      = true;
      $this->especialUnicoJogador1Disponivel      = false;
    }

    if ($this->especialUnicoJogador2Disponivel && $this->jogador2->vida <= 100) {
      $this->especiaisDisponiveisJogador2[2]      = true;
      $this->especialUnicoJogador2Disponivel      = false;
    }
  }

  public function liberarPocoes(): void
  {
    $rodada = $this->contadorRodadas % self::MAX_RODADAS_POCAO;

    match ($rodada) {
      self::RODADA_LIBERA_POCAO_1 => $this->reporPocao(0, self::POCAO_NIVEL_1),
      self::RODADA_LIBERA_POCAO_2 => $this->reporPocao(1, self::POCAO_NIVEL_2),
      self::RODADA_LIBERA_POCAO_3 => $this->reporPocao(2, self::POCAO_NIVEL_3),
      default                     => null
    };
  }

  private function reporPocao(int $indice, int $nivel): void
  {
    if ($this->pocoesDisponiveisJogador1[$indice] === 0)
      $this->pocoesDisponiveisJogador1[$indice] = $nivel;

    if ($this->pocoesDisponiveisJogador2[$indice] === 0)
      $this->pocoesDisponiveisJogador2[$indice] = $nivel;
  }

  // ─── Helpers de resolução por turno ──────────────────────────────────────

  private function resolverAtacanteEDefensor(): array
  {
    return $this->jogadorDaVez === 1
      ? [$this->jogador1, $this->jogador2]
      : [$this->jogador2, $this->jogador1];
  }

  private function resolverEspeciaisDoAtacante(): array
  {
    return $this->jogadorDaVez === 1
      ? [$this->especiaisDisponiveisJogador1, array_keys($this->jogador1::ATAQUES_ESPECIAIS)]
      : [$this->especiaisDisponiveisJogador2, array_keys($this->jogador2::ATAQUES_ESPECIAIS)];
  }

  private function resolverJogadorEPocoesDoTurno(): array
  {
    return $this->jogadorDaVez === 1
      ? [$this->jogador1, $this->pocoesDisponiveisJogador1]
      : [$this->jogador2, $this->pocoesDisponiveisJogador2];
  }

  private function consumirEspecial(int $indice): void
  {
    if ($this->jogadorDaVez === 1)
      $this->especiaisDisponiveisJogador1[$indice] = false;
    else
      $this->especiaisDisponiveisJogador2[$indice] = false;
  }

  private function consumirPocao(int $indice): void
  {
    if ($this->jogadorDaVez === 1)
      $this->pocoesDisponiveisJogador1[$indice] = 0;
    else
      $this->pocoesDisponiveisJogador2[$indice] = 0;
  }
}