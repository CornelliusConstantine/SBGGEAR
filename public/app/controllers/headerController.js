'use strict';

app.controller('HeaderController', ['$scope', '$location', 'AuthService', 'ProductService', function($scope, $location, AuthService, ProductService) {
    // Initialize search
    $scope.searchQuery = '';
    $scope.searchResults = [];
    $scope.showSearchResults = false;
    $scope.searchLoading = false;
    
    // Search products
    $scope.searchProducts = function() {
        if (!$scope.searchQuery || $scope.searchQuery.length < 3) {
            $scope.searchResults = [];
            $scope.showSearchResults = false;
            return;
        }
        
        $scope.searchLoading = true;
        
        ProductService.searchProducts($scope.searchQuery)
            .then(function(response) {
                $scope.searchResults = response.data.slice(0, 5); // Limit to 5 results for dropdown
                $scope.processProductData($scope.searchResults);
                $scope.showSearchResults = true;
            })
            .catch(function(error) {
                console.error('Error searching products', error);
                $scope.searchResults = [];
            })
            .finally(function() {
                $scope.searchLoading = false;
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
            
            // Add default price display if needed
            if (product.price) {
                product.price_display = product.price.toLocaleString('id-ID');
            }
        });
    };
    
    // Submit search form
    $scope.submitSearch = function() {
        if ($scope.searchQuery && $scope.searchQuery.trim().length > 0) {
            $scope.showSearchResults = false;
            $location.path('/search/' + encodeURIComponent($scope.searchQuery));
        }
    };
    
    // Hide search results when clicking outside
    $scope.hideSearchResults = function(event) {
        if (!event.target.closest('.search-form')) {
            $scope.showSearchResults = false;
        }
    };
    
    // Logout user
    $scope.logout = function() {
        AuthService.logout()
            .then(function() {
                $location.path('/');
            })
            .catch(function(error) {
                console.error('Error logging out', error);
            });
    };
    
    // Check if current route is active
    $scope.isActive = function(path) {
        return $location.path() === path;
    };
    
    // Initialize event listeners
    document.addEventListener('click', function(event) {
        $scope.$apply(function() {
            $scope.hideSearchResults(event);
        });
    });
}]); 