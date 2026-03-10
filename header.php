<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @package NailedIt
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair:ital,opsz,wght@0,5..1200,300..900;1,5..1200,300..900&display=swap" rel="stylesheet">
    
    <?php
    // Add Open Graph meta tags for social media sharing (non-product pages)
    // Product pages have their own OG tags in single-product.php
    if (!is_singular('product') && !get_query_var('product_id') && !get_query_var('product_sku')) {
        $og_image = function_exists('get_field') ? get_field('nailedit_og_image', 'option') : '';
        $og_title = wp_get_document_title();
        $og_description = get_bloginfo('description');
        
        // Try to get page-specific description
        if (is_singular()) {
            global $post;
            if (!empty($post->post_excerpt)) {
                $og_description = wp_strip_all_tags($post->post_excerpt);
            } elseif (!empty($post->post_content)) {
                $og_description = wp_trim_words(wp_strip_all_tags($post->post_content), 30);
            }
        }
        
        if ($og_image || $og_title || $og_description) {
            ?>
            <!-- Open Graph Meta Tags for Social Media Sharing -->
            <meta property="og:type" content="website" />
            <meta property="og:title" content="<?php echo esc_attr($og_title); ?>" />
            <?php if ($og_description): ?>
            <meta property="og:description" content="<?php echo esc_attr($og_description); ?>" />
            <?php endif; ?>
            <?php if ($og_image): ?>
            <meta property="og:image" content="<?php echo esc_url($og_image); ?>" />
            <meta property="og:image:width" content="1200" />
            <meta property="og:image:height" content="630" />
            <?php endif; ?>
            <meta property="og:url" content="<?php echo esc_url(get_permalink()); ?>" />
            <meta property="og:site_name" content="<?php echo esc_attr(get_bloginfo('name')); ?>" />
            
            <!-- Twitter Card -->
            <meta name="twitter:card" content="summary_large_image" />
            <meta name="twitter:title" content="<?php echo esc_attr($og_title); ?>" />
            <?php if ($og_description): ?>
            <meta name="twitter:description" content="<?php echo esc_attr($og_description); ?>" />
            <?php endif; ?>
            <?php if ($og_image): ?>
            <meta name="twitter:image" content="<?php echo esc_url($og_image); ?>" />
            <?php endif; ?>
            <?php
        }
    }
    ?>
    
    <?php wp_head(); ?>
</head>
<body id="page-<?php the_ID(); ?>" <?php body_class(); ?>>

<header class="nailedit-site-header sticky top-0  z-30 bg-gradient-to-b  from-[#1c0d25] to-[#56265d] backdrop-blur  ">
    <div class="nailedit-header-inner max-w-[1200px] mx-auto flex items-center justify-between gap-2 lg:gap-6  px-4 py-3 md:py-4">
        <div class="nailedit-header-left flex items-center gap-3">
            <div class="nailedit-logo flex items-center gap-2">
                <?php if ( function_exists( 'has_custom_logo' ) && has_custom_logo() ) : ?>
                    <?php echo get_custom_logo(); ?>
                <?php else : ?>
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="flex items-center gap-2">
                        <span class="font-nailedit text-[26px] md:text-[30px] leading-none text-primary">NAILEDIT</span>
                        <span class="hidden sm:inline-block text-[10px] uppercase tracking-[0.2em] text-slate-500 mt-[2px]">Beauty Concept</span>
                    </a>
                <?php endif; ?>
            </div>

        </div>

        <div class="nailedit-header-center hidden md:flex flex-1 justify-center">
            <?php
            // Menu will be handled by language plugin
            wp_nav_menu(
                array(
                    'theme_location' => 'primary',
                    'container'      => 'nav',
                    'menu_class'     => 'nailedit-main-menu flex items-center gap-6 text-[13px] uppercase tracking-[0.08em] text-slate-700',
                    'fallback_cb'    => false,
                )
            );
            ?>
        </div>

        <div class="nailedit-header-right flex items-center  md:gap-4">
            <?php if ( is_multisite() ) : ?>
                <nav class="hidden md:flex items-center text-[12px] font-semibold" aria-label="Language">
                    <?php
                    $current_site_id = get_current_blog_id();
                    
                    // Define site ID to language mapping
                    $site_languages = array(
                        1 => 'et', // Main site is ET
                        3 => 'en', // English site is EN
                        4 => 'ru', // Russian site is RU
                    );
                    
                    foreach ( $site_languages as $blog_id => $lang ) {
                        $site_url = nailedit_get_translation_url( $lang );
                        $is_current = ( $blog_id === $current_site_id );
                        $site_label = strtoupper( $lang );
                        ?>
                        <a href="<?php echo esc_url( $site_url ); ?>" 
                           class="px-2 py-1   rounded-full  <?php echo $is_current ? 'gradient-gold text-primary' : 'text-secondary hover:text-white'; ?>">
                            <?php echo esc_html( $site_label ); ?>
                        </a>
                        <?php
                    }
                    ?>
                </nav>
            <?php endif; ?>

            <!-- Search icon -->
            <div class="nailedit-header-search relative">
                <button
                    type="button"
                    id="nailedit-search-toggle"
                    class="-btn w-9 h-9 rounded-full flex items-center justify-center text-slate-600 hover:border-primary hover:text-primary transition"
                    aria-label="Otsing"
                >
                    <span class="nailedit-icon text-sm"><svg data-v-a8bed08b="" width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0_39_3192)"><path d="M17.4249 16.3951L13.1409 11.9395C14.2424 10.6301 14.8459 8.98262 14.8459 7.26749C14.8459 3.26026 11.5856 0 7.57841 0C3.57117 0 0.310913 3.26026 0.310913 7.26749C0.310913 11.2747 3.57117 14.535 7.57841 14.535C9.08278 14.535 10.5164 14.0812 11.742 13.2199L16.0586 17.7093C16.239 17.8967 16.4817 18 16.7418 18C16.9879 18 17.2214 17.9062 17.3987 17.7355C17.7753 17.3731 17.7873 16.7721 17.4249 16.3951ZM7.57841 1.89587C10.5404 1.89587 12.95 4.30552 12.95 7.26749C12.95 10.2295 10.5404 12.6391 7.57841 12.6391C4.61643 12.6391 2.20678 10.2295 2.20678 7.26749C2.20678 4.30552 4.61643 1.89587 7.57841 1.89587Z" fill="#003B76" style="fill: color(display-p3 0 0.2314 0.4627); fill-opacity: 1;"></path></g><defs><clipPath id="clip0_39_3192"><rect width="18" height="18" fill="white" style="fill: white; fill-opacity: 1;"></rect></clipPath></defs></svg></span>
                </button>
                <div id="nailedit-search-panel" class="hidden fixed md:absolute left-0 md:left-auto right-0 top-[60px] md:top-auto md:mt-2 w-full md:w-72 bg-white border border-slate-200 md:rounded-2xl shadow-lg p-3 z-40">
                    <input
                        type="search"
                        id="nailedit-search-input"
                        class="w-full rounded-full border border-slate-300 px-3 py-1.5 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-secondary"
                        placeholder="<?php echo esc_attr( nailedit_get_t( 'search_products' ) ); ?>"
                        autocomplete="off"
                    >
                    <div id="nailedit-search-results" class=" max-h-80 overflow-y-auto text-sm text-slate-800"></div>
                </div>
            </div>

            <!-- Cart icon -->
            <a
                href="<?php echo esc_url( nailedit_get_url( 'cart' ) ); ?>"
                class="nailedit-icon-btn relative w-9 h-9 rounded-full  flex items-center justify-center text-slate-600 hover:border-primary hover:text-primary transition"
                aria-label="Ostukorv"
                id="nailedit-cart-icon-link"
            >
                <span class="nailedit-icon text-sm">
                    <svg class="nailedit-icon " style="width: 18px; height: 18px;" ><use xlink:href="#cart-svg"></use></svg>
                </span>
                <span id="nailedit-cart-badge" class="hidden  absolute -top-1.5 -right-1.5 min-w-[18px] h-[18px] rounded-full gradient-gold  text-primary text-[10px] font-bold flex items-center justify-center leading-none px-1">0</span>
            </a>

            <!-- User icon with dropdown -->
            <div class="nailedit-user-dropdown relative">
                <button
                    type="button"
                    id="nailedit-user-toggle"
                    class="nailedit-icon-btn w-9 h-9 rounded-full   flex items-center justify-center text-slate-600 hover:border-primary hover:text-primary transition"
                    aria-label="Kasutaja"
                >
                    <span class="nailedit-icon text-sm"><svg   width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0_39_3196)"><path d="M15.364 11.636C14.3837 10.6558 13.217 9.93013 11.9439 9.49085C13.3074 8.55179 14.2031 6.9802 14.2031 5.20312C14.2031 2.33413 11.869 0 9 0C6.131 0 3.79688 2.33413 3.79688 5.20312C3.79688 6.9802 4.69262 8.55179 6.05609 9.49085C4.78308 9.93013 3.61631 10.6558 2.63605 11.636C0.936176 13.3359 0 15.596 0 18H1.40625C1.40625 13.8128 4.81279 10.4062 9 10.4062C13.1872 10.4062 16.5938 13.8128 16.5938 18H18C18 15.596 17.0638 13.3359 15.364 11.636ZM9 9C6.90641 9 5.20312 7.29675 5.20312 5.20312C5.20312 3.1095 6.90641 1.40625 9 1.40625C11.0936 1.40625 12.7969 3.1095 12.7969 5.20312C12.7969 7.29675 11.0936 9 9 9Z" fill="#003B76" style="fill: color(display-p3 0 0.2314 0.4627); fill-opacity: 1;"></path></g><defs><clipPath id="clip0_39_3196"><rect width="18" height="18" fill="white" style="fill: white; fill-opacity: 1;"></rect></clipPath></defs></svg></span>
                </button>
                

                <div id="nailedit-user-dropdown" class="nailedit-user-dropdown-panel hidden absolute right-0 mt-2 w-72 bg-white border shadow-lg rounded-md p-4 z-50">
                    <div id="nailedit-user-menu-wrap" class="nailedit-user-menu-wrap hidden">
                        <?php
                        wp_nav_menu(
                            array(
                                'theme_location' => 'user_menu',
                                'container'      => 'nav',
                                'menu_class'     => 'nailedit-user-menu flex flex-col gap-2',
                                'fallback_cb'    => false,
                            )
                        );
                        ?>
                    </div>
                    <div id="nailedit-login-wrap" class="nailedit-header-login space-y-3">
                            <form id="nailedit-login-form" class="flex flex-col gap-2">
                                <input
                                    class="w-full rounded-full px-4 min-h-[51px] bg-white/20 text-primary placeholder-primary/60 border border-third focus:outline-none focus:ring-2 focus:ring-secondary"
                                    type="email"
                                    name="email"
                                    placeholder="Email"
                                    required
                                >
                                <input
                                    class="w-full rounded-full px-4 min-h-[51px] bg-white/20 text-primary placeholder-primary/60 border border-third focus:outline-none focus:ring-2 focus:ring-secondary"
                                    type="password"
                                    name="password"
                                    placeholder="<?php echo esc_attr( nailedit_get_t( 'password' ) ); ?>"
                                    required
                                >
                                <input type="hidden" name="device_name" value="web">
                                <button
                                    type="submit"
                                    class="w-full rounded-full min-h-[51px] px-4 gradient-secondary text-primary font-medium hover:text-black transition"
                                >
                                    <?php nailedit_t( 'login' ); ?>
                                </button>
                            </form>
                            <a
                                id="nailedit-register-link"
                                href="<?php echo esc_url( nailedit_get_url( 'register' ) ); ?>"
                                class="block w-full rounded-full content-center min-h-[48px] px-4 mt-1 gradient-dark text-secondary text-sm text-center font-medium   transition hover:text-white"
                            >
                                <?php nailedit_t( 'register' ); ?>
                            </a>
                            <div id="nailedit-login-status" style="display:none;" class="text-sm text-primary"></div>
                            <button
                                id="nailedit-logout-btn"
                                style="display:none;"
                                type="button"
                                class="w-full rounded-full min-h-[44px] px-4  gradient-secondary  font-[500] text-primary  text-sm hover:text-black transition"
                            >
                                <?php nailedit_t( 'logout' ); ?>
                            </button>
                            <div id="nailedit-login-error" class="text-sm text-red-600"></div>
                            <div id="nailedit-forgot-wrapper" class="nailedit-forgot-password pt-1 mt-2 space-y-2">
                                <button
                                    type="button"
                                    id="nailedit-forgot-toggle"
                                    class="text-xs text-primary/80 hover:text-secondary underline underline-offset-2"
                                >
                                    <?php nailedit_t( 'forgot_password' ); ?>
                                </button>

                                <form id="nailedit-forgot-form" class="flex flex-col gap-2 mt-1 hidden">
                                    <input
                                        class="w-full rounded-full px-4 min-h-[46px] bg-white/10 text-primary placeholder-primary/60 border border-third focus:outline-none focus:ring-2 focus:ring-secondary"
                                        type="email"
                                        name="email"
                                        placeholder="<?php echo esc_attr( nailedit_get_t( 'email_for_password_reset' ) ); ?>"
                                        required
                                    >
                                    <button
                                        type="submit"
                                        class="w-full rounded-full min-h-[46px] px-4 gradient-dark  text-secondary  text-sm hover:text-white font-[500] transition"
                                    >
                                        <?php nailedit_t( 'send_reset_link' ); ?>
                                    </button>
                                    <button
                                        type="button"
                                        id="nailedit-forgot-back"
                                        class="w-full rounded-full min-h-[40px] px-4 bg-transparent text-xs text-primary/80 underline underline-offset-2 hover:text-secondary"
                                    >
                                        <?php nailedit_t( 'back_to_login' ); ?>
                                    </button>
                                </form>
                                <div id="nailedit-forgot-message" class="text-xs text-primary mt-1"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

                <!-- Mobile hamburger button -->
            <button
                type="button"
                id="nailedit-mobile-menu-toggle"
                class="md:hidden w-9 h-9 flex items-center justify-center text-white"
                aria-label="Menüü"
            >
                <svg id="nailedit-hamburger-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                <svg id="nailedit-close-icon" class="hidden" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        
    </div>
    <div class="gradient-gold h-[4px]">
        
    </div>
</header>

<!-- Mobile menu panel -->
<div id="nailedit-mobile-menu" class="fixed inset-0 z-[29] pointer-events-none">
    <div id="nailedit-mobile-menu-overlay" class="absolute inset-0 bg-black/50 opacity-0 transition-opacity duration-300"></div>
    <div id="nailedit-mobile-menu-panel" class="absolute top-0 right-0  h-full w-[280px] max-w-[80vw] bg-gradient-to-b  to-[#1c0d25] from-[#56265d] backdrop-blur  shadow-2xl translate-x-full transition-transform duration-300 ease-in-out overflow-y-auto">
        <div class="p-6 pt-20">
            <?php
            wp_nav_menu(
                array(
                    'theme_location' => 'primary',
                    'container'      => 'nav',
                    'menu_class'     => 'nailedit-mobile-nav flex flex-col   text-[15px] uppercase tracking-[0.08em] text-white',
                    'fallback_cb'    => false,
                )
            );
            ?>

      
                <?php echo do_shortcode( '[category_list parent_id="111" show_all_link="false"]' ); ?>
            

                <?php if ( is_multisite() ) : ?>
                <nav class="flex items-center gap-2 mt-4 pt-4 border-t border-white/20" aria-label="Language">
                    <?php
                    $current_site_id = get_current_blog_id();
                    $site_languages = array(
                        1 => 'et',
                        3 => 'en',
                        4 => 'ru',
                    );
                    foreach ( $site_languages as $blog_id => $lang ) {
                        $site_url = nailedit_get_translation_url( $lang );
                        $is_current = ( $blog_id === $current_site_id );
                        $site_label = strtoupper( $lang );
                        ?>
                        <a href="<?php echo esc_url( $site_url ); ?>"
                           class="px-3 py-1 rounded-full text-[12px] font-semibold <?php echo $is_current ? 'gradient-gold text-primary' : 'text-secondary hover:text-white'; ?>">
                            <?php echo esc_html( $site_label ); ?>
                        </a>
                        <?php
                    }
                    ?>
                </nav>
                <?php endif; ?>
        </div>
    </div>
</div>

<script>
(function() {
    var toggle = document.getElementById('nailedit-mobile-menu-toggle');
    var menu = document.getElementById('nailedit-mobile-menu');
    var overlay = document.getElementById('nailedit-mobile-menu-overlay');
    var panel = document.getElementById('nailedit-mobile-menu-panel');
    var hamburger = document.getElementById('nailedit-hamburger-icon');
    var closeIcon = document.getElementById('nailedit-close-icon');
    var isOpen = false;

    function openMenu() {
        isOpen = true;
        menu.classList.remove('pointer-events-none');
        menu.classList.add('pointer-events-auto');
        overlay.classList.remove('opacity-0');
        overlay.classList.add('opacity-100');
        panel.classList.remove('translate-x-full');
        panel.classList.add('translate-x-0');
        hamburger.classList.add('hidden');
        closeIcon.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeMenu() {
        isOpen = false;
        overlay.classList.remove('opacity-100');
        overlay.classList.add('opacity-0');
        panel.classList.remove('translate-x-0');
        panel.classList.add('translate-x-full');
        hamburger.classList.remove('hidden');
        closeIcon.classList.add('hidden');
        document.body.style.overflow = '';
        setTimeout(function() {
            if (!isOpen) {
                menu.classList.remove('pointer-events-auto');
                menu.classList.add('pointer-events-none');
            }
        }, 300);
    }

    if (toggle && menu) {
        toggle.addEventListener('click', function() {
            isOpen ? closeMenu() : openMenu();
        });
        overlay.addEventListener('click', closeMenu);
    }
})();
</script>




