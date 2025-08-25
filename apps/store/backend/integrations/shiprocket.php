<?php
// backend/integrations/shiprocket.php
// Shiprocket API integration module

function shiprocket_get_auth_token($api_key, $api_secret) {
    $url = "https://apiv2.shiprocket.in/v1/external/auth/login";
    $data = [
        'email' => $api_key,
        'password' => $api_secret,
    ];
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $response = curl_exec($ch);
    curl_close($ch);
    $result = json_decode($response, true);
    return $result['token'] ?? null;
}

function shiprocket_create_order($token, $order_data) {
    $url = "https://apiv2.shiprocket.in/v1/external/orders/create/adhoc";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($order_data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $token,
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

function shiprocket_get_order_status($token, $order_id) {
    $url = "https://apiv2.shiprocket.in/v1/external/orders/show/" . $order_id;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}
?>
