<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
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
            'title'    => __( 'Featured Products', 'nailedit' ),
            'show_desc' => 'true',
        ),
        $atts,
        'nailedit_products'
    );

    $endpoint = ltrim( $atts['endpoint'], '/' );
    if ( empty( $endpoint ) ) {
        return '';
    }

    $columns = max( 1, min( 4, absint( $atts['columns'] ) ) );

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

    $products = nailedit_extract_products( $decoded );
    if ( empty( $products ) ) {
        return '<div class="nailedit-products-error">' . esc_html__( 'No products found.', 'nailedit' ) . '</div>';
    }

    $products = array_slice( $products, 0, $columns );

    $output  = '<section class="nailedit-products">';
    if ( ! empty( $atts['title'] ) ) {
        $output .= '<div class="font-nailedit text-center text-[35px] text-primary mb-[23px]"><h2>' . esc_html( $atts['title'] ) . '</h2></div>';
    }
    $output .= '<div class="nailedit-products-grid columns-' . (int) $columns . '">';

    foreach ( $products as $product ) {
        $title = $product['name'] ?? ( $product['title'] ?? __( 'Unnamed product', 'nailedit' ) );
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

    return $output;
}
add_shortcode( 'nailedit_products', 'nailedit_products_shortcode' );

function nailedit_categories_shortcode( $atts ) {
    $atts = shortcode_atts(
        array(
            'endpoint' => 'v1/categories?limit=22',
            'title'    => __( 'Categories', 'nailedit' ),
        ),
        $atts,
        'kategooriad'
    );

    $endpoint = ltrim( $atts['endpoint'], '/' );
    if ( empty( $endpoint ) ) {
        return '';
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
        return '<div class="nailedit-products-error">' . esc_html__( 'Unexpected API response.', 'nailedit' ) . '</div>';
    }

    $categories = nailedit_extract_products( $decoded );
    if ( empty( $categories ) ) {
        $html = '<div class="nailedit-products-error">' . esc_html__( 'No categories found.', 'nailedit' ) . '</div>';
        // Cache the empty state as well to avoid spamming the API.
        set_transient( $cache_key, $html, 10 * MINUTE_IN_SECONDS );
        return $html;
    }

    $swiper_id = function_exists( 'wp_unique_id' ) ? wp_unique_id( 'nailedit-categories-swiper-' ) : ( 'nailedit-categories-swiper-' . uniqid() );

    $output  = '<section class="nailedit-categories">';
    if ( ! empty( $atts['title'] ) ) {
        $output .= '<div class=""><h2 class="font-nailedit text-center text-[35px] text-primary mb-[23px]">' . esc_html( $atts['title'] ) . '</h2></div>';
    }

    $output .= '<div class="swiper nailedit-categories-swiper !overflow-visible" id="' . esc_attr( $swiper_id ) . '">';
    $output .= '<div class="swiper-wrapper  my-[50px]">';

    foreach ( $categories as $category ) {
        $name = isset( $category['name'] ) ? $category['name'] : __( 'Unnamed category', 'nailedit' );
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
        $output .= '<article class="bg-white text-center   rounded-24 border border-slate-200 shadow-xl hover:shadow-2xl transition-shadow w-full">';
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

    $output .= '<script type="text/javascript">window.addEventListener("load",function(){if(typeof Swiper==="undefined"){return;}new Swiper("#' . esc_js( $swiper_id ) . '",{slidesPerView:4,spaceBetween:24,loop:false,watchSlidesProgress:true,slideVisibleClass:"now-visible",navigation:{nextEl:"#' . esc_js( $swiper_id ) . ' .swiper-button-next",prevEl:"#' . esc_js( $swiper_id ) . ' .swiper-button-prev"},pagination:{el:"#' . esc_js( $swiper_id ) . ' .",clickable:true},breakpoints:{0:{slidesPerView:1},640:{slidesPerView:2},1024:{slidesPerView:4}}});});</script>';

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
    $cached_html = get_transient( $cache_key );
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
        $output .= '<div class=""><h2 class="font-nailedit text-center text-[35px] text-primary mb-[23px]">' . esc_html( $atts['title'] ) . '</h2></div>';
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

    // Cache the rendered HTML for 10 minutes.
    set_transient( $cache_key, $output, 10 * MINUTE_IN_SECONDS );

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

    $output .= '<div class="swiper nailedit-products-swiper !overflow-visible" id="' . esc_attr( $swiper_id ) . '">';
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
                loop: false,
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
                        slidesPerView: 1,
                        spaceBetween: 16
                    },
                    640: {
                        slidesPerView: 2,
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
			'title' => __( 'Populaarsed tooted', 'nailedit' ),
		),
		$atts,
		'popular_products'
	);

	$limit = max( 1, absint( $atts['limit'] ) );

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

	$output  = '<section class="nailedit-popular-products">';
	if ( ! empty( $atts['title'] ) ) {
		$output .= '<div class=""><h2 class="font-nailedit text-center text-[35px] text-primary mb-[23px]">' . esc_html( $atts['title'] ) . '</h2></div>';
	}

	$output .= '<div class="swiper nailedit-products-swiper !overflow-visible" id="' . esc_attr( $swiper_id ) . '">';
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
		$output .= '<article class="rounded-24 bg-white w-full relative mb-[40px] shadow-xl hover:shadow-2xl transition-shadow">';
		$output .= '<a href="' . esc_url( $product_url ) . '" class="nailedit-product-link ">';
		if ( $image ) {
			$output .= '<div class="nailedit-product-thumb rounded-24 overflow-hidden " style="border-bottom-left-radius: 0px !important;border-bottom-right-radius: 0px !important;"><img src="' . esc_url( nailedit_fix_image_url( $image ) ) . '" alt="' . esc_attr( $title ) . '"></div>';
		}
		$output .= '<div class="p-[15px] pb-[30px] text-center text-[14px] flex flex-wrap flex-col gap-[10px] justify-center">';
		$output .= '<h3 class="font-bold text-[14px] text-primary">' . esc_html( $title ) . '</h3>';
		if ( '' !== $price ) {
			$output .= '<p class="font-bold text-secondary">' . esc_html( $price ) . '</p>';
		}
		$output .= '<button type="button" class="mt-2 absolute absolute         bottom-[-20px]       left-0 right-0 max-w-[130px] mx-auto  inline-flex items-center justify-center rounded-full bg-secondary text-primary font-semibold text-[13px] px-5 py-2 hover:bg-fourth transition">' . esc_html__( 'Osta', 'nailedit' ) . '</button>';
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
				slidesPerView: 4,
				spaceBetween: 24,
				loop: false,
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
						slidesPerView: 1,
						spaceBetween: 16
					},
					640: {
						slidesPerView: 2,
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

	return $output;
}
add_shortcode( 'popular_products', 'nailedit_popular_products_shortcode' );

function nailedit_contact_form_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'subject' => __( 'Uus kontaktivormi sõnum', 'nailedit' ),
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
			$errors[] = __( 'Vormi turvakontroll ebaõnnestus. Palun proovi uuesti.', 'nailedit' );
		} else {
			$name    = isset( $_POST['nailedit_name'] ) ? sanitize_text_field( wp_unslash( $_POST['nailedit_name'] ) ) : '';
			$email   = isset( $_POST['nailedit_email'] ) ? sanitize_email( wp_unslash( $_POST['nailedit_email'] ) ) : '';
			$message = isset( $_POST['nailedit_message'] ) ? wp_kses_post( wp_unslash( $_POST['nailedit_message'] ) ) : '';

			if ( '' === $name ) {
				$errors[] = __( 'Palun sisesta nimi.', 'nailedit' );
			}
			if ( ! is_email( $email ) ) {
				$errors[] = __( 'Palun sisesta kehtiv e-posti aadress.', 'nailedit' );
			}
			if ( '' === trim( $message ) ) {
				$errors[] = __( 'Palun sisesta sõnum.', 'nailedit' );
			}

			if ( empty( $errors ) ) {
				$to      = get_option( 'admin_email' );
				$subject = $atts['subject'];
				$headers = array( 'Reply-To: ' . $name . ' <' . $email . '>' );
				$body    = sprintf(
					"%s: %s\n%s: %s\n\n%s:\n%s",
					__( 'Nimi', 'nailedit' ),
					$name,
					__( 'E-post', 'nailedit' ),
					$email,
					__( 'Sõnum', 'nailedit' ),
					$message
				);

				$sent = wp_mail( $to, $subject, $body, $headers );

				if ( $sent ) {
					$success = true;
					$name    = '';
					$email   = '';
					$message = '';
				} else {
					$errors[] = __( 'Sõnumi saatmine ebaõnnestus. Palun proovi hiljem uuesti.', 'nailedit' );
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
					<?php echo esc_html__( 'Aitäh! Sõnum on saadetud.', 'nailedit' ); ?>
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
						placeholder="<?php echo esc_attr__( 'Nimi', 'nailedit' ); ?>"
						value="<?php echo esc_attr( $name ); ?>"
						required
						class="w-full rounded-full border border-slate-200 bg-slate-50 px-5 py-3 text-sm md:text-base outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition placeholder:text-slate-400"
					>
					<input
						type="email"
						name="nailedit_email"
						placeholder="<?php echo esc_attr__( 'E-post', 'nailedit' ); ?>"
						value="<?php echo esc_attr( $email ); ?>"
						required
						class="w-full rounded-full border border-slate-200 bg-slate-50 px-5 py-3 text-sm md:text-base outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition placeholder:text-slate-400"
					>
				</div>

				<div class="nailedit-contact-form-row">
					<textarea
						name="nailedit_message"
						placeholder="<?php echo esc_attr__( 'Sõnum', 'nailedit' ); ?>"
						required
						rows="5"
						class="w-full rounded-3xl border border-slate-200 bg-slate-50 px-5 py-4 text-sm md:text-base outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition placeholder:text-slate-400 resize-none"
					><?php echo esc_textarea( $message ); ?></textarea>
				</div>

				<div class="nailedit-contact-form-actions flex justify-center mt-4">
					<button
						type="submit"
						class="inline-flex items-center justify-center rounded-full bg-secondary px-10 py-3 text-sm md:text-base font-semibold text-primary shadow-md hover:bg-fourth hover:text-white transition"
					>
						<?php echo esc_html__( 'Saada', 'nailedit' ); ?>
					</button>
				</div>
			</form>
		</div>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'contact_form', 'nailedit_contact_form_shortcode' );
