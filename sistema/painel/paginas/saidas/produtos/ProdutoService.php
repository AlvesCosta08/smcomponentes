<?php
require_once 'ProdutoRepository.php';

class ProdutoService {
    private $repository;
    
    public function __construct() {
        $this->repository = new ProdutoRepository();
    }
    
    public function validarDados($dados) {
        $erros = [];
        
        // Validar nome
        if (empty($dados['nome']) || strlen($dados['nome']) < 2) {
            $erros[] = 'Nome do produto deve ter pelo menos 2 caracteres';
        }
        
        // Validar categoria
        if (empty($dados['categoria'])) {
            $erros[] = 'Selecione uma categoria';
        }
        
        // Validar valores numéricos
        $camposNumericos = ['valor_compra', 'estoque', 'nivel_estoque'];
        foreach ($camposNumericos as $campo) {
            if (isset($dados[$campo]) && !is_numeric(str_replace(',', '.', $dados[$campo]))) {
                $erros[] = "Campo $campo deve ser numérico";
            }
        }
        
        // Validar percentuais
        $percentuais = ['ipi_percentual', 'custo_nfe_percentual', 'margem_lucro_percentual'];
        foreach ($percentuais as $campo) {
            if (isset($dados[$campo]) && ($dados[$campo] < 0 || $dados[$campo] > 1000)) {
                $erros[] = "$campo deve estar entre 0 e 1000";
            }
        }
        
        return $erros;
    }
    
    public function formatarDados($dados) {
        // Converter valores monetários
        if (isset($dados['valor_compra'])) {
            $dados['valor_compra'] = str_replace(['.', ','], ['', '.'], $dados['valor_compra']);
        }
        
        if (isset($dados['valor_promocional'])) {
            $dados['valor_promocional'] = str_replace(['.', ','], ['', '.'], $dados['valor_promocional']);
        }
        
        // Calcular valor de venda se necessário
        if (isset($dados['valor_compra']) && isset($dados['ipi_percentual']) && 
            isset($dados['custo_nfe_percentual']) && isset($dados['margem_lucro_percentual'])) {
            
            $valorCompra = floatval($dados['valor_compra']);
            $ipi = floatval($dados['ipi_percentual'] ?? 0);
            $custoNFe = floatval($dados['custo_nfe_percentual'] ?? 0);
            $margem = floatval($dados['margem_lucro_percentual'] ?? 30);
            
            $custoUnitario = $valorCompra + ($valorCompra * ($ipi / 100)) + ($valorCompra * ($custoNFe / 100));
            $valorVenda = $custoUnitario * (1 + ($margem / 100));
            
            $dados['valor_venda'] = $valorVenda;
        }
        
        // Garantir valores booleanos
        $booleanos = ['ativo', 'tem_estoque', 'promocao', 'delivery', 'preparado'];
        foreach ($booleanos as $campo) {
            if (isset($dados[$campo])) {
                $dados[$campo] = in_array(strtolower($dados[$campo]), ['sim', '1', 'true', 'yes']) ? 1 : 0;
            }
        }
        
        // Gerar URL amigável se não existir
        if (empty($dados['url']) && !empty($dados['nome'])) {
            $dados['url'] = $this->gerarUrlAmigavel($dados['nome']);
        }
        
        return $dados;
    }
    
    private function gerarUrlAmigavel($texto) {
        $texto = preg_replace('/[^a-zA-Z0-9\s]/', '', $texto);
        $texto = strtolower(trim($texto));
        $texto = preg_replace('/\s+/', '-', $texto);
        $texto = substr($texto, 0, 50); // Limitar tamanho
        return $texto . '-' . time();
    }
    
    public function processarFoto($arquivo) {
        if (!$arquivo || $arquivo['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        
        // Validar tipo
        $tiposPermitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($arquivo['type'], $tiposPermitidos)) {
            throw new Exception('Tipo de arquivo não permitido');
        }
        
        // Validar tamanho (max 2MB)
        if ($arquivo['size'] > 2 * 1024 * 1024) {
            throw new Exception('Arquivo muito grande (máx 2MB)');
        }
        
        // Gerar nome único
        $extensao = pathinfo($arquivo['name'], PATHINFO_EXTENSION);
        $nomeArquivo = 'produto_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $extensao;
        $caminhoDestino = '../images/produtos/' . $nomeArquivo;
        
        // Mover arquivo
        if (!move_uploaded_file($arquivo['tmp_name'], $caminhoDestino)) {
            throw new Exception('Erro ao salvar arquivo');
        }
        
        // Otimizar imagem se necessário
        $this->otimizarImagem($caminhoDestino);
        
        return $nomeArquivo;
    }
    
    private function otimizarImagem($caminho) {
        // Implementar otimização de imagem se necessário
        // Pode usar bibliotecas como GD ou Imagick
        return true;
    }
    
    public function listar($pagina = 1, $filtros = []) {
        return $this->repository->listar($pagina, 15, $filtros);
    }
    
    public function salvar($dados, $arquivoFoto = null) {
        $erros = $this->validarDados($dados);
        if (!empty($erros)) {
            throw new Exception(implode('; ', $erros));
        }
        
        $dadosFormatados = $this->formatarDados($dados);
        
        // Processar foto
        if ($arquivoFoto) {
            $fotoNome = $this->processarFoto($arquivoFoto);
            if ($fotoNome) {
                $dadosFormatados['foto'] = $fotoNome;
            }
        }
        
        return $this->repository->salvar($dadosFormatados);
    }
    
    public function excluir($id) {
        // Verificar se o produto tem movimentações
        // Implementar lógica adicional se necessário
        
        return $this->repository->excluir($id);
    }
    
    public function registrarMovimento($produtoId, $quantidade, $tipo, $motivo) {
        if ($quantidade <= 0) {
            throw new Exception('Quantidade deve ser maior que zero');
        }
        
        if ($tipo === 'saida') {
            // Verificar estoque disponível
            $produto = $this->repository->buscarPorId($produtoId);
            if (!$produto || $produto['estoque'] < $quantidade) {
                throw new Exception('Estoque insuficiente');
            }
        }
        
        return $this->repository->registrarMovimentoEstoque($produtoId, $quantidade, $tipo, $motivo);
    }
}
?>