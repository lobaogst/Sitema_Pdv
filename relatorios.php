<?php
include 'auth.php';
include 'config.php';

// Captura os filtros
$inicio  = $_GET['inicio'] ?? date('Y-m-d');
$fim     = $_GET['fim']    ?? date('Y-m-d');
$metodo  = $_GET['metodo'] ?? 'todos';
$caixa_f = $_GET['caixa_id'] ?? '';

// 1. SQL Base - Busca vendas, itens e o nome do caixa
$query = "SELECT v.*, c.identificacao as nome_caixa 
          FROM vendas v 
          JOIN caixa c ON v.caixa_id = c.id 
          WHERE DATE(v.data_venda) BETWEEN :inicio AND :fim";

// 2. Filtro Dinâmico de Pagamento
if ($metodo !== 'todos') {
    $query .= " AND v.forma_pagamento = :metodo";
}

// 3. Filtro Dinâmico de Caixa (Aqui resolve o seu problema do Caixa Online)
if ($caixa_f !== '') {
    $query .= " AND v.caixa_id = :caixa_id";
}

$query .= " ORDER BY v.data_venda DESC";

$stmt = $pdo->prepare($query);
$stmt->bindValue(':inicio', $inicio);
$stmt->bindValue(':fim', $fim);

if ($metodo !== 'todos') $stmt->bindValue(':metodo', $metodo);
if ($caixa_f !== '') $stmt->bindValue(':caixa_id', $caixa_f);

$stmt->execute();
$vendas = $stmt->fetchAll();

// 4. Busca lista de caixas para o filtro
$lista_caixas = $pdo->query("SELECT id, identificacao FROM caixa ORDER BY identificacao ASC")->fetchAll();

$total_geral = 0;
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Relatórios de Vendas - Moda Mais Barata</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --primary-pink: #f45d8e; }
        body { background: #f8f9fa; font-family: sans-serif; }
        .sidebar { background: #fff; border-radius: 15px; padding: 20px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .btn-pink { background: var(--primary-pink); color: white; border: none; font-weight: bold; }
        .btn-pink:hover { background: #d64676; color: white; }
        .badge-online { background-color: #0d6efd; color: white; } /* Azul para destacar Vendas Online */
    </style>
</head>
<body>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">📊 Relatório de Vendas</h2>
        <a href="abrir_caixa.php" class="btn btn-outline-secondary">Voltar ao Painel</a>
    </div>

    <div class="sidebar mb-4">
        <form method="GET" class="row g-3">
            <div class="col-md-2">
                <label class="form-label small fw-bold">Data Início</label>
                <input type="date" name="inicio" class="form-control" value="<?php echo $inicio; ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Data Fim</label>
                <input type="date" name="fim" class="form-control" value="<?php echo $fim; ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold">Filtrar por Caixa</label>
                <select name="caixa_id" class="form-select">
                    <option value="">Todos os Caixas</option>
                    <?php foreach($lista_caixas as $cx): ?>
                        <option value="<?php echo $cx['id']; ?>" <?php echo ($caixa_f == $cx['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cx['identificacao']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold">Pagamento</label>
                <select name="metodo" class="form-select">
                    <option value="todos">Todos os Métodos</option>
                    <option value="dinheiro" <?php if($metodo=='dinheiro') echo 'selected'; ?>>Dinheiro</option>
                    <option value="pix" <?php if($metodo=='pix') echo 'selected'; ?>>PIX</option>
                    <option value="debito" <?php if($metodo=='debito') echo 'selected'; ?>>Cartão Débito</option>
                    <option value="credito" <?php if($metodo=='credito') echo 'selected'; ?>>Cartão Crédito</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-pink w-100 py-2">FILTRAR</button>
            </div>
        </form>
    </div>

    <div class="table-responsive bg-white rounded shadow-sm p-3">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Data/Hora</th>
                    <th>Caixa Operador</th>
                    <th>Pagamento</th>
                    <th class="text-end">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($vendas) > 0): ?>
                    <?php foreach($vendas as $v): 
                        $total_geral += $v['total_venda'];
                        // Verifica se o nome do caixa indica que é ONLINE para mudar a cor do badge
                        $isOnline = (strpos(strtoupper($v['nome_caixa']), 'ONLINE') !== false);
                    ?>
                    <tr>
                        <td>#<?php echo $v['id']; ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($v['data_venda'])); ?></td>
                        <td>
                            <span class="badge <?php echo $isOnline ? 'badge-online' : 'bg-light text-dark border'; ?>">
                                <?php echo htmlspecialchars($v['nome_caixa']); ?>
                            </span>
                        </td>
                        <td><span class="badge bg-info text-dark"><?php echo strtoupper($v['forma_pagamento']); ?></span></td>
                        <td class="text-end fw-bold text-dark">R$ <?php echo number_format($v['total_venda'], 2, ',', '.'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">Nenhum registro encontrado para este filtro.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
            <tfoot class="table-secondary">
                <tr>
                    <td colspan="4" class="text-end fw-bold">TOTAL ACUMULADO NO PERÍODO:</td>
                    <td class="text-end fw-bold fs-5 text-success">R$ <?php echo number_format($total_geral, 2, ',', '.'); ?></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="mt-3 text-end">
        <button onclick="window.print()" class="btn btn-dark">🖨️ Imprimir Relatório</button>
    </div>
</div>

</body>
</html>