/**
 * Capital SisLoc — AJAX Form Handler
 *
 * Converts form submissions to AJAX (no page reload).
 *
 * Usage:
 *   <form method="POST" action="/clientes/store" data-ajax>
 *   <form method="POST" action="/clientes/update/1" data-ajax data-redirect="/clientes">
 */
import { showToast } from './helpers.js';
import { store } from '../services/store.js';

/**
 * Initialize AJAX form handling on all forms with data-ajax attribute.
 * Call once on DOMContentLoaded.
 */
export function initAjaxForms() {
  document.addEventListener('submit', (e) => {
    const form = e.target;
    if (!form.matches('form[data-ajax]')) return;
    e.preventDefault();
    submitAjaxForm(form);
  });
}

/**
 * Submit a form via AJAX.
 */
export async function submitAjaxForm(form) {
  const action = form.action;
  const method = (form.method || 'POST').toUpperCase();
  const enctype = form.enctype || 'application/x-www-form-urlencoded';

  // Build the request body BEFORE disabling inputs — disabled form controls
  // are excluded from FormData, so this must run while fields are still enabled.
  let body;
  const headers = { 'X-Requested-With': 'XMLHttpRequest' };

  if (enctype === 'multipart/form-data') {
    body = new FormData(form);
    body.append('_csrf_token', store.getCsrfToken());
  } else {
    body = new URLSearchParams(new FormData(form));
    body.append('_csrf_token', store.getCsrfToken());
    headers['Content-Type'] = 'application/x-www-form-urlencoded';
  }

  // Disable submit button and show loading
  const btn = form.querySelector('button[type="submit"], input[type="submit"]');
  const originalText = btn?.textContent;
  if (btn) {
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner spinner-sm"></span> Salvando...';
    btn.classList.add('loading');
  }

  // Disable all form inputs during submission
  const inputs = form.querySelectorAll('input, select, textarea');
  inputs.forEach(input => input.disabled = true);

  // Clear previous validation errors
  form.querySelectorAll('.field-error').forEach(el => el.remove());
  form.querySelectorAll('.has-error').forEach(el => el.classList.remove('has-error'));

  try {
    const response = await fetch(action, {
      method,
      body,
      headers,
      credentials: 'same-origin',
    });

    const data = await response.json();

    if (data.success) {
      showToast('green', 'Sucesso', data.message || 'Operacao realizada com sucesso!');

      // Redirect if specified
      const redirect = form.dataset.redirect;
      if (redirect) {
        setTimeout(() => { window.location.href = redirect; }, 1000);
      }

      // Call custom success handler
      const onSuccess = form.dataset.onSuccess;
      if (onSuccess && typeof window[onSuccess] === 'function') {
        window[onSuccess](data);
      }
    } else if (data.errors) {
      // Show field-level validation errors
      showValidationErrors(form, data.errors);
      showToast('red', 'Erro', data.message || 'Corrija os erros abaixo.');
    } else if (data.error) {
      showToast('red', 'Erro', data.error);
    } else {
      showToast('red', 'Erro', 'Erro ao processar solicitacao.');
    }
  } catch (err) {
    showToast('red', 'Erro', 'Erro de conexao ao servidor.');
  } finally {
    // Re-enable form
    if (btn) {
      btn.disabled = false;
      btn.textContent = originalText;
      btn.classList.remove('loading');
    }
    inputs.forEach(input => input.disabled = false);
  }
}

/**
 * Show validation errors next to form fields.
 */
function showValidationErrors(form, errors) {
  Object.entries(errors).forEach(([field, messages]) => {
    const input = form.querySelector(`[name="${field}"]`);
    if (!input) return;

    input.classList.add('has-error');

    const errorEl = document.createElement('div');
    errorEl.className = 'field-error form-error';
    errorEl.textContent = Array.isArray(messages) ? messages[0] : messages;

    // Insert after input or after parent wrapper
    const parent = input.closest('.form-group') || input.parentElement;
    parent.appendChild(errorEl);
  });
}
