@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-12 mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('orders.index') }}">My Orders</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Order Confirmation</li>
                    </ol>
                </nav>
                <a href="{{ route('products.index') }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-bag"></i> Continue Shopping
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
                    <div class="step completed">
                        <div class="step-icon">
                            <i class="bi bi-check"></i>
                        </div>
                        <div class="step-label">2. Payment</div>
                    </div>
                    <div class="step active">
                        <div class="step-icon">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        <div class="step-label">3. Review</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center mb-5">
        <div class="col-lg-8">
            <div class="text-center mb-5">
                <div class="mb-4">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
                </div>
                <h2 class="mb-3">Thank You For Your Order!</h2>
                <p class="lead mb-1">Your order has been placed successfully.</p>
                <p class="mb-0">Order Number: <strong>{{ $order->order_number }}</strong></p>
                <p class="text-muted">A confirmation email has been sent to your email address.</p>
            </div>
            
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Order Status</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <h6 class="mb-3">Order Information</h6>
                            <p class="mb-1">
                                <span class="text-muted">Order Number:</span> 
                                <strong>{{ $order->order_number }}</strong>
                            </p>
                            <p class="mb-1">
                                <span class="text-muted">Date:</span> 
                                {{ $order->created_at->format('M d, Y, h:i A') }}
                            </p>
                            <p class="mb-1">
                                <span class="text-muted">Status:</span> 
                                <span class="badge bg-{{ $order->payment_status == 'paid' ? 'success' : ($order->payment_status == 'pending' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($order->payment_status) }}
                                </span>
                            </p>
                            <p class="mb-1">
                                <span class="text-muted">Payment Method:</span> 
                                {{ ucfirst($order->payment_method) }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-3">Shipping Information</h6>
                            <p class="mb-1">
                                <strong>{{ $order->shipping_name }}</strong>
                            </p>
                            <p class="mb-1">{{ $order->shipping_phone }}</p>
                            <p class="mb-1">{{ $order->shipping_address }}</p>
                            <p class="mb-1">{{ $order->shipping_city }}, {{ $order->shipping_postal_code }}</p>
                            <p class="mb-1">{{ $order->shipping_province }}</p>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="row">
                        <div class="col-12">
                            <h6 class="mb-3">Shipping Method</h6>
                            <p class="mb-1">
                                <strong>{{ $order->shipping_method }}</strong>
                            </p>
                            <p class="mb-0 text-muted">
                                Estimated delivery: 
                                @if(strpos(strtolower($order->shipping_method), 'regular') !== false)
                                    @if(strpos(strtolower($order->shipping_method), 'jne') !== false)
                                        2-3 days
                                    @elseif(strpos(strtolower($order->shipping_method), 'jnt') !== false)
                                        2-4 days
                                    @elseif(strpos(strtolower($order->shipping_method), 'sicepat') !== false)
                                        2-3 days
                                    @else
                                        2-4 days
                                    @endif
                                @else
                                    1-2 days
                                @endif
                            </p>
                        </div>
                    </div>
                    
                    @if($order->tracking_number)
                    <div class="mt-3">
                        <p class="mb-0">
                            <span class="text-muted">Tracking Number:</span> 
                            <strong>{{ $order->tracking_number }}</strong>
                        </p>
                    </div>
                    @endif
                </div>
            </div>
            
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Order Items</h5>
                </div>
                <div class="card-body p-4">
                    @foreach($order->items as $item)
                    <div class="d-flex mb-4">
                        <div class="flex-shrink-0">
                            <img src="{{ optional($item->product)->thumbnail_url ?? asset('images/placeholder.png') }}" 
                                 alt="{{ $item->product_name }}" 
                                 class="img-fluid rounded" style="width: 70px; height: 70px; object-fit: cover;">
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1">{{ $item->product_name }}</h6>
                            <p class="mb-0 text-muted">
                                {{ $item->quantity }} x IDR {{ number_format($item->price, 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="flex-shrink-0 text-end">
                            <span class="fw-bold">IDR {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    @if(!$loop->last)
                    <hr>
                    @endif
                    @endforeach
                </div>
                <div class="card-footer bg-white py-3">
                    <div class="row">
                        <div class="col-md-6 offset-md-6">
                            <div class="mb-2 d-flex justify-content-between">
                                <span>Subtotal</span>
                                <span class="fw-bold">IDR {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                            </div>
                            <div class="mb-2 d-flex justify-content-between">
                                <span>Shipping</span>
                                <span class="fw-bold">IDR {{ number_format($order->shipping_cost, 0, ',', '.') }}</span>
                            </div>
                            <hr>
                            <div class="mb-0 d-flex justify-content-between">
                                <span class="fw-bold">Total</span>
                                <span class="fw-bold fs-5">IDR {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Customer Service</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <h6 class="mb-3">Need Help?</h6>
                            <p class="mb-1">If you have any questions about your order, please contact our customer service:</p>
                            <p class="mb-1"><i class="bi bi-envelope me-2"></i> support@sbgear.com</p>
                            <p class="mb-1"><i class="bi bi-telephone me-2"></i> +62 21 1234 5678</p>
                            <p class="mb-0"><i class="bi bi-whatsapp me-2"></i> +62 812 3456 7890</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-3">Order Tracking</h6>
                            <p class="mb-3">You can track your order status in the "My Orders" section of your account.</p>
                            <div class="d-grid">
                                <a href="{{ route('orders.index') }}" class="btn btn-outline-primary">
                                    <i class="bi bi-box me-2"></i> View My Orders
                                </a>
                            </div>
                        </div>
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
</style>
@endsection 