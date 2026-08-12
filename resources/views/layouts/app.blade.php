<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    {{-- 🔒 FORÇAR HTTPS E CORRIGIR MIXED CONTENT --}}
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <meta http-equiv="Content-Security-Policy" content="block-all-mixed-content">
    
    <title>@yield('title', 'SM Componentes')</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    {{-- BARRA SUPERIOR DE CONTATO --}}
    <div class="top-bar d-none d-md-block bg-light py-2 border-bottom">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <i class="bi bi-telephone-fill text-primary"></i> (11) 99999-9999
                    <span class="divider">|</span>
                    <i class="bi bi-whatsapp text-success"></i> (11) 98888-8888
                </div>
                <div class="col-md-4 text-center">
                    <i class="bi bi-envelope text-primary"></i> contato@smcomponentes.com
                </div>
                <div class="col-md-4 text-end">
                    <i class="bi bi-clock text-primary"></i> Seg. a Sex. 9h às 17h
                </div>
            </div>
        </div>
    </div>

    {{-- NAVBAR PRINCIPAL --}}
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">
                <i class="bi bi-plug"></i> SM Componentes
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
                            <i class="bi bi-house"></i> Início
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('produtos.*') ? 'active' : '' }}" href="{{ route('produtos.index') }}">
                            <i class="bi bi-grid"></i> Produtos
                        </a>
                    </li>
                </ul>
                
                {{-- 🔥 Busca com HTTPS --}}
                <form class="d-flex me-3" action="{{ route('produtos.buscar') }}" method="GET">
                    <div class="input-group input-group-sm">
                        <input class="form-control" type="search" name="q" placeholder="Buscar produtos..." value="{{ request('q') }}">
                        <button class="btn btn-primary" type="submit">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </form>
                
                {{-- Carrinho --}}
                <a href="{{ route('carrinho.index') }}" class="btn btn-outline-light me-2 position-relative rounded-pill">
                    <i class="bi bi-cart3"></i>
                    <span class="cart-badge" id="cart-count">0</span>
                </a>
                
                {{-- Autenticação --}}
                @auth
                    <div class="dropdown">
                        <button class="btn btn-outline-light dropdown-toggle rounded-pill" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> {{ Str::limit(Auth::user()->name, 12) }}
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                            @if(Auth::user()->isAdmin() || Auth::user()->isFuncionario())
                                <li>
                                    <a class="dropdown-item" href="{{ route('dashboard') }}">
                                        <i class="bi bi-speedometer2"></i> Dashboard
                                    </a>
                                </li>
                            @endif
                            <li>
                                <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                    <i class="bi bi-gear"></i> Perfil
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="bi bi-box-arrow-right"></i> Sair
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline-light me-2 rounded-pill">
                        <i class="bi bi-box-arrow-in-right"></i> Entrar
                    </a>
                    <a href="{{ route('register') }}" class="btn btn-primary rounded-pill">
                        <i class="bi bi-person-plus"></i> Cadastrar
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    {{-- CONTEÚDO PRINCIPAL --}}
    <main>
        @yield('content')
    </main>

    {{-- FOOTER --}}
    <footer class="footer mt-5 py-4 bg-light border-top">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <h5><i class="bi bi-plug text-primary"></i> SM Componentes</h5>
                    <p class="text-muted small">Sua loja de componentes eletrônicos desde 2020. Qualidade e confiança em cada produto.</p>
                </div>
                <div class="col-md-4">
                    <h5>Links Rápidos</h5>
                    <ul class="list-unstyled small">
                        <li><a href="{{ route('produtos.index') }}" class="text-decoration-none">Todos os Produtos</a></li>
                        <li><a href="#" class="text-decoration-none">Sobre Nós</a></li>
                        <li><a href="#" class="text-decoration-none">Política de Privacidade</a></li>
                        <li><a href="#" class="text-decoration-none">Termos de Uso</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Contato</h5>
                    <ul class="list-unstyled small">
                        <li><i class="bi bi-envelope text-primary"></i> contato@smcomponentes.com</li>
                        <li><i class="bi bi-telephone text-primary"></i> (11) 99999-9999</li>
                        <li><i class="bi bi-whatsapp text-success"></i> (11) 98888-8888</li>
                    </ul>
                </div>
            </div>
            <hr class="my-4">
            <p class="text-center text-muted small mb-0">
                &copy; {{ date('Y') }} SM Componentes. Todos os direitos reservados.
            </p>
        </div>
    </footer>

    {{-- BOTÕES FLUTUANTES --}}
    <!-- WhatsApp -->
    <a href="https://wa.me/5511999999999?text=Olá!%20Vim%20pelo%20site%20SM%20Componentes%20e%20gostaria%20de%20mais%20informações." 
       target="_blank" 
       rel="noopener noreferrer"
       class="whatsapp-float" 
       style="position: fixed; bottom: 30px; right: 30px; z-index: 1000; text-decoration: none;"
       title="Fale conosco no WhatsApp">
        <div style="background: #25d366; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 6px 24px rgba(37, 211, 102, 0.4); transition: all 0.3s ease;">
            <i class="bi bi-whatsapp" style="font-size: 2.2rem; color: #fff;"></i>
        </div>
    </a>

    <!-- Instagram -->
    <a href="https://instagram.com/smcomponentes" 
       target="_blank" 
       rel="noopener noreferrer"
       class="instagram-float" 
       style="position: fixed; bottom: 100px; right: 30px; z-index: 1000; text-decoration: none;"
       title="Siga-nos no Instagram">
        <div style="background: linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888); width: 55px; height: 55px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 6px 24px rgba(220, 39, 67, 0.3); transition: all 0.3s ease;">
            <i class="bi bi-instagram" style="font-size: 2rem; color: #fff;"></i>
        </div>
    </a>

    <style>
        /* Botões flutuantes */
        .whatsapp-float:hover,
        .instagram-float:hover {
            transform: scale(1.1) !important;
        }
        
        .whatsapp-float {
            animation: pulse-whatsapp 2s infinite;
        }
        
        .instagram-float {
            animation: pulse-instagram 2s infinite 0.5s;
        }
        
        @keyframes pulse-whatsapp {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        @keyframes pulse-instagram {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        /* Contador do carrinho */
        .cart-badge {
            background: #dc3545;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.7rem;
            position: absolute;
            top: -5px;
            right: -5px;
            min-width: 18px;
            text-align: center;
        }
        
        /* Responsivo */
        @media (max-width: 768px) {
            .whatsapp-float div {
                width: 50px !important;
                height: 50px !important;
            }
            .whatsapp-float i {
                font-size: 1.8rem !important;
            }
            .instagram-float div {
                width: 45px !important;
                height: 45px !important;
            }
            .instagram-float i {
                font-size: 1.6rem !important;
            }
            .whatsapp-float { bottom: 20px; right: 20px; }
            .instagram-float { bottom: 85px; right: 20px; }
        }

        @media (max-width: 576px) {
            .whatsapp-float div {
                width: 48px !important;
                height: 48px !important;
            }
            .whatsapp-float i {
                font-size: 1.6rem !important;
            }
            .instagram-float div {
                width: 42px !important;
                height: 42px !important;
            }
            .instagram-float i {
                font-size: 1.4rem !important;
            }
            .whatsapp-float { bottom: 16px; right: 16px; }
            .instagram-float { bottom: 76px; right: 16px; }
        }

        /* Footer */
        .footer a {
            color: #6c757d;
            transition: color 0.2s;
        }
        .footer a:hover {
            color: #0d6efd;
        }
    </style>

    <script>
        function updateCartCount() {
            fetch('{{ route("carrinho.count") }}')
                .then(response => response.json())
                .then(data => {
                    const badge = document.getElementById('cart-count');
                    if (badge) {
                        badge.textContent = data.count || 0;
                    }
                })
                .catch(() => {});
        }
        
        // Atualizar a cada 30 segundos
        setInterval(updateCartCount, 30000);
        
        // Atualizar ao carregar a página
        document.addEventListener('DOMContentLoaded', updateCartCount);
    </script>
    
    @stack('scripts')
</body>
</html>