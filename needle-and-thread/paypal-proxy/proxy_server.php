<?php
require_once 'config_proxy.php';

// Set headers
header('Content-Type: application/json');

// Get request data
$input = json_decode(file_get_contents('php://input'), true);
$endpoint = $_GET['endpoint'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

// Validate endpoint
$allowed_endpoints = [
    'v2/checkout/orders',
    'v2/checkout/orders/{order_id}/capture',
    'v2/checkout/orders/{order_id}'
];

$is_valid_endpoint = false;
foreach ($allowed_endpoints as $allowed) {
    if (strpos($endpoint, 'v2/checkout/orders') === 0) {
        $is_valid_endpoint = true;
        break;
    }
}

if (!$is_valid_endpoint) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid endpoint']);
    exit;
}

// Get access token
function getPayPalAccessToken() {
    $ch = curl_init();
    
    curl_setopt_array($ch, [
        CURLOPT_URL => PAYPAL_BASE_URL . '/v1/oauth2/token',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => PROXY_TIMEOUT,
        CURLOPT_SSL_VERIFYPEER => PROXY_VERIFY_SSL,
        CURLOPT_USERPWD => PAYPAL_CLIENT_ID . ':' . PAYPAL_CLIENT_SECRET,
        CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'Accept-Language: en_US',
            'Content-Type: application/x-www-form-urlencoded'
        ]
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code !== 200) {
        throw new Exception('Failed to get access token');
    }
    
    $data = json_decode($response, true);
    return $data['access_token'];
}

// Make request to PayPal API
try {
    $access_token = getPayPalAccessToken();
    $paypal_url = PAYPAL_BASE_URL . '/' . $endpoint;
    
    $ch = curl_init();
    
    $curl_options = [
        CURLOPT_URL => $paypal_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => PROXY_TIMEOUT,
        CURLOPT_SSL_VERIFYPEER => PROXY_VERIFY_SSL,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $access_token,
            'Prefer: return=representation'
        ]
    ];
    
    switch ($method) {
        case 'POST':
            $curl_options[CURLOPT_POST] = true;
            $curl_options[CURLOPT_POSTFIELDS] = json_encode($input);
            break;
            
        case 'GET':
            // No additional options needed for GET
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            exit;
    }
    
    curl_setopt_array($ch, $curl_options);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        throw new Exception('cURL error: ' . $error);
    }
    
    http_response_code($http_code);
    echo $response;
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Proxy error',
        'message' => $e->getMessage()
    ]);
}
?>