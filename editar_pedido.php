<?php
include 'auth.php';
include 'config.php';

// 1. Verifica se o ID foi passado na URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('ID do pedido não fornecido!'); window.location='lista_online.php';</script>";
    exit;
}

$id = $_GET['id'];

// 2. Busca os dados do pedido
$stmt = $pdo->prepare("SELECT * FROM vendas WHERE id = ?");
$stmt->execute([$id]);
$pedido = $stmt->fetch();

// 3. Se o pedido não existir no banco de dados
if (!$pedido) {
    echo "<script>alert('Pedido não encontrado!'); window.location='lista_online.php';</script>";
    exit;
}

// Lógica de Processamento do Formulário (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $telefone = preg_replace('/[^0-9]/', '', $_POST['telefone']);
    $descricao = $_POST['descricao'];
    $status = $_POST['status'];
    $valor = $_POST['valor'];

    // Lógica da Foto
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $extensao = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $foto_nome = "edit_" . time() . "_" . uniqid() . "." . $extensao;
        move_uploaded_file($_FILES['foto']['tmp_name'], "uploads/" . $foto_nome);
        
        $sql = "UPDATE vendas SET telefone_cliente=?, descricao_pedido=?, status_entrega=?, total_venda=?, foto_item=? WHERE id=?";
        $pdo->prepare($sql)->execute([$telefone, $descricao, $status, $valor, $foto_nome, $id]);
    } else {
        $sql = "UPDATE vendas SET telefone_cliente=?, descricao_pedido=?, status_entrega=?, total_venda=? WHERE id=?";
        $pdo->prepare($sql)->execute([$telefone, $descricao, $status, $valor, $id]);
    }

    echo "<script>alert('Pedido atualizado com sucesso!'); window.location='lista_online.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Pedido #<?php echo $id; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card shadow border-0 col-md-8 mx-auto">
            <div class="card-header bg-warning py-3">
                <h5 class="mb-0 fw-bold">✏️ Editando Pedido #<?php echo $id; ?></h5>
            </div>
            <div class="card-body p-4">
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="fw-bold">Telefone do Cliente</label>
                        <input type="text" name="telefone" class="form-control" value="<?php echo $pedido['telefone_cliente']; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="fw-bold">Status do Pedido</label>
                        <select name="status" class="form-select border-primary">
                            <option value="pendente" <?php echo ($pedido['status_entrega'] == 'pendente') ? 'selected' : ''; ?>>🟡 Pendente / Recebido</option>
                            <option value="em_preparacao" <?php echo ($pedido['status_entrega'] == 'em_preparacao') ? 'selected' : ''; ?>>🔵 Em Preparação</option>
                            <option value="pronto" <?php echo ($pedido['status_entrega'] == 'pronto') ? 'selected' : ''; ?>>🟢 Pronto para Retirada</option>
                            <option value="entregue" <?php echo ($pedido['status_entrega'] == 'entregue') ? 'selected' : ''; ?>>⚪ Entregue</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="fw-bold">Descrição dos Itens</label>
                        <textarea name="descricao" class="form-control" rows="4" required><?php echo $pedido['descricao_pedido']; ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Valor Total (R$)</label>
                            <input type="number" step="0.01" name="valor" class="form-control" value="<?php echo $pedido['total_venda']; ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold">Foto Atual</label>
                            <?php if ($pedido['foto_item']): ?>
                                <div class="mt-1">
                                    <small class="text-success">Já existe uma foto. Envie outra se quiser trocar.</small>
                                </div>
                            <?php endif; ?>
                            <input type="file" name="foto" class="form-control">
                        </div>
                    </div>

                    <hr>
                    <div class="d-flex justify-content-between">
                        <a href="lista_online.php" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-warning fw-bold px-5">SALVAR ALTERAÇÕES</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>