<?php
require_once("../../conexao.php");
include('data_formatada.php');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Catálogo de Produtos</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        @page {
            margin: 130px 20px 50px 20px;
        }

        body {
            font-family: 'Poppins', sans-serif;
            font-size: 10px;
            color: #333;
        }

        #header {
            position: fixed;
            top: -110px;
            left: 0;
            right: 0;
            text-align: center;
            border-bottom: 1px solid #ccc;
            padding-bottom: 10px;
        }

        #footer {
            position: fixed;
            bottom: -40px;
            left: 0;
            right: 0;
            font-size: 10px;
            border-top: 1px solid #ccc;
        }

        #footer .page::after {
            content: counter(page);
        }

        .titulo-relatorio {
            font-size: 12px;
            font-weight: bold;
        }

        .tabela-produtos {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .tabela-produtos th,
        .tabela-produtos td {
            border: 1px solid #ccc;
            padding: 4px 6px;
            text-align: left;
            font-size: 9px;
        }

        .tabela-produtos th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }

        .estoque-baixo {
            color: red;
            font-weight: bold;
        }

        .inativo {
            color: #888;
        }

        .categoria-titulo {
            background-color: #e9ecef;
            font-weight: bold;
            text-transform: uppercase;
        }

        .resumo {
            margin-top: 10px;
            font-size: 9px;
        }
    </style>
</head>

<body>
    <div id="header">
        <table style="width:100%;">
            <tr>
                <td style="text-align: center;">
                    <div>
                        <span style="font-size: 13px; font-weight:bold;"><?php echo mb_strtoupper($nome_sistema) ?></span><br>
                        CNPJ: <?php echo $cnpj_sistema ?><br>
                        INSTAGRAM: <?php echo $instagram_sistema ?><br>
                        <?php echo mb_strtoupper($endereco_sistema) ?>
                    </div>
                </td>
                <td style="text-align: right;">
                    <div class="titulo-relatorio">CATÁLOGO DE PRODUTOS</div>
                    <div><?php echo mb_strtoupper($data_hoje) ?></div>
                </td>
            </tr>
        </table>
    </div>

    <div id="footer">
        <table style="width:100%;">
            <tr>
                <td><?php echo $nome_sistema ?> - Telefone: <?php echo $telefone_sistema ?></td>
                <td style="text-align:right;">Página <span class="page"></span></td>
            </tr>
        </table>
    </div>

    <table class="tabela-produtos">
        <thead>
            <tr>
                <th style="width: 10%;">CÓDIGO DE REFERÊNCIA</th>
                <th style="width: 35%;">Produto</th>
                <th style="width: 20%;">Categoria</th>
                <th style="width: 10%;">Preço(R$)</th>
                <th style="width: 10%;">Status</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $total_reg = 0;
        $produtos_ativos = 0;
        $produtos_inativos = 0;
        $estoque_baixo = 0;

        $queryCategorias = $pdo->query("SELECT * FROM categorias ORDER BY nome ASC");
        $resCategorias = $queryCategorias->fetchAll(PDO::FETCH_ASSOC);

        foreach ($resCategorias as $categoria) {
            $id_categoria = $categoria['id'];
            $nome_categoria = $categoria['nome'];

            echo "<tr><td colspan='5' class='categoria-titulo'>CATEGORIA: $nome_categoria</td></tr>";

            $queryProdutos = $pdo->query("SELECT * FROM produtos WHERE categoria = '$id_categoria' ORDER BY nome ASC");
            $resProdutos = $queryProdutos->fetchAll(PDO::FETCH_ASSOC);

            foreach ($resProdutos as $produto) {
                $total_reg++;
                $codigo_referencia = $produto['codigo_referencia'];
                $descricao = $produto['descricao'];
                $valor_venda = $produto['valor_venda'];
                $nivel_estoque = $produto['nivel_estoque'];
                $ativo = $produto['ativo'];

                $classe_estoque = ($estoque < $nivel_estoque && $ativo == 'Sim') ? 'estoque-baixo' : '';
                $classe_ativo = ($ativo != 'Sim') ? 'inativo' : '';

                if ($ativo == 'Sim') {
                    $produtos_ativos++;
                } else {
                    $produtos_inativos++;
                }

                if ($estoque < $nivel_estoque && $ativo == 'Sim') {
                    $estoque_baixo++;
                }

                echo "
                <tr class='{$classe_ativo}'>
                    <td>{$codigo_referencia}</td>
                    <td><strong>{$descricao}</strong></td>                    
                    <td class='categoria'>{$nome_categoria}</td>
                    <td>{$valor_venda}</td>
                    <td>" . ($ativo == 'Sim' ? 'Ativo' : 'Inativo') . "</td>
                </tr>";
            }
        }
        ?>
        </tbody>
    </table>
</body>
</html>




