<?php
/**
 * Template Name: Product Detail
 * Description: Displays single product from Bagisto API
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

$product_key = isset( $_GET['product'] ) ? sanitize_text_field( $_GET['product'] ) : '';

if ( empty( $product_key ) ) {
    echo '<main class="site-main"><p>' . esc_html__( 'No product specified.', 'nailedit' ) . '</p></main>';
    get_footer();
    exit;
}

// Get API base URL
if ( class_exists( 'Local_API_Shortcode_Plugin' ) ) {
    $base = get_option( Local_API_Shortcode_Plugin::OPTION_KEY, Local_API_Shortcode_Plugin::DEFAULT_BASE );
} else {
    $base = get_option( 'las_api_base_url', 'http://localhost:8083/api/' );
}
$base = trailingslashit( $base );

$response = wp_remote_get(
    $base . 'products/' . $product_key,
    array( 'timeout' => 15 )
);

if ( is_wp_error( $response ) ) {
    echo '<main class="site-main"><div class="nailedit-error">' . esc_html( $response->get_error_message() ) . '</div></main>';
    get_footer();
    exit;
}

$body = wp_remote_retrieve_body( $response );
$product = json_decode( $body, true );

if ( ! is_array( $product ) || empty( $product['data'] ) ) {
    echo '<main class="site-main"><div class="nailedit-error">' . esc_html__( 'Product not found3.', 'nailedit' ) . '</div></main>';
    get_footer();
    exit;
}

$data = $product['data'];
$name = $data['name'] ?? __( 'Unnamed Product', 'nailedit' );
$description = $data['description'] ?? '';
$price = $data['min_price'] ?? ( $data['prices']['final']['formatted_price'] ?? '' );
$sku = $data['sku'] ?? '';

// Extract images
$images = array();
if ( ! empty( $data['base_image'] ) && is_array( $data['base_image'] ) ) {
    $images[] = $data['base_image']['large_image_url'] ?? $data['base_image']['medium_image_url'] ?? '';
}
if ( ! empty( $data['images'] ) && is_array( $data['images'] ) ) {
    foreach ( $data['images'] as $img ) {
        if ( is_array( $img ) ) {
            $url = $img['large_image_url'] ?? $img['medium_image_url'] ?? '';
            if ( $url && ! in_array( $url, $images, true ) ) {
                $images[] = $url;
            }
        }
    }
}
$images = array_filter( $images );
?>

<main class="site-main nailedit-product-detail">
    <div class="nailedit-product-container">
        <div class="nailedit-product-gallery">
            <?php if ( ! empty( $images ) ) : ?>
                <div class="nailedit-gallery-main">
                    <img src="<?php echo esc_url( $images[0] ); ?>" alt="<?php echo esc_attr( $name ); ?>" id="nailedit-main-image">
                </div>
                <?php if ( count( $images ) > 1 ) : ?>
                    <div class="nailedit-gallery-thumbs">
                        <?php foreach ( $images as $idx => $img ) : ?>
                            <img src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $name ); ?>" class="nailedit-thumb" data-index="<?php echo (int) $idx; ?>">
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php else : ?>
                <div class="nailedit-no-image"><?php esc_html_e( 'No image available', 'nailedit' ); ?></div>
            <?php endif; ?>
        </div>

        <div class="nailedit-product-info">
            <h1 class="nailedit-product-name"><?php echo esc_html( $name ); ?></h1>
            <?php if ( $sku ) : ?>
                <p class="nailedit-product-sku"><?php echo esc_html__( 'SKU:', 'nailedit' ) . ' ' . esc_html( $sku ); ?></p>
            <?php endif; ?>
            <?php if ( $price ) : ?>
                <p class="nailedit-product-price-large"><?php echo esc_html( $price ); ?></p>
            <?php endif; ?>
            <?php if ( $description ) : ?>
                <div class="nailedit-product-description">
                    <?php echo wp_kses_post( wpautop( $description ) ); ?>
                </div>
            <?php endif; ?>
            <a href="javascript:history.back()" class="nailedit-back-btn"><?php esc_html_e( '← Back to products', 'nailedit' ); ?></a>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const thumbs = document.querySelectorAll('.nailedit-thumb');
    const mainImage = document.getElementById('nailedit-main-image');
    
    thumbs.forEach(function(thumb) {
        thumb.addEventListener('click', function() {
            mainImage.src = this.src;
            thumbs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
        });
    });
    
    if (thumbs.length > 0) {
        thumbs[0].classList.add('active');
    }
});
</script>

<?php
get_footer();
