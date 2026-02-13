<?php
include 'config.php'; // Apenas a conexão

$pedido = null;
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM vendas WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $pedido = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Consultar Pedido</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow border-0 text-center p-4">
                    <h3>🔍 Acompanhar Pedido</h3>
                    <form method="GET" class="mt-3 d-flex">
                        <input type="number" name="id" class="form-control form-control-lg" placeholder="Nº do Pedido" required>
                        <button type="submit" class="btn btn-primary ms-2">Buscar</button>
                    </form>

                    <?php if ($pedido): ?>
                        <div class="mt-4 text-start">
                            <hr>
                            <h5>Status: 
                                <span class="badge <?php echo $pedido['status_entrega'] == 'pendente' ? 'bg-warning' : 'bg-success'; ?>">
                                    <?php echo strtoupper($pedido['status_entrega']); ?>
                                </span>
                            </h5>
                            <p><strong>Telefone:</strong> <?php echo $pedido['telefone_cliente']; ?></p>
                            <p><strong>Descrição:</strong><br><?php echo nl2br($pedido['descricao_pedido']); ?></p>
                            
                            <?php if ($pedido['foto_item']): ?>
                                <label class="fw-bold">Foto/Referência:</label>
                                <img src="uploads/<?php echo $pedido['foto_item']; ?>" class="img-fluid rounded shadow-sm d-block mt-2">
                            <?php endif; ?>
                        </div>
                    <?php elseif (isset($_GET['id'])): ?>
                        <div class="alert alert-danger mt-4">Pedido não encontrado!</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>