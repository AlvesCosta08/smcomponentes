{{-- resources/views/produtos/partials/wishlist-button.blade.php --}}
@auth
    <button type="button" 
            class="btn wishlist-btn {{ isset($inWishlist) && $inWishlist ? 'btn-danger' : 'btn-outline-danger' }}"
            data-produto-id="{{ $produto->id }}"
            onclick="toggleWishlist(this)">
        <i class="fas fa-heart"></i>
        <span class="d-none d-sm-inline">{{ isset($inWishlist) && $inWishlist ? 'Remover' : 'Favoritar' }}</span>
    </button>
@else
    <a href="{{ route('login') }}" class="btn btn-outline-danger">
        <i class="fas fa-heart"></i> Favoritar
    </a>
@endauth

@push('scripts')
<script>
function toggleWishlist(btn) {
    const produtoId = btn.dataset.produtoId;
    const isInWishlist = btn.classList.contains('btn-danger');
    
    const url = isInWishlist ? '{{ route("wishlist.remover") }}' : '{{ route("wishlist.adicionar") }}';
    
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ produto_id: produtoId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.in_wishlist) {
                btn.classList.remove('btn-outline-danger');
                btn.classList.add('btn-danger');
                btn.querySelector('span').textContent = 'Remover';
            } else {
                btn.classList.remove('btn-danger');
                btn.classList.add('btn-outline-danger');
                btn.querySelector('span').textContent = 'Favoritar';
            }
            showNotification(data.message, 'success');
        }
    })
    .catch(() => {
        showNotification('Erro ao processar', 'error');
    });
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 3000);
}
</script>
@endpush