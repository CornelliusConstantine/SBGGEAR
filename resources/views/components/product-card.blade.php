@props(['product'])

<div class="card border-0 shadow-sm h-100 product-card">
    <a href="{{ route('products.show', $product) }}" class="text-decoration-none position-relative">
        @if($product->images && isset($product->images['main']))
            <img src="{{ asset('storage/products/thumbnails/' . $product->images['main']) }}" 
                alt="{{ $product->name }}" class="card-img-top" 
                style="height: 200px; object-fit: cover;">
        @else
            <img src="{{ asset('images/no-image.jpg') }}" 
                alt="{{ $product->name }}" class="card-img-top" 
                style="height: 200px; object-fit: cover;">
        @endif
        
        @if($product->stock <= 0)
            <div class="position-absolute top-0 end-0 m-2">
                <span class="badge bg-danger">Out of Stock</span>
            </div>
        @endif
    </a>
    <div class="card-body d-flex flex-column">
        <h5 class="card-title">
            <a href="{{ route('products.show', $product) }}" class="text-decoration-none text-dark">
                {{ $product->name }}
            </a>
        </h5>
        <p class="card-text text-primary fw-bold">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
        <div class="mt-auto">
            @if($product->stock > 0)
                <form action="{{ route('cart.add') }}" method="POST" class="add-to-cart-form">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="quantity" value="1">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-cart-plus me-1"></i> Add to Cart
                    </button>
                </form>
            @else
                <button class="btn btn-secondary btn-sm w-100" disabled>
                    <i class="bi bi-x-circle me-1"></i> Out of Stock
                </button>
            @endif
        </div>
    </div>
</div> 