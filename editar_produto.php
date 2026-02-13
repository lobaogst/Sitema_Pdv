<?php
include 'auth.php';
include 'config.php';

$id = $_GET['id'] ?? null;
if (!$id) { header("Location: lista_produtos.php"); exit; }

// 1. BUSCA OS DADOS ATUAIS DO PRODUTO
$stmt = $pdo->prepare("SELECT * FROM produtos WHERE id = ?");
$stmt->execute([$id]);
$produto = $stmt->fetch(); // A variável correta é $produto

if (!$produto) {
    echo "<script>alert('Produto não encontrado!'); window.location='lista_produtos.php';</script>";
    exit;
}

// 2. PROCESSA A ATUALIZAÇÃO
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $preco = $_POST['preco'];
    $estoque = $_POST['estoque'];

    try {
        $upd = $pdo->prepare("UPDATE produtos SET nome = ?, preco = ?, estoque = ? WHERE id = ?");
        $upd->execute([$nome, $preco, $estoque, $id]);
        echo "<script>alert('Produto atualizado com sucesso!'); window.location='lista_produtos.php';</script>";
    } catch (Exception $e) {
        echo "Erro ao atualizar: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Produto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow border-0">
                <div class="card-header bg-primary text-white text-center fw-bold">EDITAR PRODUTO</div>
                <div class="card-body p-4">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Nome do Produto</label>
                            <input type="text" name="nome" class="form-control" value="<?php echo htmlspecialchars($produto['nome']); ?>" required>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label fw-bold small">Preço (R$)</label>
                                <input type="number" step="0.01" name="preco" class="form-control" value="<?php echo $produto['preco']; ?>" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label fw-bold small">Estoque Atual</label>
                                <input type="number" name="estoque" class="form-control" value="<?php echo $produto['estoque']; ?>" required>
                            </div>
                        </div>
                        <div class="d-flex gap-2 mt-3">
                            <button type="submit" class="btn btn-success w-100 fw-bold">SALVAR</button>
                            <a href="lista_produtos.php" class="btn btn-outline-secondary w-100">CANCELAR</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>