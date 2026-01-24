// Cart functionality
document.addEventListener('DOMContentLoaded', function() {
    // Add to cart buttons
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.productId;
            addToCart(productId);
        });
    });
});

function addToCart(productId) {
    const button = document.querySelector(`.add-to-cart[data-product-id="${productId}"]`);
    const originalContent = button.innerHTML;
    
    // Show loading state
    button.disabled = true;
    button.innerHTML = '<i class="ph-bold ph-spinner animate-spin"></i>';
    
    fetch('/cart/add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: JSON.stringify({
            product_id: productId,
            quantity: 1
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success feedback
            button.innerHTML = '<i class="ph-bold ph-check"></i>';
            button.classList.add('bg-green');
            
            // Update cart count in navigation if exists
            updateCartCount(data.cart_count);
            
            // Reset button after 1 second
            setTimeout(() => {
                button.innerHTML = originalContent;
                button.classList.remove('bg-green');
                button.disabled = false;
            }, 1000);
        } else {
            alert(data.message || 'Ошибка при добавлении товара');
            button.innerHTML = originalContent;
            button.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Ошибка при добавлении товара в корзину');
        button.innerHTML = originalContent;
        button.disabled = false;
    });
}

function updateCartCount(count) {
    // Update cart badge if exists
    const cartBadge = document.querySelector('.cart-count-badge');
    if (cartBadge) {
        cartBadge.textContent = count;
        cartBadge.style.display = count > 0 ? 'block' : 'none';
    }
}

// Load cart count on page load
if (typeof fetch !== 'undefined') {
    fetch('/cart/summary')
        .then(response => response.json())
        .then(data => {
            updateCartCount(data.count);
        })
        .catch(error => console.error('Error loading cart summary:', error));
}
