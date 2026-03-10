<?php
/**
 * Template part for displaying page header with image
 *
 * @package NailedIt
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Don't show page header on single product pages
global $template;
$current_template = $template ? basename( $template ) : '';

// Debug output
echo '<!-- DEBUG page-header.php: current_template = ' . esc_html( $current_template ) . ' -->';
echo '<!-- DEBUG page-header.php: is_singular(product) = ' . ( is_singular( 'product' ) ? 'yes' : 'no' ) . ' -->';

if ( $current_template === 'single-product.php' ) {
	echo '<!-- DEBUG page-header.php: Blocking header for single-product.php -->';
	return;
}

$nailedit_header_image = get_theme_mod( 'nailedit_global_header_image' );
$nailedit_header_title = '';



global $nailedit_header_title_override;
if ( ! empty( $nailedit_header_title_override ) ) {
	$nailedit_header_title = $nailedit_header_title_override;
} elseif ( is_page_template( 'product-single.php' ) ) {
	$nailedit_header_title = get_the_title();
} elseif ( is_singular() ) {
	$nailedit_header_title = get_the_title();
} elseif ( is_archive() ) {
	$nailedit_header_title = get_the_archive_title();
} elseif ( is_search() ) {
	$nailedit_header_title = sprintf( __( 'Otsing: %s', 'nailedit' ), get_search_query() );
} elseif ( is_404() ) {
	$nailedit_header_title = __( 'Lehte ei leitud', 'nailedit' );
} else {
	$nailedit_header_title = get_bloginfo( 'name' );
}
?>

<?php
global $nailedit_breadcrumb;
$has_breadcrumb = ! empty( $nailedit_breadcrumb ) && is_array( $nailedit_breadcrumb );
?>
<div class="mb-[50px] h-[150px] overflow-hidden relative flex items-center justify-center">
	<?php if ( $nailedit_header_image ) : ?>
		<img src="<?php echo esc_url( $nailedit_header_image ); ?>" alt="" class="absolute inset-0 w-full h-full object-cover" />
		<div class="relative z-10 px-4 flex flex-col items-center gap-2">
				<?php if ( $has_breadcrumb ) : ?>
				<nav class="text-[13px] text-slate-500 "   aria-label="Breadcrumb">
					<ol class="flex items-center gap-1 flex-wrap justify-center">
						<?php foreach ( $nailedit_breadcrumb as $i => $crumb ) : ?>
							<li<?php echo $i > 0 ? ' class="before:content-[\'/\'] before:mx-1 before:text-slate-400"' : ''; ?>>
								<?php if ( ! empty( $crumb['url'] ) ) : ?>
									<a href="<?php echo esc_url( $crumb['url'] ); ?>" class="hover:text-primary transition"><?php echo esc_html( $crumb['label'] ); ?></a>
								<?php else : ?>
									<span class="text-slate-700 font-medium"><?php echo esc_html( $crumb['label'] ); ?></span>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ol>
				</nav>
			<?php endif; ?>
			<h1 class="text-[22px] md:text-[26px] font-semibold  font-nailedit text-primary text-center drop-shadow h1-placeholder">
			
				<?php echo esc_html( $nailedit_header_title ); ?>
			</h1>
			
		</div>
	<?php else : ?>
		<div class="absolute inset-0 w-full h-full bg-slate-100"></div>
		<div class="relative z-10 px-4 flex flex-col items-center gap-2">
			<h1 class="text-[22px] md:text-[26px] font-semibold font-nailedit text-primary text-center">
				<?php echo esc_html( $nailedit_header_title ); ?>
			</h1>
			<?php if ( $has_breadcrumb ) : ?>
				<nav class="text-[13px] text-slate-500" aria-label="Breadcrumb">
					<ol class="flex items-center gap-1 flex-wrap justify-center">
						<?php foreach ( $nailedit_breadcrumb as $i => $crumb ) : ?>
							<li<?php echo $i > 0 ? ' class="before:content-[\'/\'] before:mx-1 before:text-slate-400"' : ''; ?>>
								<?php if ( ! empty( $crumb['url'] ) ) : ?>
									<a href="<?php echo esc_url( $crumb['url'] ); ?>" class="hover:text-primary transition"><?php echo esc_html( $crumb['label'] ); ?></a>
								<?php else : ?>
									<span class="text-slate-700 font-medium"><?php echo esc_html( $crumb['label'] ); ?></span>
								<?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ol>
				</nav>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</div>
