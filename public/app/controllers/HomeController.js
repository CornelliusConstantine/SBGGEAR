app.controller('HomeController', ['$scope', 'ProductService', 'CategoryService', function($scope, ProductService, CategoryService) {
    $scope.featuredProducts = [];
    $scope.categories = [];
    $scope.loading = {
        featured: true,
        categories: true
    };
    
    // Load featured products
    ProductService.getFeaturedProducts(8)
        .then(function(products) {
            $scope.featuredProducts = products;
        })
        .catch(function(error) {
            console.error('Error loading featured products:', error);
        })
        .finally(function() {
            $scope.loading.featured = false;
        });
    
    // Load categories
    CategoryService.getCategories()
        .then(function(response) {
            $scope.categories = response.data || [];
        })
        .catch(function(error) {
            console.error('Error loading categories:', error);
        })
        .finally(function() {
            $scope.loading.categories = false;
        });
}]); 