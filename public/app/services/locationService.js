'use strict';

app.factory('LocationService', ['$http', function($http) {
    var service = {};
    
    // Get all provinces
    service.getProvinces = function() {
        return $http.get('/api/provinces')
            .then(function(response) {
                return response.data;
            })
            .catch(function(error) {
                console.error('Error fetching provinces:', error);
                return [];
            });
    };
    
    // Get cities by province ID
    service.getCitiesByProvince = function(provinceId) {
        return $http.get('/api/cities/' + provinceId)
            .then(function(response) {
                return response.data;
            })
            .catch(function(error) {
                console.error('Error fetching cities:', error);
                return [];
            });
    };
    
    // Get all cities
    service.getAllCities = function() {
        return $http.get('/api/cities')
            .then(function(response) {
                return response.data;
            })
            .catch(function(error) {
                console.error('Error fetching all cities:', error);
                return [];
            });
    };
    
    return service;
}]); 