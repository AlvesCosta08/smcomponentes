CREATE TABLE `abertura_mesa` (
  `id` int(11) NOT NULL,
  `mesa` int(11) NOT NULL,
  `total` decimal(8,2) DEFAULT NULL,
  `cliente` varchar(50) DEFAULT NULL,
  `data` date NOT NULL,
  `horario_abertura` time NOT NULL,
  `horario_fechamento` time DEFAULT NULL,
  `garcon` int(11) NOT NULL,
  `status` varchar(30) DEFAULT NULL,
  `obs` varchar(255) DEFAULT NULL,
  `pessoas` int(11) NOT NULL,
  `comissao_garcon` decimal(8,2) DEFAULT NULL,
  `couvert` decimal(8,2) DEFAULT NULL,
  `subtotal` decimal(8,2) DEFAULT NULL,
  `forma_pgto` varchar(35) DEFAULT NULL,
  `valor_adiantado` decimal(8,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `acessos` (
  `id` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `chave` varchar(50) NOT NULL,
  `grupo` int(11) NOT NULL,
  `pagina` varchar(5) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



CREATE TABLE `adicionais` (
  `id` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `valor` decimal(8,2) DEFAULT NULL,
  `ativo` varchar(5) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `adicionais_cat` (
  `id` int(11) NOT NULL,
  `categoria` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `ativo` varchar(5) NOT NULL,
  `valor` decimal(8,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `anotacoes` (
  `id` int(11) NOT NULL,
  `titulo` varchar(250) NOT NULL,
  `msg` text NOT NULL,
  `usuario` int(11) NOT NULL,
  `data` date NOT NULL,
  `mostrar_home` varchar(5) NOT NULL,
  `privado` varchar(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `arquivos` (
  `id` int(11) NOT NULL,
  `nome` varchar(50) DEFAULT NULL,
  `descricao` varchar(100) DEFAULT NULL,
  `arquivo` varchar(100) DEFAULT NULL,
  `data_cad` date NOT NULL,
  `registro` varchar(50) DEFAULT NULL,
  `id_reg` int(11) NOT NULL,
  `usuario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `bairros` (
  `id` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `valor` decimal(8,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `banner_rotativo` (
  `id` int(11) NOT NULL,
  `foto` varchar(100) NOT NULL,
  `categoria` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `bordas` (
  `id` int(11) NOT NULL,
  `categoria` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `ativo` varchar(5) NOT NULL,
  `valor` decimal(8,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `caixas` (
  `id` int(11) NOT NULL,
  `operador` int(11) NOT NULL,
  `data_abertura` date NOT NULL,
  `data_fechamento` date DEFAULT NULL,
  `valor_abertura` decimal(8,2) NOT NULL,
  `valor_fechamento` decimal(8,2) DEFAULT NULL,
  `quebra` decimal(8,2) DEFAULT NULL,
  `usuario_abertura` int(11) NOT NULL,
  `usuario_fechamento` int(11) DEFAULT NULL,
  `obs` varchar(255) DEFAULT NULL,
  `sangrias` decimal(8,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `cargos` (
  `id` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `carrinho` (
  `id` int(11) NOT NULL,
  `sessao` varchar(35) NOT NULL,
  `cliente` int(11) NOT NULL,
  `produto` int(11) NOT NULL,
  `quantidade` int(11) NOT NULL,
  `total_item` decimal(8,2) NOT NULL,
  `obs` varchar(255) DEFAULT NULL,
  `pedido` int(11) DEFAULT NULL,
  `data` date DEFAULT NULL,
  `id_sabor` int(11) DEFAULT NULL,
  `categoria` int(11) DEFAULT NULL,
  `item` int(11) DEFAULT NULL,
  `variacao` int(11) DEFAULT NULL,
  `mesa` varchar(25) DEFAULT NULL,
  `nome_cliente` varchar(50) DEFAULT NULL,
  `nome_produto` varchar(100) DEFAULT NULL,
  `sabores` int(11) DEFAULT NULL,
  `borda` int(11) DEFAULT NULL,
  `valor_unitario` decimal(8,2) DEFAULT NULL,
  `status` varchar(25) DEFAULT NULL,
  `hora` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `foto` varchar(100) DEFAULT NULL,
  `cor` varchar(30) NOT NULL,
  `ativo` varchar(5) NOT NULL,
  `url` varchar(100) NOT NULL,
  `mais_sabores` varchar(5) NOT NULL,
  `delivery` varchar(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `cpf` varchar(25) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `endereco` varchar(100) DEFAULT NULL,
  `numero` varchar(10) DEFAULT NULL,
  `complemento` varchar(100) DEFAULT NULL,
  `bairro` varchar(50) DEFAULT NULL,
  `cidade` varchar(50) DEFAULT NULL,
  `estado` varchar(50) DEFAULT NULL,
  `cep` varchar(20) DEFAULT NULL,
  `data_cad` date NOT NULL,
  `data_nasc` date DEFAULT NULL,
  `usuario` int(11) DEFAULT NULL,
  `data_mensagem` date DEFAULT NULL,
  `retorno_enviado` varchar(5) DEFAULT NULL,
  `cartoes` int(11) DEFAULT NULL,
  `marketing` varchar(5) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `config` (
  `nome` varchar(50) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `endereco` varchar(100) DEFAULT NULL,
  `instagram` varchar(100) DEFAULT NULL,
  `logo` varchar(100) DEFAULT NULL,
  `icone` varchar(100) DEFAULT NULL,
  `logo_rel` varchar(100) DEFAULT NULL,
  `id` int(11) NOT NULL,
  `ativo` varchar(5) DEFAULT NULL,
  `multa_atraso` decimal(8,2) DEFAULT NULL,
  `juros_atraso` decimal(8,2) DEFAULT NULL,
  `marca_dagua` varchar(5) DEFAULT NULL,
  `assinatura_recibo` varchar(5) DEFAULT NULL,
  `impressao_automatica` varchar(5) DEFAULT NULL,
  `cnpj` varchar(25) DEFAULT NULL,
  `entrar_automatico` varchar(5) DEFAULT NULL,
  `mostrar_preloader` varchar(5) DEFAULT NULL,
  `ocultar_mobile` varchar(5) DEFAULT NULL,
  `api_whatsapp` varchar(25) DEFAULT NULL,
  `token_whatsapp` varchar(70) DEFAULT NULL,
  `instancia_whatsapp` varchar(70) DEFAULT NULL,
  `dados_pagamento` varchar(100) DEFAULT NULL,
  `telefone_fixo` varchar(20) DEFAULT NULL,
  `tipo_rel` varchar(10) DEFAULT NULL,
  `tipo_miniatura` varchar(10) DEFAULT NULL,
  `previsao_entrega` int(11) DEFAULT NULL,
  `horario_abertura` time DEFAULT NULL,
  `horario_fechamento` time DEFAULT NULL,
  `texto_fechamento_horario` varchar(255) DEFAULT NULL,
  `status_estabelecimento` varchar(20) DEFAULT NULL,
  `texto_fechamento` varchar(255) DEFAULT NULL,
  `tempo_atualizar` int(11) DEFAULT NULL,
  `tipo_chave` varchar(35) DEFAULT NULL,
  `dias_apagar` int(11) DEFAULT NULL,
  `banner_rotativo` varchar(5) DEFAULT NULL,
  `pedido_minimo` decimal(8,2) DEFAULT NULL,
  `mostrar_aberto` varchar(5) DEFAULT NULL,
  `entrega_distancia` varchar(5) DEFAULT NULL,
  `chave_api_maps` varchar(255) DEFAULT NULL,
  `latitude_rest` varchar(100) DEFAULT NULL,
  `longitude_rest` varchar(100) DEFAULT NULL,
  `distancia_entrega_km` int(11) DEFAULT NULL,
  `valor_km` int(11) DEFAULT NULL,
  `mais_sabores` varchar(30) NOT NULL,
  `abrir_comprovante` varchar(5) NOT NULL,
  `mostrar_acessos` varchar(5) NOT NULL,
  `fonte_comprovante` int(11) DEFAULT NULL,
  `mensagem_auto` varchar(5) DEFAULT NULL,
  `data_cobranca` date DEFAULT NULL,
  `api_merc` varchar(255) DEFAULT NULL,
  `couvert` int(11) DEFAULT NULL,
  `comissao_garcon` decimal(8,2) DEFAULT NULL,
  `abertura_caixa` varchar(5) DEFAULT NULL,
  `dias_retorno` int(11) DEFAULT NULL,
  `mensagem_retorno` varchar(255) DEFAULT NULL,
  `link_retorno` varchar(100) DEFAULT NULL,
  `total_cartoes` int(11) DEFAULT NULL,
  `valor_cupom` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



CREATE TABLE `cupons` (
  `id` int(11) NOT NULL,
  `codigo` varchar(50) NOT NULL,
  `valor` decimal(8,2) NOT NULL,
  `data` date DEFAULT NULL,
  `quantidade` int(11) DEFAULT NULL,
  `valor_minimo` decimal(8,2) DEFAULT NULL,
  `tipo` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `dias` (
  `id` int(11) NOT NULL,
  `dia` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `disparos` (
  `id` int(11) NOT NULL,
  `campanha` int(11) NOT NULL,
  `cliente` int(11) NOT NULL,
  `nome` varchar(50) DEFAULT NULL,
  `telefone` varchar(25) DEFAULT NULL,
  `hora` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `dispositivos` (
  `id` bigint(20) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `appkey` varchar(40) CHARACTER SET utf8mb4 DEFAULT NULL,
  `status` varchar(50) NOT NULL,
  `status_api` varchar(100) DEFAULT NULL,
  `horario` datetime DEFAULT NULL,
  `empresa` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `entradas` (
  `id` int(11) NOT NULL,
  `produto` int(11) NOT NULL,
  `quantidade` int(11) NOT NULL,
  `motivo` varchar(50) NOT NULL,
  `usuario` int(11) NOT NULL,
  `data` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `formas_pgto` (
  `id` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `taxa` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `fornecedores` (
  `id` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `telefone` varchar(50) NOT NULL,
  `email` varchar(50) DEFAULT NULL,
  `endereco` varchar(100) DEFAULT NULL,
  `pix` varchar(50) DEFAULT NULL,
  `tipo_chave` varchar(50) NOT NULL,
  `data` date NOT NULL,
  `numero` varchar(10) DEFAULT NULL,
  `complemento` varchar(100) DEFAULT NULL,
  `bairro` varchar(50) DEFAULT NULL,
  `cidade` varchar(50) DEFAULT NULL,
  `estado` varchar(50) DEFAULT NULL,
  `cep` varchar(20) DEFAULT NULL,
  `usuario` int(11) NOT NULL,
  `cnpj` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `frequencias` (
  `id` int(11) NOT NULL,
  `frequencia` varchar(25) NOT NULL,
  `dias` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `grades` (
  `id` int(11) NOT NULL,
  `produto` int(11) NOT NULL,
  `tipo_item` varchar(30) NOT NULL,
  `valor_item` varchar(30) NOT NULL,
  `texto` varchar(70) NOT NULL,
  `limite` int(11) DEFAULT NULL,
  `ativo` varchar(5) DEFAULT NULL,
  `nome_comprovante` varchar(70) DEFAULT NULL,
  `adicional` varchar(5) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `grupo_acessos` (
  `id` int(11) NOT NULL,
  `nome` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



CREATE TABLE `itens_grade` (
  `id` int(11) NOT NULL,
  `produto` int(11) NOT NULL,
  `texto` varchar(70) NOT NULL,
  `valor` decimal(8,2) NOT NULL,
  `limite` int(11) NOT NULL,
  `ativo` varchar(5) DEFAULT NULL,
  `grade` int(11) NOT NULL,
  `adicional` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `marketing` (
  `id` int(11) NOT NULL,
  `data` date NOT NULL,
  `data_envio` date DEFAULT NULL,
  `envios` int(11) DEFAULT NULL,
  `titulo` varchar(100) DEFAULT NULL,
  `mensagem` varchar(500) DEFAULT NULL,
  `item1` varchar(100) DEFAULT NULL,
  `item2` varchar(100) DEFAULT NULL,
  `item3` varchar(100) DEFAULT NULL,
  `item4` varchar(100) DEFAULT NULL,
  `item5` varchar(100) DEFAULT NULL,
  `item6` varchar(100) DEFAULT NULL,
  `item7` varchar(100) DEFAULT NULL,
  `item8` varchar(100) DEFAULT NULL,
  `item9` varchar(100) DEFAULT NULL,
  `item10` varchar(100) DEFAULT NULL,
  `item11` varchar(100) DEFAULT NULL,
  `item12` varchar(100) DEFAULT NULL,
  `item13` varchar(100) DEFAULT NULL,
  `item14` varchar(100) DEFAULT NULL,
  `item15` varchar(100) DEFAULT NULL,
  `item16` varchar(100) DEFAULT NULL,
  `item17` varchar(100) DEFAULT NULL,
  `item18` varchar(100) DEFAULT NULL,
  `item19` varchar(100) DEFAULT NULL,
  `item20` varchar(100) DEFAULT NULL,
  `conclusao` varchar(500) DEFAULT NULL,
  `arquivo` varchar(100) DEFAULT NULL,
  `audio` varchar(100) DEFAULT NULL,
  `forma_envio` varchar(50) DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `video` varchar(255) DEFAULT NULL,
  `conclusao2` varchar(500) DEFAULT NULL,
  `conclusao3` varchar(500) DEFAULT NULL,
  `conclusao4` varchar(500) DEFAULT NULL,
  `link2` varchar(255) DEFAULT NULL,
  `conclusao5` varchar(500) DEFAULT NULL,
  `documento` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `mesas` (
  `id` int(11) NOT NULL,
  `nome` varchar(25) NOT NULL,
  `ativo` varchar(5) DEFAULT NULL,
  `status` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `pagar` (
  `id` int(11) NOT NULL,
  `descricao` varchar(100) DEFAULT NULL,
  `fornecedor` int(11) DEFAULT NULL,
  `funcionario` int(11) DEFAULT NULL,
  `valor` decimal(8,2) DEFAULT NULL,
  `vencimento` date DEFAULT NULL,
  `data_pgto` date DEFAULT NULL,
  `data_lanc` date DEFAULT NULL,
  `forma_pgto` varchar(50) DEFAULT NULL,
  `frequencia` int(11) DEFAULT NULL,
  `obs` varchar(100) DEFAULT NULL,
  `arquivo` varchar(100) DEFAULT NULL,
  `referencia` varchar(30) DEFAULT NULL,
  `id_ref` int(11) DEFAULT NULL,
  `multa` decimal(8,2) DEFAULT NULL,
  `juros` decimal(8,2) DEFAULT NULL,
  `desconto` decimal(8,2) DEFAULT NULL,
  `taxa` decimal(8,2) DEFAULT NULL,
  `subtotal` decimal(8,2) DEFAULT NULL,
  `usuario_lanc` int(11) DEFAULT NULL,
  `usuario_pgto` int(11) DEFAULT NULL,
  `pago` varchar(5) DEFAULT NULL,
  `residuo` varchar(5) DEFAULT NULL,
  `hora` time DEFAULT NULL,
  `hash` varchar(100) DEFAULT NULL,
  `caixa` int(11) DEFAULT NULL,
  `produto` int(11) DEFAULT NULL,
  `pessoa` int(11) DEFAULT NULL,
  `quantidade` int(11) DEFAULT NULL,
  `cliente` int(11) DEFAULT NULL,
  `foto` varchar(100) DEFAULT NULL,
  `tipo` varchar(20) DEFAULT NULL,
  `comissao` varchar(35) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



CREATE TABLE `produtos` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `descricao` varchar(1000) DEFAULT NULL,
  `categoria` int(11) NOT NULL,
  `valor_compra` decimal(8,2) NOT NULL,
  `valor_venda` decimal(8,2) NOT NULL,
  `estoque` int(11) NOT NULL,
  `foto` varchar(100) NOT NULL,
  `nivel_estoque` int(11) NOT NULL,
  `tem_estoque` varchar(5) NOT NULL,
  `ativo` varchar(5) NOT NULL,
  `url` varchar(100) NOT NULL,
  `guarnicoes` int(11) DEFAULT NULL,
  `promocao` varchar(5) DEFAULT NULL,
  `combo` varchar(5) DEFAULT NULL,
  `delivery` varchar(5) DEFAULT NULL,
  `preparado` varchar(5) DEFAULT NULL,
  `val_promocional` decimal(8,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `receber` (
  `id` int(11) NOT NULL,
  `descricao` varchar(100) DEFAULT NULL,
  `cliente` int(11) DEFAULT NULL,
  `valor` decimal(8,2) DEFAULT NULL,
  `vencimento` date DEFAULT NULL,
  `data_pgto` date DEFAULT NULL,
  `data_lanc` date DEFAULT NULL,
  `forma_pgto` varchar(50) DEFAULT NULL,
  `frequencia` int(11) DEFAULT NULL,
  `obs` varchar(100) DEFAULT NULL,
  `arquivo` varchar(100) DEFAULT NULL,
  `referencia` varchar(30) DEFAULT NULL,
  `id_ref` int(11) DEFAULT NULL,
  `multa` decimal(8,2) DEFAULT NULL,
  `juros` decimal(8,2) DEFAULT NULL,
  `desconto` decimal(8,2) DEFAULT NULL,
  `taxa` decimal(8,2) DEFAULT NULL,
  `subtotal` decimal(8,2) DEFAULT NULL,
  `usuario_lanc` int(11) DEFAULT NULL,
  `usuario_pgto` int(11) DEFAULT NULL,
  `pago` varchar(5) DEFAULT NULL,
  `residuo` varchar(5) DEFAULT NULL,
  `hora` time DEFAULT NULL,
  `hash` varchar(100) DEFAULT NULL,
  `caixa` int(11) DEFAULT NULL,
  `tipo` varchar(30) DEFAULT NULL,
  `foto` varchar(100) DEFAULT NULL,
  `pessoa` int(11) DEFAULT NULL,
  `produto` int(11) DEFAULT NULL,
  `quantidade` int(11) DEFAULT NULL,
  `funcionario` int(11) DEFAULT NULL,
  `adiantamento` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `saidas` (
  `id` int(11) NOT NULL,
  `produto` int(11) NOT NULL,
  `quantidade` int(11) NOT NULL,
  `motivo` varchar(50) NOT NULL,
  `usuario` int(11) NOT NULL,
  `data` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `sangrias` (
  `id` int(11) NOT NULL,
  `usuario` int(11) NOT NULL,
  `valor` decimal(8,2) NOT NULL,
  `data` date NOT NULL,
  `hora` time NOT NULL,
  `caixa` int(11) NOT NULL,
  `feito_por` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `tarefas` (
  `id` int(11) NOT NULL,
  `usuario` int(11) NOT NULL,
  `usuario_lanc` int(11) NOT NULL,
  `data` date NOT NULL,
  `hora` time NOT NULL,
  `hora_mensagem` time DEFAULT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `hash` varchar(100) DEFAULT NULL,
  `prioridade` varchar(25) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `temp` (
  `id` int(11) NOT NULL,
  `sessao` varchar(35) NOT NULL,
  `tabela` varchar(25) NOT NULL,
  `id_item` int(11) NOT NULL,
  `carrinho` int(11) NOT NULL,
  `data` date DEFAULT NULL,
  `categoria` varchar(5) DEFAULT NULL,
  `grade` int(11) DEFAULT NULL,
  `valor_item` decimal(8,2) DEFAULT NULL,
  `quantidade` int(11) DEFAULT NULL,
  `tipagem` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `senha` varchar(50) DEFAULT NULL,
  `senha_crip` varchar(130) NOT NULL,
  `nivel` varchar(25) NOT NULL,
  `ativo` varchar(5) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `endereco` varchar(150) DEFAULT NULL,
  `foto` varchar(100) DEFAULT NULL,
  `data` date NOT NULL,
  `comissao` int(11) DEFAULT NULL,
  `id_ref` int(11) NOT NULL,
  `pix` varchar(100) DEFAULT NULL,
  `token` varchar(150) DEFAULT NULL,
  `data_nasc` date DEFAULT NULL,
  `numero` varchar(10) DEFAULT NULL,
  `bairro` varchar(50) DEFAULT NULL,
  `cidade` varchar(50) DEFAULT NULL,
  `estado` varchar(50) DEFAULT NULL,
  `cep` varchar(20) DEFAULT NULL,
  `acessar_painel` varchar(5) DEFAULT NULL,
  `cpf` varchar(20) DEFAULT NULL,
  `mostrar_registros` varchar(5) DEFAULT NULL,
  `tipo_chave` varchar(50) NOT NULL,
  `complemento` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `usuarios_permissoes` (
  `id` int(11) NOT NULL,
  `usuario` int(11) NOT NULL,
  `permissao` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `valor_adiantamento` (
  `id` int(11) NOT NULL,
  `abertura` int(11) NOT NULL,
  `valor` decimal(8,2) NOT NULL,
  `nome` varchar(50) DEFAULT NULL,
  `forma_pgto` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `variacoes` (
  `id` int(11) NOT NULL,
  `produto` int(11) NOT NULL,
  `sigla` varchar(5) NOT NULL,
  `nome` varchar(35) DEFAULT NULL,
  `descricao` varchar(50) DEFAULT NULL,
  `valor` decimal(8,2) NOT NULL,
  `ativo` varchar(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `variacoes_cat` (
  `id` int(11) NOT NULL,
  `categoria` int(11) NOT NULL,
  `sigla` varchar(15) NOT NULL,
  `nome` varchar(35) DEFAULT NULL,
  `descricao` varchar(50) DEFAULT NULL,
  `sabores` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `vendas` (
  `id` int(11) NOT NULL,
  `cliente` int(11) NOT NULL,
  `valor` decimal(8,2) NOT NULL,
  `total_pago` decimal(8,2) NOT NULL,
  `troco` decimal(8,2) NOT NULL,
  `data` date NOT NULL,
  `hora` time NOT NULL,
  `status` varchar(25) NOT NULL,
  `pago` varchar(5) NOT NULL,
  `obs` varchar(100) DEFAULT NULL,
  `taxa_entrega` decimal(8,2) NOT NULL,
  `tipo_pgto` varchar(25) NOT NULL,
  `usuario_baixa` int(11) NOT NULL,
  `entrega` varchar(25) NOT NULL,
  `mesa` varchar(15) DEFAULT NULL,
  `nome_cliente` varchar(50) DEFAULT NULL,
  `cupom` decimal(8,2) DEFAULT NULL,
  `entregador` int(11) DEFAULT NULL,
  `pago_entregador` varchar(5) DEFAULT NULL,
  `pedido` int(11) DEFAULT NULL,
  `impressao` varchar(5) DEFAULT NULL,
  `ref_api` varchar(50) DEFAULT NULL,
  `caixa` int(11) DEFAULT NULL,
  `tipo_pedido` varchar(35) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- Tabela de programas de fidelidade
CREATE TABLE fidelidade_programas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    tipo ENUM('pizza_gratis', 'cashback') NOT NULL,
    ativo BOOLEAN DEFAULT TRUE
);

-- Tabela de progresso do cliente (pizza grátis)
CREATE TABLE cliente_pizza_fidelidade (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    tamanho ENUM('G', 'GG') NOT NULL,
    pontos INT DEFAULT 0,
    data_ultima_compra DATETIME,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id)
);

-- Tabela de cashback por categoria
CREATE TABLE cashback_categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    categoria_id INT NOT NULL,
    porcentagem DECIMAL(5,2) NOT NULL,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id)
);

-- Tabela de saldo de cashback do cliente
CREATE TABLE cliente_cashback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    categoria_id INT NOT NULL,
    saldo DECIMAL(10,2) DEFAULT 0,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id),
    FOREIGN KEY (categoria_id) REFERENCES categorias(id)
);

-- Tabela de resgates
CREATE TABLE resgates_fidelidade (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    tipo ENUM('pizza_gratis', 'cashback') NOT NULL,
    valor DECIMAL(10,2),
    data_resgate DATETIME DEFAULT CURRENT_TIMESTAMP,
    utilizado BOOLEAN DEFAULT FALSE,
    pedido_id INT,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id)
);
CREATE TABLE `fidelidade_processamentos` (
  'id' int(11) NOT NULL,
  'venda_id' int(11) NOT NULL,
  'data_processamento' datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;