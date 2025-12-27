<?php
require_once 'ProdutoService.php';

class ProdutoController {
    private $service;
    
    public function __construct() {
        $this->service = new ProdutoService();
    }
    
    public function listarCategoriasOptions() {
        $categorias = $this->service->listarCategorias();
        $options = '';
        
        foreach ($categorias as $categoria) {
            $options .= sprintf(
                '<option value="%s">%s</option>',
                htmlspecialchars($categoria['id']),
                htmlspecialchars($categoria['nome'])
            );
        }
        
        return $options;
    }
    
    public function listarFornecedoresOptions() {
        $fornecedores = $this->service->listarFornecedores();
        $options = '<option value="">Selecione um Fornecedor</option>';
        
        foreach ($fornecedores as $fornecedor) {
            $options .= sprintf(
                '<option value="%s">%s</option>',
                htmlspecialchars($fornecedor['id']),
                htmlspecialchars($fornecedor['nome'])
            );
        }
        
        return $options;
    }
    
    public function handleRequest() {
        $action = $_GET['action'] ?? 'listar';
        
        switch ($action) {
            case 'listar':
                $this->listar();
                break;
            case 'salvar':
                $this->salvar();
                break;
            case 'excluir':
                $this->excluir();
                break;
            case 'status':
                $this->mudarStatus();
                break;
            case 'entrada':
                $this->registrarEntrada();
                break;
            case 'saida':
                $this->registrarSaida();
                break;
            default:
                http_response_code(404);
                echo json_encode(['error' => 'Ação não encontrada']);
        }
    }
    
    private function listar() {
        $pagina = $_POST['pagina'] ?? 1;
        $filtros = [
            'busca' => $_POST['busca'] ?? '',
            'categoria' => $_POST['categoria'] ?? '',
            'ativo' => 1 // Por padrão mostra apenas ativos
        ];
        
        $resultado = $this->service->listar($pagina, $filtros);
        echo json_encode($resultado);
    }
    
    private function salvar() {
        try {
            $dados = $_POST;
            $arquivoFoto = $_FILES['foto'] ?? null;
            
            $resultado = $this->service->salvar($dados, $arquivoFoto);
            
            if ($resultado) {
                echo 'Salvo com Sucesso';
            } else {
                throw new Exception('Erro ao salvar produto');
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo $e->getMessage();
        }
    }
    
    private function excluir() {
        try {
            $ids = $_POST['ids'] ?? $_POST['id'] ?? '';
            
            if (strpos($ids, ',') !== false) {
                $idsArray = explode(',', $ids);
                $idsArray = array_filter($idsArray, 'is_numeric');
                
                if (empty($idsArray)) {
                    throw new Exception('Nenhum ID válido fornecido');
                }
                
                foreach ($idsArray as $id) {
                    $this->service->excluir($id);
                }
            } else {
                $this->service->excluir($ids);
            }
            
            echo 'Excluído com Sucesso';
        } catch (Exception $e) {
            http_response_code(400);
            echo $e->getMessage();
        }
    }
    
    private function mudarStatus() {
        try {
            $id = $_POST['id'];
            $ativo = $_POST['ativo'];
            
            // Implementar mudança de status
            echo 'Alterado com Sucesso';
        } catch (Exception $e) {
            http_response_code(400);
            echo $e->getMessage();
        }
    }
    
    private function registrarEntrada() {
        try {
            $produtoId = $_POST['id'];
            $quantidade = $_POST['quantidade_entrada'];
            $motivo = $_POST['motivo_entrada'] ?? 'Entrada de estoque';
            
            $this->service->registrarMovimento($produtoId, $quantidade, 'entrada', $motivo);
            
            echo 'Salvo com Sucesso';
        } catch (Exception $e) {
            http_response_code(400);
            echo $e->getMessage();
        }
    }
    
    private function registrarSaida() {
        try {
            $produtoId = $_POST['id'];
            $quantidade = $_POST['quantidade_saida'];
            $motivo = $_POST['motivo_saida'] ?? 'Saída de estoque';
            
            $this->service->registrarMovimento($produtoId, $quantidade, 'saida', $motivo);
            
            echo 'Salvo com Sucesso';
        } catch (Exception $e) {
            http_response_code(400);
            echo $e->getMessage();
        }
    }
}
?>