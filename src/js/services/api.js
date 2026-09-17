/**
 * Fetch wrapper with CSRF token and error handling
 */

const BASE = window.BASE_URL || '';

export function csrfFetch(url, options = {}) {
  const csrfToken = window.CSRF_TOKEN || '';

  const config = {
    credentials: 'same-origin',
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
      ...options.headers,
    },
    ...options,
  };

  if (config.method === 'POST' && config.body instanceof URLSearchParams) {
    config.body.append('_csrf_token', csrfToken);
  }

  return fetch(BASE + url, config)
    .then(r => {
      if (r.status === 401 || r.status === 403) {
        window.location.href = BASE + '/auth/login';
        return Promise.reject(new Error('Session expired'));
      }
      return r.json();
    })
    .catch(err => {
      showToast('red', 'Erro', 'Erro de conexao ao servidor.');
      return Promise.reject(err);
    });
}

export function get(url) {
  return csrfFetch(url);
}

export function post(url, data) {
  const body = data instanceof URLSearchParams ? data : new URLSearchParams(data);
  return csrfFetch(url, { method: 'POST', body });
}
