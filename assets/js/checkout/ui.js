const config = window.NaileditCheckoutConfig || {};

function getButton() {
  return document.getElementById('nailedit-checkout-submit');
}

function getErrorBox() {
  return document.getElementById('nailedit-checkout-address-error');
}

function getLoader() {
  return document.getElementById('nailedit-checkout-loader');
}

function defaultLabel() {
  const btn = getButton();
  if (!btn) {
    return config?.strings?.submitDefault || 'Esita tellimus';
  }

  if (!btn.dataset.defaultLabel) {
    btn.dataset.defaultLabel = btn.textContent || config?.strings?.submitDefault || 'Esita tellimus';
  }
  return btn.dataset.defaultLabel;
}

export function lock() {
  const btn = getButton();
  if (btn) {
    btn.disabled = true;
  }
}

export function unlock() {
  const btn = getButton();
  if (btn) {
    btn.disabled = false;
    btn.textContent = defaultLabel();
  }
}

export function step(text) {
  const btn = getButton();
  if (btn && text) {
    btn.textContent = text;
  }
}

export function error(message) {
  const box = getErrorBox();
  if (box) {
    box.textContent = message || '';
  }
}

export const errorMsg = error;

export function success() {
  const box = getErrorBox();
  if (box) {
    box.textContent = '';
  }
}

export function showLoader() {
  const loader = getLoader();
  if (loader) {
    loader.classList.remove('hidden');
    loader.classList.add('flex');
  }
}

export function hideLoader() {
  const loader = getLoader();
  if (loader) {
    loader.classList.add('hidden');
    loader.classList.remove('flex');
  }
}

export async function redirect(result) {
  await new Promise(resolve => setTimeout(resolve, 500));
  
  const redirectUrl = extractRedirectUrl(result);
  if (redirectUrl) {
    window.location.href = redirectUrl;
    return;
  }
  if (config?.thankYouUrl) {
    window.location.href = config.thankYouUrl;
  }
}

function extractRedirectUrl(result) {
  if (!result) return '';
  const data = result.data || {};

  if (data && typeof data.redirect_url === 'string' && data.redirect_url) {
    return data.redirect_url;
  }

  const nested = data.data;
  if (Array.isArray(nested) && nested.length) {
    const first = nested[0] || {};
    if (first.redirect_url) {
      return first.redirect_url;
    }
    if (first.payment?.redirect_url) {
      return first.payment.redirect_url;
    }
  }

  if (nested && typeof nested.redirect_url === 'string' && nested.redirect_url) {
    return nested.redirect_url;
  }

  if (nested && nested.payment?.redirect_url) {
    return nested.payment.redirect_url;
  }

  if (data.payment?.redirect_url) {
    return data.payment.redirect_url;
  }

  return '';
}
