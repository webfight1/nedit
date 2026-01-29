<?php
/**
 * Template Name: Customer Register Page
 * Description: Bagisto customer registration form via AJAX
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<?php get_template_part( 'template-parts/page-header' ); ?>

<main class="site-main nailedit-register-page max-w-[1200px] mx-auto">

    <div class="nailedit-register-wrapper max-w-md mx-auto bg-white/80 rounded-3xl shadow-lg p-6 md:p-8 mt-6">
        <form id="nailedit-register-form" class="space-y-4">
            <div class="nailedit-form-row flex flex-col gap-1">
                <label for="nailedit-first-name" class="text-sm font-medium text-primary"><?php esc_html_e( 'First name', 'nailedit' ); ?> *</label>
                <input
                    type="text"
                    id="nailedit-first-name"
                    name="first_name"
                    required
                    class="w-full rounded-full px-4 min-h-[51px] bg-white/20 text-primary placeholder-primary/60 border border-third focus:outline-none focus:ring-2 focus:ring-secondary"
                >
            </div>
            <div class="nailedit-form-row flex flex-col gap-1">
                <label for="nailedit-last-name" class="text-sm font-medium text-primary"><?php esc_html_e( 'Last name', 'nailedit' ); ?> *</label>
                <input
                    type="text"
                    id="nailedit-last-name"
                    name="last_name"
                    required
                    class="w-full rounded-full px-4 min-h-[51px] bg-white/20 text-primary placeholder-primary/60 border border-third focus:outline-none focus:ring-2 focus:ring-secondary"
                >
            </div>
            <div class="nailedit-form-row flex flex-col gap-1">
                <label for="nailedit-email" class="text-sm font-medium text-primary"><?php esc_html_e( 'Email', 'nailedit' ); ?> *</label>
                <input
                    type="email"
                    id="nailedit-email"
                    name="email"
                    required
                    class="w-full rounded-full px-4 min-h-[51px] bg-white/20 text-primary placeholder-primary/60 border border-third focus:outline-none focus:ring-2 focus:ring-secondary"
                >
            </div>
            <div class="nailedit-form-row flex flex-col gap-1">
                <label for="nailedit-password" class="text-sm font-medium text-primary"><?php esc_html_e( 'Password', 'nailedit' ); ?> *</label>
                <input
                    type="password"
                    id="nailedit-password"
                    name="password"
                    required
                    class="w-full rounded-full px-4 min-h-[51px] bg-white/20 text-primary placeholder-primary/60 border border-third focus:outline-none focus:ring-2 focus:ring-secondary"
                >
            </div>
            <div class="nailedit-form-row flex flex-col gap-1">
                <label for="nailedit-password-confirm" class="text-sm font-medium text-primary"><?php esc_html_e( 'Confirm password', 'nailedit' ); ?> *</label>
                <input
                    type="password"
                    id="nailedit-password-confirm"
                    name="password_confirmation"
                    required
                    class="w-full rounded-full px-4 min-h-[51px] bg-white/20 text-primary placeholder-primary/60 border border-third focus:outline-none focus:ring-2 focus:ring-secondary"
                >
            </div>

            <div class="nailedit-form-actions pt-2">
                <button
                    type="submit"
                    class="w-full rounded-full min-h-[51px] px-4 bg-secondary text-primary font-medium hover:bg-fourth transition"
                >
                    <?php esc_html_e( 'Register', 'nailedit' ); ?>
                </button>
            </div>
        </form>

        <div id="nailedit-register-success" class="nailedit-message nailedit-message-success" style="display:none;"></div>
        <div id="nailedit-register-error" class="nailedit-message nailedit-message-error" style="display:none;"></div>
    </div>
</main>

<script>
(function() {
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('nailedit-register-form');
        if (!form) return;

        const successEl = document.getElementById('nailedit-register-success');
        const errorEl   = document.getElementById('nailedit-register-error');

        function showMessage(el, msg) {
            if (!el) return;
            el.textContent = msg || '';
            el.style.display = msg ? 'block' : 'none';
        }

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            showMessage(successEl, '');
            showMessage(errorEl, '');

            const formData = new FormData(form);
            formData.append('action', 'nailedit_customer_register');

            fetch('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data && data.success) {
                    const msg = (data.message) ? data.message : '<?php echo esc_js( __( 'Customer registered successfully.', 'nailedit' ) ); ?>';
                    showMessage(successEl, msg);
                    form.reset();
                } else {
                    const msg = (data && data.message) ? data.message : '<?php echo esc_js( __( 'Registration failed.', 'nailedit' ) ); ?>';
                    showMessage(errorEl, msg);
                }
            })
            .catch(function(err) {
                console.error('Registration error', err);
                showMessage(errorEl, '<?php echo esc_js( __( 'Unexpected error during registration.', 'nailedit' ) ); ?>');
            });
        });
    });
})();
</script>

<?php
get_footer();
