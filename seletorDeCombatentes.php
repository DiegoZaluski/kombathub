<?php
require_once("./essencial/util/tentativaEErro.php");
class SeletorDeCombatentes
{
  use TentativaEErro;
  const PERSONAGENS = Combatentes::PERSONAGENS;

  public string $nomeCombatente1;
  public string $nomeCombatente2;
  
  public function escolherCombatentes(): array
  {
    echo "Escolha os combatentes:\n";
    $this->exibirListaDePersonagens();

    $textoErro = "\n[ERRO: OPÇÃO ESCOLHIDA PARA COMBATENTE É INVALIDA.]\n";
    $mensagemCombatente1 = "Primeiro combatente: ";
    $mensagemCombatente2 = "Segundo combatente: ";

    echo "\n[JOGADOR 1]\n";
    $this->nomeCombatente1 = $this->tentativaEErro(
      $this->selecionarPersonagem(...),
      $mensagemCombatente1,
      $textoErro);

    echo "\n[JOGADOR 2]\n";
    $this->nomeCombatente2 = $this->tentativaEErro(
      $this->selecionarPersonagem(...),
      $mensagemCombatente2,
      $textoErro);

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