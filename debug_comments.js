// Add this to the product detail controller to debug comment deletion issues
// This will log detailed information about each comment and the current user

// Add this to the loadProductDetail function after the product data is loaded
console.log('DEBUG: Product comments:', $scope.product.comments);

// Add this to the deleteComment function at the beginning
console.log('DEBUG: Attempting to delete comment:', comment);
console.log('DEBUG: User ID from token:', $scope.userId);
console.log('DEBUG: Comment user_id:', comment.user_id);
console.log('DEBUG: User ID type:', typeof $scope.userId);
console.log('DEBUG: Comment user_id type:', typeof comment.user_id);
console.log('DEBUG: userId === comment.user_id:', $scope.userId === comment.user_id);
console.log('DEBUG: parseInt(userId) === parseInt(comment.user_id):', parseInt($scope.userId) === parseInt(comment.user_id)); 