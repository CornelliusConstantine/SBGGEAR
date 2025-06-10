'use strict';

app.controller('ConfirmationController', ['$scope', '$routeParams', '$location', '$timeout', 'OrderService', 'PaymentService', function($scope, $routeParams, $location, $timeout, OrderService, PaymentService) {
    // Initialize controller
    $scope.init = function() {
        $scope.loading = true;
        $scope.order = null;
        $scope.toast = {
            show: false,
            type: 'success',
            message: ''
        };
        
        // Check if we have transaction details in localStorage
        const paymentDetails = localStorage.getItem('payment_details');
        
        // Check if we have an order ID
        if ($routeParams.id) {
            // Load order details
            $scope.loadOrder($routeParams.id);
        } else if (paymentDetails) {
            // We have payment details but no order ID, try to verify payment
            const details = JSON.parse(paymentDetails);
            $scope.verifyPayment(details.orderNumber, details.transactionId);
        } else {
            // No order ID or payment details provided, show generic confirmation
            $scope.loading = false;
            $scope.genericConfirmation = true;
        }
    };
    
    // Load order details
    $scope.loadOrder = function(orderId) {
        OrderService.getOrder(orderId)
            .then(function(response) {
                $scope.order = response.data;
            })
            .catch(function(error) {
                console.error('Error loading order:', error);
                $scope.showToast('error', 'Failed to load order details.');
                $scope.genericConfirmation = true;
            })
            .finally(function() {
                $scope.loading = false;
            });
    };
    
    // Verify payment status
    $scope.verifyPayment = function(orderNumber, transactionId) {
        if (!orderNumber || !transactionId) {
            $scope.loading = false;
            $scope.genericConfirmation = true;
            return;
        }
        
        PaymentService.verifyPaymentStatus(orderNumber, transactionId)
            .then(function(response) {
                console.log('Payment verification response:', response);
                
                if (response.verified && response.status === 'paid' && response.order_id) {
                    // We have a confirmed payment with an order ID
                    $scope.loadOrder(response.order_id);
                } else {
                    // Payment verified but no order ID or not paid
                    $scope.paymentStatus = response.status;
                    $scope.transactionStatus = response.transaction_status;
                    $scope.genericConfirmation = true;
                    $scope.loading = false;
                    
                    if (response.status === 'pending') {
                        $scope.showToast('warning', 'Your payment is still being processed. Please check your order status later.');
                    } else if (response.status === 'failed') {
                        $scope.showToast('error', 'Payment failed. Please try again.');
                    }
                }
            })
            .catch(function(error) {
                console.error('Error verifying payment:', error);
                $scope.genericConfirmation = true;
                $scope.loading = false;
                $scope.showToast('error', 'Could not verify payment status.');
            })
            .finally(function() {
                // Clear payment details from localStorage
                localStorage.removeItem('payment_details');
            });
    };
    
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
    
    // View all orders
    $scope.viewOrders = function() {
        $location.path('/orders');
    };
    
    // Initialize controller
    $scope.init();
}]); 