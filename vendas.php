<?php
include 'auth.php';
include 'config.php';

// 1. CAPTURA O CAIXA SELECIONADO
$caixa_id_url = $_GET['caixa_id'] ?? ($_POST['caixa_id_venda'] ?? null);

if ($caixa_id_url) {
    $stmt = $pdo->prepare("SELECT * FROM caixa WHERE id = ? AND status = 'aberto'");
    $stmt->execute([$caixa_id_url]);
    $caixa = $stmt->fetch();
} else {
    $stmt = $pdo->query("SELECT * FROM caixa WHERE status = 'aberto' LIMIT 1");
    $caixa = $stmt->fetch();
}

$caixa_aberto = $caixa ? true : false;

// 2. PROCESSA A VENDA
$venda_concluida_id = null;
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['produtos_id']) && $caixa_aberto) {
    try {
        $pdo->beginTransaction();
        $venda_stmt = $pdo->prepare("INSERT INTO vendas (caixa_id, total_venda, forma_pagamento, status_entrega) VALUES (?, 0, ?, 'imediata')");
        $venda_stmt->execute([$caixa['id'], $_POST['forma_pagamento']]);
        $venda_id = $pdo->lastInsertId();
        $total_venda = 0;
        
        $forma_pgto = $_POST['forma_pagamento'];
        $taxa_item = ($forma_pgto === 'credito' || $forma_pgto === 'debito') ? 5.00 : 0;

        foreach ($_POST['produtos_id'] as $key => $id_prod) {
            $qtd = $_POST['quantidades'][$key];
            $p = $pdo->prepare("SELECT preco FROM produtos WHERE id = ?");
            $p->execute([$id_prod]);
            $prod = $p->fetch();
            
            $preco_final = $prod['preco'] + $taxa_item;
            $subtotal = $preco_final * $qtd;
            $total_venda += $subtotal;

            $pdo->prepare("INSERT INTO itens_venda (venda_id, produto_id, quantidade, preco_unitario) VALUES (?, ?, ?, ?)")
                ->execute([$venda_id, $id_prod, $qtd, $preco_final]);
            
            $pdo->prepare("UPDATE produtos SET estoque = estoque - ? WHERE id = ?")
                ->execute([$qtd, $id_prod]);
        }

        $pdo->prepare("UPDATE vendas SET total_venda = ? WHERE id = ?")->execute([$total_venda, $venda_id]);
        $pdo->commit();
        $venda_concluida_id = $venda_id; 
    } catch (Exception $e) { $pdo->rollBack(); }
}

$lista_produtos = $pdo->query("SELECT * FROM produtos WHERE estoque > 0 ORDER BY nome ASC")->fetchAll();
$ultimas_vendas = $caixa_aberto ? $pdo->prepare("SELECT * FROM vendas WHERE caixa_id = ? ORDER BY id DESC LIMIT 5") : [];
if($caixa_aberto && $ultimas_vendas) { $ultimas_vendas->execute([$caixa['id']]); $ultimas_vendas = $ultimas_vendas->fetchAll(); }
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>PDV DARK - Moda Mais Barata</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style> 
        :root { --primary-pink: #f45d8e; }
        body { background: #1a1a1a; color: #e0e0e0; font-family: 'Segoe UI', sans-serif; overflow-x: hidden; }
        .navbar { background-color: #fff !important; border-bottom: 3px solid var(--primary-pink); }
        .navbar-brand { color: var(--primary-pink) !important; font-weight: bold; text-transform: uppercase; }
        .card-produto-destaque { background: #252525; border: 4px dashed #444; border-radius: 25px; padding: 60px; text-align: center; cursor: pointer; transition: 0.3s; }
        .card-produto-destaque:hover { border-color: var(--primary-pink); background: #2d2d2d; }
        .preco-destaque { font-size: 4rem; color: var(--primary-pink); font-weight: bold; }
        .btn-pink { background-color: var(--primary-pink); color: white; border: none; font-weight: bold; }
        .carrinho-painel { background: #fff; color: #333; border-radius: 20px; height: 85vh; display: flex; flex-direction: column; }
        .text-pink { color: var(--primary-pink) !important; }
        .log-container { background: #212121; border-radius: 20px; border: 1px solid var(--primary-pink); overflow: hidden; margin-top: 20px; }
    </style>
</head>
<body>

<nav class="navbar navbar-light px-4 py-2 shadow-sm">
    <div class="container-fluid d-flex justify-content-between">
        <a class="navbar-brand" href="abrir_caixa.php">MODA MAIS BARATA</a>
        <a href="abrir_caixa.php" class="btn btn-danger btn-sm">Sair</a>
    </div>
</nav>

<div class="container-fluid py-4">
    <div class="row g-4">
        <div class="col-12 col-lg-7">
            <?php if($lista_produtos): $prod = $lista_produtos[0]; ?>
                <div class="card-produto-destaque shadow-lg" id="btn-add-f1" onclick="addCarrinho('<?= $prod['id'] ?>', '<?= $prod['nome'] ?>', <?= $prod['preco'] ?>)">
                    <h1 class="fw-bold mb-2 text-white"><?= $prod['nome'] ?></h1>
                    <div class="preco-destaque">R$ <?= number_format($prod['preco'], 2, ',', '.') ?></div>
                    <div class="btn btn-pink btn-lg w-100 mt-4 py-3 shadow">➕ ADICIONAR ITEM (F1)</div>
                </div>
            <?php endif; ?>

            <div class="log-container shadow-lg">
                <div class="p-2 px-4" style="background: var(--primary-pink); color: white; font-weight: bold;">📜 ÚLTIMAS VENDAS</div>
                <table class="table table-dark table-hover mb-0">
                    <thead><tr><th class="ps-4">ID</th><th>HORA</th><th>VALOR</th><th class="text-center">AÇÕES</th></tr></thead>
                    <tbody>
                        <?php foreach($ultimas_vendas as $v): ?>
                        <tr>
                            <td class="ps-4 text-secondary">#<?= $v['id'] ?></td>
                            <td><?= date('H:i', strtotime($v['data_venda'])) ?></td>
                            <td class="fw-bold text-pink">R$ <?= number_format($v['total_venda'], 2, ',', '.') ?></td>
                            <td class="text-center">
                                <button onclick="reimprimirVenda(<?= $v['id'] ?>)" class="btn btn-sm p-1" title="Imprimir">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#f45d8e" viewBox="0 0 16 16"><path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/><path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z"/></svg>
                                </button>
                                <button onclick="excluirVenda(<?= $v['id'] ?>)" class="btn btn-sm p-1" title="Excluir">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#dc3545" viewBox="0 0 16 16"><path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/><path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/></svg>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="col-12 col-lg-5">
            <div class="carrinho-painel shadow-lg">
                <div class="p-3 bg-light border-bottom fw-bold fs-5 text-dark">CARRINHO</div>
                <div class="p-3 flex-grow-1 overflow-auto">
                    <table class="table table-sm" id="tabela_venda">
                        <thead><tr><th>ITEM</th><th>QTD</th><th class="text-end">VALOR</th><th></th></tr></thead>
                        <tbody></tbody>
                    </table>
                </div>
                <div class="p-3 border-top bg-light">
                    <form id="form-venda" method="POST" onsubmit="return confirmarVenda()">
                        <input type="hidden" name="caixa_id_venda" value="<?= $caixa['id'] ?? '' ?>">
                        <label class="small fw-bold text-dark">FORMA DE PAGAMENTO:</label>
                        <select id="forma_pagamento" name="forma_pagamento" class="form-select mb-3" required onchange="calcularTotal()">
                            <option value="" disabled selected>Selecione...</option>
                            <option value="pix">1 - 📱 PIX</option>
                            <option value="dinheiro">2 - 💵 Dinheiro</option>
                            <option value="debito">3 - 💳 Débito (+ R$ 5/item)</option>
                            <option value="credito">4 - 💳 Crédito (+ R$ 5/item)</option>
                        </select>
                        <div id="inputs-hidden-container"></div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="h4 mb-0 text-dark">TOTAL:</span>
                            <span class="h2 mb-0 text-pink fw-bold">R$ <span id="total_exibido">0,00</span></span>
                        </div>
                        <button type="submit" class="btn btn-pink btn-lg w-100 py-3 shadow">FINALIZAR (F2)</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Abertura Automática do Cupom
<?php if ($venda_concluida_id): ?>
    const caixaId = '<?= $caixa['id'] ?>';
    window.open('imprimir_cupom_' + caixaId + '.php?id=<?= $venda_concluida_id ?>', 'Cupom', 'width=400,height=600');
    window.location.href = 'vendas.php?caixa_id=<?= $caixa['id'] ?>';
<?php endif; ?>

let totalGlobal = 0;

function addCarrinho(id, nome, preco) {
    const tbody = document.querySelector('#tabela_venda tbody');
    const row = document.createElement('tr');
    row.innerHTML = `<td class="fw-bold">${nome}</td>
    <td><input type="number" value="1" min="1" class="form-control form-control-sm qtd" style="width:60px;" onchange="calcularTotal()"></td>
    <td class="text-end fw-bold text-pink">R$<span class="subtotal">0.00</span></td>
    <td class="text-center"><button type="button" class="btn btn-sm text-danger" onclick="this.closest('tr').remove(); calcularTotal();">✕</button></td>
    <input type="hidden" class="prod_id_val" value="${id}"><input type="hidden" class="preco_unit" value="${preco}">`;
    tbody.prepend(row);
    calcularTotal();
}

function calcularTotal() {
    let total = 0;
    const fp = document.getElementById('forma_pagamento').value;
    const taxaItem = (fp === 'credito' || fp === 'debito') ? 5.00 : 0;
    const container = document.getElementById('inputs-hidden-container');
    container.innerHTML = "";

    document.querySelectorAll('#tabela_venda tbody tr').forEach(row => {
        const qtd = parseInt(row.querySelector('.qtd').value) || 0;
        const precoBase = parseFloat(row.querySelector('.preco_unit').value);
        const precoComTaxa = precoBase + taxaItem;
        const subtotal = qtd * precoComTaxa;
        row.querySelector('.subtotal').innerText = subtotal.toFixed(2);
        total += subtotal;
        container.innerHTML += `<input type="hidden" name="produtos_id[]" value="${row.querySelector('.prod_id_val').value}"><input type="hidden" name="quantidades[]" value="${qtd}">`;
    });
    totalGlobal = total;
    document.getElementById('total_exibido').innerText = total.toLocaleString('pt-br', {minimumFractionDigits: 2});
}

function confirmarVenda() {
    if (document.querySelectorAll('#tabela_venda tbody tr').length === 0) { alert("Carrinho vazio!"); return false; }
    if (document.getElementById('forma_pagamento').value === "") { alert("Selecione o pagamento!"); return false; }
    return confirm("VALOR TOTAL: R$ " + totalGlobal.toLocaleString('pt-br', {minimumFractionDigits: 2}) + "\n\nConfirmar venda?");
}

function reimprimirVenda(id) {
    window.open('imprimir_cupom_<?= $caixa['id'] ?>.php?id=' + id, 'Cupom', 'width=400,height=600');
}

function excluirVenda(id) {
    const senha = prompt("SENHA DE EXCLUSÃO:");
    if (senha === "006900") {
        if (confirm("Excluir venda #" + id + "?")) {
            window.location.href = "excluir_venda.php?id=" + id + "&caixa_id=<?= $caixa['id'] ?>";
        }
    } else if (senha !== null) alert("Senha incorreta!");
}

document.addEventListener('keydown', e => {
    if (e.key === 'F1') { e.preventDefault(); document.getElementById('btn-add-f1').click(); }
    if (e.key === 'F2') { e.preventDefault(); document.getElementById('form-venda').requestSubmit(); }
    if (e.target.tagName !== 'INPUT') {
        const select = document.getElementById('forma_pagamento');
        if (e.key === '1') { select.value = 'pix'; calcularTotal(); }
        else if (e.key === '2') { select.value = 'dinheiro'; calcularTotal(); }
        else if (e.key === '3') { select.value = 'debito'; calcularTotal(); }
        else if (e.key === '4') { select.value = 'credito'; calcularTotal(); }
    }
});
</script>
</body>
</html>