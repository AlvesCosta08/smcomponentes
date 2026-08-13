<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-white">
            <i class="bi bi-trash3 text-danger me-2" aria-hidden="true"></i>
            {{ __('Excluir Conta') }}
        </h2>

        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            <i class="bi bi-info-circle text-primary me-1" aria-hidden="true"></i>
            {{ __('Ao excluir sua conta, todos os seus dados e recursos serão permanentemente removidos. Antes de excluir, faça o download de qualquer informação que deseja manter.') }}
        </p>
    </header>

    {{-- ============================================
         BOTÃO DE EXCLUSÃO - Psicologia: Atenção e Urgência
         ============================================ --}}
    <button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="btn-danger-gradient inline-flex items-center px-6 py-3 rounded-xl font-semibold text-white transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-[1.02] active:scale-[0.98]"
        style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 50%, #b91c1c 100%);"
    >
        <i class="bi bi-exclamation-triangle-fill me-2" aria-hidden="true"></i>
        {{ __('Excluir Conta') }}
    </button>

    {{-- ============================================
         MODAL DE CONFIRMAÇÃO - Psicologia: Alerta e Reflexão
         ============================================ --}}
    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            {{-- Cabeçalho do Modal - Psicologia: Alerta --}}
            <div class="flex items-start gap-4 mb-4">
                <div class="flex-shrink-0 w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                    <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size: 1.5rem;"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                        {{ __('Tem certeza que deseja excluir sua conta?') }}
                    </h2>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                        <i class="bi bi-info-circle text-primary me-1" aria-hidden="true"></i>
                        {{ __('Esta ação é permanente e não pode ser desfeita. Todos os seus dados, pedidos e informações serão removidos permanentemente.') }}
                    </p>
                </div>
            </div>

            {{-- Aviso Adicional - Psicologia: Confiabilidade --}}
            <div class="mb-6 p-4 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/50">
                <div class="flex items-start gap-3">
                    <i class="bi bi-shield-exclamation text-amber-600 dark:text-amber-400 mt-0.5" style="font-size: 1.2rem;"></i>
                    <div>
                        <p class="text-sm font-medium text-amber-800 dark:text-amber-300">
                            {{ __('Para sua segurança, confirme sua senha para prosseguir') }}
                        </p>
                        <p class="text-xs text-amber-600 dark:text-amber-400 mt-0.5">
                            {{ __('Digite sua senha atual para verificar sua identidade') }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Campo de Senha - Psicologia: Segurança --}}
            <div class="mb-6">
                <x-input-label for="password" value="{{ __('Senha') }}" class="sr-only" />
                
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="bi bi-lock text-gray-400 dark:text-gray-500" aria-hidden="true"></i>
                    </div>
                    <x-text-input
                        id="password"
                        name="password"
                        type="password"
                        class="mt-1 block w-full pl-10 pr-4 py-3 rounded-xl border-2 border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:border-danger-500 focus:ring-2 focus:ring-danger-500/20 transition-all duration-300"
                        placeholder="{{ __('Digite sua senha para confirmar') }}"
                        autofocus
                    />
                </div>

                @if ($errors->userDeletion->get('password'))
                    <div class="mt-2 flex items-center gap-2 text-sm text-danger-600 dark:text-danger-400">
                        <i class="bi bi-exclamation-circle" aria-hidden="true"></i>
                        <span>{{ $errors->userDeletion->first('password') }}</span>
                    </div>
                @endif
            </div>

            {{-- Botões de Ação - Psicologia: Reflexão e Decisão --}}
            <div class="mt-6 flex flex-col sm:flex-row justify-end gap-3">
                <button
                    type="button"
                    x-on:click="$dispatch('close')"
                    class="inline-flex items-center justify-center px-6 py-3 rounded-xl font-semibold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition-all duration-300 border-2 border-transparent hover:border-gray-300 dark:hover:border-gray-500"
                >
                    <i class="bi bi-x-circle me-2" aria-hidden="true"></i>
                    {{ __('Cancelar') }}
                </button>

                <button
                    type="submit"
                    class="inline-flex items-center justify-center px-6 py-3 rounded-xl font-semibold text-white transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-[1.02] active:scale-[0.98]"
                    style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 50%, #b91c1c 100%);"
                >
                    <i class="bi bi-trash3 me-2" aria-hidden="true"></i>
                    {{ __('Excluir Conta Permanentemente') }}
                </button>
            </div>
        </form>
    </x-modal>
</section>

{{-- ============================================
         ESTILOS ADICIONAIS
         ============================================ --}}
<style>
    /* ============================================
       PSICOLOGIA DAS CORES APLICADA
       ============================================
       
       🔴 VERMELHO (#ef4444, #dc2626, #b91c1c)
       → Urgência, Atenção, Ação Crítica
       → Usado em: Botão de exclusão, Ícones de alerta
       
       🟡 AMARELO/LARANJA (#f59e0b, #d97706)
       → Atenção, Cuidado, Precaução
       → Usado em: Avisos, Alertas
       
       🔵 AZUL (#3b82f6, #2563eb)
       → Confiança, Segurança, Informação
       → Usado em: Ícones informativos
    */

    /* Botão de perigo com gradiente */
    .btn-danger-gradient {
        position: relative;
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 50%, #b91c1c 100%);
        background-size: 200% 200%;
        animation: gradient-shift 4s ease-in-out infinite;
    }

    .btn-danger-gradient::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
        transition: left 0.6s ease;
    }

    .btn-danger-gradient:hover::before {
        left: 100%;
    }

    @keyframes gradient-shift {
        0%, 100% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
    }

    /* Efeito de pulso para o botão de exclusão */
    .btn-danger-gradient {
        animation: pulse-danger 3s ease-in-out infinite;
    }

    @keyframes pulse-danger {
        0%, 100% { 
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
        }
        50% { 
            box-shadow: 0 4px 30px rgba(239, 68, 68, 0.5);
        }
    }

    /* Input de senha com foco melhorado */
    input#password:focus {
        border-color: #dc2626 !important;
        box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.15) !important;
    }

    /* Modal com animação suave */
    .modal-content {
        border-radius: 16px;
        border: none;
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
        overflow: hidden;
    }

    /* Responsividade para telas menores */
    @media (max-width: 640px) {
        .btn-danger-gradient {
            width: 100%;
            justify-content: center;
            padding: 12px 20px;
        }

        .modal-content .p-6 {
            padding: 20px !important;
        }

        .btn-danger-gradient,
        button[type="submit"],
        button[type="button"] {
            width: 100%;
            justify-content: center;
        }

        .flex-col.sm\:flex-row {
            flex-direction: column;
        }

        .flex-col.sm\:flex-row .gap-3 {
            gap: 12px;
        }
    }

    /* Dark mode improvements */
    .dark .btn-danger-gradient {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 50%, #991b1b 100%);
    }

    .dark input#password {
        background-color: #1f2937;
        border-color: #4b5563;
        color: #f3f4f6;
    }

    .dark input#password::placeholder {
        color: #9ca3af;
    }

    /* Acessibilidade - foco visível */
    button:focus-visible,
    input:focus-visible {
        outline: 3px solid #dc2626;
        outline-offset: 2px;
    }

    /* Suporte a preferência de redução de movimento */
    @media (prefers-reduced-motion: reduce) {
        .btn-danger-gradient,
        .btn-danger-gradient::before,
        .btn-danger-gradient {
            animation: none !important;
            transition: none !important;
        }

        .btn-danger-gradient:hover {
            transform: none !important;
        }
    }

    /* Scroll suave para o modal */
    .modal {
        scroll-behavior: smooth;
    }

    /* Animação de entrada do modal */
    .modal.fade .modal-dialog {
        transform: scale(0.95) translateY(-20px);
        transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.25s ease;
    }

    .modal.show .modal-dialog {
        transform: scale(1) translateY(0);
    }

    /* Ícone de senha com tooltip */
    .password-hint {
        position: relative;
        cursor: help;
    }

    .password-hint:hover::after {
        content: 'Digite sua senha atual para confirmar';
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
</style>

{{-- ============================================
         SCRIPT PARA ANIMAÇÕES ADICIONAIS
         ============================================ --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Adicionar efeito de ripple aos botões de perigo
        document.querySelectorAll('.btn-danger-gradient, button[type="submit"]').forEach(button => {
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

        // Adicionar animação de shake no erro de senha
        const passwordInput = document.getElementById('password');
        const errorMessage = document.querySelector('.text-danger-600');
        
        if (passwordInput && errorMessage) {
            passwordInput.addEventListener('input', function() {
                if (this.value.length > 0) {
                    const error = document.querySelector('.text-danger-600');
                    if (error) {
                        error.style.transition = 'opacity 0.3s ease';
                        error.style.opacity = '0';
                        setTimeout(() => error.style.display = 'none', 300);
                    }
                }
            });
        }

        // Validar senha em tempo real
        if (passwordInput) {
            passwordInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    const form = this.closest('form');
                    if (form) {
                        const submitBtn = form.querySelector('button[type="submit"]');
                        if (submitBtn) {
                            submitBtn.click();
                        }
                    }
                }
            });
        }
    });

    // Estilo para a animação de ripple
    const style = document.createElement('style');
    style.textContent = `
        @keyframes ripple-effect {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);
</script>
