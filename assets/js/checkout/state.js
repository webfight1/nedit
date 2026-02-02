const STORAGE_KEYS = {
  authToken: 'bagisto_auth_token',
  authCookie: 'bagisto_auth_cookie',
  cartCookie: 'bagisto_cart_cookie',
  cartToken: 'bagisto_guest_cart_token',
  selectedShipping: 'nailedit_selected_shipping_method',
  selectedPayment: 'nailedit_selected_payment_method',
  omnivaLocation: 'nailedit_omniva_location_id',
  lastOrder: 'nailedit_last_order',
};

function safeGet(key) {
  try {
    return localStorage.getItem(key);
  } catch (_) {
    return '';
  }
}

function safeSet(key, value) {
  try {
    if (value === undefined || value === null || value === '') {
      localStorage.removeItem(key);
    } else {
      localStorage.setItem(key, value);
    }
  } catch (_) {}
}

export const State = {
  get authToken() {
    return safeGet(STORAGE_KEYS.authToken) || '';
  },
  set authToken(token) {
    safeSet(STORAGE_KEYS.authToken, token);
  },

  get authCookie() {
    return safeGet(STORAGE_KEYS.authCookie) || safeGet(STORAGE_KEYS.cartCookie) || '';
  },
  set authCookie(cookie) {
    safeSet(STORAGE_KEYS.authCookie, cookie);
  },

  get cartToken() {
    return safeGet(STORAGE_KEYS.cartToken) || '';
  },
  set cartToken(token) {
    safeSet(STORAGE_KEYS.cartToken, token);
  },

  persistCartToken(payload) {
    if (payload && payload.cart_token) {
      this.cartToken = payload.cart_token;
    }
  },

  authPayload() {
    const payload = {};
    const storedCookie = this.authCookie;
    const authToken = this.authToken;
    const cartToken = this.cartToken;

    if (storedCookie) {
      payload.stored_cookie = storedCookie;
    }

    if (authToken) {
      payload.auth_token = authToken;
    } else if (cartToken) {
      payload.cart_token = cartToken;
    }

    return payload;
  },

  syncFromResponse(response) {
    if (!response) return;
    if (response.cart_token) {
      this.cartToken = response.cart_token;
    }
  },

  get selectedShipping() {
    return safeGet(STORAGE_KEYS.selectedShipping) || '';
  },
  set selectedShipping(value) {
    safeSet(STORAGE_KEYS.selectedShipping, value);
  },

  get shipping() {
    return this.selectedShipping;
  },
  set shipping(value) {
    this.selectedShipping = value;
  },

  get selectedPayment() {
    return safeGet(STORAGE_KEYS.selectedPayment) || '';
  },
  set selectedPayment(value) {
    safeSet(STORAGE_KEYS.selectedPayment, value);
  },

  get payment() {
    return this.selectedPayment;
  },
  set payment(value) {
    this.selectedPayment = value;
  },

  get omnivaLocation() {
    return safeGet(STORAGE_KEYS.omnivaLocation) || '';
  },
  set omnivaLocation(value) {
    safeSet(STORAGE_KEYS.omnivaLocation, value);
  },

  saveLastOrder(order) {
    try {
      if (order) {
        localStorage.setItem(STORAGE_KEYS.lastOrder, JSON.stringify(order));
      } else {
        localStorage.removeItem(STORAGE_KEYS.lastOrder);
      }
    } catch (_) {}
  },

  clearCheckoutSelections() {
    safeSet(STORAGE_KEYS.selectedShipping, '');
    safeSet(STORAGE_KEYS.selectedPayment, '');
    safeSet(STORAGE_KEYS.omnivaLocation, '');
  },

  clearCart() {
    safeSet(STORAGE_KEYS.cartToken, '');
  },
};
