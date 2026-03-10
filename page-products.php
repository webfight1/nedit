<?php
/**
 * Template Name: Products Page
 * Description: Displays all products from Bagisto API with pagination
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<?php  // get_template_part( 'template-parts/page-header' ); ?>

<?php
// Get API base URL
if ( class_exists( 'Local_API_Shortcode_Plugin' ) ) {
    $base = get_option( Local_API_Shortcode_Plugin::OPTION_KEY, Local_API_Shortcode_Plugin::DEFAULT_BASE );
} else {
    $base = get_option( 'las_api_base_url', 'http://localhost:8083/api/' );
}
$base = trailingslashit( $base );

// Get current page from URL
$current_page = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
$per_page = 12;

// Build API request URL (Bagisto v1 API)
$api_url = add_query_arg(
    array(
        'page'  => $current_page,
        'limit' => $per_page,
    ),
    $base . 'v1/products'
);

$response = wp_remote_get(
    $api_url,
    array( 'timeout' => 15 )
);

if ( is_wp_error( $response ) ) {
    ?>
    <main class="site-main max-w-[1200px] mx-auto">
        <div class="nailedit-error">
            <?php echo esc_html( $response->get_error_message() ); ?>
        </div>
    </main>
    <?php
    get_footer();
    exit;
}

$body = wp_remote_retrieve_body( $response );
$data = json_decode( $body, true );

if ( ! is_array( $data ) ) {
    ?>
    <main class="site-main max-w-[1200px] mx-auto">
        <div class="nailedit-error">
            <?php echo esc_html( nailedit_get_t( 'unexpected_api_response' ) ); ?>
        </div>
    </main>
    <?php
    get_footer();
    exit;
}

// Extract products
$products = array();
if ( isset( $data['data'] ) && is_array( $data['data'] ) ) {
    $products = $data['data'];
}

// Extract pagination meta
$total_pages = 1;
$total_items = 0;
if ( isset( $data['meta'] ) ) {
    $total_pages = $data['meta']['last_page'] ?? 1;
    $total_items = $data['meta']['total'] ?? 0;
}
?>

<main class="site-main nailedit-products-page max-w-[1200px] mx-auto">
   

    <?php if ( empty( $products ) ) : ?>
        <div class="nailedit-no-products">
            <p><?php echo esc_html( nailedit_get_t( 'no_products_found' ) ); ?></p>
        </div>
    <?php else : ?>
        <?php $nailedit_products_grid_classes = 'nailedit-products-grid'; ?>
        <?php if ( count( $products ) > 1 ) { $nailedit_products_grid_classes .= ' columns-4'; } ?>
        <div class="<?php echo esc_attr( $nailedit_products_grid_classes ); ?>">
            <?php foreach ( $products as $product ) : ?>
                <?php
                $title = $product['name'] ?? nailedit_get_t( 'unnamed_product' );
                // Hind tuleb API-st kujul "price" => "107.0000"; kasuta seda esmalt
                $raw_price = $product['price'] ?? ( $product['min_price'] ?? ( $product['prices']['final']['price'] ?? '' ) );
                // Vorminda lihtsamaks kuvatavaks kujuks (eemalda tühjad nullid)
                if ( is_numeric( $raw_price ) ) {
                    $price = number_format( (float) $raw_price, 2, ',', ' ' );
                } else {
                    $price = $raw_price;
                }
                $product_id = $product['id'] ?? 0;
                $url_key   = $product['url_key'] ?? '';
                $description = $product['description'] ?? '';
                $description = wp_strip_all_tags( $description );
                $description = wp_trim_words( $description, 20 );

                // Extract image
                $image = '';
                if ( ! empty( $product['base_image'] ) && is_array( $product['base_image'] ) ) {
                    $image = $product['base_image']['medium_image_url'] ?? $product['base_image']['small_image_url'] ?? '';
                }
                if ( ! $image && ! empty( $product['images'] ) && is_array( $product['images'] ) ) {
                    $first = $product['images'][0];
                    if ( is_array( $first ) ) {
                        $image = $first['medium_image_url'] ?? $first['small_image_url'] ?? '';
                    }
                }

                if ( ! empty( $url_key ) ) {
                    $product_url = home_url( '/product/' . sanitize_title( $url_key ) . '/' );
                } elseif ( $product_id ) {
                    $product_url = home_url( '/product/' . absint( $product_id ) . '/' );
                } else {
                    $product_url = '#';
                }
                ?>
                <article class="rounded-24  lg:bg-white w-full relative mb-[40px] shadow-xl hover:shadow-2xl transition-shadow">
                    <a href="<?php echo esc_url( $product_url ); ?>" class="nailedit-product-link ">
                        <?php if ( $image ) : ?>
                            <div class="nailedit-product-thumb rounded-24 overflow-hidden" style="border-bottom-left-radius: 0px !important;border-bottom-right-radius: 0px !important;">
                                <img src="<?php echo esc_url( nailedit_fix_image_url( $image ) ); ?>" alt="<?php echo esc_attr( $title ); ?>">
                            </div>
                        <?php endif; ?>
                        <div class="p-[15px] pb-[30px] text-center text-[14px] flex flex-wrap flex-col gap-[10px] justify-center">
                            <h3 class="font-bold text-[14px] text-primary"><?php echo esc_html( $title ); ?></h3>
                            <?php if ( $price ) : ?>
                                <p class="font-bold text-secondary"><?php echo esc_html( $price ); ?></p>
                            <?php endif; ?>
                            <button type="button" class="mt-2 absolute bottom-[-20px] left-0 right-0 max-w-[130px] mx-auto inline-flex items-center justify-center rounded-full bg-secondary text-primary font-semibold text-[13px] px-5 py-2 hover:bg-fourth transition">
                                <?php echo esc_html( nailedit_get_t( 'buy' ) ); ?></button>
                        </div>
                    </a>
                </article>
            <?php endforeach; ?>
        </div>

        <?php if ( $total_pages > 1 ) : ?>
            <nav class="nailedit-pagination">
                <?php
                $base_url = get_permalink();
                
                // Previous button
                if ( $current_page > 1 ) {
                    $prev_url = add_query_arg( 'paged', $current_page - 1, $base_url );
                    echo '<a href="' . esc_url( $prev_url ) . '" class="nailedit-page-link nailedit-prev">&laquo; ' . esc_html( nailedit_get_t( 'previous' ) ) . '</a>';
                }

                // Page numbers
                echo '<div class="nailedit-page-numbers">';
                for ( $i = 1; $i <= $total_pages; $i++ ) {
                    $page_url = add_query_arg( 'paged', $i, $base_url );
                    $active_class = ( $i === $current_page ) ? ' active' : '';
                    echo '<a href="' . esc_url( $page_url ) . '" class="nailedit-page-link' . esc_attr( $active_class ) . '">' . (int) $i . '</a>';
                }
                echo '</div>';

                // Next button
                if ( $current_page < $total_pages ) {
                    $next_url = add_query_arg( 'paged', $current_page + 1, $base_url );
                    echo '<a href="' . esc_url( $next_url ) . '" class="nailedit-page-link nailedit-next">' . esc_html( nailedit_get_t( 'next' ) ) . ' &raquo;</a>';
                }
                ?>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</main>

<?php
get_footer();
