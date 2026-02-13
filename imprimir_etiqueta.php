<?php
include 'auth.php';
include 'config.php';

// Função para limpar texto e evitar caracteres "bugados" em impressoras térmicas
function limparParaImpressora($texto) {
    if (empty($texto)) return "";
    $com_acento = ['á','à','â','ã','ä','é','è','ê','ë','í','ì','î','ï','ó','ò','ô','õ','ö','ú','ù','û','ü','ç','ñ','Á','À','Â','Ã','Ä','É','È','Ê','Ë','Í','Ì','Î','Ï','Ó','Ò','Ô','Õ','Ö','Ú','Ù','Û','Ü','Ç','Ñ'];
    $sem_acento = ['a','a','a','a','a','e','e','e','e','i','i','i','i','o','o','o','o','o','u','u','u','u','c','n','A','A','A','A','A','E','E','E','E','I','I','I','I','O','O','O','O','O','U','U','U','U','C','N'];
    $texto = str_replace($com_acento, $sem_acento, $texto);
    // Remove qualquer caractere que não seja letra, número, espaço ou pontuação básica
    return preg_replace('/[^a-zA-Z0-9\s\-\.\#\:\/]/', '', $texto);
}

$venda_id = $_GET['id'] ?? null;
if (!$venda_id) die("Venda nao encontrada.");

// Busca os dados da venda
$stmt = $pdo->prepare("SELECT * FROM vendas WHERE id = ?");
$stmt->execute([$venda_id]);
$venda = $stmt->fetch();

// Busca os itens da venda
$stmt = $pdo->prepare("SELECT iv.*, p.nome FROM itens_venda iv JOIN produtos p ON iv.produto_id = p.id WHERE iv.venda_id = ?");
$stmt->execute([$venda_id]);
$itens = $stmt->fetchAll();

// Extrai os últimos 4 dígitos do WhatsApp
$telefone = preg_replace('/[^0-9]/', '', $venda['telefone_cliente']);
$ultimos_quatro = substr($telefone, -4);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <style>
        body { 
            font-family: 'Courier New', Courier, monospace; 
            width: 260px; 
            margin: 0 auto; 
            text-align: center; 
            color: #000; 
            font-weight: bold; 
        }
        
        /* 4 dígitos gigantes */
        .zap-topo { 
            font-size: 85px; 
            font-weight: 900; 
            border: 10px solid #000; 
            margin-bottom: 5px; 
            line-height: 1; 
            padding: 5px;
        }
        
        .nome-cliente { 
            font-size: 24px; 
            text-transform: uppercase; 
            margin-bottom: 5px; 
            border-bottom: 4px solid #000; 
            padding-bottom: 5px; 
        }
        
        .info-tempo { 
            font-size: 16px; 
            margin-bottom: 10px; 
            background: #000; 
            color: #fff; 
            padding: 5px; 
            text-transform: uppercase;
        }
        
        .detalhes { 
            text-align: left; 
            font-size: 20px; 
            margin-top: 10px; 
            border-top: 2px dashed #000; 
            padding-top: 8px; 
        }
        
        .item { 
            margin-bottom: 10px; 
            border-bottom: 1px solid #ddd; 
            padding-bottom: 5px; 
        }

        /* AUMENTO DA OBSERVAÇÃO */
        .obs-destaque {
            font-size: 20px; /* Aumentado para o tamanho do nome do produto */
            background: #eee;
            display: block;
            padding: 2px;
            margin-top: 2px;
            border: 1px solid #000;
        }

        /* AUMENTO DO RODAPÉ */
        .rodape {
            font-size: 14px; /* Aumentado de 12px para 14px */
            margin-top: 20px;
            border-top: 2px solid #000;
            padding-top: 10px;
            text-transform: uppercase;
            font-weight: 900;
            line-height: 1.3;
        }

        .btn-print { 
            background: #f45d8e; 
            color: white; 
            border: none; 
            padding: 15px; 
            width: 100%; 
            font-weight: bold; 
            cursor: pointer; 
            border-radius: 5px; 
            margin-bottom: 10px; 
        }
        
        @media print { .btn-print { display: none; } body { width: 100%; } }
    </style>
</head>
<body>
    <button class="btn-print" onclick="imprimirEFechar()">IMPRIMIR ETIQUETA</button>

    <div class="zap-topo">
        <?= $ultimos_quatro ?: '0000' ?>
    </div>

    <?php if(!empty($venda['nome_cliente'])): ?>
        <div class="nome-cliente">
            <?= limparParaImpressora($venda['nome_cliente'] . ' ' . $venda['sobrenome_cliente']) ?>
        </div>
    <?php endif; ?>

    <div class="info-tempo">
        PEDIDO #<?= $venda_id ?> | <?= date('H:i', strtotime($venda['data_venda'])) ?>
    </div>

    <div class="detalhes">
        <?php foreach ($itens as $it): ?>
            <div class="item">
                <?= $it['quantidade'] ?>x <?= limparParaImpressora(strtoupper($it['nome'])) ?>
                <?php if(!empty($it['observacao'])): ?>
                    <span class="obs-destaque">
                        OBS: <?= limparParaImpressora(strtoupper($it['observacao'])) ?>
                    </span>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="rodape">
        7 DIAS PARA TROCA MEDIANTE A<br>APRESENTACAO DESTE CUPOM<br><br>
        *** OBRIGADO PELA PREFERENCIA ***
    </div>

    <script>
    function imprimirEFechar() {
        window.print();
        window.onafterprint = function() { window.close(); };
        setTimeout(function() { window.close(); }, 1500);
    }
    </script>
</body>
</html>