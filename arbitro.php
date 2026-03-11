<?php
require_once("./src/util/tentativaEErro.php");
class Arbitro
// reporPocao
{

  use TentativaEErro;
  const VOLORES_DE_CURA = [10, 30, 50];
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

  public $golpesSimplesDisponiveis = ["chute"=> true, "distancia"=> true];

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
    // $this->liberarPocoes();
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
    $textErro = "ERRO: OPÇÃO DE ATAQUE INVALIDA.";
    $mensagem = "Escolha uma ação: ";

    $acao = (int)$this->tentativaEErro(
        readline(...),
        $mensagem,
        $textErro);
    // $acao = (int)readline($mensagem );
      
    // echo "AÇÃO LOG: $acao". is_numeric($acao);
    match ($acao) {
      1       => $this->atacar(),
      2       => $this->curar(),
      3       => $this->defender(),
      default => throw new \Exception("[Arbitro] Ação inválida: $acao")    
    };

    echo "\033[6;1H"; // MOVE
    echo "\033[J"; // LIMPA
  }

  public function atacar(): void
  {
    [$atacante, $defensor] = $this->resolverAtacanteEDefensor();
    [$especiaisDisponiveis, $nomesEspeciais] = $this->resolverEspeciaisDoAtacante();

    $this->exibirOpcoesDeAtaque($atacante, $especiaisDisponiveis, $nomesEspeciais);

    // $escolha = (int) readline("Escolha um ataque: ") -1;
    $mensagem = "Escolha um ataque: ";
    $textpErro = "ERRO: OPÇÃO DE ATAQUE INVALIDO." ;

    $escolha = (int)$this->tentativaEErro(
      readline(...),
      $mensagem,
      $textpErro) -1;
    
    $ataquesBasicos = array_keys($atacante->ataquesBasicos);
    $totalBasicos   = count($ataquesBasicos);

    if ($escolha < $totalBasicos) {
      $nomeAtaque = $ataquesBasicos[$escolha];
      $argumentos = $atacante->ataquesTotal[$nomeAtaque];
      $textoErro = "[atacar] ERRO: VALOR INVALIDO";
      $this->tentativaEErro($defensor->causarDano(...), $argumentos, $textoErro); // AQUI
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
    foreach (array_keys($atacante->ataquesBasicos) as $i => $nome){
      // $this->golpesSimplesDisponiveis[$nome];

      $danoDoGolpe = $atacante->ataquesBasicos[$nome];
      $i++;
      echo "$i: $nome [DANO: $danoDoGolpe]\n";
    }

    $totalBasicos = count($atacante->ataquesBasicos);
    foreach ($nomesEspeciais as $i => $nome) {
      if (!$especiaisDisponiveis[$i]) continue;

      $danoDoGolpe = $atacante->ataquesTotal[$nome];
      $exibicao    = $i + $totalBasicos + 1;
      echo "$exibicao: [ESPECIAL] $nome [DANO: $danoDoGolpe]\n";
    }
  }

  public function defender(): void // REVER ESSA LOGICA 
  {
    $jogador = $this->jogadorDaVez === 1 ? $this->jogador1 : $this->jogador2;
    $jogador->ativarDefesa();
    echo "Jogador {$this->jogadorDaVez} está em postura defensiva!\n";
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

    $this->liberarEspecialUnico();
  }

  private function liberarEspecialNoIndice(int $indice): void
  {
    $this->especiaisDisponiveisJogador1[$indice] = true;
    $this->especiaisDisponiveisJogador2[$indice] = true;
  }

  private function liberarEspecialUnico(): void // RENOMEAR ISSO 
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

  private function consumirEspecial(int $indice): void
  {
    if ($this->jogadorDaVez === 1)
      $this->especiaisDisponiveisJogador1[$indice] = false;
    else
      $this->especiaisDisponiveisJogador2[$indice] = false;
  }

  private function resolverJogadorEPocoesDoTurno(): array
  {
    return $this->jogadorDaVez === 1
      ? [$this->jogador1, $this->pocoesDisponiveisJogador1]
      : [$this->jogador2, $this->pocoesDisponiveisJogador2];
  }

  // ─── Logica de cura ──────────────────────────────────────

  public function curar(): void
  {
    [$jogador, $pocoesDisponiveis] = $this->resolverJogadorEPocoesDoTurno();
    $pocoesFiltradas = array_filter($pocoesDisponiveis);

    if (empty($pocoesFiltradas)) {
      echo "Nenhuma poção disponível.\n";
      return;
    }

    $this->exibirPocoesDisponiveis($pocoesFiltradas);

    $mensagem = "Digite o nível da poção: ";
    // $textoErro = "[curar]ERRO: POÇÃO NÃO DISPONIVEL.";

    // $nivelEscolhido = (int) $this->tentativaEErro(
    //   readline(...), 
    //   $mensagem, 
    //   $textoErro,
    //   true); 

    $nivelEscolhido = (int)readline($mensagem);
    if (!in_array($nivelEscolhido, $pocoesFiltradas))
      throw new \Exception("[Arbitro] Poção inválida ou indisponível: $nivelEscolhido");

    $indice = $nivelEscolhido ;
    $jogador->usarPocao($indice, self::VOLORES_DE_CURA);

    $this   ->consumirPocao($indice);
  }

  private function exibirPocoesDisponiveis(array $pocoesFiltradas): void // PESQUISAR O POR QUE FUNCIONOU QUANDO REMOVEU O PARAMETRO NÃO USADO
  {
    echo "\nPOÇÕES DISPONÍVEIS\n";
    foreach ($pocoesFiltradas as $nivel){

      $valorDaCura = self::VOLORES_DE_CURA[$nivel-1];
      echo "$nivel: Poção nível $nivel [VALOR DE CURA: $valorDaCura]\n";

    }
  }
  private function consumirPocao(int $indice): void
  {
    if ($this->jogadorDaVez === 1) 
      $this->pocoesDisponiveisJogador1[$indice] = 0;
    else
      $this->pocoesDisponiveisJogador2[$indice] = 0;
  }

    private function reporPocao(int $indice, int $nivel): void
    {
      if ($this->pocoesDisponiveisJogador1[$indice] === 0)
        $this->pocoesDisponiveisJogador1[$indice] = $nivel;

      if ($this->pocoesDisponiveisJogador2[$indice] === 0)
        $this->pocoesDisponiveisJogador2[$indice] = $nivel;
    }
}

