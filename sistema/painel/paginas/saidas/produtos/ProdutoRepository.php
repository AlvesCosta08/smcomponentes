<?php
require_once '../includes/Database.php';

class ProdutoRepository {
    private $pdo;
    
    public function __construct() {
        $this->pdo = Database::getInstance();
    }
    
    public function listar($pagina = 1, $itensPorPagina = 15, $filtros = []) {
        $offset = ($pagina - 1) * $itensPorPagina;
        
        $where = ['1=1'];
        $params = [];
        
        if (!empty($filtros['busca'])) {
            $where[] = '(p.nome LIKE :busca OR p.codigo_referencia LIKE :busca)';
            $params[':busca'] = "%{$filtros['busca']}%";
        }
        
        if (!empty($filtros['categoria'])) {
            $where[] = 'p.categoria = :categoria';
            $params[':categoria'] = $filtros['categoria'];
        }
        
        if (isset($filtros['ativo'])) {
            $where[] = 'p.ativo = :ativo';
            $params[':ativo'] = $filtros['ativo'];
        }
        
        $whereClause = implode(' AND ', $where);
        
        // Query para os produtos
        $sql = "SELECT p.*, 
                       c.nome as categoria_nome,
                       f.nome as fornecedor_nome
                FROM produtos p
                LEFT JOIN categorias c ON p.categoria = c.id
                LEFT JOIN fornecedores f ON p.fornecedor = f.id
                WHERE $whereClause
                ORDER BY p.nome ASC
                LIMIT :offset, :limit";
        
        $stmt = $this->pdo->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $itensPorPagina, PDO::PARAM_INT);
        $stmt->execute();
        $produtos = $stmt->fetchAll();
        
        // Query para total
        $sqlCount = "SELECT COUNT(*) as total FROM produtos p WHERE $whereClause";
        $stmtCount = $this->pdo->prepare($sqlCount);
        
        foreach ($params as $key => $value) {
            $stmtCount->bindValue($key, $value);
        }
        
        $stmtCount->execute();
        $total = $stmtCount->fetch()['total'];
        $totalPaginas = ceil($total / $itensPorPagina);
        
        return [
            'produtos' => $produtos,
            'total' => $total,
            'totalPaginas' => $totalPaginas,
            'paginaAtual' => $pagina
        ];
    }
    
    public function buscarPorId($id) {
        $sql = "SELECT p.*, 
                       c.nome as categoria_nome,
                       f.nome as fornecedor_nome
                FROM produtos p
                LEFT JOIN categorias c ON p.categoria = c.id
                LEFT JOIN fornecedores f ON p.fornecedor = f.id
                WHERE p.id = :id";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    public function salvar($dados) {
        if (empty($dados['id'])) {
            return $this->inserir($dados);
        } else {
            return $this->atualizar($dados);
        }
    }
    
    private function inserir($dados) {
        $campos = [];
        $placeholders = [];
        $valores = [];
        
        foreach ($dados as $campo => $valor) {
            $campos[] = $campo;
            $placeholders[] = ":$campo";
            $valores[":$campo"] = $valor;
        }
        
        $sql = "INSERT INTO produtos (" . implode(', ', $campos) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($valores);
        
        return $this->pdo->lastInsertId();
    }
    
    private function atualizar($dados) {
        $id = $dados['id'];
        unset($dados['id']);
        
        $sets = [];
        $valores = [':id' => $id];
        
        foreach ($dados as $campo => $valor) {
            $sets[] = "$campo = :$campo";
            $valores[":$campo"] = $valor;
        }
        
        $sql = "UPDATE produtos SET " . implode(', ', $sets) . " WHERE id = :id";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($valores);
        
        return $stmt->rowCount() > 0;
    }
    
    public function excluir($id) {
        $sql = "DELETE FROM produtos WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
    
    public function excluirMultiplos($ids) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "DELETE FROM produtos WHERE id IN ($placeholders)";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($ids);
    }
    
    public function mudarStatus($id, $ativo) {
        $sql = "UPDATE produtos SET ativo = :ativo WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':ativo', $ativo, PDO::PARAM_INT);
        return $stmt->execute();
    }
    
    public function registrarMovimentoEstoque($produtoId, $quantidade, $tipo, $motivo = '') {
        $sql = "INSERT INTO movimentacoes_estoque 
                (produto_id, quantidade, tipo, motivo, data) 
                VALUES (:produto_id, :quantidade, :tipo, :motivo, NOW())";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':produto_id' => $produtoId,
            ':quantidade' => $quantidade,
            ':tipo' => $tipo,
            ':motivo' => $motivo
        ]);
        
        // Atualizar estoque do produto
        $operador = $tipo === 'entrada' ? '+' : '-';
        $sqlEstoque = "UPDATE produtos 
                      SET estoque = estoque $operador :quantidade 
                      WHERE id = :id";
        
        $stmtEstoque = $this->pdo->prepare($sqlEstoque);
        $stmtEstoque->execute([
            ':quantidade' => $quantidade,
            ':id' => $produtoId
        ]);
        
        return true;
    }
    
    public function listarCategorias() {
        $sql = "SELECT id, nome FROM categorias ORDER BY nome ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }
    
    public function listarFornecedores() {
        $sql = "SELECT id, nome FROM fornecedores ORDER BY nome ASC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }
}
?>