'use strict';

app.controller('AdminProductController', ['$scope', '$routeParams', '$location', 'ProductService', function($scope, $routeParams, $location, ProductService) {
    // Initialize controller
    $scope.init = function() {
        $scope.loading = {
            products: true,
            product: false,
            save: false,
            delete: false
        };
        
        $scope.products = [];
        $scope.product = {};
        $scope.productImages = {
            main: null,
            additional: []
        };
        $scope.error = null;
        $scope.success = null;
        $scope.currentPage = 1;
        $scope.totalPages = 1;
        $scope.itemsPerPage = 10;
        $scope.categories = [];
        
        // For specifications
        $scope.specKeys = [];
        $scope.specValues = [];
        
        // Check if we're editing or adding
        $scope.productId = $routeParams.id;
        $scope.isEditing = !!$scope.productId;
        
        // Load categories
        $scope.loadCategories();
        
        // If editing, load product details
        if ($scope.isEditing) {
            $scope.loadProduct($scope.productId);
        } else {
            // Initialize new product
            $scope.product = {
                name: '',
                description: '',
                price: 0,
                stock: 0,
                category_id: '',
                sku: '',
                weight: 0,
                is_active: true,
                is_featured: false,
                specifications: {}
            };
        }
        
        // If on product list page, load products
        if ($location.path() === '/admin/products') {
            $scope.loadProducts();
        }
    };
    
    // Load all products
    $scope.loadProducts = function(page) {
        $scope.loading.products = true;
        
        ProductService.getProducts({ page: page || 1, limit: $scope.itemsPerPage })
            .then(function(response) {
                $scope.products = response.data;
                $scope.totalItems = response.meta.total;
                $scope.currentPage = response.meta.current_page;
                $scope.totalPages = response.meta.last_page;
            })
            .catch(function(error) {
                console.error('Error loading products', error);
            })
            .finally(function() {
                $scope.loading.products = false;
            });
    };
    
    // Load single product
    $scope.loadProduct = function(id) {
        $scope.loading.product = true;
        
        ProductService.getProduct(id)
            .then(function(response) {
                $scope.product = response.data;
                
                // Set up specifications for editing
                if ($scope.product.specifications) {
                    var specs = typeof $scope.product.specifications === 'string' ? 
                        JSON.parse($scope.product.specifications) : $scope.product.specifications;
                    
                    $scope.product.specifications = specs;
                    
                    // Extract keys and values for the form
                    Object.keys(specs).forEach(function(key) {
                        $scope.specKeys.push(key);
                        $scope.specValues.push(specs[key]);
                    });
                } else {
                    $scope.product.specifications = {};
                }
            })
            .catch(function(error) {
                console.error('Error loading product', error);
                $scope.error = 'Failed to load product details';
            })
            .finally(function() {
                $scope.loading.product = false;
            });
    };
    
    // Load categories
    $scope.loadCategories = function() {
        ProductService.getCategories()
            .then(function(response) {
                $scope.categories = response.data;
            })
            .catch(function(error) {
                console.error('Error loading categories', error);
            });
    };
    
    // Save product
    $scope.saveProduct = function() {
        $scope.loading.save = true;
        $scope.error = null;
        $scope.success = null;
        
        // Process specifications
        var specifications = {};
        for (var i = 0; i < $scope.specKeys.length; i++) {
            if ($scope.specKeys[i] && $scope.specValues[i]) {
                specifications[$scope.specKeys[i]] = $scope.specValues[i];
            }
        }
        $scope.product.specifications = specifications;
        
        // Create FormData for file uploads
        var formData = new FormData();
        
        // Append product data
        Object.keys($scope.product).forEach(function(key) {
            if (key === 'specifications') {
                formData.append(key, JSON.stringify($scope.product[key]));
            } else {
                formData.append(key, $scope.product[key]);
            }
        });
        
        // Append images if any
        if ($scope.productImages.main) {
            formData.append('image', $scope.productImages.main);
        }
        
        if ($scope.productImages.additional && $scope.productImages.additional.length) {
            for (var i = 0; i < $scope.productImages.additional.length; i++) {
                formData.append('additional_images[]', $scope.productImages.additional[i]);
            }
        }
        
        var savePromise;
        if ($scope.isEditing) {
            savePromise = ProductService.updateProduct($scope.productId, formData);
        } else {
            savePromise = ProductService.createProduct(formData);
        }
        
        savePromise
            .then(function(response) {
                $scope.success = 'Product saved successfully';
                
                // Redirect to product list after a short delay
                setTimeout(function() {
                    $location.path('/admin/products');
                }, 1500);
            })
            .catch(function(error) {
                console.error('Error saving product', error);
                $scope.error = error.message || 'Failed to save product';
            })
            .finally(function() {
                $scope.loading.save = false;
            });
    };
    
    // Delete product
    $scope.deleteProduct = function(id) {
        if (!confirm('Are you sure you want to delete this product?')) {
            return;
        }
        
        $scope.loading.delete = true;
        
        ProductService.deleteProduct(id)
            .then(function() {
                $scope.success = 'Product deleted successfully';
                $scope.loadProducts($scope.currentPage);
            })
            .catch(function(error) {
                console.error('Error deleting product', error);
                $scope.error = 'Failed to delete product';
            })
            .finally(function() {
                $scope.loading.delete = false;
            });
    };
    
    // Add specification field
    $scope.addSpecification = function() {
        $scope.specKeys.push('');
        $scope.specValues.push('');
    };
    
    // Remove specification field
    $scope.removeSpecification = function(index) {
        $scope.specKeys.splice(index, 1);
        $scope.specValues.splice(index, 1);
    };
    
    // Remove additional image
    $scope.removeAdditionalImage = function(index) {
        $scope.product.additional_images.splice(index, 1);
    };
    
    // Initialize controller
    $scope.init();
}]); 