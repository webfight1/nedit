import { initAddressForm, saveAddress, loadDefaultAddress } from './address.js';
import { loadShippingMethods, saveShipping } from './shipping.js';
import { loadPaymentMethods, savePayment } from './payment.js';
import { placeOrder } from './order.js';
import { updateShippingUI } from './shipping-ui.js';
import { updatePaymentUI } from './payment-ui.js';
import { updateSummary, setSummaryLoading, showCartError, hideCartError } from './summary-ui.js';
import { normalizeCart } from './summary-adapter.js';
import { api } from './api.js';
import * as uiModule from './ui.js';
import { State } from './state.js';
import { initAddressUI } from './address-ui.js';
import { addressFlow } from './flows/address.flow.js';
import { shippingFlow } from './flows/shipping.flow.js';
import { paymentFlow } from './flows/payment.flow.js';
import { orderFlow } from './flows/order.flow.js';

const STEP_ORDER = ['address', 'shipping', 'payment', 'confirm'];

const defaultDeps = {
  document: typeof document !== 'undefined' ? document : null,
  disableAutoBootstrap: false,
  api,
  ui: uiModule,
  State,
  initAddressForm,
  saveAddress,
  loadDefaultAddress,
  loadShippingMethods,
  saveShipping,
  loadPaymentMethods,
  savePayment,
  placeOrder,
  updateShippingUI,
  updatePaymentUI,
  updateSummary,
  setSummaryLoading,
  showCartError,
  hideCartError,
  normalizeCart,
};

let deps = { ...defaultDeps };

export function setCheckoutDeps(overrides = {}) {
  deps = { ...deps, ...overrides };
  return deps;
}

export function getCheckoutDeps() {
  return deps;
}

async function submitCheckout(form) {
  try {
    await orderFlow({
      ui: deps.ui,
      placeOrder: deps.placeOrder,
      saveLastOrder: deps.State.saveLastOrder?.bind(deps.State),
      getString,
    });
  } catch (error) {
    deps.ui.error(error.message || 'Midagi läks valesti!');
    console.error('Checkout submit failed:', error);
  }
}

function getStepEl(step) {
  return deps.document?.querySelector(`[data-checkout-step="${step}"]`);
}

function getStepBody(step) {
  return getStepEl(step)?.querySelector('[data-step-body]');
}

function getStepEdit(step) {
  return getStepEl(step)?.querySelector('[data-step-edit]');
}

function getStepSummary(step) {
  return getStepEl(step)?.querySelector('[data-step-summary]');
}

function getSelectedShippingLabel() {
  const input = deps.document?.querySelector('input[name="nailedit_shipping_method"]:checked');
  return input?.parentElement?.querySelector('span')?.textContent?.trim() || '';
}

function setStepEnabled(step, enabled) {
  const el = getStepEl(step);
  if (!el) return;
  el.classList.toggle('opacity-50', !enabled);
  el.classList.toggle('pointer-events-none', !enabled);
}

function setStepHidden(step, hidden) {
  const el = getStepEl(step);
  if (!el) return;
  el.classList.toggle('hidden', hidden);
}

function setStepOpen(step, open) {
  const body = getStepBody(step);
  if (!body) return;
  body.classList.toggle('hidden', !open);
}

function setStepCompleted(step, completed) {
  const edit = getStepEdit(step);
  if (edit) {
    edit.classList.toggle('hidden', !completed);
  }
}

function setStepSummary(step, value) {
  const summary = getStepSummary(step);
  if (!summary) return;
  const text = (value || '').trim();
  summary.textContent = text;
  summary.classList.toggle('hidden', !text);
}

function resetLaterSteps(fromStep) {
  const startIndex = STEP_ORDER.indexOf(fromStep);
  STEP_ORDER.forEach((step, index) => {
    if (index <= startIndex) return;
    setStepEnabled(step, false);
    setStepOpen(step, false);
    setStepCompleted(step, false);
    setStepHidden(step, false);
  });
}

function openStep(step) {
  STEP_ORDER.forEach((item) => {
    setStepOpen(item, item === step);
  });
}

function markStepComplete(step) {
  setStepCompleted(step, true);
  setStepOpen(step, false);
}

function advanceStep(step) {
  const index = STEP_ORDER.indexOf(step);
  if (index === -1) return;
  let next = STEP_ORDER[index + 1];
  while (next && getStepEl(next)?.classList.contains('hidden')) {
    const ni = STEP_ORDER.indexOf(next);
    next = STEP_ORDER[ni + 1];
  }
  if (!next) return;
  setStepEnabled(next, true);
  openStep(next);
}

function updateShippingSummary() {
  const shippingLabel = getSelectedShippingLabel();
  
  if (isOmnivaShipping(deps.State.shipping) && deps.State.omnivaLocation) {
    const select = deps.document?.getElementById('nailedit-omniva-location');
    const locationLabel = select?.options?.[select.selectedIndex]?.textContent?.trim() || '';
    if (locationLabel) {
      setStepSummary('shipping', [shippingLabel, locationLabel].filter(Boolean).join(' · '));
      return;
    }
  }
  
  if (isSmartpostShipping(deps.State.shipping) && deps.State.smartpostLocation) {
    const select = deps.document?.getElementById('nailedit-smartpost-location');
    const locationLabel = select?.options?.[select.selectedIndex]?.textContent?.trim() || '';
    if (locationLabel) {
      setStepSummary('shipping', [shippingLabel, locationLabel].filter(Boolean).join(' · '));
      return;
    }
  }
  
  if (shippingLabel) {
    setStepSummary('shipping', shippingLabel);
  }
}

async function handleConfirmShipping() {
  console.log('handleConfirmShipping called');
  console.log('Selected shipping:', deps.State.shipping);
  console.log('Omniva location:', deps.State.omnivaLocation);
  console.log('Smartpost location:', deps.State.smartpostLocation);
  
  try {
    const button = deps.document?.getElementById('nailedit-confirm-shipping');
    if (button) button.disabled = true;
    
    // Update summary with selected shipping method and location
    updateShippingSummary();
    
    console.log('Calling shippingFlow...');
    await shippingFlow({
      ui: deps.ui,
      saveShipping: deps.saveShipping,
      updateSummaryFromResult,
      refreshPaymentMethods,
      updatePaymentUI: (response) => {
        return deps.updatePaymentUI(response, {
          onSelect: (paymentMethod) => {
            if (paymentMethod?.label) {
              setStepSummary('payment', paymentMethod.label);
            }
          },
        });
      },
      isOmnivaShipping,
      isSmartpostShipping,
      markStepComplete,
      advanceStep,
      autoPaymentFlow: autoRunPaymentFlow,
      State: deps.State,
    }, deps.State.shipping);
    console.log('shippingFlow completed');
  } catch (error) {
    console.error('handleConfirmShipping error:', error);
    deps.ui.error(error.message || 'Midagi läks valesti!');
  } finally {
    const button = deps.document?.getElementById('nailedit-confirm-shipping');
    if (button) button.disabled = false;
  }
}


async function handleAddressSubmit(form) {
  try {
    const button = deps.document?.getElementById('nailedit-checkout-address-submit');
    if (button) button.disabled = true;
    await addressFlow(
      {
        ui: deps.ui,
        saveAddress: deps.saveAddress,
        refreshShippingMethods,
        updateShippingUI: (response) => {
          return deps.updateShippingUI(response, {
            onSelect: (method) => {
              // Don't auto-trigger shipping flow - wait for confirm button
              // Just update state, toggle pickup UI is handled in shipping-ui.js
            },
          });
        },
        markStepComplete,
        advanceStep,
        getString,
      },
      form,
    );
  } catch (error) {
    deps.ui.error(error.message || 'Midagi läks valesti!');
  } finally {
    const button = deps.document?.getElementById('nailedit-checkout-address-submit');
    if (button) button.disabled = false;
  }
}

function initStepUI() {
  STEP_ORDER.forEach((step, index) => {
    setStepEnabled(step, index === 0);
    setStepOpen(step, index === 0);
    setStepCompleted(step, false);
  });

  
  deps.document?.querySelectorAll('[data-step-toggle]').forEach((toggle) => {
    toggle.addEventListener('click', () => {
      const target = toggle.getAttribute('data-step-target');
      if (!target) return;
      const stepEl = getStepEl(target);
      if (!stepEl || stepEl.classList.contains('pointer-events-none')) return;
      openStep(target);
    });
  });

  deps.document?.querySelectorAll('[data-step-edit]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const target = btn.getAttribute('data-step-target');
      if (!target) return;
      setStepCompleted(target, false);
      openStep(target);
      resetLaterSteps(target);
    });
  });

  const confirmShippingBtn = deps.document?.getElementById('nailedit-confirm-shipping');
  if (confirmShippingBtn) {
    console.log('Confirm shipping button found and click listener attached');
    confirmShippingBtn.addEventListener('click', async () => {
      console.log('Confirm shipping button clicked');
      await handleConfirmShipping();
    });
  } else {
    console.warn('Confirm shipping button NOT found');
  }
}

async function handleChange(event) {
  const target = event.target;
  if (!target) return;

  if (target.name === 'nailedit_shipping_method') {
    deps.State.shipping = target.value || '';
    // Don't auto-save - wait for confirm button
  } else if (target.name === 'nailedit_payment_method') {
    deps.State.payment = target.value || '';
    const label = target.parentElement?.querySelector('span')?.textContent?.trim() || '';
    setStepSummary('payment', label);
    await paymentFlow({
      savePayment: deps.savePayment,
      markStepComplete,
      advanceStep,
    });
  } else if (target.id === 'nailedit-omniva-location') {
    // State.omnivaLocation is already set by omniva.js
    // Don't auto-save - wait for confirm button
  } else if (target.id === 'nailedit-smartpost-location') {
    // State.smartpostLocation is already set by smartpost.js
    // Don't auto-save - wait for confirm button
  }
}

function handleInput(event) {
  const target = event.target;
  if (!target) return;

  if (target.id === 'nailedit-omniva-search') {
    // Placeholder: Omniva filtering will be hooked in dedicated module.
  }
}

export function bootstrap() {
  if (!deps.document) {
    return;
  }

  const form = deps.initAddressForm();
  if (!form) {
    console.warn('Checkout form missing');
    return;
  }

  deps.document.getElementById('nailedit-checkout-address-form')?.addEventListener('submit', (e) => {
    e.preventDefault();
    handleAddressSubmit(e.target);
  });

  const submitBtn = deps.document.getElementById('nailedit-checkout-submit');
  const agreeBox = deps.document.getElementById('nailedit-agree-terms');

  if (submitBtn) {
    submitBtn.addEventListener('click', () => submitCheckout(form));
  }

  if (agreeBox && submitBtn) {
    agreeBox.addEventListener('change', () => {
      submitBtn.disabled = !agreeBox.checked;
    });
  }

  deps.document.addEventListener('change', handleChange);
  deps.document.addEventListener('input', handleInput);

  loadCartData();
  deps.loadDefaultAddress();
  initAddressUI();
  initStepUI();
}

const autoBootstrapDisabled =
  deps.disableAutoBootstrap ||
  (typeof globalThis !== 'undefined' && globalThis.__NAILEDIT_DISABLE_AUTO_BOOTSTRAP__ === true);

if (deps.document && !autoBootstrapDisabled) {
  if (deps.document.readyState === 'loading') {
    deps.document.addEventListener('DOMContentLoaded', bootstrap);
  } else {
    bootstrap();
  }
}

async function loadCartData() {
  deps.setSummaryLoading(getString('cartLoading', 'Laen ostukorvi...'));
  deps.ui.showLoader();
  try {
    const response = await deps.api('nailedit_get_cart', {}, { State: deps.State });
    console.log('[loadCartData] raw response:', response);
    const resolved = resolveCartPayload(response);
    console.log('[loadCartData] resolvedCart:', resolved);
    deps.updateSummary({ cart: resolved });
    deps.hideCartError();
  } catch (error) {
    deps.showCartError(getString('cartRequired', 'Ostukorv on tühi või seda ei õnnestunud laadida.'));
    deps.setSummaryLoading(getString('cartLoadError', 'Ostukorvi ei õnnestunud laadida.'));
    console.error('Cart load failed:', error);
  } finally {
    deps.ui.hideLoader();
  }
}

async function ensureCartHasItems() {
  deps.ui.showLoader();
  try {
    const response = await deps.api('nailedit_get_cart', {}, { State: deps.State });
    const cart = deps.normalizeCart({ cart: resolveCartPayload(response) });
    if (!cart?.items?.length) {
      throw new Error(getString('cartEmpty', 'Sinu ostukorv on tühi.'));
    }
  } catch (error) {
    deps.showCartError(getString('cartRequired', 'Ostukorv on tühi või seda ei õnnestunud laadida.'));
    throw error;
  } finally {
    deps.ui.hideLoader();
  }
}

async function refreshShippingMethods() {
  deps.ui.showLoader();
  try {
    const response = await deps.loadShippingMethods();
    const methods = deps.updateShippingUI(response, {
      onSelect: (method) => {
        // Don't auto-trigger shipping flow - wait for confirm button
        // Just update state, toggle pickup UI is handled in shipping-ui.js
      },
    });
    return methods;
  } catch (error) {
    console.error('Load shipping methods failed:', error);
    throw error;
  } finally {
    deps.ui.hideLoader();
  }
}

async function refreshPaymentMethods() {
  deps.ui.showLoader();
  try {
    const response = await deps.loadPaymentMethods();
    const methods = deps.updatePaymentUI(response, {
      onSelect: (method) => {
        if (method?.label) {
          setStepSummary('payment', method.label);
        }
      },
    });
    return methods || [];
  } catch (error) {
    console.error('Load payment methods failed:', error);
    throw error;
  } finally {
    deps.ui.hideLoader();
  }
}


async function handleShippingSelection(method) {
  await shippingFlow(
    {
      ui: deps.ui,
      saveShipping: deps.saveShipping,
      updateSummaryFromResult,
      refreshPaymentMethods,
      updatePaymentUI: (response) => {
        return deps.updatePaymentUI(response, {
          onSelect: (paymentMethod) => {
            if (paymentMethod?.label) {
              setStepSummary('payment', paymentMethod.label);
            }
          },
        });
      },
      markStepComplete,
      advanceStep,
      autoPaymentFlow: autoRunPaymentFlow,
      isOmnivaShipping,
      isSmartpostShipping,
      State: deps.State,
    },
    method,
  );
}

async function autoRunPaymentFlow() {
  try {
    setStepHidden('payment', true);
    await paymentFlow({
      savePayment: deps.savePayment,
      markStepComplete,
      advanceStep,
    });
  } catch (error) {
    console.error('Auto payment flow failed:', error);
    setStepHidden('payment', false);
    advanceStep('shipping');
  }
}

function isOmnivaShipping(method) {
  return method === 'omniva_omniva';
}

function isSmartpostShipping(method) {
  return method === 'smartpost_smartpost' || method === 'itella_smartpost';
}

function updateSummaryFromResult(result) {
  if (!result) return;
  const payload = result.data || result;
  deps.updateSummary(payload);
}


function resolveCartPayload(response) {
  const data = response?.data;
  return data?.cart || data?.data?.cart || data;
}

function getString(key, fallback) {
  return (window.NaileditCheckoutConfig?.strings || {})[key] || fallback;
}

