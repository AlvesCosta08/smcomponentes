<div class="modal fade" id="modalForm" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="titulo_inserir">Novo Produto</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="form_produtos" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" id="id" name="id">
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label">Nome do Produto *</label>
                                <input type="text" class="form-control" id="nome" name="nome" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Categoria *</label>
                                <select class="form-select select2" id="categoria" name="categoria" required>
                                    <option value="">Selecione</option>
                                    <?php echo $controller->listarCategoriasOptions(); ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Código de Referência</label>
                                <input type="text" class="form-control" id="codigo_referencia" name="codigo_referencia">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Fornecedor</label>
                                <select class="form-select select2" id="fornecedor" name="fornecedor">
                                    <?php echo $controller->listarFornecedoresOptions(); ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Descrição</label>
                        <textarea class="form-control" id="descricao" name="descricao" rows="2"></textarea>
                    </div>
                    
                    <h6 class="border-bottom pb-2 mb-3">Precificação</h6>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Valor Compra (R$) *</label>
                                <input type="text" class="form-control" id="valor_compra" name="valor_compra" required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="mb-3">
                                <label class="form-label">IPI (%)</label>
                                <input type="number" step="0.01" class="form-control" id="ipi_percentual" name="ipi_percentual" value="0.00">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="mb-3">
                                <label class="form-label">Custo NFe (%)</label>
                                <input type="number" step="0.01" class="form-control" id="custo_nfe_percentual" name="custo_nfe_percentual" value="0.00">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="mb-3">
                                <label class="form-label">Margem (%) *</label>
                                <input type="number" step="0.01" class="form-control" id="margem_lucro_percentual" name="margem_lucro_percentual" value="30.00" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Custo Unitário (R$)</label>
                                <input type="text" class="form-control" id="custo_unitario" readonly>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Valor Venda (R$)</label>
                                <input type="text" class="form-control" id="valor_venda_calculado" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Valor Promoção (R$)</label>
                                <input type="text" class="form-control" id="valor_promocional" name="valor_promocional">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="bi bi-tag"></i> Em Promoção?
                                </label>
                                <select class="form-select" id="promocao" name="promocao">
                                    <option value="0">Não</option>
                                    <option value="1">Sim</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" id="ativo" name="ativo">
                                    <option value="1">Ativo</option>
                                    <option value="0">Inativo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <h6 class="border-bottom pb-2 mb-3">Estoque</h6>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Estoque Atual</label>
                                <input type="number" class="form-control" id="estoque" name="estoque" value="0">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Alerta Estoque</label>
                                <input type="number" class="form-control" id="nivel_estoque" name="nivel_estoque" value="0">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Tem Estoque?</label>
                                <select class="form-select" id="tem_estoque" name="tem_estoque">
                                    <option value="1">Sim</option>
                                    <option value="0">Não</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Preparado?</label>
                                <select class="form-select" id="preparado" name="preparado">
                                    <option value="0">Não</option>
                                    <option value="1">Sim</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <h6 class="border-bottom pb-2 mb-3">Imagem</h6>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label">Foto do Produto</label>
                                <input type="file" class="form-control" id="foto" name="foto" accept="image/*" onchange="carregarImagem()">
                                <div class="form-text">Formatos: JPG, PNG, GIF. Máx: 2MB</div>
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <img src="images/produtos/sem-foto.jpg" id="target" class="img-thumbnail" style="width: 120px; height: 120px;">
                        </div>
                    </div>
                    
                    <div class="alert alert-info mt-3 mb-0">
                        <i class="bi bi-info-circle"></i> Campos marcados com * são obrigatórios
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Salvar Produto
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>