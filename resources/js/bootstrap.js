import axios from 'axios';

// 🔒 CONFIGURAÇÃO GLOBAL DO CSRF
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Obter token do meta tag
const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
}

// 🔒 INTERCEPTADOR PARA TRATAR ERRO 419
window.axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response && error.response.status === 419) {
            console.warn('⚠️ Token CSRF expirado. Recarregando...');
            window.location.reload();
        }
        return Promise.reject(error);
    }
);