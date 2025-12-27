<?php
// Debug - Ativar display de erros
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);

// Verificar se é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo 'Erro: Método inválido. Use POST.';
    exit();
}

// Conexão
require_once("../../../conexao.php");

// Verificar conexão
if (!isset($pdo)) {
    echo 'Erro: Conexão com banco de dados não estabelecida.';
    exit();
}

$tabela = 'produtos';

// Função para converter valores brasileiros
function converterValorBrasileiro($valor) {
    if (empty($valor) || $valor === '' || $valor === '0,00' || $valor === '0.00') {
        return 0.00;
    }
    
    $valor = preg_replace('/[^\d,\.]/', '', $valor);
    
    if (strpos($valor, ',') !== false && strpos($valor, '.') !== false) {
        $valor = str_replace('.', '', $valor);
        $valor = str_replace(',', '.', $valor);
    } elseif (strpos($valor, ',') !== false) {
        $valor = str_replace(',', '.', $valor);
    }
    
    return floatval($valor);
}

// RECEBER E VALIDAR DADOS
$id = isset($_POST['id']) ? trim($_POST['id']) : '';

// Debug: verificar se o ID está chegando
error_log("ID recebido no salvar.php: '" . $id . "'");
error_log("Tipo do ID: " . gettype($id));
error_log("ID vazio? " . (empty($id) ? 'Sim' : 'Não'));

if (!isset($_POST['nome']) || trim($_POST['nome']) === '') {
    echo 'Nome do produto é obrigatório';
    exit();
}

if (!isset($_POST['categoria']) || empty($_POST['categoria'])) {
    echo 'Selecione uma Categoria válida';
    exit();
}

// Receber dados
$nome = trim($_POST['nome']);
$descricao = isset($_POST['descricao']) ? trim($_POST['descricao']) : '';
$categoria = intval($_POST['categoria']);
$codigo_referencia = isset($_POST['codigo_referencia']) ? trim($_POST['codigo_referencia']) : '';

// Valores monetários
$valor_compra = converterValorBrasileiro($_POST['valor_compra'] ?? '0');
$valor_promocional = converterValorBrasileiro($_POST['val_promocional'] ?? '0');

// Percentuais
$ipi_percentual = floatval($_POST['percentual_ipi'] ?? 0);
$custo_nfe_percentual = floatval($_POST['percentual_custo_nfe'] ?? 0);
$margem_lucro_percentual = floatval($_POST['percentual_margem_lucro'] ?? 30);

// Estoque
$nivel_estoque = intval($_POST['nivel_estoque'] ?? 0);
$estoque = intval($_POST['estoque'] ?? 0);
$tem_estoque = isset($_POST['tem_estoque']) && $_POST['tem_estoque'] === 'Sim' ? 1 : 0;

// Outros campos
$preparado = isset($_POST['preparado']) && $_POST['preparado'] === 'Sim' ? 1 : 0;
$fornecedor = isset($_POST['fornecedor']) && !empty($_POST['fornecedor']) ? intval($_POST['fornecedor']) : null;

// Campos fixos
$ativo = 1;
$url = '';
$promocao = ($valor_promocional > 0) ? 1 : 0;
$combo = 0;
$delivery = 1;

// CÁLCULOS - Calcular valor de venda baseado nos percentuais
$custo_ipi = $valor_compra * ($ipi_percentual / 100);
$custo_nfe = $valor_compra * ($custo_nfe_percentual / 100);
$custo_total = $valor_compra + $custo_ipi + $custo_nfe;
$valor_venda = $custo_total * (1 + ($margem_lucro_percentual / 100));
$valor_venda = round($valor_venda, 2);

// Formatar valor_venda para varchar (como está na tabela)
$valor_venda_str = number_format($valor_venda, 2, ',', '');

// Verificar se valor_promocional é menor que valor_venda
if ($valor_promocional > 0 && $valor_promocional >= $valor_venda) {
    echo 'Erro: O valor promocional deve ser menor que o valor de venda!';
    exit();
}

// ✅ CORREÇÃO: VERIFICAÇÃO DE DUPLICIDADE MELHORADA
try {
    if (empty($id)) {
        // INSERÇÃO: Verificar se nome já existe
        error_log("Modo: INSERÇÃO - Verificando duplicidade para: " . $nome);
        $sql_check = "SELECT id FROM $tabela WHERE nome = :nome";
        $stmt_check = $pdo->prepare($sql_check);
        $stmt_check->execute([':nome' => $nome]);
        
        if ($stmt_check->rowCount() > 0) {
            error_log("Produto duplicado encontrado durante INSERÇÃO");
            echo 'Já existe um produto com este nome!';
            exit();
        }
        error_log("Nenhum produto duplicado encontrado - OK para inserir");
    } else {
        // EDIÇÃO: Verificar se outro produto tem o mesmo nome
        error_log("Modo: EDIÇÃO - ID: " . $id . " - Verificando duplicidade para: " . $nome);
        
        // Primeiro, verificar qual é o nome atual do produto
        $sql_current = "SELECT nome FROM $tabela WHERE id = :id";
        $stmt_current = $pdo->prepare($sql_current);
        $stmt_current->execute([':id' => $id]);
        $current_product = $stmt_current->fetch(PDO::FETCH_ASSOC);
        
        if ($current_product) {
            $current_nome = $current_product['nome'];
            error_log("Nome atual do produto ID {$id}: '{$current_nome}'");
            
            // Se o nome não mudou, não precisa verificar duplicidade
            if ($current_nome == $nome) {
                error_log("Nome não alterado - sem necessidade de verificar duplicidade");
            } else {
                // Nome mudou, verificar se outro produto tem este nome
                $sql_check = "SELECT id FROM $tabela WHERE nome = :nome AND id != :id";
                $stmt_check = $pdo->prepare($sql_check);
                $stmt_check->execute([':nome' => $nome, ':id' => $id]);
                
                if ($stmt_check->rowCount() > 0) {
                    error_log("Outro produto encontrado com este nome durante EDIÇÃO");
                    echo 'Já existe outro produto com este nome!';
                    exit();
                }
                error_log("Nenhum outro produto com este nome encontrado - OK para editar");
            }
        } else {
            error_log("Produto não encontrado com ID: " . $id);
            echo 'Produto não encontrado!';
            exit();
        }
    }
} catch (PDOException $e) {
    error_log("Erro ao verificar nome do produto: " . $e->getMessage());
    echo 'Erro ao verificar nome do produto: ' . $e->getMessage();
    exit();
}

// TRATAMENTO DA FOTO
$foto = 'sem-foto.jpg';

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
        // Continua com foto padrão em caso de erro
    }
}

// Upload de nova foto
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
    $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $extensao = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
    
    if (in_array($extensao, $extensoes_permitidas)) {
        $nome_imagem = 'produto_' . date('YmdHis') . '_' . uniqid() . '.' . $extensao;
        $caminho_destino = '../../images/produtos/' . $nome_imagem;
        
        if (!is_dir('../../images/produtos/')) {
            mkdir('../../images/produtos/', 0755, true);
        }
        
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $caminho_destino)) {
            if ($foto !== 'sem-foto.jpg' && file_exists('../../images/produtos/' . $foto)) {
                unlink('../../images/produtos/' . $foto);
            }
            $foto = $nome_imagem;
        }
    }
}

// SALVAR NO BANCO DE DADOS
try {
    if (empty($id)) {
        // INSERT
        error_log("Executando INSERT para novo produto: " . $nome);
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
        error_log("Executando UPDATE para produto ID: " . $id . " - Nome: " . $nome);
        $sql = "UPDATE $tabela SET
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
            valor_promocional = :valor_promocional
            WHERE id = :id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id);
        error_log("Bind ID para UPDATE: " . $id);
    }
    
    // Bind dos valores - usar nomes corretos da tabela
    $stmt->bindValue(':nome', $nome);
    $stmt->bindValue(':descricao', $descricao);
    $stmt->bindValue(':categoria', $categoria);
    $stmt->bindValue(':valor_compra', $valor_compra);
    $stmt->bindValue(':valor_venda', $valor_venda_str);
    $stmt->bindValue(':foto', $foto);
    $stmt->bindValue(':ativo', $ativo);
    $stmt->bindValue(':url', $url);
    $stmt->bindValue(':ipi_percentual', $ipi_percentual);
    $stmt->bindValue(':custo_nfe_percentual', $custo_nfe_percentual);
    $stmt->bindValue(':margem_lucro_percentual', $margem_lucro_percentual);
    $stmt->bindValue(':codigo_referencia', $codigo_referencia);
    $stmt->bindValue(':fornecedor', $fornecedor);
    $stmt->bindValue(':estoque', $estoque);
    $stmt->bindValue(':nivel_estoque', $nivel_estoque);
    $stmt->bindValue(':tem_estoque', $tem_estoque);
    $stmt->bindValue(':promocao', $promocao);
    $stmt->bindValue(':combo', $combo);
    $stmt->bindValue(':delivery', $delivery);
    $stmt->bindValue(':preparado', $preparado);
    $stmt->bindValue(':valor_promocional', $valor_promocional);
    
    $result = $stmt->execute();
    
    if ($result) {
        error_log("Operação bem sucedida!");
        echo 'Salvo com Sucesso';
    } else {
        error_log("Erro na execução da query");
        echo 'Erro ao salvar produto';
    }
    
} catch (PDOException $e) {
    error_log("Erro PDOException: " . $e->getMessage());
    echo 'Erro ao salvar produto: ' . $e->getMessage();
    echo '<br>SQL: ' . $sql;
}
?>