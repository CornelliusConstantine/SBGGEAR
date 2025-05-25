'use strict';

app.controller('CheckoutController', ['$scope', '$location', 'CartService', 'OrderService', 'PaymentService', 'AuthService', function($scope, $location, CartService, OrderService, PaymentService, AuthService) {
    // Initialize controller
    $scope.init = function() {
        $scope.loading = {
            cart: true,
            order: false,
            payment: false
        };
        
        $scope.cart = {
            items: [],
            total: 0
        };
        
        $scope.currentStep = 1;
        $scope.totalSteps = 3;
        $scope.steps = [
            { number: 1, name: 'Shipping', active: true },
            { number: 2, name: 'Payment', active: false },
            { number: 3, name: 'Confirmation', active: false }
        ];
        
        $scope.shippingForm = {
            name: '',
            email: '',
            phone: '',
            address: '',
            city: '',
            postal_code: '',
            notes: ''
        };
        
        $scope.paymentMethod = 'midtrans';
        
        // Check if user is logged in
        var currentUser = AuthService.getCurrentUser();
        if (currentUser) {
            $scope.shippingForm.name = currentUser.name;
            $scope.shippingForm.email = currentUser.email;
            $scope.shippingForm.phone = currentUser.phone;
            $scope.shippingForm.address = currentUser.address;
            $scope.shippingForm.city = currentUser.city;
            $scope.shippingForm.postal_code = currentUser.postal_code;
        } else {
            // Redirect to login if not logged in
            $location.path('/login').search('redirect', 'checkout');
            return;
        }
        
        // Load cart
        $scope.loadCart();
    };
    
    // Load cart
    $scope.loadCart = function() {
        $scope.loading.cart = true;
        
        CartService.getCart()
            .then(function(cart) {
                $scope.cart = cart;
                
                // Check if cart is empty
                if (!cart.items.length) {
                    $location.path('/cart');
                    return;
                }
            })
            .catch(function(error) {
                console.error('Error loading cart', error);
            })
            .finally(function() {
                $scope.loading.cart = false;
            });
    };
    
    // Go to next step
    $scope.nextStep = function() {
        // Validate current step
        if ($scope.currentStep === 1) {
            if (!$scope.validateShippingForm()) {
                return;
            }
        }
        
        // Move to next step
        if ($scope.currentStep < $scope.totalSteps) {
            $scope.currentStep++;
            $scope.updateSteps();
            
            // If moving to payment step, create order
            if ($scope.currentStep === 2) {
                $scope.createOrder();
            }
        }
    };
    
    // Go to previous step
    $scope.prevStep = function() {
        if ($scope.currentStep > 1) {
            $scope.currentStep--;
            $scope.updateSteps();
        }
    };
    
    // Update steps status
    $scope.updateSteps = function() {
        $scope.steps.forEach(function(step) {
            step.active = step.number === $scope.currentStep;
            step.completed = step.number < $scope.currentStep;
        });
    };
    
    // Validate shipping form
    $scope.validateShippingForm = function() {
        // Reset errors
        $scope.errors = {};
        
        // Required fields
        var requiredFields = ['name', 'email', 'phone', 'address', 'city', 'postal_code'];
        var isValid = true;
        
        requiredFields.forEach(function(field) {
            if (!$scope.shippingForm[field]) {
                $scope.errors[field] = 'This field is required';
                isValid = false;
            }
        });
        
        // Email validation
        if ($scope.shippingForm.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test($scope.shippingForm.email)) {
            $scope.errors.email = 'Please enter a valid email address';
            isValid = false;
        }
        
        // Phone validation
        if ($scope.shippingForm.phone && !/^\d{10,15}$/.test($scope.shippingForm.phone.replace(/[^0-9]/g, ''))) {
            $scope.errors.phone = 'Please enter a valid phone number';
            isValid = false;
        }
        
        return isValid;
    };
    
    // Create order
    $scope.createOrder = function() {
        $scope.loading.order = true;
        
        var orderData = {
            shipping_address: $scope.shippingForm.address,
            shipping_city: $scope.shippingForm.city,
            shipping_postal_code: $scope.shippingForm.postal_code,
            recipient_name: $scope.shippingForm.name,
            recipient_email: $scope.shippingForm.email,
            recipient_phone: $scope.shippingForm.phone,
            notes: $scope.shippingForm.notes,
            payment_method: $scope.paymentMethod
        };
        
        OrderService.createOrder(orderData)
            .then(function(response) {
                $scope.order = response.data;
            })
            .catch(function(error) {
                console.error('Error creating order', error);
                $scope.showToast('Failed to create order. Please try again.', 'error');
                
                // Go back to shipping step
                $scope.currentStep = 1;
                $scope.updateSteps();
            })
            .finally(function() {
                $scope.loading.order = false;
            });
    };
    
    // Process payment
    $scope.processPayment = function() {
        $scope.loading.payment = true;
        
        OrderService.payOrder($scope.order.id, { method: $scope.paymentMethod })
            .then(function(response) {
                // If using Midtrans, open the payment popup
                if ($scope.paymentMethod === 'midtrans' && response.data.snap_token) {
                    return PaymentService.openSnapPayment(response.data.snap_token);
                }
                
                return response;
            })
            .then(function(result) {
                // Move to confirmation step
                $scope.currentStep = 3;
                $scope.updateSteps();
                
                // Update order status
                return OrderService.getOrder($scope.order.id);
            })
            .then(function(response) {
                $scope.order = response.data;
                
                // Clear cart after successful payment
                return CartService.clearCart();
            })
            .catch(function(error) {
                console.error('Error processing payment', error);
                $scope.showToast('Payment failed. Please try again.', 'error');
            })
            .finally(function() {
                $scope.loading.payment = false;
            });
    };
    
    // Show toast message
    $scope.showToast = function(message, type) {
        $scope.toast = {
            message: message,
            type: type || 'success',
            show: true
        };
        
        // Hide toast after 3 seconds
        setTimeout(function() {
            $scope.$apply(function() {
                $scope.toast.show = false;
            });
        }, 3000);
    };
    
    // Initialize controller
    $scope.init();
}]); 