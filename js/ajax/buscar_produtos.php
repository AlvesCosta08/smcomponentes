<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
require_once('../../sistema/conexao.php');

// Validação segura da entrada
$buscar = filter_input(INPUT_POST, 'buscar', FILTER_VALIDATE_INT);
if ($buscar === false || $buscar === null) {
    echo '<ol class="list-group" style="margin-top: 65px"><li class="list-group-item text-center">ID inválido.</li></ol>';
    exit;
}

echo <<<HTML
<ol class="list-group" style="margin-top: 65px">
HTML;

// Buscar produto por ID
$stmt = $pdo->prepare("
    SELECT 
        p.*, 
        c.url AS url_categoria, 
        c.mais_sabores,
        c.delivery AS delivery_categoria
    FROM produtos p
    LEFT JOIN categorias c ON p.categoria = c.id
    WHERE p.id = :id AND p.ativo = 1
    ORDER BY p.valor_venda ASC
");
$stmt->bindParam(':id', $buscar, PDO::PARAM_INT);
$stmt->execute();
$produto = $stmt->fetch(PDO::FETCH_ASSOC);

if ($produto) {
    $id = $produto['id'];
    $foto = htmlspecialchars($produto['foto'] ?? '');
    $nome = htmlspecialchars($produto['nome'] ?? '');
    $descricao = htmlspecialchars($produto['descricao'] ?? '');
    $url = htmlspecialchars($produto['url'] ?? '');
    $estoque = (int)$produto['estoque'];
    $tem_estoque = $produto['tem_estoque'] ? 'Sim' : 'Não';
    $delivery_produto = $produto['delivery'] ? 'Sim' : 'Não';
    $promocao = $produto['promocao'] ? 'Sim' : 'Não';
    
    // 🔧 Correção: suporte a vírgula no valor_promocional
    $val_promocional = (float)str_replace(',', '.', $produto['valor_promocional'] ?? '0');
    
    $mais_sabores = $produto['mais_sabores'] ?? 'Não';
    $url_cat = htmlspecialchars($produto['url_categoria'] ?? '');
    $delivery_categoria = $produto['delivery_categoria'] ?? 'Sim'; // Padrão: Sim

    // 🔧 Correção: suporte a vírgula no valor_venda
    $valor_calculado = (float)str_replace(',', '.', $produto['valor_venda'] ?? '0');
    $valor_exibir = $valor_calculado;
    $valor_original = null;

    // Verifica se há promoção válida
    $em_promocao = ($promocao === 'Sim' && $val_promocional > 0);
    if ($em_promocao) {
        $valor_original = $valor_calculado;
        $valor_exibir = $val_promocional;
    }

    // Formatação para exibição (vírgula como separador decimal)
    $valor_exibirF = number_format($valor_exibir, 2, ',', '.');
    $valor_originalF = $valor_original ? number_format($valor_original, 2, ',', '.') : null;

    // Verifica sessão de mesa
    session_start();
    $nome_mesa = $_SESSION['nome_mesa'] ?? '';

    // Verifica se o produto ou a categoria permite delivery
    $delivery_permitido = ($delivery_produto === 'Sim' || $delivery_categoria === 'Sim');
    
    // Pula se não for permitido delivery e não houver mesa
    if ($nome_mesa === '' && !$delivery_permitido) {
        echo '<li class="list-group-item text-center">Produto não disponível para delivery.</li>';
        echo '</ol>';
        exit;
    }

    // Verifica estoque
    $esgotado = ($tem_estoque === 'Sim' && $estoque <= 0);
    $mostrar_esgotado = $esgotado ? '' : 'ocultar';
    $url_produto = '#';

    if (!$esgotado) {
        // Verifica se tem grade/adição
        $stmt_grade = $pdo->prepare("SELECT id FROM grades WHERE produto = :produto_id AND ativo = 'Sim' LIMIT 1");
        $stmt_grade->bindParam(':produto_id', $id, PDO::PARAM_INT);
        $stmt_grade->execute();
        $tem_grade = $stmt_grade->fetchColumn();

        if ($tem_grade) {
            $url_produto = 'adicionais-' . $url;
        } else {
            $url_produto = 'observacoes-' . $url;
        }
    }

    $link_red = ($mais_sabores === 'Sim') ? 'categoria-sabores-' . $url_cat : $url_produto;

    echo <<<HTML
    <a href="{$link_red}" class="link-neutro">
        <li class="list-group-item d-flex justify-content-between align-items-start mb-2" style="box-shadow: rgba(99, 99, 99, 0.2) 0px 2px 8px 0px; border-radius: 8px; position: relative;">
HTML;

    if ($esgotado) {
        echo '<img class="" src="img/esgotado.png" width="65px" height="65px" style="position:absolute; right:0; top:0;">';
    } elseif ($em_promocao) {
        echo '<img class="" src="img/promocao2.png" width="65px" height="65px" style="position:absolute; right:0; top:0; opacity:0.8;">';
    }

    echo <<<HTML
            <div class="row" style="width:100%">
                <div class="col-10">
                    <div class="me-auto">
                        <div class="fw-bold titulo-item" style="font-weight: 600">{$nome}</div>
                        <div class="subtitulo-item-menor" style="font-size:10px; font-weight: 380">{$descricao}</div>
                        <div class="valor-item-maior" style="font-size:12px;">
HTML;

    // Exibir variações (se houver)
    $stmt_var = $pdo->prepare("
        SELECT ig.texto, ig.valor 
        FROM grades g
        INNER JOIN itens_grade ig ON g.id = ig.grade
        WHERE g.produto = :produto_id 
          AND g.tipo_item = 'Variação' 
          AND g.ativo = 'Sim' 
          AND ig.ativo = 'Sim'
        ORDER BY g.id ASC, ig.id ASC
    ");
    $stmt_var->bindParam(':produto_id', $id, PDO::PARAM_INT);
    $stmt_var->execute();
    $variacoes = $stmt_var->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($variacoes)) {
        $textos = [];
        foreach ($variacoes as $var) {
            // 🔧 Também aplicamos str_replace aqui, caso 'valor' em itens_grade use vírgula
            $valor_adicional = (float)str_replace(',', '.', $var['valor']);
            $val_var = $valor_exibir + $valor_adicional;
            $val_varF = number_format($val_var, 2, ',', '.');
            $textos[] = '(' . htmlspecialchars($var['texto']) . ') R$ ' . $val_varF;
        }
        echo implode(' / ', $textos);
    } else {
        // Exibir valor principal
        if ($valor_exibir > 0) {
            if ($em_promocao && $valor_original) {
                echo '<del style="color: #999; font-size: 12px;">R$ ' . $valor_originalF . '</del> ';
            }
            echo '<span class="text-danger fw-bold">R$ ' . $valor_exibirF . '</span>';
        }
    }

    echo <<<HTML
                        </div>
                    </div>
                </div>
                <div class="col-2" style="margin-top: 10px;" align="right">
                    <img src="sistema/painel/images/produtos/{$foto}" width="60px" height="60px" onerror="this.src='img/sem-foto.jpg'">
                </div>
            </div>
        </li>
    </a>
HTML;

} else {
    echo '<li class="list-group-item text-center">Nenhum produto encontrado.</li>';
}

echo '</ol>';
?>