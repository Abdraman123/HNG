<?php

// Load Composer's autoloader
require "vendor/autoload.php";

// Load .env variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Set content type to JSON
header("Content-Type: application/json");

// Function to get the client's IP address
function getClientIp() {
    if (!empty($_SERVER["HTTP_CLIENT_IP"])) {
        return $_SERVER["HTTP_CLIENT_IP"];
    } elseif (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
        return $_SERVER["HTTP_X_FORWARDED_FOR"];
    } else {
        return $_SERVER["REMOTE_ADDR"];
    }
}

// Function to get the client's Location based on IP
function getClientLocation($ip) {
    $apiKey = getenv('IPINFO_API_KEY');
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
$clientIp = getClientIp();
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