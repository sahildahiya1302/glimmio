<?php
// backend/integrations/ekart.php
// Ekart API integration module

function ekart_get_auth_token($api_key, $api_secret) {
    // Ekart authentication method, adjust as per their API docs
    // Placeholder for token retrieval if needed
    return ['api_key' => $api_key, 'api_secret' => $api_secret];
}

function ekart_create_order($credentials, $order_data) {
    $url = "https://api.ekartlogistics.com/v1/orders/create";
    $headers = [
        'Content-Type: application/json',
        'Authorization: Basic ' . base64_encode($credentials['api_key'] . ':' . $credentials['api_secret']),
    ];
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($order_data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

function ekart_get_order_status($credentials, $order_id) {
    $url = "https://api.ekartlogistics.com/v1/orders/status/" . urlencode($order_id);
    $headers = [
        'Authorization: Basic ' . base64_encode($credentials['api_key'] . ':' . $credentials['api_secret']),
    ];
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}
?>
