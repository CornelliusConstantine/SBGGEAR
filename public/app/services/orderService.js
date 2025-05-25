'use strict';

app.service('OrderService', ['$http', '$q', function($http, $q) {
    var service = {};
    var API_URL = '/api';
    
    // Create a new order from cart
    service.createOrder = function(orderData) {
        var deferred = $q.defer();
        var token = localStorage.getItem('token');
        
        if (!token) {
            deferred.reject({ message: 'User not authenticated' });
            return deferred.promise;
        }
        
        $http.post(API_URL + '/orders', orderData, {
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
    
    // Get user's orders
    service.getOrders = function() {
        var deferred = $q.defer();
        var token = localStorage.getItem('token');
        
        if (!token) {
            deferred.reject({ message: 'User not authenticated' });
            return deferred.promise;
        }
        
        $http.get(API_URL + '/orders', {
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
    
    // Get a specific order
    service.getOrder = function(orderId) {
        var deferred = $q.defer();
        var token = localStorage.getItem('token');
        
        if (!token) {
            deferred.reject({ message: 'User not authenticated' });
            return deferred.promise;
        }
        
        $http.get(API_URL + '/orders/' + orderId, {
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
    
    return service;
}]); 