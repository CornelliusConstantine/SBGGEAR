'use strict';

app.controller('CheckoutController', ['$scope', '$http', '$location', 'AuthService', 'CartService', 'LocationService', function($scope, $http, $location, AuthService, CartService, LocationService) {
    // Initialize variables
    $scope.loading = true;
    $scope.cart = {};
    $scope.shipping = {
        name: '',
        phone: '',
        address: '',
        province: '',
        city: '',
        postalCode: '',
        courier: '',
        service: '',
        cost: 0,
        notes: ''
    };
    $scope.shippingServices = [];
    $scope.toast = {
        show: false,
        type: 'success',
        message: ''
    };
    $scope.payment = {
        loading: false
    };
    $scope.order = {};
    
    // Location data
    $scope.provinces = [];
    $scope.availableCities = [];
    $scope.loadingLocations = false;

    // Initialize checkout
    $scope.init = function() {
        // Check if user is logged in
        if (!AuthService.isAuthenticated()) {
            $location.path('/login');
            return;
        }

        // Load cart and location data
        Promise.all([
            $scope.loadCart(),
            $scope.loadProvinces()
        ]).then(function() {
            $scope.loading = false;
            $scope.$apply();
        });
    };

    // Load cart data
    $scope.loadCart = function() {
        $scope.loading = true;
        return CartService.getCart()
            .then(function(response) {
                $scope.cart = response;
                return response;
            })
            .catch(function(error) {
                console.error('Error loading cart:', error);
                $scope.showToast('error', 'Failed to load cart data. Please try again.');
                return null;
            });
    };
    
    // Load provinces
    $scope.loadProvinces = function() {
        $scope.loadingLocations = true;
        return LocationService.getProvinces()
            .then(function(provinces) {
                $scope.provinces = provinces;
                $scope.loadingLocations = false;
                return provinces;
            })
            .catch(function(error) {
                console.error('Error loading provinces:', error);
                $scope.loadingLocations = false;
                return [];
            });
    };
    
    // Update cities when province changes
    $scope.updateCities = function() {
        if ($scope.shipping.province) {
            $scope.loadingLocations = true;
            $scope.availableCities = [];
            
            LocationService.getCitiesByProvince($scope.shipping.province)
                .then(function(cities) {
                    $scope.availableCities = cities;
                    $scope.shipping.city = ''; // Reset city when province changes
                    $scope.loadingLocations = false;
                    $scope.$apply();
                })
                .catch(function(error) {
                    console.error('Error loading cities:', error);
                    $scope.loadingLocations = false;
                    $scope.$apply();
                });
        } else {
            $scope.availableCities = [];
            $scope.shipping.city = '';
        }
    };

    // Select courier
    $scope.selectCourier = function(courier) {
        $scope.shipping.courier = courier;
        $scope.shipping.service = '';
        $scope.shipping.cost = 0;
        
        // Load shipping services based on selected courier
        $scope.loadShippingServices(courier);
    };

    // Load shipping services
    $scope.loadShippingServices = function(courier) {
        $scope.loading = true;
        
        // Mock shipping services based on courier
        let services = [];
        
        if (courier === 'jne') {
            services = [
                { code: 'reg', name: 'JNE Regular', description: 'Delivery in 2-3 days', cost: 9000 },
                { code: 'yes', name: 'JNE YES', description: 'Delivery in 1-2 days', cost: 15000 }
            ];
        } else if (courier === 'jnt') {
            services = [
                { code: 'reg', name: 'J&T Regular', description: 'Delivery in 2-4 days', cost: 8000 },
                { code: 'exp', name: 'J&T Express', description: 'Delivery in 1-2 days', cost: 12000 }
            ];
        } else if (courier === 'sicepat') {
            services = [
                { code: 'reg', name: 'SiCepat Regular', description: 'Delivery in 2-3 days', cost: 8500 },
                { code: 'best', name: 'SiCepat BEST', description: 'Delivery in 1-2 days', cost: 13000 }
            ];
        }
        
        $scope.shippingServices = services;
        $scope.loading = false;
    };

    // Select shipping service
    $scope.selectService = function(service) {
        $scope.shipping.service = service.code;
        $scope.shipping.cost = service.cost;
    };

    // Submit shipping information
    $scope.submitShippingInfo = function() {
        if (!$scope.shipping.name || !$scope.shipping.phone || !$scope.shipping.address || 
            !$scope.shipping.province || !$scope.shipping.city || !$scope.shipping.postalCode || 
            !$scope.shipping.courier || !$scope.shipping.service) {
            $scope.showToast('error', 'Please fill in all required fields');
            return;
        }
        
        // Save shipping info and proceed to payment
        $scope.loading = true;
        
        // Get province and city names for better display
        const selectedProvince = $scope.provinces.find(p => p.id == $scope.shipping.province);
        const selectedCity = $scope.availableCities.find(c => c.id == $scope.shipping.city);
        
        // Store shipping info in localStorage for persistence
        const shippingData = {
            ...$scope.shipping,
            provinceName: selectedProvince ? selectedProvince.name : '',
            cityName: selectedCity ? selectedCity.name : ''
        };
        
        localStorage.setItem('checkout_shipping', JSON.stringify(shippingData));
        
        // Navigate to payment page
        $location.path('/checkout/payment');
    };

    // Process payment
    $scope.processPayment = function() {
        $scope.payment.loading = true;
        
        // In a real implementation, this would integrate with Midtrans
        // For now, we'll simulate a successful payment after a delay
        setTimeout(function() {
            // Create order
            $http.post('/api/orders', {
                shipping_info: $scope.shipping,
                payment_method: 'midtrans',
                payment_details: {
                    transaction_id: 'mid-' + Math.random().toString(36).substr(2, 9),
                    payment_type: 'credit_card'
                }
            })
            .then(function(response) {
                $scope.order = response.data;
                $scope.payment.loading = false;
                
                // Clear cart
                CartService.clearCart();
                
                // Navigate to confirmation page
                $location.path('/checkout/confirmation/' + $scope.order.id);
                
                // Apply scope changes
                $scope.$apply();
            })
            .catch(function(error) {
                console.error('Error creating order:', error);
                $scope.showToast('error', 'Failed to process payment. Please try again.');
                $scope.payment.loading = false;
                $scope.$apply();
            });
        }, 2000);
    };

    // Show toast notification
    $scope.showToast = function(type, message) {
        $scope.toast = {
            show: true,
            type: type,
            message: message
        };
        
        // Hide toast after 3 seconds
        setTimeout(function() {
            $scope.toast.show = false;
            $scope.$apply();
        }, 3000);
    };

    // Continue shopping
    $scope.continueShopping = function() {
        $location.path('/products');
    };

    // Initialize controller
    $scope.init();
}]); 