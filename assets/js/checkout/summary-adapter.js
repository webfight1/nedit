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

  const items = Array.isArray(cart.items)
    ? cart.items
    : Array.isArray(cart.items?.data)
    ? cart.items.data
    : cart.items && typeof cart.items === 'object'
    ? Object.values(cart.items)
    : [];

  return {
    items,
    formatted_sub_total: cart.formatted_sub_total,
    formatted_tax_total: cart.formatted_tax_total,
    formatted_discount: cart.formatted_discount,
    formatted_grand_total: cart.formatted_grand_total,
  };
}
