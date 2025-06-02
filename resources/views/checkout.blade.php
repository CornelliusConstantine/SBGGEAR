@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('cart') }}">Shopping Cart</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Checkout</li>
                </ol>
            </nav>
            <h1 class="mb-4">Checkout</h1>
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Shipping Information</h5>
                </div>
                <div class="card-body p-4">
                    <form id="checkout-form">
                        <div class="row mb-3">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label for="shipping_name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="shipping_name" name="shipping_name" required>
                                <div class="invalid-feedback" id="shipping_name_error"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="shipping_phone" class="form-label">Phone Number</label>
                                <input type="text" class="form-control" id="shipping_phone" name="shipping_phone" required>
                                <div class="invalid-feedback" id="shipping_phone_error"></div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="shipping_address" class="form-label">Address</label>
                            <textarea class="form-control" id="shipping_address" name="shipping_address" rows="3" required></textarea>
                            <div class="invalid-feedback" id="shipping_address_error"></div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-4 mb-3 mb-md-0">
                                <label for="shipping_city" class="form-label">City</label>
                                <input type="text" class="form-control" id="shipping_city" name="shipping_city" required>
                                <div class="invalid-feedback" id="shipping_city_error"></div>
                            </div>
                            <div class="col-md-4 mb-3 mb-md-0">
                                <label for="shipping_province" class="form-label">Province</label>
                                <input type="text" class="form-control" id="shipping_province" name="shipping_province" required>
                                <div class="invalid-feedback" id="shipping_province_error"></div>
                            </div>
                            <div class="col-md-4">
                                <label for="shipping_postal_code" class="form-label">Postal Code</label>
                                <input type="text" class="form-control" id="shipping_postal_code" name="shipping_postal_code" required>
                                <div class="invalid-feedback" id="shipping_postal_code_error"></div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Order Notes (Optional)</label>
                            <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="Special notes for delivery or product"></textarea>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Shipping Method</h5>
                </div>
                <div class="card-body p-4">
                    <div class="form-check mb-3 py-2 border-bottom">
                        <input class="form-check-input" type="radio" name="shipping_method" id="shipping_regular" value="regular" checked>
                        <label class="form-check-label d-flex justify-content-between align-items-center w-100" for="shipping_regular">
                            <div>
                                <span class="d-block fw-medium">Regular Shipping</span>
                                <small class="text-muted">3-5 business days</small>
                            </div>
                            <span class="fw-medium">$5.00</span>
                        </label>
                    </div>
                    <div class="form-check mb-3 py-2 border-bottom">
                        <input class="form-check-input" type="radio" name="shipping_method" id="shipping_express" value="express">
                        <label class="form-check-label d-flex justify-content-between align-items-center w-100" for="shipping_express">
                            <div>
                                <span class="d-block fw-medium">Express Shipping</span>
                                <small class="text-muted">1-2 business days</small>
                            </div>
                            <span class="fw-medium">$15.00</span>
                        </label>
                    </div>
                    <div class="form-check py-2">
                        <input class="form-check-input" type="radio" name="shipping_method" id="shipping_same_day" value="same_day">
                        <label class="form-check-label d-flex justify-content-between align-items-center w-100" for="shipping_same_day">
                            <div>
                                <span class="d-block fw-medium">Same Day Delivery</span>
                                <small class="text-muted">Selected areas only</small>
                            </div>
                            <span class="fw-medium">$25.00</span>
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Payment Method</h5>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted mb-3">Payment will be processed after order confirmation.</p>
                    
                    <div class="d-flex gap-3 mb-3">
                        <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/visa/visa-original.svg" alt="Visa" height="30">
                        <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/mastercard/mastercard-original.svg" alt="Mastercard" height="30">
                        <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/paypal/paypal-original.svg" alt="PayPal" height="30">
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4 sticky-top" style="top: 20px; z-index: 1;">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Order Summary</h5>
                </div>
                <div class="card-body p-4">
                    <div id="cart-items-summary">
                        <!-- Items will be loaded here -->
                        <div class="text-center py-4 d-none" id="empty-cart-message">
                            <p class="mb-3">Your cart is empty.</p>
                            <a href="{{ route('products.index') }}" class="btn btn-primary btn-sm px-4">Shop Now</a>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span id="subtotal">$0.00</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Shipping:</span>
                        <span id="shipping-cost">$5.00</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-4">
                        <span class="fw-bold">Total:</span>
                        <span id="total-amount" class="fw-bold">$0.00</span>
                    </div>
                    
                    <button type="button" id="place-order-btn" class="btn btn-primary w-100 py-3">
                        Place Order
                    </button>
                    
                    <p class="text-muted small text-center mt-3 mb-0">
                        By placing your order, you agree to our 
                        <a href="#">Terms of Service</a> and 
                        <a href="#">Privacy Policy</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cart item template for summary -->
<template id="cart-item-summary-template">
    <div class="d-flex align-items-center mb-3 cart-summary-item">
        <img src="" alt="Product image" class="img-thumbnail me-2" style="width: 50px; height: 50px; object-fit: cover;">
        <div class="flex-grow-1">
            <h6 class="mb-0 fs-7 item-name text-truncate"></h6>
            <small class="text-muted">
                <span class="item-quantity"></span> × <span class="item-price"></span>
            </small>
        </div>
        <div class="ms-auto item-subtotal fw-medium"></div>
    </div>
</template>

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        @auth
        loadCart();
        
        // Pre-fill user information if available
        fetch('/api/user', {
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
                    // Redirect to login if not authenticated
                    window.location.href = '{{ route("login") }}?redirect={{ url()->current() }}';
                    return Promise.reject('Unauthorized');
                }
                return response.json().then(data => Promise.reject(data.message || 'An error occurred'));
            }
            return response.json();
        })
        .then(user => {
            document.getElementById('shipping_name').value = user.name || '';
            document.getElementById('shipping_phone').value = user.phone || '';
            document.getElementById('shipping_address').value = user.address || '';
            document.getElementById('shipping_city').value = user.city || '';
            document.getElementById('shipping_province').value = user.province || '';
            document.getElementById('shipping_postal_code').value = user.postal_code || '';
        })
        .catch(error => {
            if (error !== 'Unauthorized') {
                console.error('Error loading user data:', error);
            }
        });
        
        // Handle shipping method changes
        const shippingMethods = document.querySelectorAll('input[name="shipping_method"]');
        shippingMethods.forEach(method => {
            method.addEventListener('change', updateTotal);
        });
        
        // Handle place order button
        document.getElementById('place-order-btn').addEventListener('click', placeOrder);
        @else
        // Redirect to login if not authenticated
        window.location.href = '{{ route("login") }}?redirect={{ url()->current() }}';
        @endauth
    });
    
    @auth
    function loadCart() {
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
                    // Redirect to login if not authenticated
                    window.location.href = '{{ route("login") }}?redirect={{ url()->current() }}';
                    return Promise.reject('Unauthorized');
                }
                return response.json().then(data => Promise.reject(data.message || 'An error occurred'));
            }
            return response.json();
        })
        .then(cart => {
            renderCartSummary(cart);
            updateTotal();
        })
        .catch(error => {
            if (error !== 'Unauthorized') {
                console.error('Error loading cart:', error);
            }
        });
    }
    
    function renderCartSummary(cart) {
        const container = document.getElementById('cart-items-summary');
        const template = document.getElementById('cart-item-summary-template');
        const emptyMessage = document.getElementById('empty-cart-message');
        const placeOrderBtn = document.getElementById('place-order-btn');
        
        // Clear existing items
        container.innerHTML = '';
        
        if (!cart.items || cart.items.length === 0) {
            emptyMessage.classList.remove('d-none');
            container.appendChild(emptyMessage);
            placeOrderBtn.disabled = true;
            document.getElementById('subtotal').textContent = '$0.00';
            return;
        }
        
        placeOrderBtn.disabled = false;
        
        // Add each item
        cart.items.forEach(item => {
            const clone = template.content.cloneNode(true);
            
            clone.querySelector('.item-name').textContent = item.product.name;
            
            // Set image with fallback
            const imgElement = clone.querySelector('img');
            if (item.product.images) {
                try {
                    const images = JSON.parse(item.product.images);
                    imgElement.src = images[0] || '/images/no-image.jpg';
                } catch (e) {
                    imgElement.src = '/images/no-image.jpg';
                }
            } else {
                imgElement.src = '/images/no-image.jpg';
            }
            
            clone.querySelector('.item-quantity').textContent = item.quantity;
            clone.querySelector('.item-price').textContent = `$${parseFloat(item.price).toFixed(2)}`;
            clone.querySelector('.item-subtotal').textContent = `$${parseFloat(item.subtotal).toFixed(2)}`;
            
            container.appendChild(clone);
        });
        
        // Update subtotal
        document.getElementById('subtotal').textContent = `$${parseFloat(cart.total_amount).toFixed(2)}`;
        
        // Save cart total in a data attribute for calculations
        container.dataset.subtotal = cart.total_amount;
    }
    
    function updateTotal() {
        const subtotal = parseFloat(document.getElementById('cart-items-summary').dataset.subtotal) || 0;
        let shippingCost = 5; // Default shipping cost
        
        // Get selected shipping method
        const selectedShipping = document.querySelector('input[name="shipping_method"]:checked').value;
        
        if (selectedShipping === 'express') {
            shippingCost = 15;
        } else if (selectedShipping === 'same_day') {
            shippingCost = 25;
        }
        
        document.getElementById('shipping-cost').textContent = `$${shippingCost.toFixed(2)}`;
        
        const total = subtotal + shippingCost;
        document.getElementById('total-amount').textContent = `$${total.toFixed(2)}`;
    }
    
    function placeOrder() {
        // Validate form
        const form = document.getElementById('checkout-form');
        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return;
        }
        
        // Get form data
        const formData = {
            shipping_name: document.getElementById('shipping_name').value,
            shipping_phone: document.getElementById('shipping_phone').value,
            shipping_address: document.getElementById('shipping_address').value,
            shipping_city: document.getElementById('shipping_city').value,
            shipping_province: document.getElementById('shipping_province').value,
            shipping_postal_code: document.getElementById('shipping_postal_code').value,
            shipping_method: document.querySelector('input[name="shipping_method"]:checked').value,
            shipping_cost: parseFloat(document.getElementById('shipping-cost').textContent.replace('$', '')),
            notes: document.getElementById('notes').value
        };
        
        // Disable place order button
        const placeOrderBtn = document.getElementById('place-order-btn');
        const originalBtnText = placeOrderBtn.textContent;
        placeOrderBtn.disabled = true;
        placeOrderBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
        
        // Create order via API
        fetch('/api/orders', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Authorization': `Bearer ${localStorage.getItem('token')}`
            },
            body: JSON.stringify(formData)
        })
        .then(response => {
            if (!response.ok) {
                if (response.status === 401) {
                    window.location.href = '{{ route("login") }}?redirect={{ url()->current() }}';
                    return Promise.reject('Please log in to place an order');
                }
                return response.json().then(data => {
                    throw new Error(data.message || 'An error occurred while processing your order.');
                });
            }
            return response.json();
        })
        .then(data => {
            // Redirect to order confirmation page
            window.location.href = `/orders/${data.order.id}/confirmation`;
        })
        .catch(error => {
            if (error !== 'Please log in to place an order') {
                console.error('Error placing order:', error);
                alert(error.message || 'An error occurred while processing your order. Please try again.');
            }
            
            // Re-enable place order button
            placeOrderBtn.disabled = false;
            placeOrderBtn.textContent = originalBtnText;
        });
    }
    @endauth
</script>
@endsection 