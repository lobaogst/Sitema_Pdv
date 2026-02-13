<?php
include 'auth.php'; // Garante que o usuário está logado
include 'config.php';

// 1. BUSCA O CAIXA QUE ESTÁ ABERTO
// Se foi passado um ID via URL, usa ele, senão busca o primeiro aberto
$caixa_id = $_GET['caixa_id'] ?? null;

if ($caixa_id) {
    $stmt = $pdo->prepare("SELECT * FROM caixa WHERE id = ? AND status = 'aberto'");
    $stmt->execute([$caixa_id]);
    $caixa = $stmt->fetch();
} else {
    $stmt = $pdo->query("SELECT * FROM caixa WHERE status = 'aberto' LIMIT 1");
    $caixa = $stmt->fetch();
}

// Se não encontrar nenhum caixa aberto, redireciona para a tela de abertura
if (!$caixa) {
    header("Location: abrir_caixa.php");
    exit;
}

// 2. CALCULA O TOTAL DE VENDAS DESTE CAIXA
$stmtVendas = $pdo->prepare("SELECT SUM(total_venda) as total_vendido FROM vendas WHERE caixa_id = ?");
$stmtVendas->execute([$caixa['id']]);
$vendas_data = $stmtVendas->fetch();
$total_vendas = $vendas_data['total_vendido'] ?? 0;

// Saldo final = Fundo Inicial + Vendas
$saldo_final_esperado = $caixa['valor_inicial'] + $total_vendas;

// 3. PROCESSA O FECHAMENTO APÓS VALIDAR A SENHA
$erro_senha = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['senha_fechamento'])) {
    $senha_digitada = $_POST['senha_fechamento'];
    $usuario_id = $_SESSION['usuario_id'];

    // Busca a senha do usuário logado para conferir
    $stmtUser = $pdo->prepare("SELECT senha FROM usuarios WHERE id = ?");
    $stmtUser->execute([$usuario_id]);
    $user = $stmtUser->fetch();

    if ($user && password_verify($senha_digitada, $user['senha'])) {
        $data_fechamento = date('Y-m-d H:i:s');
        
        // Atualiza o caixa para fechado
        $sql = "UPDATE caixa SET data_fechamento = ?, valor_final = ?, status = 'fechado' WHERE id = ?";
        $pdo->prepare($sql)->execute([$data_fechamento, $saldo_final_esperado, $caixa['id']]);
        
        echo "<script>alert('Caixa encerrado com sucesso!'); window.location='abrir_caixa.php';</script>";
        exit;
    } else {
        $erro_senha = "Senha incorreta! O fechamento não foi autorizado.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Encerrar Caixa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; font-size: 1rem; }
        .card { border-radius: 15px; border: none; }
        .valor-destaque { font-size: 1.5rem; color: #dc3545; font-weight: bold; }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow">
                <div class="card-header bg-danger text-white text-center py-3">
                    <h4 class="mb-0 fw-bold">FECHAMENTO DE CAIXA</h4>
                </div>
                <div class="card-body p-4 text-dark">
                    <p class="mb-2"><strong>Caixa:</strong> <?php echo htmlspecialchars($caixa['identificacao'] ?? $caixa['id']); ?></p>
                    <p class="mb-2"><strong>Aberto em:</strong> <?php echo date('d/m/Y H:i', strtotime($caixa['data_abertura'])); ?></p>
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Fundo Inicial:</span>
                        <span>R$ <?php echo number_format($caixa['valor_inicial'], 2, ',', '.'); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 text-success">
                        <span>(+) Vendas:</span>
                        <span>R$ <?php echo number_format($total_vendas, 2, ',', '.'); ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-4">
                        <span class="fw-bold">SALDO TOTAL:</span>
                        <span class="valor-destaque">R$ <?php echo number_format($saldo_final_esperado, 2, ',', '.'); ?></span>
                    </div>

                    <?php if ($erro_senha): ?>
                        <div class="alert alert-danger small text-center"><?php echo $erro_senha; ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-danger">Digite sua Senha para Confirmar:</label>
                            <input type="password" name="senha_fechamento" class="form-control form-control-lg text-center" placeholder="****" required>
                        </div>
                        <button type="submit" class="btn btn-danger btn-lg w-100 fw-bold shadow">ENCERRAR CAIXA</button>
                        <a href="vendas.php?caixa_id=<?php echo $caixa['id']; ?>" class="btn btn-link w-100 mt-3 text-muted">Cancelar e voltar</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>