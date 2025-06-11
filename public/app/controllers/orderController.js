'use strict';

app.controller('OrderController', ['$scope', '$routeParams', '$location', '$timeout', 'OrderService', function($scope, $routeParams, $location, $timeout, OrderService) {
    // Initialize controller
    $scope.init = function() {
        $scope.loading = true;
        $scope.orders = [];
        $scope.order = null;
        $scope.currentFilter = 'all';
        $scope.filteredOrders = [];
        $scope.checkingPayment = null;
        $scope.currentPage = 1;
        $scope.toast = {
            show: false,
            type: 'success',
            message: ''
        };
        
        console.log('OrderController initialized');
        
        // Check if we're on a specific order page
        if ($routeParams.id) {
            console.log('Loading specific order:', $routeParams.id);
            $scope.loadOrder($routeParams.id);
        } else {
            console.log('Loading all orders');
            $scope.loadOrders();
        }
    };
    
    // Load all orders
    $scope.loadOrders = function(page) {
        $scope.loading = true;
        console.log('loadOrders called, page:', page);
        
        OrderService.getOrders(page)
            .then(function(response) {
                console.log('Orders loaded successfully:', response);
                
                // Handle different response formats
                if (response && response.data) {
                    $scope.orders = response.data;
                    
                    // Check if there's a debug message
                    if (response.data.message) {
                        console.log('Debug message from API:', response.data.message);
                        console.log('Debug info:', response.data.debug_info);
                    }
                } else if (Array.isArray(response)) {
                    // If the API returns an array directly
                    $scope.orders = {
                        data: response,
                        current_page: 1,
                        last_page: 1,
                        total: response.length
                    };
                    console.log('Converted array response to paginated format:', $scope.orders);
                } else {
                    // If the API returns the paginated data directly
                    $scope.orders = response;
                    console.log('Using response directly as orders data:', $scope.orders);
                    
                    // Check if there's a debug message
                    if (response.message) {
                        console.log('Debug message from API:', response.message);
                        console.log('Debug info:', response.debug_info);
                    }
                }
                
                // Show a message if no orders found
                if ((!$scope.orders.data || $scope.orders.data.length === 0) && $scope.orders.message) {
                    $scope.showToast($scope.orders.message, 'warning');
                }
                
                $scope.filterOrders($scope.currentFilter);
            })
            .catch(function(error) {
                console.error('Error loading orders', error);
                $scope.showToast('Failed to load orders. Please try again.', 'error');
            })
            .finally(function() {
                $scope.loading = false;
            });
    };
    
    // Load specific order
    $scope.loadOrder = function(orderId) {
        $scope.loading = true;
        console.log('loadOrder called, orderId:', orderId);
        
        OrderService.getOrder(orderId)
            .then(function(response) {
                console.log('Order loaded successfully:', response);
                $scope.order = response.data;
            })
            .catch(function(error) {
                console.error('Error loading order', error);
                $scope.showToast('Failed to load order details. Please try again.', 'error');
            })
            .finally(function() {
                $scope.loading = false;
            });
    };
    
    // Filter orders by status
    $scope.filterOrders = function(status) {
        $scope.currentFilter = status;
        console.log('Filtering orders by status:', status);
        
        // Check if orders data exists in different formats
        if (!$scope.orders) {
            console.log('No orders object available');
            $scope.filteredOrders = [];
            return;
        }
        
        // Handle different data structures
        var ordersData = null;
        
        if (Array.isArray($scope.orders)) {
            console.log('Orders is an array');
            ordersData = $scope.orders;
        } else if ($scope.orders.data && Array.isArray($scope.orders.data)) {
            console.log('Orders has data array property');
            ordersData = $scope.orders.data;
        } else {
            console.log('Orders has unknown format:', $scope.orders);
            $scope.filteredOrders = [];
            return;
        }
        
        console.log('Orders data for filtering:', ordersData.length, 'items');
        
        if (status === 'all') {
            $scope.filteredOrders = ordersData;
        } else {
            $scope.filteredOrders = ordersData.filter(function(order) {
                return order.status === status;
            });
        }
        
        console.log('Filtered orders:', $scope.filteredOrders.length);
    };
    
    // Change page
    $scope.changePage = function(page) {
        if (page < 1 || page > $scope.orders.last_page) {
            return;
        }
        
        $scope.currentPage = page;
        $scope.loadOrders(page);
    };
    
    // Get array of page numbers for pagination
    $scope.getPages = function(totalPages) {
        var pages = [];
        var maxPages = 5; // Show max 5 page numbers
        
        if (totalPages <= maxPages) {
            // If total pages is less than max, show all pages
            for (var i = 1; i <= totalPages; i++) {
                pages.push(i);
            }
        } else {
            // Calculate which pages to show
            var startPage = Math.max(1, $scope.currentPage - 2);
            var endPage = Math.min(totalPages, startPage + maxPages - 1);
            
            // Adjust if we're near the end
            if (endPage - startPage < maxPages - 1) {
                startPage = Math.max(1, endPage - maxPages + 1);
            }
            
            for (var i = startPage; i <= endPage; i++) {
                pages.push(i);
            }
        }
        
        return pages;
    };
    
    // Calculate total items in an order
    $scope.getTotalItems = function(items) {
        if (!items || !items.length) return 0;
        
        return items.reduce(function(total, item) {
            return total + (item.quantity || 0);
        }, 0);
    };
    
    // Check payment status
    $scope.checkPaymentStatus = function(orderId) {
        $scope.checkingPayment = orderId;
        
        OrderService.checkPaymentStatus(orderId)
            .then(function(response) {
                // Find the order in the list and update its status
                var orderIndex = $scope.orders.data.findIndex(function(o) {
                    return o.id === orderId;
                });
                
                if (orderIndex !== -1) {
                    $scope.orders.data[orderIndex].status = response.data.status;
                    $scope.orders.data[orderIndex].payment_status = response.data.payment_status;
                    
                    // Re-apply filter
                    $scope.filterOrders($scope.currentFilter);
                }
                
                $scope.showToast('Payment status updated successfully', 'success');
            })
            .catch(function(error) {
                console.error('Error checking payment status', error);
                $scope.showToast('Failed to check payment status', 'error');
            })
            .finally(function() {
                $scope.checkingPayment = null;
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
        
        // Format: "Jan 1, 2023 at 12:00 PM"
        var options = { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric',
            hour: '2-digit', 
            minute: '2-digit'
        };
        
        return date.toLocaleDateString('en-US', options);
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
        $timeout(function() {
            $scope.toast.show = false;
        }, 3000);
    };
    
    // Debug orders
    $scope.debugOrders = function() {
        console.log('Debugging orders...');
        
        OrderService.debugOrders()
            .then(function(response) {
                console.log('Debug orders result:', response);
                
                // Show a toast with the debug information
                var message = 'User ID: ' + response.user_id + 
                    ', Direct orders: ' + response.direct_orders_count + 
                    ', Eloquent orders: ' + response.eloquent_orders_count;
                
                $scope.showToast(message, 'info');
                
                // If no orders found but there should be, try to fix it
                if (response.direct_orders_count === 0 && response.eloquent_orders_count === 0) {
                    $scope.showToast('No orders found for this user. Try refreshing the page.', 'warning');
                } else if (response.direct_orders_count !== response.eloquent_orders_count) {
                    $scope.showToast('Order count mismatch. Try refreshing the page.', 'warning');
                } else if (response.direct_orders_count > 0) {
                    // We have orders but they're not showing up
                    $scope.showToast('Orders found but not displaying. Try refreshing the page.', 'warning');
                    $scope.loadOrders(); // Try loading orders again
                }
            })
            .catch(function(error) {
                console.error('Debug orders error:', error);
                $scope.showToast('Error debugging orders', 'error');
            });
    };
    
    // Initialize controller
    $scope.init();
}]); 