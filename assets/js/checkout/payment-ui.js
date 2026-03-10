import { renderPaymentMethods } from './payment-render.js';
import { normalizePayment } from './payment-adapter.js';
import { State } from './state.js';

const container = document.getElementById('nailedit-payment-methods');

export function updatePaymentUI(payload, { onSelect } = {}) {
  if (!container) return [];

  const methods = normalizePayment(payload);
  container.innerHTML = renderPaymentMethods(methods, State.payment);

  
  container
    .querySelectorAll('input[name="nailedit_payment_method"]')
    .forEach((input) => {
      input.addEventListener('change', () => {
        State.payment = input.value || '';
        if (typeof onSelect === 'function') {
          const label = input.parentElement?.querySelector('span')?.textContent?.trim() || '';
          onSelect({ code: State.payment, label });
        }
      });
    });

  if (methods.length === 1) {
    const single = methods[0];
    State.payment = single.code;
    const radio = container.querySelector('input[name="nailedit_payment_method"]');
    if (radio) radio.checked = true;
    if (typeof onSelect === 'function') {
      onSelect({ code: single.code, label: single.label });
    }
  }

  return methods;
}
