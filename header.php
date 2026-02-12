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
    <?php wp_head(); ?>
</head>
<body id="page-<?php the_ID(); ?>" <?php body_class(); ?>>

<header class="nailedit-site-header sticky top-0 z-30 bg-white/90 backdrop-blur border-b border-slate-200">
    <div class="nailedit-header-inner max-w-6xl mx-auto flex items-center justify-between gap-6 px-4 py-3 md:py-4">
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

        <div class="nailedit-header-right flex items-center gap-3 md:gap-4">
            <!-- Search icon -->
            <div class="nailedit-header-search relative">
                <button
                    type="button"
                    id="nailedit-search-toggle"
                    class="-btn w-9 h-9 rounded-full border border-slate-200 flex items-center justify-center text-slate-600 hover:border-primary hover:text-primary transition"
                    aria-label="Otsing"
                >
                    <span class="nailedit-icon text-sm"><svg data-v-a8bed08b="" width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0_39_3192)"><path d="M17.4249 16.3951L13.1409 11.9395C14.2424 10.6301 14.8459 8.98262 14.8459 7.26749C14.8459 3.26026 11.5856 0 7.57841 0C3.57117 0 0.310913 3.26026 0.310913 7.26749C0.310913 11.2747 3.57117 14.535 7.57841 14.535C9.08278 14.535 10.5164 14.0812 11.742 13.2199L16.0586 17.7093C16.239 17.8967 16.4817 18 16.7418 18C16.9879 18 17.2214 17.9062 17.3987 17.7355C17.7753 17.3731 17.7873 16.7721 17.4249 16.3951ZM7.57841 1.89587C10.5404 1.89587 12.95 4.30552 12.95 7.26749C12.95 10.2295 10.5404 12.6391 7.57841 12.6391C4.61643 12.6391 2.20678 10.2295 2.20678 7.26749C2.20678 4.30552 4.61643 1.89587 7.57841 1.89587Z" fill="#003B76" style="fill: color(display-p3 0 0.2314 0.4627); fill-opacity: 1;"></path></g><defs><clipPath id="clip0_39_3192"><rect width="18" height="18" fill="white" style="fill: white; fill-opacity: 1;"></rect></clipPath></defs></svg></span>
                </button>
                <div id="nailedit-search-panel" class="hidden absolute right-0 mt-2 w-72 bg-white border border-slate-200 rounded-2xl shadow-lg p-3 z-40">
                    <input
                        type="search"
                        id="nailedit-search-input"
                        class="w-full rounded-full border border-slate-300 px-3 py-1.5 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-secondary"
                        placeholder="<?php echo esc_attr__( 'Otsi tooteid...', 'nailedit' ); ?>"
                        autocomplete="off"
                    >
                    <div id="nailedit-search-results" class=" max-h-80 overflow-y-auto text-sm text-slate-800"></div>
                </div>
            </div>

            <!-- Cart icon -->
            <a
                href="<?php echo esc_url( home_url( '/ostukorv/' ) ); ?>"
                class="nailedit-icon-btn w-9 h-9 rounded-full border border-slate-200 flex items-center justify-center text-slate-600 hover:border-primary hover:text-primary transition"
                aria-label="Ostukorv"
            >
                <span class="nailedit-icon text-sm">
                    <svg class="nailedit-icon " style="width: 18px; height: 18px;" ><use xlink:href="#cart-svg"></use></svg>
                </span>
            </a>

            <!-- User icon with dropdown -->
            <div class="nailedit-user-dropdown relative">
                <button
                    type="button"
                    id="nailedit-user-toggle"
                    class="nailedit-icon-btn w-9 h-9 rounded-full border border-slate-200 flex items-center justify-center text-slate-600 hover:border-primary hover:text-primary transition"
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
                                    placeholder="Parool"
                                    required
                                >
                                <input type="hidden" name="device_name" value="web">
                                <button
                                    type="submit"
                                    class="w-full rounded-full min-h-[51px] px-4 bg-secondary text-primary font-medium hover:bg-fourth transition"
                                >
                                    <?php esc_html_e( 'Logi sisse', 'nailedit' ); ?>
                                </button>
                            </form>
                            <a
                                id="nailedit-register-link"
                                href="<?php echo esc_url( home_url( '/registreeri-kasutajaks/' ) ); ?>"
                                class="block w-full rounded-full content-center min-h-[48px] px-4 mt-1 bg-primary text-secondary text-sm text-center font-medium hover:bg-secondary transition"
                            >
                                <?php esc_html_e( 'Registreeri', 'nailedit' ); ?>
                            </a>
                            <div id="nailedit-login-status" style="display:none;" class="text-sm text-primary"></div>
                            <button
                                id="nailedit-logout-btn"
                                style="display:none;"
                                type="button"
                                class="w-full rounded-full min-h-[44px] px-4 bg-secondary text-third text-sm hover:bg-fourth transition"
                            >
                                <?php esc_html_e( 'Logi välja', 'nailedit' ); ?>
                            </button>
                            <div id="nailedit-login-error" class="text-sm text-red-600"></div>
                            <div id="nailedit-forgot-wrapper" class="nailedit-forgot-password pt-1 mt-2 space-y-2">
                                <button
                                    type="button"
                                    id="nailedit-forgot-toggle"
                                    class="text-xs text-primary/80 hover:text-secondary underline underline-offset-2"
                                >
                                    <?php esc_html_e( 'Unustasid parooli?', 'nailedit' ); ?>
                                </button>

                                <form id="nailedit-forgot-form" class="flex flex-col gap-2 mt-1 hidden">
                                    <input
                                        class="w-full rounded-full px-4 min-h-[46px] bg-white/10 text-primary placeholder-primary/60 border border-third focus:outline-none focus:ring-2 focus:ring-secondary"
                                        type="email"
                                        name="email"
                                        placeholder="<?php esc_attr_e( 'Email parooli taastamiseks', 'nailedit' ); ?>"
                                        required
                                    >
                                    <button
                                        type="submit"
                                        class="w-full rounded-full min-h-[46px] px-4 bg-third text-primary text-sm hover:bg-secondary transition"
                                    >
                                        <?php esc_html_e( 'Saada taastamise link', 'nailedit' ); ?>
                                    </button>
                                    <button
                                        type="button"
                                        id="nailedit-forgot-back"
                                        class="w-full rounded-full min-h-[40px] px-4 bg-transparent text-xs text-primary/80 underline underline-offset-2 hover:text-secondary"
                                    >
                                        <?php esc_html_e( 'Tagasi sisselogimise juurde', 'nailedit' ); ?>
                                    </button>
                                </form>
                                <div id="nailedit-forgot-message" class="text-xs text-primary mt-1"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</header>
<?php if ( is_front_page() ) : ?>
<div class="mb-[50px]">
<?php echo do_shortcode( '[ew_slider]' ); ?>
</div>
<?php else : ?>
	<?php // get_template_part( 'template-parts/page-header' ); ?>
<?php endif; ?>



