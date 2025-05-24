<?php

global $token;
$token = check_token();

$uri = $_SERVER['REQUEST_URI'];

$uri = trim($uri, '/');

switch($uri) {
    case "orders":
        return get_orders();
        break;
    default:
        http_response_code(404);
        echo json_encode(['error' => 'صفحه مورد نظر یافت نشد']);
        break;
}


/// functions
function check_token() {
    $headers = getallheaders();
    $token = $headers['Authorization'] ?? null;

    if (!$token) {
        http_response_code(401);
        echo json_encode(['error' => 'توکن نامعتبر است']);
        exit;
    }

    return $headers['Authorization'];
}

function get_orders() {
    global $token;
    
    $apiUrl = "https://order-processing.basalam.com/v3/vendor-parcels";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: ".$token
    ]);
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);

    if (!isset($data['data'])) {
        http_response_code(500);
        echo json_encode(['error' => 'خطا در دریافت اطلاعات از سرور']);
        exit;
    }

    $filtered = array_map(function($item) {
        $order = $item['order'] ?? [];
        $customer = $order['customer'] ?? [];
        $recipient = $customer['recipient'] ?? [];
        $city = $customer['city'] ?? [];
        $province = $city['parent'] ?? [];

        $items = array_map(function($product) {
            $quantity = $product['quantity'] ?? 0;
            $weight = $product['net_weight'] ?? $product['weight'];
            return [
                'id' => $product['product']['id'] ?? null,
                'title' => $product['title'] ?? null,
                'weight' => $weight * $quantity,
                'description' => $product['product']['name'] ?? null,
                'quantity' => $quantity,
            ];
        }, $item['items'] ?? []);

        // jame kol vazn ha total weight
        $total_weight = array_reduce($items, function($carry, $item) {
            return $carry + ($item['weight'] ?? 0);
        }, 0);

        return [
            'id' => $order['id'],
            'parcel_id' => $item['id'],
            'created_at' => $item['created_at'],
            'full_name' => $recipient['name'] ?? '',
            'address' => $recipient['postal_address'] ?? null,
            'city' => $city['title'] ?? null,
            'province' => $province['title'] ?? null,
            'postal_code' => $recipient['postal_code'] ?? null,
            'phone_number' => '09369104882',
            'items_count' => count($items),
            'total_weight' => $total_weight,
            'items' => $items,
        ];
    }, $data['data']);

    header('Content-Type: application/json');
    echo json_encode(['data' => $filtered]);
}