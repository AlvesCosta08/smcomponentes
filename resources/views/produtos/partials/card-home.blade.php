{{-- resources/views/produtos/partials/card-home.blade.php --}}
@php
    $precoUnitario = $produto->valor_unitario ?? 0;
    $precoPromocional = $produto->preco_promocional ?? 0;
    $temPromocao = $precoPromocional > 0 && $precoPromocional < $precoUnitario;
    $descontoPercentual = $temPromocao ? round((($precoUnitario - $precoPromocional) / $precoUnitario) * 100) : 0;
    $precoFormatado = 'R$ ' . number_format($precoUnitario, 2, ',', '.');
    $precoPromocionalFormatado = $temPromocao ? 'R$ ' . number_format($precoPromocional, 2, ',', '.') : null;
    $podeComprar = ($produto->ativo ?? 0) == 1 && ($produto->quantidade ?? 0) > 0 && ($produto->status ?? '') === 'disponivel';
    $corBadge = $corBadge ?? 'danger';
    $corPreco = $corPreco ?? 'primary';
@endphp

<div class="col-lg-3 col-md-4 col-6">
    <div class="card produto-card-smart h-100 shadow-sm border-0">
        <div class="card-img-wrapper">
            @if($produto->imagem)
                @php $filename = basename($produto->imagem); @endphp
                <img src="{{ asset('storage/produtos/' . $filename) }}"
                     alt="{{ $produto->descricao ?? 'Produto' }}"
                     loading="lazy"
                     onerror="this.onerror=null; this.src='{{ asset('images/produto-placeholder.jpg') }}';">
            @else
                <div class="placeholder-icon">
                    <i class="bi bi-plug"></i>
                </div>
            @endif

            @if($temPromocao)
                <span class="badge bg-{{ $corBadge }} position-absolute top-0 end-0 m-2 rounded-pill">
                    -{{ $descontoPercentual }}%
                </span>
            @endif
        </div>

        <div class="card-body d-flex flex-column">
            <h6 class="card-title">{{ $produto->descricao ?? 'Produto' }}</h6>

            <p class="card-text mt-auto">
                @if($temPromocao)
                    <span class="text-decoration-line-through text-muted me-1 small">
                        {{ $precoFormatado }}
                    </span>
                    <span class="fw-bold text-{{ $corPreco }}">
                        {{ $precoPromocionalFormatado }}
                    </span>
                @else
                    <span class="fw-bold text-{{ $corPreco }}">
                        {{ $precoFormatado }}
                    </span>
                @endif
            </p>

            @if($podeComprar)
                <a href="{{ route('produtos.show', $produto->slug ?? '#') }}"
                   class="btn btn-sm btn-outline-{{ $corPreco }} w-100 rounded-pill">
                    <i class="bi bi-eye me-1"></i> Ver Detalhes
                </a>
            @else
                <button class="btn btn-sm btn-secondary w-100 rounded-pill" disabled>
                    <i class="bi bi-x-circle me-1"></i> Indisponível
                </button>
            @endif
        </div>
    </div>
</div>

<style>
    /* ============================================================
       CARD DE PRODUTO - PROFISSIONAL
       Imagem SEMPRE enquadrada (independente do tamanho original)
       ============================================================ */

    /* ---------- CARD ---------- */
    .produto-card-smart {
        transition: transform 0.25s ease, box-shadow 0.25s ease;
        border-radius: 14px !important;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        background: #fff;
        border: 1px solid #eef0f3;
    }
    .produto-card-smart:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.10) !important;
        border-color: #dbe3ec;
    }

    /* ---------- WRAPPER DA IMAGEM ---------- */
    /* Container fixo: 180x180. A imagem se adapta SEMPRE. */
    .produto-card-smart .card-img-wrapper {
        position: relative !important;
        width: 100% !important;
        height: 180px !important;
        overflow: hidden !important;
        background: #f7f8fa !important;
        flex-shrink: 0 !important;
    }

    /* ---------- IMAGEM ---------- */
    /* object-fit: contain → mostra a imagem INTEIRA, sem cortar, com fundo */
    /* object-fit: cover   → preenche tudo, cortando o excesso */
    .produto-card-smart .card-img-wrapper > img {
        position: absolute !important;
        top: 0 !important;
        left: 0 !important;
        width: 100% !important;
        height: 100% !important;
        object-fit: contain !important;      /* ← imagem INTEIRA visível */
        object-position: center !important;
        display: block !important;
        padding: 8px !important;             /* ← respiro interno (opcional) */
        transition: transform 0.3s ease;
    }
    .produto-card-smart:hover .card-img-wrapper > img {
        transform: scale(1.05);              /* zoom suave no hover */
    }

    /* ---------- PLACEHOLDER (sem imagem) ---------- */
    .produto-card-smart .card-img-wrapper .placeholder-icon {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        width: 100% !important;
        height: 100% !important;
    }
    .produto-card-smart .card-img-wrapper .placeholder-icon i {
        font-size: 3rem;
        color: #cbd2da;
    }

    /* ---------- BADGE PROMOÇÃO ---------- */
    .produto-card-smart .card-img-wrapper .badge {
        z-index: 2;
        font-size: 0.7rem;
        font-weight: 600;
        letter-spacing: 0.3px;
    }

    /* ---------- CORPO DO CARD ---------- */
    .produto-card-smart .card-body {
        padding: 10px 12px 12px !important;
        display: flex;
        flex-direction: column;
        height: 125px;                       /* ← altura fixa pra alinhar */
        overflow: hidden;
    }

    /* ---------- TÍTULO (1 linha + "...") ---------- */
    .produto-card-smart .card-title {
        font-size: 0.82rem;
        font-weight: 600;
        line-height: 1.2;
        color: #2c3e50;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;                 /* ← sempre 1 linha */
        margin-bottom: 4px;
    }

    /* ---------- PREÇO ---------- */
    .produto-card-smart .card-text {
        font-size: 0.95rem;
        margin-bottom: 6px;
        line-height: 1.2;
    }

    /* ---------- BOTÃO ---------- */
    .produto-card-smart .btn {
        font-size: 0.75rem;
        font-weight: 600;
        padding: 5px 10px;
        flex-shrink: 0;
        margin-top: auto;                    /* ← empurra pro fundo */
        transition: all 0.2s ease;
    }
    .produto-card-smart .btn:hover {
        transform: translateY(-1px);
    }

    /* ============================================================
       RESPONSIVO
       ============================================================ */
    @media (max-width: 992px) {
        .produto-card-smart .card-img-wrapper { height: 160px !important; }
    }

    @media (max-width: 768px) {
        .produto-card-smart .card-img-wrapper { height: 140px !important; }
        .produto-card-smart .card-title { font-size: 0.76rem; }
        .produto-card-smart .card-text { font-size: 0.88rem; }
        .produto-card-smart .card-body { height: 115px; padding: 8px 10px 10px !important; }
        .produto-card-smart .btn { font-size: 0.7rem; padding: 4px 8px; }
    }

    @media (max-width: 576px) {
        .produto-card-smart .card-img-wrapper { height: 110px !important; }
        .produto-card-smart .card-title { font-size: 0.7rem; }
        .produto-card-smart .card-text { font-size: 0.8rem; }
        .produto-card-smart .card-body { height: 105px; padding: 6px 8px 8px !important; }
        .produto-card-smart .btn { font-size: 0.65rem; padding: 3px 6px; }
        .produto-card-smart .card-img-wrapper > img { padding: 4px !important; }
    }
</style>