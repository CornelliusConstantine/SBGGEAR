'use strict';

/**
 * Product Card Directive
 * 
 * Creates a standardized product card component for use throughout the application
 */
app.directive('productCard', function() {
    return {
        restrict: 'E',
        scope: {
            product: '=',
            onAddToCart: '&'
        },
        templateUrl: 'app/directives/templates/product-card.html',
        controller: ['$scope', 'CartService', function($scope, CartService) {
            $scope.addToCart = function(product) {
                CartService.addItem(product.id, 1)
                    .then(function(response) {
                        if ($scope.onAddToCart) {
                            $scope.onAddToCart({product: product});
                        }
                    })
                    .catch(function(error) {
                        console.error('Error adding product to cart:', error);
                    });
            };
        }]
    };
}); 