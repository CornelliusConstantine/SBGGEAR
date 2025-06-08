@extends('layouts.app')

@section('styles')
<style>
.checkout-steps .step-circle {
    width: 30px;
    height: 30px;
    font-size: 14px;
}
.checkout-steps .step.active .step-circle {
    background-color: var(--bs-primary) !important;
    color: white !important;
    border: none !important;
}
.checkout-steps .step.active .step-label {
    color: var(--bs-primary) !important;
    font-weight: 600;
}
.shipping-options .list-group-item:hover {
    background-color: #f8f9fa;
    cursor: pointer;
}
.shipping-options .list-group-item.active {
    background-color: var(--bs-primary);
    border-color: var(--bs-primary);
}
</style>
@endsection

@section('content')
<div class="container py-5" ng-app="shippingApp" ng-controller="ShippingController" ng-init="init({{ $cart->total_amount ?? 0 }}, {{ $cartWeight ?? 250 }})">
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
    
    <!-- Checkout Progress -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="position-relative checkout-steps">
                <div class="progress" style="height: 1px;">
                    <div class="progress-bar" role="progressbar" style="width: 50%;" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <div class="position-absolute w-100 top-0 d-flex justify-content-between" style="transform: translateY(-50%);">
                    <div class="step active">
                        <div class="step-circle d-flex align-items-center justify-content-center rounded-circle bg-primary text-white">1</div>
                        <div class="step-label mt-2 text-center">Shipping</div>
                    </div>
                    <div class="step">
                        <div class="step-circle d-flex align-items-center justify-content-center rounded-circle bg-light text-muted border">2</div>
                        <div class="step-label mt-2 text-center text-muted">Payment</div>
                    </div>
                    <div class="step">
                        <div class="step-circle d-flex align-items-center justify-content-center rounded-circle bg-light text-muted border">3</div>
                        <div class="step-label mt-2 text-center text-muted">Confirmation</div>
                    </div>
                </div>
            </div>
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
                                <label for="province" class="form-label">Province</label>
                                <select class="form-select" id="province" name="province" ng-model="selectedProvince" required>
                                    <option value="">Select Province</option>
                                    <option ng-repeat="province in provinces" value="@{{ province.province_id }}">@{{ province.province }}</option>
                                </select>
                                <div class="invalid-feedback" id="province_error"></div>
                                <div class="text-danger small" ng-if="errors.provinces">@{{ errors.provinces }}</div>
                                <div class="text-muted small" ng-if="loading.provinces">Loading provinces...</div>
                            </div>
                            <div class="col-md-4 mb-3 mb-md-0">
                                <label for="city" class="form-label">City</label>
                                <select class="form-select" id="city" name="city" ng-model="selectedCity" required ng-disabled="!selectedProvince || loading.cities">
                                    <option value="">Select City</option>
                                    <option ng-repeat="city in cities" value="@{{ city.city_id }}">@{{ city.type }} @{{ city.city_name }}</option>
                                </select>
                                <div class="invalid-feedback" id="city_error"></div>
                                <div class="text-danger small" ng-if="errors.cities">@{{ errors.cities }}</div>
                                <div class="text-muted small" ng-if="loading.cities">Loading cities...</div>
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
                        
                        <!-- Hidden input for shipping cost -->
                        <input type="hidden" id="shipping-cost" name="shipping_cost" value="0">
                        <input type="hidden" id="shipping-service" name="shipping_service" value="">
                    </form>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Shipping Method</h5>
                </div>
                <div class="card-body p-4">
                    <div class="mb-4">
                        <div class="row mb-3">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label for="courier" class="form-label">Courier</label>
                                <select class="form-select" id="courier" name="courier" ng-model="selectedCourier" ng-disabled="!selectedCity">
                                    <option value="">Select Courier</option>
                                    <option ng-repeat="courier in couriers" value="@{{ courier.id }}">@{{ courier.name }}</option>
                                </select>
                                <div class="text-danger small" ng-if="errors.shipping">@{{ errors.shipping }}</div>
                            </div>
                            <div class="col-md-6">
                                <label for="weight" class="form-label">Weight</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="weight" name="weight" ng-model="weight" readonly>
                                    <span class="input-group-text">gram</span>
                                </div>
                                <small class="text-muted">Weight is calculated at 250g per item</small>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mb-3" ng-if="selectedCourier && selectedCity">
                            <button type="button" class="btn btn-outline-primary" id="calculate-shipping" ng-click="calculateShipping()" ng-disabled="loading.shipping">
                                <span ng-if="!loading.shipping"><i class="bi bi-calculator me-2"></i>Calculate Shipping Fee</span>
                                <span ng-if="loading.shipping"><i class="spinner-border spinner-border-sm me-2"></i>Calculating...</span>
                            </button>
                            <span class="text-muted small">Shipping cost will be added to your total</span>
                        </div>
                    </div>
                    
                    <div class="shipping-options" ng-if="shippingOptions.length > 0">
                        <h6 class="mb-3">Available Shipping Options:</h6>
                        <div class="list-group">
                            <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center p-3" 
                                 ng-repeat="option in shippingOptions"
                                 ng-class="{'active': selectedShipping && selectedShipping.service === option.service}"
                                 ng-click="selectShippingOption(option, option.cost[0])">
                                <div>
                                    <h6 class="mb-1">@{{ option.service }}</h6>
                                    <p class="mb-1 small">@{{ option.description }}</p>
                                    <small>Estimated delivery: @{{ option.cost[0].etd }} day(s)</small>
                                </div>
                                <div class="fw-bold">@{{ formatCurrency(option.cost[0].value) }}</div>
                            </div>
                        </div>
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
                        <span id="subtotal-amount">@{{ formatCurrency(subtotal) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Shipping:</span>
                        <span id="shipping-cost-display">@{{ formatCurrency(shippingCost) }}</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-4">
                        <span class="fw-bold">Total:</span>
                        <span id="total-amount" class="fw-bold">@{{ formatCurrency(total) }}</span>
                    </div>
                    
                    <button type="button" id="continue-btn" class="btn btn-primary w-100 py-3" disabled title="Please select a shipping option first">
                        <i class="bi bi-lock me-2"></i>Continue to Payment
                    </button>
                    
                    <p class="text-muted small text-center mt-3 mb-0">
                        By continuing, you agree to our 
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
                <span class="item-quantity quantity"></span> × <span class="item-price"></span>
            </small>
        </div>
        <div class="ms-auto item-subtotal fw-medium"></div>
    </div>
</template>

@endsection

@section('scripts')
<!-- AngularJS -->
<script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.8.2/angular.min.js"></script>
<!-- Shipping Script -->
<script src="{{ asset('js/shipping.js') }}"></script>

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
            document.getElementById('shipping_postal_code').value = user.postal_code || '';
        })
        .catch(error => {
            if (error !== 'Unauthorized') {
                console.error('Error loading user data:', error);
            }
        });
        
        // Handle continue button
        document.getElementById('continue-btn').addEventListener('click', function() {
            // Validate form
            const form = document.getElementById('checkout-form');
            if (!form.checkValidity()) {
                form.classList.add('was-validated');
                return;
            }
            
            // Get shipping cost from Angular scope
            const shippingCost = document.getElementById('shipping-cost').value;
            const shippingService = document.getElementById('shipping-service').value;
            if (!shippingCost || shippingCost === '0' || !shippingService) {
                alert('Please select a shipping option before continuing');
                return;
            }
            
            // Disable button and show loading
            const continueBtn = document.getElementById('continue-btn');
            const originalBtnText = continueBtn.innerHTML;
            continueBtn.disabled = true;
            continueBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
            
            // Save shipping details to session
            const shippingDetails = {
                shipping_name: document.getElementById('shipping_name').value,
                shipping_phone: document.getElementById('shipping_phone').value,
                shipping_address: document.getElementById('shipping_address').value,
                shipping_city: document.getElementById('city').options[document.getElementById('city').selectedIndex].text,
                shipping_province: document.getElementById('province').options[document.getElementById('province').selectedIndex].text,
                shipping_postal_code: document.getElementById('shipping_postal_code').value,
                shipping_cost: shippingCost,
                shipping_service: shippingService,
                notes: document.getElementById('notes').value
            };
            
            fetch('/api/shipping/save-details', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Authorization': `Bearer ${localStorage.getItem('token')}`
                },
                body: JSON.stringify(shippingDetails)
            })
            .then(response => {
                if (!response.ok) {
                    if (response.status === 401) {
                        window.location.href = '{{ route("login") }}?redirect={{ url()->current() }}';
                        return Promise.reject('Unauthorized');
                    }
                    return response.json().then(data => Promise.reject(data.message || 'An error occurred'));
                }
                return response.json();
            })
            .then(data => {
                // Proceed to payment page
                window.location.href = '{{ route("checkout.payment") }}';
            })
            .catch(error => {
                if (error !== 'Unauthorized') {
                    console.error('Error saving shipping details:', error);
                    alert('An error occurred while saving shipping details. Please try again.');
                }
                
                // Re-enable button
                continueBtn.disabled = false;
                continueBtn.innerHTML = originalBtnText;
            });
        });
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
        const continueBtn = document.getElementById('continue-btn');
        
        // Clear existing items
        container.innerHTML = '';
        
        if (!cart.items || cart.items.length === 0) {
            emptyMessage.classList.remove('d-none');
            container.appendChild(emptyMessage);
            continueBtn.disabled = true;
            return;
        }
        
        // Add each item
        cart.items.forEach(item => {
            const itemDiv = document.createElement('div');
            itemDiv.className = 'd-flex align-items-center mb-3 cart-summary-item';
            
            // Set image with fallback
            let imageUrl = '/images/no-image.jpg';
            if (item.product.image_url) {
                imageUrl = item.product.image_url;
            } else if (item.product.images) {
                try {
                    const images = typeof item.product.images === 'string' ? 
                        JSON.parse(item.product.images) : item.product.images;
                    imageUrl = (images.main ? `/storage/products/original/${images.main}` : 
                        (Array.isArray(images) && images.length > 0 ? images[0] : '/images/no-image.jpg'));
                } catch (e) {
                    console.error('Error parsing images:', e);
                }
            }
            
            itemDiv.innerHTML = `
                <img src="${imageUrl}" alt="${item.product.name}" class="img-thumbnail me-2" style="width: 50px; height: 50px; object-fit: cover;">
                <div class="flex-grow-1">
                    <h6 class="mb-0 fs-7 item-name text-truncate">${item.product.name}</h6>
                    <small class="text-muted">
                        <span class="item-quantity quantity">${item.quantity}</span> × 
                        <span class="item-price">Rp${parseFloat(item.price).toLocaleString('id-ID')}</span>
                    </small>
                </div>
                <div class="ms-auto item-subtotal fw-medium">Rp${parseFloat(item.subtotal).toLocaleString('id-ID')}</div>
            `;
            
            container.appendChild(itemDiv);
        });
    }
    @endauth
</script>
@endsection 