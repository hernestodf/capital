/**
 * Capital SisLoc — Main Entry Point
 * Vite bundles this into public/dist/js/main-[hash].js
 */
import { showToast, escapeHtml, formatDate, debounce } from './utils/helpers.js';
import { csrfFetch, get, post } from './services/api.js';
import { initStore, store } from './services/store.js';
import { initEventDelegation } from './utils/events.js';
import { renderHTML, parseHTML, createElement } from './utils/dom.js';
import { initAjaxForms } from './utils/forms.js';
import { initErrorBoundary } from './utils/error-boundary.js';

// Expose to global scope for PHP inline handlers (temporary)
window.showToast = showToast;
window.escapeHtml = escapeHtml;
window.formatDate = formatDate;
window.csrfFetch = csrfFetch;
window.renderHTML = renderHTML;

// Auto-init on DOM ready
document.addEventListener('DOMContentLoaded', () => {
  // Initialize error boundary first (catches errors in other init functions)
  initErrorBoundary();

  // Initialize centralized state store
  initStore();

  // Initialize event delegation (replaces inline onclick)
  initEventDelegation();

  // Initialize AJAX form handling
  initAjaxForms();

  // Import and init modules dynamically based on page
  const page = document.body.dataset.page;
  if (page) {
    import(`./modules/${page}.js`).then(m => m.init?.()).catch(() => {});
  }
});

export { store, renderHTML, parseHTML, createElement, debounce };
