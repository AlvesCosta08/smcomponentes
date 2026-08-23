@extends('layouts.app')

@section('title', 'Carrinho de Compras - SM Componentes')

@section('content')
<style>
    /* ============================================
       IDENTIDADE VISUAL SM COMPONENTES (ATUALIZADA)
       Integrada com as variáveis do layouts.app
    ============================================ */
    
    :root {
        --sm-primary: var(--color-primary-600);
        --sm-primary-dark: var(--color-primary-700);
        --sm-primary-light: var(--color-primary-400);
        --sm-secondary: var(--color-secondary-500);
        --sm-secondary-dark: var(--color-secondary-600);
        --sm-dark: var(--color-gray-900);
        --sm-dark-light: var(--color-gray-800);
        --sm-gray: var(--color-gray-100);
        --sm-gray-dark: var(--color-gray-500);
        --sm-white: #ffffff;
        --sm-gradient: var(--gradient-primary);
        --sm-gradient-hover: linear-gradient(135deg, var(--color-primary-700) 0%, var(--color-primary-800) 100%);
        --shadow-card: 0 8px 32px rgba(37, 99, 235, 0.08);
        --shadow-hover: 0 12px 48px rgba(37, 99, 235, 0.15);
        --radius: var(--border-radius);
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Container */
    .sm-container {
        background: var(--sm-gray);
        min-height: calc(100vh - 300px);
        padding: 20px 0 40px;
    }

    /* Header da Página */
    .sm-header {
        background: var(--gradient-primary);
        padding: 24px 0;
        border-radius: 0 0 var(--radius) var(--radius);
        margin-bottom: 24px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(37, 99, 235, 0.2);
    }
    .sm-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 400px;
        height: 400px;
        border-radius: 50%;
        background: rgba(255,255,255,0.05);
        pointer-events: none;
    }
    .sm-header::after {
        content: '';
        position: absolute;
        bottom: -30%;
        left: 20%;
        width: 300px;
        height: 300px;
        border-radius: 50%;
        background: rgba(255,255,255,0.03);
        pointer-events: none;
    }
    .sm-header .logo-text {
        font-size: 28px;
        font-weight: 800;
        color: white;
        letter-spacing: -0.5px;
        text-shadow: 0 2px 10px rgba(0,0,0,0.1);
        position: relative;
        z-index: 1;
    }
    .sm-header .logo-text span {
        color: var(--sm-secondary);
    }
    .sm-header .cart-title {
        font-size: 20px;
        font-weight: 600;
        color: white;
        position: relative;
        z-index: 1;
    }
    .sm-header .cart-count {
        background: rgba(255,255,255,0.15);
        backdrop-filter: blur(10px);
        color: white;
        padding: 4px 16px;
        border-radius: 50px;
        font-size: 14px;
        font-weight: 600;
        border: 1px solid rgba(255,255,255,0.2);
        position: relative;
        z-index: 1;
    }

    /* Breadcrumb */
    .sm-breadcrumb {
        background: var(--sm-white);
        padding: 12px 20px;
        border-radius: var(--radius);
        margin-bottom: 20px;
        box-shadow: var(--shadow-card);
        font-size: 14px;
        border: 1px solid rgba(37, 99, 235, 0.06);
    }
    .sm-breadcrumb a {
        color: var(--sm-primary);
        text-decoration: none;
        font-weight: 500;
        transition: var(--transition);
    }
    .sm-breadcrumb a:hover {
        color: var(--sm-primary-dark);
        text-decoration: underline;
    }
    .sm-breadcrumb .separator {
        color: var(--sm-gray-dark);
        margin: 0 8px;
    }
    .sm-breadcrumb .current {
        color: var(--sm-dark);
        font-weight: 600;
    }

    /* Product Card */
    .sm-product-card {
        background: var(--sm-white);
        border-radius: var(--radius);
        padding: 20px;
        margin-bottom: 12px;
        box-shadow: var(--shadow-card);
        transition: var(--transition);
        border: 2px solid transparent;
        position: relative;
    }
    .sm-product-card:hover {
        box-shadow: var(--shadow-hover);
        border-color: var(--sm-primary);
        transform: translateY(-2px);
    }
    .sm-product-card .product-checkbox {
        width: 20px;
        height: 20px;
        accent-color: var(--sm-primary);
        cursor: pointer;
        flex-shrink: 0;
        border-radius: 4px;
    }
    .sm-product-card .product-image {
        width: 80px;
        height: 80px;
        border-radius: 12px;
        background: var(--sm-gray);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border: 2px solid rgba(37, 99, 235, 0.1);
        overflow: hidden;
        transition: var(--transition);
    }
    .sm-product-card:hover .product-image {
        border-color: var(--sm-primary);
    }
    .sm-product-card .product-image img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        padding: 8px;
    }
    .sm-product-card .product-info {
        flex: 1;
        min-width: 0;
    }
    .sm-product-card .product-title {
        font-size: 16px;
        font-weight: 700;
        color: var(--sm-dark);
        margin-bottom: 4px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        transition: var(--transition);
    }
    .sm-product-card .product-title a {
        color: var(--sm-dark);
        text-decoration: none;
    }
    .sm-product-card .product-title a:hover {
        color: var(--sm-primary);
    }
    .sm-product-card .product-sku {
        font-size: 12px;
        color: var(--sm-gray-dark);
    }
    .sm-product-card .product-price {
        font-size: 24px;
        font-weight: 800;
        color: var(--sm-primary);
        letter-spacing: -0.5px;
    }
    .sm-product-card .product-price-old {
        font-size: 14px;
        color: var(--sm-gray-dark);
        text-decoration: line-through;
        margin-left: 8px;
        font-weight: 500;
    }
    .sm-product-card .product-installments {
        font-size: 13px;
        color: var(--sm-secondary);
        font-weight: 600;
    }
    .sm-product-card .product-shipping {
        font-size: 13px;
        color: var(--color-success-500);
        font-weight: 600;
    }
    .sm-product-card .product-shipping i {
        margin-right: 4px;
    }

    /* Quantity Control */
    .sm-qty-control {
        display: inline-flex;
        align-items: center;
        border: 2px solid var(--sm-gray);
        border-radius: 10px;
        overflow: hidden;
        background: var(--sm-white);
        transition: var(--transition);
    }
    .sm-qty-control:focus-within {
        border-color: var(--sm-primary);
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
    }
    .sm-qty-control button {
        background: none;
        border: none;
        padding: 6px 14px;
        font-size: 16px;
        font-weight: 700;
        color: var(--sm-dark);
        cursor: pointer;
        transition: var(--transition);
        min-width: 38px;
    }
    .sm-qty-control button:hover {
        background: var(--sm-primary);
        color: white;
    }
    .sm-qty-control button:active {
        transform: scale(0.9);
    }
    .sm-qty-control input {
        width: 46px;
        text-align: center;
        border: none;
        border-left: 2px solid var(--sm-gray);
        border-right: 2px solid var(--sm-gray);
        padding: 6px 0;
        font-weight: 700;
        font-size: 15px;
        outline: none;
        background: var(--sm-white);
        color: var(--sm-dark);
    }
    .sm-qty-control input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    .sm-qty-control input[type=number] {
        -moz-appearance: textfield;
    }

    /* Action Buttons */
    .sm-action-btn {
        background: none;
        border: none;
        color: var(--sm-primary);
        font-size: 13px;
        font-weight: 600;
        padding: 4px 12px;
        cursor: pointer;
        transition: var(--transition);
        border-radius: 6px;
    }
    .sm-action-btn:hover {
        background: rgba(37, 99, 235, 0.08);
        color: var(--sm-primary-dark);
    }
    .sm-action-btn.danger {
        color: var(--color-danger-500);
    }
    .sm-action-btn.danger:hover {
        background: rgba(239, 68, 68, 0.08);
    }

    /* Cart Summary */
    .sm-summary {
        background: var(--sm-white);
        border-radius: var(--radius);
        padding: 24px;
        box-shadow: var(--shadow-card);
        position: sticky;
        top: 20px;
        border: 2px solid rgba(37, 99, 235, 0.06);
    }
    .sm-summary .summary-title {
        font-size: 18px;
        font-weight: 800;
        color: var(--sm-dark);
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 3px solid var(--sm-primary);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .sm-summary .summary-title i {
        color: var(--sm-primary);
    }
    .sm-summary .summary-row {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        font-size: 14px;
        color: var(--sm-dark);
    }
    .sm-summary .summary-row.total {
        border-top: 2px solid rgba(37, 99, 235, 0.15);
        margin-top: 8px;
        padding-top: 16px;
        font-size: 18px;
        font-weight: 800;
    }
    .sm-summary .summary-row.total .total-value {
        color: var(--sm-primary);
        font-size: 24px;
    }
    .sm-summary .summary-row .discount-value {
        color: var(--sm-secondary);
    }

    /* Checkout Button */
    .sm-btn-checkout {
        background: var(--gradient-secondary);
        border: none;
        border-radius: var(--radius);
        padding: 16px;
        font-size: 16px;
        font-weight: 700;
        color: white;
        width: 100%;
        transition: var(--transition);
        cursor: pointer;
        margin-top: 12px;
        box-shadow: 0 8px 24px rgba(249, 115, 22, 0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        text-decoration: none;
    }
    .sm-btn-checkout:hover {
        background: var(--sm-secondary-dark);
        transform: translateY(-2px);
        box-shadow: 0 12px 32px rgba(249, 115, 22, 0.4);
        color: white;
    }
    .sm-btn-checkout:disabled {
        background: var(--sm-gray-dark);
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    .sm-btn-login {
        background: var(--gradient-primary);
        border: none;
        border-radius: var(--radius);
        padding: 16px;
        font-size: 16px;
        font-weight: 700;
        color: white;
        width: 100%;
        transition: var(--transition);
        cursor: pointer;
        margin-top: 12px;
        box-shadow: 0 8px 24px rgba(37, 99, 235, 0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        text-decoration: none;
    }
    .sm-btn-login:hover {
        background: var(--sm-primary-dark);
        transform: translateY(-2px);
        box-shadow: 0 12px 32px rgba(37, 99, 235, 0.4);
        color: white;
    }

    /* Coupon */
    .sm-coupon {
        margin-top: 16px;
        padding-top: 16px;
        border-top: 2px solid var(--sm-gray);
    }
    .sm-coupon label {
        font-size: 14px;
        font-weight: 600;
        color: var(--sm-dark);
        display: block;
        margin-bottom: 8px;
    }
    .sm-coupon .coupon-input-group {
        display: flex;
        gap: 8px;
        align-items: stretch;
    }
    .sm-coupon .coupon-input-group input {
        flex: 1;
        padding: 10px 14px;
        border: 2px solid var(--sm-gray);
        border-radius: 10px;
        font-size: 14px;
        outline: none;
        transition: var(--transition);
        background: var(--sm-white);
        min-width: 0;
        height: 44px;
    }
    .sm-coupon .coupon-input-group input:focus {
        border-color: var(--sm-primary);
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
    }
    .sm-coupon .btn-apply {
        padding: 10px 20px;
        background: var(--sm-primary);
        color: white;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: var(--transition);
        white-space: nowrap;
        height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 80px;
    }
    .sm-coupon .btn-apply:hover {
        background: var(--sm-primary-dark);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
    }

    /* Frete */
    .sm-shipping {
        margin-top: 16px;
        padding-top: 16px;
        border-top: 2px solid var(--sm-gray);
    }
    .sm-shipping .shipping-title {
        font-size: 14px;
        font-weight: 600;
        color: var(--sm-dark);
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .sm-shipping .shipping-input {
        display: flex;
        gap: 8px;
        align-items: stretch;
    }
    .sm-shipping .shipping-input input {
        flex: 1;
        padding: 10px 14px;
        border: 2px solid var(--sm-gray);
        border-radius: 10px;
        font-size: 14px;
        outline: none;
        transition: var(--transition);
        background: var(--sm-white);
        min-width: 0;
        height: 44px;
    }
    .sm-shipping .shipping-input input:focus {
        border-color: var(--sm-primary);
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
    }
    .sm-shipping .shipping-input button {
        padding: 10px 20px;
        background: var(--sm-gray);
        border: 2px solid var(--sm-gray);
        border-radius: 10px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: var(--transition);
        color: var(--sm-dark);
        white-space: nowrap;
        height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 80px;
    }
    .sm-shipping .shipping-input button:hover {
        background: var(--sm-primary);
        border-color: var(--sm-primary);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
    }
    .sm-shipping .shipping-result {
        margin-top: 8px;
        font-size: 13px;
        font-weight: 600;
        color: var(--color-success-500);
    }
    .sm-shipping .shipping-result .error {
        color: var(--color-danger-500);
    }

    /* Empty State */
    .sm-empty {
        text-align: center;
        padding: 60px 20px;
        background: var(--sm-white);
        border-radius: var(--radius);
        box-shadow: var(--shadow-card);
        border: 2px solid rgba(37, 99, 235, 0.06);
    }
    .sm-empty .empty-icon {
        font-size: 80px;
        color: var(--sm-primary-light);
        margin-bottom: 20px;
        opacity: 0.5;
    }
    .sm-empty .empty-icon i {
        background: var(--sm-gray);
        padding: 30px;
        border-radius: 50%;
    }
    .sm-empty h3 {
        font-size: 24px;
        font-weight: 700;
        color: var(--sm-dark);
        margin-bottom: 8px;
    }
    .sm-empty p {
        color: var(--sm-gray-dark);
        margin-bottom: 24px;
    }
    .sm-empty .btn-primary-sm {
        background: var(--gradient-primary);
        color: white;
        border: none;
        padding: 14px 40px;
        border-radius: var(--radius);
        font-weight: 700;
        font-size: 16px;
        cursor: pointer;
        transition: var(--transition);
        text-decoration: none;
        display: inline-block;
        box-shadow: 0 8px 24px rgba(37, 99, 235, 0.3);
    }
    .sm-empty .btn-primary-sm:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 32px rgba(37, 99, 235, 0.4);
        color: white;
    }

    /* Toast */
    .sm-toast {
        position: fixed;
        bottom: 30px;
        right: 30px;
        background: white;
        padding: 16px 24px;
        border-radius: var(--radius);
        box-shadow: 0 8px 32px rgba(0,0,0,0.15);
        display: flex;
        align-items: center;
        gap: 12px;
        z-index: 9999;
        max-width: 420px;
        border-left: 4px solid var(--sm-primary);
        animation: slideInRight 0.4s ease forwards;
        font-weight: 500;
    }
    .sm-toast.success { border-left-color: var(--color-success-500); }
    .sm-toast.error { border-left-color: var(--color-danger-500); }
    .sm-toast.warning { border-left-color: var(--color-secondary-500); }
    .sm-toast.info { border-left-color: var(--sm-primary); }

    @keyframes slideInRight {
        from { opacity: 0; transform: translateX(100px); }
        to { opacity: 1; transform: translateX(0); }
    }

    /* Selos de Segurança */
    .sm-trust-badges {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        margin-top: 12px;
        padding-top: 12px;
        border-top: 2px solid var(--sm-gray);
    }
    .sm-trust-badges .badge-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        color: var(--sm-gray-dark);
        font-weight: 500;
        padding: 6px 10px;
        background: var(--sm-gray);
        border-radius: 8px;
        transition: var(--transition);
    }
    .sm-trust-badges .badge-item:hover {
        background: rgba(37, 99, 235, 0.06);
    }
    .sm-trust-badges .badge-item i { font-size: 16px; }
    .sm-trust-badges .badge-item i.text-primary { color: var(--color-primary-600); }
    .sm-trust-badges .badge-item i.text-success { color: var(--color-success-500); }
    .sm-trust-badges .badge-item i.text-warning { color: var(--color-secondary-500); }
    .sm-trust-badges .badge-item i.text-danger { color: var(--color-danger-500); }

    /* Responsivo */
    @media (max-width: 992px) {
        .sm-summary { position: static; margin-top: 16px; }
    }
    @media (max-width: 768px) {
        .sm-header .logo-text { font-size: 22px; }
        .sm-header .cart-title { font-size: 16px; }
        .sm-product-card { padding: 16px; }
        .sm-product-card .product-image { width: 60px; height: 60px; }
        .sm-product-card .product-price { font-size: 18px; }
        .sm-product-card .product-title { font-size: 14px; }
        .sm-qty-control button { padding: 4px 10px; min-width: 32px; font-size: 14px; }
        .sm-qty-control input { width: 38px; font-size: 13px; }
        .sm-product-card .product-checkbox { width: 16px; height: 16px; }
        .sm-toast { bottom: 16px; right: 16px; left: 16px; max-width: none; }
        .sm-summary { padding: 16px; }
        .sm-summary .summary-title { font-size: 16px; }
        .sm-summary .summary-row.total .total-value { font-size: 20px; }
        .sm-breadcrumb { font-size: 12px; padding: 10px 14px; }
        .sm-coupon .coupon-input-group {
            flex-direction: column;
        }
        .sm-coupon .coupon-input-group input {
            height: 42px;
        }
        .sm-coupon .btn-apply {
            height: 42px;
            width: 100%;
            min-width: unset;
        }
        .sm-shipping .shipping-input {
            flex-direction: column;
        }
        .sm-shipping .shipping-input input {
            height: 42px;
        }
        .sm-shipping .shipping-input button {
            height: 42px;
            width: 100%;
            min-width: unset;
        }
    }
    @media (max-width: 480px) {
        .sm-header { padding: 14px 0; border-radius: 0 0 20px 20px; }
        .sm-header .logo-text { font-size: 18px; }
        .sm-header .cart-title { font-size: 14px; }
        .sm-header .cart-count { font-size: 12px; padding: 2px 12px; }
        .sm-product-card { padding: 12px; }
        .sm-product-card .product-image { width: 50px; height: 50px; }
        .sm-product-card .product-price { font-size: 16px; }
        .sm-product-card .product-title { font-size: 13px; }
        .sm-product-card .product-sku { font-size: 10px; }
        .sm-qty-control button { padding: 4px 8px; min-width: 28px; font-size: 12px; }
        .sm-qty-control input { width: 32px; font-size: 12px; }
        .sm-action-btn { font-size: 12px; padding: 2px 8px; }
        .sm-trust-badges { grid-template-columns: 1fr; }
        .sm-empty .empty-icon { font-size: 60px; }
        .sm-empty h3 { font-size: 20px; }
        .sm-btn-checkout, .sm-btn-login { font-size: 14px; padding: 14px; }
    }
</style>

<!-- HEADER DA PÁGINA -->
<div class="sm-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-6 col-md-4">
                <div class="logo-text">
                    SM<span>Componentes</span>
                </div>
            </div>
            <div class="col-6 col-md-8">
                <div class="d-flex align-items-center justify-content-end gap-3">
                    <span class="cart-title d-none d-sm-inline">
                        <i class="fas fa-shopping-cart me-2"></i>
                        Meu Carrinho
                    </span>
                    <span class="cart-count">
                        <i class="fas fa-box me-1"></i>
                        {{ count($carrinho ?? []) }} itens
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="sm-container">
    <div class="container">
        <!-- BREADCRUMB -->
        <div class="sm-breadcrumb">
            <a href="{{ route('home') }}"><i class="fas fa-home me-1"></i> Início</a>
            <span class="separator">›</span>
            <a href="{{ route('produtos.index') }}">Produtos</a>
            <span class="separator">›</span>
            <span class="current">Carrinho</span>
        </div>

        <!-- CONTEÚDO PRINCIPAL -->
        @if(empty($carrinho) || count($carrinho) == 0)
            <div class="sm-empty">
                <div class="empty-icon">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <h3>Seu carrinho está vazio</h3>
                <p>Parece que você ainda não adicionou nenhum produto ao carrinho.</p>
                <a href="{{ route('produtos.index') }}" class="btn-primary-sm">
                    <i class="fas fa-store me-2"></i> Continuar Comprando
                </a>
            </div>
        @else
            <div class="row g-4">
                <!-- LISTA DE PRODUTOS -->
                <div class="col-lg-8">
                    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-3">
                            <input type="checkbox" id="selectAll" onchange="toggleSelectAll()" style="width: 18px; height: 18px; accent-color: var(--sm-primary); border-radius: 4px;">
                            <label for="selectAll" style="font-size: 14px; font-weight: 600; color: var(--sm-dark); cursor: pointer;">Selecionar todos</label>
                        </div>
                        <span style="font-size: 14px; color: var(--sm-gray-dark);">
                            <i class="fas fa-box me-1"></i>
                            {{ count($carrinho) }} produto(s)
                        </span>
                    </div>

                    @foreach($carrinho as $index => $item)
                        <div class="sm-product-card">
                            <div class="row g-3 align-items-start">
                                <div class="col-auto">
                                    <input type="checkbox" class="product-checkbox" data-index="{{ $index }}" onchange="updateTotal()" checked>
                                </div>
                                <div class="col-auto">
                                    <div class="product-image">
                                        @if(isset($item['imagem']) && $item['imagem'])
                                            @php
                                                // ✅ CORRIGIDO: Padrão Laravel - extrair apenas o nome do arquivo
                                                $filename = basename($item['imagem']);
                                            @endphp
                                            <img src="{{ asset('storage/produtos/' . $filename) }}" 
                                                 alt="{{ $item['nome'] ?? 'Produto' }}"
                                                 onerror="this.onerror=null; this.src='{{ asset('images/produto-placeholder.jpg') }}';">
                                        @else
                                            <i class="fas fa-microchip" style="font-size: 28px; color: var(--sm-primary-light);"></i>
                                        @endif
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="product-info">
                                        <div class="product-title">
                                            <a href="{{ route('produtos.show', $item['slug'] ?? $item['produto_id'] ?? 1) }}">
                                                {{ $item['nome'] ?? 'Produto' }}
                                            </a>
                                        </div>
                                        <div class="product-sku">
                                            <i class="fas fa-tag me-1"></i>
                                            SKU: {{ $item['codigo'] ?? $item['referencia'] ?? 'N/A' }}
                                        </div>
                                        <div class="mt-2">
                                            <span class="product-price">
                                                R$ {{ number_format($item['preco'] ?? 0, 2, ',', '.') }}
                                            </span>
                                            @if(isset($item['preco_promocional']) && $item['preco_promocional'] > 0 && $item['preco_promocional'] < $item['preco'])
                                                <span class="product-price-old">
                                                    R$ {{ number_format($item['preco_promocional'], 2, ',', '.') }}
                                                </span>
                                            @endif
                                            <div class="product-installments">
                                                <i class="fas fa-credit-card me-1"></i>
                                                em até 6x de R$ {{ number_format(($item['preco'] ?? 0) / 6, 2, ',', '.') }} sem juros
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <div class="d-flex flex-column align-items-end gap-2">
                                        <form action="{{ route('carrinho.atualizar', $index) }}" method="POST" class="d-inline" id="form-atualizar-{{ $index }}">
                                            @csrf
                                            @method('PUT')
                                            <div class="sm-qty-control">
                                                <button type="button" onclick="decrementar(this, {{ $index }})">−</button>
                                                <input type="number" name="quantidade" value="{{ $item['quantidade'] }}" min="1" max="{{ $item['estoque'] ?? 999 }}" onchange="document.getElementById('form-atualizar-{{ $index }}').submit()">
                                                <button type="button" onclick="incrementar(this, {{ $index }})">+</button>
                                            </div>
                                        </form>
                                        <div class="d-flex gap-1 flex-wrap justify-content-end">
                                            <button class="sm-action-btn" onclick="moverParaWishlist({{ $index }})">
                                                <i class="far fa-heart me-1"></i> Salvar
                                            </button>
                                            <form action="{{ route('carrinho.remover', $index) }}" method="POST" class="d-inline" id="form-remover-{{ $index }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="sm-action-btn danger" onclick="return confirm('Remover este produto do carrinho?')">
                                                    <i class="fas fa-trash-alt me-1"></i> Remover
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3 pt-3" style="border-top: 2px solid var(--sm-gray);">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <span class="product-shipping">
                                            <i class="fas fa-truck"></i>
                                            Frete grátis
                                        </span>
                                        <span style="font-size: 12px; color: var(--sm-gray-dark);" class="ms-2">
                                            Entrega em até 5 dias úteis
                                        </span>
                                    </div>
                                    <div class="col-md-6 text-md-end">
                                        <span style="font-size: 13px; color: var(--sm-gray-dark);">
                                            <i class="fas fa-store me-1" style="color: var(--sm-primary);"></i>
                                            Vendido por SM Componentes
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <div class="d-flex flex-wrap gap-3 mt-3">
                        <a href="{{ route('produtos.index') }}" class="sm-action-btn">
                            <i class="fas fa-arrow-left me-2"></i> Continuar Comprando
                        </a>
                        <form action="{{ route('carrinho.limpar') }}" method="POST" class="d-inline" id="form-limpar">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="sm-action-btn danger" onclick="return confirm('Limpar todo o carrinho?')">
                                <i class="fas fa-trash-alt me-2"></i> Limpar Carrinho
                            </button>
                        </form>
                    </div>
                </div>

                <!-- RESUMO DO PEDIDO -->
                <div class="col-lg-4">
                    <div class="sm-summary">
                        <div class="summary-title">
                            <i class="fas fa-receipt"></i>
                            Resumo do Pedido
                        </div>
                        <div class="summary-row">
                            <span>Produtos ({{ count($carrinho) }})</span>
                            <span id="subtotalText">R$ {{ number_format($total ?? 0, 2, ',', '.') }}</span>
                        </div>
                        <div class="summary-row">
                            <span>Frete</span>
                            <span style="color: var(--color-success-500); font-weight: 600;">Gratuito</span>
                        </div>
                        <div class="summary-row">
                            <span>Desconto</span>
                            <span class="discount-value" id="descontoText">− R$ 0,00</span>
                        </div>
                        <div class="summary-row total">
                            <span>Total</span>
                            <span class="total-value" id="totalValue">
                                R$ {{ number_format($total ?? 0, 2, ',', '.') }}
                            </span>
                        </div>

                        <!-- Cupom -->
                        <div class="sm-coupon">
                            <label>
                                <i class="fas fa-ticket-alt me-1" style="color: var(--sm-primary);"></i>
                                Cupom de desconto
                            </label>
                            <div class="coupon-input-group">
                                <input type="text" id="couponInput" placeholder="Digite seu cupom">
                                <button class="btn-apply" onclick="aplicarCupom()">Aplicar</button>
                            </div>
                            <div id="couponResult" class="mt-2" style="font-size: 13px;"></div>
                        </div>

                        <!-- Frete -->
                        <div class="sm-shipping">
                            <div class="shipping-title">
                                <i class="fas fa-truck" style="color: var(--sm-primary);"></i>
                                Calcular frete
                            </div>
                            <div class="shipping-input">
                                <input type="text" id="cepInput" placeholder="Digite seu CEP" maxlength="9">
                                <button onclick="calcularFrete()">Calcular</button>
                            </div>
                            <div id="freteResultado" class="shipping-result"></div>
                        </div>

                        <!-- Checkout -->
                        @auth
                            <a href="{{ route('checkout.index') }}" class="sm-btn-checkout" id="checkoutBtn">
                                <i class="fas fa-lock"></i> Finalizar Compra
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="sm-btn-login">
                                <i class="fas fa-sign-in-alt"></i> Faça login para finalizar
                            </a>
                            <div class="text-center mt-2" style="font-size: 13px; color: var(--sm-gray-dark);">
                                Já tem conta? <a href="{{ route('login') }}" style="color: var(--sm-primary); text-decoration: none; font-weight: 600;">Clique aqui</a>
                            </div>
                        @endauth

                        <!-- Selos de Segurança -->
                        <div class="sm-trust-badges">
                            <span class="badge-item">
                                <i class="fas fa-shield-alt text-success"></i>
                                Compra segura
                            </span>
                            <span class="badge-item">
                                <i class="fas fa-credit-card text-primary"></i>
                                Parcelamento
                            </span>
                            <span class="badge-item">
                                <i class="fas fa-exchange-alt text-warning"></i>
                                Troca fácil
                            </span>
                            <span class="badge-item">
                                <i class="fas fa-headset text-danger"></i>
                                Suporte 24h
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
    // ============================================
    // FUNÇÕES SM COMPONENTES
    // ============================================

    function decrementar(btn, index) {
        let input = btn.parentElement.querySelector('input[name="quantidade"]');
        if (parseInt(input.value) > 1) {
            input.value = parseInt(input.value) - 1;
            document.getElementById('form-atualizar-' + index).submit();
        }
    }

    function incrementar(btn, index) {
        let input = btn.parentElement.querySelector('input[name="quantidade"]');
        let max = parseInt(input.max) || 999;
        if (parseInt(input.value) < max) {
            input.value = parseInt(input.value) + 1;
            document.getElementById('form-atualizar-' + index).submit();
        }
    }

    function toggleSelectAll() {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.product-checkbox');
        checkboxes.forEach(cb => cb.checked = selectAll.checked);
        updateTotal();
    }

    function updateTotal() {
        let total = 0;
        const checkboxes = document.querySelectorAll('.product-checkbox:checked');
        checkboxes.forEach(cb => {
            const card = cb.closest('.sm-product-card');
            const priceText = card.querySelector('.product-price')?.textContent || 'R$ 0,00';
            const price = parseFloat(priceText.replace(/[^0-9,]/g, '').replace(',', '.')) || 0;
            total += price;
        });
        const totalEl = document.getElementById('totalValue');
        const subtotalEl = document.getElementById('subtotalText');
        if (totalEl) {
            totalEl.textContent = 'R$ ' + total.toFixed(2).replace('.', ',');
        }
        if (subtotalEl) {
            subtotalEl.textContent = 'R$ ' + total.toFixed(2).replace('.', ',');
        }
    }

    function aplicarCupom() {
        const input = document.getElementById('couponInput');
        const result = document.getElementById('couponResult');
        const codigo = input.value.trim();

        if (!codigo) {
            result.innerHTML = '<span style="color: var(--color-danger-500);">⚠️ Digite um código de cupom válido.</span>';
            return;
        }

        const cupons = { 
            'SM10': 0.10, 
            'SM20': 0.20, 
            'SMFRETE': 0,
            'SM15': 0.15,
            'SM25': 0.25,
            'FRETEGRATIS': 0,
            'BLACKSM': 0.30
        };

        const codigoUpper = codigo.toUpperCase();

        if (cupons[codigoUpper] !== undefined) {
            const desconto = cupons[codigoUpper];
            const totalElement = document.getElementById('totalValue');
            const totalAtualTexto = totalElement.textContent.replace(/[^0-9,]/g, '').replace(',', '.');
            const totalAtual = parseFloat(totalAtualTexto) || 0;
            
            if (desconto === 0) {
                result.innerHTML = '<span style="color: var(--color-success-500);">✅ Frete grátis aplicado!</span>';
                mostrarToast('Sucesso', 'Frete grátis aplicado!', 'success');
            } else {
                const novoTotal = totalAtual * (1 - desconto);
                totalElement.textContent = 'R$ ' + novoTotal.toFixed(2).replace('.', ',');
                
                const descontoEl = document.getElementById('descontoText');
                if (descontoEl) {
                    const valorDesconto = totalAtual - novoTotal;
                    descontoEl.textContent = '− R$ ' + valorDesconto.toFixed(2).replace('.', ',');
                }
                
                result.innerHTML = `<span style="color: var(--color-success-500);">✅ Cupom aplicado! ${desconto * 100}% de desconto.</span>`;
                mostrarToast('Sucesso', `Cupom ${codigoUpper} aplicado! ${desconto * 100}% de desconto.`, 'success');
            }
            input.value = '';
        } else {
            result.innerHTML = '<span style="color: var(--color-danger-500);">❌ Cupom inválido ou expirado.</span>';
            mostrarToast('Erro', 'Cupom inválido ou expirado.', 'error');
        }
    }

    function calcularFrete() {
        const input = document.getElementById('cepInput');
        const resultado = document.getElementById('freteResultado');
        const cep = input.value.trim().replace(/\D/g, '');

        if (cep.length < 8) {
            resultado.innerHTML = '<span class="error">⚠️ Digite um CEP válido com 8 dígitos.</span>';
            return;
        }

        resultado.innerHTML = '<span>⏳ Calculando frete...</span>';

        setTimeout(() => {
            const cepNum = parseInt(cep);
            let freteBase = 0;
            
            if (cepNum >= 60000000 && cepNum <= 69999999) {
                freteBase = 5.00;
            } else if (cepNum >= 70000000 && cepNum <= 79999999) {
                freteBase = 8.00;
            } else if (cepNum >= 80000000 && cepNum <= 89999999) {
                freteBase = 10.00;
            } else if (cepNum >= 90000000 && cepNum <= 99999999) {
                freteBase = 12.00;
            } else {
                freteBase = 15.00;
            }

            const fretes = [
                { nome: 'Sedex', preco: (freteBase + Math.random() * 10 + 5).toFixed(2), prazo: Math.floor(Math.random() * 3 + 2) },
                { nome: 'PAC', preco: (freteBase + Math.random() * 5 + 3).toFixed(2), prazo: Math.floor(Math.random() * 5 + 5) },
                { nome: 'Motoboy (Fortaleza)', preco: (freteBase + Math.random() * 2 + 1).toFixed(2), prazo: '1 dia' }
            ];

            let html = '<div class="d-flex flex-column gap-1">';
            fretes.forEach(f => {
                html += `
                    <div class="d-flex justify-content-between align-items-center" style="padding: 4px 0; border-bottom: 1px solid var(--sm-gray);">
                        <span><strong>${f.nome}</strong> (${f.prazo} ${typeof f.prazo === 'number' ? 'dias úteis' : ''})</span>
                        <span style="font-weight: 600; color: var(--sm-dark);">R$ ${f.preco.replace('.', ',')}</span>
                    </div>
                `;
            });
            html += '</div>';
            resultado.innerHTML = html;
        }, 1000);
    }

    function moverParaWishlist(index) {
        mostrarToast('Sucesso', 'Produto movido para sua lista de desejos! ❤️', 'success');
    }

    function mostrarToast(titulo, mensagem, tipo = 'success') {
        const colors = {
            success: { bg: '#dcfce7', border: 'var(--color-success-500)', icon: 'fa-check-circle', color: '#166534' },
            error: { bg: '#fee2e2', border: 'var(--color-danger-500)', icon: 'fa-exclamation-circle', color: '#991b1b' },
            warning: { bg: '#ffedd5', border: 'var(--color-secondary-500)', icon: 'fa-exclamation-triangle', color: '#9a3412' },
            info: { bg: '#dbeafe', border: 'var(--color-primary-600)', icon: 'fa-info-circle', color: '#1e40af' }
        };

        const style = colors[tipo] || colors.info;
        const toast = document.createElement('div');
        toast.className = `sm-toast ${tipo}`;
        toast.style.borderLeftColor = style.border;
        toast.innerHTML = `
            <i class="fas ${style.icon}" style="color: ${style.border}; font-size: 20px;"></i>
            <div>
                <strong>${titulo}</strong>
                <div style="font-size: 14px; color: #555;">${mensagem}</div>
            </div>
            <button onclick="this.parentElement.remove()" style="background: none; border: none; font-size: 18px; cursor: pointer; color: #999; margin-left: auto; padding: 0 4px;">
                <i class="fas fa-times"></i>
            </button>
        `;

        document.body.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100px)';
            toast.style.transition = 'all 0.4s ease';
            setTimeout(() => toast.remove(), 400);
        }, 5000);
    }

    document.addEventListener('DOMContentLoaded', function() {
        const cepInput = document.getElementById('cepInput');
        if (cepInput) {
            cepInput.addEventListener('input', function(e) {
                let value = this.value.replace(/\D/g, '');
                if (value.length > 5) {
                    value = value.substring(0, 5) + '-' + value.substring(5, 8);
                }
                this.value = value;
            });
        }
        updateTotal();
    });
</script>
@endsection