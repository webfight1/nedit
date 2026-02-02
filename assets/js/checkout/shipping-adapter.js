export function normalizeShipping(payload) {
  if (!payload) return [];

  let carriers =
    payload?.data?.rates ||
    payload?.data?.data?.rates ||
    payload?.data?.shipping_rates ||
    payload?.data?.data?.shipping_rates ||
    payload?.data?.shippingMethods ||
    payload?.data?.data?.shippingMethods ||
    payload?.rates ||
    payload?.shipping_rates ||
    payload?.shippingMethods ||
    payload?.data;

  if (carriers?.shippingMethods) {
    carriers = carriers.shippingMethods;
  }

  if (!carriers) {
    return [];
  }

  const ratesArray = [];

  const appendRate = (rate) => {
    if (!rate) return;
    const code = rate.method || rate.code;
    if (!code) return;
    ratesArray.push({
      code,
      label: rate.method_title || rate.carrier_title || rate.title || code,
      price: rate.formatted_price || rate.base_formatted_price || '',
    });
  };

  if (Array.isArray(carriers)) {
    carriers.forEach((carrier) => {
      if (carrier?.rates && Array.isArray(carrier.rates)) {
        carrier.rates.forEach(appendRate);
      } else {
        appendRate(carrier);
      }
    });
    return ratesArray;
  }

  if (typeof carriers === 'object') {
    Object.values(carriers).forEach((carrier) => {
      if (!carrier) return;
      const rates = Array.isArray(carrier.rates) ? carrier.rates : carrier.rates ? [carrier.rates] : [];
      rates.forEach(appendRate);
    });
  }

  return ratesArray;
}
