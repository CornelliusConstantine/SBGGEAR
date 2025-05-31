'use strict';

app.controller('AuthController', ['$scope', '$location', '$timeout', '$routeParams', 'AuthService', function($scope, $location, $timeout, $routeParams, AuthService) {
    // Initialize controller
    $scope.init = function() {
        $scope.loading = false;
        $scope.error = null;
        
        // Login credentials
        $scope.credentials = {
            email: '',
            password: '',
            remember: false
        };
        
        // Registration data
        $scope.registration = {
            name: '',
            email: '',
            password: '',
            password_confirmation: '',
            phone: '',
            address: 'N/A',  // Default value for address field
            city: 'N/A',     // Default value for city field
            postal_code: 'N/A', // Default value for postal_code field
            province: 'N/A',  // Default value for province field
            country: 'N/A'    // Default value for country field
        };
        
        // Forgot password
        $scope.forgotEmail = '';
        $scope.resetEmailSent = false;
        
        // Reset password
        $scope.resetData = {
            token: $routeParams.token || '',
            email: $routeParams.email || '',
            password: '',
            password_confirmation: ''
        };
        $scope.invalidToken = false;
        $scope.passwordResetSuccess = false;
        
        // Check if we're on the reset password page with a token
        if ($location.path() === '/reset-password' && $scope.resetData.token) {
            $scope.validateResetToken();
        }
        
        // Check if we need to redirect after login
        var redirectPath = $location.search().redirect;
        $scope.redirectPath = redirectPath ? redirectPath : '/';
    };
    
    // Login user (works for both regular users and admins)
    $scope.login = function() {
        $scope.loading = true;
        $scope.error = null;
        
        AuthService.login($scope.credentials.email, $scope.credentials.password)
            .then(function(user) {
                // Redirect based on user role
                if (user.role === 'admin') {
                    $location.path('/admin');
                } else {
                    $location.path($scope.redirectPath);
                }
            })
            .catch(function(error) {
                $scope.error = error.message || 'Invalid email or password';
            })
            .finally(function() {
                $scope.loading = false;
            });
    };
    
    // Register user
    $scope.register = function() {
        $scope.loading = true;
        $scope.error = null;
        
        // Check if passwords match
        if ($scope.registration.password !== $scope.registration.password_confirmation) {
            $scope.error = 'Passwords do not match';
            $scope.loading = false;
            return;
        }
        
        // Ensure address fields are set (in case they were somehow removed)
        $scope.registration.address = $scope.registration.address || 'N/A';
        $scope.registration.city = $scope.registration.city || 'N/A';
        $scope.registration.postal_code = $scope.registration.postal_code || 'N/A';
        $scope.registration.province = $scope.registration.province || 'N/A';
        $scope.registration.country = $scope.registration.country || 'N/A';
        
        // Add default empty values for removed fields to satisfy backend validation
        var registrationData = angular.copy($scope.registration);
        
        AuthService.register(registrationData)
            .then(function(user) {
                $location.path('/');
            })
            .catch(function(error) {
                $scope.error = error.message || 'Registration failed';
            })
            .finally(function() {
                $scope.loading = false;
            });
    };
    
    // Request password reset
    $scope.requestPasswordReset = function() {
        $scope.loading = true;
        $scope.error = null;
        
        AuthService.forgotPassword($scope.forgotEmail)
            .then(function() {
                $scope.resetEmailSent = true;
            })
            .catch(function(error) {
                $scope.error = error.message || 'Failed to send reset email. Please try again.';
            })
            .finally(function() {
                $scope.loading = false;
            });
    };
    
    // Validate reset token
    $scope.validateResetToken = function() {
        $scope.loading = true;
        
        AuthService.validateResetToken($scope.resetData.token, $scope.resetData.email)
            .then(function() {
                $scope.invalidToken = false;
            })
            .catch(function() {
                $scope.invalidToken = true;
            })
            .finally(function() {
                $scope.loading = false;
            });
    };
    
    // Reset password
    $scope.resetPassword = function() {
        $scope.loading = true;
        $scope.error = null;
        
        // Check if passwords match
        if ($scope.resetData.password !== $scope.resetData.password_confirmation) {
            $scope.error = 'Passwords do not match';
            $scope.loading = false;
            return;
        }
        
        AuthService.resetPassword($scope.resetData)
            .then(function() {
                $scope.passwordResetSuccess = true;
                // Redirect to login page after a delay
                $timeout(function() {
                    $location.path('/login');
                }, 3000);
            })
            .catch(function(error) {
                $scope.error = error.message || 'Failed to reset password. Please try again.';
            })
            .finally(function() {
                $scope.loading = false;
            });
    };
    
    // Initialize controller
    $scope.init();
}]); 