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
                        <li class="breadcrumb-item"><a href="{{ route('checkout.shipping') }}">Shipping</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Payment</li>
                    </ol>
                </nav>
                <a href="{{ route('checkout.shipping') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Back to Shipping
                </a>
            </div>
        </div>
    </div>
    
    <!-- Checkout Steps -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="d-flex justify-content-center">
                <div class="checkout-steps">
                    <div class="step completed">
                        <div class="step-icon">
                            <i class="bi bi-check"></i>
                        </div>
                        <div class="step-label">1. Shipping</div>
                    </div>
                    <div class="step active">
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
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Payment Method</h5>
                </div>
                <div class="card-body p-4">
                    <div class="alert alert-info mb-4">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <i class="bi bi-info-circle-fill fs-4"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="alert-heading">Secure Payment</h5>
                                <p class="mb-0">You will be redirected to our secure payment gateway to complete your payment.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <h6 class="mb-3">Available Payment Methods:</h6>
                        <div class="alert alert-info small">
                            <i class="bi bi-info-circle me-2"></i>
                            When you click "Proceed to Payment", you'll be able to choose from various payment methods provided by Midtrans, including:
                        </div>
                        
                        <div class="row row-cols-2 row-cols-md-4 g-3 mb-4">
                            <div class="col">
                                <div class="card h-100 payment-method-card">
                                    <div class="card-body text-center p-3">
                                        <i class="bi bi-credit-card fs-3 mb-2"></i>
                                        <p class="mb-0 small">Credit Card</p>
                                        <span class="badge bg-light text-dark mt-1">Visa / Mastercard</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="card h-100 payment-method-card">
                                    <div class="card-body text-center p-3">
                                        <i class="bi bi-bank fs-3 mb-2"></i>
                                        <p class="mb-0 small">Bank Transfer</p>
                                        <span class="badge bg-light text-dark mt-1">BCA / Mandiri / BNI</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="card h-100 payment-method-card">
                                    <div class="card-body text-center p-3">
                                        <i class="bi bi-wallet2 fs-3 mb-2"></i>
                                        <p class="mb-0 small">E-Wallet</p>
                                        <span class="badge bg-light text-dark mt-1">GoPay / ShopeePay / DANA</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="card h-100 payment-method-card">
                                    <div class="card-body text-center p-3">
                                        <i class="bi bi-qr-code fs-3 mb-2"></i>
                                        <p class="mb-0 small">QRIS</p>
                                        <span class="badge bg-light text-dark mt-1">Multi-bank QR</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <button id="pay-button" class="btn btn-primary py-3">
                            Proceed to Payment
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Shipping Information</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <h6 class="mb-3">Contact Information</h6>
                            <p class="mb-1"><strong>{{ $shippingInfo['name'] }}</strong></p>
                            <p class="mb-1">{{ $shippingInfo['phone'] }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-3">Shipping Address</h6>
                            <p class="mb-1">{{ $shippingInfo['address'] }}</p>
                            <p class="mb-1">{{ $shippingInfo['city'] }}, {{ $shippingInfo['postal_code'] }}</p>
                            <p class="mb-1">Indonesia</p>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-12">
                            <h6 class="mb-3">Shipping Method</h6>
                            <p class="mb-1">
                                <strong>{{ strtoupper($shippingMethod['courier']) }} {{ ucfirst($shippingMethod['service']) }}</strong>
                            </p>
                            <p class="mb-0 text-muted">
                                Estimated delivery: 
                                @if($shippingMethod['service'] == 'regular')
                                    @if($shippingMethod['courier'] == 'jne')
                                        2-3 days
                                    @elseif($shippingMethod['courier'] == 'jnt')
                                        2-4 days
                                    @elseif($shippingMethod['courier'] == 'sicepat')
                                        2-3 days
                                    @endif
                                @else
                                    1-2 days
                                @endif
                            </p>
                        </div>
                    </div>
                    
                    @if($notes)
                    <hr>
                    <div class="row">
                        <div class="col-12">
                            <h6 class="mb-3">Order Notes</h6>
                            <p class="mb-0">{{ $notes }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Order Summary</h5>
                    <p class="text-muted small mb-0">Order #{{ $orderNumber }}</p>
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
                        <span class="fw-bold">IDR {{ number_format($cart->total_amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="mb-2 d-flex justify-content-between">
                        <span>Shipping</span>
                        <span class="fw-bold">IDR {{ number_format($shippingMethod['cost'], 0, ',', '.') }}</span>
                    </div>
                    <hr>
                    <div class="mb-0 d-flex justify-content-between">
                        <span class="fw-bold">Total</span>
                        <span class="fw-bold fs-5">IDR {{ number_format($totalAmount, 0, ',', '.') }}</span>
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
    
    .payment-method-card {
        border-radius: 0.5rem;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    
    .payment-method-card:hover {
        border-color: #0069d9;
        transform: translateY(-2px);
    }
</style>

@endsection

@section('scripts')
<!-- Midtrans Client Key -->
<script src="{{ env('MIDTRANS_SNAP_URL') }}" data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const payButton = document.getElementById('pay-button');
        
        payButton.addEventListener('click', function() {
            // Disable the button to prevent multiple clicks
            payButton.disabled = true;
            payButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Processing...';
            
            // Open Snap payment page
            snap.pay('{{ $snapToken }}', {
                onSuccess: function(result) {
                    /* You may add your own implementation here */
                    console.log('Payment success:', result);
                    handlePaymentResult(result);
                },
                onPending: function(result) {
                    /* You may add your own implementation here */
                    console.log('Payment pending:', result);
                    handlePaymentResult(result);
                },
                onError: function(result) {
                    /* You may add your own implementation here */
                    console.error('Payment failed:', result);
                    alert('Payment failed. Please try again.');
                    payButton.disabled = false;
                    payButton.innerHTML = 'Proceed to Payment';
                },
                onClose: function() {
                    /* You may add your own implementation here */
                    console.log('Customer closed the payment popup without completing payment');
                    payButton.disabled = false;
                    payButton.innerHTML = 'Proceed to Payment';
                },
                language: "en"
            });
        });
        
        function handlePaymentResult(result) {
            // Create a form to submit the payment result
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('checkout.payment.process') }}';
            
            // Add CSRF token
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);
            
            // Add payment result fields
            for (const key in result) {
                if (result.hasOwnProperty(key)) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = key;
                    input.value = result[key];
                    form.appendChild(input);
                }
            }
            
            // Submit the form
            document.body.appendChild(form);
            form.submit();
        }
    });
</script>
@endsection 