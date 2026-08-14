{{-- resources/views/wishlist/partials/edit-modal.blade.php --}}
<!-- Modal Editar Wishlist -->
<div class="modal fade" id="editWishlistModal{{ $wishlist->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">✏️ Editar Lista</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('wishlist.update', $wishlist->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="nome{{ $wishlist->id }}" class="form-label">Nome da Lista <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nome{{ $wishlist->id }}" 
                               name="nome" value="{{ old('nome', $wishlist->nome) }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="descricao{{ $wishlist->id }}" class="form-label">Descrição</label>
                        <textarea class="form-control" id="descricao{{ $wishlist->id }}" 
                                  name="descricao" rows="2">{{ old('descricao', $wishlist->descricao) }}</textarea>
                    </div>

                    <div class="form-check mb-2">
                        <input type="checkbox" class="form-check-input" id="is_public{{ $wishlist->id }}" 
                               name="is_public" value="1" @checked(old('is_public', $wishlist->is_public))>
                        <label class="form-check-label" for="is_public{{ $wishlist->id }}">
                            <i class="fas fa-globe me-1"></i> Tornar pública
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>