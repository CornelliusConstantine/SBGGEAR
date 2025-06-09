'use strict';

// Define the main application module
var app = angular.module('sbgGearApp', [
    'ngRoute',
    'ngAnimate',
    'ngSanitize'
]);

// Configure routes
app.config(['$routeProvider', '$locationProvider', function($routeProvider, $locationProvider) {
    $routeProvider
        // Customer routes
        .when('/', {
            template: '<div class="container mt-5"><h1>Home Page</h1><p>This is the home page of the application.</p></div>',
            controller: function($scope) {
                $scope.pageTitle = 'Home';
            }
        })
        .when('/products', {
            template: '<div class="container mt-5"><h1>Products Page</h1><p>This is the products page of the application.</p></div>',
            controller: function($scope) {
                $scope.pageTitle = 'Products';
            }
        })
        .when('/about', {
            template: '<div class="container mt-5"><h1>About Page</h1><p>This is the about page of the application.</p></div>',
            controller: function($scope) {
                $scope.pageTitle = 'About';
            }
        })
        .otherwise({
            redirectTo: '/'
        });
    
    // Use HTML5 History API
    $locationProvider.html5Mode(true);
    $locationProvider.hashPrefix('!');
}]);

// Run block
app.run(['$rootScope', '$location', function($rootScope, $location) {
    // Add a debug banner
    var debugBanner = angular.element('<div style="position:fixed;bottom:0;left:0;right:0;background:#f8d7da;color:#721c24;padding:10px;text-align:center;z-index:9999;"></div>');
    debugBanner.text('Simple App - HTML5 Mode Enabled - Path: ' + $location.path());
    angular.element(document.body).append(debugBanner);
    
    // Update the debug banner when the route changes
    $rootScope.$on('$routeChangeSuccess', function() {
        debugBanner.text('Simple App - HTML5 Mode Enabled - Path: ' + $location.path());
    });
}]); 