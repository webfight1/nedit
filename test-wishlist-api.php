<?php
/**
 * Test Wishlist API - move to cart
 * Access: /test-wishlist-api.php?item_id=677
 */

// Get wishlist item ID from URL
$wishlist_item_id = isset($_GET['item_id']) ? intval($_GET['item_id']) : 677;

// Get credentials from localStorage (you'll need to paste these)
$bagisto_token = isset($_GET['token']) ? $_GET['token'] : '';

$api_url = 'http://45.93.139.96:8088/api/v1/customer/wishlist/' . $wishlist_item_id . '/move-to-cart';

echo "<h1>Wishlist API Test</h1>";
echo "<p><strong>Testing:</strong> " . htmlspecialchars($api_url) . "</p>";

if (!$bagisto_token) {
    ?>
    <p style="color: red;">Please provide auth token:</p>
    <form method="GET">
        <input type="hidden" name="item_id" value="<?php echo $wishlist_item_id; ?>">
        <p>
            <label>Bagisto Auth Token (required):</label><br>
            <input type="text" name="token" style="width: 500px;" placeholder="Paste from localStorage.getItem('bagisto_auth_token')" required>
        </p>
        <p>
            <label>Wishlist Item ID:</label><br>
            <input type="number" name="item_id" value="<?php echo $wishlist_item_id; ?>">
        </p>
        <button type="submit">Test API</button>
    </form>
    <hr>
    <p><strong>How to get auth token:</strong></p>
    <ol>
        <li>Open browser console (F12)</li>
        <li>Type: <code>localStorage.getItem('bagisto_auth_token')</code></li>
        <li>Copy the value (without quotes) and paste above</li>
        <li>Click "Test API"</li>
    </ol>
    <p><strong>Note:</strong> Cart cookie is not needed - auth token is sufficient for authenticated requests.</p>
    <?php
    exit;
}

echo "<p><strong>Token:</strong> " . htmlspecialchars(substr($bagisto_token, 0, 50)) . "...</p>";

$headers = array(
    'Accept' => 'application/json',
    'Authorization' => 'Bearer ' . $bagisto_token
);

echo "<h2>Making API Request...</h2>";
echo "<p>Sending POST request to: " . htmlspecialchars($api_url) . "</p>";
echo "<p>With Authorization header: Bearer " . htmlspecialchars(substr($bagisto_token, 0, 20)) . "...</p>";

$start_time = microtime(true);

$response = wp_remote_post(
    $api_url,
    array(
        'timeout' => 30,
        'headers' => $headers,
        'sslverify' => false,
    )
);

$end_time = microtime(true);
$duration = round(($end_time - $start_time) * 1000, 2);

echo "<p><strong>Request took:</strong> " . $duration . " ms</p>";

if (is_wp_error($response)) {
    echo "<p style='color: red;'><strong>WP_Error:</strong> " . htmlspecialchars($response->get_error_message()) . "</p>";
    echo "<pre>" . htmlspecialchars(print_r($response, true)) . "</pre>";
    exit;
}

$status_code = wp_remote_retrieve_response_code($response);
$body_raw = wp_remote_retrieve_body($response);
$data = json_decode($body_raw, true);

echo "<h2>Response:</h2>";
echo "<p><strong>Status Code:</strong> " . $status_code . "</p>";
echo "<pre>" . htmlspecialchars(print_r($data, true)) . "</pre>";

echo "<h2>Raw Response:</h2>";
echo "<pre>" . htmlspecialchars($body_raw) . "</pre>";

if ($status_code === 200 && isset($data['message'])) {
    echo "<p style='color: green; font-size: 20px;'><strong>✓ SUCCESS:</strong> " . htmlspecialchars($data['message']) . "</p>";
} else {
    echo "<p style='color: red; font-size: 20px;'><strong>✗ FAILED:</strong> " . htmlspecialchars($data['message'] ?? 'Unknown error') . "</p>";
}

// Check cookies in response
$response_cookies = wp_remote_retrieve_cookies($response);
if (!empty($response_cookies)) {
    echo "<h2>Response Cookies:</h2>";
    echo "<pre>";
    foreach ($response_cookies as $cookie) {
        echo "Name: " . $cookie->name . "\n";
        echo "Value: " . substr($cookie->value, 0, 50) . "...\n";
        echo "---\n";
    }
    echo "</pre>";
}
