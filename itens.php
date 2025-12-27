<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once(__DIR__ . '/cabecalho.php');

// Forçar exceções no PDO
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Obter URL da categoria
$url_categoria = trim($_GET['url'] ?? '');
if (empty($url_categoria)) {
    http_response_code(400);
    echo "<p style='padding:20px; background:#f8d7da; color:#721c24;'>Erro: URL da categoria não fornecida.</p>";
    exit;
}

// 1. Buscar categoria ATIVA
$stmt_cat = $pdo->prepare("SELECT id, nome FROM categorias WHERE url = :url AND ativo = 'Sim' LIMIT 1");
$stmt_cat->bindValue(':url', $url_categoria, PDO::PARAM_STR);
$stmt_cat->execute();
$categoria = $stmt_cat->fetch(PDO::FETCH_ASSOC);

if (!$categoria) {
    http_response_code(404);
    echo "<p style='padding:20px; background:#fff3cd; color:#856404;'>Categoria não encontrada ou inativa.</p>";
    exit;
}

$id_categoria = (int) $categoria['id'];
$nome_categoria = htmlspecialchars($categoria['nome']);

// Limpar temporários (opcional)
$sessao = $_SESSION['sessao_usuario'] ?? null;
if ($sessao) {
    $stmt_temp = $pdo->prepare("DELETE FROM temp WHERE carrinho = 0 AND sessao = :sessao");
    $stmt_temp->bindValue(':sessao', $sessao, PDO::PARAM_STR);
    $stmt_temp->execute();
}

if (!empty($_SESSION['nome_mesa'])) {
    unset($_SESSION['pedido_balcao']);
}
?>

<!-- Conteúdo da página -->
<div class="main-container">
    <div class="container">
        <nav class="navbar bg-light fixed-top" style="box-shadow: 0px 3px 5px rgba(0, 0, 0, 0.20);">
            <div class="container-fluid">
                <div class="navbar-brand">
                    <a href="index.php"><big><i class="bi bi-arrow-left"></i></big></a>
                    <span style="margin-left: 15px; font-size:14px">
                        <?php echo mb_strtoupper($nome_categoria); ?>
                        <?php if (!empty($_SESSION['nome_mesa'])): ?>
                            &nbsp;- Mesa: <?php echo htmlspecialchars($_SESSION['nome_mesa']); ?>
                        <?php endif; ?>
                    </span>
                </div>
                <?php require_once(__DIR__ . '/icone-carrinho.php'); ?>
            </div>
        </nav>

        <ol class="list-group" style="margin-top: 65px">
            <?php
            // 2. Buscar produtos ativos nesta categoria - CÓDIGO OTIMIZADO
            $stmt_prod = $pdo->prepare("
                SELECT 
                    id, nome, descricao, url, foto, estoque, tem_estoque, 
                    valor_venda, promocao, delivery, valor_promocional
                FROM produtos 
                WHERE 
                    categoria = :categoria_id 
                    AND ativo = 1
                    AND (delivery = 'Sim' OR :tem_mesa = 1)
                ORDER BY valor_venda ASC
            ");
            $stmt_prod->bindValue(':categoria_id', $id_categoria, PDO::PARAM_INT);
            $stmt_prod->bindValue(':tem_mesa', !empty($_SESSION['nome_mesa']) ? 1 : 0, PDO::PARAM_INT);
            $stmt_prod->execute();
            
            if ($stmt_prod->rowCount() === 0) {
                echo '<li class="list-group-item text-center" style="padding:30px;">Nenhum produto disponível nesta categoria.</li>';
            } else {
                while ($item = $stmt_prod->fetch(PDO::FETCH_ASSOC)) {
                    $id_produto = (int) $item['id'];
                    $foto = !empty($item['foto']) ? preg_replace('/[^a-zA-Z0-9._-]/', '', $item['foto']) : '';
                    $nome_produto = htmlspecialchars($item['nome']);
                    $descricao_produto = htmlspecialchars($item['descricao']);
                    $url_produto_link = $item['url'];
                    $estoque = (int) $item['estoque'];

                    // ✅ CORREÇÃO CRÍTICA: Verificar o tipo real do campo 'tem_estoque'
                    // Pela sua tabela, parece ser VARCHAR com '1' ou '0'
                    $tem_estoque_valor = trim($item['tem_estoque']);
                    
                    // Tratamento seguro para diferentes formatos
                    if (in_array(strtolower($tem_estoque_valor), ['1', 'sim', 's', 'true', 'yes'])) {
                        $tem_estoque = 'Sim';
                    } elseif (in_array(strtolower($tem_estoque_valor), ['0', 'nao', 'não', 'n', 'false', 'no'])) {
                        $tem_estoque = 'Nao';
                    } else {
                        // Default seguro
                        $tem_estoque = 'Nao';
                    }

                    $delivery = (strtolower(trim($item['delivery'] ?? '')) === 'sim') ? 'Sim' : 'Nao';
                    $promocao = (strtolower(trim($item['promocao'] ?? '')) === 'sim') ? 'Sim' : 'Nao';

                    // Normalizar valores monetários
                    $valor_venda = (float) str_replace(',', '.', $item['valor_venda'] ?? '0');
                    $valor_promocional = (float) str_replace(',', '.', $item['valor_promocional'] ?? '0');

                    // ✅ CORREÇÃO: Lógica de esgotado correta
                    $esgotado = ($tem_estoque === 'Sim' && $estoque <= 0);

                    // Definir URL de destino
                    $url_produto = '#';
                    if (!$esgotado) {
                        // Verificar se há variações (grades)
                        $stmt_grade = $pdo->prepare("SELECT id FROM grades WHERE produto = :produto_id AND ativo = 'Sim' LIMIT 1");
                        $stmt_grade->bindValue(':produto_id', $id_produto, PDO::PARAM_INT);
                        $stmt_grade->execute();
                        if ($stmt_grade->rowCount() > 0) {
                            $url_produto = 'adicionais-' . $url_produto_link;
                        } else {
                            $url_produto = 'observacoes-' . $url_produto_link;
                        }
                    }

                    $mostrar_promo = ($promocao === 'Sim' && $valor_promocional > 0);
                    $valor_exibir = $mostrar_promo ? $valor_promocional : $valor_venda;
                    $valorF = number_format($valor_exibir, 2, ',', '.');

                    $caminho_imagem = $foto ? "sistema/painel/images/produtos/{$foto}" : '';
                    ?>
                    <a href="<?php echo $esgotado ? '#' : htmlspecialchars($url_produto); ?>" class="link-neutro">
                        <li class="list-group-item d-flex justify-content-between align-items-start mb-2" 
                            style="box-shadow: rgba(99, 99, 99, 0.2) 0px 2px 8px 0px; border-radius: 8px; position: relative;"
                            data-tem-estoque="<?php echo $tem_estoque; ?>" 
                            data-estoque="<?php echo $estoque; ?>"
                            data-id-produto="<?php echo $id_produto; ?>">
                            
                            <?php if ($esgotado): ?>
                                <img src="img/esgotado.png" width="65" height="65" 
                                     style="position: absolute; right: 0; top: 0; z-index: 2;" 
                                     alt="Esgotado" class="banner-esgotado-php">
                            <?php endif; ?>

                            <?php if ($mostrar_promo): ?>
                                <img src="img/promocao2.png" width="65" height="65" 
                                     style="position: absolute; right: 0; top: 0; opacity: 0.8; z-index: 1;" alt="Promoção">
                            <?php endif; ?>

                            <div class="row" style="width: 100%;">
                                <div class="col-10">
                                    <div class="me-auto">
                                        <div class="fw-bold titulo-item" style="font-weight: 600">
                                            <?php echo $nome_produto; ?>
                                        </div>
                                        <div class="subtitulo-item-menor" style="font-size:10px; font-weight: 380">
                                            <?php echo $descricao_produto; ?>
                                        </div>
                                        <div class="mt-1">
                                            <?php if ($mostrar_promo && !$esgotado): ?>
                                                <span class="text-decoration-line-through text-muted" style="font-size: 12px;">
                                                    R$ <?php echo number_format($valor_venda, 2, ',', '.'); ?>
                                                </span><br>
                                                <strong class="text-danger" style="font-size: 16px;">R$ <?php echo $valorF; ?></strong>
                                            <?php elseif (!$esgotado): ?>
                                                <strong class="text-danger" style="font-size: 16px;">R$ <?php echo $valorF; ?></strong>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-2 text-end" style="margin-top: 10px;">
                                    <?php if ($caminho_imagem && file_exists($caminho_imagem)): ?>
                                        <img src="<?php echo htmlspecialchars($caminho_imagem); ?>" width="60" height="60" 
                                             style="object-fit: cover;" loading="lazy">
                                    <?php endif; ?>
                                </div>
                            </div>
                        </li>
                    </a>
                    <?php
                }
            }
            ?>
        </ol>
    </div>
</div>

<script>
$(document).ready(function() {
    // Corrige os banners de esgotado com base nos dados do PHP
    $('.list-group-item').each(function() {
        const $item = $(this);
        const temEstoque = $item.data('tem-estoque');
        const estoque = $item.data('estoque');

        // A lógica de esgotado deve ser a mesma do PHP
        const estaEsgotado = (temEstoque === 'Sim' && estoque <= 0);

        if (!estaEsgotado) {
            // Se o PHP diz que NÃO está esgotado, remover o banner
            $item.find('img[src="img/esgotado.png"]').remove();
        }
    });

    // Continuar com o restante da lógica do carrinho
    $.ajax({
        url: 'js/ajax/listar-itens-carrinho-icone.php',
        method: 'POST',
        success: function(result) {
            $("#listar-itens-carrinho-icone").html(result);
        }
    });
});
</script>