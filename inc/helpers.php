<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Helper functions for nailedit theme
 * 
 * Note: nailedit_fix_image_url() function is already declared in functions.php
 * to avoid redeclaration error.
 */

/**
 * Get language-specific URL from translation config
 * 
 * @param string $key URL key (e.g., 'cart', 'checkout', 'thank_you')
 * @return string Full URL with home_url()
 */
function nailedit_get_url( $key ) {
    static $translations = null;
    
    if ( $translations === null ) {
        $lang = nailedit_get_current_lang();
        $file = get_template_directory() . '/inc/translations/' . $lang . '.php';
        
        if ( file_exists( $file ) ) {
            $translations = include $file;
        } else {
            $translations = array();
        }
    }
    
    // Get URL from _urls array
    if ( isset( $translations['_urls'][ $key ] ) ) {
        return home_url( $translations['_urls'][ $key ] );
    }
    
    // Fallback to key as path
    return home_url( '/' . $key . '/' );
}
