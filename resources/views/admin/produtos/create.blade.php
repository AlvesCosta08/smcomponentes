@extends('layouts.app')  {{-- ALTERADO --}}

@section('title', 'Novo Produto - Admin')

@section('content')
<div class="container-fluid px-4">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0"><i class="bi bi-plus-circle"></i> Novo Produto</h1>
            <small class="text-muted">Adicione um novo produto ao catálogo</small>
        </div>
        <div>
            <a href="{{ route('admin.produtos.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    <div class="admin-card">
        <div class="card-body">
            <form action="{{ route('admin.produtos.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row">
                    <!-- Descrição -->
                    <div class="col-md-8 mb-3">
                        <label for="descricao" class="form-label">Descrição *</label>
                        <input type="text" name="descricao" id="descricao" class="form-control @error('descricao') is-invalid @enderror" value="{{ old('descricao') }}" required>
                        @error('descricao')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Referência -->
                    <div class="col-md-4 mb-3">
                        <label for="referencia" class="form-label">Referência</label>
                        <input type="text" name="referencia" id="referencia" class="form-control @error('referencia') is-invalid @enderror" value="{{ old('referencia') }}">
                        @error('referencia')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Categoria -->
                    <div class="col-md-6 mb-3">
                        <label for="categoria" class="form-label">Categoria *</label>
                        <input type="text" name="categoria" id="categoria" class="form-control @error('categoria') is-invalid @enderror" value="{{ old('categoria') }}" required>
                        @error('categoria')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Quantidade -->
                    <div class="col-md-3 mb-3">
                        <label for="quantidade" class="form-label">Quantidade *</label>
                        <input type="number" name="quantidade" id="quantidade" class="form-control @error('quantidade') is-invalid @enderror" value="{{ old('quantidade', 0) }}" min="0" required>
                        @error('quantidade')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Disponibilidade -->
                    <div class="col-md-3 mb-3">
                        <label for="disponibilidade" class="form-label">Disponibilidade *</label>
                        <select name="disponibilidade" id="disponibilidade" class="form-select @error('disponibilidade') is-invalid @enderror" required>
                            <option value="DISPONÍVEL" {{ old('disponibilidade') == 'DISPONÍVEL' ? 'selected' : '' }}>Disponível</option>
                            <option value="INDISPONÍVEL" {{ old('disponibilidade') == 'INDISPONÍVEL' ? 'selected' : '' }}>Indisponível</option>
                        </select>
                        @error('disponibilidade')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Preço Unitário -->
                    <div class="col-md-4 mb-3">
                        <label for="valor_unitario" class="form-label">Preço Unitário *</label>
                        <div class="input-group">
                            <span class="input-group-text">R$</span>
                            <input type="number" step="0.01" name="valor_unitario" id="valor_unitario" class="form-control @error('valor_unitario') is-invalid @enderror" value="{{ old('valor_unitario') }}" required>
                        </div>
                        @error('valor_unitario')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Preço Promocional -->
                    <div class="col-md-4 mb-3">
                        <label for="preco_promocional" class="form-label">Preço Promocional</label>
                        <div class="input-group">
                            <span class="input-group-text">R$</span>
                            <input type="number" step="0.01" name="preco_promocional" id="preco_promocional" class="form-control @error('preco_promocional') is-invalid @enderror" value="{{ old('preco_promocional') }}">
                        </div>
                        @error('preco_promocional')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Ativo -->
                    <div class="col-md-4 mb-3">
                        <label for="ativo" class="form-label">Status</label>
                        <select name="ativo" id="ativo" class="form-select @error('ativo') is-invalid @enderror">
                            <option value="1" {{ old('ativo', 1) == 1 ? 'selected' : '' }}>Ativo</option>
                            <option value="0" {{ old('ativo') == 0 ? 'selected' : '' }}>Inativo</option>
                        </select>
                        @error('ativo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Imagem -->
                    <div class="col-12 mb-3">
                        <label for="imagem" class="form-label">Imagem</label>
                        <input type="file" name="imagem" id="imagem" class="form-control @error('imagem') is-invalid @enderror" accept="image/*">
                        <small class="text-muted">Formatos permitidos: JPG, PNG, GIF. Máximo 2MB.</small>
                        @error('imagem')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div id="imagePreview" class="mt-2"></div>
                    </div>

                    <!-- Botões -->
                    <div class="col-12 mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Salvar Produto
                        </button>
                        <a href="{{ route('admin.produtos.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancelar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('imagem').addEventListener('change', function(e) {
        const preview = document.getElementById('imagePreview');
        preview.innerHTML = '';
        
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.style.maxWidth = '200px';
                img.style.maxHeight = '200px';
                img.style.borderRadius = '5px';
                preview.appendChild(img);
            }
            reader.readAsDataURL(this.files[0]);
        }
    });
</script>
@endpush
@endsection