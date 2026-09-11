{{-- resources/views/produtos/partials/autocomplete.blade.php --}}
@php
    $variant  = $variant ?? 'light'; // 'light' ou 'dark'
    $uniqueId = $id ?? 'default';
@endphp

<div class="autocomplete-wrapper autocomplete-{{ $variant }} position-relative"
     id="autocomplete-{{ $uniqueId }}">
    <form action="{{ route('produtos.buscar') }}" method="GET"
          class="position-relative autocomplete-form" autocomplete="off">
        <input type="text"
               name="q"
               id="autocomplete-input-{{ $uniqueId }}"
               class="form-control autocomplete-input {{ $inputClass ?? '' }}"
               placeholder="{{ $placeholder ?? 'Buscar por nome ou referência...' }}"
               value="{{ $termo ?? '' }}"
               autocomplete="off"
               data-autocomplete-url="{{ route('api.produtos.autocomplete') }}">
        <button type="submit" class="btn autocomplete-btn" aria-label="Buscar">
            <i class="bi bi-search"></i>
        </button>
    </form>

    <div class="autocomplete-dropdown" id="autocomplete-dropdown-{{ $uniqueId }}">
        <div class="autocomplete-loading d-none">
            <div class="spinner-border spinner-border-sm text-primary me-2"></div>
            Buscando...
        </div>
        <div class="autocomplete-empty d-none">
            <i class="bi bi-search me-2"></i> Nenhum produto encontrado
        </div>
        <div class="autocomplete-results"></div>
    </div>
</div>

@once
@push('styles')
<style>
    /* ============================================================
       BASE DO AUTOCOMPLETE
       ============================================================ */
    .autocomplete-wrapper {
        position: relative;
        width: 100%;
    }

    .autocomplete-form {
        position: relative;
        margin: 0;
    }

    .autocomplete-input {
        padding-right: 48px !important;
        height: 42px;
        border-radius: 50px;
        font-size: 0.9rem;
        transition: all 0.25s ease;
        width: 100%;
    }

    .autocomplete-btn {
        position: absolute;
        top: 50%;
        right: 4px;
        transform: translateY(-50%);
        border-radius: 50px;
        padding: 6px 14px;
        height: 34px;
        border: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        z-index: 2;
    }

    /* ============================================================
       VARIANTE "LIGHT" (padrão — páginas internas)
       ============================================================ */
    .autocomplete-light .autocomplete-input {
        background: #fff;
        color: #212529;
        border: 1px solid #dee2e6;
    }

    .autocomplete-light .autocomplete-input::placeholder {
        color: #6c757d;
    }

    .autocomplete-light .autocomplete-input:focus {
        background: #fff;
        color: #212529;
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
    }

    .autocomplete-light .autocomplete-btn {
        background: #0d6efd;
        color: #fff;
    }

    .autocomplete-light .autocomplete-btn:hover {
        background: #0b5ed7;
        color: #fff;
    }

    /* ============================================================
       VARIANTE "DARK" (navbar com fundo escuro/azul)
       ============================================================ */
    .autocomplete-dark .autocomplete-input {
        background: rgba(255, 255, 255, 0.12) !important;
        color: #fff !important;
        border: 2px solid rgba(255, 255, 255, 0.22) !important;
        backdrop-filter: blur(6px);
    }

    .autocomplete-dark .autocomplete-input::placeholder {
        color: rgba(255, 255, 255, 0.65) !important;
    }

    .autocomplete-dark .autocomplete-input:focus {
        background: rgba(255, 255, 255, 0.20) !important;
        color: #fff !important;
        border-color: #f97316 !important;
        box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.18) !important;
    }

    .autocomplete-dark .autocomplete-btn {
        background: #f97316;
        color: #fff;
    }

    .autocomplete-dark .autocomplete-btn:hover {
        background: #ea580c;
        color: #fff;
    }

    /* ============================================================
       DROPDOWN
       ============================================================ */
    .autocomplete-dropdown {
        position: absolute;
        top: calc(100% + 6px);
        left: 0;
        right: 0;
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 10px;
        box-shadow: 0 12px 35px rgba(0, 0, 0, 0.15);
        max-height: 420px;
        overflow-y: auto;
        z-index: 1060;
        display: none;
        min-width: 320px;
    }

    .autocomplete-dropdown.show {
        display: block;
    }

    .autocomplete-loading,
    .autocomplete-empty {
        padding: 16px;
        text-align: center;
        color: #6c757d;
        font-size: 0.9rem;
    }

    .autocomplete-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 15px;
        border-bottom: 1px solid #f1f3f5;
        cursor: pointer;
        transition: background 0.15s;
        text-decoration: none;
        color: inherit;
    }

    .autocomplete-item:last-child {
        border-bottom: none;
    }

    .autocomplete-item:hover,
    .autocomplete-item.active {
        background: #f8f9fa;
        text-decoration: none;
        color: inherit;
    }

    .autocomplete-item-image {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid #e9ecef;
        flex-shrink: 0;
        background: #f8f9fa;
    }

    .autocomplete-item-info {
        flex: 1;
        min-width: 0;
    }

    .autocomplete-item-title {
        font-size: 0.9rem;
        font-weight: 600;
        color: #212529;
        margin: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .autocomplete-item-meta {
        font-size: 0.75rem;
        color: #6c757d;
        margin: 2px 0 0 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .autocomplete-item-meta strong {
        color: #0d6efd;
    }

    .autocomplete-item-price {
        font-size: 0.95rem;
        font-weight: 700;
        color: #0d6efd;
        white-space: nowrap;
        flex-shrink: 0;
    }

    .autocomplete-item-disponivel {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        margin-left: 6px;
    }

    .autocomplete-item-disponivel.sim { background: #198754; }
    .autocomplete-item-disponivel.nao { background: #dc3545; }

    .autocomplete-highlight {
        background: #fff3cd;
        padding: 0 2px;
        border-radius: 2px;
    }
</style>
@endpush
@endonce

@push('scripts')
<script>
(function() {
    const wrapperId = '{{ $uniqueId }}';
    const input     = document.getElementById('autocomplete-input-' + wrapperId);
    const dropdown  = document.getElementById('autocomplete-dropdown-' + wrapperId);

    if (!input || !dropdown || input.dataset.autocompleteReady === '1') return;
    input.dataset.autocompleteReady = '1';

    const resultsContainer = dropdown.querySelector('.autocomplete-results');
    const loadingEl        = dropdown.querySelector('.autocomplete-loading');
    const emptyEl          = dropdown.querySelector('.autocomplete-empty');
    const autocompleteUrl  = input.dataset.autocompleteUrl;
    const placeholderImg   = '{{ asset('images/produto-placeholder.jpg') }}';

    let debounceTimer  = null;
    let currentRequest = null;
    let selectedIndex  = -1;
    let currentItems   = [];

    function debounce(cb, delay) {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(cb, delay);
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text ?? '';
        return div.innerHTML;
    }

    function highlightTerm(text, term) {
        if (!term || !text) return escapeHtml(text);
        const escaped  = escapeHtml(text);
        const safeTerm = term.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        const regex    = new RegExp(`(${safeTerm})`, 'gi');
        return escaped.replace(regex, '<span class="autocomplete-highlight">$1</span>');
    }

    function openDropdown()  { dropdown.classList.add('show'); }
    function closeDropdown() {
        dropdown.classList.remove('show');
        selectedIndex = -1;
    }

    function renderItems(items, term) {
        if (!items || items.length === 0) {
            loadingEl.classList.add('d-none');
            emptyEl.classList.remove('d-none');
            resultsContainer.innerHTML = '';
            currentItems = [];
            return;
        }

        loadingEl.classList.add('d-none');
        emptyEl.classList.add('d-none');
        currentItems = items;

        resultsContainer.innerHTML = items.map((item, index) => {
            const img = item.imagem
                ? `<img src="${escapeHtml(item.imagem)}" alt="${escapeHtml(item.descricao)}" class="autocomplete-item-image" onerror="this.onerror=null; this.src='${placeholderImg}'">`
                : `<div class="autocomplete-item-image d-flex align-items-center justify-content-center"><i class="bi bi-plug text-muted"></i></div>`;

            const disponivel = item.disponivel
                ? `<span class="autocomplete-item-disponivel sim" title="Disponível"></span>`
                : `<span class="autocomplete-item-disponivel nao" title="Indisponível"></span>`;

            return `
                <a href="${escapeHtml(item.url)}"
                   class="autocomplete-item"
                   data-index="${index}">
                    ${img}
                    <div class="autocomplete-item-info">
                        <p class="autocomplete-item-title">
                            ${highlightTerm(item.descricao, term)} ${disponivel}
                        </p>
                        <p class="autocomplete-item-meta">
                            ${item.referencia ? `<strong>Ref:</strong> ${highlightTerm(item.referencia, term)} • ` : ''}
                            ${escapeHtml(item.categoria || 'Sem categoria')}
                        </p>
                    </div>
                    <div class="autocomplete-item-price">${escapeHtml(item.preco)}</div>
                </a>
            `;
        }).join('');

        resultsContainer.querySelectorAll('.autocomplete-item').forEach(el => {
            el.addEventListener('mouseenter', () => {
                selectedIndex = parseInt(el.dataset.index, 10);
                updateActiveItem();
            });
        });
    }

    function updateActiveItem() {
        resultsContainer.querySelectorAll('.autocomplete-item').forEach((el, i) => {
            el.classList.toggle('active', i === selectedIndex);
        });
        const active = resultsContainer.querySelector('.autocomplete-item.active');
        if (active) active.scrollIntoView({ block: 'nearest' });
    }

    function fetchResults(term) {
        if (currentRequest) currentRequest.abort();

        if (term.length < 2) {
            closeDropdown();
            return;
        }

        openDropdown();
        loadingEl.classList.remove('d-none');
        emptyEl.classList.add('d-none');
        resultsContainer.innerHTML = '';

        currentRequest = new AbortController();

        fetch(`${autocompleteUrl}?q=${encodeURIComponent(term)}`, {
            headers: { 'Accept': 'application/json' },
            signal:  currentRequest.signal,
        })
        .then(r => r.json())
        .then(data => renderItems(data, term))
        .catch(err => {
            if (err.name !== 'AbortError') {
                console.error('Erro no autocomplete:', err);
                loadingEl.classList.add('d-none');
                emptyEl.classList.remove('d-none');
            }
        });
    }

    input.addEventListener('input', function() {
        const term = this.value.trim();
        debounce(() => fetchResults(term), 250);
    });

    input.addEventListener('focus', function() {
        if (this.value.trim().length >= 2) {
            fetchResults(this.value.trim());
        }
    });

    input.addEventListener('keydown', function(e) {
        const isOpen = dropdown.classList.contains('show');

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (currentItems.length > 0) {
                selectedIndex = (selectedIndex + 1) % currentItems.length;
                updateActiveItem();
            }
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (currentItems.length > 0) {
                selectedIndex = selectedIndex <= 0 ? currentItems.length - 1 : selectedIndex - 1;
                updateActiveItem();
            }
        } else if (e.key === 'Enter') {
            if (isOpen && selectedIndex >= 0 && currentItems[selectedIndex]) {
                e.preventDefault();
                window.location.href = currentItems[selectedIndex].url;
            }
        } else if (e.key === 'Escape') {
            closeDropdown();
        }
    });

    document.addEventListener('click', function(e) {
        if (!input.contains(e.target) && !dropdown.contains(e.target)) {
            closeDropdown();
        }
    });
})();
</script>
@endpush