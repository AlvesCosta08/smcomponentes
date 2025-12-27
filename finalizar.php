<?php
@session_start();
require_once("cabecalho.php");
require_once("js/ajax/ApiConfig.php");

$sessao = @$_SESSION['sessao_usuario'];
$id_usuario = @$_SESSION['id'];
$sessao_pedido_balcao = @$_SESSION['pedido_balcao'];

// Verifica se há itens no carrinho
$query = $pdo->query("SELECT * FROM carrinho WHERE sessao = '$sessao'");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$total_reg = @count($res);

if ($total_reg == 0) {
    echo "<script>window.location='index'</script>";
    exit();
}

$total_carrinho = 0;
foreach ($res as $item) {
    $total_carrinho += $item['total_item'] * $item['quantidade'];
}
$total_carrinhoF = number_format($total_carrinho, 2, ',', '.');

// Recuperar dados salvos (se existirem)
$nome_salvo = isset($_SESSION['nome_cli_del']) ? $_SESSION['nome_cli_del'] : '';
$tel_salvo = isset($_SESSION['telefone_cli_del']) ? $_SESSION['telefone_cli_del'] : '';
?>

<div class="main-container">
  <nav class="navbar bg-light fixed-top" style="box-shadow: 0px 3px 5px rgba(0, 0, 0, 0.20);">
    <div class="container-fluid">
      <a class="navbar-brand" href="index.php">
        <img src="sistema/img/<?= $logo_sistema ?>" alt="" width="80px" class="d-inline-block align-text-top">
        Finalizar Pedido
      </a>
      <?php require_once("icone-carrinho.php") ?>
    </div>
  </nav>

  <div style="margin-top: 70px; padding: 20px; max-width: 500px; margin-left: auto; margin-right: auto;">
    <div class="text-center mb-4">
      <h5>Informe seus dados para finalizar</h5>
      <p class="text-muted">Apenas seu nome e telefone</p>
    </div>

    <!-- Nome -->
    <div class="mb-3">
      <label for="nome" class="form-label">Nome ou Razão Social</label>
      <input type="text" class="form-control" id="nome" placeholder="Seu nome completo" 
             value="<?= htmlspecialchars($nome_salvo) ?>" required>
    </div>

    <!-- Telefone -->
    <div class="mb-4">
      <label for="telefone" class="form-label">Telefone (com DDD)</label>
      <input type="tel" class="form-control" id="telefone" placeholder="(00) 00000-0000" 
             value="<?= htmlspecialchars($tel_salvo) ?>" required>
    </div>

    <!-- Total -->
    <div class="card mb-4">
      <div class="card-body text-center">
        <h6 class="card-title mb-1">Total do Pedido</h6>
        <h4 class="card-text text-success">R$ <?= $total_carrinhoF ?></h4>
        <?php if (!empty($pedido_minimo) && $total_carrinho < $pedido_minimo): ?>
          <small class="text-danger">Mínimo: R$ <?= number_format($pedido_minimo, 2, ',', '.') ?></small>
        <?php endif; ?>
      </div>
    </div>

    <!-- Botões -->
    <div class="d-grid gap-2">
      <button class="btn btn-success btn-lg" onclick="finalizarPedidoSimplificado()">
        <i class="bi bi-check-circle"></i> Concluir Pedido
      </button>
      <a href="index.php" class="btn btn-outline-secondary">← Voltar</a>
    </div>
  </div>
</div>

<!-- Loading -->
<div id="loading" class="modal fade" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content text-center p-3">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Enviando...</span>
      </div>
      <p class="mt-2 mb-0">Enviando seu pedido...</p>
    </div>
  </div>
</div>

<!-- Scripts -->
<script src="js/jquery-3.4.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="js/mascaras.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.11/jquery.mask.min.js"></script>

<script>
$(document).ready(function() {
  $('#telefone').mask('(00) 00000-0000');
});

function finalizarPedidoSimplificado() {
  const nome = $('#nome').val().trim();
  const telefone = $('#telefone').val().trim();

  if (!nome) {
    Swal.fire('Atenção', 'Por favor, informe seu nome.', 'warning');
    return;
  }

  if (telefone.replace(/\D/g, '').length < 10) {
    Swal.fire('Atenção', 'Telefone inválido. Informe um número com DDD.', 'warning');
    return;
  }

  <?php if (!empty($pedido_minimo)): ?>
  const totalCarrinho = <?= $total_carrinho ?>;
  if (totalCarrinho < <?= $pedido_minimo ?>) {
    Swal.fire('Pedido mínimo', 'Seu pedido deve ser de pelo menos R$ <?= number_format($pedido_minimo, 2, ',', '.') ?>.', 'info');
    return;
  }
  <?php endif; ?>

  // Salvar nos dados locais
  localStorage.setItem('nome_cli_del', nome);
  localStorage.setItem('telefone_cli_del', telefone);
  <?php if (!empty($sessao_pedido_balcao)): ?>
  sessionStorage.setItem('nome_cli_balcao', nome);
  sessionStorage.setItem('telefone_cli_balcao', telefone);
  <?php endif; ?>

  // Mostrar loading
  $('#loading').modal({ backdrop: 'static', keyboard: false });

  const formData = new FormData();
  formData.append('nome_cliente', nome);
  formData.append('tel_cliente', telefone);
  formData.append('entrega', 'Retirar');
  formData.append('pagamento', 'A combinar');
  formData.append('obs', '');
  formData.append('taxa_entrega', '0');
  formData.append('cupom', '0');
  formData.append('esta_pago', '<?= ($sessao_pedido_balcao == 'BALCÃO') ? 'Não' : '' ?>');

  <?php if ($sessao_pedido_balcao == 'BALCÃO'): ?>
  formData.append('mesa', '');
  <?php endif; ?>

  $.ajax({
    url: 'js/ajax/inserir-pedido.php',
    method: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    timeout: 15000
  })
  .done(function(resposta) {
    $('#loading').modal('hide');
    const partes = resposta.split('*');
    const status = partes[1]?.trim();
    const idPedido = partes[2]?.trim();

    if (status === 'Pedido Finalizado' && idPedido) {
      Swal.fire({
        icon: 'success',
        title: 'Pedido enviado!',
        text: 'Seu pedido foi recebido com sucesso.',
        timer: 2000,
        showConfirmButton: false
      }).then(() => {
        <?php if ($sessao_pedido_balcao == 'BALCÃO'): ?>
        window.close();
        <?php else: ?>
        window.location.href = 'pedido/' + idPedido;
        <?php endif; ?>
      });
    } else {
      Swal.fire('Erro', 'Não foi possível enviar seu pedido. Tente novamente.', 'error');
    }
  })
  .fail(function() {
    $('#loading').modal('hide');
    Swal.fire('Erro de conexão', 'Verifique sua internet e tente novamente.', 'error');
  });
}
</script>




