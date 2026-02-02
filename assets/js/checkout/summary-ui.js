import { normalizeCart } from './summary-adapter.js';
import { renderSummary } from './summary.js';

const summaryContainer = document.getElementById('nailedit-checkout-summary-body');
const requireCartEl = document.getElementById('nailedit-checkout-require-cart');

export function setSummaryLoading(message) {
  if (summaryContainer) {
    summaryContainer.innerHTML = `<p>${message}</p>`;
  }
}

export function showCartError(message) {
  if (requireCartEl) {
    requireCartEl.classList.remove('hidden');
    requireCartEl.textContent = message;
  }
}

export function hideCartError() {
  if (requireCartEl) {
    requireCartEl.classList.add('hidden');
    requireCartEl.textContent = '';
  }
}

export function updateSummary(payload) {
  if (!summaryContainer) return;
  const cart = normalizeCart(payload);
  summaryContainer.innerHTML = renderSummary(cart);
}
