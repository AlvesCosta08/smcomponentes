<?php
session_start();
require_once '../includes/Database.php';
require_once 'produtos/ProdutoController.php';

// Verificar permissões
if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../login.php');
    exit();
}

$controller = new ProdutoController();
$pagina = 'produtos';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Produtos</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    
    <style>
        .table-hover tbody tr:hover { background-color: rgba(13, 110, 253, 0.05); }
        .badge-estoque { font-size: 0.75em; }
        .img-thumbnail { max-width: 80px; max-height: 80px; object-fit: cover; }
        .cursor-pointer { cursor: pointer; }
        .btn-action { width: 36px; height: 36px; padding: 0; }
        .card-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    </style>
</head>
<body>
    <div class="container-fluid py-3">
        <!-- Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
            <div>
                <h2 class="h4 mb-1 text-primary">
                    <i class="bi bi-box-seam me-2"></i>Produtos
                </h2>
                <p class="text-muted small mb-0">Gerencie seu inventário de produtos</p>
            </div>
            
            <div class="d-flex flex-wrap gap-2">
                <button class="btn btn-primary" onclick="ProdutoUI.inserir()">
                    <i class="bi bi-plus-lg me-1"></i>Novo Produto
                </button>
                
                <button class="btn btn-outline-danger d-none" id="btn-deletar-multiplos" onclick="ProdutoUI.deletarSelecionados()">
                    <i class="bi bi-trash me-1"></i>Excluir Selecionados
                </button>
                
                <select class="form-select form-select-sm w-auto" id="filtro-categoria" onchange="ProdutoUI.buscar()">
                    <option value="">Todas as Categorias</option>
                    <?php echo $controller->listarCategoriasOptions(); ?>
                </select>
                
                <div class="input-group input-group-sm" style="width: 200px;">
                    <input type="text" class="form-control" placeholder="Buscar produto..." id="busca-nome" onkeyup="ProdutoUI.buscar()">
                    <button class="btn btn-outline-secondary" type="button">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Loading -->
        <div id="loading" class="text-center py-5 d-none">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Carregando...</span>
            </div>
            <p class="text-muted mt-2">Carregando produtos...</p>
        </div>
        
        <!-- Tabela de Produtos -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="tabela-produtos">
                        <thead class="table-light">
                            <tr>
                                <th width="40">
                                    <input type="checkbox" class="form-check-input" id="selecionar-todos" onchange="ProdutoUI.selecionarTodos(this.checked)">
                                </th>
                                <th>Produto</th>
                                <th width="100">Categoria</th>
                                <th width="100">Compra</th>
                                <th width="100">Venda</th>
                                <th width="80">Estoque</th>
                                <th width="80">Status</th>
                                <th width="120" class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-produtos">
                            <!-- Os produtos serão carregados via AJAX -->
                        </tbody>
                    </table>
                </div>
                
                <!-- Paginação -->
                <div class="d-flex justify-content-between align-items-center p-3 border-top" id="paginacao">
                    <!-- Paginação será carregada via AJAX -->
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modals -->
    <?php include 'produtos/views/modal-form.php'; ?>
    <?php include 'produtos/views/modal-dados.php'; ?>
    <?php include 'produtos/views/modal-entrada.php'; ?>
    <?php include 'produtos/views/modal-saida.php'; ?>
    
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Aplicação JS -->
<script>
        const ProdutoAPI = {
            baseUrl: 'paginas/produtos/api/',
            
            async listar(pagina = 1, busca = '', categoria = '') {
                const formData = new FormData();
                formData.append('pagina', pagina);
                if (busca) formData.append('busca', busca);
                if (categoria) formData.append('categoria', categoria);
                
                const response = await fetch(`${this.baseUrl}listar.php`, {
                    method: 'POST',
                    body: formData
                });
                return await response.json();
            },
            
            async salvar(formData) {
                const response = await fetch(`${this.baseUrl}salvar.php`, {
                    method: 'POST',
                    body: formData
                });
                return await response.text();
            },
            
            async deletar(id) {
                const formData = new FormData();
                formData.append('id', id);
                
                const response = await fetch(`${this.baseUrl}excluir.php`, {
                    method: 'POST',
                    body: formData
                });
                return await response.text();
            },
            
            async deletarMultiplos(ids) {
                const formData = new FormData();
                formData.append('ids', ids.join(','));
                
                const response = await fetch(`${this.baseUrl}excluir.php`, {
                    method: 'POST',
                    body: formData
                });
                return await response.text();
            },
            
            async mudarStatus(id, status) {
                const formData = new FormData();
                formData.append('id', id);
                formData.append('ativo', status ? 1 : 0);
                
                const response = await fetch(`${this.baseUrl}status.php`, {
                    method: 'POST',
                    body: formData
                });
                return await response.text();
            },
            
            async registrarEntrada(dados) {
                const formData = new FormData();
                Object.entries(dados).forEach(([key, value]) => {
                    formData.append(key, value);
                });
                
                const response = await fetch(`${this.baseUrl}entrada.php`, {
                    method: 'POST',
                    body: formData
                });
                return await response.text();
            },
            
            async registrarSaida(dados) {
                const formData = new FormData();
                Object.entries(dados).forEach(([key, value]) => {
                    formData.append(key, value);
                });
                
                const response = await fetch(`${this.baseUrl}saida.php`, {
                    method: 'POST',
                    body: formData
                });
                return await response.text();
            },
            
            async buscarPorId(id) {
                const formData = new FormData();
                formData.append('id', id);
                formData.append('action', 'buscar');
                
                const response = await fetch(`${this.baseUrl}listar.php`, {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                return data.produtos?.[0] || null;
            }
        };
        
        const ProdutoUI = {
            paginaAtual: 1,
            idsSelecionados: new Set(),
            
            async buscar() {
                const busca = $('#busca-nome').val();
                const categoria = $('#filtro-categoria').val();
                
                $('#loading').removeClass('d-none');
                $('#tbody-produtos').empty();
                
                try {
                    const data = await ProdutoAPI.listar(this.paginaAtual, busca, categoria);
                    this.renderizarProdutos(data);
                } catch (error) {
                    console.error('Erro ao buscar produtos:', error);
                    this.mostrarErro('Erro ao carregar produtos');
                } finally {
                    $('#loading').addClass('d-none');
                }
            },
            
            renderizarProdutos(data) {
                const tbody = $('#tbody-produtos');
                tbody.empty();
                
                if (!data.produtos || data.produtos.length === 0) {
                    tbody.html(`
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="bi bi-box-seam display-6 d-block mb-2"></i>
                                Nenhum produto encontrado
                            </td>
                        </tr>
                    `);
                    return;
                }
                
                data.produtos.forEach(produto => {
                    const estoqueClass = produto.estoque <= produto.nivel_estoque ? 'bg-warning' : 'bg-success';
                    const statusClass = produto.ativo ? 'bg-success' : 'bg-danger';
                    
                    const row = `
                        <tr>
                            <td>
                                <input type="checkbox" class="form-check-input produto-checkbox" 
                                       value="${produto.id}" 
                                       onchange="ProdutoUI.selecionarProduto(${produto.id}, this.checked)">
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="images/produtos/${produto.foto || 'sem-foto.jpg'}" 
                                         class="img-thumbnail me-3" 
                                         style="width: 60px; height: 60px;">
                                    <div>
                                        <div class="fw-medium">${this.escapeHtml(produto.nome)}</div>
                                        ${produto.promocao ? '<span class="badge bg-danger">PROMOÇÃO</span>' : ''}
                                        <div class="text-muted small">${produto.codigo_referencia || ''}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">${this.escapeHtml(produto.categoria_nome || '')}</span>
                            </td>
                            <td class="fw-medium">
                                R$ ${parseFloat(produto.valor_compra).toFixed(2)}
                            </td>
                            <td class="fw-bold text-primary">
                                R$ ${parseFloat(produto.valor_venda).toFixed(2)}
                            </td>
                            <td>
                                <span class="badge ${estoqueClass} badge-estoque">
                                    ${produto.estoque}
                                </span>
                            </td>
                            <td>
                                <span class="badge ${statusClass}">
                                    ${produto.ativo ? 'Ativo' : 'Inativo'}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary btn-action" 
                                            onclick="ProdutoUI.editar(${produto.id})"
                                            title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-outline-info btn-action" 
                                            onclick="ProdutoUI.verDados(${produto.id})"
                                            title="Visualizar">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button class="btn btn-outline-success btn-action" 
                                            onclick="ProdutoUI.abrirEntrada(${produto.id}, '${this.escapeHtml(produto.nome)}', ${produto.estoque})"
                                            title="Entrada Estoque">
                                        <i class="bi bi-box-arrow-in-down"></i>
                                    </button>
                                    <button class="btn btn-outline-warning btn-action" 
                                            onclick="ProdutoUI.abrirSaida(${produto.id}, '${this.escapeHtml(produto.nome)}', ${produto.estoque})"
                                            title="Saída Estoque">
                                        <i class="bi bi-box-arrow-up"></i>
                                    </button>
                                    <button class="btn btn-outline-danger btn-action" 
                                            onclick="ProdutoUI.confirmarExclusao(${produto.id}, '${this.escapeHtml(produto.nome)}')"
                                            title="Excluir">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                    tbody.append(row);
                });
                
                this.atualizarPaginacao(data);
            },
            
            escapeHtml(text) {
                const map = {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                };
                return text ? text.replace(/[&<>"']/g, m => map[m]) : '';
            },
            
            atualizarPaginacao(data) {
                const paginacao = $('#paginacao');
                if (data.totalPaginas <= 1) {
                    paginacao.empty();
                    return;
                }
                
                let paginasHTML = '<nav><ul class="pagination pagination-sm mb-0">';
                
                // Botão anterior
                if (this.paginaAtual > 1) {
                    paginasHTML += `
                        <li class="page-item">
                            <a class="page-link" href="#" onclick="ProdutoUI.irParaPagina(${this.paginaAtual - 1})">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        </li>
                    `;
                }
                
                // Números de página
                for (let i = 1; i <= data.totalPaginas; i++) {
                    if (i === this.paginaAtual) {
                        paginasHTML += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
                    } else {
                        paginasHTML += `
                            <li class="page-item">
                                <a class="page-link" href="#" onclick="ProdutoUI.irParaPagina(${i})">${i}</a>
                            </li>
                        `;
                    }
                }
                
                // Botão próximo
                if (this.paginaAtual < data.totalPaginas) {
                    paginasHTML += `
                        <li class="page-item">
                            <a class="page-link" href="#" onclick="ProdutoUI.irParaPagina(${this.paginaAtual + 1})">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    `;
                }
                
                paginasHTML += '</ul></nav>';
                paginacao.html(paginasHTML);
            },
            
            irParaPagina(pagina) {
                this.paginaAtual = pagina;
                this.buscar();
                window.scrollTo(0, 0);
            },
            
            selecionarProduto(id, checked) {
                if (checked) {
                    this.idsSelecionados.add(id);
                } else {
                    this.idsSelecionados.delete(id);
                }
                this.atualizarBtnDeletar();
            },
            
            selecionarTodos(checked) {
                this.idsSelecionados.clear();
                
                if (checked) {
                    $('.produto-checkbox:checked').each((_, checkbox) => {
                        this.idsSelecionados.add(parseInt(checkbox.value));
                    });
                } else {
                    $('.produto-checkbox').prop('checked', false);
                }
                
                this.atualizarBtnDeletar();
            },
            
            atualizarBtnDeletar() {
                const btn = $('#btn-deletar-multiplos');
                if (this.idsSelecionados.size > 0) {
                    btn.removeClass('d-none').text(`Excluir (${this.idsSelecionados.size})`);
                } else {
                    btn.addClass('d-none');
                }
            },
            
            async deletarSelecionados() {
                if (this.idsSelecionados.size === 0) {
                    this.mostrarAlerta('Selecione pelo menos um produto');
                    return;
                }
                
                const result = await Swal.fire({
                    title: 'Confirmar Exclusão',
                    text: `Deseja excluir ${this.idsSelecionados.size} produto(s) selecionado(s)?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sim, excluir!',
                    cancelButtonText: 'Cancelar'
                });
                
                if (result.isConfirmed) {
                    try {
                        const response = await ProdutoAPI.deletarMultiplos([...this.idsSelecionados]);
                        
                        if (response === 'Excluído com Sucesso') {
                            this.mostrarSucesso('Produtos excluídos com sucesso!');
                            this.idsSelecionados.clear();
                            this.buscar();
                        } else {
                            this.mostrarErro(response);
                        }
                    } catch (error) {
                        this.mostrarErro('Erro ao excluir produtos');
                    }
                }
            },
            
            async editar(id) {
                try {
                    const produto = await ProdutoAPI.buscarPorId(id);
                    
                    if (!produto) {
                        this.mostrarErro('Produto não encontrado');
                        return;
                    }
                    
                    // Preencher formulário
                    $('#id').val(produto.id);
                    $('#nome').val(produto.nome);
                    $('#categoria').val(produto.categoria).trigger('change');
                    $('#fornecedor').val(produto.fornecedor || '').trigger('change');
                    $('#codigo_referencia').val(produto.codigo_referencia || '');
                    $('#descricao').val(produto.descricao || '');
                    $('#valor_compra').val(parseFloat(produto.valor_compra).toLocaleString('pt-BR', { minimumFractionDigits: 2 }));
                    $('#ipi_percentual').val(produto.ipi_percentual || '0.00');
                    $('#custo_nfe_percentual').val(produto.custo_nfe_percentual || '0.00');
                    $('#margem_lucro_percentual').val(produto.margem_lucro_percentual || '30.00');
                    $('#valor_promocional').val(produto.valor_promocional ? parseFloat(produto.valor_promocional).toLocaleString('pt-BR', { minimumFractionDigits: 2 }) : '');
                    $('#promocao').val(produto.promocao || '0');
                    $('#ativo').val(produto.ativo || '1');
                    $('#estoque').val(produto.estoque || '0');
                    $('#nivel_estoque').val(produto.nivel_estoque || '0');
                    $('#tem_estoque').val(produto.tem_estoque || '1');
                    $('#preparado').val(produto.preparado || '0');
                    $('#delivery').val(produto.delivery || '1');
                    
                    // Preencher imagem
                    $('#target').attr('src', `images/produtos/${produto.foto || 'sem-foto.jpg'}`);
                    
                    // Calcular preços
                    setTimeout(() => {
                        calcularPrecos();
                    }, 300);
                    
                    // Mostrar modal
                    $('#titulo_inserir').text(`Editar: ${produto.nome}`);
                    const modal = new bootstrap.Modal(document.getElementById('modalForm'));
                    modal.show();
                    
                } catch (error) {
                    console.error('Erro ao editar produto:', error);
                    this.mostrarErro('Erro ao carregar produto: ' + error.message);
                }
            },
            
            inserir() {
                $('#form_produtos')[0].reset();
                $('#id').val('');
                $('#target').attr('src', 'images/produtos/sem-foto.jpg');
                $('#titulo_inserir').text('Novo Produto');
                
                // Resetar valores padrão
                $('#categoria').val('').trigger('change');
                $('#fornecedor').val('').trigger('change');
                $('#margem_lucro_percentual').val('30.00');
                $('#ipi_percentual').val('0.00');
                $('#custo_nfe_percentual').val('0.00');
                $('#promocao').val('0');
                $('#ativo').val('1');
                $('#estoque').val('0');
                $('#nivel_estoque').val('0');
                $('#tem_estoque').val('1');
                $('#preparado').val('0');
                $('#delivery').val('1');
                $('#custo_unitario').val('');
                $('#valor_venda_calculado').val('');
                
                const modal = new bootstrap.Modal(document.getElementById('modalForm'));
                modal.show();
                
                // Calcular preços após mostrar modal
                setTimeout(() => {
                    calcularPrecos();
                }, 300);
            },
            
            async verDados(id) {
                try {
                    const produto = await ProdutoAPI.buscarPorId(id);
                    
                    if (!produto) {
                        this.mostrarErro('Produto não encontrado');
                        return;
                    }
                    
                    // Preencher dados
                    $('#nome_detalhes').text(produto.nome);
                    $('#categoria_detalhes').text(produto.categoria_nome || '---');
                    $('#fornecedor_detalhes').text(produto.fornecedor_nome || '---');
                    $('#codigo_detalhes').text(produto.codigo_referencia || '---');
                    $('#descricao_detalhes').html(produto.descricao ? produto.descricao.replace(/\n/g, '<br>') : '<em class="text-muted">Nenhuma descrição informada</em>');
                    
                    // Status
                    $('#status_detalhes').text(produto.ativo ? 'Ativo' : 'Inativo')
                        .removeClass().addClass('badge ' + (produto.ativo ? 'bg-success' : 'bg-danger'));
                    
                    // Estoque
                    $('#estoque_detalhes').text(produto.estoque)
                        .removeClass().addClass('badge ' + (produto.estoque > 0 ? 'bg-success' : 'bg-danger'));
                    $('#alerta_detalhes').text(produto.nivel_estoque || '0');
                    $('#tem_estoque_detalhes').text(produto.tem_estoque ? 'Sim' : 'Não');
                    
                    // Preparado e Promoção
                    $('#preparado_detalhes').text(produto.preparado ? 'Sim' : 'Não');
                    $('#promocao_detalhes').text(produto.promocao ? 'Sim' : 'Não');
                    
                    // Valores monetários
                    $('#compra_detalhes').text('R$ ' + parseFloat(produto.valor_compra).toFixed(2));
                    $('#venda_detalhes').text('R$ ' + parseFloat(produto.valor_venda).toFixed(2));
                    $('#promocao_valor_detalhes').text(produto.valor_promocional ? 'R$ ' + parseFloat(produto.valor_promocional).toFixed(2) : '---');
                    
                    // Calcular custo unitário
                    const vc = parseFloat(produto.valor_compra);
                    const ipi = parseFloat(produto.ipi_percentual || 0);
                    const custoNFe = parseFloat(produto.custo_nfe_percentual || 0);
                    const custoUnitario = vc + (vc * (ipi / 100)) + (vc * (custoNFe / 100));
                    $('#custo_detalhes').text('R$ ' + custoUnitario.toFixed(2));
                    
                    // Percentuais
                    $('#ipi_detalhes').text((produto.ipi_percentual || '0.00') + '%');
                    $('#custo_nfe_detalhes').text((produto.custo_nfe_percentual || '0.00') + '%');
                    $('#margem_detalhes').text((produto.margem_lucro_percentual || '30.00') + '%');
                    
                    // Foto
                    $('#foto_detalhes').attr('src', 'images/produtos/' + (produto.foto || 'sem-foto.jpg'));
                    
                    // Armazenar ID para edição
                    $('#modalDados').data('produto-id', id);
                    
                    $('#modalDados').modal('show');
                } catch (error) {
                    console.error('Erro ao ver dados:', error);
                    this.mostrarErro('Erro ao carregar detalhes do produto');
                }
            },
            
            editarProdutoAtual() {
                const id = $('#modalDados').data('produto-id');
                if (id) {
                    $('#modalDados').modal('hide');
                    setTimeout(() => {
                        this.editar(id);
                    }, 300);
                }
            },
            
            abrirEntrada(id, nome, estoque) {
                $('#id_entrada').val(id);
                $('#produto_nome_entrada').val(nome);
                $('#estoque_entrada').val(estoque);
                $('#estoque_atual_entrada').val(estoque);
                $('#quantidade_entrada').val('');
                $('#motivo_entrada').val('');
                $('#mensagem_entrada').addClass('d-none');
                
                const modal = new bootstrap.Modal(document.getElementById('modalEntrada'));
                modal.show();
                $('#quantidade_entrada').focus();
            },
            
            abrirSaida(id, nome, estoque) {
                $('#id_saida').val(id);
                $('#produto_nome_saida').val(nome);
                $('#estoque_saida').val(estoque);
                $('#estoque_atual_saida').val(estoque);
                $('#quantidade_saida').val('');
                $('#motivo_saida').val('');
                $('#mensagem_saida').addClass('d-none');
                
                const modal = new bootstrap.Modal(document.getElementById('modalSaida'));
                modal.show();
                $('#quantidade_saida').focus();
            },
            
            async confirmarExclusao(id, nome) {
                const result = await Swal.fire({
                    title: 'Excluir Produto',
                    text: `Deseja excluir o produto "${nome}"?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sim, excluir!',
                    cancelButtonText: 'Cancelar'
                });
                
                if (result.isConfirmed) {
                    try {
                        const response = await ProdutoAPI.deletar(id);
                        
                        if (response === 'Excluído com Sucesso') {
                            this.mostrarSucesso('Produto excluído com sucesso!');
                            this.buscar();
                        } else {
                            this.mostrarErro(response);
                        }
                    } catch (error) {
                        this.mostrarErro('Erro ao excluir produto');
                    }
                }
            },
            
            mostrarSucesso(mensagem) {
                Swal.fire({
                    icon: 'success',
                    title: 'Sucesso',
                    text: mensagem,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
            },
            
            mostrarErro(mensagem) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: mensagem,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 4000
                });
            },
            
            mostrarAlerta(mensagem) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Atenção',
                    text: mensagem,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
            }
        };
        
        // Inicializar
        $(document).ready(function() {
            ProdutoUI.buscar();
            
            // Configurar Select2
            $('.select2').select2({
                theme: 'bootstrap-5',
                width: '100%'
            });
            
            // Configurar cálculo em tempo real
            $('#valor_compra, #ipi_percentual, #custo_nfe_percentual, #margem_lucro_percentual').on('input', function() {
                calcularPrecos();
            });
            
            // Configurar formatação monetária
            $('#valor_compra, #valor_promocional').on('blur', function() {
                formatarMoeda($(this));
            });
            
            // Configurar formulário de produtos
            $('#form_produtos').submit(async function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const botao = $(this).find('button[type="submit"]');
                
                botao.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Salvando...');
                
                try {
                    const response = await ProdutoAPI.salvar(formData);
                    
                    if (response === 'Salvo com Sucesso') {
                        ProdutoUI.mostrarSucesso('Produto salvo com sucesso!');
                        
                        setTimeout(() => {
                            $('#modalForm').modal('hide');
                            ProdutoUI.buscar();
                            botao.prop('disabled', false).html('<i class="bi bi-check-lg"></i> Salvar Produto');
                        }, 1500);
                    } else {
                        ProdutoUI.mostrarErro(response);
                        botao.prop('disabled', false).html('<i class="bi bi-check-lg"></i> Salvar Produto');
                    }
                } catch (error) {
                    ProdutoUI.mostrarErro('Erro ao salvar produto: ' + error);
                    botao.prop('disabled', false).html('<i class="bi bi-check-lg"></i> Salvar Produto');
                }
            });
            
            // Configurar formulário de entrada
            $('#form_entrada').submit(async function(e) {
                e.preventDefault();
                
                const dados = {
                    id: $('#id_entrada').val(),
                    quantidade_entrada: $('#quantidade_entrada').val(),
                    motivo_entrada: $('#motivo_entrada').val()
                };
                
                const botao = $(this).find('button[type="submit"]');
                botao.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Processando...');
                
                try {
                    const response = await ProdutoAPI.registrarEntrada(dados);
                    
                    if (response === 'Salvo com Sucesso') {
                        ProdutoUI.mostrarSucesso('Entrada registrada com sucesso!');
                        
                        setTimeout(() => {
                            $('#modalEntrada').modal('hide');
                            ProdutoUI.buscar();
                            botao.prop('disabled', false).html('<i class="bi bi-box-arrow-in-down"></i> Registrar Entrada');
                        }, 1500);
                    } else {
                        ProdutoUI.mostrarErro(response);
                        botao.prop('disabled', false).html('<i class="bi bi-box-arrow-in-down"></i> Registrar Entrada');
                    }
                } catch (error) {
                    ProdutoUI.mostrarErro('Erro ao registrar entrada: ' + error);
                    botao.prop('disabled', false).html('<i class="bi bi-box-arrow-in-down"></i> Registrar Entrada');
                }
            });
            
            // Configurar formulário de saída
            $('#form_saida').submit(async function(e) {
                e.preventDefault();
                
                const dados = {
                    id: $('#id_saida').val(),
                    quantidade_saida: $('#quantidade_saida').val(),
                    motivo_saida: $('#motivo_saida').val()
                };
                
                const botao = $(this).find('button[type="submit"]');
                botao.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Processando...');
                
                try {
                    const response = await ProdutoAPI.registrarSaida(dados);
                    
                    if (response === 'Salvo com Sucesso') {
                        ProdutoUI.mostrarSucesso('Saída registrada com sucesso!');
                        
                        setTimeout(() => {
                            $('#modalSaida').modal('hide');
                            ProdutoUI.buscar();
                            botao.prop('disabled', false).html('<i class="bi bi-box-arrow-up"></i> Registrar Saída');
                        }, 1500);
                    } else {
                        ProdutoUI.mostrarErro(response);
                        botao.prop('disabled', false).html('<i class="bi bi-box-arrow-up"></i> Registrar Saída');
                    }
                } catch (error) {
                    ProdutoUI.mostrarErro('Erro ao registrar saída: ' + error);
                    botao.prop('disabled', false).html('<i class="bi bi-box-arrow-up"></i> Registrar Saída');
                }
            });
            
            // Inicializar cálculo
            calcularPrecos();
        });
        
        function formatarMoeda(input) {
            let valor = input.val().replace(/\D/g, '');
            valor = (valor / 100).toFixed(2) + '';
            valor = valor.replace(".", ",");
            valor = valor.replace(/(\d)(\d{3})(\d{3}),/g, "$1.$2.$3,");
            valor = valor.replace(/(\d)(\d{3}),/g, "$1.$2,");
            input.val(valor);
        }
        
        function calcularPrecos() {
            const vc = parseFloat($('#valor_compra').val().replace(/\./g, '').replace(',', '.')) || 0;
            const ipi = parseFloat($('#ipi_percentual').val()) || 0;
            const custo = parseFloat($('#custo_nfe_percentual').val()) || 0;
            const margem = parseFloat($('#margem_lucro_percentual').val()) || 0;
            
            const custoIpi = vc * (ipi / 100);
            const custoNFe = vc * (custo / 100);
            const custoUnitario = vc + custoIpi + custoNFe;
            const valorVenda = custoUnitario * (1 + margem / 100);
            
            $('#custo_unitario').val(custoUnitario.toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
            
            $('#valor_venda_calculado').val(valorVenda.toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
        }
        
        function carregarImagem() {
            const input = document.getElementById('foto');
            const target = document.getElementById('target');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    target.src = e.target.result;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
</script>
</body>
</html>