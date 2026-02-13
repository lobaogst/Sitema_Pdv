<?php
include 'auth.php';
include 'config.php';

$venda_id = $_GET['id'] ?? null;
if (!$venda_id) die("Venda não encontrada.");

// Busca os dados da venda
$stmt = $pdo->prepare("SELECT * FROM vendas WHERE id = ?");
$stmt->execute([$venda_id]);
$venda = $stmt->fetch();

// Busca os itens da venda
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
        body { 
            font-family: 'Arial', sans-serif; 
            width: 280px; 
            margin: 0 auto; 
            padding: 10px; 
            font-size: 16px; 
            color: #000;
            ;
            text-shadow: 0.3px 0 0 #000;
            line-height: 1.1;
font-weight: 900
        }
        
        .header { text-align: center; border-bottom: 3px solid #000; padding-bottom: 5px; margin-bottom: 10px; }
        .loja { font-size: 22px; text-transform: uppercase; }
        .item-linha { display: flex; justify-content: space-between; margin-bottom: 8px; }
        .total-area { border-top: 3px solid #000; margin-top: 10px; padding-top: 5px; font-size: 20px; }
        .flex-space { display: flex; justify-content: space-between; }

        /* Estilo padronizado com o tamanho da DATA (12px) */
        .info-rodape {
            font-size: 15px; /* Mesmo tamanho da data solicitado */
            font-weight: normal;
            text-shadow: none;
            margin-top: 5px;
	font-weight: 500
        }

        /* 4 Linhas em branco para o corte */
        .espaco-final {
            height: 80px; 
        }

        @media print { 
            .btn-print { display: none; } 
            body { width: 100%; margin: 0; }
        }
    </style>
</head>
<body">
   <div class="btn-print" style="margin-top: 10px;">
        <button onclick="window.print()" style="width: 100%; padding: 10px; font-weight: bold;">IMPRIMIR</button>
        <button onclick="window.close()" style="width: 100%; padding: 10px; margin-top: 5px;">FECHAR</button>
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
            Rua Caetes 252 centro BH/MG<br><br>
            7 DIAS PARA TROCA MEDIANTE A APRESENTAÇÃO DESTE CUPOM<br><br>
            *** OBRIGADO PELA PREFERENCIA ***  <br><br>.<br>.      </div>
    </div>

    <div class="espaco-final"></div>



</body>
</html>