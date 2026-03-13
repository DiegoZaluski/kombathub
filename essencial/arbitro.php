<?php
require_once("./essencial/util/tentativaEErro.php");
require_once("./essencial/util/painel.php");
class Arbitro
// reporPocao
{
  use Painel;
  use TentativaEErro;

  const POCAO_NIVEL_1                = 1;
  const POCAO_NIVEL_2                = 2;
  const POCAO_NIVEL_3                = 3;
  const INDICE1_APOS_ATAQUES_SIMPLES = 4;
  const INDICE2_APOS_ATAQUES_SIMPLES = 5;
  const INDICE3_APOS_ATAQUES_SIMPLES = 6;
  const MAX_RODADAS_POCAO            = 7;
  const MAX_RODADAS_ESPECIAL         = 10;
  const RODADA_LIBERA_ESPECIAL_1     = 3;
  const RODADA_LIBERA_ESPECIAL_2     = 6;
  const RODADA_LIBERA_POCAO_1        = 2;
  const RODADA_LIBERA_POCAO_2        = 4;
  const RODADA_LIBERA_POCAO_3        = 6;

  const VOLORES_DE_CURA = [10, 30, 50];

  const ESPECIAS_POSSIVEIS_INDICES = 
  [ 
    self::INDICE1_APOS_ATAQUES_SIMPLES,
    self::INDICE2_APOS_ATAQUES_SIMPLES,
    self::INDICE3_APOS_ATAQUES_SIMPLES
  ];

  public $jogador1;
  public $jogador2;
  public string|null $nomeCombatente1 = null;
  public string|null $nomeCombatente2 = null;

  public  int $jogadorDaVez    = 1;
  private int $contadorRodadas = 0; 

  public  array $especiaisDisponiveisJogador1 = [false, false, false];
  public  array $especiaisDisponiveisJogador2 = [false, false, false];
  public  array $golpesSimplesDisponiveis     = ["chute"=> true, "distancia"=> true];
  public  array $pocoesDisponiveisJogador1    = [self::POCAO_NIVEL_1, self::POCAO_NIVEL_2, self::POCAO_NIVEL_3];
  public  array $pocoesDisponiveisJogador2    = [self::POCAO_NIVEL_1, self::POCAO_NIVEL_2, self::POCAO_NIVEL_3];
  private array $pocaosJaUsadasJogador1       = [ ];
  private array $pocaosJaUsadasJogador2       = [ ];

  private bool $especialUnicoJogador1Disponivel = true;
  private bool $especialUnicoJogador2Disponivel = true;

  public \closure $disponivelSuperEspecial;
  public \closure $emojiMorto;

  public function __construct($jogador1, $jogador2, string $nomeCombatente1, string $nomeCombatente2)
  {
    $this->jogador1        = $jogador1;
    $this->jogador2        = $jogador2;
    $this->nomeCombatente1 = $nomeCombatente1;
    $this->nomeCombatente2 = $nomeCombatente2;

    $this->emojiMorto = fn ($vida) => $vida <= 0 ? "💀" : $vida;
  }
  public function disponivelSuperEspecial(): mixed {
    // barraDeEspecial
    $arrayDeRetorno = [ ];
    if ($this->jogador1->barraDeEspecial != 10) {
      $arrayDeRetorno[ ] = $this->jogador1->barraDeEspecial;
    } elseif ($this->jogador1->barraDeEspecial === 10 && $this->especiaisDisponiveisJogador1[2] && $this->jogador1->vida <= 100){
      $arrayDeRetorno[ ] = "[DISPONIVEL]";
    } elseif ($this->jogador1->barraDeEspecial === 10 && !$this->especiaisDisponiveisJogador1[2] && $this->jogador1->vida > 100) {
      $arrayDeRetorno[ ] = "[ESPERANDO O DESESPERO🗡️💥]";
    } else {
      $arrayDeRetorno[ ] = "[INDISPONIVEL]";
    }

    if ($this->jogador2->barraDeEspecial != 10) {
      $arrayDeRetorno[ ] = $this->jogador2->barraDeEspecial;
    } elseif ($this->jogador2->barraDeEspecial === 10 && $this->especiaisDisponiveisJogador2[2] && $this->jogador2->vida <= 100){
      $arrayDeRetorno[ ] = "[DISPONIVEL]";
    } elseif ($this->jogador2->barraDeEspecial === 10 && !$this->especiaisDisponiveisJogador2[2] && $this->jogador2->vida > 100) {
      $arrayDeRetorno[ ] = "[ESPERANDO O DESESPERO🗡️💥]";
    } else {
      $arrayDeRetorno[ ] = "[INDISPONIVEL]";
    }

    return $arrayDeRetorno;
  }
  // ─── Controle de turno ───────────────────────────────────────────────────

  public function avancarTurno(): void
  {
    $this->morteSubta();
    $this->jogadorDaVez = $this->jogadorDaVez === 1 ? 2 : 1;
    $this->contadorRodadas++;
    $this->liberarEspeciaisPorRodada();
  }
  public function exibirEstadoDaBatalha(): void
  {

    $vivoOuMorto1   = ($this->emojiMorto)($this->jogador1->vida);;
    $vivoOuMorto2   = ($this->emojiMorto)($this->jogador2->vida);

    $this->painel([$vivoOuMorto1, $vivoOuMorto2 ], $this->disponivelSuperEspecial()); // AQUI 
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
      $textErro, 
      true,
      3);
      
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
    [$atacante, $defensor]                         = $this->resolverAtacanteEDefensor();
    [$especiaisDisponiveis, $nomesEspeciais]       = $this->resolverEspeciaisDoAtacante();
    [$naoPermitidosEspecias, $permitidosEspeciais] = $this->exibirOpcoesDeAtaque($atacante, $especiaisDisponiveis, $nomesEspeciais); // primeiro ponto 
    
    $mensagem = "Escolha um ataque: ";
    $textErro = "ERRO: OPÇÃO DE ATAQUE INVALIDO." ;

    // $comprimentoNaoPermitidos = count($PermitidosEspecias);
    if ($permitidosEspeciais && count($permitidosEspeciais) > 1) {
      $numeroSuperior = max(...$permitidosEspeciais);
      $numeroDeGolpes = $numeroSuperior;
    } elseif($permitidosEspeciais && count($permitidosEspeciais) === 1) {
      $numeroDeGolpes = $permitidosEspeciais[0];
    } else {
      $numeroDeGolpes = 3;
    }

    $escolha = (int)$this->tentativaEErro(
      readline(...),
      $mensagem,
      $textErro,
      true,
      $numeroDeGolpes, 
      $naoPermitidosEspecias) -1;
    
    $ataquesBasicos = array_keys($atacante->ataquesBasicos);
    $totalBasicos   = count($ataquesBasicos);

    if ($escolha >=0 && $escolha < $totalBasicos) { 
      if ($escolha === 1 || $escolha === 2) $atacante->barraDeEspecial++;

      $nomeAtaque = $ataquesBasicos[$escolha];
      $argumentos = $atacante->ataquesTotal[$nomeAtaque];
      $textoErro  = "[atacar] ERRO: VALOR INVALIDO";
      $defensor->causarDano($argumentos, $textoErro);
      return;
    }

    $indiceEspecial = $escolha - $totalBasicos;

    if (!isset($nomesEspeciais[$indiceEspecial]) || !$especiaisDisponiveis[$indiceEspecial])
      throw new \Exception("[Arbitro] Ataque especial inválido ou indisponível.");

    $nomeAtaque = $nomesEspeciais[$indiceEspecial];
    $defensor->causarDano($atacante->ataquesTotal[$nomeAtaque]);
    $this->consumirEspecial($indiceEspecial);
  }

  private function exibirOpcoesDeAtaque($atacante, array $especiaisDisponiveis, array $nomesEspeciais): mixed
  {
    $numeroDosAtaquesDispoveis = [ ];
    echo "\nATAQUES BÁSICOS\n";
    foreach (array_keys($atacante->ataquesBasicos) as $i => $nome){

      $danoDoGolpe = $atacante->ataquesBasicos[$nome];
      $i++;
      echo "$i: $nome [DANO: $danoDoGolpe]\n";
    }

    $totalBasicos = count($atacante->ataquesBasicos);
    foreach ($nomesEspeciais as $i => $nome) {
      if (!$especiaisDisponiveis[$i]) continue;

      $danoDoGolpe                  = $atacante->ataquesTotal[$nome];
      $exibicao                     = $i + $totalBasicos + 1;
      $numeroDosAtaquesDispoveis[ ] = $exibicao;

      echo "$exibicao: [ESPECIAL] $nome [DANO: $danoDoGolpe]\n";
    }

    if (!$numeroDosAtaquesDispoveis) return [null, null];

    $arrayAtaquesIndiponiveis = array_diff(self::ESPECIAS_POSSIVEIS_INDICES,$numeroDosAtaquesDispoveis);
    $arrayAtaquesDisponiveis  = array_intersect(self::ESPECIAS_POSSIVEIS_INDICES,$numeroDosAtaquesDispoveis);

    // var_dump($arrayAtaquesIndiponiveis);
    return [$arrayAtaquesIndiponiveis, $arrayAtaquesDisponiveis];
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

  private function liberarEspecialUnico(): void 
  {
    [$jogador, $_] = $this->resolverAtacanteEDefensor();
    match($this->jogadorDaVez) {
      1 => $this->podeUsarEspecialUnico($this->especialUnicoJogador1Disponivel, $jogador),
      2 => $this->podeUsarEspecialUnico($this->especialUnicoJogador2Disponivel, $jogador),
    };
  }

  private function podeUsarEspecialUnico($especialDisponivel, $jogador): void {
    if (!$especialDisponivel || $jogador->vida > 100) return;
    if ($jogador->barraDeEspecial != 10) return;
    switch($this->jogadorDaVez) {
      case (1): {
        if ($this->especiaisDisponiveisJogador1[2] === true) return;
        $this->especiaisDisponiveisJogador1[2] = true;
        $this->especialUnicoJogador1Disponivel = false; 
        break;
      }
      case (2): {
        if ($this->especiaisDisponiveisJogador2[2] === true) return;
        $this->especiaisDisponiveisJogador2[2] = true;
        $this->especialUnicoJogador2Disponivel = false; 
        break;
      }
      default: { throw new \Exception("[podeUsarEspecialUnico] Não deveria cair nesse case"); }
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
    $textoErro = "[curar]ERRO: POÇÃO NÃO DISPONIVEL.";
    
    $nivelEscolhido = (int) $this->tentativaEErro(
      readline(...), 
      $mensagem, 
      $textoErro,
      true,3
      ,$this->addOuPegarpocaosJaUsada());  

    $this->addOuPegarpocaosJaUsada($nivelEscolhido);
    if (!in_array($nivelEscolhido, $pocoesFiltradas))
      throw new \Exception("[Arbitro] Poção inválida ou indisponível: $nivelEscolhido");

    $indice = $nivelEscolhido -1;
    $jogador->usarPocao($indice, self::VOLORES_DE_CURA);

    $this->consumirPocao($indice);
  }

  private function exibirPocoesDisponiveis(array $pocoesFiltradas): void 
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

  public function addOuPegarpocaosJaUsada($add = null) {
  /**
   * @param int|null $add: para setar um valor no respectiva propriedade 
   *  do jogador atual da rodada (pocaosJaUsadasJogador1 | pocaosJaUsadasJogador2)
   * @return array
   */
    if ($this->jogadorDaVez === 1) {
      if (!$add) return $this->pocaosJaUsadasJogador1;
      $this->pocaosJaUsadasJogador1[ ] = $add;
    } else {
      if (!$add) return $this->pocaosJaUsadasJogador2;
      $this->pocaosJaUsadasJogador2[ ] = $add;
    }
  }

  // ─── Limite passe de cura ──────────────────────────────────────
  private function morteSubta():void {
    $vidaJogador1 = $this->jogador1->vida;  
    $vidaJogador2 = $this->jogador2->vida;
    $mesagem = "A ganância leva o homem à destruição.\n";
    if ($vidaJogador1 >= 300 && $vidaJogador2 >= 300) {
      echo "[AMBOS SE ENVENENRAM COM EXESSO DE POÇÃO]\n" . $mesagem; 
    } elseif($vidaJogador1 >= 300) {
      echo $mesagem;
      $this->jogador1->vida = 0;
    } elseif($vidaJogador2 >= 300) {
      echo $mesagem;
      $this->jogador2->vida = 0;
    }
  }
}