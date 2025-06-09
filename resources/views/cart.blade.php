@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Shopping Cart</li>
                </ol>
            </nav>
            <h1 class="mb-4">Shopping Cart</h1>
            
            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif
            
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            <thead class="table-light">
                                <tr>
                                    <th class="py-3">Product</th>
                                    <th class="py-3 text-center">Price</th>
                                    <th class="py-3 text-center">Quantity</th>
                                    <th class="py-3 text-center">Subtotal</th>
                                    <th class="py-3 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody id="cart-items-container">
                                <!-- Cart items will be inserted here via JavaScript -->
                                <tr id="empty-cart-message" class="d-none">
                                    <td colspan="5" class="text-center py-5">
                                        <div class="py-4">
                                            <i class="bi bi-cart-x fs-1 text-muted"></i>
                                            <p class="mt-3 mb-4">Your cart is empty.</p>
                                            <a href="{{ route('products.index') }}" class="btn btn-primary px-4">Continue Shopping</a>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-5">
                <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Continue Shopping
                </a>
                <button id="update-cart-btn" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-repeat me-2"></i>Update Cart
                </button>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Order Summary</h5>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between mb-3">
                        <span>Subtotal</span>
                        <span id="cart-subtotal" class="fw-medium">$0.00</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>Shipping</span>
                        <span class="text-muted">Calculated at checkout</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-4">
                        <span class="fw-bold">Total</span>
                        <span id="cart-total" class="fw-bold">$0.00</span>
                    </div>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i> Checkout feature is currently under maintenance.
                    </div>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="mb-3">We Accept</h5>
                    <div class="d-flex gap-3">
                        <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/visa/visa-original.svg" alt="Visa" height="30">
                        <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/mastercard/mastercard-original.svg" alt="Mastercard" height="30">
                        <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/paypal/paypal-original.svg" alt="PayPal" height="30">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Item template for JavaScript -->
<template id="cart-item-template">
    <tr class="cart-item" data-id="">
        <td class="py-3">
            <div class="d-flex align-items-center">
                <img src="" alt="Product image" class="cart-item-image rounded me-3" style="width: 80px; height: 80px; object-fit: cover;">
                <div>
                    <h6 class="cart-item-name mb-1"></h6>
                    <small class="text-muted cart-item-sku d-block"></small>
                    <div class="stock-warning text-danger small mt-1 d-none">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i><span class="stock-message"></span>
                    </div>
                </div>
            </div>
        </td>
        <td class="py-3 text-center cart-item-price"></td>
        <td class="py-3 text-center">
            <div class="input-group input-group-sm mx-auto" style="max-width: 130px;">
                <button class="btn btn-outline-secondary quantity-decrease" type="button">
                    <i class="bi bi-dash"></i>
                </button>
                <input type="number" class="form-control text-center quantity-input" min="1" value="1">
                <button class="btn btn-outline-secondary quantity-increase" type="button">
                    <i class="bi bi-plus"></i>
                </button>
            </div>
        </td>
        <td class="py-3 text-center cart-item-subtotal fw-medium"></td>
        <td class="py-3 text-center">
            <button class="btn btn-sm btn-link text-danger remove-item-btn" title="Remove item">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    </tr>
</template>

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        @auth
        loadCart();
        
        // Event delegation for quantity changes and item removal
        document.getElementById('cart-items-container').addEventListener('click', function(event) {
            const target = event.target;
            const cartItem = target.closest('.cart-item');
            
            if (!cartItem) return;
            
            const itemId = cartItem.dataset.id;
            const quantityInput = cartItem.querySelector('.quantity-input');
            let quantity = parseInt(quantityInput.value);
            
            if (target.classList.contains('quantity-decrease') || target.closest('.quantity-decrease')) {
                if (quantity > 1) {
                    updateCartItemQuantity(itemId, quantity - 1);
                }
            } else if (target.classList.contains('quantity-increase') || target.closest('.quantity-increase')) {
                updateCartItemQuantity(itemId, quantity + 1);
            } else if (target.classList.contains('remove-item-btn') || target.closest('.remove-item-btn')) {
                removeCartItem(itemId);
            }
        });
        
        // Handle direct input of quantity
        document.getElementById('cart-items-container').addEventListener('change', function(event) {
            const target = event.target;
            
            if (target.classList.contains('quantity-input')) {
                const cartItem = target.closest('.cart-item');
                const itemId = cartItem.dataset.id;
                let quantity = parseInt(target.value);
                
                if (isNaN(quantity) || quantity < 1) {
                    quantity = 1;
                    target.value = 1;
                }
                
                updateCartItemQuantity(itemId, quantity);
            }
        });
        
        // Update cart button
        document.getElementById('update-cart-btn').addEventListener('click', function() {
            loadCart();
        });
        @endauth
    });
    
    @auth
    function loadCart() {
        // Use the cart.js library if available
        if (typeof cart !== 'undefined') {
            cart.getCart()
                .then(data => {
                    if (data) {
                        renderCart(data);
                    }
                })
                .catch(error => {
                    console.error('Error loading cart:', error);
                });
        } else {
            // Fallback to direct fetch
            fetch('/api/cart', {
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Authorization': `Bearer ${localStorage.getItem('token')}`
                }
            })
            .then(response => {
                if (!response.ok) {
                    if (response.status === 401) {
                        // Handle unauthorized - user not logged in
                        showEmptyCart();
                        return Promise.reject('Please log in to view your cart');
                    }
                    return response.json().then(data => Promise.reject(data.message || 'An error occurred'));
                }
                return response.json();
            })
            .then(data => {
                renderCart(data);
                // Update cart count in the navbar
                if (typeof fetchCartCount === 'function') {
                    fetchCartCount();
                }
            })
            .catch(error => {
                console.error('Error loading cart:', error);
            });
        }
    }
    
    function showEmptyCart() {
        const container = document.getElementById('cart-items-container');
        const emptyMessage = document.getElementById('empty-cart-message');
        
        // Clear existing items
        container.innerHTML = '';
        
        emptyMessage.classList.remove('d-none');
        container.appendChild(emptyMessage);
        document.getElementById('cart-subtotal').textContent = '$0.00';
        document.getElementById('cart-total').textContent = '$0.00';
    }
    
    function renderCart(cart) {
        const container = document.getElementById('cart-items-container');
        const template = document.getElementById('cart-item-template');
        const emptyMessage = document.getElementById('empty-cart-message');
        
        // Clear existing items
        container.innerHTML = '';
        
        if (!cart.items || cart.items.length === 0) {
            emptyMessage.classList.remove('d-none');
            container.appendChild(emptyMessage);
            document.getElementById('cart-subtotal').textContent = '$0.00';
            document.getElementById('cart-total').textContent = '$0.00';
            return;
        }
        
        // Add each item
        cart.items.forEach(item => {
            const clone = template.content.cloneNode(true);
            const row = clone.querySelector('.cart-item');
            
            row.dataset.id = item.id;
            row.querySelector('.cart-item-name').textContent = item.product.name;
            row.querySelector('.cart-item-sku').textContent = `SKU: ${item.product.sku}`;
            
            // Set image with fallback
            const imgElement = row.querySelector('.cart-item-image');
            if (item.product.image_url) {
                imgElement.src = item.product.image_url;
            } else if (item.product.images) {
                try {
                    const images = typeof item.product.images === 'string' ? 
                        JSON.parse(item.product.images) : item.product.images;
                    imgElement.src = (images.main ? `/storage/products/original/${images.main}` : 
                        (Array.isArray(images) && images.length > 0 ? images[0] : '/images/no-image.jpg'));
                } catch (e) {
                    imgElement.src = '/images/no-image.jpg';
                }
            } else {
                imgElement.src = '/images/no-image.jpg';
            }
            
            row.querySelector('.cart-item-price').textContent = `$${parseFloat(item.price).toFixed(2)}`;
            row.querySelector('.quantity-input').value = item.quantity;
            row.querySelector('.cart-item-subtotal').textContent = `$${parseFloat(item.subtotal).toFixed(2)}`;
            
            container.appendChild(row);
        });
        
        // Update total
        document.getElementById('cart-subtotal').textContent = `$${parseFloat(cart.total_amount).toFixed(2)}`;
        document.getElementById('cart-total').textContent = `$${parseFloat(cart.total_amount).toFixed(2)}`;
    }
    
    function updateCartItemQuantity(itemId, quantity) {
        // Use the cart.js library if available
        if (typeof cart !== 'undefined') {
            cart.updateItem(itemId, quantity)
                .then(data => {
                    if (data) {
                        renderCart(data.cart);
                        cart.showNotification('success', 'Cart updated successfully');
                    }
                })
                .catch(error => {
                    console.error('Error updating cart item:', error);
                    cart.showNotification('error', error.message || 'An error occurred while updating your cart');
                    // Reload cart to reset any invalid quantities
                    loadCart();
                });
        } else {
            // Fallback to direct fetch
            fetch(`/api/cart/${itemId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Authorization': `Bearer ${localStorage.getItem('token')}`
                },
                body: JSON.stringify({ quantity })
            })
            .then(response => {
                if (!response.ok) {
                    if (response.status === 401) {
                        window.location.href = '{{ route("login") }}';
                        return Promise.reject('Please log in to update your cart');
                    }
                    return response.json().then(data => Promise.reject(data.message || 'An error occurred'));
                }
                return response.json();
            })
            .then(data => {
                renderCart(data.cart);
            })
            .catch(error => {
                console.error('Error updating cart item:', error);
                // If there's a specific error message, show it
                if (typeof error === 'string') {
                    alert(error);
                }
                // Reload cart to reset any invalid quantities
                loadCart();
            });
        }
    }
    
    function removeCartItem(itemId) {
        // Use the cart.js library if available
        if (typeof cart !== 'undefined') {
            cart.removeItem(itemId)
                .then(data => {
                    if (data) {
                        renderCart(data.cart);
                        cart.showNotification('success', 'Item removed from cart');
                    }
                })
                .catch(error => {
                    console.error('Error removing cart item:', error);
                    cart.showNotification('error', error.message || 'An error occurred while removing the item');
                });
        } else {
            // Fallback to direct fetch
            fetch(`/api/cart/${itemId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Authorization': `Bearer ${localStorage.getItem('token')}`
                }
            })
            .then(response => {
                if (!response.ok) {
                    if (response.status === 401) {
                        window.location.href = '{{ route("login") }}';
                        return Promise.reject('Please log in to remove items from your cart');
                    }
                    return response.json().then(data => Promise.reject(data.message || 'An error occurred'));
                }
                return response.json();
            })
            .then(data => {
                renderCart(data.cart);
            })
            .catch(error => {
                console.error('Error removing cart item:', error);
            });
        }
    }
    @endauth
</script>
@endsection 