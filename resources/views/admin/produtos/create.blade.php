@extends('layouts.app')

@section('title', 'Novo Produto')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-plus-circle"></i> Novo Produto</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.produtos.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <!-- DADOS BÁSICOS -->
                            <div class="col-md-6 mb-3">
                                <label for="descricao" class="form-label">Descrição *</label>
                                <input type="text" name="descricao" id="descricao" 
                                    class="form-control @error('descricao') is-invalid @enderror" 
                                    value="{{ old('descricao') }}" required>
                                @error('descricao')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="referencia" class="form-label">Referência</label>
                                <input type="text" name="referencia" id="referencia" 
                                    class="form-control @error('referencia') is-invalid @enderror" 
                                    value="{{ old('referencia') }}">
                                @error('referencia')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="tipo" class="form-label">Tipo</label>
                                <select name="tipo" id="tipo" class="form-select">
                                    <option value="">Selecione</option>
                                    <option value="UNI" {{ old('tipo') == 'UNI' ? 'selected' : '' }}>UNI (Unidade)</option>
                                    <option value="PÇ" {{ old('tipo') == 'PÇ' ? 'selected' : '' }}>PÇ (Peça)</option>
                                    <option value="CX" {{ old('tipo') == 'CX' ? 'selected' : '' }}>CX (Caixa)</option>
                                    <option value="PCO" {{ old('tipo') == 'PCO' ? 'selected' : '' }}>PCO (Pacote)</option>
                                    <option value="KIT" {{ old('tipo') == 'KIT' ? 'selected' : '' }}>KIT</option>
                                </select>
                            </div>

                            <!-- CATEGORIA -->
                            <div class="col-md-6 mb-3">
                                <label for="categoria" class="form-label">Categoria</label>
                                <input type="text" name="categoria" id="categoria" 
                                    class="form-control @error('categoria') is-invalid @enderror" 
                                    value="{{ old('categoria') }}" placeholder="Ex: AUDIO E VIDEO / JACK USB">
                                @error('categoria')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="categoria_id" class="form-label">Categoria (Relacionada)</label>
                                <select name="categoria_id" id="categoria_id" class="form-select">
                                    <option value="">Selecione uma categoria</option>
                                    @foreach($categorias as $categoria)
                                        <option value="{{ $categoria->id }}" 
                                            {{ old('categoria_id') == $categoria->id ? 'selected' : '' }}>
                                            {{ $categoria->nome }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- ESTOQUE -->
                            <div class="col-md-4 mb-3">
                                <label for="quantidade" class="form-label">Quantidade *</label>
                                <input type="number" name="quantidade" id="quantidade" 
                                    class="form-control @error('quantidade') is-invalid @enderror" 
                                    value="{{ old('quantidade', 0) }}" min="0" required>
                                @error('quantidade')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="estoque_minimo" class="form-label">Estoque Mínimo</label>
                                <input type="number" name="estoque_minimo" id="estoque_minimo" 
                                    class="form-control" value="{{ old('estoque_minimo', 5) }}" min="0">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="data_compra" class="form-label">Data da Compra</label>
                                <input type="date" name="data_compra" id="data_compra" 
                                    class="form-control" value="{{ old('data_compra') }}">
                            </div>

                            <!-- ========================================== -->
                            <!-- PREÇOS E CÁLCULOS - PARTE PRINCIPAL -->
                            <!-- ========================================== -->
                            
                            <div class="col-12">
                                <hr>
                                <h5 class="mb-3"><i class="fas fa-calculator"></i> Cálculo de Preços</h5>
                            </div>

                            <!-- VALOR DE COMPRA -->
                            <div class="col-md-3 mb-3">
                                <label for="valor_compra" class="form-label">Valor de Compra *</label>
                                <div class="input-group">
                                    <span class="input-group-text">R$</span>
                                    <input type="number" step="0.01" name="valor_compra" id="valor_compra" 
                                        class="form-control @error('valor_compra') is-invalid @enderror" 
                                        value="{{ old('valor_compra', 0) }}" min="0" required>
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
                                        readonly style="background-color: #f8f9fa; font-weight: bold; color: #0d6efd;">
                                </div>
                                <small class="text-muted">💡 Valor de Compra + Despesas</small>
                            </div>

                            <!-- MARGEM DE LUCRO - SELECT COM OPÇÕES 60% A 150% -->
                            <div class="col-md-3 mb-3">
                                <label for="margem_lucro" class="form-label">Margem de Lucro *</label>
                                <select name="margem_lucro" id="margem_lucro" 
                                    class="form-select @error('margem_lucro') is-invalid @enderror" required>
                                    <option value="">Selecione a Margem</option>
                                    @foreach($margens as $margem)
                                        <option value="{{ $margem }}" 
                                            {{ old('margem_lucro', 80) == $margem ? 'selected' : '' }}>
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

                            <!-- PREÇO DE VENDA (CALCULADO) -->
                            <div class="col-md-3 mb-3">
                                <label for="valor_unitario" class="form-label">Preço de Venda *</label>
                                <div class="input-group">
                                    <span class="input-group-text">R$</span>
                                    <input type="text" id="valor_unitario" class="form-control" 
                                        readonly style="background-color: #f8f9fa; font-weight: bold; color: #198754;">
                                </div>
                                <small class="text-muted">🔄 Calculado: Custo ÷ (1 - Margem/100)</small>
                            </div>

                            <!-- IPI -->
                            <div class="col-md-3 mb-3">
                                <label for="ipi" class="form-label">IPI (%)</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" name="ipi" id="ipi" 
                                        class="form-control @error('ipi') is-invalid @enderror" 
                                        value="{{ old('ipi', 9.75) }}" min="0" max="100">
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
                                    <input type="text" id="preco_com_ipi" class="form-control" 
                                        readonly style="background-color: #f8f9fa; font-weight: bold; color: #dc3545;">
                                </div>
                                <small class="text-muted">💡 Preço de Venda + IPI</small>
                            </div>

                            <!-- MARKUP (CALCULADO) -->
                            <div class="col-md-2 mb-3">
                                <label for="markup" class="form-label">Markup</label>
                                <input type="text" id="markup" class="form-control" readonly 
                                    style="background-color: #f8f9fa; font-weight: bold;">
                                <small class="text-muted">📊 Fator multiplicador</small>
                            </div>

                            <!-- VALOR DO IPI (CALCULADO) -->
                            <div class="col-md-2 mb-3">
                                <label for="valor_ipi" class="form-label">Valor do IPI</label>
                                <div class="input-group">
                                    <span class="input-group-text">R$</span>
                                    <input type="text" id="valor_ipi" class="form-control" 
                                        readonly style="background-color: #f8f9fa; font-weight: bold; color: #fd7e14;">
                                </div>
                            </div>

                            <!-- PERCENTUAL DE CUSTO (CALCULADO) -->
                            <div class="col-md-2 mb-3">
                                <label for="percentual_custo" class="form-label">% Custo</label>
                                <input type="text" id="percentual_custo" class="form-control" readonly 
                                    style="background-color: #f8f9fa; font-weight: bold;">
                                <small class="text-muted">📊 Custo ÷ Preço</small>
                            </div>

                            <!-- LUCRO BRUTO (CALCULADO) -->
                            <div class="col-md-2 mb-3">
                                <label for="lucro_bruto" class="form-label">Lucro Bruto</label>
                                <div class="input-group">
                                    <span class="input-group-text">R$</span>
                                    <input type="text" id="lucro_bruto" class="form-control" 
                                        readonly style="background-color: #f8f9fa; font-weight: bold; color: #198754;">
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
                                        value="{{ old('preco_promocional') }}" min="0">
                                </div>
                                @error('preco_promocional')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- PREÇO ATACADO -->
                            <div class="col-md-4 mb-3">
                                <label for="valor_atacado" class="form-label">Preço Atacado</label>
                                <div class="input-group">
                                    <span class="input-group-text">R$</span>
                                    <input type="number" step="0.01" name="valor_atacado" id="valor_atacado" 
                                        class="form-control @error('valor_atacado') is-invalid @enderror" 
                                        value="{{ old('valor_atacado') }}" min="0">
                                </div>
                                @error('valor_atacado')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- STATUS -->
                            <div class="col-md-4 mb-3">
                                <label for="ativo" class="form-label">Status</label>
                                <select name="ativo" id="ativo" class="form-select">
                                    <option value="1" {{ old('ativo', 1) == 1 ? 'selected' : '' }}>Ativo</option>
                                    <option value="0" {{ old('ativo') == 0 ? 'selected' : '' }}>Inativo</option>
                                </select>
                            </div>

                            <!-- DESTAQUES -->
                            <div class="col-md-3 mb-3">
                                <div class="form-check">
                                    <input type="checkbox" name="destaque" id="destaque" 
                                        class="form-check-input" value="1" 
                                        {{ old('destaque') ? 'checked' : '' }}>
                                    <label for="destaque" class="form-check-label">Destaque</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" name="novo" id="novo" 
                                        class="form-check-input" value="1" 
                                        {{ old('novo') ? 'checked' : '' }}>
                                    <label for="novo" class="form-check-label">Produto Novo</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" name="mais_vendido" id="mais_vendido" 
                                        class="form-check-input" value="1" 
                                        {{ old('mais_vendido') ? 'checked' : '' }}>
                                    <label for="mais_vendido" class="form-check-label">Mais Vendido</label>
                                </div>
                            </div>

                            <!-- IMAGEM -->
                            <div class="col-md-9 mb-3">
                                <label for="imagem" class="form-label">Imagem Principal</label>
                                <input type="file" name="imagem" id="imagem" 
                                    class="form-control @error('imagem') is-invalid @enderror" accept="image/*">
                                <small class="text-muted">Formatos: JPG, PNG, GIF. Máx: 2MB</small>
                                @error('imagem')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div id="imagePreview" class="mt-2"></div>
                            </div>

                            <!-- BOTÕES -->
                            <div class="col-12 mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Salvar Produto
                                </button>
                                <a href="{{ route('admin.produtos.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancelar
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
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
        if (margem <= 0 || margem >= 100) {
            return custo;
        }
        return custo / (1 - (margem / 100));
    }

    function calcularMarkup(margem) {
        if (margem <= 0 || margem >= 100) {
            return 1;
        }
        return 1 / (1 - (margem / 100));
    }

    function calcularPrecoComIPI(preco, ipi) {
        if (ipi <= 0) {
            return preco;
        }
        return preco * (1 + (ipi / 100));
    }

    function calcularValorIPI(preco, ipi) {
        if (ipi <= 0) {
            return 0;
        }
        return preco * (ipi / 100);
    }

    function calcularPercentualCusto(custo, preco) {
        if (preco <= 0) {
            return 0;
        }
        return (custo / preco) * 100;
    }

    // ============================================================
    // FUNÇÃO PRINCIPAL - ATUALIZA TODOS OS CAMPOS
    // ============================================================

    function atualizarPrecos() {
        // 1. Obtém os valores
        const valorCompra = parseFloat(document.getElementById('valor_compra').value.replace(',', '.')) || 0;
        const margem = parseFloat(document.getElementById('margem_lucro').value) || 80;
        const ipi = parseFloat(document.getElementById('ipi').value.replace(',', '.')) || 0;
        const despesas = 0;

        // 2. Calcula o custo (compra + despesas)
        const custo = valorCompra + despesas;
        document.getElementById('valor_custo').value = formatarMoeda(custo);

        // 3. Calcula o preço de venda
        const precoVenda = calcularPrecoVenda(custo, margem);
        document.getElementById('valor_unitario').value = formatarMoeda(precoVenda);

        // 4. Calcula o markup
        const markup = calcularMarkup(margem);
        document.getElementById('markup').value = markup.toFixed(2) + 'x';

        // 5. Calcula o preço com IPI
        const precoComIPI = calcularPrecoComIPI(precoVenda, ipi);
        document.getElementById('preco_com_ipi').value = formatarMoeda(precoComIPI);

        // 6. Calcula o valor do IPI
        const valorIPI = calcularValorIPI(precoVenda, ipi);
        document.getElementById('valor_ipi').value = formatarMoeda(valorIPI);

        // 7. Calcula o percentual de custo
        const percentualCusto = calcularPercentualCusto(custo, precoVenda);
        document.getElementById('percentual_custo').value = percentualCusto.toFixed(2) + '%';

        // 8. Calcula o lucro bruto
        const lucro = precoVenda - custo;
        document.getElementById('lucro_bruto').value = formatarMoeda(lucro);

        // 9. Atualiza cores baseado na margem
        const precoField = document.getElementById('valor_unitario');
        if (margem >= 120) {
            precoField.style.color = '#dc3545';
        } else if (margem >= 100) {
            precoField.style.color = '#fd7e14';
        } else if (margem >= 80) {
            precoField.style.color = '#198754';
        } else {
            precoField.style.color = '#0d6efd';
        }

        console.log('📊 Cálculos:', {
            'Valor Compra': 'R$ ' + valorCompra.toFixed(2),
            'Custo': 'R$ ' + custo.toFixed(2),
            'Margem': margem + '%',
            'Markup': markup.toFixed(2) + 'x',
            'Preço Venda': 'R$ ' + precoVenda.toFixed(2),
            'IPI': ipi + '%',
            'Preço com IPI': 'R$ ' + precoComIPI.toFixed(2),
            'Valor IPI': 'R$ ' + valorIPI.toFixed(2),
            'Lucro': 'R$ ' + lucro.toFixed(2)
        });
    }

    // ============================================================
    // EVENT LISTENERS
    // ============================================================

    document.getElementById('valor_compra').addEventListener('input', atualizarPrecos);
    document.getElementById('margem_lucro').addEventListener('change', atualizarPrecos);
    document.getElementById('ipi').addEventListener('input', atualizarPrecos);

    document.querySelectorAll('.margem-rapida').forEach(btn => {
        btn.addEventListener('click', function() {
            const margem = this.dataset.margem;
            document.getElementById('margem_lucro').value = margem;
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
        const valor = parseFloat(this.value);
        if (valor > 150) {
            this.value = 150;
            alert('⚠️ Margem máxima permitida: 150%');
            atualizarPrecos();
        }
        if (valor < 60) {
            this.value = 60;
            alert('⚠️ Margem mínima recomendada: 60%');
            atualizarPrecos();
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        // Preview da imagem
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

        // Atualiza preços ao carregar
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