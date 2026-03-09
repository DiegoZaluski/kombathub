<?php

require_once "./src/combatentes.php";
/**
 * @property array $ataques
 */
class Arbitro
{
  public $classeJogador1;
  public $classeJogador2;
  public array  $EspecialDisponivelCombatente1 = [false, false, false];
  public array  $EspecialDisponivelCombatente2 = [false, false, false];
  public array  $ataquesEspeciasJogador1;
  public array  $ataquesEspeciasJogador2;

  public int $jogadorDaVez = 1;
  const PERSONAGENS        = Combatentes::PERSONAGENS;
  
  public string|null $combatente1 = null;
  public string|null $combatente2 = null;

  public function __construct($classeJogador1, $classeJogador2) {
    $this->classeJogador1  =  $classeJogador1;
    $this->classeJogador2  =  $classeJogador2;
    $this->ataquesEspeciasJogador1 = array_keys($this->classeJogador1::ATAQUES_ESPECIAIS);
    $this->ataquesEspeciasJogador2 = array_keys($this->classeJogador2::ATAQUES_ESPECIAIS);

  }

  private function exibirCombatentes(): void
  {
    foreach (self::PERSONAGENS as $indice => $combatente) {
      $indice++;
      echo "$indice: $combatente\n";
    }
  }

  public function escolherCombatentes(): void
  {
    echo "Escolha os combatentes:\n";
    $this->exibirCombatentes();
    echo "\n[JOGADOR1]\n";
    $escolha1 = (int)readline("Primeiro combatente: ");

    if (!is_numeric($escolha1) || !isset(self::PERSONAGENS[$escolha1 - 1]))
      throw new \Exception("[Arbitro] ERRO: Primeiro combatente invalido.");
    
    echo "\n[JOGADOR2]\n";
    $escolha2 = (int)readline("Segundo combatente: ");
    if (!is_numeric($escolha2) || !isset(self::PERSONAGENS[$escolha2 - 1]))
      throw new \Exception("[Arbitro] ERRO: Segundo combatente invalido.");

    $this->combatente1 = self::PERSONAGENS[$escolha1 - 1];
    $this->combatente2 = self::PERSONAGENS[$escolha2 - 1];
  }

  public function atacar(): void
  {
    $poderesEspecias = match($this->jogadorDaVez) {
      1       => $this->EspecialDisponivelCombatente1,
      2       => $this->EspecialDisponivelCombatente2,
      default => []
    };

    $ataquesBasicos = array_keys($this->classeJogador1->ataquesBasicos);
    echo "\nATAQUES DIRETOS\n";

    foreach($ataquesBasicos as $index =>$ataque) echo "$index: $ataque\n";
    foreach (array_filter($poderesEspecias) as $index => $poderEspecial) {
      $index += 2;
      echo "PODER ESPECIAL DISPONIVEL!\n";
      echo "$index: $poderEspecial\n";
    }

    $ataqueEscolhido = (int)readline("escolha um ataque: ");
    if (!is_numeric($ataqueEscolhido) && $ataqueEscolhido > 5) 
      throw new \Exception("[Arbitro][atacar] ERRO: input de \"ataqueEscolhido\" invalido");

    if ($ataqueEscolhido <3) {
      $nomeDoAtaque = $ataquesBasicos[$ataqueEscolhido];
      match ($this->jogadorDaVez) {
        1       => $this->classeJogador1->causarDano($this->classeJogador2->ataquesBasicos[$nomeDoAtaque]),
        2       => $this->classeJogador2->causarDano($this->classeJogador1->ataquesBasicos[$nomeDoAtaque]),
        default => throw new \Exception("ERRO: DESCONHECIDO")
      };
    } else {
      $nomeDoAtaque = $poderesEspecias[$ataqueEscolhido];
      match ($this->jogadorDaVez) {
        1       => $this->classeJogador1->causarDano($this->classeJogador1->ataqueTota2[$nomeDoAtaque]),
        2       => $this->classeJogador1->causarDano($this->classeJogador1->ataqueTotal[$nomeDoAtaque]),
        default => throw new \Exception("ERRO: DESCONHECIDO")
      };

    }

    if ($this->jogadorDaVez === 1){
      $this->jogadorDaVez ++;
    }  
    $this->jogadorDaVez   --;
  }
}

// $c = new Cavaleiro();
// $b = new Bruxa();
// $a = new Arbitro($c, $b);
// $a->atacar();

// print_r($c->vida);