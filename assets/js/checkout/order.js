import { api } from './api.js';
import { State } from './state.js';

export async function placeOrder() {
  return api('nailedit_checkout_save_order', {}, { State });
}
