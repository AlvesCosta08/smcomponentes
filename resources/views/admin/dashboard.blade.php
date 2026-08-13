@extends('layouts.app')

@section('title', 'Dashboard - Admin')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-0">📊 Dashboard</h1>
            <small class="text-muted">Visão geral da sua loja</small>
        </div>
        <div>
            <span class="badge bg-primary fs-6">
                <i class="bi bi-clock me-1"></i> 
                {{ now()->format('d/m/Y H:i') }}
            </span>
        </div>
    </div>

    <!-- Cards de Estatísticas - TAMANHOS UNIFORMES -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="admin-stat-card h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Usuários</div>
                        <div class="stat-number">{{ $totalUsuarios ?? 0 }}</div>
                        <small class="text-muted">
                            <i class="bi bi-arrow-up-circle text-success"></i>
                            +{{ $novosUsuariosHoje ?? 0 }} hoje
                        </small>
                    </div>
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary flex-shrink-0">
                        <i class="bi bi-people-fill"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <a href="{{ route('admin.usuarios.index') }}" class="text-decoration-none small">
                        <i class="bi bi-arrow-right-circle"></i> Gerenciar usuários
                    </a>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="admin-stat-card h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Produtos</div>
                        <div class="stat-number">{{ $totalProdutos ?? 0 }}</div>
                        <small class="text-muted">
                            <i class="bi bi-check-circle text-success"></i>
                            {{ $produtosAtivos ?? 0 }} ativos
                        </small>
                    </div>
                    <div class="stat-icon bg-success bg-opacity-10 text-success flex-shrink-0">
                        <i class="bi bi-box-seam-fill"></i>
                    </div>
                </div>
                @if(($estoqueBaixoCount ?? 0) > 0)
                    <div class="mt-2">
                        <span class="badge bg-warning text-dark">
                            <i class="bi bi-exclamation-triangle"></i>
                            {{ $estoqueBaixoCount }} em alerta
                        </span>
                    </div>
                @endif
                <div class="mt-2">
                    <a href="{{ route('admin.produtos.index') }}" class="text-decoration-none small">
                        <i class="bi bi-arrow-right-circle"></i> Gerenciar produtos
                    </a>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="admin-stat-card h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Pedidos</div>
                        <div class="stat-number">{{ $totalPedidos ?? 0 }}</div>
                        <small class="text-muted">
                            <i class="bi bi-clock-history text-warning"></i>
                            {{ $pedidosStatus['pendente'] ?? 0 }} pendentes
                        </small>
                    </div>
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning flex-shrink-0">
                        <i class="bi bi-cart-fill"></i>
                    </div>
                </div>
                @if(($pedidosPendentesCount ?? 0) > 0)
                    <div class="mt-2">
                        <span class="badge bg-danger">
                            <i class="bi bi-bell-fill"></i>
                            {{ $pedidosPendentesCount }} aguardando
                        </span>
                    </div>
                @endif
                <div class="mt-2">
                    <a href="{{ route('admin.pedidos.index') }}" class="text-decoration-none small">
                        <i class="bi bi-arrow-right-circle"></i> Gerenciar pedidos
                    </a>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="admin-stat-card h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="stat-label">Faturamento Total</div>
                        <div class="stat-number">R$ {{ number_format($faturamentoTotal ?? 0, 2, ',', '.') }}</div>
                        <small class="text-muted">
                            <i class="bi bi-calendar-month"></i>
                            Mês: R$ {{ number_format($faturamentoMes ?? 0, 2, ',', '.') }}
                        </small>
                    </div>
                    <div class="stat-icon bg-info bg-opacity-10 text-info flex-shrink-0">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <a href="{{ route('admin.pedidos.relatorio') }}" class="text-decoration-none small">
                        <i class="bi bi-arrow-right-circle"></i> Ver relatórios
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumo Rápido - CARDS UNIFORMES -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="admin-card h-100">
                <div class="card-body">
                    <h6 class="text-muted"><i class="bi bi-calendar3 me-2 text-primary"></i>Pedidos Hoje</h6>
                    <h3 class="fw-bold">{{ $pedidosHoje ?? 0 }}</h3>
                    <small class="text-muted">
                        <i class="bi bi-graph-up"></i>
                        Média: {{ number_format($mediaPedidosDia ?? 0, 1) }} por dia
                    </small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="admin-card h-100">
                <div class="card-body">
                    <h6 class="text-muted"><i class="bi bi-cash-stack me-2 text-success"></i>Faturamento Hoje</h6>
                    <h3 class="fw-bold text-success">R$ {{ number_format($faturamentoDia ?? 0, 2, ',', '.') }}</h3>
                    <small class="text-muted">
                        <i class="bi bi-arrow-up-circle"></i>
                        {{ ($faturamentoDia ?? 0) > 0 ? 'Vendas realizadas hoje' : 'Nenhuma venda hoje' }}
                    </small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="admin-card h-100">
                <div class="card-body">
                    <h6 class="text-muted"><i class="bi bi-graph-up me-2 text-info"></i>Ticket Médio</h6>
                    <h3 class="fw-bold text-info">
                        R$ {{ number_format(($totalPedidos ?? 0) > 0 ? ($faturamentoTotal ?? 0) / ($totalPedidos ?? 1) : 0, 2, ',', '.') }}
                    </h3>
                    <small class="text-muted">
                        <i class="bi bi-receipt"></i>
                        {{ $totalPedidos ?? 0 }} pedidos realizados
                    </small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Status dos Pedidos -->
        <div class="col-md-6">
            <div class="admin-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-pie-chart me-2 text-primary"></i>Status dos Pedidos</span>
                    <span class="badge bg-secondary">{{ $totalPedidos ?? 0 }} total</span>
                </div>
                <div class="card-body">
                    @php
                        $colors = ['pendente' => 'warning', 'pago' => 'info', 'processando' => 'primary', 'enviado' => 'success', 'entregue' => 'success', 'cancelado' => 'danger'];
                        $labels = ['pendente' => 'Pendente', 'pago' => 'Pago', 'processando' => 'Processando', 'enviado' => 'Enviado', 'entregue' => 'Entregue', 'cancelado' => 'Cancelado'];
                        $icons = ['pendente' => 'bi-clock', 'pago' => 'bi-credit-card', 'processando' => 'bi-arrow-repeat', 'enviado' => 'bi-truck', 'entregue' => 'bi-check-circle', 'cancelado' => 'bi-x-circle'];
                    @endphp
                    @forelse($pedidosStatus ?? [] as $status => $quantidade)
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span>
                                <i class="bi {{ $icons[$status] ?? 'bi-circle' }} me-1"></i>
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
            <div class="admin-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
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
                                        <th>Pedido</th>
                                        <th>Cliente</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($ultimosPedidos as $pedido)
                                        <tr>
                                            <td>
                                                <strong>#{{ $pedido->numero_pedido ?? $pedido->id }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $pedido->created_at->format('d/m/Y H:i') }}</small>
                                            </td>
                                            <td>{{ $pedido->user->name ?? 'N/A' }}</td>
                                            <td class="fw-bold">R$ {{ number_format($pedido->total, 2, ',', '.') }}</td>
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

    <!-- Estoque Crítico com Paginação -->
    <div class="row g-4 mt-2">
        <div class="col-12">
            <div class="admin-card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                    <span>
                        <i class="bi bi-exclamation-triangle me-2 text-warning"></i>
                        Produtos com Estoque Crítico
                    </span>
                    <div>
                        <span class="badge bg-warning text-dark me-2">
                            {{ ($estoqueBaixo->count() ?? 0) + ($estoqueZero->count() ?? 0) }} em alerta
                        </span>
                        <a href="{{ route('admin.produtos.index') }}?estoque=baixo" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-box-seam"></i> Ver todos
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    @php
                        // Combinar produtos críticos
                        $produtosCriticos = collect();
                        
                        if(isset($estoqueZero) && $estoqueZero->count() > 0) {
                            $produtosCriticos = $produtosCriticos->merge($estoqueZero->map(function($p) {
                                $p->status_critico = 'Sem Estoque';
                                $p->classe_critico = 'table-danger';
                                $p->badge_critico = 'bg-danger';
                                $p->icone_critico = '🚨';
                                return $p;
                            }));
                        }
                        
                        if(isset($estoqueBaixo) && $estoqueBaixo->count() > 0) {
                            $produtosCriticos = $produtosCriticos->merge($estoqueBaixo->map(function($p) {
                                $p->status_critico = 'Estoque Baixo';
                                $p->classe_critico = 'table-warning';
                                $p->badge_critico = 'bg-warning text-dark';
                                $p->icone_critico = '⚠️';
                                return $p;
                            }));
                        }
                        
                        // Paginar (5 itens por página)
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
                                        <th style="width: 5%;">#</th>
                                        <th style="width: 25%;">Produto</th>
                                        <th style="width: 18%;">Categoria</th>
                                        <th style="width: 10%;">Qtd</th>
                                        <th style="width: 22%;">Status</th>
                                        <th style="width: 20%;">Ação</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($produtosPaginados as $produto)
                                        <tr class="{{ $produto->classe_critico }}">
                                            <td>{{ $produto->id }}</td>
                                            <td>
                                                <strong>{{ Str::limit($produto->descricao, 30) }}</strong>
                                                <br>
                                                <small class="text-muted">Ref: {{ $produto->referencia ?? '-' }}</small>
                                            </td>
                                            <td>{{ Str::limit($produto->categoria, 20) }}</td>
                                            <td>
                                                <span class="badge {{ $produto->badge_critico }} fs-6">
                                                    {{ $produto->quantidade }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge {{ $produto->badge_critico }}">
                                                    {{ $produto->icone_critico }} {{ $produto->status_critico }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <a href="{{ route('admin.produtos.edit', $produto->id) }}" 
                                                       class="btn btn-sm {{ $produto->quantidade <= 0 ? 'btn-outline-danger' : 'btn-outline-warning' }}">
                                                        <i class="bi bi-pencil"></i> 
                                                        {{ $produto->quantidade <= 0 ? 'Repor' : 'Reabastecer' }}
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
                        
                        <!-- PAGINAÇÃO DO ESTOQUE CRÍTICO -->
                        @if($totalPaginas > 1)
                        <div class="p-3 border-top">
                            <div class="d-flex justify-content-between align-items-center flex-wrap">
                                <div class="text-muted small">
                                    Mostrando <strong>{{ $produtosPaginados->count() }}</strong> 
                                    de <strong>{{ $totalItens }}</strong> produtos em alerta
                                </div>
                                <nav aria-label="Paginação do estoque crítico">
                                    <ul class="pagination pagination-sm mb-0">
                                        {{-- Botão Anterior --}}
                                        <li class="page-item {{ $paginaAtual <= 1 ? 'disabled' : '' }}">
                                            <a class="page-link" href="{{ request()->fullUrlWithQuery(['estoque_page' => $paginaAtual - 1]) }}" 
                                               aria-label="Anterior">
                                                <span aria-hidden="true">&laquo;</span>
                                            </a>
                                        </li>
                                        
                                        {{-- Números das páginas --}}
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
                                        
                                        {{-- Botão Próximo --}}
                                        <li class="page-item {{ $paginaAtual >= $totalPaginas ? 'disabled' : '' }}">
                                            <a class="page-link" href="{{ request()->fullUrlWithQuery(['estoque_page' => $paginaAtual + 1]) }}" 
                                               aria-label="Próximo">
                                                <span aria-hidden="true">&raquo;</span>
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
    <div class="row g-4 mt-2">
        <div class="col-md-6">
            <div class="admin-card h-100">
                <div class="card-header">
                    <span><i class="bi bi-graph-up-arrow me-2 text-primary"></i>Vendas Mensais</span>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        @foreach($vendasMensais as $venda)
                            <div class="col-4 col-lg-4 mb-2">
                                <div class="p-2 bg-light rounded text-center">
                                    <small class="text-muted d-block">{{ $venda['mes'] }}</small>
                                    <strong class="text-primary">R$ {{ number_format($venda['total'], 2, ',', '.') }}</strong>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-3 text-center">
                        <a href="{{ route('admin.pedidos.relatorio') }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-file-earmark-bar-graph"></i> Ver relatório completo
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="admin-card h-100">
                <div class="card-header">
                    <span><i class="bi bi-star me-2 text-warning"></i>Top Clientes</span>
                </div>
                <div class="card-body">
                    @if(isset($clientesTop) && $clientesTop->count() > 0)
                        @foreach($clientesTop as $cliente)
                            <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded">
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
                            <a href="{{ route('admin.usuarios.index') }}" class="btn btn-sm btn-outline-primary">
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

    <!-- Ações Rápidas - BOTÕES ALINHADOS -->
    <div class="row g-4 mt-2">
        <div class="col-12">
            <div class="admin-card">
                <div class="card-header">
                    <span><i class="bi bi-lightning me-2 text-warning"></i>Ações Rápidas</span>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <!-- Produtos -->
                        <a href="{{ route('admin.produtos.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Novo Produto
                        </a>
                        <a href="{{ route('admin.produtos.index') }}" class="btn btn-success">
                            <i class="bi bi-grid"></i> Gerenciar Produtos
                        </a>
                        
                        <!-- Pedidos -->
                        <a href="{{ route('admin.pedidos.index') }}" class="btn btn-info text-white">
                            <i class="bi bi-box-seam"></i> Ver Pedidos
                        </a>
                        <a href="{{ route('admin.pedidos.relatorio') }}" class="btn btn-info text-white">
                            <i class="bi bi-file-earmark-bar-graph"></i> Relatórios
                        </a>
                        
                        <!-- Banners -->
                        <a href="{{ route('admin.banners.index') }}" class="btn btn-dark">
                            <i class="bi bi-images"></i> Gerenciar Banners
                        </a>
                        <a href="{{ route('admin.banners.create') }}" class="btn btn-outline-dark">
                            <i class="bi bi-plus-circle"></i> Novo Banner
                        </a>
                        
                        <!-- Usuários -->
                        <a href="{{ route('admin.usuarios.index') }}" class="btn btn-secondary">
                            <i class="bi bi-people"></i> Usuários
                        </a>
                        
                        <!-- Cache -->
                        <a href="{{ route('admin.cache.clear') }}" class="btn btn-warning" 
                           onclick="event.preventDefault(); if(confirm('Deseja limpar todo o cache?')) document.getElementById('clear-cache-form').submit();">
                            <i class="bi bi-arrow-clockwise"></i> Limpar Cache
                        </a>
                        <form id="clear-cache-form" action="{{ route('admin.cache.clear') }}" method="GET" class="d-none"></form>
                        
                        <a href="{{ route('admin.cache.clear-banners') }}" class="btn btn-warning" 
                           onclick="event.preventDefault(); if(confirm('Deseja limpar o cache dos banners?')) document.getElementById('clear-banners-form').submit();">
                            <i class="bi bi-arrow-clockwise"></i> Limpar Cache Banners
                        </a>
                        <form id="clear-banners-form" action="{{ route('admin.cache.clear-banners') }}" method="GET" class="d-none"></form>
                        
                        <a href="{{ route('admin.cache.reload-banners') }}" class="btn btn-warning" 
                           onclick="event.preventDefault(); if(confirm('Deseja recarregar os banners?')) document.getElementById('reload-banners-form').submit();">
                            <i class="bi bi-arrow-repeat"></i> Recarregar Banners
                        </a>
                        <form id="reload-banners-form" action="{{ route('admin.cache.reload-banners') }}" method="GET" class="d-none"></form>
                        
                        <!-- Loja -->
                        <a href="{{ route('home') }}" class="btn btn-secondary" target="_blank">
                            <i class="bi bi-eye"></i> Ver Loja
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CSS Adicional -->
<style>
    .admin-stat-card {
        background: #fff;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        border: 1px solid #e9ecef;
        transition: all 0.2s ease;
        display: flex;
        flex-direction: column;
        height: 100%;
        min-height: 180px;
    }
    .admin-stat-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }
    .stat-label {
        font-size: 0.85rem;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        margin: 4px 0;
    }
    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
    }
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
        padding: 12px 20px;
        border-bottom: 1px solid #e9ecef;
        font-weight: 600;
        flex-shrink: 0;
    }
    .admin-card .card-body {
        padding: 20px;
        flex: 1;
    }
    .admin-table th {
        background: #f8f9fa;
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6c757d;
    }
    .admin-table td {
        vertical-align: middle;
        padding: 10px 12px;
    }
    .badge-status {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
    }
    
    /* Responsivo */
    @media (max-width: 768px) {
        .stat-number {
            font-size: 1.5rem;
        }
        .admin-stat-card {
            min-height: 140px;
            padding: 15px;
        }
        .d-flex.flex-wrap.gap-2 {
            gap: 8px !important;
        }
        .d-flex.flex-wrap.gap-2 .btn {
            width: 100%;
            justify-content: center;
        }
    }
    
    @media (max-width: 576px) {
        .admin-stat-card {
            min-height: 120px;
            padding: 12px;
        }
        .stat-number {
            font-size: 1.2rem;
        }
        .stat-icon {
            width: 36px;
            height: 36px;
            font-size: 1rem;
        }
    }
</style>
@endsection