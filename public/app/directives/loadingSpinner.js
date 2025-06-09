'use strict';

/**
 * Loading Spinner Directive
 * 
 * Creates a loading spinner component for use during AJAX requests
 */
app.directive('loadingSpinner', function() {
    return {
        restrict: 'E',
        scope: {
            show: '=',
            text: '@?'
        },
        templateUrl: 'app/directives/templates/loading-spinner.html',
        controller: ['$scope', function($scope) {
            $scope.text = $scope.text || 'Loading...';
        }]
    };
}); 