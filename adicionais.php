<?php
session_start();

// Sanitização segura da URL
$url_completa = filter_input(INPUT_GET, 'url', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
if (!$url_completa) {
    die('URL inválida.');
}

// Dados da sessão com fallback seguro
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
$nova_sessao = $_SESSION['sessao_usuario'];

// Remover itens temporários não associados ao carrinho
try {
    $stmt_clean = $pdo->prepare("DELETE FROM temp WHERE carrinho = '0' AND sessao = ?");
    $stmt_clean->execute([$nova_sessao]);
} catch (Exception $e) {
    // Opcional: logar erro
}

// Separar URL (remover sufixo, como "pizza_2sabores")
$separar_url = explode("_", $url_completa);
$url = $separar_url[0];

// Buscar produto com prepared statement
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$stmt = $pdo->prepare("SELECT * FROM produtos WHERE url = ? AND ativo = '1' LIMIT 1");
$stmt->execute([$url]);
$produto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$produto) {
    http_response_code(404);
    die('Produto não encontrado.');
}

// Dados do produto
$nome = htmlspecialchars($produto['nome']);
$descricao = htmlspecialchars($produto['descricao'] ?? '');
$foto = $produto['foto'] ?? '';
$id_prod = (int)$produto['id'];
$categoria = (int)$produto['categoria'];
$valor_venda = $produto['valor_venda'] ?? '0';
$val_promocional = $produto['val_promocional'] ?? '0';
$promocao = $produto['promocao'] ?? 'Não';

// Aplicar promoção
$valor_base = ($promocao === 'Sim') ? $val_promocional : $valor_venda;

// 🔧 Função para normalizar valor monetário
function normalizarValorMonetario($valor): array {
    $valor = preg_replace('/[^\d,]/', '', (string)$valor);
    if ($valor === '' || $valor === ',') $valor = '0,00';
    if (strpos($valor, ',') === false) $valor .= ',00';
    else {
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

$valor_tratado = normalizarValorMonetario($valor_base);
$valorF = $valor_tratado['exibir'];
$valor_para_calculo = $valor_tratado['calcular'];
$valor_total_do_item = $valor_para_calculo;
$tem_variacao = 'Não';

// Buscar categoria
$stmt_cat = $pdo->prepare("SELECT url FROM categorias WHERE id = ? LIMIT 1");
$stmt_cat->execute([$categoria]);
$cat_data = $stmt_cat->fetch(PDO::FETCH_ASSOC);
$url_cat = $cat_data ? htmlspecialchars($cat_data['url']) : '';

// Verificação de horário de funcionamento (apenas se não for mesa nem balcão)
if ($id_mesa === null && $pedido_balcao === null) {
    if ($status_estabelecimento === "Fechado") {
        echo "<script>alert('" . addslashes($texto_fechamento ?? 'Estabelecimento fechado.') . "'); window.location='index.php';</script>";
        exit();
    }

    $data = date('Y-m-d');
    $diasemana = ["Domingo", "Segunda-Feira", "Terça-Feira", "Quarta-Feira", "Quinta-Feira", "Sexta-Feira", "Sábado"];
    $dia_procurado = $diasemana[date('w', strtotime($data))];

    $stmt_dia = $pdo->prepare("SELECT COUNT(*) FROM dias WHERE dia = ?");
    $stmt_dia->execute([$dia_procurado]);
    if ($stmt_dia->fetchColumn() > 0) {
        echo "<script>alert('Estamos Fechados! Não funcionamos Hoje!'); window.location='index.php';</script>";
        exit();
    }

    // Corrigir formatação de horário
    $hora_atual = time();
    $start = strtotime(date('Y-m-d') . ' ' . ($horario_abertura ?? '00:00'));
    $end = strtotime(date('Y-m-d') . ' ' . ($horario_fechamento ?? '23:59'));

    if ($end < $start) {
        // Funciona até o dia seguinte
        $end = strtotime('+1 day', $end);
    }

    if (!($hora_atual >= $start && $hora_atual <= $end)) {
        echo "<script>alert('" . addslashes($texto_fechamento_horario ?? 'Fora do horário de funcionamento.') . "'); window.location='index.php';</script>";
        exit();
    }
}

// Buscar grades do produto
$stmt_grades = $pdo->prepare("SELECT * FROM grades WHERE produto = ? AND ativo = 'Sim' ORDER BY id ASC");
$stmt_grades->execute([$id_prod]);
$grades = $stmt_grades->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="main-container">

<nav class="navbar bg-light fixed-top" style="box-shadow: 0px 3px 5px rgba(0, 0, 0, 0.20);">
    <div class="container-fluid">
        <div class="navbar-brand">
            <a href="categoria-<?php echo $url_cat ?>"><big><i class="bi bi-arrow-left"></i></big></a>
            <span style="margin-left: 15px; font-size:14px"><?php echo $nome ?> - <?php echo htmlspecialchars($nome_mesa) ?></span>
        </div>
        <?php require_once("icone-carrinho.php") ?>
    </div>
</nav>

<div class="destaque" style="border: solid 1px #ababab; border-radius: 10px; margin-top: 60px; padding: 15px;">
    <b><?php echo mb_strtoupper($nome, 'UTF-8'); ?></b>
    <div class="text-center mt-2">
        <?php if ($foto): ?>
            <img src="sistema/painel/images/produtos/<?php echo htmlspecialchars($foto); ?>" 
                 width="70%" style="border-radius:10px; max-height:200px; object-fit:contain;">
        <?php endif; ?>
    </div>
</div>

<div style="margin-top: 15px;">
    <?php foreach ($grades as $grade): 
        $id_grade = (int)$grade['id'];
        $tipo_item = $grade['tipo_item'];
        $valor_item = (float)$grade['valor_item'];
        $texto = htmlspecialchars($grade['texto']);
        $limite = (int)$grade['limite'];

        if ($tipo_item === 'Variação') {
            $tem_variacao = 'Sim';
        }
    ?>
        <div class="titulo-itens" style="margin-top: 15px;">
            <input type="hidden" id="qt_<?php echo $id_grade ?>" value="0">
            <?php echo $texto; ?>
            <?php if ($limite > 0): ?>
                <span style="font-size:13px; color:#000">(até <?php echo $limite; ?> itens!)</span>
            <?php endif; ?>
        </div>
        <ol class="list-group">
            <?php
            $stmt_itens = $pdo->prepare("SELECT * FROM itens_grade WHERE grade = ? AND ativo = 'Sim' ORDER BY id ASC");
            $stmt_itens->execute([$id_grade]);
            $itens_grade = $stmt_itens->fetchAll(PDO::FETCH_ASSOC);

            foreach ($itens_grade as $item):
                $id_item = (int)$item['id'];
                $texto_item = htmlspecialchars($item['texto']);
                $valor_item_grade = (float)$item['valor'];
                $limite_item = (int)$item['limite'];
                $valor_item_gradeF = number_format($valor_item_grade, 2, ',', '.');

                $ocultar_valor = ($valor_item_grade > 0) ? '' : 'ocultar';
            ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <span style="font-size: 12px;">
                        <?php echo $texto_item; ?>
                        <?php if ($limite_item > 0): ?>
                            <span style="font-size:11px; color:red">(até <?php echo $limite_item; ?> itens!)</span>
                        <?php endif; ?>
                        <?php if ($valor_item_grade > 0): ?>
                            <span class="valor-item">(R$ <?php echo $valor_item_gradeF; ?>)</span>
                        <?php endif; ?>
                    </span>

                    <?php if ($tipo_item === '1 de Cada'): ?>
                        <span class="form-switch">
                            <input class="form-check-input" type="checkbox" id="item_<?php echo $id_item ?>"
                                   onchange="itens('<?php echo $id_item ?>', '<?php echo $id_grade ?>', '<?php echo $valor_item_grade ?>', '<?php echo $tipo_item ?>', '1', '<?php echo $valor_item ?>', '<?php echo $limite ?>')">
                        </span>

                    <?php elseif ($tipo_item === 'Múltiplo'): ?>
                        <span style="font-size:14px;">
                            <button type="button" class="btn p-0" onclick="dim('<?php echo $id_grade ?>', '<?php echo $id_item ?>', '<?php echo $valor_item_grade ?>', '<?php echo $tipo_item ?>', '<?php echo $valor_item ?>', '<?php echo $limite_item ?>', '<?php echo $limite ?>')">
                                <i class="bi bi-dash-circle-fill text-danger"></i>
                            </button>
                            <b><input id="quantidade_item_<?php echo $id_item ?>" value="0" style="background: transparent; border:none; width:20px; text-align: center" readonly></b>
                            <button type="button" class="btn p-0" onclick="aum('<?php echo $id_grade ?>', '<?php echo $id_item ?>', '<?php echo $valor_item_grade ?>', '<?php echo $tipo_item ?>', '<?php echo $valor_item ?>', '<?php echo $limite_item ?>', '<?php echo $limite ?>')">
                                <i class="bi bi-plus-circle-fill text-success"></i>
                            </button>
                        </span>

                    <?php elseif (in_array($tipo_item, ['Único', 'Variação'])): ?>
                        <span>
                            <input class="form-check-input" type="radio" name="grade_<?php echo $id_grade ?>" 
                                   value="<?php echo $id_item ?>" 
                                   onchange="itens('<?php echo $id_item ?>', '<?php echo $id_grade ?>', '<?php echo $valor_item_grade ?>', '<?php echo $tipo_item ?>', '1', '<?php echo $valor_item ?>')">
                        </span>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ol>
    <?php endforeach; ?>
</div>

<?php
$valor_total_pedidoF = number_format($valor_total_do_item, 2, ',', '.');
?>

<div class="destaque-qtd" style="border: solid 1px #ababab; border-radius: 10px; margin-top: 15px; padding: 10px;">
    <b>QUANTIDADE</b>
    <span class="direita">
        <big>
            <button type="button" style="background:transparent; border:none" onclick="diminuirQuant()"><i class="bi bi-dash-circle-fill text-danger"></i></button>
            <b><span id="quant">1</span></b>
            <button type="button" style="background:transparent; border:none" onclick="aumentarQuant()"><i class="bi bi-plus-circle-fill text-success"></i></button>
        </big>
    </span>
</div>

<div class="destaque-qtd" style="border: solid 1px #ababab; border-radius: 10px; margin-top: 15px; padding: 10px;">
    <b>Subtotal</b>
    <span class="direita">
        <input type="hidden" id="valor_total_produto" value="<?php echo $valor_total_do_item ?>">
        <input type="hidden" id="valor_total_input" value="<?php echo $valor_total_do_item ?>">
        <b>R$ <span id="valor_item_quantF"><?php echo $valor_total_pedidoF ?></span></b>
    </span>
</div>

<hr>

<div class="destaque-qtd" style="border: solid 1px #ababab; border-radius: 10px; margin-top: 15px; padding: 10px;">
    <b>OBSERVAÇÕES</b>
    <div class="form-group mt-3">
        <textarea maxlength="255" class="form-control" id="obs" placeholder="Deseja adicionar alguma observação?"></textarea>
    </div>
</div>

<div class="d-grid gap-2 col-8 mx-auto mt-3">
    <button onclick="addCarrinho()" class="btn btn-warning btn-lg">
        Adicionar ao Pedido <i class="fal fa-long-arrow-right"></i>
    </button>
</div>

</div>

<script>
// Atualiza quantidade visível
document.getElementById('quant').textContent = document.getElementById('quantidade').value || '1';

function aumentarQuant() {
    let q = parseInt(document.getElementById('quantidade').value) || 1;
    document.getElementById('quantidade').value = q + 1;
    document.getElementById('quant').textContent = q + 1;
    atualizarSubtotal();
}

function diminuirQuant() {
    let q = parseInt(document.getElementById('quantidade').value) || 1;
    if (q > 1) {
        document.getElementById('quantidade').value = q - 1;
        document.getElementById('quant').textContent = q - 1;
        atualizarSubtotal();
    }
}

function atualizarSubtotal() {
    const qtd = parseInt(document.getElementById('quantidade').value) || 1;
    const base = parseFloat(document.getElementById('valor_total_input').value) || 0;
    const total = (qtd * base).toFixed(2);
    document.getElementById('valor_item_quantF').textContent = total.replace('.', ',');
}

function itens(id, grade, valor, tipo, quantidade, tipagem, limite_grade) {
    let qtd_marcada = parseFloat(document.getElementById('qt_' + grade).value) || 0;
    let novo_qtd = qtd_marcada;

    if (tipo === '1 de Cada') {
        const checked = document.getElementById('item_' + id)?.checked;
        novo_qtd = checked ? qtd_marcada + 1 : qtd_marcada - 1;
        if (limite_grade && novo_qtd > limite_grade) {
            alert('O limite para essa escolha é de ' + limite_grade + ' Itens!');
            document.getElementById('item_' + id).checked = false;
            return;
        }
    }

    document.getElementById('qt_' + grade).value = novo_qtd;

    // Envia via AJAX (mantém compatibilidade com script antigo)
    $.post('js/ajax/adicionar_item.php', {
        id, grade, valor, tipo, quantidade, tipagem
    }, function(mensagem) {
        if (mensagem.trim() === "Alterado com Sucesso") {
            listarItens();
        }
    });
}

function aum(grade, item, valor, tipo, tipagem, limite, limite_grade) {
    let q = parseInt(document.getElementById('quantidade_item_' + item).value) || 0;
    if (limite && q >= limite) return alert("Limite de " + limite + " itens atingido!");
    q++;
    document.getElementById('quantidade_item_' + item).value = q;
    itens(item, grade, valor, tipo, q, tipagem, limite_grade);
}

function dim(grade, item, valor, tipo, tipagem, limite, limite_grade) {
    let q = parseInt(document.getElementById('quantidade_item_' + item).value) || 0;
    if (q <= 0) return;
    q--;
    document.getElementById('quantidade_item_' + item).value = q;
    itens(item, grade, valor, tipo, q, tipagem, limite_grade);
}

function listarItens() {
    $.post('js/ajax/listar_itens_grade.php', { id: <?php echo $id_prod; ?> }, function(result) {
        const [val_calc, val_exib, val_base] = result.split('*');
        document.getElementById('valor_total_input').value = val_calc || '0';
        document.getElementById('valor_item_quantF').textContent = val_exib || '0,00';
        document.getElementById('valor_total_produto').value = val_base || '0';
        atualizarSubtotal();
    });
}

function addCarrinho() {
    const quantidade = document.getElementById('quantidade').value;
    const total_item = document.getElementById('valor_total_input').value;
    const produto = <?php echo json_encode($id_prod); ?>;
    const obs = document.getElementById('obs').value;
    const tem_var = <?php echo json_encode($tem_variacao); ?>;
    const mesa = <?php echo json_encode($id_ab_mesa); ?>;
    const valor_produto = document.getElementById('valor_total_produto').value;

    if (parseFloat(total_item) <= 0 && parseFloat(valor_produto) <= 0) {
        alert("O valor do pedido é zero. Selecione as opções!");
        return;
    }
    if (parseFloat(valor_produto) <= 0 && tem_var === 'Sim') {
        alert("Selecione a variação do item!");
        return;
    }

    $.post('js/ajax/add-carrinho.php', {
        quantidade, total_item, produto, obs, valor_produto, mesa
    }, function(mensagem) {
        if (mensagem.trim() === "Inserido com Sucesso") {
            window.location = 'carrinho';
        } else {
            alert(mensagem);
        }
    });
}

// Inicializar
$(document).ready(function() {
    document.getElementById('quant').textContent = '1';
});
</script>