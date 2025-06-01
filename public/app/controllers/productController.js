'use strict';

app.controller('ProductController', ['$scope', '$routeParams', '$location', 'ProductService', 'CartService', '$rootScope', 'CategoryService', function($scope, $routeParams, $location, ProductService, CartService, $rootScope, CategoryService) {
    // Initialize controller
    $scope.init = function() {
        $scope.loading = true;
        $scope.products = [];
        $scope.categories = [];
        $scope.totalItems = 0;
        $scope.currentPage = 1;
        $scope.itemsPerPage = 12;
        $scope.totalPages = 0;
        $scope.searchQuery = $routeParams.query || '';
        $scope.searchSuggestions = [];
        $scope.productId = $routeParams.id;
        $scope.categorySlug = $routeParams.slug; // Get category slug from route params
        $scope.quantity = 1;
        $scope.toast = {
            show: false,
            message: '',
            type: 'success'
        };
        
        // Initialize filters
        $scope.filters = {
            sort: 'created_at',
            direction: 'desc',
            minPrice: '',
            maxPrice: '',
            rating: '',
            selectedCategories: {},
            category: $location.search().category || null
        };
        
        // Get filters from URL query params
        var queryParams = $location.search();
        if (queryParams.sort) {
            var sortParts = queryParams.sort.split(',');
            $scope.filters.sort = sortParts[0] || 'created_at';
            $scope.filters.direction = sortParts[1] || 'desc';
        }
        if (queryParams.min_price) $scope.filters.minPrice = queryParams.min_price;
        if (queryParams.max_price) $scope.filters.maxPrice = queryParams.max_price;
        if (queryParams.rating) $scope.filters.rating = queryParams.rating;
        if (queryParams.search) $scope.searchQuery = queryParams.search;
        
        // Check if we're on a product detail page
        if ($scope.productId) {
            $scope.loadProductDetail($scope.productId);
        } 
        // Check if we're on a category page
        else if ($scope.categorySlug) {
            $scope.loadCategoryProducts($scope.categorySlug);
        }
        // Check if we're on a search results page
        else if ($scope.searchQuery) {
            $scope.loadSearchResults($scope.searchQuery);
        }
        // Check if we're on a category page (using query parameter)
        else if ($scope.filters.category) {
            $scope.loadCategoryProducts($scope.filters.category);
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
            selectedRatings: {}
        };
        $scope.applyFilters();
    };
    
    // Load product detail
    $scope.loadProductDetail = function(id) {
        $scope.loading = true;
        
        // Debug user role - use $rootScope values instead of trying to parse token
        $scope.isAdmin = $rootScope.isAdmin === true;
        console.log('User login status:', $scope.isLoggedIn);
        console.log('Is admin from $rootScope:', $rootScope.isAdmin);
        console.log('Is admin in controller:', $scope.isAdmin);
        
        ProductService.getProduct(id)
            .then(function(response) {
                // Handle different response formats
                if (response.data && response.success === true) {
                    $scope.product = response.data;
                } else if (response.data) {
                    $scope.product = response.data;
                } else {
                    throw new Error('Invalid product data received');
                }
                
                // Process product data
                $scope.processProductData([$scope.product]);
                
                // Initialize carousel after DOM update
                setTimeout(function() {
                    if (typeof bootstrap !== 'undefined') {
                        var carousel = document.getElementById('productImageCarousel');
                        if (carousel) {
                            new bootstrap.Carousel(carousel, {
                                interval: false, // Don't auto-rotate
                                wrap: true,     // Allow wrapping around
                                touch: true     // Enable touch swiping
                            });
                        }
                    }
                }, 100);
                
                // Load related products
                return ProductService.getProducts({
                    category_id: $scope.product.category_id,
                    exclude_id: $scope.product.id,
                    limit: 4
                });
            })
            .then(function(response) {
                if (response && response.data) {
                    $scope.relatedProducts = response.data;
                    
                    // Process related products data
                    $scope.processProductData($scope.relatedProducts);
                } else {
                    $scope.relatedProducts = [];
                }
            })
            .catch(function(error) {
                console.error('Error loading product detail:', error);
                // Set product to null to show the "Product Not Found" message
                $scope.product = null;
                $scope.showToast('Error loading product details. Please try again later.', 'error');
            })
            .finally(function() {
                $scope.loading = false;
            });
    };
    
    // Process product data to add missing fields needed by the frontend
    $scope.processProductData = function(products) {
        if (!products || !Array.isArray(products)) return;
        
        products.forEach(function(product) {
            // Special case for demo product
            if (product.name === 'aya') {
                product.image = 'images/aya.jpg';
                product.gallery_urls = ['images/aya2.jpg'];
            }
            
            // Add default values for missing fields
            if (!product.hasOwnProperty('rating')) {
                product.rating = 4;
            }
            
            if (!product.hasOwnProperty('rating_count')) {
                product.rating_count = 0;
            }
            
            // Convert reviews to comments if needed for backward compatibility
            if (product.hasOwnProperty('reviews') && !product.hasOwnProperty('comments')) {
                product.comments = product.reviews.map(function(review) {
                    return {
                        user_name: review.user_name,
                        question: review.comment,
                        created_at: review.created_at,
                        admin_reply: null,
                        replied_at: null
                    };
                });
            }
            
            // Ensure comment user_id is an integer for proper comparison
            if (product.comments && Array.isArray(product.comments)) {
                product.comments.forEach(function(comment) {
                    if (comment.user_id) {
                        comment.user_id = parseInt(comment.user_id);
                    }
                    
                    // Also convert user_id in replies to integers
                    if (comment.replies && Array.isArray(comment.replies)) {
                        comment.replies.forEach(function(reply) {
                            if (reply.user_id) {
                                reply.user_id = parseInt(reply.user_id);
                            }
                        });
                    }
                });
            }
            
            // Set comments_count
            if (!product.hasOwnProperty('comments_count') && product.comments) {
                product.comments_count = product.comments.length;
            } else if (!product.hasOwnProperty('comments_count')) {
                product.comments_count = 0;
            }
            
            if (!product.hasOwnProperty('short_description')) {
                product.short_description = product.description ? 
                    (product.description.length > 200 ? product.description.substring(0, 200) + '...' : product.description) : 
                    '';
            }
            
            // Process specifications if they exist
            if (product.hasOwnProperty('specifications')) {
                // If specifications is a string, try to parse it
                if (typeof product.specifications === 'string' && product.specifications) {
                    try {
                        product.specifications = JSON.parse(product.specifications);
                    } catch (e) {
                        console.error('Error parsing specifications:', e);
                        product.specifications = {};
                    }
                } 
                // If specifications is null or undefined, set to empty object
                else if (!product.specifications) {
                    product.specifications = {};
                }
            } else {
                product.specifications = {};
            }
        });
    };
    
    // Load search results
    $scope.loadSearchResults = function(query) {
        $scope.loading = true;
        $scope.searchQuery = query;
        
        console.log('Loading search results for query:', query);
        
        ProductService.searchProducts(query)
            .then(function(response) {
                console.log('Search results:', response);
                
                if (response.data && Array.isArray(response.data)) {
                    $scope.products = response.data;
                } else if (response.data && response.data.data) {
                    $scope.products = response.data.data;
                } else {
                    $scope.products = [];
                }
                
                // Process product data
                $scope.processProductData($scope.products);
                
                // Set pagination data
                if (response.data && response.data.meta) {
                    $scope.totalItems = response.data.meta.total;
                    $scope.totalPages = response.data.meta.last_page;
                    $scope.currentPage = response.data.meta.current_page;
                } else {
                    $scope.totalItems = $scope.products.length;
                    $scope.totalPages = Math.ceil($scope.totalItems / $scope.itemsPerPage);
                    $scope.currentPage = 1;
                }
            })
            .catch(function(error) {
                console.error('Error loading search results', error);
                $scope.showToast('Error searching products', 'error');
                $scope.products = [];
                $scope.totalItems = 0;
                $scope.totalPages = 0;
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
        console.log('Loading products for category slug:', slug);
        
        // First load category details
        CategoryService.getCategory(slug)
            .then(function(categoryData) {
                console.log('Category details loaded:', categoryData);
                $scope.category = categoryData;
                
                // Prepare filter parameters
                var params = {
                    page: $scope.currentPage,
                    limit: $scope.itemsPerPage
                };
                
                // Add sort parameters separately
                if ($scope.filters.sort) {
                    params.sort = $scope.filters.sort;
                }
                if ($scope.filters.direction) {
                    params.direction = $scope.filters.direction;
                }
                
                // Add price filters if set
                if ($scope.filters.minPrice) {
                    params.min_price = $scope.filters.minPrice;
                }
                if ($scope.filters.maxPrice) {
                    params.max_price = $scope.filters.maxPrice;
                }
                
                // Then load products in that category
                return ProductService.getProductsByCategory(slug, params);
            })
            .then(function(response) {
                console.log('Category products loaded:', response);
                $scope.products = response.data || [];
                
                // Process product data
                $scope.processProductData($scope.products);
                
                $scope.totalItems = response.meta ? response.meta.total : $scope.products.length;
                $scope.totalPages = Math.ceil($scope.totalItems / $scope.itemsPerPage);
                $scope.currentPage = response.meta ? response.meta.current_page : 1;
            })
            .catch(function(error) {
                console.error('Error loading category products for slug:', slug, error);
                $scope.showToast(error.message || 'Error loading category products', 'error');
                $scope.products = [];
                $scope.totalItems = 0;
                $scope.totalPages = 0;
            })
            .finally(function() {
                $scope.loading = false;
            });
    };
    
    // Load categories
    $scope.loadCategories = function() {
        CategoryService.getCategories()
            .then(function(response) {
                console.log('ProductController: Categories loaded:', response);
                $scope.categories = response.data;
            })
            .catch(function(error) {
                console.error('ProductController: Error loading categories', error);
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
    
    // Submit comment for product
    $scope.submitComment = function() {
        // Check if user is logged in
        if (!localStorage.getItem('token')) {
            $scope.showToast('Please login to submit a question', 'error');
            return;
        }
        
        if (!$scope.newComment || !$scope.newComment.question) {
            $scope.showToast('Please provide a question', 'error');
            return;
        }
        
        $scope.submittingComment = true;
        
        ProductService.submitComment($scope.product.id, $scope.newComment)
            .then(function(response) {
                $scope.showToast('Question submitted successfully');
                
                // Refresh product details to show the new comment
                $scope.loadProductDetail($scope.product.id);
                
                // Reset comment form
                $scope.newComment = {
                    question: ''
                };
            })
            .catch(function(error) {
                console.error('Error submitting question', error);
                if (error && error.status === 401) {
                    $scope.showToast('You must be logged in to submit questions. Please log in and try again.', 'error');
                    localStorage.removeItem('token'); // Clear invalid token
                    $scope.isLoggedIn = false;
                } else {
                    $scope.showToast('Error submitting question. Please try again.', 'error');
                }
            })
            .finally(function() {
                $scope.submittingComment = false;
            });
    };
    
    // Reply to a comment
    $scope.replyToComment = function(comment) {
        // Check if user is logged in
        if (!localStorage.getItem('token')) {
            $scope.showToast('Please login to reply to this question', 'error');
            return;
        }
        
        if (!comment.reply) {
            $scope.showToast('Please provide a reply', 'error');
            return;
        }
        
        $scope.submittingReply = comment.id;
        
        var replyData = {
            reply: comment.reply
        };
        
        ProductService.replyToComment($scope.product.id, comment.id, replyData)
            .then(function(response) {
                $scope.showToast('Reply submitted successfully');
                
                // Clear the reply text
                comment.reply = '';
                comment.showReplyForm = false;
                
                // Refresh product details to show the new reply
                $scope.loadProductDetail($scope.product.id);
            })
            .catch(function(error) {
                console.error('Error submitting reply', error);
                var errorMsg = 'Error submitting reply. ';
                
                if (error && error.status === 401) {
                    errorMsg += 'You must be logged in. Please log in and try again.';
                    localStorage.removeItem('token'); // Clear invalid token
                    $scope.isLoggedIn = false;
                } else if (error && error.message) {
                    errorMsg += error.message;
                } else {
                    errorMsg += 'Please try again.';
                }
                
                $scope.showToast(errorMsg, 'error');
            })
            .finally(function() {
                $scope.submittingReply = null;
            });
    };
    
    // Delete a comment 
    $scope.deleteComment = function(comment) {
        if (!confirm('Are you sure you want to delete this comment? This action cannot be undone.')) {
            return;
        }
        
        // Check if user is logged in and is admin
        if (!$scope.isLoggedIn) {
            $scope.showToast('You must be logged in to delete comments', 'error');
            return;
        }
        
        // Only allow admins to delete comments
        if (!$scope.isAdmin) {
            $scope.showToast('Only administrators can delete comments', 'error');
            return;
        }
        
        // Debug info for troubleshooting
        console.log('Deleting comment:', comment);
        console.log('Is admin:', $scope.isAdmin);
        
        ProductService.deleteComment($scope.product.id, comment.id)
            .then(function(response) {
                $scope.showToast('Comment deleted successfully');
                
                // Refresh product details
                $scope.loadProductDetail($scope.product.id);
            })
            .catch(function(error) {
                console.error('Error deleting comment', error);
                $scope.showToast(error.message || 'Error deleting comment', 'error');
            });
    };
    
    // Select image from thumbnails
    $scope.selectImage = function(index) {
        $scope.selectedImageIndex = index;
        
        // If using Bootstrap's JavaScript, manually activate the carousel item
        if (typeof bootstrap !== 'undefined') {
            var carousel = document.getElementById('productImageCarousel');
            if (carousel) {
                var bsCarousel = bootstrap.Carousel.getInstance(carousel);
                if (bsCarousel) {
                    // If index is null, show first slide (main product image)
                    if (index === null) {
                        bsCarousel.to(0);
                    } else {
                        // Add 1 because the main image is at index 0
                        bsCarousel.to(index + 1);
                    }
                } else {
                    // Create carousel instance if it doesn't exist
                    bsCarousel = new bootstrap.Carousel(carousel, {
                        interval: false,
                        wrap: true
                    });
                    
                    // Then select the slide
                    setTimeout(function() {
                        if (index === null) {
                            bsCarousel.to(0);
                        } else {
                            bsCarousel.to(index + 1);
                        }
                    }, 100);
                }
            }
        }
        
        // Update active-thumbnail class
        setTimeout(function() {
            $scope.$apply();
        }, 50);
    };
    
    // Delete a specific reply
    $scope.deleteReply = function(commentId, replyId) {
        if (!confirm('Are you sure you want to delete this reply?')) {
            return;
        }
        
        // Check if user is logged in and is admin
        if (!$scope.isLoggedIn) {
            $scope.showToast('You must be logged in to delete replies', 'error');
            return;
        }
        
        // Only allow admins to delete replies
        if (!$scope.isAdmin) {
            $scope.showToast('Only administrators can delete replies', 'error');
            return;
        }
        
        // Debug info for troubleshooting
        console.log('Deleting reply - Comment ID:', commentId, 'Reply ID:', replyId);
        console.log('Is admin:', $scope.isAdmin);
        
        ProductService.deleteReply($scope.product.id, commentId, replyId)
            .then(function(response) {
                $scope.showToast('Reply deleted successfully');
                
                // Refresh product details
                $scope.loadProductDetail($scope.product.id);
            })
            .catch(function(error) {
                console.error('Error deleting reply:', error);
                $scope.showToast(error.message || 'Error deleting reply', 'error');
            });
    };
    
    // Initialize controller
    $scope.init();
}]); 