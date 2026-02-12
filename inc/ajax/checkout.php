<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// AJAX handler for checkout save address (proxy to Bagisto /v1/customer/checkout/save-address)
function nailedit_checkout_save_address() {
    if ( function_exists( 'nailedit_get_local_api_base' ) ) {
        $base = nailedit_get_local_api_base();
    } else {
        $base = trailingslashit( get_option( 'las_api_base_url', 'http://localhost:8083/api/' ) );
    }
    
    

    $current_host = isset( $_SERVER['HTTP_HOST'] ) ? (string) $_SERVER['HTTP_HOST'] : '';
    if ( $current_host && strpos( $current_host, '45.93.139.96' ) !== false ) {
        $base = 'http://45.93.139.96:8088/api/';
    }

    
    $auth_token = isset( $_POST['auth_token'] ) ? sanitize_text_field( wp_unslash( $_POST['auth_token'] ) ) : '';
    $cart_token = isset( $_POST['cart_token'] ) ? sanitize_text_field( wp_unslash( $_POST['cart_token'] ) ) : '';

    if ( $auth_token ) {
        $url = rtrim( $base, '/' ) . '/v1/customer/checkout/save-address';
    } else {
        if ( ! $cart_token ) {
            $cart_token = nailedit_guest_cart_create( $base );
            if ( is_wp_error( $cart_token ) ) {
                wp_send_json_error( array( 'message' => $cart_token->get_error_message() ), 500 );
            }
        }
        $url = rtrim( $base, '/' ) . '/v1/guest/checkout/addresses';
    }

    $headers = array(
        'Accept'       => 'application/json',
        'Content-Type' => 'application/json',
    );

    $stored_cookie = isset( $_POST['stored_cookie'] ) ? sanitize_text_field( wp_unslash( $_POST['stored_cookie'] ) ) : '';
    if ( $stored_cookie ) {
        $headers['Cookie'] = $stored_cookie;
    }

    if ( $auth_token ) {
        $headers['Authorization'] = 'Bearer ' . $auth_token;
    } else {
        $headers['X-Cart-Token'] = $cart_token;
        if ( strpos( $url, '45.93.139.96:8088' ) !== false ) {
            $headers['Host'] = '45.93.139.96';
            $headers['X-Forwarded-Host'] = '45.93.139.96';
        }
    }

    // Expect billing_* and shipping_* fields and map them into Bagisto structure
    $billing_address_raw = isset( $_POST['billing_address'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_address'] ) ) : '';
    $billing_address = $billing_address_raw ? array( $billing_address_raw ) : array( '—' );

    $billing_postcode = isset( $_POST['billing_postcode'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_postcode'] ) ) : '';
    $billing_phone = isset( $_POST['billing_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_phone'] ) ) : '';

    $billing = array(
        'id'              => isset( $_POST['billing_id'] ) ? intval( $_POST['billing_id'] ) : null,
        'address'         => $billing_address,
        'save_as_address' => isset( $_POST['billing_save_as_address'] ) && '1' === (string) $_POST['billing_save_as_address'],
        'use_for_shipping'=> isset( $_POST['billing_use_for_shipping'] ) && '1' === (string) $_POST['billing_use_for_shipping'],
        'first_name'      => isset( $_POST['billing_first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_first_name'] ) ) : '',
        'last_name'       => isset( $_POST['billing_last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_last_name'] ) ) : '',
        'email'           => isset( $_POST['billing_email'] ) ? sanitize_email( wp_unslash( $_POST['billing_email'] ) ) : '',
        'company_name'    => isset( $_POST['billing_company_name'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_company_name'] ) ) : '',
        'city'            => isset( $_POST['billing_city'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_city'] ) ) : '',
        'state'           => isset( $_POST['billing_state'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_state'] ) ) : '',
        'country'         => isset( $_POST['billing_country'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_country'] ) ) : '',
        'postcode'        => is_numeric( $billing_postcode ) ? intval( $billing_postcode ) : $billing_postcode,
        'phone'           => is_numeric( $billing_phone ) ? intval( $billing_phone ) : $billing_phone,
    );

    $shipping_address_raw = isset( $_POST['shipping_address'] ) ? sanitize_text_field( wp_unslash( $_POST['shipping_address'] ) ) : '';
    $shipping_address = $shipping_address_raw ? array( $shipping_address_raw ) : array( '—' );

    $shipping_postcode = isset( $_POST['shipping_postcode'] ) ? sanitize_text_field( wp_unslash( $_POST['shipping_postcode'] ) ) : '';
    $shipping_phone = isset( $_POST['shipping_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['shipping_phone'] ) ) : '';

    $shipping = array(
        'id'              => isset( $_POST['shipping_id'] ) ? intval( $_POST['shipping_id'] ) : null,
        'address'         => $shipping_address,
        'save_as_address' => isset( $_POST['shipping_save_as_address'] ) && '1' === (string) $_POST['shipping_save_as_address'],
        'first_name'      => isset( $_POST['shipping_first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['shipping_first_name'] ) ) : '',
        'last_name'       => isset( $_POST['shipping_last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['shipping_last_name'] ) ) : '',
        'email'           => isset( $_POST['shipping_email'] ) ? sanitize_email( wp_unslash( $_POST['shipping_email'] ) ) : '',
        'company_name'    => isset( $_POST['shipping_company_name'] ) ? sanitize_text_field( wp_unslash( $_POST['shipping_company_name'] ) ) : '',
        'city'            => isset( $_POST['shipping_city'] ) ? sanitize_text_field( wp_unslash( $_POST['shipping_city'] ) ) : '',
        'state'           => isset( $_POST['shipping_state'] ) ? sanitize_text_field( wp_unslash( $_POST['shipping_state'] ) ) : '',
        'country'         => isset( $_POST['shipping_country'] ) ? sanitize_text_field( wp_unslash( $_POST['shipping_country'] ) ) : '',
        'postcode'        => is_numeric( $shipping_postcode ) ? intval( $shipping_postcode ) : $shipping_postcode,
        'phone'           => is_numeric( $shipping_phone ) ? intval( $shipping_phone ) : $shipping_phone,
    );

    // Add parcel locker data if present (Omniva or Smartpost)
    $pickup_location = isset( $_POST['pickup_location'] ) ? sanitize_text_field( wp_unslash( $_POST['pickup_location'] ) ) : '';
    if ( $pickup_location ) {
        $location_data = json_decode( stripslashes( $pickup_location ), true );
        if ( $location_data && isset( $location_data['locker_id'] ) && isset( $location_data['locker_name'] ) ) {
            $shipping['additional'] = array(
                'parcel_locker' => array(
                    'locker_id'       => $location_data['locker_id'],
                    'locker_name'     => $location_data['locker_name'],
                    'locker_address'  => isset( $location_data['locker_address'] ) ? $location_data['locker_address'] : '',
                    'locker_city'     => isset( $location_data['locker_city'] ) ? $location_data['locker_city'] : '',
                    'locker_postcode' => isset( $location_data['locker_postcode'] ) ? $location_data['locker_postcode'] : '',
                    'locker_country'  => isset( $location_data['locker_country'] ) ? $location_data['locker_country'] : 'EE',
                ),
            );
        }
    }

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
    if ( null === $data && '' !== $body_raw ) {
        $data = array(
            'raw' => $body_raw,
        );
    }

    $payload = array(
        'data'   => $data,
        'status' => $status_code,
    );

    if ( ! $auth_token && $cart_token ) {
        $payload['cart_token'] = $cart_token;
    }

    if ( $status_code >= 200 && $status_code < 300 ) {
        $payload['success'] = true;
        wp_send_json( $payload, $status_code );
    }

    $payload['success'] = false;
    if ( empty( $body_raw ) ) {
        $payload['message'] = 'Bagisto returned an empty response.';
    } elseif ( isset( $data['message'] ) ) {
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

    $current_host = isset( $_SERVER['HTTP_HOST'] ) ? (string) $_SERVER['HTTP_HOST'] : '';
    if ( $current_host && strpos( $current_host, '45.93.139.96' ) !== false ) {
        $base = 'http://45.93.139.96:8088/api/';
    }

    $auth_token = isset( $_POST['auth_token'] ) ? sanitize_text_field( wp_unslash( $_POST['auth_token'] ) ) : '';
    $cart_token = isset( $_POST['cart_token'] ) ? sanitize_text_field( wp_unslash( $_POST['cart_token'] ) ) : '';

    if ( $auth_token ) {
        $url = rtrim( $base, '/' ) . '/v1/customer/checkout/save-order';
    } else {
        if ( ! $cart_token ) {
            $cart_token = nailedit_guest_cart_create( $base );
            if ( is_wp_error( $cart_token ) ) {
                wp_send_json_error( array( 'message' => $cart_token->get_error_message() ), 500 );
            }
        }
        $url = rtrim( $base, '/' ) . '/v1/guest/checkout/place-order';
    }

    $headers = array(
        'Accept' => 'application/json',
    );

    $stored_cookie = isset( $_POST['stored_cookie'] ) ? sanitize_text_field( wp_unslash( $_POST['stored_cookie'] ) ) : '';
    if ( $stored_cookie ) {
        $headers['Cookie'] = $stored_cookie;
    }

    if ( $auth_token ) {
        $headers['Authorization'] = 'Bearer ' . $auth_token;
    } else {
        $headers['X-Cart-Token'] = $cart_token;
        if ( strpos( $url, '45.93.139.96:8088' ) !== false ) {
            $headers['Host'] = '45.93.139.96';
            $headers['X-Forwarded-Host'] = '45.93.139.96';
        }
    }

    $args = array(
        'method'  => 'POST',
        'timeout' => 30,
        'headers' => $headers,
        'body'    => array(),
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

    if ( ! $auth_token && $cart_token ) {
        $payload['cart_token'] = $cart_token;
    }

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

    $current_host = isset( $_SERVER['HTTP_HOST'] ) ? (string) $_SERVER['HTTP_HOST'] : '';
    if ( $current_host && strpos( $current_host, '45.93.139.96' ) !== false ) {
        $base = 'http://45.93.139.96:8088/api/';
    }

    $auth_token = isset( $_POST['auth_token'] ) ? sanitize_text_field( wp_unslash( $_POST['auth_token'] ) ) : '';
    $cart_token = isset( $_POST['cart_token'] ) ? sanitize_text_field( wp_unslash( $_POST['cart_token'] ) ) : '';

    if ( $auth_token ) {
        $url = rtrim( $base, '/' ) . '/v1/customer/checkout/save-shipping';
    } else {
        if ( ! $cart_token ) {
            $cart_token = nailedit_guest_cart_create( $base );
            if ( is_wp_error( $cart_token ) ) {
                wp_send_json_error( array( 'message' => $cart_token->get_error_message() ), 500 );
            }
        }
        $url = rtrim( $base, '/' ) . '/v1/guest/checkout/shipping-method';
    }

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
    } else {
        $headers['X-Cart-Token'] = $cart_token;
        if ( strpos( $url, '45.93.139.96:8088' ) !== false ) {
            $headers['Host'] = '45.93.139.96';
            $headers['X-Forwarded-Host'] = '45.93.139.96';
        }
    }

    // Shipping method can be overridden via POST; default to flatrate_flatrate
    $shipping_method = isset( $_POST['shipping_method'] ) ? sanitize_text_field( wp_unslash( $_POST['shipping_method'] ) ) : 'flatrate_flatrate';

    $body = array(
        'shipping_method' => $shipping_method,
    );

    // Add parcel locker data if present (Omniva or Smartpost)
    $pickup_location = isset( $_POST['pickup_location'] ) ? sanitize_text_field( wp_unslash( $_POST['pickup_location'] ) ) : '';
    if ( $pickup_location ) {
        $location_data = json_decode( stripslashes( $pickup_location ), true );
        if ( $location_data && isset( $location_data['locker_id'] ) && isset( $location_data['locker_name'] ) ) {
            $body['parcel_locker'] = array(
                'locker_id'       => $location_data['locker_id'],
                'locker_name'     => $location_data['locker_name'],
                'locker_address'  => isset( $location_data['locker_address'] ) ? $location_data['locker_address'] : '',
                'locker_city'     => isset( $location_data['locker_city'] ) ? $location_data['locker_city'] : '',
                'locker_postcode' => isset( $location_data['locker_postcode'] ) ? $location_data['locker_postcode'] : '',
                'locker_country'  => isset( $location_data['locker_country'] ) ? $location_data['locker_country'] : 'EE',
            );
        }
    }

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

    if ( ! $auth_token && $cart_token ) {
        $payload['cart_token'] = $cart_token;
    }

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

// AJAX handler for checkout shipping methods (proxy to Bagisto /v1/guest/checkout/shipping-methods)
function nailedit_checkout_shipping_methods() {
    if ( function_exists( 'nailedit_get_local_api_base' ) ) {
        $base = nailedit_get_local_api_base();
    } else {
        $base = trailingslashit( get_option( 'las_api_base_url', 'http://localhost:8083/api/' ) );
    }

    $current_host = isset( $_SERVER['HTTP_HOST'] ) ? (string) $_SERVER['HTTP_HOST'] : '';
    if ( $current_host && strpos( $current_host, '45.93.139.96' ) !== false ) {
        $base = 'http://45.93.139.96:8088/api/';
    }

    $auth_token = isset( $_POST['auth_token'] ) ? sanitize_text_field( wp_unslash( $_POST['auth_token'] ) ) : '';
    $cart_token = isset( $_POST['cart_token'] ) ? sanitize_text_field( wp_unslash( $_POST['cart_token'] ) ) : '';

    if ( $auth_token ) {
        $url = rtrim( $base, '/' ) . '/v1/checkout/shipping-methods';
    } else {
        if ( ! $cart_token ) {
            $cart_token = nailedit_guest_cart_create( $base );
            if ( is_wp_error( $cart_token ) ) {
                wp_send_json_error( array( 'message' => $cart_token->get_error_message() ), 500 );
            }
        }
        $url = rtrim( $base, '/' ) . '/v1/guest/checkout/shipping-methods';
    }

    $headers = array(
        'Accept' => 'application/json',
    );

    $stored_cookie = isset( $_POST['stored_cookie'] ) ? sanitize_text_field( wp_unslash( $_POST['stored_cookie'] ) ) : '';
    if ( $stored_cookie ) {
        $headers['Cookie'] = $stored_cookie;
    }

    if ( $auth_token ) {
        $headers['Authorization'] = 'Bearer ' . $auth_token;
    } else {
        $headers['X-Cart-Token'] = $cart_token;
        if ( strpos( $url, '45.93.139.96:8088' ) !== false ) {
            $headers['Host'] = '45.93.139.96';
            $headers['X-Forwarded-Host'] = '45.93.139.96';
        }
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

    if ( ! $auth_token && $cart_token ) {
        $payload['cart_token'] = $cart_token;
    }

    $payload['success'] = $status_code >= 200 && $status_code < 300;

    if ( ! $payload['success'] && is_array( $data ) && isset( $data['message'] ) && is_string( $data['message'] ) ) {
        $payload['message'] = $data['message'];
    }

    wp_send_json( $payload, $status_code );
}
add_action( 'wp_ajax_nailedit_checkout_shipping_methods', 'nailedit_checkout_shipping_methods' );
add_action( 'wp_ajax_nopriv_nailedit_checkout_shipping_methods', 'nailedit_checkout_shipping_methods' );

// AJAX handler for checkout save payment (proxy to Bagisto /v1/customer/checkout/save-payment)
function nailedit_checkout_save_payment() {
    if ( function_exists( 'nailedit_get_local_api_base' ) ) {
        $base = nailedit_get_local_api_base();
    } else {
        $base = trailingslashit( get_option( 'las_api_base_url', 'http://localhost:8083/api/' ) );
    }

    $current_host = isset( $_SERVER['HTTP_HOST'] ) ? (string) $_SERVER['HTTP_HOST'] : '';
    if ( $current_host && strpos( $current_host, '45.93.139.96' ) !== false ) {
        $base = 'http://45.93.139.96:8088/api/';
    }

    $auth_token = isset( $_POST['auth_token'] ) ? sanitize_text_field( wp_unslash( $_POST['auth_token'] ) ) : '';
    $cart_token = isset( $_POST['cart_token'] ) ? sanitize_text_field( wp_unslash( $_POST['cart_token'] ) ) : '';

    if ( $auth_token ) {
        $url = rtrim( $base, '/' ) . '/v1/customer/checkout/save-payment';
    } else {
        if ( ! $cart_token ) {
            $cart_token = nailedit_guest_cart_create( $base );
            if ( is_wp_error( $cart_token ) ) {
                wp_send_json_error( array( 'message' => $cart_token->get_error_message() ), 500 );
            }
        }
        $url = rtrim( $base, '/' ) . '/v1/guest/checkout/payment-method';
    }

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
    } else {
        $headers['X-Cart-Token'] = $cart_token;
        if ( strpos( $url, '45.93.139.96:8088' ) !== false ) {
            $headers['Host'] = '45.93.139.96';
            $headers['X-Forwarded-Host'] = '45.93.139.96';
        }
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

    if ( ! $auth_token && $cart_token ) {
        $payload['cart_token'] = $cart_token;
    }

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

// AJAX handler for checkout payment methods (proxy to Bagisto /v1/guest/checkout/payment-methods)
function nailedit_checkout_payment_methods() {
    if ( function_exists( 'nailedit_get_local_api_base' ) ) {
        $base = nailedit_get_local_api_base();
    } else {
        $base = trailingslashit( get_option( 'las_api_base_url', 'http://localhost:8083/api/' ) );
    }

    $current_host = isset( $_SERVER['HTTP_HOST'] ) ? (string) $_SERVER['HTTP_HOST'] : '';
    if ( $current_host && strpos( $current_host, '45.93.139.96' ) !== false ) {
        $base = 'http://45.93.139.96:8088/api/';
    }

    $auth_token = isset( $_POST['auth_token'] ) ? sanitize_text_field( wp_unslash( $_POST['auth_token'] ) ) : '';
    $cart_token = isset( $_POST['cart_token'] ) ? sanitize_text_field( wp_unslash( $_POST['cart_token'] ) ) : '';

    if ( $auth_token ) {
        $url = rtrim( $base, '/' ) . '/v1/customer/checkout/payment-methods';
    } else {
        if ( ! $cart_token ) {
            $cart_token = nailedit_guest_cart_create( $base );
            if ( is_wp_error( $cart_token ) ) {
                wp_send_json_error( array( 'message' => $cart_token->get_error_message() ), 500 );
            }
        }
        $url = rtrim( $base, '/' ) . '/v1/guest/checkout/payment-methods';
    }

    $headers = array(
        'Accept' => 'application/json',
    );

    $stored_cookie = isset( $_POST['stored_cookie'] ) ? sanitize_text_field( wp_unslash( $_POST['stored_cookie'] ) ) : '';
    if ( $stored_cookie ) {
        $headers['Cookie'] = $stored_cookie;
    }

    if ( $auth_token ) {
        $headers['Authorization'] = 'Bearer ' . $auth_token;
    } else {
        $headers['X-Cart-Token'] = $cart_token;
        if ( strpos( $url, '45.93.139.96:8088' ) !== false ) {
            $headers['Host'] = '45.93.139.96';
            $headers['X-Forwarded-Host'] = '45.93.139.96';
        }
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

    if ( ! $auth_token && $cart_token ) {
        $payload['cart_token'] = $cart_token;
    }

    $payload['success'] = $status_code >= 200 && $status_code < 300;

    if ( ! $payload['success'] && is_array( $data ) && isset( $data['message'] ) && is_string( $data['message'] ) ) {
        $payload['message'] = $data['message'];
    }

    wp_send_json( $payload, $status_code );
}
add_action( 'wp_ajax_nailedit_checkout_payment_methods', 'nailedit_checkout_payment_methods' );
add_action( 'wp_ajax_nopriv_nailedit_checkout_payment_methods', 'nailedit_checkout_payment_methods' );

// AJAX handler for checkout payment status (proxy to Bagisto /v1/guest/checkout/payment-status)
function nailedit_checkout_payment_status() {
    if ( function_exists( 'nailedit_get_local_api_base' ) ) {
        $base = nailedit_get_local_api_base();
    } else {
        $base = trailingslashit( get_option( 'las_api_base_url', 'http://localhost:8083/api/' ) );
    }

    $current_host = isset( $_SERVER['HTTP_HOST'] ) ? (string) $_SERVER['HTTP_HOST'] : '';
    if ( $current_host && strpos( $current_host, '45.93.139.96' ) !== false ) {
        $base = 'http://45.93.139.96:8088/api/';
    }

    $auth_token = isset( $_POST['auth_token'] ) ? sanitize_text_field( wp_unslash( $_POST['auth_token'] ) ) : '';
    $cart_token = isset( $_POST['cart_token'] ) ? sanitize_text_field( wp_unslash( $_POST['cart_token'] ) ) : '';

    if ( $auth_token ) {
        $url = rtrim( $base, '/' ) . '/v1/customer/checkout/payment-status';
    } else {
        if ( ! $cart_token ) {
            $cart_token = nailedit_guest_cart_create( $base );
            if ( is_wp_error( $cart_token ) ) {
                wp_send_json_error( array( 'message' => $cart_token->get_error_message() ), 500 );
            }
        }
        $url = rtrim( $base, '/' ) . '/v1/guest/checkout/payment-status';
    }

    $headers = array(
        'Accept' => 'application/json',
    );

    $stored_cookie = isset( $_POST['stored_cookie'] ) ? sanitize_text_field( wp_unslash( $_POST['stored_cookie'] ) ) : '';
    if ( $stored_cookie ) {
        $headers['Cookie'] = $stored_cookie;
    }

    if ( $auth_token ) {
        $headers['Authorization'] = 'Bearer ' . $auth_token;
    } else {
        $headers['X-Cart-Token'] = $cart_token;
        if ( strpos( $url, '45.93.139.96:8088' ) !== false ) {
            $headers['Host'] = '45.93.139.96';
            $headers['X-Forwarded-Host'] = '45.93.139.96';
        }
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

    if ( ! $auth_token && $cart_token ) {
        $payload['cart_token'] = $cart_token;
    }

    $payload['success'] = $status_code >= 200 && $status_code < 300;

    if ( ! $payload['success'] && is_array( $data ) && isset( $data['message'] ) && is_string( $data['message'] ) ) {
        $payload['message'] = $data['message'];
    }

    wp_send_json( $payload, $status_code );
}
add_action( 'wp_ajax_nailedit_checkout_payment_status', 'nailedit_checkout_payment_status' );
add_action( 'wp_ajax_nopriv_nailedit_checkout_payment_status', 'nailedit_checkout_payment_status' );

// AJAX handler for Omniva locations list (proxy to Bagisto /v1/omniva/locations)
function nailedit_omniva_locations() {
    if ( function_exists( 'nailedit_get_local_api_base' ) ) {
        $base = nailedit_get_local_api_base();
    } else {
        $base = trailingslashit( get_option( 'las_api_base_url', 'http://localhost:8083/api/' ) );
    }

    $current_host = isset( $_SERVER['HTTP_HOST'] ) ? (string) $_SERVER['HTTP_HOST'] : '';
    if ( $current_host && strpos( $current_host, '45.93.139.96' ) !== false ) {
        $base = 'http://45.93.139.96:8088/api/';
    }

    $url = rtrim( $base, '/' ) . '/v1/omniva/locations';

    $headers = array(
        'Accept' => 'application/json',
    );

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
        'success' => $status_code >= 200 && $status_code < 300,
    );

    if ( ! $payload['success'] && is_array( $data ) && isset( $data['message'] ) && is_string( $data['message'] ) ) {
        $payload['message'] = $data['message'];
    }

    wp_send_json( $payload, $status_code );
}
add_action( 'wp_ajax_nailedit_omniva_locations', 'nailedit_omniva_locations' );
add_action( 'wp_ajax_nopriv_nailedit_omniva_locations', 'nailedit_omniva_locations' );

// AJAX handler for Smartpost locations list (proxy to Bagisto /v1/smartpost/locations)
function nailedit_smartpost_locations() {
    if ( function_exists( 'nailedit_get_local_api_base' ) ) {
        $base = nailedit_get_local_api_base();
    } else {
        $base = trailingslashit( get_option( 'las_api_base_url', 'http://localhost:8083/api/' ) );
    }

    $current_host = isset( $_SERVER['HTTP_HOST'] ) ? (string) $_SERVER['HTTP_HOST'] : '';
    if ( $current_host && strpos( $current_host, '45.93.139.96' ) !== false ) {
        $base = 'http://45.93.139.96:8088/api/';
    }

    $url = rtrim( $base, '/' ) . '/v1/smartpost/locations';

    $headers = array(
        'Accept' => 'application/json',
    );

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
        'success' => $status_code >= 200 && $status_code < 300,
    );

    if ( ! $payload['success'] && is_array( $data ) && isset( $data['message'] ) && is_string( $data['message'] ) ) {
        $payload['message'] = $data['message'];
    }

    wp_send_json( $payload, $status_code );
}
add_action( 'wp_ajax_nailedit_smartpost_locations', 'nailedit_smartpost_locations' );
add_action( 'wp_ajax_nopriv_nailedit_smartpost_locations', 'nailedit_smartpost_locations' );

// AJAX handler to get order by reference (for Esto callback)
function nailedit_get_order_by_reference() {
    if ( function_exists( 'nailedit_get_local_api_base' ) ) {
        $base = nailedit_get_local_api_base();
    } else {
        $base = trailingslashit( get_option( 'las_api_base_url', 'http://localhost:8083/api/' ) );
    }

    $current_host = isset( $_SERVER['HTTP_HOST'] ) ? (string) $_SERVER['HTTP_HOST'] : '';
    if ( $current_host && strpos( $current_host, '45.93.139.96' ) !== false ) {
        $base = 'http://45.93.139.96:8088/api/';
    }

    $reference = isset( $_POST['reference'] ) ? sanitize_text_field( wp_unslash( $_POST['reference'] ) ) : '';

    if ( ! $reference ) {
        wp_send_json_error( array( 'message' => 'Reference is required' ), 400 );
    }

    $endpoint = rtrim( $base, '/' ) . '/esto/order-by-reference/' . rawurlencode( $reference );

    $args = array(
        'method'  => 'GET',
        'timeout' => 30,
        'headers' => array(
            'Accept' => 'application/json',
        ),
    );

    $response = wp_remote_get( $endpoint, $args );

    if ( is_wp_error( $response ) ) {
        wp_send_json_error( array( 'message' => $response->get_error_message() ), 500 );
    }

    $status_code = wp_remote_retrieve_response_code( $response );
    $body_raw    = wp_remote_retrieve_body( $response );
    $data        = json_decode( $body_raw, true );

    if ( $status_code >= 200 && $status_code < 300 && ! empty( $data ) ) {
        $normalized = isset( $data['data'] ) ? $data['data'] : $data;
        wp_send_json( array(
            'success' => true,
            'data'    => array( 'order' => $normalized ),
            'status'  => $status_code,
        ), $status_code );
    }

    $message = 'Order not found for reference: ' . $reference;
    if ( isset( $data['message'] ) && is_string( $data['message'] ) ) {
        $message = $data['message'];
    }

    wp_send_json_error( array( 'message' => $message ), $status_code ?: 404 );
}
add_action( 'wp_ajax_nailedit_get_order_by_reference', 'nailedit_get_order_by_reference' );
add_action( 'wp_ajax_nopriv_nailedit_get_order_by_reference', 'nailedit_get_order_by_reference' );
