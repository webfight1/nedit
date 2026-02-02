import { jest } from '@jest/globals';
import { bootstrap, setCheckoutDeps } from '../assets/js/checkout/checkout.js';

function createMockDocument() {
  const events = {};
  const elements = new Map();

  const createElement = (id) => {
    const handlers = {};
    const classes = new Set();
    return {
      id,
      classList: {
        add: (...names) => names.forEach((name) => classes.add(name)),
        remove: (...names) => names.forEach((name) => classes.delete(name)),
        contains: (name) => classes.has(name),
      },
      addEventListener: (event, handler) => {
        handlers[event] = handler;
      },
      click: () => handlers.click && handlers.click({}),
      dispatchEvent: (event) => handlers[event.type]?.(event),
    };
  };

  ['nailedit-checkout-address-form', 'nailedit-checkout-submit', 'nailedit-checkout-address-submit'].forEach((id) => {
    elements.set(id, createElement(id));
  });

  return {
    readyState: 'complete',
    addEventListener: (event, handler) => {
      events[event] = handler;
    },
    querySelector: () => null,
    querySelectorAll: () => [],
    getElementById: (id) => elements.get(id) || null,
    trigger: (event, payload) => {
      if (events[event]) {
        events[event](payload);
      }
    },
  };
}

describe('checkout bootstrap', () => {
  beforeEach(() => {
    globalThis.__NAILEDIT_DISABLE_AUTO_BOOTSTRAP__ = true;
  });

  afterEach(() => {
    setCheckoutDeps({ document: null, disableAutoBootstrap: false });
    delete globalThis.__NAILEDIT_DISABLE_AUTO_BOOTSTRAP__;
  });

  test('bootstraps without DOM errors when dependencies are injected', () => {
    const doc = createMockDocument();
    const ui = {
      lock: jest.fn(),
      unlock: jest.fn(),
      showLoader: jest.fn(),
      hideLoader: jest.fn(),
      step: jest.fn(),
      error: jest.fn(),
      success: jest.fn(),
      redirect: jest.fn(),
    };

    setCheckoutDeps({
      document: doc,
      disableAutoBootstrap: true,
      ui,
      api: jest.fn().mockResolvedValue({ data: { items: [] } }),
      initAddressForm: jest.fn().mockReturnValue(doc.getElementById('nailedit-checkout-address-form')),
      saveAddress: jest.fn().mockResolvedValue({}),
      loadShippingMethods: jest.fn().mockResolvedValue([]),
      loadPaymentMethods: jest.fn().mockResolvedValue([]),
      placeOrder: jest.fn().mockResolvedValue({}),
      updateShippingUI: jest.fn().mockReturnValue([]),
      updatePaymentUI: jest.fn(),
      updateSummary: jest.fn(),
      setSummaryLoading: jest.fn(),
      showCartError: jest.fn(),
      hideCartError: jest.fn(),
      normalizeCart: jest.fn().mockReturnValue({ items: [] }),
      State: { shipping: '', payment: '', omnivaLocation: '' },
    });

    expect(() => bootstrap()).not.toThrow();
  });
});
