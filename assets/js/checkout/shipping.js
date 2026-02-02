import { api } from './api.js';
import { State } from './state.js';

export async function loadShippingMethods() {
  return api('nailedit_checkout_shipping_methods', {}, { State });
}

export async function saveShipping() {
  if (!State.shipping) {
    throw new Error('Tarneviis pole valitud');
  }
  return api(
    'nailedit_checkout_save_shipping',
    {
      shipping_method: State.shipping,
    },
    { State }
  );
}

export async function loadOmnivaLocations() {
  return api('nailedit_omniva_locations');
}
