<?php
/**
 * Front Page Template
 *
 * Used automatically for the site front page if it exists.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<main class="site-main nailedit-front-page   overflow-hidden">
	<div class="max-w-[1200px] mx-auto px-4">
		<?php
		if ( have_posts() ) :
			while ( have_posts() ) :
				the_post();
				the_content();


			endwhile;
		endif;
		?>

		<!-- <?php
		// Display parent categories only
		if ( function_exists( 'nailedit_get_local_api_base' ) ) {
			$base = nailedit_get_local_api_base();
		} else {
			$base = trailingslashit( get_option( 'las_api_base_url', 'http://localhost:8083/api/' ) );
		}

		$categories_url = add_query_arg(
			array(
				'page'  => 1,
				'limit' => 100,
			),
			$base . 'v1/categories'
		);

		$cat_response = wp_remote_get(
			$categories_url,
			array( 'timeout' => 15 )
		);

		if ( ! is_wp_error( $cat_response ) ) {
			$cat_body = wp_remote_retrieve_body( $cat_response );
			$cat_data = json_decode( $cat_body, true );

			$parent_categories = array();

			if ( isset( $cat_data['data'] ) && is_array( $cat_data['data'] ) ) {
				foreach ( $cat_data['data'] as $cat ) {
					// Only include categories with no parent (parent_id is null, 0, or not set)
					if ( ! isset( $cat['parent_id'] ) || $cat['parent_id'] === null || (int) $cat['parent_id'] === 0 ) {
						$parent_categories[] = $cat;
					}
				}
			}

			if ( ! empty( $parent_categories ) ) :
				?>
				<section class="nailedit-categories mt-12">
					<h2 class="text-2xl font-bold text-primary mb-6 text-center"><?php esc_html_e( 'Kategooriad', 'nailedit' ); ?></h2>
					<div class="nailedit-products-grid sm:grid-cols-2 lg:grid-cols-3">
						<?php foreach ( $parent_categories as $category ) : ?>
							<?php
							$cat_name = isset( $category['name'] ) ? $category['name'] : '';
							$cat_slug = isset( $category['slug'] ) ? $category['slug'] : '';

							if ( ! $cat_slug ) {
								continue;
							}

							$cat_url = home_url( '/category/' . sanitize_title( $cat_slug ) . '/' );
							?>
							<article class="rounded-24 bg-white w-full relative mb-[40px] shadow-xl hover:shadow-2xl transition-shadow">
								<a href="<?php echo esc_url( $cat_url ); ?>" class="block h-full p-[20px] text-center flex flex-col items-center justify-center gap-3 min-h-[200px]">
									<h3 class="font-bold text-[18px] text-primary line-clamp-2"><?php echo esc_html( $cat_name ); ?></h3>
									<span class="inline-flex items-center justify-center rounded-full bg-secondary text-primary font-semibold text-[13px] px-5 py-2 hover:bg-fourth transition"><?php esc_html_e( 'Vaata kategooriat', 'nailedit' ); ?></span>
								</a>
							</article>
						<?php endforeach; ?>
					</div>
				</section>
				<?php
			endif;
		}
		?> -->
	</div>
</main>

<?php
get_footer();
