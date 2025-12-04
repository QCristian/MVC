<?php
require '../models/ContactoModel.php';
$data = ContactoModel::getDatos();
$titulo = "Contacto";
$contenido = "<h1>{$data['titulo']} - Contacto</h1><p>{$data['contenido']}</p>";
include '../views/layout.php';
