@extends('layouts.app')

@section('title', 'Dashboard - Admin')

@section('content')
<div class="container-fluid px-2 px-md-4">
    <!-- Header -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-4 gap-2">
        <div>
            <h1 class="h3 h2-md mb-0">📊 Dashboard</h1>
            <small class="text-muted d-none d-sm-inline">Visão geral da sua loja</small>
        </div>
        <div class="w-100 w-sm-auto">
            <span class="badge bg-primary fs-6 d-block d-sm-inline-block text-center">
                <i class="bi bi-clock me-1"></i> 
                {{ now()->format('d/m/Y H:i') }}
            </span>
        </div>
    </div>

    <!-- Cards de Estatísticas -->
    <div class="row g-2 g-md-3 g-lg-4 mb-4">
        @php
            $stats = [
                ['label' => 'Usuários', 'value' => $totalUsuarios ?? 0, 'sub' => $novosUsuariosHoje ?? 0, 'subLabel' => 'hoje', 'icon' => 'people-fill', 'color' => 'primary', 'route' => 'admin.usuarios.index'],
                ['label' => 'Produtos', 'value' => $totalProdutos ?? 0, 'sub' => $produtosAtivos ?? 0, 'subLabel' => 'ativos', 'icon' => 'box-seam-fill', 'color' => 'success', 'route' => 'admin.produtos.index'],
                ['label' => 'Pedidos', 'value' => $totalPedidos ?? 0, 'sub' => $pedidosStatus['pendente'] ?? 0, 'subLabel' => 'pendentes', 'icon' => 'cart-fill', 'color' => 'warning', 'route' => 'admin.pedidos.index'],
                ['label' => 'Faturamento', 'value' => 'R$ '.number_format($faturamentoTotal ?? 0, 2, ',', '.'), 'sub' => 'R$ '.number_format($faturamentoMes ?? 0, 2, ',', '.'), 'subLabel' => 'mês', 'icon' => 'currency-dollar', 'color' => 'info', 'route' => 'admin.pedidos.relatorio'],
            ];
        @endphp
        
        @foreach($stats as $stat)
        <div class="col-6 col-lg-3 col-xl-3">
            <div class="admin-stat-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1 me-2">
                        <div class="stat-label">{{ $stat['label'] }}</div>
                        <div class="stat-number text-truncate">{{ $stat['value'] }}</div>
                        <small class="text-muted d-flex align-items-center gap-1 flex-wrap">
                            <i class="bi bi-arrow-up-circle text-success"></i>
                            {{ $stat['sub'] }} {{ $stat['subLabel'] }}
                        </small>
                    </div>
                    <div class="stat-icon bg-{{ $stat['color'] }} bg-opacity-10 text-{{ $stat['color'] }} flex-shrink-0">
                        <i class="bi bi-{{ $stat['icon'] }}"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <a href="{{ route($stat['route']) }}" class="text-decoration-none small">
                        <i class="bi bi-arrow-right-circle"></i> Gerenciar
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Resumo Rápido -->
    <div class="row g-2 g-md-3 g-lg-4 mb-4">
        <div class="col-sm-4 col-12">
            <div class="admin-card">
                <div class="card-body p-3 p-md-4">
                    <h6 class="text-muted small text-uppercase mb-1"><i class="bi bi-calendar3 me-2 text-primary"></i>Pedidos Hoje</h6>
                    <h3 class="fw-bold mb-1">{{ $pedidosHoje ?? 0 }}</h3>
                    <small class="text-muted">
                        <i class="bi bi-graph-up"></i>
                        Média: {{ number_format($mediaPedidosDia ?? 0, 1) }} dia
                    </small>
                </div>
            </div>
        </div>
        <div class="col-sm-4 col-12">
            <div class="admin-card">
                <div class="card-body p-3 p-md-4">
                    <h6 class="text-muted small text-uppercase mb-1"><i class="bi bi-cash-stack me-2 text-success"></i>Faturamento Hoje</h6>
                    <h3 class="fw-bold text-success mb-1">R$ {{ number_format($faturamentoDia ?? 0, 2, ',', '.') }}</h3>
                    <small class="text-muted">
                        <i class="bi bi-arrow-up-circle"></i>
                        {{ ($faturamentoDia ?? 0) > 0 ? 'Vendas hoje' : 'Nenhuma venda' }}
                    </small>
                </div>
            </div>
        </div>
        <div class="col-sm-4 col-12">
            <div class="admin-card">
                <div class="card-body p-3 p-md-4">
                    <h6 class="text-muted small text-uppercase mb-1"><i class="bi bi-graph-up me-2 text-info"></i>Ticket Médio</h6>
                    <h3 class="fw-bold text-info mb-1">
                        R$ {{ number_format(($totalPedidos ?? 0) > 0 ? ($faturamentoTotal ?? 0) / ($totalPedidos ?? 1) : 0, 2, ',', '.') }}
                    </h3>
                    <small class="text-muted">
                        <i class="bi bi-receipt"></i>
                        {{ $totalPedidos ?? 0 }} pedidos
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Status e Últimos Pedidos -->
    <div class="row g-2 g-md-3 g-lg-4">
        <!-- Status dos Pedidos -->
        <div class="col-md-6">
            <div class="admin-card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span><i class="bi bi-pie-chart me-2 text-primary"></i>Status dos Pedidos</span>
                    <span class="badge bg-secondary">{{ $totalPedidos ?? 0 }} total</span>
                </div>
                <div class="card-body p-3 p-md-4">
                    @php
                        $colors = ['pendente' => 'warning', 'pago' => 'info', 'processando' => 'primary', 'enviado' => 'success', 'entregue' => 'success', 'cancelado' => 'danger'];
                        $labels = ['pendente' => 'Pendente', 'pago' => 'Pago', 'processando' => 'Processando', 'enviado' => 'Enviado', 'entregue' => 'Entregue', 'cancelado' => 'Cancelado'];
                        $icons = ['pendente' => 'bi-clock', 'pago' => 'bi-credit-card', 'processando' => 'bi-arrow-repeat', 'enviado' => 'bi-truck', 'entregue' => 'bi-check-circle', 'cancelado' => 'bi-x-circle'];
                    @endphp
                    @forelse($pedidosStatus ?? [] as $status => $quantidade)
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="d-flex align-items-center gap-1 flex-wrap">
                                <i class="bi {{ $icons[$status] ?? 'bi-circle' }}"></i>
                                <span class="badge bg-{{ $colors[$status] ?? 'secondary' }}">{{ $labels[$status] ?? $status }}</span>
                            </span>
                            <span class="fw-bold">{{ $quantidade }}</span>
                        </div>
                        <div class="progress mb-3" style="height: 6px; background: #e9ecef;">
                            <div class="progress-bar bg-{{ $colors[$status] ?? 'secondary' }}" 
                                 style="width: {{ ($totalPedidos ?? 0) > 0 ? ($quantidade / max(($totalPedidos ?? 1), 1)) * 100 : 0 }}%">
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center py-3">Nenhum pedido encontrado.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Últimos Pedidos -->
        <div class="col-md-6">
            <div class="admin-card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span><i class="bi bi-clock-history me-2 text-warning"></i>Últimos Pedidos</span>
                    <a href="{{ route('admin.pedidos.index') }}" class="btn btn-sm btn-outline-primary">
                        Ver todos <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
                <div class="card-body p-0">
                    @if(isset($ultimosPedidos) && $ultimosPedidos->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover admin-table mb-0">
                                <thead>
                                    <tr>
                                        <th class="d-none d-sm-table-cell">Pedido</th>
                                        <th>Cliente</th>
                                        <th class="d-none d-sm-table-cell">Total</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($ultimosPedidos as $pedido)
                                        <tr>
                                            <td class="d-none d-sm-table-cell">
                                                <strong>#{{ $pedido->numero_pedido ?? $pedido->id }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $pedido->created_at->format('d/m/Y H:i') }}</small>
                                            </td>
                                            <td>
                                                <span class="d-sm-none">#{{ $pedido->numero_pedido ?? $pedido->id }} </span>
                                                {{ $pedido->user->name ?? 'N/A' }}
                                            </td>
                                            <td class="d-none d-sm-table-cell fw-bold">R$ {{ number_format($pedido->total, 2, ',', '.') }}</td>
                                            <td>
                                                <span class="badge-status bg-{{ $colors[$pedido->status] ?? 'secondary' }} text-white">
                                                    {{ $labels[$pedido->status] ?? $pedido->status }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center py-4">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            Nenhum pedido encontrado.
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Estoque Crítico -->
    <div class="row g-2 g-md-3 g-lg-4 mt-2">
        <div class="col-12">
            <div class="admin-card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span>
                        <i class="bi bi-exclamation-triangle me-2 text-warning"></i>
                        Produtos com Estoque Crítico
                    </span>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        @php
                            // ✅ CORRIGIDO: Verificar se são coleções ou inteiros
                            $totalEstoqueCritico = 0;
                            if (isset($estoqueBaixo) && isset($estoqueZero)) {
                                if ($estoqueBaixo instanceof \Illuminate\Support\Collection) {
                                    $totalEstoqueCritico = $estoqueBaixo->count() + $estoqueZero->count();
                                } else {
                                    $totalEstoqueCritico = $estoqueBaixo + $estoqueZero;
                                }
                            }
                        @endphp
                        <span class="badge bg-warning text-dark">
                            {{ $totalEstoqueCritico }} em alerta
                        </span>
                        <a href="{{ route('admin.produtos.index') }}?estoque=baixo" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-box-seam"></i> Ver todos
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    @php
                        // ✅ CORRIGIDO: Inicializar coleção vazia
                        $produtosCriticos = collect();
                        
                        if (isset($estoqueZero) && $estoqueZero instanceof \Illuminate\Support\Collection && $estoqueZero->count() > 0) {
                            $produtosCriticos = $produtosCriticos->merge($estoqueZero->map(function($p) {
                                $p->status_critico = 'Sem Estoque';
                                $p->classe_critico = 'table-danger';
                                $p->badge_critico = 'bg-danger';
                                $p->icone_critico = '🚨';
                                return $p;
                            }));
                        }
                        
                        if (isset($estoqueBaixo) && $estoqueBaixo instanceof \Illuminate\Support\Collection && $estoqueBaixo->count() > 0) {
                            $produtosCriticos = $produtosCriticos->merge($estoqueBaixo->map(function($p) {
                                $p->status_critico = 'Estoque Baixo';
                                $p->classe_critico = 'table-warning';
                                $p->badge_critico = 'bg-warning text-dark';
                                $p->icone_critico = '⚠️';
                                return $p;
                            }));
                        }
                        
                        $paginaAtual = request()->get('estoque_page', 1);
                        $porPagina = 5;
                        $totalItens = $produtosCriticos->count();
                        $produtosPaginados = $produtosCriticos->slice(($paginaAtual - 1) * $porPagina, $porPagina);
                        $totalPaginas = ceil($totalItens / $porPagina);
                    @endphp

                    @if($totalItens > 0)
                        <div class="table-responsive">
                            <table class="table table-hover admin-table mb-0">
                                <thead>
                                    <tr>
                                        <th class="d-none d-sm-table-cell" style="width: 5%;">#</th>
                                        <th style="min-width: 120px;">Produto</th>
                                        <th class="d-none d-md-table-cell">Categoria</th>
                                        <th style="min-width: 60px;">Qtd</th>
                                        <th style="min-width: 100px;">Status</th>
                                        <th style="min-width: 120px;">Ação</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($produtosPaginados as $produto)
                                        <tr class="{{ $produto->classe_critico }}">
                                            <td class="d-none d-sm-table-cell">{{ $produto->id }}</td>
                                            <td>
                                                <strong class="d-block d-sm-inline">{{ Str::limit($produto->descricao, 25) }}</strong>
                                                <br class="d-sm-none">
                                                <small class="text-muted d-block d-sm-inline">Ref: {{ $produto->referencia ?? '-' }}</small>
                                                <span class="d-sm-none d-block mt-1">
                                                    <span class="badge {{ $produto->badge_critico }}">
                                                        {{ $produto->icone_critico }} {{ $produto->status_critico }}
                                                    </span>
                                                </span>
                                            </td>
                                            <td class="d-none d-md-table-cell">{{ Str::limit($produto->categoria, 20) }}</td>
                                            <td>
                                                <span class="badge {{ $produto->badge_critico }} fs-6">
                                                    {{ $produto->quantidade }}
                                                </span>
                                            </td>
                                            <td class="d-none d-sm-table-cell">
                                                <span class="badge {{ $produto->badge_critico }}">
                                                    {{ $produto->icone_critico }} {{ $produto->status_critico }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1 flex-wrap">
                                                    <a href="{{ route('admin.produtos.edit', $produto->id) }}" 
                                                       class="btn btn-sm {{ $produto->quantidade <= 0 ? 'btn-outline-danger' : 'btn-outline-warning' }}">
                                                        <i class="bi bi-pencil"></i> 
                                                        <span class="d-none d-sm-inline">{{ $produto->quantidade <= 0 ? 'Repor' : 'Reabastecer' }}</span>
                                                    </a>
                                                    <a href="{{ route('admin.produtos.show', $produto->id) }}" 
                                                       class="btn btn-sm btn-outline-info">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Paginação do Estoque -->
                        @if($totalPaginas > 1)
                        <div class="p-3 border-top">
                            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
                                <div class="text-muted small">
                                    Mostrando <strong>{{ $produtosPaginados->count() }}</strong> 
                                    de <strong>{{ $totalItens }}</strong> produtos
                                </div>
                                <nav aria-label="Paginação do estoque crítico">
                                    <ul class="pagination pagination-sm mb-0 flex-wrap justify-content-center">
                                        <li class="page-item {{ $paginaAtual <= 1 ? 'disabled' : '' }}">
                                            <a class="page-link" href="{{ request()->fullUrlWithQuery(['estoque_page' => $paginaAtual - 1]) }}">
                                                &laquo;
                                            </a>
                                        </li>
                                        
                                        @php
                                            $intervalo = 2;
                                            $inicio = max(1, $paginaAtual - $intervalo);
                                            $fim = min($totalPaginas, $paginaAtual + $intervalo);
                                        @endphp
                                        
                                        @if($inicio > 1)
                                            <li class="page-item">
                                                <a class="page-link" href="{{ request()->fullUrlWithQuery(['estoque_page' => 1]) }}">1</a>
                                            </li>
                                            @if($inicio > 2)
                                                <li class="page-item disabled"><span class="page-link">...</span></li>
                                            @endif
                                        @endif
                                        
                                        @for($i = $inicio; $i <= $fim; $i++)
                                            <li class="page-item {{ $paginaAtual == $i ? 'active' : '' }}">
                                                <a class="page-link" href="{{ request()->fullUrlWithQuery(['estoque_page' => $i]) }}">
                                                    {{ $i }}
                                                </a>
                                            </li>
                                        @endfor
                                        
                                        @if($fim < $totalPaginas)
                                            @if($fim < $totalPaginas - 1)
                                                <li class="page-item disabled"><span class="page-link">...</span></li>
                                            @endif
                                            <li class="page-item">
                                                <a class="page-link" href="{{ request()->fullUrlWithQuery(['estoque_page' => $totalPaginas]) }}">
                                                    {{ $totalPaginas }}
                                                </a>
                                            </li>
                                        @endif
                                        
                                        <li class="page-item {{ $paginaAtual >= $totalPaginas ? 'disabled' : '' }}">
                                            <a class="page-link" href="{{ request()->fullUrlWithQuery(['estoque_page' => $paginaAtual + 1]) }}">
                                                &raquo;
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                        @endif
                        
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-check-circle-fill text-success fs-1 d-block mb-2"></i>
                            <p class="text-success fw-bold fs-5">✅ Todos os produtos com estoque OK!</p>
                            <small class="text-muted">Nenhum produto em estado crítico.</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Vendas Mensais e Top Clientes -->
    @if(isset($vendasMensais) && count($vendasMensais) > 0)
    <div class="row g-2 g-md-3 g-lg-4 mt-2">
        <div class="col-md-6">
            <div class="admin-card">
                <div class="card-header">
                    <span><i class="bi bi-graph-up-arrow me-2 text-primary"></i>Vendas Mensais</span>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div class="row g-2">
                        @foreach($vendasMensais as $venda)
                            <div class="col-4 col-lg-4 mb-2">
                                <div class="p-2 bg-light rounded text-center">
                                    <small class="text-muted d-block">{{ $venda['mes'] ?? $venda->mes }}</small>
                                    <strong class="text-primary small small-md">R$ {{ number_format($venda['total'] ?? $venda->total, 2, ',', '.') }}</strong>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-3 text-center">
                        <a href="{{ route('admin.pedidos.relatorio') }}" class="btn btn-sm btn-outline-primary w-100 w-sm-auto">
                            <i class="bi bi-file-earmark-bar-graph"></i> Ver relatório completo
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="admin-card">
                <div class="card-header">
                    <span><i class="bi bi-star me-2 text-warning"></i>Top Clientes</span>
                </div>
                <div class="card-body p-3 p-md-4">
                    @if(isset($clientesTop) && $clientesTop->count() > 0)
                        @foreach($clientesTop as $cliente)
                            <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded flex-wrap gap-2">
                                <div>
                                    <strong>{{ $cliente->name ?? 'Cliente #' . $cliente->id }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $cliente->total_pedidos ?? 0 }} pedidos</small>
                                </div>
                                <span class="badge bg-success fs-6">
                                    R$ {{ number_format($cliente->total_gasto ?? 0, 2, ',', '.') }}
                                </span>
                            </div>
                        @endforeach
                        <div class="mt-3 text-center">
                            <a href="{{ route('admin.usuarios.index') }}" class="btn btn-sm btn-outline-primary w-100 w-sm-auto">
                                <i class="bi bi-people"></i> Ver todos os usuários
                            </a>
                        </div>
                    @else
                        <p class="text-muted text-center py-3">Nenhum cliente com vendas ainda.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Ações Rápidas -->
    <div class="row g-2 g-md-3 g-lg-4 mt-2">
        <div class="col-12">
            <div class="admin-card">
                <div class="card-header">
                    <span><i class="bi bi-lightning me-2 text-warning"></i>Ações Rápidas</span>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex flex-wrap gap-2 align-items-center justify-content-start">
                        <!-- Produtos -->
                        <a href="{{ route('admin.produtos.create') }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-plus-circle"></i> <span class="d-none d-sm-inline">Novo Produto</span>
                            <span class="d-inline d-sm-none">Produto</span>
                        </a>
                        <a href="{{ route('admin.produtos.index') }}" class="btn btn-success btn-sm">
                            <i class="bi bi-grid"></i> <span class="d-none d-sm-inline">Gerenciar</span>
                        </a>
                        
                        <!-- Pedidos -->
                        <a href="{{ route('admin.pedidos.index') }}" class="btn btn-info btn-sm text-white">
                            <i class="bi bi-box-seam"></i> <span class="d-none d-sm-inline">Pedidos</span>
                        </a>
                        <a href="{{ route('admin.pedidos.relatorio') }}" class="btn btn-info btn-sm text-white">
                            <i class="bi bi-file-earmark-bar-graph"></i> <span class="d-none d-sm-inline">Relatórios</span>
                        </a>
                        
                        <!-- Banners -->
                        <a href="{{ route('admin.banners.index') }}" class="btn btn-dark btn-sm">
                            <i class="bi bi-images"></i> <span class="d-none d-sm-inline">Banners</span>
                        </a>
                        <a href="{{ route('admin.banners.create') }}" class="btn btn-outline-dark btn-sm">
                            <i class="bi bi-plus-circle"></i> <span class="d-none d-sm-inline">Novo Banner</span>
                        </a>
                        
                        <!-- Usuários -->
                        <a href="{{ route('admin.usuarios.index') }}" class="btn btn-secondary btn-sm">
                            <i class="bi bi-people"></i> <span class="d-none d-sm-inline">Usuários</span>
                        </a>
                        
                        <!-- Cache - Agrupado -->
                        <div class="btn-group btn-group-sm" role="group">
                            <a href="{{ route('admin.cache.clear') }}" class="btn btn-warning" 
                               onclick="event.preventDefault(); if(confirm('Deseja limpar todo o cache?')) document.getElementById('clear-cache-form').submit();">
                                <i class="bi bi-arrow-clockwise"></i> <span class="d-none d-sm-inline">Cache</span>
                            </a>
                            <a href="{{ route('admin.cache.clear-banners') }}" class="btn btn-warning" 
                               onclick="event.preventDefault(); if(confirm('Deseja limpar o cache dos banners?')) document.getElementById('clear-banners-form').submit();">
                                <i class="bi bi-arrow-clockwise"></i> <span class="d-none d-sm-inline">Banners</span>
                            </a>
                            <a href="{{ route('admin.cache.reload-banners') }}" class="btn btn-warning" 
                               onclick="event.preventDefault(); if(confirm('Deseja recarregar os banners?')) document.getElementById('reload-banners-form').submit();">
                                <i class="bi bi-arrow-repeat"></i> <span class="d-none d-sm-inline">Recarregar</span>
                            </a>
                        </div>
                        
                        <!-- Loja -->
                        <a href="{{ route('home') }}" class="btn btn-secondary btn-sm" target="_blank">
                            <i class="bi bi-eye"></i> <span class="d-none d-sm-inline">Ver Loja</span>
                        </a>
                    </div>
                    
                    <!-- Forms para cache -->
                    <form id="clear-cache-form" action="{{ route('admin.cache.clear') }}" method="GET" class="d-none"></form>
                    <form id="clear-banners-form" action="{{ route('admin.cache.clear-banners') }}" method="GET" class="d-none"></form>
                    <form id="reload-banners-form" action="{{ route('admin.cache.reload-banners') }}" method="GET" class="d-none"></form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CSS Otimizado -->
<style>
    /* ===== CARDS PRINCIPAIS ===== */
    .admin-stat-card {
        background: #fff;
        border-radius: 12px;
        padding: 1rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        border: 1px solid #e9ecef;
        transition: all 0.2s ease;
        height: 100%;
        min-height: 140px;
        display: flex;
        flex-direction: column;
    }
    
    .admin-stat-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }
    
    .stat-label {
        font-size: 0.7rem;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
    }
    
    .stat-number {
        font-size: 1.5rem;
        font-weight: 700;
        margin: 4px 0;
        line-height: 1.2;
    }
    
    .stat-icon {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
    }
    
    /* ===== CARDS GERAIS ===== */
    .admin-card {
        background: #fff;
        border-radius: 12px;
        border: 1px solid #e9ecef;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        height: 100%;
    }
    
    .admin-card .card-header {
        background: #f8f9fa;
        padding: 0.75rem 1rem;
        border-bottom: 1px solid #e9ecef;
        font-weight: 600;
        flex-shrink: 0;
        font-size: 0.9rem;
    }
    
    .admin-card .card-body {
        padding: 1rem;
        flex: 1;
    }
    
    /* ===== TABELAS ===== */
    .admin-table th {
        background: #f8f9fa;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6c757d;
        padding: 0.5rem 0.75rem;
        white-space: nowrap;
    }
    
    .admin-table td {
        vertical-align: middle;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
    }
    
    .badge-status {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 500;
        white-space: nowrap;
    }
    
    /* ===== PROGRESS ===== */
    .progress {
        border-radius: 4px;
        background-color: #e9ecef;
    }
    
    /* ===== RESPONSIVIDADE ===== */
    /* Mobile */
    @media (max-width: 575.98px) {
        .admin-stat-card {
            min-height: 110px;
            padding: 0.75rem;
        }
        
        .stat-number {
            font-size: 1.2rem;
        }
        
        .stat-icon {
            width: 32px;
            height: 32px;
            font-size: 1rem;
        }
        
        .admin-card .card-header {
            font-size: 0.8rem;
            padding: 0.5rem 0.75rem;
        }
        
        .admin-card .card-body {
            padding: 0.75rem;
        }
        
        .admin-table td, 
        .admin-table th {
            padding: 0.4rem 0.5rem;
            font-size: 0.75rem;
        }
        
        .badge-status {
            font-size: 0.65rem;
            padding: 3px 8px;
        }
        
        .btn-sm {
            font-size: 0.7rem;
            padding: 0.25rem 0.5rem;
        }
        
        .container-fluid {
            padding-left: 8px;
            padding-right: 8px;
        }
        
        .row {
            margin-left: -4px;
            margin-right: -4px;
        }
        
        .row > * {
            padding-left: 4px;
            padding-right: 4px;
        }
        
        .pagination-sm .page-link {
            padding: 0.2rem 0.5rem;
            font-size: 0.7rem;
        }
    }
    
    /* Tablet */
    @media (min-width: 576px) and (max-width: 767.98px) {
        .admin-stat-card {
            min-height: 130px;
        }
        
        .stat-number {
            font-size: 1.3rem;
        }
    }
    
    /* Desktop */
    @media (min-width: 768px) {
        .admin-stat-card {
            min-height: 160px;
            padding: 1.25rem;
        }
        
        .stat-number {
            font-size: 1.75rem;
        }
        
        .stat-icon {
            width: 48px;
            height: 48px;
            font-size: 1.5rem;
        }
        
        .admin-card .card-header {
            padding: 1rem 1.25rem;
        }
        
        .admin-card .card-body {
            padding: 1.25rem;
        }
    }
    
    /* Desktop Grande */
    @media (min-width: 1200px) {
        .stat-number {
            font-size: 2rem;
        }
    }
    
    /* ===== UTILIDADES ===== */
    .text-truncate {
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    
    .flex-wrap {
        flex-wrap: wrap !important;
    }
    
    .gap-1 { gap: 0.25rem !important; }
    .gap-2 { gap: 0.5rem !important; }
    .gap-3 { gap: 1rem !important; }
    .gap-4 { gap: 1.5rem !important; }
    
    /* ===== SCROLLBAR ===== */
    .table-responsive::-webkit-scrollbar {
        height: 6px;
    }
    
    .table-responsive::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    
    .table-responsive::-webkit-scrollbar-thumb {
        background: #c1c7cd;
        border-radius: 4px;
    }
    
    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #a8b0b8;
    }
</style>
@endsection