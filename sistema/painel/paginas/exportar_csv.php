<?php
session_start();
if (isset($_SESSION['nome_usuario'])) {
    $pdo = $pdo; // Assume que $pdo está disponível no escopo
    $pag = 'produtos';

    // Consulta para buscar os dados
    // Se houver filtro por categoria (via $_POST ou $_GET), você pode adaptar aqui
    $query = $pdo->query("SELECT p.*, c.nome as nome_categoria, f.nome as nome_fornecedor FROM produtos p LEFT JOIN categorias c ON p.categoria = c.id LEFT JOIN fornecedores f ON p.fornecedor_id = f.id ORDER BY p.nome ASC");
    $produtos = $query->fetchAll(PDO::FETCH_ASSOC);

    if (count($produtos) === 0) {
        echo "Nenhum produto encontrado para exportar.";
        exit();
    }

    // Define os nomes dos campos que serão exportados
    $campos = [
        'ID',
        'Nome',
        'Categoria',
        'Descrição',
        'Código de Referência',
        'Fornecedor',
        'Valor de Compra (R$)',
        'IPI (%)',
        'Custos NFe (%)',
        'Margem (%)',
        'Custo Unitário (R$)',
        'Valor de Venda (R$)',
        'Valor Promocional (R$)',
        'Estoque',
        'Nível de Alerta',
        'Tem Estoque',
        'Preparado',
        'Ativo'
    ];

    $nome_arquivo = 'produtos_exportados_' . date('Y-m-d_H-i-s') . '.csv';

    // Configura headers para download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $nome_arquivo);
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');

    // Adiciona BOM (Byte Order Mark) para UTF-8 para garantir leitura correta em Excel
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    // Escreve os cabeçalhos
    fputcsv($output, $campos, ';'); // Usando ';' como delimitador

    // Escreve os dados
    foreach ($produtos as $produto) {
        $linha = [
            $produto['id'],
            $produto['nome'],
            $produto['nome_categoria'], // Nome da categoria
            $produto['descricao'],
            $produto['codigo_referencia'],
            $produto['nome_fornecedor'], // Nome do fornecedor
            number_format($produto['valor_compra'], 2, ',', ''), // Formato brasileiro
            number_format($produto['percentual_ipi'], 2, ',', ''), // Formato brasileiro
            number_format($produto['percentual_custo_nfe'], 2, ',', ''), // Formato brasileiro
            number_format($produto['percentual_margem_lucro'], 2, ',', ''), // Formato brasileiro
            number_format($produto['custo_unitario'], 2, ',', ''), // Formato brasileiro
            number_format($produto['valor_venda'], 2, ',', ''), // Formato brasileiro
            number_format($produto['val_promocional'], 2, ',', ''), // Formato brasileiro
            $produto['estoque'],
            $produto['nivel_estoque'],
            $produto['tem_estoque'],
            $produto['preparado'],
            $produto['ativo']
        ];
        fputcsv($output, $linha, ';'); // Usando ';' como delimitador
    }

    fclose($output);
    exit();
} else {
    echo "Acesso negado.";
}
?>