import { api } from './api.js';
import { State } from './state.js';

const config = window.NaileditCheckoutConfig || {};
const strings = config.strings || {};

const SELECTORS = {
  wrap: 'nailedit-omniva-pickup',
  select: 'nailedit-omniva-location',
  note: 'nailedit-omniva-note',
  search: 'nailedit-omniva-search',
};

let locationsCache = null;
let filteredLocations = null;
let uiInitialized = false;

function str(key, fallback) {
  return strings[key] || fallback;
}

function byId(id) {
  return document.getElementById(id);
}

function setNote(message) {
  const el = byId(SELECTORS.note);
  if (el) {
    el.textContent = message || '';
  }
}

function normalizeLocations(result) {
  if (!result) return [];
  const data = result.data;
  if (!data) return [];
  if (Array.isArray(data.data?.locations)) {
    return data.data.locations;
  }
  if (Array.isArray(data.locations)) {
    return data.locations;
  }
  if (Array.isArray(data.data)) {
    return data.data;
  }
  if (Array.isArray(data)) {
    return data;
  }
  return [];
}

function optionLabel(location) {
  const name = location.name || location.title || '';
  const city = location.city || '';
  return [name, city].filter(Boolean).join(' ');
}

function renderLocations(list) {
  const select = byId(SELECTORS.select);
  if (!select) return;

  const current = State.omnivaLocation;
  const options = (list || []).map((loc, idx) => {
    if (!loc) return null;
    const id = loc.id != null ? String(loc.id) : `${loc.zip || loc.postcode || 'loc'}_${idx}`;
    return {
      id,
      label: optionLabel(loc),
    };
  }).filter(Boolean);

  options.unshift({
    id: '',
    label: str('omnivaSelect', 'Vali pakiautomaat'),
  });

  select.innerHTML = options
    .map((opt) => `<option value="${opt.id}">${opt.label}</option>`)
    .join('');

  if (current && options.some((opt) => opt.id === current)) {
    select.value = current;
  } else {
    select.value = '';
  }
}

async function fetchLocations() {
  setNote(str('omnivaLoading', 'Laen automaate...'));
  try {
    const response = await api('nailedit_omniva_locations', {}, { State });
    const list = normalizeLocations(response);
    locationsCache = list;
    filteredLocations = list;
    renderLocations(list);
    setNote('');
  } catch (error) {
    setNote(str('omnivaLoadError', 'Automaate ei õnnestunud laadida.'));
    console.error('Omniva load failed:', error);
  }
}

export async function show() {
  const wrap = byId(SELECTORS.wrap);
  if (wrap) {
    wrap.classList.remove('hidden');
  }
  initOmnivaUI();
  if (!locationsCache) {
    await fetchLocations();
  } else {
    renderLocations(filteredLocations || locationsCache);
  }
}

export function hide() {
  const wrap = byId(SELECTORS.wrap);
  if (wrap) {
    wrap.classList.add('hidden');
  }
}

export function filterLocations(query) {
  if (!locationsCache) {
    return;
  }
  const q = (query || '').toLowerCase();
  if (!q) {
    filteredLocations = locationsCache;
    renderLocations(filterLocations);
    renderLocations(locationsCache);
    return;
  }

  filteredLocations = locationsCache.filter((loc) => {
    try {
      const haystack = `${loc.zip || loc.postcode || ''} ${loc.name || loc.title || ''} ${loc.address || ''} ${loc.city || ''}`.toLowerCase();
      return haystack.includes(q);
    } catch (error) {
      return false;
    }
  });
  renderLocations(filteredLocations);
}

export function initOmnivaUI() {
  if (uiInitialized) return;
  uiInitialized = true;

  const select = byId(SELECTORS.select);
  if (select) {
    select.addEventListener('change', (event) => {
      State.omnivaLocation = event.target.value || '';
    });
  }

  const search = byId(SELECTORS.search);
  if (search) {
    search.addEventListener('input', (event) => {
      filterLocations(event.target.value);
    });
  }
}
