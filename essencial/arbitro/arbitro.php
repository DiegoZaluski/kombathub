<?php

require_once(__DIR__ . "/../util/tentativaEErro.php");
require_once(__DIR__ . "/../util/painel.php");
require_once(__DIR__ . "/arbitroConstantes.php");
require_once(__DIR__ . "/arbitroEstado.php");
require_once(__DIR__ . "/arbitroTurno.php");
require_once(__DIR__ . "/arbitroAtaque.php");
require_once(__DIR__ . "/arbitroCura.php");

class Arbitro implements ArbitroConstantes
{
  use Painel, TentativaEErro, ArbitroEstado, ArbitroTurno, ArbitroAtaque, ArbitroCura {
    TentativaEErro::tentativaEErro insteadof ArbitroAtaque, ArbitroCura;
  }

  public object      $jogador1;
  public object      $jogador2;
  public string|null $nomeCombatente1 = null;
  public string|null $nomeCombatente2 = null;

  public  int  $jogadorDaVez    = 1;
  private int  $contadorRodadas = 0;

  public  array $especiaisDisponiveisJogador1 = [false, false, false];
  public  array $especiaisDisponiveisJogador2 = [false, false, false];
  public  array $pocoesDisponiveisJogador1    = [self::POCAO_NIVEL_1, self::POCAO_NIVEL_2, self::POCAO_NIVEL_3];
  public  array $pocoesDisponiveisJogador2    = [self::POCAO_NIVEL_1, self::POCAO_NIVEL_2, self::POCAO_NIVEL_3];
  private array $pocaosJaUsadasJogador1       = [];
  private array $pocaosJaUsadasJogador2       = [];

  private bool $especialUnicoJogador1Disponivel = true;
  private bool $especialUnicoJogador2Disponivel = true;

  public function __construct(object $jogador1, object $jogador2, string $nomeCombatente1, string $nomeCombatente2)
  {
    $this->jogador1        = $jogador1;
    $this->jogador2        = $jogador2;
    $this->nomeCombatente1 = $nomeCombatente1;
    $this->nomeCombatente2 = $nomeCombatente2;
  }

  // ─── Estado ──────────────────────────────────────────────────────────────

  /**
   * Exibe o painel de estado atual da batalha com vida e barra especial.
   */
  public function exibirEstadoDaBatalha(): void
  {
    $emojiMorto   = fn ($vida) => $vida <= 0 ? "💀" : $vida;
    $vivoOuMorto1 = $emojiMorto($this->jogador1->vida);
    $vivoOuMorto2 = $emojiMorto($this->jogador2->vida);

    $this->painel([$vivoOuMorto1, $vivoOuMorto2], $this->disponivelSuperEspecial());
  }

  // ─── Ação do turno ───────────────────────────────────────────────────────

  /**
   * Solicita e executa a ação do jogador da vez (atacar, curar ou defender).
   *
   * @throws \Exception Se a ação escolhida for inválida
   */
  public function executarAcaoDoTurno(): void
  {
    echo "\nO que deseja fazer?\n";
    echo "1: Atacar\n";
    echo "2: Usar poção\n";
    echo "3: Defender (pular turno com redução de dano)\n";

    $acao = (int) $this->tentativaEErro(
      readline(...),
      "Escolha uma ação: ",
      "ERRO: OPÇÃO DE AÇÃO INVÁLIDA.",
      true,
      3
    );

    match ($acao) {
      1       => $this->atacar(),
      2       => $this->curar(),
      3       => $this->defender(),
      default => throw new \Exception("[Arbitro] Ação inválida: $acao"),
    };

    echo "\033[6;1H"; // move cursor
    echo "\033[J";    // limpa abaixo
  }

  // ─── Helpers de resolução por turno ──────────────────────────────────────

  /**
   * Retorna [atacante, defensor] conforme o jogador da vez.
   *
   * @return array{0: object, 1: object}
   */
  private function resolverAtacanteEDefensor(): array
  {
    return $this->jogadorDaVez === 1
      ? [$this->jogador1, $this->jogador2]
      : [$this->jogador2, $this->jogador1];
  }

  /**
   * Retorna [especiaisDisponiveis, nomesEspeciais] do atacante da vez.
   *
   * @return array{0: array, 1: array}
   */
  private function resolverEspeciaisDoAtacante(): array
  {
    return $this->jogadorDaVez === 1
      ? [$this->especiaisDisponiveisJogador1, array_keys($this->jogador1::ATAQUES_ESPECIAIS)]
      : [$this->especiaisDisponiveisJogador2, array_keys($this->jogador2::ATAQUES_ESPECIAIS)];
  }

  /**
   * Retorna [jogador, pocoesDisponiveis] do jogador da vez.
   *
   * @return array{0: object, 1: array}
   */
  private function resolverJogadorEPocoesDoTurno(): array
  {
    return $this->jogadorDaVez === 1
      ? [$this->jogador1, $this->pocoesDisponiveisJogador1]
      : [$this->jogador2, $this->pocoesDisponiveisJogador2];
  }
}