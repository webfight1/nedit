<?php
/**
 * ESTO API Test - Correct endpoint and auth method
 */

// ESTO credentials (shop_id is username, secret_key is password for Basic Auth)
$shop_id = 'nailedit';
$secret_key = '2IIbVDcvWHbgXmf1SpWW4wbors2aXQ2R';

$apiUrl = 'https://api.esto.ee/v2/purchase';

$data = [
    "amount" => 0.10,
    "currency" => "EUR",
    "reference" => "TEST-" . time(),
    "return_url" => "https://nailedit.ee/return",
    "notification_url" => "https://nailedit.ee/notify",
    "schedule_type" => "ESTO_PAY",
    "customer" => [
        "email" => "test@test.ee",
        "phone" => "+37255555555",
        "first_name" => "Test",
        "last_name" => "User"
    ]
];

echo "<h2>ESTO API Test</h2>";
echo "<p><strong>Endpoint:</strong> " . htmlspecialchars($apiUrl) . "</p>";
echo "<p><strong>Shop ID:</strong> " . htmlspecialchars($shop_id) . "</p>";
echo "<p><strong>Secret Key:</strong> " . htmlspecialchars($secret_key) . "</p>";
echo "<hr>";

$ch = curl_init($apiUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_USERPWD => $shop_id . ':' . $secret_key,  // Basic Auth
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json'
    ],
    CURLOPT_POSTFIELDS => json_encode($data)
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

echo "<p><strong>HTTP Status:</strong> " . $httpCode . "</p>";

if ($curlError) {
    echo "<p style='color:red;'><strong>cURL Error:</strong> " . htmlspecialchars($curlError) . "</p>";
}

if ($httpCode >= 200 && $httpCode < 300) {
    echo "<p style='color:green;font-weight:bold;font-size:18px;'>✅ Edukas! ESTO API töötab!</p>";
    
    $decoded = json_decode($response, true);
    
    // ESTO response structure: {"errors":[],"data":"...","mac":"..."}
    // The "data" field is a JSON string that needs to be decoded again
    if (isset($decoded['data'])) {
        $purchaseData = json_decode($decoded['data'], true);
        
        if (isset($purchaseData['purchase_url'])) {
            $purchaseUrl = $purchaseData['purchase_url'];
            echo "<div style='background:#e8f5e9;border:2px solid #4caf50;padding:20px;border-radius:10px;margin:20px 0;'>";
            echo "<h3 style='margin-top:0;color:#2e7d32;'>🔗 Makselink valmis!</h3>";
            echo "<p style='margin:10px 0;'><strong>Summa:</strong> " . number_format($purchaseData['amount'], 2) . " " . $purchaseData['currency'] . "</p>";
            echo "<p style='margin:10px 0;'><strong>Tüüp:</strong> " . $purchaseData['schedule_type'] . "</p>";
            echo "<p style='margin:10px 0;'><strong>Staatus:</strong> " . $purchaseData['status'] . "</p>";
            echo "<p style='margin:10px 0;'><strong>ID:</strong> " . $purchaseData['id'] . "</p>";
            echo "<a href='" . htmlspecialchars($purchaseUrl) . "' target='_blank' style='display:inline-block;background:#4caf50;color:white;padding:15px 30px;text-decoration:none;border-radius:8px;font-weight:bold;font-size:16px;margin-top:10px;'>➜ Mine maksma (ESTO)</a>";
            echo "</div>";
            
            echo "<details style='margin-top:20px;'>";
            echo "<summary style='cursor:pointer;font-weight:bold;'>📋 Täielik vastus (JSON)</summary>";
            echo "<pre style='background:#f5f5f5;padding:15px;border-radius:5px;overflow:auto;max-height:400px;margin-top:10px;'>";
            echo htmlspecialchars(json_encode($purchaseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            echo "</pre>";
            echo "</details>";
        } else {
            echo "<p style='color:orange;'>⚠️ purchase_url puudub vastuses</p>";
            echo "<pre style='background:#f5f5f5;padding:15px;border-radius:5px;overflow:auto;max-height:500px;'>";
            echo htmlspecialchars($response);
            echo "</pre>";
        }
    } else {
        echo "<p style='color:orange;'>⚠️ Ootamatu vastuse struktuur</p>";
        echo "<pre style='background:#f5f5f5;padding:15px;border-radius:5px;overflow:auto;max-height:500px;'>";
        echo htmlspecialchars($response);
        echo "</pre>";
    }
} elseif ($httpCode == 401) {
    echo "<p style='color:red;font-weight:bold;'>❌ 401 Unauthorized - Shop ID või Secret Key on vale</p>";
} elseif ($httpCode == 404) {
    echo "<p style='color:red;font-weight:bold;'>❌ 404 Not Found - Endpoint ei eksisteeri</p>";
} elseif ($httpCode == 422) {
    echo "<p style='color:red;font-weight:bold;'>❌ 422 Validation Error</p>";
    echo "<pre style='background:#fff3cd;padding:15px;border-radius:5px;overflow:auto;'>";
    echo htmlspecialchars($response);
    echo "</pre>";
} else {
    echo "<p style='color:red;font-weight:bold;'>❌ API päring ebaõnnestus</p>";
    echo "<pre style='background:#f5f5f5;padding:15px;border-radius:5px;overflow:auto;max-height:500px;'>";
    echo htmlspecialchars($response);
    echo "</pre>";
}
