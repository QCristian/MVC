<?php
require '../models/DraftModel.php';
$data = DraftModel::getDatos();
$titulo = "Drafts";

// Contenido fijo que quieres mantener
$contenido = "<h1>{$data['titulo']} - Tus Drafts</h1><p>{$data['contenido']}</p>";

// Captura adicional de la vista, se concatena al contenido fijo
ob_start();
include '../views/draft.php';
$contenido .= ob_get_clean(); // concatena lo nuevo de la vista

include '../views/layout.php';
