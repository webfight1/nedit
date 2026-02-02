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

        <div id="nailedit-checkout-loader" class="fixed inset-0 z-50 hidden items-center justify-center bg-white/70 backdrop-blur-sm">
            <div class="flex flex-col items-center gap-3 rounded-2xl bg-white/90 px-6 py-4 shadow-xl">
                <svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><style>.spinner_6kVp{transform-origin:center;animation:spinner_irSm .75s infinite linear}@keyframes spinner_irSm{100%{transform:rotate(360deg)}}</style><path d="M10.72,19.9a8,8,0,0,1-6.5-9.79A7.77,7.77,0,0,1,10.4,4.16a8,8,0,0,1,9.49,6.52A1.54,1.54,0,0,0,21.38,12h.13a1.37,1.37,0,0,0,1.38-1.54,11,11,0,1,0-12.7,12.39A1.54,1.54,0,0,0,12,21.34h0A1.47,1.47,0,0,0,10.72,19.9Z" class="spinner_6kVp"/></svg>
                <p class="text-sm font-medium text-slate-700"><?php esc_html_e( 'Laen...', 'nailedit' ); ?></p>
            </div>
        </div>

        <div id="nailedit-checkout-wrapper" class="grid gap-8 md:grid-cols-[minmax(0,2fr)_minmax(0,1fr)] items-start">
            <section id="nailedit-checkout-form-section" class="space-y-6">
                <div class="bg-white/80 rounded-3xl shadow-lg p-6 md:p-8 flex flex-col gap-6" data-checkout-step="address">
                    <div class="flex items-center justify-between gap-4 ">
                        <div class="flex items-center gap-3 cursor-pointer" data-step-toggle data-step-target="address">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-secondary text-primary text-sm font-semibold">1</span>
                            <div>
                                <h2 class="text-lg font-semibold text-slate-900"><?php esc_html_e( 'Aadress', 'nailedit' ); ?></h2>
                            </div>
                        </div>
                        <button type="button" class="text-xs font-semibold uppercase tracking-[0.16em] text-primary hover:text-secondary hidden" data-step-edit data-step-target="address">
                            <?php esc_html_e( 'Muuda', 'nailedit' ); ?>
                        </button>
                    </div>

                    <div data-step-body>
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

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="flex flex-col gap-1">
                                        <label for="billing_email" class="text-sm font-medium text-primary"><?php esc_html_e( 'E-post', 'nailedit' ); ?> *</label>
                                        <input id="billing_email" name="billing_email" type="email" required class="w-full rounded-full px-4 min-h-[48px] bg-white/20 text-primary placeholder-primary/60 border border-third focus:outline-none focus:ring-2 focus:ring-secondary" />
                                    </div>
                                    <div class="flex flex-col gap-1">
                                        <label for="billing_phone" class="text-sm font-medium text-primary"><?php esc_html_e( 'Telefon', 'nailedit' ); ?> *</label>
                                        <input id="billing_phone" name="billing_phone" type="text" required class="w-full rounded-full px-4 min-h-[48px] bg-white/20 text-primary placeholder-primary/60 border border-third focus:outline-none focus:ring-2 focus:ring-secondary" />
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 pt-2">
                                    <input id="billing_is_company" name="billing_is_company" type="checkbox" value="1" class="h-4 w-4 rounded border-slate-300 text-secondary focus:ring-secondary" />
                                    <label for="billing_is_company" class="text-xs text-slate-700"><?php esc_html_e( 'Soovin osta ettevõttele', 'nailedit' ); ?></label>
                                </div>
                            </div>

                            <div id="nailedit-company-fields" class="space-y-4 border-t border-slate-100 pt-6 mt-4 hidden">
                                <h3 class="text-sm font-semibold text-slate-800 uppercase tracking-[0.12em]"><?php esc_html_e( 'Ettevõtte andmed', 'nailedit' ); ?></h3>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="flex flex-col gap-1">
                                        <label for="billing_company_name" class="text-sm font-medium text-primary"><?php esc_html_e( 'Ettevõte', 'nailedit' ); ?> *</label>
                                        <input id="billing_company_name" name="billing_company_name" type="text" class="w-full rounded-full px-4 min-h-[48px] bg-white/20 text-primary placeholder-primary/60 border border-third focus:outline-none focus:ring-2 focus:ring-secondary" />
                                    </div>
                                    <div class="flex flex-col gap-1">
                                        <label for="billing_company_reg" class="text-sm font-medium text-primary"><?php esc_html_e( 'Reg. kood', 'nailedit' ); ?> *</label>
                                        <input id="billing_company_reg" name="billing_company_reg" type="text" class="w-full rounded-full px-4 min-h-[48px] bg-white/20 text-primary placeholder-primary/60 border border-third focus:outline-none focus:ring-2 focus:ring-secondary" />
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="flex flex-col gap-1">
                                        <label for="billing_company_vat" class="text-sm font-medium text-primary"><?php esc_html_e( 'KMKNR kood', 'nailedit' ); ?></label>
                                        <input id="billing_company_vat" name="billing_company_vat" type="text" class="w-full rounded-full px-4 min-h-[48px] bg-white/20 text-primary placeholder-primary/60 border border-third focus:outline-none focus:ring-2 focus:ring-secondary" />
                                    </div>
                                    <div class="flex flex-col gap-1">
                                        <label for="billing_address" class="text-sm font-medium text-primary"><?php esc_html_e( 'Aadress', 'nailedit' ); ?> *</label>
                                        <input id="billing_address" name="billing_address[]" type="text" class="w-full rounded-full px-4 min-h-[48px] bg-white/20 text-primary placeholder-primary/60 border border-third focus:outline-none focus:ring-2 focus:ring-secondary" />
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="flex flex-col gap-1">
                                        <label for="billing_city" class="text-sm font-medium text-primary"><?php esc_html_e( 'Linn', 'nailedit' ); ?> *</label>
                                        <input id="billing_city" name="billing_city" type="text" class="w-full rounded-full px-4 min-h-[48px] bg-white/20 text-primary placeholder-primary/60 border border-third focus:outline-none focus:ring-2 focus:ring-secondary" />
                                    </div>
                                    <div class="flex flex-col gap-1">
                                        <label for="billing_state" class="text-sm font-medium text-primary"><?php esc_html_e( 'Maakond', 'nailedit' ); ?> *</label>
                                        <input id="billing_state" name="billing_state" type="text" class="w-full rounded-full px-4 min-h-[48px] bg-white/20 text-primary placeholder-primary/60 border border-third focus:outline-none focus:ring-2 focus:ring-secondary" />
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="flex flex-col gap-1">
                                        <label for="billing_postcode" class="text-sm font-medium text-primary"><?php esc_html_e( 'Sihtnumber', 'nailedit' ); ?> *</label>
                                        <input id="billing_postcode" name="billing_postcode" type="text" class="w-full rounded-full px-4 min-h-[48px] bg-white/20 text-primary placeholder-primary/60 border border-third focus:outline-none focus:ring-2 focus:ring-secondary" />
                                    </div>
                                    <div class="flex flex-col gap-1">
                                        <label for="billing_country" class="text-sm font-medium text-primary"><?php esc_html_e( 'Riik', 'nailedit' ); ?> *</label>
                                        <input id="billing_country" name="billing_country" type="text" class="w-full rounded-full px-4 min-h-[48px] bg-white/20 text-primary placeholder-primary/60 border border-third focus:outline-none focus:ring-2 focus:ring-secondary" />
                                    </div>
                                </div>
                            </div>

                            <div class="pt-2">
                                <button type="submit" id="nailedit-checkout-address-submit" class="w-full rounded-full min-h-[51px] px-4 bg-secondary text-primary font-semibold hover:bg-fourth transition">
                                    <?php esc_html_e( 'Jätka tarneviisiga', 'nailedit' ); ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="bg-white/80 rounded-3xl shadow-lg p-6 md:p-8  flex flex-col gap-6 opacity-50 pointer-events-none" data-checkout-step="shipping">
                    <div class="flex items-center justify-between gap-4 ">
                        <div class="flex items-center gap-3 cursor-pointer" data-step-toggle data-step-target="shipping">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-secondary text-primary text-sm font-semibold">2</span>
                            <div>
                                <h2 class="text-lg font-semibold text-slate-900"><?php esc_html_e( 'Tarneviis', 'nailedit' ); ?></h2>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-xs text-slate-600 hidden" data-step-summary="shipping"></span>
                            <button type="button" class="text-xs font-semibold uppercase tracking-[0.16em] text-primary hover:text-secondary hidden" data-step-edit data-step-target="shipping">
                                <?php esc_html_e( 'Muuda', 'nailedit' ); ?>
                            </button>
                        </div>
                    </div>

                    <div data-step-body class="hidden space-y-4">
                        <div id="nailedit-shipping-methods" class="space-y-2">
                            <div class="text-xs text-slate-600 italic"><?php esc_html_e( 'Tarneviisid ilmuvad siia pärast aadressi salvestamist.', 'nailedit' ); ?></div>
                        </div>
                        <div id="nailedit-shipping-validation" class="text-xs text-red-600"></div>
                        <div id="nailedit-omniva-pickup" class="hidden mt-3 space-y-2">
                            <div class="text-xs font-semibold tracking-[0.14em] text-slate-500 uppercase"><?php esc_html_e( 'Pakiautomaat', 'nailedit' ); ?></div>
                            <input id="nailedit-omniva-search" type="text" class="w-full rounded-full px-4 min-h-[44px] bg-white/20 text-primary placeholder-primary/60 border border-third focus:outline-none focus:ring-2 focus:ring-secondary" placeholder="<?php echo esc_attr( __( 'Otsi automaati...', 'nailedit' ) ); ?>" />
                            <select id="nailedit-omniva-location" class="w-full rounded-full px-4 min-h-[44px] bg-white/20 text-primary border border-third focus:outline-none focus:ring-2 focus:ring-secondary"></select>
                            <div id="nailedit-omniva-note" class="text-xs text-slate-600"></div>
                        </div>
                    </div>
                </div>

                <div class="bg-white/80 rounded-3xl shadow-lg p-6 md:p-8 flex flex-col gap-6 opacity-50 pointer-events-none" data-checkout-step="payment">
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3 cursor-pointer" data-step-toggle data-step-target="payment">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-secondary text-primary text-sm font-semibold">3</span>
                            <div>
                                <h2 class="text-lg font-semibold text-slate-900"><?php esc_html_e( 'Makseviis', 'nailedit' ); ?></h2>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-xs text-slate-600 hidden" data-step-summary="payment"></span>
                            <button type="button" class="text-xs font-semibold uppercase tracking-[0.16em] text-primary hover:text-secondary hidden" data-step-edit data-step-target="payment">
                                <?php esc_html_e( 'Muuda', 'nailedit' ); ?>
                            </button>
                        </div>
                    </div>

                    <div data-step-body class="hidden space-y-4">
                        <div id="nailedit-payment-methods" class="space-y-2">
                            <div class="text-xs text-slate-600 italic"><?php esc_html_e( 'Makseviisid ilmuvad siia pärast tarneviisi salvestamist.', 'nailedit' ); ?></div>
                        </div>
                    </div>
                </div>

                <div class="bg-white/80 rounded-3xl shadow-lg p-6 md:p-8  flex flex-col gap-6 opacity-50 pointer-events-none" data-checkout-step="confirm">
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3 cursor-pointer" data-step-toggle data-step-target="confirm">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-secondary text-primary text-sm font-semibold">4</span>
                            <div>
                                <h2 class="text-lg font-semibold text-slate-900"><?php esc_html_e( 'Kinnita tellimus', 'nailedit' ); ?></h2>
                            </div>
                        </div>
                        <button type="button" class="text-xs font-semibold uppercase tracking-[0.16em] text-primary hover:text-secondary hidden" data-step-edit data-step-target="confirm">
                            <?php esc_html_e( 'Muuda', 'nailedit' ); ?>
                        </button>
                    </div>

                    <div data-step-body class="hidden space-y-4">
                        <div id="nailedit-checkout-address-error" class="text-sm text-red-600 min-h-[20px]"></div>
                        <button type="button" id="nailedit-checkout-submit" class="w-full rounded-full min-h-[51px] px-4 bg-secondary text-primary font-semibold hover:bg-fourth transition">
                            <?php esc_html_e( 'Esita tellimus', 'nailedit' ); ?>
                        </button>
                    </div>
                </div>
            </section>

            <aside id="nailedit-checkout-summary" class="bg-white/80 rounded-3xl shadow-lg p-6 md:p-8 md:sticky md:top-24">
                <h2 class="text-lg font-semibold text-slate-900 mb-4"><?php esc_html_e( 'Tellimuse kokkuvõte', 'nailedit' ); ?></h2>
                <div id="nailedit-checkout-summary-body" class="text-sm text-slate-700 space-y-2 mb-4">
                    <p><?php esc_html_e( 'Laen ostukorvi...', 'nailedit' ); ?></p>
                </div>
            </aside>
        </div>
    </div>
</main>

<?php
get_footer();
