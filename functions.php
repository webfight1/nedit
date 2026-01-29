<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function nailedit_setup() {
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support(
        'custom-logo',
        array(
            'height'      => 250,
            'width'       => 250,
            'flex-height' => true,
            'flex-width'  => true,
        )
    );
    add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );

    register_nav_menus(
        array(
            'primary'   => __( 'Primary Menu', 'nailedit' ),
            'user_menu' => __( 'User Menu', 'nailedit' ),
        )
    );
}
add_action( 'after_setup_theme', 'nailedit_setup' );

function nailedit_customize_register( $wp_customize ) {
    $wp_customize->add_section(
        'nailedit_header_section',
        array(
            'title'    => __( 'Peise seaded', 'nailedit' ),
            'priority' => 30,
        )
    );

    $wp_customize->add_setting(
        'nailedit_global_header_image',
        array(
            'default'           => '',
            'sanitize_callback' => 'esc_url_raw',
        )
    );

    $wp_customize->add_control(
        new WP_Customize_Image_Control(
            $wp_customize,
            'nailedit_global_header_image_control',
            array(
                'label'   => __( 'Uldine lehe pise pilt', 'nailedit' ),
                'section' => 'nailedit_header_section',
                'settings'=> 'nailedit_global_header_image',
            )
        )
    );
}
add_action( 'customize_register', 'nailedit_customize_register' );

function nailedit_assets() {
    // Google Fonts: Montserrat as primary font
    wp_enqueue_style(
        'nailedit-google-fonts',
        'https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap',
        array(),
        null
    );

    wp_enqueue_style( 'nailedit-style', get_stylesheet_uri(), array( 'nailedit-google-fonts' ), wp_get_theme()->get( 'Version' ) );

    // Tailwind compiled CSS (from assets/css/tailwind.build.css)
    $tailwind_path = get_template_directory() . '/assets/css/tailwind.build.css';
    if ( file_exists( $tailwind_path ) ) {
        wp_enqueue_style(
            'nailedit-tailwind',
            get_template_directory_uri() . '/assets/css/tailwind.build.css',
            array( 'nailedit-style' ),
            filemtime( $tailwind_path )
        );
    }
    

    // SCSS compiled CSS (from assets/css/theme.css)
    $theme_css_path = get_template_directory() . '/assets/css/theme.css';
    if ( file_exists( $theme_css_path ) ) {
        wp_enqueue_style(
            'nailedit-theme',
            get_template_directory_uri() . '/assets/css/theme.css',
            array( 'nailedit-tailwind' ),
            filemtime( $theme_css_path )
        );
    }
    
    // Enqueue Swiper.js CSS and JS
    wp_enqueue_style( 'swiper-css', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css', array(), '11.0.0' );
    wp_enqueue_script( 'swiper-js', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', array(), '11.0.0', true );

    // Shared customer/login JS
    $customer_js_path = get_template_directory() . '/assets/js/nailedit-customer.js';
    if ( file_exists( $customer_js_path ) ) {
        wp_enqueue_script(
            'nailedit-customer',
            get_template_directory_uri() . '/assets/js/nailedit-customer.js',
            array(),
            filemtime( $customer_js_path ),
            true
        );

        wp_localize_script(
            'nailedit-customer',
            'NaileditSettings',
            array(
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            )
        );
    }
}
add_action( 'wp_enqueue_scripts', 'nailedit_assets' );

// Custom rewrite rules for /product/{id}/
function nailedit_rewrite_rules() {
    add_rewrite_rule( '^product/([0-9]+)/?$', 'index.php?product_id=$matches[1]', 'top' );
    add_rewrite_rule( '^product/([^/]+)/?$', 'index.php?product_sku=$matches[1]', 'top' );
    add_rewrite_rule( '^category/([^/]+)/?$', 'index.php?bagisto_category_slug=$matches[1]', 'top' );
}
add_action( 'init', 'nailedit_rewrite_rules' );

// Flush rewrite rules on theme activation
function nailedit_flush_rewrite_rules() {
    nailedit_rewrite_rules();
    flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'nailedit_flush_rewrite_rules' );

// Temporary: Force flush rewrite rules once
if ( ! get_option( 'nailedit_rewrite_flushed_v2' ) ) {
    flush_rewrite_rules();
    update_option( 'nailedit_rewrite_flushed_v2', true );
}

function nailedit_query_vars( $vars ) {
    $vars[] = 'product_id';
    $vars[] = 'product_sku';
    $vars[] = 'bagisto_category_slug';
    return $vars;
}
add_filter( 'query_vars', 'nailedit_query_vars' );

function nailedit_template_redirect() {
    $product_id    = get_query_var( 'product_id' );
    $product_sku   = get_query_var( 'product_sku' );
    $category_slug = get_query_var( 'bagisto_category_slug' );

    // DEBUG: Always output to see if this function runs
    echo '<!-- NAILEDIT TEMPLATE REDIRECT CALLED -->';
    echo '<!-- product_id: ' . var_export($product_id, true) . ' -->';
    echo '<!-- product_sku: ' . var_export($product_sku, true) . ' -->';
    echo '<!-- category_slug: ' . var_export($category_slug, true) . ' -->';
    
    if ( $product_id || $product_sku ) {
        add_filter( 'body_class', function( $classes ) {
            $classes[] = 'nailedit-template-single-product';
            return $classes;
        });
        echo '<!-- NAILEDIT: Loading single-product.php -->';
        include get_template_directory() . '/single-product.php';
        exit;
    }

    if ( $category_slug ) {
        error_log( 'NAILEDIT: Loading category-products.php for slug: ' . $category_slug );
        add_filter( 'body_class', function( $classes ) use ( $category_slug ) {
            $classes[] = 'nailedit-template-category-products';
            $classes[] = 'nailedit-category-' . sanitize_html_class( $category_slug );
            return $classes;
        });
        echo '<!-- NAILEDIT: Loading category-products.php for slug: ' . esc_html($category_slug) . ' -->';
        include get_template_directory() . '/category-products.php';
        exit;
    }
    
    error_log( 'NAILEDIT: No custom template matched, using default WordPress template' );
}
add_action( 'template_redirect', 'nailedit_template_redirect' );

// AJAX handler for cart operations (proxy to avoid CORS)
function nailedit_add_to_cart() {
    // Get API base URL (re-use the same helper/fallback as templates)
    if (function_exists('nailedit_get_local_api_base')) {
        $base = nailedit_get_local_api_base();
    } else {
        $base = trailingslashit(get_option('las_api_base_url', 'http://localhost:8083/api/'));
    }
    
    // Get request data
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    
    if (!$product_id) {
        wp_send_json_error(['message' => 'Product ID is required'], 400);
    }
    
    // Get stored cookie from client
    $stored_cookie = isset($_POST['stored_cookie']) ? sanitize_text_field($_POST['stored_cookie']) : '';
    $auth_token   = isset($_POST['auth_token']) ? sanitize_text_field($_POST['auth_token']) : '';
    
    // Prepare request (Bagisto v1 API - new cart add endpoint)
    $url = rtrim($base, '/') . '/v1/customer/cart/add/' . $product_id;
    $args = [
        'method' => 'POST',
        'timeout' => 15,
        'headers' => [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ],
        'body' => json_encode([
            'product_id' => $product_id,
            'quantity'   => $quantity,
            'is_buy_now' => 0,
        ])
    ];
    
    // Add cookie if provided
    if ($stored_cookie) {
        $args['headers']['Cookie'] = $stored_cookie;
    }

    if ($auth_token) {
        $args['headers']['Authorization'] = 'Bearer ' . $auth_token;
    }
    
    // Make request
    $response = wp_remote_post($url, $args);
    
    if (is_wp_error($response)) {
        wp_send_json_error(['message' => $response->get_error_message()], 500);
    }
    
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    $status_code = wp_remote_retrieve_response_code($response);
    
    // Extract cookies from response
    $cookies = wp_remote_retrieve_header($response, 'set-cookie');
    
    // Send response with cookies
    wp_send_json([
        'success' => $status_code >= 200 && $status_code < 300,
        'data' => $data,
        'cookies' => $cookies,
        'status' => $status_code
    ], $status_code);
}
add_action('wp_ajax_nailedit_add_to_cart', 'nailedit_add_to_cart');
add_action('wp_ajax_nopriv_nailedit_add_to_cart', 'nailedit_add_to_cart');

function nailedit_get_cart() {
    if ( function_exists( 'nailedit_get_local_api_base' ) ) {
        $base = nailedit_get_local_api_base();
    } else {
        $base = trailingslashit( get_option( 'las_api_base_url', 'http://localhost:8083/api/' ) );
    }

    $url = rtrim( $base, '/' ) . '/v1/customer/cart';

    $headers = array(
        'Accept' => 'application/json',
    );

    $stored_cookie = isset( $_POST['stored_cookie'] ) ? sanitize_text_field( wp_unslash( $_POST['stored_cookie'] ) ) : '';
    if ( $stored_cookie ) {
        $headers['Cookie'] = $stored_cookie;
    }

    $auth_token = isset( $_POST['auth_token'] ) ? sanitize_text_field( wp_unslash( $_POST['auth_token'] ) ) : '';
    if ( $auth_token ) {
        $headers['Authorization'] = 'Bearer ' . $auth_token;
    }

    $response = wp_remote_get(
        $url,
        array(
            'timeout' => 20,
            'headers' => $headers,
        )
    );

    if ( is_wp_error( $response ) ) {
        wp_send_json_error( array( 'message' => $response->get_error_message() ), 500 );
    }

    $status_code = wp_remote_retrieve_response_code( $response );
    $body_raw    = wp_remote_retrieve_body( $response );
    $data        = json_decode( $body_raw, true );

    $payload = array(
        'data'   => $data,
        'status' => $status_code,
    );

    $payload['success'] = $status_code >= 200 && $status_code < 300;

    wp_send_json( $payload, $status_code );
}
add_action( 'wp_ajax_nailedit_get_cart', 'nailedit_get_cart' );
add_action( 'wp_ajax_nopriv_nailedit_get_cart', 'nailedit_get_cart' );

// AJAX handler for applying coupon to cart
function nailedit_apply_coupon() {
    if ( function_exists( 'nailedit_get_local_api_base' ) ) {
        $base = nailedit_get_local_api_base();
    } else {
        $base = trailingslashit( get_option( 'las_api_base_url', 'http://localhost:8083/api/' ) );
    }

    $coupon = isset( $_POST['coupon'] ) ? sanitize_text_field( wp_unslash( $_POST['coupon'] ) ) : '';

    if ( '' === $coupon ) {
        wp_send_json_error( array( 'message' => __( 'Coupon code is required.', 'nailedit' ) ), 400 );
    }

    $url = rtrim( $base, '/' ) . '/v1/customer/cart/coupon';

    $headers = array(
        'Accept'       => 'application/json',
        'Content-Type' => 'application/json',
    );

    $stored_cookie = isset( $_POST['stored_cookie'] ) ? sanitize_text_field( wp_unslash( $_POST['stored_cookie'] ) ) : '';
    if ( $stored_cookie ) {
        $headers['Cookie'] = $stored_cookie;
    }

    $auth_token = isset( $_POST['auth_token'] ) ? sanitize_text_field( wp_unslash( $_POST['auth_token'] ) ) : '';
    if ( $auth_token ) {
        $headers['Authorization'] = 'Bearer ' . $auth_token;
    }

    $args = array(
        'method'  => 'POST',
        'timeout' => 20,
        'headers' => $headers,
        'body'    => wp_json_encode( array( 'code' => $coupon ) ),
    );

    $response = wp_remote_post( $url, $args );

    if ( is_wp_error( $response ) ) {
        wp_send_json_error( array( 'message' => $response->get_error_message() ), 500 );
    }

    $status_code = wp_remote_retrieve_response_code( $response );
    $body_raw    = wp_remote_retrieve_body( $response );
    $data        = json_decode( $body_raw, true );

    $payload = array(
        'data'   => $data,
        'status' => $status_code,
    );

    if ( $status_code >= 200 && $status_code < 300 ) {
        $payload['success'] = true;
        if ( isset( $data['message'] ) ) {
            $payload['message'] = $data['message'];
        }
        wp_send_json( $payload, $status_code );
    }

    $payload['success'] = false;
    if ( isset( $data['message'] ) ) {
        $payload['message'] = $data['message'];
    }

    wp_send_json( $payload, $status_code );
}
add_action( 'wp_ajax_nailedit_apply_coupon', 'nailedit_apply_coupon' );
add_action( 'wp_ajax_nopriv_nailedit_apply_coupon', 'nailedit_apply_coupon' );

// AJAX handler for removing coupon from cart
function nailedit_remove_coupon() {
    if ( function_exists( 'nailedit_get_local_api_base' ) ) {
        $base = nailedit_get_local_api_base();
    } else {
        $base = trailingslashit( get_option( 'las_api_base_url', 'http://localhost:8083/api/' ) );
    }

    $url = rtrim( $base, '/' ) . '/v1/customer/cart/coupon';

    $headers = array(
        'Accept' => 'application/json',
    );

    $stored_cookie = isset( $_POST['stored_cookie'] ) ? sanitize_text_field( wp_unslash( $_POST['stored_cookie'] ) ) : '';
    if ( $stored_cookie ) {
        $headers['Cookie'] = $stored_cookie;
    }

    $auth_token = isset( $_POST['auth_token'] ) ? sanitize_text_field( wp_unslash( $_POST['auth_token'] ) ) : '';
    if ( $auth_token ) {
        $headers['Authorization'] = 'Bearer ' . $auth_token;
    }

    $args = array(
        'method'  => 'DELETE',
        'timeout' => 20,
        'headers' => $headers,
    );

    $response = wp_remote_request( $url, $args );

    if ( is_wp_error( $response ) ) {
        wp_send_json_error( array( 'message' => $response->get_error_message() ), 500 );
    }

    $status_code = wp_remote_retrieve_response_code( $response );
    $body_raw    = wp_remote_retrieve_body( $response );
    $data        = json_decode( $body_raw, true );

    $payload = array(
        'data'   => $data,
        'status' => $status_code,
    );

    if ( $status_code >= 200 && $status_code < 300 ) {
        $payload['success'] = true;
        if ( isset( $data['message'] ) ) {
            $payload['message'] = $data['message'];
        }
        wp_send_json( $payload, $status_code );
    }

    $payload['success'] = false;
    if ( isset( $data['message'] ) ) {
        $payload['message'] = $data['message'];
    }

    wp_send_json( $payload, $status_code );
}
add_action( 'wp_ajax_nailedit_remove_coupon', 'nailedit_remove_coupon' );
add_action( 'wp_ajax_nopriv_nailedit_remove_coupon', 'nailedit_remove_coupon' );

// AJAX handler for updating cart item quantities
function nailedit_update_cart() {
    if ( function_exists( 'nailedit_get_local_api_base' ) ) {
        $base = nailedit_get_local_api_base();
    } else {
        $base = trailingslashit( get_option( 'las_api_base_url', 'http://localhost:8083/api/' ) );
    }

    $qty_raw = isset( $_POST['qty'] ) ? (array) $_POST['qty'] : array();

    if ( empty( $qty_raw ) ) {
        wp_send_json_error( array( 'message' => 'No quantities provided.' ), 400 );
    }

    // Normalize quantities to integers and ensure >= 1
    $qty = array();
    foreach ( $qty_raw as $item_id => $amount ) {
        $item_id = (string) $item_id;
        $amount  = max( 1, (int) $amount );
        $qty[ $item_id ] = $amount;
    }

    $url = rtrim( $base, '/' ) . '/v1/customer/cart/update';

    $headers = array(
        'Accept'       => 'application/json',
        'Content-Type' => 'application/json',
    );

    $stored_cookie = isset( $_POST['stored_cookie'] ) ? sanitize_text_field( wp_unslash( $_POST['stored_cookie'] ) ) : '';
    if ( $stored_cookie ) {
        $headers['Cookie'] = $stored_cookie;
    }

    $auth_token = isset( $_POST['auth_token'] ) ? sanitize_text_field( wp_unslash( $_POST['auth_token'] ) ) : '';
    if ( $auth_token ) {
        $headers['Authorization'] = 'Bearer ' . $auth_token;
    }

    $args = array(
        'method'  => 'PUT', // Bagisto cart/update expects PUT
        'timeout' => 20,
        'headers' => $headers,
        'body'    => wp_json_encode( array( 'qty' => $qty ) ),
    );

    $response = wp_remote_request( $url, $args );

    if ( is_wp_error( $response ) ) {
        wp_send_json_error( array( 'message' => $response->get_error_message() ), 500 );
    }

    $status_code = wp_remote_retrieve_response_code( $response );
    $body_raw    = wp_remote_retrieve_body( $response );
    $data        = json_decode( $body_raw, true );

    $payload = array(
        'data'   => $data,
        'status' => $status_code,
    );

    if ( $status_code >= 200 && $status_code < 300 ) {
        $payload['success'] = true;
        wp_send_json( $payload, $status_code );
    }

    $message = isset( $data['message'] ) ? $data['message'] : 'Failed to update cart.';
    $payload['success'] = false;
    $payload['message'] = $message;

    wp_send_json( $payload, $status_code );
}
add_action( 'wp_ajax_nailedit_update_cart', 'nailedit_update_cart' );
add_action( 'wp_ajax_nopriv_nailedit_update_cart', 'nailedit_update_cart' );

// AJAX handler for removing item from cart
function nailedit_remove_cart_item() {
    if ( function_exists( 'nailedit_get_local_api_base' ) ) {
        $base = nailedit_get_local_api_base();
    } else {
        $base = trailingslashit( get_option( 'las_api_base_url', 'http://localhost:8083/api/' ) );
    }

    $cart_item_id = isset( $_POST['cart_item_id'] ) ? absint( $_POST['cart_item_id'] ) : 0;
    if ( ! $cart_item_id ) {
        wp_send_json_error( array( 'message' => 'Cart item ID is required.' ), 400 );
    }

    $url = rtrim( $base, '/' ) . '/v1/customer/cart/remove/' . $cart_item_id;

    $headers = array(
        'Accept' => 'application/json',
    );

    $stored_cookie = isset( $_POST['stored_cookie'] ) ? sanitize_text_field( wp_unslash( $_POST['stored_cookie'] ) ) : '';
    if ( $stored_cookie ) {
        $headers['Cookie'] = $stored_cookie;
    }

    $auth_token = isset( $_POST['auth_token'] ) ? sanitize_text_field( wp_unslash( $_POST['auth_token'] ) ) : '';
    if ( $auth_token ) {
        $headers['Authorization'] = 'Bearer ' . $auth_token;
    }

    $args = array(
        'method'  => 'DELETE',
        'timeout' => 20,
        'headers' => $headers,
    );

    $response = wp_remote_request( $url, $args );

    if ( is_wp_error( $response ) ) {
        wp_send_json_error( array( 'message' => $response->get_error_message() ), 500 );
    }

    $status_code = wp_remote_retrieve_response_code( $response );
    $body_raw    = wp_remote_retrieve_body( $response );
    $data        = json_decode( $body_raw, true );

    $payload = array(
        'data'   => $data,
        'status' => $status_code,
    );

    if ( $status_code >= 200 && $status_code < 300 ) {
        $payload['success'] = true;
        wp_send_json( $payload, $status_code );
    }

    $message = isset( $data['message'] ) ? $data['message'] : 'Failed to remove item from cart.';
    $payload['success'] = false;
    $payload['message'] = $message;

    wp_send_json( $payload, $status_code );
}
add_action( 'wp_ajax_nailedit_remove_cart_item', 'nailedit_remove_cart_item' );
add_action( 'wp_ajax_nopriv_nailedit_remove_cart_item', 'nailedit_remove_cart_item' );

// AJAX handler for checkout save address (proxy to Bagisto /v1/customer/checkout/save-address)
function nailedit_checkout_save_address() {
    if ( function_exists( 'nailedit_get_local_api_base' ) ) {
        $base = nailedit_get_local_api_base();
    } else {
        $base = trailingslashit( get_option( 'las_api_base_url', 'http://localhost:8083/api/' ) );
    }

    $url = rtrim( $base, '/' ) . '/v1/customer/checkout/save-address';

    $headers = array(
        'Accept'       => 'application/json',
        'Content-Type' => 'application/json',
    );

    $stored_cookie = isset( $_POST['stored_cookie'] ) ? sanitize_text_field( wp_unslash( $_POST['stored_cookie'] ) ) : '';
    if ( $stored_cookie ) {
        $headers['Cookie'] = $stored_cookie;
    }

    $auth_token = isset( $_POST['auth_token'] ) ? sanitize_text_field( wp_unslash( $_POST['auth_token'] ) ) : '';
    if ( $auth_token ) {
        $headers['Authorization'] = 'Bearer ' . $auth_token;
    }

    // Expect billing_* and shipping_* fields and map them into Bagisto structure
    $billing = array(
        'id'              => isset( $_POST['billing_id'] ) ? intval( $_POST['billing_id'] ) : null,
        'address'         => isset( $_POST['billing_address'] ) ? array_map( 'sanitize_text_field', (array) $_POST['billing_address'] ) : array(),
        'save_as_address' => isset( $_POST['billing_save_as_address'] ) && '1' === (string) $_POST['billing_save_as_address'],
        'use_for_shipping'=> isset( $_POST['billing_use_for_shipping'] ) && '1' === (string) $_POST['billing_use_for_shipping'],
        'first_name'      => isset( $_POST['billing_first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_first_name'] ) ) : '',
        'last_name'       => isset( $_POST['billing_last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_last_name'] ) ) : '',
        'email'           => isset( $_POST['billing_email'] ) ? sanitize_email( wp_unslash( $_POST['billing_email'] ) ) : '',
        'company_name'    => isset( $_POST['billing_company_name'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_company_name'] ) ) : '',
        'city'            => isset( $_POST['billing_city'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_city'] ) ) : '',
        'state'           => isset( $_POST['billing_state'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_state'] ) ) : '',
        'country'         => isset( $_POST['billing_country'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_country'] ) ) : '',
        'postcode'        => isset( $_POST['billing_postcode'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_postcode'] ) ) : '',
        'phone'           => isset( $_POST['billing_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_phone'] ) ) : '',
    );

    $shipping = array(
        'id'              => isset( $_POST['shipping_id'] ) ? intval( $_POST['shipping_id'] ) : null,
        'address'         => isset( $_POST['shipping_address'] ) ? array_map( 'sanitize_text_field', (array) $_POST['shipping_address'] ) : array(),
        'save_as_address' => isset( $_POST['shipping_save_as_address'] ) && '1' === (string) $_POST['shipping_save_as_address'],
        'first_name'      => isset( $_POST['shipping_first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['shipping_first_name'] ) ) : '',
        'last_name'       => isset( $_POST['shipping_last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['shipping_last_name'] ) ) : '',
        'email'           => isset( $_POST['shipping_email'] ) ? sanitize_email( wp_unslash( $_POST['shipping_email'] ) ) : '',
        'company_name'    => isset( $_POST['shipping_company_name'] ) ? sanitize_text_field( wp_unslash( $_POST['shipping_company_name'] ) ) : '',
        'city'            => isset( $_POST['shipping_city'] ) ? sanitize_text_field( wp_unslash( $_POST['shipping_city'] ) ) : '',
        'state'           => isset( $_POST['shipping_state'] ) ? sanitize_text_field( wp_unslash( $_POST['shipping_state'] ) ) : '',
        'country'         => isset( $_POST['shipping_country'] ) ? sanitize_text_field( wp_unslash( $_POST['shipping_country'] ) ) : '',
        'postcode'        => isset( $_POST['shipping_postcode'] ) ? sanitize_text_field( wp_unslash( $_POST['shipping_postcode'] ) ) : '',
        'phone'           => isset( $_POST['shipping_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['shipping_phone'] ) ) : '',
    );

    $body = array(
        'billing'  => $billing,
        'shipping' => $shipping,
    );

    $args = array(
        'method'  => 'POST',
        'timeout' => 30,
        'headers' => $headers,
        'body'    => wp_json_encode( $body ),
    );

    $response = wp_remote_post( $url, $args );

    if ( is_wp_error( $response ) ) {
        wp_send_json_error( array( 'message' => $response->get_error_message() ), 500 );
    }

    $status_code = wp_remote_retrieve_response_code( $response );
    $body_raw    = wp_remote_retrieve_body( $response );
    $data        = json_decode( $body_raw, true );

    $payload = array(
        'data'   => $data,
        'status' => $status_code,
    );

    if ( $status_code >= 200 && $status_code < 300 ) {
        $payload['success'] = true;
        wp_send_json( $payload, $status_code );
    }

    $payload['success'] = false;
    if ( isset( $data['message'] ) ) {
        $payload['message'] = $data['message'];
    }

    wp_send_json( $payload, $status_code );
}
add_action( 'wp_ajax_nailedit_checkout_save_address', 'nailedit_checkout_save_address' );
add_action( 'wp_ajax_nopriv_nailedit_checkout_save_address', 'nailedit_checkout_save_address' );

// AJAX handler for checkout save order (proxy to Bagisto /v1/customer/checkout/save-order)
function nailedit_checkout_save_order() {
    if ( function_exists( 'nailedit_get_local_api_base' ) ) {
        $base = nailedit_get_local_api_base();
    } else {
        $base = trailingslashit( get_option( 'las_api_base_url', 'http://localhost:8083/api/' ) );
    }

    $url = rtrim( $base, '/' ) . '/v1/customer/checkout/save-order';

    $headers = array(
        'Accept' => 'application/json',
    );

    $stored_cookie = isset( $_POST['stored_cookie'] ) ? sanitize_text_field( wp_unslash( $_POST['stored_cookie'] ) ) : '';
    if ( $stored_cookie ) {
        $headers['Cookie'] = $stored_cookie;
    }

    $auth_token = isset( $_POST['auth_token'] ) ? sanitize_text_field( wp_unslash( $_POST['auth_token'] ) ) : '';
    if ( $auth_token ) {
        $headers['Authorization'] = 'Bearer ' . $auth_token;
    }

    $args = array(
        'method'  => 'POST',
        'timeout' => 30,
        'headers' => $headers,
    );

    $response = wp_remote_post( $url, $args );

    if ( is_wp_error( $response ) ) {
        wp_send_json_error( array( 'message' => $response->get_error_message() ), 500 );
    }

    $status_code = wp_remote_retrieve_response_code( $response );
    $body_raw    = wp_remote_retrieve_body( $response );
    $data        = json_decode( $body_raw, true );

    $payload = array(
        'data'   => $data,
        'status' => $status_code,
    );

    if ( $status_code >= 200 && $status_code < 300 ) {
        $payload['success'] = true;
        wp_send_json( $payload, $status_code );
    }

    $payload['success'] = false;
    if ( isset( $data['message'] ) ) {
        $payload['message'] = $data['message'];
    }

    wp_send_json( $payload, $status_code );
}
add_action( 'wp_ajax_nailedit_checkout_save_order', 'nailedit_checkout_save_order' );
add_action( 'wp_ajax_nopriv_nailedit_checkout_save_order', 'nailedit_checkout_save_order' );

// AJAX handler for checkout save shipping (proxy to Bagisto /v1/customer/checkout/save-shipping)
function nailedit_checkout_save_shipping() {
    if ( function_exists( 'nailedit_get_local_api_base' ) ) {
        $base = nailedit_get_local_api_base();
    } else {
        $base = trailingslashit( get_option( 'las_api_base_url', 'http://localhost:8083/api/' ) );
    }

    $url = rtrim( $base, '/' ) . '/v1/customer/checkout/save-shipping';

    $headers = array(
        'Accept'       => 'application/json',
        'Content-Type' => 'application/json',
    );

    $stored_cookie = isset( $_POST['stored_cookie'] ) ? sanitize_text_field( wp_unslash( $_POST['stored_cookie'] ) ) : '';
    if ( $stored_cookie ) {
        $headers['Cookie'] = $stored_cookie;
    }

    $auth_token = isset( $_POST['auth_token'] ) ? sanitize_text_field( wp_unslash( $_POST['auth_token'] ) ) : '';
    if ( $auth_token ) {
        $headers['Authorization'] = 'Bearer ' . $auth_token;
    }

    // Shipping method can be overridden via POST; default to flatrate_flatrate
    $shipping_method = isset( $_POST['shipping_method'] ) ? sanitize_text_field( wp_unslash( $_POST['shipping_method'] ) ) : 'flatrate_flatrate';

    $body = array(
        'shipping_method' => $shipping_method,
    );

    $args = array(
        'method'  => 'POST',
        'timeout' => 30,
        'headers' => $headers,
        'body'    => wp_json_encode( $body ),
    );

    $response = wp_remote_post( $url, $args );

    if ( is_wp_error( $response ) ) {
        wp_send_json_error( array( 'message' => $response->get_error_message() ), 500 );
    }

    $status_code = wp_remote_retrieve_response_code( $response );
    $body_raw    = wp_remote_retrieve_body( $response );
    $data        = json_decode( $body_raw, true );

    $payload = array(
        'data'   => $data,
        'status' => $status_code,
    );

    if ( $status_code >= 200 && $status_code < 300 ) {
        $payload['success'] = true;
        wp_send_json( $payload, $status_code );
    }

    $payload['success'] = false;
    if ( isset( $data['message'] ) ) {
        $payload['message'] = $data['message'];
    }

    wp_send_json( $payload, $status_code );
}
add_action( 'wp_ajax_nailedit_checkout_save_shipping', 'nailedit_checkout_save_shipping' );
add_action( 'wp_ajax_nopriv_nailedit_checkout_save_shipping', 'nailedit_checkout_save_shipping' );

// AJAX handler for checkout save payment (proxy to Bagisto /v1/customer/checkout/save-payment)
function nailedit_checkout_save_payment() {
    if ( function_exists( 'nailedit_get_local_api_base' ) ) {
        $base = nailedit_get_local_api_base();
    } else {
        $base = trailingslashit( get_option( 'las_api_base_url', 'http://localhost:8083/api/' ) );
    }

    $url = rtrim( $base, '/' ) . '/v1/customer/checkout/save-payment';

    $headers = array(
        'Accept'       => 'application/json',
        'Content-Type' => 'application/json',
    );

    $stored_cookie = isset( $_POST['stored_cookie'] ) ? sanitize_text_field( wp_unslash( $_POST['stored_cookie'] ) ) : '';
    if ( $stored_cookie ) {
        $headers['Cookie'] = $stored_cookie;
    }

    $auth_token = isset( $_POST['auth_token'] ) ? sanitize_text_field( wp_unslash( $_POST['auth_token'] ) ) : '';
    if ( $auth_token ) {
        $headers['Authorization'] = 'Bearer ' . $auth_token;
    }

    // Payment method can be overridden via POST; default to cashondelivery
    $payment_method = isset( $_POST['payment_method'] ) ? sanitize_text_field( wp_unslash( $_POST['payment_method'] ) ) : 'cashondelivery';

    $body = array(
        'payment' => array(
            'method' => $payment_method,
        ),
    );

    $args = array(
        'method'  => 'POST',
        'timeout' => 30,
        'headers' => $headers,
        'body'    => wp_json_encode( $body ),
    );

    $response = wp_remote_post( $url, $args );

    if ( is_wp_error( $response ) ) {
        wp_send_json_error( array( 'message' => $response->get_error_message() ), 500 );
    }

    $status_code = wp_remote_retrieve_response_code( $response );
    $body_raw    = wp_remote_retrieve_body( $response );
    $data        = json_decode( $body_raw, true );

    $payload = array(
        'data'   => $data,
        'status' => $status_code,
    );

    if ( $status_code >= 200 && $status_code < 300 ) {
        $payload['success'] = true;
        wp_send_json( $payload, $status_code );
    }

    $payload['success'] = false;
    if ( isset( $data['message'] ) ) {
        $payload['message'] = $data['message'];
    }

    wp_send_json( $payload, $status_code );
}
add_action( 'wp_ajax_nailedit_checkout_save_payment', 'nailedit_checkout_save_payment' );
add_action( 'wp_ajax_nopriv_nailedit_checkout_save_payment', 'nailedit_checkout_save_payment' );

// AJAX handler for customer login (proxy to avoid CORS)
function nailedit_customer_login() {
    // Get API base URL
    if ( function_exists( 'nailedit_get_local_api_base' ) ) {
        $base = nailedit_get_local_api_base();
    } else {
        $base = trailingslashit( get_option( 'las_api_base_url', 'http://localhost:8083/api/' ) );
    }

    $email       = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
    $password    = isset( $_POST['password'] ) ? (string) wp_unslash( $_POST['password'] ) : '';
    $device_name = isset( $_POST['device_name'] ) ? sanitize_text_field( wp_unslash( $_POST['device_name'] ) ) : 'web';
    $accept_token = isset( $_POST['accept_token'] ) ? (bool) $_POST['accept_token'] : true;

    if ( empty( $email ) || empty( $password ) ) {
        wp_send_json_error( array( 'message' => 'Email and password are required.' ), 400 );
    }

    $url = rtrim( $base, '/' ) . '/v1/customer/login';

    // Build query args for accept_token flag if needed
    if ( $accept_token ) {
        $url = add_query_arg( 'accept_token', '1', $url );
    }

    $body = array(
        'email'       => $email,
        'password'    => $password,
        'device_name' => $device_name,
    );

    $args = array(
        'method'  => 'POST',
        'timeout' => 20,
        'headers' => array(
            'Accept' => 'application/json',
        ),
        'body'    => $body,
    );

    $response = wp_remote_post( $url, $args );

    if ( is_wp_error( $response ) ) {
        wp_send_json_error( array( 'message' => $response->get_error_message() ), 500 );
    }

    $status_code = wp_remote_retrieve_response_code( $response );
    $body_raw    = wp_remote_retrieve_body( $response );
    $data        = json_decode( $body_raw, true );
    $cookies     = wp_remote_retrieve_header( $response, 'set-cookie' );

    $payload = array(
        'data'    => $data,
        'cookies' => $cookies,
        'status'  => $status_code,
    );

    if ( $status_code >= 200 && $status_code < 300 ) {
        $payload['success'] = true;
        wp_send_json( $payload, $status_code );
    }

    $message = isset( $data['message'] ) ? $data['message'] : 'Login failed.';
    $payload['success'] = false;
    $payload['message'] = $message;

    wp_send_json( $payload, $status_code );
}
add_action( 'wp_ajax_nailedit_customer_login', 'nailedit_customer_login' );
add_action( 'wp_ajax_nopriv_nailedit_customer_login', 'nailedit_customer_login' );

function nailedit_customer_review() {
    if ( function_exists( 'nailedit_get_local_api_base' ) ) {
        $base = nailedit_get_local_api_base();
    } else {
        $base = trailingslashit( get_option( 'las_api_base_url', 'http://localhost:8083/api/' ) );
    }

    $review     = isset( $_POST['review'] ) ? wp_kses_post( wp_unslash( $_POST['review'] ) ) : '';
    $product_id = isset( $_POST['product_id'] ) ? sanitize_text_field( wp_unslash( $_POST['product_id'] ) ) : '';
    $rating     = isset( $_POST['rating'] ) ? intval( $_POST['rating'] ) : 5;

    // Ensure rating is between 1-5
    if ( $rating < 1 || $rating > 5 ) {
        $rating = 5;
    }

    error_log( '=== REVIEW DEBUG START ===' );
    error_log( 'Review text: ' . $review );
    error_log( 'Product ID: ' . $product_id );
    error_log( 'Rating: ' . $rating );

    if ( '' === $review || '' === $product_id ) {
        error_log( 'REVIEW ERROR: Missing review or product_id' );
        wp_send_json_error( array( 'message' => 'Review text and product ID are required.' ), 400 );
    }

    $url = rtrim( $base, '/' ) . '/v1/products/' . rawurlencode( (string) $product_id ) . '/review';
    error_log( 'Review URL: ' . $url );

    $headers = array(
        'Accept'       => 'application/json',
        'Content-Type' => 'application/json',
    );

    $stored_cookie = isset( $_POST['stored_cookie'] ) ? sanitize_text_field( wp_unslash( $_POST['stored_cookie'] ) ) : '';
    if ( $stored_cookie ) {
        $headers['Cookie'] = $stored_cookie;
        error_log( 'Cookie header: ' . substr( $stored_cookie, 0, 100 ) . '...' );
    } else {
        error_log( 'NO stored_cookie received from JS' );
    }

    $auth_token = isset( $_POST['auth_token'] ) ? sanitize_text_field( wp_unslash( $_POST['auth_token'] ) ) : '';
    if ( $auth_token ) {
        $headers['Authorization'] = 'Bearer ' . $auth_token;
        error_log( 'Auth token: ' . substr( $auth_token, 0, 20 ) . '...' );
    } else {
        error_log( 'NO auth_token received from JS' );
    }

    $title = mb_substr( wp_strip_all_tags( $review ), 0, 60 );
    if ( '' === $title ) {
        $title = 'Arvustus';
    }

    $body = array(
        'title'   => $title,
        'comment' => $review,
        'rating'  => $rating,
    );

    error_log( 'Request body: ' . wp_json_encode( $body ) );
    error_log( 'Request headers: ' . print_r( $headers, true ) );

    $args = array(
        'method'  => 'POST',
        'timeout' => 20,
        'headers' => $headers,
        'body'    => wp_json_encode( $body ),
    );

    $response = wp_remote_post( $url, $args );

    if ( is_wp_error( $response ) ) {
        error_log( 'WP_Error: ' . $response->get_error_message() );
        wp_send_json_error( array( 'message' => $response->get_error_message() ), 500 );
    }

    $status_code = wp_remote_retrieve_response_code( $response );
    $body_raw    = wp_remote_retrieve_body( $response );
    $data        = json_decode( $body_raw, true );

    error_log( 'Response status: ' . $status_code );
    error_log( 'Response body: ' . $body_raw );
    error_log( '=== REVIEW DEBUG END ===' );

    $payload = array(
        'data'   => $data,
        'status' => $status_code,
    );

    if ( $status_code >= 200 && $status_code < 300 ) {
        $payload['success'] = true;
        wp_send_json( $payload, $status_code );
    }

    $payload['success'] = false;
    if ( isset( $data['message'] ) ) {
        $payload['message'] = $data['message'];
    }

    wp_send_json( $payload, $status_code );
}
add_action( 'wp_ajax_nailedit_customer_review', 'nailedit_customer_review' );
add_action( 'wp_ajax_nopriv_nailedit_customer_review', 'nailedit_customer_review' );

function nailedit_get_product_reviews() {
	if ( function_exists( 'nailedit_get_local_api_base' ) ) {
		$base = nailedit_get_local_api_base();
	} else {
		$base = trailingslashit( get_option( 'las_api_base_url', 'http://localhost:8083/api/' ) );
	}

	$product_id = isset( $_POST['product_id'] ) ? sanitize_text_field( wp_unslash( $_POST['product_id'] ) ) : '';

	if ( '' === $product_id ) {
		wp_send_json_error( array( 'message' => 'Product ID is required.' ), 400 );
	}

	$url = rtrim( $base, '/' ) . '/v1/products/' . rawurlencode( (string) $product_id ) . '/reviews';

	$headers = array(
		'Accept' => 'application/json',
	);

	$stored_cookie = isset( $_POST['stored_cookie'] ) ? sanitize_text_field( wp_unslash( $_POST['stored_cookie'] ) ) : '';
	if ( $stored_cookie ) {
		$headers['Cookie'] = $stored_cookie;
	}

	$auth_token = isset( $_POST['auth_token'] ) ? sanitize_text_field( wp_unslash( $_POST['auth_token'] ) ) : '';
	if ( $auth_token ) {
		$headers['Authorization'] = 'Bearer ' . $auth_token;
	}

	$response = wp_remote_get(
		$url,
		array(
			'timeout' => 20,
			'headers' => $headers,
		)
	);

	if ( is_wp_error( $response ) ) {
		wp_send_json_error( array( 'message' => $response->get_error_message() ), 500 );
	}

	$status_code = wp_remote_retrieve_response_code( $response );
	$body_raw    = wp_remote_retrieve_body( $response );
	$data        = json_decode( $body_raw, true );

	$payload = array(
		'data'   => $data,
		'status' => $status_code,
	);

	if ( $status_code >= 200 && $status_code < 300 ) {
		$payload['success'] = true;
		wp_send_json( $payload, $status_code );
	}

	$payload['success'] = false;
	if ( isset( $data['message'] ) ) {
		$payload['message'] = $data['message'];
	}

	wp_send_json( $payload, $status_code );
}
add_action( 'wp_ajax_nailedit_get_product_reviews', 'nailedit_get_product_reviews' );
add_action( 'wp_ajax_nopriv_nailedit_get_product_reviews', 'nailedit_get_product_reviews' );

// AJAX handler for live product search in header
function nailedit_search_products() {
	if ( function_exists( 'nailedit_get_local_api_base' ) ) {
		$base = nailedit_get_local_api_base();
	} else {
		$base = trailingslashit( get_option( 'las_api_base_url', 'http://localhost:8083/api/' ) );
	}

	// Override API base for VPS deployment
	$current_host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
	if (strpos($current_host, '45.93.139.96') !== false) {
		$base = 'http://45.93.139.96:8088/api/';
	}

	$term = isset( $_POST['q'] ) ? sanitize_text_field( wp_unslash( $_POST['q'] ) ) : '';
	if ( '' === $term ) {
		wp_send_json_error( array( 'message' => 'Search term is required.' ), 400 );
	}

	$api_url = add_query_arg(
		array(
			'page'   => 1,
			'limit'  => 8,
			'search' => $term,
			'sort'   => 'relevance', // Try to sort by relevance
			'order'  => 'desc',
		),
		rtrim( $base, '/' ) . '/v1/products'
	);

	$response = wp_remote_get(
		$api_url,
		array(
			'timeout' => 15,
			'headers' => array(
				'Accept' => 'application/json',
			),
		)
	);

	if ( is_wp_error( $response ) ) {
		wp_send_json_error( array( 'message' => $response->get_error_message() ), 500 );
	}

	$status_code = wp_remote_retrieve_response_code( $response );
	$body_raw    = wp_remote_retrieve_body( $response );
	$data        = json_decode( $body_raw, true );

	if ( ! is_array( $data ) || $status_code < 200 || $status_code >= 300 ) {
		$message = isset( $data['message'] ) ? $data['message'] : 'Failed to search products.';
		wp_send_json_error(
			array(
				'message' => $message,
			),
			$status_code ? $status_code : 500
		);
	}

	$items   = array();
	$rawlist = isset( $data['data'] ) && is_array( $data['data'] ) ? $data['data'] : array();
	foreach ( $rawlist as $product ) {
		if ( ! is_array( $product ) ) {
			continue;
		}
		$title      = isset( $product['name'] ) ? (string) $product['name'] : '';
		$product_id = isset( $product['id'] ) ? (int) $product['id'] : 0;
		$url_key    = isset( $product['url_key'] ) ? (string) $product['url_key'] : '';
		$price      = '';
		if ( isset( $product['formatted_price'] ) && '' !== $product['formatted_price'] ) {
			$price = $product['formatted_price'];
		} elseif ( isset( $product['price'] ) ) {
			$price = (string) $product['price'];
		}

		$image = '';
		if ( ! empty( $product['base_image'] ) && is_array( $product['base_image'] ) ) {
			$image = $product['base_image']['small_image_url'] ?? $product['base_image']['medium_image_url'] ?? '';
		}

		if ( ! empty( $url_key ) ) {
			$url = home_url( '/product/' . sanitize_title( $url_key ) . '/' );
		} elseif ( $product_id ) {
			$url = home_url( '/product/' . absint( $product_id ) . '/' );
		} else {
			$url = '#';
		}

		$items[] = array(
			'title' => $title,
			'url'   => $url,
			'image' => $image,
			'price' => $price,
			'relevance_score' => nailedit_calculate_relevance_score($title, $term),
		);
	}

	// Sort by relevance score (highest first)
	usort($items, function($a, $b) {
		return $b['relevance_score'] - $a['relevance_score'];
	});

	// Remove relevance_score from final output
	foreach ($items as &$item) {
		unset($item['relevance_score']);
	}

	wp_send_json_success(
		array(
			'results' => $items,
		),
		$status_code
	);
}
// Helper function to calculate relevance score for search results
function nailedit_calculate_relevance_score($title, $term) {
    if (empty($title) || empty($term)) {
        return 0;
    }
    
    $title_lower = strtolower($title);
    $term_lower = strtolower($term);
    
    // Exact match gets highest score
    if ($title_lower === $term_lower) {
        return 100;
    }
    
    // Title starts with search term
    if (strpos($title_lower, $term_lower) === 0) {
        return 80;
    }
    
    // Search term appears in title
    if (strpos($title_lower, $term_lower) !== false) {
        return 60;
    }
    
    // Partial matches in words
    $title_words = explode(' ', $title_lower);
    $term_words = explode(' ', $term_lower);
    $score = 0;
    
    foreach ($term_words as $term_word) {
        foreach ($title_words as $title_word) {
            if (strpos($title_word, $term_word) !== false) {
                $score += 20;
            }
        }
    }
    
    return $score;
}

add_action( 'wp_ajax_nailedit_search_products', 'nailedit_search_products' );
add_action( 'wp_ajax_nopriv_nailedit_search_products', 'nailedit_search_products' );

/**
 * AJAX handler for moving wishlist item to cart
 */
function nailedit_wishlist_move_to_cart() {
    $product_id       = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
    $wishlist_item_id = isset( $_POST['wishlist_item_id'] ) ? absint( $_POST['wishlist_item_id'] ) : 0;
    $bagisto_cookie   = isset( $_POST['bagisto_cookie'] ) ? sanitize_text_field( wp_unslash( $_POST['bagisto_cookie'] ) ) : '';
    $bagisto_token    = isset( $_POST['bagisto_token'] ) ? sanitize_text_field( wp_unslash( $_POST['bagisto_token'] ) ) : '';

    error_log( 'AJAX Wishlist move to cart - Product ID: ' . $product_id );
    error_log( 'AJAX Wishlist move to cart - Wishlist Row ID: ' . $wishlist_item_id );
    error_log( 'AJAX Wishlist move to cart - Has cookie: ' . ( $bagisto_cookie ? 'yes' : 'no' ) );
    error_log( 'AJAX Wishlist move to cart - Has token: ' . ( $bagisto_token ? 'yes' : 'no' ) );

    if ( ! $product_id && ! $wishlist_item_id ) {
        wp_send_json_error( 'Invalid product ID' );
    }

    if ( function_exists( 'nailedit_get_local_api_base' ) ) {
        $base = nailedit_get_local_api_base();
    } else {
        $base = trailingslashit( get_option( 'las_api_base_url', 'http://localhost:8083/api/' ) );
    }

    // Override API base for VPS deployment
    $current_host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
    if (strpos($current_host, '45.93.139.96') !== false) {
        $base = 'http://45.93.139.96:8088/api/';
    }

    $move_id = $product_id ? $product_id : $wishlist_item_id;
    $url = rtrim( $base, '/' ) . '/v1/customer/wishlist/' . $move_id . '/move-to-cart';

    $headers = array( 'Accept' => 'application/json' );
    if ( $bagisto_cookie ) {
        $headers['Cookie'] = $bagisto_cookie;
    }
    if ( $bagisto_token ) {
        $headers['Authorization'] = 'Bearer ' . $bagisto_token;
    }

    error_log( 'AJAX Wishlist move to cart - URL: ' . $url );

    $response = wp_remote_post(
        $url,
        array(
            'timeout' => 20,
            'headers' => $headers,
        )
    );

    if ( is_wp_error( $response ) ) {
        error_log( 'AJAX Wishlist move to cart - WP Error: ' . $response->get_error_message() );
        wp_send_json_error( $response->get_error_message() );
    }

    $status_code = wp_remote_retrieve_response_code( $response );
    $body_raw    = wp_remote_retrieve_body( $response );
    $data        = json_decode( $body_raw, true );

    error_log( 'AJAX Wishlist move to cart - Status: ' . $status_code );
    error_log( 'AJAX Wishlist move to cart - Response: ' . $body_raw );

    // Extract cookies from response to update cart session
    $response_cookies = wp_remote_retrieve_cookies( $response );
    $new_cart_cookie = '';
    
    if ( ! empty( $response_cookies ) ) {
        foreach ( $response_cookies as $cookie ) {
            if ( strpos( $cookie->name, 'bagisto' ) !== false || strpos( $cookie->name, 'cart' ) !== false ) {
                $new_cart_cookie .= $cookie->name . '=' . $cookie->value . '; ';
                error_log( 'AJAX Wishlist move to cart - New cookie: ' . $cookie->name . '=' . substr( $cookie->value, 0, 20 ) . '...' );
            }
        }
    }

    if ( 200 === $status_code && isset( $data['message'] ) ) {
        $response_data = array( 'message' => $data['message'] );
        
        // Send back new cart cookie if available
        if ( $new_cart_cookie ) {
            $response_data['cart_cookie'] = rtrim( $new_cart_cookie, '; ' );
        }
        
        wp_send_json_success( $response_data );
    } else {
        $error_msg = isset( $data['message'] ) ? $data['message'] : 'Midagi läks valesti!';
        error_log( 'AJAX Wishlist move to cart - Error: ' . $error_msg );
        wp_send_json_error( $error_msg );
    }
}
add_action( 'wp_ajax_nailedit_wishlist_move_to_cart', 'nailedit_wishlist_move_to_cart' );
add_action( 'wp_ajax_nopriv_nailedit_wishlist_move_to_cart', 'nailedit_wishlist_move_to_cart' );

// AJAX handler for customer registration (proxy to avoid CORS)
function nailedit_customer_register() {
    if ( function_exists( 'nailedit_get_local_api_base' ) ) {
        $base = nailedit_get_local_api_base();
    } else {
        $base = trailingslashit( get_option( 'las_api_base_url', 'http://localhost:8083/api/' ) );
    }

    $url = rtrim( $base, '/' ) . '/v1/customer/register';

    $first_name            = isset( $_POST['first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) : '';
    $last_name             = isset( $_POST['last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['last_name'] ) ) : '';
    $email                 = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
    $password              = isset( $_POST['password'] ) ? (string) wp_unslash( $_POST['password'] ) : '';
    $password_confirmation = isset( $_POST['password_confirmation'] ) ? (string) wp_unslash( $_POST['password_confirmation'] ) : '';

    if ( empty( $first_name ) || empty( $last_name ) || empty( $email ) || empty( $password ) || empty( $password_confirmation ) ) {
        wp_send_json_error( array( 'message' => 'All fields are required.' ), 400 );
    }

    $body = array(
        'first_name'            => $first_name,
        'last_name'             => $last_name,
        'email'                 => $email,
        'password'              => $password,
        'password_confirmation' => $password_confirmation,
    );

    $args = array(
        'method'  => 'POST',
        'timeout' => 20,
        'headers' => array(
            'Accept' => 'application/json',
        ),
        'body'    => $body,
    );

    $response = wp_remote_post( $url, $args );

    if ( is_wp_error( $response ) ) {
        wp_send_json_error( array( 'message' => $response->get_error_message() ), 500 );
    }

    $status_code = wp_remote_retrieve_response_code( $response );
    $body_raw    = wp_remote_retrieve_body( $response );
    $data        = json_decode( $body_raw, true );

    $payload = array(
        'data'   => $data,
        'status' => $status_code,
    );

    if ( $status_code >= 200 && $status_code < 300 ) {
        $payload['success'] = true;
        if ( isset( $data['message'] ) ) {
            $payload['message'] = $data['message'];
        }
        wp_send_json( $payload, $status_code );
    }

    $payload['success'] = false;
    if ( isset( $data['message'] ) ) {
        $payload['message'] = $data['message'];
    }

    wp_send_json( $payload, $status_code );
}
add_action( 'wp_ajax_nailedit_customer_register', 'nailedit_customer_register' );
add_action( 'wp_ajax_nopriv_nailedit_customer_register', 'nailedit_customer_register' );

// AJAX handler for customer forgot password (proxy to avoid CORS)
function nailedit_customer_forgot_password() {
    if ( function_exists( 'nailedit_get_local_api_base' ) ) {
        $base = nailedit_get_local_api_base();
    } else {
        $base = trailingslashit( get_option( 'las_api_base_url', 'http://localhost:8083/api/' ) );
    }

    $url = rtrim( $base, '/' ) . '/v1/customer/forgot-password';

    $email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';

    if ( empty( $email ) ) {
        wp_send_json_error( array( 'message' => 'Email is required.' ), 400 );
    }

    $body = array(
        'email' => $email,
    );

    $args = array(
        'method'  => 'POST',
        'timeout' => 20,
        'headers' => array(
            'Accept' => 'application/json',
        ),
        'body'    => $body,
    );

    $response = wp_remote_post( $url, $args );

    if ( is_wp_error( $response ) ) {
        wp_send_json_error( array( 'message' => $response->get_error_message() ), 500 );
    }

    $status_code = wp_remote_retrieve_response_code( $response );
    $body_raw    = wp_remote_retrieve_body( $response );
    $data        = json_decode( $body_raw, true );

    $payload = array(
        'data'   => $data,
        'status' => $status_code,
    );

    if ( $status_code >= 200 && $status_code < 300 ) {
        $payload['success'] = true;
        if ( isset( $data['message'] ) ) {
            $payload['message'] = $data['message'];
        }
        wp_send_json( $payload, $status_code );
    }

    $payload['success'] = false;
    if ( isset( $data['message'] ) ) {
        $payload['message'] = $data['message'];
    }

    wp_send_json( $payload, $status_code );
}
add_action( 'wp_ajax_nailedit_customer_forgot_password', 'nailedit_customer_forgot_password' );
add_action( 'wp_ajax_nopriv_nailedit_customer_forgot_password', 'nailedit_customer_forgot_password' );

// AJAX handler for customer profile update (proxy to avoid CORS)
function nailedit_customer_profile() {
    if ( function_exists( 'nailedit_get_local_api_base' ) ) {
        $base = nailedit_get_local_api_base();
    } else {
        $base = trailingslashit( get_option( 'las_api_base_url', 'http://localhost:8083/api/' ) );
    }

    $url = rtrim( $base, '/' ) . '/v1/customer/profile';

    $body = array(
        '_method'                   => isset( $_POST['_method'] ) ? sanitize_text_field( wp_unslash( $_POST['_method'] ) ) : 'PUT',
        'first_name'                => isset( $_POST['first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) : '',
        'last_name'                 => isset( $_POST['last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['last_name'] ) ) : '',
        'gender'                    => isset( $_POST['gender'] ) ? sanitize_text_field( wp_unslash( $_POST['gender'] ) ) : '',
        'date_of_birth'             => isset( $_POST['date_of_birth'] ) ? sanitize_text_field( wp_unslash( $_POST['date_of_birth'] ) ) : '',
        'phone'                     => isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '',
        'email'                     => isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '',
        'current_password'          => isset( $_POST['current_password'] ) ? (string) wp_unslash( $_POST['current_password'] ) : '',
        'new_password'              => isset( $_POST['new_password'] ) ? (string) wp_unslash( $_POST['new_password'] ) : '',
        'new_password_confirmation' => isset( $_POST['new_password_confirmation'] ) ? (string) wp_unslash( $_POST['new_password_confirmation'] ) : '',
        'subscribed_to_news_letter' => isset( $_POST['subscribed_to_news_letter'] ) ? 1 : 0,
    );

    $headers = array(
        'Accept' => 'application/json',
    );

    $stored_cookie = isset( $_POST['stored_cookie'] ) ? sanitize_text_field( wp_unslash( $_POST['stored_cookie'] ) ) : '';
    if ( $stored_cookie ) {
        $headers['Cookie'] = $stored_cookie;
    }

    // Optional Bearer token-based auth
    $auth_token = isset( $_POST['auth_token'] ) ? sanitize_text_field( wp_unslash( $_POST['auth_token'] ) ) : '';
    if ( $auth_token ) {
        $headers['Authorization'] = 'Bearer ' . $auth_token;
    }

    $args = array(
        'method'  => 'POST',
        'timeout' => 30,
        'headers' => $headers,
        'body'    => $body,
    );

    $response = wp_remote_post( $url, $args );

    if ( is_wp_error( $response ) ) {
        wp_send_json_error( array( 'message' => $response->get_error_message() ), 500 );
    }

    $status_code = wp_remote_retrieve_response_code( $response );
    $body_raw    = wp_remote_retrieve_body( $response );
    $data        = json_decode( $body_raw, true );

    $payload = array(
        'data'   => $data,
        'status' => $status_code,
    );

    if ( $status_code >= 200 && $status_code < 300 ) {
        $payload['success'] = true;
        wp_send_json( $payload, $status_code );
    }

    $message = isset( $data['message'] ) ? $data['message'] : 'Profile update failed.';
    $payload['success'] = false;
    $payload['message'] = $message;

    wp_send_json( $payload, $status_code );
}
add_action( 'wp_ajax_nailedit_customer_profile', 'nailedit_customer_profile' );
add_action( 'wp_ajax_nopriv_nailedit_customer_profile', 'nailedit_customer_profile' );

// AJAX handler for customer get (proxy to avoid CORS)
function nailedit_customer_get() {
    if ( function_exists( 'nailedit_get_local_api_base' ) ) {
        $base = nailedit_get_local_api_base();
    } else {
        $base = trailingslashit( get_option( 'las_api_base_url', 'http://localhost:8083/api/' ) );
    }

    $url = rtrim( $base, '/' ) . '/v1/customer/get';

    $headers = array(
        'Accept' => 'application/json',
    );

    $stored_cookie = isset( $_POST['stored_cookie'] ) ? sanitize_text_field( wp_unslash( $_POST['stored_cookie'] ) ) : '';
    if ( $stored_cookie ) {
        $headers['Cookie'] = $stored_cookie;
    }

    $auth_token = isset( $_POST['auth_token'] ) ? sanitize_text_field( wp_unslash( $_POST['auth_token'] ) ) : '';
    if ( $auth_token ) {
        $headers['Authorization'] = 'Bearer ' . $auth_token;
    }

    $response = wp_remote_get(
        $url,
        array(
            'timeout' => 20,
            'headers' => $headers,
        )
    );

    if ( is_wp_error( $response ) ) {
        wp_send_json_error( array( 'message' => $response->get_error_message() ), 500 );
    }

    $status_code = wp_remote_retrieve_response_code( $response );
    $body_raw    = wp_remote_retrieve_body( $response );
    $data        = json_decode( $body_raw, true );

    $payload = array(
        'data'   => $data,
        'status' => $status_code,
    );

    if ( $status_code >= 200 && $status_code < 300 ) {
        $payload['success'] = true;
        wp_send_json( $payload, $status_code );
    }

    $payload['success'] = false;
    if ( isset( $data['message'] ) ) {
        $payload['message'] = $data['message'];
    }

    wp_send_json( $payload, $status_code );
}
add_action( 'wp_ajax_nailedit_customer_get', 'nailedit_customer_get' );
add_action( 'wp_ajax_nopriv_nailedit_customer_get', 'nailedit_customer_get' );

// AJAX handler for customer logout (proxy to avoid CORS)
function nailedit_customer_logout() {
    if ( function_exists( 'nailedit_get_local_api_base' ) ) {
        $base = nailedit_get_local_api_base();
    } else {
        $base = trailingslashit( get_option( 'las_api_base_url', 'http://localhost:8083/api/' ) );
    }

    $url = rtrim( $base, '/' ) . '/v1/customer/logout';

    $headers = array(
        'Accept' => 'application/json',
    );

    $stored_cookie = isset( $_POST['stored_cookie'] ) ? sanitize_text_field( wp_unslash( $_POST['stored_cookie'] ) ) : '';
    if ( $stored_cookie ) {
        $headers['Cookie'] = $stored_cookie;
    }

    $auth_token = isset( $_POST['auth_token'] ) ? sanitize_text_field( wp_unslash( $_POST['auth_token'] ) ) : '';
    if ( $auth_token ) {
        $headers['Authorization'] = 'Bearer ' . $auth_token;
    }

    $args = array(
        'method'  => 'POST',
        'timeout' => 20,
        'headers' => $headers,
    );

    $response = wp_remote_post( $url, $args );

    if ( is_wp_error( $response ) ) {
        wp_send_json_error( array( 'message' => $response->get_error_message() ), 500 );
    }

    $status_code = wp_remote_retrieve_response_code( $response );
    $body_raw    = wp_remote_retrieve_body( $response );
    $data        = json_decode( $body_raw, true );

    $payload = array(
        'data'   => $data,
        'status' => $status_code,
    );

    if ( $status_code >= 200 && $status_code < 300 ) {
        $payload['success'] = true;
        wp_send_json( $payload, $status_code );
    }

    $payload['success'] = false;
    if ( isset( $data['message'] ) ) {
        $payload['message'] = $data['message'];
    }

    wp_send_json( $payload, $status_code );
}
add_action( 'wp_ajax_nailedit_customer_logout', 'nailedit_customer_logout' );
add_action( 'wp_ajax_nopriv_nailedit_customer_logout', 'nailedit_customer_logout' );

// AJAX handler for creating customer address (proxy to avoid CORS)
function nailedit_customer_address() {
    if ( function_exists( 'nailedit_get_local_api_base' ) ) {
        $base = nailedit_get_local_api_base();
    } else {
        $base = trailingslashit( get_option( 'las_api_base_url', 'http://localhost:8083/api/' ) );
    }

    $url = rtrim( $base, '/' ) . '/v1/customer/addresses';

    // Build body according to Bagisto API
    $body = array(
        'company_name'   => isset( $_POST['company_name'] ) ? sanitize_text_field( wp_unslash( $_POST['company_name'] ) ) : '',
        'first_name'     => isset( $_POST['first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) : '',
        'last_name'      => isset( $_POST['last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['last_name'] ) ) : '',
        'country'        => isset( $_POST['country'] ) ? sanitize_text_field( wp_unslash( $_POST['country'] ) ) : '',
        'state'          => isset( $_POST['state'] ) ? sanitize_text_field( wp_unslash( $_POST['state'] ) ) : '',
        'city'           => isset( $_POST['city'] ) ? sanitize_text_field( wp_unslash( $_POST['city'] ) ) : '',
        'postcode'       => isset( $_POST['postcode'] ) ? sanitize_text_field( wp_unslash( $_POST['postcode'] ) ) : '',
        'phone'          => isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '',
        'email'          => isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '',
        'vat_id'         => isset( $_POST['vat_id'] ) ? sanitize_text_field( wp_unslash( $_POST['vat_id'] ) ) : '',
        'default_address'=> isset( $_POST['default_address'] ) ? 1 : 0,
    );

    // address[] array fields
    if ( isset( $_POST['address'] ) ) {
        $addresses = (array) $_POST['address'];
        $clean = array();
        foreach ( $addresses as $addr ) {
            $clean[] = sanitize_text_field( wp_unslash( $addr ) );
        }
        $body['address'] = $clean;
    }

    $headers = array(
        'Accept' => 'application/json',
    );

    $stored_cookie = isset( $_POST['stored_cookie'] ) ? sanitize_text_field( wp_unslash( $_POST['stored_cookie'] ) ) : '';
    if ( $stored_cookie ) {
        $headers['Cookie'] = $stored_cookie;
    }

    $auth_token = isset( $_POST['auth_token'] ) ? sanitize_text_field( wp_unslash( $_POST['auth_token'] ) ) : '';
    if ( $auth_token ) {
        $headers['Authorization'] = 'Bearer ' . $auth_token;
    }

    $args = array(
        'method'  => 'POST',
        'timeout' => 30,
        'headers' => $headers,
        'body'    => $body,
    );

    $response = wp_remote_post( $url, $args );

    if ( is_wp_error( $response ) ) {
        wp_send_json_error( array( 'message' => $response->get_error_message() ), 500 );
    }

    $status_code = wp_remote_retrieve_response_code( $response );
    $body_raw    = wp_remote_retrieve_body( $response );
    $data        = json_decode( $body_raw, true );

    $payload = array(
        'data'   => $data,
        'status' => $status_code,
    );

    if ( $status_code >= 200 && $status_code < 300 ) {
        $payload['success'] = true;
        wp_send_json( $payload, $status_code );
    }

    $message = isset( $data['message'] ) ? $data['message'] : 'Address create failed.';
    $payload['success'] = false;
    $payload['message'] = $message;

    wp_send_json( $payload, $status_code );
}
add_action( 'wp_ajax_nailedit_customer_address', 'nailedit_customer_address' );
add_action( 'wp_ajax_nopriv_nailedit_customer_address', 'nailedit_customer_address' );

// AJAX handler: list customer addresses
function nailedit_list_addresses() {
    if ( function_exists( 'nailedit_get_local_api_base' ) ) {
        $base = nailedit_get_local_api_base();
    } else {
        $base = trailingslashit( get_option( 'las_api_base_url', 'http://localhost:8083/api/' ) );
    }

    $url = rtrim( $base, '/' ) . '/v1/customer/addresses';

    $headers = array(
        'Accept' => 'application/json',
    );

    $stored_cookie = isset( $_POST['stored_cookie'] ) ? sanitize_text_field( wp_unslash( $_POST['stored_cookie'] ) ) : '';
    if ( $stored_cookie ) {
        $headers['Cookie'] = $stored_cookie;
    }

    $auth_token = isset( $_POST['auth_token'] ) ? sanitize_text_field( wp_unslash( $_POST['auth_token'] ) ) : '';
    if ( $auth_token ) {
        $headers['Authorization'] = 'Bearer ' . $auth_token;
    }

    $response = wp_remote_get(
        $url,
        array(
            'timeout' => 20,
            'headers' => $headers,
        )
    );

    if ( is_wp_error( $response ) ) {
        wp_send_json_error( array( 'message' => $response->get_error_message() ), 500 );
    }

    $status_code = wp_remote_retrieve_response_code( $response );
    $body_raw    = wp_remote_retrieve_body( $response );
    $data        = json_decode( $body_raw, true );

    $payload = array(
        'data'   => $data,
        'status' => $status_code,
    );

    $payload['success'] = $status_code >= 200 && $status_code < 300;

    wp_send_json( $payload, $status_code );
}
add_action( 'wp_ajax_nailedit_list_addresses', 'nailedit_list_addresses' );
add_action( 'wp_ajax_nopriv_nailedit_list_addresses', 'nailedit_list_addresses' );

// AJAX handler: make address default
function nailedit_make_default_address() {
    if ( function_exists( 'nailedit_get_local_api_base' ) ) {
        $base = nailedit_get_local_api_base();
    } else {
        $base = trailingslashit( get_option( 'las_api_base_url', 'http://localhost:8083/api/' ) );
    }

    $address_id = isset( $_POST['address_id'] ) ? absint( $_POST['address_id'] ) : 0;
    if ( ! $address_id ) {
        wp_send_json_error( array( 'message' => 'Address ID is required.' ), 400 );
    }

    $url = rtrim( $base, '/' ) . '/v1/customer/addresses/make-default/' . $address_id;

    $headers = array(
        'Accept' => 'application/json',
    );

    $stored_cookie = isset( $_POST['stored_cookie'] ) ? sanitize_text_field( wp_unslash( $_POST['stored_cookie'] ) ) : '';
    if ( $stored_cookie ) {
        $headers['Cookie'] = $stored_cookie;
    }

    $auth_token = isset( $_POST['auth_token'] ) ? sanitize_text_field( wp_unslash( $_POST['auth_token'] ) ) : '';
    if ( $auth_token ) {
        $headers['Authorization'] = 'Bearer ' . $auth_token;
    }

    $response = wp_remote_request(
        $url,
        array(
            'method'  => 'PATCH',
            'timeout' => 20,
            'headers' => $headers,
        )
    );

    if ( is_wp_error( $response ) ) {
        wp_send_json_error( array( 'message' => $response->get_error_message() ), 500 );
    }

    $status_code = wp_remote_retrieve_response_code( $response );
    $body_raw    = wp_remote_retrieve_body( $response );
    $data        = json_decode( $body_raw, true );

    $payload = array(
        'data'   => $data,
        'status' => $status_code,
    );

    if ( $status_code >= 200 && $status_code < 300 ) {
        $payload['success'] = true;
        wp_send_json( $payload, $status_code );
    }

    $message = isset( $data['message'] ) ? $data['message'] : 'Make default failed.';
    $payload['success'] = false;
    $payload['message'] = $message;

    wp_send_json( $payload, $status_code );
}
add_action( 'wp_ajax_nailedit_make_default_address', 'nailedit_make_default_address' );
add_action( 'wp_ajax_nopriv_nailedit_make_default_address', 'nailedit_make_default_address' );

// AJAX handler: delete address
function nailedit_delete_address() {
    if ( function_exists( 'nailedit_get_local_api_base' ) ) {
        $base = nailedit_get_local_api_base();
    } else {
        $base = trailingslashit( get_option( 'las_api_base_url', 'http://localhost:8083/api/' ) );
    }

    $address_id = isset( $_POST['address_id'] ) ? absint( $_POST['address_id'] ) : 0;
    if ( ! $address_id ) {
        wp_send_json_error( array( 'message' => 'Address ID is required.' ), 400 );
    }

    $url = rtrim( $base, '/' ) . '/v1/customer/addresses/' . $address_id;

    $headers = array(
        'Accept' => 'application/json',
    );

    $stored_cookie = isset( $_POST['stored_cookie'] ) ? sanitize_text_field( wp_unslash( $_POST['stored_cookie'] ) ) : '';
    if ( $stored_cookie ) {
        $headers['Cookie'] = $stored_cookie;
    }

    $auth_token = isset( $_POST['auth_token'] ) ? sanitize_text_field( wp_unslash( $_POST['auth_token'] ) ) : '';
    if ( $auth_token ) {
        $headers['Authorization'] = 'Bearer ' . $auth_token;
    }

    $args = array(
        'method'  => 'DELETE',
        'timeout' => 20,
        'headers' => $headers,
    );

    $response = wp_remote_request( $url, $args );

    if ( is_wp_error( $response ) ) {
        wp_send_json_error( array( 'message' => $response->get_error_message() ), 500 );
    }

    $status_code = wp_remote_retrieve_response_code( $response );
    $body_raw    = wp_remote_retrieve_body( $response );
    $data        = json_decode( $body_raw, true );

    $payload = array(
        'data'   => $data,
        'status' => $status_code,
    );

    if ( $status_code >= 200 && $status_code < 300 ) {
        $payload['success'] = true;
        wp_send_json( $payload, $status_code );
    }

    $message = isset( $data['message'] ) ? $data['message'] : 'Delete failed.';
    $payload['success'] = false;
    $payload['message'] = $message;

    wp_send_json( $payload, $status_code );
}
add_action( 'wp_ajax_nailedit_delete_address', 'nailedit_delete_address' );
add_action( 'wp_ajax_nopriv_nailedit_delete_address', 'nailedit_delete_address' );

// AJAX handler: list customer orders
function nailedit_customer_orders() {
    if ( function_exists( 'nailedit_get_local_api_base' ) ) {
        $base = nailedit_get_local_api_base();
    } else {
        $base = trailingslashit( get_option( 'las_api_base_url', 'http://localhost:8083/api/' ) );
    }

    $page  = isset( $_POST['page'] ) ? max( 1, absint( $_POST['page'] ) ) : 1;
    $limit = isset( $_POST['limit'] ) ? max( 1, absint( $_POST['limit'] ) ) : 10;

    $url = add_query_arg(
        array(
            'page'  => $page,
            'limit' => $limit,
        ),
        rtrim( $base, '/' ) . '/v1/customer/orders'
    );

    $headers = array(
        'Accept' => 'application/json',
    );

    $stored_cookie = isset( $_POST['stored_cookie'] ) ? sanitize_text_field( wp_unslash( $_POST['stored_cookie'] ) ) : '';
    if ( $stored_cookie ) {
        $headers['Cookie'] = $stored_cookie;
    }

    $auth_token = isset( $_POST['auth_token'] ) ? sanitize_text_field( wp_unslash( $_POST['auth_token'] ) ) : '';
    if ( $auth_token ) {
        $headers['Authorization'] = 'Bearer ' . $auth_token;
    }

    $response = wp_remote_get(
        $url,
        array(
            'timeout' => 20,
            'headers' => $headers,
        )
    );

    if ( is_wp_error( $response ) ) {
        wp_send_json_error( array( 'message' => $response->get_error_message() ), 500 );
    }

    $status_code = wp_remote_retrieve_response_code( $response );
    $body_raw    = wp_remote_retrieve_body( $response );
    $data        = json_decode( $body_raw, true );

    $payload = array(
        'data'   => $data,
        'status' => $status_code,
    );

    $payload['success'] = $status_code >= 200 && $status_code < 300;

    wp_send_json( $payload, $status_code );
}
add_action( 'wp_ajax_nailedit_customer_orders', 'nailedit_customer_orders' );
add_action( 'wp_ajax_nopriv_nailedit_customer_orders', 'nailedit_customer_orders' );

function nailedit_toggle_wishlist() {
    if ( function_exists( 'nailedit_get_local_api_base' ) ) {
        $base = nailedit_get_local_api_base();
    } else {
        $base = trailingslashit( get_option( 'las_api_base_url', 'http://localhost:8083/api/' ) );
    }

    $product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
    if ( ! $product_id ) {
        wp_send_json_error( array( 'message' => 'Product ID is required.' ), 400 );
    }

    $url = rtrim( $base, '/' ) . '/v1/customer/wishlist/' . $product_id;

    $headers = array(
        'Accept' => 'application/json',
    );

    $stored_cookie = isset( $_POST['stored_cookie'] ) ? sanitize_text_field( wp_unslash( $_POST['stored_cookie'] ) ) : '';
    if ( $stored_cookie ) {
        $headers['Cookie'] = $stored_cookie;
    }

    $auth_token = isset( $_POST['auth_token'] ) ? sanitize_text_field( wp_unslash( $_POST['auth_token'] ) ) : '';
    if ( $auth_token ) {
        $headers['Authorization'] = 'Bearer ' . $auth_token;
    }

    $args = array(
        'method'  => 'POST',
        'timeout' => 20,
        'headers' => $headers,
    );

    $response = wp_remote_post( $url, $args );

    if ( is_wp_error( $response ) ) {
        wp_send_json_error( array( 'message' => $response->get_error_message() ), 500 );
    }

    $status_code = wp_remote_retrieve_response_code( $response );
    $body_raw    = wp_remote_retrieve_body( $response );
    $data        = json_decode( $body_raw, true );

    $payload = array(
        'data'   => $data,
        'status' => $status_code,
    );

    if ( $status_code === 200 ) {
        $payload['success'] = true;
        wp_send_json( $payload, 200 );
    }

    $message = isset( $data['message'] ) ? $data['message'] : 'Something went wrong!';
    $payload['success'] = false;
    $payload['message'] = $message;

    wp_send_json( $payload, $status_code );
}
add_action( 'wp_ajax_nailedit_toggle_wishlist', 'nailedit_toggle_wishlist' );
add_action( 'wp_ajax_nopriv_nailedit_toggle_wishlist', 'nailedit_toggle_wishlist' );

// Load helpers and shortcodes.
require_once get_template_directory() . '/inc/helpers.php';
require_once get_template_directory() . '/inc/shortcodes.php';


function nailedit_get_wishlist() {
    if ( function_exists( 'nailedit_get_local_api_base' ) ) {
        $base = nailedit_get_local_api_base();
    } else {
        $base = trailingslashit( get_option( 'las_api_base_url', 'http://localhost:8083/api/' ) );
    }

    $url = rtrim( $base, '/' ) . '/v1/customer/wishlist/all';

    $headers = array(
        'Accept' => 'application/json',
    );

    $stored_cookie = isset( $_POST['stored_cookie'] ) ? sanitize_text_field( wp_unslash( $_POST['stored_cookie'] ) ) : '';
    if ( $stored_cookie ) {
        $headers['Cookie'] = $stored_cookie;
    }

    $auth_token = isset( $_POST['auth_token'] ) ? sanitize_text_field( wp_unslash( $_POST['auth_token'] ) ) : '';
    if ( $auth_token ) {
        $headers['Authorization'] = 'Bearer ' . $auth_token;
    }

    $response = wp_remote_get(
        $url,
        array(
            'timeout' => 20,
            'headers' => $headers,
        )
    );

    if ( is_wp_error( $response ) ) {
        wp_send_json_error( array( 'message' => $response->get_error_message() ), 500 );
    }

    $status_code = wp_remote_retrieve_response_code( $response );
    $body_raw    = wp_remote_retrieve_body( $response );
    $data        = json_decode( $body_raw, true );

    $payload = array(
        'data'   => $data,
        'status' => $status_code,
    );

    $payload['success'] = $status_code >= 200 && $status_code < 300;

    wp_send_json( $payload, $status_code );
}

add_action( 'wp_ajax_nailedit_get_wishlist', 'nailedit_get_wishlist' );
add_action( 'wp_ajax_nopriv_nailedit_get_wishlist', 'nailedit_get_wishlist' );

// Add custom image sizes for product optimization
function nailedit_add_image_sizes() {
    add_image_size('product-large', 496, 496, true);  // Main product image
    add_image_size('product-thumb', 80, 80, true);    // Thumbnail image
    add_image_size('product-medium', 300, 300, true); // Medium size
}
add_action('after_setup_theme', 'nailedit_add_image_sizes');

// Fix image URLs to use WordPress image sizes
function nailedit_fix_image_url($image_url) {
    if (empty($image_url)) {
        return $image_url;
    }
    
    // Try to get attachment ID from URL
    $attachment_id = attachment_url_to_postid($image_url);
    
    if ($attachment_id) {
        // Try to get medium-large image first, then fallback to medium
        $new_url = wp_get_attachment_image_url($attachment_id, 'product-large');
        if (!$new_url) {
            $new_url = wp_get_attachment_image_url($attachment_id, 'medium');
        }
        if (!$new_url) {
            $new_url = wp_get_attachment_image_url($attachment_id, 'full');
        }
        
        if ($new_url) {
            return $new_url;
        }
    }
    
    return $image_url;
}

/**
 * Format price with euro symbol and decimal places
 * 
 * @param float|string $price Price value
 * @param int $decimals Number of decimal places (default 2)
 * @return string Formatted price (e.g. "14.50 €")
 */
function nailedit_format_price($price, $decimals = 2) {
    if (empty($price) || $price === '0' || $price === 0) {
        return '0.00 €';
    }
    
    // Convert to float if string
    $price_float = (float) $price;
    
    // Format with decimal places and add euro symbol
    $formatted = number_format($price_float, $decimals, '.', '') . ' €';
    
    return $formatted;
}