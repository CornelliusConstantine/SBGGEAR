'use strict';

app.service('CategoryService', ['$http', '$q', function($http, $q) {
    var service = {};
    var API_URL = '/api';
    
    // Get all categories
    service.getCategories = function() {
        var deferred = $q.defer();
        
        $http.get(API_URL + '/categories')
            .then(function(response) {
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
        
        $http.get(API_URL + '/categories/' + slug)
            .then(function(response) {
                if (response.data && response.data.data) {
                    deferred.resolve(response.data.data);
                } else {
                    deferred.resolve(response.data);
                }
            })
            .catch(function(error) {
                deferred.reject(error.data || { message: 'Error loading category' });
            });
        
        return deferred.promise;
    };
    
    return service;
}]); 