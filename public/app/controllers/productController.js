'use strict';

app.controller('ProductController', ['$scope', '$routeParams', '$location', 'ProductService', 'CartService', function($scope, $routeParams, $location, ProductService, CartService) {
    // Initialize controller
    $scope.init = function() {
        $scope.loading = true;
        $scope.products = [];
        $scope.categories = [];
        $scope.product = null;
        $scope.quantity = 1;
        $scope.currentPage = 1;
        $scope.totalPages = 1;
        $scope.itemsPerPage = 12;
        
        // Filter options
        $scope.filters = {
            category: $routeParams.slug || null,
            search: $routeParams.query || null,
            minPrice: null,
            maxPrice: null,
            sort: 'created_at',
            direction: 'desc'
        };
        
        // Check if we're on a product detail page
        if ($routeParams.id) {
            $scope.loadProductDetail($routeParams.id);
        } 
        // Check if we're on a search results page
        else if ($routeParams.query) {
            $scope.loadSearchResults($routeParams.query);
        }
        // Check if we're on a category page
        else if ($routeParams.slug) {
            $scope.loadCategoryProducts($routeParams.slug);
        }
        // Otherwise, load all products
        else {
            $scope.loadProducts();
        }
        
        // Load categories for filter sidebar
        $scope.loadCategories();
    };
    
    // Load all products with filters
    $scope.loadProducts = function(page) {
        $scope.loading = true;
        
        // Set pagination
        var params = angular.copy($scope.filters);
        params.page = page || $scope.currentPage;
        params.limit = $scope.itemsPerPage;
        
        ProductService.getProducts(params)
            .then(function(response) {
                $scope.products = response.data;
                
                // Process product data
                $scope.processProductData($scope.products);
                
                $scope.totalItems = response.meta ? response.meta.total : $scope.products.length;
                $scope.totalPages = Math.ceil($scope.totalItems / $scope.itemsPerPage);
                $scope.currentPage = response.meta ? response.meta.current_page : 1;
            })
            .catch(function(error) {
                console.error('Error loading products', error);
            })
            .finally(function() {
                $scope.loading = false;
            });
    };
    
    // Load product detail
    $scope.loadProductDetail = function(id) {
        $scope.loading = true;
        
        ProductService.getProduct(id)
            .then(function(response) {
                $scope.product = response.data;
                
                // Process product data
                $scope.processProductData([$scope.product]);
                
                // Parse specifications from JSON if needed
                if (typeof $scope.product.specifications === 'string') {
                    try {
                        $scope.product.specifications = JSON.parse($scope.product.specifications);
                    } catch (e) {
                        $scope.product.specifications = {};
                    }
                }
                
                // Parse images from JSON if needed
                if (typeof $scope.product.images === 'string') {
                    try {
                        $scope.product.images = JSON.parse($scope.product.images);
                    } catch (e) {
                        $scope.product.images = {};
                    }
                }
                
                // Load related products
                return ProductService.getProducts({
                    category_id: $scope.product.category_id,
                    exclude_id: $scope.product.id,
                    limit: 4
                });
            })
            .then(function(response) {
                $scope.relatedProducts = response.data;
                
                // Process related products data
                $scope.processProductData($scope.relatedProducts);
            })
            .catch(function(error) {
                console.error('Error loading product detail', error);
            })
            .finally(function() {
                $scope.loading = false;
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
            
            // Add default brand if not present
            if (!product.hasOwnProperty('brand')) {
                product.brand = 'SBG';
            }
            
            // Add default is_featured if not present
            if (!product.hasOwnProperty('is_featured')) {
                product.is_featured = false;
            }
        });
    };
    
    // Load search results
    $scope.loadSearchResults = function(query) {
        $scope.loading = true;
        $scope.searchQuery = query;
        
        ProductService.searchProducts(query)
            .then(function(response) {
                $scope.products = response.data;
                
                // Process product data
                $scope.processProductData($scope.products);
                
                $scope.totalItems = response.data.length;
                $scope.totalPages = Math.ceil($scope.totalItems / $scope.itemsPerPage);
            })
            .catch(function(error) {
                console.error('Error loading search results', error);
            })
            .finally(function() {
                $scope.loading = false;
            });
    };
    
    // Load products by category
    $scope.loadCategoryProducts = function(slug) {
        $scope.loading = true;
        
        // First load category details
        ProductService.getCategory(slug)
            .then(function(response) {
                $scope.category = response.data;
                
                // Then load products in that category
                return ProductService.getProductsByCategory(slug, {
                    page: $scope.currentPage,
                    limit: $scope.itemsPerPage,
                    sort: $scope.filters.sort,
                    direction: $scope.filters.direction,
                    min_price: $scope.filters.minPrice,
                    max_price: $scope.filters.maxPrice
                });
            })
            .then(function(response) {
                $scope.products = response.data;
                
                // Process product data
                $scope.processProductData($scope.products);
                
                $scope.totalItems = response.meta ? response.meta.total : $scope.products.length;
                $scope.totalPages = Math.ceil($scope.totalItems / $scope.itemsPerPage);
                $scope.currentPage = response.meta ? response.meta.current_page : 1;
            })
            .catch(function(error) {
                console.error('Error loading category products', error);
            })
            .finally(function() {
                $scope.loading = false;
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
    
    // Add product to cart
    $scope.addToCart = function(product, quantity) {
        $scope.addingToCart = product.id;
        
        CartService.addToCart(product, quantity || $scope.quantity || 1)
            .then(function() {
                // Show success message
                $scope.showToast('Product added to cart');
                
                // Reset quantity if on product detail page
                if ($scope.product) {
                    $scope.quantity = 1;
                }
            })
            .catch(function(error) {
                console.error('Error adding product to cart', error);
                $scope.showToast('Failed to add product to cart', 'error');
            })
            .finally(function() {
                $scope.addingToCart = null;
            });
    };
    
    // Change quantity
    $scope.changeQuantity = function(amount) {
        $scope.quantity = Math.max(1, $scope.quantity + amount);
    };
    
    // Apply filters
    $scope.applyFilters = function() {
        $scope.currentPage = 1;
        
        if ($routeParams.slug) {
            $scope.loadCategoryProducts($routeParams.slug);
        } else {
            $scope.loadProducts();
        }
    };
    
    // Change sort order
    $scope.changeSort = function(sort) {
        $scope.filters.sort = sort;
        $scope.applyFilters();
    };
    
    // Change page
    $scope.changePage = function(page) {
        if (page < 1 || page > $scope.totalPages) {
            return;
        }
        
        $scope.currentPage = page;
        
        if ($routeParams.slug) {
            $scope.loadCategoryProducts($routeParams.slug);
        } else {
            $scope.loadProducts(page);
        }
        
        // Scroll to top
        window.scrollTo(0, 0);
    };
    
    // Filter by category
    $scope.filterByCategory = function(categoryId) {
        $scope.filters.category = categoryId;
        $scope.applyFilters();
    };
    
    // Show toast message
    $scope.showToast = function(message, type) {
        $scope.toast = {
            message: message,
            type: type || 'success',
            show: true
        };
        
        // Hide toast after 3 seconds
        setTimeout(function() {
            $scope.$apply(function() {
                $scope.toast.show = false;
            });
        }, 3000);
    };
    
    // Initialize controller
    $scope.init();
}]); 