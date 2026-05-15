<?php
$payload = '{"event":"order.created","data":{"id":1}}';
$secret = 'your-webhook-secret';
$sig = hash_hmac('sha256', $payload, $secret);

echo "Testing with signature: $sig\n";

$ch = curl_init('http://localhost:8000/api/v1/webhook');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'X-Webhook-Signature: ' . $sig
));

$response = curl_exec($ch);
$info = curl_getinfo($ch);
curl_close($ch);

echo "HTTP Status: " . $info['http_code'] . "\n";
echo "Response: $response\n";
