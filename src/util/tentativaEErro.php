<?php 
trait TentativaEErro {
  // __ Utilitario ____________________________________________
  private function tentativaEErro(
    callable $func , 
    string|null $argumento, 
    string $textoErro, 
    string|bool $numerico = false

    ): string|bool|int|null
    
  {
    while (true) {
      try {
        
        if (!$argumento) {
          $valorDaFuncao = $func();
          return $valorDaFuncao;
        } 

        $valorDaFuncao = $func($argumento);

        if ($numerico && $argumento && is_numeric($valorDaFuncao) ) {
          return $valorDaFuncao;

        } elseif ($numerico) {
          echo "\n$textoErro\n";
          continue;
        } 

        return $valorDaFuncao;
      } 
      catch (Exception $e ) {
        echo "\n$textoErro\n";
      }
    } 
  }
}