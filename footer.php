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

$footer_info_links = array_filter( $footer_info_links, function( $link ) {
    return ! empty( $link['label'] ) || ! empty( $link['url'] );
} );

function nailedit_footer_link( $url, $label ) {
    if ( empty( $label ) ) {
        return '';
    }
    if ( ! $url ) {
        return sprintf( '<span class="block text-white">%s</span>', esc_html( $label ) );
    }
    return sprintf(
        '<a href="%s" class="block text-secondary hover:text-white">%s</a>',
        esc_url( $url ),
        esc_html( $label )
    );
}
?>
<footer class="site-footer mt-[50px]  pt-10 pb-6 text-sm text-slate-800 bg-gradient-to-b  from-[#1c0d25] to-[#56265d] backdrop-blur"  
">
	<div class="max-w-[1200px] mx-auto px-4">
		<div class="flex flex-col md:flex-row  md:items-start md:justify-between gap-8 border-b border-secondary pb-8">
			<!-- Logo + payments -->
			<div class="space-y-4 max-w-xs">
				  <div class="nailedit-header-left flex items-center gap-3">
            <div class="nailedit-logo flex items-center gap-2 max-w-[140px]">
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
					<br>
					<div class="flex items-center gap-3 text-[12px] font-semibold text-primary ">
						<span class="inline-flex items-center justify-center rounded-[4px] bg-white px-2 py-1 border border-slate-200"><?php esc_html_e( 'VISA', 'nailedit' ); ?></span>
						<span class="inline-flex items-center justify-center rounded-[4px] bg-white px-2 py-1 border border-slate-200"><?php esc_html_e( 'Mastercard', 'nailedit' ); ?></span>
						
					</div>
				</div>
			</div>

			<?php if ( ! empty( $footer_info_links ) || $footer_info_title ) : ?>
			<!-- Navigation -->
			<div class="grid grid-cols-2 gap-8 text-[13px] uppercase tracking-[0.08em]">
				<div class="space-y-2">
					<?php if ( $footer_info_title ) : ?>
						<div class="font-semibold text-secondary mb-1">
							<?php echo esc_html( $footer_info_title ); ?>
						</div>
					<?php endif; ?>
					<?php
						foreach ( $footer_info_links as $link ) {
							echo nailedit_footer_link( $link['url'], $link['label'] );
						}
					?>
				</div>
			</div>
			<?php endif; ?>

			<?php $has_contact = $footer_contact_title || $footer_phone || $footer_email || $footer_hours || $footer_instagram || $footer_tiktok; ?>
			<?php if ( $has_contact ) : ?>
			<!-- Contacts + social -->
			<div class="space-y-3 max-w-xs">
				<?php if ( $footer_contact_title ) : ?>
					<div class="font-semibold uppercase tracking-[0.08em] text-secondary">
						<?php echo esc_html( $footer_contact_title ); ?>
					</div>
				<?php endif; ?>
				<?php if ( $footer_phone || $footer_email || $footer_hours ) : ?>
					<div class="flex items-start gap-3">
						<div class="mt-[2px] text-primary">☎</div>
						<div class="space-y-1 text-[13px]">
							<?php if ( $footer_phone ) : ?>
								<div class="font-semibold text-secondary"	><?php echo esc_html( $footer_phone ); ?></div>
							<?php endif; ?>
							<?php if ( $footer_email ) : ?>
								<div class="text-secondary"><?php echo esc_html( $footer_email ); ?></div>
							<?php endif; ?>
							<?php if ( $footer_hours ) : ?>
								<div class="text-slate-500 text-[12px]"><?php echo esc_html( $footer_hours ); ?></div>
							<?php endif; ?>
						</div>
					</div>
				<?php endif; ?>
				<?php if ( $footer_instagram || $footer_tiktok ) : ?>
					<div class="flex items-center gap-3 pt-1">
						<?php if ( $footer_instagram ) : ?>
							<a href="<?php echo esc_url( $footer_instagram ); ?>" class="w-8 h-8 rounded-full border  border-secondary flex items-center justify-center  text-secondary hover:border-secondary hover:text-white  transition" aria-label="Instagram" target="_blank" rel="noopener">
								<span class="text-[14px]"><svg class="nailedit-icon"><use xlink:href="#instagram-svg"></use></svg></span>
							</a>
						<?php endif; ?>
						<?php if ( $footer_tiktok ) : ?>
							<a href="<?php echo esc_url( $footer_tiktok ); ?>" class="w-8 h-8 rounded-full border border-secondary flex items-center justify-center text-white  hover:border-secondary hover:text-secondary transition" aria-label="TikTok" target="_blank" rel="noopener">
								<span class="text-[14px]"><svg class="nailedit-icon"><use xlink:href="#tiktok-svg"></use></svg></span>
							</a>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
			<?php endif; ?>
		</div>

		<!-- Bottom bar -->
		<div class="flex flex-col md:flex-row items-center justify-between gap-3 pt-4 text-[12px] text-secondary">
			<?php if ( $footer_privacy_url ) : ?>
				<a href="<?php echo esc_url( $footer_privacy_url ); ?>" class="underline underline-offset-2 hover:text-white">
					<?php echo esc_html( $footer_privacy_label ? $footer_privacy_label : __( 'Privaatsuspoliitika', 'nailedit' ) ); ?>
				</a>
			<?php endif; ?>
			<div class="text-center text-secondary">
				<?php echo esc_html( date_i18n( 'Y' ) ); ?> &copy; <?php bloginfo( 'name' ); ?>. <?php esc_html_e( 'Kõik õigused kaitstud.', 'nailedit' ); ?>
			</div>
			<?php if ( $footer_terms_url ) : ?>
				<a href="<?php echo esc_url( $footer_terms_url ); ?>" class="underline underline-offset-2 hover:text-white">
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

<script>
(function() {
    function updateCartBadge() {
        var badge = document.getElementById('nailedit-cart-badge');
        if (!badge) return;

        var formData = new FormData();
        formData.append('action', 'nailedit_get_cart');

        var authToken = localStorage.getItem('bagisto_auth_token');
        var cartToken = localStorage.getItem('bagisto_guest_cart_token');
        var storedCookie = localStorage.getItem('bagisto_cart_cookie');

        if (storedCookie) formData.append('stored_cookie', storedCookie);
        if (authToken) {
            formData.append('auth_token', authToken);
        } else if (cartToken) {
            formData.append('cart_token', cartToken);
        }

        fetch('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!data || !data.success) {
                badge.classList.add('hidden');
                return;
            }
            var payload = data.data || data;
            var cart = (payload && payload.data && payload.data.cart) ? payload.data.cart : (payload.data || payload);
            var items = Array.isArray(cart.items) ? cart.items : [];
            var count = 0;
            for (var i = 0; i < items.length; i++) {
                count += parseInt(items[i].quantity || 1, 10);
            }
            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : count;
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        })
        .catch(function() {
            badge.classList.add('hidden');
        });
    }

    window.naileditUpdateCartBadge = updateCartBadge;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', updateCartBadge);
    } else {
        updateCartBadge();
    }
})();
</script>

<?php wp_footer(); ?>
</body>
</html>
