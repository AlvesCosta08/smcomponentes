<?php
@session_start();
require_once("cabecalho.php");

// Lógica existente de sessão/mesa
$id_mesa = @$_POST['id_mesa'];
$pedido_balcao = @$_POST['pedido_balcao'];

$url_instagram = 'https://www.instagram.com/' . $instagram_sistema . '/';

if ($pedido_balcao != "") {
  unset($_SESSION['id_mesa'], $_SESSION['nome_mesa'], $_SESSION['id_ab_mesa'], $_SESSION['sessao_usuario'], $_SESSION['id_edicao']);
  $_SESSION['pedido_balcao'] = $pedido_balcao;

  
}

@$sessão_balcao = $_SESSION['pedido_balcao'];

if ($sessão_balcao != '') {
  $nome_sistema = 'Pedido Balcão';
}


if ($id_mesa != "") {
  $_SESSION['id_mesa'] = $id_mesa;
  unset($_SESSION['id_edicao']);
}

if (@$_SESSION['id_mesa'] != "") {
  $id_mesa = $_SESSION['id_mesa'];
  unset($_SESSION['id_edicao']);
}



//buscar informações da edição pedido
$id_edicao = @$_POST['id_edicao'];

if ($id_edicao != "") {
  $_SESSION['id_edicao'] = $id_edicao;
  unset($_SESSION['id_mesa'], $_SESSION['nome_mesa'], $_SESSION['id_ab_mesa'], $_SESSION['sessao_usuario']);
}

if (@$_SESSION['id_edicao'] != "") {
  $id_edicao = $_SESSION['id_edicao'];
  unset($_SESSION['id_mesa'], $_SESSION['nome_mesa'], $_SESSION['id_ab_mesa'], $_SESSION['sessao_usuario']);
}

//buscar os dados da mesa
$query2 = $pdo->query("SELECT * FROM mesas where id = '$id_mesa' ");
$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
$nome_mesa = 'Mesa: ' . @$res2[0]['nome'];

if (@$res2[0]['nome'] == "") {
  $_SESSION['nome_mesa'] = '';
} else {
  $_SESSION['nome_mesa'] = $nome_mesa;
}

$query2 = $pdo->query("SELECT * FROM abertura_mesa where mesa = '$id_mesa' and status = 'Aberta'");
$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
$id_ab_mesa = @$res2[0]['id'];
$_SESSION['id_ab_mesa'] = $id_ab_mesa;


$img = 'aberto.png';




if ($status_estabelecimento == "Fechado" and $id_mesa == "" and $sessão_balcao == "") {
  $img = 'fechado.png';
}

if ($id_mesa == "" and $sessão_balcao == "") {
  $data = date('Y-m-d');
  //verificar se está aberto hoje
  $diasemana = array("Domingo", "Segunda-Feira", "Terça-Feira", "Quarta-Feira", "Quinta-Feira", "Sexta-Feira", "Sábado");
  $diasemana_numero = date('w', strtotime($data));
  $dia_procurado = $diasemana[$diasemana_numero];

  //percorrer os dias da semana que ele trabalha
  $query = $pdo->query("SELECT * FROM dias where dia = '$dia_procurado'");
  $res = $query->fetchAll(PDO::FETCH_ASSOC);
  if (@count($res) > 0) {
    $img = 'fechado.png';
  }

  $hora_atual = date('H:i:s');

  //nova verificação de horarios
  $start = strtotime(date('Y-m-d' . $horario_abertura));
  $end = strtotime(date('Y-m-d' . $horario_fechamento));
  $now = time();

  if ($start <= $now && $now <= $end) {
  } else {

    if ($end < $start) {

      if ($now > $start) {
      } else {
        if ($now < $end) {
        } else {
          $img = 'fechado.png';
        }
      }
    } else {
      $img = 'fechado.png';
    }
  }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $nome_sistema ?> - E-commerce</title>
    
    <!-- CSS estilo Mercado Livre -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/ml-style.css">
    
    <!-- Bootstrap (opcional) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="css/principal.css">
    <link rel="stylesheet" href="css/chatbot.css">

</head>
<body class="bg-light">

<!-- Header fixo no topo com nova identidade visual e responsivo estilo e-commerce -->
<header class="bg-primary py-3 shadow-sm fixed-top">
    <div class="container-fluid px-3">
        <!-- Linha principal: logo | busca | ícones -->
        <div class="row align-items-center g-3">
            <!-- Logo -->
            <div class="col-6 col-md-3 d-flex align-items-center justify-content-start">
                <a href="index.php" class="d-flex align-items-center text-decoration-none">
                    <img src="sistema/img/<?php echo $logo_sistema ?>" alt="Logo" width="120" class="me-2">
                    <span class="text-white fw-bold fs-5 d-none d-md-inline"><?php //echo $nome_sistema ?></span>
                </a>
            </div>

            <!-- Barra de Busca -->
            <div class="col-12 col-md-6 order-3 order-md-2">
                <div class="input-group">
                    <input type="text" class="form-control border-0 rounded-pill shadow-sm" placeholder="Buscar produtos..." id="buscar" onkeyup="buscarProduto()">
                    <button class="btn btn-outline-light rounded-pill" type="button" aria-label="Buscar">
                        <i class="bi bi-search text-white"></i>
                    </button>
                </div>
            </div>

            <!-- Ícones: Conta e Carrinho -->
            <div class="col-6 col-md-3 d-flex justify-content-end align-items-center gap-3 order-2 order-md-3">
                <a href="#" class="text-white text-decoration-none d-flex align-items-center" aria-label="Minha Conta">
                    <i class="bi bi-person-fill fs-4"></i>
                </a>
                <?php require_once("icone-carrinho.php") ?>
            </div>
        </div>

        <!-- Aviso CNPJ (sempre visível e centralizado) -->
        <div class="row mt-2">
            <div class="col-12 text-center">
                <div class="text-warning fw-bold small">
                    🚨 Atendemos somente clientes CNPJ
                </div>
            </div>
        </div>
    </div>
</header>







<!-- Banner Principal -->
<div>
  <div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel">
    
    <!-- Indicadores -->
    <div class="carousel-indicators">
      <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
      <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="1" aria-label="Slide 2"></button>
      <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="2" aria-label="Slide 3"></button>
    </div>

    <!-- Slides -->
    <div class="carousel-inner rounded">
      
      <!-- Slide 1 -->
      <div class="carousel-item active">
        <a href="index.php?pagina=produtos&cat=eletronicos">
          <img src="img/circuit-board-96597_1280.jpg" class="d-block w-100 banner-img" alt="Banner 1">
          <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-50 rounded p-3">
            <h5 class="text-white">A sua distribuidora de confiança em componentes eletrônicos</h5>
            <p class="text-light small">Atuamos com foco em qualidade, variedade e agilidade no atendimento para CNPJs.</p>
          </div>
        </a>
      </div>

      <!-- Slide 2 -->
      <div class="carousel-item">
        <a href="index.php?pagina=produtos&cat=acessorios">
          <img src="img/circuit-board-1429589_1280.jpg" class="d-block w-100 banner-img" alt="Banner 2">
          <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-50 rounded p-3">
            <h5 class="text-white">Estoque completo: transistores, capacitores, diodos, resistores e muito mais!</h5>
            <p class="text-light small">Tudo o que você precisa, em um só lugar, com pronta entrega.</p>
          </div>
        </a>
      </div>

      <!-- Slide 3 -->
      <div class="carousel-item">
        <a href="index.php?pagina=produtos&cat=promocoes">
          <img src="img/capacitors-5367873_1280.jpg" class="d-block w-100 banner-img" alt="Banner 3">
          <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-50 rounded p-3">
            <h5 class="text-white">Soluções para todos os seus projetos eletrônicos</h5>
            <p class="text-light small">Do protótipo à produção: suporte completo para lojistas, técnicos e engenheiros.</p>
          </div>
        </a>
      </div>

    </div>

    <!-- Controles -->
    <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
      <span class="carousel-control-prev-icon" aria-hidden="true"></span>
      <span class="visually-hidden">Anterior</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
      <span class="carousel-control-next-icon" aria-hidden="true"></span>
      <span class="visually-hidden">Próximo</span>
    </button>
  </div>
</div>






<!-- Ofertas com novo estilo -->
<section class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
        <h2 class="h4 fw-bold text-azul-marinho">
            <i class="bi bi-lightning-charge-fill text-warning me-2"></i>
            Ofertas da semana
        </h2>
        <a href="#" class="text-decoration-none text-primary fw-bold">Ver todas <i class="bi bi-chevron-right"></i></a>
    </div>
    
    <div class="row g-3">
        <?php
        $query = $pdo->query("SELECT * FROM produtos WHERE ativo = 'Sim' AND promocao = 'Sim'");
        $res = $query->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($res as $produto) {
            if ($produto['tem_estoque'] == 'Sim' && $produto['estoque'] <= 0) continue;
            
            $valor = $produto['valor_venda'];
            $val_promocional = $produto['val_promocional'];
            $desconto = $val_promocional ? round((($valor - $val_promocional) / $valor) * 100) : 0;
        ?>
        <div class="col-6 col-md-3">
            <div class="card h-100 border-0">
                <div class="position-relative overflow-hidden">
                    <img src="sistema/painel/images/produtos/<?php echo $produto['foto'] ?>" class="card-img-top" alt="<?php echo $produto['nome'] ?>">
                    <?php if ($desconto > 0) { ?>
                        <span class="badge bg-danger position-absolute top-0 start-0 m-2"><?php echo $desconto ?>% OFF</span>
                    <?php } ?>
                    <div class="card-img-overlay d-flex align-items-end p-0">
                        <div class="w-100 bg-azul-marinho-opacity text-center py-1">
                            <small class="text-white">Frete grátis</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <h5 class="card-title fs-6 text-azul-marinho"><?php echo $produto['nome'] ?></h5>
                    <div class="d-flex align-items-center mb-2">
                        <?php if ($val_promocional) { ?>
                            <span class="fw-bold text-danger fs-5">R$ <?php echo number_format($val_promocional, 2, ',', '.') ?></span>
                            <small class="text-muted text-decoration-line-through ms-2">R$ <?php echo number_format($valor, 2, ',', '.') ?></small>
                        <?php } else { ?>
                            <span class="fw-bold text-dark fs-5">R$ <?php echo number_format($valor, 2, ',', '.') ?></span>
                        <?php } ?>
                    </div>
                    <a href="produto-<?php echo $produto['url'] ?>" class="btn btn-outline-primary w-100 btn-sm">Comprar agora</a>
                </div>
            </div>
        </div>
        <?php } ?>
    </div>
</section>

<!-- Categorias com novo estilo -->
<section class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
        <h2 class="h4 fw-bold text-azul-marinho">
            <i class="bi bi-grid-fill text-primary me-2"></i>
            Nossas Categorias
        </h2>
        <a href="#" class="text-decoration-none text-primary fw-bold">Ver todas <i class="bi bi-chevron-right"></i></a>
    </div>
    
    <?php
    // Definir o número de itens por página
    $itens_por_pagina = 8;
    
    // Pegar a página atual da URL, se não existir, definir como 1
    $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
    
    // Calcular o OFFSET
    $offset = ($pagina - 1) * $itens_por_pagina;
    
    // Contar o total de categorias ativas
    $query_count = $pdo->query("SELECT COUNT(*) as total FROM categorias WHERE ativo = 'Sim'");
    $total_categorias = $query_count->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Calcular o número total de páginas
    $total_paginas = ceil($total_categorias / $itens_por_pagina);
    
    // Obter as categorias com o limite e offset
    $query = $pdo->query("SELECT * FROM categorias WHERE ativo = 'Sim' LIMIT $itens_por_pagina OFFSET $offset");
    $res = $query->fetchAll(PDO::FETCH_ASSOC);
    ?>
    
    <div class="row g-4">
        <?php
        foreach ($res as $categoria) {
            $query2 = $pdo->query("SELECT COUNT(*) as total FROM produtos WHERE categoria = '{$categoria['id']}' AND ativo = 'Sim'");
            $total_produtos = $query2->fetch(PDO::FETCH_ASSOC)['total'];
            
            if ($total_produtos == 0) continue;
            
            $link = $categoria['mais_sabores'] == 'Sim' ? "categoria-sabores-{$categoria['url']}" : "categoria-{$categoria['url']}";
        ?>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="card shadow-sm h-100" style="width: 100%;">
                <img src="sistema/painel/images/categorias/<?php echo $categoria['foto'] ?>" class="card-img-top" alt="<?php echo $categoria['nome'] ?>" style="height: 200px; object-fit: cover;">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="card-title mb-0 text-azul-marinho"><?php echo $categoria['nome'] ?></h6>
                        <small class="text-muted"><?php echo $total_produtos ?> itens</small>
                    </div>
                    <a href="<?php echo $link ?>" class="btn btn-primary mt-auto">Ver Categoria</a>
                </div>
            </div>
        </div>
        <?php } ?>
    </div>

    <!-- Paginação -->
    <div class="d-flex justify-content-center mt-4">
        <ul class="pagination">
            <li class="page-item <?php echo $pagina == 1 ? 'disabled' : ''; ?>">
                <a class="page-link" href="?pagina=<?php echo $pagina - 1; ?>" aria-label="Anterior">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>
            
            <?php
            // Exibir links das páginas
            for ($i = 1; $i <= $total_paginas; $i++) {
                echo "<li class='page-item " . ($pagina == $i ? 'active' : '') . "'><a class='page-link' href='?pagina=$i'>$i</a></li>";
            }
            ?>
            
            <li class="page-item <?php echo $pagina == $total_paginas ? 'disabled' : ''; ?>">
                <a class="page-link" href="?pagina=<?php echo $pagina + 1; ?>" aria-label="Próxima">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        </ul>
    </div>
</section>




<!-- Rodapé com nova identidade -->
<footer class="bg-primary text-white mt-5 py-4">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4 mb-md-0">
                <h5 class="fw-bold mb-3">
                    <img src="sistema/img/<?php echo $logo_sistema ?>" width="30" class="me-2">
                    <?php //echo $nome_sistema ?>
                </h5>
                <p class="small"><?php echo $endereco_sistema ?></p>
                <div class="social-icons">
                    <a href="<?php echo $url_instagram ?>" class="text-white me-2"><i class="bi bi-instagram fs-5"></i></a>
                    <a href="http://api.whatsapp.com/send?1=pt_BR&phone=<?php echo $tel_whats ?>" class="text-white me-2"><i class="bi bi-whatsapp fs-5"></i></a>
                    <a href="#" class="text-white me-2"><i class="bi bi-facebook fs-5"></i></a>
                </div>
            </div>
            
            <div class="col-md-2 mb-4 mb-md-0">
                <h5 class="fw-bold mb-3">Institucional</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="#" class="text-white text-decoration-none small">Sobre nós</a></li>
                    <li class="mb-2"><a href="#" class="text-white text-decoration-none small">Termos de uso</a></li>
                    <li class="mb-2"><a href="#" class="text-white text-decoration-none small">Políticas de privacidade</a></li>
                </ul>
            </div>
            
            <div class="col-md-3 mb-4 mb-md-0">
                <h5 class="fw-bold mb-3">Atendimento</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><i class="bi bi-headset me-2"></i> <span class="small">Central de ajuda</span></li>
                    <li class="mb-2"><i class="bi bi-envelope me-2"></i> <span class="small">contato@<?php echo strtolower(str_replace(' ', '', $nome_sistema)) ?>.com</span></li>
                    <li class="mb-2"><i class="bi bi-telephone me-2"></i> <span class="small"><?php echo $telefone_sistema ?></span></li>
                </ul>
            </div>
            
            <div class="col-md-3">
                <h5 class="fw-bold mb-3">Formas de pagamento</h5>
                <div class="d-flex flex-wrap">
                    <img src="img/payments/visa.png" width="40" class="me-2 mb-2">
                    <img src="img/payments/mastercard.png" width="40" class="me-2 mb-2">
                    <img src="img/payments/pix.png" width="40" class="me-2 mb-2">
                    <img src="img/payments/boleto.png" width="40" class="me-2 mb-2">
                </div>
                <div class="mt-3">
                    <a href="sistema" class="btn btn-outline-light btn-sm">
                        <i class="bi bi-lock"></i> Área administrativa
                    </a>
                </div>
            </div>
        </div>
        
        <div class="row mt-4 pt-3 border-top">
            <div class="col-md-12 text-center">
                <p class="small mb-0">© <?php echo date('Y') ?> <?php echo $nome_sistema ?> - Todos os direitos reservados</p>
            </div>
        </div>
    </div>
</footer>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function buscarProduto() {
    var buscar = $('#buscar').val();
    if(buscar == "") {
        $('#area_busca').hide();
    } else {
        $('#area_busca').show();
        $.ajax({
            url: 'js/ajax/buscar_produtos.php',
            method: 'POST',
            data: { buscar: buscar },
            success: function(mensagem) {
                $('#area_busca').html(mensagem);
            }
        });
    }
}
</script>
<!-- Botão Flutuante do WhatsApp -->
<a href="https://wa.me/5585999055729" class="whatsapp-float" target="_blank" rel="noopener noreferrer">
    <i class="fab fa-whatsapp whatsapp-icon"></i>
</a>
<!-- Font Awesome e Scripts -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="./includes/chatbot.css">
<!-- Botão flutuante -->
<button id="chatbot-toggle" class="chatbot-float">
  <i class="fas fa-comments chatbot-icon"></i>
</button>

<!-- Container do Chatbot -->
<div id="chatbot" class="chatbot-container">
  <div class="chatbot-header">
    <span>Atendente Virtual</span>
    <button id="chatbot-close" class="close-btn"><i class="fas fa-times"></i></button>
  </div>
  <div class="chatbot-body" id="chatbot-messages">
    <div class="chat-message bot">Olá! Como posso ajudar?</div>
  </div>
  <div class="chatbot-footer">
    <input type="text" id="chatbot-input" placeholder="Digite sua mensagem...">
    <button id="chatbot-send"><i class="fas fa-paper-plane"></i></button>
    <button id="chatbot-listen" title="Falar com o microfone">
      <i class="fas fa-microphone"></i>
    </button>
    <button id="chatbot-speak" title="Ouvir a resposta" disabled>
      <i class="fas fa-volume-up"></i>
    </button>
  </div>
</div>
<script src="./includes/chatbot.js"></script>
</body>
</html>