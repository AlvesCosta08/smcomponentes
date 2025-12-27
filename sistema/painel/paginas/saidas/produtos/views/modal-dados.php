<div class="modal fade" id="modalDados" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Detalhes do Produto</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-3 text-center mb-4">
                        <img src="images/produtos/sem-foto.jpg" id="foto_detalhes" class="img-thumbnail" style="width: 180px; height: 180px;">
                    </div>
                    <div class="col-md-9">
                        <h4 id="nome_detalhes" class="mb-3"></h4>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-sm">
                                    <tr>
                                        <th width="40%">Categoria:</th>
                                        <td id="categoria_detalhes"></td>
                                    </tr>
                                    <tr>
                                        <th>Fornecedor:</th>
                                        <td id="fornecedor_detalhes"></td>
                                    </tr>
                                    <tr>
                                        <th>Código Ref:</th>
                                        <td id="codigo_detalhes"></td>
                                    </tr>
                                    <tr>
                                        <th>Status:</th>
                                        <td><span id="status_detalhes" class="badge"></span></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-sm">
                                    <tr>
                                        <th width="40%">Estoque:</th>
                                        <td><span id="estoque_detalhes" class="badge"></span></td>
                                    </tr>
                                    <tr>
                                        <th>Alerta:</th>
                                        <td id="alerta_detalhes"></td>
                                    </tr>
                                    <tr>
                                        <th>Preparado:</th>
                                        <td id="preparado_detalhes"></td>
                                    </tr>
                                    <tr>
                                        <th>Promoção:</th>
                                        <td id="promocao_detalhes"></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <h6 class="border-top pt-3 mt-3">Precificação</h6>
                <div class="row">
                    <div class="col-md-3">
                        <div class="card border-0 bg-light">
                            <div class="card-body text-center">
                                <div class="text-muted small">Compra</div>
                                <div class="h5" id="compra_detalhes"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 bg-light">
                            <div class="card-body text-center">
                                <div class="text-muted small">Custo Unitário</div>
                                <div class="h5" id="custo_detalhes"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 bg-light">
                            <div class="card-body text-center">
                                <div class="text-muted small">Venda</div>
                                <div class="h5" id="venda_detalhes"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 bg-light">
                            <div class="card-body text-center">
                                <div class="text-muted small">Promoção</div>
                                <div class="h5" id="promocao_valor_detalhes"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <h6 class="border-top pt-3 mt-3">Percentuais</h6>
                <div class="row">
                    <div class="col-md-3">
                        <div class="text-center">
                            <div class="text-muted small">IPI</div>
                            <div class="h6" id="ipi_detalhes"></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <div class="text-muted small">Custo NFe</div>
                            <div class="h6" id="custo_nfe_detalhes"></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <div class="text-muted small">Margem</div>
                            <div class="h6" id="margem_detalhes"></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <div class="text-muted small">Tem Estoque</div>
                            <div class="h6" id="tem_estoque_detalhes"></div>
                        </div>
                    </div>
                </div>
                
                <h6 class="border-top pt-3 mt-3">Descrição</h6>
                <div class="bg-light p-3 rounded" id="descricao_detalhes">
                    <em class="text-muted">Nenhuma descrição informada</em>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary" onclick="ProdutoUI.editarProdutoAtual()">
                    <i class="bi bi-pencil"></i> Editar
                </button>
            </div>
        </div>
    </div>
</div>