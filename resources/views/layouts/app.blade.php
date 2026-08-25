<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="SM Componentes - Sua loja de componentes eletrônicos com qualidade e confiança">
    <meta name="theme-color" content="#2563eb">
    
    {{-- 🔒 FORÇAR HTTPS E CORRIGIR MIXED CONTENT --}}
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    
    <title>@yield('title', config('app.name', 'SM Componentes'))</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">

    {{-- Font Awesome 6 --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    {{-- Bootstrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    {{-- Scripts --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        /* ============================================
           VARIÁVEIS DE COR E TEMA
           ============================================ */
        :root {
            --color-primary-50: #eff6ff;
            --color-primary-100: #dbeafe;
            --color-primary-200: #bfdbfe;
            --color-primary-300: #93c5fd;
            --color-primary-400: #60a5fa;
            --color-primary-500: #3b82f6;
            --color-primary-600: #2563eb;
            --color-primary-700: #1d4ed8;
            --color-primary-800: #1e40af;
            --color-primary-900: #1e3a8a;

            --color-secondary-500: #f97316;
            --color-secondary-600: #ea580c;

            --color-success-500: #22c55e;
            --color-danger-500: #ef4444;
            --color-gray-50: #f8fafc;
            --color-gray-100: #f1f5f9;
            --color-gray-200: #e2e8f0;
            --color-gray-300: #cbd5e1;
            --color-gray-400: #94a3b8;
            --color-gray-500: #64748b;
            --color-gray-600: #475569;
            --color-gray-700: #334155;
            --color-gray-800: #1e293b;
            --color-gray-900: #0f172a;

            --gradient-primary: linear-gradient(135deg, #1e40af 0%, #2563eb 50%, #3b82f6 100%);
            --gradient-secondary: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            --gradient-success: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            --gradient-dark: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            --gradient-hero: linear-gradient(135deg, #0f172a 0%, #1e293b 40%, #1e40af 100%);

            --font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            --border-radius: 12px;
            --border-radius-sm: 8px;
            --border-radius-full: 50px;
        }

        /* ============================================
           RESET E BASE
           ============================================ */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: var(--font-family);
            background: #ffffff;
            color: var(--color-gray-800);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* ============================================
           TOP BAR
           ============================================ */
        .top-bar {
            background: linear-gradient(135deg, var(--color-gray-50) 0%, var(--color-gray-100) 100%);
            border-bottom: 2px solid var(--color-primary-100);
            padding: 8px 0;
            font-size: 0.875rem;
            color: var(--color-gray-600);
            position: relative;
            overflow: hidden;
        }

        .top-bar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--gradient-primary);
        }

        .top-bar .divider {
            color: var(--color-gray-300);
            margin: 0 12px;
        }

        .top-bar i {
            margin-right: 6px;
        }

        .top-bar a {
            color: var(--color-gray-600);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .top-bar a:hover {
            color: var(--color-primary-600);
        }

        /* ============================================
           NAVBAR
           ============================================ */
        .nav-main {
            background: var(--gradient-primary);
            box-shadow: 0 4px 20px rgba(30, 58, 138, 0.3);
            position: sticky;
            top: 0;
            z-index: 1050;
            border-bottom: 3px solid transparent;
            border-image: linear-gradient(90deg, var(--color-secondary-500), #8b5cf6) 1;
        }

        .nav-link-custom {
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            padding: 8px 16px;
            border-radius: var(--border-radius-sm);
            transition: all 0.3s ease;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: transparent;
            border: none;
            cursor: pointer;
        }

        .nav-link-custom:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.12);
            transform: translateY(-1px);
        }

        .nav-link-custom.active {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.18);
            position: relative;
        }

        .nav-link-custom.active::after {
            content: '';
            position: absolute;
            bottom: 4px;
            left: 50%;
            transform: translateX(-50%);
            width: 24px;
            height: 3px;
            background: var(--color-secondary-500);
            border-radius: 3px;
            box-shadow: 0 0 20px rgba(249, 115, 22, 0.4);
        }

        .nav-link-custom i {
            font-size: 1.1rem;
        }

        .nav-link-custom.text-danger:hover {
            background: rgba(239, 68, 68, 0.15);
            color: #fca5a5;
        }

        .nav-main .mobile-menu {
            background: rgba(15, 23, 42, 0.98);
            backdrop-filter: blur(20px);
            border-radius: var(--border-radius);
            padding: 16px;
            margin-top: 8px;
            border: 1px solid rgba(255, 255, 255, 0.06);
        }

        .nav-main .mobile-menu .nav-link-custom {
            padding: 12px 16px;
            border-radius: var(--border-radius-sm);
            width: 100%;
        }

        .nav-main .mobile-menu .nav-link-custom:hover {
            background: rgba(255, 255, 255, 0.08);
        }

        .nav-main .mobile-menu .nav-link-custom.active::after {
            display: none;
        }

        .nav-main .btn-link {
            color: #ffffff !important;
            transition: transform 0.3s ease;
        }

        .nav-main .btn-link:hover {
            transform: scale(1.1);
        }

        .navbar-brand {
            white-space: nowrap;
            font-size: 1.3rem;
        }

        /* ============================================
           BUSCA
           ============================================ */
        .search-form .form-control {
            border-radius: var(--border-radius-full) 0 0 var(--border-radius-full);
            border: 2px solid rgba(255, 255, 255, 0.2);
            background: rgba(255, 255, 255, 0.08);
            color: #ffffff;
            padding: 8px 20px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            width: 200px;
            backdrop-filter: blur(10px);
        }

        .search-form .form-control::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .search-form .form-control:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: var(--color-secondary-500);
            box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.15);
            width: 260px;
            color: #ffffff;
        }

        .search-form .btn-search {
            border-radius: 0 var(--border-radius-full) var(--border-radius-full) 0;
            padding: 8px 20px;
            background: var(--color-secondary-500);
            border: 2px solid var(--color-secondary-500);
            color: #ffffff;
            transition: all 0.3s ease;
            font-weight: 600;
        }

        .search-form .btn-search:hover {
            background: var(--color-secondary-600);
            border-color: var(--color-secondary-600);
            transform: scale(1.02);
            box-shadow: 0 4px 15px rgba(249, 115, 22, 0.4);
        }

        /* ============================================
           CARRINHO
           ============================================ */
        .cart-wrapper {
            position: relative;
            display: inline-flex;
            align-items: center;
        }

        .cart-btn {
            border-radius: var(--border-radius-full);
            padding: 8px 18px;
            border: 2px solid rgba(255, 255, 255, 0.25);
            background: transparent;
            color: #ffffff;
            transition: all 0.3s ease;
            position: relative;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .cart-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--color-secondary-500);
            transform: translateY(-2px);
            color: #ffffff;
        }

        .cart-btn i {
            font-size: 1.1rem;
        }

        .cart-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: var(--color-danger-500);
            color: #ffffff;
            border-radius: 50%;
            min-width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: 700;
            box-shadow: 0 2px 12px rgba(239, 68, 68, 0.5);
            animation: pulse-badge 2.5s ease-in-out infinite;
            padding: 0 6px;
            border: 2px solid #ffffff;
        }

        @keyframes pulse-badge {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.15); }
        }

        /* ============================================
           BOTÕES
           ============================================ */
        .btn-action {
            border-radius: var(--border-radius-full);
            padding: 8px 24px;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-action-outline {
            border: 2px solid rgba(255, 255, 255, 0.3);
            color: #ffffff;
            background: transparent;
        }

        .btn-action-outline:hover {
            background: rgba(255, 255, 255, 0.12);
            border-color: rgba(255, 255, 255, 0.6);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            color: #ffffff;
        }

        .btn-action-primary {
            background: var(--gradient-secondary);
            border: none;
            color: #ffffff;
            box-shadow: 0 4px 20px rgba(249, 115, 22, 0.35);
        }

        .btn-action-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 30px rgba(249, 115, 22, 0.5);
            color: #ffffff;
        }

        /* ============================================
           FOOTER
           ============================================ */
        .footer-custom {
            background: var(--gradient-dark);
            color: rgba(255, 255, 255, 0.8);
            padding: 60px 0 30px;
            margin-top: auto;
            position: relative;
            overflow: hidden;
        }

        .footer-custom::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--gradient-primary);
        }

        .footer-custom h5 {
            color: #ffffff;
            font-weight: 600;
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 12px;
        }

        .footer-custom h5::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 40px;
            height: 3px;
            background: var(--gradient-secondary);
            border-radius: 3px;
        }

        .footer-custom .footer-brand {
            font-size: 1.6rem;
            font-weight: 800;
            color: #ffffff;
            display: flex;
            align-items: center;
        }

        .footer-custom .footer-brand i {
            color: var(--color-secondary-500);
            font-size: 1.8rem;
            margin-right: 12px;
            filter: drop-shadow(0 0 20px rgba(249, 115, 22, 0.3));
        }

        .footer-custom .footer-text {
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.95rem;
            line-height: 1.8;
        }

        .footer-custom .footer-link {
            color: rgba(255, 255, 255, 0.6);
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
            padding: 4px 0;
        }

        .footer-custom .footer-link:hover {
            color: var(--color-secondary-500);
            transform: translateX(8px);
        }

        .footer-custom .footer-social {
            display: flex;
            gap: 12px;
            margin-top: 16px;
            flex-wrap: wrap;
        }

        .footer-custom .footer-social a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.06);
            color: #ffffff;
            transition: all 0.3s ease;
            text-decoration: none;
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .footer-custom .footer-social a:hover {
            background: var(--color-primary-600);
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(37, 99, 235, 0.3);
            border-color: var(--color-primary-600);
        }

        .footer-custom .footer-social a:nth-child(2):hover {
            background: #7c3aed;
            border-color: #7c3aed;
        }

        .footer-custom .footer-social a:nth-child(3):hover {
            background: #1877f2;
            border-color: #1877f2;
        }

        .footer-custom .footer-social a:nth-child(4):hover {
            background: #ff0000;
            border-color: #ff0000;
        }

        .footer-custom .footer-divider {
            border-color: rgba(255, 255, 255, 0.06);
            margin: 30px 0 20px;
        }

        .footer-custom .footer-bottom {
            color: rgba(255, 255, 255, 0.35);
            font-size: 0.85rem;
        }

        .footer-custom .footer-bottom .heart {
            color: var(--color-danger-500);
            display: inline-block;
            animation: heart-beat 1.5s ease-in-out infinite;
        }

        @keyframes heart-beat {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }

        /* ============================================
           BOTÕES FLUTUANTES
           ============================================ */
        .float-btn {
            position: fixed;
            z-index: 1000;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.15);
        }

        .float-btn:hover {
            transform: scale(1.12) translateY(-4px);
            box-shadow: 0 8px 40px rgba(0, 0, 0, 0.25);
        }

        .float-btn-whatsapp {
            bottom: 30px;
            right: 30px;
            width: 62px;
            height: 62px;
            border-radius: 50%;
            background: var(--gradient-success);
            box-shadow: 0 4px 30px rgba(34, 197, 94, 0.4);
            animation: float-whatsapp 3s ease-in-out infinite;
        }

        .float-btn-whatsapp i {
            font-size: 2.4rem;
            color: #ffffff;
        }

        @keyframes float-whatsapp {
            0%, 100% { transform: scale(1) translateY(0); }
            50% { transform: scale(1.06) translateY(-6px); }
        }

        .float-btn-instagram {
            bottom: 105px;
            right: 30px;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888);
            box-shadow: 0 4px 30px rgba(220, 39, 67, 0.35);
            animation: float-instagram 3s ease-in-out infinite 0.5s;
        }

        .float-btn-instagram i {
            font-size: 2rem;
            color: #ffffff;
        }

        @keyframes float-instagram {
            0%, 100% { transform: scale(1) translateY(0); }
            50% { transform: scale(1.06) translateY(-6px); }
        }

        /* ============================================
           ALERTAS
           ============================================ */
        .alert-custom {
            border-radius: var(--border-radius);
            border: none;
            padding: 16px 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        }

        /* ============================================
           RESPONSIVIDADE
           ============================================ */
        @media (max-width: 1199.98px) {
            .search-form .form-control {
                width: 160px;
            }
            .search-form .form-control:focus {
                width: 200px;
            }
        }

        @media (max-width: 991.98px) {
            .search-form .form-control {
                width: 140px;
            }
            .search-form .form-control:focus {
                width: 180px;
            }
        }

        @media (max-width: 767.98px) {
            .top-bar {
                font-size: 0.75rem;
                padding: 6px 0;
            }
            .top-bar .divider {
                margin: 0 6px;
            }
            .top-bar .col-md-4 {
                padding: 2px 0;
            }

            .float-btn-whatsapp {
                width: 52px;
                height: 52px;
                bottom: 20px;
                right: 20px;
            }
            .float-btn-whatsapp i {
                font-size: 2rem;
            }
            .float-btn-instagram {
                width: 46px;
                height: 46px;
                bottom: 86px;
                right: 20px;
            }
            .float-btn-instagram i {
                font-size: 1.6rem;
            }

            .footer-custom {
                padding: 40px 0 20px;
            }
            .footer-custom .footer-social {
                gap: 8px;
            }
        }

        @media (max-width: 575.98px) {
            .top-bar {
                display: none !important;
            }

            .float-btn-whatsapp {
                width: 46px;
                height: 46px;
                bottom: 16px;
                right: 16px;
            }
            .float-btn-whatsapp i {
                font-size: 1.6rem;
            }
            .float-btn-instagram {
                width: 40px;
                height: 40px;
                bottom: 76px;
                right: 16px;
            }
            .float-btn-instagram i {
                font-size: 1.3rem;
            }

            .footer-custom .footer-social a {
                width: 38px;
                height: 38px;
            }
            .footer-custom h5::after {
                width: 30px;
            }
        }

        /* ============================================
           ACESSIBILIDADE
           ============================================ */
        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }

        :focus-visible {
            outline: 3px solid var(--color-secondary-500);
            outline-offset: 2px;
        }

        ::-webkit-scrollbar {
            width: 10px;
            height: 10px;
        }
        ::-webkit-scrollbar-track {
            background: var(--color-gray-100);
        }
        ::-webkit-scrollbar-thumb {
            background: var(--gradient-primary);
            border-radius: 6px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: var(--gradient-secondary);
        }

        /* ============================================
           UTILITÁRIOS
           ============================================ */
        .text-gradient-primary {
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .text-gradient-secondary {
            background: var(--gradient-secondary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .bg-gradient-primary {
            background: var(--gradient-primary);
        }
        .bg-gradient-secondary {
            background: var(--gradient-secondary);
        }
        .bg-gradient-success {
            background: var(--gradient-success);
        }

        .gap-1 { gap: 0.25rem !important; }
        .gap-2 { gap: 0.5rem !important; }
        .gap-3 { gap: 1rem !important; }
        .gap-4 { gap: 1.5rem !important; }
    </style>

    @stack('styles')
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100">
        {{-- TOP BAR --}}
        <div class="top-bar">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-4 text-center text-md-start">
                        <i class="bi bi-telephone-fill text-primary" aria-hidden="true"></i> 
                        <a href="tel:+5511999999999">(11) 99999-9999</a>
                        <span class="divider">|</span>
                        <i class="bi bi-whatsapp text-success" aria-hidden="true"></i> 
                        <a href="https://wa.me/5511999999999" target="_blank" rel="noopener noreferrer">(11) 98888-8888</a>
                    </div>
                    <div class="col-md-4 text-center">
                        <i class="bi bi-envelope text-primary" aria-hidden="true"></i> 
                        <a href="mailto:contato@smcomponentes.com">contato@smcomponentes.com</a>
                    </div>
                    <div class="col-md-4 text-center text-md-end">
                        <i class="bi bi-clock text-primary" aria-hidden="true"></i> 
                        <span>Seg. a Sex. 9h às 17h</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- NAVBAR --}}
        @include('layouts.navigation')

        {{-- ALERTAS --}}
        <div class="container mt-0">
            @if(session('success'))
                <div class="alert alert-success alert-custom alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2" aria-hidden="true"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-custom alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle-fill me-2" aria-hidden="true"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning alert-custom alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2" aria-hidden="true"></i>
                    {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                </div>
            @endif

            @if(session('info'))
                <div class="alert alert-info alert-custom alert-dismissible fade show" role="alert">
                    <i class="bi bi-info-circle-fill me-2" aria-hidden="true"></i>
                    {{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                </div>
            @endif
        </div>

        {{-- CONTEÚDO --}}
        <main>
            @yield('content')
        </main>

        {{-- FOOTER --}}
        @include('layouts.footer')

        {{-- BOTÕES FLUTUANTES --}}
        <a href="https://wa.me/5511999999999?text=Olá!%20Vim%20pelo%20site%20SM%20Componentes%20e%20gostaria%20de%20mais%20informações." 
           target="_blank" rel="noopener noreferrer"
           class="float-btn float-btn-whatsapp" 
           aria-label="Fale conosco no WhatsApp"
           title="Fale conosco no WhatsApp">
            <i class="bi bi-whatsapp" aria-hidden="true"></i>
        </a>

        <a href="https://instagram.com/smcomponentes" 
           target="_blank" rel="noopener noreferrer"
           class="float-btn float-btn-instagram" 
           aria-label="Siga-nos no Instagram"
           title="Siga-nos no Instagram">
            <i class="bi bi-instagram" aria-hidden="true"></i>
        </a>
    </div>

    {{-- SCRIPTS --}}
    <script src="https://code.jquery.com/jquery-3.6.4.min.js" 
            integrity="sha256-oP6HI9z1XaZNBrJURtCoUT5SUnxFr8s3BzRl+cbzUq8=" 
            crossorigin="anonymous">
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
            crossorigin="anonymous">
    </script>

    <script>
        (function() {
            'use strict';

            // ✅ FUNÇÃO PARA ATUALIZAR O CONTADOR DO CARRINHO
            function updateCartCount() {
                const badge = document.getElementById('cart-count');
                if (!badge) return;

                fetch('{{ route("carrinho.count") }}', {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                    .then(function(response) {
                        if (!response.ok) throw new Error('Erro na resposta');
                        return response.json();
                    })
                    .then(function(data) {
                        const count = data.count || 0;
                        badge.textContent = count;
                        badge.style.display = count > 0 ? 'flex' : 'none';
                    })
                    .catch(function(error) {
                        console.debug('ℹ️ Carrinho vazio ou indisponível:', error.message);
                    });
            }

            // ✅ FUNÇÃO PARA BUSCA AO DIGITAR
            function setupSearch() {
                const searchInput = document.querySelector('.search-form input[type="search"]');
                if (!searchInput) return;

                let timeout;
                searchInput.addEventListener('input', function() {
                    clearTimeout(timeout);
                    const value = this.value.trim();
                    if (value.length >= 2) {
                        timeout = setTimeout(function() {
                            const form = searchInput.closest('form');
                            if (form) {
                                // ✅ GARANTIR CSRF TOKEN NO FORMULÁRIO DE BUSCA
                                let csrfField = form.querySelector('input[name="_token"]');
                                if (!csrfField) {
                                    csrfField = document.createElement('input');
                                    csrfField.type = 'hidden';
                                    csrfField.name = '_token';
                                    csrfField.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                                    form.appendChild(csrfField);
                                }
                                form.submit();
                            }
                        }, 300);
                    }
                });
            }

            // ✅ GARANTIR CSRF EM TODOS OS FORMS
            function ensureCsrfInForms() {
                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                document.querySelectorAll('form:not([data-csrf-added])').forEach(function(form) {
                    if (form.method.toUpperCase() === 'POST') {
                        let csrfField = form.querySelector('input[name="_token"]');
                        if (!csrfField) {
                            csrfField = document.createElement('input');
                            csrfField.type = 'hidden';
                            csrfField.name = '_token';
                            csrfField.value = token;
                            form.prepend(csrfField);
                            form.setAttribute('data-csrf-added', 'true');
                        }
                    }
                });
            }

            // ✅ INICIAR TUDO
            function init() {
                updateCartCount();
                setupSearch();
                ensureCsrfInForms();
                setInterval(updateCartCount, 30000);
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', init);
            } else {
                init();
            }

            window.addEventListener('pageshow', function(event) {
                if (event.persisted) {
                    updateCartCount();
                }
            });

            // ✅ QUANDO A PÁGINA CARREGAR DINAMICAMENTE (AJAX)
            document.addEventListener('DOMContentLoaded', function() {
                ensureCsrfInForms();
            });

            // ✅ PARA FORMS ADICIONADOS DINAMICAMENTE
            const observer = new MutationObserver(function() {
                ensureCsrfInForms();
            });
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });

        })();
    </script>

    @stack('scripts')
</body>
</html>