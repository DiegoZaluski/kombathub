<?php

interface ArbitroConstantes
{
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

  const VALORES_DE_CURA = [10, 30, 50];

  const ESPECIAS_POSSIVEIS_INDICES = [
    self::INDICE1_APOS_ATAQUES_SIMPLES,
    self::INDICE2_APOS_ATAQUES_SIMPLES,
    self::INDICE3_APOS_ATAQUES_SIMPLES,
  ];
}