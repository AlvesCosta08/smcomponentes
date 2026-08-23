// ============================================
// BOOTSTRAP
// ============================================
import './bootstrap';

// Importar CSS do Bootstrap
import 'bootstrap/dist/css/bootstrap.min.css';

// Importar Bootstrap Icons
import 'bootstrap-icons/font/bootstrap-icons.css';

// Importar Bootstrap JS (já inclui Popper)
import 'bootstrap/dist/js/bootstrap.bundle.min.js';

// ============================================
// ALPINE.JS
// ============================================
import Alpine from 'alpinejs';
window.Alpine = Alpine;

// Iniciar Alpine
Alpine.start();

// ============================================
// CONFIGURAÇÕES GLOBAIS
// ============================================
import axios from 'axios';
window.axios = axios;

// 🔒 CONFIGURAÇÃO DO CSRF PARA AXIOS
// Obter token do meta tag
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

// Configurar headers padrão do Axios
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Adicionar CSRF token para todas as requisições
if (csrfToken) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
    console.log('✅ CSRF Token configurado no Axios');
} else {
    console.warn('⚠️ CSRF token não encontrado no meta tag');
}

// 🔒 INTERCEPTADOR PARA TRATAR ERRO 419 (CSRF Token Expired)
window.axios.interceptors.response.use(
    response => response,
    error => {
        // Se o erro for 419 (CSRF token mismatch)
        if (error.response && error.response.status === 419) {
            console.warn('⚠️ Token CSRF expirado. Recarregando página...');
            
            // Mostrar mensagem amigável
            const toast = document.createElement('div');
            toast.className = 'alert alert-warning alert-dismissible fade show position-fixed';
            toast.style.cssText = `
                top: 20px;
                left: 50%;
                transform: translateX(-50%);
                z-index: 99999;
                max-width: 500px;
                box-shadow: 0 10px 40px rgba(0,0,0,0.2);
                border-radius: 12px;
                text-align: center;
            `;
            toast.innerHTML = `
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Sessão expirada!</strong> Recarregando a página para renovar sua segurança...
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(toast);
            
            // Recarregar a página após 2 segundos
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        }
        return Promise.reject(error);
    }
);

// ============================================
// 🔒 CONFIGURAÇÃO DO CSRF PARA FETCH API
// ============================================
// Salvar token em variável global para uso em fetch
window.csrfToken = csrfToken;

// Função helper para fazer requisições com CSRF
window.fetchWithCsrf = function(url, options = {}) {
    const defaultOptions = {
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken || '',
            'Accept': 'application/json'
        }
    };
    
    const mergedOptions = {
        ...defaultOptions,
        ...options,
        headers: {
            ...defaultOptions.headers,
            ...(options.headers || {})
        }
    };
    
    return fetch(url, mergedOptions);
};

// Interceptar fetch global para adicionar CSRF automaticamente
const originalFetch = window.fetch;
window.fetch = function(url, options = {}) {
    // Se for POST, PUT, DELETE, PATCH, adicionar CSRF
    const method = (options.method || 'GET').toUpperCase();
    if (['POST', 'PUT', 'DELETE', 'PATCH'].includes(method)) {
        if (!options.headers) options.headers = {};
        if (!options.headers['X-CSRF-TOKEN']) {
            options.headers['X-CSRF-TOKEN'] = csrfToken || '';
        }
        if (!options.headers['Content-Type']) {
            options.headers['Content-Type'] = 'application/json';
        }
        if (!options.headers['Accept']) {
            options.headers['Accept'] = 'application/json';
        }
    }
    return originalFetch(url, options);
};

// ============================================
// SEUS SCRIPTS PERSONALIZADOS
// ============================================

// Função para atualizar o contador do carrinho
function atualizarCarrinho() {
    const badge = document.getElementById('carrinho-count');
    if (!badge) return;

    fetch('/carrinho/count', {
        headers: {
            'X-CSRF-TOKEN': csrfToken || ''
        }
    })
    .then(response => {
        if (!response.ok) throw new Error('Erro na resposta');
        return response.json();
    })
    .then(data => {
        badge.textContent = data.count || 0;
        badge.style.display = data.count > 0 ? 'inline-block' : 'none';
    })
    .catch(error => console.debug('ℹ️ Carrinho vazio ou indisponível:', error.message));
}

document.addEventListener('DOMContentLoaded', function() {
    // 🔒 GARANTIR CSRF EM TODOS OS FORMS
    function ensureCsrfInForms() {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (!token) {
            console.warn('⚠️ CSRF token não encontrado');
            return;
        }

        document.querySelectorAll('form:not([data-csrf-added])').forEach(function(form) {
            const method = form.method?.toUpperCase() || 'GET';
            if (['POST', 'PUT', 'DELETE', 'PATCH'].includes(method)) {
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

    // 🔒 GARANTIR CSRF EM LINKS COM MÉTODO DELETE
    function ensureCsrfInLinks() {
        document.querySelectorAll('a[data-method="DELETE"]').forEach(function(link) {
            if (!link.hasAttribute('data-csrf-added')) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = this.href;
                    form.style.display = 'none';
                    
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = token;
                    form.appendChild(csrfInput);
                    
                    const methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = 'DELETE';
                    form.appendChild(methodInput);
                    
                    document.body.appendChild(form);
                    form.submit();
                });
                link.setAttribute('data-csrf-added', 'true');
            }
        });
    }

    // Inicializar CSRF
    ensureCsrfInForms();
    ensureCsrfInLinks();

    // Observer para novos formulários adicionados dinamicamente
    const observer = new MutationObserver(function() {
        ensureCsrfInForms();
        ensureCsrfInLinks();
    });
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });

    // Inicializar tooltips do Bootstrap
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Atualizar carrinho a cada 30 segundos
    atualizarCarrinho();
    setInterval(atualizarCarrinho, 30000);
});

// ============================================
// FUNÇÕES AUXILIARES GLOBAIS
// ============================================

// Função para mostrar toast de notificação
window.showToast = function(message, type = 'info', duration = 4000) {
    const colors = {
        success: { bg: '#dcfce7', border: '#22c55e', icon: 'bi-check-circle-fill' },
        error: { bg: '#fee2e2', border: '#ef4444', icon: 'bi-x-circle-fill' },
        warning: { bg: '#ffedd5', border: '#f97316', icon: 'bi-exclamation-triangle-fill' },
        info: { bg: '#dbeafe', border: '#3b82f6', icon: 'bi-info-circle-fill' }
    };

    const style = colors[type] || colors.info;
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    toast.style.cssText = `
        top: 80px;
        right: 20px;
        z-index: 99999;
        max-width: 400px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.15);
        border-radius: 12px;
        border-left: 5px solid ${style.border};
        animation: slideInRight 0.4s ease forwards;
    `;
    toast.innerHTML = `
        <i class="bi ${style.icon} me-2" style="color: ${style.border};"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(toast);

    setTimeout(() => {
        if (toast && toast.remove) {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.4s ease';
            setTimeout(() => toast.remove(), 400);
        }
    }, duration);
};

// Função para recarregar token CSRF
window.refreshCsrfToken = function() {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (token) {
        window.csrfToken = token;
        window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
        console.log('✅ CSRF Token atualizado:', token);
        return token;
    }
    console.warn('⚠️ Não foi possível atualizar o CSRF Token');
    return null;
};

// ============================================
// CSS PARA ANIMAÇÃO DO TOAST
// ============================================
const styleSheet = document.createElement('style');
styleSheet.textContent = `
    @keyframes slideInRight {
        from { opacity: 0; transform: translateX(100px); }
        to { opacity: 1; transform: translateX(0); }
    }
`;
document.head.appendChild(styleSheet);

// ============================================
// EXPORTAÇÕES
// ============================================
export default {
    Alpine,
    axios
};

console.log('✅ SM Componentes - Frontend carregado com CSRF ativo!');