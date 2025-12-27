<div class="modal fade" id="modalEntrada" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="nome_entrada">Entrada de Estoque</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="form_entrada">
                <div class="modal-body">
                    <input type="hidden" id="id_entrada" name="id">
                    <input type="hidden" id="estoque_entrada" name="estoque">
                    
                    <div class="mb-3">
                        <label class="form-label">Produto</label>
                        <input type="text" class="form-control" id="produto_nome_entrada" readonly>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Quantidade *</label>
                                <input type="number" class="form-control" id="quantidade_entrada" name="quantidade_entrada" min="1" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Estoque Atual</label>
                                <input type="text" class="form-control" id="estoque_atual_entrada" readonly>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Motivo *</label>
                        <input type="text" class="form-control" id="motivo_entrada" name="motivo_entrada" required>
                        <div class="form-text">Ex: Compra, Ajuste, Devolução</div>
                    </div>
                    
                    <div id="mensagem_entrada" class="alert d-none"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-box-arrow-in-down"></i> Registrar Entrada
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>