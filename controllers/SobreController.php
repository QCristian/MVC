<?php
require '../models/SobreModel.php';
$data = SobreModel::getDatos();
$titulo = "Sobre";
$contenido = "<h1>{$data['titulo']} - Sobre</h1><p>{$data['contenido']}</p>";
include '../views/layout.php';
