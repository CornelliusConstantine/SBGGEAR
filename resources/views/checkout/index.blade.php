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
            <h1 class="mb-4">Checkout</h1>
        </div>
    </div>

    <!-- Checkout Steps -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="d-flex justify-content-between position-relative checkout-steps">
                <div class="checkout-step active">
                    <div class="step-number">1</div>
                    <div class="step-title">Shipping</div>
                </div>
                <div class="checkout-step">
                    <div class="step-number">2</div>
                    <div class="step-title">Payment</div>
                </div>
                <div class="checkout-step">
                    <div class="step-number">3</div>
                    <div class="step-title">Confirmation</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="mb-4">Redirecting to shipping information...</h5>
                    <div class="d-flex justify-content-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <p class="text-center mt-3">You will be redirected to the shipping information page in a moment.</p>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Order Summary -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Order Summary</h5>
                </div>
                <div class="card-body p-4">
                    @if(isset($cart) && $cart->items->count() > 0)
                        @foreach($cart->items as $item)
                            <div class="d-flex mb-3">
                                <div class="flex-shrink-0">
                                    @if($item->product->image_url)
                                        <img src="{{ $item->product->image_url }}" alt="{{ $item->product->name }}" class="img-fluid rounded" style="width: 60px; height: 60px; object-fit: cover;">
                                    @else
                                        <div class="bg-light rounded" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                                            <i class="bi bi-image text-muted"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1">{{ $item->product->name }}</h6>
                                    <p class="mb-0 text-muted">{{ $item->quantity }} x Rp{{ number_format($item->price, 0, ',', '.') }}</p>
                                </div>
                                <div class="flex-shrink-0 ms-3 text-end">
                                    <span class="fw-medium">Rp{{ number_format($item->subtotal, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        @endforeach

                        <hr>

                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal</span>
                            <span class="fw-medium">Rp{{ number_format($cart->total_amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Shipping</span>
                            <span class="text-muted">Calculated at next step</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-0">
                            <span class="fw-bold">Total</span>
                            <span class="fw-bold">Rp{{ number_format($cart->total_amount, 0, ',', '.') }}</span>
                        </div>
                    @else
                        <p class="text-center mb-0">Your cart is empty</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .checkout-steps {
        padding-bottom: 2rem;
    }
    .checkout-steps::after {
        content: '';
        position: absolute;
        top: 15px;
        left: 10%;
        right: 10%;
        height: 2px;
        background-color: #e9ecef;
        z-index: -1;
    }
    .checkout-step {
        text-align: center;
        z-index: 1;
    }
    .step-number {
        width: 30px;
        height: 30px;
        line-height: 30px;
        background-color: #e9ecef;
        color: #6c757d;
        border-radius: 50%;
        margin: 0 auto 0.5rem;
        font-weight: 500;
    }
    .checkout-step.active .step-number {
        background-color: #0d6efd;
        color: #fff;
    }
    .checkout-step.active .step-title {
        font-weight: 600;
        color: #0d6efd;
    }
    .checkout-step.completed .step-number {
        background-color: #198754;
        color: #fff;
    }
</style>

@endsection

@section('scripts')
<script>
    // Redirect to shipping page after a short delay
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() {
            window.location.href = "{{ route('checkout.shipping') }}";
        }, 1500);
    });
</script>
@endsection 