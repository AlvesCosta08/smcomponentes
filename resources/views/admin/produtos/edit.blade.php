@extends('layouts.app')

@section('title', 'Editar Produto - Admin')

@section('content')
<div class="container-fluid px-4">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0"><i class="bi bi-pencil"></i> Editar Produto</h1>
            <small class="text-muted">#{{ $produto->id }} - {{ $produto->descricao }}</small>
        </div>
        <div>
            <a href="{{ route('admin.produtos.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Voltar
            </a>
        </div>
    </div>

    <div class="admin-card">
        <div class="card-body">
            <form action="{{ route('admin.produtos.update', $produto->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row">
                    <!-- Descrição -->
                    <div class="col-md-8 mb-3">
                        <label for="descricao" class="form-label">Descrição *</label>
                        <input type="text" name="descricao" id="descricao" 
                            class="form-control @error('descricao') is-invalid @enderror" 
                            value="{{ old('descricao', $produto->descricao) }}" required>
                        @error('descricao')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Referência -->
                    <div class="col-md-4 mb-3">
                        <label for="referencia" class="form-label">Referência</label>
                        <input type="text" name="referencia" id="referencia" 
                            class="form-control @error('referencia') is-invalid @enderror" 
                            value="{{ old('referencia', $produto->referencia) }}">
                        @error('referencia')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Tipo -->
                    <div class="col-md-4 mb-3">
                        <label for="tipo" class="form-label">Tipo</label>
                        <select name="tipo" id="tipo" class="form-select">
                            <option value="">Selecione</option>
                            <option value="UNI" {{ old('tipo', $produto->tipo) == 'UNI' ? 'selected' : '' }}>UNI (Unidade)</option>
                            <option value="PÇ" {{ old('tipo', $produto->tipo) == 'PÇ' ? 'selected' : '' }}>PÇ (Peça)</option>
                            <option value="CX" {{ old('tipo', $produto->tipo) == 'CX' ? 'selected' : '' }}>CX (Caixa)</option>
                            <option value="PCO" {{ old('tipo', $produto->tipo) == 'PCO' ? 'selected' : '' }}>PCO (Pacote)</option>
                            <option value="KIT" {{ old('tipo', $produto->tipo) == 'KIT' ? 'selected' : '' }}>KIT</option>
                        </select>
                    </div>

                    <!-- Categoria (relacionada) -->
                    <div class="col-md-4 mb-3">
                        <label for="categoria_id" class="form-label">Categoria</label>
                        <select name="categoria_id" id="categoria_id" class="form-select">
                            <option value="">Selecione uma categoria</option>
                            @foreach($categorias as $categoria)
                                <option value="{{ $categoria->id }}" 
                                    {{ old('categoria_id', $produto->categoria_id) == $categoria->id ? 'selected' : '' }}>
                                    {{ $categoria->nome }}
                                </option>
                            @endforeach
                        </select>
                        @error('categoria_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Fornecedor -->
                    <div class="col-md-4 mb-3">
                        <label for="fornecedor" class="form-label">Fornecedor</label>
                        <input type="text" name="fornecedor" id="fornecedor" 
                            class="form-control @error('fornecedor') is-invalid @enderror" 
                            value="{{ old('fornecedor', $produto->fornecedor) }}" placeholder="Nome do fornecedor">
                        @error('fornecedor')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Estoque -->
                    <div class="col-md-3 mb-3">
                        <label for="quantidade" class="form-label">Quantidade *</label>
                        <input type="number" name="quantidade" id="quantidade" 
                            class="form-control @error('quantidade') is-invalid @enderror" 
                            value="{{ old('quantidade', $produto->quantidade) }}" min="0" required>
                        @error('quantidade')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="estoque_minimo" class="form-label">Estoque Mínimo</label>
                        <input type="number" name="estoque_minimo" id="estoque_minimo" 
                            class="form-control" value="{{ old('estoque_minimo', $produto->estoque_minimo ?? 5) }}" min="0">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="data_compra" class="form-label">Data da Compra</label>
                        <input type="date" name="data_compra" id="data_compra" 
                            class="form-control" value="{{ old('data_compra', $produto->data_compra) }}">
                    </div>

                    <!-- Preços (apenas unitário e promocional) -->
                    <div class="col-12">
                        <hr>
                        <h5 class="mb-3"><i class="bi bi-tag"></i> Preços</h5>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="valor_unitario" class="form-label">Preço Unitário *</label>
                        <div class="input-group">
                            <span class="input-group-text">R$</span>
                            <input type="number" step="0.01" name="valor_unitario" id="valor_unitario" 
                                class="form-control @error('valor_unitario') is-invalid @enderror" 
                                value="{{ old('valor_unitario', $produto->valor_unitario ?? 0) }}" min="0" required>
                        </div>
                        @error('valor_unitario')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="preco_promocional" class="form-label">Preço Promocional</label>
                        <div class="input-group">
                            <span class="input-group-text">R$</span>
                            <input type="number" step="0.01" name="preco_promocional" id="preco_promocional" 
                                class="form-control @error('preco_promocional') is-invalid @enderror" 
                                value="{{ old('preco_promocional', $produto->preco_promocional) }}" min="0">
                        </div>
                        @error('preco_promocional')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div class="col-md-4 mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="disponivel" {{ old('status', $produto->status) == 'disponivel' ? 'selected' : '' }}>Disponível</option>
                            <option value="indisponivel" {{ old('status', $produto->status) == 'indisponivel' ? 'selected' : '' }}>Indisponível</option>
                            <option value="sob_encomenda" {{ old('status', $produto->status) == 'sob_encomenda' ? 'selected' : '' }}>Sob Encomenda</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Ativo -->
                    <div class="col-md-4 mb-3">
                        <label for="ativo" class="form-label">Ativo</label>
                        <select name="ativo" id="ativo" class="form-select">
                            <option value="1" {{ old('ativo', $produto->ativo) == 1 ? 'selected' : '' }}>Sim</option>
                            <option value="0" {{ old('ativo', $produto->ativo) == 0 ? 'selected' : '' }}>Não</option>
                        </select>
                        @error('ativo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Flags -->
                    <div class="col-md-4 mb-3">
                        <div class="form-check">
                            <input type="checkbox" name="destaque" id="destaque" 
                                class="form-check-input" value="1" 
                                {{ old('destaque', $produto->destaque) ? 'checked' : '' }}>
                            <label for="destaque" class="form-check-label">Destaque</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="novo" id="novo" 
                                class="form-check-input" value="1" 
                                {{ old('novo', $produto->novo) ? 'checked' : '' }}>
                            <label for="novo" class="form-check-label">Produto Novo</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="mais_vendido" id="mais_vendido" 
                                class="form-check-input" value="1" 
                                {{ old('mais_vendido', $produto->mais_vendido) ? 'checked' : '' }}>
                            <label for="mais_vendido" class="form-check-label">Mais Vendido</label>
                        </div>
                    </div>

                    <!-- Imagem Atual -->
                    @if($produto->imagem)
                    <div class="col-12 mb-3">
                        <label class="form-label">Imagem Atual</label>
                        <div>
                            <img src="{{ $produto->imagem_url }}" alt="{{ $produto->descricao }}" 
                                class="img-thumbnail" style="max-width: 150px;">
                        </div>
                    </div>
                    @endif

                    <!-- Nova Imagem -->
                    <div class="col-12 mb-3">
                        <label for="imagem" class="form-label">Nova Imagem (opcional)</label>
                        <input type="file" name="imagem" id="imagem" 
                            class="form-control @error('imagem') is-invalid @enderror" accept="image/*">
                        <small class="text-muted">Deixe em branco para manter a imagem atual. Máx: 2MB</small>
                        @error('imagem')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div id="imagePreview" class="mt-2"></div>
                    </div>

                    <!-- Imagens Adicionais -->
                    <div class="col-12 mb-3">
                        <label for="imagens" class="form-label">Imagens Adicionais (Galeria)</label>
                        <input type="file" name="imagens[]" id="imagens" 
                            class="form-control @error('imagens.*') is-invalid @enderror" 
                            accept="image/*" multiple>
                        <small class="text-muted">Selecione várias imagens (Ctrl+Clique). Máx 2MB cada.</small>
                        @error('imagens.*')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Botões -->
                    <div class="col-12 mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Atualizar Produto
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
    document.addEventListener('DOMContentLoaded', function() {
        // Preview da nova imagem
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

        // Validar preço promocional
        document.getElementById('preco_promocional').addEventListener('input', function() {
            const unitario = parseFloat(document.getElementById('valor_unitario').value) || 0;
            const promocional = parseFloat(this.value) || 0;
            if (promocional > 0 && promocional >= unitario) {
                this.value = (unitario - 0.01).toFixed(2);
                alert('⚠️ O preço promocional deve ser menor que o preço unitário.');
            }
        });
    });
</script>
@endpush
@endsection