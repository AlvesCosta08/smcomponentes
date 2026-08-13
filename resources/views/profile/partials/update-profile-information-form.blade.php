<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-white flex items-center gap-3">
            <i class="bi bi-person-circle text-primary" style="font-size: 1.3rem;" aria-hidden="true"></i>
            {{ __('Informações do Perfil') }}
        </h2>

        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            <i class="bi bi-info-circle text-primary me-1" aria-hidden="true"></i>
            {{ __('Atualize as informações do seu perfil e endereço de e-mail.') }}
        </p>
    </header>

    {{-- ============================================
         FORMULÁRIO DE VERIFICAÇÃO DE E-MAIL
         ============================================ --}}
    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    {{-- ============================================
         FORMULÁRIO DE ATUALIZAÇÃO DE PERFIL
         Psicologia: Confiança e Personalização
         ============================================ --}}
    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6" id="profile-form">
        @csrf
        @method('patch')

        {{-- Campo: Nome - Psicologia: Identidade --}}
        <div class="form-group">
            <x-input-label for="name" :value="__('Nome')" class="text-gray-700 dark:text-gray-300 font-medium" />
            <div class="relative mt-1">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="bi bi-person text-gray-400 dark:text-gray-500" aria-hidden="true"></i>
                </div>
                <x-text-input 
                    id="name" 
                    name="name" 
                    type="text" 
                    class="block w-full pl-10 pr-4 py-3 rounded-xl border-2 border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-all duration-300" 
                    :value="old('name', $user->name)" 
                    required 
                    autofocus 
                    autocomplete="name"
                    placeholder="{{ __('Digite seu nome completo') }}"
                />
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        {{-- Campo: E-mail - Psicologia: Verificação --}}
        <div class="form-group">
            <x-input-label for="email" :value="__('E-mail')" class="text-gray-700 dark:text-gray-300 font-medium" />
            <div class="relative mt-1">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="bi bi-envelope text-gray-400 dark:text-gray-500" aria-hidden="true"></i>
                </div>
                <x-text-input 
                    id="email" 
                    name="email" 
                    type="email" 
                    class="block w-full pl-10 pr-4 py-3 rounded-xl border-2 border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-all duration-300" 
                    :value="old('email', $user->email)" 
                    required 
                    autocomplete="username"
                    placeholder="{{ __('Digite seu e-mail') }}"
                />
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            {{-- Verificação de e-mail - Psicologia: Confiabilidade --}}
            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-4 p-4 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/50">
                    <div class="flex items-start gap-3">
                        <i class="bi bi-shield-exclamation text-amber-600 dark:text-amber-400 mt-0.5" style="font-size: 1.2rem;" aria-hidden="true"></i>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-amber-800 dark:text-amber-300">
                                {{ __('Seu endereço de e-mail não está verificado.') }}
                            </p>
                            <button 
                                form="send-verification" 
                                class="mt-2 text-sm text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 font-medium transition-colors inline-flex items-center gap-2 hover:underline"
                            >
                                <i class="bi bi-arrow-repeat" aria-hidden="true"></i>
                                {{ __('Clique aqui para reenviar o e-mail de verificação.') }}
                            </button>
                        </div>
                    </div>

                    @if (session('status') === 'verification-link-sent')
                        <div class="mt-3 flex items-center gap-2 text-sm text-success-600 dark:text-success-400">
                            <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
                            {{ __('Um novo link de verificação foi enviado para seu e-mail.') }}
                        </div>
                    @endif
                </div>
            @endif
        </div>

        {{-- Avatar/Imagem de Perfil - Psicologia: Personalização --}}
        <div class="form-group">
            <x-input-label :value="__('Foto de Perfil')" class="text-gray-700 dark:text-gray-300 font-medium" />
            <div class="mt-2 flex items-center gap-4">
                <div class="relative">
                    <div class="w-20 h-20 rounded-full bg-gradient-primary flex items-center justify-center text-white text-3xl font-semibold shadow-lg ring-4 ring-primary-100 dark:ring-primary-900/30">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <button 
                        type="button" 
                        class="absolute -bottom-1 -right-1 w-8 h-8 rounded-full bg-primary-500 hover:bg-primary-600 text-white flex items-center justify-center shadow-lg transition-all duration-300 hover:scale-110"
                        title="{{ __('Alterar foto') }}"
                        onclick="document.getElementById('avatar-upload').click()"
                    >
                        <i class="bi bi-camera" style="font-size: 0.9rem;" aria-hidden="true"></i>
                    </button>
                    <input type="file" id="avatar-upload" accept="image/*" class="hidden" onchange="handleAvatarUpload(this)">
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        <i class="bi bi-info-circle text-primary me-1" aria-hidden="true"></i>
                        {{ __('Clique no ícone da câmera para alterar sua foto') }}
                    </p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                        {{ __('Formatos: JPG, PNG, GIF. Máx: 2MB') }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Botões de Ação - Psicologia: Confiança e Progresso --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 pt-2">
            <button 
                type="submit" 
                class="btn-primary-gradient inline-flex items-center justify-center px-8 py-3 rounded-xl font-semibold text-white transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-[1.02] active:scale-[0.98] w-full sm:w-auto"
                id="submit-profile-btn"
            >
                <i class="bi bi-check-circle me-2" aria-hidden="true"></i>
                {{ __('Salvar Alterações') }}
            </button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 3000)"
                    class="text-sm text-success-600 dark:text-success-400 flex items-center gap-2"
                >
                    <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
                    {{ __('Perfil atualizado com sucesso!') }}
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
       → Confiança, Segurança, Profissionalismo
       → Usado em: Ícones, Foco, Botão principal
       
       🟢 VERDE (#22c55e, #16a34a)
       → Sucesso, Confirmação
       → Usado em: Feedback positivo, Verificação
       
       🟡 AMARELO (#f59e0b, #d97706)
       → Atenção, Cuidado
       → Usado em: Alertas de verificação
       
       🟣 ROXO (#8b5cf6, #7c3aed)
       → Criatividade, Inovação
       → Usado em: Gradientes, Elementos de destaque
    */

    /* Avatar com gradiente */
    .bg-gradient-primary {
        background: linear-gradient(135deg, #2563eb 0%, #7c3aed 50%, #8b5cf6 100%);
    }

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

    /* Animação do avatar */
    .bg-gradient-primary {
        animation: avatar-pulse 3s ease-in-out infinite;
    }

    @keyframes avatar-pulse {
        0%, 100% { box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2); }
        50% { box-shadow: 0 0 0 8px rgba(59, 130, 246, 0.1); }
    }

    /* Botão de upload de avatar */
    button[onclick*="avatar-upload"] {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    button[onclick*="avatar-upload"]:hover {
        transform: scale(1.15) rotate(-10deg);
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

        .flex-col.sm\:flex-row {
            flex-direction: column;
        }

        .flex-col.sm\:flex-row .gap-4 {
            gap: 12px;
        }

        .w-20.h-20 {
            width: 64px;
            height: 64px;
        }

        .w-20.h-20 .text-3xl {
            font-size: 1.5rem;
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

    .dark .bg-gradient-primary {
        background: linear-gradient(135deg, #1d4ed8 0%, #7c3aed 50%, #6d28d9 100%);
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
        .btn-primary-gradient::before,
        .bg-gradient-primary {
            animation: none !important;
            transition: none !important;
        }

        button[onclick*="avatar-upload"]:hover {
            transform: none !important;
        }
    }

    /* Preview do avatar carregado */
    .avatar-preview {
        object-fit: cover;
        border-radius: 50%;
        width: 100%;
        height: 100%;
        border: 3px solid #ffffff;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    /* Tooltip de ajuda */
    .help-tip {
        position: relative;
        cursor: help;
    }

    .help-tip:hover::after {
        content: 'Use um e-mail válido para receber notificações';
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
         SCRIPT PARA FUNCIONALIDADES ADICIONAIS
         ============================================ --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ============================================
    // 1. UPLOAD DE AVATAR COM PREVIEW
    // ============================================
    window.handleAvatarUpload = function(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            
            // Validar tamanho (2MB)
            if (file.size > 2 * 1024 * 1024) {
                alert('A imagem deve ter no máximo 2MB.');
                input.value = '';
                return;
            }

            // Validar tipo
            const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!validTypes.includes(file.type)) {
                alert('Formato de imagem inválido. Use JPG, PNG, GIF ou WEBP.');
                input.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                const avatarContainer = input.closest('.relative').querySelector('.w-20.h-20');
                if (avatarContainer) {
                    // Criar ou atualizar preview
                    let preview = avatarContainer.querySelector('img');
                    if (!preview) {
                        preview = document.createElement('img');
                        preview.className = 'avatar-preview';
                        avatarContainer.innerHTML = '';
                        avatarContainer.appendChild(preview);
                    }
                    preview.src = e.target.result;
                    
                    // Adicionar efeito de fade in
                    preview.style.animation = 'fadeIn 0.5s ease';
                    setTimeout(() => preview.style.animation = '', 500);
                }
            };
            reader.readAsDataURL(file);
        }
    };

    // Adicionar estilo para fade in
    const fadeStyle = document.createElement('style');
    fadeStyle.textContent = `
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.8); }
            to { opacity: 1; transform: scale(1); }
        }
    `;
    document.head.appendChild(fadeStyle);

    // ============================================
    // 2. VALIDAÇÃO DE E-MAIL EM TEMPO REAL
    // ============================================
    const emailInput = document.getElementById('email');
    
    if (emailInput) {
        emailInput.addEventListener('input', function() {
            const email = this.value;
            const isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
            
            // Remover feedback anterior
            const existingFeedback = this.parentElement.parentElement.querySelector('.email-feedback');
            if (existingFeedback) {
                existingFeedback.remove();
            }

            if (email.length > 0 && !isValid) {
                const feedback = document.createElement('div');
                feedback.className = 'mt-2 text-sm text-danger-600 dark:text-danger-400 flex items-center gap-2 email-feedback';
                feedback.innerHTML = `
                    <i class="bi bi-exclamation-circle" aria-hidden="true"></i>
                    <span>{{ __('Digite um e-mail válido.') }}</span>
                `;
                this.parentElement.parentElement.appendChild(feedback);
                
                // Adicionar borda vermelha
                this.style.borderColor = '#dc2626';
            } else if (email.length > 0 && isValid) {
                const feedback = document.createElement('div');
                feedback.className = 'mt-2 text-sm text-success-600 dark:text-success-400 flex items-center gap-2 email-feedback';
                feedback.innerHTML = `
                    <i class="bi bi-check-circle" aria-hidden="true"></i>
                    <span>{{ __('E-mail válido.') }}</span>
                `;
                this.parentElement.parentElement.appendChild(feedback);
                
                // Remover borda vermelha
                this.style.borderColor = '';
            } else {
                this.style.borderColor = '';
            }
        });
    }

    // ============================================
    // 3. ANIMAÇÃO DE RIPPLE NO BOTÃO
    // ============================================
    const submitBtn = document.getElementById('submit-profile-btn');
    
    if (submitBtn) {
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
    }

    // ============================================
    // 4. VALIDAÇÃO DO FORMULÁRIO
    // ============================================
    const form = document.getElementById('profile-form');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            let hasError = false;

            // Validar nome
            if (name.length < 2) {
                e.preventDefault();
                hasError = true;
                const nameInput = document.getElementById('name');
                const parent = nameInput.parentElement.parentElement;
                
                // Remover erro anterior
                const oldError = parent.querySelector('.text-danger-600');
                if (oldError) oldError.remove();
                
                const error = document.createElement('div');
                error.className = 'mt-2 text-sm text-danger-600 dark:text-danger-400 flex items-center gap-2';
                error.innerHTML = `
                    <i class="bi bi-exclamation-circle" aria-hidden="true"></i>
                    <span>{{ __('O nome deve ter pelo menos 2 caracteres.') }}</span>
                `;
                parent.appendChild(error);
                nameInput.focus();
            }

            // Validar e-mail
            if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                e.preventDefault();
                hasError = true;
                const emailInput = document.getElementById('email');
                const parent = emailInput.parentElement.parentElement;
                
                // Remover erro anterior
                const oldError = parent.querySelector('.text-danger-600');
                if (oldError) oldError.remove();
                
                const error = document.createElement('div');
                error.className = 'mt-2 text-sm text-danger-600 dark:text-danger-400 flex items-center gap-2';
                error.innerHTML = `
                    <i class="bi bi-exclamation-circle" aria-hidden="true"></i>
                    <span>{{ __('Digite um e-mail válido.') }}</span>
                `;
                parent.appendChild(error);
                emailInput.focus();
            }
        });
    }

    // ============================================
    // 5. LIMPAR ERROS AO DIGITAR
    // ============================================
    document.querySelectorAll('.form-group input').forEach(input => {
        input.addEventListener('input', function() {
            const parent = this.parentElement.parentElement;
            const errors = parent.querySelectorAll('.text-danger-600');
            errors.forEach(error => {
                if (!error.closest('.email-feedback')) {
                    error.style.transition = 'opacity 0.3s ease';
                    error.style.opacity = '0';
                    setTimeout(() => error.remove(), 300);
                }
            });
            
            // Remover feedback de e-mail se o campo estiver vazio
            if (this.id === 'email' && this.value.length === 0) {
                const feedback = parent.querySelector('.email-feedback');
                if (feedback) feedback.remove();
                this.style.borderColor = '';
            }
        });
    });

    // ============================================
    // 6. ENTER KEY PARA SUBMETER FORMULÁRIO
    // ============================================
    document.querySelectorAll('#email, #name').forEach(input => {
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const form = this.closest('form');
                if (form) {
                    const submitBtn = form.querySelector('button[type="submit"]');
                    if (submitBtn) submitBtn.click();
                }
            }
        });
    });

    // ============================================
    // 7. DRAG & DROP PARA AVATAR
    // ============================================
    const avatarContainer = document.querySelector('.relative .w-20.h-20');
    if (avatarContainer) {
        avatarContainer.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.style.boxShadow = '0 0 0 4px #3b82f6, 0 0 20px rgba(59, 130, 246, 0.3)';
        });

        avatarContainer.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.style.boxShadow = '';
        });

        avatarContainer.addEventListener('drop', function(e) {
            e.preventDefault();
            this.style.boxShadow = '';
            
            const files = e.dataTransfer.files;
            if (files && files[0]) {
                const input = document.getElementById('avatar-upload');
                if (input) {
                    input.files = files;
                    window.handleAvatarUpload(input);
                }
            }
        });
    }
});
</script>
