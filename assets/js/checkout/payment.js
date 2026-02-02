import { api } from './api.js';
import { State } from './state.js';

export async function loadPaymentMethods() {
  return api('nailedit_checkout_payment_methods', {}, { State });
}

export async function savePayment() {
  if (!State.payment) {
    throw new Error('Makseviis pole valitud');
  }
  return api(
    'nailedit_checkout_save_payment',
    {
      payment_method: State.payment,
    },
    { State }
  );
}
