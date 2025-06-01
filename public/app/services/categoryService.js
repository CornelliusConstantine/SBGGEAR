'use strict';

app.service('CategoryService', ['$http', '$q', function($http, $q) {
    var service = {};
    var API_URL = '/api';
    
    // Get all categories
    service.getCategories = function() {
        var deferred = $q.defer();
        
        console.log('CategoryService: Fetching all categories');
        $http.get(API_URL + '/categories')
            .then(function(response) {
                console.log('CategoryService: Categories response:', response);
                if (response.data && response.data.data) {
                    // New API format with data wrapper
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
                console.error('CategoryService: Error fetching categories:', error);
                deferred.reject(error.data || { message: 'Error loading categories' });
            });
        
        return deferred.promise;
    };
    
    // Get a single category by slug
    service.getCategory = function(slug) {
        var deferred = $q.defer();
        
        console.log('CategoryService: Fetching category with slug:', slug);
        $http.get(API_URL + '/categories/' + slug)
            .then(function(response) {
                console.log('CategoryService: Category response:', response);
                if (response.data && response.data.data) {
                    // Return just the category data
                    deferred.resolve(response.data.data);
                } else if (response.data) {
                    // Old API format
                    deferred.resolve(response.data);
                } else {
                    // No data
                    deferred.reject({ message: 'No category data received' });
                }
            })
            .catch(function(error) {
                console.error('CategoryService: Error fetching category:', error, 'Slug:', slug);
                if (error.status === 404) {
                    deferred.reject({ message: 'Category not found' });
                } else {
                    deferred.reject(error.data || { message: 'Error loading category' });
                }
            });
        
        return deferred.promise;
    };
    
    return service;
}]); 