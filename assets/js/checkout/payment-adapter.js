export function normalizePayment(payload) {
  if (!payload) return [];

  let list =
    payload?.data?.methods ||
    payload?.data?.data?.methods ||
    payload?.data?.payment_methods ||
    payload?.data?.paymentMethods ||
    payload?.data?.data?.payment_methods ||
    payload?.methods ||
    payload?.data ||
    payload?.payment_methods;

  while (list && typeof list === 'object' && !Array.isArray(list)) {
    if (list.payment_methods) {
      list = list.payment_methods;
    } else if (list.methods) {
      list = list.methods;
    } else {
      break;
    }
  }

  if (Array.isArray(list)) {
    return list.map((method) => normalizeMethod(method));
  }

  if (list && typeof list === 'object') {
    return Object.keys(list).map((key) => normalizeMethod(list[key], key));
  }

  return [];
}

function normalizeMethod(method, fallbackCode) {
  if (!method) {
    return {
      code: fallbackCode || '',
      label: fallbackCode || '',
    };
  }

  const code = method.method || method.code || fallbackCode || '';
  const label = method.method_title || method.title || method.label || method.name || code;

  return {
    code,
    label,
  };
}
