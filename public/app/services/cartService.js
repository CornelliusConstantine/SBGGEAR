'use strict';

app.service('CartService', ['$http', '$q', '$rootScope', function($http, $q, $rootScope) {
    var service = {};
    var API_URL = '/api';
    var cart = {
        items: [],
        total: 0
    };
    
    // Initialize cart
    service.init = function() {
        var token = localStorage.getItem('token');
        
        if (token) {
            // If user is logged in, get cart from server
            service.getCart();
        } else {
            // Otherwise, try to get cart from localStorage
            var localCart = localStorage.getItem('cart');
            if (localCart) {
                try {
                    cart = JSON.parse(localCart);
                    $rootScope.cartItemCount = service.getItemCount();
                } catch (e) {
                    console.error('Error parsing cart from localStorage', e);
                    cart = { items: [], total: 0 };
                }
            }
        }
    };
    
    // Save cart to localStorage
    service.saveCart = function() {
        localStorage.setItem('cart', JSON.stringify(cart));
        $rootScope.cartItemCount = service.getItemCount();
    };
    
    // Get cart from server
    service.getCart = function() {
        var deferred = $q.defer();
        var token = localStorage.getItem('token');
        
        if (!token) {
            console.log('No token found, using local cart');
            deferred.resolve(cart);
            return deferred.promise;
        }
        
        console.log('Fetching cart from server');
        $http.get(API_URL + '/cart', {
            headers: {
                'Authorization': 'Bearer ' + token
            }
        })
            .then(function(response) {
                console.log('Cart fetched successfully:', response.data);
                cart = response.data;
                
                // Ensure cart has items array
                if (!cart.items) {
                    cart.items = [];
                }
                
                // Calculate total if not provided
                if (typeof cart.total === 'undefined' || cart.total === null) {
                    service.calculateTotal();
                } else if (cart.total_amount && !cart.total) {
                    // If total_amount exists but total doesn't, use total_amount
                    cart.total = cart.total_amount;
                }
                
                $rootScope.cartItemCount = service.getItemCount();
                deferred.resolve(cart);
            })
            .catch(function(error) {
                console.error('Error fetching cart:', error);
                
                if (error.status === 401) {
                    // Clear token if unauthorized
                    localStorage.removeItem('token');
                    $rootScope.isLoggedIn = false;
                    
                    // Use local cart
                    deferred.resolve(cart);
                } else {
                    // For other errors, still use local cart but reject the promise
                    deferred.reject(error.data);
                }
            });
        
        return deferred.promise;
    };
    
    // Add item to cart
    service.addToCart = function(product, quantity) {
        var deferred = $q.defer();
        var token = localStorage.getItem('token');
        
        // Validate quantity
        if (!quantity || quantity < 1) {
            quantity = 1;
        }
        
        // Check if product has stock
        if (product.stock !== undefined && product.stock <= 0) {
            deferred.reject({ message: 'This product is out of stock' });
            return deferred.promise;
        }
        
        // Check if quantity exceeds stock
        if (product.stock !== undefined && quantity > product.stock) {
            deferred.reject({ message: 'Only ' + product.stock + ' items available in stock' });
            return deferred.promise;
        }
        
        if (token) {
            // If user is logged in, add to server cart
            $http.post(API_URL + '/cart', {
                product_id: product.id,
                quantity: quantity
            }, {
                headers: {
                    'Authorization': 'Bearer ' + token
                }
            })
                .then(function(response) {
                    cart = response.data.cart || response.data;
                    $rootScope.cartItemCount = service.getItemCount();
                    deferred.resolve(cart);
                })
                .catch(function(error) {
                    console.error('Error adding to cart:', error);
                    if (error.status === 401) {
                        // Clear token if unauthorized
                        localStorage.removeItem('token');
                        $rootScope.isLoggedIn = false;
                    }
                    deferred.reject(error);
                });
        } else {
            // Otherwise, add to local cart
            var existingItem = cart.items.find(function(item) {
                return item.product_id === product.id;
            });
            
            if (existingItem) {
                existingItem.quantity += quantity;
            } else {
                cart.items.push({
                    product_id: product.id,
                    product: product,
                    quantity: quantity,
                    price: product.price
                });
            }
            
            // Recalculate total
            service.calculateTotal();
            service.saveCart();
            deferred.resolve(cart);
        }
        
        return deferred.promise;
    };
    
    // Update cart item quantity
    service.updateQuantity = function(itemId, quantity) {
        var deferred = $q.defer();
        var token = localStorage.getItem('token');
        
        // Validate quantity
        if (!quantity || quantity < 1) {
            deferred.reject({ message: 'Quantity must be at least 1' });
            return deferred.promise;
        }
        
        if (token) {
            // Find the cart item ID first
            var cartItemId = null;
            var item = cart.items.find(function(item) {
                return item.product_id === itemId;
            });
            
            if (item && item.id) {
                cartItemId = item.id;
            } else {
                console.error('Cart item not found or missing ID:', itemId);
                // If we can't find the cart item ID, fall back to local update
                return updateLocalCartItem(itemId, quantity, deferred);
            }
            
            console.log('Updating cart item with ID:', cartItemId, 'for product ID:', itemId);
            
            // If user is logged in, update server cart
            $http.put(API_URL + '/cart/' + cartItemId, {
                quantity: quantity
            }, {
                headers: {
                    'Authorization': 'Bearer ' + token
                }
            })
                .then(function(response) {
                    console.log('Cart update response:', response);
                    cart = response.data.cart || response.data;
                    $rootScope.cartItemCount = service.getItemCount();
                    deferred.resolve(cart);
                })
                .catch(function(error) {
                    console.error('Error updating cart item:', error);
                    if (error.status === 401) {
                        // Clear token if unauthorized
                        localStorage.removeItem('token');
                        $rootScope.isLoggedIn = false;
                    }
                    
                    // If API call fails, fall back to local update
                    if (error.status === 404) {
                        console.log('Cart item not found on server, updating locally');
                        return updateLocalCartItem(itemId, quantity, deferred);
                    } else {
                        deferred.reject(error);
                    }
                });
        } else {
            // Otherwise, update local cart
            return updateLocalCartItem(itemId, quantity, deferred);
        }
        
        return deferred.promise;
    };
    
    // Helper function to update local cart item
    function updateLocalCartItem(itemId, quantity, deferred) {
        var item = cart.items.find(function(item) {
            return item.product_id === itemId;
        });
        
        if (item) {
            // Check if quantity exceeds stock
            if (item.product && item.product.stock !== undefined && quantity > item.product.stock) {
                deferred.reject({ message: 'Only ' + item.product.stock + ' items available in stock' });
                return deferred.promise;
            }
            
            item.quantity = quantity;
            item.subtotal = quantity * item.price;
            
            // Recalculate total
            service.calculateTotal();
            service.saveCart();
            deferred.resolve(cart);
        } else {
            deferred.reject({ message: 'Item not found in cart' });
        }
        
        return deferred.promise;
    }
    
    // Remove item from cart
    service.removeFromCart = function(itemId) {
        var deferred = $q.defer();
        var token = localStorage.getItem('token');
        
        if (token) {
            // Find the cart item ID first
            var cartItemId = null;
            var item = cart.items.find(function(item) {
                return item.product_id === itemId;
            });
            
            if (item && item.id) {
                cartItemId = item.id;
            } else {
                console.error('Cart item not found or missing ID for removal:', itemId);
                // If we can't find the cart item ID, fall back to local removal
                return removeLocalCartItem(itemId, deferred);
            }
            
            console.log('Removing cart item with ID:', cartItemId, 'for product ID:', itemId);
            
            // If user is logged in, remove from server cart
            $http.delete(API_URL + '/cart/' + cartItemId, {
                headers: {
                    'Authorization': 'Bearer ' + token
                }
            })
                .then(function(response) {
                    console.log('Cart remove response:', response);
                    cart = response.data.cart || response.data;
                    $rootScope.cartItemCount = service.getItemCount();
                    deferred.resolve(cart);
                })
                .catch(function(error) {
                    console.error('Error removing cart item:', error);
                    
                    // If API call fails, fall back to local removal
                    if (error.status === 404) {
                        console.log('Cart item not found on server, removing locally');
                        return removeLocalCartItem(itemId, deferred);
                    } else {
                        deferred.reject(error.data);
                    }
                });
        } else {
            // Otherwise, remove from local cart
            return removeLocalCartItem(itemId, deferred);
        }
        
        return deferred.promise;
    };
    
    // Helper function to remove local cart item
    function removeLocalCartItem(itemId, deferred) {
        var index = cart.items.findIndex(function(item) {
            return item.product_id === itemId;
        });
        
        if (index !== -1) {
            cart.items.splice(index, 1);
            
            // Recalculate total
            service.calculateTotal();
            service.saveCart();
            deferred.resolve(cart);
        } else {
            deferred.reject({ message: 'Item not found in cart' });
        }
        
        return deferred.promise;
    }
    
    // Clear cart
    service.clearCart = function() {
        var deferred = $q.defer();
        var token = localStorage.getItem('token');
        
        if (token) {
            // If user is logged in, clear server cart
            // Note: This is a custom endpoint that might need to be added to the API
            $http.delete(API_URL + '/cart', {
                headers: {
                    'Authorization': 'Bearer ' + token
                }
            })
                .then(function(response) {
                    cart = { items: [], total: 0 };
                    $rootScope.cartItemCount = 0;
                    deferred.resolve(cart);
                })
                .catch(function(error) {
                    deferred.reject(error.data);
                });
        } else {
            // Otherwise, clear local cart
            cart = { items: [], total: 0 };
            service.saveCart();
            $rootScope.cartItemCount = 0;
            deferred.resolve(cart);
        }
        
        return deferred.promise;
    };
    
    // Calculate cart total
    service.calculateTotal = function() {
        if (!cart.items || cart.items.length === 0) {
            cart.total = 0;
            return;
        }
        
        cart.total = cart.items.reduce(function(total, item) {
            var itemPrice = parseFloat(item.price) || 0;
            var itemQuantity = parseInt(item.quantity) || 0;
            return total + (itemPrice * itemQuantity);
        }, 0);
        
        console.log('Cart total calculated:', cart.total);
    };
    
    // Get cart item count
    service.getItemCount = function() {
        return cart.items.reduce(function(count, item) {
            return count + item.quantity;
        }, 0);
    };
    
    // Sync local cart with server after login
    service.syncCartAfterLogin = function() {
        var localCart = localStorage.getItem('cart');
        if (!localCart) return $q.resolve();
        
        try {
            var parsedCart = JSON.parse(localCart);
            if (!parsedCart.items || !parsedCart.items.length) {
                return $q.resolve();
            }
            
            // Sync each item to server
            var promises = parsedCart.items.map(function(item) {
                return service.addToCart(item.product, item.quantity);
            });
            
            return $q.all(promises).then(function() {
                // Clear local cart after syncing
                localStorage.removeItem('cart');
                return service.getCart();
            });
        } catch (e) {
            console.error('Error syncing cart after login', e);
            return $q.reject(e);
        }
    };
    
    // Reset cart completely
    service.resetCart = function() {
        cart = {
            items: [],
            total: 0
        };
        localStorage.removeItem('cart');
        $rootScope.cartItemCount = 0;
        return cart;
    };
    
    return service;
}]); 