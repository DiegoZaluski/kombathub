<?php 
trait TentativaEErro {

  private function tentativaEErro(
    callable       $func,
    string|null    $argumento,
    string         $textoErro,
    string|bool    $numerico = false,
    int|null       $numeroPosiveis = null,
    array|null          $NumerosNaoPermitidos = []
  ): string|bool|int|null {

    while (true) {
      try {
        $valor = $argumento ? $func($argumento) : $func();
      } catch (Exception $_) {
        echo "$textoErro\n";
        continue;
      }
      if (!$numerico) return $valor;
      (int)$valor;

      if (!is_numeric($valor) || !(!$numeroPosiveis || $valor > 0 && $valor <= $numeroPosiveis)) {
        echo "\n$textoErro\n[CAUSA] VALOR NÃO NUMERICO OU VALOR DIGITADO NÃO É UM OPÇÃO NUMERICA.\n";
        continue;
      }

      if ($NumerosNaoPermitidos && in_array($valor, $NumerosNaoPermitidos)) {
        echo "\n$textoErro\n O VALOR DIGITADO NÃO É UM OPÇÃO DISPONÍVEL.\n" ;
        continue;
      }
      return $valor;
    }
  }
}

