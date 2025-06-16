'use strict';

app.service('ProductService', ['$http', '$q', function($http, $q) {
    var service = {};
    var API_URL = '/api';
    
    // Get all products with optional filters
    service.getProducts = function(filters) {
        var deferred = $q.defer();
        var url = API_URL + '/products';
        var config = { params: filters || {} };
        
        $http.get(url, config)
            .then(function(response) {
                deferred.resolve(response.data);
            })
            .catch(function(error) {
                deferred.reject(error.data);
            });
        
        return deferred.promise;
    };
    
    // Get a single product by ID
    service.getProduct = function(id) {
        var deferred = $q.defer();
        
        $http.get(API_URL + '/products/' + id)
            .then(function(response) {
                // Check for different response formats
                if (response.data && response.data.success === true && response.data.data) {
                    // New API format with success flag and data wrapper
                    deferred.resolve({
                        data: response.data.data,
                        success: response.data.success
                    });
                } else if (response.data && response.data.data) {
                    // API format with just data wrapper
                    deferred.resolve({
                        data: response.data.data
                    });
                } else {
                    // Old API format or fallback
                    deferred.resolve({
                        data: response.data
                    });
                }
            })
            .catch(function(error) {
                console.error('ProductService: Error fetching product details:', error);
                if (error.status === 404) {
                    deferred.reject({message: 'Product not found or has been removed.'});
                } else if (error.data && error.data.message) {
                    deferred.reject({message: error.data.message});
                } else {
                    deferred.reject({message: 'Error loading product details. Please try again later.'});
                }
            });
        
        return deferred.promise;
    };
    
    // Search products
    service.searchProducts = function(query) {
        var deferred = $q.defer();
        
        $http.get(API_URL + '/products/search/' + encodeURIComponent(query))
            .then(function(response) {
                deferred.resolve(response.data);
            })
            .catch(function(error) {
                deferred.reject(error.data);
            });
        
        return deferred.promise;
    };
    
    // Get search suggestions for autocomplete
    service.getSearchSuggestions = function(query) {
        var deferred = $q.defer();
        
        $http.get(API_URL + '/products/suggestions/' + encodeURIComponent(query))
            .then(function(response) {
                deferred.resolve(response.data);
            })
            .catch(function(error) {
                deferred.reject(error.data);
            });
        
        return deferred.promise;
    };
    
    // Get products by category
    service.getProductsByCategory = function(categorySlug, filters) {
        var deferred = $q.defer();
        var params = {};
        
        // Copy filters to params
        if (filters) {
            for (var key in filters) {
                if (filters.hasOwnProperty(key)) {
                    params[key] = filters[key];
                }
            }
        }
        
        // Add category parameter
        params.category = categorySlug;
        
        // Make sure sort and direction are separate parameters
        if (params.sort && params.sort.indexOf(',') > -1) {
            var sortParts = params.sort.split(',');
            params.sort = sortParts[0];
            params.direction = sortParts[1];
        }
        
        console.log('ProductService: Getting products by category slug:', categorySlug, 'with params:', params);
        $http.get(API_URL + '/products', { params: params })
            .then(function(response) {
                console.log('ProductService: Products by category response:', response);
                if (response.data && response.data.data) {
                    // New API format with data wrapper
                    deferred.resolve({
                        data: response.data.data,
                        meta: response.data.meta
                    });
                } else if (response.data) {
                    // Old API format or fallback
                    deferred.resolve({
                        data: response.data,
                        meta: null
                    });
                } else {
                    // No data
                    deferred.resolve({
                        data: [],
                        meta: null
                    });
                }
            })
            .catch(function(error) {
                console.error('ProductService: Error getting products by category:', error);
                if (error.status === 404) {
                    deferred.reject({ message: 'Category not found' });
                } else {
                    deferred.reject(error.data || { message: 'Failed to load products for this category' });
                }
            });
        
        return deferred.promise;
    };
    
    // Get all categories
    service.getCategories = function() {
        var deferred = $q.defer();
        console.log('ProductService: Fetching categories from', API_URL + '/categories');
        
        $http.get(API_URL + '/categories')
            .then(function(response) {
                console.log('ProductService: Categories API response:', response);
                if (response.data && response.data.data) {
                    // New API format with data wrapper
                    deferred.resolve({
                        data: response.data.data
                    });
                } else {
                    // Old API format or fallback
                    deferred.resolve(response);
                }
            })
            .catch(function(error) {
                console.error('ProductService: Error fetching categories:', error);
                deferred.reject(error.data);
            });
        
        return deferred.promise;
    };
    
    // Get a single category by slug
    service.getCategory = function(slug) {
        var deferred = $q.defer();
        
        console.log('ProductService: Getting category by slug:', slug);
        $http.get(API_URL + '/categories/' + slug)
            .then(function(response) {
                console.log('ProductService: Category response:', response);
                if (response.data && response.data.data) {
                    // New API format with data wrapper
                    deferred.resolve({
                        data: response.data.data
                    });
                } else {
                    // Old API format or fallback
                    deferred.resolve(response);
                }
            })
            .catch(function(error) {
                console.error('ProductService: Error getting category by slug:', slug, error);
                deferred.reject(error.data || { message: 'Category not found' });
            });
        
        return deferred.promise;
    };
    
    // Submit a product review
    service.submitReview = function(productId, reviewData) {
        var deferred = $q.defer();
        var token = localStorage.getItem('token');
        
        $http.post(API_URL + '/products/' + productId + '/reviews', reviewData, {
            headers: {
                'Authorization': 'Bearer ' + token
            }
        })
            .then(function(response) {
                deferred.resolve(response.data);
            })
            .catch(function(error) {
                deferred.reject(error.data);
            });
        
        return deferred.promise;
    };
    
    // Admin: Create a product
    service.createProduct = function(productData) {
        var deferred = $q.defer();
        var token = localStorage.getItem('token');
        
        // Get CSRF token
        var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        var headers = {
            'Authorization': 'Bearer ' + token,
            'Content-Type': undefined // Let the browser set the content type for FormData
        };
        
        $http.post(API_URL + '/admin/products', productData, {
            headers: headers,
            transformRequest: angular.identity // Don't transform the FormData
        })
            .then(function(response) {
                deferred.resolve(response.data);
            })
            .catch(function(error) {
                console.error('Error creating product:', error);
                if (error.data) {
                    deferred.reject(error.data);
                } else {
                    deferred.reject({ message: 'Network error. Please try again.' });
                }
            });
        
        return deferred.promise;
    };
    
    // Admin: Update a product
    service.updateProduct = function(id, productData) {
        var deferred = $q.defer();
        var token = localStorage.getItem('token');
        
        // Get CSRF token
        var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        var headers = {
            'Authorization': 'Bearer ' + token,
            'Content-Type': undefined // Let the browser set the content type for FormData
        };
        
        $http.post(API_URL + '/admin/products/' + id, productData, {
            headers: headers,
            transformRequest: angular.identity // Don't transform the FormData
        })
            .then(function(response) {
                deferred.resolve(response.data);
            })
            .catch(function(error) {
                console.error('Error updating product:', error);
                if (error.data) {
                    deferred.reject(error.data);
                } else {
                    deferred.reject({ message: 'Network error. Please try again.' });
                }
            });
        
        return deferred.promise;
    };
    
    // Admin: Delete a product
    service.deleteProduct = function(id) {
        var deferred = $q.defer();
        var token = localStorage.getItem('token');
        
        // Function to attempt deletion
        var attemptDeletion = function(retryCount) {
            console.log('Attempting to delete product ID:', id);
            
            $http.delete(API_URL + '/admin/products/' + id, {
                headers: {
                    'Authorization': 'Bearer ' + token
                }
            })
            .then(function(response) {
                console.log('Delete product response:', response);
                if (response.data && response.data.success === true) {
                    deferred.resolve(response.data);
                } else {
                    // If we got a response but success is not true, resolve with the response
                    // but add a warning
                    console.warn('Product deletion returned non-success response:', response.data);
                    deferred.resolve({
                        ...response.data,
                        warning: 'The server returned a non-success response. The product may not have been fully deleted.'
                    });
                }
            })
            .catch(function(error) {
                console.error('Error deleting product:', error);
                
                // If we have retries left and it's a network error, retry
                if (retryCount > 0 && (!error.status || error.status >= 500)) {
                    console.log('Retrying product deletion, attempts left:', retryCount);
                    setTimeout(function() {
                        attemptDeletion(retryCount - 1);
                    }, 1000);
                } else {
                    // No retries left or not a retryable error
                    if (error.data && error.data.message) {
                        deferred.reject({message: error.data.message});
                    } else if (error.status === 405) {
                        deferred.reject({message: 'Method not allowed. The API endpoint may have changed or you may not have permission to delete this product.'});
                    } else {
                        deferred.reject({message: 'Failed to delete product. Please try again.'});
                    }
                }
            });
        };
        
        // Start with 2 retry attempts
        attemptDeletion(2);
        
        return deferred.promise;
    };
    
    // Admin: Create a category
    service.createCategory = function(categoryData) {
        var deferred = $q.defer();
        var token = localStorage.getItem('token');
        
        // Get CSRF token
        var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        $http.post(API_URL + '/admin/categories', categoryData, {
            headers: {
                'Authorization': 'Bearer ' + token,
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        })
            .then(function(response) {
                deferred.resolve(response.data);
            })
            .catch(function(error) {
                console.error('Error creating category:', error);
                if (error.data) {
                    deferred.reject(error.data);
                } else {
                    deferred.reject({ message: 'Network error. Please try again.' });
                }
            });
        
        return deferred.promise;
    };
    
    // Admin: Update a category
    service.updateCategory = function(id, categoryData) {
        var deferred = $q.defer();
        var token = localStorage.getItem('token');
        
        // Get CSRF token
        var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        $http.put(API_URL + '/admin/categories/' + id, categoryData, {
            headers: {
                'Authorization': 'Bearer ' + token,
                'X-CSRF-TOKEN': csrfToken
            }
        })
            .then(function(response) {
                deferred.resolve(response.data);
            })
            .catch(function(error) {
                deferred.reject(error.data);
            });
        
        return deferred.promise;
    };
    
    // Admin: Delete a category
    service.deleteCategory = function(id) {
        var deferred = $q.defer();
        var token = localStorage.getItem('token');
        
        // Get CSRF token
        var csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        $http.delete(API_URL + '/admin/categories/' + id, {
            headers: {
                'Authorization': 'Bearer ' + token,
                'X-CSRF-TOKEN': csrfToken
            }
        })
            .then(function(response) {
                deferred.resolve(response.data);
            })
            .catch(function(error) {
                deferred.reject(error.data);
            });
        
        return deferred.promise;
    };
    
    // Get featured products
    service.getFeaturedProducts = function(limit) {
        var deferred = $q.defer();
        var params = {};
        
        if (limit) {
            params.limit = limit;
        }
        
        $http.get(API_URL + '/products/featured', { params: params })
            .then(function(response) {
                if (response.data && response.data.data) {
                    // New API format with data wrapper
                    deferred.resolve(response.data.data);
                } else {
                    // Old API format or fallback
                    deferred.resolve(response.data);
                }
            })
            .catch(function(error) {
                console.error('ProductService: Error fetching featured products:', error);
                deferred.reject(error.data || { message: 'Error loading featured products' });
            });
        
        return deferred.promise;
    };
    
    // Submit a product comment
    service.submitComment = function(productId, commentData) {
        var deferred = $q.defer();
        var token = localStorage.getItem('token');
        
        console.log('DEBUG: ProductService.submitComment called');
        console.log('DEBUG: Product ID:', productId);
        console.log('DEBUG: Comment data:', commentData);
        console.log('DEBUG: Token exists:', !!token);
        
        if (!token) {
            console.log('DEBUG: No token found, rejecting');
            deferred.reject({
                message: 'Authentication required',
                status: 401
            });
            return deferred.promise;
        }
        
        console.log('DEBUG: Making API request to:', API_URL + '/products/' + productId + '/comments');
        
        $http.post(API_URL + '/products/' + productId + '/comments', commentData, {
            headers: {
                'Authorization': 'Bearer ' + token
            }
        })
            .then(function(response) {
                console.log('DEBUG: API request successful:', response);
                deferred.resolve(response.data);
            })
            .catch(function(error) {
                console.error('DEBUG: Error submitting comment to API:', error);
                if (error.status === 401) {
                    // Authentication error
                    deferred.reject({
                        message: 'Authentication required',
                        status: 401
                    });
                } else if (error.data && error.data.message) {
                    // Server error message
                    deferred.reject({
                        message: error.data.message,
                        status: error.status
                    });
                } else {
                    // Generic error
                    deferred.reject({
                        message: 'Failed to submit comment. Please try again.',
                        status: error.status || 500
                    });
                }
            });
        
        return deferred.promise;
    };
    
    // Reply to a product comment (available to all users)
    service.replyToComment = function(productId, commentId, replyData) {
        var deferred = $q.defer();
        var token = localStorage.getItem('token');
        
        if (!token) {
            deferred.reject({
                message: 'Authentication required',
                status: 401
            });
            return deferred.promise;
        }
        
        $http.post(API_URL + '/products/' + productId + '/comments/' + commentId + '/reply', replyData, {
            headers: {
                'Authorization': 'Bearer ' + token
            }
        })
            .then(function(response) {
                deferred.resolve(response.data);
            })
            .catch(function(error) {
                if (error.status === 401) {
                    // Authentication error
                    deferred.reject({
                        message: 'Authentication required',
                        status: 401
                    });
                } else if (error.data && error.data.message) {
                    // Server error message
                    deferred.reject({
                        message: error.data.message,
                        status: error.status
                    });
                } else {
                    // Generic error
                    deferred.reject({
                        message: 'Failed to submit reply. Please try again.',
                        status: error.status || 500
                    });
                }
            });
        
        return deferred.promise;
    };
    
    // Delete a specific reply
    service.deleteReply = function(productId, commentId, replyId) {
        var deferred = $q.defer();
        var token = localStorage.getItem('token');
        
        if (!token) {
            deferred.reject({
                message: 'Authentication required',
                status: 401
            });
            return deferred.promise;
        }
        
        $http.delete(API_URL + '/admin/products/' + productId + '/comments/' + commentId + '/replies/' + replyId, {
            headers: {
                'Authorization': 'Bearer ' + token
            }
        })
            .then(function(response) {
                deferred.resolve(response.data);
            })
            .catch(function(error) {
                if (error.status === 401) {
                    // Authentication error
                    deferred.reject({
                        message: 'Authentication required',
                        status: 401
                    });
                } else if (error.status === 403) {
                    // Unauthorized
                    deferred.reject({
                        message: 'Unauthorized. Only administrators can delete replies.',
                        status: 403
                    });
                } else if (error.data && error.data.message) {
                    // Server error message
                    deferred.reject({
                        message: error.data.message,
                        status: error.status
                    });
                } else {
                    // Generic error
                    deferred.reject({
                        message: 'Failed to delete reply. Please try again.',
                        status: error.status || 500
                    });
                }
            });
        
        return deferred.promise;
    };
    
    // Delete a comment (admin only)
    service.deleteComment = function(productId, commentId) {
        var deferred = $q.defer();
        var token = localStorage.getItem('token');
        
        if (!token) {
            deferred.reject({
                message: 'Authentication required',
                status: 401
            });
            return deferred.promise;
        }
        
        // Debug info
        console.log('Deleting comment:', commentId, 'for product:', productId);
        
        // Use the admin endpoint for deleting comments
        $http.delete(API_URL + '/admin/products/' + productId + '/comments/' + commentId, {
            headers: {
                'Authorization': 'Bearer ' + token
            }
        })
            .then(function(response) {
                deferred.resolve(response.data);
            })
            .catch(function(error) {
                if (error.status === 401) {
                    // Authentication error
                    deferred.reject({
                        message: 'Authentication required',
                        status: 401
                    });
                } else if (error.status === 403) {
                    // Unauthorized
                    deferred.reject({
                        message: 'Unauthorized. Only administrators can delete comments.',
                        status: 403
                    });
                } else if (error.data && error.data.message) {
                    // Server error message
                    deferred.reject({
                        message: error.data.message,
                        status: error.status
                    });
                } else {
                    // Generic error
                    deferred.reject({
                        message: 'Failed to delete comment. Please try again.',
                        status: error.status || 500
                    });
                }
            });
        
        return deferred.promise;
    };
    
    // Deprecated: Use deleteReply instead
    service.deleteCommentReply = function(productId, commentId, replyId) {
        console.warn('ProductService.deleteCommentReply is deprecated. Use ProductService.deleteReply instead.');
        return service.deleteReply(productId, commentId, replyId);
    };
    
    return service;
}]); 