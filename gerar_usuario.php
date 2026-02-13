<?php
include 'config.php';

$nome = 'Administrador';
$user = 'katia';
$senha_pura = '310574';
// Gera o hash seguro que o seu index.php espera
$senha_hash = password_hash($senha_pura, PASSWORD_DEFAULT);

try {
    // Limpa o usuário anterior para não dar erro de duplicidade
    $pdo->prepare("DELETE FROM usuarios WHERE usuario = ?")->execute([$user]);
    
    // Insere o novo usuário com o hash correto
    $sql = "INSERT INTO usuarios (nome, usuario, senha, nivel) VALUES (?, ?, ?, 'admin')";
    $pdo->prepare($sql)->execute([$nome, $user, $senha_hash]);
    
    echo "Usuário '$user' com senha '$senha_pura' criado com sucesso!";
} catch (PDOException $e) {
    echo "Erro ao criar usuário: " . $e->getMessage();
}
?>