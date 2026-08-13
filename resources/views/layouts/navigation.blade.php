<nav x-data="{ open: false }" class="nav-main">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center py-2">
            <!-- Desktop Navigation -->
            <div class="d-none d-md-flex gap-2">
                <a href="{{ route('home') }}" class="nav-link-custom {{ request()->routeIs('home') ? 'active' : '' }}">
                    <i class="bi bi-house"></i> Início
                </a>
                <a href="{{ route('produtos.index') }}" class="nav-link-custom {{ request()->routeIs('produtos.index') ? 'active' : '' }}">
                    <i class="bi bi-grid"></i> Produtos
                </a>
                @auth
                    <a href="{{ route('dashboard') }}" class="nav-link-custom {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                    <a href="{{ route('profile.edit') }}" class="nav-link-custom {{ request()->routeIs('profile.edit') ? 'active' : '' }}">
                        <i class="bi bi-person"></i> Perfil
                    </a>
                @endauth
            </div>

            <!-- User Info & Logout (Desktop) -->
            <div class="d-none d-md-flex align-items-center gap-3">
                @auth
                    <span class="text-muted small">
                        <i class="bi bi-person-circle"></i> {{ Auth::user()->name }}
                    </span>
                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-box-arrow-right"></i> Sair
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-box-arrow-in-right"></i> Entrar
                    </a>
                    <a href="{{ route('register') }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-person-plus"></i> Registrar
                    </a>
                @endauth
            </div>

            <!-- Mobile Hamburger -->
            <button @click="open = !open" class="d-md-none btn btn-link text-dark p-0" style="font-size: 1.5rem;">
                <i class="bi" :class="open ? 'bi-x-lg' : 'bi-list'"></i>
            </button>
        </div>

        <!-- Mobile Navigation -->
        <div x-show="open" x-transition:enter.duration.300ms.opacity class="d-md-none pb-3" style="display: none;">
            <div class="d-flex flex-column gap-2">
                <a href="{{ route('home') }}" class="nav-link-custom {{ request()->routeIs('home') ? 'active' : '' }}">
                    <i class="bi bi-house"></i> Início
                </a>
                <a href="{{ route('produtos.index') }}" class="nav-link-custom {{ request()->routeIs('produtos.index') ? 'active' : '' }}">
                    <i class="bi bi-grid"></i> Produtos
                </a>
                @auth
                    <a href="{{ route('dashboard') }}" class="nav-link-custom {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                    <a href="{{ route('profile.edit') }}" class="nav-link-custom {{ request()->routeIs('profile.edit') ? 'active' : '' }}">
                        <i class="bi bi-person"></i> Perfil
                    </a>
                    <hr class="my-2">
                    <span class="text-muted small px-2">
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
