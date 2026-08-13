@extends('layouts.app')

@section('title', 'Novo Banner - Admin')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 mb-0">📸 Novo Banner</h1>
        <a href="{{ route('admin.banners.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.banners.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="tipo" class="form-label">Tipo do Banner *</label>
                        <select name="tipo" id="tipo" class="form-select @error('tipo') is-invalid @enderror" required>
                            <option value="imagem">Imagem</option>
                            <option value="texto">Somente Texto</option>
                            <option value="misto">Misto (Texto + Imagem)</option>
                        </select>
                        @error('tipo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="ativo" class="form-label">Status</label>
                        <div class="form-check form-switch mt-2">
                            <input type="hidden" name="ativo" value="0">
                            <input type="checkbox" name="ativo" id="ativo" class="form-check-input" value="1" checked>
                            <label class="form-check-label" for="ativo">Ativo</label>
                        </div>
                    </div>

                    <div class="col-12 mb-3" id="campoImagem">
                        <label for="imagem" class="form-label">Imagem do Banner</label>
                        <input type="file" name="imagem" id="imagem" class="form-control @error('imagem') is-invalid @enderror" accept="image/*">
                        <small class="text-muted">Formatos: JPG, PNG, GIF, WEBP. Máx: 2MB</small>
                        @error('imagem')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div id="previewImagem" class="mt-2" style="display: none;">
                            <img src="" alt="Preview" style="max-height: 200px; border-radius: 8px;">
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="titulo" class="form-label">Título</label>
                        <input type="text" name="titulo" id="titulo" class="form-control @error('titulo') is-invalid @enderror" value="{{ old('titulo') }}">
                        @error('titulo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="subtitulo" class="form-label">Subtítulo</label>
                        <input type="text" name="subtitulo" id="subtitulo" class="form-control @error('subtitulo') is-invalid @enderror" value="{{ old('subtitulo') }}">
                        @error('subtitulo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 mb-3">
                        <label for="descricao" class="form-label">Descrição</label>
                        <textarea name="descricao" id="descricao" rows="3" class="form-control @error('descricao') is-invalid @enderror">{{ old('descricao') }}</textarea>
                        @error('descricao')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="cor_fundo" class="form-label">Cor do Fundo</label>
                        <input type="text" name="cor_fundo" id="cor_fundo" class="form-control @error('cor_fundo') is-invalid @enderror" 
                               value="{{ old('cor_fundo', '#0d6efd') }}" placeholder="#0d6efd ou linear-gradient(...)">
                        <small class="text-muted">Use hexadecimal ou gradiente CSS</small>
                        @error('cor_fundo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="cor_texto" class="form-label">Cor do Texto</label>
                        <input type="color" name="cor_texto" id="cor_texto" class="form-control form-control-color" 
                               value="{{ old('cor_texto', '#ffffff') }}">
                        @error('cor_texto')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="link" class="form-label">Link do Botão</label>
                        <input type="url" name="link" id="link" class="form-control @error('link') is-invalid @enderror" 
                               value="{{ old('link') }}" placeholder="https://...">
                        @error('link')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="texto_botao" class="form-label">Texto do Botão</label>
                        <input type="text" name="texto_botao" id="texto_botao" class="form-control @error('texto_botao') is-invalid @enderror" 
                               value="{{ old('texto_botao', 'Saiba Mais') }}">
                        @error('texto_botao')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="cor_botao" class="form-label">Cor do Botão</label>
                        <select name="cor_botao" id="cor_botao" class="form-select @error('cor_botao') is-invalid @enderror">
                            <option value="primary">Primário</option>
                            <option value="secondary">Secundário</option>
                            <option value="success">Sucesso</option>
                            <option value="danger">Perigo</option>
                            <option value="warning">Atenção</option>
                            <option value="info">Info</option>
                            <option value="light">Claro</option>
                            <option value="dark">Escuro</option>
                        </select>
                        @error('cor_botao')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="ordem" class="form-label">Ordem</label>
                        <input type="number" name="ordem" id="ordem" class="form-control @error('ordem') is-invalid @enderror" 
                               value="{{ old('ordem', $bannersMaxOrder ?? 1) }}">
                        <small class="text-muted">Deixe em branco para adicionar ao final</small>
                        @error('ordem')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Salvar Banner
                    </button>
                    <a href="{{ route('admin.banners.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Preview da imagem
        document.getElementById('imagem').addEventListener('change', function(e) {
            const preview = document.getElementById('previewImagem');
            const img = preview.querySelector('img');
            
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    img.src = event.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(this.files[0]);
            } else {
                preview.style.display = 'none';
            }
        });

        // Mostrar/ocultar campo de imagem baseado no tipo
        document.getElementById('tipo').addEventListener('change', function() {
            const campoImagem = document.getElementById('campoImagem');
            if (this.value === 'texto') {
                campoImagem.style.display = 'none';
            } else {
                campoImagem.style.display = 'block';
            }
        });
    });
</script>
@endpush
@endsection