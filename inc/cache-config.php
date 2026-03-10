<?php
/**
 * Cache configuration for shortcodes and API calls
 * 
 * Centralized cache duration settings for all theme components.
 * All durations are in seconds.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Get cache duration for a specific component
 * 
 * @param string $component Component name (e.g., 'products', 'categories', 'featured_products')
 * @return int Cache duration in seconds
 */
function nailedit_get_cache_duration( $component ) {
    $durations = array(
        // Shortcodes
        'products_shortcode'          => 100 * MINUTE_IN_SECONDS,  // 10 minutes
        'categories_shortcode'        => 100 * MINUTE_IN_SECONDS,  // 10 minutes
        'popular_products_shortcode'  => 0 * MINUTE_IN_SECONDS,  // 10 minutes
        'featured_products_shortcode' => 0 * MINUTE_IN_SECONDS,  // 10 minutes
        
        // Category pages
        'category_products'           => 2 * MINUTE_IN_SECONDS,   // 2 minutes
        'category_list'               => 5 * MINUTE_IN_SECONDS,   // 5 minutes
        'price_range'                 => 3 * MINUTE_IN_SECONDS,   // 3 minutes
        'sidebar_categories'          => 5 * MINUTE_IN_SECONDS,   // 5 minutes
        
        // Product pages
        'single_product'              => 5 * MINUTE_IN_SECONDS,   // 5 minutes
        
        // Default fallback
        'default'                     => 10 * MINUTE_IN_SECONDS,  // 10 minutes
    );
    
    // Allow filtering cache durations via WordPress filter
    $durations = apply_filters( 'nailedit_cache_durations', $durations );
    
    return isset( $durations[ $component ] ) ? (int) $durations[ $component ] : (int) $durations['default'];
}

/**
 * Check if caching is enabled for current user
 * 
 * @return bool True if cache should be used, false otherwise
 */
function nailedit_use_cache() {
    // Disable cache for logged-in users (they always see fresh data)
    if ( is_user_logged_in() ) {
        return false;
    }
    
    // Allow disabling cache via filter
    return apply_filters( 'nailedit_use_cache', true );

}




/**
 * Clear all nailedit transients
 * 
 * @return int Number of transients deleted
 */
function nailedit_clear_all_cache() {
    global $wpdb;
    
    $deleted = $wpdb->query(
        "DELETE FROM {$wpdb->options} 
        WHERE option_name LIKE '_transient_nailedit_%' 
        OR option_name LIKE '_transient_timeout_nailedit_%'"
    );
    
    return $deleted;
}

/**
 * Clear cache for a specific component
 * 
 * @param string $component Component name or cache key pattern
 * @return int Number of transients deleted
 */
function nailedit_clear_component_cache( $component ) {
    global $wpdb;
    
    $pattern = 'nailedit_' . $component . '%';
    
    $deleted = $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->options} 
            WHERE option_name LIKE %s 
            OR option_name LIKE %s",
            '_transient_' . $pattern,
            '_transient_timeout_' . $pattern
        )
    );
    
    return $deleted;
}

/**
 * Get cache statistics
 * 
 * @return array Cache statistics
 */
function nailedit_get_cache_stats() {
    global $wpdb;
    
    $total = $wpdb->get_var(
        "SELECT COUNT(*) FROM {$wpdb->options} 
        WHERE option_name LIKE '_transient_nailedit_%'"
    );
    
    $size = $wpdb->get_var(
        "SELECT SUM(LENGTH(option_value)) FROM {$wpdb->options} 
        WHERE option_name LIKE '_transient_nailedit_%'"
    );
    
    return array(
        'total_items' => (int) $total,
        'total_size'  => (int) $size,
        'size_mb'     => round( (int) $size / 1024 / 1024, 2 ),
    );
}
