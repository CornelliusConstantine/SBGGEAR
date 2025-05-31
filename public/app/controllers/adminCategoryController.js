'use strict';

app.controller('AdminCategoryController', ['$scope', 'ProductService', '$timeout', function($scope, ProductService, $timeout) {
    // Initialize controller
    $scope.init = function() {
        console.log('Initializing AdminCategoryController');
        $scope.loading = {
            categories: true,
            save: false,
            delete: false
        };
        
        $scope.categories = [];
        $scope.categoryForm = {};
        $scope.error = null;
        $scope.success = null;
        $scope.categoryError = null;
        $scope.isEditingCategory = false;
        
        // Load categories
        $scope.loadCategories();
    };
    
    // Load all categories
    $scope.loadCategories = function() {
        console.log('Loading categories...');
        $scope.loading.categories = true;
        
        ProductService.getCategories()
            .then(function(response) {
                console.log('Categories loaded successfully:', response);
                // Handle both old and new response formats
                if (response.data && Array.isArray(response.data.data)) {
                    $scope.categories = response.data.data;
                } else if (response.data && Array.isArray(response.data)) {
                    $scope.categories = response.data;
                } else {
                    $scope.categories = [];
                }
                console.log('Categories in scope:', $scope.categories);
            })
            .catch(function(error) {
                console.error('Error loading categories', error);
                $scope.error = 'Failed to load categories';
            })
            .finally(function() {
                $scope.loading.categories = false;
            });
    };
    
    // Reset category form
    $scope.resetCategoryForm = function() {
        $scope.categoryForm = {
            name: '',
            description: '',
            is_active: true
        };
        $scope.isEditingCategory = false;
        $scope.categoryError = null;
    };
    
    // Edit category
    $scope.editCategory = function(category) {
        $scope.categoryForm = {
            id: category.id,
            name: category.name,
            description: category.description,
            is_active: category.is_active
        };
        $scope.isEditingCategory = true;
        $scope.categoryError = null;
    };
    
    // Save category
    $scope.saveCategory = function() {
        $scope.loading.save = true;
        $scope.categoryError = null;
        
        // Basic client-side validation
        if (!$scope.categoryForm.name || !$scope.categoryForm.name.trim()) {
            $scope.categoryError = 'Category name is required';
            $scope.loading.save = false;
            return;
        }
        
        if (!$scope.categoryForm.description || !$scope.categoryForm.description.trim()) {
            $scope.categoryError = 'Category description is required';
            $scope.loading.save = false;
            return;
        }
        
        var savePromise;
        if ($scope.isEditingCategory) {
            savePromise = ProductService.updateCategory($scope.categoryForm.id, $scope.categoryForm);
        } else {
            savePromise = ProductService.createCategory($scope.categoryForm);
        }
        
        savePromise
            .then(function(response) {
                $scope.success = 'Category ' + ($scope.isEditingCategory ? 'updated' : 'created') + ' successfully';
                
                // Close modal using Bootstrap's native JS
                var modalElement = document.getElementById('categoryModal');
                var modalInstance = bootstrap.Modal.getInstance(modalElement);
                if (modalInstance) {
                    modalInstance.hide();
                }
                
                $scope.loadCategories();
                $scope.resetCategoryForm();
            })
            .catch(function(error) {
                console.error('Error saving category', error);
                
                // Handle specific error messages
                if (error && error.message) {
                    $scope.categoryError = error.message;
                } else if (error && error.errors) {
                    // Format validation errors
                    var errorMessages = [];
                    for (var field in error.errors) {
                        errorMessages.push(error.errors[field].join(', '));
                    }
                    $scope.categoryError = errorMessages.join('. ');
                } else if (error && error.sqlstate && error.sqlstate.includes('23505')) {
                    // Handle unique constraint violation
                    $scope.categoryError = 'A category with this name already exists. Please choose a different name.';
                } else {
                    $scope.categoryError = 'Failed to save category. Please try again.';
                }
            })
            .finally(function() {
                $scope.loading.save = false;
            });
    };
    
    // Delete category
    $scope.deleteCategory = function(id) {
        if (!confirm('Are you sure you want to delete this category?')) {
            return;
        }
        
        $scope.loading.delete = true;
        
        ProductService.deleteCategory(id)
            .then(function() {
                $scope.success = 'Category deleted successfully';
                $scope.loadCategories();
            })
            .catch(function(error) {
                console.error('Error deleting category', error);
                if (error && error.message) {
                    $scope.error = error.message;
                } else {
                    $scope.error = 'Failed to delete category';
                }
            })
            .finally(function() {
                $scope.loading.delete = false;
            });
    };
    
    // Initialize controller
    $scope.init();
}]); 