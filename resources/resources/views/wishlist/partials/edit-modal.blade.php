<!-- Modal Editar Lista -->
<div class="modal fade" id="editWishlistModal{{ $wishlist->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('wishlist.update', $wishlist->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i> Editar Lista
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nome{{ $wishlist->id }}" class="form-label">Nome da Lista <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" 
                               id="nome{{ $wishlist->id }}" name="nome" 
                               value="{{ old('nome', $wishlist->nome) }}" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="descricao{{ $wishlist->id }}" class="form-label">Descrição</label>
                        <textarea class="form-control" 
                                  id="descricao{{ $wishlist->id }}" name="descricao" rows="2">{{ old('descricao', $wishlist->descricao) }}</textarea>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="is_public{{ $wishlist->id }}" 
                               name="is_public" value="1" {{ $wishlist->is_public ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_public{{ $wishlist->id }}">
                            <i class="fas fa-globe me-1"></i> Lista Pública
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>