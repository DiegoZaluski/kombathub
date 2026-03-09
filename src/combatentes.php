<?php

require_once "./src/acoes.php";

class Combatentes
{
  const PERSONAGENS = ["mago", "cavaleiro", "bruxa"];

  const ATAQUES_BASICOS = [
    "soco"      => 5,
    "chute"     => 10,
    "distancia" => 15,
  ];

  protected int   $vida    = 200;
  protected array $ataquesBasicos = [];

  public function __construct()
  {
    $this->ataquesBasicos = self::ATAQUES_BASICOS;
  }

  public function __get(string $propriedade): mixed
  {
    return match ($propriedade) {
      'vida'  => $this->vida,
      'ataquesBasicos'=> $this->ataquesBasicos,
      default => throw new InvalidArgumentException("[__get] Propriedade '$propriedade' nao existe."),
    };
  }

  public function __set(string $propriedade, int $valor): void
  {
    if (!is_numeric($valor))
      throw new InvalidArgumentException("[__set] Valor deve ser numerico.");

    match ($propriedade) {
      "vida"  => $this->vida = $valor,
      default => throw new InvalidArgumentException("[__set] Propriedade '$propriedade' nao existe."),
    };
  }

  public function buscarAtaque(string $ataque, string $combatente): array
  {
    if (!is_numeric($ataque) && isset($this->ataques[$combatente][$ataque]))
      return [$ataque, [$this->ataques[$combatente][$ataque]]];

    throw new InvalidArgumentException("[buscarAtaque] Ataque '$ataque' nao encontrado para '$combatente'.");
  }
}

class Mago extends Combatentes
{
  use Acao;
  const ATAQUES_ESPECIAIS = [
    "explosao_de_luz" => 20,
    "raio"            => 30,
    "bola_de_fogo"    => 50,
  ];

  public array $ataquesTotal = [];
  public function __construct()
  {
    parent::__construct();
    $this->ataquesTotal = array_merge($this->ataquesBasicos, self::ATAQUES_ESPECIAIS);
  }
}

class Cavaleiro extends Combatentes
{
  use Acao;
  const ATAQUES_ESPECIAIS = [
    "golpe_de_escudo" => 15,
    "corte"           => 20,
    "investida"       => 30,
  ];

  public array $ataquesTotal = [];
  public function __construct()
  {
    parent::__construct();
    $this->ataquesTotal = array_merge($this->ataquesBasicos, self::ATAQUES_ESPECIAIS);
  }
}

class Bruxa extends Combatentes
{
  use Acao;
  const ATAQUES_ESPECIAIS = [
    "explosao_negra"   => 20,
    "raio_amaldicoado" => 30,
    "bola_de_maldicao" => 50,
  ];

  const BONUS_DE_VIDA = 50;

  public array $ataquesTotal = [];
  public function __construct()
  {
    parent::__construct();
    $this->ataquesTotal  = array_merge($this->ataquesBasicos, self::ATAQUES_ESPECIAIS);
    $this->vida         += self::BONUS_DE_VIDA;
  }
}