<?php
/**
 * Template Name: Customer Profile
 * Description: Customer profile page using Bagisto API
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<?php get_template_part( 'template-parts/page-header' ); ?>

<main class="site-main nailedit-profile-page py-10">
    <div class="max-w-[1200px] mx-auto px-4">

        <div id="nailedit-profile-require-login" class="mb-6 hidden text-center text-sm text-red-600">
            <p><?php esc_html_e( 'Palun logi sisse, et oma profiili vaadata ja muuta.', 'nailedit' ); ?></p>
        </div>

        <div class="max-w-3xl">
            <section class="bg-white/80 rounded-3xl shadow-lg p-6 md:p-8">
                <form id="nailedit-profile-form" enctype="multipart/form-data" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1">
                            <label for="first_name" class="text-sm font-medium text-primary"><?php esc_html_e( 'Eesnimi', 'nailedit' ); ?> *</label>
                            <input type="text" id="first_name" name="first_name" required class="nailedit-input w-full rounded-full px-4 min-h-[48px] bg-white/20 text-primary placeholder-primary/60 border border-third focus:outline-none focus:ring-2 focus:ring-secondary">
                        </div>
                        <div class="flex flex-col gap-1">
                            <label for="last_name" class="text-sm font-medium text-primary"><?php esc_html_e( 'Perenimi', 'nailedit' ); ?> *</label>
                            <input type="text" id="last_name" name="last_name" required class="nailedit-input w-full rounded-full px-4 min-h-[48px] bg-white/20 text-primary placeholder-primary/60 border border-third focus:outline-none focus:ring-2 focus:ring-secondary">
                        </div>
                        <div class="flex flex-col gap-1">
                            <label for="gender" class="text-sm font-medium text-primary"><?php esc_html_e( 'Sugu', 'nailedit' ); ?> *</label>
                            <select id="gender" name="gender" required class="nailedit-input w-full rounded-full px-4 min-h-[48px] bg-white/20 text-primary placeholder-primary/60 border border-third focus:outline-none focus:ring-2 focus:ring-secondary">
                                <option value=""><?php esc_html_e( 'Vali...', 'nailedit' ); ?></option>
                                <option value="Male"><?php esc_html_e( 'Male', 'nailedit' ); ?></option>
                                <option value="Female"><?php esc_html_e( 'Female', 'nailedit' ); ?></option>
                                <option value="Other"><?php esc_html_e( 'Other', 'nailedit' ); ?></option>
                            </select>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label for="date_of_birth" class="text-sm font-medium text-primary"><?php esc_html_e( 'Sünniaeg', 'nailedit' ); ?> *</label>
                            <input type="text" id="date_of_birth" name="date_of_birth" placeholder="1991-05-15" required class="nailedit-input w-full rounded-full px-4 min-h-[48px] bg-white/20 text-primary placeholder-primary/60 border border-third focus:outline-none focus:ring-2 focus:ring-secondary">
                        </div>
                        <div class="flex flex-col gap-1">
                            <label for="phone" class="text-sm font-medium text-primary"><?php esc_html_e( 'Telefon', 'nailedit' ); ?> *</label>
                            <input type="text" id="phone" name="phone" required class="nailedit-input w-full rounded-full px-4 min-h-[48px] bg-white/20 text-primary placeholder-primary/60 border border-third focus:outline-none focus:ring-2 focus:ring-secondary">
                        </div>
                        <div class="flex flex-col gap-1">
                            <label for="email" class="text-sm font-medium text-primary"><?php esc_html_e( 'Email', 'nailedit' ); ?> *</label>
                            <input type="email" id="email" name="email" required class="nailedit-input w-full rounded-full px-4 min-h-[48px] bg-white/20 text-primary placeholder-primary/60 border border-third focus:outline-none focus:ring-2 focus:ring-secondary">
                        </div>
                    </div>

                    <div class="border-t border-slate-200 pt-6">
                        <h3 class="text-base font-semibold text-slate-900 mb-4"><?php esc_html_e( 'Muuda parooli', 'nailedit' ); ?></h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex flex-col gap-1 md:col-span-2">
                                <label for="current_password" class="text-sm font-medium text-primary"><?php esc_html_e( 'Praegune parool', 'nailedit' ); ?></label>
                                <input type="password" id="current_password" name="current_password" class="nailedit-input w-full rounded-full px-4 min-h-[48px] bg-white/20 text-primary placeholder-primary/60 border border-third focus:outline-none focus:ring-2 focus:ring-secondary">
                            </div>
                            <div class="flex flex-col gap-1">
                                <label for="new_password" class="text-sm font-medium text-primary"><?php esc_html_e( 'Uus parool', 'nailedit' ); ?></label>
                                <input type="password" id="new_password" name="new_password" class="nailedit-input w-full rounded-full px-4 min-h-[48px] bg-white/20 text-primary placeholder-primary/60 border border-third focus:outline-none focus:ring-2 focus:ring-secondary">
                            </div>
                            <div class="flex flex-col gap-1">
                                <label for="new_password_confirmation" class="text-sm font-medium text-primary"><?php esc_html_e( 'Uus parool uuesti', 'nailedit' ); ?></label>
                                <input type="password" id="new_password_confirmation" name="new_password_confirmation" class="nailedit-input w-full rounded-full px-4 min-h-[48px] bg-white/20 text-primary placeholder-primary/60 border border-third focus:outline-none focus:ring-2 focus:ring-secondary">
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-slate-200 pt-6">
                        <div class="flex flex-col gap-4">
                           
                            <div class="flex items-center gap-2">
                                <input type="checkbox" id="subscribed_to_news_letter" name="subscribed_to_news_letter" value="1" class="h-4 w-4 text-secondary border-slate-300 rounded focus:ring-secondary">
                                <label for="subscribed_to_news_letter" class="text-sm text-slate-700"><?php esc_html_e( 'Tahan uudiskirja', 'nailedit' ); ?></label>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="_method" value="PUT">

                    <div class="flex flex-col gap-2 pt-4">
                        <button type="submit" class="inline-flex items-center justify-center rounded-full bg-primary px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-primary/90 transition">
                            <?php esc_html_e( 'Salvesta profiil', 'nailedit' ); ?>
                        </button>
                        <div id="nailedit-profile-success" class="text-sm text-emerald-600 min-h-[1.25rem]"></div>
                        <div id="nailedit-profile-error" class="text-sm text-red-600 min-h-[1.25rem]"></div>
                    </div>
                </form>
            </section>
        </div>
    </div>
</main>

<script>
(document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('nailedit-profile-form');
    const requireLogin = document.getElementById('nailedit-profile-require-login');
    const successEl = document.getElementById('nailedit-profile-success');
    const errorEl = document.getElementById('nailedit-profile-error');

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
            requireLogin.classList.remove('hidden');
        }
        form.classList.add('hidden');
        return;
    }

    // Prefill fields from stored customer data
    if (customer.first_name) document.getElementById('first_name').value = customer.first_name;
    if (customer.last_name) document.getElementById('last_name').value = customer.last_name;
    if (customer.gender) document.getElementById('gender').value = customer.gender;
    if (customer.date_of_birth) document.getElementById('date_of_birth').value = customer.date_of_birth;
    if (customer.phone) document.getElementById('phone').value = customer.phone;
    if (customer.email) document.getElementById('email').value = customer.email;
    if (customer.subscribed_to_news_letter === '1' || customer.subscribed_to_news_letter === 1 || customer.subscribed_to_news_letter === true) {
        document.getElementById('subscribed_to_news_letter').checked = true;
    }

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        if (successEl) successEl.textContent = '';
        if (errorEl) errorEl.textContent = '';

        const fd = new FormData(form);
        fd.append('action', 'nailedit_customer_profile');

        const storedCookie = getStoredAuthCookie();
        if (storedCookie) {
            fd.append('stored_cookie', storedCookie);
        }

        const storedToken = getStoredAuthToken();
        if (storedToken) {
            fd.append('auth_token', storedToken);
        }

        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = '<?php echo esc_js( __( 'Salvestan...', 'nailedit' ) ); ?>';
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
                let msg = data.message || result.message || 'Midagi läks valesti.';
                if (data.errors) {
                    const firstField = Object.keys(data.errors)[0];
                    if (firstField && data.errors[firstField] && data.errors[firstField][0]) {
                        msg = data.errors[firstField][0];
                    }
                }
                if (errorEl) errorEl.textContent = msg;
                return;
            }

            const customerData = data.data || data;
            try {
                localStorage.setItem('nailedit_customer', JSON.stringify(customerData));
            } catch (e) {
                // ignore
            }

            if (successEl) {
                successEl.textContent = data.message || '<?php echo esc_js( __( 'Profiil on edukalt uuendatud.', 'nailedit' ) ); ?>';
            }
        })
        .catch(err => {
            console.error('Profile update error:', err);
            if (errorEl) errorEl.textContent = 'Midagi läks valesti: ' + err.message;
        })
        .finally(() => {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = '<?php echo esc_js( __( 'Salvesta profiil', 'nailedit' ) ); ?>';
            }
        });
    });
}));
</script>

<?php
get_footer();
