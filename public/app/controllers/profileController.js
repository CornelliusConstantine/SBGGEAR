'use strict';

app.controller('ProfileController', ['$scope', 'AuthService', function($scope, AuthService) {
    // Initialize controller
    $scope.init = function() {
        $scope.loading = {
            profile: true,
            update: false
        };
        
        $scope.user = {};
        $scope.updateData = {};
        $scope.error = null;
        $scope.success = null;
        
        // Load user profile
        $scope.loadProfile();
    };
    
    // Load user profile
    $scope.loadProfile = function() {
        $scope.loading.profile = true;
        
        AuthService.getCurrentUser()
            .then(function(user) {
                $scope.user = user;
                
                // Copy user data for update form
                $scope.updateData = angular.copy(user);
            })
            .catch(function(error) {
                console.error('Error loading profile', error);
            })
            .finally(function() {
                $scope.loading.profile = false;
            });
    };
    
    // Update profile
    $scope.updateProfile = function() {
        $scope.loading.update = true;
        $scope.error = null;
        $scope.success = null;
        
        AuthService.updateProfile($scope.updateData)
            .then(function(user) {
                $scope.user = user;
                $scope.success = 'Profile updated successfully';
            })
            .catch(function(error) {
                $scope.error = error.message || 'Failed to update profile';
            })
            .finally(function() {
                $scope.loading.update = false;
            });
    };
    
    // Initialize controller
    $scope.init();
}]); 