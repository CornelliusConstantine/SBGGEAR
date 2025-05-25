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
            deferred.resolve(cart);
            return deferred.promise;
        }
        
        $http.get(API_URL + '/cart', {
            headers: {
                'Authorization': 'Bearer ' + token
            }
        })
            .then(function(response) {
                cart = response.data;
                $rootScope.cartItemCount = service.getItemCount();
                deferred.resolve(cart);
            })
            .catch(function(error) {
                deferred.reject(error.data);
            });
        
        return deferred.promise;
    };
    
    // Add item to cart
    service.addToCart = function(product, quantity) {
        var deferred = $q.defer();
        var token = localStorage.getItem('token');
        
        if (token) {
            // If user is logged in, add to server cart
            $http.post(API_URL + '/cart', {
                product_id: product.id,
                quantity: quantity || 1
            }, {
                headers: {
                    'Authorization': 'Bearer ' + token
                }
            })
                .then(function(response) {
                    cart = response.data;
                    $rootScope.cartItemCount = service.getItemCount();
                    deferred.resolve(cart);
                })
                .catch(function(error) {
                    deferred.reject(error.data);
                });
        } else {
            // Otherwise, add to local cart
            var existingItem = cart.items.find(function(item) {
                return item.product_id === product.id;
            });
            
            if (existingItem) {
                existingItem.quantity += (quantity || 1);
            } else {
                cart.items.push({
                    product_id: product.id,
                    product: product,
                    quantity: quantity || 1,
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
        
        if (token) {
            // If user is logged in, update server cart
            $http.put(API_URL + '/cart/' + itemId, {
                quantity: quantity
            }, {
                headers: {
                    'Authorization': 'Bearer ' + token
                }
            })
                .then(function(response) {
                    cart = response.data;
                    $rootScope.cartItemCount = service.getItemCount();
                    deferred.resolve(cart);
                })
                .catch(function(error) {
                    deferred.reject(error.data);
                });
        } else {
            // Otherwise, update local cart
            var item = cart.items.find(function(item) {
                return item.product_id === itemId;
            });
            
            if (item) {
                item.quantity = quantity;
                
                // Recalculate total
                service.calculateTotal();
                service.saveCart();
                deferred.resolve(cart);
            } else {
                deferred.reject({ message: 'Item not found in cart' });
            }
        }
        
        return deferred.promise;
    };
    
    // Remove item from cart
    service.removeFromCart = function(itemId) {
        var deferred = $q.defer();
        var token = localStorage.getItem('token');
        
        if (token) {
            // If user is logged in, remove from server cart
            $http.delete(API_URL + '/cart/' + itemId, {
                headers: {
                    'Authorization': 'Bearer ' + token
                }
            })
                .then(function(response) {
                    cart = response.data;
                    $rootScope.cartItemCount = service.getItemCount();
                    deferred.resolve(cart);
                })
                .catch(function(error) {
                    deferred.reject(error.data);
                });
        } else {
            // Otherwise, remove from local cart
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
        }
        
        return deferred.promise;
    };
    
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
        cart.total = cart.items.reduce(function(total, item) {
            return total + (item.price * item.quantity);
        }, 0);
    };
    
    // Get cart
    service.getCart = function() {
        return cart;
    };
    
    // Get cart item count
    service.getItemCount = function() {
        return cart.items.reduce(function(count, item) {
            return count + item.quantity;
        }, 0);
    };
    
    // Sync local cart with server after login
    service.syncCartAfterLogin = function() {
        var deferred = $q.defer();
        var token = localStorage.getItem('token');
        
        if (!token || cart.items.length === 0) {
            deferred.resolve();
            return deferred.promise;
        }
        
        // For each item in the local cart, add it to the server cart
        var promises = cart.items.map(function(item) {
            return $http.post(API_URL + '/cart', {
                product_id: item.product_id,
                quantity: item.quantity
            }, {
                headers: {
                    'Authorization': 'Bearer ' + token
                }
            });
        });
        
        $q.all(promises)
            .then(function() {
                // After syncing, get the updated cart from server
                return service.getCart();
            })
            .then(function(updatedCart) {
                // Clear local cart
                localStorage.removeItem('cart');
                deferred.resolve(updatedCart);
            })
            .catch(function(error) {
                deferred.reject(error);
            });
        
        return deferred.promise;
    };
    
    return service;
}]); 