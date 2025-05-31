'use strict';

app.controller('AdminController', ['$scope', '$http', '$filter', 'AuthService', 'ProductService', 'OrderService', function($scope, $http, $filter, AuthService, ProductService, OrderService) {
    // Initialize controller
    $scope.init = function() {
        // Check if user is admin
        if (!AuthService.isAdmin()) {
            window.location.href = '#!/';
            return;
        }
        
        // Show admin mode indicator
        $scope.showAdminModeIndicator();
        
        // Initialize data
        $scope.today = new Date();
        $scope.stats = {};
        $scope.recentOrders = [];
        $scope.lowStockProducts = [];
        
        // Loading states
        $scope.loading = {
            stats: true,
            recentOrders: true,
            lowStock: true
        };
        
        // Toast notifications
        $scope.toast = {
            show: false,
            message: '',
            type: 'success'
        };
        
        // Load dashboard data
        $scope.loadDashboardData();
    };
    
    // Show admin mode indicator on all admin pages
    $scope.showAdminModeIndicator = function() {
        // Remove existing indicator if any
        var existingIndicator = document.getElementById('admin-mode-indicator');
        if (existingIndicator) {
            existingIndicator.remove();
        }
        
        // Create new indicator
        var indicator = document.createElement('div');
        indicator.id = 'admin-mode-indicator';
        indicator.className = 'admin-mode-indicator';
        indicator.innerHTML = '<i class="fas fa-user-shield"></i> Admin Mode';
        
        // Add to body
        document.body.appendChild(indicator);
    };
    
    // Load dashboard data
    $scope.loadDashboardData = function() {
        // Simulate loading dashboard data
        setTimeout(function() {
            // Mock stats data
            $scope.$apply(function() {
                $scope.stats = {
                    totalSales: 12500000,
                    totalOrders: 45,
                    totalProducts: 120,
                    totalCustomers: 89
                };
                $scope.loading.stats = false;
            });
        }, 800);
        
        // Simulate loading recent orders
        setTimeout(function() {
            // Mock recent orders data
            $scope.$apply(function() {
                $scope.recentOrders = [
                    {
                        id: 1,
                        order_number: 'ORD-2023-001',
                        created_at: '2023-06-15T08:30:00',
                        recipient_name: 'John Doe',
                        total: 750000,
                        status: 'delivered'
                    },
                    {
                        id: 2,
                        order_number: 'ORD-2023-002',
                        created_at: '2023-06-16T10:15:00',
                        recipient_name: 'Jane Smith',
                        total: 1250000,
                        status: 'processing'
                    },
                    {
                        id: 3,
                        order_number: 'ORD-2023-003',
                        created_at: '2023-06-16T14:45:00',
                        recipient_name: 'Robert Johnson',
                        total: 500000,
                        status: 'pending'
                    },
                    {
                        id: 4,
                        order_number: 'ORD-2023-004',
                        created_at: '2023-06-17T09:20:00',
                        recipient_name: 'Emily Williams',
                        total: 1800000,
                        status: 'shipped'
                    },
                    {
                        id: 5,
                        order_number: 'ORD-2023-005',
                        created_at: '2023-06-17T16:10:00',
                        recipient_name: 'Michael Brown',
                        total: 950000,
                        status: 'cancelled'
                    }
                ];
                $scope.loading.recentOrders = false;
            });
        }, 1200);
        
        // Simulate loading low stock products
        setTimeout(function() {
            // Mock low stock products data
            $scope.$apply(function() {
                $scope.lowStockProducts = [
                    {
                        id: 1,
                        name: 'Safety Helmet Type A',
                        stock: 3,
                        category: { name: 'Head Protection' }
                    },
                    {
                        id: 2,
                        name: 'Safety Goggles',
                        stock: 5,
                        category: { name: 'Eye Protection' }
                    },
                    {
                        id: 3,
                        name: 'Reflective Vest XL',
                        stock: 2,
                        category: { name: 'Visibility' }
                    }
                ];
                $scope.loading.lowStock = false;
            });
        }, 1500);
    };
    
    // Load dashboard statistics
    $scope.loadStats = function() {
        $scope.loading.stats = true;
        
        var token = localStorage.getItem('token');
        
        $http.get('/api/admin/dashboard/stats', {
            headers: {
                'Authorization': 'Bearer ' + token
            }
        })
            .then(function(response) {
                $scope.stats = response.data;
            })
            .catch(function(error) {
                console.error('Error loading dashboard stats', error);
            })
            .finally(function() {
                $scope.loading.stats = false;
            });
    };
    
    // Load recent orders
    $scope.loadRecentOrders = function() {
        $scope.loading.recentOrders = true;
        
        OrderService.getAllOrders({ limit: 5, sort: 'created_at', direction: 'desc' })
            .then(function(response) {
                $scope.recentOrders = response.data;
            })
            .catch(function(error) {
                console.error('Error loading recent orders', error);
            })
            .finally(function() {
                $scope.loading.recentOrders = false;
            });
    };
    
    // Load low stock products
    $scope.loadLowStockProducts = function() {
        $scope.loading.lowStock = true;
        
        var token = localStorage.getItem('token');
        
        $http.get('/api/admin/products/low-stock', {
            headers: {
                'Authorization': 'Bearer ' + token
            }
        })
            .then(function(response) {
                $scope.lowStockProducts = response.data;
            })
            .catch(function(error) {
                console.error('Error loading low stock products', error);
            })
            .finally(function() {
                $scope.loading.lowStock = false;
            });
    };
    
    // Get status class
    $scope.getStatusClass = function(status) {
        switch (status) {
            case 'pending':
                return 'text-warning';
            case 'processing':
                return 'text-primary';
            case 'shipped':
                return 'text-info';
            case 'delivered':
                return 'text-success';
            case 'cancelled':
                return 'text-danger';
            default:
                return 'text-secondary';
        }
    };
    
    // Format date
    $scope.formatDate = function(dateString) {
        return $filter('date')(new Date(dateString), 'dd MMM yyyy');
    };
    
    // Format currency
    $scope.formatCurrency = function(value) {
        return 'Rp' + $filter('number')(value);
    };
    
    // Show toast notification
    $scope.showToast = function(message, type) {
        $scope.toast.message = message;
        $scope.toast.type = type || 'success';
        $scope.toast.show = true;
        
        // Auto hide after 3 seconds
        setTimeout(function() {
            $scope.$apply(function() {
                $scope.toast.show = false;
            });
        }, 3000);
    };
    
    // Clean up when controller is destroyed
    $scope.$on('$destroy', function() {
        // Remove admin mode indicator when leaving admin pages
        var indicator = document.getElementById('admin-mode-indicator');
        if (indicator) {
            indicator.remove();
        }
    });
    
    // Initialize controller
    $scope.init();
}]);

// Admin Product Controller
app.controller('AdminProductController', ['$scope', '$routeParams', '$location', 'ProductService', function($scope, $routeParams, $location, ProductService) {
    // Initialize controller
    $scope.init = function() {
        $scope.loading = {
            products: true,
            product: false,
            categories: true
        };
        
        $scope.products = [];
        $scope.categories = [];
        $scope.product = {
            name: '',
            description: '',
            price: 0,
            stock: 0,
            category_id: '',
            is_featured: false,
            is_active: true
        };
        
        $scope.isEditing = !!$routeParams.id;
        $scope.errors = {};
        
        // Load categories
        $scope.loadCategories();
        
        // Check if we're editing a product
        if ($scope.isEditing) {
            $scope.loadProduct($routeParams.id);
        } else {
            $scope.loadProducts();
        }
    };
    
    // Load all products
    $scope.loadProducts = function() {
        $scope.loading.products = true;
        
        ProductService.getProducts({ admin: true })
            .then(function(response) {
                $scope.products = response.data;
            })
            .catch(function(error) {
                console.error('Error loading products', error);
            })
            .finally(function() {
                $scope.loading.products = false;
            });
    };
    
    // Load specific product
    $scope.loadProduct = function(id) {
        $scope.loading.product = true;
        
        ProductService.getProduct(id)
            .then(function(response) {
                $scope.product = response.data;
            })
            .catch(function(error) {
                console.error('Error loading product', error);
            })
            .finally(function() {
                $scope.loading.product = false;
            });
    };
    
    // Load categories
    $scope.loadCategories = function() {
        $scope.loading.categories = true;
        
        ProductService.getCategories()
            .then(function(response) {
                $scope.categories = response.data;
            })
            .catch(function(error) {
                console.error('Error loading categories', error);
            })
            .finally(function() {
                $scope.loading.categories = false;
            });
    };
    
    // Save product
    $scope.saveProduct = function() {
        if (!$scope.validateProduct()) {
            return;
        }
        
        $scope.saving = true;
        
        if ($scope.isEditing) {
            ProductService.updateProduct($scope.product.id, $scope.product)
                .then(function(response) {
                    $scope.showToast('Product updated successfully');
                    $location.path('/admin/products');
                })
                .catch(function(error) {
                    console.error('Error updating product', error);
                    $scope.showToast('Failed to update product', 'error');
                    
                    if (error.errors) {
                        $scope.errors = error.errors;
                    }
                })
                .finally(function() {
                    $scope.saving = false;
                });
        } else {
            ProductService.createProduct($scope.product)
                .then(function(response) {
                    $scope.showToast('Product created successfully');
                    $location.path('/admin/products');
                })
                .catch(function(error) {
                    console.error('Error creating product', error);
                    $scope.showToast('Failed to create product', 'error');
                    
                    if (error.errors) {
                        $scope.errors = error.errors;
                    }
                })
                .finally(function() {
                    $scope.saving = false;
                });
        }
    };
    
    // Delete product
    $scope.deleteProduct = function(id) {
        if (!confirm('Are you sure you want to delete this product?')) {
            return;
        }
        
        $scope.deleting = id;
        
        ProductService.deleteProduct(id)
            .then(function(response) {
                $scope.showToast('Product deleted successfully');
                $scope.loadProducts();
            })
            .catch(function(error) {
                console.error('Error deleting product', error);
                $scope.showToast('Failed to delete product', 'error');
            })
            .finally(function() {
                $scope.deleting = null;
            });
    };
    
    // Validate product form
    $scope.validateProduct = function() {
        $scope.errors = {};
        var isValid = true;
        
        if (!$scope.product.name) {
            $scope.errors.name = 'Product name is required';
            isValid = false;
        }
        
        if (!$scope.product.category_id) {
            $scope.errors.category_id = 'Category is required';
            isValid = false;
        }
        
        if (!$scope.product.price || $scope.product.price <= 0) {
            $scope.errors.price = 'Price must be greater than 0';
            isValid = false;
        }
        
        if ($scope.product.stock < 0) {
            $scope.errors.stock = 'Stock cannot be negative';
            isValid = false;
        }
        
        return isValid;
    };
    
    // Handle image upload
    $scope.handleImageUpload = function(event) {
        var file = event.target.files[0];
        if (file) {
            $scope.product.image = file;
            
            // Preview image
            var reader = new FileReader();
            reader.onload = function(e) {
                $scope.$apply(function() {
                    $scope.imagePreview = e.target.result;
                });
            };
            reader.readAsDataURL(file);
        }
    };
    
    // Cancel editing
    $scope.cancel = function() {
        $location.path('/admin/products');
    };
    
    // Initialize controller
    $scope.init();
}]);

// Admin Order Controller
app.controller('AdminOrderController', ['$scope', '$routeParams', '$location', 'OrderService', function($scope, $routeParams, $location, OrderService) {
    // Initialize controller
    $scope.init = function() {
        $scope.loading = true;
        $scope.orders = [];
        $scope.order = null;
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
        
        OrderService.getAllOrders()
            .then(function(response) {
                $scope.orders = response.data;
            })
            .catch(function(error) {
                console.error('Error loading orders', error);
            })
            .finally(function() {
                $scope.loading = false;
            });
    };
    
    // Load specific order
    $scope.loadOrder = function(orderId) {
        $scope.loading = true;
        
        OrderService.getOrder(orderId)
            .then(function(response) {
                $scope.order = response.data;
            })
            .catch(function(error) {
                console.error('Error loading order', error);
            })
            .finally(function() {
                $scope.loading = false;
            });
    };
    
    // Update order status
    $scope.updateOrderStatus = function(status) {
        $scope.updatingStatus = true;
        
        OrderService.updateOrderStatus($scope.order.id, status)
            .then(function(response) {
                $scope.order = response.data;
                $scope.showToast('Order status updated successfully');
            })
            .catch(function(error) {
                console.error('Error updating order status', error);
                $scope.showToast('Failed to update order status', 'error');
            })
            .finally(function() {
                $scope.updatingStatus = false;
            });
    };
    
    // Get status class
    $scope.getStatusClass = function(status) {
        switch (status) {
            case 'pending':
                return 'text-warning';
            case 'processing':
                return 'text-primary';
            case 'shipped':
                return 'text-info';
            case 'delivered':
                return 'text-success';
            case 'cancelled':
                return 'text-danger';
            default:
                return 'text-secondary';
        }
    };
    
    // View order details
    $scope.viewOrder = function(orderId) {
        $location.path('/admin/order/' + orderId);
    };
    
    // Back to orders list
    $scope.backToOrders = function() {
        $location.path('/admin/orders');
    };
    
    // Initialize controller
    $scope.init();
}]); 