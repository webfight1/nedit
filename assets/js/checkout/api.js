const ajaxUrl = window?.NaileditCheckoutConfig?.ajaxUrl || '';

function ensureAjaxUrl() {
  if (!ajaxUrl) {
    throw new Error('AJAX URL is missing.');
  }
  return ajaxUrl;
}

function createFormData(payload) {
  if (payload instanceof FormData) {
    return payload;
  }

  const fd = new FormData();
  Object.entries(payload || {}).forEach(([key, value]) => {
    if (value === undefined || value === null) {
      return;
    }

    if (Array.isArray(value)) {
      value.forEach((item) => {
        fd.append(key, item);
      });
    } else {
      fd.append(key, value);
    }
  });

  return fd;
}

function appendKey(formData, key, value) {
  if (value === undefined || value === null || value === '') {
    return;
  }
  if (typeof formData.set === 'function') {
    formData.set(key, value);
  } else {
    formData.delete(key);
    formData.append(key, value);
  }
}

function appendIfMissing(formData, key, value) {
  if (value === undefined || value === null || value === '') {
    return;
  }
  if (!formData.has(key)) {
    formData.append(key, value);
  }
}

async function parseJson(response) {
  const text = await response.text();
  if (!text) {
    return null;
  }
  try {
    return JSON.parse(text);
  } catch (error) {
    const err = new Error('Server vastas vigase andmega.');
    err.cause = error;
    err.raw = text;
    throw err;
  }
}

export async function api(action, payload = {}, options = {}) {
  const { includeAuth = true, authPayload = {}, State } = options;

  if (!action) {
    throw new Error('API action is required.');
  }

  const formData = createFormData(payload);
  appendKey(formData, 'action', action);

  if (includeAuth && State) {
    const authFields = State.authPayload();
    Object.entries({ ...authFields, ...authPayload }).forEach(([key, value]) => {
      appendIfMissing(formData, key, value);
    });
  } else if (includeAuth && !State) {
    console.warn('State helper missing – auth payload not appended.');
  }

  const response = await fetch(ensureAjaxUrl(), {
    method: 'POST',
    body: formData,
    credentials: 'same-origin',
  });

  const data = await parseJson(response);
  const success = response.ok && data && data.success !== false;

  if (State) {
    State.syncFromResponse?.(data);
  }

  if (!success) {
    const message = data?.message || data?.data?.message || `API error (${response.status})`;
    const error = new Error(message);
    error.response = data;
    throw error;
  }

  return data;
}
