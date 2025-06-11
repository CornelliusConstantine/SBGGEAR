'use strict';

app.controller('AdminOrderController', ['$scope', '$http', '$routeParams', '$location', '$uibModal', function($scope, $http, $routeParams, $location, $uibModal) {
    // Initialize controller
    $scope.init = function() {
        $scope.loading = true;
        $scope.orders = [];
        $scope.order = null;
        $scope.statusOptions = [
            'pending',
            'processing',
            'shipped',
            'delivered',
            'cancelled'
        ];
        
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
        
        $http.get('/api/admin/orders')
            .then(function(response) {
                $scope.orders = response.data;
            })
            .catch(function(error) {
                console.error('Error loading orders', error);
                
                // Show error message
                $scope.showError('Failed to load orders. Please try again later.');
            })
            .finally(function() {
                $scope.loading = false;
            });
    };
    
    // Load specific order
    $scope.loadOrder = function(orderId) {
        $scope.loading = true;
        
        $http.get('/api/admin/orders/' + orderId)
            .then(function(response) {
                $scope.order = response.data;
            })
            .catch(function(error) {
                console.error('Error loading order', error);
                
                // Show error message
                $scope.showError('Failed to load order details. Please try again later.');
                
                // Redirect back to orders list
                $location.path('/admin/orders');
            })
            .finally(function() {
                $scope.loading = false;
            });
    };
    
    // Update order status
    $scope.updateStatus = function(status) {
        if (!$scope.order || $scope.order.status === status) {
            return;
        }
        
        $http.put('/api/admin/orders/' + $scope.order.id + '/status', {
            status: status
        })
            .then(function(response) {
                // Update local order data
                $scope.order.status = status;
                
                // Show success message
                $scope.showSuccess('Order status updated successfully.');
            })
            .catch(function(error) {
                console.error('Error updating order status', error);
                
                // Show error message
                $scope.showError('Failed to update order status. Please try again later.');
            });
    };
    
    // Add tracking number
    $scope.addTrackingNumber = function() {
        var modalInstance = $uibModal.open({
            templateUrl: 'trackingNumberModal.html',
            controller: 'TrackingNumberModalController',
            resolve: {
                order: function() {
                    return $scope.order;
                }
            }
        });
        
        modalInstance.result.then(function(trackingNumber) {
            $http.put('/api/admin/orders/' + $scope.order.id + '/tracking', {
                tracking_number: trackingNumber
            })
                .then(function(response) {
                    // Update local order data
                    $scope.order.tracking_number = trackingNumber;
                    
                    // Show success message
                    $scope.showSuccess('Tracking number added successfully.');
                })
                .catch(function(error) {
                    console.error('Error adding tracking number', error);
                    
                    // Show error message
                    $scope.showError('Failed to add tracking number. Please try again later.');
                });
        });
    };
    
    // Show success message
    $scope.showSuccess = function(message) {
        $scope.alertMessage = message;
        $scope.alertType = 'success';
        $scope.showAlert = true;
        
        // Hide alert after 5 seconds
        setTimeout(function() {
            $scope.$apply(function() {
                $scope.showAlert = false;
            });
        }, 5000);
    };
    
    // Show error message
    $scope.showError = function(message) {
        $scope.alertMessage = message;
        $scope.alertType = 'danger';
        $scope.showAlert = true;
        
        // Hide alert after 5 seconds
        setTimeout(function() {
            $scope.$apply(function() {
                $scope.showAlert = false;
            });
        }, 5000);
    };
}]);

// Tracking Number Modal Controller
app.controller('TrackingNumberModalController', ['$scope', '$uibModalInstance', 'order', function($scope, $uibModalInstance, order) {
    $scope.order = order;
    $scope.trackingNumber = order.tracking_number || '';
    
    $scope.save = function() {
        $uibModalInstance.close($scope.trackingNumber);
    };
    
    $scope.cancel = function() {
        $uibModalInstance.dismiss('cancel');
    };
}]); 