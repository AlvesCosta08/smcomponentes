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
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// ============================================
// SEUS SCRIPTS PERSONALIZADOS
// ============================================
// Exemplo: Contador do carrinho
document.addEventListener('DOMContentLoaded', function() {
    // Atualizar contador do carrinho
    function atualizarCarrinho() {
        fetch('/carrinho/count')
            .then(response => response.json())
            .then(data => {
                const badge = document.getElementById('carrinho-count');
                if (badge) {
                    badge.textContent = data.count || 0;
                }
            })
            .catch(error => console.error('Erro ao carregar carrinho:', error));
    }

    // Inicializar tooltips do Bootstrap
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Atualizar carrinho a cada 30 segundos (opcional)
    atualizarCarrinho();
    setInterval(atualizarCarrinho, 30000);
});

// Exportar módulos se necessário
export default {
    Alpine,
    axios
};