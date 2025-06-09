<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'SBGEAR') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    
    <!-- Cart Script -->
    <script src="{{ asset('js/cart.js') }}" defer></script>
    
    <!-- Checkout Script -->
    <script src="{{ asset('js/checkout.js') }}" defer></script>
    
    <style>
        :root {
            --bs-primary: #0069d9;
            --bs-primary-rgb: 0, 105, 217;
        }
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f8f9fa;
        }
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }
        .nav-link {
            font-weight: 600;
        }
        .btn-primary {
            background-color: var(--bs-primary);
            border-color: var(--bs-primary);
        }
        .btn-outline-primary {
            color: var(--bs-primary);
            border-color: var(--bs-primary);
        }
        .btn-outline-primary:hover {
            background-color: var(--bs-primary);
            border-color: var(--bs-primary);
        }
        .fs-7 {
            font-size: 0.875rem;
        }
        .search-form {
            width: 100%;
            max-width: 350px;
        }
        .search-form .form-control {
            border-right: none;
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }
        .search-form .btn {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }
        .footer {
            background-color: #fff;
            border-top: 1px solid #eaeaea;
            padding: 3rem 0;
        }
        .social-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #f0f0f0;
            color: #666;
            transition: all 0.3s ease;
            margin-right: 0.5rem;
        }
        .social-icon:hover {
            background-color: var(--bs-primary);
            color: white;
        }
        #cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            transition: all 0.3s ease;
        }
        .cart-link {
            position: relative;
        }
        .cart-icon {
            font-size: 1.2rem;
        }
    </style>
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    SBGEAR
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('products.index') }}">Shop</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('products.new') }}">New Arrivals</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('products.index') }}">Products</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('products.sale') }}">On Sale</a>
                        </li>
                    </ul>
                    
                    <!-- Search Form -->
                    <form class="d-flex search-form mx-auto" action="{{ route('products.index') }}" method="GET">
                        <input class="form-control" type="search" name="search" placeholder="Search for products..." aria-label="Search">
                        <button class="btn btn-outline-secondary" type="submit">
                            <i class="bi bi-search"></i>
                        </button>
                    </form>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ms-auto">
                        <!-- Authentication Links -->
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                </li>
                            @endif

                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item">
                                <a class="nav-link cart-link" href="{{ route('cart') }}">
                                    <i class="bi bi-cart cart-icon"></i>
                                    <span class="badge bg-primary rounded-pill" id="cart-count">0</span>
                                </a>
                            </li>
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    <i class="bi bi-person-circle me-1"></i>{{ Auth::user()->name }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('account') }}">
                                        <i class="bi bi-person me-2"></i>My Account
                                    </a>
                                    <a class="dropdown-item" href="{{ route('orders.index') }}">
                                        <i class="bi bi-box me-2"></i>My Orders
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        <i class="bi bi-box-arrow-right me-2"></i>{{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
            @yield('content')
        </main>
        
        <footer class="footer">
            <div class="container">
                <div class="row">
                    <div class="col-lg-3 mb-4 mb-lg-0">
                        <h5 class="fw-bold mb-4">SBGEAR</h5>
                        <p class="mb-4">Your one-stop shop for the best gear and accessories.</p>
                        <div class="d-flex">
                            <a href="#" class="social-icon"><i class="bi bi-facebook"></i></a>
                            <a href="#" class="social-icon"><i class="bi bi-instagram"></i></a>
                            <a href="#" class="social-icon"><i class="bi bi-twitter"></i></a>
                            <a href="#" class="social-icon"><i class="bi bi-youtube"></i></a>
                        </div>
                    </div>
                    <div class="col-lg-3 mb-4 mb-lg-0">
                        <h6 class="fw-bold mb-3">Shop</h6>
                        <ul class="list-unstyled">
                            <li class="mb-2"><a href="#" class="text-decoration-none text-muted">All Products</a></li>
                            <li class="mb-2"><a href="#" class="text-decoration-none text-muted">New Arrivals</a></li>
                            <li class="mb-2"><a href="#" class="text-decoration-none text-muted">Best Sellers</a></li>
                            <li class="mb-2"><a href="#" class="text-decoration-none text-muted">On Sale</a></li>
                        </ul>
                    </div>
                    <div class="col-lg-3 mb-4 mb-lg-0">
                        <h6 class="fw-bold mb-3">Customer Service</h6>
                        <ul class="list-unstyled">
                            <li class="mb-2"><a href="#" class="text-decoration-none text-muted">Contact Us</a></li>
                            <li class="mb-2"><a href="#" class="text-decoration-none text-muted">FAQs</a></li>
                            <li class="mb-2"><a href="#" class="text-decoration-none text-muted">Shipping & Returns</a></li>
                            <li class="mb-2"><a href="#" class="text-decoration-none text-muted">Track Order</a></li>
                        </ul>
                    </div>
                    <div class="col-lg-3">
                        <h6 class="fw-bold mb-3">Newsletter</h6>
                        <p class="text-muted mb-3">Subscribe to receive updates, access to exclusive deals, and more.</p>
                        <form action="#" method="POST">
                            <div class="input-group mb-3">
                                <input type="email" class="form-control" placeholder="Enter your email" aria-label="Email" aria-describedby="button-addon2">
                                <button class="btn btn-primary" type="submit" id="button-addon2">Subscribe</button>
                            </div>
                        </form>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-6 text-center text-md-start">
                        <p class="text-muted mb-0">&copy; 2023 SBGEAR. All rights reserved.</p>
                    </div>
                    <div class="col-md-6 text-center text-md-end">
                        <p class="text-muted mb-0">
                            <a href="#" class="text-decoration-none text-muted me-3">Privacy Policy</a>
                            <a href="#" class="text-decoration-none text-muted me-3">Terms of Service</a>
                            <a href="#" class="text-decoration-none text-muted">Cookie Policy</a>
                        </p>
                    </div>
                </div>
            </div>
        </footer>
    </div>
    
    @yield('scripts')
    
    @auth
    <script>
        // Update cart count in navbar when page loads
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof cart !== 'undefined') {
                cart.getCart().then(cartData => {
                    if (cartData) {
                        cart.updateCartCount(cartData);
                    }
                }).catch(error => {
                    console.error('Error fetching cart count:', error);
                });
            } else {
                // Fallback if cart.js hasn't loaded yet
                fetchCartCount();
            }
        });
        
        // Legacy function for backward compatibility
        function fetchCartCount() {
            fetch('/api/cart', {
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Authorization': `Bearer ${localStorage.getItem('token')}`
                }
            })
            .then(response => {
                if (!response.ok) {
                    if (response.status === 401) {
                        // Handle unauthorized - clear token and don't show count
                        localStorage.removeItem('token');
                        const countElement = document.getElementById('cart-count');
                        if (countElement) {
                            countElement.style.display = 'none';
                        }
                        return Promise.reject('Unauthorized');
                    }
                    return response.json().then(data => Promise.reject(data.message || 'An error occurred'));
                }
                return response.json();
            })
            .then(data => {
                const itemCount = data.items ? data.items.length : 0;
                const countElement = document.getElementById('cart-count');
                if (countElement) {
                    countElement.textContent = itemCount;
                    countElement.style.display = itemCount > 0 ? 'inline-block' : 'none';
                }
            })
            .catch(error => {
                if (error !== 'Unauthorized') {
                    console.error('Error fetching cart count:', error);
                }
            });
        }
    </script>
    @endauth
</body>
</html>
