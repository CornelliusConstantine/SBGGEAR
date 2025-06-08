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
                    <li class="breadcrumb-item"><a href="{{ route('checkout.payment') }}">Payment</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Confirmation</li>
                </ol>
            </nav>
        </div>
    </div>
    
    <!-- Checkout Progress -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="position-relative checkout-steps">
                <div class="progress" style="height: 1px;">
                    <div class="progress-bar" role="progressbar" style="width: 100%;" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <div class="position-absolute w-100 top-0 d-flex justify-content-between" style="transform: translateY(-50%);">
                    <div class="step completed">
                        <div class="step-circle d-flex align-items-center justify-content-center rounded-circle bg-success text-white">
                            <i class="bi bi-check"></i>
                        </div>
                        <div class="step-label mt-2 text-center">Shipping</div>
                    </div>
                    <div class="step completed">
                        <div class="step-circle d-flex align-items-center justify-content-center rounded-circle bg-success text-white">
                            <i class="bi bi-check"></i>
                        </div>
                        <div class="step-label mt-2 text-center">Payment</div>
                    </div>
                    <div class="step active">
                        <div class="step-circle d-flex align-items-center justify-content-center rounded-circle bg-primary text-white">3</div>
                        <div class="step-label mt-2 text-center">Confirmation</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4 text-center p-4">
                <div class="card-body">
                    <div class="mb-4">
                        <div class="bg-success text-white rounded-circle mx-auto d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="bi bi-check-lg fs-1"></i>
                        </div>
                    </div>
                    <h2 class="mb-3">Thank You for Your Order!</h2>
                    <p class="mb-4 text-muted">Your order has been placed and is being processed. You will receive an email confirmation shortly.</p>
                    
                    <div class="mb-4 p-3 bg-light rounded">
                        <h5>Order Number: {{ $order->order_number }}</h5>
                        <p class="mb-0 text-muted">Please keep this order number for your reference.</p>
                    </div>
                    
                    <div class="d-flex justify-content-center gap-3">
                        <a href="{{ route('orders.show', $order->id) }}" class="btn btn-primary px-4 py-2">
                            <i class="bi bi-file-text me-2"></i>View Order Details
                        </a>
                        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary px-4 py-2">
                            <i class="bi bi-bag me-2"></i>Continue Shopping
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Order Summary</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <h6 class="mb-3">Shipping Address</h6>
                            <address class="mb-0">
                                <strong>{{ $order->shipping_name }}</strong><br>
                                {{ $order->shipping_address }}<br>
                                {{ $order->shipping_city }}, {{ $order->shipping_province }} {{ $order->shipping_postal_code }}<br>
                                <abbr title="Phone">P:</abbr> {{ $order->shipping_phone }}
                            </address>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-3">Order Details</h6>
                            <p class="mb-1"><strong>Order Number:</strong> {{ $order->order_number }}</p>
                            <p class="mb-1"><strong>Order Date:</strong> {{ $order->created_at->format('F j, Y') }}</p>
                            <p class="mb-1"><strong>Payment Method:</strong> {{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</p>
                            <p class="mb-0"><strong>Shipping Method:</strong> {{ $order->shipping_service }}</p>
                        </div>
                    </div>
                    
                    <h6 class="mb-3">Order Items</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-end">Price</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                    <tr>
                                        <td>{{ $item->product->name }}</td>
                                        <td class="text-center">{{ $item->quantity }}</td>
                                        <td class="text-end">${{ number_format($item->price, 2) }}</td>
                                        <td class="text-end">${{ number_format($item->subtotal, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end">Subtotal:</td>
                                    <td class="text-end">${{ number_format($order->subtotal, 2) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end">Shipping:</td>
                                    <td class="text-end">${{ number_format($order->shipping_cost, 2) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                    <td class="text-end"><strong>${{ number_format($order->total_amount, 2) }}</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">What's Next?</h5>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex mb-3">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; min-width: 40px;">
                            <span>1</span>
                        </div>
                        <div>
                            <h6 class="mb-1">Order Processing</h6>
                            <p class="text-muted mb-0">We're processing your order and will send you a confirmation email with the details.</p>
                        </div>
                    </div>
                    
                    <div class="d-flex mb-3">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; min-width: 40px;">
                            <span>2</span>
                        </div>
                        <div>
                            <h6 class="mb-1">Order Shipment</h6>
                            <p class="text-muted mb-0">Once your order is shipped, we'll send you a tracking number so you can follow your package.</p>
                        </div>
                    </div>
                    
                    <div class="d-flex">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; min-width: 40px;">
                            <span>3</span>
                        </div>
                        <div>
                            <h6 class="mb-1">Delivery</h6>
                            <p class="text-muted mb-0">Your order will be delivered to your shipping address. Enjoy your purchase!</p>
                        </div>
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