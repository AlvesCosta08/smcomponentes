<?php
session_start();

// Destrói todas as variáveis de sessão
$_SESSION = [];
session_unset();
session_destroy();

// Evita o uso de cache da página anterior
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
header("Location: login.php");
exit;



