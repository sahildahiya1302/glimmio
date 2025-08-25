<?php
// backend/integrations/delhivery.php
// Delhivery API integration module

function delhivery_get_auth_token($api_key, $api_secret) {
    // Delhivery uses API key and secret for authentication, typically via headers
    // This function can be expanded if token-based auth is required
    return ['api_key' => $api_key, 'api_secret' => $api_secret];
}

function delhivery_create_order($credentials, $order_data) {
    $url = "https://track.delhivery.com/api/cmu/create.json";
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

function delhivery_get_order_status($credentials, $waybill) {
    $url = "https://track.delhivery.com/api/packages/json/?waybill=" . urlencode($waybill);
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
