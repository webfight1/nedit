<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$_gf = function_exists( 'get_field' ) ? 'get_field' : null;

$footer_info_title    = $_gf ? $_gf( 'footer_info_title', 'option' ) : '';
$footer_info_links    = array(
    array( 'label' => $_gf ? $_gf( 'footer_info_link_1_label', 'option' ) : '', 'url' => $_gf ? $_gf( 'footer_info_link_1_url', 'option' ) : '' ),
    array( 'label' => $_gf ? $_gf( 'footer_info_link_2_label', 'option' ) : '', 'url' => $_gf ? $_gf( 'footer_info_link_2_url', 'option' ) : '' ),
    array( 'label' => $_gf ? $_gf( 'footer_info_link_3_label', 'option' ) : '', 'url' => $_gf ? $_gf( 'footer_info_link_3_url', 'option' ) : '' ),
);
$footer_contact_title = $_gf ? $_gf( 'footer_contact_title', 'option' ) : '';
$footer_phone         = $_gf ? $_gf( 'footer_phone', 'option' ) : '';
$footer_email         = $_gf ? $_gf( 'footer_email', 'option' ) : '';
$footer_hours         = $_gf ? $_gf( 'footer_hours', 'option' ) : '';
$footer_instagram     = $_gf ? $_gf( 'footer_instagram_url', 'option' ) : '';
$footer_tiktok        = $_gf ? $_gf( 'footer_tiktok_url', 'option' ) : '';
$footer_privacy_label = $_gf ? $_gf( 'footer_privacy_label', 'option' ) : '';
$footer_privacy_url   = $_gf ? $_gf( 'footer_privacy_url', 'option' ) : '';
$footer_terms_label   = $_gf ? $_gf( 'footer_terms_label', 'option' ) : '';
$footer_terms_url     = $_gf ? $_gf( 'footer_terms_url', 'option' ) : '';

$footer_info_fallbacks = array(
    __( 'Tarne', 'nailedit' ),
    __( 'Tagastused', 'nailedit' ),
    __( 'Klienditugi', 'nailedit' ),
);

function nailedit_footer_link( $url, $label, $fallback_label = '' ) {
    $text = $label ? $label : $fallback_label;
    if ( ! $url ) {
        return $text
            ? sprintf( '<span class="block text-slate-500">%s</span>', esc_html( $text ) )
            : '';
    }
    return sprintf(
        '<a href="%s" class="block hover:text-primary">%s</a>',
        esc_url( $url ),
        esc_html( $text )
    );
}
?>
<footer class="site-footer mt-[50px] bg-[#eef1f8] pt-10 pb-6 text-sm text-slate-800">
	<div class="max-w-[1200px] mx-auto px-4">
		<div class="flex flex-col md:flex-row md:items-start md:justify-between gap-8 border-b border-slate-300 pb-8">
			<!-- Logo + payments -->
			<div class="space-y-4 max-w-xs">
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
				<div class="space-y-1">
					<div class="text-[11px] text-slate-500 uppercase"><?php esc_html_e( 'Makseviisid', 'nailedit' ); ?></div>
					<div class="flex items-center gap-3 text-[12px] font-semibold text-slate-700">
						<span class="inline-flex items-center justify-center rounded-[4px] bg-white px-2 py-1 border border-slate-200"><?php esc_html_e( 'VISA', 'nailedit' ); ?></span>
						<span class="inline-flex items-center justify-center rounded-[4px] bg-white px-2 py-1 border border-slate-200"><?php esc_html_e( 'Mastercard', 'nailedit' ); ?></span>
						
					</div>
				</div>
			</div>

			<!-- Navigation -->
			<div class="grid grid-cols-2 gap-8 text-[13px] uppercase tracking-[0.08em]">
				<div class="space-y-2">
					<div class="font-semibold text-slate-700 mb-1">
						<?php echo esc_html( $footer_info_title ? $footer_info_title : __( 'Info', 'nailedit' ) ); ?>
					</div>
					<?php
						foreach ( $footer_info_links as $i => $link ) {
							$fb = isset( $footer_info_fallbacks[ $i ] ) ? $footer_info_fallbacks[ $i ] : '';
							echo nailedit_footer_link( $link['url'], $link['label'], $fb );
						}
					?>
				</div>
			</div>

			<!-- Contacts + social -->
			<div class="space-y-3 max-w-xs">
				<div class="font-semibold uppercase tracking-[0.08em] text-slate-700">
					<?php echo esc_html( $footer_contact_title ? $footer_contact_title : __( 'Kontaktid', 'nailedit' ) ); ?>
				</div>
				<div class="flex items-start gap-3">
					<div class="mt-[2px] text-primary">☎</div>
					<div class="space-y-1 text-[13px]">
						<div class="font-semibold text-slate-900">
							<?php echo esc_html( $footer_phone ? $footer_phone : __( '+372 5555 5555', 'nailedit' ) ); ?>
						</div>
						<div class="text-slate-600">
							<?php echo esc_html( $footer_email ? $footer_email : __( 'info@nailedit.ee', 'nailedit' ) ); ?>
						</div>
						<div class="text-slate-500 text-[12px]">
							<?php echo esc_html( $footer_hours ? $footer_hours : __( 'E–R 09:00 – 19:00', 'nailedit' ) ); ?>
						</div>
					</div>
				</div>
				<div class="flex items-center gap-3 pt-1">
					<?php if ( $footer_instagram ) : ?>
						<a href="<?php echo esc_url( $footer_instagram ); ?>" class="w-8 h-8 rounded-full border border-slate-300 flex items-center justify-center text-slate-700 hover:border-primary hover:text-primary transition" aria-label="Instagram" target="_blank" rel="noopener">
							<span class="text-[14px]"><svg class="nailedit-icon"><use xlink:href="#instagram-svg"></use></svg></span>
						</a>
					<?php endif; ?>
					<?php if ( $footer_tiktok ) : ?>
						<a href="<?php echo esc_url( $footer_tiktok ); ?>" class="w-8 h-8 rounded-full border border-slate-300 flex items-center justify-center text-slate-700 hover:border-primary hover:text-primary transition" aria-label="TikTok" target="_blank" rel="noopener">
							<span class="text-[14px]"><svg class="nailedit-icon"><use xlink:href="#tiktok-svg"></use></svg></span>
						</a>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<!-- Bottom bar -->
		<div class="flex flex-col md:flex-row items-center justify-between gap-3 pt-4 text-[12px] text-slate-600">
			<?php if ( $footer_privacy_url ) : ?>
				<a href="<?php echo esc_url( $footer_privacy_url ); ?>" class="underline underline-offset-2 hover:text-primary">
					<?php echo esc_html( $footer_privacy_label ? $footer_privacy_label : __( 'Privaatsuspoliitika', 'nailedit' ) ); ?>
				</a>
			<?php endif; ?>
			<div class="text-center">
				<?php echo esc_html( date_i18n( 'Y' ) ); ?> &copy; <?php bloginfo( 'name' ); ?>. <?php esc_html_e( 'Kõik õigused kaitstud.', 'nailedit' ); ?>
			</div>
			<?php if ( $footer_terms_url ) : ?>
				<a href="<?php echo esc_url( $footer_terms_url ); ?>" class="underline underline-offset-2 hover:text-primary">
					<?php echo esc_html( $footer_terms_label ? $footer_terms_label : __( 'Müügi tingimused', 'nailedit' ) ); ?>
				</a>
			<?php endif; ?>
		</div>
	</div>
</footer>

<button
	id="nailedit-scroll-top"
	type="button"
	class="fixed bottom-6 right-6 z-40 hidden md:flex items-center justify-center w-12 h-12 rounded-full border border-primary text-primary bg-white shadow-lg hover:bg-primary hover:text-white transition"
	aria-label="<?php echo esc_attr__( 'Tagasi üles', 'nailedit' ); ?>"
>
	<span class="text-xl leading-none">
		&uarr;
	</span>
</button>
<?php include __DIR__ . '/icons.php'; ?>

<?php wp_footer(); ?>
</body>
</html>
