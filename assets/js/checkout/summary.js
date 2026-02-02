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
  if (cart?.formatted_sub_total) {
    rows.push(row(t('subtotal', 'Vahesumma'), cart.formatted_sub_total));
  }
  if (cart?.formatted_tax_total) {
    rows.push(row(t('taxes', 'Maksud'), cart.formatted_tax_total));
  }
  if (cart?.formatted_discount) {
    rows.push(row(t('discount', 'Allahindlus'), cart.formatted_discount));
  }
  rows.push(row(t('grandTotal', 'Kokku'), cart?.formatted_grand_total || cart?.grand_total || '', true));

  return `
    <div class="space-y-3">
      ${itemsHtml}

      <div class="border-t pt-3 space-y-1 text-sm">
        ${rows.join('')}
      </div>
    </div>
  `;
}

function row(label, value, strong = false) {
  return `
    <div class="flex justify-between ${strong ? 'font-semibold' : ''}">
      <span>${label}</span>
      <span>${value || ''}</span>
    </div>
  `;
}
