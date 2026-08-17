<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-white flex items-center gap-3">
            <i class="bi bi-shield-lock text-primary" style="font-size: 1.3rem;" aria-hidden="true"></i>
            {{ __('Atualizar Senha') }}
        </h2>

        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            <i class="bi bi-info-circle text-primary me-1" aria-hidden="true"></i>
            {{ __('Mantenha sua conta segura usando uma senha longa e aleatória.') }}
        </p>
    </header>

    {{-- ============================================
         FORMULÁRIO DE ATUALIZAÇÃO DE SENHA
         Psicologia: Segurança e Confiança
         ============================================ --}}
    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6" id="update-password-form">
        @csrf
        @method('put')

        {{-- Campo: Senha Atual - Psicologia: Verificação --}}
        <div class="form-group">
            <x-input-label for="update_password_current_password" :value="__('Senha Atual')" class="text-gray-700 dark:text-gray-300 font-medium" />
            <div class="relative mt-1">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="bi bi-lock text-gray-400 dark:text-gray-500" aria-hidden="true"></i>
                </div>
                <x-text-input 
                    id="update_password_current_password" 
                    name="current_password" 
                    type="password" 
                    class="block w-full pl-10 pr-12 py-3 rounded-xl border-2 border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-all duration-300" 
                    autocomplete="current-password"
                    placeholder="{{ __('Digite sua senha atual') }}"
                />
                <button 
                    type="button" 
                    class="absolute inset-y-0 right-0 pr-3 flex items-center toggle-password"
                    data-target="update_password_current_password"
                    aria-label="{{ __('Mostrar/Ocultar senha') }}"
                >
                    <i class="bi bi-eye text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 transition-colors" aria-hidden="true"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        {{-- Campo: Nova Senha - Psicologia: Força e Segurança --}}
        <div class="form-group">
            <x-input-label for="update_password_password" :value="__('Nova Senha')" class="text-gray-700 dark:text-gray-300 font-medium" />
            <div class="relative mt-1">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="bi bi-key text-gray-400 dark:text-gray-500" aria-hidden="true"></i>
                </div>
                <x-text-input 
                    id="update_password_password" 
                    name="password" 
                    type="password" 
                    class="block w-full pl-10 pr-12 py-3 rounded-xl border-2 border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-all duration-300" 
                    autocomplete="new-password"
                    placeholder="{{ __('Digite sua nova senha') }}"
                />
                <button 
                    type="button" 
                    class="absolute inset-y-0 right-0 pr-3 flex items-center toggle-password"
                    data-target="update_password_password"
                    aria-label="{{ __('Mostrar/Ocultar senha') }}"
                >
                    <i class="bi bi-eye text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 transition-colors" aria-hidden="true"></i>
                </button>
            </div>
            
            {{-- Medidor de Força da Senha - Psicologia: Progresso --}}
            <div class="mt-3 space-y-2" id="password-strength">
                <div class="flex items-center gap-3">
                    <div class="flex-1 h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                        <div class="h-full transition-all duration-500 ease-out rounded-full" style="width: 0%;" id="strength-bar"></div>
                    </div>
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400 min-w-[80px] text-right" id="strength-text">{{ __('Força:') }} {{ __('Fraca') }}</span>
                </div>
                <div class="flex gap-1 text-xs text-gray-500 dark:text-gray-400" id="strength-requirements">
                    <span class="requirement" data-requirement="length">
                        <i class="bi bi-circle" aria-hidden="true"></i> {{ __('Mínimo 8 caracteres') }}
                    </span>
                    <span class="requirement" data-requirement="uppercase">
                        <i class="bi bi-circle" aria-hidden="true"></i> {{ __('Letra maiúscula') }}
                    </span>
                    <span class="requirement" data-requirement="number">
                        <i class="bi bi-circle" aria-hidden="true"></i> {{ __('Número') }}
                    </span>
                    <span class="requirement" data-requirement="special">
                        <i class="bi bi-circle" aria-hidden="true"></i> {{ __('Caractere especial') }}
                    </span>
                </div>
            </div>
            
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        {{-- Campo: Confirmar Senha - Psicologia: Verificação --}}
        <div class="form-group">
            <x-input-label for="update_password_password_confirmation" :value="__('Confirmar Senha')" class="text-gray-700 dark:text-gray-300 font-medium" />
            <div class="relative mt-1">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="bi bi-check-circle text-gray-400 dark:text-gray-500" aria-hidden="true"></i>
                </div>
                <x-text-input 
                    id="update_password_password_confirmation" 
                    name="password_confirmation" 
                    type="password" 
                    class="block w-full pl-10 pr-12 py-3 rounded-xl border-2 border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-all duration-300" 
                    autocomplete="new-password"
                    placeholder="{{ __('Confirme sua nova senha') }}"
                />
                <button 
                    type="button" 
                    class="absolute inset-y-0 right-0 pr-3 flex items-center toggle-password"
                    data-target="update_password_password_confirmation"
                    aria-label="{{ __('Mostrar/Ocultar senha') }}"
                >
                    <i class="bi bi-eye text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 transition-colors" aria-hidden="true"></i>
                </button>
            </div>
            <div id="password-match-feedback" class="mt-2 text-sm hidden"></div>
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        {{-- Botões de Ação - Psicologia: Confiança e Progresso --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 pt-2">
            <button 
                type="submit" 
                class="btn-primary-gradient inline-flex items-center justify-center px-8 py-3 rounded-xl font-semibold text-white transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-[1.02] active:scale-[0.98] w-full sm:w-auto"
                id="submit-password-btn"
            >
                <i class="bi bi-check-circle me-2" aria-hidden="true"></i>
                {{ __('Atualizar Senha') }}
            </button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 3000)"
                    class="text-sm text-success-600 dark:text-success-400 flex items-center gap-2"
                >
                    <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
                    {{ __('Senha atualizada com sucesso!') }}
                </p>
            @endif
        </div>
    </form>
</section>

{{-- ============================================
         ESTILOS ADICIONAIS
         ============================================ --}}
<style>
    /* ============================================
       PSICOLOGIA DAS CORES APLICADA
       ============================================
       
       🔵 AZUL (#3b82f6, #2563eb, #1d4ed8)
       → Confiança, Segurança, Estabilidade
       → Usado em: Ícones, Foco, Botão principal
       
       🟢 VERDE (#22c55e, #16a34a)
       → Sucesso, Confirmação, Segurança
       → Usado em: Força da senha, Feedback positivo
       
       🟡 AMARELO (#f59e0b, #d97706)
       → Atenção, Cuidado
       → Usado em: Força média da senha
       
       🔴 VERMELHO (#ef4444, #dc2626)
       → Alerta, Ação Crítica
       → Usado em: Força fraca da senha, Erros
    */

    /* Botão primário com gradiente */
    .btn-primary-gradient {
        position: relative;
        overflow: hidden;
        background: linear-gradient(135deg, #2563eb 0%, #3b82f6 50%, #60a5fa 100%);
        background-size: 200% 200%;
        animation: gradient-shift 4s ease-in-out infinite;
    }

    .btn-primary-gradient::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.6s ease;
    }

    .btn-primary-gradient:hover::before {
        left: 100%;
    }

    @keyframes gradient-shift {
        0%, 100% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
    }

    /* Inputs com efeito de foco melhorado */
    .form-group input:focus {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15) !important;
    }

    .form-group input:focus + .toggle-password i {
        color: #3b82f6 !important;
    }

    /* Toggle de senha */
    .toggle-password {
        cursor: pointer;
        background: transparent;
        border: none;
        padding: 0 12px;
    }

    .toggle-password:hover i {
        color: #2563eb !important;
    }

    /* Medidor de força da senha */
    #strength-bar {
        transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    }

    #strength-bar.weak {
        background: linear-gradient(90deg, #ef4444, #dc2626);
    }

    #strength-bar.medium {
        background: linear-gradient(90deg, #f59e0b, #d97706);
    }

    #strength-bar.strong {
        background: linear-gradient(90deg, #22c55e, #16a34a);
    }

    #strength-bar.very-strong {
        background: linear-gradient(90deg, #8b5cf6, #7c3aed);
    }

    /* Requisitos da senha */
    .requirement {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 2px 8px;
        border-radius: 12px;
        background: #f3f4f6;
        color: #6b7280;
        font-size: 0.7rem;
        transition: all 0.3s ease;
        cursor: default;
    }

    .dark .requirement {
        background: #374151;
        color: #9ca3af;
    }

    .requirement.met {
        background: #dcfce7;
        color: #16a34a;
    }

    .dark .requirement.met {
        background: #052e16;
        color: #4ade80;
    }

    .requirement i {
        font-size: 0.6rem;
        transition: all 0.3s ease;
    }

    .requirement.met i {
        color: #22c55e;
    }

    /* Feedback de confirmação de senha */
    #password-match-feedback.match {
        display: flex !important;
        align-items: center;
        gap: 6px;
        color: #16a34a;
    }

    #password-match-feedback.no-match {
        display: flex !important;
        align-items: center;
        gap: 6px;
        color: #dc2626;
    }

    #password-match-feedback i {
        font-size: 1rem;
    }

    /* Responsividade */
    @media (max-width: 640px) {
        .btn-primary-gradient {
            width: 100%;
            justify-content: center;
        }

        .form-group input {
            font-size: 16px !important; /* Previne zoom em iOS */
        }

        #strength-requirements {
            flex-wrap: wrap;
            gap: 4px;
        }

        .requirement {
            font-size: 0.65rem;
            padding: 2px 6px;
        }

        .flex-col.sm\:flex-row {
            flex-direction: column;
        }

        .flex-col.sm\:flex-row .gap-4 {
            gap: 12px;
        }
    }

    /* Dark mode improvements */
    .dark .form-group input {
        background-color: #1f2937;
        border-color: #4b5563;
        color: #f3f4f6;
    }

    .dark .form-group input::placeholder {
        color: #6b7280;
    }

    /* Acessibilidade */
    button:focus-visible,
    input:focus-visible {
        outline: 3px solid #3b82f6;
        outline-offset: 2px;
    }

    /* Suporte a preferência de redução de movimento */
    @media (prefers-reduced-motion: reduce) {
        .btn-primary-gradient,
        .btn-primary-gradient::before {
            animation: none !important;
            transition: none !important;
        }

        #strength-bar {
            transition: none !important;
        }
    }

    /* Tooltip de ajuda para senha */
    .password-hint {
        position: relative;
        cursor: help;
    }

    .password-hint:hover::after {
        content: 'Use uma senha forte com letras, números e símbolos';
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
         SCRIPT PARA VALIDAÇÃO E INTERAÇÃO
         ============================================ --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ============================================
    // 1. TOGGLE DE VISUALIZAÇÃO DE SENHA
    // ============================================
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.dataset.target;
            const input = document.getElementById(targetId);
            const icon = this.querySelector('i');
            
            if (input) {
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.className = 'bi bi-eye-slash text-gray-400 dark:text-gray-500';
                } else {
                    input.type = 'password';
                    icon.className = 'bi bi-eye text-gray-400 dark:text-gray-500';
                }
            }
        });
    });

    // ============================================
    // 2. MEDIDOR DE FORÇA DA SENHA
    // ============================================
    const passwordInput = document.getElementById('update_password_password');
    const strengthBar = document.getElementById('strength-bar');
    const strengthText = document.getElementById('strength-text');
    const requirements = document.querySelectorAll('.requirement');

    function checkPasswordStrength(password) {
        let score = 0;
        const checks = {
            length: password.length >= 8,
            uppercase: /[A-Z]/.test(password),
            number: /\d/.test(password),
            special: /[!@#$%^&*(),.?":{}|<>]/.test(password)
        };

        // Atualizar requisitos
        requirements.forEach(req => {
            const key = req.dataset.requirement;
            const icon = req.querySelector('i');
            if (checks[key]) {
                req.classList.add('met');
                icon.className = 'bi bi-check-circle-fill';
            } else {
                req.classList.remove('met');
                icon.className = 'bi bi-circle';
            }
        });

        // Calcular força
        if (password.length === 0) return { score: 0, label: 'Fraca', class: 'weak' };
        
        if (checks.length) score++;
        if (checks.uppercase) score++;
        if (checks.number) score++;
        if (checks.special) score++;

        const levels = [
            { min: 0, label: 'Fraca', class: 'weak' },
            { min: 1, label: 'Fraca', class: 'weak' },
            { min: 2, label: 'Média', class: 'medium' },
            { min: 3, label: 'Forte', class: 'strong' },
            { min: 4, label: 'Muito Forte', class: 'very-strong' }
        ];

        const result = levels.find(l => score <= l.min + 1 && score > 0) || levels[levels.length - 1];
        return { score, ...result };
    }

    passwordInput.addEventListener('input', function() {
        const password = this.value;
        const result = checkPasswordStrength(password);
        
        // Atualizar barra
        const percentage = password.length > 0 ? (result.score / 4) * 100 : 0;
        strengthBar.style.width = percentage + '%';
        strengthBar.className = 'h-full transition-all duration-500 ease-out rounded-full ' + (password.length > 0 ? result.class : '');
        
        // Atualizar texto
        strengthText.textContent = password.length > 0 ? `Força: ${result.label}` : 'Força: Fraca';
        strengthText.className = 'text-xs font-medium min-w-[80px] text-right ' + 
            (password.length > 0 ? 
                (result.class === 'weak' ? 'text-danger-500 dark:text-danger-400' :
                result.class === 'medium' ? 'text-amber-500 dark:text-amber-400' :
                result.class === 'strong' ? 'text-success-500 dark:text-success-400' :
                'text-purple-500 dark:text-purple-400') : 
                'text-gray-500 dark:text-gray-400'
            );
    });

    // ============================================
    // 3. CONFIRMAÇÃO DE SENHA EM TEMPO REAL
    // ============================================
    const confirmPasswordInput = document.getElementById('update_password_password_confirmation');
    const matchFeedback = document.getElementById('password-match-feedback');

    function checkPasswordMatch() {
        const password = passwordInput.value;
        const confirm = confirmPasswordInput.value;
        
        if (confirm.length === 0) {
            matchFeedback.className = 'mt-2 text-sm hidden';
            matchFeedback.innerHTML = '';
            return;
        }

        if (password === confirm) {
            matchFeedback.className = 'mt-2 text-sm match';
            matchFeedback.innerHTML = '<i class="bi bi-check-circle-fill" aria-hidden="true"></i> As senhas coincidem';
            matchFeedback.style.color = '#16a34a';
        } else {
            matchFeedback.className = 'mt-2 text-sm no-match';
            matchFeedback.innerHTML = '<i class="bi bi-exclamation-circle-fill" aria-hidden="true"></i> As senhas não coincidem';
            matchFeedback.style.color = '#dc2626';
        }
    }

    confirmPasswordInput.addEventListener('input', checkPasswordMatch);
    passwordInput.addEventListener('input', function() {
        if (confirmPasswordInput.value.length > 0) {
            checkPasswordMatch();
        }
    });

    // ============================================
    // 4. VALIDAÇÃO DO FORMULÁRIO
    // ============================================
    const form = document.getElementById('update-password-form');
    const submitBtn = document.getElementById('submit-password-btn');

    form.addEventListener('submit', function(e) {
        const currentPassword = document.getElementById('update_password_current_password').value;
        const newPassword = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;

        // Validar senha atual
        if (!currentPassword) {
            e.preventDefault();
            const error = document.querySelector('[name="current_password"] + .text-danger-600');
            if (!error) {
                const div = document.createElement('div');
                div.className = 'mt-2 text-sm text-danger-600 dark:text-danger-400';
                div.innerHTML = '<i class="bi bi-exclamation-circle" aria-hidden="true"></i> Digite sua senha atual';
                document.getElementById('update_password_current_password').parentElement.parentElement.appendChild(div);
            }
            return;
        }

        // Validar nova senha
        if (newPassword.length < 8) {
            e.preventDefault();
            const error = document.querySelector('[name="password"] + .text-danger-600');
            if (!error) {
                const div = document.createElement('div');
                div.className = 'mt-2 text-sm text-danger-600 dark:text-danger-400';
                div.innerHTML = '<i class="bi bi-exclamation-circle" aria-hidden="true"></i> A senha deve ter no mínimo 8 caracteres';
                document.getElementById('update_password_password').parentElement.parentElement.appendChild(div);
            }
            return;
        }

        // Validar confirmação
        if (newPassword !== confirmPassword) {
            e.preventDefault();
            const error = document.querySelector('[name="password_confirmation"] + .text-danger-600');
            if (!error) {
                const div = document.createElement('div');
                div.className = 'mt-2 text-sm text-danger-600 dark:text-danger-400';
                div.innerHTML = '<i class="bi bi-exclamation-circle" aria-hidden="true"></i> As senhas não coincidem';
                document.getElementById('update_password_password_confirmation').parentElement.parentElement.appendChild(div);
            }
            return;
        }

        // Remover mensagens de erro antigas
        document.querySelectorAll('.text-danger-600').forEach(el => {
            if (el.closest('.form-group')) {
                el.remove();
            }
        });
    });

    // ============================================
    // 5. LIMPAR ERROS AO DIGITAR
    // ============================================
    document.querySelectorAll('.form-group input').forEach(input => {
        input.addEventListener('input', function() {
            const parent = this.parentElement.parentElement;
            const error = parent.querySelector('.text-danger-600');
            if (error) {
                error.style.transition = 'opacity 0.3s ease';
                error.style.opacity = '0';
                setTimeout(() => error.remove(), 300);
            }
        });
    });

    // ============================================
    // 6. ANIMAÇÃO DE RIPPLE NO BOTÃO
    // ============================================
    submitBtn.addEventListener('click', function(e) {
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

    // ============================================
    // 7. ENTER KEY PARA SUBMETER FORMULÁRIO
    // ============================================
    confirmPasswordInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            submitBtn.click();
        }
    });
});

// Adicionar estilo para animação de ripple
const rippleStyle = document.createElement('style');
rippleStyle.textContent = `
    @keyframes ripple-effect {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }
`;
document.head.appendChild(rippleStyle);
</script>
