<?php
include 'auth.php';
include 'config.php';

// Busca o CAIXA ONLINE
$stmt = $pdo->query("SELECT id, identificacao FROM caixa WHERE status = 'aberto' AND identificacao LIKE '%ONLINE%' LIMIT 1");
$caixa = $stmt->fetch();

if (!$caixa) { 
    echo "<script>alert('Abra o CAIXA ONLINE primeiro!'); window.location='abrir_caixa.php';</script>"; 
    exit; 
}

$lista_produtos = $pdo->query("SELECT * FROM produtos WHERE estoque > 0 ORDER BY nome ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $telefone = preg_replace('/[^0-9]/', '', $_POST['telefone']);
    $nome = $_POST['nome_cliente'] ?? null;
    $sobrenome = $_POST['sobrenome_cliente'] ?? null;
    $pagamento = $_POST['forma_pagamento'];
    $total_venda = (float)$_POST['total_venda_input'];
    
    try {
        $pdo->beginTransaction();
        
        // Insere a venda com Nome e Sobrenome
        $sql = "INSERT INTO vendas (caixa_id, total_venda, forma_pagamento, telefone_cliente, nome_cliente, sobrenome_cliente, status_entrega) VALUES (?, ?, ?, ?, ?, ?, 'pendente')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$caixa['id'], $total_venda, $pagamento, $telefone, $nome, $sobrenome]);
        $venda_id = $pdo->lastInsertId();

        if (isset($_POST['produtos_ids'])) {
            foreach ($_POST['produtos_ids'] as $idx => $prod_id) {
                $observacao = $_POST['obs_itens'][$idx] ?? null; // Captura observação do item
                $foto_nome = null;

                // Processa upload da foto
                if (isset($_FILES['fotos_itens']['name'][$idx]) && $_FILES['fotos_itens']['error'][$idx] == 0) {
                    $diretorio = "uploads/";
                    if (!is_dir($diretorio)) mkdir($diretorio, 0777, true);
                    $extensao = pathinfo($_FILES['fotos_itens']['name'][$idx], PATHINFO_EXTENSION);
                    $foto_nome = "online_" . $venda_id . "_" . $idx . "_" . time() . "." . $extensao;
                    move_uploaded_file($_FILES['fotos_itens']['tmp_name'][$idx], $diretorio . $foto_nome);
                }

                $p_stmt = $pdo->prepare("SELECT preco FROM produtos WHERE id = ?");
                $p_stmt->execute([$prod_id]);
                $p_info = $p_stmt->fetch();

                // Insere item com Foto e Observação
                $sql_item = "INSERT INTO itens_venda (venda_id, produto_id, quantidade, preco_unitario, foto_item, observacao) VALUES (?, ?, 1, ?, ?, ?)";
                $pdo->prepare($sql_item)->execute([$venda_id, $prod_id, $p_info['preco'], $foto_nome, $observacao]);
                
                // Baixa no estoque
                $pdo->prepare("UPDATE produtos SET estoque = estoque - 1 WHERE id = ?")->execute([$prod_id]);
            }
        }
        $pdo->commit();
        
        // Pergunta se deseja imprimir a etiqueta logo após a venda
        echo "<script>
            if(confirm('Venda Registrada! Deseja imprimir a etiqueta agora?')){
                window.open('imprimir_etiqueta.php?id=$venda_id', 'Etiqueta', 'width=400,height=600');
            }
            window.location='venda_online.php';
        </script>";
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Erro: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Venda Online - Moda Mais Barata</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --primary-pink: #f45d8e; }
        body { background: #fff5f8; font-family: 'Segoe UI', sans-serif; }
        .prod-card { background: var(--primary-pink); color: white; border-radius: 15px; padding: 10px; text-align: center; cursor: pointer; transition: 0.2s; height: 100%; display: flex; flex-direction: column; justify-content: center; border: 2px solid transparent; }
        .prod-card:active { transform: scale(0.95); background: #d44a77; }
        .item-selecionado { background: white; border-radius: 15px; padding: 12px; margin-bottom: 10px; border-left: 5px solid var(--primary-pink); box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .scroll-produtos { max-height: 250px; overflow-y: auto; padding: 10px; background: #fff; border-radius: 15px; border: 1px solid #ddd; margin-bottom: 20px; }
        .btn-pink { background: var(--primary-pink); color: white; border: none; font-weight: bold; border-radius: 10px; }
        .text-pink { color: var(--primary-pink) !important; }
    </style>
</head>
<body>

<div class="container py-3">
    <div class="text-center mb-3">
        <h4 class="text-pink fw-bold">💖 CAIXA ONLINE</h4>
    </div>

    <form method="POST" enctype="multipart/form-data" id="formOnline">
        <div class="row g-2 mb-2">
            <div class="col">
                <input type="text" name="nome_cliente" class="form-control" placeholder="Nome">
            </div>
            <div class="col">
                <input type="text" name="sobrenome_cliente" class="form-control" placeholder="Sobrenome">
            </div>
        </div>

        <div class="mb-3">
            <input type="tel" name="telefone" class="form-control" placeholder="WhatsApp (Obrigatório)" required>
        </div>

        <label class="small fw-bold text-muted mb-1">PRODUTOS (CLIQUE PARA ADD):</label>
        <div class="scroll-produtos">
            <div class="row g-2">
                <?php foreach($lista_produtos as $p): ?>
                    <div class="col-4">
                        <div class="prod-card" onclick="adicionarItem('<?= $p['id'] ?>', '<?= addslashes($p['nome']) ?>', <?= $p['preco'] ?>)">
                            <div style="font-size: 0.8rem; font-weight: bold;"><?= $p['nome'] ?></div>
                            <div style="font-size: 0.75rem;">R$<?= number_format($p['preco'], 0) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div id="lista_itens" class="mb-3">
            </div>

        <div class="card p-3 shadow-sm border-0">
            <select name="forma_pagamento" id="forma_pagamento" class="form-select mb-3" required onchange="recalcularTotal()">
                <option value="pix">PIX</option>
                <option value="dinheiro">Dinheiro</option>
                <option value="debito">Débito (+R$5)</option>
                <option value="credito">Crédito (+R$5)</option>
            </select>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="fw-bold">TOTAL:</span>
                <h3 class="text-pink fw-bold mb-0">R$ <span id="label_total">0,00</span></h3>
            </div>

            <input type="hidden" name="total_venda_input" id="total_venda_input" value="0">
            <button type="submit" class="btn btn-pink btn-lg w-100 py-3">FINALIZAR E GERAR ETIQUETA</button>
        </div>
    </form>
</div>

<script>
let contador = 0;

function adicionarItem(id, nome, preco) {
    const container = document.getElementById('lista_itens');
    const div = document.createElement('div');
    div.className = 'item-selecionado';
    div.id = 'item_' + contador;
    
    div.innerHTML = `
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="fw-bold text-pink">${nome}</span>
            <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="removerItem(${contador})">✕</button>
        </div>
        <div class="row g-1">
            <div class="col-6">
                <label class="small text-muted">Foto da Peça:</label>
                <input type="file" name="fotos_itens[${contador}]" class="form-control form-control-sm" accept="image/*">
            </div>
            <div class="col-6">
                <label class="small text-muted">Obs (Cor/Tam):</label>
                <input type="text" name="obs_itens[${contador}]" class="form-control form-control-sm" placeholder="Ex: G Azul">
            </div>
        </div>
        <input type="hidden" name="produtos_ids[${contador}]" value="${id}">
        <input type="hidden" class="item-preco" value="${preco}">
    `;
    
    container.appendChild(div);
    contador++;
    recalcularTotal();
}

function removerItem(id) {
    document.getElementById('item_' + id).remove();
    recalcularTotal();
}

function recalcularTotal() {
    let total = 0;
    document.querySelectorAll('.item-preco').forEach(p => total += parseFloat(p.value));
    const fp = document.getElementById('forma_pagamento').value;
    if((fp === 'debito' || fp === 'credito') && total > 0) total += 5;
    document.getElementById('label_total').innerText = total.toLocaleString('pt-br', {minimumFractionDigits: 2});
    document.getElementById('total_venda_input').value = total;
}
</script>

</body>
</html>