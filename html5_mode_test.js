// This script adds a simple test to verify HTML5 mode is working
document.addEventListener('DOMContentLoaded', function() {
    console.log('Testing HTML5 mode...');
    
    // Create a test element
    const testDiv = document.createElement('div');
    testDiv.style.position = 'fixed';
    testDiv.style.bottom = '10px';
    testDiv.style.right = '10px';
    testDiv.style.padding = '10px';
    testDiv.style.background = '#f8f9fa';
    testDiv.style.border = '1px solid #dee2e6';
    testDiv.style.borderRadius = '4px';
    testDiv.style.zIndex = '9999';
    
    // Add content to the test element
    testDiv.innerHTML = `
        <h5>HTML5 Mode Test</h5>
        <p>Current Path: <code>${window.location.pathname}</code></p>
        <p>Hash: <code>${window.location.hash}</code></p>
        <button id="test-navigation" class="btn btn-sm btn-primary">Test Navigation</button>
    `;
    
    // Add the test element to the body
    document.body.appendChild(testDiv);
    
    // Add event listener to the test button
    document.getElementById('test-navigation').addEventListener('click', function() {
        // Get the Angular injector
        const injector = angular.element(document.body).injector();
        
        // Get the $location service
        const $location = injector.get('$location');
        
        // Navigate to a test page
        $location.path('/products');
        
        // Apply the changes
        angular.element(document.body).scope().$apply();
        
        // Show alert
        alert('Navigated to /products using $location.path()');
    });
}); 