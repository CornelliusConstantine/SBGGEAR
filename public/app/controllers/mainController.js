'use strict';

app.controller('MainController', ['$scope', 'ProductService', 'CartService', '$location', 'CategoryService', function($scope, ProductService, CartService, $location, CategoryService) {
    // Initialize controller
    $scope.init = function() {
        $scope.loading = {
            featuredProducts: true,
            newArrivals: true,
            bestSellers: true,
            categories: true
        };
        
        $scope.featuredProducts = [];
        $scope.newArrivals = [];
        $scope.bestSellers = [];
        $scope.categories = [];
        
        // Initialize toast
        $scope.toast = {
            show: false,
            message: '',
            type: 'success'
        };
        
        // Load featured products
        ProductService.getProducts({ featured: true, limit: 4 })
            .then(function(response) {
                $scope.featuredProducts = response.data;
                $scope.processProductData($scope.featuredProducts);
            })
            .catch(function(error) {
                console.error('Error loading featured products', error);
            })
            .finally(function() {
                $scope.loading.featuredProducts = false;
            });
        
        // Load new arrivals
        ProductService.getProducts({ sort: 'created_at', direction: 'desc', limit: 4 })
            .then(function(response) {
                $scope.newArrivals = response.data;
                $scope.processProductData($scope.newArrivals);
            })
            .catch(function(error) {
                console.error('Error loading new arrivals', error);
            })
            .finally(function() {
                $scope.loading.newArrivals = false;
            });
        
        // Load best sellers
        ProductService.getProducts({ sort: 'stock', direction: 'asc', limit: 4 })
            .then(function(response) {
                $scope.bestSellers = response.data;
                $scope.processProductData($scope.bestSellers);
            })
            .catch(function(error) {
                console.error('Error loading best sellers', error);
            })
            .finally(function() {
                $scope.loading.bestSellers = false;
            });
        
        // Load categories
        CategoryService.getCategories()
            .then(function(response) {
                console.log('MainController: Categories loaded:', response);
                $scope.categories = response.data;
            })
            .catch(function(error) {
                console.error('MainController: Error loading categories', error);
            })
            .finally(function() {
                $scope.loading.categories = false;
            });
    };
    
    // Navigate to category page
    $scope.goToCategory = function(slug) {
        if (!slug) {
            console.error('No category slug provided');
            return;
        }
        
        console.log('Navigating to category:', slug);
        
        // First check if the category exists
        CategoryService.getCategory(slug)
            .then(function(categoryData) {
                // Category exists, navigate to it
                $location.path('/category/' + slug);
            })
            .catch(function(error) {
                console.error('Error checking category before navigation:', error);
                // Show error toast
                $scope.showToast(error.message || 'Category not found', 'error');
            });
    };
    
    // Process product data to add missing fields needed by the frontend
    $scope.processProductData = function(products) {
        if (!products) return;
        
        products.forEach(function(product) {
            // Add image_url if not present
            if (!product.image_url && product.images) {
                try {
                    const images = typeof product.images === 'string' ? JSON.parse(product.images) : product.images;
                    if (images && images.main) {
                        product.image_url = '/storage/products/' + images.main;
                    }
                } catch (e) {
                    console.error('Error parsing product images', e);
                }
            }
            
            // Add default rating if not present
            if (!product.hasOwnProperty('rating')) {
                product.rating = 4;
            }
            
            // Add default rating_count if not present
            if (!product.hasOwnProperty('rating_count')) {
                product.rating_count = 0;
            }
            
            // Add default short_description if not present
            if (!product.hasOwnProperty('short_description')) {
                product.short_description = product.description;
            }
            
            // Add default is_featured if not present
            if (!product.hasOwnProperty('is_featured')) {
                product.is_featured = false;
            }
        });
    };
    
    // Add product to cart
    $scope.addToCart = function(product) {
        $scope.addingToCart = product.id;
        
        CartService.addToCart(product, 1)
            .then(function() {
                // Show success message or update UI
                $scope.showToast('Product added to cart');
            })
            .catch(function(error) {
                console.error('Error adding product to cart', error);
                $scope.showToast('Failed to add product to cart', 'error');
            })
            .finally(function() {
                $scope.addingToCart = null;
            });
    };
    
    // Show toast notification
    $scope.showToast = function(message, type) {
        $scope.toast = {
            show: true,
            message: message,
            type: type || 'success'
        };
        
        // Auto-hide toast after 3 seconds
        setTimeout(function() {
            $scope.$apply(function() {
                $scope.toast.show = false;
            });
        }, 3000);
    };
    
    // Initialize controller
    $scope.init();
}]); 