'use strict';

app.factory('LocationService', ['$http', function($http) {
    return {
        // Get all provinces
        getProvinces: function() {
            return $http.get('/api/provinces')
                .then(function(response) {
                    return response.data;
                })
                .catch(function(error) {
                    console.error('Error fetching provinces:', error);
                    return [];
                });
        },
        
        // Get cities by province
        getCitiesByProvince: function(provinceId) {
            return $http.get('/api/cities/' + provinceId)
                .then(function(response) {
                    return response.data;
                })
                .catch(function(error) {
                    console.error('Error fetching cities:', error);
                    return [];
                });
        },
        
        // Get shipping cost
        getShippingCost: function(data) {
            return $http.post('/api/shipping/cost', data)
                .then(function(response) {
                    return response.data;
                })
                .catch(function(error) {
                    console.error('Error calculating shipping cost:', error);
                    return null;
                });
        }
    };
}]); 