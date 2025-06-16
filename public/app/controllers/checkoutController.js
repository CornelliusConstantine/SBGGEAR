'use strict';

app.controller('CheckoutController', ['$scope', '$http', '$location', '$q', '$timeout', 'AuthService', 'CartService', 'LocationService', 'PaymentService', function($scope, $http, $location, $q, $timeout, AuthService, CartService, LocationService, PaymentService) {
    // Initialize variables
    $scope.loading = true;
    $scope.cart = {
        items: [],
        subtotal: 0,
        total: 0
    };
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
        loading: false,
        snapToken: null,
        clientKey: null,
        orderNumber: null,
        transactionId: null,
        transactionStatus: null
    };
    $scope.order = {};
    $scope.orderSummary = {
        subtotal: 0,
        shipping: 0,
        total: 0
    };
    
    // Location data
    $scope.provinces = [];
    $scope.availableCities = [];
    $scope.loadingLocations = false;

    // Helper function to safely apply changes to the scope
    function safeApply(fn) {
        var phase = $scope.$root.$$phase;
        if (phase == '$apply' || phase == '$digest') {
            if (fn && (typeof(fn) === 'function')) {
                fn();
            }
        } else {
            $scope.$apply(fn);
        }
    }

    // Helper function to get auth headers
    function getAuthHeaders() {
        var token = localStorage.getItem('token');
        var headers = {
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        };
        
        if (token) {
            headers['Authorization'] = 'Bearer ' + token;
        }
        
        return headers;
    }

    // Initialize checkout
    $scope.init = function() {
        // Initialize variables
        $scope.loading = true;
        $scope.cart = {
            items: [],
            subtotal: 0,
            total: 0
        };
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
            loading: false,
            snapToken: null,
            clientKey: null,
            orderNumber: null,
            transactionId: null,
            transactionStatus: null
        };
        $scope.order = {};
        $scope.orderSummary = {
            subtotal: 0,
            shipping: 0,
            total: 0
        };
        
        // Check if we're on a specific page in the checkout flow
        var currentPath = $location.path();
        
        // Check for Midtrans redirect parameters in URL
        var orderId = $location.search().order_id;
        var transactionId = $location.search().transaction_id;
        var transactionStatus = $location.search().transaction_status;
        
        console.log('URL parameters:', {
            orderId: orderId,
            transactionId: transactionId,
            transactionStatus: transactionStatus
        });
        
        // If we have redirect parameters from Midtrans, handle them
        if (orderId && (transactionId || transactionStatus)) {
            console.log('Detected Midtrans redirect with parameters');
            
            // Store payment details
            localStorage.setItem('payment_details', JSON.stringify({
                orderNumber: orderId,
                transactionId: transactionId || 'unknown',
                transactionStatus: transactionStatus || 'settlement',
                paymentType: $location.search().payment_type || 'unknown'
            }));
            
            // If we're not already on the confirmation page, redirect there
            if (currentPath !== '/checkout/confirmation') {
                $location.path('/checkout/confirmation').search('order_number', orderId);
                return;
            }
        }
        
        // Check if we're on the confirmation page
        if (currentPath.startsWith('/checkout/confirmation')) {
            // Load order data if available
            var orderId = $routeParams.id;
            var orderNumber = $location.search().order_number;
            
            if (orderId) {
                // Load order by ID
                $scope.loadOrderById(orderId);
            } else if (orderNumber) {
                // Load order by order number
                $scope.loadOrderByNumber(orderNumber);
            } else {
                // Check if we have payment details in localStorage
                var savedPaymentDetails = localStorage.getItem('payment_details');
                if (savedPaymentDetails) {
                    var paymentDetails = JSON.parse(savedPaymentDetails);
                    $scope.payment.orderNumber = paymentDetails.orderNumber;
                    $scope.payment.transactionId = paymentDetails.transactionId;
                    $scope.payment.transactionStatus = paymentDetails.transactionStatus;
                    
                    // Try to load order by order number
                    if (paymentDetails.orderNumber) {
                        $scope.loadOrderByNumber(paymentDetails.orderNumber);
                    } else {
                        // Show generic confirmation
                        $scope.genericConfirmation = true;
                        $scope.loading = false;
                    }
                } else {
                    // Show generic confirmation
                    $scope.genericConfirmation = true;
                    $scope.loading = false;
                }
            }
            return;
        }
        
        // Check if we're on the payment page
        if ($location.path() === '/checkout/payment') {
            // Load saved shipping and order summary data
            const savedShipping = localStorage.getItem('checkout_shipping');
            const savedSummary = localStorage.getItem('checkout_summary');
            const savedPaymentDetails = localStorage.getItem('payment_details');
            
            if (savedShipping) {
                $scope.shipping = JSON.parse(savedShipping);
            }
            
            if (savedSummary) {
                $scope.orderSummary = JSON.parse(savedSummary);
            }
            
            // Check if we have payment details (for returning after payment)
            if (savedPaymentDetails) {
                const paymentDetails = JSON.parse(savedPaymentDetails);
                $scope.payment.transactionId = paymentDetails.transactionId;
                $scope.payment.transactionStatus = paymentDetails.transactionStatus;
                $scope.payment.orderNumber = paymentDetails.orderNumber;
                
                // If payment was successful, show success message
                if (paymentDetails.transactionStatus === 'settlement' || paymentDetails.transactionStatus === 'capture') {
                    $scope.loading = false;
                    return;
                }
            }
            
            // Load cart and get payment token
            $q.all([
                $scope.loadCart(),
                $scope.getPaymentToken()
            ]).then(function() {
                $scope.loading = false;
                $scope.initMidtransSnap();
                safeApply();
            }).catch(function(error) {
                console.error('Failed to initialize payment:', error);
                $scope.loading = false;
                $scope.showToast('error', 'Failed to initialize payment. Please try again.');
                safeApply();
            });
        } else {
            // Load cart and location data for shipping page
            $q.all([
                $scope.loadCart(),
                $scope.loadProvinces()
            ]).then(function() {
                $scope.loading = false;
                $scope.updateOrderSummary();
                safeApply();
            }).catch(function(error) {
                console.error('Failed to initialize checkout:', error);
                $scope.loading = false;
                safeApply();
            });
        }
    };

    // Load cart data
    $scope.loadCart = function() {
        $scope.loading = true;
        return CartService.getCart()
            .then(function(response) {
                $scope.cart = response || { items: [], subtotal: 0, total: 0 };
                
                // Make sure subtotal is available
                if (!$scope.cart.subtotal && $scope.cart.total) {
                    $scope.cart.subtotal = $scope.cart.total;
                }
                
                // Calculate subtotal if not available
                if (!$scope.cart.subtotal) {
                    $scope.cart.subtotal = 0;
                    if ($scope.cart.items && $scope.cart.items.length > 0) {
                        $scope.cart.items.forEach(function(item) {
                            $scope.cart.subtotal += (parseFloat(item.price) * parseInt(item.quantity));
                        });
                    }
                }
                
                $scope.updateOrderSummary();
                return response;
            })
            .catch(function(error) {
                console.error('Error loading cart:', error);
                $scope.showToast('error', 'Failed to load cart data. Please try again.');
                return $q.reject(error);
            });
    };
    
    // Update order summary calculations
    $scope.updateOrderSummary = function() {
        $scope.orderSummary.subtotal = parseFloat($scope.cart.subtotal) || 0;
        $scope.orderSummary.shipping = parseFloat($scope.shipping.cost) || 0;
        $scope.orderSummary.total = $scope.orderSummary.subtotal + $scope.orderSummary.shipping;
    };
    
    // Load provinces
    $scope.loadProvinces = function() {
        $scope.loadingLocations = true;
        return LocationService.getProvinces()
            .then(function(provinces) {
                $scope.provinces = provinces || [];
                $scope.loadingLocations = false;
                return provinces;
            })
            .catch(function(error) {
                console.error('Error loading provinces:', error);
                $scope.loadingLocations = false;
                return $q.reject(error);
            });
    };
    
    // Update cities when province changes
    $scope.updateCities = function() {
        if ($scope.shipping.province) {
            $scope.loadingLocations = true;
            $scope.availableCities = [];
            
            LocationService.getCitiesByProvince($scope.shipping.province)
                .then(function(cities) {
                    $scope.availableCities = cities || [];
                    $scope.shipping.city = ''; // Reset city when province changes
                    $scope.loadingLocations = false;
                    safeApply();
                })
                .catch(function(error) {
                    console.error('Error loading cities:', error);
                    $scope.loadingLocations = false;
                    safeApply();
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
        $scope.updateOrderSummary();
        
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
        $scope.updateOrderSummary();
    };

    // Get payment token from server
    $scope.getPaymentToken = function() {
        $scope.payment.loading = true;
        console.log('Requesting payment token with data:', {
            shipping_info: $scope.shipping,
            order_summary: $scope.orderSummary
        });
        
        return PaymentService.getSnapToken({
            shipping_info: $scope.shipping,
            order_summary: $scope.orderSummary
        })
        .then(function(response) {
            console.log('Payment token response:', response);
            if (response && response.token) {
                $scope.payment.snapToken = response.token;
                $scope.payment.clientKey = response.client_key;
                $scope.payment.orderNumber = response.order_number;
                
                // Initialize Midtrans Snap
                if (typeof snap !== 'undefined') {
                    // Snap is already loaded
                    console.log('Snap is already loaded, initializing...');
                    $scope.initMidtransSnap();
                } else {
                    // Load Snap.js
                    console.log('Loading Snap.js with client key:', $scope.payment.clientKey);
                    var script = document.createElement('script');
                    
                    // Try to get the snap URL from a data attribute if available
                    var snapUrl = document.querySelector('meta[name="midtrans-snap-url"]');
                    script.src = snapUrl ? snapUrl.getAttribute('content') : 'https://app.sandbox.midtrans.com/snap/snap.js';
                    
                    console.log('Loading Midtrans Snap from: ' + script.src);
                    
                    script.setAttribute('data-client-key', $scope.payment.clientKey);
                    script.onload = function() {
                        console.log('Snap.js loaded successfully');
                        $scope.initMidtransSnap();
                    };
                    script.onerror = function(error) {
                        console.error('Error loading Snap.js:', error);
                        $scope.showToast('error', 'Failed to load payment gateway. Please refresh the page.');
                        $scope.payment.loading = false;
                        safeApply();
                    };
                    document.body.appendChild(script);
                }
                
                return response;
            } else {
                console.error('Invalid response format:', response);
                return $q.reject('Invalid response from payment service');
            }
        })
        .catch(function(error) {
            console.error('Error getting payment token:', error);
            $scope.showToast('error', 'Failed to initialize payment. Please try again.');
            $scope.payment.loading = false;
            return $q.reject(error);
        });
    };
    
    // Initialize Midtrans Snap
    $scope.initMidtransSnap = function() {
        console.log('Initializing Midtrans Snap...');
        $scope.payment.loading = false;
        
        // Check if Snap.js is already loaded
        if (typeof snap === 'undefined') {
            console.log('Snap.js is not loaded, loading it now...');
            // Load Snap.js dynamically
            var script = document.createElement('script');
            
            // Try to get the snap URL from a meta attribute if available
            var snapUrl = document.querySelector('meta[name="midtrans-snap-url"]');
            script.src = snapUrl ? snapUrl.getAttribute('content') : 'https://app.sandbox.midtrans.com/snap/snap.js';
            
            console.log('Loading Midtrans Snap from: ' + script.src);
            
            // Use the client key from the payment response if available
            var clientKey = $scope.payment.clientKey || '';
            
            if (clientKey) {
                script.setAttribute('data-client-key', clientKey);
                console.log('Setting client key:', clientKey);
            }
            
            script.onload = function() {
                console.log('Snap.js loaded successfully');
                safeApply();
            };
            script.onerror = function(error) {
                console.error('Error loading Snap.js:', error);
                $scope.showToast('error', 'Failed to load payment gateway. Please refresh the page.');
                safeApply();
            };
            document.body.appendChild(script);
        } else {
            console.log('Snap.js is already loaded');
        }
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
        
        // Store order summary for payment page
        localStorage.setItem('checkout_summary', JSON.stringify($scope.orderSummary));
        localStorage.setItem('checkout_shipping', JSON.stringify(shippingData));
        
        // Navigate to payment page
        $location.path('/checkout/payment');
    };

    // Process payment
    $scope.processPayment = function() {
        $scope.payment.loading = true;
        
        console.log('Processing payment...');
        
        // Check if we have a snap token
        if (!$scope.payment.snapToken) {
            console.log('No payment token available, getting a new one...');
            $scope.showToast('info', 'Initializing payment gateway...');
            
            // Get a new payment token
            $scope.getPaymentToken()
                .then(function(response) {
                    console.log('Successfully retrieved payment token');
                    // Open the Snap payment window
                    $timeout(function() {
                        $scope.openSnapPayment();
                    }, 1000);
                })
                .catch(function(error) {
                    console.error('Failed to get payment token:', error);
                    $scope.showToast('error', 'Failed to initialize payment. Please try again.');
                    $scope.payment.loading = false;
                });
            return;
        }
        
        // We have a token, open the Snap payment window
        $scope.openSnapPayment();
    };
    
    // Open Snap payment window
    $scope.openSnapPayment = function() {
        console.log('Opening Snap payment with token:', $scope.payment.snapToken);
        
        try {
            // Make sure snap is defined
            if (typeof snap === 'undefined') {
                console.error('Snap.js is not loaded properly');
                $scope.showToast('error', 'Payment gateway is not initialized. Please refresh the page.');
                $scope.payment.loading = false;
                return;
            }
            
            // Open Midtrans Snap payment page
            snap.pay($scope.payment.snapToken, {
                onSuccess: function(result) {
                    console.log('Payment success:', result);
                    // Save payment details to localStorage for confirmation page
                    localStorage.setItem('payment_details', JSON.stringify({
                        orderNumber: $scope.payment.orderNumber,
                        transactionId: result.transaction_id,
                        transactionStatus: result.transaction_status,
                        paymentType: result.payment_type
                    }));
                    $scope.handlePaymentResult(result, 'success');
                },
                onPending: function(result) {
                    console.log('Payment pending:', result);
                    // Save payment details to localStorage for confirmation page
                    localStorage.setItem('payment_details', JSON.stringify({
                        orderNumber: $scope.payment.orderNumber,
                        transactionId: result.transaction_id,
                        transactionStatus: result.transaction_status,
                        paymentType: result.payment_type
                    }));
                    $scope.handlePaymentResult(result, 'pending');
                },
                onError: function(result) {
                    console.error('Payment failed:', result);
                    if (result && result.transaction_id) {
                        // Save payment details to localStorage for confirmation page
                        localStorage.setItem('payment_details', JSON.stringify({
                            orderNumber: $scope.payment.orderNumber,
                            transactionId: result.transaction_id,
                            transactionStatus: result.transaction_status || 'error',
                            paymentType: result.payment_type
                        }));
                    }
                    $scope.handlePaymentResult(result, 'error');
                },
                onClose: function() {
                    console.log('Customer closed the payment popup without completing payment');
                    $scope.$apply(function() {
                        $scope.payment.loading = false;
                    });
                },
                language: "en"
            });
        } catch (error) {
            console.error('Error opening Snap payment:', error);
            $scope.showToast('error', 'Failed to open payment window. Please refresh and try again.');
            $scope.payment.loading = false;
            
            // Try to get a new token for next attempt
            $scope.getPaymentToken().catch(function(err) {
                console.error('Failed to get new token after payment error:', err);
            });
        }
    };
    
    // Handle payment result from Midtrans
    $scope.handlePaymentResult = function(result, status) {
        console.log('Payment result received:', result);
        $scope.payment.transactionId = result.transaction_id;
        $scope.payment.transactionStatus = result.transaction_status;
        
        // First, verify payment status with Midtrans
        PaymentService.verifyPaymentStatus($scope.payment.orderNumber, result.transaction_id)
            .then(function(verificationResponse) {
                console.log('Payment verification response:', verificationResponse);
                
                if (verificationResponse.verified) {
                    // Payment is verified, proceed with processing
                    processVerifiedPayment(result);
                } else {
                    // Payment verification failed, but we'll still try to process it
                    console.warn('Payment verification pending, attempting to process anyway');
                    $scope.showToast('warning', 'Payment verification pending. Processing payment...');
                    processVerifiedPayment(result);
                }
            })
            .catch(function(error) {
                console.error('Error verifying payment:', error);
                // Verification failed, but we'll still try to process the payment
                $scope.showToast('warning', 'Payment verification failed. Attempting to process payment...');
                processVerifiedPayment(result);
            });
    };
    
    // Process payment after verification
    function processVerifiedPayment(result) {
        // Update payment status in UI
        $scope.payment.transactionStatus = result.transaction_status;
        $scope.payment.transactionId = result.transaction_id;
        
        // Process payment with our backend
        PaymentService.processPayment({
            order_number: $scope.payment.orderNumber,
            transaction_id: result.transaction_id,
            transaction_status: result.transaction_status,
            payment_type: result.payment_type,
            shipping_info: $scope.shipping,
            order_summary: $scope.orderSummary
        })
        .then(function(response) {
            $scope.payment.loading = false;
            
            // Clear cart
            CartService.clearCart();
            
            // Store order data if available
            if (response && response.order) {
                $scope.order = response.order;
                
                // Automatically redirect to confirmation page after 1 second
                $timeout(function() {
                    $scope.proceedToConfirmation();
                }, 1000);
            } else {
                // If we don't have the order data, just redirect
                $timeout(function() {
                    $scope.proceedToConfirmation();
                }, 1000);
            }
            
            // Apply changes to UI
            safeApply();
            
            // Show success message
            $scope.showToast('success', 'Payment successful! Redirecting to confirmation...');
        })
        .catch(function(error) {
            console.error('Error processing payment:', error);
            $scope.showToast('error', 'Failed to process payment. Please try again.');
            $scope.payment.loading = false;
            safeApply();
        });
    }

    // Show toast notification
    $scope.showToast = function(type, message) {
        $scope.toast = {
            show: true,
            type: type,
            message: message
        };
        
        // Hide toast after 3 seconds
        $timeout(function() {
            $scope.toast.show = false;
        }, 3000);
    };

    // Continue shopping
    $scope.continueShopping = function() {
        $location.path('/products');
    };

    // Proceed to confirmation page
    $scope.proceedToConfirmation = function() {
        // Clear checkout data
        localStorage.removeItem('checkout_shipping');
        localStorage.removeItem('checkout_summary');
        
        // Navigate to confirmation page
        if ($scope.order && $scope.order.id) {
            // If we have an order ID, navigate to specific order confirmation
            $location.path('/checkout/confirmation/' + $scope.order.id);
        } else if ($scope.payment.orderNumber) {
            // If we don't have order ID but have order number, try to use that
            $location.path('/checkout/confirmation').search({order_number: $scope.payment.orderNumber});
        } else {
            // Otherwise navigate to generic confirmation
            $location.path('/checkout/confirmation');
        }
    };

    // Load order by ID
    $scope.loadOrderById = function(orderId) {
        $scope.loading = true;
        $http.get('/api/orders/' + orderId, {
            headers: getAuthHeaders()
        })
        .then(function(response) {
            $scope.order = response.data;
            $scope.genericConfirmation = false;
            $scope.loading = false;
            safeApply();
        })
        .catch(function(error) {
            console.error('Error loading order by ID:', error);
            $scope.genericConfirmation = true;
            $scope.loading = false;
            $scope.showToast('error', 'Failed to load order details');
            safeApply();
        });
    };
    
    // Load order by order number
    $scope.loadOrderByNumber = function(orderNumber) {
        $scope.loading = true;
        $http.get('/api/orders', {
            params: { order_number: orderNumber },
            headers: getAuthHeaders()
        })
        .then(function(response) {
            if (response.data && response.data.data && response.data.data.length > 0) {
                // Get the first order that matches
                $scope.order = response.data.data[0];
                $scope.genericConfirmation = false;
            } else {
                $scope.genericConfirmation = true;
            }
            $scope.loading = false;
            safeApply();
        })
        .catch(function(error) {
            console.error('Error loading order by number:', error);
            $scope.genericConfirmation = true;
            $scope.loading = false;
            $scope.showToast('error', 'Failed to load order details');
            safeApply();
        });
    };

    // Initialize controller
    $scope.init();
}]); 