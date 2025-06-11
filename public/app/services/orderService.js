'use strict';

app.service('OrderService', ['$http', '$q', function($http, $q) {
    var service = {};
    var API_URL = '/api';
    
    // Create a new order from cart
    service.createOrder = function(orderData) {
        var deferred = $q.defer();
        var token = localStorage.getItem('token');
        
        console.log('OrderService.createOrder called with data:', orderData);
        
        if (!token) {
            console.error('OrderService: No authentication token found');
            deferred.reject({ message: 'User not authenticated' });
            return deferred.promise;
        }
        
        $http.post(API_URL + '/orders', orderData, {
            headers: {
                'Authorization': 'Bearer ' + token
            }
        })
            .then(function(response) {
                console.log('Order created successfully:', response.data);
                deferred.resolve(response.data);
            })
            .catch(function(error) {
                console.error('Error creating order:', error);
                deferred.reject(error.data);
            });
        
        return deferred.promise;
    };
    
    // Get user's orders
    service.getOrders = function(page) {
        var deferred = $q.defer();
        var token = localStorage.getItem('token');
        
        console.log('OrderService.getOrders called, page:', page);
        console.log('Token exists:', !!token);
        
        if (!token) {
            console.error('OrderService: No authentication token found');
            deferred.reject({ message: 'User not authenticated' });
            return deferred.promise;
        }
        
        var params = {};
        if (page) {
            params.page = page;
        }
        
        console.log('Making API request to /orders with params:', params);
        
        $http.get(API_URL + '/orders', {
            headers: {
                'Authorization': 'Bearer ' + token
            },
            params: params
        })
            .then(function(response) {
                console.log('Orders API response:', response);
                
                // Handle different response formats
                if (response && response.data !== undefined) {
                    console.log('Orders loaded successfully, data property exists');
                    deferred.resolve(response);
                } else {
                    console.error('Unexpected API response format:', response);
                    deferred.reject({ message: 'Invalid response format' });
                }
            })
            .catch(function(error) {
                console.error('Error loading orders:', error);
                deferred.reject(error.data || error);
            });
        
        return deferred.promise;
    };
    
    // Get a specific order
    service.getOrder = function(orderId) {
        var deferred = $q.defer();
        var token = localStorage.getItem('token');
        
        console.log('OrderService.getOrder called, orderId:', orderId);
        console.log('Token exists:', !!token);
        
        if (!token) {
            console.error('OrderService: No authentication token found');
            deferred.reject({ message: 'User not authenticated' });
            return deferred.promise;
        }
        
        console.log('Making API request to /orders/' + orderId);
        
        $http.get(API_URL + '/orders/' + orderId, {
            headers: {
                'Authorization': 'Bearer ' + token
            }
        })
            .then(function(response) {
                console.log('Order loaded successfully:', response.data);
                deferred.resolve(response.data);
            })
            .catch(function(error) {
                console.error('Error loading order:', error);
                deferred.reject(error.data);
            });
        
        return deferred.promise;
    };
    
    // Process payment for an order
    service.payOrder = function(orderId, paymentData) {
        var deferred = $q.defer();
        var token = localStorage.getItem('token');
        
        if (!token) {
            deferred.reject({ message: 'User not authenticated' });
            return deferred.promise;
        }
        
        $http.post(API_URL + '/orders/' + orderId + '/pay', paymentData, {
            headers: {
                'Authorization': 'Bearer ' + token
            }
        })
            .then(function(response) {
                deferred.resolve(response.data);
            })
            .catch(function(error) {
                deferred.reject(error.data);
            });
        
        return deferred.promise;
    };
    
    // Check payment status for an order
    service.checkPaymentStatus = function(orderId) {
        var deferred = $q.defer();
        var token = localStorage.getItem('token');
        
        if (!token) {
            deferred.reject({ message: 'User not authenticated' });
            return deferred.promise;
        }
        
        $http.get(API_URL + '/orders/' + orderId + '/status', {
            headers: {
                'Authorization': 'Bearer ' + token
            }
        })
            .then(function(response) {
                deferred.resolve(response.data);
            })
            .catch(function(error) {
                deferred.reject(error.data);
            });
        
        return deferred.promise;
    };
    
    // Admin: Get all orders
    service.getAllOrders = function(filters) {
        var deferred = $q.defer();
        var token = localStorage.getItem('token');
        
        if (!token) {
            deferred.reject({ message: 'User not authenticated' });
            return deferred.promise;
        }
        
        $http.get(API_URL + '/admin/orders', {
            headers: {
                'Authorization': 'Bearer ' + token
            },
            params: filters || {}
        })
            .then(function(response) {
                deferred.resolve(response.data);
            })
            .catch(function(error) {
                deferred.reject(error.data);
            });
        
        return deferred.promise;
    };
    
    // Admin: Update order status
    service.updateOrderStatus = function(orderId, status) {
        var deferred = $q.defer();
        var token = localStorage.getItem('token');
        
        if (!token) {
            deferred.reject({ message: 'User not authenticated' });
            return deferred.promise;
        }
        
        $http.put(API_URL + '/admin/orders/' + orderId + '/status', {
            status: status
        }, {
            headers: {
                'Authorization': 'Bearer ' + token
            }
        })
            .then(function(response) {
                deferred.resolve(response.data);
            })
            .catch(function(error) {
                deferred.reject(error.data);
            });
        
        return deferred.promise;
    };
    
    // Debug: Get detailed order information
    service.debugOrders = function() {
        var deferred = $q.defer();
        var token = localStorage.getItem('token');
        
        console.log('OrderService.debugOrders called');
        
        if (!token) {
            console.error('OrderService: No authentication token found');
            deferred.reject({ message: 'User not authenticated' });
            return deferred.promise;
        }
        
        $http.get(API_URL + '/debug/orders', {
            headers: {
                'Authorization': 'Bearer ' + token
            }
        })
            .then(function(response) {
                console.log('Debug orders response:', response.data);
                deferred.resolve(response.data);
            })
            .catch(function(error) {
                console.error('Error in debug orders:', error);
                deferred.reject(error.data || error);
            });
        
        return deferred.promise;
    };
    
    return service;
}]); 