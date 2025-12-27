<?php
// Ativar debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Iniciar sessão para logs
session_start();

// Log para debug
error_log("=== EXCLUIR-ITEM.PHP INICIADO ===");

// Verificar se é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "Erro: Método inválido. Use POST.";
    error_log("Erro: Método inválido. Não é POST.");
    exit();
}

// Verificar se ID foi enviado
if (!isset($_POST['id']) || empty($_POST['id'])) {
    echo "Erro: ID não recebido";
    error_log("Erro: ID não recebido no POST");
    exit();
}

// Conexão
$caminho_conexao = realpath(__DIR__ . '/../../../conexao.php');
error_log("Caminho conexão: " . $caminho_conexao);

if (!file_exists($caminho_conexao)) {
    echo "Erro: Arquivo de conexão não encontrado";
    error_log("Erro: Arquivo conexao.php não existe em: " . $caminho_conexao);
    exit();
}

require_once($caminho_conexao);

// Verificar conexão
if (!isset($pdo) || !($pdo instanceof PDO)) {
    echo "Erro: Conexão com banco de dados não estabelecida";
    error_log("Erro: Variável \$pdo não está definida ou não é PDO");
    exit();
}

// Testar conexão
try {
    $pdo->query("SELECT 1");
    error_log("✅ Conexão com banco OK");
} catch (Exception $e) {
    echo "Erro na conexão com o banco: " . $e->getMessage();
    error_log("❌ Erro na conexão: " . $e->getMessage());
    exit();
}

// Obter ID
$id = intval($_POST['id']);
$nome = isset($_POST['nome']) ? trim($_POST['nome']) : 'Desconhecido';

error_log("ID recebido: " . $id . " (tipo: " . gettype($id) . ")");
error_log("Nome recebido: " . $nome);

try {
    // 1. Primeiro, verificar se a tabela produtos existe
    error_log("Verificando se tabela produtos existe...");
    $stmt = $pdo->query("SHOW TABLES LIKE 'produtos'");
    if ($stmt->rowCount() == 0) {
        error_log("❌ Tabela 'produtos' não existe!");
        echo "Erro: Tabela de produtos não encontrada";
        exit();
    }
    error_log("✅ Tabela 'produtos' existe");

    // 2. Verificar estrutura da tabela
    error_log("Verificando estrutura da tabela produtos...");
    $stmt = $pdo->query("DESCRIBE produtos");
    $colunas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    error_log("Colunas da tabela produtos: " . implode(', ', $colunas));

    // 3. Verificar se produto existe (COM MAIS DETALHES)
    error_log("Buscando produto com ID: " . $id);
    $stmt = $pdo->prepare("SELECT * FROM produtos WHERE id = ?");
    $stmt->execute([$id]);
    $produto = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($produto) {
        error_log("✅ Produto encontrado:");
        error_log("ID: " . $produto['id']);
        error_log("Nome: " . $produto['nome']);
        error_log("Foto: " . ($produto['foto'] ?? 'Não informada'));
        error_log("Total de produtos encontrados: " . $stmt->rowCount());
        
        // 4. Excluir foto se não for a padrão
        if (isset($produto['foto']) && $produto['foto'] && $produto['foto'] !== 'sem-foto.jpg') {
            $diretorio_fotos = realpath(__DIR__ . '/../../images/produtos/');
            if ($diretorio_fotos) {
                $caminho_foto = $diretorio_fotos . '/' . $produto['foto'];
                error_log("Caminho da foto: " . $caminho_foto);
                
                if (file_exists($caminho_foto)) {
                    if (unlink($caminho_foto)) {
                        error_log("✅ Foto excluída com sucesso: " . $produto['foto']);
                    } else {
                        error_log("⚠️ Não foi possível excluir a foto, mas continuando...");
                    }
                } else {
                    error_log("⚠️ Foto não encontrada no caminho: " . $caminho_foto);
                }
            } else {
                error_log("⚠️ Diretório de fotos não encontrado");
            }
        } else {
            error_log("ℹ️ Foto padrão ou não informada, não precisa excluir");
        }
        
        // 5. Excluir produto do banco
        error_log("Excluindo produto do banco de dados...");
        $stmt = $pdo->prepare("DELETE FROM produtos WHERE id = ?");
        
        if ($stmt->execute([$id])) {
            $linhas_afetadas = $stmt->rowCount();
            error_log("✅ Produto excluído do banco. Linhas afetadas: " . $linhas_afetadas);
            
            if ($linhas_afetadas > 0) {
                echo "Excluído com Sucesso";
            } else {
                echo "Erro: Nenhuma linha foi afetada na exclusão";
                error_log("❌ Nenhuma linha foi afetada na exclusão");
            }
        } else {
            $errorInfo = $stmt->errorInfo();
            echo "Erro ao excluir do banco: " . $errorInfo[2];
            error_log("❌ Erro PDO: " . print_r($errorInfo, true));
        }
        
    } else {
        error_log("❌ Produto não encontrado com ID: " . $id);
        
        // Verificar quantos produtos existem no banco
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM produtos");
        $total = $stmt->fetch(PDO::FETCH_ASSOC);
        error_log("Total de produtos no banco: " . $total['total']);
        
        // Listar alguns produtos para debug
        $stmt = $pdo->query("SELECT id, nome FROM produtos LIMIT 5");
        $alguns_produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("Alguns produtos no banco: " . print_r($alguns_produtos, true));
        
        echo "Erro: Produto não encontrado no banco de dados";
    }
    
} catch (PDOException $e) {
    error_log("❌ Erro PDOException: " . $e->getMessage());
    error_log("Código do erro: " . $e->getCode());
    error_log("Arquivo: " . $e->getFile() . ":" . $e->getLine());
    echo "Erro no banco de dados: " . $e->getMessage();
} catch (Exception $e) {
    error_log("❌ Erro Exception: " . $e->getMessage());
    echo "Erro: " . $e->getMessage();
}

error_log("=== EXCLUIR-ITEM.PHP FINALIZADO ===");
?>