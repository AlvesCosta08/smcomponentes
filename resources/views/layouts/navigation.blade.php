<nav x-data="{ open: false }" class="nav-main">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center py-2 flex-wrap">
            <!-- Brand / Logo -->
            <a href="{{ route('home') }}" class="navbar-brand fw-bold text-white" style="font-size: 1.2rem; white-space: nowrap;">
                <i class="bi bi-plug" style="color: var(--color-secondary-500);"></i>
                <span class="d-none d-sm-inline">SM Componentes</span>
                <span class="d-inline d-sm-none">SM</span>
            </a>

            <!-- Links Desktop -->
            <div class="d-none d-md-flex gap-1">
                <a href="{{ route('home') }}" class="nav-link-custom {{ request()->routeIs('home') ? 'active' : '' }}" style="font-size: 0.85rem; padding: 6px 12px;">
                    <i class="bi bi-house"></i> Início
                </a>
                <a href="{{ route('produtos.index') }}" class="nav-link-custom {{ request()->routeIs('produtos.index') ? 'active' : '' }}" style="font-size: 0.85rem; padding: 6px 12px;">
                    <i class="bi bi-grid"></i> Produtos
                </a>
                @auth
                    @if(Auth::user()->hasRole('Admin') || Auth::user()->hasRole('Funcionario'))
                        <a href="{{ route('admin.dashboard') }}" class="nav-link-custom {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" style="font-size: 0.85rem; padding: 6px 12px;">
                            <i class="bi bi-speedometer2"></i> Admin
                        </a>
                    @endif
                    <a href="{{ route('profile.edit') }}" class="nav-link-custom {{ request()->routeIs('profile.edit') ? 'active' : '' }}" style="font-size: 0.85rem; padding: 6px 12px;">
                        <i class="bi bi-person"></i> Perfil
                    </a>
                @endauth
            </div>

            <!-- Desktop Right: Busca + Carrinho + User -->
            <div class="d-none d-md-flex align-items-center gap-2 flex-wrap" style="gap: 6px;">
                {{-- Busca --}}
                <form class="search-form d-flex" action="{{ route('produtos.buscar') }}" method="GET" role="search" style="max-width: 200px;">
                    <div class="input-group" style="flex-wrap: nowrap;">
                        <input class="form-control" type="search" name="q" placeholder="Buscar..." 
                               value="{{ request('q') }}" aria-label="Buscar produtos" 
                               style="width: 120px; font-size: 0.85rem; padding: 6px 12px; min-width: 80px;">
                        <button class="btn btn-search" type="submit" aria-label="Buscar" style="padding: 6px 12px; font-size: 0.85rem;">
                            <i class="bi bi-search" aria-hidden="true"></i>
                        </button>
                    </div>
                </form>

                {{-- Carrinho --}}
                <div class="cart-wrapper">
                    <a href="{{ route('carrinho.index') }}" class="cart-btn" aria-label="Ver carrinho" style="padding: 6px 12px; font-size: 0.85rem;">
                        <i class="bi bi-cart3" aria-hidden="true"></i>
                        <span class="cart-badge" id="cart-count" aria-live="polite" style="min-width: 20px; height: 20px; font-size: 0.65rem;">0</span>
                    </a>
                </div>

                {{-- User / Auth --}}
                @auth
                    <div class="d-flex align-items-center gap-1">
                        <span class="text-white small d-none d-xl-inline" style="font-size: 0.8rem; white-space: nowrap;">
                            <i class="bi bi-person-circle"></i> {{ Auth::user()->name }}
                        </span>
                        <form method="POST" action="{{ route('logout') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-action btn-action-outline" style="padding: 4px 12px; font-size: 0.8rem; border-width: 1px;">
                                <i class="bi bi-box-arrow-right"></i> Sair
                            </button>
                        </form>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="btn btn-action btn-action-outline" style="padding: 4px 12px; font-size: 0.8rem;">
                        <i class="bi bi-box-arrow-in-right" aria-hidden="true"></i> Entrar
                    </a>
                    <a href="{{ route('register') }}" class="btn btn-action btn-action-primary" style="padding: 4px 12px; font-size: 0.8rem;">
                        <i class="bi bi-person-plus" aria-hidden="true"></i> Cadastrar
                    </a>
                @endauth
            </div>

            <!-- Mobile Hamburger -->
            <button @click="open = !open" class="d-md-none btn btn-link text-white p-0" style="font-size: 1.5rem;">
                <i class="bi" :class="open ? 'bi-x-lg' : 'bi-list'"></i>
            </button>
        </div>

        <!-- Mobile Navigation -->
        <div x-show="open" x-transition:enter.duration.300ms.opacity class="d-md-none pb-3 mobile-menu" style="display: none;">
            <div class="d-flex flex-column gap-2">
                {{-- Busca Mobile --}}
                <form class="search-form d-flex w-100" action="{{ route('produtos.buscar') }}" method="GET" role="search">
                    <div class="input-group">
                        <input class="form-control" type="search" name="q" placeholder="Buscar produtos..." 
                               value="{{ request('q') }}" aria-label="Buscar produtos">
                        <button class="btn btn-search" type="submit" aria-label="Buscar">
                            <i class="bi bi-search" aria-hidden="true"></i>
                        </button>
                    </div>
                </form>

                {{-- Links Mobile --}}
                <a href="{{ route('home') }}" class="nav-link-custom {{ request()->routeIs('home') ? 'active' : '' }}">
                    <i class="bi bi-house"></i> Início
                </a>
                <a href="{{ route('produtos.index') }}" class="nav-link-custom {{ request()->routeIs('produtos.index') ? 'active' : '' }}">
                    <i class="bi bi-grid"></i> Produtos
                </a>
                
                @auth
                    @if(Auth::user()->hasRole('Admin') || Auth::user()->hasRole('Funcionario'))
                        <a href="{{ route('admin.dashboard') }}" class="nav-link-custom {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <i class="bi bi-speedometer2"></i> Admin
                        </a>
                    @endif
                    
                    <a href="{{ route('profile.edit') }}" class="nav-link-custom {{ request()->routeIs('profile.edit') ? 'active' : '' }}">
                        <i class="bi bi-person"></i> Perfil
                    </a>
                    
                    <hr class="my-2" style="border-color: rgba(255,255,255,0.1);">
                    
                    <span class="text-white-50 small px-2">
                        <i class="bi bi-person-circle"></i> {{ Auth::user()->name }}
                    </span>
                    
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="nav-link-custom text-danger border-0 bg-transparent w-100 text-start">
                            <i class="bi bi-box-arrow-right"></i> Sair
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="nav-link-custom">
                        <i class="bi bi-box-arrow-in-right"></i> Entrar
                    </a>
                    <a href="{{ route('register') }}" class="nav-link-custom">
                        <i class="bi bi-person-plus"></i> Registrar
                    </a>
                @endauth
            </div>
        </div>
    </div>
</nav>