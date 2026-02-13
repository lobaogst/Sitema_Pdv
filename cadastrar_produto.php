<?php
include 'config.php';

$mensagem = "";

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome    = $_POST['nome'];
    $preco   = $_POST['preco'];
    $estoque = $_POST['estoque'];

    try {
        $sql = "INSERT INTO produtos (nome, preco, estoque) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nome, $preco, $estoque]);
        
        $mensagem = "<div class='alert alert-success'>Produto '$nome' cadastrado com sucesso!</div>";
    } catch (PDOException $e) {
        $mensagem = "<div class='alert alert-danger'>Erro ao cadastrar: " . $e->getMessage() . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastro de Produtos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Cadastrar Novo Produto</h4>
                </div>
                <div class="card-body">
                    <?php echo $mensagem; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Nome do Produto</label>
                            <input type="text" name="nome" class="form-control" placeholder="Ex: Arroz 5kg" required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Preço de Venda (R$)</label>
                                <input type="number" step="0.01" name="preco" class="form-control" placeholder="0.00" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Estoque Inicial</label>
                                <input type="number" name="estoque" class="form-control" placeholder="0" required>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success">Salvar Produto</button>
                            <a href="abrir_caixa.php" class="btn btn-outline-secondary">Voltar ao Início</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="mt-4">
                <h5>Produtos Cadastrados</h5>
                <table class="table table-striped bg-white shadow-sm">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Preço</th>
                            <th>Estoque</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $produtos = $pdo->query("SELECT * FROM produtos ORDER BY id DESC LIMIT 5")->fetchAll();
                        foreach ($produtos as $p) {
                            echo "<tr>
                                    <td>{$p['id']}</td>
                                    <td>{$p['nome']}</td>
                                    <td>R$ " . number_format($p['preco'], 2, ',', '.') . "</td>
                                    <td>{$p['estoque']}</td>
                                  </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</body>
</html>