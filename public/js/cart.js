// Cart functionality
document.addEventListener('DOMContentLoaded', function() {
    // Add to cart buttons
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.productId;
            addToCart(productId);
        });
    });

    // Increase quantity buttons in product cards
    document.querySelectorAll('.cart-increase').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.productId;
            const quantityDisplay = document.querySelector(`.cart-quantity-display[data-product-id="${productId}"]`);
            const currentQuantity = parseInt(quantityDisplay.textContent);
            updateCartQuantity(productId, currentQuantity + 1, 'increase');
        });
    });

    // Decrease quantity buttons in product cards
    document.querySelectorAll('.cart-decrease').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.productId;
            const quantityDisplay = document.querySelector(`.cart-quantity-display[data-product-id="${productId}"]`);
            const currentQuantity = parseInt(quantityDisplay.textContent);
            if (currentQuantity > 1) {
                updateCartQuantity(productId, currentQuantity - 1, 'decrease');
            } else {
                // Remove from cart if quantity becomes 0
                removeFromCart(productId);
            }
        });
    });
});

function addToCart(productId) {
    const button = document.querySelector(`.add-to-cart[data-product-id="${productId}"]`);
    if (!button) return;
    
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
            // Replace add button with quantity controls
            replaceWithQuantityControls(productId, 1);
            
            // Update cart count in navigation if exists
            updateCartCount(data.cart_count);
        } else {
            button.innerHTML = originalContent;
            button.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        button.innerHTML = originalContent;
        button.disabled = false;
    });
}

function updateCartQuantity(productId, quantity, buttonType = null) {
    const quantityDisplay = document.querySelector(`.cart-quantity-display[data-product-id="${productId}"]`);
    if (!quantityDisplay) return;
    
    // Find buttons and store original content
    const increaseBtn = document.querySelector(`.cart-increase[data-product-id="${productId}"]`);
    const decreaseBtn = document.querySelector(`.cart-decrease[data-product-id="${productId}"]`);
    
    let increaseOriginalContent = null;
    let decreaseOriginalContent = null;
    
    // Show spinner on clicked button
    if (buttonType === 'increase' && increaseBtn) {
        increaseOriginalContent = increaseBtn.innerHTML;
        increaseBtn.disabled = true;
        increaseBtn.innerHTML = '<i class="ph-bold ph-spinner animate-spin text-xs"></i>';
    }
    
    if (buttonType === 'decrease' && decreaseBtn) {
        decreaseOriginalContent = decreaseBtn.innerHTML;
        decreaseBtn.disabled = true;
        decreaseBtn.innerHTML = '<i class="ph-bold ph-spinner animate-spin text-xs"></i>';
    }
    
    // Add animation class
    quantityDisplay.classList.add('quantity-animate');
    
    fetch(`/cart/${productId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: JSON.stringify({ quantity: quantity })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update quantity display with animation
            quantityDisplay.textContent = quantity;
            
            // Remove animation class after animation completes
            setTimeout(() => {
                quantityDisplay.classList.remove('quantity-animate');
            }, 300);
            
            // Update cart count in navigation
            updateCartCount(data.cart_count);
        } else {
            quantityDisplay.classList.remove('quantity-animate');
        }
        
        // Restore buttons
        if (increaseBtn && increaseOriginalContent) {
            increaseBtn.innerHTML = increaseOriginalContent;
            increaseBtn.disabled = false;
        }
        if (decreaseBtn && decreaseOriginalContent) {
            decreaseBtn.innerHTML = decreaseOriginalContent;
            decreaseBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        quantityDisplay.classList.remove('quantity-animate');
        
        // Restore buttons on error
        if (increaseBtn && increaseOriginalContent) {
            increaseBtn.innerHTML = increaseOriginalContent;
            increaseBtn.disabled = false;
        }
        if (decreaseBtn && decreaseOriginalContent) {
            decreaseBtn.innerHTML = decreaseOriginalContent;
            decreaseBtn.disabled = false;
        }
    });
}

function removeFromCart(productId) {
    // Try multiple ways to find the quantity controls
    let quantityControls = document.querySelector(`.cart-increase[data-product-id="${productId}"]`)?.closest('.flex.items-center.border');
    
    // If not found, try finding by decrease button
    if (!quantityControls) {
        quantityControls = document.querySelector(`.cart-decrease[data-product-id="${productId}"]`)?.closest('.flex.items-center.border');
    }
    
    // If still not found, try finding by quantity display
    if (!quantityControls) {
        const quantityDisplay = document.querySelector(`.cart-quantity-display[data-product-id="${productId}"]`);
        if (quantityDisplay) {
            quantityControls = quantityDisplay.closest('.flex.items-center.border');
        }
    }
    
    // Last resort: find any flex container with cart controls
    if (!quantityControls) {
        const allControls = document.querySelectorAll(`[data-product-id="${productId}"]`);
        for (let control of allControls) {
            const flexParent = control.closest('.flex');
            if (flexParent && (flexParent.querySelector('.cart-decrease') || flexParent.querySelector('.cart-increase'))) {
                quantityControls = flexParent;
                break;
            }
        }
    }
    
    if (!quantityControls) {
        console.error('Quantity controls not found for product:', productId);
        // Still try to remove from cart even if UI update fails
        fetch(`/cart/${productId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateCartCount(data.cart_count);
                // Force page reload as fallback
                location.reload();
            }
        });
        return;
    }
    
    // Store reference to parent before animation
    const parent = quantityControls.parentElement;
    
    // Add fade-out animation (faster)
    quantityControls.style.transition = 'opacity 0.15s ease-out, transform 0.15s ease-out';
    quantityControls.style.opacity = '0';
    quantityControls.style.transform = 'scale(0.9)';
    
    fetch(`/cart/${productId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Wait for fade-out animation to complete, then replace with add button (faster)
            setTimeout(() => {
                // Double-check element still exists
                if (quantityControls && quantityControls.parentElement) {
                    replaceWithAddButtonDirect(productId, quantityControls, parent);
                } else {
                    // If element was removed, try to find and replace
                    replaceWithAddButton(productId);
                }
            }, 150);
            
            // Update cart count in navigation
            updateCartCount(data.cart_count);
        } else {
            // Restore on error
            quantityControls.style.opacity = '1';
            quantityControls.style.transform = 'scale(1)';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Restore on error
        quantityControls.style.opacity = '1';
        quantityControls.style.transform = 'scale(1)';
    });
}

function replaceWithAddButtonDirect(productId, quantityControlsElement, parentElement) {
    if (!quantityControlsElement || !parentElement) {
        console.error('Missing elements for replacement');
        replaceWithAddButton(productId);
        return;
    }
    
    // Create new button element
    const addButton = document.createElement('button');
    addButton.type = 'button';
    addButton.className = 'add-to-cart cursor-pointer text-sm h-6.5 w-6.5 text-white text-center bg-main rounded-sm hover:bg-opacity-90 transition';
    addButton.setAttribute('data-product-id', productId);
    addButton.setAttribute('title', 'Добавить в корзину');
    addButton.style.opacity = '0';
    addButton.style.transform = 'scale(0.9)';
    addButton.innerHTML = '<i class="ph ph-plus block"></i>';
    
    // Replace the quantity controls with new button
    quantityControlsElement.replaceWith(addButton);
    
    // Animate fade-in
    setTimeout(() => {
        addButton.style.transition = 'opacity 0.3s ease-in, transform 0.3s ease-in';
        addButton.style.opacity = '1';
        addButton.style.transform = 'scale(1)';
    }, 10);
    
    // Attach event listener
    addButton.addEventListener('click', function() {
        addToCart(productId);
    });
}

function replaceWithQuantityControls(productId, quantity) {
    const button = document.querySelector(`.add-to-cart[data-product-id="${productId}"]`);
    if (!button) return;
    
    const parent = button.parentElement;
    const quantityControls = `
        <div class="flex items-center border border-main rounded-sm">
            <button
                type="button"
                class="cart-decrease text-sm h-6.5 w-6.5 text-main hover:bg-main hover:text-white transition flex items-center justify-center"
                data-product-id="${productId}"
                title="Уменьшить количество"
            >
                <i class="ph-bold ph-minus text-xs"></i>
            </button>
            <span class="cart-quantity-display px-2 text-sm font-semibold text-main min-w-[1.5rem] text-center" data-product-id="${productId}">
                ${quantity}
            </span>
            <button
                type="button"
                class="cart-increase text-sm h-6.5 w-6.5 text-main hover:bg-main hover:text-white transition flex items-center justify-center"
                data-product-id="${productId}"
                title="Увеличить количество"
            >
                <i class="ph-bold ph-plus text-xs"></i>
            </button>
        </div>
    `;
    
    parent.innerHTML = parent.innerHTML.replace(button.outerHTML, quantityControls);
    
    // Reattach event listeners
    const increaseBtn = parent.querySelector(`.cart-increase[data-product-id="${productId}"]`);
    const decreaseBtn = parent.querySelector(`.cart-decrease[data-product-id="${productId}"]`);
    
    if (increaseBtn) {
        increaseBtn.addEventListener('click', function() {
            const quantityDisplay = document.querySelector(`.cart-quantity-display[data-product-id="${productId}"]`);
            const currentQuantity = parseInt(quantityDisplay.textContent);
            updateCartQuantity(productId, currentQuantity + 1, 'increase');
        });
    }
    
    if (decreaseBtn) {
        decreaseBtn.addEventListener('click', function() {
            const quantityDisplay = document.querySelector(`.cart-quantity-display[data-product-id="${productId}"]`);
            const currentQuantity = parseInt(quantityDisplay.textContent);
            if (currentQuantity > 1) {
                updateCartQuantity(productId, currentQuantity - 1, 'decrease');
            } else {
                removeFromCart(productId);
            }
        });
    }
}

function replaceWithAddButton(productId, quantityControlsElement = null) {
    // Use provided element or try to find it
    let quantityControls = quantityControlsElement;
    
    if (!quantityControls) {
        quantityControls = document.querySelector(`.cart-increase[data-product-id="${productId}"]`)?.closest('.flex.items-center.border');
    }
    
    if (!quantityControls) {
        quantityControls = document.querySelector(`.cart-decrease[data-product-id="${productId}"]`)?.closest('.flex.items-center.border');
    }
    
    if (!quantityControls) {
        const quantityDisplay = document.querySelector(`.cart-quantity-display[data-product-id="${productId}"]`);
        if (quantityDisplay) {
            quantityControls = quantityDisplay.closest('.flex.items-center.border');
        }
    }
    
    if (!quantityControls) {
        // Last resort: find any flex container with cart controls
        const allControls = document.querySelectorAll(`[data-product-id="${productId}"]`);
        for (let control of allControls) {
            const flexParent = control.closest('.flex');
            if (flexParent && (flexParent.querySelector('.cart-decrease') || flexParent.querySelector('.cart-increase'))) {
                quantityControls = flexParent;
                break;
            }
        }
    }
    
    if (!quantityControls) {
        console.error('Could not find quantity controls to replace for product:', productId);
        return;
    }
    
    const parent = quantityControls.parentElement;
    if (!parent) {
        console.error('Parent element not found');
        return;
    }
    
    // Create new button element
    const addButton = document.createElement('button');
    addButton.type = 'button';
    addButton.className = 'add-to-cart cursor-pointer text-sm h-6.5 w-6.5 text-white text-center bg-main rounded-sm hover:bg-opacity-90 transition';
    addButton.setAttribute('data-product-id', productId);
    addButton.setAttribute('title', 'Добавить в корзину');
    addButton.style.opacity = '0';
    addButton.style.transform = 'scale(0.9)';
    addButton.innerHTML = '<i class="ph ph-plus block"></i>';
    
    // Replace the quantity controls with new button
    quantityControls.replaceWith(addButton);
    
    // Animate fade-in
    setTimeout(() => {
        addButton.style.transition = 'opacity 0.3s ease-in, transform 0.3s ease-in';
        addButton.style.opacity = '1';
        addButton.style.transform = 'scale(1)';
    }, 10);
    
    // Attach event listener
    addButton.addEventListener('click', function() {
        addToCart(productId);
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
