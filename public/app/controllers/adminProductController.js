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
        $scope.product = {
            name: '',
            description: '',
            price: 0,
            stock: 0,
            weight: 0,
            category_id: '',
            specifications: {},
            is_active: true,
            is_featured: false
        };
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
                weight: 0,
                category_id: '',
                specifications: {},
                is_active: true,
                is_featured: false
            };
            
            // Initialize with one empty specification row
            $scope.addSpecification();
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
        $scope.error = null;
        
        ProductService.getProduct(id)
            .then(function(response) {
                // Check if we have a valid product object
                if (!response.data) {
                    throw new Error('Invalid product data received');
                }
                
                $scope.product = response.data;
                
                // Set up specifications for editing
                if ($scope.product.specifications) {
                    try {
                        var specs = typeof $scope.product.specifications === 'string' ? 
                            JSON.parse($scope.product.specifications) : $scope.product.specifications;
                        
                        $scope.product.specifications = specs;
                        $scope.specKeys = [];
                        $scope.specValues = [];
                        
                        // Extract keys and values for the form
                        Object.keys(specs).forEach(function(key) {
                            $scope.specKeys.push(key);
                            $scope.specValues.push(specs[key]);
                        });
                    } catch (e) {
                        console.error('Error parsing specifications:', e);
                        $scope.product.specifications = {};
                        $scope.specKeys = [];
                        $scope.specValues = [];
                    }
                } else {
                    $scope.product.specifications = {};
                    $scope.specKeys = [];
                    $scope.specValues = [];
                }
                
                // If no specifications, add an empty row
                if ($scope.specKeys.length === 0) {
                    $scope.addSpecification();
                }
                
                // Process images
                if ($scope.product.images) {
                    try {
                        if (typeof $scope.product.images === 'string') {
                            $scope.product.images = JSON.parse($scope.product.images);
                        }
                        
                        // Set thumbnail URL if available
                        if ($scope.product.images && $scope.product.images.main) {
                            $scope.product.thumbnail_url = '/storage/products/thumbnails/' + $scope.product.images.main;
                        }
                        
                        // Set gallery URLs if available
                        if ($scope.product.images && $scope.product.images.gallery && Array.isArray($scope.product.images.gallery)) {
                            $scope.product.gallery_urls = $scope.product.images.gallery.map(function(image) {
                                return {
                                    original: '/storage/products/original/' + image,
                                    thumbnail: '/storage/products/thumbnails/' + image
                                };
                            });
                        } else {
                            $scope.product.gallery_urls = [];
                        }
                    } catch (e) {
                        console.error('Error processing product images:', e);
                        $scope.product.gallery_urls = [];
                    }
                }
            })
            .catch(function(error) {
                console.error('Error loading product:', error);
                $scope.error = error.message || 'Failed to load product details. The product may not exist or has been removed.';
                
                // Initialize empty product to prevent UI errors
                $scope.product = {
                    name: '',
                    description: '',
                    price: 0,
                    stock: 0,
                    weight: 0,
                    category_id: '',
                    specifications: {},
                    is_active: true,
                    is_featured: false
                };
                $scope.specKeys = [];
                $scope.specValues = [];
                $scope.addSpecification();
            })
            .finally(function() {
                $scope.loading.product = false;
            });
    };
    
    // Load categories
    $scope.loadCategories = function() {
        console.log('AdminProductController: Loading categories...');
        
        ProductService.getCategories()
            .then(function(response) {
                console.log('AdminProductController: Categories loaded:', response);
                // Handle both old and new response formats
                if (response.data && Array.isArray(response.data.data)) {
                    $scope.categories = response.data.data;
                } else if (response.data && Array.isArray(response.data)) {
                    $scope.categories = response.data;
                } else {
                    $scope.categories = [];
                }
                console.log('AdminProductController: Categories in scope:', $scope.categories);
            })
            .catch(function(error) {
                console.error('AdminProductController: Error loading categories', error);
            });
    };
    
    // Save product
    $scope.saveProduct = function() {
        $scope.loading.save = true;
        $scope.error = null;
        $scope.success = null;
        
        // Validate required fields
        if (!$scope.product.name) {
            $scope.error = 'Product name is required';
            $scope.loading.save = false;
            return;
        }
        
        if (!$scope.product.description) {
            $scope.error = 'Product description is required';
            $scope.loading.save = false;
            return;
        }
        
        if (!$scope.product.price || $scope.product.price <= 0) {
            $scope.error = 'Valid product price is required';
            $scope.loading.save = false;
            return;
        }
        
        if (!$scope.product.category_id) {
            $scope.error = 'Please select a category';
            $scope.loading.save = false;
            return;
        }
        
        // Process specifications
        var specifications = {};
        for (var i = 0; i < $scope.specKeys.length; i++) {
            if ($scope.specKeys[i] && $scope.specValues[i]) {
                specifications[$scope.specKeys[i]] = $scope.specValues[i];
            }
        }
        
        // Create FormData for file uploads
        var formData = new FormData();
        
        // Ensure boolean fields are properly formatted
        var productData = angular.copy($scope.product);
        productData.is_active = productData.is_active === true;
        productData.is_featured = productData.is_featured === true;
        
        // Append product data
        Object.keys(productData).forEach(function(key) {
            if (key === 'specifications') {
                formData.append(key, JSON.stringify(specifications));
            } else if (key === 'is_active' || key === 'is_featured') {
                formData.append(key, productData[key] ? '1' : '0');
            } else {
                formData.append(key, productData[key]);
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
                    $scope.$apply(function() {
                        $location.path('/admin/products');
                    });
                }, 1500);
            })
            .catch(function(error) {
                console.error('Error saving product', error);
                
                // Handle specific error messages
                if (error && error.message) {
                    $scope.error = error.message;
                } else if (error && error.errors) {
                    // Format validation errors
                    var errorMessages = [];
                    for (var field in error.errors) {
                        errorMessages.push(error.errors[field].join(', '));
                    }
                    $scope.error = errorMessages.join('. ');
                } else {
                    $scope.error = 'Failed to save product. Please try again.';
                }
                
                // Scroll to error message
                setTimeout(function() {
                    var errorElement = document.querySelector('.alert-danger');
                    if (errorElement) {
                        errorElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }, 100);
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
        
        // Check if user is authenticated
        var token = localStorage.getItem('token');
        if (!token) {
            alert('You must be logged in to delete products');
            $location.path('/login');
            return;
        }
        
        $scope.loading.delete = id; // Track which product is being deleted
        $scope.error = null;
        $scope.success = null;
        
        console.log('Deleting product ID:', id);
        
        ProductService.deleteProduct(id)
            .then(function(response) {
                console.log('Product deletion successful:', response);
                $scope.success = 'Product deleted successfully';
                // Reload the current page of products
                $scope.loadProducts($scope.currentPage);
            })
            .catch(function(error) {
                console.error('Error deleting product:', error);
                
                // Create a detailed error message
                var errorMessage = 'Failed to delete product';
                
                if (error.message) {
                    errorMessage += ': ' + error.message;
                }
                
                if (error.status) {
                    errorMessage += ' (Status: ' + error.status + ')';
                }
                
                $scope.error = errorMessage;
                
                // Show alert to user
                alert(errorMessage);
                
                // If unauthorized, redirect to login
                if (error.status === 401) {
                    localStorage.removeItem('token');
                    $location.path('/login');
                }
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