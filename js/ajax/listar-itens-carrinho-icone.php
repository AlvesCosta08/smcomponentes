<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
@session_start();
require_once('../../sistema/conexao.php');

// Função auxiliar para formatar moeda (reutilizável)
function formatarMoeda(float $valor): string {
    return number_format($valor, 2, ',', '.');
}

// Obter e validar sessão
$sessao = isset($_SESSION['sessao_usuario']) ? trim($_SESSION['sessao_usuario']) : '';
$id_mesa = isset($_SESSION['id_ab_mesa']) && is_numeric($_SESSION['id_ab_mesa']) ? (int)$_SESSION['id_ab_mesa'] : null;
$id_edicao = isset($_SESSION['id_edicao']) && is_numeric($_SESSION['id_edicao']) ? (int)$_SESSION['id_edicao'] : null;

// Preparar condição de busca
if ($id_edicao) {
    $stmt = $pdo->prepare("SELECT * FROM carrinho WHERE pedido = :pedido");
    $stmt->bindValue(':pedido', $id_edicao, PDO::PARAM_INT);
} elseif ($id_mesa) {
    $stmt = $pdo->prepare("SELECT * FROM carrinho WHERE mesa = :mesa");
    $stmt->bindValue(':mesa', $id_mesa, PDO::PARAM_INT);
} else {
    if (!$sessao) {
        echo 'Nenhum item adicionado!';
        return;
    }
    $stmt = $pdo->prepare("SELECT * FROM carrinho WHERE sessao = :sessao");
    $stmt->bindValue(':sessao', $sessao, PDO::PARAM_STR);
}

$stmt->execute();
$itens_carrinho = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($itens_carrinho)) {
    echo 'Nenhum item adicionado!';
    return;
}

// Coletar IDs únicos para carregar em lote
$ids_produtos = [];
$ids_variacoes = [];
foreach ($itens_carrinho as $item) {
    if ($item['produto']) $ids_produtos[] = (int)$item['produto'];
    if ($item['variacao']) $ids_variacoes[] = (int)$item['variacao'];
}
$ids_produtos = array_unique($ids_produtos);
$ids_variacoes = array_unique($ids_variacoes);

// Carregar produtos ativos em lote
$produtos = [];
if (!empty($ids_produtos)) {
    $placeholders = str_repeat('?,', count($ids_produtos) - 1) . '?';
    $stmt_prod = $pdo->prepare("SELECT id, nome, foto FROM produtos WHERE id IN ($placeholders) AND ativo = 1");
    $stmt_prod->execute($ids_produtos);
    foreach ($stmt_prod->fetchAll(PDO::FETCH_ASSOC) as $p) {
        $produtos[$p['id']] = $p;
    }
}

// Carregar variações em lote
$variacoes = [];
if (!empty($ids_variacoes)) {
    $placeholders = str_repeat('?,', count($ids_variacoes) - 1) . '?';
    $stmt_var = $pdo->prepare("SELECT id, sigla FROM variacoes WHERE id IN ($placeholders)");
    $stmt_var->execute($ids_variacoes);
    foreach ($stmt_var->fetchAll(PDO::FETCH_ASSOC) as $v) {
        $variacoes[$v['id']] = $v;
    }
}

// Calcular totais e exibir
$total_carrinho = 0;
$total_reg = count($itens_carrinho);

foreach ($itens_carrinho as $item) {
    $id = (int)$item['id'];
    $quantidade = (int)$item['quantidade'];
    $total_item = (float)$item['total_item'];
    $produto_id = (int)$item['produto'];
    $variacao_id = (int)$item['variacao'];
    $obs = $item['obs'] ?? '';
    $nome_produto_tab = $item['nome_produto'] ?? '';
    $sabores = (int)($item['sabores'] ?? 0);
    $valor_unit = !empty($item['valor_unitario']) ? (float)$item['valor_unitario'] : ($quantidade > 0 ? $total_item / $quantidade : 0);

    // Recalcular total_item por segurança (evitar inconsistência)
    $total_item = $valor_unit * $quantidade;
    $total_carrinho += $total_item;

    // Obter dados do produto
    if ($sabores > 0) {
        $nome_produto = htmlspecialchars($nome_produto_tab);
        $foto_produto = '';
    } else {
        if (isset($produtos[$produto_id])) {
            $nome_produto = htmlspecialchars($produtos[$produto_id]['nome']);
            $foto_produto = htmlspecialchars($produtos[$produto_id]['foto'] ?: '');
        } else {
            $nome_produto = htmlspecialchars($nome_produto_tab ?: 'Produto removido');
            $foto_produto = '';
        }
    }

    // Obter sigla da variação
    $sigla_variacao = '';
    if ($variacao_id && isset($variacoes[$variacao_id])) {
        $sigla_variacao = '(' . htmlspecialchars($variacoes[$variacao_id]['sigla']) . ')';
    }

    // Classe de observação
    $classe_obs = $obs === '' ? 'text-warning' : 'text-danger';

    // Controle de exclusão
    $ocultar_excluir = ($id_mesa > 0) ? 'ocultar' : '';

    // Caminho da imagem
    $caminho_imagem = $foto_produto ? "sistema/painel/images/produtos/{$foto_produto}" : '';

    echo <<<HTML
<li>
  <div class="tpcart__item">
    <div class="tpcart__img">
      <img src="{$caminho_imagem}" alt="{$nome_produto}">
    </div>
    <div class="tpcart__content">
      <span class="tpcart__content-title"><b>{$nome_produto} {$sigla_variacao}</b></span>
      <div class="tpcart__cart-price">
        <span class="quantity">{$quantidade} x</span>
        <span class="text-success">R$ {$total_itemF}</span>
      </div>
    </div>
    <div class="direita">
      <a href="#" onclick="excluirCarrinhoIcone('{$id}')" class="link-neutro {$ocultar_excluir}">
        <i class="icon-x-circle text-warning"></i>
      </a>
    </div>
  </div>
</li>
HTML;
}

$total_carrinhoF = formatarMoeda($total_carrinho);
?>

<script type="text/javascript">
	$("#total-carrinho-icone").text("<?= $total_carrinhoF ?>");
	$("#total-itens-carrinho").text("<?= $total_reg ?>");
	$("#total-carrinho-finalizar").text("<?= $total_carrinhoF ?>");

	function excluirCarrinhoIcone(id) {
		$.ajax({
			url: 'js/ajax/excluir-carrinho.php',
			method: 'POST',
			data: { id: id },
			dataType: "text",
			success: function(mensagem) {
				if (mensagem.trim() === "Excluido com Sucesso") {
					listarCarrinhoIcone();
				}
			}
		});
	}
</script>