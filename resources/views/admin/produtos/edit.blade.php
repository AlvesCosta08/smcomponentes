@extends('layouts.app')  {{-- ALTERADO --}}

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
                        <input type="text" name="descricao" id="descricao" class="form-control @error('descricao') is-invalid @enderror" value="{{ old('descricao', $produto->descricao) }}" required>
                        @error('descricao')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Referência -->
                    <div class="col-md-4 mb-3">
                        <label for="referencia" class="form-label">Referência</label>
                        <input type="text" name="referencia" id="referencia" class="form-control @error('referencia') is-invalid @enderror" value="{{ old('referencia', $produto->referencia) }}">
                        @error('referencia')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Categoria -->
                    <div class="col-md-6 mb-3">
                        <label for="categoria" class="form-label">Categoria *</label>
                        <input type="text" name="categoria" id="categoria" class="form-control @error('categoria') is-invalid @enderror" value="{{ old('categoria', $produto->categoria) }}" required>
                        @error('categoria')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Quantidade -->
                    <div class="col-md-3 mb-3">
                        <label for="quantidade" class="form-label">Quantidade *</label>
                        <input type="number" name="quantidade" id="quantidade" class="form-control @error('quantidade') is-invalid @enderror" value="{{ old('quantidade', $produto->quantidade) }}" min="0" required>
                        @error('quantidade')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Disponibilidade -->
                    <div class="col-md-3 mb-3">
                        <label for="disponibilidade" class="form-label">Disponibilidade *</label>
                        <select name="disponibilidade" id="disponibilidade" class="form-select @error('disponibilidade') is-invalid @enderror" required>
                            <option value="DISPONIVEL" {{ old('disponibilidade', $produto->disponibilidade) == 'DISPONIVEL' ? 'selected' : '' }}>Disponível</option>
                            <option value="INDISPONIVEL" {{ old('disponibilidade', $produto->disponibilidade) == 'INDISPONIVEL' ? 'selected' : '' }}>Indisponível</option>
                            <option value="EST.BAIXO" {{ old('disponibilidade', $produto->disponibilidade) == 'EST.BAIXO' ? 'selected' : '' }}>Estoque Baixo</option>
                        </select>
                        @error('disponibilidade')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- ========================================== -->
                    <!-- PREÇOS E CÁLCULOS - PARTE PRINCIPAL -->
                    <!-- ========================================== -->
                    
                    <div class="col-12">
                        <hr>
                        <h5 class="mb-3"><i class="bi bi-calculator"></i> Cálculo de Preços</h5>
                    </div>

                    <!-- VALOR DE COMPRA -->
                    <div class="col-md-3 mb-3">
                        <label for="valor_compra" class="form-label">Valor de Compra *</label>
                        <div class="input-group">
                            <span class="input-group-text">R$</span>
                            <input type="number" step="0.01" name="valor_compra" id="valor_compra" 
                                class="form-control @error('valor_compra') is-invalid @enderror" 
                                value="{{ old('valor_compra', $produto->valor_compra) }}" min="0" required>
                        </div>
                        @error('valor_compra')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- VALOR DE CUSTO (CALCULADO) -->
                    <div class="col-md-3 mb-3">
                        <label for="valor_custo" class="form-label">Valor de Custo</label>
                        <div class="input-group">
                            <span class="input-group-text">R$</span>
                            <input type="text" id="valor_custo" class="form-control" 
                                readonly style="background-color: #f8f9fa; font-weight: bold; color: #0d6efd;" 
                                value="{{ old('valor_custo', $produto->valor_custo ? 'R$ ' . number_format($produto->valor_custo, 2, ',', '.') : '') }}">
                        </div>
                        <small class="text-muted">💡 Valor de Compra + Despesas</small>
                    </div>

                    <!-- MARGEM DE LUCRO -->
                    <div class="col-md-3 mb-3">
                        <label for="margem_lucro" class="form-label">Margem de Lucro *</label>
                        <select name="margem_lucro" id="margem_lucro" 
                            class="form-select @error('margem_lucro') is-invalid @enderror" required>
                            <option value="">Selecione a Margem</option>
                            @foreach(\App\Models\Produto::getMargensDisponiveis() as $margem)
                                <option value="{{ $margem }}" 
                                    {{ old('margem_lucro', $produto->margem_lucro ?? 80) == $margem ? 'selected' : '' }}>
                                    {{ $margem }}% 
                                    @if($margem == 60) (Mínima)
                                    @elseif($margem == 80) (Recomendada)
                                    @elseif($margem == 100) (Alta)
                                    @elseif($margem >= 120) (Premium)
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('margem_lucro')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- PREÇO ATACADO (CALCULADO) -->
                    <div class="col-md-3 mb-3">
                        <label for="valor_atacado" class="form-label">Preço Atacado *</label>
                        <div class="input-group">
                            <span class="input-group-text">R$</span>
                            <input type="text" id="valor_atacado_display" class="form-control" 
                                readonly style="background-color: #f8f9fa; font-weight: bold; color: #198754;"
                                value="{{ old('valor_atacado', $produto->valor_atacado ? 'R$ ' . number_format($produto->valor_atacado, 2, ',', '.') : '') }}">
                        </div>
                        <small class="text-muted">🔄 Calculado: Custo ÷ (1 - Margem/100)</small>
                        <input type="hidden" name="valor_atacado" id="valor_atacado" value="{{ old('valor_atacado', $produto->valor_atacado) }}">
                    </div>

                    <!-- IPI -->
                    <div class="col-md-3 mb-3">
                        <label for="ipi" class="form-label">IPI (%)</label>
                        <div class="input-group">
                            <input type="number" step="0.01" name="ipi" id="ipi" 
                                class="form-control @error('ipi') is-invalid @enderror" 
                                value="{{ old('ipi', $produto->ipi ?? 9.75) }}" min="0" max="100">
                            <span class="input-group-text">%</span>
                        </div>
                        @error('ipi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- PREÇO COM IPI (CALCULADO) -->
                    <div class="col-md-3 mb-3">
                        <label for="preco_com_ipi" class="form-label">Preço com IPI</label>
                        <div class="input-group">
                            <span class="input-group-text">R$</span>
                            <input type="text" id="preco_com_ipi_display" class="form-control" 
                                readonly style="background-color: #f8f9fa; font-weight: bold; color: #dc3545;"
                                value="{{ old('preco_com_ipi', isset($produto->valor_atacado) && isset($produto->ipi) ? 'R$ ' . number_format($produto->valor_atacado * (1 + $produto->ipi/100), 2, ',', '.') : '') }}">
                        </div>
                        <small class="text-muted">💡 Preço Atacado + IPI</small>
                    </div>

                    <!-- VALOR DO IPI (CALCULADO) -->
                    <div class="col-md-2 mb-3">
                        <label for="valor_ipi" class="form-label">Valor do IPI</label>
                        <div class="input-group">
                            <span class="input-group-text">R$</span>
                            <input type="text" id="valor_ipi_display" class="form-control" 
                                readonly style="background-color: #f8f9fa; font-weight: bold; color: #fd7e14;"
                                value="{{ old('valor_ipi', isset($produto->valor_atacado) && isset($produto->ipi) ? 'R$ ' . number_format($produto->valor_atacado * ($produto->ipi/100), 2, ',', '.') : '') }}">
                        </div>
                    </div>

                    <!-- MARKUP (CALCULADO) -->
                    <div class="col-md-2 mb-3">
                        <label for="markup" class="form-label">Markup</label>
                        <input type="text" id="markup_display" class="form-control" readonly 
                            style="background-color: #f8f9fa; font-weight: bold;"
                            value="{{ old('markup', isset($produto->margem_lucro) ? number_format(1/(1 - $produto->margem_lucro/100), 2) : '') }}x">
                    </div>

                    <!-- PERCENTUAL DE CUSTO (CALCULADO) -->
                    <div class="col-md-2 mb-3">
                        <label for="percentual_custo" class="form-label">% Custo</label>
                        <input type="text" id="percentual_custo_display" class="form-control" readonly 
                            style="background-color: #f8f9fa; font-weight: bold;"
                            value="{{ old('percentual_custo', isset($produto->percentual_custo) ? number_format($produto->percentual_custo, 2) . '%' : '') }}">
                    </div>

                    <!-- LUCRO BRUTO (CALCULADO) -->
                    <div class="col-md-3 mb-3">
                        <label for="lucro_bruto" class="form-label">Lucro Bruto</label>
                        <div class="input-group">
                            <span class="input-group-text">R$</span>
                            <input type="text" id="lucro_bruto_display" class="form-control" 
                                readonly style="background-color: #f8f9fa; font-weight: bold; color: #198754;"
                                value="{{ old('lucro_bruto', isset($produto->valor_atacado) && isset($produto->valor_custo) ? 'R$ ' . number_format($produto->valor_atacado - $produto->valor_custo, 2, ',', '.') : '') }}">
                        </div>
                    </div>

                    <!-- BOTÕES DE MARGEM RÁPIDA -->
                    <div class="col-12 mb-3">
                        <label class="form-label">⚡ Margens Rápidas:</label>
                        <div class="btn-group flex-wrap gap-1" role="group">
                            @foreach([60, 70, 80, 90, 100, 120, 150] as $m)
                                <button type="button" class="btn btn-outline-primary btn-sm margem-rapida" 
                                    data-margem="{{ $m }}">
                                    {{ $m }}%
                                </button>
                            @endforeach
                        </div>
                        <small class="text-muted d-block mt-1">
                            Clique para aplicar a margem rapidamente
                        </small>
                    </div>

                    <!-- PREÇO PROMOCIONAL -->
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

                    <!-- Ativo -->
                    <div class="col-md-4 mb-3">
                        <label for="ativo" class="form-label">Status</label>
                        <select name="ativo" id="ativo" class="form-select @error('ativo') is-invalid @enderror">
                            <option value="1" {{ old('ativo', $produto->ativo) == 1 ? 'selected' : '' }}>Ativo</option>
                            <option value="0" {{ old('ativo', $produto->ativo) == 0 ? 'selected' : '' }}>Inativo</option>
                        </select>
                        @error('ativo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Destaques -->
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
                        <label for="imagem" class="form-label">Nova Imagem</label>
                        <input type="file" name="imagem" id="imagem" 
                            class="form-control @error('imagem') is-invalid @enderror" accept="image/*">
                        <small class="text-muted">Deixe em branco para manter a imagem atual.</small>
                        @error('imagem')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div id="imagePreview" class="mt-2"></div>
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
    // ============================================================
    // FUNÇÕES DE CÁLCULO
    // ============================================================

    function formatarMoeda(valor) {
        return 'R$ ' + valor.toFixed(2).replace('.', ',');
    }

    function calcularPrecoVenda(custo, margem) {
        if (margem <= 0 || margem >= 100) return custo;
        return custo / (1 - (margem / 100));
    }

    function calcularMarkup(margem) {
        if (margem <= 0 || margem >= 100) return 1;
        return 1 / (1 - (margem / 100));
    }

    function calcularPrecoComIPI(preco, ipi) {
        if (ipi <= 0) return preco;
        return preco * (1 + (ipi / 100));
    }

    function calcularValorIPI(preco, ipi) {
        if (ipi <= 0) return 0;
        return preco * (ipi / 100);
    }

    function calcularPercentualCusto(custo, preco) {
        if (preco <= 0) return 0;
        return (custo / preco) * 100;
    }

    // ============================================================
    // FUNÇÃO PRINCIPAL
    // ============================================================

    function atualizarPrecos() {
        const valorCompra = parseFloat(document.getElementById('valor_compra').value.replace(',', '.')) || 0;
        const margem = parseFloat(document.getElementById('margem_lucro').value) || 80;
        const ipi = parseFloat(document.getElementById('ipi').value.replace(',', '.')) || 0;

        // Custo (compra + despesas)
        const custo = valorCompra;
        
        // Preço Atacado
        const precoAtacado = calcularPrecoVenda(custo, margem);
        document.getElementById('valor_atacado_display').value = formatarMoeda(precoAtacado);
        document.getElementById('valor_atacado').value = precoAtacado;

        // Valor Custo
        document.getElementById('valor_custo').value = formatarMoeda(custo);

        // Markup
        const markup = calcularMarkup(margem);
        document.getElementById('markup_display').value = markup.toFixed(2) + 'x';

        // Percentual Custo
        const pctCusto = calcularPercentualCusto(custo, precoAtacado);
        document.getElementById('percentual_custo_display').value = pctCusto.toFixed(2) + '%';

        // Preço com IPI
        const precoComIPI = calcularPrecoComIPI(precoAtacado, ipi);
        document.getElementById('preco_com_ipi_display').value = formatarMoeda(precoComIPI);

        // Valor IPI
        const valorIPI = calcularValorIPI(precoAtacado, ipi);
        document.getElementById('valor_ipi_display').value = formatarMoeda(valorIPI);

        // Lucro Bruto
        const lucro = precoAtacado - custo;
        document.getElementById('lucro_bruto_display').value = formatarMoeda(lucro);

        // Cor do preço
        const precoField = document.getElementById('valor_atacado_display');
        if (margem >= 120) precoField.style.color = '#dc3545';
        else if (margem >= 100) precoField.style.color = '#fd7e14';
        else if (margem >= 80) precoField.style.color = '#198754';
        else precoField.style.color = '#0d6efd';
    }

    // ============================================================
    // EVENT LISTENERS
    // ============================================================

    document.getElementById('valor_compra').addEventListener('input', atualizarPrecos);
    document.getElementById('margem_lucro').addEventListener('change', atualizarPrecos);
    document.getElementById('ipi').addEventListener('input', atualizarPrecos);

    document.querySelectorAll('.margem-rapida').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('margem_lucro').value = this.dataset.margem;
            atualizarPrecos();
            this.classList.add('btn-primary');
            this.classList.remove('btn-outline-primary');
            setTimeout(() => {
                this.classList.remove('btn-primary');
                this.classList.add('btn-outline-primary');
            }, 300);
        });
    });

    document.getElementById('margem_lucro').addEventListener('change', function() {
        let valor = parseFloat(this.value);
        if (valor > 150) { this.value = 150; alert('⚠️ Margem máxima: 150%'); atualizarPrecos(); }
        if (valor < 60) { this.value = 60; alert('⚠️ Margem mínima: 60%'); atualizarPrecos(); }
    });

    // Preview imagem
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

    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('valor_compra').value) {
            atualizarPrecos();
        }
        if (!document.getElementById('margem_lucro').value) {
            document.getElementById('margem_lucro').value = 80;
            atualizarPrecos();
        }
    });
</script>
@endpush
@endsection