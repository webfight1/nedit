<?php
/**
 * Template Name: Wishlist
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<?php get_template_part( 'template-parts/page-header' ); ?>

<?php
// Note: We don't check WordPress login here because wishlist uses Bagisto authentication
// The JavaScript will auto-submit with localStorage credentials if available

// Handle wishlist actions
$action_message = '';
$action_success = false;

// No longer using URL parameters for messages since we removed redirects

// Move single wishlist item to cart (per-row button)
// Bagisto expects WISHLIST ROW id (integer) in the {id} path for /v1/customer/wishlist/{id}/move-to-cart
if ( isset( $_POST['move_to_cart'] ) && isset( $_POST['wishlist_id'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'move_wishlist_' . absint( $_POST['wishlist_id'] ) ) ) {
    $wishlist_id    = absint( $_POST['wishlist_id'] );
    $bagisto_cookie = isset( $_POST['bagisto_cookie'] ) ? sanitize_text_field( wp_unslash( $_POST['bagisto_cookie'] ) ) : '';
    $bagisto_token  = isset( $_POST['bagisto_token'] ) ? sanitize_text_field( wp_unslash( $_POST['bagisto_token'] ) ) : '';

    if ( function_exists( 'nailedit_get_local_api_base' ) ) {
        $base = nailedit_get_local_api_base();
    } else {
        $base = trailingslashit( get_option( 'las_api_base_url', 'http://localhost:8083/api/' ) );
    }



    // Bagisto expects WISHLIST ROW id in the path
    $url = rtrim( $base, '/' ) . '/v1/customer/wishlist/' . $wishlist_id . '/move-to-cart';

    $headers = array( 'Accept' => 'application/json' );
    if ( $bagisto_cookie ) {
        $headers['Cookie'] = $bagisto_cookie;
    }
    if ( $bagisto_token ) {
        $headers['Authorization'] = 'Bearer ' . $bagisto_token;
    }

    $response = wp_remote_post(
        $url,
        array(
            'timeout' => 20,
            'headers' => $headers,
        )
    );

    if ( ! is_wp_error( $response ) ) {
        $status_code = wp_remote_retrieve_response_code( $response );
        $body_raw    = wp_remote_retrieve_body( $response );
        $data        = json_decode( $body_raw, true );

        if ( 200 === $status_code && isset( $data['message'] ) ) {
            $action_message = $data['message'];
            $action_success = true;
        } else {
            $action_message = isset( $data['message'] ) ? $data['message'] : __( 'Midagi läks valesti!', 'nailedit' );
        }
    } else {
        $action_message = $response->get_error_message();
    }
}

// Add wishlist item to cart (per-row button)
if ( isset( $_POST['add_wishlist_to_cart'] ) && isset( $_POST['product_id'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'add_wishlist_to_cart_' . absint( $_POST['wishlist_item_id'] ) ) ) {
    $wishlist_item_id = absint( $_POST['wishlist_item_id'] );
    $product_id     = absint( $_POST['product_id'] );
    $quantity       = isset( $_POST['quantity'] ) ? absint( $_POST['quantity'] ) : 1;
    $bagisto_cookie = isset( $_POST['bagisto_cookie'] ) ? sanitize_text_field( wp_unslash( $_POST['bagisto_cookie'] ) ) : '';
    $bagisto_token  = isset( $_POST['bagisto_token'] ) ? sanitize_text_field( wp_unslash( $_POST['bagisto_token'] ) ) : '';

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

    $url = rtrim( $base, '/' ) . '/v1/customer/wishlist/' . $wishlist_item_id . '/move-to-cart';

    $headers = array( 'Accept' => 'application/json' );
    if ( $bagisto_cookie ) {
        $headers['Cookie'] = $bagisto_cookie;
    }
    if ( $bagisto_token ) {
        $headers['Authorization'] = 'Bearer ' . $bagisto_token;
    }

    error_log( 'Wishlist add to cart - URL: ' . $url );
    error_log( 'Wishlist add to cart - Item ID: ' . $wishlist_item_id );
    error_log( 'Wishlist add to cart - Product ID: ' . $product_id );
    error_log( 'Wishlist add to cart - Has cookie: ' . ( $bagisto_cookie ? 'yes' : 'no' ) );
    error_log( 'Wishlist add to cart - Has token: ' . ( $bagisto_token ? 'yes' : 'no' ) );

    $response = wp_remote_post(
        $url,
        array(
            'timeout' => 20,
            'headers' => $headers,
        )
    );

    if ( ! is_wp_error( $response ) ) {
        $status_code = wp_remote_retrieve_response_code( $response );
        $body_raw    = wp_remote_retrieve_body( $response );
        $data        = json_decode( $body_raw, true );

        error_log( 'Wishlist add to cart - Status: ' . $status_code );
        error_log( 'Wishlist add to cart - Response: ' . $body_raw );

        if ( 200 === $status_code && isset( $data['message'] ) ) {
            $action_message = $data['message'];
            $action_success = true;
        } else {
            $action_message = isset( $data['message'] ) ? $data['message'] : __( 'Midagi läks valesti!', 'nailedit' );
        }
    } else {
        error_log( 'Wishlist add to cart - Error: ' . $response->get_error_message() );
        $action_message = $response->get_error_message();
    }
}

// Delete all items - try DELETE first, if fails use individual POST deletes
if ( isset( $_POST['delete_all'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'delete_all_wishlist' ) ) {
    $bagisto_cookie = isset( $_POST['bagisto_cookie'] ) ? sanitize_text_field( wp_unslash( $_POST['bagisto_cookie'] ) ) : '';
    $bagisto_token  = isset( $_POST['bagisto_token'] ) ? sanitize_text_field( wp_unslash( $_POST['bagisto_token'] ) ) : '';

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

    $url = rtrim( $base, '/' ) . '/v1/customer/wishlist/all';

    $headers = array( 'Accept' => 'application/json' );
    if ( $bagisto_cookie ) {
        $headers['Cookie'] = $bagisto_cookie;
    }
    if ( $bagisto_token ) {
        $headers['Authorization'] = 'Bearer ' . $bagisto_token;
    }

    $response = wp_remote_request( $url, array( 'method' => 'DELETE', 'timeout' => 20, 'headers' => $headers ) );

    if ( ! is_wp_error( $response ) ) {
        $status_code = wp_remote_retrieve_response_code( $response );
        $body_raw    = wp_remote_retrieve_body( $response );
        $data        = json_decode( $body_raw, true );

        if ( $status_code === 200 && isset( $data['message'] ) ) {
            $action_message = $data['message'];
            $action_success = true;
        } elseif ( $status_code === 405 ) {
            // Method not allowed - DELETE /all not supported, show error
            $action_message = __( 'Kõigi kustutamine ei ole API poolt toetatud. Palun kustuta tooted ükshaaval.', 'nailedit' );
        } else {
            $action_message = isset( $data['message'] ) ? $data['message'] : __( 'Midagi läks valesti!', 'nailedit' );
        }
    } else {
        $action_message = $response->get_error_message();
    }
    // Note: No redirect - page will reload with POST data, browser will show confirmation on refresh
}

// Fetch wishlist from Bagisto (server-side, using cookie/token from JS localStorage via hidden form)
$wishlist_items = array();
$wishlist_error = '';

// Check if we have Bagisto credentials in POST (from JS form submission on page load)
$bagisto_cookie = isset( $_POST['bagisto_cookie'] ) ? sanitize_text_field( wp_unslash( $_POST['bagisto_cookie'] ) ) : '';
$bagisto_token  = isset( $_POST['bagisto_token'] ) ? sanitize_text_field( wp_unslash( $_POST['bagisto_token'] ) ) : '';

if ( $bagisto_cookie || $bagisto_token ) {
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

    $url = rtrim( $base, '/' ) . '/v1/customer/wishlist';

    $headers = array( 'Accept' => 'application/json' );
    if ( $bagisto_cookie ) {
        $headers['Cookie'] = $bagisto_cookie;
    }
    if ( $bagisto_token ) {
        $headers['Authorization'] = 'Bearer ' . $bagisto_token;
    }

    // DEBUG: Log request details
    error_log( 'Wishlist Request URL: ' . $url );
    error_log( 'Wishlist Has cookie: ' . ( $bagisto_cookie ? 'yes' : 'no' ) );
    error_log( 'Wishlist Has token: ' . ( $bagisto_token ? 'yes' : 'no' ) );
    error_log( 'Wishlist Request Headers: ' . print_r( $headers, true ) );
    
    $response = wp_remote_get( $url, array( 'timeout' => 20, 'headers' => $headers ) );

    if ( ! is_wp_error( $response ) ) {
        $status_code = wp_remote_retrieve_response_code( $response );
        $body_raw    = wp_remote_retrieve_body( $response );
        $data        = json_decode( $body_raw, true );
        
        // DEBUG: Log response
        error_log( 'Wishlist Response Status: ' . $status_code );
        error_log( 'Wishlist Response Body: ' . $body_raw );
        
        if ( isset( $data['data'] ) && is_array( $data['data'] ) ) {
            error_log( 'Wishlist Items Count: ' . count( $data['data'] ) );
            foreach ( $data['data'] as $idx => $item ) {
                error_log( 'Wishlist Item ' . $idx . ' ID: ' . ( isset( $item['id'] ) ? $item['id'] : 'N/A' ) );
                error_log( 'Wishlist Item ' . $idx . ' Product ID: ' . ( isset( $item['product_id'] ) ? $item['product_id'] : 'N/A' ) );
            }
        }

        // Debug info
        $debug_info = sprintf(
            'URL: %s | Status: %d | Cookie: %s | Token: %s | Response: %s',
            $url,
            $status_code,
            $bagisto_cookie ? substr($bagisto_cookie, 0, 50) . '...' : 'no',
            $bagisto_token ? substr($bagisto_token, 0, 20) . '...' : 'no',
            substr( $body_raw, 0, 200 )
        );

        if ( $status_code >= 200 && $status_code < 300 && is_array( $data ) ) {
            if ( isset( $data['data'] ) && is_array( $data['data'] ) ) {
                $wishlist_items = $data['data'];
            } elseif ( is_array( $data ) ) {
                // Maybe the response is directly an array of items
                $wishlist_items = $data;
            }
        } else {
            $wishlist_error = sprintf( __( 'Soovinimekirja päring ebaõnnestus (staatus %d). Debug: %s', 'nailedit' ), (int) $status_code, $debug_info );
        }
    } else {
        $wishlist_error = $response->get_error_message();
    }
}
?>

<main class="site-main nailedit-wishlist-page py-10">
    <div class="max-w-[1200px] mx-auto px-4">


        <div id="wishlist-auth-check" class="mb-6 hidden text-center text-sm text-red-600">
            <p><?php esc_html_e( 'Soovinimekirja vaatamiseks pead olema kliendina sisse logitud.', 'nailedit' ); ?></p>
            <p class="mt-2"><a href="<?php echo esc_url( home_url( '/minu-aadressid/' ) ); ?>" class="inline-flex items-center justify-center rounded-full bg-primary px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-primary/90 transition"><?php esc_html_e( 'Logi sisse', 'nailedit' ); ?></a></p>
        </div>

        <section id="wishlist-content" class="bg-white/80 rounded-3xl shadow-lg p-6 md:p-8">
            <?php if ( $action_message ) : ?>
                <div class="mb-4 text-sm <?php echo $action_success ? 'text-emerald-600' : 'text-red-600'; ?>">
                    <?php echo esc_html( $action_message ); ?>
                </div>
            <?php endif; ?>

            <?php if ( ! empty( $wishlist_items ) ) : ?>
            <div class="flex items-center justify-between gap-3 mb-4">
                <h2 class="text-lg font-semibold text-slate-900"><?php esc_html_e( 'Minu soovinimekiri', 'nailedit' ); ?></h2>
                <form method="POST" class="nailedit-wishlist-form inline-flex items-center gap-2">
                    <?php wp_nonce_field( 'delete_all_wishlist' ); ?>
                    <input type="hidden" name="bagisto_cookie" class="bagisto-cookie-field">
                    <input type="hidden" name="bagisto_token" class="bagisto-token-field">
                    <button type="submit" name="delete_all" class="inline-flex items-center rounded-full border border-red-200 px-4 py-2 text-xs font-medium text-red-700 hover:bg-red-50"
                        onclick="return confirm('<?php echo esc_js( __( 'Kas oled kindel, et soovid kustutada kõik soovinimekirja tooted?', 'nailedit' ) ); ?>');"><?php esc_html_e( 'Kustuta kõik', 'nailedit' ); ?></button>
                </form>
            </div>

            <div class="overflow-x-auto -mx-2 md:mx-0">
                <table class="min-w-full border-separate border-spacing-y-2">
                    <thead>
                        <tr class="text-left text-xs font-medium uppercase tracking-[0.14em] text-slate-500">
                            <th class="px-2 py-1 md:px-3"><?php esc_html_e( 'Toode', 'nailedit' ); ?></th>
                            <th class="px-2 py-1 md:px-3"><?php esc_html_e( 'Hind', 'nailedit' ); ?></th>
                            <th class="px-2 py-1 md:px-3"><?php esc_html_e( 'Lisatud', 'nailedit' ); ?></th>
                            <th class="px-2 py-1 md:px-3 text-right"><?php esc_html_e( 'Tegevused', 'nailedit' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $wishlist_items as $item ) : ?>
                            <?php
                            $product_name  = '';
                            $product_price = '';
                            $product_id    = isset( $item['product_id'] ) ? $item['product_id'] : '';
                            $created_at    = isset( $item['created_at'] ) ? $item['created_at'] : '';
                            $image_url     = '';

                            if ( isset( $item['product'] ) && is_array( $item['product'] ) ) {
                                $p = $item['product'];
                                
                                // Get product ID from product object if not at top level
                                if ( ! $product_id && isset( $p['id'] ) ) {
                                    $product_id = $p['id'];
                                }
                                
                                if ( isset( $p['name'] ) ) {
                                    $product_name = $p['name'];
                                }
                                if ( isset( $p['formatted_price'] ) ) {
                                    $product_price = $p['formatted_price'];
                                } elseif ( isset( $p['price'] ) ) {
                                    $product_price = $p['price'];
                                }

                                if ( isset( $p['images'] ) && is_array( $p['images'] ) && ! empty( $p['images'] ) ) {
                                    $first_image = $p['images'][0];
                                    if ( is_array( $first_image ) ) {
                                        if ( ! empty( $first_image['small_image_url'] ) ) {
                                            $image_url = $first_image['small_image_url'];
                                        } elseif ( ! empty( $first_image['url'] ) ) {
                                            $image_url = $first_image['url'];
                                        }
                                    }
                                }
                            }

                            if ( ! $product_name && isset( $item['product_name'] ) ) {
                                $product_name = $item['product_name'];
                            }

                            if ( ! $product_price && isset( $item['formatted_price'] ) ) {
                                $product_price = $item['formatted_price'];
                            } elseif ( ! $product_price && isset( $item['price'] ) ) {
                                $product_price = $item['price'];
                            }

                            if ( ! $created_at && isset( $item['created_at'] ) ) {
                                $created_at = $item['created_at'];
                            }

                            // Build product URL using url_key
                            $url_key = '';
                            if ( isset( $item['product'] ) && is_array( $item['product'] ) && isset( $item['product']['url_key'] ) ) {
                                $url_key = $item['product']['url_key'];
                            }
                            
                            if ( ! empty( $url_key ) ) {
                                $product_url = home_url( '/product/' . sanitize_title( $url_key ) . '/' );
                            } elseif ( $product_id ) {
                                $product_url = home_url( '/product/' . absint( $product_id ) . '/' );
                            } else {
                                $product_url = '#';
                            }
                            ?>
                            <tr class="align-top border-b border-slate-100 last:border-b-0">
                                <td class="px-2 py-3 md:px-3">
                                    <div class="flex items-center gap-3 text-sm text-slate-900">
                                        <a href="<?php echo esc_url( $product_url ); ?>" class="h-10 w-10 rounded-2xl bg-slate-100 flex items-center justify-center overflow-hidden block hover:bg-slate-200 transition">
                                            <?php if ( $image_url ) : ?>
                                                <img src="<?php echo esc_url( $image_url ); ?>" alt="" class="h-full w-full object-cover" />
                                            <?php else : ?>
                                                <span class="text-xs font-semibold text-slate-500"><?php echo esc_html( $product_name ? mb_substr( $product_name, 0, 2 ) : '#' . $product_id ); ?></span>
                                            <?php endif; ?>
                                        </a>
                                        <div class="flex flex-col">
                                            <span class="text-sm font-semibold text-slate-900"><?php echo esc_html( $product_name ? $product_name : ( '#' . $product_id ) ); ?></span>
                                            <?php if ( $product_id ) : ?>
                                                <span class="text-[11px] uppercase tracking-[0.14em] text-slate-400"><?php esc_html_e( 'ID', 'nailedit' ); ?> <?php echo esc_html( $product_id ); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-2 py-3 md:px-3 align-middle">
                                    <div class="text-sm text-slate-900 min-w-[80px]">
                                        <?php echo $product_price ? esc_html( $product_price ) : '—'; ?>
                                    </div>
                                </td>
                                <td class="px-2 py-3 md:px-3 align-middle">
                                    <div class="text-xs text-slate-700 min-w-[140px]">
                                        <?php 
                                        if ( $created_at ) {
                                            $date = new DateTime( $created_at );
                                            echo esc_html( $date->format( 'd.m.Y H:i' ) );
                                        } else {
                                            echo '—';
                                        }
                                        ?>
                                    </div>
                                </td>
                                <td class="px-2 py-3 md:px-3 text-right align-middle">
                                    <div class="text-xs text-slate-700 flex items-center justify-end">
                                        <form method="POST" class="nailedit-wishlist-add-to-cart-form inline-flex items-center gap-2">
                                            <?php wp_nonce_field( 'add_wishlist_to_cart_' . absint( $item['id'] ) ); ?>
                                            <input type="hidden" name="wishlist_item_id" value="<?php echo esc_attr( $item['id'] ); ?>">
                                            <input type="hidden" name="product_id" value="<?php echo esc_attr( $product_id ); ?>">
                                            <input type="hidden" name="quantity" value="1">
                                            <input type="hidden" name="bagisto_cookie" class="bagisto-cookie-field">
                                            <input type="hidden" name="bagisto_token" class="bagisto-token-field">
                                            <button type="submit" name="add_wishlist_to_cart" class="inline-flex items-center rounded-full bg-primary px-4 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-primary/90 transition">
                                                <?php esc_html_e( 'Lisa korvi', 'nailedit' ); ?>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php elseif ( $wishlist_error ) : ?>
            <div class="text-sm text-red-600">
                <?php echo esc_html( $wishlist_error ); ?>
            </div>
        <?php else : ?>
            <div class="text-sm text-slate-700 space-y-1">
                <p><?php esc_html_e( 'Sinu soovinimekiri on tühi.', 'nailedit' ); ?></p>
            </div>
        <?php endif; ?>
        </section>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check if customer is logged in to Bagisto
    const storedCookie = localStorage.getItem('bagisto_cart_cookie');
    const authToken    = localStorage.getItem('bagisto_auth_token');
    
    const authCheckEl = document.getElementById('wishlist-auth-check');
    const contentEl   = document.getElementById('wishlist-content');

    // If no Bagisto credentials, show login message
    if (!storedCookie && !authToken) {
        authCheckEl.style.display = 'block';
        contentEl.style.display = 'none';
        return;
    }

    // Has credentials, show content
    authCheckEl.style.display = 'none';
    contentEl.style.display = 'block';

    // Handle wishlist add to cart forms with AJAX
    const addCartForms = document.querySelectorAll('.nailedit-wishlist-add-to-cart-form');
    console.log('Found ' + addCartForms.length + ' wishlist add-to-cart forms');
    
    addCartForms.forEach(function(form, index) {
        const cookieField = form.querySelector('.bagisto-cookie-field');
        const tokenField = form.querySelector('.bagisto-token-field');
        const button = form.querySelector('button[type="submit"]');
        const wishlistRowId = form.querySelector('input[name="wishlist_item_id"]').value;
        const productId = form.querySelector('input[name="product_id"]').value;
        
        console.log('Form ' + index + ' - Wishlist Row ID: ' + wishlistRowId);
        console.log('Form ' + index + ' - Product ID: ' + productId);
        console.log('Form ' + index + ' - Has button: ' + (button ? 'yes' : 'no'));
        console.log('Form ' + index + ' - Auth token: ' + (authToken ? authToken.substring(0, 20) + '...' : 'MISSING'));
        
        if (cookieField && storedCookie) {
            cookieField.value = storedCookie;
        }
        if (tokenField && authToken) {
            tokenField.value = authToken;
        }
        
        form.addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent page reload
            console.log('=== WISHLIST ADD TO CART CLICKED ===');
            console.log('Wishlist Row ID: ' + wishlistRowId);
            console.log('Product ID: ' + productId);
            console.log('Auth Token: ' + (authToken ? 'present' : 'MISSING'));
            
            if (!authToken) {
                console.error('No auth token - cannot proceed');
                alert('Palun logi sisse');
                return;
            }
            
            const originalText = button.textContent;
            button.disabled = true;
            button.textContent = 'Lisan...';
            
            console.log('Making AJAX request to admin-ajax.php');
            
            // Make AJAX request via WordPress admin-ajax.php
            const formData = new FormData();
            formData.append('action', 'nailedit_wishlist_move_to_cart');
            formData.append('product_id', productId);
            formData.append('bagisto_cookie', storedCookie || '');
            formData.append('bagisto_token', authToken);
            
            console.log('FormData prepared:', {
                action: 'nailedit_wishlist_move_to_cart',
                product_id: productId,
                has_token: !!authToken
            });
            
            fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
                method: 'POST',
                body: formData
            })
            .then(function(response) {
                console.log('AJAX Response status:', response.status);
                console.log('AJAX Response ok:', response.ok);
                return response.json();
            })
            .then(function(data) {
                console.log('AJAX Response data:', data);
                
                if (data.success) {
                    console.log('SUCCESS! Item moved to cart');
                    // Update cart cookie if provided
                    if (data.data.cart_cookie) {
                        localStorage.setItem('bagisto_cart_cookie', data.data.cart_cookie);
                        console.log('Updated cart cookie');
                    }
                    
                    // Show success message
                    const messageDiv = document.createElement('div');
                    messageDiv.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
                    messageDiv.textContent = data.data.message || 'Toode lisatud ostukorvi!';
                    document.body.appendChild(messageDiv);
                    
                    // Remove message after 3 seconds
                    setTimeout(function() {
                        messageDiv.remove();
                    }, 10000);
                    
                    // Reload page to update wishlist
                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);
                } else {
                    const errorMsg = data.data || 'Midagi läks valesti';
                    console.log('ERROR:', errorMsg);
                    
                    // Show error message
                    const messageDiv = document.createElement('div');
                    messageDiv.className = 'fixed top-4 right-4 bg-orange-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
                    
                    if (errorMsg.includes('not found') || errorMsg.includes('Selected wishlist product not found')) {
                        messageDiv.textContent = 'Värskendame soovinimekirja...';
                        document.body.appendChild(messageDiv);
                        
                        // Item is already moved or deleted, just reload to show updated wishlist
                        console.log('Item not found - reloading page to show updated wishlist');
                        setTimeout(function() {
                            window.location.reload();
                        }, 500);
                    } else {
                        messageDiv.textContent = 'Viga: ' + errorMsg;
                        document.body.appendChild(messageDiv);
                        
                        // Remove message after 3 seconds
                        setTimeout(function() {
                            messageDiv.remove();
                        }, 3000);
                        
                        button.disabled = false;
                        button.textContent = originalText;
                    }
                }
            })
            .catch(function(error) {
                console.error('Error:', error);
                alert('Viga: ' + error.message);
                button.disabled = false;
                button.textContent = originalText;
            });
        });
    });
});
</script>

<?php if ( empty( $wishlist_items ) && empty( $wishlist_error ) && ! isset( $_POST['bagisto_cookie'] ) && ! isset( $_POST['bagisto_token'] ) ) : ?>
<script>
// If we don't have wishlist data yet and we have credentials, submit the form
// PHP will prevent resubmission by checking if POST data was already sent
document.addEventListener('DOMContentLoaded', function() {
    const storedCookie = localStorage.getItem('bagisto_cart_cookie');
    const authToken    = localStorage.getItem('bagisto_auth_token');
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.style.display = 'none';

    if (storedCookie) {
        const cookieInput = document.createElement('input');
        cookieInput.type = 'hidden';
        cookieInput.name = 'bagisto_cookie';
        cookieInput.value = storedCookie;
        form.appendChild(cookieInput);
    }

    if (authToken) {
        const tokenInput = document.createElement('input');
        tokenInput.type = 'hidden';
        tokenInput.name = 'bagisto_token';
        tokenInput.value = authToken;
        form.appendChild(tokenInput);
    }

    document.body.appendChild(form);
    form.submit();
});
</script>
<?php endif; ?>

<?php
get_footer();
?>

