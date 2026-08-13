<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="SM Componentes - Sua loja de componentes eletrônicos com qualidade e confiança">
    <meta name="theme-color" content="#2563eb">
    
    {{-- 🔒 FORÇAR HTTPS E CORRIGIR MIXED CONTENT --}}
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <meta http-equiv="Content-Security-Policy" content="block-all-mixed-content">
    
    <title>@yield('title', 'SM Componentes - Qualidade em Componentes Eletrônicos')</title>
    
    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    
    {{-- ════════════════════════════════════════════════════ --}}
    {{-- FONT AWESOME 6 (GRATUITO)                          --}}
    {{-- ════════════════════════════════════════════════════ --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    {{-- Vite Assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        /* ============================================
           🎨 PSICOLOGIA DAS CORES APLICADA
           ============================================
           
           🔵 AZUL (#2563eb, #1e40af, #3b82f6)
           → Confiança, Segurança, Tecnologia, Estabilidade
           → Usado em: Navbar, Botões principais, Links
           
           🟠 LARANJA (#f97316, #ea580c)
           → Energia, Entusiasmo, Ação, Impulso de Compra
           → Usado em: Badges, Promoções, CTAs
           
           🟢 VERDE (#22c55e, #16a34a)
           → Crescimento, Harmonia, Sustentabilidade
           → Usado em: WhatsApp, Sucesso, Confirmações
           
           🟣 ROXO (#8b5cf6, #7c3aed)
           → Criatividade, Inovação, Luxo
           → Usado em: Destaques, Elementos premium
           
           🔴 VERMELHO (#ef4444, #dc2626)
           → Urgência, Atenção, Ofertas
           → Usado em: Descontos, Alertas, Badges do carrinho
           
           ⚫ PRETO/CINZA (#0f172a, #1e293b, #334155)
           → Sofisticação, Elegância, Profissionalismo
           → Usado em: Textos, Fundos, Footer
        */

        /* ============================================
           VARIÁVEIS DE COR E TEMA
           ============================================ */
        :root {
            /* Cores Primárias - Psicologia: Confiança e Tecnologia */
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

            /* Cores Secundárias - Psicologia: Energia e Ação */
            --color-secondary-50: #fff7ed;
            --color-secondary-500: #f97316;
            --color-secondary-600: #ea580c;
            --color-secondary-700: #c2410c;

            /* Cores de Sucesso - Psicologia: Confiança e Crescimento */
            --color-success-500: #22c55e;
            --color-success-600: #16a34a;

            /* Cores de Destaque - Psicologia: Criatividade e Inovação */
            --color-purple-500: #8b5cf6;
            --color-purple-600: #7c3aed;

            /* Cores de Alerta - Psicologia: Urgência */
            --color-danger-500: #ef4444;
            --color-danger-600: #dc2626;

            /* Cores Neutras - Psicologia: Sofisticação */
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

            /* Gradientes */
            --gradient-primary: linear-gradient(135deg, #1e40af 0%, #2563eb 50%, #3b82f6 100%);
            --gradient-secondary: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            --gradient-success: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            --gradient-purple: linear-gradient(135deg, #7c3aed 0%, #8b5cf6 100%);
            --gradient-hero: linear-gradient(135deg, #0f172a 0%, #1e293b 40%, #1e40af 100%);
            --gradient-dark: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);

            /* Outras variáveis */
            --font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            --transition-speed: 0.3s;
            --border-radius: 12px;
            --border-radius-sm: 8px;
            --border-radius-full: 50px;
            --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07), 0 1px 3px rgba(0, 0, 0, 0.1);
            --box-shadow-hover: 0 10px 30px rgba(37, 99, 235, 0.15), 0 4px 10px rgba(0, 0, 0, 0.05);
            --box-shadow-glow: 0 0 40px rgba(59, 130, 246, 0.2);
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
           TOP BAR - Psicologia: Confiabilidade
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

        .top-bar .text-primary {
            color: var(--color-primary-600) !important;
        }

        .top-bar .text-success {
            color: var(--color-success-500) !important;
        }

        .top-bar a {
            color: var(--color-gray-600);
            text-decoration: none;
            transition: color var(--transition-speed) ease;
        }

        .top-bar a:hover {
            color: var(--color-primary-600);
        }

        /* ============================================
           NAVBAR - Psicologia: Confiança e Poder
           ============================================ */
        .navbar-custom {
            background: var(--gradient-primary);
            box-shadow: 0 4px 20px rgba(30, 58, 138, 0.3);
            padding: 12px 0;
            transition: all var(--transition-speed) ease;
            min-height: 72px;
            position: sticky;
            top: 0;
            z-index: 1050;
        }

        .navbar-custom::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--color-secondary-500), var(--color-purple-500), transparent);
            opacity: 0.5;
        }

        .navbar-custom .navbar-brand {
            font-weight: 800;
            font-size: 1.3rem;
            letter-spacing: -0.5px;
            color: #ffffff;
            padding: 0;
            transition: transform var(--transition-speed) ease;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .navbar-custom .navbar-brand:hover {
            transform: scale(1.03);
            color: #ffffff;
        }

        .navbar-custom .navbar-brand i {
            font-size: 1.6rem;
            margin-right: 10px;
            color: var(--color-secondary-500);
            filter: drop-shadow(0 0 15px rgba(249, 115, 22, 0.3));
            transition: transform var(--transition-speed) ease;
        }

        .navbar-custom .navbar-brand:hover i {
            transform: rotate(-15deg) scale(1.1);
        }

        .navbar-custom .nav-link {
            color: rgba(255, 255, 255, 0.85);
            font-weight: 500;
            padding: 8px 18px;
            border-radius: var(--border-radius-sm);
            transition: all var(--transition-speed) ease;
            position: relative;
        }

        .navbar-custom .nav-link:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.12);
            transform: translateY(-1px);
        }

        .navbar-custom .nav-link.active {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.18);
        }

        .navbar-custom .nav-link.active::after {
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

        /* ============================================
           BUSCA - Psicologia: Clareza e Eficiência
           ============================================ */
        .search-form .form-control {
            border-radius: var(--border-radius-full) 0 0 var(--border-radius-full);
            border: 2px solid rgba(255, 255, 255, 0.2);
            background: rgba(255, 255, 255, 0.08);
            color: #ffffff;
            padding: 8px 20px;
            font-size: 0.9rem;
            transition: all var(--transition-speed) ease;
            width: 220px;
            backdrop-filter: blur(10px);
        }

        .search-form .form-control::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .search-form .form-control:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: var(--color-secondary-500);
            box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.15);
            width: 280px;
            color: #ffffff;
        }

        .search-form .btn-search {
            border-radius: 0 var(--border-radius-full) var(--border-radius-full) 0;
            padding: 8px 20px;
            background: var(--color-secondary-500);
            border: 2px solid var(--color-secondary-500);
            color: #ffffff;
            transition: all var(--transition-speed) ease;
            font-weight: 600;
        }

        .search-form .btn-search:hover {
            background: var(--color-secondary-600);
            border-color: var(--color-secondary-600);
            transform: scale(1.02);
            box-shadow: 0 4px 15px rgba(249, 115, 22, 0.4);
        }

        /* ============================================
           BOTÕES DE AÇÃO - Psicologia: Urgência e Confiança
           ============================================ */
        .btn-action {
            border-radius: var(--border-radius-full);
            padding: 8px 24px;
            font-weight: 600;
            transition: all var(--transition-speed) ease;
            position: relative;
            overflow: hidden;
        }

        .btn-action::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.25);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .btn-action:active::after {
            width: 400px;
            height: 400px;
        }

        /* Botão Outline - Psicologia: Sofisticação */
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

        /* Botão Primário - Psicologia: Confiança */
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

        /* Botão Sucesso - Psicologia: Crescimento */
        .btn-action-success {
            background: var(--gradient-success);
            border: none;
            color: #ffffff;
            box-shadow: 0 4px 20px rgba(34, 197, 94, 0.35);
        }

        .btn-action-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 30px rgba(34, 197, 94, 0.5);
            color: #ffffff;
        }

        /* ============================================
           CARRINHO - Psicologia: Urgência e Ação
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
            transition: all var(--transition-speed) ease;
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
           DROPDOWN - Psicologia: Credibilidade
           ============================================ */
        .dropdown-menu-custom {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: 0 15px 50px rgba(15, 23, 42, 0.15);
            padding: 8px;
            margin-top: 10px;
            min-width: 220px;
            animation: slideDown 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
            border: 1px solid var(--color-gray-100);
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-12px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .dropdown-item-custom {
            border-radius: var(--border-radius-sm);
            padding: 10px 16px;
            transition: all var(--transition-speed) ease;
            font-weight: 500;
            color: var(--color-gray-700);
            text-decoration: none;
            display: block;
        }

        .dropdown-item-custom:hover {
            background: var(--color-primary-50);
            transform: translateX(4px);
            color: var(--color-primary-700);
        }

        .dropdown-item-custom i {
            width: 22px;
            text-align: center;
            margin-right: 10px;
            color: var(--color-primary-500);
        }

        .dropdown-item-custom.text-danger:hover {
            background: #fef2f2;
            color: var(--color-danger-600);
        }

        .dropdown-item-custom.text-danger i {
            color: var(--color-danger-500);
        }

        .dropdown-divider-custom {
            margin: 6px 12px;
            border-color: var(--color-gray-100);
        }

        /* ============================================
           HERO - Psicologia: Inspiração e Inovação
           ============================================ */
        .hero-section {
            background: var(--gradient-hero);
            padding: 80px 0;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.1) 0%, transparent 70%);
            border-radius: 50%;
            animation: float-hero 20s ease-in-out infinite;
        }

        @keyframes float-hero {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(-20px, 30px) scale(1.1); }
            66% { transform: translate(20px, -20px) scale(0.9); }
        }

        .hero-section .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            line-height: 1.1;
            background: linear-gradient(135deg, #ffffff 0%, #93c5fd 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-section .hero-title .highlight {
            background: var(--gradient-secondary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-section .hero-subtitle {
            color: rgba(255, 255, 255, 0.7);
            font-size: 1.2rem;
            max-width: 500px;
        }

        /* ============================================
           FOOTER - Psicologia: Confiança e Estabilidade
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
            letter-spacing: -0.3px;
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
            transition: all var(--transition-speed) ease;
            display: inline-block;
            padding: 4px 0;
            position: relative;
        }

        .footer-custom .footer-link::before {
            content: '→';
            opacity: 0;
            margin-right: 6px;
            transition: all var(--transition-speed) ease;
        }

        .footer-custom .footer-link:hover {
            color: var(--color-secondary-500);
            transform: translateX(8px);
        }

        .footer-custom .footer-link:hover::before {
            opacity: 1;
        }

        /* Redes Sociais - Psicologia: Conexão */
        .footer-custom .footer-social {
            display: flex;
            gap: 12px;
            margin-top: 16px;
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
            transition: all var(--transition-speed) ease;
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
            background: var(--gradient-purple);
            border-color: var(--color-purple-500);
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
           BOTÕES FLUTUANTES - Psicologia: Ação e Conexão
           ============================================ */
        .float-btn {
            position: fixed;
            z-index: 1000;
            border: none;
            cursor: pointer;
            transition: all var(--transition-speed) ease;
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

        /* WhatsApp - Psicologia: Confiança e Crescimento */
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

        /* Instagram - Psicologia: Criatividade e Inspiração */
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
           ALERTAS - Psicologia: Clareza e Confiança
           ============================================ */
        .alert-custom {
            border-radius: var(--border-radius);
            border: none;
            padding: 16px 20px;
            box-shadow: var(--box-shadow);
        }

        .alert-custom .btn-close {
            filter: brightness(0.8);
        }

        /* ============================================
           RESPONSIVIDADE
           ============================================ */
        @media (max-width: 991.98px) {
            .navbar-custom .navbar-collapse {
                background: rgba(15, 23, 42, 0.98);
                backdrop-filter: blur(20px);
                padding: 20px;
                border-radius: var(--border-radius);
                margin-top: 12px;
                box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
                border: 1px solid rgba(255, 255, 255, 0.06);
            }

            .search-form .form-control {
                width: 100%;
            }

            .search-form .form-control:focus {
                width: 100%;
            }

            .navbar-custom .nav-link {
                padding: 12px 16px;
                border-radius: var(--border-radius-sm);
            }

            .navbar-custom .nav-link.active::after {
                display: none;
            }

            .btn-action {
                width: 100%;
                justify-content: center;
            }

            .cart-wrapper {
                width: 100%;
                margin: 8px 0;
            }

            .cart-btn {
                width: 100%;
                justify-content: center;
            }

            .hero-section .hero-title {
                font-size: 2.5rem;
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

            .navbar-custom {
                padding: 8px 0;
                min-height: 60px;
            }

            .navbar-custom .navbar-brand {
                font-size: 1.1rem;
            }

            .navbar-custom .navbar-brand i {
                font-size: 1.3rem;
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

            .hero-section {
                padding: 50px 0;
            }

            .hero-section .hero-title {
                font-size: 2rem;
            }

            .hero-section .hero-subtitle {
                font-size: 1rem;
            }
        }

        @media (max-width: 575.98px) {
            .top-bar {
                display: none !important;
            }

            .navbar-custom .navbar-brand {
                font-size: 0.95rem;
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

        /* Focus visible para navegação por teclado */
        :focus-visible {
            outline: 3px solid var(--color-secondary-500);
            outline-offset: 2px;
        }

        /* ============================================
           SCROLLBAR PERSONALIZADA
           ============================================ */
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
           UTILITÁRIOS DE COR
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

        .bg-gradient-purple {
            background: var(--gradient-purple);
        }

        /* ============================================
           CUSTOM - CARD PRODUTOS
           ============================================ */
        .product-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            border-radius: var(--border-radius);
            overflow: hidden;
            background: #ffffff;
            box-shadow: var(--box-shadow);
            height: 100%;
        }

        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--box-shadow-hover);
        }

        .product-card .card-img-top {
            height: 200px;
            object-fit: contain;
            padding: 16px;
            background: var(--color-gray-50);
        }

        .product-card .card-title {
            font-size: 0.9rem;
            font-weight: 600;
            min-height: 44px;
            color: var(--color-gray-800);
        }

        .product-card .price {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--color-success-500);
        }

        .product-card .old-price {
            font-size: 0.85rem;
            color: var(--color-gray-400);
            text-decoration: line-through;
            margin-left: 8px;
        }

        .product-card .categoria-badge {
            font-size: 0.7rem;
            color: var(--color-gray-500);
            background: var(--color-gray-100);
            padding: 4px 12px;
            border-radius: 20px;
            display: inline-block;
        }

        /* ============================================
           CUSTOM - ADMIN STAT CARDS
           ============================================ */
        .admin-stat-card {
            background: #fff;
            border-radius: var(--border-radius);
            padding: 15px 20px;
            border: 1px solid var(--color-gray-200);
            transition: var(--transition-speed) ease;
            text-align: center;
        }

        .admin-stat-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--box-shadow-hover);
        }

        .admin-stat-card .stat-number {
            font-size: 2rem;
            font-weight: 700;
        }

        .admin-stat-card .stat-label {
            font-size: 0.85rem;
            color: var(--color-gray-500);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ============================================
           CUSTOM - ADMIN TABLES
           ============================================ */
        .admin-table th {
            background: var(--color-gray-50);
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--color-gray-500);
            border-bottom: 2px solid var(--color-gray-200);
        }

        .admin-table td {
            vertical-align: middle;
            padding: 10px 12px;
        }

        .admin-table tr:hover {
            background-color: var(--color-gray-50);
        }

        /* ============================================
           CUSTOM - PAGINAÇÃO
           ============================================ */
        .pagination {
            gap: 6px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .pagination .page-link {
            border: none;
            border-radius: 10px !important;
            padding: 10px 16px;
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--color-gray-700);
            background: #ffffff;
            box-shadow: 0 2px 6px rgba(0,0,0,0.04);
            transition: var(--transition-speed) ease;
            min-width: 44px;
            text-align: center;
        }

        .pagination .page-link:hover {
            background: var(--color-primary-50);
            color: var(--color-primary-600);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.15);
        }

        .pagination .page-item.active .page-link {
            background: var(--gradient-primary);
            color: #ffffff;
            box-shadow: 0 4px 16px rgba(37, 99, 235, 0.35);
        }

        .pagination .page-item.disabled .page-link {
            color: var(--color-gray-400);
            background: var(--color-gray-50);
            cursor: not-allowed;
            box-shadow: none;
            transform: none;
        }

        /* ============================================
           CUSTOM - BANNER SLIDE
           ============================================ */
        .banner-slide {
            border-radius: 16px;
            position: relative;
            overflow: hidden;
        }

        .banner-slide::after {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 40%;
            height: 200%;
            background: rgba(255,255,255,0.05);
            transform: rotate(-15deg);
            pointer-events: none;
        }

        .carousel-control-prev-icon,
        .carousel-control-next-icon {
            background-size: 50% 50%;
            width: 48px;
            height: 48px;
        }

        .carousel-indicators button {
            width: 12px !important;
            height: 12px !important;
            border-radius: 50% !important;
            border: 2px solid rgba(255,255,255,0.6) !important;
            margin: 0 6px !important;
        }

        .carousel-indicators .active {
            background: #fff !important;
            border-color: #fff !important;
        }

        .carousel-item {
            transition: transform 0.6s ease-in-out;
        }

        .carousel-item .banner-slide {
            animation: fadeSlide 0.8s ease;
        }

        @keyframes fadeSlide {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
    
    @stack('styles')
</head>
<body>
    {{-- ============================================
         TOP BAR - Psicologia: Confiabilidade
         ============================================ --}}
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

    {{-- ============================================
         NAVBAR - Psicologia: Confiança e Poder
         ============================================ --}}
    <nav class="navbar navbar-expand-lg navbar-custom" role="navigation" aria-label="Navegação principal">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}" aria-label="SM Componentes - Página inicial">
                <i class="bi bi-plug" aria-hidden="true"></i> 
                <span class="d-none d-sm-inline">SM Componentes</span>
                <span class="d-inline d-sm-none">SM</span>
            </a>
            
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Alternar navegação">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}" aria-current="page">
                            <i class="bi bi-house" aria-hidden="true"></i> Início
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('produtos.*') ? 'active' : '' }}" href="{{ route('produtos.index') }}">
                            <i class="bi bi-grid" aria-hidden="true"></i> Produtos
                        </a>
                    </li>
                    @auth
                        @if(Auth::user()->hasRole('Admin') || Auth::user()->hasRole('Funcionario'))
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.dashboard') }}">
                                    <i class="bi bi-speedometer2" aria-hidden="true"></i> Admin
                                </a>
                            </li>
                        @endif
                    @endauth
                </ul>
                
                {{-- 🔥 Busca --}}
                <form class="search-form d-flex me-3 mb-2 mb-lg-0" action="{{ route('produtos.buscar') }}" method="GET" role="search">
                    <div class="input-group">
                        <input class="form-control" type="search" name="q" placeholder="Buscar produtos..." 
                               value="{{ request('q') }}" aria-label="Buscar produtos" id="search-input">
                        <button class="btn btn-search" type="submit" aria-label="Buscar">
                            <i class="bi bi-search" aria-hidden="true"></i>
                        </button>
                    </div>
                </form>
                
                {{-- Carrinho - Psicologia: Urgência --}}
                <div class="cart-wrapper">
                    <a href="{{ route('carrinho.index') }}" class="cart-btn" aria-label="Ver carrinho">
                        <i class="bi bi-cart3" aria-hidden="true"></i>
                        <span class="cart-badge" id="cart-count" aria-live="polite">0</span>
                    </a>
                </div>
                
                {{-- Autenticação --}}
                @auth
                    <div class="dropdown">
                        <button class="btn btn-action btn-action-outline dropdown-toggle" data-bs-toggle="dropdown" 
                                aria-expanded="false" id="userDropdown" aria-label="Menu do usuário">
                            <i class="bi bi-person-circle" aria-hidden="true"></i> 
                            <span class="d-none d-sm-inline">{{ Str::limit(Auth::user()->name, 14) }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-custom" aria-labelledby="userDropdown">
                            <li>
                                <a class="dropdown-item dropdown-item-custom" href="{{ route('profile.edit') }}">
                                    <i class="bi bi-gear" aria-hidden="true"></i> Perfil
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item dropdown-item-custom" href="{{ route('dashboard') }}">
                                    <i class="bi bi-speedometer2" aria-hidden="true"></i> Dashboard
                                </a>
                            </li>
                            <li><hr class="dropdown-divider-custom"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item dropdown-item-custom text-danger">
                                        <i class="bi bi-box-arrow-right" aria-hidden="true"></i> Sair
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="btn btn-action btn-action-outline me-2">
                        <i class="bi bi-box-arrow-in-right" aria-hidden="true"></i> Entrar
                    </a>
                    <a href="{{ route('register') }}" class="btn btn-action btn-action-primary">
                        <i class="bi bi-person-plus" aria-hidden="true"></i> Cadastrar
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    {{-- ============================================
         MENSAGENS DE ALERTA
         ============================================ --}}
    <div class="container mt-3">
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

    {{-- ============================================
         CONTEÚDO PRINCIPAL
         ============================================ --}}
    <main role="main">
        @yield('content')
    </main>

    {{-- ============================================
         FOOTER - Psicologia: Confiança e Estabilidade
         ============================================ --}}
    <footer class="footer-custom" role="contentinfo">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="footer-brand">
                        <i class="bi bi-plug" aria-hidden="true"></i> SM Componentes
                    </div>
                    <p class="footer-text mt-3">
                        Sua loja de componentes eletrônicos desde 2020. Qualidade e confiança em cada produto.
                    </p>
                    <div class="footer-social">
                        <a href="https://wa.me/5511999999999" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp">
                            <i class="bi bi-whatsapp" aria-hidden="true"></i>
                        </a>
                        <a href="https://instagram.com/smcomponentes" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
                            <i class="bi bi-instagram" aria-hidden="true"></i>
                        </a>
                        <a href="#" aria-label="Facebook">
                            <i class="bi bi-facebook" aria-hidden="true"></i>
                        </a>
                        <a href="#" aria-label="YouTube">
                            <i class="bi bi-youtube" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>
                <div class="col-md-4">
                    <h5>Links Rápidos</h5>
                    <ul class="list-unstyled">
                        <li><a href="{{ route('produtos.index') }}" class="footer-link">Todos os Produtos</a></li>
                        <li><a href="#" class="footer-link">Sobre Nós</a></li>
                        <li><a href="#" class="footer-link">Política de Privacidade</a></li>
                        <li><a href="#" class="footer-link">Termos de Uso</a></li>
                        <li><a href="#" class="footer-link">Central de Ajuda</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Contato</h5>
                    <ul class="list-unstyled footer-text">
                        <li class="mb-2">
                            <i class="bi bi-envelope text-primary" aria-hidden="true"></i> 
                            <a href="mailto:contato@smcomponentes.com" class="footer-link">contato@smcomponentes.com</a>
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-telephone text-primary" aria-hidden="true"></i> 
                            <a href="tel:+5511999999999" class="footer-link">(11) 99999-9999</a>
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-whatsapp text-success" aria-hidden="true"></i> 
                            <a href="https://wa.me/5511999999999" target="_blank" rel="noopener noreferrer" class="footer-link">(11) 98888-8888</a>
                        </li>
                        <li>
                            <i class="bi bi-geo-alt text-primary" aria-hidden="true"></i> 
                            <span>São Paulo, SP - Brasil</span>
                        </li>
                    </ul>
                </div>
            </div>
            <hr class="footer-divider">
            <div class="footer-bottom text-center">
                &copy; {{ date('Y') }} SM Componentes. Todos os direitos reservados.
                <span class="d-block d-sm-inline mt-1 mt-sm-0">
                    Desenvolvido com <span class="heart" aria-hidden="true">❤</span> para você
                </span>
            </div>
        </div>
    </footer>

    {{-- ============================================
         BOTÕES FLUTUANTES - Psicologia: Ação e Conexão
         ============================================ --}}
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

    <script>
        /**
         * Atualiza o contador do carrinho
         */
        function updateCartCount() {
            const badge = document.getElementById('cart-count');
            if (!badge) return;

            fetch('{{ route("carrinho.count") }}')
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    const count = data.count || 0;
                    badge.textContent = count;
                    badge.style.display = count > 0 ? 'flex' : 'none';
                })
                .catch(error => {
                    console.debug('Erro ao atualizar carrinho:', error);
                });
        }

        /**
         * Debounce para busca
         */
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        // Inicialização
        document.addEventListener('DOMContentLoaded', () => {
            // Atualizar contador do carrinho
            updateCartCount();

            // Atualizar a cada 30 segundos
            setInterval(updateCartCount, 30000);

            // Busca com debounce
            const searchInput = document.getElementById('search-input');
            if (searchInput) {
                const handleSearch = debounce((event) => {
                    const form = event.target.closest('form');
                    if (form && event.target.value.length >= 2) {
                        form.submit();
                    }
                }, 300);
                searchInput.addEventListener('input', handleSearch);
            }

            // Fechar dropdown ao clicar fora
            document.addEventListener('click', (event) => {
                const dropdown = document.querySelector('.dropdown');
                if (dropdown && !dropdown.contains(event.target)) {
                    const menu = dropdown.querySelector('.dropdown-menu');
                    if (menu) {
                        const bsDropdown = bootstrap.Dropdown.getInstance(dropdown.querySelector('.dropdown-toggle'));
                        if (bsDropdown) bsDropdown.hide();
                    }
                }
            });
        });

        // Atualizar contador quando a página for restaurada pelo BFCache
        window.addEventListener('pageshow', (event) => {
            if (event.persisted) {
                updateCartCount();
            }
        });
    </script>
    
    @stack('scripts')
</body>
</html>