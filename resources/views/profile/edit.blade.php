<x-app-layout>
    <x-slot name="header">
        {{-- ============================================
             HEADER - Psicologia: Identidade e Confiança
             ============================================ --}}
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight flex items-center gap-3">
                <i class="bi bi-person-circle text-primary" style="font-size: 1.5rem;" aria-hidden="true"></i>
                {{ __('Meu Perfil') }}
            </h2>
            
            {{-- Badge de status - Psicologia: Segurança --}}
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-success-100 dark:bg-success-900/30 text-success-700 dark:text-success-400 border border-success-200 dark:border-success-800/50">
                    <i class="bi bi-shield-check me-1" aria-hidden="true"></i>
                    {{ __('Conta verificada') }}
                </span>
            </div>
        </div>
    </x-slot>

    {{-- ============================================
         CONTEÚDO PRINCIPAL
         Psicologia: Organização e Clareza
         ============================================ --}}
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- ============================================
                 CARDS DE CONFIGURAÇÃO
                 Psicologia: Hierarquia e Confiança
                 ============================================ --}}
            
            {{-- 1. INFORMAÇÕES DO PERFIL --}}
            <div class="profile-card p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-lg hover:shadow-xl transition-all duration-300 rounded-2xl border border-gray-100 dark:border-gray-700">
                <div class="card-header flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center">
                        <i class="bi bi-person text-primary-600 dark:text-primary-400" style="font-size: 1.2rem;" aria-hidden="true"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ __('Informações do Perfil') }}
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ __('Gerencie seus dados pessoais') }}
                        </p>
                    </div>
                </div>
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            {{-- 2. ATUALIZAR SENHA --}}
            <div class="profile-card p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-lg hover:shadow-xl transition-all duration-300 rounded-2xl border border-gray-100 dark:border-gray-700">
                <div class="card-header flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-secondary-100 dark:bg-secondary-900/30 flex items-center justify-center">
                        <i class="bi bi-shield-lock text-secondary-600 dark:text-secondary-400" style="font-size: 1.2rem;" aria-hidden="true"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ __('Atualizar Senha') }}
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ __('Mantenha sua conta segura') }}
                        </p>
                    </div>
                </div>
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            {{-- 3. EXCLUIR CONTA - Psicologia: Atenção e Cuidado --}}
            <div class="profile-card p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-lg hover:shadow-xl transition-all duration-300 rounded-2xl border border-danger-100 dark:border-danger-900/30">
                <div class="card-header flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-danger-100 dark:bg-danger-900/30 flex items-center justify-center">
                        <i class="bi bi-trash3 text-danger-600 dark:text-danger-400" style="font-size: 1.2rem;" aria-hidden="true"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ __('Excluir Conta') }}
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ __('Ação permanente e irreversível') }}
                        </p>
                    </div>
                </div>
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>

        </div>
    </div>
</x-app-layout>

{{-- ============================================
         ESTILOS ADICIONAIS
         ============================================ --}}
<style>
    /* ============================================
       PSICOLOGIA DAS CORES APLICADA
       ============================================
       
       🔵 AZUL (#3b82f6, #2563eb, #1d4ed8)
       → Confiança, Segurança, Profissionalismo
       → Usado em: Header, Ícones principais, Cards
       
       🟠 LARANJA (#f97316, #ea580c)
       → Energia, Ação, Destaque
       → Usado em: Badges de status, Ícones secundários
       
       🟢 VERDE (#22c55e, #16a34a)
       → Sucesso, Confirmação, Segurança
       → Usado em: Badge de verificação
       
       🔴 VERMELHO (#ef4444, #dc2626)
       → Atenção, Cuidado, Ação Crítica
       → Usado em: Card de exclusão, Alertas
    */

    /* Cards com efeito de elevação */
    .profile-card {
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .profile-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, transparent, var(--card-accent, #3b82f6), transparent);
        opacity: 0;
        transition: opacity 0.4s ease;
    }

    .profile-card:hover::before {
        opacity: 1;
    }

    .profile-card:first-child {
        --card-accent: #3b82f6;
    }

    .profile-card:nth-child(2) {
        --card-accent: #f97316;
    }

    .profile-card:nth-child(3) {
        --card-accent: #ef4444;
    }

    /* Cards com gradiente de hover */
    .profile-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08), 0 8px 20px rgba(0, 0, 0, 0.04);
    }

    .dark .profile-card:hover {
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3), 0 8px 20px rgba(0, 0, 0, 0.2);
    }

    /* Cabeçalho dos cards com ícones */
    .card-header {
        position: relative;
    }

    .card-header::after {
        content: '';
        position: absolute;
        bottom: -6px;
        left: 0;
        width: 40px;
        height: 2px;
        background: var(--card-accent, #3b82f6);
        border-radius: 2px;
        opacity: 0.3;
    }

    /* Badge de status */
    .bg-success-100 {
        background-color: #dcfce7;
    }

    .dark .bg-success-100 {
        background-color: #052e16;
    }

    .text-success-700 {
        color: #15803d;
    }

    .dark .text-success-700 {
        color: #4ade80;
    }

    .border-success-200 {
        border-color: #bbf7d0;
    }

    .dark .border-success-200 {
        border-color: #166534;
    }

    /* Cores dos ícones */
    .bg-primary-100 {
        background-color: #dbeafe;
    }

    .dark .bg-primary-100 {
        background-color: #1e3a8a;
    }

    .text-primary-600 {
        color: #2563eb;
    }

    .dark .text-primary-600 {
        color: #60a5fa;
    }

    .bg-secondary-100 {
        background-color: #fff7ed;
    }

    .dark .bg-secondary-100 {
        background-color: #7c2d12;
    }

    .text-secondary-600 {
        color: #ea580c;
    }

    .dark .text-secondary-600 {
        color: #fb923c;
    }

    .bg-danger-100 {
        background-color: #fee2e2;
    }

    .dark .bg-danger-100 {
        background-color: #7f1d1d;
    }

    .text-danger-600 {
        color: #dc2626;
    }

    .dark .text-danger-600 {
        color: #f87171;
    }

    /* Responsividade */
    @media (max-width: 640px) {
        .profile-card {
            padding: 20px !important;
            border-radius: 16px !important;
        }

        .profile-card .max-w-xl {
            max-width: 100%;
        }

        .card-header {
            flex-direction: column;
            align-items: flex-start !important;
            gap: 8px !important;
        }

        .card-header .w-10.h-10 {
            width: 40px;
            height: 40px;
        }

        .card-header h3 {
            font-size: 1.1rem;
        }

        .flex.items-center.justify-between {
            flex-direction: column;
            align-items: flex-start !important;
            gap: 12px;
        }

        .flex.items-center.justify-between .gap-3 {
            flex-wrap: wrap;
        }

        .py-12 {
            padding-top: 30px;
            padding-bottom: 30px;
        }

        .space-y-6 {
            gap: 20px;
        }
    }

    @media (max-width: 480px) {
        .profile-card {
            padding: 16px !important;
        }

        .card-header .w-10.h-10 {
            width: 36px;
            height: 36px;
        }

        .card-header .w-10.h-10 i {
            font-size: 1rem !important;
        }

        .card-header h3 {
            font-size: 1rem;
        }

        .card-header p {
            font-size: 0.8rem;
        }

        .badge-verified {
            font-size: 0.65rem;
            padding: 4px 10px;
        }

        .text-xl {
            font-size: 1.1rem !important;
        }
    }

    /* Dark mode improvements */
    .dark .profile-card {
        background: #1f2937 !important;
        border-color: #374151 !important;
    }

    .dark .profile-card .text-gray-900 {
        color: #f3f4f6 !important;
    }

    .dark .profile-card .text-gray-800 {
        color: #e5e7eb !important;
    }

    .dark .profile-card .text-gray-500 {
        color: #9ca3af !important;
    }

    /* Acessibilidade */
    .profile-card:focus-visible {
        outline: 3px solid #3b82f6;
        outline-offset: 2px;
    }

    /* Suporte a preferência de redução de movimento */
    @media (prefers-reduced-motion: reduce) {
        .profile-card,
        .profile-card::before,
        .profile-card:hover {
            transition: none !important;
            transform: none !important;
            animation: none !important;
        }

        .profile-card:hover {
            transform: none !important;
        }
    }

    /* Animação de entrada dos cards */
    .profile-card {
        opacity: 0;
        transform: translateY(20px);
        animation: card-fade-in 0.6s ease forwards;
    }

    .profile-card:nth-child(1) { animation-delay: 0.1s; }
    .profile-card:nth-child(2) { animation-delay: 0.2s; }
    .profile-card:nth-child(3) { animation-delay: 0.3s; }

    @keyframes card-fade-in {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Scrollbar personalizada para o container */
    .max-w-7xl {
        scroll-behavior: smooth;
    }

    /* Tooltip de ajuda para o header */
    .help-tip-header {
        position: relative;
        cursor: help;
    }

    .help-tip-header:hover::after {
        content: 'Gerencie suas configurações de perfil';
        position: absolute;
        bottom: calc(100% + 8px);
        left: 50%;
        transform: translateX(-50%);
        background: #1f2937;
        color: #f3f4f6;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 0.75rem;
        white-space: nowrap;
        z-index: 10;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    /* Efeito de loading skeleton para os cards */
    .profile-card.loading .card-header,
    .profile-card.loading .max-w-xl {
        opacity: 0.5;
        pointer-events: none;
    }

    .profile-card.loading::after {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 200%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
        animation: loading-shimmer 1.5s infinite;
    }

    @keyframes loading-shimmer {
        0% { transform: translateX(-100%); }
        100% { transform: translateX(100%); }
    }
</style>

{{-- ============================================
         SCRIPT PARA INTERAÇÕES ADICIONAIS
         ============================================ --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ============================================
    // 1. ANIMAÇÃO DE ENTRADA DOS CARDS
    // ============================================
    const cards = document.querySelectorAll('.profile-card');
    
    // Usar Intersection Observer para animar cards quando visíveis
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animation = 'card-fade-in 0.6s ease forwards';
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '50px'
    });

    cards.forEach(card => {
        // Resetar animação se já estiver estilizada
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        observer.observe(card);
    });

    // ============================================
    // 2. NAVEGAÇÃO POR TABS (Mobile)
    // ============================================
    const isMobile = window.innerWidth < 640;
    
    if (isMobile) {
        // Adicionar indicadores de seção para mobile
        const sections = document.querySelectorAll('.profile-card');
        const navContainer = document.createElement('div');
        navContainer.className = 'mobile-nav flex gap-2 overflow-x-auto pb-3 mb-4 sm:hidden';
        navContainer.style.cssText = `
            display: flex;
            gap: 8px;
            overflow-x: auto;
            padding-bottom: 12px;
            margin-bottom: 16px;
            -webkit-overflow-scrolling: touch;
            scroll-snap-type: x mandatory;
        `;

        sections.forEach((section, index) => {
            const title = section.querySelector('h3')?.textContent || `Seção ${index + 1}`;
            const icon = section.querySelector('.card-header i')?.className || 'bi bi-circle';
            const color = section.classList.contains('border-danger-100') ? 'danger' : 
                         index === 1 ? 'secondary' : 'primary';

            const btn = document.createElement('button');
            btn.className = `flex-shrink-0 px-4 py-2 rounded-full text-xs font-medium transition-all duration-300 scroll-snap-align-start`;
            btn.style.cssText = `
                background: ${color === 'danger' ? '#fef2f2' : color === 'secondary' ? '#fff7ed' : '#eff6ff'};
                color: ${color === 'danger' ? '#dc2626' : color === 'secondary' ? '#ea580c' : '#2563eb'};
                border: 2px solid ${color === 'danger' ? '#fecaca' : color === 'secondary' ? '#fed7aa' : '#bfdbfe'};
                white-space: nowrap;
            `;
            btn.innerHTML = `<i class="${icon} me-1" aria-hidden="true"></i> ${title}`;
            
            btn.addEventListener('click', () => {
                section.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'start',
                    inline: 'nearest'
                });
            });

            navContainer.appendChild(btn);
        });

        // Inserir navegação mobile antes dos cards
        const container = document.querySelector('.max-w-7xl');
        if (container && !container.querySelector('.mobile-nav')) {
            container.insertBefore(navContainer, container.firstChild);
        }
    }

    // ============================================
    // 3. DESTAQUE DO CARD ATIVO (Scroll Spy)
    // ============================================
    if ('IntersectionObserver' in window) {
        const sectionObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const card = entry.target;
                    const navBtns = document.querySelectorAll('.mobile-nav button');
                    const title = card.querySelector('h3')?.textContent;
                    
                    navBtns.forEach(btn => {
                        const isActive = btn.textContent.includes(title || '');
                        if (isActive) {
                            btn.style.transform = 'scale(1.05)';
                            btn.style.boxShadow = '0 4px 12px rgba(0,0,0,0.1)';
                        } else {
                            btn.style.transform = 'scale(1)';
                            btn.style.boxShadow = 'none';
                        }
                    });
                }
            });
        }, {
            threshold: 0.3,
            rootMargin: '-50px 0px'
        });

        document.querySelectorAll('.profile-card').forEach(card => {
            sectionObserver.observe(card);
        });
    }

    // ============================================
    // 4. ATUALIZAR NAVEGAÇÃO MOBILE NO REDIMENSIONAMENTO
    // ============================================
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            const isMobileNow = window.innerWidth < 640;
            const nav = document.querySelector('.mobile-nav');
            
            if (isMobileNow && !nav) {
                // Recarregar navegação mobile
                location.reload();
            } else if (!isMobileNow && nav) {
                nav.remove();
            }
        }, 250);
    });

    // ============================================
    // 5. ANIMAÇÃO DE RIPPLE NOS BOTÕES DOS CARDS
    // ============================================
    document.querySelectorAll('.profile-card button[type="submit"]').forEach(button => {
        button.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            
            ripple.style.cssText = `
                position: absolute;
                border-radius: 50%;
                background: rgba(255,255,255,0.3);
                width: ${size}px;
                height: ${size}px;
                left: ${e.clientX - rect.left - size/2}px;
                top: ${e.clientY - rect.top - size/2}px;
                transform: scale(0);
                animation: ripple-effect 0.6s ease-out;
                pointer-events: none;
            `;
            
            this.style.position = 'relative';
            this.style.overflow = 'hidden';
            this.appendChild(ripple);
            
            setTimeout(() => ripple.remove(), 600);
        });
    });

    // ============================================
    // 6. SCROLL SUAVE PARA CARDS
    // ============================================
    document.querySelectorAll('.profile-card').forEach(card => {
        card.addEventListener('click', function(e) {
            // Se clicou em um link ou botão dentro do card, não scrollar
            if (e.target.closest('a') || e.target.closest('button') || e.target.closest('input')) {
                return;
            }
            
            // Scroll suave para o card em mobile
            if (window.innerWidth < 640) {
                this.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    });

    // ============================================
    // 7. DETECTAR MUDANÇAS DE TEMA
    // ============================================
    if (window.matchMedia) {
        const darkModeMedia = window.matchMedia('(prefers-color-scheme: dark)');
        
        darkModeMedia.addEventListener('change', function(e) {
            // Ajustar cores dos cards baseado no tema
            document.querySelectorAll('.profile-card').forEach((card, index) => {
                if (e.matches) {
                    // Dark mode
                    card.style.borderColor = '#374151';
                } else {
                    // Light mode
                    const colors = ['#e5e7eb', '#e5e7eb', '#fecaca'];
                    card.style.borderColor = colors[index] || '#e5e7eb';
                }
            });
        });
    }
});
</script>
