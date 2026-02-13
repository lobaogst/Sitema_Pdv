<?php
include 'config.php';

$pedidos = [];
$tel_exibicao = "";

if (isset($_GET['telefone'])) {
    $tel_busca = preg_replace('/[^0-9]/', '', $_GET['telefone']);
    $tel_exibicao = $_GET['telefone'];
    
    // Busca as vendas e os detalhes de cada item (incluindo a foto_item da tabela itens_venda)
    $stmt = $pdo->prepare("SELECT v.*, iv.foto_item, p.nome as nome_produto 
                           FROM vendas v 
                           LEFT JOIN itens_venda iv ON v.id = iv.venda_id 
                           LEFT JOIN produtos p ON iv.produto_id = p.id
                           WHERE v.telefone_cliente = ? 
                           ORDER BY v.id DESC, iv.id ASC");
    $stmt->execute([$tel_busca]);
    
    // Organiza por ID da venda para não repetir o cabeçalho do pedido
    $resultados = $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC); 
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Pedidos - Moda Mais Barata</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f7f6; font-family: 'Segoe UI', sans-serif; }
        .card-pedido { border-radius: 15px; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.08); margin-bottom: 2rem; overflow: hidden; }
        .header-pedido { background: #f45d8e; color: white; padding: 15px; }
        .item-row { border-bottom: 1px solid #eee; padding: 15px; background: #fff; }
        .item-row:last-child { border-bottom: none; }
        .img-produto { 
            width: 80px; 
            height: 80px; 
            object-fit: cover; 
            border-radius: 10px; 
            cursor: pointer; 
            transition: 0.3s;
            border: 1px solid #ddd;
        }
        .img-produto:hover { transform: scale(1.05); }
        .status-badge { border-radius: 20px; padding: 5px 15px; font-size: 0.8rem; fw-bold; }
        #imgModalPreview { width: 100%; border-radius: 10px; }
    </style>
</head>
<body>

<div class="container py-4">
    <div class="text-center mb-4">
        <h2 class="fw-bold" style="color: #f45d8e;">🛍️ MEUS PEDIDOS</h2>
        <p class="text-muted">Acompanhe as suas compras abaixo</p>
    </div>

    <div class="row justify-content-center mb-5">
        <div class="col-md-6">
            <form method="GET" class="input-group">
                <input type="tel" name="telefone" class="form-control form-control-lg" placeholder="Digite seu WhatsApp..." value="<?php echo $tel_exibicao; ?>" required>
                <button class="btn btn-dark btn-lg" type="submit">BUSCAR</button>
            </form>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-10">
            <?php if (isset($resultados) && !empty($resultados)): ?>
                <?php foreach ($resultados as $venda_id => $itens): 
                    $venda = $itens[0]; // Dados principais da venda
                ?>
                    <div class="card card-pedido">
                        <div class="header-pedido d-flex justify-content-between align-items-center">
                            <span><strong>PEDIDO #<?php echo $venda_id; ?></strong></span>
                            <span class="badge bg-white text-dark status-badge text-uppercase">
                                <?php echo $venda['status_entrega'] ?? 'Pendente'; ?>
                            </span>
                        </div>
                        
                        <div class="card-body p-0">
                            <?php foreach ($itens as $item): ?>
                                <div class="item-row d-flex align-items-center">
                                    <div class="me-3">
                                        <?php if (!empty($item['foto_item'])): ?>
                                            <img src="uploads/<?php echo $item['foto_item']; ?>" 
                                                 class="img-produto" 
                                                 onclick="verFoto('uploads/<?php echo $item['foto_item']; ?>', '<?php echo $item['nome_produto']; ?>')"
                                                 alt="Foto do produto">
                                        <?php else: ?>
                                            <div class="bg-light text-muted d-flex align-items-center justify-content-center rounded" style="width:80px; height:80px; font-size: 10px; border: 1px dashed #ccc;">
                                                Sem Foto
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0 fw-bold"><?php echo $item['nome_produto']; ?></h6>
                                        <small class="text-muted">Forma de Pgto: <?php echo strtoupper($venda['forma_pagamento']); ?></small>
                                    </div>
                                    <div class="text-end">
                                        <span class="fw-bold text-pink">R$ <?php echo number_format($venda['total_venda'], 2, ',', '.'); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="card-footer bg-white border-0 p-3 text-center">
                            <small class="text-muted">Data da Compra: <?php echo date('d/m/Y H:i', strtotime($venda['data_venda'])); ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php elseif (isset($_GET['telefone'])): ?>
                <div class="alert alert-warning text-center">Nenhum pedido encontrado para este número.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="fotoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title small fw-bold" id="nomeProdutoModal">Visualizar Produto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-2">
                <img src="" id="imgModalPreview" class="img-fluid" alt="Zoom">
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function verFoto(url, nome) {
    document.getElementById('imgModalPreview').src = url;
    document.getElementById('nomeProdutoModal').innerText = nome;
    var myModal = new bootstrap.Modal(document.getElementById('fotoModal'));
    myModal.show();
}
</script>

</body>
</html>