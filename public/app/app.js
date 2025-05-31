'use strict';

// Define the main application module
var app = angular.module('sbgGearApp', [
    'ngRoute',
    'ngAnimate',
    'ngSanitize'
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
            templateUrl: 'app/views/customer/checkout.html',
            controller: 'CheckoutController'
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
    
    // Use HTML5 History API
    $locationProvider.hashPrefix('!');
}]);

// Run block for application initialization
app.run(['$rootScope', 'AuthService', 'CartService', function($rootScope, AuthService, CartService) {
    // Check authentication status when app starts
    AuthService.checkAuth();
    
    // Initialize cart
    CartService.init();
    
    // Handle route change errors (e.g., unauthorized access)
    $rootScope.$on('$routeChangeError', function(event, current, previous, rejection) {
        if (rejection === 'unauthorized') {
            window.location.href = '#!/login';
        } else if (rejection === 'forbidden') {
            window.location.href = '#!/';
        }
    });
}]); 