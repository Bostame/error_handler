<?php

// Load WordPress.
require realpath('../wp-load.php');

// Set the timezone to German time
date_default_timezone_set('Europe/Berlin');

// Define the relative path to the configuration file
$config_path = realpath(__DIR__ . '/token/secret.php');

// Check if the configuration file exists
if (!$config_path || !file_exists($config_path)) {
    error_log('Configuration file not found: ' . $config_path);
    http_response_code(500);
    echo 'Internal Server Error: Configuration file not found.';
    exit;
}

// Load the configuration file
$config = require($config_path);

// Function to verify the hashed token
function verify_token($hashedToken) {
    global $config;

    // Verify if the hashed token matches the hashed secret token
    return hash_equals($config['secret_token'], $hashedToken);
}

// Function to clear caches and log the action
function clear_caches_and_log() {
    $messages = [];

    // Clear WP Rocket cache
    if (function_exists('rocket_clean_domain')) {
        rocket_clean_domain();
        $messages[] = 'WP Rocket cache cleared successfully.';
    } else {
        $messages[] = 'WP Rocket cache function not found.';
    }

    // Clear minified CSS and JavaScript files if function exists
    if (function_exists('rocket_clean_minify')) {
        rocket_clean_minify();
        $messages[] = 'WP Rocket minified files cleared successfully.';
    }

    // Clear Object Cache Pro cache
    if (function_exists('wp_cache_flush')) {
        wp_cache_flush();
        $messages[] = 'Object Cache Pro cache cleared successfully.';
    } else {
        $messages[] = 'Object Cache Pro cache function not found.';
    }

    // Log the date and time of the action
    $log_file = __DIR__ . '/log/uptime-kuma-log.txt';
    $current_time = date('Y-m-d H:i:s');
    $log_message = $current_time . ' - ' . implode(' ', $messages) . PHP_EOL;

    file_put_contents($log_file, $log_message, FILE_APPEND);

    echo implode(' ', $messages);
}

// Log request details for debugging
function log_request($message) {
    $log_file = __DIR__ . '/log/uptime-kuma-log.txt';
    $current_time = date('Y-m-d H:i:s');
    $log_message = $current_time . ' - ' . $message . PHP_EOL;

    file_put_contents($log_file, $log_message, FILE_APPEND);
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    log_request('Received POST request');
    
    // Get the POST data
    $postData = file_get_contents("php://input");
    $request = json_decode($postData, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        log_request('Invalid JSON in request: ' . json_last_error_msg());
        http_response_code(400);
        echo 'Invalid JSON';
        exit;
    }

    // Log the received POST data for debugging
    log_request('POST data received: ' . $postData);

    // Check if 'error' key exists and its value is 500
    if (isset($request['error']) && $request['error'] == 500) {
        // Verify the hashed token before proceeding
        if (isset($request['token']) && verify_token($request['token'])) {
            clear_caches_and_log();
        } else {
            log_request('Access Denied. Invalid token.');
            http_response_code(403);
            echo 'Access Denied';
        }
    } else {
        // Log the failure due to incorrect error value
        log_request('No action taken. Error value is not 500.');
        echo 'No action taken. Error value is not 500.';
    }
} else {
    // Log the failure due to incorrect request method
    log_request('Method Not Allowed. Received method: ' . $_SERVER['REQUEST_METHOD']);

    // Respond with an error if the request method is not POST
    http_response_code(405);
    echo 'Method Not Allowed';
}

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

?>
