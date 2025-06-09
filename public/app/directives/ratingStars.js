'use strict';

/**
 * Rating Stars Directive
 * 
 * Creates a star rating display component
 */
app.directive('ratingStars', function() {
    return {
        restrict: 'E',
        scope: {
            rating: '=',
            max: '=?',
            readonly: '=?'
        },
        templateUrl: 'app/directives/templates/rating-stars.html',
        controller: ['$scope', function($scope) {
            $scope.max = $scope.max || 5;
            $scope.readonly = $scope.readonly !== false;
            
            $scope.getStars = function() {
                var stars = [];
                var rating = parseFloat($scope.rating) || 0;
                
                for (var i = 1; i <= $scope.max; i++) {
                    var starClass = '';
                    if (i <= rating) {
                        starClass = 'fas fa-star';
                    } else if (i - 0.5 <= rating) {
                        starClass = 'fas fa-star-half-alt';
                    } else {
                        starClass = 'far fa-star';
                    }
                    
                    stars.push({
                        value: i,
                        class: starClass
                    });
                }
                
                return stars;
            };
            
            $scope.setRating = function(value) {
                if (!$scope.readonly) {
                    $scope.rating = value;
                }
            };
        }]
    };
}); 