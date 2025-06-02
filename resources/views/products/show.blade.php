@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Products</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $product->name }}</li>
                </ol>
            </nav>
            
            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif
            
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6 mb-4 mb-lg-0">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    @if($product->images)
                        @php
                            try {
                                $images = json_decode($product->images, true);
                                $mainImage = $images[0] ?? '/images/no-image.jpg';
                            } catch (\Exception $e) {
                                $mainImage = '/images/no-image.jpg';
                                $images = [];
                            }
                        @endphp
                        <img src="{{ $mainImage }}" alt="{{ $product->name }}" class="img-fluid w-100" id="main-product-image" style="object-fit: cover; height: 400px;">
                        
                        @if(count($images) > 1)
                            <div class="d-flex mt-3">
                                @foreach($images as $index => $image)
                                    <div class="product-thumbnail me-2 {{ $index === 0 ? 'active' : '' }}" data-image="{{ $image }}">
                                        <img src="{{ $image }}" alt="{{ $product->name }}" class="img-thumbnail" style="width: 80px; height: 80px; object-fit: cover;">
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @else
                        <img src="/images/no-image.jpg" alt="{{ $product->name }}" class="img-fluid w-100" style="object-fit: cover; height: 400px;">
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <h1 class="mb-3">{{ $product->name }}</h1>
            <p class="text-muted mb-2">SKU: {{ $product->sku }}</p>
            <h3 class="text-primary mb-4">${{ number_format($product->price, 2) }}</h3>
            
            <div class="mb-4">
                <p class="mb-2"><strong>Availability:</strong> 
                    @if($product->stock > 0)
                        <span class="text-success">In Stock ({{ $product->stock }} available)</span>
                    @else
                        <span class="text-danger">Out of Stock</span>
                    @endif
                </p>
                <p class="mb-2"><strong>Weight:</strong> {{ $product->weight }}g</p>
                <p class="mb-2"><strong>Category:</strong> {{ $product->category->name ?? 'Uncategorized' }}</p>
            </div>
            
            <div class="mb-4">
                <h5>Description</h5>
                <p>{{ $product->description }}</p>
            </div>
            
            @if($product->specifications)
                <div class="mb-4">
                    <h5>Specifications</h5>
                    <ul class="list-unstyled">
                        @foreach(json_decode($product->specifications, true) as $key => $value)
                            <li class="mb-1"><strong>{{ $key }}:</strong> {{ $value }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <div class="mb-4">
                @auth
                <form id="add-to-cart-form" class="d-flex align-items-center">
                    <div class="input-group me-3" style="max-width: 130px;">
                        <button class="btn btn-outline-secondary" type="button" id="quantity-decrease">
                            <i class="bi bi-dash"></i>
                        </button>
                        <input type="number" class="form-control text-center" id="quantity" name="quantity" value="1" min="1" max="{{ $product->stock }}">
                        <button class="btn btn-outline-secondary" type="button" id="quantity-increase">
                            <i class="bi bi-plus"></i>
                        </button>
                    </div>
                    
                    <button type="submit" class="btn btn-primary py-2 px-4" {{ $product->stock <= 0 ? 'disabled' : '' }}>
                        <i class="bi bi-cart-plus me-2"></i>Add to Cart
                    </button>
                </form>
                @else
                <div class="alert alert-info">
                    <p class="mb-2">Please login to add this item to your cart</p>
                    <a href="{{ route('login', ['redirect' => url()->current()]) }}" class="btn btn-primary">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Login to Continue
                    </a>
                </div>
                @endauth
            </div>
        </div>
    </div>
    
    @if($relatedProducts->count() > 0)
        <div class="row mt-5">
            <div class="col-12">
                <h3 class="mb-4">Related Products</h3>
                <div class="row">
                    @foreach($relatedProducts as $relatedProduct)
                        <div class="col-md-3 mb-4">
                            <div class="card border-0 shadow-sm h-100">
                                <a href="{{ route('products.show', $relatedProduct) }}" class="text-decoration-none">
                                    @if($relatedProduct->images)
                                        @php
                                            try {
                                                $images = json_decode($relatedProduct->images, true);
                                                $image = $images[0] ?? '/images/no-image.jpg';
                                            } catch (\Exception $e) {
                                                $image = '/images/no-image.jpg';
                                            }
                                        @endphp
                                        <img src="{{ $image }}" alt="{{ $relatedProduct->name }}" class="card-img-top" style="height: 200px; object-fit: cover;">
                                    @else
                                        <img src="/images/no-image.jpg" alt="{{ $relatedProduct->name }}" class="card-img-top" style="height: 200px; object-fit: cover;">
                                    @endif
                                </a>
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <a href="{{ route('products.show', $relatedProduct) }}" class="text-decoration-none text-dark">
                                            {{ $relatedProduct->name }}
                                        </a>
                                    </h5>
                                    <p class="card-text text-primary fw-bold">${{ number_format($relatedProduct->price, 2) }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Product image gallery
        const thumbnails = document.querySelectorAll('.product-thumbnail');
        const mainImage = document.getElementById('main-product-image');
        
        thumbnails.forEach(thumbnail => {
            thumbnail.addEventListener('click', function() {
                // Remove active class from all thumbnails
                thumbnails.forEach(t => t.classList.remove('active'));
                
                // Add active class to clicked thumbnail
                this.classList.add('active');
                
                // Update main image
                mainImage.src = this.dataset.image;
            });
        });
        
        // Quantity controls
        const quantityInput = document.getElementById('quantity');
        const maxQuantity = {{ $product->stock }};
        
        if (quantityInput) {
            document.getElementById('quantity-decrease').addEventListener('click', function() {
                const currentValue = parseInt(quantityInput.value);
                if (currentValue > 1) {
                    quantityInput.value = currentValue - 1;
                }
            });
            
            document.getElementById('quantity-increase').addEventListener('click', function() {
                const currentValue = parseInt(quantityInput.value);
                if (currentValue < maxQuantity) {
                    quantityInput.value = currentValue + 1;
                }
            });
            
            // Add to cart form
            const addToCartForm = document.getElementById('add-to-cart-form');
            
            if (addToCartForm) {
                addToCartForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const quantity = parseInt(quantityInput.value);
                    
                    if (isNaN(quantity) || quantity < 1 || quantity > maxQuantity) {
                        alert('Please enter a valid quantity');
                        return;
                    }
                    
                    fetch('/api/cart', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Authorization': `Bearer ${localStorage.getItem('token')}`
                        },
                        body: JSON.stringify({
                            product_id: {{ $product->id }},
                            quantity: quantity
                        })
                    })
                    .then(response => {
                        if (!response.ok) {
                            if (response.status === 401) {
                                // User not logged in, redirect to login
                                window.location.href = '{{ route("login") }}?redirect={{ url()->current() }}';
                                return Promise.reject('Please log in to add items to your cart');
                            }
                            return response.json().then(data => Promise.reject(data.message || 'An error occurred'));
                        }
                        return response.json();
                    })
                    .then(data => {
                        // Show success message
                        alert('Product added to cart successfully');
                        
                        // Update cart count in navbar if the function exists
                        if (typeof fetchCartCount === 'function') {
                            fetchCartCount();
                        }
                    })
                    .catch(error => {
                        console.error('Error adding to cart:', error);
                        if (error !== 'Please log in to add items to your cart') {
                            alert(error || 'An error occurred while adding the product to your cart');
                        }
                    });
                });
            }
        }
    });
</script>
@endsection 