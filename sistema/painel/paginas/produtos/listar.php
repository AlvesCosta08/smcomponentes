<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once("../../../conexao.php");
$tabela = 'produtos';
$busca = isset($_POST['busca']) ? $_POST['busca'] : '';

$select_fields = "id, nome, descricao, categoria, valor_compra, 
    valor_venda, foto, ativo, ipi_percentual, custo_nfe_percentual,
    margem_lucro_percentual, codigo_referencia, fornecedor, estoque, 
    nivel_estoque, tem_estoque, promocao, preparado, valor_promocional";

if ($busca != "") {
    $query = $pdo->prepare("SELECT $select_fields FROM $tabela WHERE categoria = :busca AND ativo = '1' ORDER BY nome ASC");
    $query->bindValue(':busca', $busca);
    $query->execute();
} else {
    $query = $pdo->query("SELECT $select_fields FROM $tabela WHERE ativo = '1' ORDER BY nome ASC");
}

$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = count($res);

// 🔧 FUNÇÃO SIMPLIFICADA PARA FORMATAR VALORES
function formatarValor($valor) {
    if ($valor === null || $valor === '' || $valor === 0 || $valor === '0') {
        return '0,00';
    }
    
    if (is_string($valor) && strpos($valor, ',') !== false) {
        return $valor;
    }
    
    $valor_float = floatval($valor);
    return number_format($valor_float, 2, ',', '');
}

if ($total_reg > 0) {
    $html = '<small>
    <table class="table table-hover table-bordered text-nowrap border-bottom" id="tabela">
    <thead>
    <tr>
        <th align="center" width="5%" class="text-center">
            <input type="checkbox" id="selecionar-todos" onclick="selecionarTodos()" title="Selecionar Todos">
        </th>
        <th>Nome</th>    
        <th>Categoria</th>
        <th>Valor Venda</th>
        <th>Estoque</th>
        <th>Cod Referência</th>
        <th>Ações</th>
    </tr>
    </thead>
    <tbody>';
    
    for ($i = 0; $i < $total_reg; $i++) {
        $id = $res[$i]['id'];
        $nome = htmlspecialchars($res[$i]['nome']);
        $descricao = htmlspecialchars($res[$i]['descricao'] ?? '');
        $categoria_id = $res[$i]['categoria'];
        $valor_compra = $res[$i]['valor_compra'];
        $valor_venda = $res[$i]['valor_venda'];
        $foto = $res[$i]['foto'];
        $ipi_percentual = $res[$i]['ipi_percentual'];
        $custo_nfe_percentual = $res[$i]['custo_nfe_percentual'];
        $margem_lucro_percentual = $res[$i]['margem_lucro_percentual'];
        $codigo_referencia = htmlspecialchars($res[$i]['codigo_referencia'] ?? '');
        $fornecedor_id = $res[$i]['fornecedor'];
        $estoque = $res[$i]['estoque'];
        $nivel_estoque = $res[$i]['nivel_estoque'];
        $tem_estoque = $res[$i]['tem_estoque'];
        $promocao = $res[$i]['promocao'];
        $preparado = $res[$i]['preparado'];
        $valor_promocional = $res[$i]['valor_promocional'];
        $ativo = $res[$i]['ativo'];

        // Formatar valores
        $nomeF = mb_strimwidth($nome, 0, 25, "...");
        
        $valor_vendaF = 'R$ ' . formatarValor($valor_venda);
        $valor_compraF = 'R$ ' . formatarValor($valor_compra);
        $valor_promocionalF = ($valor_promocional > 0) ? 'R$ ' . formatarValor($valor_promocional) : 'R$ 0,00';
        
        $valor_compra_raw = $valor_compra;
        $valor_promocional_raw = $valor_promocional;

        // Status do produto
        if ($ativo == 1) {
            $icone = 'fa-check-square';
            $titulo_link = 'Desativar Item';
            $acao = '0';
            $classe_linha = '';
            $btn_class = 'success';
        } else {
            $icone = 'fa-square-o';
            $titulo_link = 'Ativar Item';
            $acao = '1';
            $classe_linha = 'text-muted';
            $btn_class = 'secondary';
        }

        // Buscar nome da categoria
        $nome_cat = 'Sem Categoria';
        if ($categoria_id > 0) {
            $query2 = $pdo->prepare("SELECT nome FROM categorias WHERE id = :categoria_id");
            $query2->bindValue(':categoria_id', $categoria_id);
            $query2->execute();
            $res2 = $query2->fetch(PDO::FETCH_ASSOC);
            $nome_cat = $res2 ? htmlspecialchars($res2['nome']) : 'Sem Categoria';
        }

        // Verificar alerta de estoque
        $alerta_estoque = '';
        if ($nivel_estoque >= $estoque && $tem_estoque == 1) {
            $alerta_estoque = 'text-danger fw-bold';
        }

        // Verificar promoção
        $classe_promo = 'd-none';
        if ($promocao == 1 && $valor_promocional > 0) {
            $classe_promo = '';
        }

        // Mostrar entrada/saída
        $mostrar_ent_said = '';
        if ($tem_estoque == 0) {
            $mostrar_ent_said = 'd-none';
        }

        // Cor de fundo baseada no estoque
        $cor_fundo = '';
        if ($estoque == 0 && $tem_estoque == 1) {
            $cor_fundo = 'background-color: #ffebee !important;';
        } else if ($estoque <= $nivel_estoque && $tem_estoque == 1) {
            $cor_fundo = 'background-color: #fff3cd !important;';
        } else if ($estoque > 0) {
            $cor_fundo = 'background-color: #d1e7dd !important;';
        }

        // Converter valores boolean para string
        $tem_estoque_str = ($tem_estoque == 1) ? 'Sim' : 'Não';
        $preparado_str = ($preparado == 1) ? 'Sim' : 'Não';
        
        // Adicionar linha HTML
        $html .= "
        <input type=\"hidden\" id=\"descricao_{$id}\" value=\"{$descricao}\">
        <input type=\"hidden\" id=\"codigo_referencia_{$id}\" value=\"{$codigo_referencia}\">
        <input type=\"hidden\" id=\"percentual_ipi_{$id}\" value=\"{$ipi_percentual}\">
        <input type=\"hidden\" id=\"percentual_custo_nfe_{$id}\" value=\"{$custo_nfe_percentual}\">
        <input type=\"hidden\" id=\"percentual_margem_lucro_{$id}\" value=\"{$margem_lucro_percentual}\">
        <input type=\"hidden\" id=\"fornecedor_id_{$id}\" value=\"{$fornecedor_id}\">

        <tr style=\"{$cor_fundo}\">
            <td align=\"center\">
                <div class=\"form-check\">
                    <input type=\"checkbox\" class=\"form-check-input\" id=\"seletor-{$id}\" onchange=\"selecionar('{$id}')\">
                    <label for=\"seletor-{$id}\" class=\"form-check-label\"></label>
                </div>
            </td>
            <td class=\"{$classe_linha}\">
                <img class=\"rounded-circle mr-2\" src=\"images/produtos/{$foto}\" width=\"30px\" height=\"30px\" onerror=\"this.src='images/produtos/sem-foto.jpg'\">
                {$nomeF} <span class=\"{$classe_promo} text-primary\" style=\"font-size: 10px\">(Promoção)</span>
            </td>
            <td class=\"{$classe_linha}\">{$nome_cat}</td>
            <td class=\"{$classe_linha} {$alerta_estoque}\">{$valor_vendaF}</td>
            <td class=\"{$classe_linha} {$alerta_estoque}\">{$estoque}</td>
            <td class=\"{$classe_linha}\">{$codigo_referencia}</td>
            <td>
                <div class=\"btn-group btn-group-sm\" role=\"group\">
                    <a class=\"btn btn-outline-info\" 
                       href=\"#\" 
                       data-editar 
                       data-id=\"{$id}\" 
                       data-nome=\"{$nome}\" 
                       data-categoria=\"{$categoria_id}\" 
                       data-valor-compra=\"{$valor_compra_raw}\" 
                       data-foto=\"{$foto}\" 
                       data-nivel-estoque=\"{$nivel_estoque}\" 
                       data-tem-estoque=\"{$tem_estoque_str}\" 
                       data-preparado=\"{$preparado_str}\" 
                       data-val-promocional=\"{$valor_promocional_raw}\" 
                       title=\"Editar Dados\">
                        <i class=\"fa fa-edit\"></i>
                    </a>
                    
                    <a class=\"btn btn-outline-primary\" href=\"#\" 
                       onclick=\"dados('{$id}','{$nome}','{$nome_cat}','{$valor_compraF}','{$valor_vendaF}','{$estoque}','{$foto}','{$nivel_estoque}','{$tem_estoque_str}','{$preparado_str}','{$valor_promocionalF}','{$ipi_percentual}','{$custo_nfe_percentual}','{$margem_lucro_percentual}','{$codigo_referencia}')\" 
                       title=\"Ver Dados\">
                        <i class=\"fa fa-info-circle\"></i>
                    </a>
                    
                    <a href=\"#\" class=\"btn btn-outline-danger\" onclick=\"excluir('{$id}')\" title=\"Excluir\">
                        <i class=\"fa fa-trash\"></i>
                    </a>
                    
                    <a class=\"btn btn-outline-{$btn_class}\" 
                       href=\"#\" onclick=\"ativar('{$id}', '{$acao}')\" 
                       title=\"{$titulo_link}\">
                        <i class=\"fa {$icone}\"></i>
                    </a>
                    
                    <a class=\"btn btn-outline-warning {$mostrar_ent_said}\" 
                       href=\"#\" onclick=\"saida('{$id}','{$nome}', '{$estoque}')\" 
                       title=\"Saída de Produto\">
                        <i class=\"fa fa-sign-out\"></i>
                    </a>
                    
                    <a class=\"btn btn-outline-success {$mostrar_ent_said}\" 
                       href=\"#\" onclick=\"entrada('{$id}','{$nome}', '{$estoque}')\" 
                       title=\"Entrada de Produto\">
                        <i class=\"fa fa-sign-in\"></i>
                    </a>
                </div>
            </td>
        </tr>";
    }
    
    $html .= "
    </tbody>
    </table>
    
    <div class=\"alert alert-info mt-3\">
        <i class=\"fa fa-info-circle\"></i> Total de produtos: <strong>{$total_reg}</strong>
    </div>
    </small>";
    
    echo $html;
} else {
    echo '<div class="alert alert-warning text-center">Nenhum produto cadastrado!</div>';
}