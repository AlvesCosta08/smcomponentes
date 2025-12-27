<div class="modal fade" id="modalSaida" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="nome_saida">Saída de Estoque</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="form_saida">
                <div class="modal-body">
                    <input type="hidden" id="id_saida" name="id">
                    <input type="hidden" id="estoque_saida" name="estoque">
                    
                    <div class="mb-3">
                        <label class="form-label">Produto</label>
                        <input type="text" class="form-control" id="produto_nome_saida" readonly>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Quantidade *</label>
                                <input type="number" class="form-control" id="quantidade_saida" name="quantidade_saida" min="1" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Estoque Atual</label>
                                <input type="text" class="form-control" id="estoque_atual_saida" readonly>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Motivo *</label>
                        <input type="text" class="form-control" id="motivo_saida" name="motivo_saida" required>
                        <div class="form-text">Ex: Venda, Perda, Ajuste</div>
                    </div>
                    
                    <div id="mensagem_saida" class="alert d-none"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-box-arrow-up"></i> Registrar Saída
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>