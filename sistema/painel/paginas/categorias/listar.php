<?php
require_once("../../../conexao.php");
$tabela = 'categorias';

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->prepare("SELECT * FROM $tabela ORDER BY id DESC");
    $stmt->execute();
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($categorias)) {
        echo '<div class="alert alert-info">Nenhuma categoria cadastrada.</div>';
        return;
    }

    echo <<<HTML
<small>
<table class="table table-hover table-bordered text-nowrap border-bottom dt-responsive" id="tabela">
<thead> 
<tr> 
	<th class="text-center" width="5%">Selecionar</th>
	<th>Foto</th>
	<th>Nome</th>	
	<th>Descrição</th> 		
	<th>Delivery</th> 
	<th>Produtos</th> 
	<th>Ações</th>
</tr> 
</thead> 
<tbody>
HTML;

    foreach ($categorias as $cat) {
        $id = (int)$cat['id'];
        $nome = htmlspecialchars($cat['nome'] ?? '');
        $ativo = $cat['ativo'] ?? 'Não';
        $foto = htmlspecialchars($cat['foto'] ?? 'sem-foto.jpg');
        $descricao = htmlspecialchars($cat['descricao'] ?? '');
        $mais_sabores = $cat['mais_sabores'] ?? 'Não';
        $delivery = htmlspecialchars($cat['delivery'] ?? '');

        // Truncar descrição com segurança
        $descricaoF = mb_strlen($descricao) > 50 
            ? mb_substr($descricao, 0, 50, 'UTF-8') . '...' 
            : $descricao;

        // Definir status ativo/desativo
        if ($ativo === 'Sim') {
            $icone = 'fa-check-square';
            $titulo_link = 'Desativar Item';
            $acao = 'Não';
            $classe_linha = '';
        } else {
            $icone = 'fa-square';
            $titulo_link = 'Ativar Item';
            $acao = 'Sim';
            $classe_linha = '#c4c4c4';
        }

        // Contar produtos com prepared statement
        $stmtProd = $pdo->prepare("SELECT COUNT(*) FROM produtos WHERE categoria = ?");
        $stmtProd->execute([$id]);
        $produtos = (int)$stmtProd->fetchColumn();

        // Exibir botões condicionalmente
        $ocultar_ad = ($mais_sabores === 'Sim') ? '' : 'd-none';

        echo <<<HTML
<tr style="background-color: {$classe_linha}">
	<td class="text-center">
		<div class="form-check">
			<input type="checkbox" class="form-check-input" id="seletor-{$id}" onchange="selecionar({$id})">
			<label class="form-check-label" for="seletor-{$id}"></label>
		</div>
	</td>
	<td class="text-center">
		<img src="images/categorias/{$foto}" 
			 onerror="this.src='images/categorias/sem-foto.jpg'" 
			 class="rounded-circle" width="30" height="30" alt="Foto da categoria">
	</td>
	<td>{$nome}</td>
	<td>{$descricaoF}</td>
	<td>{$delivery}</td>
	<td class="text-center">{$produtos}</td>
	<td>
		<button type="button" class="btn btn-info-light btn-sm" 
				onclick="editar({$id}, '{$nome}', '{$descricao}', '{$foto}', '{$mais_sabores}', '{$delivery}')" 
				title="Editar Dados">
			<i class="fa fa-edit"></i>
		</button>

		<button type="button" class="btn btn-danger-light btn-sm" 
				onclick="excluir({$id})" 
				title="Excluir">
			<i class="fa fa-trash-can text-danger"></i>
		</button>

		<button type="button" class="btn btn-success-light btn-sm" 
				onclick="ativar({$id}, '{$acao}')" 
				title="{$titulo_link}">
			<i class="fa {$icone}"></i>
		</button>

		<button type="button" class="btn btn-primary-light btn-sm {$ocultar_ad}" 
				onclick="variacoes({$id}, '{$nome}')" 
				title="Variações do Produto">
			<i class="fa fa-list"></i>
		</button>

		<button type="button" class="btn btn-success-light btn-sm {$ocultar_ad}" 
				onclick="adicionais({$id}, '{$nome}')" 
				title="Adicionais da Categoria">
			<i class="fa fa-plus"></i>
		</button>

		<button type="button" class="btn btn-warning-light btn-sm {$ocultar_ad}" 
				onclick="bordas({$id}, '{$nome}')" 
				title="Opções de Bordas">
			<i class="fa fa-plus-circle"></i>
		</button>
	</td>
</tr>
HTML;
    }

    echo <<<HTML
</tbody>
</table>
<div id="mensagem-excluir" class="text-center mt-3"></div>
</small>
HTML;

} catch (PDOException $e) {
    error_log("Erro ao carregar categorias: " . $e->getMessage());
    echo '<div class="alert alert-danger">Erro ao carregar os dados.</div>';
}
?>

<script>
$(document).ready(function() {
	$('#tabela').DataTable({
		"ordering": false,
		"stateSave": true,
		"language": {
			"url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json"
		}
	});
	$('#tabela_filter input').focus();
});

// Funções JS (mantidas, mas com melhorias implícitas pela correção do HTML)
</script>

<script>
function editar(id, nome, descricao, foto, mais_sabores, delivery) {
	$('#id').val(id);
	$('#nome').val(nome);
	$('#descricao').val(descricao);
	$('#mais_sabores').val(mais_sabores).trigger('change');
	$('#delivery').val(delivery).trigger('change');
	$('#titulo_inserir').text('Editar Registro');
	$('#modalForm').modal('show');
	$('#foto').val('');
	$('#target').attr('src', 'images/categorias/' + foto);
}

function limparCampos() {
	$('#id').val('');
	$('#nome').val('');
	$('#descricao').val('');
	$('#foto').val('');
	$('#delivery').val('Sim').trigger('change');
	$('#target').attr('src', 'images/categorias/sem-foto.jpg');
}

function variacoes(id, nome) {
	$('#titulo_nome_var').text(nome);
	$('#id_var').val(id);
	listarVariacoes(id);
	$('#modalVariacoes').modal('show');
	$('#btn_var').text('Salvar');
	$('#id_var_editar').val('');
	limparCamposVar();
}

function adicionais(id, nome) {
	$('#titulo_nome_adc').text(nome);
	$('#id_adc').val(id);
	$('#btn_editar_adc').text('Salvar');
	$('#id_adc_editar').val('');
	limparCamposVar();
	listarAdicionais(id);
	$('#modalAdicionais').modal('show');
	limparCamposAdc();
}

function bordas(id, nome) {
	$('#titulo_nome_bordas').text(nome);
	$('#id_bordas').val(id);
	listarBordas(id);
	$('#modalBordas').modal('show');
	limparCamposBordas();
}
</script>