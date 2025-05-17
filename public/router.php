<?php

$request = $_SERVER['REQUEST_URI'];

switch ($request) {
    case '/' :
        echo "<h1>Welcome to PHP Nginx App</h1>";
        break;
    case '/hello' :
        header('Content-Type: application/json');
        echo json_encode(["message" => "Hello from PHP running on Nginx!"]);
        break;
    default:
        http_response_code(404);
        echo json_encode(["error" => "Not Found"]);
        break;
}
