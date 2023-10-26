<?php
namespace App\Service;

class GeneradorDeMensajes {
  private const mensaje = 'Bienvenid@';

  public function getMensaje(): string
  {
    return $this::mensaje;
  }


}