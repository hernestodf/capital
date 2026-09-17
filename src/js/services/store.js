/**
 * Capital SisLoc — Centralized State Store
 *
 * Replaces scattered window.* globals with a single source of truth.
 * PHP views still inject values into window.* (transition period),
 * but JS modules read from this store instead.
 */

const _state = {
  eventoId: null,
  baseUrl: '',
  csrfToken: '',
  salasMap: {},
  currentUser: null,
};

let _listeners = {};

/**
 * Initialize store from PHP-injected window.* globals
 * Called once on DOMContentLoaded
 */
export function initStore() {
  _state.eventoId = window.EVENTO_ID ?? null;
  _state.baseUrl = window.BASE_URL ?? '';
  _state.csrfToken = window.CSRF_TOKEN ?? '';
  _state.salasMap = window.SALAS_MAP ?? {};

  // Expose getters on window for inline handlers (transition)
  window.AppState = {
    get eventoId() { return _state.eventoId; },
    get baseUrl() { return _state.baseUrl; },
    get csrfToken() { return _state.csrfToken; },
    get salasMap() { return _state.salasMap; },
  };
}

// --- Getters ---

export const store = {
  getEventoId: () => _state.eventoId,
  getBaseUrl: () => _state.baseUrl,
  getCsrfToken: () => _state.csrfToken,
  getSalasMap: () => _state.salasMap,

  // --- Setters ---

  setEventoId: (id) => {
    _state.eventoId = id;
    _emit('eventoId', id);
  },

  setSalasMap: (map) => {
    _state.salasMap = map;
    _emit('salasMap', map);
  },

  // --- Subscriptions ---

  on: (key, callback) => {
    if (!_listeners[key]) _listeners[key] = [];
    _listeners[key].push(callback);
    return () => {
      _listeners[key] = _listeners[key].filter(cb => cb !== callback);
    };
  },
};

function _emit(key, value) {
  if (_listeners[key]) {
    _listeners[key].forEach(cb => cb(value));
  }
}

export default store;
