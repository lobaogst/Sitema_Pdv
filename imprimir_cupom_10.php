<?php
include 'auth.php';
include 'config.php';

$venda_id = $_GET['id'] ?? null;
if (!$venda_id) die("Venda não encontrada.");

$stmt = $pdo->prepare("SELECT * FROM vendas WHERE id = ?");
$stmt->execute([$venda_id]);
$venda = $stmt->fetch();

$stmt = $pdo->prepare("SELECT iv.*, p.nome FROM itens_venda iv JOIN produtos p ON iv.produto_id = p.id WHERE iv.venda_id = ?");
$stmt->execute([$venda_id]);
$itens = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cupom - MODA MAIS BARATA</title>
    <style>
        body { font-family: 'Arial', sans-serif; width: 280px; margin: 0 auto; padding: 10px; font-size: 16px; color: #000; text-shadow: 0.3px 0 0 #000; line-height: 1.1; font-weight: 900; }
        .header { text-align: center; border-bottom: 3px solid #000; padding-bottom: 5px; margin-bottom: 10px; }
        .loja { font-size: 22px; text-transform: uppercase; }
        .item-linha { display: flex; justify-content: space-between; margin-bottom: 8px; }
        .total-area { border-top: 3px solid #000; margin-top: 10px; padding-top: 5px; font-size: 20px; }
        .flex-space { display: flex; justify-content: space-between; }
        .info-rodape { font-size: 15px; font-weight: 500; text-shadow: none; margin-top: 5px; }
        .espaco-final { height: 80px; }
        
        /* Botões Rosa */
        .btn-print-container { margin-top: 10px; margin-bottom: 20px; text-align: center; }
        .btn-pink-print { background-color: #f45d8e; color: white; border: none; border-radius: 5px; padding: 15px; font-weight: bold; width: 100%; cursor: pointer; text-transform: uppercase; font-size: 18px; }
        .btn-close { background: #444; color: white; border: none; border-radius: 5px; padding: 10px; width: 100%; margin-top: 8px; cursor: pointer; }
        
        @media print { .btn-print-container { display: none; } body { width: 100%; margin: 0; } }
    </style>
</head>
<body>
   <div class="btn-print-container">
        <button onclick="imprimirAgora()" class="btn-pink-print">IMPRIMIR E FECHAR</button>
        <button onclick="window.close()" class="btn-close">SÓ FECHAR</button>
    </div>

    <div class="header">
        <div class="loja">MODA MAIS BARATA</div>
        <div style="font-size: 12px; margin-top: 5px;">
            PEDIDO: #<?php echo $venda['id']; ?><br>
            DATA: <?php echo date('d/m/Y H:i'); ?>
        </div>
    </div>

    <div style="margin-bottom: 5px;">PRODUTOS:</div>
    
    <?php foreach ($itens as $item): ?>
        <div class="item-linha">
            <span><?php echo $item['quantidade']; ?>x <?php echo strtoupper($item['nome']); ?></span>
            <span>R$ <?php echo number_format($item['preco_unitario'] * $item['quantidade'], 2, ',', '.'); ?></span>
        </div>
    <?php endforeach; ?>

    <div class="total-area">
        <?php if ($venda['forma_pagamento'] == 'credito' || $venda['forma_pagamento'] == 'debito'): ?>
            <div class="flex-space" style="font-size: 14px;">
                <span>TAXA CARTÃO:</span>
                <span>R$ 5,00</span>
            </div>
        <?php endif; ?>
        <div class="flex-space">
            <span>TOTAL:</span>
            <span>R$ <?php echo number_format($venda['total_venda'], 2, ',', '.'); ?></span>
        </div>
    </div>

    <div style="text-align: center; margin-top: 25px;">
        <div class="info-rodape">
            PAGAMENTO: <?php echo strtoupper($venda['forma_pagamento']); ?><br>
            Avenida Afonso pena 749 centro BH/MG<br><br>
            7 DIAS PARA TROCA MEDIANTE A APRESENTAÇÃO DESTE CUPOM<br><br>
            *** OBRIGADO PELA PREFERENCIA ***<br>.<br>.<br>.
        </div>
    </div>
    <div class="espaco-final"></div>

<script>
function imprimirAgora() {
    // 1. Abre a caixa de impressão
    window.print();

    // 2. Tenta fechar imediatamente após a caixa de diálogo ser fechada (Imprimir ou Cancelar)
    window.onafterprint = function() {
        window.close();
    };

    // 3. Reforço: Se após 1 segundo a página ainda estiver aberta, tenta fechar quando ganhar foco
    setTimeout(function() {
        window.close(); // Tenta fechar forçado
        
        // Se ainda não fechou, fecha assim que clicar na tela
        window.onfocus = function() {
            window.close();
        };
    }, 1000);
}
</script>
</body>
</html>