<?php
/**
 * Template Name: Customer Orders
 * Description: Customer orders list using Bagisto API
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<?php get_template_part( 'template-parts/page-header' ); ?>

<main class="site-main nailedit-orders-page py-10">
    <div class="max-w-[1200px] mx-auto px-4">
     

        <div id="nailedit-orders-require-login" class="mb-6 hidden text-center text-sm text-red-600">
            <p><?php nailedit_t( 'login_to_view_orders' ); ?></p>
        </div>

        <section class="bg-white/80 rounded-3xl shadow-lg p-6 md:p-8">
            <h2 class="text-lg font-semibold text-slate-900 mb-4"><?php nailedit_t( 'your_orders' ); ?></h2>
            <div id="nailedit-orders-container" class="space-y-4"></div>
            <div id="nailedit-orders-error" class="mt-3 text-sm text-red-600"></div>
        </section>
    </div>
</main>

<script>
(document.addEventListener('DOMContentLoaded', function() {
    const requireLogin = document.getElementById('nailedit-orders-require-login');
    const container = document.getElementById('nailedit-orders-container');
    const errorEl = document.getElementById('nailedit-orders-error');

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
        if (requireLogin) requireLogin.style.display = 'block';
        return;
    }

    function formatOrderDate(value) {
        if (!value) return '';
        const parsed = new Date(value.replace(' ', 'T'));
        if (Number.isNaN(parsed.getTime())) {
            return value;
        }
        return parsed.toLocaleDateString('<?php echo esc_js( nailedit_get_current_lang() === 'en' ? 'en-US' : 'et-EE' ); ?>', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
        });
    }

    function renderOrders(data) {
        const items = data.data || data || [];
        if (!items || !items.length) {
            container.innerHTML = '<p class="text-sm text-slate-600"><?php echo esc_js( nailedit_get_t( 'no_orders_yet' ) ); ?></p>';
            return;
        }

        container.innerHTML = items.map(function(order) {
            const id = order.id || '';
            const number = order.increment_id || ('#' + id);
            const status = order.status || '';
            const total = order.formatted_grand_total || order.grand_total || '';
            const method = order.payment_title || '';
            const createdAt = formatOrderDate(order.created_at || '');

            let itemSummary = '';
            if (order.items) {
                const it = Array.isArray(order.items) ? order.items : [order.items];
                if (it.length) {
                    itemSummary = '<ul class="list-disc pl-4">' + it.map(function(item) {
                        const qty = item.qty_ordered || '';
                        const name = item.name || '';
                        return '<li>' + qty + ' × ' + name + '</li>';
                    }).join('') + '</ul>';
                }
            }

            return '<div class="nailedit-order-item" style="border:1px solid #ddd;padding:10px;margin-bottom:8px;">'
                + '<strong><?php echo esc_js( nailedit_get_t( 'order' ) ); ?> ' + number + '</strong><br>'
                + (createdAt ? '<strong><?php echo esc_js( nailedit_get_t( 'date' ) ); ?> </strong>' + createdAt + '<br>' : '')
                + (status ? '<strong><?php echo esc_js( nailedit_get_t( 'status' ) ); ?> </strong>' + status + '<br>' : '')
                + (total ? '<strong><?php echo esc_js( nailedit_get_t( 'total' ) ); ?>: </strong>' + total + '<br>' : '')
                + (method ? '<strong><?php echo esc_js( nailedit_get_t( 'payment_type' ) ); ?> </strong>' + method + '<br>' : '')
                + (itemSummary ? '<strong><?php echo esc_js( nailedit_get_t( 'products_label' ) ); ?> </strong>' + itemSummary : '')
                + '</div>';
        }).join('');
    }

    function loadOrders(page) {
        if (errorEl) errorEl.textContent = '';
        if (!page) page = 1;

        const fd = new FormData();
        fd.append('action', 'nailedit_customer_orders');
        fd.append('page', page);
        fd.append('limit', 10);

        const storedCookie = getStoredAuthCookie();
        if (storedCookie) fd.append('stored_cookie', storedCookie);
        const storedToken = getStoredAuthToken();
        if (storedToken) fd.append('auth_token', storedToken);

        fetch('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', {
            method: 'POST',
            body: fd
        })
        .then(r => r.json())
        .then(result => {
            const ok = result && result.success;
            const data = result && result.data ? result.data : {};

            if (!ok) {
                let msg = data.message || result.message || '<?php echo esc_js( nailedit_get_t( 'orders_load_failed' ) ); ?>';
                if (errorEl) errorEl.textContent = msg;
                return;
            }

            renderOrders(data);
        })
        .catch(err => {
            console.error('Orders load error:', err);
            if (errorEl) errorEl.textContent = '<?php echo esc_js( nailedit_get_t( 'orders_generic_error' ) ); ?>' + err.message;
        });
    }

    loadOrders(1);
}));
</script>

<?php
get_footer();
