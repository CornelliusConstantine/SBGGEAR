'use strict';

app.controller('CartController', ['$scope', '$location', 'CartService', function($scope, $location, CartService) {
    // Initialize controller
    $scope.init = function() {
        $scope.loading = true;
        $scope.cart = {
            items: [],
            total: 0
        };
        
        // Load cart
        $scope.loadCart();
    };
    
    // Load cart
    $scope.loadCart = function() {
        $scope.loading = true;
        
        CartService.getCart()
            .then(function(cart) {
                $scope.cart = cart;
            })
            .catch(function(error) {
                console.error('Error loading cart', error);
            })
            .finally(function() {
                $scope.loading = false;
            });
    };
    
    // Update item quantity
    $scope.updateQuantity = function(item, amount) {
        var newQuantity = item.quantity + amount;
        
        // Ensure quantity is at least 1
        if (newQuantity < 1) {
            return;
        }
        
        $scope.updatingItem = item.product_id;
        
        CartService.updateQuantity(item.product_id, newQuantity)
            .then(function(cart) {
                $scope.cart = cart;
            })
            .catch(function(error) {
                console.error('Error updating quantity', error);
                $scope.showToast('Failed to update quantity', 'error');
            })
            .finally(function() {
                $scope.updatingItem = null;
            });
    };
    
    // Remove item from cart
    $scope.removeItem = function(item) {
        $scope.removingItem = item.product_id;
        
        CartService.removeFromCart(item.product_id)
            .then(function(cart) {
                $scope.cart = cart;
                $scope.showToast('Item removed from cart');
            })
            .catch(function(error) {
                console.error('Error removing item from cart', error);
                $scope.showToast('Failed to remove item from cart', 'error');
            })
            .finally(function() {
                $scope.removingItem = null;
            });
    };
    
    // Clear cart
    $scope.clearCart = function() {
        $scope.clearingCart = true;
        
        CartService.clearCart()
            .then(function(cart) {
                $scope.cart = cart;
                $scope.showToast('Cart cleared');
            })
            .catch(function(error) {
                console.error('Error clearing cart', error);
                $scope.showToast('Failed to clear cart', 'error');
            })
            .finally(function() {
                $scope.clearingCart = false;
            });
    };
    
    // Proceed to checkout
    $scope.checkout = function() {
        $location.path('/checkout');
    };
    
    // Continue shopping
    $scope.continueShopping = function() {
        $location.path('/products');
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