<!DOCTYPE html>
<html lang="en" ng-app="sbgGearApp">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SBGEAR - Safety Equipment Store</title>
    <!-- Base URL for HTML5 mode -->
    <base href="/">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('assets/img/favicon.png') }}">
    <style>
        /* Fix for duplicated navbar */
        header:not(#main-header) {
            display: none !important;
        }
        /* Make sure stars are black, not green */
        .star-symbol {
            color: #000 !important;
        }
        /* Remove hero-decoration (green stars) */
        .hero-decoration {
            display: none !important;
        }
        /* Search results dropdown styling */
        .search-form {
            position: relative;
        }
        .search-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            z-index: 1000;
            max-height: 400px;
            overflow-y: auto;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .search-thumbnail {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
        }
    </style>
    @yield('styles')
</head>
<body>
    <!-- Pass Laravel auth data to Angular -->
    <script>
        window.Laravel = {!! json_encode([
            'csrfToken' => csrf_token(),
            'apiToken' => auth()->check() ? auth()->user()->api_token : null,
            'user' => auth()->check() ? [
                'id' => auth()->user()->id,
                'name' => auth()->user()->name,
                'email' => auth()->user()->email,
                'isAdmin' => auth()->user()->is_admin ? true : false
            ] : null,
            'baseUrl' => url('/')
        ]) !!};
        
        // Store auth data in localStorage for Angular to access
        if (window.Laravel.apiToken) {
            localStorage.setItem('token', window.Laravel.apiToken);
            localStorage.setItem('user', JSON.stringify(window.Laravel.user));
        }
    </script>

    @yield('content')

    <!-- Bootstrap JS with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AngularJS -->
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.8.2/angular.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.8.2/angular-route.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.8.2/angular-animate.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.8.2/angular-sanitize.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/angular-ui-bootstrap/2.5.6/ui-bootstrap-tpls.min.js"></script>
    
    <!-- App Module -->
    <script src="{{ asset('app/app.js') }}"></script>
    
    <!-- Services -->
    <script src="{{ asset('app/services/authService.js') }}"></script>
    <script src="{{ asset('app/services/productService.js') }}"></script>
    <script src="{{ asset('app/services/cartService.js') }}"></script>
    <script src="{{ asset('app/services/orderService.js') }}"></script>
    <script src="{{ asset('app/services/paymentService.js') }}"></script>
    <script src="{{ asset('app/services/categoryService.js') }}"></script>
    <script src="{{ asset('app/services/locationService.js') }}"></script>
    
    <!-- Controllers -->
    <script src="{{ asset('app/controllers/headerController.js') }}"></script>
    <script src="{{ asset('app/controllers/mainController.js') }}"></script>
    <script src="{{ asset('app/controllers/productController.js') }}"></script>
    <script src="{{ asset('app/controllers/cartController.js') }}"></script>
    <script src="{{ asset('app/controllers/checkoutController.js') }}"></script>
    <script src="{{ asset('app/controllers/orderController.js') }}"></script>
    <script src="{{ asset('app/controllers/profileController.js') }}"></script>
    <script src="{{ asset('app/controllers/adminController.js') }}"></script>
    <script src="{{ asset('app/controllers/authController.js') }}"></script>
    <script src="{{ asset('app/controllers/adminProductController.js') }}"></script>
    <script src="{{ asset('app/controllers/adminCategoryController.js') }}"></script>
    
    <!-- Directives -->
    <script src="{{ asset('app/directives/productCard.js') }}"></script>
    <script src="{{ asset('app/directives/ratingStars.js') }}"></script>
    <script src="{{ asset('app/directives/loadingSpinner.js') }}"></script>
    <script src="{{ asset('app/directives/fileModel.js') }}"></script>
    
    @yield('scripts')
</body>
</html> 