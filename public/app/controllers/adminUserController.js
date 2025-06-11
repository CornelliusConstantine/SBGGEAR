'use strict';

app.controller('AdminUserController', ['$scope', '$http', '$uibModal', function($scope, $http, $uibModal) {
    // Initialize controller
    $scope.init = function() {
        $scope.loading = true;
        $scope.users = [];
        $scope.searchQuery = '';
        
        // Get current user data
        $scope.getCurrentUser();
        
        // Load users
        $scope.loadUsers();
    };
    
    // Get current user data
    $scope.getCurrentUser = function() {
        $http.get('/api/user')
            .then(function(response) {
                $scope.currentUser = response.data;
            })
            .catch(function(error) {
                console.error('Error getting current user', error);
            });
    };
    
    // Load users
    $scope.loadUsers = function() {
        $scope.loading = true;
        
        $http.get('/api/admin/users')
            .then(function(response) {
                $scope.users = response.data;
            })
            .catch(function(error) {
                console.error('Error loading users', error);
                
                // Show error message
                $scope.showError('Failed to load users. Please try again later.');
            })
            .finally(function() {
                $scope.loading = false;
            });
    };
    
    // View user details
    $scope.viewUser = function(user) {
        var modalInstance = $uibModal.open({
            templateUrl: 'userDetailModal.html',
            controller: 'UserDetailModalController',
            resolve: {
                user: function() {
                    return user;
                }
            }
        });
    };
    
    // Toggle user status (active/inactive)
    $scope.toggleUserStatus = function(user) {
        var newStatus = !user.is_active;
        
        $http.put('/api/admin/users/' + user.id + '/status', {
            is_active: newStatus
        })
            .then(function(response) {
                // Update user status in the list
                user.is_active = newStatus;
                
                // Show success message
                $scope.showSuccess('User status updated successfully.');
            })
            .catch(function(error) {
                console.error('Error updating user status', error);
                
                // Show error message
                $scope.showError('Failed to update user status. Please try again later.');
            });
    };
    
    // Promote user to admin
    $scope.promoteToAdmin = function(user) {
        if (user.role === 'admin') {
            return;
        }
        
        // Confirm action
        if (!confirm('Are you sure you want to promote this user to admin? This will grant them full access to the admin panel.')) {
            return;
        }
        
        $http.put('/api/admin/users/' + user.id + '/role', {
            role: 'admin'
        })
            .then(function(response) {
                // Update user role in the list
                user.role = 'admin';
                
                // Show success message
                $scope.showSuccess('User promoted to admin successfully.');
            })
            .catch(function(error) {
                console.error('Error promoting user', error);
                
                // Show error message
                $scope.showError('Failed to promote user. Please try again later.');
            });
    };
    
    // Demote admin to customer
    $scope.demoteToCustomer = function(user) {
        if (user.role !== 'admin' || user.id === $scope.currentUser.id) {
            return;
        }
        
        // Confirm action
        if (!confirm('Are you sure you want to demote this admin to customer? They will lose access to the admin panel.')) {
            return;
        }
        
        $http.put('/api/admin/users/' + user.id + '/role', {
            role: 'customer'
        })
            .then(function(response) {
                // Update user role in the list
                user.role = 'customer';
                
                // Show success message
                $scope.showSuccess('Admin demoted to customer successfully.');
            })
            .catch(function(error) {
                console.error('Error demoting admin', error);
                
                // Show error message
                $scope.showError('Failed to demote admin. Please try again later.');
            });
    };
    
    // Delete user
    $scope.deleteUser = function(user) {
        // Prevent deleting current user
        if (user.id === $scope.currentUser.id) {
            $scope.showError('You cannot delete your own account.');
            return;
        }
        
        // Confirm action
        if (!confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
            return;
        }
        
        $http.delete('/api/admin/users/' + user.id)
            .then(function(response) {
                // Remove user from the list
                var index = $scope.users.findIndex(function(u) {
                    return u.id === user.id;
                });
                
                if (index !== -1) {
                    $scope.users.splice(index, 1);
                }
                
                // Show success message
                $scope.showSuccess('User deleted successfully.');
            })
            .catch(function(error) {
                console.error('Error deleting user', error);
                
                // Show error message
                $scope.showError('Failed to delete user. Please try again later.');
            });
    };
    
    // Show success message
    $scope.showSuccess = function(message) {
        $scope.alertMessage = message;
        $scope.alertType = 'success';
        $scope.showAlert = true;
        
        // Hide alert after 5 seconds
        setTimeout(function() {
            $scope.$apply(function() {
                $scope.showAlert = false;
            });
        }, 5000);
    };
    
    // Show error message
    $scope.showError = function(message) {
        $scope.alertMessage = message;
        $scope.alertType = 'danger';
        $scope.showAlert = true;
        
        // Hide alert after 5 seconds
        setTimeout(function() {
            $scope.$apply(function() {
                $scope.showAlert = false;
            });
        }, 5000);
    };
}]);

// User Detail Modal Controller
app.controller('UserDetailModalController', ['$scope', '$uibModalInstance', 'user', function($scope, $uibModalInstance, user) {
    $scope.user = user;
    
    $scope.cancel = function() {
        $uibModalInstance.dismiss('cancel');
    };
}]); 