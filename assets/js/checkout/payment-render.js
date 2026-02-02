export function renderPaymentMethods(methods, selected) {
  if (!methods || !methods.length) {
    return `<div class="text-xs text-slate-600 italic">Makseviise ei leitud.</div>`;
  }

  return methods
    .map(
      (m) => `
    <label class="flex items-center gap-2 text-sm cursor-pointer">
      <input
        type="radio"
        name="nailedit_payment_method"
        value="${m.code}"
        ${m.code === selected ? 'checked' : ''}
      />
      <span>${m.label}</span>
    </label>
  `
    )
    .join('');
}
