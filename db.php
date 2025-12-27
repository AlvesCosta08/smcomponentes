<?php
// Configurações do banco de dados
$host = '108.167.151.55';
$dbname = 'codig267_chatbot_sm';
$user = 'codig267_chatbot_sm';
$pass = 'Alves1974#';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro ao conectar ao banco de dados: " . $e->getMessage());
}


