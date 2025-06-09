@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-12 mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('cart') }}">Shopping Cart</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Checkout</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <form id="checkoutForm" action="{{ route('checkout-standalone.process') }}" method="POST">
                @csrf
                <!-- Shipping Information -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">Shipping Information</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="shipping_name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control @error('shipping_name') is-invalid @enderror" 
                                       id="shipping_name" name="shipping_name" value="{{ old('shipping_name') }}" required>
                                @error('shipping_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="shipping_phone" class="form-label">Phone Number *</label>
                                <input type="text" class="form-control @error('shipping_phone') is-invalid @enderror" 
                                       id="shipping_phone" name="shipping_phone" value="{{ old('shipping_phone') }}" required>
                                @error('shipping_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="shipping_address" class="form-label">Address *</label>
                            <textarea class="form-control @error('shipping_address') is-invalid @enderror" 
                                      id="shipping_address" name="shipping_address" rows="3" required>{{ old('shipping_address') }}</textarea>
                            @error('shipping_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="province" class="form-label">Province *</label>
                                <select class="form-select @error('province') is-invalid @enderror" 
                                        id="province" name="province" required>
                                    <option value="">Select Province</option>
                                    @foreach($provinces as $province)
                                        <option value="{{ $province['province_id'] }}">{{ $province['province'] }}</option>
                                    @endforeach
                                </select>
                                @error('province')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="city" class="form-label">City *</label>
                                <select class="form-select @error('city') is-invalid @enderror" 
                                        id="city" name="city" required disabled>
                                    <option value="">Select City</option>
                                </select>
                                @error('city')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="shipping_postal_code" class="form-label">Postal Code *</label>
                            <input type="text" class="form-control @error('shipping_postal_code') is-invalid @enderror" 
                                   id="shipping_postal_code" name="shipping_postal_code" value="{{ old('shipping_postal_code') }}" required>
                            @error('shipping_postal_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
                                            id="courier" name="courier" required disabled>
                                        <option value="">Select Courier</option>
                                        <option value="jne">JNE</option>
                                        <option value="pos">POS Indonesia</option>
                                        <option value="tiki">TIKI</option>
                                    </select>
                                    @error('courier')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="weight" class="form-label">Weight</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="weight" name="weight" 
                                               value="{{ $cartWeight ?? 250 }}" readonly>
                                        <span class="input-group-text">gram</span>
                                    </div>
                                    <small class="text-muted">Weight is calculated at 250g per item</small>
                                </div>
                            </div>

                            <div id="shippingOptions" class="mt-3">
                                <!-- Shipping options will be loaded here -->
                            </div>

                            <input type="hidden" name="shipping_service" id="shipping_service" value="{{ old('shipping_service') }}">
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
                            <textarea class="form-control" id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
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
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Order Summary</h5>
                </div>
                <div class="card-body p-4">
                    <!-- Cart Items -->
                    @foreach($cart->items as $item)
                    <div class="d-flex mb-4">
                        <div class="flex-shrink-0">
                            <img src="{{ asset('storage/' . $item->product->image) }}" alt="{{ $item->product->name }}" 
                                 class="img-fluid rounded" style="width: 60px; height: 60px; object-fit: cover;">
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1">{{ $item->product->name }}</h6>
                            <p class="mb-0 text-muted">
                                {{ $item->quantity }} x Rp{{ number_format($item->price, 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="flex-shrink-0 text-end">
                            <span class="fw-bold">Rp{{ number_format($item->subtotal, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    @endforeach

                    <hr>

                    <!-- Summary -->
                    <div class="mb-2 d-flex justify-content-between">
                        <span>Subtotal</span>
                        <span class="fw-bold" id="subtotal-amount">Rp{{ number_format($cart->total_amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="mb-2 d-flex justify-content-between">
                        <span>Shipping</span>
                        <span class="fw-bold" id="shipping-cost-display">Rp0</span>
                    </div>
                    <hr>
                    <div class="mb-4 d-flex justify-content-between">
                        <span class="fw-bold">Total</span>
                        <span class="fw-bold fs-5" id="total-amount">Rp{{ number_format($cart->total_amount, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get DOM elements
    const provinceSelect = document.getElementById('province');
    const citySelect = document.getElementById('city');
    const courierSelect = document.getElementById('courier');
    const shippingOptionsDiv = document.getElementById('shippingOptions');
    const shippingServiceInput = document.getElementById('shipping_service');
    const shippingCostInput = document.getElementById('shipping_cost');
    const shippingCostDisplay = document.getElementById('shipping-cost-display');
    const totalAmountElement = document.getElementById('total-amount');
    const subtotalAmountElement = document.getElementById('subtotal-amount');
    const continueBtn = document.getElementById('continueBtn');
    const subtotal = {{ $cart->total_amount }};
    const weight = {{ $cartWeight ?? 250 }};
    
    // Province change handler
    provinceSelect.addEventListener('change', function() {
        citySelect.innerHTML = '<option value="">Select City</option>';
        citySelect.disabled = true;
        courierSelect.disabled = true;
        shippingOptionsDiv.innerHTML = '';
        updateTotals(0);
        continueBtn.disabled = true;

        if (this.value) {
            // Get CSRF token
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            // Fetch cities
            fetch('/api/shipping/cities', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({ province_id: this.value })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status && data.data) {
                    data.data.forEach(city => {
                        const option = document.createElement('option');
                        option.value = city.city_id;
                        option.textContent = `${city.type} ${city.city_name}`;
                        citySelect.appendChild(option);
                    });
                    citySelect.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error loading cities:', error);
                alert('Failed to load cities. Please try again.');
            });
        }
    });

    // City change handler
    citySelect.addEventListener('change', function() {
        courierSelect.disabled = !this.value;
        shippingOptionsDiv.innerHTML = '';
        updateTotals(0);
        continueBtn.disabled = true;
    });

    // Courier change handler
    courierSelect.addEventListener('change', function() {
        shippingOptionsDiv.innerHTML = '';
        updateTotals(0);
        continueBtn.disabled = true;

        if (this.value && citySelect.value) {
            // Get CSRF token
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            // Show loading indicator
            shippingOptionsDiv.innerHTML = '<div class="alert alert-info"><i class="bi bi-hourglass-split me-2"></i>Loading shipping options...</div>';
            
            // Fetch shipping costs
            fetch('/api/shipping/cost', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({
                    city_id: citySelect.value,
                    weight: weight,
                    courier: this.value
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status && data.data.results) {
                    const results = data.data.results;
                    
                    if (results.length > 0) {
                        let html = '<div class="list-group">';
                        
                        results.forEach((service, index) => {
                            html += `
                                <label class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between align-items-center">
                                        <div>
                                            <input type="radio" name="shipping_option" class="form-check-input me-2" 
                                                   value="${service.service}" data-cost="${service.cost}"
                                                   ${index === 0 ? 'checked' : ''}>
                                            <strong>${service.service}</strong> - ${service.description}
                                            <div class="text-muted small">Estimated delivery: ${service.etd} day(s)</div>
                                        </div>
                                        <div class="text-end">
                                            <strong>Rp${numberFormat(service.cost)}</strong>
                                        </div>
                                    </div>
                                </label>
                            `;
                        });
                        
                        html += '</div>';
                        shippingOptionsDiv.innerHTML = html;

                        // Select first option by default
                        if (results.length > 0) {
                            const firstOption = results[0];
                            shippingServiceInput.value = firstOption.service;
                            shippingCostInput.value = firstOption.cost;
                            updateTotals(firstOption.cost);
                            continueBtn.disabled = false;
                        }

                        // Add change event listeners to radio buttons
                        document.querySelectorAll('input[name="shipping_option"]').forEach(radio => {
                            radio.addEventListener('change', function() {
                                shippingServiceInput.value = this.value;
                                shippingCostInput.value = this.dataset.cost;
                                updateTotals(parseInt(this.dataset.cost));
                            });
                        });
                    } else {
                        shippingOptionsDiv.innerHTML = '<div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-2"></i>No shipping options available for the selected location.</div>';
                    }
                } else {
                    shippingOptionsDiv.innerHTML = '<div class="alert alert-danger"><i class="bi bi-x-circle me-2"></i>Failed to load shipping options. Please try again.</div>';
                }
            })
            .catch(error => {
                console.error('Error loading shipping costs:', error);
                shippingOptionsDiv.innerHTML = '<div class="alert alert-danger"><i class="bi bi-x-circle me-2"></i>Error loading shipping options. Please try again.</div>';
            });
        }
    });

    // Helper function to format numbers
    function numberFormat(number) {
        return new Intl.NumberFormat('id-ID').format(number);
    }

    // Helper function to update totals
    function updateTotals(shippingCost) {
        shippingCostDisplay.textContent = `Rp${numberFormat(shippingCost)}`;
        totalAmountElement.textContent = `Rp${numberFormat(subtotal + parseInt(shippingCost))}`;
    }
});
</script>
@endpush 