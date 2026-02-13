<?php
include 'auth.php';
include 'config.php';

$pedidos = $pdo->query("SELECT * FROM vendas WHERE telefone_cliente IS NOT NULL ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Pedidos Online</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <h3>📋 Todos os Pedidos Online</h3>
        <table class="table bg-white shadow-sm mt-3">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Telefone</th>
                    <th>Status</th>
                    <th>Valor</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($pedidos as $p): ?>
                <tr>
                    <td>#<?php echo $p['id']; ?></td>
                    <td><?php echo $p['telefone_cliente']; ?></td>
                    <td><span class="badge bg-info"><?php echo $p['status_entrega']; ?></span></td>
                    <td>R$ <?php echo number_format($p['total_venda'], 2, ',', '.'); ?></td>
                    <td>
                        <a href="editar_pedido.php?id=<?php echo $p['id']; ?>" class="btn btn-sm btn-warning">Editar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>