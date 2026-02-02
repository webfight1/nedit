export async function orderFlow({
  ui,
  placeOrder,
  saveLastOrder,
  getString,
}) {
  ui.lock();
  ui.showLoader();

  try {
    ui.step('Salvestan tellimust…');
    const result = await placeOrder();
    const order = extractOrder(result);
    if (saveLastOrder) {
      saveLastOrder(order || result);
    }

    if (!order && !hasRedirectUrl(result) && !result?.success) {
      const status = result?.status ?? 'unknown';
      console.error('Place-order response missing order/redirect:', result);
      throw new Error(`Place-order ei tagastanud vastust (status ${status}).`);
    }

    try {
      localStorage.removeItem('bagisto_guest_cart_token');
    } catch (_) {}

    ui.success();
    await ui.redirect(result);
  } catch (error) {
    if (error?.response) {
      console.error('Place-order API error response:', error.response);
    }
    throw error;
  } finally {
    ui.hideLoader();
    ui.unlock();
  }
}

function extractOrder(result) {
  if (!result) return null;
  const data = result.data || result;

  if (Array.isArray(data)) {
    const first = data[0] || {};
    return first.order || first;
  }

  if (data?.order) {
    return data.order;
  }

  if (data?.data?.order) {
    return data.data.order;
  }

  if (Array.isArray(data?.data) && data.data.length) {
    const first = data.data[0] || {};
    return first.order || first;
  }

  if (data?.order_id || data?.data?.order_id) {
    return {
      id: data?.order_id || data?.data?.order_id,
    };
  }

  return null;
}

function hasRedirectUrl(result) {
  if (!result) return false;
  const data = result.data || {};
  if (data && typeof data.redirect_url === 'string' && data.redirect_url) {
    return true;
  }
  const nested = data.data;
  if (Array.isArray(nested) && nested.length) {
    const first = nested[0] || {};
    if (first.redirect_url || first.payment?.redirect_url) {
      return true;
    }
  }
  if (nested && typeof nested.redirect_url === 'string' && nested.redirect_url) {
    return true;
  }
  if (nested && nested.payment?.redirect_url) {
    return true;
  }
  if (data.payment?.redirect_url) {
    return true;
  }
  return false;
}
