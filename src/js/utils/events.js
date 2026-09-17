/**
 * Capital SisLoc — Event Delegation System
 *
 * Replaces inline onclick="..." with data-action attributes.
 */

const _handlers = {};

/**
 * Register a handler for a data-action
 */
export function registerAction(name, handler) {
  _handlers[name] = handler;
}

/**
 * Register multiple handlers at once
 */
export function registerActions(actions) {
  Object.entries(actions).forEach(([name, handler]) => {
    _handlers[name] = handler;
  });
}

/**
 * Initialize event delegation on document.body
 * Call once on DOMContentLoaded.
 * IMPORTANT: This must be called AFTER scripts.js loads,
 * so that the global functions are available.
 */
export function initEventDelegation() {
  document.body.addEventListener('click', (e) => {
    const el = e.target.closest('[data-action]');
    if (!el) return;

    const action = el.dataset.action;

    // Built-in navigation action
    if (action === 'navegar') {
      const url = el.dataset.url;
      if (url) window.location.href = url;
      return;
    }

    // Built-in modal actions
    if (action === 'open-modal') {
      const target = el.dataset.target;
      if (target) openModal(target);
      return;
    }

    if (action === 'close-modal') {
      const target = el.dataset.target;
      if (target) closeModal(target);
      return;
    }

    // Sidebar / canvas actions
    if (action === 'open-left') {
      if (typeof window.openLeft === 'function') window.openLeft();
      return;
    }
    if (action === 'close-left') {
      if (typeof window.closeLeft === 'function') window.closeLeft();
      return;
    }
    if (action === 'open-right') {
      if (typeof window.openRight === 'function') window.openRight();
      return;
    }
    if (action === 'close-right') {
      if (typeof window.closeRight === 'function') window.closeRight();
      return;
    }
    if (action === 'open-sidebar') {
      if (typeof window.openSidebar === 'function') window.openSidebar();
      return;
    }
    if (action === 'close-sidebar') {
      if (typeof window.closeSidebar === 'function') window.closeSidebar();
      return;
    }

    // Registered handler
    const handler = _handlers[action];
    if (handler) {
      handler(el, e);
    }
  });
}

/**
 * Open a modal by ID
 */
function openModal(id) {
  const modal = document.getElementById(id);
  if (modal) {
    modal.style.display = 'flex';
    modal.classList.add('open');
  }
}

/**
 * Close a modal by ID
 */
function closeModal(id) {
  const modal = document.getElementById(id);
  if (modal) {
    modal.style.display = 'none';
    modal.classList.remove('open');
  }
}

export { openModal, closeModal };
