{{-- resources/views/wishlist/partials/create-modal.blade.php --}}
<!-- Modal Criar Wishlist -->
<div class="modal fade" id="createWishlistModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">📝 Criar Nova Lista de Desejos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('wishlist.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome da Lista <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nome') is-invalid @enderror" 
                               id="nome" name="nome" value="{{ old('nome') }}" required>
                        @error('nome')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="descricao" class="form-label">Descrição</label>
                        <textarea class="form-control @error('descricao') is-invalid @enderror" 
                                  id="descricao" name="descricao" rows="2">{{ old('descricao') }}</textarea>
                        @error('descricao')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-check mb-2">
                        <input type="checkbox" class="form-check-input" id="is_public" name="is_public" value="1">
                        <label class="form-check-label" for="is_public">
                            <i class="fas fa-globe me-1"></i> Tornar pública
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Criar Lista
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>