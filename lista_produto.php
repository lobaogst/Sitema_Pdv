<?php
include 'auth.php';
include 'config.php';

$produtos = $pdo->query("SELECT * FROM produtos ORDER BY nome ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Produtos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>📦 Meus Produtos</h3>
        <a href="vendas.php" class="btn btn-secondary btn-sm">Voltar</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Nome</th>
                        <th>Preço</th>
                        <th>Estoque</th>
                        <th class="text-center">Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($produtos as $p): ?>
                    <tr>
                        <td><?php echo $p['nome']; ?></td>
                        <td>R$ <?php echo number_format($p['preco'], 2, ',', '.'); ?></td>
                        <td>
                            <span class="badge <?php echo $p['estoque'] <= 2 ? 'bg-danger' : 'bg-success'; ?>">
                                <?php echo $p['estoque']; ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <a href="editar_produto.php?id=<?php echo $p['id']; ?>" class="btn btn-primary btn-sm">Editar</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>