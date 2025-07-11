'use strict';

app.service('AuthService', ['$http', '$q', '$rootScope', 'CartService', function($http, $q, $rootScope, CartService) {
    var service = {};
    var API_URL = '/api';
    
    // User data
    var currentUser = null;
    
    // Check if user is authenticated
    service.checkAuth = function() {
        var deferred = $q.defer();
        
        console.log('DEBUG: AuthService.checkAuth called');
        
        // Check if we have a token in localStorage
        var token = localStorage.getItem('token');
        console.log('DEBUG: Token exists:', !!token);
        
        if (token) {
            console.log('DEBUG: Making API request to check auth');
            // Get user data with the token
            $http.get(API_URL + '/user', {
                headers: {
                    'Authorization': 'Bearer ' + token
                }
            }).then(function(response) {
                console.log('DEBUG: Auth check successful, user data:', response.data);
                currentUser = response.data;
                $rootScope.isLoggedIn = true;
                $rootScope.currentUser = currentUser;
                
                // Ensure role is set properly
                var userRole = currentUser.role || 'customer';
                $rootScope.isAdmin = userRole === 'admin';
                
                // Store user role in localStorage for UI customization
                localStorage.setItem('userRole', userRole);
                
                console.log('AuthService: User authenticated', {
                    userId: currentUser.id,
                    role: userRole,
                    isAdmin: $rootScope.isAdmin
                });
                
                deferred.resolve(currentUser);
            }).catch(function(error) {
                console.error('DEBUG: Auth check failed:', error);
                
                // Token might be invalid or expired
                localStorage.removeItem('token');
                localStorage.removeItem('userRole');
                $rootScope.isLoggedIn = false;
                $rootScope.currentUser = null;
                $rootScope.isAdmin = false;
                deferred.reject('unauthorized');
            });
        } else {
            console.log('DEBUG: No token found, user not authenticated');
            localStorage.removeItem('userRole');
            $rootScope.isLoggedIn = false;
            $rootScope.currentUser = null;
            $rootScope.isAdmin = false;
            deferred.reject('unauthorized');
        }
        
        return deferred.promise;
    };
    
    // Login user
    service.login = function(email, password) {
        var deferred = $q.defer();
        
        $http.post(API_URL + '/login', {
            email: email,
            password: password
        }).then(function(response) {
            // Store token
            localStorage.setItem('token', response.data.token);
            
            // Get user data
            return service.checkAuth();
        }).then(function(user) {
            deferred.resolve(user);
        }).catch(function(error) {
            deferred.reject(error.data);
        });
        
        return deferred.promise;
    };
    
    // Register new user
    service.register = function(userData) {
        var deferred = $q.defer();
        
        $http.post(API_URL + '/register', userData)
            .then(function(response) {
                // Store token
                localStorage.setItem('token', response.data.token);
                
                // Get user data
                return service.checkAuth();
            })
            .then(function(user) {
                deferred.resolve(user);
            })
            .catch(function(error) {
                deferred.reject(error.data);
            });
        
        return deferred.promise;
    };
    
    // Forgot password - request reset email
    service.forgotPassword = function(email) {
        var deferred = $q.defer();
        
        $http.post(API_URL + '/forgot-password', { email: email })
            .then(function(response) {
                deferred.resolve(response.data);
            })
            .catch(function(error) {
                deferred.reject(error.data);
            });
        
        return deferred.promise;
    };
    
    // Validate reset token
    service.validateResetToken = function(token, email) {
        var deferred = $q.defer();
        
        $http.post(API_URL + '/validate-reset-token', { 
            token: token,
            email: email
        })
            .then(function(response) {
                deferred.resolve(response.data);
            })
            .catch(function(error) {
                deferred.reject(error.data);
            });
        
        return deferred.promise;
    };
    
    // Reset password
    service.resetPassword = function(resetData) {
        var deferred = $q.defer();
        
        $http.post(API_URL + '/reset-password', resetData)
            .then(function(response) {
                deferred.resolve(response.data);
            })
            .catch(function(error) {
                deferred.reject(error.data);
            });
        
        return deferred.promise;
    };
    
    // Logout user
    service.logout = function() {
        var deferred = $q.defer();
        var token = localStorage.getItem('token');
        
        if (token) {
            $http.post(API_URL + '/logout', {}, {
                headers: {
                    'Authorization': 'Bearer ' + token
                }
            }).then(function() {
                // Clear token and user data
                localStorage.removeItem('token');
                localStorage.removeItem('userRole');
                currentUser = null;
                $rootScope.isLoggedIn = false;
                $rootScope.currentUser = null;
                $rootScope.isAdmin = false;
                
                // Reset cart
                CartService.resetCart();
                
                deferred.resolve();
            }).catch(function(error) {
                // Even if the server-side logout fails, we'll still clear the local data
                localStorage.removeItem('token');
                localStorage.removeItem('userRole');
                currentUser = null;
                $rootScope.isLoggedIn = false;
                $rootScope.currentUser = null;
                $rootScope.isAdmin = false;
                
                // Reset cart
                CartService.resetCart();
                
                deferred.resolve();
            });
        } else {
            // Clear any local cart data even if not logged in
            CartService.resetCart();
            deferred.resolve();
        }
        
        return deferred.promise;
    };
    
    // Update user profile
    service.updateProfile = function(userData) {
        var deferred = $q.defer();
        var token = localStorage.getItem('token');
        
        if (token) {
            $http.put(API_URL + '/user', userData, {
                headers: {
                    'Authorization': 'Bearer ' + token
                }
            }).then(function(response) {
                currentUser = response.data;
                $rootScope.currentUser = currentUser;
                deferred.resolve(currentUser);
            }).catch(function(error) {
                deferred.reject(error.data);
            });
        } else {
            deferred.reject('unauthorized');
        }
        
        return deferred.promise;
    };
    
    // Check if user is authenticated
    service.isAuthenticated = function() {
        return !!localStorage.getItem('token');
    };
    
    // Require authentication for routes
    service.requireAuth = function() {
        var deferred = $q.defer();
        
        if (service.isAuthenticated()) {
            service.checkAuth()
                .then(function() {
                    deferred.resolve();
                })
                .catch(function() {
                    deferred.reject('unauthorized');
                });
        } else {
            deferred.reject('unauthorized');
        }
        
        return deferred.promise;
    };
    
    // Require admin role for routes
    service.requireAdmin = function() {
        var deferred = $q.defer();
        
        if (service.isAuthenticated()) {
            service.checkAuth()
                .then(function(user) {
                    if (user.role === 'admin') {
                        deferred.resolve();
                    } else {
                        deferred.reject('forbidden');
                    }
                })
                .catch(function() {
                    deferred.reject('unauthorized');
                });
        } else {
            deferred.reject('unauthorized');
        }
        
        return deferred.promise;
    };
    
    // Get current user data
    service.getCurrentUser = function() {
        var deferred = $q.defer();
        var token = localStorage.getItem('token');
        
        if (token) {
            // If we already have the user data cached and it's valid
            if (currentUser) {
                deferred.resolve(currentUser);
            } else {
                // Otherwise fetch from API
                $http.get(API_URL + '/user', {
                    headers: {
                        'Authorization': 'Bearer ' + token
                    }
                }).then(function(response) {
                    currentUser = response.data;
                    $rootScope.currentUser = currentUser;
                    $rootScope.isLoggedIn = true;
                    
                    // Ensure role is set properly
                    var userRole = currentUser.role || 'customer';
                    $rootScope.isAdmin = userRole === 'admin';
                    
                    deferred.resolve(currentUser);
                }).catch(function(error) {
                    console.error('Failed to get current user:', error);
                    deferred.reject(error.data);
                });
            }
        } else {
            deferred.reject('unauthorized');
        }
        
        return deferred.promise;
    };
    
    // Check if user is admin
    service.isAdmin = function() {
        return currentUser && currentUser.role === 'admin';
    };
    
    return service;
}]); 