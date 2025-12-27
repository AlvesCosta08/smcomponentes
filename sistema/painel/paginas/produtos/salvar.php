<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verificar se é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Erro: Método inválido');
}

// Conexão
require_once("../../../conexao.php");

if (!$pdo) {
    die('Erro: Conexão com banco de dados não estabelecida');
}

$tabela = 'produtos';

// ✅ FUNÇÃO MELHORADA PARA CONVERSÃO
function converterValor($valor) {
    if (empty($valor) || $valor === '' || $valor === '0,00') {
        return 0.00;
    }
    
    // Remover R$ e espaços
    $valor = str_replace(['R$', ' '], '', $valor);
    
    // Verificar formato
    if (strpos($valor, ',') !== false && strpos($valor, '.') !== false) {
        // Formato: 1.234,56
        $valor = str_replace('.', '', $valor);
        $valor = str_replace(',', '.', $valor);
    } elseif (strpos($valor, ',') !== false) {
        // Formato: 1234,56 ou 3,52
        $valor = str_replace(',', '.', $valor);
    }
    
    // Garantir que seja float
    return floatval($valor);
}

// ✅ FUNÇÃO PARA FORMATAR PARA O BANCO
function formatarParaBanco($valor) {
    $valor_float = floatval($valor);
    return number_format($valor_float, 2, ',', '');
}

// RECEBER DADOS
$id = isset($_POST['id']) ? trim($_POST['id']) : '';

// Log para debug
error_log("=== SALVAR.PHP ===");
error_log("ID recebido: '{$id}'");
error_log("Método: " . (empty($id) ? 'INSERT' : 'UPDATE'));

// VALIDAÇÕES
if (empty($_POST['nome']) || trim($_POST['nome']) === '') {
    die('Nome do produto é obrigatório');
}

if (empty($_POST['categoria']) || !is_numeric($_POST['categoria'])) {
    die('Selecione uma Categoria válida');
}

// Preparar dados
$nome = trim($_POST['nome']);
$descricao = trim($_POST['descricao'] ?? '');
$categoria = intval($_POST['categoria']);
$codigo_referencia = trim($_POST['codigo_referencia'] ?? '');

// Valores monetários
$valor_compra = converterValor($_POST['valor_compra'] ?? '0');
$valor_promocional = converterValor($_POST['val_promocional'] ?? '0');

// Percentuais
$ipi_percentual = floatval($_POST['percentual_ipi'] ?? 0);
$custo_nfe_percentual = floatval($_POST['percentual_custo_nfe'] ?? 0);
$margem_lucro_percentual = floatval($_POST['percentual_margem_lucro'] ?? 30);

// Estoque
$nivel_estoque = intval($_POST['nivel_estoque'] ?? 0);
$estoque = intval($_POST['estoque'] ?? 0);
$tem_estoque = ($_POST['tem_estoque'] ?? 'Não') === 'Sim' ? 1 : 0;

// Outros campos
$preparado = ($_POST['preparado'] ?? 'Não') === 'Sim' ? 1 : 0;
$fornecedor = !empty($_POST['fornecedor']) ? intval($_POST['fornecedor']) : null;

// Campos fixos
$ativo = 1;
$url = '';
$promocao = ($valor_promocional > 0) ? 1 : 0;
$combo = 0;
$delivery = 1;

// ✅ CÁLCULO DO VALOR DE VENDA
$custo_ipi = $valor_compra * ($ipi_percentual / 100);
$custo_nfe = $valor_compra * ($custo_nfe_percentual / 100);
$custo_total = $valor_compra + $custo_ipi + $custo_nfe;
$valor_venda = $custo_total * (1 + ($margem_lucro_percentual / 100));
$valor_venda = round($valor_venda, 2);

// Formatar para varchar
$valor_venda_str = formatarParaBanco($valor_venda);
$valor_compra_str = formatarParaBanco($valor_compra);
$valor_promocional_str = formatarParaBanco($valor_promocional);

// ✅ VALIDAÇÃO: valor promocional deve ser menor que valor de venda
if ($valor_promocional > 0 && $valor_promocional >= $valor_venda) {
    die('Erro: O valor promocional deve ser menor que o valor de venda!');
}

// ✅ VERIFICAÇÃO DE DUPLICIDADE
try {
    if (empty($id)) {
        // INSERÇÃO
        $sql_check = "SELECT id FROM $tabela WHERE nome = :nome";
        $stmt_check = $pdo->prepare($sql_check);
        $stmt_check->execute([':nome' => $nome]);
        
        if ($stmt_check->rowCount() > 0) {
            die('Já existe um produto com este nome!');
        }
    } else {
        // EDIÇÃO
        $sql_check = "SELECT id FROM $tabela WHERE nome = :nome AND id != :id";
        $stmt_check = $pdo->prepare($sql_check);
        $stmt_check->execute([':nome' => $nome, ':id' => $id]);
        
        if ($stmt_check->rowCount() > 0) {
            die('Já existe outro produto com este nome!');
        }
    }
} catch (PDOException $e) {
    die('Erro ao verificar nome: ' . $e->getMessage());
}

// ✅ TRATAMENTO DA FOTO
$foto = 'sem-foto.jpg';

// Se está editando, manter foto atual
if (!empty($id)) {
    try {
        $sql_foto = "SELECT foto FROM $tabela WHERE id = :id";
        $stmt_foto = $pdo->prepare($sql_foto);
        $stmt_foto->execute([':id' => $id]);
        $foto_atual = $stmt_foto->fetchColumn();
        
        if ($foto_atual && $foto_atual !== 'sem-foto.jpg') {
            $foto = $foto_atual;
        }
    } catch (PDOException $e) {
        // Continua com foto padrão
    }
}

// ✅ UPLOAD DE NOVA FOTO
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
    $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $extensao = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
    
    if (in_array($extensao, $extensoes_permitidas)) {
        $nome_imagem = 'produto_' . date('YmdHis') . '_' . uniqid() . '.' . $extensao;
        $diretorio = '../../images/produtos/';
        $caminho_destino = $diretorio . $nome_imagem;
        
        // Criar diretório se não existir
        if (!is_dir($diretorio)) {
            mkdir($diretorio, 0755, true);
        }
        
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $caminho_destino)) {
            // Excluir foto antiga se existir
            if ($foto !== 'sem-foto.jpg' && file_exists($diretorio . $foto)) {
                @unlink($diretorio . $foto);
            }
            $foto = $nome_imagem;
        }
    }
}

// ✅ SALVAR NO BANCO
try {
    if (empty($id)) {
        // INSERT
        $sql = "INSERT INTO $tabela SET
            nome = :nome,
            descricao = :descricao,
            categoria = :categoria,
            valor_compra = :valor_compra,
            valor_venda = :valor_venda,
            foto = :foto,
            ativo = :ativo,
            url = :url,
            ipi_percentual = :ipi_percentual,
            custo_nfe_percentual = :custo_nfe_percentual,
            margem_lucro_percentual = :margem_lucro_percentual,
            codigo_referencia = :codigo_referencia,
            fornecedor = :fornecedor,
            estoque = :estoque,
            nivel_estoque = :nivel_estoque,
            tem_estoque = :tem_estoque,
            promocao = :promocao,
            combo = :combo,
            delivery = :delivery,
            preparado = :preparado,
            valor_promocional = :valor_promocional";
        
        $stmt = $pdo->prepare($sql);
    } else {
        // UPDATE
        $sql = "UPDATE $tabela SET
            nome = :nome,
            descricao = :descricao,
            categoria = :categoria,
            valor_compra = :valor_compra,
            valor_venda = :valor_venda,
            foto = :foto,
            ipi_percentual = :ipi_percentual,
            custo_nfe_percentual = :custo_nfe_percentual,
            margem_lucro_percentual = :margem_lucro_percentual,
            codigo_referencia = :codigo_referencia,
            fornecedor = :fornecedor,
            estoque = :estoque,
            nivel_estoque = :nivel_estoque,
            tem_estoque = :tem_estoque,
            promocao = :promocao,
            preparado = :preparado,
            valor_promocional = :valor_promocional
            WHERE id = :id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id);
    }
    
    // Bind dos valores
    $stmt->bindValue(':nome', $nome);
    $stmt->bindValue(':descricao', $descricao);
    $stmt->bindValue(':categoria', $categoria);
    $stmt->bindValue(':valor_compra', $valor_compra_str);
    $stmt->bindValue(':valor_venda', $valor_venda_str);
    $stmt->bindValue(':foto', $foto);
    $stmt->bindValue(':ipi_percentual', $ipi_percentual);
    $stmt->bindValue(':custo_nfe_percentual', $custo_nfe_percentual);
    $stmt->bindValue(':margem_lucro_percentual', $margem_lucro_percentual);
    $stmt->bindValue(':codigo_referencia', $codigo_referencia);
    $stmt->bindValue(':fornecedor', $fornecedor);
    $stmt->bindValue(':estoque', $estoque);
    $stmt->bindValue(':nivel_estoque', $nivel_estoque);
    $stmt->bindValue(':tem_estoque', $tem_estoque);
    $stmt->bindValue(':preparado', $preparado);
    $stmt->bindValue(':valor_promocional', $valor_promocional_str);
    
    if (empty($id)) {
        $stmt->bindValue(':ativo', $ativo);
        $stmt->bindValue(':url', $url);
        $stmt->bindValue(':combo', $combo);
        $stmt->bindValue(':delivery', $delivery);
        $stmt->bindValue(':promocao', $promocao);
    } else {
        $stmt->bindValue(':promocao', $promocao);
    }
    
    $result = $stmt->execute();
    
    if ($result) {
        $produto_id = empty($id) ? $pdo->lastInsertId() : $id;
        error_log("✅ Produto salvo com sucesso! ID: {$produto_id}");
        echo 'Salvo com Sucesso';
    } else {
        error_log("❌ Erro ao executar query");
        echo 'Erro ao salvar produto';
    }
    
} catch (PDOException $e) {
    error_log("❌ Erro PDO: " . $e->getMessage());
    echo 'Erro ao salvar produto: ' . $e->getMessage();
}
?>