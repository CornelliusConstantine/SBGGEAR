@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-12 mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('cart') }}">Cart</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Shipping</li>
                    </ol>
                </nav>
                <a href="{{ route('cart') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Back to Cart
                </a>
            </div>
        </div>
    </div>
    
    <!-- Checkout Steps -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="d-flex justify-content-center">
                <div class="checkout-steps">
                    <div class="step active">
                        <div class="step-icon">
                            <i class="bi bi-truck"></i>
                        </div>
                        <div class="step-label">1. Shipping</div>
                    </div>
                    <div class="step">
                        <div class="step-icon">
                            <i class="bi bi-credit-card"></i>
                        </div>
                        <div class="step-label">2. Payment</div>
                    </div>
                    <div class="step">
                        <div class="step-icon">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        <div class="step-label">3. Review</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <form id="shippingForm" action="{{ route('checkout.shipping.process') }}" method="POST">
                @csrf
                <!-- Shipping Information -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">Shipping Information</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="shipping_name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control @error('shipping_name') is-invalid @enderror" 
                                       id="shipping_name" name="shipping_name" value="{{ old('shipping_name') }}" 
                                       placeholder="Enter at least 2 words" required minlength="3">
                                <div class="form-text">Please enter your full name (first and last name)</div>
                                @error('shipping_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="shipping_phone" class="form-label">Phone Number *</label>
                            <input type="text" class="form-control @error('shipping_phone') is-invalid @enderror" 
                                   id="shipping_phone" name="shipping_phone" value="{{ old('shipping_phone') }}" 
                                   placeholder="Enter your phone number" required minlength="10">
                            @error('shipping_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="shipping_address" class="form-label">Address *</label>
                            <textarea class="form-control @error('shipping_address') is-invalid @enderror" 
                                      id="shipping_address" name="shipping_address" rows="3" 
                                      placeholder="Enter your complete address (min. 5 characters)" 
                                      required minlength="5">{{ old('shipping_address') }}</textarea>
                            @error('shipping_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="shipping_city" class="form-label">City *</label>
                                <select class="form-select @error('shipping_city') is-invalid @enderror" 
                                        id="shipping_city" name="shipping_city" required>
                                    <option value="">Select City</option>
                                    @foreach($cities as $key => $city)
                                        <option value="{{ $key }}" {{ old('shipping_city') == $key ? 'selected' : '' }}>{{ $city }}</option>
                                    @endforeach
                                </select>
                                @error('shipping_city')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="shipping_postal_code" class="form-label">Postal Code *</label>
                                <input type="text" class="form-control @error('shipping_postal_code') is-invalid @enderror" 
                                       id="shipping_postal_code" name="shipping_postal_code" 
                                       value="{{ old('shipping_postal_code') }}" 
                                       placeholder="5-digit postal code" required pattern="[0-9]{5}">
                                @error('shipping_postal_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Shipping Method -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">Shipping Method</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-4">
                            <div class="row mb-3">
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <label for="courier" class="form-label">Courier *</label>
                                    <select class="form-select @error('courier') is-invalid @enderror" 
                                            id="courier" name="courier" required>
                                        <option value="">Select Courier</option>
                                        <option value="jne" {{ old('courier') == 'jne' ? 'selected' : '' }}>JNE</option>
                                        <option value="jnt" {{ old('courier') == 'jnt' ? 'selected' : '' }}>J&T Express</option>
                                        <option value="sicepat" {{ old('courier') == 'sicepat' ? 'selected' : '' }}>SiCepat</option>
                                    </select>
                                    @error('courier')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="weight" class="form-label">Weight</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="weight" name="weight" 
                                               value="{{ $totalWeight }}" readonly>
                                        <span class="input-group-text">gram</span>
                                    </div>
                                    <div class="form-text">Total weight of all items in your cart</div>
                                </div>
                            </div>

                            <div id="shippingOptions" class="mt-3">
                                <div class="d-flex justify-content-center my-3" id="loadingShipping" style="display: none !important;">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <span class="ms-2">Calculating shipping options...</span>
                                </div>
                                <div id="shippingServices"></div>
                            </div>

                            <input type="hidden" name="service" id="service" value="{{ old('service') }}">
                            <input type="hidden" name="shipping_cost" id="shipping_cost" value="{{ old('shipping_cost') }}">
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">Additional Notes</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label for="notes" class="form-label">Order Notes (Optional)</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" 
                                      placeholder="Special instructions for delivery">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="d-grid mb-4">
                    <button type="submit" class="btn btn-primary py-3" id="continueBtn" disabled>
                        Continue to Payment
                    </button>
                </div>
            </form>
        </div>

        <!-- Order Summary -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Order Summary</h5>
                </div>
                <div class="card-body p-4">
                    <!-- Cart Items -->
                    @foreach($cart->items as $item)
                    <div class="d-flex mb-4">
                        <div class="flex-shrink-0">
                            <img src="{{ $item->product->thumbnail_url }}" alt="{{ $item->product->name }}" 
                                 class="img-fluid rounded" style="width: 60px; height: 60px; object-fit: cover;">
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1">{{ $item->product->name }}</h6>
                            <p class="mb-0 text-muted">
                                {{ $item->quantity }} x IDR {{ number_format($item->price, 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="flex-shrink-0 text-end">
                            <span class="fw-bold">IDR {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    @endforeach

                    <hr>

                    <!-- Summary -->
                    <div class="mb-2 d-flex justify-content-between">
                        <span>Subtotal</span>
                        <span class="fw-bold" id="subtotal-amount">IDR {{ number_format($cart->total_amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="mb-2 d-flex justify-content-between">
                        <span>Shipping</span>
                        <span class="fw-bold" id="shipping-cost-display">IDR 0</span>
                    </div>
                    <hr>
                    <div class="mb-0 d-flex justify-content-between">
                        <span class="fw-bold">Total</span>
                        <span class="fw-bold fs-5" id="total-amount">IDR {{ number_format($cart->total_amount, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .checkout-steps {
        display: flex;
        justify-content: space-between;
        width: 100%;
        max-width: 600px;
        position: relative;
    }
    
    .checkout-steps::before {
        content: '';
        position: absolute;
        top: 24px;
        left: 60px;
        right: 60px;
        height: 2px;
        background-color: #dee2e6;
        z-index: 0;
    }
    
    .step {
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        z-index: 1;
        flex: 1;
    }
    
    .step-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background-color: #fff;
        border: 2px solid #dee2e6;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        margin-bottom: 0.5rem;
        color: #6c757d;
    }
    
    .step.active .step-icon {
        background-color: #0069d9;
        border-color: #0069d9;
        color: #fff;
    }
    
    .step.completed .step-icon {
        background-color: #28a745;
        border-color: #28a745;
        color: #fff;
    }
    
    .step-label {
        font-weight: 600;
        font-size: 0.875rem;
    }
    
    .shipping-option {
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        padding: 1rem;
        margin-bottom: 1rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .shipping-option:hover {
        border-color: #0069d9;
    }
    
    .shipping-option.selected {
        border-color: #0069d9;
        background-color: rgba(0, 105, 217, 0.05);
    }
</style>

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const courierSelect = document.getElementById('courier');
        const citySelect = document.getElementById('shipping_city');
        const weightInput = document.getElementById('weight');
        const shippingServicesDiv = document.getElementById('shippingServices');
        const loadingDiv = document.getElementById('loadingShipping');
        const serviceInput = document.getElementById('service');
        const shippingCostInput = document.getElementById('shipping_cost');
        const shippingCostDisplay = document.getElementById('shipping-cost-display');
        const totalAmountDisplay = document.getElementById('total-amount');
        const subtotalAmount = parseFloat('{{ $cart->total_amount }}');
        const continueBtn = document.getElementById('continueBtn');
        
        // Format currency function
        function formatCurrency(amount) {
            return 'IDR ' + amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }
        
        // Calculate shipping when courier or city changes
        function calculateShipping() {
            const courier = courierSelect.value;
            const city = citySelect.value;
            const weight = weightInput.value;
            
            if (!courier || !city) {
                return;
            }
            
            // Show loading
            loadingDiv.style.display = 'flex';
            shippingServicesDiv.innerHTML = '';
            serviceInput.value = '';
            shippingCostInput.value = '';
            continueBtn.disabled = true;
            
            // Calculate shipping via AJAX
            fetch('{{ route('checkout.calculate-shipping') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    courier: courier,
                    city: city,
                    weight: weight
                })
            })
            .then(response => response.json())
            .then(data => {
                loadingDiv.style.display = 'none';
                
                if (data.services && data.services.length > 0) {
                    let servicesHtml = '';
                    
                    data.services.forEach(service => {
                        servicesHtml += `
                            <div class="shipping-option" data-service="${service.service}" data-cost="${service.cost}">
                                <div class="form-check">
                                    <input class="form-check-input shipping-radio" type="radio" name="shipping_service_radio" 
                                           id="service_${service.service}" value="${service.service}">
                                    <label class="form-check-label" for="service_${service.service}">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>${data.courier.toUpperCase()} - ${service.description}</strong>
                                                <div class="text-muted">Estimated delivery: ${service.estimate}</div>
                                            </div>
                                            <div class="fw-bold">
                                                ${formatCurrency(service.cost)}
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        `;
                    });
                    
                    shippingServicesDiv.innerHTML = servicesHtml;
                    
                    // Add event listeners to shipping options
                    document.querySelectorAll('.shipping-option').forEach(option => {
                        option.addEventListener('click', function() {
                            const service = this.dataset.service;
                            const cost = parseFloat(this.dataset.cost);
                            
                            // Update hidden inputs
                            serviceInput.value = service;
                            shippingCostInput.value = cost;
                            
                            // Update display
                            shippingCostDisplay.textContent = formatCurrency(cost);
                            totalAmountDisplay.textContent = formatCurrency(subtotalAmount + cost);
                            
                            // Update UI
                            document.querySelectorAll('.shipping-option').forEach(opt => {
                                opt.classList.remove('selected');
                            });
                            this.classList.add('selected');
                            
                            // Check the radio button
                            const radio = this.querySelector('.shipping-radio');
                            radio.checked = true;
                            
                            // Enable continue button
                            continueBtn.disabled = false;
                        });
                    });
                } else {
                    shippingServicesDiv.innerHTML = '<div class="alert alert-warning">No shipping services available for this destination.</div>';
                }
            })
            .catch(error => {
                loadingDiv.style.display = 'none';
                shippingServicesDiv.innerHTML = '<div class="alert alert-danger">Error calculating shipping. Please try again.</div>';
                console.error('Error:', error);
            });
        }
        
        // Add event listeners
        courierSelect.addEventListener('change', calculateShipping);
        citySelect.addEventListener('change', calculateShipping);
        
        // Form validation
        document.getElementById('shippingForm').addEventListener('submit', function(e) {
            if (!serviceInput.value || !shippingCostInput.value) {
                e.preventDefault();
                alert('Please select a shipping method');
            }
        });
        
        // Initialize if values are pre-selected (e.g., from validation errors)
        if (courierSelect.value && citySelect.value) {
            calculateShipping();
        }
    });
</script>
@endsection