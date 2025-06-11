<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>HTML5 Mode Debug</h1>";
echo "<pre>";
echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'] . "\n";
echo "DOCUMENT_ROOT: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "SERVER_SOFTWARE: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
echo "PHP_SELF: " . $_SERVER['PHP_SELF'] . "\n";
echo "</pre>";

echo "<h2>Testing .htaccess Rewrite Rules</h2>";
echo "<p>If you're seeing this page directly, your server is correctly processing PHP files.</p>";

echo "<h2>HTML5 Mode Test Links</h2>";
echo "<ul>";
echo "<li><a href='/'>Home</a></li>";
echo "<li><a href='/products'>Products</a></li>";
echo "<li><a href='/product/13'>Product 13</a></li>";
echo "<li><a href='/cart'>Cart</a></li>";
echo "</ul>";

echo "<h2>Hash Mode Test Links</h2>";
echo "<ul>";
echo "<li><a href='/#!'>Home (Hash)</a></li>";
echo "<li><a href='/#!/products'>Products (Hash)</a></li>";
echo "<li><a href='/#!/product/13'>Product 13 (Hash)</a></li>";
echo "<li><a href='/#!/cart'>Cart (Hash)</a></li>";
echo "</ul>";

echo "<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Current URL:', window.location);
    console.log('HTML5 History API supported:', 'pushState' in history);
});
</script>";
?> 