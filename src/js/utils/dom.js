/**
 * Capital SisLoc — Safe HTML Rendering
 *
 * Uses <template> element for safer HTML parsing.
 * Scripts and external resources are NOT executed.
 */

const _template = document.createElement('template');

/**
 * Parse HTML string safely using <template> element.
 * Does NOT execute scripts or load external resources.
 *
 * @param {string} html - HTML string to parse
 * @returns {DocumentFragment} Parsed DOM nodes
 */
export function parseHTML(html) {
  _template.innerHTML = html;
  return _template.content.cloneNode(true);
}

/**
 * Render HTML string into a container safely.
 * Replaces container contents with parsed HTML.
 *
 * @param {HTMLElement} container - Target container
 * @param {string} html - HTML string to render
 */
export function renderHTML(container, html) {
  if (!container) return;
  container.replaceChildren();
  container.appendChild(parseHTML(html));
}

/**
 * Create a DOM element with attributes and children.
 *
 * @param {string} tag - Element tag name
 * @param {Object} attrs - Attributes to set
 * @param {Array|string} children - Child elements or text
 * @returns {HTMLElement}
 */
export function createElement(tag, attrs = {}, children = []) {
  const el = document.createElement(tag);
  Object.entries(attrs).forEach(([key, value]) => {
    if (key === 'className') el.className = value;
    else if (key === 'dataset') Object.assign(el.dataset, value);
    else if (key.startsWith('on')) el.addEventListener(key.slice(2).toLowerCase(), value);
    else el.setAttribute(key, value);
  });
  if (typeof children === 'string') el.textContent = children;
  else if (Array.isArray(children)) children.forEach(c => {
    if (typeof c === 'string') el.appendChild(document.createTextNode(c));
    else if (c) el.appendChild(c);
  });
  return el;
}
