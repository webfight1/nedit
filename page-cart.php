<?php
/**
 * Template Name: Cart Page
 * Description: Shows Bagisto customer cart contents via AJAX
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<?php get_template_part( 'template-parts/page-header' ); ?>

<main class="site-main nailedit-cart-page   py-10">
    <div class="max-w-[1200px] mx-auto px-4">
       

        <div id="nailedit-cart-root" class="bg-white rounded-24 shadow-lg p-4 md:p-6 min-h-[160px] flex items-start justify-center flex-col">
            <p class="text-sm text-slate-500"><?php esc_html_e( 'Laen ostukorvi...', 'nailedit' ); ?></p>
        </div>
    </div>
</main>

<script>
(function() {
    document.addEventListener('DOMContentLoaded', function() {
        const root = document.getElementById('nailedit-cart-root');
        if (!root) return;

        function renderCart(data) {
            if (!data || !data.data) {
                root.innerHTML = '<p><?php echo esc_js( __( 'Sinu ostukorv on tühi.', 'nailedit' ) ); ?></p>';
                return;
            }

            const payload = data.data;
            const cart = (payload && payload.data && payload.data.cart) ? payload.data.cart : (payload.data || payload);
            const items = Array.isArray(cart.items) ? cart.items : (cart.items ? [cart.items] : []);

            if (!items.length) {
                root.innerHTML = '<p><?php echo esc_js( __( 'Sinu ostukorv on tühi.', 'nailedit' ) ); ?></p>';
                return;
            }

            let html = '';
            html += '<div class="w-full overflow-x-auto">';
            html += '<table class="nailedit-cart-table w-full text-[13px] md:text-[14px] text-slate-800">';
            html += '<thead>' +
                '<tr class="bg-slate-100 text-slate-600 text-left text-[12px] uppercase tracking-wide">' +
                    '<th class="px-3 py-2 rounded-l-lg"><?php echo esc_js( __( 'Toode', 'nailedit' ) ); ?></th>' +
                    '<th class="px-3 py-2 text-center"><?php echo esc_js( __( 'Kogus', 'nailedit' ) ); ?></th>' +
                    '<th class="px-3 py-2 text-right"><?php echo esc_js( __( 'Hind', 'nailedit' ) ); ?></th>' +
                    '<th class="px-3 py-2 text-right"><?php echo esc_js( __( 'Kokku', 'nailedit' ) ); ?></th>' +
                    '<th class="px-3 py-2 text-right rounded-r-lg"><?php echo esc_js( __( 'Tegevused', 'nailedit' ) ); ?></th>' +
                '</tr>' +
            '</thead>';
            html += '<tbody class="divide-y divide-slate-100">';

            items.forEach(function(item) {
                const name  = item.name || '';
                const qty   = item.quantity || 0;
                const price = item.formatted_price || (item.price != null ? item.price : '');
                const total = item.formatted_total || (item.total != null ? item.total : '');
                const itemId = item.id || 0;

                html += '<tr data-item-id="' + itemId + '" class="hover:bg-slate-50 transition-colors">' +
                    '<td class="px-3 py-3 align-middle font-medium text-slate-900">' + (name ? name : '') + '</td>' +
                    '<td class="px-3 py-3 align-middle text-center text-slate-700">' +
                        '<div class="inline-flex items-center gap-2 rounded-full border border-primary bg-white px-3 py-1 shadow-sm justify-center">' +
                            '<button type="button" class="nailedit-cart-qty-minus w-6 h-6 rounded-md bg-slate-100 border border-slate-200 text-slate-700 text-xs flex items-center justify-center" data-item-id="' + itemId + '">-</button>' +
                            '<input type="number" min="1" step="1" value="' + qty + '" data-item-id="' + itemId + '" class="nailedit-cart-qty-input w-8 h-6 text-center text-[13px] bg-transparent border-0 focus:outline-none appearance-none" />' +
                            '<button type="button" class="nailedit-cart-qty-plus w-6 h-6 rounded-md bg-slate-100 border border-slate-200 text-slate-700 text-xs flex items-center justify-center" data-item-id="' + itemId + '">+</button>' +
                        '</div>' +
                    '</td>' +
                    '<td class="px-3 py-3 align-middle text-right text-slate-700">' + (price ? price : '') + '</td>' +
                    '<td class="px-3 py-3 align-middle text-right font-semibold text-slate-900">' + (total ? total : '') + '</td>' +
                    '<td class="px-3 py-3 align-middle text-right">' +
                        '<button class="nailedit-remove-item inline-flex items-center px-3 py-1.5 rounded-full border border-slate-300 text-[12px] text-slate-600 hover:border-primary hover:text-primary transition" data-item-id="' + itemId + '">' +
                            '<?php echo esc_js( __( 'Kustuta', 'nailedit' ) ); ?>' +
                        '</button>' +
                    '</td>' +
                '</tr>';
            });

            html += '</tbody>';
            html += '</table>';
            html += '</div>';

            const grand = cart.formatted_grand_total || (cart.grand_total != null ? cart.grand_total : '');

            html += '<div class="nailedit-cart-summary mt-6 border-t border-slate-100 pt-4 flex flex-col gap-4 self-end w-full md:w-auto">';
            if (grand) {
                html += '<div class="flex flex-col items-end">' +
                    '<div class="text-xs uppercase tracking-wide text-slate-500 mb-1"><?php echo esc_js( __( 'Tellimuse kokkuvõte', 'nailedit' ) ); ?></div>' +
                    '<p class="text-[15px] md:text-[16px] text-slate-900"><span class="font-semibold mr-1"><?php echo esc_js( __( 'Summa kokku:', 'nailedit' ) ); ?></span>' + grand + '</p>' +
                '</div>';
            }

            // Coupon form
            html += '<div class="mt-2 flex flex-col md:flex-row md:items-center md:justify-between gap-3">' +
                '<div class="text-sm text-slate-600"><?php echo esc_js( __( 'Kas sul on kupongikood?', 'nailedit' ) ); ?></div>' +
                '<div class="flex flex-col items-stretch md:items-end gap-1">' +
                    '<div class="flex flex-wrap items-center gap-2">' +
                        '<input type="text" id="nailedit-cart-coupon" class="border border-slate-300 rounded-full px-3 py-1.5 text-sm focus:outline-none focus:border-primary min-w-[160px]" placeholder="<?php echo esc_js( __( 'Kupongikood', 'nailedit' ) ); ?>" />' +
                        '<button type="button" id="nailedit-cart-apply-coupon" class="inline-flex items-center px-4 py-1.5 rounded-full bg-secondary text-slate-900 text-sm font-semibold hover:bg-fourth hover:text-white transition"><?php echo esc_js( __( 'Rakenda', 'nailedit' ) ); ?></button>' +
                        '<button type="button" id="nailedit-cart-remove-coupon" class="inline-flex items-center px-3 py-1.5 rounded-full border border-slate-300 text-slate-600 text-xs font-medium hover:border-primary hover:text-primary transition"><?php echo esc_js( __( 'Eemalda kupong', 'nailedit' ) ); ?></button>' +
                    '</div>' +
                    '<div id="nailedit-cart-coupon-message" class="text-xs text-slate-500 min-h-[16px]"></div>' +
                '</div>' +
            '</div>';

            // Checkout button
            html += '<div class="mt-4 flex justify-end">' +
                '<a href="<?php echo esc_url( home_url( '/kassa/' ) ); ?>" class="inline-flex items-center px-5 py-2.5 rounded-full bg-primary text-white text-sm font-semibold shadow-md hover:bg-primary/90 transition">' +
                    '<?php echo esc_js( __( 'Mine kassasse', 'nailedit' ) ); ?>' +
                '</a>' +
            '</div>';

            html += '</div>';

            root.innerHTML = html;

            // Add event listeners to remove buttons
            const removeButtons = document.querySelectorAll('.nailedit-remove-item');
            removeButtons.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const itemId = this.getAttribute('data-item-id');
                    if (itemId && confirm('<?php echo esc_js( __( 'Kas oled kindel, et soovid selle toote eemaldada?', 'nailedit' ) ); ?>')) {
                        removeCartItem(itemId);
                    }
                });
            });

            // Quantity +/- controls
            const minusButtons = document.querySelectorAll('.nailedit-cart-qty-minus');
            const plusButtons  = document.querySelectorAll('.nailedit-cart-qty-plus');

            minusButtons.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const itemId = this.getAttribute('data-item-id');
                    const input  = root.querySelector('.nailedit-cart-qty-input[data-item-id="' + itemId + '"]');
                    if (!input) return;
                    let current = parseInt(input.value || '1', 10);
                    if (isNaN(current) || current <= 1) {
                        current = 1;
                    } else {
                        current = current - 1;
                    }
                    input.value = current;
                    updateCartQuantity(itemId, current);
                });
            });

            plusButtons.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const itemId = this.getAttribute('data-item-id');
                    const input  = root.querySelector('.nailedit-cart-qty-input[data-item-id="' + itemId + '"]');
                    if (!input) return;
                    let current = parseInt(input.value || '1', 10);
                    if (isNaN(current) || current < 1) {
                        current = 1;
                    } else {
                        current = current + 1;
                    }
                    input.value = current;
                    updateCartQuantity(itemId, current);
                });
            });

            // Coupon apply handler
            const couponInput        = document.getElementById('nailedit-cart-coupon');
            const couponButton       = document.getElementById('nailedit-cart-apply-coupon');
            const couponRemoveButton = document.getElementById('nailedit-cart-remove-coupon');
            const couponMsg          = document.getElementById('nailedit-cart-coupon-message');

            if (couponButton && couponInput && couponMsg) {
                couponButton.addEventListener('click', function() {
                    const code = (couponInput.value || '').trim();
                    if (!code) {
                        couponMsg.textContent = '<?php echo esc_js( __( 'Please enter a coupon code.', 'nailedit' ) ); ?>';
                        couponMsg.style.color = '#b91c1c';
                        return;
                    }

                    applyCoupon(code, couponMsg, couponButton);
                });
            }

            if (couponRemoveButton && couponMsg) {
                couponRemoveButton.addEventListener('click', function() {
                    removeCoupon(couponMsg, couponRemoveButton);
                });
            }
        }

        function updateCartQuantity(cartItemId, quantity) {
            quantity = parseInt(quantity, 10);
            if (!cartItemId || isNaN(quantity) || quantity < 1) {
                alert('<?php echo esc_js( __( 'Quantity cannot be lesser than one.', 'nailedit' ) ); ?>');
                return;
            }

            const formData = new FormData();
            formData.append('action', 'nailedit_update_cart');
            formData.append('qty[' + cartItemId + ']', quantity);

            const storedCookie = localStorage.getItem('bagisto_cart_cookie');
            const authToken    = localStorage.getItem('bagisto_auth_token');
            const cartToken    = localStorage.getItem('bagisto_guest_cart_token');

            if (storedCookie) {
                formData.append('stored_cookie', storedCookie);
            }
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
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data && data.cart_token) {
                    localStorage.setItem('bagisto_guest_cart_token', data.cart_token);
                }
                if (data && data.success) {
                    loadCart();
                } else {
                    const msg = (data && data.message) ? data.message : '<?php echo esc_js( __( 'Something went wrong!', 'nailedit' ) ); ?>';
                    alert(msg);
                }
            })
            .catch(function(err) {
                console.error('Update cart error:', err);
                alert('<?php echo esc_js( __( 'Viga koguse uuendamisel.', 'nailedit' ) ); ?>');
            });
        }

        function applyCoupon(code, messageEl, buttonEl) {
            const formData = new FormData();
            formData.append('action', 'nailedit_apply_coupon');
            formData.append('coupon', code);

            const storedCookie = localStorage.getItem('bagisto_cart_cookie');
            const authToken    = localStorage.getItem('bagisto_auth_token');
            const cartToken    = localStorage.getItem('bagisto_guest_cart_token');

            if (storedCookie) {
                formData.append('stored_cookie', storedCookie);
            }
            if (authToken) {
                formData.append('auth_token', authToken);
            } else if (cartToken) {
                formData.append('cart_token', cartToken);
            }

            if (buttonEl) {
                buttonEl.disabled = true;
            }
            if (messageEl) {
                messageEl.textContent = '<?php echo esc_js( __( 'Applying coupon...', 'nailedit' ) ); ?>';
                messageEl.style.color = '#4b5563';
            }

            fetch('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data && data.cart_token) {
                    localStorage.setItem('bagisto_guest_cart_token', data.cart_token);
                }
                if (data && data.success) {
                    if (messageEl) {
                        const msg = (data.message) ? data.message : '<?php echo esc_js( __( 'Coupon applied successfully.', 'nailedit' ) ); ?>';
                        messageEl.textContent = msg;
                        messageEl.style.color = '#15803d';
                    }
                    loadCart();
                } else {
                    const msg = (data && data.message) ? data.message : '<?php echo esc_js( __( 'Could not apply coupon.', 'nailedit' ) ); ?>';
                    if (messageEl) {
                        messageEl.textContent = msg;
                        messageEl.style.color = '#b91c1c';
                    }
                }
            })
            .catch(function(err) {
                console.error('Apply coupon error:', err);
                if (messageEl) {
                    messageEl.textContent = '<?php echo esc_js( __( 'Error applying coupon.', 'nailedit' ) ); ?>';
                    messageEl.style.color = '#b91c1c';
                }
            })
            .finally(function() {
                if (buttonEl) {
                    buttonEl.disabled = false;
                }
            });
        }

        function removeCoupon(messageEl, buttonEl) {
            const formData = new FormData();
            formData.append('action', 'nailedit_remove_coupon');

            const storedCookie = localStorage.getItem('bagisto_cart_cookie');
            const authToken    = localStorage.getItem('bagisto_auth_token');
            const cartToken    = localStorage.getItem('bagisto_guest_cart_token');

            if (storedCookie) {
                formData.append('stored_cookie', storedCookie);
            }
            if (authToken) {
                formData.append('auth_token', authToken);
            } else if (cartToken) {
                formData.append('cart_token', cartToken);
            }

            if (buttonEl) {
                buttonEl.disabled = true;
            }
            if (messageEl) {
                messageEl.textContent = '<?php echo esc_js( __( 'Removing coupon...', 'nailedit' ) ); ?>';
                messageEl.style.color = '#4b5563';
            }

            fetch('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data && data.cart_token) {
                    localStorage.setItem('bagisto_guest_cart_token', data.cart_token);
                }
                if (data && data.success) {
                    if (messageEl) {
                        const msg = (data.message) ? data.message : '<?php echo esc_js( __( 'Coupon removed successfully.', 'nailedit' ) ); ?>';
                        messageEl.textContent = msg;
                        messageEl.style.color = '#15803d';
                    }
                    loadCart();
                } else {
                    const msg = (data && data.message) ? data.message : '<?php echo esc_js( __( 'Could not remove coupon.', 'nailedit' ) ); ?>';
                    if (messageEl) {
                        messageEl.textContent = msg;
                        messageEl.style.color = '#b91c1c';
                    }
                }
            })
            .catch(function(err) {
                console.error('Remove coupon error:', err);
                if (messageEl) {
                    messageEl.textContent = '<?php echo esc_js( __( 'Error removing coupon.', 'nailedit' ) ); ?>';
                    messageEl.style.color = '#b91c1c';
                }
            })
            .finally(function() {
                if (buttonEl) {
                    buttonEl.disabled = false;
                }
            });
        }

        function removeCartItem(cartItemId) {
            const formData = new FormData();
            formData.append('action', 'nailedit_remove_cart_item');
            formData.append('cart_item_id', cartItemId);

            const storedCookie = localStorage.getItem('bagisto_cart_cookie');
            const authToken = localStorage.getItem('bagisto_auth_token');
            const cartToken = localStorage.getItem('bagisto_guest_cart_token');

            if (storedCookie) {
                formData.append('stored_cookie', storedCookie);
            }
            if (authToken) {
                formData.append('auth_token', authToken);
            } else if (cartToken) {
                formData.append('cart_token', cartToken);
            }

            // Disable all remove buttons during request
            const allButtons = document.querySelectorAll('.nailedit-remove-item');
            allButtons.forEach(function(btn) { btn.disabled = true; });

            fetch('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data && data.cart_token) {
                    localStorage.setItem('bagisto_guest_cart_token', data.cart_token);
                }
                if (data && data.success) {
                    // Reload cart to show updated state
                    loadCart();
                } else {
                    const msg = (data && data.message) ? data.message : '<?php echo esc_js( __( 'Could not remove item.', 'nailedit' ) ); ?>';
                    alert(msg);
                    // Re-enable buttons
                    allButtons.forEach(function(btn) { btn.disabled = false; });
                }
            })
            .catch(function(err) {
                console.error('Remove item error:', err);
                alert('<?php echo esc_js( __( 'Error removing item.', 'nailedit' ) ); ?>');
                // Re-enable buttons
                allButtons.forEach(function(btn) { btn.disabled = false; });
            });
        }

        function loadCart() {
            const formData = new FormData();
            formData.append('action', 'nailedit_get_cart');

            const storedCookie = localStorage.getItem('bagisto_cart_cookie');
            const authToken    = localStorage.getItem('bagisto_auth_token');
            const cartToken    = localStorage.getItem('bagisto_guest_cart_token');

            if (storedCookie) {
                formData.append('stored_cookie', storedCookie);
            }
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
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data && data.cart_token) {
                    localStorage.setItem('bagisto_guest_cart_token', data.cart_token);
                }
                if (!data || !data.success) {
                    const msg = (data && data.message) ? data.message : '<?php echo esc_js( __( 'Could not load cart.', 'nailedit' ) ); ?>';
                    root.innerHTML = '<p>' + msg + '</p>';
                    return;
                }
                renderCart(data);
            })
            .catch(function(err) {
                console.error('Cart load error', err);
                root.innerHTML = '<p><?php echo esc_js( __( 'Unexpected error while loading cart.', 'nailedit' ) ); ?></p>';
            });
        }

        loadCart();
    });
})();
</script>

<?php
get_footer();
