<?php
/**
 * Template Name: Checkout Page
 * Description: Bagisto checkout address step via AJAX (save-address)
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<?php get_template_part( 'template-parts/page-header' ); ?>

<main class="site-main nailedit-checkout-page py-10">
    <div class="max-w-[1200px] mx-auto px-4">
       

        <div id="nailedit-checkout-require-cart" class="hidden mb-4 text-center text-sm text-red-600"></div>

        <div id="nailedit-checkout-wrapper" class="grid gap-8 md:grid-cols-[minmax(0,2fr)_minmax(0,1fr)] items-start">
            <section id="nailedit-checkout-form-section" class="bg-white/80 rounded-3xl shadow-lg p-6 md:p-8">
                <h2 class="text-lg font-semibold text-slate-900 mb-4"><?php esc_html_e( 'Tarne andmed', 'nailedit' ); ?></h2>

                <form id="nailedit-checkout-address-form" class="space-y-6">
                    <div class="space-y-4">
                        <h3 class="text-sm font-semibold text-slate-800 uppercase tracking-[0.12em]"><?php esc_html_e( 'Arveaadress', 'nailedit' ); ?></h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex flex-col gap-1">
                                <label for="billing_first_name" class="text-sm font-medium text-primary"><?php esc_html_e( 'Eesnimi', 'nailedit' ); ?> *</label>
                                <input id="billing_first_name" name="billing_first_name" type="text" required class="w-full rounded-full px-4 min-h-[48px] bg-white/20 text-primary placeholder-primary/60 border border-third focus:outline-none focus:ring-2 focus:ring-secondary" />
                            </div>
                            <div class="flex flex-col gap-1">
                                <label for="billing_last_name" class="text-sm font-medium text-primary"><?php esc_html_e( 'Perekonnanimi', 'nailedit' ); ?> *</label>
                                <input id="billing_last_name" name="billing_last_name" type="text" required class="w-full rounded-full px-4 min-h-[48px] bg-white/20 text-primary placeholder-primary/60 border border-third focus:outline-none focus:ring-2 focus:ring-secondary" />
                            </div>
                        </div>

                        <div class="flex flex-col gap-1">
                            <label for="billing_email" class="text-sm font-medium text-primary"><?php esc_html_e( 'E-post', 'nailedit' ); ?> *</label>
                            <input id="billing_email" name="billing_email" type="email" required class="w-full rounded-full px-4 min-h-[48px] bg-white/20 text-primary placeholder-primary/60 border border-third focus:outline-none focus:ring-2 focus:ring-secondary" />
                        </div>

                        <div class="flex flex-col gap-1">
                            <label for="billing_company_name" class="text-sm font-medium text-primary"><?php esc_html_e( 'Ettevõte (valikuline)', 'nailedit' ); ?></label>
                            <input id="billing_company_name" name="billing_company_name" type="text" class="w-full rounded-full px-4 min-h-[48px] bg-white/20 text-primary placeholder-primary/60 border border-third focus:outline-none focus:ring-2 focus:ring-secondary" />
                        </div>

                        <div class="flex flex-col gap-1">
                            <label for="billing_address" class="text-sm font-medium text-primary"><?php esc_html_e( 'Aadress', 'nailedit' ); ?> *</label>
                            <input id="billing_address" name="billing_address[]" type="text" required class="w-full rounded-full px-4 min-h-[48px] bg-white/20 text-primary placeholder-primary/60 border border-third focus:outline-none focus:ring-2 focus:ring-secondary" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="flex flex-col gap-1">
                                <label for="billing_postcode" class="text-sm font-medium text-primary"><?php esc_html_e( 'Sihtnumber', 'nailedit' ); ?> *</label>
                                <input id="billing_postcode" name="billing_postcode" type="text" required class="w-full rounded-full px-4 min-h-[48px] bg-white/20 text-primary placeholder-primary/60 border border-third focus:outline-none focus:ring-2 focus:ring-secondary" />
                            </div>
                            <div class="flex flex-col gap-1">
                                <label for="billing_city" class="text-sm font-medium text-primary"><?php esc_html_e( 'Linn', 'nailedit' ); ?> *</label>
                                <input id="billing_city" name="billing_city" type="text" required class="w-full rounded-full px-4 min-h-[48px] bg-white/20 text-primary placeholder-primary/60 border border-third focus:outline-none focus:ring-2 focus:ring-secondary" />
                            </div>
                            <div class="flex flex-col gap-1">
                                <label for="billing_state" class="text-sm font-medium text-primary"><?php esc_html_e( 'Maakond', 'nailedit' ); ?></label>
                                <input id="billing_state" name="billing_state" type="text" class="w-full rounded-full px-4 min-h-[48px] bg-white/20 text-primary placeholder-primary/60 border border-third focus:outline-none focus:ring-2 focus:ring-secondary" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex flex-col gap-1">
                                <label for="billing_country" class="text-sm font-medium text-primary"><?php esc_html_e( 'Riik', 'nailedit' ); ?> *</label>
                                <input id="billing_country" name="billing_country" type="text" required class="w-full rounded-full px-4 min-h-[48px] bg-white/20 text-primary placeholder-primary/60 border border-third focus:outline-none focus:ring-2 focus:ring-secondary" />
                            </div>
                            <div class="flex flex-col gap-1">
                                <label for="billing_phone" class="text-sm font-medium text-primary"><?php esc_html_e( 'Telefon', 'nailedit' ); ?> *</label>
                                <input id="billing_phone" name="billing_phone" type="text" required class="w-full rounded-full px-4 min-h-[48px] bg-white/20 text-primary placeholder-primary/60 border border-third focus:outline-none focus:ring-2 focus:ring-secondary" />
                            </div>
                        </div>

                        <div class="flex items-center gap-2 pt-2">
                            <input id="billing_use_for_shipping" name="billing_use_for_shipping" type="checkbox" value="1" class="h-4 w-4 rounded border-slate-300 text-secondary focus:ring-secondary" checked />
                            <label for="billing_use_for_shipping" class="text-xs text-slate-700"><?php esc_html_e( 'Kasuta sama aadressi ka tarneks', 'nailedit' ); ?></label>
                        </div>
                    </div>

                    <div id="nailedit-shipping-fields" class="space-y-4 border-t border-slate-100 pt-6 mt-4 hidden">
                        <h3 class="text-sm font-semibold text-slate-800 uppercase tracking-[0.12em]"><?php esc_html_e( 'Tarneaadress', 'nailedit' ); ?></h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex flex-col gap-1">
                                <label for="shipping_first_name" class="text-sm font-medium text-primary"><?php esc_html_e( 'Eesnimi', 'nailedit' ); ?> *</label>
                                <input id="shipping_first_name" name="shipping_first_name" type="text" class="w-full rounded-full px-4 min-h-[48px] bg-white/20 text-primary placeholder-primary/60 border border-third focus:outline-none focus:ring-2 focus:ring-secondary" />
                            </div>
                            <div class="flex flex-col gap-1">
                                <label for="shipping_last_name" class="text-sm font-medium text-primary"><?php esc_html_e( 'Perekonnanimi', 'nailedit' ); ?> *</label>
                                <input id="shipping_last_name" name="shipping_last_name" type="text" class="w-full rounded-full px-4 min-h-[48px] bg-white/20 text-primary placeholder-primary/60 border border-third focus:outline-none focus:ring-2 focus:ring-secondary" />
                            </div>
                        </div>

                        <div class="flex flex-col gap-1">
                            <label for="shipping_email" class="text-sm font-medium text-primary"><?php esc_html_e( 'E-post', 'nailedit' ); ?> *</label>
                            <input id="shipping_email" name="shipping_email" type="email" class="w-full rounded-full px-4 min-h-[48px] bg-white/20 text-primary placeholder-primary/60 border border-third focus:outline-none focus:ring-2 focus:ring-secondary" />
                        </div>

                        <div class="flex flex-col gap-1">
                            <label for="shipping_company_name" class="text-sm font-medium text-primary"><?php esc_html_e( 'Ettevõte (valikuline)', 'nailedit' ); ?></label>
                            <input id="shipping_company_name" name="shipping_company_name" type="text" class="w-full rounded-full px-4 min-h-[48px] bg-white/20 text-primary placeholder-primary/60 border border-third focus:outline-none focus:ring-2 focus:ring-secondary" />
                        </div>

                        <div class="flex flex-col gap-1">
                            <label for="shipping_address" class="text-sm font-medium text-primary"><?php esc_html_e( 'Aadress', 'nailedit' ); ?> *</label>
                            <input id="shipping_address" name="shipping_address[]" type="text" class="w-full rounded-full px-4 min-h-[48px] bg-white/20 text-primary placeholder-primary/60 border border-third focus:outline-none focus:ring-2 focus:ring-secondary" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="flex flex-col gap-1">
                                <label for="shipping_postcode" class="text-sm font-medium text-primary"><?php esc_html_e( 'Sihtnumber', 'nailedit' ); ?> *</label>
                                <input id="shipping_postcode" name="shipping_postcode" type="text" class="w-full rounded-full px-4 min-h-[48px] bg-white/20 text-primary placeholder-primary/60 border border-third focus:outline-none focus:ring-2 focus:ring-secondary" />
                            </div>
                            <div class="flex flex-col gap-1">
                                <label for="shipping_city" class="text-sm font-medium text-primary"><?php esc_html_e( 'Linn', 'nailedit' ); ?> *</label>
                                <input id="shipping_city" name="shipping_city" type="text" class="w-full rounded-full px-4 min-h-[48px] bg-white/20 text-primary placeholder-primary/60 border border-third focus:outline-none focus:ring-2 focus:ring-secondary" />
                            </div>
                            <div class="flex flex-col gap-1">
                                <label for="shipping_state" class="text-sm font-medium text-primary"><?php esc_html_e( 'Maakond', 'nailedit' ); ?></label>
                                <input id="shipping_state" name="shipping_state" type="text" class="w-full rounded-full px-4 min-h-[48px] bg-white/20 text-primary placeholder-primary/60 border border-third focus:outline-none focus:ring-2 focus:ring-secondary" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex flex-col gap-1">
                                <label for="shipping_country" class="text-sm font-medium text-primary"><?php esc_html_e( 'Riik', 'nailedit' ); ?> *</label>
                                <input id="shipping_country" name="shipping_country" type="text" class="w-full rounded-full px-4 min-h-[48px] bg-white/20 text-primary placeholder-primary/60 border border-third focus:outline-none focus:ring-2 focus:ring-secondary" />
                            </div>
                            <div class="flex flex-col gap-1">
                                <label for="shipping_phone" class="text-sm font-medium text-primary"><?php esc_html_e( 'Telefon', 'nailedit' ); ?> *</label>
                                <input id="shipping_phone" name="shipping_phone" type="text" class="w-full rounded-full px-4 min-h-[48px] bg-white/20 text-primary placeholder-primary/60 border border-third focus:outline-none focus:ring-2 focus:ring-secondary" />
                            </div>
                        </div>
                    </div>

                    <div id="nailedit-checkout-address-error" class="text-sm text-red-600 min-h-[20px]"></div>

                    <div class="pt-2">
                        <button type="submit" id="nailedit-checkout-address-submit" class="w-full rounded-full min-h-[51px] px-4 bg-secondary text-primary font-semibold hover:bg-fourth transition">
                            <?php esc_html_e( 'Jätka tarne ja maksega', 'nailedit' ); ?>
                        </button>
                    </div>
                </form>
            </section>

            <aside id="nailedit-checkout-summary" class="bg-white/80 rounded-3xl shadow-lg p-6 md:p-8">
                <h2 class="text-lg font-semibold text-slate-900 mb-4"><?php esc_html_e( 'Tellimuse kokkuvõte', 'nailedit' ); ?></h2>
                <div id="nailedit-checkout-summary-body" class="text-sm text-slate-700 space-y-2">
                    <p><?php esc_html_e( 'Laen ostukorvi...', 'nailedit' ); ?></p>
                </div>
            </aside>
        </div>
    </div>
</main>

<script>
(function() {
  document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('nailedit-checkout-address-form');
    const useForShipping = document.getElementById('billing_use_for_shipping');
    const shippingFields = document.getElementById('nailedit-shipping-fields');
    const errorEl = document.getElementById('nailedit-checkout-address-error');
    const summaryBody = document.getElementById('nailedit-checkout-summary-body');
    const requireCartEl = document.getElementById('nailedit-checkout-require-cart');
    const submitBtn = document.getElementById('nailedit-checkout-address-submit');

    if (!form || !summaryBody) return;

    function getStoredAuthCookie() {
      try {
        return localStorage.getItem('bagisto_auth_cookie') || localStorage.getItem('bagisto_cart_cookie') || '';
      } catch (e) {
        return '';
      }
    }

    function getStoredAuthToken() {
      try {
        return localStorage.getItem('bagisto_auth_token') || '';
      } catch (e) {
        return '';
      }
    }

    function getStoredCartToken() {
      try {
        return localStorage.getItem('bagisto_guest_cart_token') || '';
      } catch (e) {
        return '';
      }
    }

    function persistCartToken(result) {
      try {
        if (result && result.cart_token) {
          localStorage.setItem('bagisto_guest_cart_token', result.cart_token);
        }
      } catch (e) {}
    }

    function renderSummaryFromCartPayload(payload) {
      const root = summaryBody;
      if (window && window.console) {
        console.log('Checkout summary payload:', payload);
      }
      if (!payload || !payload.data || !payload.data.length) {
        root.innerHTML = '<p><?php echo esc_js( __( 'Sinu ostukorv on tühi.', 'nailedit' ) ); ?></p>';
        return;
      }

      const first = payload.data[0] || {};
      let cart = first.cart || {};
      // Normalize cart structure: Bagisto cart API usually returns { data: { ...cart... } }
      if (cart && cart.data) {
        cart = cart.data;
      }
      const items = cart.items ? (Array.isArray(cart.items) ? cart.items : [cart.items]) : [];

      let html = '';

      if (!items.length) {
        html += '<p><?php echo esc_js( __( 'Sinu ostukorv on tühi.', 'nailedit' ) ); ?></p>';
      } else {
        html += '<div class="space-y-3 mb-4">';
        items.forEach(function (item) {
          const name = item.name || '';
          const qty = item.quantity || 0;
          const total = item.formatted_total || item.total || '';
          html += '<div class="flex items-center justify-between text-sm">'
            + '<span class="text-slate-700">' + qty + ' × ' + name + '</span>'
            + '<span class="font-medium text-slate-900">' + (total || '') + '</span>'
            + '</div>';
        });
        html += '</div>';
      }

      const grand = cart.formatted_grand_total || cart.grand_total || '';
      const sub   = cart.formatted_sub_total || cart.sub_total || '';
      const tax   = cart.formatted_tax_total || cart.tax_total || '';
      const disc  = cart.formatted_discount || cart.discount || '';

      html += '<div class="border-t border-slate-100 pt-3 mt-2 space-y-1 text-sm">';
      if (sub) {
        html += '<div class="flex justify-between">'
          + '<span class="text-slate-600"><?php echo esc_js( __( 'Vahesumma', 'nailedit' ) ); ?></span>'
          + '<span class="text-slate-800">' + sub + '</span>'
          + '</div>';
      }
      if (tax) {
        html += '<div class="flex justify-between">'
          + '<span class="text-slate-600"><?php echo esc_js( __( 'Maksud', 'nailedit' ) ); ?></span>'
          + '<span class="text-slate-800">' + tax + '</span>'
          + '</div>';
      }
      if (disc) {
        html += '<div class="flex justify-between">'
          + '<span class="text-slate-600"><?php echo esc_js( __( 'Allahindlus', 'nailedit' ) ); ?></span>'
          + '<span class="text-slate-800">' + disc + '</span>'
          + '</div>';
      }

      // Hard-coded shipping block: two methods the user can click
      html += '<div class="mt-3 pt-3 border-t border-slate-100 space-y-2">'
        + '<div class="text-xs font-semibold tracking-[0.14em] text-slate-500 uppercase"><?php echo esc_js( __( 'Tarneviisid', 'nailedit' ) ); ?></div>'
        + '<label for="nailedit-ship-flatrate" class="flex items-center justify-between gap-3 text-xs md:text-sm cursor-pointer">'
        +   '<div class="flex items-center gap-2">'
        +     '<input type="radio" id="nailedit-ship-flatrate" name="nailedit_shipping_method" value="flatrate_flatrate" class="h-4 w-4 text-secondary border-slate-300 focus:ring-secondary" checked />'
        +     '<span class="text-slate-700"><?php echo esc_js( __( 'Tavapost - fikseeritud hind', 'nailedit' ) ); ?></span>'
        +   '</div>'
        +   '<span class="text-slate-800">€30.00</span>'
        + '</label>'
        + '<label for="nailedit-ship-freeshipping" class="flex items-center justify-between gap-3 text-xs md:text-sm cursor-pointer">'
        +   '<div class="flex items-center gap-2">'
        +     '<input type="radio" id="nailedit-ship-freeshipping" name="nailedit_shipping_method" value="free_shipping_free_shipping" class="h-4 w-4 text-secondary border-slate-300 focus:ring-secondary" />'
        +     '<span class="text-slate-700"><?php echo esc_js( __( 'Tasuta transport', 'nailedit' ) ); ?></span>'
        +   '</div>'
        +   '<span class="text-slate-800">€0.00</span>'
        + '</label>'
        + '</div>';

      // Hard-coded payment block: one simple method
      html += '<div class="mt-4 pt-3 border-t border-slate-100 space-y-2">'
        + '<div class="text-xs font-semibold tracking-[0.14em] text-slate-500 uppercase"><?php echo esc_js( __( 'Makseviis', 'nailedit' ) ); ?></div>'
        + '<label for="nailedit-pay-cod" class="flex items-center justify-between gap-3 text-xs md:text-sm cursor-pointer">'
        +   '<div class="flex items-center gap-2">'
        +     '<input type="radio" id="nailedit-pay-cod" name="nailedit_payment_method" value="cashondelivery" class="h-4 w-4 text-secondary border-slate-300 focus:ring-secondary" checked />'
        +     '<span class="text-slate-700"><?php echo esc_js( __( 'Sularaha kättesaamisel', 'nailedit' ) ); ?></span>'
        +   '</div>'
        + '</label>'
        + '</div>';

      if (grand) {
        html += '<div class="flex justify-between pt-2 mt-1 border-t border-slate-200">'
          + '<span class="text-slate-900 font-semibold"><?php echo esc_js( __( 'Kokku', 'nailedit' ) ); ?></span>'
          + '<span class="text-slate-900 font-semibold">' + grand + '</span>'
          + '</div>';
      }

      html += '</div>';

      root.innerHTML = html;
    }

    function loadCartForSummary() {
      const fd = new FormData();
      fd.append('action', 'nailedit_get_cart');

      const storedCookie = getStoredAuthCookie();
      const storedToken = getStoredAuthToken();
      const cartToken   = getStoredCartToken();
      if (storedCookie) fd.append('stored_cookie', storedCookie);
      if (storedToken) {
        fd.append('auth_token', storedToken);
      } else if (cartToken) {
        fd.append('cart_token', cartToken);
      }

      fetch('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', {
        method: 'POST',
        body: fd,
        credentials: 'same-origin'
      })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          persistCartToken(data);
          if (!data || !data.success) {
            if (requireCartEl) {
              requireCartEl.classList.remove('hidden');
              requireCartEl.textContent = (data && data.message) ? data.message : '<?php echo esc_js( __( 'Ostukorv on tühi või seda ei õnnestunud laadida.', 'nailedit' ) ); ?>';
            }
            summaryBody.innerHTML = '<p><?php echo esc_js( __( 'Sinu ostukorv on tühi.', 'nailedit' ) ); ?></p>';
            return;
          }
          // For consistency with save-address payload, wrap into similar shape
          const wrapped = {
            data: [
              {
                cart: data.data,
                rates: []
              }
            ]
          };
          renderSummaryFromCartPayload(wrapped);
        })
        .catch(function () {
          summaryBody.innerHTML = '<p><?php echo esc_js( __( 'Ostukorvi ei õnnestunud laadida.', 'nailedit' ) ); ?></p>';
        });
    }

    function prefillFromAddress(addr, prefix) {
      if (!addr) return;
      function setValue(id, value) {
        var el = document.getElementById(prefix + '_' + id);
        if (el && value != null && value !== '') {
          el.value = value;
        }
      }

      var firstName = addr.first_name || '';
      var lastName  = addr.last_name || '';
      var email     = addr.email || '';
      var company   = addr.company_name || addr.company || '';
      var address1  = '';
      if (Array.isArray(addr.address1) && addr.address1.length) {
        address1 = addr.address1[0];
      } else if (Array.isArray(addr.address) && addr.address.length) {
        address1 = addr.address[0];
      } else if (addr.address1) {
        address1 = addr.address1;
      }
      var postcode  = addr.postcode || '';
      var city      = addr.city || '';
      var state     = addr.state || '';
      var country   = addr.country || '';
      var phone     = addr.phone || '';

      setValue('first_name', firstName);
      setValue('last_name', lastName);
      setValue('email', email);
      setValue('company_name', company);

      var addressInput = document.getElementById(prefix + '_address');
      if (addressInput && address1) {
        addressInput.value = address1;
      }

      setValue('postcode', postcode);
      setValue('city', city);
      setValue('state', state);
      setValue('country', country);
      setValue('phone', phone);
    }

    function loadDefaultAddress() {
      // Only for logged-in customers (auth token/cookie present)
      var storedCookie = getStoredAuthCookie();
      var storedToken  = getStoredAuthToken();
      if (!storedCookie && !storedToken) {
        return;
      }

      var fd = new FormData();
      fd.append('action', 'nailedit_list_addresses');
      if (storedCookie) fd.append('stored_cookie', storedCookie);
      if (storedToken) fd.append('auth_token', storedToken);

      fetch('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', {
        method: 'POST',
        body: fd,
        credentials: 'same-origin'
      })
        .then(function (r) { return r.json(); })
        .then(function (result) {
          var ok = result && result.success;
          if (!ok) return;

          var data = result && result.data ? result.data : {};
          var items = data.data || data || [];
          if (!items || !items.length) return;

          var def = null;
          for (var i = 0; i < items.length; i++) {
            var a = items[i];
            if (a && (a.is_default === 1 || a.is_default === '1' || a.default_address === 1 || a.default_address === '1')) {
              def = a;
              break;
            }
          }
          if (!def) {
            def = items[0];
          }

          prefillFromAddress(def, 'billing');

          // Kui kasutame billingut ka shippinguks, kopeerime samad väärtused shipping-väljadele
          if (useForShipping && useForShipping.checked) {
            prefillFromAddress(def, 'shipping');
          }
        })
        .catch(function () {
          // vaikne ebaõnnestumine – ei täida, aga checkout töötab edasi
        });
    }

    if (useForShipping && shippingFields) {
      useForShipping.addEventListener('change', function () {
        if (this.checked) {
          shippingFields.classList.add('hidden');
        } else {
          shippingFields.classList.remove('hidden');
        }
      });
      // initial state
      shippingFields.classList.add('hidden');
    }

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      if (errorEl) errorEl.textContent = '';

      const storedCookie = getStoredAuthCookie();
      const storedToken = getStoredAuthToken();
      const cartToken   = getStoredCartToken();

      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = '<?php echo esc_js( __( 'Salvestan aadressi...', 'nailedit' ) ); ?>';
      }
      // 1) Save address from form
      const fdAddress = new FormData(form);
      fdAddress.append('action', 'nailedit_checkout_save_address');
      if (storedCookie) fdAddress.append('stored_cookie', storedCookie);
      if (storedToken) {
        fdAddress.append('auth_token', storedToken);
      } else if (cartToken) {
        fdAddress.append('cart_token', cartToken);
      }

      fetch('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', {
        method: 'POST',
        body: fdAddress,
        credentials: 'same-origin'
      })
        .then(function (r) { return r.json(); })
        .then(function (addressResult) {
          persistCartToken(addressResult);
          var okAddr = addressResult && addressResult.success;
          var addrData = addressResult && addressResult.data ? addressResult.data : {};

          if (!okAddr) {
            var msg = (addrData && addrData.message) || (addressResult && addressResult.message) || '<?php echo esc_js( __( 'Aadressi ei õnnestunud salvestada.', 'nailedit' ) ); ?>';
            if (errorEl) {
              errorEl.textContent = msg;
            }
            throw new Error('save-address failed');
          }

          // 2) Save shipping method using selected option
          const selectedMethodInput = document.querySelector('input[name="nailedit_shipping_method"]:checked');
          let selectedMethod = selectedMethodInput ? selectedMethodInput.value : '';
          if (!selectedMethod) {
            // fallback vaikimisi meetodile, kui radio pole valitud
            selectedMethod = 'flatrate_flatrate';
          }

          if (submitBtn) {
            submitBtn.textContent = '<?php echo esc_js( __( 'Salvestan tarnet...', 'nailedit' ) ); ?>';
          }

          const fdShipping = new FormData();
          fdShipping.append('action', 'nailedit_checkout_save_shipping');
          fdShipping.append('shipping_method', selectedMethod);
          if (storedCookie) fdShipping.append('stored_cookie', storedCookie);
          if (storedToken) {
            fdShipping.append('auth_token', storedToken);
          } else {
            const latestCartToken = getStoredCartToken();
            if (latestCartToken) {
              fdShipping.append('cart_token', latestCartToken);
            }
          }

          return fetch('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', {
            method: 'POST',
            body: fdShipping,
            credentials: 'same-origin'
          }).then(function (r) { return r.json(); });
        })
        .then(function (shippingResult) {
          persistCartToken(shippingResult);
          if (!shippingResult) return;

          if (window && window.console) {
            console.log('save-shipping result:', shippingResult);
          }
          var okShip = shippingResult && shippingResult.success;
          var shipData = shippingResult && shippingResult.data ? shippingResult.data : {};

          if (!okShip) {
            var msg = (shipData && shipData.message) || (shippingResult && shippingResult.message) || '<?php echo esc_js( __( 'Tarneviisi ei õnnestunud salvestada.', 'nailedit' ) ); ?>';
            if (errorEl) {
              errorEl.textContent = msg;
            }
            throw new Error('save-shipping failed');
          }

          // Update summary based on shipping response
          renderSummaryFromCartPayload(shipData);

          // 3) Save payment method
          const selectedPaymentInput = document.querySelector('input[name="nailedit_payment_method"]:checked');
          const selectedPayment = selectedPaymentInput ? selectedPaymentInput.value : 'cashondelivery';

          if (submitBtn) {
            submitBtn.textContent = '<?php echo esc_js( __( 'Salvestan makset...', 'nailedit' ) ); ?>';
          }

          const fdPayment = new FormData();
          fdPayment.append('action', 'nailedit_checkout_save_payment');
          fdPayment.append('payment_method', selectedPayment);
          if (storedCookie) fdPayment.append('stored_cookie', storedCookie);
          if (storedToken) {
            fdPayment.append('auth_token', storedToken);
          } else {
            const latestCartToken = getStoredCartToken();
            if (latestCartToken) {
              fdPayment.append('cart_token', latestCartToken);
            }
          }

          return fetch('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', {
            method: 'POST',
            body: fdPayment,
            credentials: 'same-origin'
          }).then(function (r) { return r.json(); });
        })
        .then(function (paymentResult) {
          persistCartToken(paymentResult);
          if (!paymentResult) return;

          var okPay = paymentResult && paymentResult.success;
          var payData = paymentResult && paymentResult.data ? paymentResult.data : {};

          if (!okPay) {
            var msg = (payData && payData.message) || (paymentResult && paymentResult.message) || '<?php echo esc_js( __( 'Makseviisi ei õnnestunud salvestada.', 'nailedit' ) ); ?>';
            if (errorEl) {
              errorEl.textContent = msg;
            }
            throw new Error('save-payment failed');
          }

          // 4) Save order
          if (submitBtn) {
            submitBtn.textContent = '<?php echo esc_js( __( 'Salvestan tellimust...', 'nailedit' ) ); ?>';
          }

          const fdOrder = new FormData();
          fdOrder.append('action', 'nailedit_checkout_save_order');
          if (storedCookie) fdOrder.append('stored_cookie', storedCookie);
          if (storedToken) {
            fdOrder.append('auth_token', storedToken);
          } else {
            const latestCartToken = getStoredCartToken();
            if (latestCartToken) {
              fdOrder.append('cart_token', latestCartToken);
            }
          }

          return fetch('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', {
            method: 'POST',
            body: fdOrder,
            credentials: 'same-origin'
          }).then(function (r) { return r.json(); });
        })
        .then(function (orderResult) {
          persistCartToken(orderResult);
          if (!orderResult) return;

          var ok = orderResult && orderResult.success;
          var data = orderResult && orderResult.data ? orderResult.data : {};

          if (!ok) {
            var msg = (data && data.message) || (orderResult && orderResult.message) || '<?php echo esc_js( __( 'Midagi läks valesti!', 'nailedit' ) ); ?>';
            if (errorEl) {
              errorEl.textContent = msg;
            }
            return;
          }

          var message = (data && data.message) || '<?php echo esc_js( __( 'Tellimus salvestati edukalt.', 'nailedit' ) ); ?>';
          if (errorEl) {
            errorEl.textContent = message;
          }

          // Save order info locally for thank-you page
          try {
            var orderObj = null;
            var root = data && data.data ? data.data : null;

            if (root) {
              // Shape A: { data: [ { order: {...} } ] }
              if (Array.isArray(root) && root.length) {
                var w = root[0] || {};
                if (w.order) {
                  orderObj = w.order;
                }
              }

              // Shape B: { data: { order: {...} } }
              if (!orderObj && root.data && root.data.order) {
                orderObj = root.data.order;
              }

              // Shape C: { order: {...} }
              if (!orderObj && root.order) {
                orderObj = root.order;
              }
            }

            if (orderObj) {
              if (window && window.console) {
                console.log('Saving last order for thank-you page:', orderObj);
              }
              localStorage.setItem('nailedit_last_order', JSON.stringify(orderObj));
            }
          } catch (e) {}

          // Temporarily do not redirect automatically so we can inspect response data.
          // To re-enable redirect, uncomment the following line:
           window.location.href = '<?php echo esc_url( trailingslashit( site_url( '/aitah' ) ) ); ?>';
        })
        .catch(function (err) {
          if (err && console && console.error) {
            console.error('Checkout flow error', err);
          }
        })
        .finally(function () {
          if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = '<?php echo esc_js( __( 'Jätka tarne ja maksega', 'nailedit' ) ); ?>';
          }
        });
    });

    loadCartForSummary();
    loadDefaultAddress();
  });
})();
</script>

<?php
get_footer();
