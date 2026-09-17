/**
 * Shared utility helpers
 */

export function showToast(color, title, message, duration = 4000) {
  const existing = document.querySelector('.toast-notification');
  if (existing) existing.remove();

  const toast = document.createElement('div');
  toast.className = `toast-notification toast-${color}`;
  toast.innerHTML = `
    <div style="display:flex;align-items:flex-start;gap:10px">
      <div style="flex:1">
        <div style="font-weight:600;font-size:13px;margin-bottom:2px">${escapeHtml(title)}</div>
        <div style="font-size:12px;opacity:0.9">${escapeHtml(message)}</div>
      </div>
      <button onclick="this.closest('.toast-notification').remove()" style="background:none;border:none;color:inherit;cursor:pointer;font-size:16px;padding:0">&times;</button>
    </div>
  `;

  Object.assign(toast.style, {
    position: 'fixed',
    top: '20px',
    right: '20px',
    zIndex: '10000',
    maxWidth: '400px',
    padding: '12px 16px',
    borderRadius: '8px',
    boxShadow: '0 4px 12px rgba(0,0,0,0.3)',
    animation: 'toastSlideIn 0.3s ease',
    color: '#fff',
    background: color === 'green' ? '#10b981' : color === 'red' ? '#ef4444' : color === 'yellow' ? '#f59e0b' : '#3b82f6',
  });

  document.body.appendChild(toast);
  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateX(100%)';
    toast.style.transition = 'all 0.3s ease';
    setTimeout(() => toast.remove(), 300);
  }, duration);
}

export function escapeHtml(str) {
  if (!str) return '';
  return String(str).replace(/[&<>"']/g, m => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
  }[m]));
}

export function formatDate(dateStr) {
  if (!dateStr) return '';
  const [y, m, d] = dateStr.split('-');
  return `${d}/${m}/${y}`;
}

export function debounce(fn, delay = 300) {
  let timer;
  return (...args) => {
    clearTimeout(timer);
    timer = setTimeout(() => fn(...args), delay);
  };
}
