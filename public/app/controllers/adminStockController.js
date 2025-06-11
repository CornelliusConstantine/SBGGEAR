'use strict';

app.controller('AdminStockController', ['$scope', '$http', '$uibModal', function($scope, $http, $uibModal) {
    // Initialize controller
    $scope.init = function() {
        $scope.loading = true;
        $scope.loadingHistory = true;
        $scope.products = [];
        $scope.stockHistory = [];
        
        // Load products with stock information
        $scope.loadProducts();
        
        // Load stock history
        $scope.loadStockHistory();
    };
    
    // Load products with stock information
    $scope.loadProducts = function() {
        $scope.loading = true;
        
        $http.get('/api/admin/stock')
            .then(function(response) {
                $scope.products = response.data;
            })
            .catch(function(error) {
                console.error('Error loading products', error);
                
                // Show error message
                $scope.showError('Failed to load products. Please try again later.');
            })
            .finally(function() {
                $scope.loading = false;
            });
    };
    
    // Load stock history
    $scope.loadStockHistory = function() {
        $scope.loadingHistory = true;
        
        $http.get('/api/admin/stock/history')
            .then(function(response) {
                $scope.stockHistory = response.data;
            })
            .catch(function(error) {
                console.error('Error loading stock history', error);
                
                // Show error message
                $scope.showError('Failed to load stock history. Please try again later.');
            })
            .finally(function() {
                $scope.loadingHistory = false;
            });
    };
    
    // Open stock modal for adding or reducing stock
    $scope.openStockModal = function(product, type) {
        var modalInstance = $uibModal.open({
            templateUrl: 'stockModal.html',
            controller: 'StockModalController',
            resolve: {
                product: function() {
                    return product;
                },
                modalType: function() {
                    return type;
                }
            }
        });
        
        modalInstance.result.then(function(result) {
            // Call API to update stock
            var endpoint = '/api/admin/stock/' + product.id + '/' + (result.type === 'add' ? 'add' : 'reduce');
            
            $http.post(endpoint, {
                quantity: result.quantity,
                notes: result.notes
            })
                .then(function(response) {
                    // Update product stock in the list
                    if (result.type === 'add') {
                        product.stock += result.quantity;
                    } else {
                        product.stock -= result.quantity;
                    }
                    
                    // Refresh stock history
                    $scope.loadStockHistory();
                    
                    // Show success message
                    $scope.showSuccess('Stock updated successfully.');
                })
                .catch(function(error) {
                    console.error('Error updating stock', error);
                    
                    // Show error message
                    $scope.showError('Failed to update stock. Please try again later.');
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

// Stock Modal Controller
app.controller('StockModalController', ['$scope', '$uibModalInstance', 'product', 'modalType', function($scope, $uibModalInstance, product, modalType) {
    $scope.product = product;
    $scope.modalType = modalType;
    $scope.quantity = 1;
    $scope.notes = '';
    
    $scope.save = function() {
        $uibModalInstance.close({
            type: $scope.modalType,
            quantity: $scope.quantity,
            notes: $scope.notes
        });
    };
    
    $scope.cancel = function() {
        $uibModalInstance.dismiss('cancel');
    };
}]); 