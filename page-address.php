<?php
/**
 * Template Name: Customer Address
 * Description: Customer address create page using Bagisto API
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<?php get_template_part( 'template-parts/page-header' ); ?>

<main class="site-main nailedit-address-page py-10">
    <div class="max-w-[1200px] mx-auto px-4">
      

        <div id="nailedit-address-require-login" class="mb-6 hidden text-center text-sm text-red-600">
            <p><?php nailedit_t( 'address_login_required' ); ?></p>
        </div>

        <div class="grid gap-8 md:grid-cols-[minmax(0,2fr)_minmax(0,1.4fr)] items-start">
            <section class="bg-white/80 rounded-3xl shadow-lg p-6 md:p-8">
                <h2 class="text-lg font-semibold text-slate-900 mb-4"><?php nailedit_t( 'add_new_address' ); ?></h2>

                <form id="nailedit-address-form" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1">
                            <label for="company_name" class="text-sm font-medium text-primary"><?php nailedit_t( 'company' ); ?></label>
                            <input type="text" id="company_name" name="company_name" class="nailedit-input w-full rounded-full px-4 min-h-[48px] bg-white/20 text-primary placeholder-primary/60 border border-third focus:outline-none focus:ring-2 focus:ring-secondary" />
                        </div>
                        <div class="flex flex-col gap-1">
                            <label for="first_name" class="text-sm font-medium text-primary"><?php nailedit_t( 'first_name' ); ?> *</label>
                            <input type="text" id="first_name" name="first_name" required class="nailedit-input w-full rounded-full px-4 min-h-[48px] bg-white/20 text-primary placeholder-primary/60 border border-third focus:outline-none focus:ring-2 focus:ring-secondary" />
                        </div>
                        <div class="flex flex-col gap-1">
                            <label for="last_name" class="text-sm font-medium text-primary"><?php nailedit_t( 'last_name' ); ?> *</label>
                            <input type="text" id="last_name" name="last_name" required class="nailedit-input w-full rounded-full px-4 min-h-[48px] bg-white/20 text-primary placeholder-primary/60 border border-third focus:outline-none focus:ring-2 focus:ring-secondary" />
                        </div>
                        <div class="flex flex-col gap-1 md:col-span-2">
                            <label for="address1" class="text-sm font-medium text-primary"><?php nailedit_t( 'street_address' ); ?> *</label>
                            <input type="text" id="address1" name="address[]" required class="nailedit-input w-full rounded-full px-4 min-h-[48px] bg-white/20 text-primary placeholder-primary/60 border border-third focus:outline-none focus:ring-2 focus:ring-secondary" />
                        </div>
                        <div class="flex flex-col gap-1">
                            <label for="country" class="text-sm font-medium text-primary"><?php nailedit_t( 'country' ); ?> *</label>
                            <input type="text" id="country" name="country" placeholder="US" required class="nailedit-input w-full rounded-full px-4 min-h-[48px] bg-white/20 text-primary placeholder-primary/60 border border-third focus:outline-none focus:ring-2 focus:ring-secondary" />
                        </div>
                        <div class="flex flex-col gap-1">
                            <label for="state" class="text-sm font-medium text-primary"><?php nailedit_t( 'state' ); ?> *</label>
                            <input type="text" id="state" name="state" placeholder="CA" required class="nailedit-input w-full rounded-full px-4 min-h-[48px] bg-white/20 text-primary placeholder-primary/60 border border-third focus:outline-none focus:ring-2 focus:ring-secondary" />
                        </div>
                        <div class="flex flex-col gap-1">
                            <label for="city" class="text-sm font-medium text-primary"><?php nailedit_t( 'city' ); ?> *</label>
                            <input type="text" id="city" name="city" required class="nailedit-input w-full rounded-full px-4 min-h-[48px] bg-white/20 text-primary placeholder-primary/60 border border-third focus:outline-none focus:ring-2 focus:ring-secondary" />
                        </div>
                        <div class="flex flex-col gap-1">
                            <label for="postcode" class="text-sm font-medium text-primary"><?php nailedit_t( 'postcode' ); ?> *</label>
                            <input type="text" id="postcode" name="postcode" required class="nailedit-input w-full rounded-full px-4 min-h-[48px] bg-white/20 text-primary placeholder-primary/60 border border-third focus:outline-none focus:ring-2 focus:ring-secondary" />
                        </div>
                        <div class="flex flex-col gap-1">
                            <label for="phone" class="text-sm font-medium text-primary"><?php nailedit_t( 'phone' ); ?> *</label>
                            <input type="text" id="phone" name="phone" required class="nailedit-input w-full rounded-full px-4 min-h-[48px] bg-white/20 text-primary placeholder-primary/60 border border-third focus:outline-none focus:ring-2 focus:ring-secondary" />
                        </div>
                        <div class="flex flex-col gap-1">
                            <label for="email" class="text-sm font-medium text-primary"><?php nailedit_t( 'email' ); ?> *</label>
                            <input type="email" id="email" name="email" required class="nailedit-input w-full rounded-full px-4 min-h-[48px] bg-white/20 text-primary placeholder-primary/60 border border-third focus:outline-none focus:ring-2 focus:ring-secondary" />
                        </div>
                        <div class="flex flex-col gap-1 md:col-span-2">
                            <label for="vat_id" class="text-sm font-medium text-primary"><?php nailedit_t( 'vat_id' ); ?></label>
                            <input type="text" id="vat_id" name="vat_id" class="nailedit-input w-full rounded-full px-4 min-h-[48px] bg-white/20 text-primary placeholder-primary/60 border border-third focus:outline-none focus:ring-2 focus:ring-secondary" />
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="default_address" name="default_address" value="1" checked class="h-4 w-4 text-secondary border-slate-300 rounded focus:ring-secondary" />
                        <label for="default_address" class="text-sm text-slate-700"><?php nailedit_t( 'set_as_default_address' ); ?></label>
                    </div>

                    <div class="flex flex-col gap-2">
                        <button type="submit" class="inline-flex items-center justify-center rounded-full gradient-dark text-secondary px-5 py-2.5 text-sm font-semibold text-secondary hover:text-white shadow-sm hover:bg-primary/90 transition">
                            <?php nailedit_t( 'save_address' ); ?>
                        </button>
                        <div id="nailedit-address-success" class="text-sm text-emerald-600 min-h-[1.25rem]"></div>
                        <div id="nailedit-address-error" class="text-sm text-red-600 min-h-[1.25rem]"></div>
                    </div>
                </form>
            </section>

            <section class="bg-white/80 rounded-3xl shadow-lg p-6 md:p-8">
                <h2 class="text-lg font-semibold text-slate-900 mb-4"><?php nailedit_t( 'my_addresses' ); ?></h2>
                <div id="nailedit-address-list" class="space-y-4"></div>
            </section>
        </div>
    </div>
</main>

<script>
(document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('nailedit-address-form');
    const requireLogin = document.getElementById('nailedit-address-require-login');
    const successEl = document.getElementById('nailedit-address-success');
    const errorEl = document.getElementById('nailedit-address-error');
    const listEl = document.getElementById('nailedit-address-list');

    if (!form) {
        return;
    }

    function getStoredCustomer() {
        try {
            const stored = localStorage.getItem('nailedit_customer');
            return stored ? JSON.parse(stored) : null;
        } catch (e) {
            return null;
        }
    }

    function getStoredAuthCookie() {
        try {
            return localStorage.getItem('bagisto_auth_cookie') || '';
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

    const customer = getStoredCustomer();
    if (!customer) {
        if (requireLogin) {
            requireLogin.style.display = 'block';
        }
        form.style.display = 'none';
        return;
    }

    // Prefill some fields from customer
    if (customer.first_name) document.getElementById('first_name').value = customer.first_name;
    if (customer.last_name) document.getElementById('last_name').value = customer.last_name;
    if (customer.email) document.getElementById('email').value = customer.email;
    if (customer.phone) document.getElementById('phone').value = customer.phone;

    let hasPrefilled = false;

    function prefillFromAddress(addr) {
        if (!addr || hasPrefilled) return;
        const address1 = addr.address1 || (addr.address && addr.address[0]) || '';
        const country = addr.country || '';
        const state = addr.state || '';
        const city = addr.city || '';
        const postcode = addr.postcode || '';
        const phone = addr.phone || '';
        const company = addr.company_name || '';
        const vat = addr.vat_id || '';

        if (company) document.getElementById('company_name').value = company;
        if (address1) document.getElementById('address1').value = address1;
        if (country) document.getElementById('country').value = country;
        if (state) document.getElementById('state').value = state;
        if (city) document.getElementById('city').value = city;
        if (postcode) document.getElementById('postcode').value = postcode;
        if (phone) document.getElementById('phone').value = phone;
        if (vat) document.getElementById('vat_id').value = vat;

        hasPrefilled = true;
    }

    function loadAddresses() {
        if (!listEl) return;

        const fd = new FormData();
        fd.append('action', 'nailedit_list_addresses');

        const storedCookie = getStoredAuthCookie();
        if (storedCookie) {
            fd.append('stored_cookie', storedCookie);
        }

        const storedToken = getStoredAuthToken();
        if (storedToken) {
            fd.append('auth_token', storedToken);
        }

        fetch('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', {
            method: 'POST',
            body: fd
        })
        .then(r => r.json())
        .then(result => {
            const ok = result && result.success;
            const data = result && result.data ? result.data : {};

            if (!ok) {
                listEl.innerHTML = '<p><?php echo esc_js( nailedit_get_t( 'addresses_load_failed' ) ); ?></p>';
                return;
            }

            const items = data.data || data || [];
            
            if (!items || !items.length) {
                listEl.innerHTML = '<p><?php echo esc_js( nailedit_get_t( 'no_addresses' ) ); ?></p>';
                return;
            }

            if (!hasPrefilled) {
                const defaultAddr = items.find(function(addr) {
                    return addr.is_default === '1' || addr.is_default === 1 || addr.is_default === true;
                }) || items[0];
                prefillFromAddress(defaultAddr);
            }

            listEl.innerHTML = items.map(function(addr) {
                const id = addr.id || '';
                const name = (addr.first_name || '') + ' ' + (addr.last_name || '');
                const address1 = addr.address1 || (addr.address && addr.address[0]) || '';
                const cityLine = [addr.postcode, addr.city].filter(Boolean).join(' ');
                const isDefault = addr.is_default === '1' || addr.is_default === 1 || addr.is_default === true;

                const borderClass = isDefault ? 'border-2 border-primary' : 'border border-slate-200';
                const bgClass = isDefault ? 'bg-primary/5' : 'bg-white';

                return '<div class="nailedit-address-item rounded-2xl ' + borderClass + ' ' + bgClass + ' px-4 py-3 text-sm shadow-sm flex flex-col gap-1" data-id="' + id + '">'
                    + '<div class="font-semibold text-slate-900">' + name + '</div>'
                    + '<div class="text-slate-700">' + address1 + '</div>'
                    + '<div class="text-slate-700">' + cityLine + '</div>'
                    + '<div class="text-slate-700">' + (addr.country_name || addr.country || '') + '</div>'
                    + '<div class="text-slate-500 text-xs"><?php echo esc_js( nailedit_get_t( 'phone_prefix' ) ); ?>' + (addr.phone || '') + '</div>'
                    + (isDefault ? '<span class="mt-1 inline-flex items-center self-start rounded-full bg-primary px-2 py-0.5 text-[11px] font-medium text-white"><?php echo esc_js( nailedit_get_t( 'default_address' ) ); ?></span>' : '')
                    + '<div class="mt-2 flex flex-wrap gap-2">' + (isDefault ? '' : '<button type="button" class="nailedit-make-default inline-flex items-center rounded-full border border-slate-300 px-3 py-1 text-xs font-medium text-slate-700 hover:bg-slate-50" data-id="' + id + '"><?php echo esc_js( nailedit_get_t( 'make_default' ) ); ?></button> ') + '<button type="button" class="nailedit-delete-address inline-flex items-center rounded-full border border-red-200 px-3 py-1 text-xs font-medium text-red-700 hover:bg-red-50" data-id="' + id + '"><?php echo esc_js( nailedit_get_t( 'delete' ) ); ?></button></div>'
                    + '</div>';
            }).join('');

            // Bind buttons
            listEl.querySelectorAll('.nailedit-make-default').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    if (!id) return;

                    const originalText = this.textContent;
                    this.disabled = true;
                    this.textContent = '<?php echo esc_js( nailedit_get_t( 'setting' ) ); ?>';

                    const fd2 = new FormData();
                    fd2.append('action', 'nailedit_make_default_address');
                    fd2.append('address_id', id);

                    const storedCookie2 = getStoredAuthCookie();
                    if (storedCookie2) fd2.append('stored_cookie', storedCookie2);
                    const storedToken2 = getStoredAuthToken();
                    if (storedToken2) fd2.append('auth_token', storedToken2);

                    fetch('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', {
                        method: 'POST',
                        body: fd2
                    })
                    .then(r => r.json())
                    .then(() => {
                        // Small delay to ensure API has updated
                        setTimeout(() => {
                            loadAddresses();
                        }, 300);
                    })
                    .catch(err => {
                        console.error('make-default error', err);
                        this.disabled = false;
                        this.textContent = originalText;
                    });
                });
            });

            listEl.querySelectorAll('.nailedit-delete-address').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    if (!id) return;

                    if (!confirm('<?php echo esc_js( nailedit_get_t( 'confirm_delete_address' ) ); ?>')) {
                        return;
                    }

                    const fd3 = new FormData();
                    fd3.append('action', 'nailedit_delete_address');
                    fd3.append('address_id', id);

                    const storedCookie3 = getStoredAuthCookie();
                    if (storedCookie3) fd3.append('stored_cookie', storedCookie3);
                    const storedToken3 = getStoredAuthToken();
                    if (storedToken3) fd3.append('auth_token', storedToken3);

                    fetch('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', {
                        method: 'POST',
                        body: fd3
                    })
                    .then(r => r.json())
                    .then(() => {
                        loadAddresses();
                    })
                    .catch(err => console.error('delete error', err));
                });
            });
        })
        .catch(err => {
            console.error('List addresses error:', err);
            if (listEl) {
                listEl.innerHTML = '<p><?php echo esc_js( nailedit_get_t( 'addresses_load_failed' ) ); ?></p>';
            }
        });
    }

    loadAddresses();

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        if (successEl) successEl.textContent = '';
        if (errorEl) errorEl.textContent = '';

        const fd = new FormData(form);
        fd.append('action', 'nailedit_customer_address');

        const storedCookie = getStoredAuthCookie();
        if (storedCookie) {
            fd.append('stored_cookie', storedCookie);
        }

        const storedToken = getStoredAuthToken();
        if (storedToken) {
            fd.append('auth_token', storedToken);
        }

        // default_address checkbox -> 1/0
        if (!document.getElementById('default_address').checked) {
            fd.set('default_address', '0');
        }

        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = '<?php echo esc_js( nailedit_get_t( 'saving' ) ); ?>';
        }

        fetch('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', {
            method: 'POST',
            body: fd
        })
        .then(r => r.json())
        .then(result => {
            const ok = result && result.success;
            const data = result && result.data ? result.data : {};

            if (!ok) {
                let msg = data.message || result.message || '<?php echo esc_js( nailedit_get_t( 'something_went_wrong' ) ); ?>';
                if (data.errors) {
                    const firstField = Object.keys(data.errors)[0];
                    if (firstField && data.errors[firstField] && data.errors[firstField][0]) {
                        msg = data.errors[firstField][0];
                    }
                }
                if (errorEl) errorEl.textContent = msg;
                return;
            }

            if (successEl) {
                successEl.textContent = data.message || '<?php echo esc_js( nailedit_get_t( 'address_added_success' ) ); ?>';
            }

            form.reset();
        })
        .catch(err => {
            console.error('Address create error:', err);
            if (errorEl) errorEl.textContent = '<?php echo esc_js( nailedit_get_t( 'error_prefix' ) ); ?>' + err.message;
        })
        .finally(() => {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = '<?php echo esc_js( nailedit_get_t( 'save_address' ) ); ?>';
            }
        });
    });
}));
</script>

<?php
get_footer();
