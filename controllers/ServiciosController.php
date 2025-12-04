<?php
require '../models/ServiciosModel.php';
$data = ServiciosModel::getDatos();
$titulo = "Servicios";
$contenido = "<h1>{$data['titulo']} - Servicios</h1><p>{$data['contenido']}</p>";
include '../views/layout.php';
