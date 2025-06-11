/**
 * HTML5 Mode Fix for AngularJS
 * 
 * This script helps fix routing issues when transitioning from hashbang mode to HTML5 mode.
 * It automatically converts hashbang URLs to HTML5 mode URLs and handles direct navigation.
 */
(function() {
    // Run when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        console.log('HTML5 Mode Fix: Initializing');
        
        // Check if we're using hashbang in the URL
        if (window.location.hash && window.location.hash.startsWith('#!')) {
            console.log('HTML5 Mode Fix: Detected hashbang URL, converting to HTML5 mode');
            
            // Get the path from the hash (remove the #!)
            var path = window.location.hash.substring(2);
            
            // Redirect to the HTML5 mode URL
            var newUrl = window.location.protocol + '//' + window.location.host + path;
            console.log('HTML5 Mode Fix: Redirecting to ' + newUrl);
            window.location.replace(newUrl);
        }
        
        // Add a global click handler for hashbang links
        document.body.addEventListener('click', function(event) {
            // Check if the clicked element is a link with a hashbang URL
            var target = event.target;
            while (target && target.tagName !== 'A') {
                target = target.parentNode;
            }
            
            if (target && target.href && target.href.indexOf('#!') !== -1) {
                console.log('HTML5 Mode Fix: Intercepted click on hashbang link');
                
                // Prevent the default navigation
                event.preventDefault();
                
                // Extract the path from the hashbang URL
                var hashIndex = target.href.indexOf('#!');
                var path = target.href.substring(hashIndex + 2);
                
                // Create the HTML5 mode URL
                var newUrl = window.location.protocol + '//' + window.location.host + path;
                console.log('HTML5 Mode Fix: Redirecting to ' + newUrl);
                
                // Navigate to the HTML5 mode URL
                window.location.href = newUrl;
            }
        });
        
        console.log('HTML5 Mode Fix: Initialized successfully');
    });
})(); 