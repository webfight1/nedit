import { renderShippingMethods } from './shipping-render.js';
import { normalizeShipping } from './shipping-adapter.js';
import { State } from './state.js';
import { show as showOmniva, hide as hideOmniva } from './omniva.js';
import { show as showSmartpost, hide as hideSmartpost } from './smartpost.js';

const container = document.getElementById('nailedit-shipping-methods');

export function updateShippingUI(payload, { onSelect } = {}) {
  if (!container) {
    return [];
  }
  

  const methods = normalizeShipping(payload);
  container.innerHTML = renderShippingMethods(methods, State.shipping);

  const inputs = Array.from(container.querySelectorAll('input[name="nailedit_shipping_method"]'));

  if (!State.shipping && inputs.length) {
    inputs.forEach((input) => {
      input.checked = false;
    });
  }

  inputs.forEach((input) => {
    input.addEventListener('change', () => {
      State.shipping = input.value || '';
      toggleOmniva(State.shipping);
      toggleSmartpost(State.shipping);
      if (typeof onSelect === 'function') {
        const label = input.parentElement?.querySelector('span')?.textContent?.trim() || '';
        onSelect({ code: State.shipping, label });
      }
    });
  });

  toggleOmniva(State.shipping);
  toggleSmartpost(State.shipping);

  return methods;
}

function toggleOmniva(method) {
  if (method === 'omniva_omniva') {
    showOmniva();
  } else {
    hideOmniva();
  }
}

function toggleSmartpost(method) {
  if (method === 'smartpost_smartpost' || method === 'itella_smartpost') {
    showSmartpost();
  } else {
    hideSmartpost();
  }
}
