<?php
// PayPal Configuration
// Replace these with your actual PayPal credentials

// PayPal API Credentials (Use these for live environment)
define('PAYPAL_CLIENT_ID', 'YOUR_PAYPAL_CLIENT_ID_HERE');
define('PAYPAL_CLIENT_SECRET', 'YOUR_PAYPAL_CLIENT_SECRET_HERE');

// PayPal API Endpoints
define('PAYPAL_BASE_URL', 'https://api-m.sandbox.paypal.com'); // Sandbox
// define('PAYPAL_BASE_URL', 'https://api-m.paypal.com'); // Live

// Proxy Configuration
define('PROXY_TIMEOUT', 30);
define('PROXY_VERIFY_SSL', false); // Set to true in production
?>
