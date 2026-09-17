/**
 * Capital SisLoc — Error Boundary
 *
 * Catches JS errors and shows user-friendly messages instead of crashing.
 *
 * Usage:
 *   import { withErrorBoundary } from './utils/error-boundary.js';
 *   const safeFn = withErrorBoundary(riskyFunction);
 *   // or
 *   withErrorBoundary(() => { ... })();
 */

const _errorHandlers = [];

/**
 * Register a global error handler.
 */
export function onError(handler) {
  _errorHandlers.push(handler);
  return () => {
    const idx = _errorHandlers.indexOf(handler);
    if (idx > -1) _errorHandlers.splice(idx, 1);
  };
}

/**
 * Wrap a function with error boundary.
 * Returns a new function that catches errors and shows a toast.
 */
export function withErrorBoundary(fn, fallback) {
  return (...args) => {
    try {
      const result = fn(...args);
      // Handle async functions
      if (result && typeof result.catch === 'function') {
        return result.catch(err => handleError(err, fallback));
      }
      return result;
    } catch (err) {
      handleError(err, fallback);
    }
  };
}

/**
 * Wrap an async function with error boundary.
 */
export function withAsyncErrorBoundary(fn, fallback) {
  return async (...args) => {
    try {
      return await fn(...args);
    } catch (err) {
      handleError(err, fallback);
    }
  };
}

/**
 * Initialize global error handlers.
 * Call once on DOMContentLoaded.
 */
export function initErrorBoundary() {
  // Catch unhandled errors
  window.addEventListener('error', (event) => {
    // Ignore script loading errors (extensions, etc.)
    if (event.message === 'Script error.' || event.message?.includes('ResizeObserver')) {
      return;
    }
    handleError(event.error || new Error(event.message), null, false);
  });

  // Catch unhandled promise rejections
  window.addEventListener('unhandledrejection', (event) => {
    // Ignore fetch abort errors
    if (event.reason?.name === 'AbortError') return;
    handleError(event.reason || new Error('Unhandled promise rejection'), null, false);
  });
}

function handleError(err, fallback, showToastFlag = true) {
  // Log to console
  console.error('[Capital Error]', err);

  // Notify registered handlers
  _errorHandlers.forEach(handler => {
    try { handler(err); } catch (e) { /* ignore */ }
  });

  // Show toast notification
  if (showToastFlag && typeof window.showToast === 'function') {
    window.showToast('red', 'Erro', 'Ocorreu um erro inesperado. Tente novamente.');
  }

  // Call fallback if provided
  if (typeof fallback === 'function') {
    try { fallback(err); } catch (e) { /* ignore */ }
  }
}

export { handleError };
