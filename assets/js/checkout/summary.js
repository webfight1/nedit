const config = typeof window !== 'undefined' ? window.NaileditCheckoutConfig || {} : {};
const strings = config.strings || {};

function t(key, fallback) {
  return strings[key] || fallback;
}

export function renderSummary(cart) {
  const items = Array.isArray(cart?.items)
    ? cart.items
    : cart?.items
    ? [cart.items]
    : [];

  if (!items.length) {
    return `<p class="text-sm text-slate-600">${t('cartEmpty', 'Sinu ostukorv on tühi.')}</p>`;
  }

  const itemsHtml = items
    .map(
      (item) => `
    <div class="flex justify-between text-sm">
      <span>${item.quantity} × ${item.name}</span>
      <span>${item.formatted_total || item.total || ''}</span>
    </div>
  `
    )
    .join('');

  const rows = [];
  if (cart?.formatted_shipping_amount) {
    const shippingLabel = cart.shipping_method
      ? `${t('shipping', 'Tarne')} (${cart.shipping_method})`
      : t('shipping', 'Tarne');
    rows.push(row(shippingLabel, cart.formatted_shipping_amount));
  }
  if (cart?.formatted_discount) {
    rows.push(row(t('discount', 'Allahindlus'), cart.formatted_discount));
  }
  rows.push(row(t('grandTotal', 'Kokku'), cart?.formatted_grand_total || cart?.grand_total || '', true));

  return `
    <div class="space-y-3">
      ${itemsHtml}

      <div class="border-t space-y-1 text-sm" style="padding-top:0.75rem">
        ${rows.join('')}
      </div>
    </div>
  `;
}


function row(label, value, strong = false) {
  return `
    <div class="flex justify-between ${strong ? 'font-semibold' : ''}">
      <span>${label}</span>
      <span class="whitespace-nowrap">${value || ''}</span>
    </div>
  `;  
}
