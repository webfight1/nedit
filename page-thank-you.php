<?php
/**
 * Template Name: Thank You Page
 * Description: Shows order confirmation after checkout.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<?php get_template_part( 'template-parts/page-header' ); ?>

<main class="site-main nailedit-thankyou-page py-10">
    <div class="max-w-[1200px] mx-auto px-4 text-center">

        <section class="bg-white/80 rounded-3xl shadow-lg px-6 py-8 md:px-10 md:py-10 inline-block text-left">
            <p class="text-sm text-slate-700 mb-2"><?php esc_html_e( 'Your order number is', 'nailedit' ); ?></p>
            <p id="nailedit-thankyou-order-id" class="text-2xl font-semibold text-primary mb-4">&mdash;</p>
            <p class="text-xs text-slate-500"><?php esc_html_e( 'Kinnitusmeil tellimuse andmetega on sulle saadetud (kui see on kohaldatav).', 'nailedit' ); ?></p>
        </section>
    </div>
</main>

<script>
(function() {
  document.addEventListener('DOMContentLoaded', function () {
    var el = document.getElementById('nailedit-thankyou-order-id');
    if (!el) return;

    try {
      localStorage.removeItem('bagisto_guest_cart_token');
      localStorage.removeItem('nailedit_selected_shipping_method');
      localStorage.removeItem('nailedit_selected_payment_method');
      localStorage.removeItem('nailedit_omniva_location_id');
    } catch (e) {}

    var incrementId = '';
    try {
      var raw = localStorage.getItem('nailedit_last_order');
      if (raw) {
        var parsed = JSON.parse(raw);
        if (parsed && parsed.increment_id) {
          incrementId = parsed.increment_id;
        } else if (parsed && parsed.id) {
          incrementId = parsed.id;
        }
      }
    } catch (e) {}

    if (incrementId) {
      el.textContent = incrementId;
    } else {
      el.textContent = '<?php echo esc_js( __( 'Unknown', 'nailedit' ) ); ?>';
    }
  });
})();
</script>

<?php
get_footer();
