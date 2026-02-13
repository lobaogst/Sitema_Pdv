<?php
include 'auth.php';
include 'config.php';

$id = $_GET['id'] ?? null;
$caixa_id = $_GET['caixa_id'] ?? null;

if ($id) {
    $pdo->prepare("DELETE FROM itens_venda WHERE venda_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM vendas WHERE id = ?")->execute([$id]);
}

header("Location: vendas.php?caixa_id=" . $caixa_id);