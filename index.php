<?php

// Load Composer's autoloader
require "vendor/autoload.php";

// Load .env variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Set content type to JSON
header("Content-Type: application/json");

// Function to get the client's IP address
function getClientIpFromHeaders() {
    $headers = [
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_REAL_IP',
        'HTTP_CLIENT_IP',
        'HTTP_CF_CONNECTING_IP' // Cloudflare
    ];

    foreach ($headers as $header) {
        if (!empty($_SERVER[$header])) {
            $ipList = explode(',', $_SERVER[$header]);
            $ip = trim(end($ipList)); // In case of multiple IPs, get the last one
            return $ip;
        }
    }

    return 'Unknown';
}
// Function to get the client's Location based on IP
function getClientLocation($ip) {
    $apiKey = $_ENV['IPINFO_API_KEY'];
    $url = "https://ipinfo.io/json?token=$apiKey";
    $response = file_get_contents($url);
    $data = json_decode($response, true);
    return $data["city"]; // Return the city from the API response data
}

// Function to fetch weather data
function getWeather($city) {
    // Get the API key from environment variables
    $apKey = $_ENV['OPENWEATHER_API_KEY'];
    $url = "https://api.openweathermap.org/data/2.5/weather?q=$city&appid=$apKey&units=metric";
    $response = file_get_contents($url);
    $data = json_decode($response, true);
    return $data['main']['temp']; // Return the temperature from the API response data
}

// Main logic
$visitorName = isset($_GET['visitor_name']) ? trim($_GET['visitor_name'], '"') : 'Visitor';
$clientIp = getClientIpFromHeaders();
$location = getClientLocation($clientIp);
$temperature = getWeather($location);

// Prepare JSON response
$response = [
    'client_ip' => $clientIp,
    'location' => $location,
    'greeting' => "Hello, $visitorName! The temperature is $temperature degrees Celsius in $location"
];

// Output JSON response
echo json_encode($response);