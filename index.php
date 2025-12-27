<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
@session_start();
require_once("cabecalho.php");

$url_instagram = 'https://www.instagram.com/' . $instagram_sistema . '/';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($nome_sistema) ?> - E-commerce</title>
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- CSS estilo -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/ml-style.css">
    
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="css/principal.css">
    <link rel="stylesheet" href="css/chatbot.css">
</head>
<body class="bg-light">

<!-- HEADER FIXO, RESPONSIVO E BEM DISTRIBUÍDO -->
<header class="bg-primary text-white fixed-top shadow-sm">
    <div class="container-fluid px-3 py-2">
        <!-- Linha de cima: Logo + Ícones -->
        <div class="d-flex justify-content-between align-items-center mb-2">
            <!-- LOGO -->
            <a href="index.php" class="d-flex align-items-center text-white text-decoration-none">
                <img src="sistema/img/<?php echo htmlspecialchars($logo_sistema) ?>" alt="Logo" style="width: 120px;" class="me-2">
                <span class="fw-bold fs-6 d-none d-md-inline"><?php // echo $nome_sistema ?></span>
            </a>

            <!-- ÍCONES: Conta e Carrinho -->
            <div class="d-flex align-items-center gap-3">
                <a href="#" class="text-white fs-5" title="Minha Conta">
                    
                </a>
                <?php require_once("icone-carrinho.php"); ?>
            </div>
        </div>



        <!-- Aviso CNPJ -->
        <div class="text-center mt-2">
            <small class="text-warning fw-bold">
                🚨 Atendemos exclusivamente clientes com CNPJ
            </small>
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

<!-- Categorias com novo estilo -->
<section class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
        <h2 class="h4 fw-bold text-azul-marinho">
            <i class="bi bi-grid-fill text-primary me-2"></i> Faça seu pedido
            <div id="area_busca" style="display:none; margin-top:-30px"></div>
        </h2>       
    </div>
            <!-- Linha de busca -->
        <div class="row">
            <div class="col-12">
                <form onsubmit="event.preventDefault(); buscarProduto();">
                    <div class="input-group">
                        <input 
                            type="text" 
                            name="buscar" 
                            id="buscar"
                            class="form-control border-0 rounded-start-pill shadow-sm"
                            placeholder="Buscar produto..." 
                            onkeyup="buscarProduto()">
                        <button type="button" onclick="buscarProduto()" class="btn btn-light rounded-end-pill">
                            <i class="bi bi-search text-primary"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    <?php
    // Definir o número de itens por página
    $itens_por_pagina = 8;

    // Pegar a página atual da URL, se não existir, definir como 1
    $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
    $pagina = max(1, $pagina); // Evita páginas negativas

    // Calcular o OFFSET
    $offset = ($pagina - 1) * $itens_por_pagina;

    // Contar o total de categorias ativas com produtos ativos
    $countStmt = $pdo->prepare("
        SELECT COUNT(DISTINCT c.id) as total
        FROM categorias c
        INNER JOIN produtos p ON p.categoria = c.id AND p.ativo = 1
        WHERE c.ativo = 'Sim'
    ");
    $countStmt->execute();
    $total_categorias = (int) $countStmt->fetchColumn();

    // Calcular o número total de páginas
    $total_paginas = $total_categorias ? ceil($total_categorias / $itens_por_pagina) : 1;

    // Obter as categorias com produtos ativos (paginadas)
    $dataStmt = $pdo->prepare("
        SELECT 
            c.*,
            COUNT(p.id) AS total_produtos
        FROM categorias c
        INNER JOIN produtos p ON p.categoria = c.id AND p.ativo = 1
        WHERE c.ativo = 'Sim'
        GROUP BY c.id
        ORDER BY c.nome
        LIMIT :limit OFFSET :offset
    ");
    $dataStmt->bindValue(':limit', $itens_por_pagina, PDO::PARAM_INT);
    $dataStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $dataStmt->execute();
    $res = $dataStmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <div class="row g-4">
        <?php foreach ($res as $categoria): ?>
            <?php
            $total_produtos = (int) $categoria['total_produtos'];
            $link = $categoria['mais_sabores'] === 'Sim' 
                ? "categoria-sabores-" . $categoria['url'] 
                : "categoria-" . $categoria['url'];

            $foto = !empty($categoria['foto']) 
                ? "sistema/painel/images/categorias/" . htmlspecialchars($categoria['foto']) 
                : "img/default-categoria.jpg";
            ?>
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="card shadow-sm h-100" style="width: 100%;">
                    <img src="<?php echo $foto ?>" class="card-img-top" alt="<?php echo htmlspecialchars($categoria['nome']) ?>" style="height: 200px; object-fit: cover;">
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="card-title mb-0 text-muted"><?php echo htmlspecialchars($categoria['nome']) ?></h6>
                            <small class="text-muted"><?php echo $total_produtos ?> itens</small>
                        </div>
                        <a href="<?php echo htmlspecialchars($link) ?>" class="btn btn-primary mt-auto">Ver Categoria</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Paginação -->
    <div class="d-flex justify-content-center mt-4">
        <ul class="pagination">
            <li class="page-item <?php echo $pagina == 1 ? 'disabled' : ''; ?>">
                <a class="page-link" href="?pagina=<?php echo max(1, $pagina - 1); ?>" aria-label="Anterior">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>

            <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                <li class="page-item <?php echo $pagina == $i ? 'active' : ''; ?>">
                    <a class="page-link" href="?pagina=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>

            <li class="page-item <?php echo $pagina >= $total_paginas ? 'disabled' : ''; ?>">
                <a class="page-link" href="?pagina=<?php echo min($total_paginas, $pagina + 1); ?>" aria-label="Próxima">
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
                    <img src="sistema/img/<?php echo htmlspecialchars($logo_sistema) ?>" width="80" class="me-2">
                    <?php //echo $nome_sistema ?>
                </h5>
                <p class="small"><?php echo htmlspecialchars($endereco_sistema) ?></p>
                <div class="social-icons">
                    <a href="<?php echo htmlspecialchars($url_instagram) ?>" class="text-white me-2"><i class="bi bi-instagram fs-5"></i></a>
                    <a href="http://api.whatsapp.com/send?1=pt_BR&phone=<?php echo htmlspecialchars($tel_whats) ?>" class="text-white me-2"><i class="bi bi-whatsapp fs-5"></i></a>
                    <a href="#" class="text-white me-2"><i class="bi bi-facebook fs-5"></i></a>
                </div>
            </div>
            
            <div class="col-md-2 mb-4 mb-md-0">
                <h5 class="fw-bold mb-3">Institucional</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="#" class="text-white text-decoration-none small">Sobre nós</a></li>
                    <li class="mb-2"><a href="termo_uso.html" class="text-white text-decoration-none small">Termos de uso</a></li>
                    <li class="mb-2"><a href="politica.html" class="text-white text-decoration-none small">Política de Privacidade e Proteção de Dados</a></li>
                </ul>
            </div>
            
            <div class="col-md-3 mb-4 mb-md-0">
                <h5 class="fw-bold mb-3">Atendimento</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><i class="bi bi-headset me-2"></i> <span class="small">Central de ajuda</span></li>
                    <li class="mb-2"><i class="bi bi-envelope me-2"></i> <span class="small">contato@<?php echo strtolower(str_replace(' ', '', $nome_sistema)) ?>.com</span></li>
                    <li class="mb-2"><i class="bi bi-telephone me-2"></i> <span class="small"><?php echo htmlspecialchars($telefone_sistema) ?></span></li>
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
                       <i class="bi bi-lock" style="opacity: 0.5;"></i>
                    </a>
                </div>
            </div>
        </div>
        
        <div class="row mt-4 pt-3 border-top">
            <div class="col-md-12 text-center">
                <p class="small mb-0">© <?php echo date('Y') ?> <?php echo htmlspecialchars($nome_sistema) ?> - Todos os direitos reservados</p>
            </div>
        </div>
    </div>
</footer>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Font Awesome -->
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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggleBtn = document.getElementById('chatbot-toggle');
        const chatbot = document.getElementById('chatbot');
        const closeBtn = document.getElementById('chatbot-close');

        toggleBtn.addEventListener('click', function () {
            chatbot.style.display = 'block';
            toggleBtn.style.zIndex = '0';
        });

        closeBtn.addEventListener('click', function () {
            chatbot.style.display = 'none';
            toggleBtn.style.zIndex = '9999';
        });
    });

    function buscarProduto(){
        var buscar = $('#buscar').val();
        if(buscar == ""){
            $('#area_busca').hide();
        }else{
            $('#area_busca').show();

            $.ajax({
                url: 'js/ajax/buscar_produtos.php',
                method: 'POST',
                data: { buscar: buscar },
                dataType: "text",
                success: function(mensagem) {       
                    $('#area_busca').html(mensagem);
                },
            });
        }
    }
</script>

<!-- Botão Flutuante do WhatsApp -->
<a href="https://wa.me/<?php echo ltrim($tel_whats, '+'); ?>" class="whatsapp-float" target="_blank" rel="noopener noreferrer">
    <i class="fab fa-whatsapp whatsapp-icon"></i>
</a>

</body>
</html>