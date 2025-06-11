'use strict';

app.controller('ProfileController', ['$scope', '$location', 'AuthService', function($scope, $location, AuthService) {
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
        $scope.formSubmitted = false;
        
        // Load user profile
        $scope.loadProfile();
    };
    
    // Logout function
    $scope.logout = function() {
        AuthService.logout().then(function() {
            $location.path('/login');
        });
    };
    
    // Load user profile
    $scope.loadProfile = function() {
        $scope.loading.profile = true;
        $scope.error = null;
        
        AuthService.getCurrentUser()
            .then(function(user) {
                $scope.user = user;
                
                // Copy user data for update form
                $scope.updateData = angular.copy(user);
                
                // Handle any fields that might be null
                $scope.updateData.phone = $scope.updateData.phone || '';
                $scope.updateData.address = $scope.updateData.address || '';
                $scope.updateData.city = $scope.updateData.city || '';
                $scope.updateData.province = $scope.updateData.province || '';
                $scope.updateData.postal_code = $scope.updateData.postal_code || '';
            })
            .catch(function(error) {
                console.error('Error loading profile', error);
                $scope.error = 'Failed to load profile. Please try again later.';
            })
            .finally(function() {
                $scope.loading.profile = false;
            });
    };
    
    // Update profile
    $scope.updateProfile = function() {
        $scope.formSubmitted = true;
        
        // Check if form is valid
        if ($scope.profileForm.$invalid) {
            $scope.error = 'Please fill out all required fields correctly.';
            return;
        }
        
        $scope.loading.update = true;
        $scope.error = null;
        $scope.success = null;
        
        AuthService.updateProfile($scope.updateData)
            .then(function(response) {
                $scope.user = response.user;
                $scope.success = 'Profile updated successfully';
                
                // Update the form data with the latest user data
                $scope.updateData = angular.copy($scope.user);
                
                // Reset form validation state
                $scope.formSubmitted = false;
                if ($scope.profileForm) {
                    $scope.profileForm.$setPristine();
                    $scope.profileForm.$setUntouched();
                }
            })
            .catch(function(error) {
                console.error('Error updating profile', error);
                $scope.error = error.message || 'Failed to update profile. Please try again.';
            })
            .finally(function() {
                $scope.loading.update = false;
            });
    };
    
    // Initialize controller
    $scope.init();
}]); 