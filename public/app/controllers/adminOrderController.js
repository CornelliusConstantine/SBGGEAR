'use strict';

app.controller('AdminOrderController', ['$scope', '$http', '$routeParams', '$location', '$uibModal', 'AuthService', function($scope, $http, $routeParams, $location, $uibModal, AuthService) {
    // Setup CSRF token from Laravel global object for all requests
    var csrfToken = window.Laravel ? window.Laravel.csrfToken : document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (csrfToken) {
        $http.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
    }
    
    // Get session ID and include it in all requests
    $http.defaults.withCredentials = true;

    // For debugging
    console.log('CSRF Token:', csrfToken);
    console.log('User Auth:', window.Laravel ? window.Laravel.user : 'No user data in window.Laravel');
    // Force reload page if admin login was just performed
    if (sessionStorage.getItem('admin_login_redirect')) {
        sessionStorage.removeItem('admin_login_redirect');
        window.location.reload();
    }
    console.log('Current Location Path:', $location.path());
    console.log('Route Params:', $routeParams);

    // Initialize controller
    $scope.init = function() {
        console.log('Initializing AdminOrderController...');
        
        // Check if user is admin
        console.log('User Auth check:', window.Laravel ? window.Laravel.user : 'No user data', 
                   'Is Admin:', window.Laravel && window.Laravel.user ? window.Laravel.user.isAdmin : false);
                   
        if (!window.Laravel || !window.Laravel.user || window.Laravel.user.isAdmin !== true) {
            console.log('User is not admin, redirecting...');
            $location.path('/login').search({redirect: window.location.pathname});
            return;
        }
        
        $scope.loading = true;
        $scope.orders = [];
        $scope.order = null;
        $scope.filterStatus = '';
        $scope.showAlert = false;
        $scope.statusOptions = [
            'pending',
            'processing',
            'shipped',
            'delivered',
            'cancelled'
        ];
        
        // Check if we're on a specific order page
        if ($routeParams.id) {
            $scope.loadOrder($routeParams.id);
        } else {
            $scope.loadOrders();
        }
    };
    
    // Load all orders
    $scope.loadOrders = function() {
        $scope.loading = true;
        
        // Debug: Log before making request
        console.log('Loading orders from admin API endpoint');
        
        // Use direct admin API endpoint with web session
        var url = '/admin/api/orders';
        if ($scope.filterStatus) {
            url += '?status=' + $scope.filterStatus;
        }
        
        console.log('Sending request to:', url);
        
        // Use vanilla JavaScript to make the request
        var xhr = new XMLHttpRequest();
        xhr.open('GET', url, true);
        xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.setRequestHeader('Content-Type', 'application/json');
        // Ensure cookies are sent with the request
        xhr.withCredentials = true;
        // Prevent browser from trying to parse as HTML
        xhr.overrideMimeType('application/json; charset=utf-8');
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                $scope.$apply(function() {
                    $scope.loading = false;
                    console.log('XHR Status:', xhr.status);
                    console.log('Response Headers:', xhr.getAllResponseHeaders());
                    
                    if (xhr.status === 200) {
                        try {
                            var response = JSON.parse(xhr.responseText);
                            console.log('Orders loaded successfully:', response);
                            
                            if (response.data && Array.isArray(response.data)) {
                                $scope.orders = response.data.map(function(order, index) {
                                    order._uniqueIndex = index;
                                    return order;
                                });
                            } else {
                                console.error('Response data format is not as expected:', response);
                                $scope.orders = [];
                                $scope.showError('Invalid response format from server');
                            }
                        } catch (e) {
                            console.error('Error parsing JSON response:', e);
                            console.log('Raw response:', xhr.responseText.substring(0, 500)); // Show only first 500 chars
                            $scope.orders = [];
                            $scope.showError('Failed to parse server response: ' + e.message);
                        }
                    } else if (xhr.status === 401) {
                        console.error('Authentication error:', xhr.status, xhr.statusText);
                        console.log('Response:', xhr.responseText.substring(0, 500)); 
                        $scope.orders = [];
                        // Redirect to login page
                        window.location.href = '/login?redirect=' + encodeURIComponent(window.location.pathname);
                    } else {
                        console.error('HTTP error:', xhr.status, xhr.statusText);
                        console.log('Response:', xhr.responseText.substring(0, 500)); // Show only first 500 chars
                        $scope.orders = [];
                        $scope.showError('Failed to load orders: ' + xhr.statusText);
                    }
                });
            }
        };
        xhr.send();
    };
    
    // Load specific order
    $scope.loadOrder = function(orderId) {
        $scope.loading = true;
        
        var url = '/admin/api/orders/' + orderId;
        console.log('Loading specific order from:', url);
        
        // Use vanilla JavaScript to make the request
        var xhr = new XMLHttpRequest();
        xhr.open('GET', url, true);
        xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.setRequestHeader('Content-Type', 'application/json');
        // Ensure cookies are sent with the request
        xhr.withCredentials = true;
        // Prevent browser from trying to parse as HTML
        xhr.overrideMimeType('application/json; charset=utf-8');
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                $scope.$apply(function() {
                    $scope.loading = false;
                    console.log('XHR Status:', xhr.status);
                    console.log('Response Headers:', xhr.getAllResponseHeaders());
                    
                    if (xhr.status === 200) {
                        try {
                            var response = JSON.parse(xhr.responseText);
                            console.log('Order loaded successfully:', response);
                            $scope.order = response.data || response;
                        } catch (e) {
                            console.error('Error parsing JSON response:', e);
                            console.log('Raw response:', xhr.responseText.substring(0, 500)); // Show only first 500 chars
                            $scope.showError('Failed to parse server response: ' + e.message);
                            // Redirect back to orders list
                            $location.path('/admin/orders');
                        }
                    } else if (xhr.status === 401) {
                        console.error('Authentication error:', xhr.status, xhr.statusText);
                        console.log('Response:', xhr.responseText.substring(0, 500)); 
                        // Redirect to login page
                        window.location.href = '/login?redirect=' + encodeURIComponent(window.location.pathname);
                    } else {
                        console.error('HTTP error:', xhr.status, xhr.statusText);
                        console.log('Response:', xhr.responseText.substring(0, 500)); // Show only first 500 chars
                        $scope.showError('Failed to load order details: ' + xhr.statusText);
                        // Redirect back to orders list
                        $location.path('/admin/orders');
                    }
                });
            }
        };
        xhr.send();
    };
    
    // Update order status
    $scope.updateStatus = function(status) {
        if (!$scope.order || $scope.order.status === status) {
            return;
        }
        
        // Use the API endpoint
        $http.put('/admin/api/orders/' + $scope.order.id + '/status', {
            status: status
        }, {
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            withCredentials: true
        })
            .then(function(response) {
                // Update local order data
                $scope.order.status = status;
                
                // Show success message
                $scope.showSuccess('Order status updated successfully.');
            })
            .catch(function(error) {
                console.error('Error updating order status', error);
                
                // Show error message
                $scope.showError('Failed to update order status. Please try again later.');
            });
    };
    
    // Add tracking number
    $scope.addTrackingNumber = function() {
        var modalInstance = $uibModal.open({
            templateUrl: 'trackingNumberModal.html',
            controller: 'TrackingNumberModalController',
            resolve: {
                order: function() {
                    return $scope.order;
                }
            }
        });
        
        modalInstance.result.then(function(trackingNumber) {
            // Use the API endpoint
            $http.put('/admin/api/orders/' + $scope.order.id + '/tracking', {
                tracking_number: trackingNumber
            }, {
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                withCredentials: true
            })
                .then(function(response) {
                    // Update local order data
                    $scope.order.tracking_number = trackingNumber;
                    
                    // Show success message
                    $scope.showSuccess('Tracking number added successfully.');
                })
                .catch(function(error) {
                    console.error('Error adding tracking number', error);
                    
                    // Show error message
                    $scope.showError('Failed to add tracking number. Please try again later.');
                });
        });
    };
    
    // Add seat receipt number
    $scope.addSeatReceiptNumber = function() {
        var modalInstance = $uibModal.open({
            templateUrl: 'seatReceiptNumberModal.html',
            controller: 'SeatReceiptNumberModalController',
            resolve: {
                order: function() {
                    return $scope.order;
                }
            }
        });
        
        modalInstance.result.then(function(seatReceiptNumber) {
            // Use the API endpoint
            $http.put('/admin/api/orders/' + $scope.order.id + '/seat-receipt', {
                seat_receipt_number: seatReceiptNumber
            }, {
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                withCredentials: true
            })
                .then(function(response) {
                    // Update local order data
                    $scope.order.seat_receipt_number = seatReceiptNumber;
                    
                    // Show success message
                    $scope.showSuccess('Seat receipt number added successfully.');
                })
                .catch(function(error) {
                    console.error('Error adding seat receipt number', error);
                    
                    // Show error message
                    $scope.showError('Failed to add seat receipt number. Please try again later.');
                });
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

// Tracking Number Modal Controller
app.controller('TrackingNumberModalController', ['$scope', '$uibModalInstance', 'order', function($scope, $uibModalInstance, order) {
    $scope.order = order;
    $scope.trackingNumber = order.tracking_number || '';
    
    $scope.save = function() {
        $uibModalInstance.close($scope.trackingNumber);
    };
    
    $scope.cancel = function() {
        $uibModalInstance.dismiss('cancel');
    };
}]);

// Seat Receipt Number Modal Controller
app.controller('SeatReceiptNumberModalController', ['$scope', '$uibModalInstance', 'order', function($scope, $uibModalInstance, order) {
    $scope.order = order;
    $scope.seatReceiptNumber = order.seat_receipt_number || '';
    
    $scope.save = function() {
        $uibModalInstance.close($scope.seatReceiptNumber);
    };
    
    $scope.cancel = function() {
        $uibModalInstance.dismiss('cancel');
    };
}]); 