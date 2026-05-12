<?php
require 'config/conexion.php';
require 'app/models/PagesModel.php';
$m = new PagesModel();
$id = $m->createSchool([
    'nombre' => 'Prueba Codex',
    'disciplina' => 'Futbol',
    'dia_pago' => 10,
    'valor_inscripcion' => '10000',
    'valor_mensualidad' => '50000',
    'correo' => 'prueba_codex_'.time().'@mail.com',
    'pass_app' => 'abc123',
    'telefono' => '3001234567',
    'direccion' => 'Calle 1',
    'escudo_path' => null,
    'firma_path' => null,
]);
var_dump($id);
var_dump($m->lastError());
