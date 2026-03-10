<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Get translation string based on language
 * 
 * @param string $key Translation key
 * @param string $lang Language code (et or en)
 * @return string Translated string
 */
function nailedit_translate( $key, $lang = 'et' ) {
    static $translations = array();
    
    // Validate language
    $allowed = array( 'et', 'en', 'ru' );
    $lang = in_array( $lang, $allowed, true ) ? $lang : 'et';
    
    // Load translations if not already loaded
    if ( ! isset( $translations[ $lang ] ) ) {
        $file = get_template_directory() . '/inc/translations/' . $lang . '.php';
        if ( file_exists( $file ) ) {
            $translations[ $lang ] = include $file;
        } else {
            $translations[ $lang ] = array();
        }
    }
    
    // Return translation or key if not found
    return isset( $translations[ $lang ][ $key ] ) ? $translations[ $lang ][ $key ] : $key;
}

/**
 * Determine language from shortcode attributes or WordPress multisite blog ID
 * 
 * @param array $atts Shortcode attributes
 * @return string Language code (et or en)
 */
function nailedit_get_shortcode_lang( $atts ) {
    // Check if lang parameter is set in shortcode
    if ( isset( $atts['lang'] ) && in_array( $atts['lang'], array( 'et', 'en', 'ru' ), true ) ) {
        return $atts['lang'];
    }
    
    // Check multisite blog ID
    if ( is_multisite() ) {
        $blog_id = get_current_blog_id();
        if ( $blog_id === 3 ) {
            return 'en';
        }
        if ( $blog_id === 4 ) {
            return 'ru';
        }
        return 'et';
    }
    
    // Fall back to WordPress locale
    $locale = get_locale();
    return ( $locale === 'en_US' || $locale === 'en' ) ? 'en' : 'et';
}

function nailedit_get_local_api_base() {
    if ( class_exists( 'Local_API_Shortcode_Plugin' ) ) {
        $base = get_option( Local_API_Shortcode_Plugin::OPTION_KEY, Local_API_Shortcode_Plugin::DEFAULT_BASE );
    } else {
        $base = get_option( 'las_api_base_url', 'http://localhost:8083/api/' );
    }

    return trailingslashit( $base );
}

function nailedit_extract_products( $payload ) {
    if ( isset( $payload['data'] ) && is_array( $payload['data'] ) ) {
        return $payload['data'];
    }

    if ( is_array( $payload ) && array_keys( $payload ) === range( 0, count( $payload ) - 1 ) ) {
        return $payload;
    }

    return array();
}

function nailedit_get_product_page_url( $product_id ) {
    return home_url( '/product/' . absint( $product_id ) . '/' );
}

function nailedit_products_shortcode( $atts ) {
    $atts = shortcode_atts(
        array(
            'endpoint' => 'v1/products?limit=4',
            'columns'  => 4,
            'title'    => '',
            'show_desc' => 'true',
            'lang'     => '',
        ),
        $atts,
        'nailedit_products'
    );
    
    $lang = nailedit_get_shortcode_lang( $atts );
    
    // Use default title from translations if not provided
    if ( empty( $atts['title'] ) ) {
        $atts['title'] = nailedit_translate( 'featured_products', $lang );
    }

    $endpoint = ltrim( $atts['endpoint'], '/' );
    if ( empty( $endpoint ) ) {
        return '';
    }

    $columns = max( 1, min( 4, absint( $atts['columns'] ) ) );

    $cache_key  = 'nailedit_products_' . md5( $endpoint . '_' . $columns . '_' . $lang );
    $cached_html = nailedit_use_cache() ? get_transient( $cache_key ) : false;
    if ( false !== $cached_html ) {
        return $cached_html;
    }

    $response = wp_remote_get(
        nailedit_get_local_api_base() . $endpoint,
        array( 'timeout' => 15 )
    );

    if ( is_wp_error( $response ) ) {
        return '<div class="nailedit-products-error">' . esc_html( $response->get_error_message() ) . '</div>';
    }

    $body    = wp_remote_retrieve_body( $response );
    $decoded = json_decode( $body, true );

    if ( ! is_array( $decoded ) ) {
        return '<div class="nailedit-products-error">' . esc_html( nailedit_translate( 'unexpected_api_response', $lang ) ) . '</div>';
    }

    $products = nailedit_extract_products( $decoded );
    if ( empty( $products ) ) {
        return '<div class="nailedit-products-error">' . esc_html( nailedit_translate( 'no_products_found', $lang ) ) . '</div>';
    }

    $products = array_slice( $products, 0, $columns );

    $output  = '<section class="nailedit-products">';
    if ( ! empty( $atts['title'] ) ) {
        $output .= '<div class="1 font-nailedit text-center text-[35px] text-primary mb-[23px]"><h2>' . esc_html( $atts['title'] ) . '</h2></div>';
    }
    $output .= '<div class="nailedit-products-grid columns-' . (int) $columns . '">';

    foreach ( $products as $product ) {
        $title = $product['name'] ?? ( $product['title'] ?? nailedit_translate( 'unnamed_product', $lang ) );
        $price = $product['min_price'] ?? ( $product['prices']['final']['formatted_price'] ?? '' );
        $description = $product['description'] ?? ( $product['short_description'] ?? '' );
        $description = wp_strip_all_tags( $description );
        $description = ( 'true' === strtolower( $atts['show_desc'] ) ) ? wp_trim_words( $description, 25 ) : '';
        $product_id = $product['id'] ?? 0;

        $image = '';
        if ( ! empty( $product['base_image'] ) && is_array( $product['base_image'] ) ) {
            $image = $product['base_image']['large_image_url'] ?? $product['base_image']['medium_image_url'] ?? '';
        }
        if ( ! $image && ! empty( $product['image_url'] ) ) {
            $image = $product['image_url'];
        }
        if ( ! $image && ! empty( $product['images'] ) && is_array( $product['images'] ) ) {
            $first = $product['images'][0];
            if ( is_array( $first ) ) {
                $image = $first['large_image_url'] ?? $first['medium_image_url'] ?? '';
            } elseif ( is_string( $first ) ) {
                $image = $first;
            }
        }

        $product_url = $product_id ? nailedit_get_product_page_url( $product_id ) : '#';

        $output .= '<article class="bg-white rounded-24">';
        $output .= '<a href="' . esc_url( $product_url ) . '" class="nailedit-product-link">';
        if ( $image ) {
            $output .= '<div class="nailedit-product-thumb"><img src="' . esc_url( nailedit_fix_image_url( $image ) ) . '" alt="' . esc_attr( $title ) . '"></div>';
        }
        $output .= '<div class="nailedit-product-body">';
        $output .= '<h3 class="font-bold text-[20px] text-primary">' . esc_html( $title ) . '</h3>';
        if ( '' !== $price ) {
            $output .= '<p class="nailedit-product-price">' . esc_html( $price ) . '</p>';
        }
        if ( $description ) {
            $output .= '<p class="nailedit-product-desc">' . esc_html( $description ) . '</p>';
        }
        $output .= '</div>';
        $output .= '</a>';
        $output .= '</article>';
    }

    $output .= '</div></section>';

    set_transient( $cache_key, $output, nailedit_get_cache_duration( 'products_shortcode' ) );

    return $output;
}
add_shortcode( 'nailedit_products', 'nailedit_products_shortcode' );

function nailedit_categories_shortcode( $atts ) {
    $atts = shortcode_atts(
        array(
            'endpoint' => 'v1/categories?limit=22',
            'title'    => '',
            'lang'     => '',
        ),
        $atts,
        'kategooriad'
    );
    
    $lang = nailedit_get_shortcode_lang( $atts );
    
    // Use default title from translations if not provided
    if ( empty( $atts['title'] ) ) {
        $atts['title'] = nailedit_translate( 'categories', $lang );
    }

    $endpoint = ltrim( $atts['endpoint'], '/' );
    if ( empty( $endpoint ) ) {
        return '';
    }

    $cache_key   = 'nailedit_kategooriad_' . md5( $endpoint . '_' . $lang );
    $cached_html = nailedit_use_cache() ? get_transient( $cache_key ) : false;
    if ( false !== $cached_html ) {
        return $cached_html;
    }

    $response = wp_remote_get(
        nailedit_get_local_api_base() . $endpoint,
        array( 'timeout' => 15 )
    );

    if ( is_wp_error( $response ) ) {
        return '<div class="nailedit-products-error">' . esc_html( $response->get_error_message() ) . '</div>';
    }

    $body    = wp_remote_retrieve_body( $response );
    $decoded = json_decode( $body, true );

    if ( ! is_array( $decoded ) ) {
        return '<div class="nailedit-products-error">' . esc_html( nailedit_translate( 'unexpected_api_response', $lang ) ) . '</div>';
    }

    $categories = nailedit_extract_products( $decoded );
    if ( empty( $categories ) ) {
        $html = '<div class="nailedit-products-error">' . esc_html( nailedit_translate( 'no_categories_found', $lang ) ) . '</div>';
        set_transient( $cache_key, $html, nailedit_get_cache_duration( 'categories_shortcode' ) );
        return $html;
    }

    $swiper_id = function_exists( 'wp_unique_id' ) ? wp_unique_id( 'nailedit-categories-swiper-' ) : ( 'nailedit-categories-swiper-' . uniqid() );

    $output  = '<section class="nailedit-categories">';
    if ( ! empty( $atts['title'] ) ) {
        $output .= '<div class=""><h2 class="2 font-nailedit text-center text-[35px] text-primary mb-[23px]">' . esc_html( $atts['title'] ) . '</h2></div>';
    }

    $output .= '<div class="swiper nailedit-categories-swiper !overflow-visible" id="' . esc_attr( $swiper_id ) . '">';
    $output .= '<div class="swiper-wrapper  my-[50px]">';

    foreach ( $categories as $category ) {
        $name = isset( $category['name'] ) ? $category['name'] : nailedit_translate( 'unnamed_category', $lang );
        $slug = isset( $category['slug'] ) ? $category['slug'] : '';

        // Skip root category
        if ( strtolower( (string) $slug ) === 'root' ) {
            continue;
        }

       // $output .= '<script>console.log("nailedit_category", ' . wp_json_encode( $category ) . ');</script>';

        $image = '';
        if ( ! empty( $category['logo_url'] ) ) {
            $image = $category['logo_url'];
        } 

        $category_url = $slug ? home_url( '/category/' . sanitize_title( $slug ) . '/' ) : '#';

        $output .= '<div class="swiper-slide !h-auto !flex">';
        $output .= '<article class="bg-white text-center   rounded-24 border border-slate-200  border-[2px] shadow-xl hover:shadow-2xl transition-shadow w-full">';
        $output .= '<a href="' . esc_url( $category_url ) . '" class="block h-full">';
        if ( $image ) {
            $output .= '<div class="aspect-[4/3] overflow-hidden rounded-t-24 bg-white">';
            // Add WebP dimensions to image URL
            $resized_image = add_query_arg(array('width' => 219, 'height' => 165, 'format' => 'webp'), $image);
            $output .= '<img class="w-auto h-full object-contain mx-auto" src="' . esc_url( $image ) . '" alt="' . esc_attr( $name ) . '">';
            $output .= '</div>';
        }
        $output .= '<div class="p-4 flex flex-col gap-2">';
        $output .= '<h3 class="text-base font-bold text-slate-900 line-clamp-2">' . esc_html( $name ) . '</h3>';
        $output .= '</div>';
        $output .= '</a>';
        $output .= '</article>';
        $output .= '</div>';
    }

    $output .= '</div>';
    $output .= '<div class="swiper-button-prev"></div><div class="swiper-button-next"></div><div class="swiper-pagination"></div>';
    $output .= '</div>';
    $output .= '</section>';

    $output .= '<script type="text/javascript">
    window.addEventListener("load", function() {
        if (typeof Swiper === "undefined") {
            return;
        }
        new Swiper("#' . esc_js( $swiper_id ) . '", {
            slidesPerView: 4,
            spaceBetween: 24,
            initialSlide: 2,
            loop: true,
            watchSlidesProgress: true,
            centeredSlides: false,
            slideVisibleClass: "now-visible",
            navigation: {
                nextEl: "#' . esc_js( $swiper_id ) . ' .swiper-button-next",
                prevEl: "#' . esc_js( $swiper_id ) . ' .swiper-button-prev"
            },
            pagination: {
                el: "#' . esc_js( $swiper_id ) . ' .",
                clickable: true
            },
            breakpoints: {
                0: {
                    slidesPerView: 2,
                    spaceBetween: 20
                },
                640: {
                    slidesPerView: 3,
                    spaceBetween: 16
                },
                1024: {
                    slidesPerView: 4,
                    spaceBetween: 24
                }
            }
        });
    });
    </script>';

    set_transient( $cache_key, $output, nailedit_get_cache_duration( 'categories_shortcode' ) );

    return $output;
}
add_shortcode( 'kategooriad', 'nailedit_categories_shortcode' );

/**
 * Categories grid shortcode (without Swiper)
 * Usage: [kategooriad_grid title="Kategooriad"]
 */
function nailedit_categories_grid_shortcode( $atts ) {
    $atts = shortcode_atts(
        array(
            'title' => __( 'Categories', 'nailedit' ),
            'parent_id' => '58',
        ),
        $atts,
        'kategooriad_grid'
    );

    // Cache key per parent category to avoid hitting the API on every page load.
    $cache_key = 'nailedit_kategooriad_grid_' . absint( $atts['parent_id'] );

    // If cached HTML exists, return it immediately.
    $cached_html = nailedit_use_cache() ? get_transient( $cache_key ) : false;
    if ( false !== $cached_html ) {
        return $cached_html;
    }

    // Fetch only 3rd level categories (children of SAGA category ID: 58)
    $endpoint = 'v1/descendant-categories?parent_id=' . absint( $atts['parent_id'] );

    $response = wp_remote_get(
        nailedit_get_local_api_base() . $endpoint,
        array( 'timeout' => 15 )
    );

    if ( is_wp_error( $response ) ) {
        return '<div class="nailedit-products-error">' . esc_html( $response->get_error_message() ) . '</div>';
    }

    $body    = wp_remote_retrieve_body( $response );
    $decoded = json_decode( $body, true );

    if ( ! is_array( $decoded ) ) {
        return '<div class="nailedit-products-error">' . esc_html__( 'Unexpected API response.', 'nailedit' ) . '</div>';
    }

    $categories = nailedit_extract_products( $decoded );
    if ( empty( $categories ) ) {
        return '<div class="nailedit-products-error">' . esc_html__( 'No categories found.', 'nailedit' ) . '</div>';
    }

    $output  = '<section class="nailedit-categories nailedit-categories-grid">';
    if ( ! empty( $atts['title'] ) ) {
        $output .= '<div class=""><h2 class="3 font-nailedit text-center text-[35px] text-primary mb-[23px]">' . esc_html( $atts['title'] ) . '</h2></div>';
    }

    $output .= '<div class="nailedit-products-grid columns-4 gap-[24px]">';

    foreach ( $categories as $category ) {
        $name = isset( $category['name'] ) ? $category['name'] : __( 'Unnamed category', 'nailedit' );
        $slug = isset( $category['slug'] ) ? $category['slug'] : '';

        // Skip root category
        if ( strtolower( (string) $slug ) === 'root' ) {
            continue;
        }

        $image = '';
        if ( ! empty( $category['logo_url'] ) ) {
            $image = $category['logo_url'];
        } elseif ( ! empty( $category['image_url'] ) ) {
            $image = $category['image_url'];
        } elseif ( ! empty( $category['category_icon_path'] ) ) {
            $image = $category['category_icon_path'];
        }

        $category_url = $slug ? home_url( '/category/' . sanitize_title( $slug ) . '/' ) : '#';

        $output .= '<article class="bg-white text-center rounded-24 border border-slate-200 shadow-xl hover:shadow-2xl transition-shadow">';
        $output .= '<a href="' . esc_url( $category_url ) . '" class="block h-full">';
        if ( $image ) {
            $output .= '<div class="aspect-[4/3] overflow-hidden rounded-t-24 bg-slate-50">';
            // Add WebP dimensions to image URL
            
            $output .= '<img class="w-full h-full object-cover asdf" src="' . esc_url($image ) . '" alt="' . esc_attr( $name ) . '">';
            $output .= '</div>';
        }
        $output .= '<div class="p-4 flex flex-col gap-2">';
        $output .= '<h3 class="text-base font-bold text-slate-900 line-clamp-2">' . esc_html( $name ) . '</h3>';
        $output .= '</div>';
        $output .= '</a>';
        $output .= '</article>';
    }

    $output .= '</div>';
    $output .= '</section>';

    // Cache the rendered HTML
    set_transient( $cache_key, $output, nailedit_get_cache_duration( 'products_shortcode' ) );

    return $output;
}
add_shortcode( 'kategooriad_grid', 'nailedit_categories_grid_shortcode' );

/**
 * Products by attribute shortcode with Swiper carousel
 * Usage: [products_by_attribute attribute="brand" value="adidas,nike" title="Brand Products"]
 */
function nailedit_products_by_attribute_shortcode( $atts ) {
    $atts = shortcode_atts(
        array(
            'attribute' => 'brand',
            'value'     => '',
            'title'     => __( 'Products', 'nailedit' ),
            'limit'     => 12,
        ),
        $atts,
        'products_by_attribute'
    );

    $attribute = sanitize_text_field( $atts['attribute'] );
    $value     = sanitize_text_field( $atts['value'] );

    if ( empty( $attribute ) || empty( $value ) ) {
        return '<div class="nailedit-products-error">' . esc_html__( 'Attribute and value are required.', 'nailedit' ) . '</div>';
    }

    $cache_key   = 'nailedit_prodattr_' . md5( $attribute . '_' . $value . '_' . $atts['limit'] );
    $cached_html = nailedit_use_cache() ? get_transient( $cache_key ) : false;
    if ( false !== $cached_html ) {
        return $cached_html;
    }

    // Build API endpoint (without 'api/' prefix since base already includes it)
    $endpoint = 'attribute/' . urlencode( $attribute ) . '/' . urlencode( $value );
    
    // Add limit if specified
    if ( ! empty( $atts['limit'] ) ) {
        $endpoint .= '?limit=' . absint( $atts['limit'] );
    }

    $api_url = rtrim( nailedit_get_local_api_base(), '/' ) . '/' . ltrim( $endpoint, '/' );

    $response = wp_remote_get(
        $api_url,
        array( 'timeout' => 15 )
    );

    if ( is_wp_error( $response ) ) {
        return '<div class="nailedit-products-error">' . esc_html( $response->get_error_message() ) . '</div>';
    }

    $body    = wp_remote_retrieve_body( $response );
    $decoded = json_decode( $body, true );

    // Debug output
    $debug_info = '';
    if ( current_user_can( 'manage_options' ) ) {
        $debug_info = '<pre style="background:#f5f5f5;padding:10px;overflow:auto;">API URL: ' . esc_html( $api_url ) . "\n";
        $debug_info .= 'Response Code: ' . wp_remote_retrieve_response_code( $response ) . "\n";
        $debug_info .= 'Response Body: ' . esc_html( substr( $body, 0, 500 ) ) . '</pre>';
    }

    if ( ! is_array( $decoded ) || ! isset( $decoded['success'] ) || ! $decoded['success'] ) {
        return '<div class="nailedit-products-error">' . esc_html__( 'Failed to fetch products.', 'nailedit' ) . $debug_info . '</div>';
    }

    $products = isset( $decoded['products'] ) ? $decoded['products'] : array();
    
    if ( empty( $products ) ) {
        return '<div class="nailedit-products-error">' . esc_html__( 'No products found.', 'nailedit' ) . '</div>';
    }

    $swiper_id = function_exists( 'wp_unique_id' ) ? wp_unique_id( 'nailedit-products-swiper-' ) : ( 'nailedit-products-swiper-' . uniqid() );

    $output  = '<section class="nailedit-products-by-attribute">';
    if ( ! empty( $atts['title'] ) ) {
        $output .= '<div class="nailedit-products-heading font-nailedit text-center"><h2>' . esc_html( $atts['title'] ) . '</h2></div>';
    }

    $output .= '<div class="swiper nailedit-products-swiper !px-0 !overflow-visible" id="' . esc_attr( $swiper_id ) . '">';
    $output .= '<div class="swiper-wrapper">';

    foreach ( $products as $product ) {
        $title = $product['name'] ?? __( 'Unnamed product', 'nailedit' );
        $price = $product['price'] ?? '';
        $product_id = $product['id'] ?? 0;
        $sku = $product['sku'] ?? '';
        $image = $product['image_url'] ?? '';

        $product_url = $product_id ? nailedit_get_product_page_url( $product_id ) : '#';

        $output .= '<div class="swiper-slide">';
        $output .= '<article class="rounded-24 bg-white">';
        $output .= '<a href="' . esc_url( $product_url ) . '" class="nailedit-product-link asdf">';
        if ( $image ) {
            // Add WebP dimensions to image URL
            $resized_image = add_query_arg(array('width' => 260, 'height' => 220, 'format' => 'webp'), $image);
            $output .= '<div class="nailedit-product-thumb"><img src="' . esc_url( $resized_image ) . '" alt="' . esc_attr( $title ) . '"></div>';
        }
        $output .= '<div class="nailedit-product-body">';
        $output .= '<h3 class="font-bold text-[20px] text-primary">' . esc_html( $title ) . '</h3>';
        if ( '' !== $price ) {
            $output .= '<p class="nailedit-product-price">' . esc_html( $price ) . '</p>';
        }
        if ( $sku ) {
            $output .= '<p class="nailedit-product-sku">' . esc_html__( 'SKU:', 'nailedit' ) . ' ' . esc_html( $sku ) . '</p>';
        }
        $output .= '</div>';
        $output .= '</a>';
        $output .= '</article>';
        $output .= '</div>';
    }

    $output .= '</div>';
    $output .= '<div class="swiper-button-prev"></div><div class="swiper-button-next"></div><div class="swiper-pagination !hidden"></div>';
    $output .= '</div>';
    $output .= '</section>';

    $output .= '<script type="text/javascript">
    (function() {
        function initSwiper' . esc_js( str_replace( '-', '_', $swiper_id ) ) . '() {
            if (typeof Swiper === "undefined") {
                setTimeout(initSwiper' . esc_js( str_replace( '-', '_', $swiper_id ) ) . ', 100);
                return;
            }
            new Swiper("#' . esc_js( $swiper_id ) . '", {
                slidesPerView: 4,
                spaceBetween: 24,
                loop: true,
                watchSlidesProgress: true,
                slideVisibleClass: "now-visible",
                navigation: {
                    nextEl: "#' . esc_js( $swiper_id ) . ' .swiper-button-next",
                    prevEl: "#' . esc_js( $swiper_id ) . ' .swiper-button-prev"
                },
                pagination: {
                    el: "#' . esc_js( $swiper_id ) . ' .swiper-pagination !hidden",
                    clickable: true
                },
                breakpoints: {
                    0: {
                        slidesPerView: 2,
                        spaceBetween: 16
                    },
                    640: {
                         slidesPerView: 3,
                        spaceBetween: 20
                    },
                    1024: {
                          slidesPerView: 4,
                        spaceBetween: 24
                    }
                }
            });
        }
        if (document.readyState === "loading") {
            document.addEventListener("DOMContentLoaded", initSwiper' . esc_js( str_replace( '-', '_', $swiper_id ) ) . ');
        } else {
            initSwiper' . esc_js( str_replace( '-', '_', $swiper_id ) ) . '();
        }
    })();
    </script>';

    set_transient( $cache_key, $output, 10 * MINUTE_IN_SECONDS );

    return $output;
}
add_shortcode( 'products_by_attribute', 'nailedit_products_by_attribute_shortcode' );

/**
 * Popular products shortcode with Swiper carousel
 * Usage: [popular_products limit="20" title="Populaarsed tooted"]
 */
function nailedit_popular_products_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'limit' => 20,
			'title' => '',
			'lang'  => '',
		),
		$atts,
		'popular_products'
	);
	
	$lang = nailedit_get_shortcode_lang( $atts );
	
	// Use default title from translations if not provided
	if ( empty( $atts['title'] ) ) {
		$atts['title'] = nailedit_translate( 'popular_products', $lang );
	}

	$limit = max( 1, absint( $atts['limit'] ) );

	$cache_key   = 'nailedit_popular_' . $limit . '_' . $lang;
	$cached_html = nailedit_use_cache() ? get_transient( $cache_key ) : false;
	if ( false !== $cached_html ) {
		return $cached_html;
	}

	$endpoint = 'products/popular/' . $limit;
	$api_url  = rtrim( nailedit_get_local_api_base(), '/' ) . '/' . ltrim( $endpoint, '/' );

	$response = wp_remote_get(
		$api_url,
		array( 'timeout' => 15 )
	);

	if ( is_wp_error( $response ) ) {
		return '<div class="nailedit-products-error">' . esc_html( $response->get_error_message() ) . '</div>';
	}

	$body    = wp_remote_retrieve_body( $response );
	$decoded = json_decode( $body, true );

	if ( ! is_array( $decoded ) ) {
		return '<div class="nailedit-products-error">' . esc_html__( 'Unexpected API response.', 'nailedit' ) . '</div>';
	}

	$products = nailedit_extract_products( $decoded );
	if ( empty( $products ) ) {
		return '<div class="nailedit-products-error">' . esc_html__( 'No products found.', 'nailedit' ) . '</div>';
	}

	$swiper_id = function_exists( 'wp_unique_id' ) ? wp_unique_id( 'nailedit-popular-products-swiper-' ) : ( 'nailedit-popular-products-swiper-' . uniqid() );

	$output  = '<section class="nailedit-popular-products fullWidth ">';
	if ( ! empty( $atts['title'] ) ) {
		$output .= '<div class=""><h2 class="4 font-nailedit text-center text-[35px] text-primary mb-[23px]">' . esc_html( $atts['title'] ) . '</h2></div>';
	}

	$output .= '<div class="swiper nailedit-products-swiper  !px-0 !overflow-visible" id="' . esc_attr( $swiper_id ) . '">';
	$output .= '<div class="swiper-wrapper">';

	foreach ( $products as $product ) {
		$title      = $product['name'] ?? __( 'Unnamed product', 'nailedit' );
		$price      = $product['price'] ?? ( $product['min_price'] ?? '' );
		$product_id = $product['id'] ?? 0;
		$sku        = $product['sku'] ?? '';
		$url_key    = $product['url_key'] ?? '';
		$image      = '';

		if ( ! empty( $product['base_image'] ) && is_array( $product['base_image'] ) ) {
			$image = $product['base_image']['large_image_url'] ?? $product['base_image']['medium_image_url'] ?? '';
		}
		if ( ! $image && ! empty( $product['image_url'] ) ) {
			$image = $product['image_url'];
		}
		if ( ! $image && ! empty( $product['images'] ) && is_array( $product['images'] ) ) {
			$first = $product['images'][0];
			if ( is_array( $first ) ) {
				$image = $first['large_image_url'] ?? $first['medium_image_url'] ?? '';
			} elseif ( is_string( $first ) ) {
				$image = $first;
			}
		}

		// Prefer pretty URL with url_key (/product/url_key/), fall back to ID if needed
		if ( ! empty( $url_key ) ) {
			$product_url = home_url( '/product/' . rawurlencode( $url_key ) . '/' );
		} elseif ( $product_id ) {
			$product_url = nailedit_get_product_page_url( $product_id );
		} else {
			$product_url = '#';
		}

		$output .= '<div class="swiper-slide !flex my-[40px]">';
		$output .= '<article class="rounded-24 bg-white w-full relative mb-[40px] shadow-xl hover:shadow-2xl transition-shadow border-[2px] border-gray-200">';
		$output .= '<a href="' . esc_url( $product_url ) . '" class="nailedit-product-link ">';
		if ( $image ) {
			$output .= '<div class="nailedit-product-thumb rounded-24 overflow-hidden " style="border-bottom-left-radius: 0px !important;border-bottom-right-radius: 0px !important;"><img src="' . esc_url( nailedit_fix_image_url( $image ) ) . '" alt="' . esc_attr( $title ) . '"></div>';
		}
		$output .= '<div class="p-[15px] pb-[30px] text-center text-[14px] flex flex-wrap flex-col gap-[10px] justify-center">';
		$output .= '<h3 class="font-bold text-[14px] text-primary">' . esc_html( $title ) . '</h3>';
		if ( '' !== $price ) {
			$output .= '<p class="font-bold text-primary">' . esc_html(nailedit_format_price($price)) . '</p>';
		}
		$output .= '<button type="button" class="mt-2 absolute absolute         bottom-[-20px]       left-0 right-0 max-w-[80px] lg:max-w-[130px] mx-auto  inline-flex items-center justify-center rounded-full bg-secondary text-primary font-semibold text-[13px] px-5 py-2 hover:bg-fourth transition">' . esc_html( nailedit_translate( 'buy', $lang ) ) . '</button>';
		$output .= '</div>';
		$output .= '</a>';
		$output .= '</article>';
		$output .= '</div>';
	}

	$output .= '</div>';
	$output .= '<div class="swiper-button-prev"></div><div class="swiper-button-next"></div><div class="swiper-pagination"></div>';
	$output .= '</div>';
	$output .= '</section>';

	$output .= '<script type="text/javascript">
	(function() {
		function initSwiper' . esc_js( str_replace( '-', '_', $swiper_id ) ) . '() {
			if (typeof Swiper === "undefined") {
				setTimeout(initSwiper' . esc_js( str_replace( '-', '_', $swiper_id ) ) . ', 100);
				return;
			}
			new Swiper("#' . esc_js( $swiper_id ) . '", {
				
				spaceBetween: 24,
                slidesPerView: 4,
				loop: true,
                centeredSlides: false,
				watchSlidesProgress: true,
				slideVisibleClass: "now-visible",
                grabCursor: true,
                
				navigation: {
					nextEl: "#' . esc_js( $swiper_id ) . ' .swiper-button-next",
					prevEl: "#' . esc_js( $swiper_id ) . ' .swiper-button-prev"
				},
				pagination: {
					el: "#' . esc_js( $swiper_id ) . ' .swiper-pagination !hidden",
					clickable: true
				},
				breakpoints: {
					0: {
						 slidesPerView: 2,
						spaceBetween: 8
					},
					640: {
						 slidesPerView: 3,
						spaceBetween: 20
					},
					1024: {
						 slidesPerView: 4,
						spaceBetween: 24
					}
				}
			});
		}
		if (document.readyState === "loading") {
			document.addEventListener("DOMContentLoaded", initSwiper' . esc_js( str_replace( '-', '_', $swiper_id ) ) . ');
		} else {
			initSwiper' . esc_js( str_replace( '-', '_', $swiper_id ) ) . '();
		}
	})();
	</script>';

	set_transient( $cache_key, $output, nailedit_get_cache_duration( 'popular_products_shortcode' ) );

	return $output;
}
add_shortcode( 'popular_products', 'nailedit_popular_products_shortcode' );

/**
 * [featured_products ids="1,2,3" title="Valitud tooted"]
 * Displays hand-picked products by Bagisto product IDs.
 */
function nailedit_featured_products_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'ids'   => '',
			'title' => '',
			'lang'  => '',
		),
		$atts,
		'featured_products'
	);
	
	$lang = nailedit_get_shortcode_lang( $atts );
	
	// Use default title from translations if not provided
	if ( empty( $atts['title'] ) ) {
		$atts['title'] = nailedit_translate( 'featured_products', $lang );
	}
    

	$raw_ids = array_filter( array_map( 'absint', explode( ',', $atts['ids'] ) ) );
	if ( empty( $raw_ids ) ) {
		return '<div class="nailedit-products-error">' . esc_html( nailedit_translate( 'no_products_found', $lang ) ) . '</div>';
	}

	$cache_key   = 'nailedit_featured_' . md5( implode( ',', $raw_ids ) . '_' . $lang );
	$cached_html = nailedit_use_cache() ? get_transient( $cache_key ) : false;
	if ( false !== $cached_html ) {
		return $cached_html;
	}

	$base = rtrim( nailedit_get_local_api_base(), '/' );
	$products = array();

	foreach ( $raw_ids as $pid ) {
		$api_url  = $base . '/v1/products/' . $pid;
		$response = wp_remote_get( $api_url, array( 'timeout' => 15 ) );

		if ( is_wp_error( $response ) ) {
			continue;
		}

		$body    = wp_remote_retrieve_body( $response );
		$decoded = json_decode( $body, true );

		if ( ! is_array( $decoded ) ) {
			continue;
		}

		$product = null;
		if ( isset( $decoded['id'] ) ) {
			$product = $decoded;
		} elseif ( isset( $decoded['data'] ) && is_array( $decoded['data'] ) ) {
			if ( isset( $decoded['data']['id'] ) ) {
				$product = $decoded['data'];
			} elseif ( ! empty( $decoded['data'][0] ) ) {
				$product = $decoded['data'][0];
			}
		}

		if ( $product && ! empty( $product['id'] ) ) {
			$products[] = $product;
		}
	}

	if ( empty( $products ) ) {
		return '<div class="nailedit-products-error">' . esc_html__( 'No products found.', 'nailedit' ) . '</div>';
	}

	$swiper_id = function_exists( 'wp_unique_id' ) ? wp_unique_id( 'nailedit-featured-products-swiper-' ) : ( 'nailedit-featured-products-swiper-' . uniqid() );    

	$output  = '<section class="nailedit-popular-products gradient-dark  fullWidth mb-16   backdrop-blur  "> <div class="max-w-[1200px] px-[20px] lg:px-0 mx-auto">';

	if ( ! empty( $atts['title'] ) ) {
		$output .= '<div class=""><h2 class="5 pt-12 font-nailedit text-center text-[35px]  text-secondary lg:mb-[23px]">' . esc_html( $atts['title'] ) . '</h2></div>';
	}

	$output .= '<div class="swiper nailedit-products-swiper !px-0 !overflow-visible" id="' . esc_attr( $swiper_id ) . '">';
	$output .= '<div class="swiper-wrapper">';

	foreach ( $products as $product ) {
		$title      = $product['name'] ?? __( 'Unnamed product', 'nailedit' );
		$price      = $product['price'] ?? ( $product['min_price'] ?? '' );
		$product_id = $product['id'] ?? 0;
		$url_key    = $product['url_key'] ?? '';
		$image      = '';

		if ( ! empty( $product['base_image'] ) && is_array( $product['base_image'] ) ) {
			$image = $product['base_image']['large_image_url'] ?? $product['base_image']['medium_image_url'] ?? '';
		}
		if ( ! $image && ! empty( $product['image_url'] ) ) {
			$image = $product['image_url'];
		}
		if ( ! $image && ! empty( $product['images'] ) && is_array( $product['images'] ) ) {
			$first = $product['images'][0];
			if ( is_array( $first ) ) {
				$image = $first['large_image_url'] ?? $first['medium_image_url'] ?? '';
			} elseif ( is_string( $first ) ) {
				$image = $first;
			}
		}

		if ( ! empty( $url_key ) ) {
			$product_url = home_url( '/product/' . rawurlencode( $url_key ) . '/' );
		} elseif ( $product_id ) {
			$product_url = nailedit_get_product_page_url( $product_id );
		} else {
			$product_url = '#';
		}

		$output .= '<div class="swiper-slide !flex my-[40px]">';
		$output .= '<article class=" lg:rounded-24 lg:bg-white w-full relative lg:mb-[40px]   lg:shadow-xl lg:hover:shadow-2xl lg:transition-shadow lg:border-[2px] lg:border-gray-200">';
		$output .= '<a href="' . esc_url( $product_url ) . '" class="nailedit-product-link ">';
		if ( $image ) {
			$output .= '<div class="nailedit-product-thumb rounded-24 bg-white pb-6 lg:pb-0 overflow-hidden "  "><img src="' . esc_url( nailedit_fix_image_url( $image ) ) . '" alt="' . esc_attr( $title ) . '"></div>';
		}
		$output .= '<div class="p-[15px] pb-0 lg:pb-[15px]  text-center text-[14px] flex flex-wrap flex-col gap-[10px] justify-center">';
		$output .= '<h3 class="font-bold text-[14px] text-white lg:text-primary">' . esc_html( $title ) . '</h3>';
		if ( '' !== $price ) {
			$output .= '<p class="font-bold text-white lg:text-primary">' . esc_html( nailedit_format_price( $price ) ) . '</p>';
		}
		$output .= '<button type="button" class="mt-2         sm:max-w-[130px]             max-w-[80px] lg:min-w-[130px]  mx-auto  inline-flex items-center justify-center rounded-full  gradient-secondary text-primary font-semibold text-[13px] px-5 py-2 hover:bg-fourth transition">' . esc_html( nailedit_translate( 'buy', $lang ) ) . '</button>';
		$output .= '</div>';
		$output .= '</a>';
		$output .= '</article>';
		$output .= '</div>';
	}

	$output .= '</div>';
	$output .= '<div class="swiper-button-prev !hidden lg:!flex"></div><div class="swiper-button-next !hidden lg:!flex"></div><div class="swiper-pagination lg:!hidden"></div>';
	$output .= '</div></div>';
	$output .= '</section>';

	$output .= '<script type="text/javascript">
	(function() {
		function initSwiper' . esc_js( str_replace( '-', '_', $swiper_id ) ) . '() {
			if (typeof Swiper === "undefined") {
				setTimeout(initSwiper' . esc_js( str_replace( '-', '_', $swiper_id ) ) . ', 100);
				return;
			}
			new Swiper("#' . esc_js( $swiper_id ) . '", {
				spaceBetween: 8,
                slidesPerView: 4,
				initialSlide: 2,
				loop: true,
				watchSlidesProgress: true,
				slideVisibleClass: "now-visible",
                grabCursor: true,
				navigation: {
					nextEl: "#' . esc_js( $swiper_id ) . ' .swiper-button-next",
					prevEl: "#' . esc_js( $swiper_id ) . ' .swiper-button-prev"
				},
				pagination: {
					el: "#' . esc_js( $swiper_id ) . ' .swiper-pagination",
					clickable: true
				},
				breakpoints: {
					0: {
						 slidesPerView: 2,
						spaceBetween: 20
					},
					640: {
						 slidesPerView: 3,
						spaceBetween: 20
					},
					1024: {
						 slidesPerView: 4,
						spaceBetween: 24
					}
				}
			});
		}
		if (document.readyState === "loading") {
			document.addEventListener("DOMContentLoaded", initSwiper' . esc_js( str_replace( '-', '_', $swiper_id ) ) . ');
		} else {
			initSwiper' . esc_js( str_replace( '-', '_', $swiper_id ) ) . '();
		}
	})();
	</script>';

	set_transient( $cache_key, $output, nailedit_get_cache_duration( 'featured_products_shortcode' ) );

	return $output;
}
add_shortcode( 'featured_products', 'nailedit_featured_products_shortcode' );

function nailedit_contact_form_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'subject' => nailedit_get_t( 'contact_form_subject' ),
			'title'   => '',
		),
		$atts,
		'contact_form'
	);

	$errors  = array();
	$success = false;
	$name    = '';
	$email   = '';
	$message = '';

	if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['nailedit_contact_form_nonce'] ) ) {
		if ( ! wp_verify_nonce( wp_unslash( $_POST['nailedit_contact_form_nonce'] ), 'nailedit_contact_form' ) ) {
			$errors[] = nailedit_get_t( 'form_security_failed' );
		} else {
			$name    = isset( $_POST['nailedit_name'] ) ? sanitize_text_field( wp_unslash( $_POST['nailedit_name'] ) ) : '';
			$email   = isset( $_POST['nailedit_email'] ) ? sanitize_email( wp_unslash( $_POST['nailedit_email'] ) ) : '';
			$message = isset( $_POST['nailedit_message'] ) ? wp_kses_post( wp_unslash( $_POST['nailedit_message'] ) ) : '';

			if ( '' === $name ) {
				$errors[] = nailedit_get_t( 'please_enter_name' );
			}
			if ( ! is_email( $email ) ) {
				$errors[] = nailedit_get_t( 'please_enter_valid_email' );
			}
			if ( '' === trim( $message ) ) {
				$errors[] = nailedit_get_t( 'please_enter_message' );
			}

            
			if ( empty( $errors ) ) {
				$to      = 'pood@nailedit.ee';
				$subject = $atts['subject'];
				$headers = array( 'Reply-To: ' . $name . ' <' . $email . '>' );
				$body    = sprintf(
					"%s: %s\n%s: %s\n\n%s:\n%s",
					nailedit_get_t( 'name' ),
					$name,
					nailedit_get_t( 'email' ),
					$email,
					nailedit_get_t( 'message' ),
					$message
				);

				$sent = wp_mail( $to, $subject, $body, $headers );

				if ( $sent ) {
					$success = true;
					$name    = '';
					$email   = '';
					$message = '';
				} else {
					$errors[] = nailedit_get_t( 'message_send_failed' );
				}
			}
		}
	}

	ob_start();
	?>
	<div class="nailedit-contact-form-wrapper py-10 flex justify-center">
		<div class="w-full max-w-4xl bg-white rounded-24 shadow-xl px-6 py-8 md:px-12 md:py-10 relative overflow-hidden">
			<?php if ( ! empty( $atts['title'] ) ) : ?>
				<h2 class="font-nailedit text-center text-[26px] md:text-[32px] text-primary mb-6">
					<?php echo esc_html( $atts['title'] ); ?>
				</h2>
			<?php endif; ?>
			<?php if ( $success ) : ?>
				<div class="mb-4 rounded-md bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3">
					<?php nailedit_t( 'message_sent_success' ); ?>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $errors ) ) : ?>
				<ul class="mb-4 rounded-md bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 space-y-1">
					<?php foreach ( $errors as $error ) : ?>
						<li><?php echo esc_html( $error ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

			<form class="nailedit-contact-form space-y-6" method="post">
				<?php wp_nonce_field( 'nailedit_contact_form', 'nailedit_contact_form_nonce' ); ?>

				<div class="nailedit-contact-form-row grid grid-cols-1 md:grid-cols-2 gap-4">
					<input
						type="text"
						name="nailedit_name"
						placeholder="<?php echo esc_attr( nailedit_get_t( 'name' ) ); ?>"
						value="<?php echo esc_attr( $name ); ?>"
						required
						class="w-full rounded-full border border-slate-200 bg-slate-50 px-5 py-3 text-sm md:text-base outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition placeholder:text-slate-400"
					>
					<input
						type="email"
						name="nailedit_email"
						placeholder="<?php echo esc_attr( nailedit_get_t( 'email' ) ); ?>"
						value="<?php echo esc_attr( $email ); ?>"
						required
						class="w-full rounded-full border border-slate-200 bg-slate-50 px-5 py-3 text-sm md:text-base outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition placeholder:text-slate-400"
					>
				</div>

				<div class="nailedit-contact-form-row">
					<textarea
						name="nailedit_message"
						placeholder="<?php echo esc_attr( nailedit_get_t( 'message' ) ); ?>"
						required
						rows="5"
						class="w-full rounded-3xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm md:text-base outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition placeholder:text-slate-400 resize-none"
					><?php echo esc_textarea( $message ); ?></textarea>
				</div>

				<div class="nailedit-contact-form-actions flex justify-center mt-4">
					<button
						type="submit"
						class="inline-flex items-center justify-center rounded-full gradient-secondary px-10 py-3 text-sm md:text-base font-semibold text-primary shadow-md hover:bg-fourth hover:text-white transition"
					>
						<?php nailedit_t( 'send' ); ?>
					</button>
				</div>
			</form>
		</div>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'contact_form', 'nailedit_contact_form_shortcode' );

/**
 * Simple category list shortcode for mobile menu
 * Usage: [category_list parent_id="58"]
 */
function nailedit_category_list_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'parent_id' => '58', // Default to SAGA category
			'show_all_link' => 'false',
		),
		$atts,
		'category_list'
	);

	$cache_key = 'nailedit_category_list_' . absint( $atts['parent_id'] );
	$cached_html = nailedit_use_cache() ? get_transient( $cache_key ) : false;
	if ( false !== $cached_html ) {
		return $cached_html;
	}

	$endpoint = 'v1/descendant-categories?parent_id=' . absint( $atts['parent_id'] );
	$response = wp_remote_get(
		nailedit_get_local_api_base() . $endpoint,
		array( 'timeout' => 15 )
	);

	if ( is_wp_error( $response ) ) {
		return '';
	}

	$body = wp_remote_retrieve_body( $response );
	$decoded = json_decode( $body, true );

	if ( ! is_array( $decoded ) || empty( $decoded['data'] ) ) {
		return '';
	}

	$categories = $decoded['data'];
	if ( empty( $categories ) ) {
		return '';
	}

	$output = '<ul class="nailedit-category-list flex flex-col gap-3">';
	
	// Add "All Products" link if enabled
	if ( 'true' === strtolower( $atts['show_all_link'] ) ) {
		$all_products_url = nailedit_get_url( 'products' );
		$all_products_text = nailedit_get_t( 'all_products' );
		$output .= '<li><a href="' . esc_url( $all_products_url ) . '" class="text-[15px] uppercase tracking-[0.08em] text-white hover:text-secondary transition">' . esc_html( $all_products_text ) . '</a></li>';
	}

	foreach ( $categories as $category ) {
		$name = $category['name'] ?? '';
		$slug = $category['slug'] ?? '';
		
		if ( empty( $name ) || empty( $slug ) ) {
			continue;
		}

		// Build category URL
		$category_url = home_url( '/category/' . $slug . '/' );
		
		$output .= '<li>';
		$output .= '<a href="' . esc_url( $category_url ) . '" class="text-[15px] uppercase tracking-[0.08em] text-white hover:text-secondary py-2 transition">';
		$output .= esc_html( $name );
		$output .= '</a>';
		$output .= '</li>';
	}

	$output .= '</ul>';

	set_transient( $cache_key, $output, nailedit_get_cache_duration( 'categories_shortcode' ) );

	return $output;
}
add_shortcode( 'category_list', 'nailedit_category_list_shortcode' );
