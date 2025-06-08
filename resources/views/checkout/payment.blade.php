@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('cart') }}">Shopping Cart</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('checkout') }}">Shipping</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Payment</li>
                </ol>
            </nav>
            <h1 class="mb-4">Payment</h1>
        </div>
    </div>
    
    <!-- Checkout Progress -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="position-relative checkout-steps">
                <div class="progress" style="height: 1px;">
                    <div class="progress-bar" role="progressbar" style="width: 75%;" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <div class="position-absolute w-100 top-0 d-flex justify-content-between" style="transform: translateY(-50%);">
                    <div class="step completed">
                        <div class="step-circle d-flex align-items-center justify-content-center rounded-circle bg-success text-white">
                            <i class="bi bi-check"></i>
                        </div>
                        <div class="step-label mt-2 text-center">Shipping</div>
                    </div>
                    <div class="step active">
                        <div class="step-circle d-flex align-items-center justify-content-center rounded-circle bg-primary text-white">2</div>
                        <div class="step-label mt-2 text-center">Payment</div>
                    </div>
                    <div class="step">
                        <div class="step-circle d-flex align-items-center justify-content-center rounded-circle bg-light text-muted border">3</div>
                        <div class="step-label mt-2 text-center text-muted">Confirmation</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Payment Method</h5>
                </div>
                <div class="card-body p-4">
                    <form id="payment-form" action="{{ route('checkout.process') }}" method="POST">
                        @csrf
                        
                        <!-- Hidden fields for shipping details -->
                        <input type="hidden" name="shipping_name" value="{{ $shippingDetails['shipping_name'] ?? '' }}">
                        <input type="hidden" name="shipping_phone" value="{{ $shippingDetails['shipping_phone'] ?? '' }}">
                        <input type="hidden" name="shipping_address" value="{{ $shippingDetails['shipping_address'] ?? '' }}">
                        <input type="hidden" name="shipping_city" value="{{ $shippingDetails['shipping_city'] ?? '' }}">
                        <input type="hidden" name="shipping_province" value="{{ $shippingDetails['shipping_province'] ?? '' }}">
                        <input type="hidden" name="shipping_postal_code" value="{{ $shippingDetails['shipping_postal_code'] ?? '' }}">
                        <input type="hidden" name="shipping_cost" value="{{ $shippingDetails['shipping_cost'] ?? 0 }}">
                        <input type="hidden" name="shipping_service" value="{{ $shippingDetails['shipping_service'] ?? '' }}">
                        <input type="hidden" name="notes" value="{{ $shippingDetails['notes'] ?? '' }}">
                        
                        <div class="mb-4">
                            <div class="form-check mb-3 py-3 border-bottom">
                                <input class="form-check-input" type="radio" name="payment_method" id="payment_midtrans" value="midtrans" checked>
                                <label class="form-check-label d-flex justify-content-between align-items-center w-100" for="payment_midtrans">
                                    <div>
                                        <span class="d-block fw-medium">Midtrans Payment Gateway</span>
                                        <small class="text-muted">Credit Card, Bank Transfer, E-Wallet, and more</small>
                                    </div>
                                    <img src="https://midtrans.com/assets/images/logo-midtrans-color.png" alt="Midtrans" height="30">
                                </label>
                            </div>
                            
                            <div class="form-check mb-3 py-3 border-bottom">
                                <input class="form-check-input" type="radio" name="payment_method" id="payment_bank" value="bank_transfer">
                                <label class="form-check-label d-flex justify-content-between align-items-center w-100" for="payment_bank">
                                    <div>
                                        <span class="d-block fw-medium">Bank Transfer</span>
                                        <small class="text-muted">Manual transfer to our bank account</small>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <img src="https://upload.wikimedia.org/wikipedia/commons/a/ad/Bank_Mandiri_logo.svg" alt="Mandiri" height="30">
                                        <img src="https://upload.wikimedia.org/wikipedia/commons/9/97/Logo_BCA.png" alt="BCA" height="30">
                                    </div>
                                </label>
                            </div>
                            
                            <div class="form-check py-3">
                                <input class="form-check-input" type="radio" name="payment_method" id="payment_cod" value="cash_on_delivery">
                                <label class="form-check-label d-flex justify-content-between align-items-center w-100" for="payment_cod">
                                    <div>
                                        <span class="d-block fw-medium">Cash on Delivery</span>
                                        <small class="text-muted">Pay when you receive the package</small>
                                    </div>
                                    <i class="bi bi-cash-coin fs-4"></i>
                                </label>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary py-2 px-4">
                                <i class="bi bi-credit-card me-2"></i>Place Order
                            </button>
                            <a href="{{ route('checkout') }}" class="btn btn-outline-secondary py-2 px-4 ms-2">
                                <i class="bi bi-arrow-left me-2"></i>Back to Shipping
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Secure Payment</h5>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <i class="bi bi-shield-lock fs-4 text-success me-3"></i>
                        <div>
                            <h6 class="mb-1">Secure Payment</h6>
                            <p class="mb-0 text-muted small">Your payment information is securely processed</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mb-3">
                        <i class="bi bi-credit-card fs-4 text-success me-3"></i>
                        <div>
                            <h6 class="mb-1">Multiple Payment Options</h6>
                            <p class="mb-0 text-muted small">Choose from various payment methods</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="bi bi-box-seam fs-4 text-success me-3"></i>
                        <div>
                            <h6 class="mb-1">Order Tracking</h6>
                            <p class="mb-0 text-muted small">Track your order status after purchase</p>
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
                        @if($cart->items->count() > 0)
                            @foreach($cart->items as $item)
                                <div class="d-flex align-items-center mb-3 cart-summary-item">
                                    @php
                                        $image = '/images/no-image.jpg';
                                        if ($item->product->images) {
                                            try {
                                                $images = json_decode($item->product->images, true);
                                                $image = $images[0] ?? '/images/no-image.jpg';
                                            } catch (\Exception $e) {}
                                        }
                                    @endphp
                                    <img src="{{ $image }}" alt="{{ $item->product->name }}" class="img-thumbnail me-2" style="width: 50px; height: 50px; object-fit: cover;">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0 fs-7 item-name text-truncate">{{ $item->product->name }}</h6>
                                        <small class="text-muted">
                                            <span class="item-quantity">{{ $item->quantity }}</span> × 
                                            <span class="item-price">${{ number_format($item->price, 2) }}</span>
                                        </small>
                                    </div>
                                    <div class="ms-auto item-subtotal fw-medium">${{ number_format($item->subtotal, 2) }}</div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center py-4">
                                <p class="mb-3">Your cart is empty.</p>
                                <a href="{{ route('products.index') }}" class="btn btn-primary btn-sm px-4">Shop Now</a>
                            </div>
                        @endif
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span id="subtotal-amount">${{ number_format($cart->total_amount, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Shipping:</span>
                        <span id="shipping-cost-display">${{ number_format($shippingDetails['shipping_cost'] ?? 0, 2) }}</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-4">
                        <span class="fw-bold">Total:</span>
                        <span id="total-amount" class="fw-bold">${{ number_format(($cart->total_amount + ($shippingDetails['shipping_cost'] ?? 0)), 2) }}</span>
                    </div>
                    
                    <div class="d-flex gap-3 justify-content-center mb-3">
                        <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/visa/visa-original.svg" alt="Visa" height="30">
                        <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/mastercard/mastercard-original.svg" alt="Mastercard" height="30">
                        <img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/paypal/paypal-original.svg" alt="PayPal" height="30">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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
.checkout-steps .step.completed .step-circle {
    background-color: var(--bs-success) !important;
    color: white !important;
    border: none !important;
}
.checkout-steps .step.completed .step-label {
    color: var(--bs-success) !important;
    font-weight: 600;
}
</style>
@endsection 