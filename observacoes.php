<?php
session_start();

// Sanitização segura da URL
$url_completa = filter_input(INPUT_GET, 'url', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$sabores = filter_input(INPUT_GET, 'sabores', FILTER_VALIDATE_INT);

if (!$url_completa) {
    die('URL inválida.');
}

// Dados da sessão (com fallback seguro)
$id_usuario = $_SESSION['id'] ?? null;
$nome_mesa = $_SESSION['nome_mesa'] ?? '';
$id_mesa = $_SESSION['id_mesa'] ?? null;
$id_ab_mesa = $_SESSION['id_ab_mesa'] ?? null;
$pedido_balcao = $_SESSION['pedido_balcao'] ?? null;

require_once("cabecalho.php");

// Garantir sessão única
if (empty($_SESSION['sessao_usuario'])) {
    $_SESSION['sessao_usuario'] = date('Y-m-d-H-i-s') . '-' . rand(1000, 9999);
}
$sessao = $_SESSION['sessao_usuario'];

// Definir texto do botão
$texto_botao = ($nome_mesa !== '') ? 'Adicionar à Mesa' : 'Adicionar ao Carrinho';

// Se há mesa ativa, remove pedido no balcão
if ($nome_mesa !== '') {
    unset($_SESSION['pedido_balcao']);
}

// Buscar produto com prepared statement
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$stmt = $pdo->prepare("SELECT * FROM produtos WHERE url = :url AND ativo = 1 LIMIT 1");
$stmt->bindValue(':url', $url_completa, PDO::PARAM_STR);
$stmt->execute();
$produto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$produto) {
    http_response_code(404);
    die('Produto não encontrado.');
}

// Dados básicos do produto
$nome = htmlspecialchars($produto['nome']);
$descricao = htmlspecialchars($produto['descricao']);
$foto = $produto['foto'] ?: '';
$id_prod = (int)$produto['id'];
$categoria = (int)$produto['categoria'];

// 🔧 FUNÇÃO PARA NORMALIZAR VALOR MONETÁRIO (SEGURA)
function normalizarValorMonetario($valor): array {
    $valor = preg_replace('/[^\d,]/', '', (string)$valor);
    if ($valor === '' || $valor === ',') {
        $valor = '0,00';
    }
    if (strpos($valor, ',') === false) {
        $valor .= ',00';
    } else {
        [$int, $dec] = explode(',', $valor . ',00');
        $int = $int ?: '0';
        $dec = substr(str_pad($dec, 2, '0', STR_PAD_RIGHT), 0, 2);
        $valor = $int . ',' . $dec;
    }
    return [
        'exibir' => $valor,
        'calcular' => (float)str_replace(',', '.', $valor)
    ];
}

// Aplicar normalização ao valor_venda
$valor_tratado = normalizarValorMonetario($produto['valor_venda'] ?? '0');
$valor_itemF = $valor_tratado['exibir'];
$valor_para_calculo = $valor_tratado['calcular'];

// Não há promoção → valor_original é nulo
$valor_original = null;

// 🔧 CORREÇÃO AQUI: Buscar URL da categoria apenas se estiver ativa
$stmt_cat = $pdo->prepare("SELECT url FROM categorias WHERE id = :id AND ativo = 'Sim' LIMIT 1");
$stmt_cat->bindValue(':id', $categoria, PDO::PARAM_INT);
$stmt_cat->execute();
$cat_data = $stmt_cat->fetch(PDO::FETCH_ASSOC);
$url_cat = $cat_data ? htmlspecialchars($cat_data['url']) : 'todas'; // fallback seguro

// Caminho da imagem
$caminho_imagem = $foto ? "sistema/painel/images/produtos/{$foto}" : '';
?>

<style type="text/css">
	body {
		background: #f2f2f2;
	}
	.direita {
		float: right;
	}
	.clearfix::after {
		content: "";
		clear: both;
		display: table;
	}
</style>

<div class="main-container" style="background:#fff">
	<nav class="navbar bg-light fixed-top" style="box-shadow: 0px 3px 5px rgba(0, 0, 0, 0.20);">
		<div class="container-fluid">
			<div class="navbar-brand">
				<a href="categoria-<?php echo $url_cat; ?>"><big><i class="bi bi-arrow-left"></i></big></a>
				<span style="margin-left: 15px; font-size:14px">DETALHES DO PRODUTO</span>
			</div>
			<?php require_once("icone-carrinho.php"); ?>
		</div>
	</nav>

	<div class="destaque" style="border: solid 1px #ababab; border-radius: 10px; padding: 15px; margin-top: 60px;">
		<h5 class="text-center fw-bold"><?php echo $nome; ?></h5>
		<div class="text-center mb-3">
			<?php if ($caminho_imagem): ?>
				<img src="<?php echo htmlspecialchars($caminho_imagem); ?>" 
					 width="60%" style="max-height: 200px; object-fit: contain;">
			<?php endif; ?>
		</div>

		<div class="text-center mb-3">
			<?php if (isset($valor_original)): ?>
				<del class="text-muted">R$ <?php echo $valor_original; ?></del><br>
			<?php endif; ?>
			<span class="text-danger fw-bold" style="font-size: 22px;">R$ <?php echo $valor_itemF; ?></span>
		</div>
	</div>

	<?php if (!in_array($sabores, [1, 2])): ?>
	<div class="destaque-qtd clearfix" style="border: solid 1px #ababab; border-radius: 10px; padding: 15px; margin-top: 15px;">
		<b>QUANTIDADE</b>
		<span class="direita">
			<big>
				<a href="#" onclick="diminuirQuant(); return false;"><i class="bi bi-dash-circle-fill text-danger"></i></a>
				&nbsp;<b><span id="quant">1</span></b>&nbsp;
				<a href="#" onclick="aumentarQuant(); return false;"><i class="bi bi-plus-circle-fill text-success"></i></a>
			</big>
		</span>
	</div>
	<?php endif; ?>

	<input type="hidden" id="quantidade" value="1">
	<input type="hidden" id="total_item_input" value="<?php echo $valor_para_calculo; ?>">

	<div class="destaque-qtd" style="border: solid 1px #ababab; border-radius: 10px; padding: 15px; margin-top: 15px;">
		<b>OBSERVAÇÕES</b>
		<div class="form-group mt-3">
			<textarea maxlength="255" class="form-control" id="obs" placeholder="Deseja adicionar alguma observação?"></textarea>
		</div>
	</div>

	<div class="destaque-qtd clearfix" style="border: solid 1px #ababab; border-radius: 10px; padding: 15px; margin-top: 15px;">
		<b>TOTAL</b>
		<span class="direita">
			<b class="text-danger" style="font-size: 18px;">R$ <span id="total_item"><?php echo $valor_itemF; ?></span></b>
		</span>
	</div>

	<div class="d-grid gap-2 col-8 mx-auto mt-4">
		<button onclick="addCarrinho()" class="btn btn-warning btn-lg">
			<?php echo $texto_botao; ?> <i class="fal fa-long-arrow-right"></i>
		</button>
	</div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function atualizarTotal() {
	const qtd = parseInt(document.getElementById('quantidade').value) || 1;
	const valorUnit = <?php echo json_encode($valor_para_calculo); ?>;
	const total = (qtd * valorUnit).toFixed(2);
	const totalBR = total.replace('.', ',');
	document.getElementById('total_item').textContent = totalBR;
	document.getElementById('total_item_input').value = total;
}

function aumentarQuant() {
	let qtd = parseInt(document.getElementById('quantidade').value) || 1;
	document.getElementById('quantidade').value = qtd + 1;
	document.getElementById('quant').textContent = qtd + 1;
	atualizarTotal();
}

function diminuirQuant() {
	let qtd = parseInt(document.getElementById('quantidade').value) || 1;
	if (qtd > 1) {
		document.getElementById('quantidade').value = qtd - 1;
		document.getElementById('quant').textContent = qtd - 1;
		atualizarTotal();
	}
}

function addCarrinho() {
	const quantidade = document.getElementById('quantidade').value;
	const total_item = document.getElementById('total_item_input').value;
	const produto = <?php echo json_encode($id_prod); ?>;
	const obs = document.getElementById('obs').value;
	const mesa = <?php echo json_encode($id_ab_mesa); ?>;

	fetch('js/ajax/add-carrinho.php', {
		method: 'POST',
		headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
		body: new URLSearchParams({
			quantidade,
			total_item,
			produto,
			obs,
			mesa
		})
	})
	.then(response => response.text())
	.then(mensagem => {
		if (mensagem.trim() === "Inserido com Sucesso") {
			window.location.href = 'carrinho';
		} else {
			alert(mensagem);
		}
	})
	.catch(err => {
		alert('Erro ao adicionar ao carrinho: ' + err.message);
	});
}

document.addEventListener('DOMContentLoaded', function() {
	atualizarTotal();
});
</script>