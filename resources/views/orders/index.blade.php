@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="mb-4">My Orders</h1>
        </div>
    </div>

    @if($orders->isEmpty())
        <div class="alert alert-info p-4 text-center">
            <div class="mb-3">
                <i class="bi bi-box fs-1"></i>
            </div>
            <h4>You haven't placed any orders yet.</h4>
            <p class="mb-3">Browse our products and start shopping!</p>
            <a href="{{ route('products.index') }}" class="btn btn-primary">Shop Now</a>
        </div>
    @else
        <div class="row">
            <div class="col-12">
                @foreach($orders as $order)
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0">Order #{{ $order->order_number }}</h5>
                                <small class="text-muted">Placed on {{ $order->created_at->format('M d, Y') }}</small>
                            </div>
                            <div>
                                @if($order->status == 'pending')
                                    <span class="badge bg-warning text-dark">Pending</span>
                                @elseif($order->status == 'processing')
                                    <span class="badge bg-info">Processing</span>
                                @elseif($order->status == 'shipped')
                                    <span class="badge bg-primary">Shipped</span>
                                @elseif($order->status == 'delivered')
                                    <span class="badge bg-success">Delivered</span>
                                @elseif($order->status == 'cancelled')
                                    <span class="badge bg-danger">Cancelled</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($order->status) }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Product</th>
                                            <th>Price</th>
                                            <th>Quantity</th>
                                            <th class="text-end">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($order->items as $item)
                                            <tr>
                                                <td data-label="Product">
                                                    <div class="d-flex align-items-center">
                                                        @if($item->product && isset($item->product->images['main']))
                                                            <img src="{{ asset('storage/products/thumbnails/' . $item->product->images['main']) }}" 
                                                                alt="{{ $item->product_name }}" class="img-thumbnail me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                                        @endif
                                                        <div>
                                                            <h6 class="mb-0">{{ $item->product_name }}</h6>
                                                            @if($item->product)
                                                                <a href="{{ route('products.show', $item->product->slug) }}" class="text-muted small">View Product</a>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>
                                                <td data-label="Price">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                                <td data-label="Quantity">{{ $item->quantity }}</td>
                                                <td data-label="Subtotal" class="text-end">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                            <td class="text-end"><strong>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</strong></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer bg-white text-end">
                            <a href="{{ route('orders.show', $order->id) }}" class="btn btn-primary">
                                <i class="bi bi-eye"></i> Order Details
                            </a>
                        </div>
                    </div>
                @endforeach
                
                <div class="d-flex justify-content-center mt-4">
                    {{ $orders->links() }}
                </div>
            </div>
        </div>
    @endif
</div>

<style>
    .table th, .table td {
        padding: 1rem;
        vertical-align: middle;
    }
    
    .badge {
        font-size: 0.85rem;
        padding: 0.5em 0.75em;
    }
    
    @media (max-width: 767.98px) {
        .table thead {
            display: none;
        }
        
        .table tbody tr {
            display: block;
            border-bottom: 1px solid #dee2e6;
            padding: 0.75rem 0;
        }
        
        .table tbody td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: none;
            padding: 0.5rem 1rem;
        }
        
        .table tbody td:before {
            content: attr(data-label);
            font-weight: bold;
            margin-right: 1rem;
        }
        
        .table tfoot td {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 1rem;
        }
        
        .table td.text-end {
            text-align: right !important;
        }
    }
</style>
@endsection 