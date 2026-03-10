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
            <p class="text-sm text-slate-700 mb-2"><?php nailedit_t( 'order_reference' ); ?></p>
            <p id="nailedit-thankyou-order-id" class="text-2xl font-semibold text-primary mb-4">&mdash;</p>
            <p class="text-xs text-slate-500"><?php nailedit_t( 'confirmation_email_sent' ); ?></p>
            <div id="nailedit-thankyou-details" class="mt-4 text-sm text-slate-600"></div>
        </section>
    </div>
</main>


<script>
(function() {
  const ajaxUrl = '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>';
  const strings = {
    loading: '<?php echo esc_js( nailedit_get_t( 'loading_order' ) ); ?>',
    notFound: '<?php echo esc_js( nailedit_get_t( 'order_not_found' ) ); ?>',
    paymentNotApproved: '<?php echo esc_js( nailedit_get_t( 'payment_not_approved' ) ); ?>',
    processingError: '<?php echo esc_js( nailedit_get_t( 'error_processing_payment' ) ); ?>',
    fallbackInfo: '<?php echo esc_js( nailedit_get_t( 'order_received_email_info' ) ); ?>',
  };

  function clearStorage() {
    try {
      localStorage.removeItem('bagisto_guest_cart_token');
      localStorage.removeItem('nailedit_selected_shipping_method');
      localStorage.removeItem('nailedit_selected_payment_method');
      localStorage.removeItem('nailedit_omniva_location_id');
      localStorage.removeItem('nailedit_smartpost_location_id');
    } catch (e) {}
  }

  function setOrderId(el, value) {
    el.textContent = value || strings.notFound;
  }

  function renderDetails(detailsEl, html) {
    if (detailsEl) {
      detailsEl.innerHTML = html || '';
    }
  }

  function renderFallback(detailsEl, estoData) {
    const ref = estoData?.reference || estoData?.merchant_reference || strings.notFound;
    const amount = (estoData?.amount != null ? estoData.amount : '') + (estoData?.currency ? ' ' + estoData.currency : '');
    const html = `
      <div class="font-semibold text-slate-700 mb-1"><?php echo esc_js( nailedit_get_t( 'order_reference' ) ); ?>: ${ref}</div>
      <div><?php echo esc_js( nailedit_get_t( 'paid_amount' ) ); ?>: ${amount || '<?php echo esc_js( nailedit_get_t( 'unknown' ) ); ?>'}</div>
      <div class="mt-2 text-slate-500">${strings.fallbackInfo}</div>
    `;
    renderDetails(detailsEl, html);
  }

  async function fetchOrder(reference) {
    const formData = new FormData();
    formData.append('action', 'nailedit_get_order_by_reference');
    formData.append('reference', reference);

    const response = await fetch(ajaxUrl, {
      method: 'POST',
      body: formData,
      credentials: 'same-origin',
    });

    return response.json();
  }

  function showStoredOrder(el, detailsEl) {
    clearStorage();
    let incrementId = '';
    let amountLine = '';
    try {
      const raw = localStorage.getItem('nailedit_last_order');
      if (raw) {
        const parsed = JSON.parse(raw);
        if (parsed && parsed.increment_id) {
          incrementId = parsed.increment_id;
        } else if (parsed && parsed.id) {
          incrementId = parsed.id;
        }
        if (parsed && parsed.grand_total) {
          const currency = parsed.base_currency_code || parsed.order_currency_code || '';
          amountLine = `<?php echo esc_js( nailedit_get_t( 'paid_amount' ) ); ?>: ${parsed.grand_total} ${currency}`;
        }
      }
    } catch (e) {}

    setOrderId(el, incrementId || strings.notFound);
    if (amountLine) {
      renderDetails(detailsEl, `<div>${amountLine}</div>`);
    }
  }

  document.addEventListener('DOMContentLoaded', async function () {
    const idEl = document.getElementById('nailedit-thankyou-order-id');
    const detailsEl = document.getElementById('nailedit-thankyou-details');
    if (!idEl) return;

    const urlParams = new URLSearchParams(window.location.search);
    const estoJson = urlParams.get('json');

    if (estoJson) {
      try {
        const estoData = JSON.parse(decodeURIComponent(estoJson));
        const reference = estoData.reference || estoData.merchant_reference || '';

        if (estoData.status === 'APPROVED' && reference) {
          setOrderId(idEl, strings.loading);
          try {
            const result = await fetchOrder(reference);
            console.log('Order lookup result:', result);

            if (result.success && result.data && result.data.order) {
              const order = result.data.order;
              const orderId = order.increment_id || order.order_id || order.id || reference;
              const amount = order.amount || order.grand_total || estoData.amount;
              const currency = order.currency || order.currency_code || estoData.currency || '';
              const status = (order.status || order.order_status || '').toString().toUpperCase();

              setOrderId(idEl, orderId);
              renderDetails(detailsEl, `
                <div class="mb-1 font-semibold text-slate-700 hidden"><?php echo esc_js( nailedit_get_t( 'payment_status' ) ); ?>: ${status || '<?php echo esc_js( nailedit_get_t( 'unknown' ) ); ?>'}</div>
                <div><?php echo esc_js( nailedit_get_t( 'paid_amount' ) ); ?>: ${amount} ${currency}</div>
              `);
              localStorage.setItem('nailedit_last_order', JSON.stringify(order));
              clearStorage();
              return;
            }

            console.warn('Order not returned, falling back to Esto data');
            setOrderId(idEl, strings.notFound);
            renderFallback(detailsEl, estoData);
          } catch (fetchError) {
            console.error('Order lookup failed:', fetchError);
            setOrderId(idEl, strings.notFound);
            renderFallback(detailsEl, estoData);
          }
        } else {
          setOrderId(idEl, strings.paymentNotApproved);
          renderFallback(detailsEl, estoData);
        }
      } catch (error) {
        console.error('Error processing Esto callback:', error);
        setOrderId(idEl, strings.processingError);
      }
      return;
    }

    showStoredOrder(idEl, detailsEl);
  });
})();
</script>

<?php
get_footer();
