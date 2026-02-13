<?php
include 'auth.php';
include 'config.php';

// Lógica para mudar o status via GET
if (isset($_GET['id']) && isset($_GET['novo_status'])) {
    $stmt = $pdo->prepare("UPDATE vendas SET status_entrega = ? WHERE id = ?");
    $stmt->execute([$_GET['novo_status'], $_GET['id']]);
    header("Location: producao.php");
    exit;
}

// SQL: Busca a venda incluindo nome, sobrenome e as fotos dos itens
$sql = "SELECT v.*, iv.foto_item, iv.observacao, p.nome as nome_produto 
        FROM vendas v 
        LEFT JOIN itens_venda iv ON v.id = iv.venda_id 
        LEFT JOIN produtos p ON iv.produto_id = p.id
        WHERE v.status_entrega != 'entregue' 
        ORDER BY v.id ASC";

$stmt = $pdo->query($sql);
$pedidos = $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produção com Fotos - Moda Mais Barata</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f0f2f5; font-size: 0.85rem; }
        .col-status { min-height: 85vh; background: #ebedef; border-radius: 12px; padding: 15px; margin-bottom: 20px; }
        .pedido-card { background: #fff; border-radius: 10px; padding: 15px; margin-bottom: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border-top: 5px solid #dee2e6; }
        .card-pendente { border-top-color: #ffc107; }
        .card-preparacao { border-top-color: #0dcaf0; }
        .card-pronto { border-top-color: #198754; }
        .header-status { text-align: center; font-weight: 800; margin-bottom: 15px; padding: 10px; border-radius: 8px; color: #fff; text-transform: uppercase; }
        .bg-pendente { background: #ffc107; color: #000 !important; }
        .bg-preparacao { background: #0dcaf0; }
        .bg-pronto { background: #198754; }
        
        /* Estilo das Fotos */
        .foto-miniatura { width: 60px; height: 60px; object-fit: cover; border-radius: 8px; cursor: pointer; border: 2px solid #ddd; transition: 0.2s; }
        .foto-miniatura:hover { transform: scale(1.1); border-color: #f45d8e; }
        .item-producao { display: flex; align-items: center; gap: 12px; margin-bottom: 10px; padding: 8px; background: #f9f9f9; border-radius: 8px; }
    </style>
</head>
<body>

<div class="container-fluid mt-3">
    <div class="d-flex justify-content-between align-items-center mb-4 px-3">
        <h3 class="fw-bold m-0">👨‍🍳 Produção e Fotos</h3>
        <div class="d-flex gap-2">
            <button onclick="window.location.reload()" class="btn btn-outline-primary btn-sm">🔄 Atualizar</button>
            <a href="vendas.php" class="btn btn-secondary btn-sm">Voltar</a>
        </div>
    </div>

    <div class="row g-3">
        <?php 
        $colunas = [
            'pendente' => ['titulo' => '🟡 RECEBIDOS', 'classe' => 'bg-pendente', 'borda' => 'card-pendente'],
            'em_preparacao' => ['titulo' => '🔵 PREPARANDO', 'classe' => 'bg-preparacao', 'borda' => 'card-preparacao'],
            'pronto' => ['titulo' => '🟢 PRONTOS', 'classe' => 'bg-pronto', 'borda' => 'card-pronto']
        ];

        foreach ($colunas as $status_chave => $info): ?>
            <div class="col-12 col-md-4">
                <div class="col-status">
                    <div class="header-status <?php echo $info['classe']; ?>"><?php echo $info['titulo']; ?></div>
                    
                    <?php foreach ($pedidos as $id_venda => $itens): 
                        $venda = $itens[0]; 
                        if ($venda['status_entrega'] == $status_chave): 
                    ?>
                        <div class="pedido-card <?php echo $info['borda']; ?>">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <span class="badge bg-dark">#<?php echo $id_venda; ?></span><br>
                                    <div class="fw-bold text-dark text-uppercase mt-1">
                                        <?php echo ($venda['nome_cliente']) ? $venda['nome_cliente'].' '.$venda['sobrenome_cliente'] : 'Cliente PDV'; ?>
                                    </div>
                                    <small class="text-primary fw-bold"><?php echo $venda['telefone_cliente']; ?></small>
                                </div>
                                <div class="text-end">
                                    <small class="d-block text-muted" style="font-size: 0.75rem;">
                                        📅 <?php echo date('d/m', strtotime($venda['data_venda'])); ?><br>
                                        ⏰ <?php echo date('H:i', strtotime($venda['data_venda'])); ?>
                                    </small>
                                </div>
                            </div>

                            <div class="container-itens mt-3">
                                <?php foreach ($itens as $it): ?>
                                    <div class="item-producao">
                                        <?php if (!empty($it['foto_item'])): ?>
                                            <img src="uploads/<?php echo $it['foto_item']; ?>" 
                                                 class="foto-miniatura" 
                                                 onclick="abrirFoto('uploads/<?php echo $it['foto_item']; ?>')" 
                                                 alt="Foto Item">
                                        <?php else: ?>
                                            <div style="width:60px; height:60px; background:#eee; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:10px; color:#999;">SEM FOTO</div>
                                        <?php endif; ?>

                                        <div class="flex-grow-1">
                                            <div class="fw-bold text-uppercase"><?php echo $it['nome_produto']; ?></div>
                                            <?php if (!empty($it['observacao'])): ?>
                                                <small class="text-danger d-block">Obs: <?php echo htmlspecialchars($it['observacao']); ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="mt-3 d-flex gap-1">
                                <button onclick="window.open('imprimir_etiqueta.php?id=<?php echo $id_venda; ?>', 'Etiqueta', 'width=400,height=600')" class="btn btn-warning btn-sm">🖨️</button>

                                <?php if ($status_chave == 'pendente'): ?>
                                    <a href="?id=<?php echo $id_venda; ?>&novo_status=em_preparacao" class="btn btn-info btn-sm w-100 text-white">INICIAR ➔</a>
                                <?php elseif ($status_chave == 'em_preparacao'): ?>
                                    <a href="?id=<?php echo $id_venda; ?>&novo_status=pronto" class="btn btn-success btn-sm w-100">PRONTO ➔</a>
                                <?php elseif ($status_chave == 'pronto'): ?>
                                    <a href="?id=<?php echo $id_venda; ?>&novo_status=entregue" class="btn btn-dark btn-sm w-100">FINALIZAR</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="modal fade" id="modalFoto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-transparent border-0">
            <div class="modal-body text-center p-0">
                <img src="" id="fotoGrande" class="img-fluid rounded shadow-lg" style="max-height: 90vh;">
                <button type="button" class="btn btn-light mt-3 fw-bold" data-bs-dismiss="modal">FECHAR</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function abrirFoto(src) {
    document.getElementById('fotoGrande').src = src;
    var myModal = new bootstrap.Modal(document.getElementById('modalFoto'));
    myModal.show();
}
</script>
</body>
</html>