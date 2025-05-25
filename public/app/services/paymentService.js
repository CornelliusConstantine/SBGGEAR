'use strict';

app.service('PaymentService', ['$window', '$q', function($window, $q) {
    var service = {};
    
    // Initialize Midtrans Snap
    service.initMidtransSnap = function() {
        var deferred = $q.defer();
        
        // Check if Midtrans Snap is already loaded
        if ($window.snap) {
            deferred.resolve($window.snap);
            return deferred.promise;
        }
        
        // Load Midtrans Snap.js
        var script = document.createElement('script');
        script.src = 'https://app.sandbox.midtrans.com/snap/snap.js';
        script.setAttribute('data-client-key', 'YOUR_MIDTRANS_CLIENT_KEY'); // This should be set from environment variable
        
        script.onload = function() {
            if ($window.snap) {
                deferred.resolve($window.snap);
            } else {
                deferred.reject('Failed to load Midtrans Snap');
            }
        };
        
        script.onerror = function() {
            deferred.reject('Failed to load Midtrans Snap');
        };
        
        document.body.appendChild(script);
        
        return deferred.promise;
    };
    
    // Open Midtrans Snap payment popup
    service.openSnapPayment = function(snapToken) {
        var deferred = $q.defer();
        
        service.initMidtransSnap()
            .then(function(snap) {
                snap.pay(snapToken, {
                    onSuccess: function(result) {
                        deferred.resolve({
                            status: 'success',
                            data: result
                        });
                    },
                    onPending: function(result) {
                        deferred.resolve({
                            status: 'pending',
                            data: result
                        });
                    },
                    onError: function(result) {
                        deferred.reject({
                            status: 'error',
                            data: result
                        });
                    },
                    onClose: function() {
                        deferred.reject({
                            status: 'closed',
                            data: null
                        });
                    }
                });
            })
            .catch(function(error) {
                deferred.reject({
                    status: 'error',
                    message: 'Failed to initialize payment gateway',
                    error: error
                });
            });
        
        return deferred.promise;
    };
    
    return service;
}]); 