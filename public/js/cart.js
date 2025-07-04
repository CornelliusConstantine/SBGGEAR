/**
 * SBGEAR E-commerce Cart Operations
 * Handles cart functionality across the site
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize cart count
    updateCartCount();
    
    // Add to cart buttons
    const addToCartButtons = document.querySelectorAll('.add-to-cart-btn');
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const productId = this.dataset.productId;
            const quantity = document.querySelector('#quantity') ? parseInt(document.querySelector('#quantity').value) : 1;
            
            addToCart(productId, quantity);
        });
    });
    
    // Update quantity buttons in cart
    const updateQuantityButtons = document.querySelectorAll('.update-quantity-btn');
    updateQuantityButtons.forEach(button => {
        button.addEventListener('click', function() {
            const itemId = this.dataset.itemId;
            const quantity = parseInt(document.querySelector(`#quantity-${itemId}`).value);
            
            updateCartItem(itemId, quantity);
        });
    });
    
    // Remove from cart buttons
    const removeButtons = document.querySelectorAll('.remove-from-cart-btn');
    removeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const itemId = this.dataset.itemId;
            removeFromCart(itemId);
        });
    });
});

/**
 * Add a product to the cart
 */
function addToCart(productId, quantity = 1) {
    showLoading();
    
    fetch('/api/cart/add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Authorization': `Bearer ${localStorage.getItem('token')}`
        },
        body: JSON.stringify({
            product_id: productId,
            quantity: quantity
        })
    })
    .then(response => {
        if (!response.ok) {
            if (response.status === 401) {
                window.location.href = '/login?redirect=' + encodeURIComponent(window.location.pathname);
                return Promise.reject('Unauthorized');
            }
            return response.json().then(data => Promise.reject(data.message || 'An error occurred'));
        }
        return response.json();
    })
    .then(data => {
        hideLoading();
        showNotification('success', 'Product added to cart');
        updateCartCount();
    })
    .catch(error => {
        hideLoading();
        if (error !== 'Unauthorized') {
            showNotification('error', error);
        }
    });
}

/**
 * Update cart item quantity
 */
function updateCartItem(itemId, quantity) {
    showLoading();
    
    fetch('/api/cart/update', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Authorization': `Bearer ${localStorage.getItem('token')}`
        },
        body: JSON.stringify({
            cart_item_id: itemId,
            quantity: quantity
        })
    })
    .then(response => {
        if (!response.ok) {
            if (response.status === 401) {
                window.location.href = '/login?redirect=' + encodeURIComponent(window.location.pathname);
                return Promise.reject('Unauthorized');
            }
            return response.json().then(data => Promise.reject(data.message || 'An error occurred'));
        }
        return response.json();
    })
    .then(data => {
        hideLoading();
        
        // Update subtotal
        const subtotalElement = document.querySelector(`#subtotal-${itemId}`);
        if (subtotalElement) {
            subtotalElement.textContent = `$${parseFloat(data.item.subtotal).toFixed(2)}`;
        }
        
        // Update total
        const totalElement = document.querySelector('#cart-total');
        if (totalElement) {
            totalElement.textContent = `$${parseFloat(data.cart.total_amount).toFixed(2)}`;
        }
        
        showNotification('success', 'Cart updated');
        updateCartCount();
    })
    .catch(error => {
        hideLoading();
        if (error !== 'Unauthorized') {
            showNotification('error', error);
        }
    });
}

/**
 * Remove item from cart
 */
function removeFromCart(itemId) {
    showLoading();
    
    fetch('/api/cart/remove', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Authorization': `Bearer ${localStorage.getItem('token')}`
        },
        body: JSON.stringify({
            cart_item_id: itemId
        })
    })
    .then(response => {
        if (!response.ok) {
            if (response.status === 401) {
                window.location.href = '/login?redirect=' + encodeURIComponent(window.location.pathname);
                return Promise.reject('Unauthorized');
            }
            return response.json().then(data => Promise.reject(data.message || 'An error occurred'));
        }
        return response.json();
    })
    .then(data => {
        hideLoading();
        
        // Remove item from DOM
        const itemElement = document.querySelector(`#cart-item-${itemId}`);
        if (itemElement) {
            itemElement.remove();
        }
        
        // Update total
        const totalElement = document.querySelector('#cart-total');
        if (totalElement) {
            totalElement.textContent = `$${parseFloat(data.cart.total_amount).toFixed(2)}`;
        }
        
        // Show empty cart message if no items left
        if (data.cart.items.length === 0) {
            const emptyMessage = document.querySelector('#empty-cart-message');
            if (emptyMessage) {
                emptyMessage.classList.remove('d-none');
            }
        }
        
        showNotification('success', 'Item removed from cart');
        updateCartCount();
    })
    .catch(error => {
        hideLoading();
        if (error !== 'Unauthorized') {
            showNotification('error', error);
        }
    });
}

/**
 * Update cart count in navbar
 */
function updateCartCount() {
    fetch('/api/cart/count', {
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Authorization': `Bearer ${localStorage.getItem('token')}`
        }
    })
    .then(response => {
        if (!response.ok) {
            return Promise.reject('Failed to get cart count');
        }
        return response.json();
    })
    .then(data => {
        const cartCountElement = document.querySelector('#cart-count');
        if (cartCountElement) {
            cartCountElement.textContent = data.count;
            
            // Show/hide based on count
            if (data.count > 0) {
                cartCountElement.classList.remove('d-none');
            } else {
                cartCountElement.classList.add('d-none');
            }
        }
    })
    .catch(error => {
        console.error('Error updating cart count:', error);
    });
}

/**
 * Show loading indicator
 */
function showLoading() {
    // Check if loading overlay already exists
    let loadingOverlay = document.querySelector('#loading-overlay');
    
    if (!loadingOverlay) {
        loadingOverlay = document.createElement('div');
        loadingOverlay.id = 'loading-overlay';
        loadingOverlay.style.position = 'fixed';
        loadingOverlay.style.top = '0';
        loadingOverlay.style.left = '0';
        loadingOverlay.style.width = '100%';
        loadingOverlay.style.height = '100%';
        loadingOverlay.style.backgroundColor = 'rgba(255, 255, 255, 0.7)';
        loadingOverlay.style.display = 'flex';
        loadingOverlay.style.justifyContent = 'center';
        loadingOverlay.style.alignItems = 'center';
        loadingOverlay.style.zIndex = '9999';
        
        const spinner = document.createElement('div');
        spinner.className = 'spinner-border text-primary';
        spinner.setAttribute('role', 'status');
        
        const srOnly = document.createElement('span');
        srOnly.className = 'visually-hidden';
        srOnly.textContent = 'Loading...';
        
        spinner.appendChild(srOnly);
        loadingOverlay.appendChild(spinner);
        
        document.body.appendChild(loadingOverlay);
    } else {
        loadingOverlay.style.display = 'flex';
    }
}

/**
 * Hide loading indicator
 */
function hideLoading() {
    const loadingOverlay = document.querySelector('#loading-overlay');
    if (loadingOverlay) {
        loadingOverlay.style.display = 'none';
    }
}

/**
 * Show notification
 */
function showNotification(type, message) {
    // Check if notification container exists
    let notificationContainer = document.querySelector('#notification-container');
    
    if (!notificationContainer) {
        notificationContainer = document.createElement('div');
        notificationContainer.id = 'notification-container';
        notificationContainer.style.position = 'fixed';
        notificationContainer.style.top = '20px';
        notificationContainer.style.right = '20px';
        notificationContainer.style.zIndex = '9999';
        
        document.body.appendChild(notificationContainer);
    }
    
    // Create notification
    const notification = document.createElement('div');
    notification.className = `toast align-items-center ${type === 'success' ? 'bg-success' : 'bg-danger'} text-white border-0`;
    notification.setAttribute('role', 'alert');
    notification.setAttribute('aria-live', 'assertive');
    notification.setAttribute('aria-atomic', 'true');
    
    const notificationContent = `
        <div class="d-flex">
            <div class="toast-body">
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;
    
    notification.innerHTML = notificationContent;
    notificationContainer.appendChild(notification);
    
    // Initialize Bootstrap toast
    const toast = new bootstrap.Toast(notification, {
        autohide: true,
        delay: 3000
    });
    
    toast.show();
    
    // Remove notification after it's hidden
    notification.addEventListener('hidden.bs.toast', function() {
        notification.remove();
    });
}
