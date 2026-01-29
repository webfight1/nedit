<?php
/**
 * Test Wishlist API with CURL - move to cart
 * Access: /test-curl-api.php?item_id=677&token=YOUR_TOKEN
 */

// Get wishlist item ID from URL
$wishlist_item_id = isset($_GET['item_id']) ? intval($_GET['item_id']) : 677;
$bagisto_token = isset($_GET['token']) ? $_GET['token'] : '';

$api_url = 'http://45.93.139.96:8088/api/v1/customer/wishlist/' . $wishlist_item_id . '/move-to-cart';

echo "<h1>Wishlist API Test (CURL)</h1>";
echo "<p><strong>Testing:</strong> " . htmlspecialchars($api_url) . "</p>";

if (!$bagisto_token) {
    ?>
    <p style="color: red;">Please provide auth token:</p>
    <form method="GET">
        <input type="hidden" name="item_id" value="<?php echo $wishlist_item_id; ?>">
        <p>
            <label>Bagisto Auth Token:</label><br>
            <input type="text" name="token" style="width: 500px;" placeholder="e.g., 8|eXAudLku9Vhr9WsdIEdAa9NmdeCzx6DPeP9XUYRH655d2cc2" required>
        </p>
        <p>
            <label>Wishlist Item ID:</label><br>
            <input type="number" name="item_id" value="<?php echo $wishlist_item_id; ?>">
        </p>
        <button type="submit">Test API with CURL</button>
    </form>
    <?php
    exit;
}

echo "<p><strong>Token:</strong> " . htmlspecialchars(substr($bagisto_token, 0, 50)) . "...</p>";

// Test with CURL
echo "<h2>Testing with CURL...</h2>";

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Accept: application/json',
    'Authorization: Bearer ' . $bagisto_token,
    'Content-Type: application/json'
));

$start_time = microtime(true);
$response = curl_exec($ch);
$end_time = microtime(true);

$duration = round(($end_time - $start_time) * 1000, 2);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);

curl_close($ch);

echo "<p><strong>Request took:</strong> " . $duration . " ms</p>";

if ($curl_error) {
    echo "<p style='color: red;'><strong>CURL Error:</strong> " . htmlspecialchars($curl_error) . "</p>";
    exit;
}

echo "<h2>Response:</h2>";
echo "<p><strong>HTTP Status Code:</strong> " . $http_code . "</p>";

$data = json_decode($response, true);

if ($data) {
    echo "<h3>Parsed JSON:</h3>";
    echo "<pre>" . htmlspecialchars(print_r($data, true)) . "</pre>";
} else {
    echo "<h3>Raw Response:</h3>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
}

if ($http_code === 200 && isset($data['message'])) {
    echo "<p style='color: green; font-size: 20px;'><strong>✓ SUCCESS:</strong> " . htmlspecialchars($data['message']) . "</p>";
    
    if (isset($data['data'])) {
        echo "<h3>Response Data:</h3>";
        echo "<pre>" . htmlspecialchars(print_r($data['data'], true)) . "</pre>";
    }
} else {
    echo "<p style='color: red; font-size: 20px;'><strong>✗ FAILED:</strong> " . htmlspecialchars($data['message'] ?? 'Unknown error') . "</p>";
}

// Also test if we can reach the API at all
echo "<hr>";
echo "<h2>Testing API Connectivity...</h2>";

$test_url = 'http://45.93.139.96:8088/api/v1/customer/wishlist';

$ch2 = curl_init();
curl_setopt($ch2, CURLOPT_URL, $test_url);
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_TIMEOUT, 10);
curl_setopt($ch2, CURLOPT_HTTPHEADER, array(
    'Accept: application/json',
    'Authorization: Bearer ' . $bagisto_token
));

$test_response = curl_exec($ch2);
$test_http_code = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
$test_error = curl_error($ch2);
curl_close($ch2);

echo "<p><strong>GET /v1/customer/wishlist Status:</strong> " . $test_http_code . "</p>";

if ($test_error) {
    echo "<p style='color: red;'>Connection Error: " . htmlspecialchars($test_error) . "</p>";
} elseif ($test_http_code === 200) {
    echo "<p style='color: green;'>✓ API is reachable and authenticated</p>";
    $test_data = json_decode($test_response, true);
    if (isset($test_data['data']) && is_array($test_data['data'])) {
        echo "<p>Found " . count($test_data['data']) . " items in wishlist</p>";
        if (!empty($test_data['data'])) {
            echo "<h3>Available Wishlist Items:</h3>";
            echo "<ul>";
            foreach ($test_data['data'] as $item) {
                $item_id = $item['id'] ?? 'N/A';
                $product_name = $item['product']['name'] ?? 'Unknown';
                echo "<li>ID: " . htmlspecialchars($item_id) . " - " . htmlspecialchars($product_name) . "</li>";
            }
            echo "</ul>";
        }
    }
} else {
    echo "<p style='color: orange;'>API returned status: " . $test_http_code . "</p>";
}
