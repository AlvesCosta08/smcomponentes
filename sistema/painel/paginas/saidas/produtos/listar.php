<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once("../../../conexao.php");
$tabela = 'produtos';
$busca = @$_POST['busca'];

// ✅ CORREÇÃO: Usar nomes de campos corretos da tabela
$select_fields = "id, nome, descricao, categoria, valor_compra, valor_venda, 
                 foto, ativo, url, ipi_percentual, custo_nfe_percentual, 
                 margem_lucro_percentual, codigo_referencia, fornecedor, 
                 estoque, nivel_estoque, tem_estoque, promocao, combo, 
                 delivery, preparado, valor_promocional";

if ($busca != "") {
    $query = $pdo->prepare("SELECT $select_fields FROM $tabela WHERE categoria = :busca ORDER BY id ASC");
    $query->bindValue(':busca', $busca);
    $query->execute();
} else {
    $query = $pdo->query("SELECT $select_fields FROM $tabela ORDER BY id ASC");
}

$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);

if ($total_reg > 0) {
    echo <<<HTML
    <small>
    <table class="table table-hover table-bordered text-nowrap border-bottom" id="tabela">
    <thead>
    <tr>
        <th align="center" width="5%" class="text-center">Selecionar</th>
        <th>Nome</th>    
        <th>Categoria</th>
        <th>Valor Venda</th>
        <th>Estoque</th>
        <th>Cod Referencia</th>
        <th>Ações</th>
    </tr>
    </thead>
    <tbody>
HTML;

    for ($i = 0; $i < $total_reg; $i++) {
        $id = $res[$i]['id'];
        $nome = $res[$i]['nome'];
        $descricao = $res[$i]['descricao'] ?? '';
        $categoria_id = $res[$i]['categoria'];
        $valor_compra = $res[$i]['valor_compra'] ?? 0;
        $valor_venda = $res[$i]['valor_venda'] ?? 0;
        $foto = $res[$i]['foto'] ?? 'sem-foto.jpg';
        $ativo = $res[$i]['ativo'] ?? 1;
        $ipi_percentual = $res[$i]['ipi_percentual'] ?? 0;
        $custo_nfe_percentual = $res[$i]['custo_nfe_percentual'] ?? 0;
        $margem_lucro_percentual = $res[$i]['margem_lucro_percentual'] ?? 30;
        $codigo_referencia = $res[$i]['codigo_referencia'] ?? '';
        $fornecedor_id = $res[$i]['fornecedor'] ?? 0;
        $estoque = $res[$i]['estoque'] ?? 0;
        $nivel_estoque = $res[$i]['nivel_estoque'] ?? 0;
        $tem_estoque = $res[$i]['tem_estoque'] ?? 1;
        $promocao = $res[$i]['promocao'] ?? 0;
        $combo = $res[$i]['combo'] ?? 0;
        $delivery = $res[$i]['delivery'] ?? 1;
        $preparado = $res[$i]['preparado'] ?? 0;
        $valor_promocional = $res[$i]['valor_promocional'] ?? 0;

        // Converter valores int para string onde necessário
        $tem_estoque_text = ($tem_estoque == 1) ? 'Sim' : 'Não';
        $ativo_text = ($ativo == 1) ? 'Sim' : 'Não';
        $preparado_text = ($preparado == 1) ? 'Sim' : 'Não';
        $promocao_text = ($promocao == 1) ? 'Sim' : 'Não';

        // Formatação de valores
        $nomeF = mb_strimwidth($nome, 0, 25, "...");
        $valor_vendaF = number_format((float)$valor_venda, 2, ',', '.');
        $valor_compraF = number_format((float)$valor_compra, 2, ',', '.');
        $valor_promocionalF = number_format((float)$valor_promocional, 2, ',', '.');

        // Status do produto - usar valores int da tabela
        if ($ativo == 1) {
            $icone = 'fa-check-square';
            $titulo_link = 'Desativar Item';
            $acao = '0';
            $classe_linha = '';
        } else {
            $icone = 'fa-square-o';
            $titulo_link = 'Ativar Item';
            $acao = '1';
            $classe_linha = '#c4c4c4';
        }

        // Buscar nome da categoria
        $query2 = $pdo->prepare("SELECT * FROM categorias WHERE id = :categoria_id");
        $query2->bindValue(':categoria_id', $categoria_id);
        $query2->execute();
        $res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($res2) > 0) {
            $nome_cat = $res2[0]['nome'];
            $mais_sabores = $res2[0]['mais_sabores'] ?? '';
        } else {
            $nome_cat = 'Sem Referência!';
            $mais_sabores = '';
        }

        // Buscar nome do fornecedor se existir
        $nome_fornecedor = '---';
        if ($fornecedor_id && $fornecedor_id > 0) {
            $query3 = $pdo->prepare("SELECT nome FROM fornecedores WHERE id = :fornecedor_id");
            $query3->bindValue(':fornecedor_id', $fornecedor_id);
            $query3->execute();
            $res3 = $query3->fetch(PDO::FETCH_ASSOC);
            if ($res3) {
                $nome_fornecedor = $res3['nome'];
            }
        }

        // Verificar alerta de estoque
        $alerta_estoque = '';
        if ($nivel_estoque >= $estoque && $tem_estoque == 1) {
            $alerta_estoque = 'text-danger';
        }

        // Verificar promoção
        $classe_promo = 'ocultar';
        if ($valor_promocional > 0) {
            $classe_promo = '';
        }

        // Configurações de grade/variação
        $ocultar_grade = '';
        $ocultar_var = 'ocultar';
        if ($mais_sabores == 'Sim') {
            $ocultar_grade = 'ocultar';
            $ocultar_var = '';
        }

        // Mostrar entrada/saída
        $mostrar_ent_said = '';
        if ($tem_estoque != 1) {
            $mostrar_ent_said = 'ocultar';
        }

        // Cor de fundo baseada no estoque
        $cor = '';
        if ($estoque == 0 && $tem_estoque == 1) {
            $cor = '#ffdddd';
        } else if ($estoque <= $nivel_estoque && $tem_estoque == 1) {
            $cor = '#fff8dd';
        } else if ($estoque > 0) {
            $cor = '#ddffe1';
        }

        echo <<<HTML
        <input type="hidden" id="descricao_{$id}" value="{$descricao}">
        <input type="hidden" id="codigo_referencia_{$id}" value="{$codigo_referencia}">
        <input type="hidden" id="ipi_percentual_{$id}" value="{$ipi_percentual}">
        <input type="hidden" id="custo_nfe_percentual_{$id}" value="{$custo_nfe_percentual}">
        <input type="hidden" id="margem_lucro_percentual_{$id}" value="{$margem_lucro_percentual}">
        <input type="hidden" id="fornecedor_id_{$id}" value="{$fornecedor_id}">

        <tr style="background: {$cor}" class="{$alerta_estoque}">
            <td align="center">
                <div class="custom-checkbox custom-control">
                    <input type="checkbox" class="custom-control-input" id="seletor-{$id}" onchange="selecionar('{$id}')">
                    <label for="seletor-{$id}" class="custom-control-label mt-1 text-dark"></label>
                </div>
            </td>
            <td style="color:{$classe_linha}">
                <img class="rounded-circle mr-2" src="images/produtos/{$foto}" width="30px" height="30px" onerror="this.src='images/produtos/sem-foto.jpg'">
                {$nomeF} <span class="{$classe_promo} text-primary" style="font-size: 10px">(Promoção)</span>
            </td>
            <td style="color:{$classe_linha}">{$nome_cat}</td>
            <td style="color:{$classe_linha}">R\$ {$valor_vendaF}</td>
            <td style="color:{$classe_linha}">{$estoque}</td>
            <td style="color:{$classe_linha}">{$codigo_referencia}</td>
            <td>
                <a class="btn btn-info-light btn-sm" 
                   href="#" 
                   data-editar 
                   data-id="{$id}" 
                   data-nome="{$nome}" 
                   data-categoria="{$categoria_id}" 
                   data-valor-compra="{$valor_compra}" 
                   data-valor-venda="{$valor_venda}" 
                   data-foto="{$foto}" 
                   data-nivel-estoque="{$nivel_estoque}" 
                   data-tem-estoque="{$tem_estoque}" 
                   data-preparado="{$preparado}" 
                   data-val-promocional="{$valor_promocional}" 
                   title="Editar Dados">
                    <i class="fa fa-edit"></i>
                </a>
                <a class="btn btn-primary-light btn-sm" href="#" onclick="mostrar('{$id}','{$nome}', '{$nome_cat}', '{$valor_compraF}', '{$valor_vendaF}', '{$estoque}', '{$foto}', '{$nivel_estoque}', '{$tem_estoque_text}', '{$preparado_text}', '{$valor_promocionalF}', '{$ipi_percentual}', '{$custo_nfe_percentual}', '{$margem_lucro_percentual}', '{$codigo_referencia}')" title="Ver Dados"><i class="fa fa-info-circle"></i></a>
                <big><a href="#" class="btn btn-danger-light btn-sm" onclick="excluir('{$id}')" title="Excluir"><i class="fa fa-trash-can text-danger"></i></a></big>
                <a class="btn btn-success-light btn-sm" href="#" onclick="ativar('{$id}', '{$acao}')" title="{$titulo_link}"><i class="fa {$icone}"></i></a>
                <a class="btn btn-danger-light btn-sm {$mostrar_ent_said}" href="#" onclick="saida('{$id}','{$nome}', '{$estoque}')" title="Saída de Produto"><i class="fa fa-sign-out"></i></a>
                <a class="btn btn-success-light btn-sm {$mostrar_ent_said}" href="#" onclick="entrada('{$id}','{$nome}', '{$estoque}')" title="Entrada de Produto"><i class="fa fa-sign-in"></i></a>
                <a class="{$ocultar_grade} btn btn-warning-light btn-sm" href="#" onclick="grades('{$id}','{$nome}','{$categoria_id}')" title="Grade de Produtos"><i class="fa fa-list"></i></a>
                <a class="{$ocultar_var} btn btn-info-light btn-sm" href="#" onclick="variacoes('{$id}','{$nome}','{$categoria_id}')" title="Variações do Produto"><i class="fa fa-list"></i></a>
            </td>
        </tr>
HTML;
    }

    echo <<<HTML
    </tbody>
    <small><div align="center" id="mensagem-excluir"></div></small>
    </table>
    </small>
HTML;

} else {
    echo '<div class="alert alert-info text-center">Não possui registros cadastrados!</div>';
}
?>