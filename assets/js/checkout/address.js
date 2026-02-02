import { api } from './api.js';
import { State } from './state.js';

function byId(id) {
  return document.getElementById(id);
}

function ensureRequiredShippingFields(payload) {
  const defaults = {
    shipping_first_name: payload.get('billing_first_name') || '',
    shipping_last_name: payload.get('billing_last_name') || '',
    shipping_email: payload.get('billing_email') || '',
    shipping_phone: payload.get('billing_phone') || '',
    shipping_address: payload.get('billing_address[]') || payload.get('billing_address') || '—',
    shipping_city: payload.get('billing_city') || 'Tallinn',
    shipping_state: payload.get('billing_state') || 'Harjumaa',
    shipping_postcode: payload.get('billing_postcode') || '00000',
    shipping_country: payload.get('billing_country') || 'EE',
  };

  Object.entries(defaults).forEach(([key, value]) => {
    const current = payload.get(key);
    if (!current) {
      payload.set(key, value);
    }
  });
}

function ensureRequiredBillingFields(payload) {
  const isCompany = payload.get('billing_is_company');
  if (isCompany) {
    return;
  }

  const defaults = {
    billing_city: 'Tallinn',
    billing_state: 'Harjumaa',
    billing_postcode: '00000',
    billing_country: 'EE',
  };

  Object.entries(defaults).forEach(([key, value]) => {
    const current = payload.get(key);
    if (!current) {
      payload.set(key, value);
    }
  });
}

function ensureForm(formOrSelector) {
  if (formOrSelector instanceof HTMLFormElement) {
    return formOrSelector;
  }
  if (formOrSelector instanceof FormData) {
    return formOrSelector;
  }
  if (typeof formOrSelector === 'string') {
    return document.querySelector(formOrSelector);
  }
  return byId('nailedit-checkout-address-form');
}

export function initAddressForm() {
  const form = ensureForm();
  return form || null;
}

export async function saveAddress(formOrData) {
  const form = ensureForm(formOrData);

  let payload;
  if (form instanceof FormData) {
    payload = form;
  } else if (form instanceof HTMLFormElement) {
    payload = new FormData(form);
  } else {
    throw new Error('Aadressi vormi ei leitud.');
  }

  ensureRequiredBillingFields(payload);
  ensureRequiredShippingFields(payload);

  return api('nailedit_checkout_save_address', payload, { State });
}

function setField(prefix, key, value) {
  const el = byId(`${prefix}_${key}`);
  if (el && value !== undefined && value !== null && value !== '') {
    el.value = value;
  }
}

function fillAddress(prefix, addr) {
  if (!addr) return;

  const address1 = Array.isArray(addr.address1) && addr.address1.length
    ? addr.address1[0]
    : Array.isArray(addr.address) && addr.address.length
    ? addr.address[0]
    : addr.address1 || '';

  setField(prefix, 'first_name', addr.first_name || '');
  setField(prefix, 'last_name', addr.last_name || '');
  setField(prefix, 'email', addr.email || '');
  setField(prefix, 'company_name', addr.company_name || addr.company || '');

  const addressInput = byId(`${prefix}_address`);
  if (addressInput && address1) {
    addressInput.value = address1;
  }

  setField(prefix, 'postcode', addr.postcode || '');
  setField(prefix, 'city', addr.city || '');
  setField(prefix, 'state', addr.state || '');
  setField(prefix, 'country', addr.country || '');
  setField(prefix, 'phone', addr.phone || '');
}

export async function loadDefaultAddress() {
  const hasAuth = Boolean(State.authToken || State.authCookie);
  if (!hasAuth) {
    return;
  }

  try {
    const result = await api('nailedit_list_addresses', {}, { State });
    const data = result?.data;
    if (!data) return;

    const list = Array.isArray(data.data) ? data.data : Array.isArray(data) ? data : [];
    if (!list.length) return;

    let defaults = list.find(
      (addr) => addr && (addr.is_default === 1 || addr.is_default === '1' || addr.default_address === 1 || addr.default_address === '1')
    );
    if (!defaults) {
      defaults = list[0];
    }

    fillAddress('billing', defaults);

    const useForShipping = byId('billing_use_for_shipping');
    if (useForShipping && useForShipping.checked) {
      fillAddress('shipping', defaults);
    }
  } catch (error) {
    console.warn('Default address load failed:', error);
  }
}
