'use strict';

app.controller('ProductController', ['$scope', '$routeParams', '$location', 'ProductService', 'CartService', function($scope, $routeParams, $location, ProductService, CartService) {
    // Initialize controller
    $scope.init = function() {
        $scope.loading = true;
        $scope.products = [];
        $scope.categories = [];
        $scope.brands = [];
        $scope.product = null;
        $scope.quantity = 1;
        $scope.currentPage = 1;
        $scope.totalPages = 1;
        $scope.itemsPerPage = 12;
        $scope.searchQuery = '';
        $scope.searchSuggestions = [];
        
        // Toast notification
        $scope.toast = {
            show: false,
            message: '',
            type: 'success'
        };
        
        // Filter options
        $scope.filters = {
            category: $routeParams.slug || null,
            search: $routeParams.query || null,
            minPrice: null,
            maxPrice: null,
            sort: 'created_at,desc',
            selectedCategories: {},
            selectedBrands: {},
            selectedRatings: {}
        };
        
        // Check if we're on a product detail page
        if ($routeParams.id) {
            $scope.loadProductDetail($routeParams.id);
        } 
        // Check if we're on a search results page
        else if ($routeParams.query) {
            $scope.searchQuery = $routeParams.query;
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
        
        // Load unique brands for filter
        $scope.loadBrands();
    };
    
    // Load all products with filters
    $scope.loadProducts = function(page) {
        $scope.loading = true;
        
        // Set pagination
        var params = $scope.getFilterParams();
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
                $scope.showToast('Error loading products', 'error');
            })
            .finally(function() {
                $scope.loading = false;
            });
    };
    
    // Get filter parameters from the filter object
    $scope.getFilterParams = function() {
        var params = {};
        
        // Category filters
        var selectedCategories = [];
        for (var catId in $scope.filters.selectedCategories) {
            if ($scope.filters.selectedCategories[catId]) {
                selectedCategories.push(catId);
            }
        }
        if (selectedCategories.length > 0) {
            params.categories = selectedCategories.join(',');
        }
        
        // Brand filters
        var selectedBrands = [];
        for (var brand in $scope.filters.selectedBrands) {
            if ($scope.filters.selectedBrands[brand]) {
                selectedBrands.push(brand);
            }
        }
        if (selectedBrands.length > 0) {
            params.brands = selectedBrands.join(',');
        }
        
        // Rating filters
        var selectedRatings = [];
        for (var rating in $scope.filters.selectedRatings) {
            if ($scope.filters.selectedRatings[rating]) {
                selectedRatings.push(rating);
            }
        }
        if (selectedRatings.length > 0) {
            params.min_rating = Math.min.apply(null, selectedRatings);
        }
        
        // Price range
        if ($scope.filters.minPrice) {
            params.min_price = $scope.filters.minPrice;
        }
        if ($scope.filters.maxPrice) {
            params.max_price = $scope.filters.maxPrice;
        }
        
        // Sort options
        if ($scope.filters.sort) {
            var sortParts = $scope.filters.sort.split(',');
            params.sort = sortParts[0];
            params.direction = sortParts[1];
        }
        
        return params;
    };
    
    // Apply filters and reload products
    $scope.applyFilters = function() {
        $scope.currentPage = 1;
        $scope.loadProducts(1);
    };
    
    // Reset all filters
    $scope.resetFilters = function() {
        $scope.filters = {
            category: null,
            search: null,
            minPrice: null,
            maxPrice: null,
            sort: 'created_at,desc',
            selectedCategories: {},
            selectedBrands: {},
            selectedRatings: {}
        };
        $scope.applyFilters();
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
                $scope.showToast('Error loading product details', 'error');
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
            
            // Calculate discount percentage if original_price exists
            if (product.original_price && product.price < product.original_price) {
                product.discount_percentage = Math.round((1 - (product.price / product.original_price)) * 100);
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
                $scope.showToast('Error searching products', 'error');
            })
            .finally(function() {
                $scope.loading = false;
            });
    };
    
    // Search products from search bar
    $scope.search = function() {
        if (!$scope.searchQuery || $scope.searchQuery.trim() === '') return;
        
        $location.path('/search/' + $scope.searchQuery);
    };
    
    // Get search suggestions as user types
    $scope.getSearchSuggestions = function() {
        if ($scope.searchQuery.length < 3) {
            $scope.searchSuggestions = [];
            return;
        }
        
        ProductService.getSearchSuggestions($scope.searchQuery)
            .then(function(response) {
                $scope.searchSuggestions = response.data;
            })
            .catch(function(error) {
                console.error('Error getting search suggestions', error);
            });
    };
    
    // Watch for changes in search query to update suggestions
    $scope.$watch('searchQuery', function(newVal, oldVal) {
        if (newVal !== oldVal && newVal.length >= 3) {
            $scope.getSearchSuggestions();
        }
    });
    
    // Select a search suggestion
    $scope.selectSearchSuggestion = function(suggestion) {
        $scope.searchQuery = suggestion.name;
        $scope.search();
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
                $scope.showToast('Error loading category products', 'error');
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
    
    // Load unique brands for filtering
    $scope.loadBrands = function() {
        ProductService.getBrands()
            .then(function(response) {
                $scope.brands = response.data;
            })
            .catch(function(error) {
                console.error('Error loading brands', error);
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
                $scope.showToast('Error adding product to cart', 'error');
            })
            .finally(function() {
                $scope.addingToCart = null;
            });
    };
    
    // Change quantity on product detail page
    $scope.changeQuantity = function(change) {
        var newQuantity = $scope.quantity + change;
        
        // Don't allow quantity less than 1
        if (newQuantity < 1) return;
        
        // Don't allow quantity more than available stock
        if ($scope.product && $scope.product.stock && newQuantity > $scope.product.stock) return;
        
        $scope.quantity = newQuantity;
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
    
    // Pagination: Get array of page numbers to display
    $scope.getPageArray = function() {
        var pages = [];
        var totalPages = $scope.totalPages;
        var currentPage = $scope.currentPage;
        
        // Always show first page
        pages.push(1);
        
        // Show pages around current page
        for (var i = Math.max(2, currentPage - 2); i <= Math.min(totalPages - 1, currentPage + 2); i++) {
            pages.push(i);
        }
        
        // Always show last page if there is more than one page
        if (totalPages > 1) {
            pages.push(totalPages);
        }
        
        // Remove duplicates and sort
        return [...new Set(pages)].sort(function(a, b) {
            return a - b;
        });
    };
    
    // Go to specific page
    $scope.goToPage = function(page) {
        if (page < 1 || page > $scope.totalPages || page === $scope.currentPage) return;
        
        $scope.currentPage = page;
        $scope.loadProducts(page);
    };
    
    // Submit review for product
    $scope.submitReview = function() {
        if (!$scope.newReview || !$scope.newReview.rating || !$scope.newReview.comment) {
            $scope.showToast('Please provide both rating and comment', 'error');
            return;
        }
        
        $scope.submittingReview = true;
        
        ProductService.submitReview($scope.product.id, $scope.newReview)
            .then(function(response) {
                $scope.showToast('Review submitted successfully');
                
                // Refresh product details to show the new review
                $scope.loadProductDetail($scope.product.id);
                
                // Reset review form
                $scope.newReview = {
                    rating: 5,
                    comment: ''
                };
            })
            .catch(function(error) {
                console.error('Error submitting review', error);
                $scope.showToast('Error submitting review', 'error');
            })
            .finally(function() {
                $scope.submittingReview = false;
            });
    };
    
    // Initialize controller
    $scope.init();
}]); 