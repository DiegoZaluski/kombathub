<?php

class SeletorDeCombatentes
{
  const PERSONAGENS = Combatentes::PERSONAGENS;

  public string $nomeCombatente1;
  public string $nomeCombatente2;

  public function escolherCombatentes(): array
  {
    echo "Escolha os combatentes:\n";
    $this->exibirListaDePersonagens();

    echo "\n[JOGADOR 1]\n";
    $this->nomeCombatente1 = $this->selecionarPersonagem("Primeiro combatente: ");

    echo "\n[JOGADOR 2]\n";
    $this->nomeCombatente2 = $this->selecionarPersonagem("Segundo combatente: ");

    return [
      $this->instanciarCombatente($this->nomeCombatente1),
      $this->instanciarCombatente($this->nomeCombatente2),
    ];
  }

  private function instanciarCombatente(string $nome): Combatentes
  {
    return match ($nome) {
      'mago'      => new Mago(),
      'cavaleiro' => new Cavaleiro(),
      'bruxa'     => new Bruxa(),
      default     => throw new \Exception("[SeletorDeCombatentes] Combatente inválido: $nome"),
    };
  }

  private function exibirListaDePersonagens(): void
  {
    foreach (self::PERSONAGENS as $indice => $nome)
      echo ($indice + 1) . ": $nome\n";
  }

  private function selecionarPersonagem(string $mensagem): string
  {
    $escolha = (int) readline($mensagem);

    if (!isset(self::PERSONAGENS[$escolha - 1]))
      throw new \Exception("[Arbitro] Personagem inválido: $escolha");

    return self::PERSONAGENS[$escolha - 1];
  }
}