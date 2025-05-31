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
                deferred.resolve(response.data);
            })
            .catch(function(error) {
                deferred.reject(error.data);
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
        var params = filters || {};
        params.category = categorySlug;
        
        $http.get(API_URL + '/products', { params: params })
            .then(function(response) {
                deferred.resolve(response.data);
            })
            .catch(function(error) {
                deferred.reject(error.data);
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
        
        $http.get(API_URL + '/categories/' + slug)
            .then(function(response) {
                deferred.resolve(response.data);
            })
            .catch(function(error) {
                deferred.reject(error.data);
            });
        
        return deferred.promise;
    };
    
    // Get all unique brands
    service.getBrands = function() {
        var deferred = $q.defer();
        
        $http.get(API_URL + '/brands')
            .then(function(response) {
                deferred.resolve(response.data);
            })
            .catch(function(error) {
                deferred.reject(error.data);
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
    
    // Admin: Create a new product
    service.createProduct = function(productData) {
        var deferred = $q.defer();
        var token = localStorage.getItem('token');
        
        $http.post(API_URL + '/admin/products', productData, {
            headers: {
                'Authorization': 'Bearer ' + token,
                'Content-Type': undefined // Let the browser set the content type for FormData
            },
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
        
        $http.post(API_URL + '/admin/products/' + id, productData, {
            headers: {
                'Authorization': 'Bearer ' + token,
                'Content-Type': undefined // Let the browser set the content type for FormData
            },
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
        
        $http.delete(API_URL + '/admin/products/' + id, {
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
    
    // Admin: Create a category
    service.createCategory = function(categoryData) {
        var deferred = $q.defer();
        var token = localStorage.getItem('token');
        
        $http.post(API_URL + '/admin/categories', categoryData, {
            headers: {
                'Authorization': 'Bearer ' + token,
                'Content-Type': 'application/json'
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
        
        $http.put(API_URL + '/admin/categories/' + id, categoryData, {
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
    
    // Admin: Delete a category
    service.deleteCategory = function(id) {
        var deferred = $q.defer();
        var token = localStorage.getItem('token');
        
        $http.delete(API_URL + '/admin/categories/' + id, {
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
    
    return service;
}]); 