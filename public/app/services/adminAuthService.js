'use strict';

app.factory('AdminAuthService', ['$http', '$location', function($http, $location) {
    var service = {};
    
    // Check if admin is authenticated
    service.isAuthenticated = function() {
        // First check Laravel window object
        if (window.Laravel && window.Laravel.user && window.Laravel.user.isAdmin) {
            console.log('Admin authenticated via Laravel window object');
            return true;
        }
        
        // Then check localStorage
        var user = JSON.parse(localStorage.getItem('user') || '{}');
        if (user && user.isAdmin) {
            console.log('Admin authenticated via localStorage');
            return true;
        }
        
        console.log('Admin not authenticated');
        return false;
    };
    
    // Check admin auth status and redirect if not admin
    service.requireAdminAuth = function() {
        if (!service.isAuthenticated()) {
            console.log('Admin auth required, redirecting to login');
            $location.path('/login').search({ redirect: '/admin/orders' });
            return false;
        }
        return true;
    };
    
    // Verify admin credentials with server
    service.verifyAdminCredentials = function() {
        return $http.get('/test-auth', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            withCredentials: true
        })
        .then(function(response) {
            console.log('Auth verification response:', response.data);
            if (response.data.authenticated && response.data.user && response.data.user.is_admin) {
                return true;
            } else {
                console.error('User authenticated but not admin:', response.data);
                return false;
            }
        })
        .catch(function(error) {
            console.error('Auth verification failed:', error);
            return false;
        });
    };
    
    return service;
}]); 