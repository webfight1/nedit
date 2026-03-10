# Cache Configuration Guide

## Overview

All shortcodes and API calls use centralized cache configuration. Cache durations can be easily adjusted in one place.

## Configuration File

**Location:** `/inc/cache-config.php`

## Cache Durations

All cache durations are defined in the `nailedit_get_cache_duration()` function:

```php
$durations = array(
    // Shortcodes
    'products_shortcode'          => 10 * MINUTE_IN_SECONDS,  // 10 minutes
    'categories_shortcode'        => 10 * MINUTE_IN_SECONDS,  // 10 minutes
    'popular_products_shortcode'  => 10 * MINUTE_IN_SECONDS,  // 10 minutes
    'featured_products_shortcode' => 10 * MINUTE_IN_SECONDS,  // 10 minutes
    
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
```

## How to Change Cache Duration

### Method 1: Edit cache-config.php (Recommended)

Open `/inc/cache-config.php` and modify the durations:

```php
'products_shortcode' => 30 * MINUTE_IN_SECONDS,  // Change to 30 minutes
'categories_shortcode' => 1 * HOUR_IN_SECONDS,   // Change to 1 hour
'popular_products_shortcode' => 5 * MINUTE_IN_SECONDS,  // Change to 5 minutes
```

### Method 2: Use WordPress Filter

Add to your `functions.php` or custom plugin:

```php
add_filter( 'nailedit_cache_durations', function( $durations ) {
    // Override specific cache durations
    $durations['products_shortcode'] = 30 * MINUTE_IN_SECONDS;
    $durations['categories_shortcode'] = 1 * HOUR_IN_SECONDS;
    
    return $durations;
});
```

## Time Constants

WordPress provides helpful time constants:

- `MINUTE_IN_SECONDS` = 60 seconds
- `HOUR_IN_SECONDS` = 3600 seconds
- `DAY_IN_SECONDS` = 86400 seconds
- `WEEK_IN_SECONDS` = 604800 seconds

**Examples:**
```php
5 * MINUTE_IN_SECONDS   // 5 minutes (300 seconds)
2 * HOUR_IN_SECONDS     // 2 hours (7200 seconds)
1 * DAY_IN_SECONDS      // 1 day (86400 seconds)
```

## Cache Management Functions

### Check if Cache is Enabled

```php
if ( nailedit_use_cache() ) {
    // Cache is enabled
}
```

**Note:** Cache is automatically disabled for logged-in users.

### Clear All Cache

```php
$deleted = nailedit_clear_all_cache();
echo "Deleted $deleted cache entries";
```

### Clear Specific Component Cache

```php
$deleted = nailedit_clear_component_cache( 'products_shortcode' );
echo "Deleted $deleted product shortcode cache entries";
```

### Get Cache Statistics

```php
$stats = nailedit_get_cache_stats();
echo "Total cache items: " . $stats['total_items'];
echo "Total cache size: " . $stats['size_mb'] . " MB";
```

## Manual Cache Clearing

### Via URL (Admin Only)

Add `?clear_cache=1` to any category page URL:
```
https://yoursite.com/category/builder-gel/?clear_cache=1
```

This clears ALL nailedit transients (requires user to be logged in with edit permissions).

### Via WordPress Admin

Install a cache plugin like "Transient Cleaner" or use WP-CLI:

```bash
wp transient delete --all
```

Or delete specific transients:
```bash
wp transient delete nailedit_products_shortcode_*
```

## Cache Keys

Cache keys are automatically generated based on:
- Component name
- Language (ET/EN)
- Shortcode attributes
- Page number
- Filters

**Example cache keys:**
```
nailedit_products_shortcode_et_limit_12
nailedit_categories_shortcode_en
nailedit_popular_products_shortcode_et_limit_8
nailedit_category_products_builder-gel_p1
```

## Performance Recommendations

### High Traffic Sites
```php
'products_shortcode'          => 30 * MINUTE_IN_SECONDS,  // 30 min
'categories_shortcode'        => 1 * HOUR_IN_SECONDS,     // 1 hour
'popular_products_shortcode'  => 1 * HOUR_IN_SECONDS,     // 1 hour
'featured_products_shortcode' => 1 * HOUR_IN_SECONDS,     // 1 hour
```

### Frequently Updated Products
```php
'products_shortcode'          => 2 * MINUTE_IN_SECONDS,   // 2 min
'category_products'           => 1 * MINUTE_IN_SECONDS,   // 1 min
'single_product'              => 2 * MINUTE_IN_SECONDS,   // 2 min
```

### Development (Disable Cache)
```php
add_filter( 'nailedit_use_cache', '__return_false' );
```

Or set very short durations:
```php
'products_shortcode' => 10,  // 10 seconds
```

## Debugging Cache

### Check if Cache is Being Used

Add to your template:
```php
$cache_key = 'nailedit_products_shortcode_et_limit_12';
$cached = get_transient( $cache_key );

if ( $cached !== false ) {
    echo "<!-- Cache HIT for: $cache_key -->";
} else {
    echo "<!-- Cache MISS for: $cache_key -->";
}
```

### View All Cached Items

```php
global $wpdb;
$results = $wpdb->get_results(
    "SELECT option_name, LENGTH(option_value) as size 
    FROM {$wpdb->options} 
    WHERE option_name LIKE '_transient_nailedit_%'
    ORDER BY size DESC"
);

foreach ( $results as $row ) {
    echo $row->option_name . ': ' . round($row->size / 1024, 2) . ' KB<br>';
}
```

## Best Practices

1. **Balance freshness vs performance**: Longer cache = faster but less fresh data
2. **Different durations for different components**: Products change more often than categories
3. **Clear cache after bulk updates**: Use `nailedit_clear_all_cache()` after importing products
4. **Monitor cache size**: Use `nailedit_get_cache_stats()` to track cache growth
5. **Disable cache for logged-in users**: Already implemented by default
6. **Test cache behavior**: Use `?clear_cache=1` to verify fresh data

## Troubleshooting

### Cache Not Clearing
- Check if you're logged in (cache disabled for logged-in users)
- Verify transients are being deleted from database
- Check if object cache (Redis/Memcached) is enabled

### Stale Data Showing
- Reduce cache duration for that component
- Clear cache manually with `nailedit_clear_all_cache()`
- Check if caching plugin is interfering

### Performance Issues
- Increase cache durations
- Check cache statistics with `nailedit_get_cache_stats()`
- Consider implementing object cache (Redis/Memcached)

## Example: Custom Cache Duration Setup

```php
// In functions.php or custom plugin

// Set custom durations
add_filter( 'nailedit_cache_durations', function( $durations ) {
    // Products update every 15 minutes
    $durations['products_shortcode'] = 15 * MINUTE_IN_SECONDS;
    
    // Categories rarely change - cache for 2 hours
    $durations['categories_shortcode'] = 2 * HOUR_IN_SECONDS;
    
    // Popular products update hourly
    $durations['popular_products_shortcode'] = 1 * HOUR_IN_SECONDS;
    
    // Single product pages - 10 minutes
    $durations['single_product'] = 10 * MINUTE_IN_SECONDS;
    
    return $durations;
});

// Add admin menu to clear cache
add_action( 'admin_menu', function() {
    add_management_page(
        'Clear Nailedit Cache',
        'Clear Cache',
        'manage_options',
        'nailedit-clear-cache',
        function() {
            if ( isset( $_POST['clear_cache'] ) ) {
                $deleted = nailedit_clear_all_cache();
                echo '<div class="notice notice-success"><p>Cleared ' . $deleted . ' cache entries!</p></div>';
            }
            
            $stats = nailedit_get_cache_stats();
            ?>
            <div class="wrap">
                <h1>Nailedit Cache Management</h1>
                <p>Total cache items: <?php echo $stats['total_items']; ?></p>
                <p>Total cache size: <?php echo $stats['size_mb']; ?> MB</p>
                
                <form method="post">
                    <input type="submit" name="clear_cache" class="button button-primary" value="Clear All Cache">
                </form>
            </div>
            <?php
        }
    );
});
```
