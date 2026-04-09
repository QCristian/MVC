<?php
session_destroy();
header('Location: /mvc/public/index.php?page=inicio');
exit;