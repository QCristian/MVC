<?php
require '../models/HomeModel.php';
$data = HomeModel::getDatos();
$titulo = "Inicio";
$contenido = "<h1>{$data['titulo']} - Inicio</h1><p>{$data['contenido']}</p>";
include '../views/layout.php';
