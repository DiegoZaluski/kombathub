<?php 
trait TentativaEErro {
  // __ Utilitario ____________________________________________
  private function tentativaEErro(
    callable $func , 
    string $menssagemParaArgumento, 
    string $textoErro): string|bool|int

  {
    while (true) {
      try {
        $valorDaFuncao = $func($menssagemParaArgumento);
        return $valorDaFuncao;
      } 
      catch (Exception $e ) {
        echo "\n$textoErro\n";
      }
    } 
  }
//_____________________________________________________________
}