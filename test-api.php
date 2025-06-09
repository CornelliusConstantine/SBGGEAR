<?php

// Test API shipping cost
$url = 'http://localhost:8000/api/shipping/cost';
$data = json_encode([
    'city_id' => '151',
    'weight' => 500,
    'courier' => 'jne'
]);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'X-CSRF-TOKEN: dummy',
    'Accept: application/json'
]);

$response = curl_exec($ch);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "Error: " . $error;
} else {
    echo "Response: " . $response;
} 