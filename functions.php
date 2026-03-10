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

        $current_lang = function_exists('nailedit_get_current_lang') ? nailedit_get_current_lang() : 'et';
        
        wp_localize_script(
            'nailedit-customer',
            'NaileditSettings',
            array(
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'translations' => array(
                    'no_ratings_yet' => nailedit_translate('no_ratings_yet', $current_lang),
                    'no_reviews_yet' => nailedit_translate('no_reviews_yet', $current_lang),
                ),
            )
        );
    }
}
add_action( 'wp_enqueue_scripts', 'nailedit_assets' );

function nailedit_register_site_settings() {
    if ( ! function_exists( 'acf_add_options_page' ) || ! function_exists( 'acf_add_local_field_group' ) ) {
        return;
    }

    acf_add_options_page(
        array(
            'page_title' => __( 'Lehe seaded', 'nailedit' ),
            'menu_title' => __( 'Lehe seaded', 'nailedit' ),
            'menu_slug'  => 'nailedit-site-settings',
            'capability' => 'manage_options',
            'redirect'   => false,
            'icon_url'   => 'dashicons-admin-generic',
            'position'   => 59,
        )
    );

    acf_add_local_field_group(
        array(
            'key'                   => 'group_nailedit_footer_settings',
            'title'                 => __( 'Jalus', 'nailedit' ),
            'fields'                => array(
                // ── Info lingid ──
                array(
                    'key'   => 'field_nailedit_footer_tab_info',
                    'label' => __( 'Info lingid', 'nailedit' ),
                    'type'  => 'tab',
                ),
                array(
                    'key'   => 'field_nailedit_footer_info_title',
                    'label' => __( 'Info veeru pealkiri', 'nailedit' ),
                    'name'  => 'footer_info_title',
                    'type'  => 'text',
                    'placeholder' => 'Info',
                ),
                array(
                    'key'   => 'field_nailedit_footer_info_link_1_label',
                    'label' => __( 'Info link 1 – pealkiri', 'nailedit' ),
                    'name'  => 'footer_info_link_1_label',
                    'type'  => 'text',
                    'placeholder' => 'Tarne',
                ),
                array(
                    'key'   => 'field_nailedit_footer_info_link_1_url',
                    'label' => __( 'Info link 1 – URL', 'nailedit' ),
                    'name'  => 'footer_info_link_1_url',
                    'type'  => 'url',
                    'placeholder' => 'https://',
                ),
                array(
                    'key'   => 'field_nailedit_footer_info_link_2_label',
                    'label' => __( 'Info link 2 – pealkiri', 'nailedit' ),
                    'name'  => 'footer_info_link_2_label',
                    'type'  => 'text',
                    'placeholder' => 'Tagastused',
                ),
                array(
                    'key'   => 'field_nailedit_footer_info_link_2_url',
                    'label' => __( 'Info link 2 – URL', 'nailedit' ),
                    'name'  => 'footer_info_link_2_url',
                    'type'  => 'url',
                    'placeholder' => 'https://',
                ),
                array(
                    'key'   => 'field_nailedit_footer_info_link_3_label',
                    'label' => __( 'Info link 3 – pealkiri', 'nailedit' ),
                    'name'  => 'footer_info_link_3_label',
                    'type'  => 'text',
                    'placeholder' => 'Klienditugi',
                ),
                array(
                    'key'   => 'field_nailedit_footer_info_link_3_url',
                    'label' => __( 'Info link 3 – URL', 'nailedit' ),
                    'name'  => 'footer_info_link_3_url',
                    'type'  => 'url',
                    'placeholder' => 'https://',
                ),

                // ── Kontaktid ──
                array(
                    'key'   => 'field_nailedit_footer_tab_contact',
                    'label' => __( 'Kontaktid', 'nailedit' ),
                    'type'  => 'tab',
                ),
                array(
                    'key'   => 'field_nailedit_footer_contact_title',
                    'label' => __( 'Kontaktide pealkiri', 'nailedit' ),
                    'name'  => 'footer_contact_title',
                    'type'  => 'text',
                    'placeholder' => 'Kontaktid',
                ),
                array(
                    'key'   => 'field_nailedit_footer_phone',
                    'label' => __( 'Telefon', 'nailedit' ),
                    'name'  => 'footer_phone',
                    'type'  => 'text',
                    'placeholder' => '+372 5555 5555',
                ),
                array(
                    'key'   => 'field_nailedit_footer_email',
                    'label' => __( 'E-post', 'nailedit' ),
                    'name'  => 'footer_email',
                    'type'  => 'email',
                    'placeholder' => 'info@nailedit.ee',
                ),
                array(
                    'key'   => 'field_nailedit_footer_hours',
                    'label' => __( 'Lahtiolekuajad', 'nailedit' ),
                    'name'  => 'footer_hours',
                    'type'  => 'text',
                    'placeholder' => 'E–R 09:00 – 19:00',
                ),

                // ── Sotsiaalmeedia ──
                array(
                    'key'   => 'field_nailedit_footer_tab_social',
                    'label' => __( 'Sotsiaalmeedia', 'nailedit' ),
                    'type'  => 'tab',
                ),
                array(
                    'key'   => 'field_nailedit_footer_instagram',
                    'label' => __( 'Instagram URL', 'nailedit' ),
                    'name'  => 'footer_instagram_url',
                    'type'  => 'url',
                    'placeholder' => 'https://instagram.com/...',
                ),
                array(
                    'key'   => 'field_nailedit_footer_tiktok',
                    'label' => __( 'TikTok URL', 'nailedit' ),
                    'name'  => 'footer_tiktok_url',
                    'type'  => 'url',
                    'placeholder' => 'https://tiktok.com/@...',
                ),

                // ── Sotsiaalmeedia jagamine ──
                array(
                    'key'   => 'field_nailedit_footer_tab_og',
                    'label' => __( 'Sotsiaalmeedia jagamine', 'nailedit' ),
                    'type'  => 'tab',
                ),
                array(
                    'key'   => 'field_nailedit_og_image',
                    'label' => __( 'Vaikimisi jagamise pilt', 'nailedit' ),
                    'name'  => 'nailedit_og_image',
                    'type'  => 'image',
                    'instructions' => __( 'See pilt kuvatakse, kui leht jagatakse Facebookis, Instagramis või muudes sotsiaalvõrgustikes. Soovitatav suurus: 1200x630px. Toote lehtedel kasutatakse automaatselt toote pilti.', 'nailedit' ),
                    'return_format' => 'url',
                    'preview_size' => 'medium',
                ),
                
                // ── Juriidika ──
                array(
                    'key'   => 'field_nailedit_footer_tab_legal',
                    'label' => __( 'Juriidika', 'nailedit' ),
                    'type'  => 'tab',
                ),
                array(
                    'key'   => 'field_nailedit_footer_privacy_label',
                    'label' => __( 'Privaatsuspoliitika – pealkiri', 'nailedit' ),
                    'name'  => 'footer_privacy_label',
                    'type'  => 'text',
                    'placeholder' => 'Privaatsuspoliitika',
                ),
                array(
                    'key'   => 'field_nailedit_footer_privacy_url',
                    'label' => __( 'Privaatsuspoliitika – URL', 'nailedit' ),
                    'name'  => 'footer_privacy_url',
                    'type'  => 'url',
                    'placeholder' => 'https://',
                ),
                array(
                    'key'   => 'field_nailedit_footer_terms_label',
                    'label' => __( 'Müügitingimused – pealkiri', 'nailedit' ),
                    'name'  => 'footer_terms_label',
                    'type'  => 'text',
                    'placeholder' => 'Müügi tingimused',
                ),
                array(
                    'key'   => 'field_nailedit_footer_terms_url',
                    'label' => __( 'Müügitingimused – URL', 'nailedit' ),
                    'name'  => 'footer_terms_url',
                    'type'  => 'url',
                    'placeholder' => 'https://',
                ),
            ),
            'location'              => array(
                array(
                    array(
                        'param'    => 'options_page',
                        'operator' => '==',
                        'value'    => 'nailedit-site-settings',
                    ),
                ),
            ),
            'style'                 => 'default',
            'position'              => 'normal',
            'label_placement'       => 'left',
            'instruction_placement' => 'label',
            'active'                => true,
        )
    );
}
add_action( 'acf/init', 'nailedit_register_site_settings' );

/**
 * Enqueue modular checkout bundle only on the custom checkout template.
 */
function nailedit_enqueue_checkout_assets() {
	if ( ! is_page_template( 'page-checkout.php' ) ) {
		return;
	}

	$script_path = get_template_directory() . '/assets/js/checkout/checkout.js';
	if ( ! file_exists( $script_path ) ) {
		return;
	}

	wp_enqueue_script(
		'nailedit-checkout',
		get_template_directory_uri() . '/assets/js/checkout/checkout.js',
		array(),
		filemtime( $script_path ),
		true
	);

	wp_localize_script(
		'nailedit-checkout',
		'NaileditCheckoutConfig',
		array(
			'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
			'thankYouUrl'  => esc_url_raw( nailedit_get_url( 'thank_you' ) ),
			'features'     => array(
				'omniva' => true,
			),
			'strings'      => array(
				'cartEmpty'              => nailedit_get_t( 'cart_empty' ),
				'cartLoadError'          => nailedit_get_t( 'cart_load_error' ),
				'cartRequired'           => nailedit_get_t( 'cart_required' ),
				'subtotal'               => nailedit_get_t( 'subtotal' ),
				'taxes'                  => nailedit_get_t( 'taxes' ),
				'discount'               => nailedit_get_t( 'discount' ),
				'grandTotal'             => nailedit_get_t( 'grand_total' ),
				'shipping'               => nailedit_get_t( 'shipping' ),
				'shippingTitle'          => nailedit_get_t( 'shipping_methods' ),
				'shippingPlaceholder'    => nailedit_get_t( 'shipping_placeholder' ),
				'shippingSelectPrompt'   => nailedit_get_t( 'shipping_select_prompt' ),
				'shippingLoading'        => nailedit_get_t( 'shipping_loading' ),
				'shippingSlow'           => nailedit_get_t( 'shipping_slow' ),
				'shippingLoadError'      => nailedit_get_t( 'shipping_load_error' ),
				'shippingNotFound'       => nailedit_get_t( 'shipping_not_found' ),
				'shippingSelectError'    => nailedit_get_t( 'shipping_select_error' ),
				'paymentTitle'           => nailedit_get_t( 'payment_method' ),
				'paymentPlaceholder'     => nailedit_get_t( 'payment_placeholder' ),
				'paymentLoading'         => nailedit_get_t( 'payment_loading' ),
				'paymentLoadError'       => nailedit_get_t( 'payment_load_error' ),
				'paymentNotFound'        => nailedit_get_t( 'payment_not_found' ),
				'paymentSelectError'     => nailedit_get_t( 'payment_select_error' ),
				'omnivaTitle'            => nailedit_get_t( 'pickup_locker' ),
				'omnivaSearchPlaceholder'=> nailedit_get_t( 'search_locker_placeholder' ),
				'omnivaLoading'          => nailedit_get_t( 'loading_lockers' ),
				'omnivaLoadError'        => nailedit_get_t( 'locker_load_error' ),
				'savingAddress'          => nailedit_get_t( 'saving_address' ),
				'savingShipping'         => nailedit_get_t( 'saving_shipping' ),
				'savingPayment'          => nailedit_get_t( 'saving_payment' ),
				'savingOrder'            => nailedit_get_t( 'saving_order' ),
				'orderSuccess'           => nailedit_get_t( 'order_success' ),
				'genericError'           => nailedit_get_t( 'something_went_wrong' ),
				'processingPayment'      => nailedit_get_t( 'processing_payment' ),
			),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'nailedit_enqueue_checkout_assets' );

/**
 * Mark the checkout bundle as an ES module so imports work without a bundler.
 */
function nailedit_checkout_module_type( $tag, $handle, $src ) {
	if ( 'nailedit-checkout' !== $handle ) {
		return $tag;
	}

	return str_replace( '<script ', '<script type="module" ', $tag );
}
add_filter( 'script_loader_tag', 'nailedit_checkout_module_type', 10, 3 );

// Custom rewrite rules for Bagisto products and categories
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

function nailedit_query_vars( $vars ) {
    $vars[] = 'product_id';
    $vars[] = 'product_sku';
    $vars[] = 'bagisto_category_slug';
    return $vars;
}
add_filter( 'query_vars', 'nailedit_query_vars' );

// Add blog ID to body class for debugging
add_filter( 'body_class', function( $classes ) {
    if ( is_multisite() ) {
        $classes[] = 'blog-id-' . get_current_blog_id();
    }
    return $classes;
} );

/**
 * Get current language for page templates
 * Uses multisite blog ID to determine language
 * 
 * @return string Language code (et or en)
 */
function nailedit_get_current_lang() {
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
    
    $locale = get_locale();
    return ( $locale === 'en_US' || $locale === 'en' ) ? 'en' : 'et';
}

/**
 * Get translation URL for a specific language based on translation_group custom field
 * 
 * @param string $lang_code Language code (et, en, ru)
 * @return string URL of the translated page or home URL if not found
 */
function nailedit_get_translation_url( $lang_code ) {
    // Site ID to language mapping
    $sites = array(
        'et' => 1,
        'en' => 3,
        'ru' => 4,
    );
    
    if ( ! isset( $sites[ $lang_code ] ) ) {
        return home_url( '/' );
    }
    
    $target_blog_id = $sites[ $lang_code ];
    
    // If we're already on the target site, return current URL
    if ( get_current_blog_id() === $target_blog_id ) {
        return is_singular() || is_page() ? get_permalink() : home_url( '/' );
    }
    
    // Check if this is a category page (dynamic route)
    $category_slug = get_query_var( 'bagisto_category_slug' );
    if ( ! empty( $category_slug ) ) {
        // For category pages, keep the same slug across languages
        // home_url() automatically includes language prefix for non-main sites
        switch_to_blog( $target_blog_id );
        $url = home_url( '/category/' . sanitize_title( $category_slug ) . '/' );
        restore_current_blog();
        return $url;
    }
    
    // Check if this is a product page (dynamic route)
    $product_sku = get_query_var( 'product_sku' );
    $product_id = get_query_var( 'product_id' );
    
    if ( ! empty( $product_sku ) || ! empty( $product_id ) ) {
        // For product pages, keep the same slug/ID across languages
        // home_url() automatically includes language prefix for non-main sites
        switch_to_blog( $target_blog_id );
        if ( ! empty( $product_sku ) ) {
            $url = home_url( '/product/' . sanitize_title( $product_sku ) . '/' );
        } else {
            $url = home_url( '/product/' . absint( $product_id ) . '/' );
        }
        restore_current_blog();
        return $url;
    }
    
    // Get translation group from current post/page
    $group = get_post_meta( get_the_ID(), 'translation_group', true );
    
    if ( ! $group ) {
        // No translation group, return home URL of target site
        switch_to_blog( $target_blog_id );
        $url = home_url( '/' );
        restore_current_blog();
        return $url;
    }
    
    // Switch to target site and find post with same translation_group
    switch_to_blog( $target_blog_id );
    
    $args = array(
        'post_type'      => 'any',
        'posts_per_page' => 1,
        'post_status'    => 'publish',
        'meta_query'     => array(
            array(
                'key'   => 'translation_group',
                'value' => $group,
            ),
        ),
    );
    
    $posts = get_posts( $args );
    $url = $posts ? get_permalink( $posts[0]->ID ) : home_url( '/' );
    
    restore_current_blog();
    
    return $url;
}

/**
 * Translate and echo text for page templates
 * 
 * @param string $key Translation key
 */
function nailedit_t( $key ) {
    if ( ! function_exists( 'nailedit_translate' ) ) {
        echo esc_html( $key );
        return;
    }
    echo esc_html( nailedit_translate( $key, nailedit_get_current_lang() ) );
}

/**
 * Translate and return text for page templates
 * 
 * @param string $key Translation key
 * @return string Translated text
 */
function nailedit_get_t( $key ) {
    if ( ! function_exists( 'nailedit_translate' ) ) {
        return $key;
    }
    return nailedit_translate( $key, nailedit_get_current_lang() );
}

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
function nailedit_extract_guest_cart_token( $data ) {
    if ( is_array( $data ) ) {
        if ( isset( $data['cart_token'] ) && is_string( $data['cart_token'] ) ) {
            return $data['cart_token'];
        }

        if ( isset( $data['token'] ) && is_string( $data['token'] ) ) {
            return $data['token'];
        }

        if ( isset( $data['data'] ) && is_array( $data['data'] ) ) {
            if ( isset( $data['data']['cart_token'] ) && is_string( $data['data']['cart_token'] ) ) {
                return $data['data']['cart_token'];
            }

            if ( isset( $data['data']['token'] ) && is_string( $data['data']['token'] ) ) {
                return $data['data']['token'];
            }
        }
    }

    return '';
}

function nailedit_guest_cart_create( $base ) {
    // Ensure API base points to Bagisto API on VPS
    $current_host = isset( $_SERVER['HTTP_HOST'] ) ? (string) $_SERVER['HTTP_HOST'] : '';
    if ( $current_host && strpos( $current_host, '45.93.139.96' ) !== false ) {
        $base = 'http://45.93.139.96:8088/api/';
    }

    $url = rtrim( $base, '/' ) . '/v1/guest/cart';

    $headers = array(
        'Accept' => 'application/json',
    );

    // Bagisto channel hostname may be configured without port; force Host header.
    if ( strpos( $url, '45.93.139.96:8088' ) !== false ) {
        $headers['Host'] = '45.93.139.96';
        $headers['X-Forwarded-Host'] = '45.93.139.96';
    }

    $response = wp_remote_post(
        $url,
        array(
            'timeout' => 20,
            'headers' => $headers,
        )
    );

    if ( is_wp_error( $response ) ) {
        return $response;
    }

    $status_code = wp_remote_retrieve_response_code( $response );
    $body_raw    = wp_remote_retrieve_body( $response );
    error_log( 'NAILEDIT Guest cart create - URL: ' . $url );
    error_log( 'NAILEDIT Guest cart create - Status: ' . $status_code );
    error_log( 'NAILEDIT Guest cart create - Body: ' . $body_raw );

    if ( $status_code < 200 || $status_code >= 300 ) {
        $data = json_decode( $body_raw, true );
        if ( is_array( $data ) && isset( $data['message'] ) && is_string( $data['message'] ) ) {
            $msg = (string) $data['message'];
        } else {
            $snippet = is_string( $body_raw ) ? trim( preg_replace( '/\s+/', ' ', substr( $body_raw, 0, 220 ) ) ) : '';
            $msg = 'Guest cart create failed (HTTP ' . $status_code . ')' . ( $snippet ? (': ' . $snippet) : '' );
        }
        return new WP_Error( 'nailedit_guest_cart_create_failed', $msg );
    }

    $data  = json_decode( $body_raw, true );
    $token    = nailedit_extract_guest_cart_token( $data );

    if ( ! $token ) {
        return new WP_Error( 'nailedit_guest_cart_token_missing', 'Guest cart token missing' );
    }

    return $token;
}

function nailedit_add_to_cart() {
    // Get API base URL (re-use the same helper/fallback as templates)
    if (function_exists('nailedit_get_local_api_base')) {
        $base = nailedit_get_local_api_base();
    } else {
        $base = trailingslashit(get_option('las_api_base_url', 'http://localhost:8083/api/'));
    }

    $current_host = isset( $_SERVER['HTTP_HOST'] ) ? (string) $_SERVER['HTTP_HOST'] : '';
    if ( $current_host && strpos( $current_host, '45.93.139.96' ) !== false ) {
        $base = 'http://45.93.139.96:8088/api/';
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
    $cart_token   = isset($_POST['cart_token']) ? sanitize_text_field($_POST['cart_token']) : '';
    $selected_configurable_option = isset($_POST['selected_configurable_option']) ? absint($_POST['selected_configurable_option']) : 0;
    $super_attribute_raw = isset($_POST['super_attribute']) ? wp_unslash($_POST['super_attribute']) : '';
    $super_attribute = array();
    if ( is_string( $super_attribute_raw ) && $super_attribute_raw !== '' ) {
        $decoded = json_decode( $super_attribute_raw, true );
        if ( is_array( $decoded ) ) {
            $super_attribute = $decoded;
        }
    }
    
    if ( $auth_token ) {
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

        $args['headers']['Authorization'] = 'Bearer ' . $auth_token;

        // Make request
        $response = wp_remote_post($url, $args);
    } else {
        if ( ! $cart_token ) {
            $cart_token = nailedit_guest_cart_create( $base );
            if ( is_wp_error( $cart_token ) ) {
                wp_send_json_error( array( 'message' => $cart_token->get_error_message() ), 500 );
            }
        }

        $url = rtrim( $base, '/' ) . '/v1/guest/cart/items';
        $payload = array(
            'product_id' => $product_id,
            'quantity'   => $quantity,
        );
        if ( $selected_configurable_option ) {
            $payload['selected_configurable_option'] = $selected_configurable_option;
        }
        if ( ! empty( $super_attribute ) ) {
            $payload['super_attribute'] = $super_attribute;
        }

        $args = array(
            'method'  => 'POST',
            'timeout' => 20,
            'headers' => array(
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json',
                'X-Cart-Token' => $cart_token,
            ),
            'body' => wp_json_encode( $payload ),
        );

        if ( strpos( $url, '45.93.139.96:8088' ) !== false ) {
            $args['headers']['Host'] = '45.93.139.96';
            $args['headers']['X-Forwarded-Host'] = '45.93.139.96';
        }

        $response = wp_remote_post( $url, $args );
    }
    
    if (is_wp_error($response)) {
        wp_send_json_error(['message' => $response->get_error_message()], 500);
    }
    
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    $status_code = wp_remote_retrieve_response_code($response);
    
    // Extract cookies from response
    $cookies = wp_remote_retrieve_header($response, 'set-cookie');
    
    // Send response with cookies
    $payload = [
        'success' => $status_code >= 200 && $status_code < 300,
        'data' => $data,
        'cookies' => $cookies,
        'status' => $status_code
    ];

    if ( ! $auth_token && is_string( $cart_token ) && $cart_token ) {
        $payload['cart_token'] = $cart_token;
    }

    wp_send_json( $payload, $status_code );
}
add_action('wp_ajax_nailedit_add_to_cart', 'nailedit_add_to_cart');
add_action('wp_ajax_nopriv_nailedit_add_to_cart', 'nailedit_add_to_cart');

function nailedit_get_cart() {
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
        $url = rtrim( $base, '/' ) . '/v1/customer/cart';
    } else {
        if ( ! $cart_token ) {
            $cart_token = nailedit_guest_cart_create( $base );
            if ( is_wp_error( $cart_token ) ) {
                wp_send_json_error( array( 'message' => $cart_token->get_error_message() ), 500 );
            }
        }
        $url = rtrim( $base, '/' ) . '/v1/guest/cart';
    }

    $headers = array(
        'Accept' => 'application/json',
    );

    $stored_cookie = isset( $_POST['stored_cookie'] ) ? sanitize_text_field( wp_unslash( $_POST['stored_cookie'] ) ) : '';
    if ( $stored_cookie && $auth_token ) {
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

    $auth_token = isset( $_POST['auth_token'] ) ? sanitize_text_field( wp_unslash( $_POST['auth_token'] ) ) : '';
    $cart_token = isset( $_POST['cart_token'] ) ? sanitize_text_field( wp_unslash( $_POST['cart_token'] ) ) : '';

    $coupon = isset( $_POST['coupon'] ) ? sanitize_text_field( wp_unslash( $_POST['coupon'] ) ) : '';

    if ( '' === $coupon ) {
        wp_send_json_error( array( 'message' => __( 'Coupon code is required.', 'nailedit' ) ), 400 );
    }

    if ( $auth_token ) {
        $url = rtrim( $base, '/' ) . '/v1/customer/cart/coupon';
    } else {
        if ( ! $cart_token ) {
            $cart_token = nailedit_guest_cart_create( $base );
            if ( is_wp_error( $cart_token ) ) {
                wp_send_json_error( array( 'message' => $cart_token->get_error_message() ), 500 );
            }
        }
        $url = rtrim( $base, '/' ) . '/v1/guest/cart/coupon';
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

    if ( ! $auth_token && $cart_token ) {
        $payload['cart_token'] = $cart_token;
    }

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

    $auth_token = isset( $_POST['auth_token'] ) ? sanitize_text_field( wp_unslash( $_POST['auth_token'] ) ) : '';
    $cart_token = isset( $_POST['cart_token'] ) ? sanitize_text_field( wp_unslash( $_POST['cart_token'] ) ) : '';

    if ( $auth_token ) {
        $url = rtrim( $base, '/' ) . '/v1/customer/cart/coupon';
    } else {
        if ( ! $cart_token ) {
            $cart_token = nailedit_guest_cart_create( $base );
            if ( is_wp_error( $cart_token ) ) {
                wp_send_json_error( array( 'message' => $cart_token->get_error_message() ), 500 );
            }
        }
        $url = rtrim( $base, '/' ) . '/v1/guest/cart/coupon';
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

    if ( ! $auth_token && $cart_token ) {
        $payload['cart_token'] = $cart_token;
    }

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

    $auth_token = isset( $_POST['auth_token'] ) ? sanitize_text_field( wp_unslash( $_POST['auth_token'] ) ) : '';
    $cart_token = isset( $_POST['cart_token'] ) ? sanitize_text_field( wp_unslash( $_POST['cart_token'] ) ) : '';

    if ( $auth_token ) {
        $url = rtrim( $base, '/' ) . '/v1/customer/cart/update';
    } else {
        if ( ! $cart_token ) {
            $cart_token = nailedit_guest_cart_create( $base );
            if ( is_wp_error( $cart_token ) ) {
                wp_send_json_error( array( 'message' => $cart_token->get_error_message() ), 500 );
            }
        }
        $url = rtrim( $base, '/' ) . '/v1/guest/cart/items';
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

    $args = array(
        'method'  => 'PUT',
        'timeout' => 20,
        'headers' => $headers,
        'body'    => wp_json_encode( $auth_token ? array( 'qty' => $qty ) : array( 'qty' => $qty ) ),
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

    if ( ! $auth_token && $cart_token ) {
        $payload['cart_token'] = $cart_token;
    }

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

    $auth_token = isset( $_POST['auth_token'] ) ? sanitize_text_field( wp_unslash( $_POST['auth_token'] ) ) : '';
    $cart_token = isset( $_POST['cart_token'] ) ? sanitize_text_field( wp_unslash( $_POST['cart_token'] ) ) : '';

    if ( $auth_token ) {
        $url = rtrim( $base, '/' ) . '/v1/customer/cart/remove/' . $cart_item_id;
    } else {
        if ( ! $cart_token ) {
            $cart_token = nailedit_guest_cart_create( $base );
            if ( is_wp_error( $cart_token ) ) {
                wp_send_json_error( array( 'message' => $cart_token->get_error_message() ), 500 );
            }
        }
        $url = rtrim( $base, '/' ) . '/v1/guest/cart/items/' . $cart_item_id;
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
    } else {
        $headers['X-Cart-Token'] = $cart_token;
        if ( strpos( $url, '45.93.139.96:8088' ) !== false ) {
            $headers['Host'] = '45.93.139.96';
            $headers['X-Forwarded-Host'] = '45.93.139.96';
        }
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

    if ( ! $auth_token && $cart_token ) {
        $payload['cart_token'] = $cart_token;
    }

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

    $customer_id = isset( $_POST['customer_id'] ) ? sanitize_text_field( wp_unslash( $_POST['customer_id'] ) ) : '';
    $name = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
    
    // Fallback name if not provided
    if ( empty( $name ) ) {
        $name = 'Klient';
    }

    $body = array(
        'title'       => $title,
        'comment'     => $review,
        'rating'      => $rating,
        'name'        => $name,
        'customer_id' => $customer_id,
    );

    error_log( 'Customer ID: ' . $customer_id );
    error_log( 'Customer name: ' . $name );
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
	if ( $stored_cookie && $auth_token ) {
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
require_once get_template_directory() . '/inc/cache-config.php';
require_once get_template_directory() . '/inc/helpers.php';
require_once get_template_directory() . '/inc/shortcodes.php';
require_once get_template_directory() . '/inc/ajax/checkout.php';


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
if ( ! function_exists( 'nailedit_fix_image_url' ) ) {
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

// Temporary: Clear cache via URL parameter
add_action( 'init', function() {
    if ( isset( $_GET['clear_cache'] ) && current_user_can( 'edit_posts' ) ) {
        $deleted = nailedit_clear_all_cache();
        wp_die( 'Cache cleared! Deleted ' . $deleted . ' transients. <a href="' . remove_query_arg( 'clear_cache' ) . '">Go back</a>' );
    }
});