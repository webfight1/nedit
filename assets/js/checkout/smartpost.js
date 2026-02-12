import { api } from './api.js';
import { State } from './state.js';

const config = window.NaileditCheckoutConfig || {};
const strings = config.strings || {};

const SELECTORS = {
  wrap: 'nailedit-smartpost-pickup',
  select: 'nailedit-smartpost-location',
  note: 'nailedit-smartpost-note',
  search: 'nailedit-smartpost-search',
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

  const current = State.smartpostLocation;
  const currentId = typeof current === 'object' && current ? current.locker_id : current;
  
  const options = (list || [])
    .map((loc, idx) => {
      if (!loc) return null;
      const id = loc.id != null ? String(loc.id) : `${loc.zip || loc.postcode || 'loc'}_${idx}`;
      // Store the generated ID back to location for matching in change handler
      loc._generatedId = id;
      return {
        id,
        label: optionLabel(loc),
      };
    })
    .filter(Boolean);

  options.unshift({
    id: '',
    label: str('smartpostSelect', 'Vali pakiautomaat'),
  });

  select.innerHTML = options.map((opt) => `<option value="${opt.id}">${opt.label}</option>`).join('');

  if (currentId && options.some((opt) => opt.id === currentId)) {
    select.value = currentId;
  } else {
    select.value = '';
  }
}

async function fetchLocations() {
  setNote(str('smartpostLoading', 'Laen automaate...'));
  try {
    const response = await api('nailedit_smartpost_locations', {}, { State });
    const list = normalizeLocations(response);
    locationsCache = list;
    filteredLocations = list;
    renderLocations(list);
    setNote('');
  } catch (error) {
    setNote(str('smartpostLoadError', 'Automaate ei õnnestunud laadida.'));
    console.error('Smartpost load failed:', error);
  }
}

export async function show() {
  const wrap = byId(SELECTORS.wrap);
  if (wrap) {
    wrap.classList.remove('hidden');
  }
  initSmartpostUI();
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

export function initSmartpostUI() {
  console.log('initSmartpostUI called, uiInitialized:', uiInitialized);
  if (uiInitialized) return;
  uiInitialized = true;

  const select = byId(SELECTORS.select);
  console.log('Smartpost select element found:', !!select, 'ID:', SELECTORS.select);
  
  if (select) {
    select.addEventListener('change', (event) => {
      console.log('Smartpost select changed, value:', event.target.value);
      const selectedId = event.target.value || '';
      if (!selectedId) {
        State.smartpostLocation = '';
        console.log('Smartpost location cleared (no selection)');
        return;
      }
      
      // Find the full location object from cache using _generatedId
      const location = (locationsCache || []).find((loc) => {
        return loc._generatedId === selectedId;
      });
      
      console.log('Found location in cache:', location);
      
      if (location) {
        const locationObj = {
          locker_id: location.location_id || location.zip || location.postcode || location.id || '',
          locker_name: location.name || '',
          locker_address: location.address || location.address1 || '',
          locker_city: location.city || '',
          locker_postcode: location.postal_code || location.postcode || location.zip || '',
          locker_country: location.country || 'EE',
        };
        State.smartpostLocation = locationObj;
        console.log('Smartpost location saved to State:', JSON.stringify(locationObj, null, 2));
      } else {
        State.smartpostLocation = '';
        console.log('Smartpost location cleared (not found in cache)');
      }
    });
    console.log('Smartpost change event listener attached');
  } else {
    console.error('Smartpost select element NOT FOUND!');
  }

  const search = byId(SELECTORS.search);
  if (search) {
    search.addEventListener('input', (event) => {
      filterLocations(event.target.value);
    });
  }
}
