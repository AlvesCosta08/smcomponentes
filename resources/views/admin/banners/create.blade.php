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

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.banners.store') }}" method="POST" enctype="multipart/form-data" id="bannerForm">
                @csrf

                <div class="row">
                    <!-- Tipo do Banner -->
                    <div class="col-md-6 mb-3">
                        <label for="tipo" class="form-label">Tipo do Banner</label>
                        <select name="tipo" id="tipo" class="form-select @error('tipo') is-invalid @enderror">
                            <option value="imagem" {{ old('tipo', 'imagem') == 'imagem' ? 'selected' : '' }}>Imagem</option>
                            <option value="texto" {{ old('tipo') == 'texto' ? 'selected' : '' }}>Somente Texto</option>
                            <option value="misto" {{ old('tipo') == 'misto' ? 'selected' : '' }}>Misto (Texto + Imagem)</option>
                            <option value="hero" {{ old('tipo') == 'hero' ? 'selected' : '' }}>Hero</option>
                            <option value="promocional" {{ old('tipo') == 'promocional' ? 'selected' : '' }}>Promocional</option>
                            <option value="informativo" {{ old('tipo') == 'informativo' ? 'selected' : '' }}>Informativo</option>
                        </select>
                        @error('tipo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div class="col-md-6 mb-3">
                        <label for="ativo" class="form-label">Status</label>
                        <div class="form-check form-switch mt-2">
                            <input type="hidden" name="ativo" value="0">
                            <input type="checkbox" name="ativo" id="ativo" class="form-check-input" value="1" {{ old('ativo', false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="ativo">Ativo</label>
                        </div>
                    </div>

                    <!-- Imagem (controlada pelo tipo) -->
                    <div class="col-12 mb-3" id="campoImagem">
                        <label for="imagem" class="form-label">Imagem do Banner <span class="text-muted">(opcional)</span></label>
                        <input type="file" 
                               name="imagem" 
                               id="imagem" 
                               class="form-control @error('imagem') is-invalid @enderror" 
                               accept="image/*"
                               aria-describedby="imagemHelp">
                        <small id="imagemHelp" class="text-muted">
                            Formatos: JPG, PNG, GIF, WEBP, SVG. Máx: 5MB 
                            <span class="text-warning">(opcional)</span>
                        </small>
                        @error('imagem')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div id="previewImagem" class="mt-2" style="display: none;">
                            <img src="" alt="Preview" style="max-height: 200px; border-radius: 8px; border: 1px solid #ddd; padding: 4px;">
                            <br>
                            <button type="button" class="btn btn-sm btn-danger mt-1" onclick="removerPreview()">
                                <i class="bi bi-x-circle"></i> Remover
                            </button>
                        </div>
                    </div>

                    <!-- Título -->
                    <div class="col-md-6 mb-3">
                        <label for="titulo" class="form-label">Título <span class="text-muted">(opcional)</span></label>
                        <input type="text" 
                               name="titulo" 
                               id="titulo" 
                               class="form-control @error('titulo') is-invalid @enderror" 
                               value="{{ old('titulo') }}"
                               placeholder="Digite o título do banner">
                        @error('titulo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Subtítulo -->
                    <div class="col-md-6 mb-3">
                        <label for="subtitulo" class="form-label">Subtítulo <span class="text-muted">(opcional)</span></label>
                        <input type="text" 
                               name="subtitulo" 
                               id="subtitulo" 
                               class="form-control @error('subtitulo') is-invalid @enderror" 
                               value="{{ old('subtitulo') }}"
                               placeholder="Digite o subtítulo do banner">
                        @error('subtitulo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Descrição -->
                    <div class="col-12 mb-3">
                        <label for="descricao" class="form-label">Descrição <span class="text-muted">(opcional)</span></label>
                        <textarea name="descricao" 
                                  id="descricao" 
                                  rows="3" 
                                  class="form-control @error('descricao') is-invalid @enderror"
                                  placeholder="Digite a descrição do banner">{{ old('descricao') }}</textarea>
                        @error('descricao')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Cor do Fundo -->
                    <div class="col-md-6 mb-3">
                        <label for="cor_fundo" class="form-label">Cor do Fundo <span class="text-muted">(opcional)</span></label>
                        <input type="text" 
                               name="cor_fundo" 
                               id="cor_fundo" 
                               class="form-control @error('cor_fundo') is-invalid @enderror" 
                               value="{{ old('cor_fundo') }}" 
                               placeholder="#0d6efd ou linear-gradient(...)">
                        <small class="text-muted">Use hexadecimal ou gradiente CSS</small>
                        @error('cor_fundo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Cor do Texto -->
                    <div class="col-md-6 mb-3">
                        <label for="cor_texto" class="form-label">Cor do Texto <span class="text-muted">(opcional)</span></label>
                        <input type="color" 
                               name="cor_texto" 
                               id="cor_texto" 
                               class="form-control form-control-color" 
                               value="{{ old('cor_texto', '#ffffff') }}">
                        @error('cor_texto')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Link -->
                    <div class="col-md-6 mb-3">
                        <label for="link" class="form-label">Link do Botão <span class="text-muted">(opcional)</span></label>
                        <input type="url" 
                               name="link" 
                               id="link" 
                               class="form-control @error('link') is-invalid @enderror" 
                               value="{{ old('link') }}" 
                               placeholder="https://exemplo.com">
                        @error('link')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Texto do Botão -->
                    <div class="col-md-3 mb-3">
                        <label for="texto_botao" class="form-label">Texto do Botão <span class="text-muted">(opcional)</span></label>
                        <input type="text" 
                               name="texto_botao" 
                               id="texto_botao" 
                               class="form-control @error('texto_botao') is-invalid @enderror" 
                               value="{{ old('texto_botao', 'Saiba Mais') }}"
                               placeholder="Texto do botão">
                        @error('texto_botao')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Cor do Botão -->
                    <div class="col-md-3 mb-3">
                        <label for="cor_botao" class="form-label">Cor do Botão <span class="text-muted">(opcional)</span></label>
                        <select name="cor_botao" id="cor_botao" class="form-select @error('cor_botao') is-invalid @enderror">
                            <option value="">Selecione</option>
                            <option value="primary" {{ old('cor_botao') == 'primary' ? 'selected' : '' }}>Primário</option>
                            <option value="secondary" {{ old('cor_botao') == 'secondary' ? 'selected' : '' }}>Secundário</option>
                            <option value="success" {{ old('cor_botao') == 'success' ? 'selected' : '' }}>Sucesso</option>
                            <option value="danger" {{ old('cor_botao') == 'danger' ? 'selected' : '' }}>Perigo</option>
                            <option value="warning" {{ old('cor_botao') == 'warning' ? 'selected' : '' }}>Atenção</option>
                            <option value="info" {{ old('cor_botao') == 'info' ? 'selected' : '' }}>Info</option>
                            <option value="light" {{ old('cor_botao') == 'light' ? 'selected' : '' }}>Claro</option>
                            <option value="dark" {{ old('cor_botao') == 'dark' ? 'selected' : '' }}>Escuro</option>
                        </select>
                        @error('cor_botao')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Ordem -->
                    <div class="col-md-6 mb-3">
                        <label for="ordem" class="form-label">Ordem <span class="text-muted">(opcional)</span></label>
                        <input type="number" 
                               name="ordem" 
                               id="ordem" 
                               class="form-control @error('ordem') is-invalid @enderror" 
                               value="{{ old('ordem', ($bannersMaxOrder ?? 0) + 1) }}">
                        <small class="text-muted">Deixe em branco para adicionar ao final</small>
                        @error('ordem')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Data de Início -->
                    <div class="col-md-6 mb-3">
                        <label for="inicio_em" class="form-label">Data de Início <span class="text-muted">(opcional)</span></label>
                        <input type="datetime-local" 
                               name="inicio_em" 
                               id="inicio_em" 
                               class="form-control @error('inicio_em') is-invalid @enderror" 
                               value="{{ old('inicio_em') }}">
                        @error('inicio_em')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Data de Término -->
                    <div class="col-md-6 mb-3">
                        <label for="termino_em" class="form-label">Data de Término <span class="text-muted">(opcional)</span></label>
                        <input type="datetime-local" 
                               name="termino_em" 
                               id="termino_em" 
                               class="form-control @error('termino_em') is-invalid @enderror" 
                               value="{{ old('termino_em') }}">
                        @error('termino_em')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Botões -->
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary" id="btnSalvar">
                        <i class="bi bi-save"></i> Salvar Banner
                    </button>
                    <a href="{{ route('admin.banners.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Elementos
        const inputImagem = document.getElementById('imagem');
        const previewDiv = document.getElementById('previewImagem');
        const previewImg = previewDiv.querySelector('img');
        const tipoSelect = document.getElementById('tipo');
        const campoImagem = document.getElementById('campoImagem');

        // Função para controlar visibilidade do campo imagem
        function toggleCampoImagem() {
            const tipo = tipoSelect.value;
            
            // Se for "texto", esconde o campo de imagem
            if (tipo === 'texto') {
                campoImagem.style.display = 'none';
                inputImagem.removeAttribute('required');
                // Limpa o preview se estiver escondendo
                if (previewDiv.style.display !== 'none') {
                    removerPreview();
                }
            } else {
                campoImagem.style.display = 'block';
                // Não torna obrigatório, mantém opcional
                inputImagem.removeAttribute('required');
            }
        }

        // Preview da imagem
        inputImagem.addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    previewImg.src = event.target.result;
                    previewDiv.style.display = 'block';
                };
                reader.readAsDataURL(this.files[0]);
            } else {
                previewDiv.style.display = 'none';
            }
        });

        // Remover preview
        window.removerPreview = function() {
            inputImagem.value = '';
            previewDiv.style.display = 'none';
            previewImg.src = '';
        };

        // Evento de mudança do tipo
        tipoSelect.addEventListener('change', function() {
            toggleCampoImagem();
        });

        // Executar ao carregar a página
        toggleCampoImagem();

        // Prevenir envio se o tipo for texto e tiver imagem
        document.getElementById('bannerForm').addEventListener('submit', function(e) {
            if (tipoSelect.value === 'texto' && inputImagem.files.length > 0) {
                // Se for texto, remove a imagem do upload
                inputImagem.value = '';
                previewDiv.style.display = 'none';
            }
        });
    });
</script>
@endpush
@endsection
