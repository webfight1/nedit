export function normalizeCart(payload) {
  if (!payload) return null;

  let cart =
    payload?.data?.cart ||
    payload?.cart ||
    payload?.data?.data?.cart ||
    payload?.data?.[0]?.cart ||
    payload?.data;

  if (cart?.cart) {
    cart = cart.cart;
  }

  if (cart?.data) {
    cart = cart.data;
  }
  

  if (!cart || typeof cart !== 'object') {
    return null;
  }

  console.log('[summary-adapter] full cart keys:', Object.keys(cart));
  console.log('[summary-adapter] cart.shipping_amount:', cart.shipping_amount, 'cart.base_shipping_amount:', cart.base_shipping_amount);

  const items = Array.isArray(cart.items)
    ? cart.items
    : Array.isArray(cart.items?.data)
    ? cart.items.data
    : cart.items && typeof cart.items === 'object'
    ? Object.values(cart.items)
    : [];

  console.log('[summary-adapter] cart shipping fields:', {
    selected_shipping_rate: cart.selected_shipping_rate,
    shipping_rate: cart.shipping_rate,
    shipping_method: cart.shipping_method,
    shipping_method_title: cart.shipping_method_title,
  });

  const shippingRate =
    cart.selected_shipping_rate?.data ||
    cart.selected_shipping_rate ||
    cart.shipping_rate?.data ||
    cart.shipping_rate ||
    null;

  const shippingTitle =
    shippingRate?.method_title ||
    shippingRate?.carrier_title ||
    cart.shipping_method_title ||
    '';

  return {
    items,
    formatted_sub_total: cart.formatted_sub_total,
    formatted_tax_total: cart.formatted_tax_total,
    formatted_discount: cart.formatted_discount,
    formatted_grand_total: cart.formatted_grand_total,
    shipping_method: shippingTitle,
    formatted_shipping_amount:
      shippingRate?.formatted_price ||
      shippingRate?.formatted_amount ||
      cart.formatted_shipping_amount ||
      (typeof cart.shipping_amount === 'number' ? cart.shipping_amount.toFixed(2) + ' €' : ''),
  };
}
