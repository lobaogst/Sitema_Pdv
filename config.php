<?php
$host = 'localhsot';
$db   = 'sistema_pdvv';
$user = 'root'; // ajuste se necessário
$pass = '';     // ajuste se necessário

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro ao conectar: " . $e->getMessage());
}
?>