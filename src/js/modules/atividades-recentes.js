/**
 * Atividades Recentes — CSV via backend
 */

const ATIVIDADES_URL = '/dashboard/atividades-recentes-csv';
const GOOGLE_SHEET_URL =
  'https://docs.google.com/spreadsheets/d/e/2PACX-1vRVWRxIqJYc8G_jyDktjos5kTMb67eRnvSPvLLq8D4mgWiQkGCF82Dc6RziRs3BCg/pub?output=csv';

async function loadAtividadesRecentes() {
  const container = document.getElementById('atividades-recentes-container');
  if (!container) return;

  try {
    const response = await fetch(ATIVIDADES_URL, { headers: { 'Accept': 'text/csv' } });
    if (!response.ok) throw new Error('Falha ao carregar atividades');
    const csvText = await response.text();
    const rows = parseCSV(csvText);

    renderAtividades(container, rows);
  } catch (error) {
    container.innerHTML = `<div class="card-body"><p style="color:var(--text-3)">Nao foi possivel carregar Atividades Recentes</p></div>`;
  }
}

function parseCSVLine(line) {
  const cells = [];
  let cur = '';
  let inQuotes = false;

  for (let i = 0; i < line.length; i++) {
    const char = line[i];
    if (inQuotes) {
      if (char === '"' && line[i + 1] === '"') {
        cur += '"';
        i++;
      } else if (char === '"') {
        inQuotes = false;
      } else {
        cur += char;
      }
    } else if (char === '"') {
      inQuotes = true;
    } else if (char === ',') {
      cells.push(cur);
      cur = '';
    } else {
      cur += char;
    }
  }
  cells.push(cur);
  return cells;
}

// Retorna uma matriz de linhas/celulas — sem chaves de objeto, pois a
// planilha de origem pode ter cabecalhos vazios ou duplicados (ex: grade
// semanal com "DOMINGO" repetido), o que colidiria em um objeto por linha.
function parseCSV(text) {
  return text
    .split(/\r?\n/)
    .filter(line => line.trim() !== '')
    .map(parseCSVLine);
}

function renderAtividades(container, rows) {
  if (!rows.length) {
    container.innerHTML = `<div class="card-body"><p style="color:var(--text-3)">Nenhuma atividade registrada</p></div>`;
    return;
  }

  const table = document.createElement('table');
  table.className = 'table';

  const thead = document.createElement('thead');
  const headerRow = document.createElement('tr');
  rows[0].forEach(cell => {
    const th = document.createElement('th');
    th.textContent = cell;
    headerRow.appendChild(th);
  });
  thead.appendChild(headerRow);
  table.appendChild(thead);

  const tbody = document.createElement('tbody');
  rows.slice(1).forEach(cells => {
    const tr = document.createElement('tr');
    cells.forEach(value => {
      const td = document.createElement('td');
      td.textContent = value;
      tr.appendChild(td);
    });
    tbody.appendChild(tr);
  });
  table.appendChild(tbody);

  container.innerHTML = '';
  container.appendChild(table);
}

export function initAtividadesRecentes() {
  loadAtividadesRecentes();
}
