@props(['categories'])

<div class="container my-5">
    <h2 class="text-center mb-4">{{ __('BROWSE BY SAFETY GEAR TYPE') }}</h2>
    
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
        @foreach($categories as $category)
            <div class="col">
                <a href="{{ route('products.category', $category->slug) }}" class="text-decoration-none">
                    <div class="card h-100 bg-gradient shadow category-card position-relative overflow-hidden">
                        @php
                            $imageName = '';
                            if ($category->slug == 'eye-protection') $imageName = 'pelindungmata.jpg';
                            elseif ($category->slug == 'footwear') $imageName = 'alaskaki.jpg';
                            elseif ($category->slug == 'hand-protection') $imageName = 'pelindungtangan.jpg';
                            elseif ($category->slug == 'head-protection') $imageName = 'pelindungkepala.jpg';
                            elseif ($category->slug == 'respiratory-protection') $imageName = 'pelindungpernapasan.jpeg';
                            elseif ($category->slug == 'visibility') $imageName = 'alatvisibilitas.jpg';
                            elseif ($category->slug == 'ear-protection') $imageName = 'pelindungtelinga.jpg';
                            elseif ($category->slug == 'fall-protection') $imageName = 'alatkeselamatanketinggian.jpg';
                        @endphp
                        
                        <img src="{{ asset('images/categoryimg/' . $imageName) }}" 
                             alt="{{ $category->name }}" 
                             class="card-img category-img">
                        <div class="card-img-overlay d-flex align-items-center justify-content-center text-center">
                            <h3 class="card-title text-white fw-bold">{{ $category->name }}</h3>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
</div>

<style>
    .category-card {
        transition: all 0.3s ease;
        border: none;
    }
    
    .category-card:hover {
        transform: translateY(-5px);
    }
    
    .category-img {
        height: 200px;
        object-fit: cover;
        filter: brightness(0.7);
    }
    
    .card-img-overlay {
        background: linear-gradient(rgba(0,0,0,0.2), rgba(0,0,0,0.6));
    }
</style> 