<?php
if (!defined('ABSPATH')) {
    exit;
}

$product_id = get_query_var('product_id');
$product_sku = get_query_var('product_sku');

// Add Open Graph meta tags for social media sharing before header
add_action('wp_head', function() use ($product_id, $product_sku) {
    if (function_exists('nailedit_get_local_api_base')) {
        $base = nailedit_get_local_api_base();
    } else {
        $base = trailingslashit(get_option('las_api_base_url', 'http://localhost:8083/api/'));
    }
    
    $current_host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
    if (strpos($current_host, '45.93.139.96') !== false) {
        $base = 'http://45.93.139.96:8088/api/';
    }
    
    if ($product_sku) {
        $api_url = $base . 'v1/product/' . rawurlencode(sanitize_text_field($product_sku));
    } elseif ($product_id) {
        $api_url = add_query_arg(array('id' => absint($product_id)), $base . 'v1/products');
    } else {
        return;
    }
    
    $response = wp_remote_get($api_url, array('timeout' => 10));
    if (is_wp_error($response)) {
        return;
    }
    
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    $product = null;
    if (is_array($data) && isset($data['id'])) {
        $product = $data;
    } elseif (isset($data['data'])) {
        if (is_array($data['data']) && isset($data['data']['id'])) {
            $product = $data['data'];
        } elseif (is_array($data['data']) && !empty($data['data'])) {
            $product = $data['data'][0] ?? null;
        }
    }
    
    if (!is_array($product)) {
        return;
    }
    
    // Debug: Log product image data
    error_log('OG Debug - Product ID: ' . ($product['id'] ?? 'unknown'));
    error_log('OG Debug - Has images: ' . (isset($product['images']) ? 'yes' : 'no'));
    error_log('OG Debug - Has base_image: ' . (isset($product['base_image']) ? 'yes' : 'no'));
    if (isset($product['images'])) {
        error_log('OG Debug - Images data: ' . json_encode($product['images']));
    }
    if (isset($product['base_image'])) {
        error_log('OG Debug - Base image data: ' . json_encode($product['base_image']));
    }
    
    $nailedit_lang = function_exists('nailedit_get_current_lang') ? nailedit_get_current_lang() : 'et';
    $translation = array();
    if (isset($product['translations'][$nailedit_lang]) && is_array($product['translations'][$nailedit_lang])) {
        $translation = $product['translations'][$nailedit_lang];
    }
    
    $name = $translation['name'] ?? ($product['name'] ?? '');
    $short_desc = $translation['short_description'] ?? ($product['short_description'] ?? '');
    $description = $translation['description'] ?? ($product['description'] ?? '');
    
    // Get product image - try multiple sources
    $og_image = '';
    
    // Try images array first
    if (!empty($product['images']) && is_array($product['images'])) {
        $first_image = $product['images'][0] ?? null;
        if (is_array($first_image)) {
            $og_image = $first_image['large_image_url'] ?? ($first_image['medium_image_url'] ?? ($first_image['small_image_url'] ?? ($first_image['original_image_url'] ?? '')));
        } elseif (is_string($first_image)) {
            $og_image = $first_image;
        }
    }
    
    // Fallback to base_image
    if (empty($og_image) && !empty($product['base_image'])) {
        if (is_array($product['base_image'])) {
            $og_image = $product['base_image']['large_image_url'] ?? ($product['base_image']['medium_image_url'] ?? ($product['base_image']['small_image_url'] ?? ($product['base_image']['original_image_url'] ?? '')));
        } elseif (is_string($product['base_image'])) {
            $og_image = $product['base_image'];
        }
    }
    
    // Try product_image field
    if (empty($og_image) && !empty($product['product_image'])) {
        $og_image = $product['product_image'];
    }
    
    // Fix image URL (convert localhost to production URL)
    if (!empty($og_image)) {
        if (function_exists('nailedit_fix_image_url')) {
            $og_image = nailedit_fix_image_url($og_image);
        }
        // Ensure HTTPS for social media
        $og_image = str_replace('http://', 'https://', $og_image);
    }
    
    // Fallback to admin settings image if no product image found
    if (empty($og_image) && function_exists('get_field')) {
        $og_image = get_field('nailedit_og_image', 'option');
        error_log('OG Debug - Using fallback admin image: ' . $og_image);
    }
    
    error_log('OG Debug - Final OG image: ' . $og_image);
    
    // Get price
    $price = '';
    if (isset($product['price'])) {
        $price = $product['price'];
    } elseif (isset($product['min_price'])) {
        $price = $product['min_price'];
    }
    
    if ($price && function_exists('nailedit_format_price')) {
        $price = nailedit_format_price($price);
    }
    
    // Clean description for meta tag
    $meta_description = wp_strip_all_tags($short_desc ?: $description);
    $meta_description = substr($meta_description, 0, 200);
    
    ?>
    <!-- Open Graph Meta Tags for Social Media Sharing -->
    <meta property="og:type" content="product" />
    <meta property="og:title" content="<?php echo esc_attr($name); ?>" />
    <?php if ($meta_description): ?>
    <meta property="og:description" content="<?php echo esc_attr($meta_description); ?>" />
    <?php endif; ?>
    <?php if ($og_image): ?>
    <meta property="og:image" content="<?php echo esc_url($og_image); ?>" />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="630" />
    <?php endif; ?>
    <meta property="og:url" content="<?php echo esc_url(get_permalink()); ?>" />
    <meta property="og:site_name" content="<?php echo esc_attr(get_bloginfo('name')); ?>" />
    <?php if ($price): ?>
    <meta property="product:price:amount" content="<?php echo esc_attr($price); ?>" />
    <meta property="product:price:currency" content="EUR" />
    <?php endif; ?>
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="<?php echo esc_attr($name); ?>" />
    <?php if ($meta_description): ?>
    <meta name="twitter:description" content="<?php echo esc_attr($meta_description); ?>" />
    <?php endif; ?>
    <?php if ($og_image): ?>
    <meta name="twitter:image" content="<?php echo esc_url($og_image); ?>" />
    <?php endif; ?>
    <?php
}, 5);

get_header();



if (empty($product_id) && empty($product_sku)) {
    echo '<main class="site-main"><p>' . esc_html( nailedit_get_t( 'no_product_specified' ) ) . '</p></main>';
    get_footer();
    exit;
}

if (function_exists('nailedit_get_local_api_base')) {
    $base = nailedit_get_local_api_base();
} else {
    $base = trailingslashit(get_option('las_api_base_url', 'http://localhost:8083/api/'));
}

// Override API base for VPS deployment
$current_host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
if (strpos($current_host, '45.93.139.96') !== false) {
    $base = 'http://45.93.139.96:8088/api/';
}

// Use lightweight single product endpoint: v1/product/{url_key}
// This is much faster than v1/products?url_key=... which returns excessive data.
if ($product_sku) {
    $api_url = $base . 'v1/product/' . rawurlencode(sanitize_text_field($product_sku));
} elseif ($product_id) {
    // Fallback to old endpoint if only ID is available
    $api_url = add_query_arg(array('id' => absint($product_id)), $base . 'v1/products');
} else {
    $api_url = '';
}

if (empty($api_url)) {
    echo '<main class="site-main"><p>' . esc_html( nailedit_get_t( 'invalid_product_identifier' ) ) . '</p></main>';
    get_footer();
    exit;
}

$response = wp_remote_get(
    $api_url,
    array('timeout' => 20)
);

if (is_wp_error($response)) {
    echo '<main class="site-main"><div class="nailedit-error">' . esc_html($response->get_error_message()) . '</div></main>';
    get_footer();
    exit;
}

$body = wp_remote_retrieve_body($response);
$data = json_decode($body, true);

// If new endpoint fails, try old endpoint as fallback
if ((!is_array($data) || !isset($data['id'])) && $product_sku) {
    $fallback_url = add_query_arg(array('url_key' => sanitize_text_field($product_sku)), $base . 'v1/products');
    $response = wp_remote_get($fallback_url, array('timeout' => 20));
    
    if (!is_wp_error($response)) {
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
    }
}

// Debug output
echo '<!-- DEBUG product_id: ' . esc_html($product_id) . ' -->';
echo '<!-- DEBUG product_sku: ' . esc_html($product_sku) . ' -->';
echo '<!-- DEBUG API URL: ' . esc_html($api_url) . ' -->';
echo '<!-- DEBUG Response body length: ' . strlen($body) . ' -->';
echo '<!-- DEBUG Used fallback: ' . (isset($fallback_url) ? 'yes' : 'no') . ' -->';
if (isset($fallback_url)) {
    echo '<!-- DEBUG Fallback URL: ' . esc_html($fallback_url) . ' -->';
}
if (is_array($data)) {
    echo '<!-- DEBUG Response keys: ' . esc_html(json_encode(array_keys($data))) . ' -->';
    echo '<!-- DEBUG Has id: ' . (isset($data['id']) ? 'yes' : 'no') . ' -->';
    echo '<!-- DEBUG Has data: ' . (isset($data['data']) ? 'yes' : 'no') . ' -->';
    if (isset($data['data'])) {
        echo '<!-- DEBUG data type: ' . gettype($data['data']) . ' -->';
        if (is_array($data['data'])) {
            echo '<!-- DEBUG data has id: ' . (isset($data['data']['id']) ? 'yes' : 'no') . ' -->';
            echo '<!-- DEBUG data keys: ' . esc_html(json_encode(array_keys($data['data']))) . ' -->';
        }
    }
    // Show first 500 chars of response for debugging
    echo '<!-- DEBUG Response preview: ' . esc_html(substr($body, 0, 500)) . ' -->';
} else {
    echo '<!-- DEBUG data is not array, type: ' . gettype($data) . ' -->';
    echo '<!-- DEBUG Response preview: ' . esc_html(substr($body, 0, 500)) . ' -->';
}

// Extract product from API response
// New v1/product/{url_key} endpoint returns product directly (no wrapper)
// Old v1/products?id=... endpoint returns { data: {...} } or { data: [{...}] }
$product = null;
if (is_array($data) && isset($data['id'])) {
    // Response is directly the product (new v1/product/{url_key} endpoint)
    $product = $data;
} elseif (isset($data['data'])) {
    if (is_array($data['data']) && isset($data['data']['id'])) {
        // data is directly the product object
        $product = $data['data'];
    } elseif (is_array($data['data']) && array_keys($data['data']) === range(0, count($data['data']) - 1)) {
        // data is an array of products, take first
        $product = $data['data'][0] ?? null;
    }
}

if (!is_array($product)) {
    echo '<main class="site-main"><div class="nailedit-error">';
    echo '<h3>' . esc_html( nailedit_get_t( 'product_not_found' ) ) . '</h3>';
    echo '<p><strong>Debug info:</strong></p>';
    echo '<p>Product ID: ' . esc_html($product_id ?? 'none') . '</p>';
    echo '<p>Product SKU: ' . esc_html($product_sku ?? 'none') . '</p>';
    echo '<p>API URL: ' . esc_html($api_url ?? 'none') . '</p>';
    echo '<p>Response type: ' . gettype($data) . '</p>';
    if (!empty($body)) {
        echo '<p>Response preview: ' . esc_html(substr($body, 0, 200)) . '</p>';
    }
    echo '</div></main>';
    get_footer();
    exit;
}

echo '<!-- DEBUG product categories: ' . esc_html( json_encode( $product['categories'] ?? 'NOT SET' ) ) . ' -->';
// Determine language from multisite blog ID
$nailedit_lang = function_exists( 'nailedit_get_current_lang' ) ? nailedit_get_current_lang() : 'et';

$translation   = array();
if ( isset( $product['translations'] ) && is_array( $product['translations'] ) ) {
    if ( isset( $product['translations'][ $nailedit_lang ] ) && is_array( $product['translations'][ $nailedit_lang ] ) ) {
        $translation = $product['translations'][ $nailedit_lang ];
    } else {
        $first = reset( $product['translations'] );
        if ( is_array( $first ) ) {
            $translation = $first;
        }
    }
}
$bagisto_id = isset($product['id']) ? (int) $product['id'] : 0;
$name        = $translation['name'] ?? ( $product['name'] ?? nailedit_get_t( 'unnamed_product' ) );
$short_desc  = $translation['short_description'] ?? ( $product['short_description'] ?? '' );
$description = $translation['description'] ?? ( $product['description'] ?? '' );

$variants = isset($product['variants']) && is_array($product['variants']) ? $product['variants'] : array();

// New API doesn't return super_attributes, so we need to build it from variants
$super_attributes = isset($product['super_attributes']) && is_array($product['super_attributes']) ? $product['super_attributes'] : array();

// Debug variant data
echo '<!-- DEBUG Variants count: ' . count($variants) . ' -->';
echo '<!-- DEBUG Super attributes: ' . esc_html(json_encode($super_attributes)) . ' -->';
if (!empty($variants)) {
    echo '<!-- DEBUG First variant: ' . esc_html(json_encode($variants[0])) . ' -->';
}

// If super_attributes is empty but we have variants, build it from variant attributes
if (empty($super_attributes) && !empty($variants)) {
    $attr_map = array();
    // Skip non-configurable attributes like weight
    $skip_attributes = array('weight', 'sku', 'price', 'cost', 'special_price');
    
    foreach ($variants as $variant) {
        if (!empty($variant['attributes']) && is_array($variant['attributes'])) {
            foreach ($variant['attributes'] as $attr) {
                $code = $attr['code'] ?? '';
                
                // Get translated attribute name based on current language
                $attr_name = $code;
                if (!empty($attr['names']) && is_array($attr['names'])) {
                    $attr_name = $attr['names'][$nailedit_lang] ?? ($attr['name'] ?? $code);
                } elseif (!empty($attr['name'])) {
                    $attr_name = $attr['name'];
                }
                
                $value = $attr['value'] ?? '';
                
                // Skip non-configurable attributes
                if (in_array($code, $skip_attributes)) {
                    continue;
                }
                
                if ($code && $value) {
                    if (!isset($attr_map[$code])) {
                        $attr_map[$code] = array(
                            'code' => $code,
                            'name' => $attr_name,
                            'admin_name' => $attr_name,
                            'label' => $attr_name,
                            'options' => array()
                        );
                    }
                    
                    // Add unique option
                    $option_exists = false;
                    foreach ($attr_map[$code]['options'] as $opt) {
                        if ($opt['label'] === $value) {
                            $option_exists = true;
                            break;
                        }
                    }
                    
                    if (!$option_exists) {
                        $attr_map[$code]['options'][] = array(
                            'id' => $value,
                            'label' => $value,
                            'swatch_value' => null
                        );
                    }
                }
            }
        }
    }
    $super_attributes = array_values($attr_map);
}

$is_configurable = (isset($product['type']) && $product['type'] === 'configurable' && !empty($super_attributes) && !empty($variants));

// Debug configurable check
echo '<!-- DEBUG product type: ' . esc_html($product['type'] ?? 'none') . ' -->';
echo '<!-- DEBUG super_attributes count: ' . count($super_attributes) . ' -->';
echo '<!-- DEBUG variants count: ' . count($variants) . ' -->';
echo '<!-- DEBUG is_configurable: ' . ($is_configurable ? 'true' : 'false') . ' -->';



$price = '';

// If product is configurable, show the lowest variant price (or first available)
if ($is_configurable && !empty($variants) && is_array($variants)) {
    $min_value = null;
    $min_display = '';

    foreach ($variants as $v) {
        if (!is_array($v)) {
            continue;
        }

        $display = '';
        if (isset($v['formatted_special_price']) && $v['formatted_special_price'] !== '') {
            $display = (string) $v['formatted_special_price'];
        } elseif (isset($v['formatted_price']) && $v['formatted_price'] !== '') {
            $display = (string) $v['formatted_price'];
        } elseif (isset($v['formatted_regular_price']) && $v['formatted_regular_price'] !== '') {
            $display = (string) $v['formatted_regular_price'];
        }

        $value_str = '';
        if (isset($v['special_price']) && $v['special_price'] !== null && $v['special_price'] !== '') {
            $value_str = (string) $v['special_price'];
        } elseif (isset($v['price']) && $v['price'] !== null && $v['price'] !== '') {
            $value_str = (string) $v['price'];
        }

        if ($value_str === '') {
            continue;
        }

        $value = (float) $value_str;
        if ($min_value === null || $value < $min_value) {
            $min_value = $value;
            $min_display = ($display !== '') ? $display : $value_str;
        }
    }

    if ($min_value !== null) {
        $price = $min_display;
    }
}

// Fallback for simple products (or if variant price missing)
if ($price === '') {
    if (isset($product['formatted_special_price']) && $product['formatted_special_price'] !== '') {
        $price = $product['formatted_special_price'];
    } elseif (isset($product['formatted_price']) && $product['formatted_price'] !== '') {
        $price = $product['formatted_price'];
    } elseif (isset($product['formatted_regular_price']) && $product['formatted_regular_price'] !== '') {
        $price = $product['formatted_regular_price'];
    } elseif (isset($product['price'])) {
        $price = (string) $product['price'];
    }
}

$images = array();
$thumbnails = array();

// New v1/product/{url_key} API returns images as array of { id, position, original, thumbnail, large }
// Old v1/products API returns base_image and images with *_image_url fields
if (!empty($product['images']) && is_array($product['images'])) {
    foreach ($product['images'] as $img) {
        if (!is_array($img)) {
            continue;
        }
        // Newest API format: { id, position, original, thumbnail, large }
        if (!empty($img['large']) && !empty($img['thumbnail'])) {
            // Build full URLs from relative paths
            $large_url = $img['large'];
            $thumb_url = $img['thumbnail'];
            
            // Only add base URL if it's a relative path
            if (strpos($large_url, 'http') !== 0 && strpos($large_url, '/storage/') === 0) {
                $image_base = str_replace('/api/', '/', $base);
                $large_url = rtrim($image_base, '/') . $large_url;
            } elseif (strpos($large_url, 'http') !== 0) {
                $image_base = str_replace('/api/', '/', $base);
                $large_url = rtrim($image_base, '/') . '/storage/' . ltrim($large_url, '/');
            }
            
            if (strpos($thumb_url, 'http') !== 0 && strpos($thumb_url, '/storage/') === 0) {
                $image_base = str_replace('/api/', '/', $base);
                $thumb_url = rtrim($image_base, '/') . $thumb_url;
            } elseif (strpos($thumb_url, 'http') !== 0) {
                $image_base = str_replace('/api/', '/', $base);
                $thumb_url = rtrim($image_base, '/') . '/storage/' . ltrim($thumb_url, '/');
            }
            
            $images[] = $large_url;
            $thumbnails[] = $thumb_url;
        }
        // Previous API format: { original: "product/464/...", optimized: "/storage/cache/..." }
        elseif (!empty($img['original'])) {
            // Build full URL from relative path
            $img_url = $img['original'];
            if (strpos($img_url, 'http') !== 0) {
                $image_base = str_replace('/api/', '/', $base);
                $img_url = rtrim($image_base, '/') . '/storage/' . ltrim($img_url, '/');
            }
            $images[] = $img_url;
            $thumbnails[] = '';
        }
        // Old API format: { large_image_url, medium_image_url, small_image_url, url }
        elseif (!empty($img['large_image_url'])) {
            $images[] = $img['large_image_url'];
            $thumbnails[] = '';
        } elseif (!empty($img['medium_image_url'])) {
            $images[] = $img['medium_image_url'];
            $thumbnails[] = '';
        } elseif (!empty($img['small_image_url'])) {
            $images[] = $img['small_image_url'];
            $thumbnails[] = '';
        } elseif (!empty($img['url'])) {
            $images[] = $img['url'];
            $thumbnails[] = '';
        }
    }
}

// Fallback: Old API base_image field
if (empty($images) && !empty($product['base_image']) && is_array($product['base_image'])) {
    if (!empty($product['base_image']['large_image_url'])) {
        $img_url = add_query_arg(array('width' => 496, 'height' => 496, 'format' => 'webp'), $product['base_image']['large_image_url']);
        $images[] = $img_url;
        $thumbnails[] = '';
    } elseif (!empty($product['base_image']['medium_image_url'])) {
        $img_url = add_query_arg(array('width' => 496, 'height' => 496, 'format' => 'webp'), $product['base_image']['medium_image_url']);
        $images[] = $img_url;
        $thumbnails[] = '';
    }
}

// For configurable products, use first variant's images instead of parent images to avoid flickering
if ($is_configurable && !empty($product['variants']) && is_array($product['variants'])) {
    $first_variant = reset($product['variants']);
    if (!empty($first_variant['images']) && is_array($first_variant['images'])) {
        $temp_images = array();
        $temp_thumbs = array();
        
        foreach ($first_variant['images'] as $img) {
            if (!is_array($img)) {
                continue;
            }
            
            if (!empty($img['large']) && !empty($img['thumbnail'])) {
                $large_url = $img['large'];
                $thumb_url = $img['thumbnail'];
                
                // Add base URL if relative path
                if (strpos($large_url, 'http') !== 0 && strpos($large_url, '/storage/') === 0) {
                    $image_base = str_replace('/api/', '/', $base);
                    $large_url = rtrim($image_base, '/') . $large_url;
                } elseif (strpos($large_url, 'http') !== 0) {
                    $image_base = str_replace('/api/', '/', $base);
                    $large_url = rtrim($image_base, '/') . '/storage/' . ltrim($large_url, '/');
                }
                
                if (strpos($thumb_url, 'http') !== 0 && strpos($thumb_url, '/storage/') === 0) {
                    $image_base = str_replace('/api/', '/', $base);
                    $thumb_url = rtrim($image_base, '/') . $thumb_url;
                } elseif (strpos($thumb_url, 'http') !== 0) {
                    $image_base = str_replace('/api/', '/', $base);
                    $thumb_url = rtrim($image_base, '/') . '/storage/' . ltrim($thumb_url, '/');
                }
                
                // Clean thumbnail URL
                $thumb_url = preg_replace('/_\d+x\d+_(\d+x\d+\.webp)$/i', '_$1', $thumb_url);
                
                $temp_images[] = $large_url;
                $temp_thumbs[] = $thumb_url;
            }
        }
        
        if (!empty($temp_images)) {
            $images = $temp_images;
            $thumbnails = $temp_thumbs;
        }
    }
}

$images = array_values(array_unique(array_filter($images)));
$thumbnails = array_values($thumbnails);

// Debug: Check how many images we have
echo '<!-- DEBUG: Total images loaded: ' . count($images) . ' -->';
echo '<!-- DEBUG: Images array: ' . esc_html(json_encode($images)) . ' -->';

// Pass product name to global header banner
global $nailedit_header_title_override;
$nailedit_header_title_override = $name;

$nailedit_header_image = get_theme_mod( 'nailedit_global_header_image' );

?>

<div class="mb-[50px] h-[150px] overflow-hidden relative flex items-center justify-center">
	<?php if ( $nailedit_header_image ) : ?>
		<img src="<?php echo esc_url( $nailedit_header_image ); ?>" alt="" class="absolute inset-0 w-full h-full object-cover" />
		<div class="relative z-10 px-4 flex flex-col items-center gap-2">
			<?php
			$bread_categories = isset( $product['categories'] ) && is_array( $product['categories'] ) ? $product['categories'] : array();
			$bread_categories = array_filter( $bread_categories, function( $c ) {
				return strtolower( $c['slug'] ?? '' ) !== 'root';
			} );
			?>
			<nav class="text-[13px] text-slate-500" aria-label="Breadcrumb">
				<ol class="flex items-center gap-1 flex-wrap justify-center">
					<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="hover:text-primary transition"><?php echo esc_html( nailedit_get_t( 'home' ) ); ?></a></li>
					<?php foreach ( $bread_categories as $bread_cat ) : ?>
						<li class="before:content-['/'] before:mx-1 before:text-slate-400">
							<?php
							$bread_cat_slug  = $bread_cat['slug'] ?? '';
							$bread_cat_label = '';
							if ( isset( $bread_cat['names'] ) && is_array( $bread_cat['names'] ) ) {
								$bread_cat_label = $bread_cat['names'][ $nailedit_lang ] ?? '';
							}
							if ( $bread_cat_label === '' ) {
								$bread_cat_label = $bread_cat['name'] ?? '';
							}
							?>
							<a href="<?php echo esc_url( home_url( '/category/' . sanitize_title( $bread_cat_slug ) . '/' ) ); ?>" class="hover:text-primary transition"><?php echo esc_html( $bread_cat_label ); ?></a>
						</li>
					<?php endforeach; ?>
					<li class="before:content-['/'] before:mx-1 before:text-slate-400">
						<span class="text-slate-700 font-medium"><?php echo esc_html( $name ); ?></span>
					</li>
				</ol>
			</nav>
			<h1 class="text-[35px] md:text-[40tepx] font-semibold font-nailedit text-primary text-center drop-shadow h1-placeholder">
				<?php echo esc_html($name); ?>
			</h1>
		</div>
	<?php else : ?>
		<div class="px-4 flex flex-col items-center gap-2">
			<?php
			$bread_categories = isset( $product['categories'] ) && is_array( $product['categories'] ) ? $product['categories'] : array();
			$bread_categories = array_filter( $bread_categories, function( $c ) {
				return strtolower( $c['slug'] ?? '' ) !== 'root';
			} );
			?>
			<nav class="text-[13px] text-slate-500" aria-label="Breadcrumb">
				<ol class="flex items-center gap-1 flex-wrap justify-center">
					<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="hover:text-primary transition"><?php echo esc_html( nailedit_get_t( 'home' ) ); ?></a></li>
					<?php foreach ( $bread_categories as $bread_cat ) : ?>
						<li class="before:content-['/'] before:mx-1 before:text-slate-400">
							<?php
							$bread_cat_slug  = $bread_cat['slug'] ?? '';
							$bread_cat_label = '';
							if ( isset( $bread_cat['names'] ) && is_array( $bread_cat['names'] ) ) {
								$bread_cat_label = $bread_cat['names'][ $nailedit_lang ] ?? '';
							}
							if ( $bread_cat_label === '' ) {
								$bread_cat_label = $bread_cat['name'] ?? '';
							}
							?>
							<a href="<?php echo esc_url( home_url( '/category/' . sanitize_title( $bread_cat_slug ) . '/' ) ); ?>" class="hover:text-primary transition"><?php echo esc_html( $bread_cat_label ); ?></a>
						</li>
					<?php endforeach; ?>
					<li class="before:content-['/'] before:mx-1 before:text-slate-400">
						<span class="text-slate-700 font-medium"><?php echo esc_html( $name ); ?></span>
					</li>
				</ol>
			</nav>
			<h1 class="text-[35px] font-semibold font-nailedit text-primary text-center h1-placeholder">
				<?php echo esc_html($name); ?>
			</h1>
		</div>
	<?php endif; ?>
</div>


<main class="site-main nailedit-product-detail   lg:py-10">
	<div class="max-w-[1200px] mx-auto px-4">
		
		<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-20 items-start">
			<!-- LEFT: Gallery card -->
	
<div class="bg-white rounded-24 overflow-hidden  md:p-6 shadow-lg relative">
    <?php if (!empty($images)): ?>
        <!-- Mobile: Swiper slider with dots -->
        <div class="md:hidden">
            <div class="swiper nailedit-product-swiper">
                <div class="swiper-wrapper">
                    <?php foreach ($images as $index => $img_url): ?>
                        <div class="swiper-slide">
                            <div class="overflow-hidden rounded-xl bg-slate-100 aspect-[4/4] flex items-center justify-center">
                                <img
                                    src="<?php echo esc_url($img_url); ?>"
                                    alt="<?php echo esc_attr($name); ?>"
                                    class="w-full h-full object-cover object-top"
                                >
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="swiper-pagination !bottom-2 <?php echo count($images) <= 1 ? 'hidden' : ''; ?>"></div>
            </div>
        </div>

        <!-- Desktop: Main image with thumbnail gallery -->
        <div class="hidden md:block">
            <div class="overflow-hidden rounded-xl bg-slate-100 aspect-[4/4] flex items-center justify-center">
                <img
                    id="nailedit-main-image"
                    src="<?php echo esc_url($images[0]); ?>"
                    alt="<?php echo esc_attr($name); ?>"
                    class="w-full h-full object-cover object-top"
                >
            </div>

            <div id="nailedit-thumbs" class="<?php echo count($images) > 1 ? '' : 'hidden'; ?> flex flex-col gap-2 w-[70px] absolute right-4 top-4 max-h-[400px] lg:max-h-[500px] overflow-y-auto overflow-x-hidden scrollbar-thin scrollbar-thumb-slate-300 scrollbar-track-transparent">
                <?php if (count($images) > 1): ?>
                    <?php foreach ($images as $index => $img_url): 
                        // Use pre-generated thumbnail if available, otherwise fallback to main image
                        $thumb_url = !empty($thumbnails[$index]) ? $thumbnails[$index] : $img_url;
                    ?>
                        <div class="rounded-xl overflow-hidden border border-slate-200 bg-slate-50 cursor-pointer hover:border-primary transition flex-shrink-0 w-[70px] h-[70px]">
                            <img
                                src="<?php echo esc_url($thumb_url); ?>"
                                alt="<?php echo esc_attr($name); ?>"
                                class="nailedit-thumb w-[70px] h-[70px] object-cover object-top"
                                data-index="<?php echo (int) $index; ?>"
                                data-full-url="<?php echo esc_attr($img_url); ?>"
                            >
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
    <?php else: ?>
        <div class="flex-1 flex items-center justify-center rounded-24 bg-slate-100 min-h-[260px] text-slate-500 text-sm">
            <?php echo esc_html( nailedit_get_t( 'no_image_available' ) ); ?>
        </div>
    <?php endif; ?>
</div>
			<!-- RIGHT: Info card -->
			<div class="  flex flex-col gap-4">
				<?php
				// Build variant_map early so wishlist button can use it
				$variant_map = array();
				if ($is_configurable && !empty($variants) && is_array($variants)) {
					foreach ($variants as $v) {
						$variant_id = (int) $v['id'];
						$color_val = '';
						$size_val = '';
						
						if (!empty($v['attributes']) && is_array($v['attributes'])) {
							foreach ($v['attributes'] as $attr) {
								if (isset($attr['code']) && isset($attr['value'])) {
									$code = $attr['code'];
									$val = (string) $attr['value'];
									
									if ($code === 'color' && $val !== '') {
										$color_val = $val;
									} elseif ($code === 'size' && $val !== '') {
										$size_val = $val;
									}
								}
							}
						}
						
						// Fallback to direct properties
						if (empty($color_val) && isset($v['color'])) {
							$color_val = (string) $v['color'];
						}
						if (empty($size_val) && isset($v['size'])) {
							$size_val = (string) $v['size'];
						}
						
						$key = '';
						if ($color_val !== '' && $size_val !== '') {
							$key = $color_val . '-' . $size_val;
							$variant_map[$key] = $variant_id;
						} elseif ($color_val !== '') {
							$key = $color_val;
							$variant_map[$key] = $variant_id;
						}
					}
				}
				?>
				<div class="flex items-start justify-between gap-3">
					<div>
						<div id="nailedit-review-summary" class="flex items-center gap-1 text-[#f5b300] text-sm mb-1">
							<span id="nailedit-review-summary-stars">★★★★★</span>
							<span id="nailedit-review-summary-text" class="text-[11px] text-primary ml-1"><?php echo esc_html( nailedit_get_t( 'no_reviews_yet' ) ); ?></span>
						</div>
						<?php if ($short_desc): ?>
							<div class="short-description-wrapper mt-4">
								<div id="short-description" class="text-[17px] text-slate-700 leading-snug max-h-[120px] overflow-hidden transition-all duration-300">
									<?php echo wp_kses_post(wpautop($short_desc)); ?>
								</div>
								<?php if (strlen($short_desc) > 200): ?>
									<button type="button" id="toggle-description" class="text-primary text-[14px] font-medium mt-2 hover:underline">
										<span id="toggle-text"><?php echo esc_html( nailedit_get_t( 'read_more' ) ); ?></span>
										<span id="toggle-icon" class="ml-1">▼</span>
									</button>
								<?php endif; ?>
							</div>
						<?php endif; ?>
					</div>
<button
						id="toggle-wishlist-btn"
						class="nailedit-wishlist-btn min-w-9 w-9 h-9 rounded-full border border-slate-300 flex items-center justify-center text-slate-600 hover:border-primary hover:text-primary transition"
						data-product-id="<?php echo esc_attr($bagisto_id); ?>"
						<?php if ($is_configurable && !empty($variant_map)): ?>
						data-configurable="1"
						data-variant-map="<?php echo esc_attr(wp_json_encode($variant_map)); ?>"
						<?php endif; ?>
						aria-label="<?php echo esc_attr( nailedit_get_t( 'add_to_wishlist' ) ); ?>"
					>
						<span><svg class="nailedit-icon" style="width: 18px; height: 18px;"><use xlink:href="#heart-thick-svg"></use></svg></span>
					</button>
				</div>
				<div id="wishlist-message" class="nailedit-wishlist-message text-xs text-green-600"></div>

				<div class="flex flex-wrap items-center gap-3 text-[12px] mt-1">
					<span class="inline-flex items-center rounded-full bg-[#e3f5e5] text-[#2f8f3a] px-3 py-1">
						<?php echo esc_html( nailedit_get_t( 'in_stock' ) ); ?>
					</span>
				</div>


				

				<?php if ($is_configurable): ?>
					<?php
						$variant_map = array();
						$variant_price_map = array();
						$used_option_ids_by_code = array();
						$super_attr_codes = array();
						$variant_images_map = array();
						$variant_thumbnails_map = array();
						$variant_swatch_map = array();
						$variant_select_options = array();
						
						// Sort super_attributes to show color/color2 first
						usort($super_attributes, function($a, $b) {
							$code_a = isset($a['code']) ? (string) $a['code'] : '';
							$code_b = isset($b['code']) ? (string) $b['code'] : '';
							
							$is_color_a = ($code_a === 'color' || $code_a === 'color2');
							$is_color_b = ($code_b === 'color' || $code_b === 'color2');
							
							if ($is_color_a && !$is_color_b) {
								return -1;
							}
							if (!$is_color_a && $is_color_b) {
								return 1;
							}
							return 0;
						});
						
						foreach ($super_attributes as $attr) {
							$code = isset($attr['code']) ? (string) $attr['code'] : '';
							if (! $code) {
								continue;
							}
							$super_attr_codes[] = $code;
							$used_option_ids_by_code[$code] = array();
						}

						foreach ($variants as $v) {
            $variant_id = (int) $v['id'];
            $variant_name = isset($v['name']) ? (string) $v['name'] : '';
            
            // Extract all super attribute values dynamically
            $attr_values = array();
            
            if (!empty($v['attributes']) && is_array($v['attributes'])) {
                foreach ($v['attributes'] as $attr) {
                    if (isset($attr['code']) && isset($attr['value'])) {
                        $code = $attr['code'];
                        $val = (string) $attr['value'];
                        
                        // Track used option IDs for all super attributes
                        if (in_array($code, $super_attr_codes) && $val !== '') {
                            $used_option_ids_by_code[$code][$val] = true;
                            // Store first non-empty value for each super attribute
                            if (!isset($attr_values[$code])) {
                                $attr_values[$code] = $val;
                            }
                        }
                    }
                }
            }
            
            // Fallback to direct properties (for old API format)
            if (empty($attr_values['color']) && isset($v['color'])) {
                $attr_values['color'] = (string) $v['color'];
            }
            if (empty($attr_values['size']) && isset($v['size'])) {
                $attr_values['size'] = (string) $v['size'];
            }
            
            // Build variant key from all super attribute values in order
            $key_parts = array();
            foreach ($super_attr_codes as $code) {
                if (!empty($attr_values[$code])) {
                    $key_parts[] = $attr_values[$code];
                }
            }
            $key = implode('-', $key_parts);
            
            if ($key !== '') {
                $variant_map[$key] = $variant_id;
                
                // Store variant price (both regular and special for sale display)
                $variant_price = '';
                $variant_regular_price = '';
                $has_special = false;
                
                // Check for special price first
                if (isset($v['special_price']) && $v['special_price'] !== null && $v['special_price'] !== '') {
                    $has_special = true;
                    if (isset($v['formatted_special_price']) && $v['formatted_special_price'] !== '') {
                        $variant_price = (string) $v['formatted_special_price'];
                    } else {
                        $variant_price = nailedit_format_price((string) $v['special_price']);
                    }
                    
                    // Get regular price for strikethrough
                    if (isset($v['formatted_price']) && $v['formatted_price'] !== '') {
                        $variant_regular_price = (string) $v['formatted_price'];
                    } elseif (isset($v['price']) && $v['price'] !== null && $v['price'] !== '') {
                        $variant_regular_price = nailedit_format_price((string) $v['price']);
                    }
                } else {
                    // No special price, use regular price
                    if (isset($v['formatted_price']) && $v['formatted_price'] !== '') {
                        $variant_price = (string) $v['formatted_price'];
                    } elseif (isset($v['formatted_regular_price']) && $v['formatted_regular_price'] !== '') {
                        $variant_price = (string) $v['formatted_regular_price'];
                    } elseif (isset($v['price']) && $v['price'] !== null && $v['price'] !== '') {
                        $variant_price = nailedit_format_price((string) $v['price']);
                    }
                }
                
                $variant_price_map[$key] = array(
                    'price' => $variant_price,
                    'regular_price' => $variant_regular_price,
                    'has_special' => $has_special
                );
                
                $variant_select_options[] = array_merge(
                    array('id' => $variant_id, 'name' => $variant_name),
                    $attr_values
                );
            }

            $variant_images = array();
            $variant_thumbs = array();
            $swatch_url = '';
            
            // New API format: images array with id, position, original, thumbnail, large
            if (!empty($v['images']) && is_array($v['images'])) {
                foreach ($v['images'] as $img) {
                    if (!is_array($img)) {
                        continue;
                    }
                    
                    if ($swatch_url === '' && !empty($img['thumbnail'])) {
                        $swatch_url = $img['thumbnail'];
                    } elseif ($swatch_url === '' && !empty($img['large']) && is_string($img['large'])) {
                        $large_url = (string) $img['large'];
                        if (preg_match('/_800x800\\.webp$/i', $large_url)) {
                            $swatch_url = preg_replace('/_800x800\\.webp$/i', '_80x80.webp', $large_url);
                        } else {
                            $swatch_url = preg_replace('/\\.(jpg|jpeg|png|gif|webp)$/i', '_80x80.webp', $large_url);
                        }
                    }
                    
                    // Collect both large images and thumbnails
                    if (!empty($img['large']) && !empty($img['thumbnail'])) {
                        $large_url = $img['large'];
                        $thumb_url = $img['thumbnail'];
                        
                        // Add base URL if relative path
                        if (strpos($large_url, 'http') !== 0 && strpos($large_url, '/storage/') === 0) {
                            $image_base = str_replace('/api/', '/', $base);
                            $large_url = rtrim($image_base, '/') . $large_url;
                        } elseif (strpos($large_url, 'http') !== 0) {
                            $image_base = str_replace('/api/', '/', $base);
                            $large_url = rtrim($image_base, '/') . '/storage/' . ltrim($large_url, '/');
                        }
                        
                        if (strpos($thumb_url, 'http') !== 0 && strpos($thumb_url, '/storage/') === 0) {
                            $image_base = str_replace('/api/', '/', $base);
                            $thumb_url = rtrim($image_base, '/') . $thumb_url;
                        } elseif (strpos($thumb_url, 'http') !== 0) {
                            $image_base = str_replace('/api/', '/', $base);
                            $thumb_url = rtrim($image_base, '/') . '/storage/' . ltrim($thumb_url, '/');
                        }
                        
                        // Clean thumbnail URL: remove any size markers like _992x992_ before _80x80.webp
                        $thumb_url = preg_replace('/_\d+x\d+_(\d+x\d+\.webp)$/i', '_$1', $thumb_url);
                        
                        $variant_images[] = $large_url;
                        $variant_thumbs[] = $thumb_url;
                    }
                    // Fallback: only large available
                    elseif (!empty($img['large'])) {
                        $img_url = $img['large'];
                        // Only add base URL if it's a relative path
                        if (strpos($img_url, 'http') !== 0 && strpos($img_url, '/storage/') === 0) {
                            $image_base = str_replace('/api/', '/', $base);
                            $img_url = rtrim($image_base, '/') . $img_url;
                        } elseif (strpos($img_url, 'http') !== 0) {
                            $image_base = str_replace('/api/', '/', $base);
                            $img_url = rtrim($image_base, '/') . '/storage/' . ltrim($img_url, '/');
                        }
                        $variant_images[] = $img_url;
                        $variant_thumbs[] = '';
                    }
                    // Fallback to original if large is not available
                    elseif (!empty($img['original'])) {
                        $img_url = $img['original'];
                        if (strpos($img_url, 'http') !== 0) {
                            $image_base = str_replace('/api/', '/', $base);
                            $img_url = rtrim($image_base, '/') . $img_url;
                        }
                        $variant_images[] = $img_url;
                    }
                    // Old format with large_image_url
                    elseif (!empty($img['large_image_url'])) {
                        $variant_images[] = $img['large_image_url'];
                    } elseif (!empty($img['medium_image_url'])) {
                        $variant_images[] = $img['medium_image_url'];
                    } elseif (!empty($img['small_image_url'])) {
                        $variant_images[] = $img['small_image_url'];
                    } elseif (!empty($img['url'])) {
                        $variant_images[] = $img['url'];
                    }
                }
            }
            
            // Fallback: Old API base_image field
            if (empty($variant_images) && !empty($v['base_image']) && is_array($v['base_image'])) {
                if (!empty($v['base_image']['large_image_url'])) {
                    $variant_images[] = $v['base_image']['large_image_url'];
                } elseif (!empty($v['base_image']['medium_image_url'])) {
                    $variant_images[] = $v['base_image']['medium_image_url'];
                }
            }

            if ($swatch_url !== '') {
                if (strpos($swatch_url, 'http') !== 0 && strpos($swatch_url, '/storage/') === 0) {
                    $image_base = str_replace('/api/', '/', $base);
                    $swatch_url = rtrim($image_base, '/') . $swatch_url;
                } elseif (strpos($swatch_url, 'http') !== 0) {
                    $image_base = str_replace('/api/', '/', $base);
                    $swatch_url = rtrim($image_base, '/') . '/storage/' . ltrim($swatch_url, '/');
                }
            }

            $variant_images = array_values(array_unique(array_filter($variant_images)));
            $variant_thumbs = array_values($variant_thumbs);
            
            if (!empty($variant_images)) {
                $variant_images_map[$variant_id] = $variant_images;
                $variant_thumbnails_map[$variant_id] = $variant_thumbs;
            }
            
            // Store swatch by each super attribute value (for color/color2)
            foreach ($super_attr_codes as $code) {
                if (!empty($attr_values[$code]) && $swatch_url !== '') {
                    $variant_swatch_map[$attr_values[$code]] = $swatch_url;
                }
            }
						}
					?>
					<div class="mt-3 flex flex-wrap gap-3 text-[12px] flex-col vvcont">
						<?php foreach ($super_attributes as $attr): ?>
							<?php
								$code    = isset($attr['code']) ? (string) $attr['code'] : '';
								$raw_label = isset($attr['admin_name']) ? (string) $attr['admin_name'] : (isset($attr['name']) ? (string) $attr['name'] : '');
								$attr_labels_et = array(
									'color'  => nailedit_get_t( 'color' ),
									'color2' => nailedit_get_t( 'color' ),
									'size'   => nailedit_get_t( 'size' ),
								);
								$label = isset( $attr_labels_et[ $code ] ) ? $attr_labels_et[ $code ] : $raw_label;
								$options = isset($attr['options']) && is_array($attr['options']) ? $attr['options'] : array();
								if (! $code || empty($options)) {
									continue;
								}
								$allowed_option_ids = isset($used_option_ids_by_code[$code]) && is_array($used_option_ids_by_code[$code])
									? $used_option_ids_by_code[$code]
									: array();
							?>
                            
							<div class="flex flex-col gap-1">
								<span class="text-primary text-[17px] font-medium"><?php echo esc_html($label); ?>: <span class="nailedit-selected-label text-primary" data-attr-code="<?php echo esc_attr($code); ?>">—</span></span>
								<?php if ($code === 'color' || $code === 'color2'): ?>
									<div class="nailedit-variant-swatches flex flex-wrap gap-2" data-attr-code="<?php echo esc_attr($code); ?>">
										<?php foreach ($options as $opt): ?>
											<?php
												$opt_id    = isset($opt['id']) ? (string) $opt['id'] : '';
												$opt_label = isset($opt['label']) ? (string) $opt['label'] : (isset($opt['admin_name']) ? (string) $opt['admin_name'] : '');
												if (! $opt_id) {
													continue;
												}
												if (!empty($allowed_option_ids) && !isset($allowed_option_ids[$opt_id])) {
													continue;
												}

												$swatch_key = $opt_id;
												$swatch_src = $variant_swatch_map[$swatch_key] ?? '';
											?>
											<button type="button" class="nailedit-swatch-btn w-[40px] h-[40px] rounded-full overflow-hidden border border-slate-200 bg-slate-50" data-value="<?php echo esc_attr($opt_id); ?>" aria-label="<?php echo esc_attr($opt_label); ?>">
												<?php if ($swatch_src): ?>
													<img src="<?php echo esc_url($swatch_src); ?>" alt="" class="w-full h-full object-cover object-top" loading="lazy">
												<?php endif; ?>
											</button>
										<?php endforeach; ?>
									</div>

									<select class="nailedit-variant-select  max-w-[200px]  border border-slate-300 rounded-full px-3 py-1 text-[12px] text-slate-800 bg-white hidden" data-attr-code="<?php echo esc_attr($code); ?>">
										<option value=""><?php echo esc_html( nailedit_get_t( 'select' ) ); ?></option>
										<?php foreach ($options as $opt): ?>
											<?php
												$opt_id    = isset($opt['id']) ? (string) $opt['id'] : '';
												$opt_label = isset($opt['label']) ? (string) $opt['label'] : (isset($opt['admin_name']) ? (string) $opt['admin_name'] : '');
												if (! $opt_id) {
													continue;
												}
												if (!empty($allowed_option_ids) && !isset($allowed_option_ids[$opt_id])) {
													continue;
												}
											?>
											<option value="<?php echo esc_attr($opt_id); ?>"><?php echo esc_html($opt_label); ?></option>
										<?php endforeach; ?>
									</select>
								<?php else: ?>
									<select class="nailedit-variant-select max-w-[200px] border border-slate-300 rounded-full px-3 py-1 text-[12px] text-slate-800 bg-white" data-attr-code="<?php echo esc_attr($code); ?>">
										<option value=""><?php echo esc_html( nailedit_get_t( 'select' ) ); ?></option>
										<?php foreach ($options as $opt): ?>
											<?php
												$opt_id    = isset($opt['id']) ? (string) $opt['id'] : '';
												$opt_label = isset($opt['label']) ? (string) $opt['label'] : (isset($opt['admin_name']) ? (string) $opt['admin_name'] : '');
												if (! $opt_id) {
													continue;
												}
												if (!empty($allowed_option_ids) && !isset($allowed_option_ids[$opt_id])) {
													continue;
												}
											?>
											<option value="<?php echo esc_attr($opt_id); ?>"><?php echo esc_html($opt_label); ?></option>
										<?php endforeach; ?>
									</select>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<?php if ($price !== ''): ?>
					<div class="mt-2 flex items-center gap-3 flex-wrap">
						<div class="flex items-baseline gap-2">
							<p id="nailedit-product-price" class="text-[22px] md:text-[26px] font-bold text-primary"><?php echo esc_html(nailedit_format_price($price)); ?></p>
							<p id="nailedit-product-regular-price" class="text-[16px] text-slate-500 line-through hidden"></p>
						</div>
						<span id="nailedit-sale-badge" class="hidden inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800">
							<?php echo esc_html( nailedit_get_t( 'sale' ) ); ?>
						</span>
					</div>
				<?php endif; ?>

				<div class="mt-4 flex flex-wrap items-center gap-4">
					<div class="inline-flex items-center gap-2 rounded-full border border-primary bg-white px-3 py-1 shadow-sm">
		    			<button type="button" class="qty-minus w-[20px] h-[20px] rounded-md bg-slate-100 border border-slate-200 text-slate-700 text-sm flex items-center justify-center">-</button>
		    			
		    			<input 
		        			type="number"
		        			name="quantity"
		        			min="1"
		        			step="1"
		        			value="1"
		        			class="qty-input w-8 h-8 text-center text-[13px] bg-transparent border-0 focus:outline-none appearance-none"
		    			/>
		    			
		    			<button type="button" class="qty-plus w-[20px] h-[20px] rounded-md bg-slate-100 border border-slate-200 text-slate-700 text-sm flex items-center justify-center">+</button>
					</div>
					
					<div class="flex-1 min-w-[180px]">
						<button
							id="add-to-cart-btn"
							class="nailedit-add-to-cart-btn w-full rounded-full gradient-secondary  text-slate-900 font-semibold text-[14px] py-3 shadow-md flex items-center justify-center gap-2 hover:bg-fourth hover:text-white transition"
							data-product-id="<?php echo esc_attr($bagisto_id); ?>"
							<?php if ($is_configurable && !empty($variant_map)): ?>
							data-configurable="1"
							data-variant-map="<?php echo esc_attr(wp_json_encode($variant_map)); ?>"
							<?php endif; ?>
						>
							<span class="inline-flex items-center justify-center w-5 h-5">
								<svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
									<path d="M7 4h-2l-1 2h2l2.68 8.03A2 2 0 0 0 10.6 15h7.55a1 1 0 0 0 .97-.76l1.7-7A1 1 0 0 0 19.86 6H8.21L7 4zm2.5 15a1.5 1.5 0 1 1-3 0a1.5 1.5 0 0 1 3 0zm9 0a1.5 1.5 0 1 1-3 0a1.5 1.5 0 0 1 3 0z" />
								</svg>
							</span>
							<span><?php echo esc_html( nailedit_get_t( 'add_to_cart_short' ) ); ?></span>
						</button>
					</div>
				</div>
				<div id="cart-message" class="nailedit-cart-message text-sm mt-2"></div>

		

			
			</div>
</div>

		</div>
        
        <div class="max-w-[1200px] mx-auto px-4 mt-10">

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-20 items-start">

        <div class="lg:border-r lg:border-slate-200 lg:pr-10">
		<?php if ($description && $description !== $short_desc): ?>
					<div class="mt-4 text-[17px] text-slate-700 leading-snug">
						<?php echo wp_kses_post(wpautop($description)); ?>
					</div>
				<?php endif; ?>

        </div>
                    <div class="asdf lg:pl-10">
                        <section id="nailedit-review-section" class="p-4 md:p-6">
                            <h2 class="text-[18px] md:text-[35px] text-primary font-semibold   mb-3 font-nailedit"><?php echo esc_html( nailedit_get_t( 'leave_review' ) ); ?></h2>
                            
                            <!-- Star Rating -->
                            <div class="mb-4">
                                <label class="block text-[14px] font-medium text-slate-700 mb-2"><?php echo esc_html( nailedit_get_t( 'rating' ) ); ?></label>
                                <div id="nailedit-star-rating" class="flex gap-1" data-rating="5">
                                    <span class="nailedit-star cursor-pointer text-[28px] transition-colors duration-150" data-value="1">★</span>
                                    <span class="nailedit-star cursor-pointer text-[28px] transition-colors duration-150" data-value="2">★</span>
                                    <span class="nailedit-star cursor-pointer text-[28px] transition-colors duration-150" data-value="3">★</span>
                                    <span class="nailedit-star cursor-pointer text-[28px] transition-colors duration-150" data-value="4">★</span>
                                    <span class="nailedit-star cursor-pointer text-[28px] transition-colors duration-150" data-value="5">★</span>
                                </div>
                            </div>

                            <textarea
                                id="nailedit-review-text"
                                class="w-full rounded-2xl border border-slate-300 px-3 py-2 text-[13px] text-slate-900 focus:outline-none focus:ring-2 focus:ring-secondary mb-3"
                                rows="4"
                                placeholder="<?php echo esc_attr( nailedit_get_t( 'write_review_placeholder' ) ); ?>"
                            ></textarea>
                            <button
                                id="nailedit-review-submit"
                                type="button"
                                class="rounded-full gradient-secondary  text-slate-900 font-semibold text-[14px] px-6 py-2 hover:bg-fourth hover:text-white transition"
                            >
                                <?php echo esc_html( nailedit_get_t( 'send_review' ) ); ?>
                            </button>
                            <div id="nailedit-review-message" class="text-sm mt-2"></div>
                        </section>
                        <section id="nailedit-review-list-section" class="  p-4 md:p-6   mt-6">
                            <h2 class="text-[18px] md:text-[20px]  font-semibold text-primary mb-3"><?php echo esc_html( nailedit_get_t( 'customer_reviews' ) ); ?></h2>
                            <div id="nailedit-review-list" class="space-y-4 text-[13px] text-slate-800">
                                <p class="text-primary"><?php echo esc_html( nailedit_get_t( 'loading_reviews' ) ); ?></p>
                            </div>
                        </section>
                    </div>
                </div>
                </div>
	</div>
</main>

<script>
    
document.addEventListener('DOMContentLoaded', function () {
    // Initialize Swiper for mobile product gallery
    const productSwiper = new Swiper('.nailedit-product-swiper', {
        slidesPerView: 1,
        spaceBetween: 0,
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
            dynamicBullets: true,
        },
        loop: false,
        autoHeight: false,
        speed: 100,
        touchRatio: 1.5,
        threshold: 5,
    });

    const mainImage = document.getElementById('nailedit-main-image');
    const thumbsContainer = document.getElementById('nailedit-thumbs');
    const priceElement = document.getElementById('nailedit-product-price');
    const regularPriceElement = document.getElementById('nailedit-product-regular-price');
    const saleBadge = document.getElementById('nailedit-sale-badge');
    const originalImages = <?php echo wp_json_encode($images); ?>;
    const variantImagesMap = <?php echo wp_json_encode(isset($variant_images_map) && is_array($variant_images_map) ? $variant_images_map : array()); ?>;
    const variantThumbnailsMap = <?php echo wp_json_encode(isset($variant_thumbnails_map) && is_array($variant_thumbnails_map) ? $variant_thumbnails_map : array()); ?>;
    const variantPriceMap = <?php echo wp_json_encode(isset($variant_price_map) && is_array($variant_price_map) ? $variant_price_map : array()); ?>;
    const apiBase = <?php echo wp_json_encode(trailingslashit($base)); ?>;
    const addToCartBtn = document.getElementById('add-to-cart-btn');
    let variantIdMap = {};
    const variantImagesCache = {};

    if (addToCartBtn && addToCartBtn.dataset && addToCartBtn.dataset.variantMap) {
        try {
            variantIdMap = JSON.parse(addToCartBtn.dataset.variantMap || '{}') || {};
        } catch (e) {
            variantIdMap = {};
        }
    }
    
    console.log('Variant ID Map:', variantIdMap);
    console.log('Variant Images Map:', variantImagesMap);
    console.log('Original images:', originalImages);
    console.log('Add to cart button dataset:', addToCartBtn ? addToCartBtn.dataset : null);
    
    // Debug: Log all variant IDs and their images
    if (variantImagesMap) {
        Object.keys(variantImagesMap).forEach(function(vid) {
            console.log('Variant ID ' + vid + ' has ' + (variantImagesMap[vid] ? variantImagesMap[vid].length : 0) + ' images');
        });
    }

    function bindThumbClicks() {
        const thumbs = document.querySelectorAll('.nailedit-thumb');
        if (!thumbs.length || !mainImage) {
            return;
        }
        thumbs.forEach(function(thumb) {
            thumb.addEventListener('click', function() {
                // Use full-size URL from data attribute if available, otherwise use thumbnail src
                const fullUrl = this.getAttribute('data-full-url') || this.src;
                mainImage.src = fullUrl;
                thumbs.forEach(function(t) { t.classList.remove('active'); });
                this.classList.add('active');
            });
        });
        thumbs[0].classList.add('active');
    }

    function renderThumbnails(urls, thumbUrls) {
        if (!thumbsContainer) {
            console.error('thumbsContainer not found!');
            return;
        }

        const safeUrls = Array.isArray(urls) ? urls.filter(Boolean) : [];
        const safeThumbs = Array.isArray(thumbUrls) ? thumbUrls : [];
        console.log('renderThumbnails called with', safeUrls.length, 'images:', safeUrls);

        // Hide container only when there are no images.
        // If there is 1 image, we still show it as a single thumbnail.
        if (safeUrls.length === 0) {
            thumbsContainer.innerHTML = '';
            thumbsContainer.classList.add('hidden');
            return;
        }

        thumbsContainer.classList.remove('hidden');
        thumbsContainer.innerHTML = safeUrls.map(function(u, idx) {
            // Use provided thumbnail URL if available, otherwise generate from large image
            let thumbUrl = safeThumbs[idx] || u;
            
            // If no thumbnail provided, try to generate one
            if (!safeThumbs[idx]) {
                const baseUrl = u.split('?')[0];
                
                if (baseUrl.indexOf('/storage/cache/') > -1 || baseUrl.indexOf('/cache/') > -1) {
                    // If already a thumb, keep it
                    if (/_80x80\.webp$/i.test(baseUrl)) {
                        thumbUrl = baseUrl;
                    } 
                    // If it has any size marker (800x800, 992x992, 496x496, etc), replace with 80x80
                    else if (/_\d+x\d+\.webp$/i.test(baseUrl)) {
                        thumbUrl = baseUrl.replace(/_\d+x\d+\.webp$/i, '_80x80.webp');
                    } 
                    else {
                        // Replace file extension with _80x80.webp
                        thumbUrl = baseUrl.replace(/\.(jpg|jpeg|png|gif|webp)$/i, '_80x80.webp');
                    }
                }
            }
            
            const fullUrl = String(u).replace(/"/g, '&quot;');
            const thumbUrlEscaped = String(thumbUrl).replace(/"/g, '&quot;');
            
            return (
                '<div class="rounded-2xl overflow-hidden min-h-[70px] border border-slate-200 bg-slate-50 cursor-pointer hover:border-primary transition">' +
                    '<img src="' + thumbUrlEscaped + '" alt="" loading="lazy" class="nailedit-thumb w-[70px] h-[70px] object-cover object-top" data-index="' + String(idx) + '" data-full-url="' + fullUrl + '">' +
                '</div>'
            );
        }).join('');

        bindThumbClicks();
    }

    function setGalleryImages(urls, thumbUrls) {
        const safeUrls = Array.isArray(urls) ? urls.filter(Boolean) : [];
        const safeThumbs = Array.isArray(thumbUrls) ? thumbUrls : [];
        if (!mainImage) {
            return;
        }
        if (safeUrls.length) {
            mainImage.src = safeUrls[0];
        }
        renderThumbnails(safeUrls, safeThumbs);
        
        // Update mobile Swiper with new images
        if (productSwiper && safeUrls.length > 0) {
            productSwiper.removeAllSlides();
            safeUrls.forEach(function(url) {
                const slideHtml = '<div class="swiper-slide">' +
                    '<div class="overflow-hidden rounded-xl bg-slate-100 aspect-[4/4] flex items-center justify-center">' +
                    '<img src="' + url + '" alt="" class="w-full h-full object-cover object-top">' +
                    '</div>' +
                    '</div>';
                productSwiper.appendSlide(slideHtml);
            });
            productSwiper.slideTo(0);
            // Update pagination after adding slides
            if (productSwiper.pagination) {
                productSwiper.pagination.render();
                productSwiper.pagination.update();
                
                // Show/hide pagination based on number of slides
                const paginationEl = productSwiper.pagination.el;
                if (paginationEl) {
                    if (safeUrls.length > 1) {
                        paginationEl.classList.remove('hidden');
                    } else {
                        paginationEl.classList.add('hidden');
                    }
                }
            }
        }
    }

    bindThumbClicks();

    // Function to update images and price based on selected variants
    function updateVariantImages() {
        const selects = document.querySelectorAll('.nailedit-variant-select');
        const attrValues = [];
        let allSelected = true;

        selects.forEach(function(select) {
            const val = select.value || '';
            if (val) {
                attrValues.push(val);
            } else {
                allSelected = false;
            }
        });

        // If all variants are selected, try to find matching variant images and price
        if (allSelected && attrValues.length > 0) {
            const variantKey = attrValues.join('-');
            console.log('Looking for variant with key:', variantKey);
            
            // Update price if available
            if (variantPriceMap && variantPriceMap[variantKey] && priceElement) {
                const priceData = variantPriceMap[variantKey];
                
                if (typeof priceData === 'object') {
                    // Update main price
                    priceElement.textContent = priceData.price || '';
                    
                    // Show/hide regular price and sale badge
                    if (priceData.has_special && priceData.regular_price) {
                        if (regularPriceElement) {
                            regularPriceElement.textContent = priceData.regular_price;
                            regularPriceElement.classList.remove('hidden');
                        }
                        if (saleBadge) {
                            saleBadge.classList.remove('hidden');
                        }
                    } else {
                        if (regularPriceElement) {
                            regularPriceElement.classList.add('hidden');
                        }
                        if (saleBadge) {
                            saleBadge.classList.add('hidden');
                        }
                    }
                    console.log('Updated price to:', priceData);
                } else {
                    // Fallback for old format (string)
                    priceElement.textContent = priceData;
                    if (regularPriceElement) regularPriceElement.classList.add('hidden');
                    if (saleBadge) saleBadge.classList.add('hidden');
                }
            }
            
            // Try to find variant ID from the variant map
            if (variantIdMap && variantIdMap[variantKey]) {
                const variantId = variantIdMap[variantKey];
                console.log('Found variant ID:', variantId);
                
                // Check if we have images for this variant
                if (variantImagesMap && variantImagesMap[variantId]) {
                    const variantImages = variantImagesMap[variantId];
                    const variantThumbs = variantThumbnailsMap && variantThumbnailsMap[variantId] ? variantThumbnailsMap[variantId] : [];
                    console.log('Found variant images:', variantImages);
                    console.log('Found variant thumbnails:', variantThumbs);
                    setGalleryImages(variantImages, variantThumbs);
                    return;
                }
            }
        }
        
        // Fallback: show original product images if no variant images found
        console.log('No variant images found, showing original images');
        setGalleryImages(originalImages, []);
    }

    // Auto-select first option for all variant selects
    const allSelects = document.querySelectorAll('.nailedit-variant-select');
    allSelects.forEach(function(select) {
        const firstOption = select.querySelector('option[value]:not([value=""])');
        if (firstOption) {
            select.value = firstOption.value;
            
            // Update label
            const attrCode = select.getAttribute('data-attr-code');
            const label = document.querySelector('.nailedit-selected-label[data-attr-code="' + attrCode + '"]');
            if (label) {
                label.textContent = firstOption.textContent;
            }
            
            // If this is a color/color2 attribute with swatches, mark first swatch as active
            const swatchContainer = document.querySelector('.nailedit-variant-swatches[data-attr-code="' + attrCode + '"]');
            if (swatchContainer) {
                const firstSwatch = swatchContainer.querySelector('.nailedit-swatch-btn[data-value="' + firstOption.value + '"]');
                if (firstSwatch) {
                    firstSwatch.classList.add('ring-2', 'ring-primary', 'ring-offset-2');
                }
            }
        }
    });
    
    // Update variant images after auto-selection
    updateVariantImages();

    // Bind change event to all variant selects
    allSelects.forEach(function(select) {
        select.addEventListener('change', function() {
            const attrCode = this.getAttribute('data-attr-code');
            const value = this.value;
            
            // Update label
            const label = document.querySelector('.nailedit-selected-label[data-attr-code="' + attrCode + '"]');
            if (label) {
                const selectedOption = this.querySelector('option[value="' + value + '"]');
                label.textContent = selectedOption ? selectedOption.textContent : '—';
            }
            
            // Update images and price
            updateVariantImages();
        });
    });

    // Bind swatch button clicks to update hidden select elements
    const swatchButtons = document.querySelectorAll('.nailedit-swatch-btn');
    swatchButtons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            const value = this.getAttribute('data-value');
            const attrCode = this.closest('.nailedit-variant-swatches').getAttribute('data-attr-code');
            
            // Find and update the corresponding hidden select
            const select = document.querySelector('.nailedit-variant-select[data-attr-code="' + attrCode + '"]');
            if (select) {
                select.value = value;
                
                // Update the selected label
                const label = document.querySelector('.nailedit-selected-label[data-attr-code="' + attrCode + '"]');
                if (label) {
                    const selectedOption = select.querySelector('option[value="' + value + '"]');
                    label.textContent = selectedOption ? selectedOption.textContent : '—';
                }
            }
            
            // Visual feedback: mark this button as active
            const siblings = this.closest('.nailedit-variant-swatches').querySelectorAll('.nailedit-swatch-btn');
            siblings.forEach(function(s) {
                s.classList.remove('ring-2', 'ring-primary', 'ring-offset-2');
            });
            this.classList.add('ring-2', 'ring-primary', 'ring-offset-2');
            
            // Update variant images based on selection
            updateVariantImages();
        });
    });

    const cartMessage = document.getElementById('cart-message');
    const qtyInput   = document.querySelector('.qty-input');

    if (addToCartBtn && cartMessage) {
        		addToCartBtn.addEventListener('click', function() {
			let productId    = this.dataset.productId;
			const storedCookie = localStorage.getItem('bagisto_cart_cookie');
			const authToken    = localStorage.getItem('bagisto_auth_token');
			const guestCartToken = localStorage.getItem('bagisto_guest_cart_token');

            			let selectedConfigurableOption = '';
			let superAttributePayload = '';

			// Handle configurable products: resolve selected variant ID
			if (this.dataset.configurable === '1') {
                var selects = document.querySelectorAll('.nailedit-variant-select');
                var attrValues = [];
                var allSelected = true;

                selects.forEach(function(select) {
                    const val = select.value || '';
                    if (val) {
                        attrValues.push(val);
                    } else {
                        allSelected = false;
                    }
                });

                // If not all required variants are selected, show message
                if (!allSelected) {
                    // Add visual error feedback to unselected fields
                    selects.forEach(function(select) {
                        const container = select.closest('.flex.flex-col.gap-1');
                        if (container) {
                            if (!select.value) {
                                container.classList.add('border', 'border-red-500', 'rounded-lg', 'p-2', '-m-2');
                            } else {
                                container.classList.remove('border', 'border-red-500', 'rounded-lg', 'p-2', '-m-2');
                            }
                        }
                    });
                    
                    var msgSel = '<?php echo esc_js( nailedit_get_t( 'select_variations_first' ) ); ?>';
					if (typeof window.naileditShowToast === 'function') {
						window.naileditShowToast(msgSel, 'error');
					} else {
						cartMessage.textContent = msgSel;
						cartMessage.style.color = 'red';
					}
                    return;
                }
                
                // Remove error styling when all selected
                selects.forEach(function(select) {
                    const container = select.closest('.flex.flex-col.gap-1');
                    if (container) {
                        container.classList.remove('border', 'border-red-500', 'rounded-lg', 'p-2', '-m-2');
                    }
                });

                // Resolve variant ID using variant map
                if (this.dataset && this.dataset.variantMap) {
                    try {
                        var variantIdMap = JSON.parse(this.dataset.variantMap || '{}') || {};
                        var variantKey = attrValues.join('-');
                        
                        if (variantKey && variantIdMap[variantKey]) {
                            productId = variantIdMap[variantKey];
                        }
                    } catch (e) {
                        console.error('Error parsing variant map:', e);
                    }
                }
            }
            
			let quantity = 1;
            if (qtyInput) {
                const parsed = parseInt(qtyInput.value || '1', 10);
                quantity = isNaN(parsed) || parsed <= 0 ? 1 : parsed;
            }

            			const formData = new FormData();
			formData.append('action', 'nailedit_add_to_cart');
			formData.append('product_id', productId);
			formData.append('quantity', quantity);
            if (storedCookie) {
                formData.append('stored_cookie', storedCookie);
            }
            			if (authToken) {
				formData.append('auth_token', authToken);
			} else if (guestCartToken) {
				formData.append('cart_token', guestCartToken);
			}
			if (selectedConfigurableOption) {
				formData.append('selected_configurable_option', selectedConfigurableOption);
			}
			if (superAttributePayload) {
				formData.append('super_attribute', superAttributePayload);
			}

			addToCartBtn.disabled = true;
			addToCartBtn.textContent = '<?php echo esc_js( nailedit_get_t( 'adding_to_cart' ) ); ?>';
			cartMessage.textContent = '';

			fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
				method: 'POST',
				body: formData
			})
			.then(function(response) { return response.json(); })
			.then(function(result) {
						if (result && result.cart_token) {
							localStorage.setItem('bagisto_guest_cart_token', result.cart_token);
						}
						if (result && result.cookies) {
                    const cookieStr = Array.isArray(result.cookies) ? result.cookies.join('; ') : result.cookies;
                    if (cookieStr) {
                        localStorage.setItem('bagisto_cart_cookie', cookieStr);
                    }
                }
						if (result && result.success) {
                    cartMessage.textContent = '';
                    cartMessage.style.color = '';
                    if (typeof window.naileditShowToast === 'function') {
                        window.naileditShowToast('<?php echo esc_js( nailedit_get_t( 'product_added_to_cart_short' ) ); ?>', 'success');
                    }
                    if (typeof window.naileditUpdateCartBadge === 'function') {
                        window.naileditUpdateCartBadge();
                    }
                } else {
                    const msg = (result && result.data && result.data.message) ? result.data.message : '<?php echo esc_js( nailedit_get_t( 'unknown_error' ) ); ?>';
                    throw new Error(msg);
                }
            })
			.catch(function(error) {
				console.error('Cart API error:', error);
				cartMessage.textContent = '<?php echo esc_js( nailedit_get_t( 'error_prefix' ) ); ?>' + error.message;
				cartMessage.style.color = 'red';
			})
			.finally(function() {
				addToCartBtn.disabled = false;
				addToCartBtn.textContent = '<?php echo esc_js( nailedit_get_t( 'add_to_cart_short' ) ); ?>';
			});
		});
    }

    const wishlistBtn = document.getElementById('toggle-wishlist-btn');
    const wishlistMessage = document.getElementById('wishlist-message');

    if (wishlistBtn && wishlistMessage) {
        wishlistBtn.addEventListener('click', function() {
            let productId    = this.dataset.productId;
            const isConfigurable = this.dataset.configurable === '1';
            
            console.log('Wishlist button clicked');
            console.log('Product ID:', productId);
            console.log('Is configurable:', isConfigurable);
            console.log('Button dataset:', this.dataset);
            
            // Handle configurable products: resolve selected variant ID
            if (isConfigurable) {
                var selects = document.querySelectorAll('.nailedit-variant-select');
                var attrValues = [];
                var allSelected = true;

                console.log('Found variant selects:', selects.length);

                selects.forEach(function(select) {
                    const val = select.value || '';
                    console.log('Select value:', val);
                    if (val) {
                        attrValues.push(val);
                    } else {
                        allSelected = false;
                    }
                });

                console.log('Attribute values:', attrValues);
                console.log('All selected:', allSelected);

                				// If not all required variants are selected, show message
				if (!allSelected) {
					wishlistMessage.textContent = '⚠️ <?php echo esc_js( nailedit_get_t( 'select_variations_first' ) ); ?>';
					wishlistMessage.className = 'nailedit-wishlist-message text-sm text-red-600 font-semibold';
					
					console.log('Showing variant selection warning');
                    
                    // Clear message after 3 seconds
                    setTimeout(function() {
                        wishlistMessage.textContent = '';
                    }, 3000);
                    return;
                }

                // Resolve variant ID using variant map
                if (this.dataset && this.dataset.variantMap) {
                    try {
                        var variantIdMap = JSON.parse(this.dataset.variantMap || '{}') || {};
                        var variantKey = attrValues.join('-');
                        
                        if (variantKey && variantIdMap[variantKey]) {
                            productId = variantIdMap[variantKey];
                        }
                    } catch (e) {
                        console.error('Error parsing variant map:', e);
                    }
                }
            }
            
            const storedCookie = localStorage.getItem('bagisto_cart_cookie');
            const authToken    = localStorage.getItem('bagisto_auth_token');

            const formData = new FormData();
            formData.append('action', 'nailedit_toggle_wishlist');
            formData.append('product_id', productId);
            if (storedCookie) {
                formData.append('stored_cookie', storedCookie);
            }
            if (authToken) {
                formData.append('auth_token', authToken);
            }

            wishlistBtn.disabled = true;
            wishlistMessage.textContent = '';

            fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
                method: 'POST',
                body: formData
            })
            .then(function(response) { return response.json(); })
            .then(function(result) {
                if (result && result.success) {
                    wishlistMessage.textContent = '';
                    wishlistMessage.style.color = '';
                    if (typeof window.naileditShowToast === 'function') {
                        window.naileditShowToast('<?php echo esc_js( nailedit_get_t( 'product_added_to_wishlist' ) ); ?>', 'success');
                    }
                } else if (result && !result.success) {
                    var msg = (result.message || (result.data && result.data.message)) || '<?php echo esc_js( nailedit_get_t( 'could_not_add_to_wishlist' ) ); ?>';
                    if (msg === 'Unauthenticated.' || msg === 'Unauthenticated') {
                        msg = '<?php echo esc_js( nailedit_get_t( 'login_to_add_to_wishlist' ) ); ?>';
                    }
                    wishlistMessage.textContent = msg;
                    wishlistMessage.style.color = 'red';
                } else {
                    wishlistMessage.textContent = '<?php echo esc_js( nailedit_get_t( 'unknown_error' ) ); ?>';
                    wishlistMessage.style.color = 'red';
                }
            })
            .catch(function(error) {
                console.error('Wishlist API error:', error);
                wishlistMessage.textContent = '<?php echo esc_js( nailedit_get_t( 'something_went_wrong' ) ); ?>';
                wishlistMessage.style.color = 'red';
            })
            .finally(function() {
                wishlistBtn.disabled = false;
            });
        });
    }

    // Toggle description functionality
    const toggleBtn = document.getElementById('toggle-description');
    const shortDesc = document.getElementById('short-description');
    const toggleText = document.getElementById('toggle-text');
    const toggleIcon = document.getElementById('toggle-icon');
    
    if (toggleBtn && shortDesc) {
        let isExpanded = false;
        
        toggleBtn.addEventListener('click', function() {
            isExpanded = !isExpanded;
            
            if (isExpanded) {
                // Expand
                shortDesc.classList.remove('max-h-[120px]', 'overflow-hidden');
                shortDesc.classList.add('max-h-[9999px]');
                toggleText.textContent = '<?php echo esc_js( nailedit_get_t( 'read_less' ) ); ?>';
                toggleIcon.textContent = '▲';
            } else {
                // Collapse
                shortDesc.classList.add('max-h-[120px]', 'overflow-hidden');
                shortDesc.classList.remove('max-h-[9999px]');
                toggleText.textContent = '<?php echo esc_js( nailedit_get_t( 'read_more' ) ); ?>';
                toggleIcon.textContent = '▼';
            }
        });
    }
});
</script>


<?php
get_footer(); ?>