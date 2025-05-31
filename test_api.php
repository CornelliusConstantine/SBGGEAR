<?php

$url = 'http://localhost:8000/api/categories';
echo "Testing API endpoint: $url\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Response Code: $httpCode\n";
echo "Response Body:\n";
echo $response;

// Try to decode JSON
$decoded = json_decode($response, true);
if (json_last_error() === JSON_ERROR_NONE) {
    echo "\n\nDecoded JSON:\n";
    print_r($decoded);
} else {
    echo "\n\nFailed to decode JSON: " . json_last_error_msg();
} 