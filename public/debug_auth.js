// Debug script for authentication and comment submission issues

// Function to check authentication status
function checkAuthStatus() {
    console.log('=== AUTH STATUS CHECK ===');
    const token = localStorage.getItem('token');
    console.log('Token exists:', !!token);
    console.log('Token value:', token ? token.substring(0, 10) + '...' : 'null');
    
    const userRole = localStorage.getItem('userRole');
    console.log('User role:', userRole || 'not set');
    
    console.log('isLoggedIn (rootScope):', angular.element(document.body).scope().$root.isLoggedIn);
    console.log('currentUser (rootScope):', angular.element(document.body).scope().$root.currentUser);
    console.log('=== END AUTH STATUS ===');
}

// Function to test comment submission
function testCommentSubmission(productId) {
    console.log('=== TESTING COMMENT SUBMISSION ===');
    const token = localStorage.getItem('token');
    if (!token) {
        console.log('No token found, cannot submit comment');
        return;
    }
    
    const commentData = {
        question: 'Test comment ' + new Date().toISOString()
    };
    
    console.log('Submitting test comment:', commentData);
    
    fetch('/api/products/' + productId + '/comments', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + token
        },
        body: JSON.stringify(commentData)
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
    })
    .catch(error => {
        console.error('Error submitting comment:', error);
    });
    
    console.log('=== END TEST SUBMISSION ===');
}

// Function to set a test token
function setTestToken(token) {
    localStorage.setItem('token', token);
    console.log('Set test token:', token.substring(0, 10) + '...');
}

// Function to clear auth data
function clearAuth() {
    localStorage.removeItem('token');
    localStorage.removeItem('userRole');
    console.log('Auth data cleared');
}

// Log instructions
console.log('Debug auth script loaded. Available functions:');
console.log('- checkAuthStatus(): Check current authentication status');
console.log('- testCommentSubmission(productId): Test comment submission for a product');
console.log('- setTestToken(token): Set a test token');
console.log('- clearAuth(): Clear authentication data');

// Run initial check
checkAuthStatus(); 