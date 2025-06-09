'use strict';

app.controller('CartController', ['$scope', '$location', 'CartService', '$timeout', function($scope, $location, CartService, $timeout) {
    // Initialize controller
    $scope.init = function() {
        $scope.loading = true;
        $scope.cart = {
            items: [],
            total: 0
        };
        
        // Initialize CartService if needed
        if (!$scope.cartInitialized) {
            CartService.init();
            $scope.cartInitialized = true;
        }
        
        // Load cart
        $scope.loadCart();
        
        // Ensure cart total is calculated after view is rendered
        $timeout(function() {
            if ($scope.cart && $scope.cart.items && $scope.cart.items.length > 0) {
                $scope.cart.total = $scope.calculateCartTotal();
                console.log('Cart total initialized on timeout:', $scope.cart.total);
            }
        }, 500);
    };
    
    // Load cart
    $scope.loadCart = function() {
        $scope.loading = true;
        
        CartService.getCart()
            .then(function(cart) {
                $scope.cart = cart;
                console.log('Cart loaded:', cart);
                
                // Ensure total is calculated properly
                if (!$scope.cart.total && $scope.cart.items && $scope.cart.items.length > 0) {
                    $scope.cart.total = $scope.calculateCartTotal();
                    console.log('Total recalculated:', $scope.cart.total);
                }
            })
            .catch(function(error) {
                console.error('Error loading cart', error);
            })
            .finally(function() {
                $scope.loading = false;
            });
    };
    
    // Calculate cart total
    $scope.calculateCartTotal = function() {
        if (!$scope.cart || !$scope.cart.items || $scope.cart.items.length === 0) {
            return 0;
        }
        
        return $scope.cart.items.reduce(function(total, item) {
            var itemPrice = parseFloat(item.price) || 0;
            var itemQuantity = parseInt(item.quantity) || 0;
            return total + (itemPrice * itemQuantity);
        }, 0);
    };
    
    // Get cart total (safe method that always returns a number)
    $scope.getCartTotal = function() {
        if ($scope.cart && typeof $scope.cart.total !== 'undefined' && $scope.cart.total !== null) {
            return $scope.cart.total;
        }
        return $scope.calculateCartTotal();
    };
    
    // Update item quantity
    $scope.updateQuantity = function(item, amount) {
        var newQuantity = item.quantity + amount;
        
        // Ensure quantity is at least 1
        if (newQuantity < 1) {
            return;
        }
        
        // Ensure quantity doesn't exceed stock
        if (item.product && item.product.stock && newQuantity > item.product.stock) {
            $scope.showToast('Sorry, only ' + item.product.stock + ' items available', 'error');
            return;
        }
        
        $scope.updatingItem = item.product_id;
        console.log('Updating quantity for product ID:', item.product_id, 'New quantity:', newQuantity);
        
        CartService.updateQuantity(item.product_id, newQuantity)
            .then(function(cart) {
                console.log('Cart updated successfully:', cart);
                $scope.cart = cart;
                $scope.showToast('Cart updated successfully');
            })
            .catch(function(error) {
                console.error('Error updating quantity:', error);
                
                // Handle different error types
                if (error && error.message) {
                    $scope.showToast(error.message, 'error');
                } else if (error && error.status === 401) {
                    $scope.showToast('Please login to update your cart', 'error');
                    localStorage.removeItem('token'); // Clear invalid token
                    window.location.href = '#!/login';
                } else if (error && error.status === 422) {
                    $scope.showToast('Insufficient stock available', 'error');
                } else if (error && error.status === 404) {
                    // Handle 404 error specifically
                    console.log('Item not found on server, updating locally');
                    // Update item locally
                    item.quantity = newQuantity;
                    $scope.cart.total = $scope.calculateCartTotal();
                    $scope.showToast('Cart updated locally', 'success');
                } else {
                    $scope.showToast('Failed to update quantity', 'error');
                }
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
        // Display maintenance message
        $scope.showToast('Checkout feature is currently under maintenance', 'info');
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