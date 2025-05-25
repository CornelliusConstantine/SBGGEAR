'use strict';

app.controller('OrderController', ['$scope', '$routeParams', '$location', 'OrderService', function($scope, $routeParams, $location, OrderService) {
    // Initialize controller
    $scope.init = function() {
        $scope.loading = true;
        $scope.orders = [];
        $scope.order = null;
        
        // Check if we're on a specific order page
        if ($routeParams.id) {
            $scope.loadOrder($routeParams.id);
        } else {
            $scope.loadOrders();
        }
    };
    
    // Load all orders
    $scope.loadOrders = function() {
        $scope.loading = true;
        
        OrderService.getOrders()
            .then(function(response) {
                $scope.orders = response.data;
            })
            .catch(function(error) {
                console.error('Error loading orders', error);
            })
            .finally(function() {
                $scope.loading = false;
            });
    };
    
    // Load specific order
    $scope.loadOrder = function(orderId) {
        $scope.loading = true;
        
        OrderService.getOrder(orderId)
            .then(function(response) {
                $scope.order = response.data;
            })
            .catch(function(error) {
                console.error('Error loading order', error);
            })
            .finally(function() {
                $scope.loading = false;
            });
    };
    
    // Check payment status
    $scope.checkPaymentStatus = function(orderId) {
        $scope.checkingPayment = true;
        
        OrderService.checkPaymentStatus(orderId)
            .then(function(response) {
                // Update order status
                $scope.order.status = response.data.status;
                $scope.order.payment_status = response.data.payment_status;
                
                $scope.showToast('Payment status updated');
            })
            .catch(function(error) {
                console.error('Error checking payment status', error);
                $scope.showToast('Failed to check payment status', 'error');
            })
            .finally(function() {
                $scope.checkingPayment = false;
            });
    };
    
    // Get status class
    $scope.getStatusClass = function(status) {
        switch (status) {
            case 'pending':
                return 'text-warning';
            case 'processing':
                return 'text-primary';
            case 'shipped':
                return 'text-info';
            case 'delivered':
                return 'text-success';
            case 'cancelled':
                return 'text-danger';
            default:
                return 'text-secondary';
        }
    };
    
    // Get payment status class
    $scope.getPaymentStatusClass = function(status) {
        switch (status) {
            case 'paid':
                return 'text-success';
            case 'pending':
                return 'text-warning';
            case 'failed':
                return 'text-danger';
            default:
                return 'text-secondary';
        }
    };
    
    // Format date
    $scope.formatDate = function(dateString) {
        if (!dateString) return '';
        
        var date = new Date(dateString);
        return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    };
    
    // View order details
    $scope.viewOrder = function(orderId) {
        $location.path('/order/' + orderId);
    };
    
    // Back to orders list
    $scope.backToOrders = function() {
        $location.path('/orders');
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