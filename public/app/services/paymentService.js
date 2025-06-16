'use strict';

app.factory('PaymentService', ['$http', function($http) {
    // Helper function to get auth headers
    function getAuthHeaders() {
        var token = localStorage.getItem('token');
        return {
            'Authorization': 'Bearer ' + token
        };
    }
    
    return {
        // Get payment methods
        getPaymentMethods: function() {
            return $http.get('/api/payment/methods', {
                headers: getAuthHeaders()
            })
                .then(function(response) {
                    return response.data;
                })
                .catch(function(error) {
                    console.error('Error fetching payment methods:', error);
                    return [];
                });
        },
        
        // Get Midtrans snap token
        getSnapToken: function(orderData) {
            console.log('PaymentService: Requesting snap token with data:', orderData);
            return $http.post('/api/payment/token', orderData, {
                headers: getAuthHeaders()
            })
                .then(function(response) {
                    console.log('PaymentService: Snap token response:', response);
                    if (response.data && response.data.token) {
                        return response.data;
                    } else {
                        console.error('PaymentService: Invalid response format:', response);
                        return Promise.reject('Invalid response format from server');
                    }
                })
                .catch(function(error) {
                    console.error('PaymentService: Error getting snap token:', error);
                    if (error.data && error.data.message) {
                        return Promise.reject(error.data.message);
                    }
                    if (error.status === 500) {
                        return Promise.reject('Server error: Payment gateway configuration issue');
                    }
                    return Promise.reject('Failed to get payment token: ' + (error.statusText || 'Unknown error'));
                });
        },
        
        // Process payment
        processPayment: function(paymentData) {
            console.log('PaymentService: Processing payment with data:', paymentData);
            return $http.post('/api/payment/process', paymentData, {
                headers: getAuthHeaders()
            })
                .then(function(response) {
                    console.log('PaymentService: Payment process response:', response);
                    return response.data;
                })
                .catch(function(error) {
                    console.error('PaymentService: Error processing payment:', error);
                    // Return a minimal response object instead of null
                    // This allows the checkout flow to continue even if there's an error
                    return {
                        success: false,
                        message: error.data && error.data.message ? error.data.message : 'Payment processing error',
                        order: null
                    };
                });
        },
        
        // Verify payment status
        verifyPaymentStatus: function(orderNumber, transactionId) {
            console.log('PaymentService: Verifying payment status for order:', orderNumber, 'transaction:', transactionId);
            return $http.get('/api/payment/verify', {
                params: {
                    order_number: orderNumber,
                    transaction_id: transactionId
                },
                headers: getAuthHeaders()
            })
                .then(function(response) {
                    console.log('PaymentService: Payment verification response:', response);
                    return response.data;
                })
                .catch(function(error) {
                    console.error('PaymentService: Error verifying payment:', error);
                    return {
                        success: false,
                        verified: false,
                        message: error.data && error.data.message ? error.data.message : 'Payment verification failed',
                        status: 'error'
                    };
                });
        }
    };
}]); 