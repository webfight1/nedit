export function renderShippingMethods(methods, selected) {
  if (!methods || !methods.length) {
    return `<div class="text-xs text-slate-600 italic">Tarneviise ei leitud.</div>`;
  }

  return methods
    .map(
      (m) => `
    <label class="flex justify-between items-center text-sm cursor-pointer">
      <span class="flex items-center gap-2">
        <input
          type="radio"
          name="nailedit_shipping_method"
          value="${m.code}"
          ${m.code === selected ? 'checked' : ''}
        />
        <span>${m.label}</span>
      </span>
      <span>${m.price || ''}</span>
    </label>
  `
    )
    .join('');
}
