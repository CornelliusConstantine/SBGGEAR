'use strict';

// Define the main application module
var app = angular.module('sbgGearApp', [
    'ngRoute',
    'ngAnimate',
    'ngSanitize',
    'ui.bootstrap'
]);

// Configure routes
app.config(['$routeProvider', '$locationProvider', function($routeProvider, $locationProvider) {
    $routeProvider
        // Customer routes
        .when('/', {
            templateUrl: 'app/views/customer/home.html',
            controller: 'MainController'
        })
        .when('/products', {
            templateUrl: 'app/views/customer/products.html',
            controller: 'ProductController'
        })
        .when('/product/:id', {
            templateUrl: 'app/views/customer/product-detail.html',
            controller: 'ProductController'
        })
        .when('/search/:query', {
            templateUrl: 'app/views/customer/search-results.html',
            controller: 'ProductController'
        })
        .when('/cart', {
            templateUrl: 'app/views/customer/cart.html',
            controller: 'CartController'
        })
        .when('/checkout', {
            templateUrl: 'app/views/customer/checkout/shipping.html',
            controller: 'CheckoutController',
            resolve: {
                auth: ['AuthService', function(AuthService) {
                    return AuthService.requireAuth();
                }]
            }
        })
        .when('/checkout/shipping', {
            templateUrl: 'app/views/customer/checkout/shipping.html',
            controller: 'CheckoutController',
            resolve: {
                auth: ['AuthService', function(AuthService) {
                    return AuthService.requireAuth();
                }]
            }
        })
        .when('/checkout/payment', {
            templateUrl: 'app/views/customer/checkout/payment.html',
            controller: 'CheckoutController',
            resolve: {
                auth: ['AuthService', function(AuthService) {
                    return AuthService.requireAuth();
                }]
            }
        })
        .when('/checkout/confirmation/:id', {
            templateUrl: 'app/views/customer/checkout/confirmation.html',
            controller: 'ConfirmationController',
            resolve: {
                auth: ['AuthService', function(AuthService) {
                    return AuthService.requireAuth();
                }]
            }
        })
        .when('/checkout/confirmation', {
            templateUrl: 'app/views/customer/checkout/confirmation.html',
            controller: 'ConfirmationController',
            resolve: {
                auth: ['AuthService', function(AuthService) {
                    return AuthService.requireAuth();
                }]
            }
        })
        .when('/order-success/:id', {
            templateUrl: 'app/views/customer/order-success.html',
            controller: 'OrderController'
        })
        .when('/orders', {
            templateUrl: 'app/views/customer/orders.html',
            controller: 'OrderController',
            resolve: {
                auth: ['AuthService', function(AuthService) {
                    return AuthService.requireAuth();
                }]
            }
        })
        .when('/order/:id', {
            templateUrl: 'app/views/customer/order-detail.html',
            controller: 'OrderController',
            resolve: {
                auth: ['AuthService', function(AuthService) {
                    return AuthService.requireAuth();
                }]
            }
        })
        .when('/profile', {
            templateUrl: 'app/views/customer/profile.html',
            controller: 'ProfileController',
            resolve: {
                auth: ['AuthService', function(AuthService) {
                    return AuthService.requireAuth();
                }]
            }
        })
        .when('/login', {
            templateUrl: 'app/views/customer/login.html',
            controller: 'AuthController'
        })
        .when('/register', {
            templateUrl: 'app/views/customer/register.html',
            controller: 'AuthController'
        })
        .when('/forgot-password', {
            templateUrl: 'app/views/customer/forgot-password.html',
            controller: 'AuthController'
        })
        .when('/reset-password', {
            templateUrl: 'app/views/customer/reset-password.html',
            controller: 'AuthController'
        })
        .when('/on-sale', {
            templateUrl: 'app/views/customer/on-sale.html',
            controller: 'ProductController'
        })
        .when('/new-arrivals', {
            templateUrl: 'app/views/customer/new-arrivals.html',
            controller: 'ProductController'
        })
        .when('/category/:slug', {
            templateUrl: 'app/views/customer/category.html',
            controller: 'ProductController'
        })
        
        // Admin routes
        .when('/admin', {
            templateUrl: 'app/views/admin/dashboard.html',
            controller: 'AdminController',
            resolve: {
                auth: ['AuthService', function(AuthService) {
                    return AuthService.requireAdmin();
                }]
            }
        })
        .when('/admin/products', {
            templateUrl: 'app/views/admin/products.html',
            controller: 'AdminProductController',
            resolve: {
                auth: ['AuthService', function(AuthService) {
                    return AuthService.requireAdmin();
                }]
            }
        })
        .when('/admin/product/:id?', {
            templateUrl: 'app/views/admin/product-form.html',
            controller: 'AdminProductController',
            resolve: {
                auth: ['AuthService', function(AuthService) {
                    return AuthService.requireAdmin();
                }]
            }
        })
        .when('/admin/categories', {
            templateUrl: 'app/views/admin/categories.html',
            controller: 'AdminCategoryController',
            resolve: {
                auth: ['AuthService', function(AuthService) {
                    return AuthService.requireAdmin();
                }]
            }
        })
        .when('/admin/orders', {
            templateUrl: 'app/views/admin/orders.html',
            controller: 'AdminOrderController',
            resolve: {
                auth: ['AuthService', function(AuthService) {
                    return AuthService.requireAdmin();
                }]
            }
        })
        .when('/admin/order/:id', {
            templateUrl: 'app/views/admin/order-detail.html',
            controller: 'AdminOrderController',
            resolve: {
                auth: ['AuthService', function(AuthService) {
                    return AuthService.requireAdmin();
                }]
            }
        })
        .when('/admin/stock', {
            templateUrl: 'app/views/admin/stock.html',
            controller: 'AdminStockController',
            resolve: {
                auth: ['AuthService', function(AuthService) {
                    return AuthService.requireAdmin();
                }]
            }
        })
        .when('/admin/users', {
            templateUrl: 'app/views/admin/users.html',
            controller: 'AdminUserController',
            resolve: {
                auth: ['AuthService', function(AuthService) {
                    return AuthService.requireAdmin();
                }]
            }
        })
        .when('/admin/reports', {
            templateUrl: 'app/views/admin/reports.html',
            controller: 'AdminReportController',
            resolve: {
                auth: ['AuthService', function(AuthService) {
                    return AuthService.requireAdmin();
                }]
            }
        })
        .otherwise({
            redirectTo: '/'
        });
    
    // Use HTML5 History API instead of hashbang
    $locationProvider.html5Mode({
        enabled: true,
        requireBase: true
    });
    $locationProvider.hashPrefix('!');
}]);

// Run block to check authentication status on app start
app.run(['$rootScope', '$location', '$window', '$http', 'AuthService', 'CartService', 
    function($rootScope, $location, $window, $http, AuthService, CartService) {
    
    // Fetch CSRF token
    $http.get('/csrf-token')
        .then(function(response) {
            if (response.data && response.data.csrf_token) {
                // Store token in meta tag
                var metaTag = document.querySelector('meta[name="csrf-token"]');
                if (metaTag) {
                    metaTag.setAttribute('content', response.data.csrf_token);
                } else {
                    // Create meta tag if it doesn't exist
                    metaTag = document.createElement('meta');
                    metaTag.setAttribute('name', 'csrf-token');
                    metaTag.setAttribute('content', response.data.csrf_token);
                    document.head.appendChild(metaTag);
                }
                
                // Store in window.Laravel for compatibility
                if (!$window.Laravel) {
                    $window.Laravel = {};
                }
                $window.Laravel.csrfToken = response.data.csrf_token;
                
                console.log('CSRF token fetched successfully');
            }
        })
        .catch(function(error) {
            console.error('Failed to fetch CSRF token:', error);
        });
    
    // Check authentication on app start
    AuthService.checkAuth()
        .then(function(user) {
            // User is authenticated, initialize cart
            CartService.init();
        })
        .catch(function() {
            // User is not authenticated, reset cart
            CartService.resetCart();
        });
    
    // Handle route change errors (e.g., unauthorized access)
    $rootScope.$on('$routeChangeError', function(event, current, previous, rejection) {
        if (rejection === 'unauthorized') {
            $location.path('/login');
        } else if (rejection === 'forbidden') {
            $location.path('/');
        }
    });

    // Handle links with hashbang URLs and convert them to HTML5 mode
    $rootScope.$on('$viewContentLoaded', function() {
        // Find all links with href starting with "#!/"
        setTimeout(function() {
            var hashbangLinks = document.querySelectorAll('a[href^="#!/"]');
            
            // Convert each hashbang link to HTML5 mode
            angular.forEach(hashbangLinks, function(link) {
                var href = link.getAttribute('href');
                if (href && href.startsWith('#!/')) {
                    // Convert #!/path to /path
                    var newHref = href.replace('#!/', '/');
                    link.setAttribute('ng-href', newHref);
                    link.setAttribute('href', newHref);
                    
                    // Add click handler to prevent default and use $location
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        var path = newHref;
                        $rootScope.$apply(function() {
                            $location.path(path);
                        });
                    });
                }
            });
        }, 100);
    });
    
    // Handle hash URLs in the browser address bar
    if ($window.location.hash && $window.location.hash.startsWith('#!')) {
        var path = $window.location.hash.substring(2); // Remove the '#!'
        $window.history.replaceState({}, document.title, path);
        $location.path(path).replace();
    }

    // Load Midtrans script
    var midtransSnapUrl = document.querySelector('meta[name="midtrans-snap-url"]');
    var midtransClientKey = document.querySelector('meta[name="midtrans-client-key"]');

    if (midtransSnapUrl && midtransClientKey) {
        var snapUrl = midtransSnapUrl.getAttribute('content');
        var clientKey = midtransClientKey.getAttribute('content');
        
        if (snapUrl && clientKey) {
            console.log('Loading Midtrans Snap.js with client key:', clientKey);
            var script = document.createElement('script');
            script.src = snapUrl;
            script.setAttribute('data-client-key', clientKey);
            document.head.appendChild(script);
        } else {
            console.error('Missing Midtrans configuration: snapUrl or clientKey is not set');
        }
    } else {
        console.error('Missing Midtrans meta tags in HTML');
    }
}]);

// Global HTTP interceptor for authentication
app.factory('AuthInterceptor', ['$q', '$location', '$window', function($q, $location, $window) {
    return {
        responseError: function(rejection) {
            if (rejection.status === 401) {
                // Unauthorized, redirect to login
                $window.localStorage.removeItem('token');
                $location.path('/login');
            }
            return $q.reject(rejection);
        }
    };
}]);

// CSRF Token Interceptor
app.factory('CSRFInterceptor', ['$window', function($window) {
    return {
        request: function(config) {
            // Try to get the CSRF token from meta tag
            var csrfToken = document.querySelector('meta[name="csrf-token"]');
            
            if (csrfToken) {
                config.headers['X-CSRF-TOKEN'] = csrfToken.getAttribute('content');
            } else if ($window.Laravel && $window.Laravel.csrfToken) {
                config.headers['X-CSRF-TOKEN'] = $window.Laravel.csrfToken;
            }
            
            return config;
        }
    };
}]);

// Configure HTTP interceptors
app.config(['$httpProvider', function($httpProvider) {
    $httpProvider.interceptors.push('AuthInterceptor');
    $httpProvider.interceptors.push('CSRFInterceptor');
}]); 