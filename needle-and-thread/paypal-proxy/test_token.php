<?php
require_once 'config_proxy.php';

$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL => PAYPAL_BASE_URL . '/v1/oauth2/token',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_USERPWD => PAYPAL_CLIENT_ID . ':' . PAYPAL_CLIENT_SECRET,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => 'grant_type=client_credentials'
]);

$response = curl_exec($ch);
$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP CODE: $http<br>";
echo "RESPONSE:<br>";
echo "<pre>$response</pre>";
