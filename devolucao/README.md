# DevolvApp — PWA de Devolução de Itens

Sistema PWA para registro de devolução de itens em eventos.

## Estrutura de arquivos

```
pwa-devolucao/
├── index.html       ← Aplicação principal (login + devolução)
├── manifest.json    ← Configuração do PWA
├── sw.js            ← Service Worker (suporte offline)
├── icons/
│   ├── icon-192.png ← Ícone do app (192x192)
│   └── icon-512.png ← Ícone do app (512x512)
└── README.md
```

## Como rodar localmente

Você precisa servir os arquivos via HTTP (não abrir direto como arquivo).

### Opção 1 — Python (sem instalar nada)
```bash
cd pwa-devolucao
python3 -m http.server 8080
```
Acesse: http://localhost:8080

### Opção 2 — Node.js (npx serve)
```bash
cd pwa-devolucao
npx serve .
```

### Opção 3 — VS Code Live Server
Instale a extensão "Live Server" e clique em "Go Live".

## Deploy (produção)

O app é estático — funciona em qualquer host de arquivos:

- **Vercel**: `vercel deploy`
- **Netlify**: arraste a pasta para app.netlify.com
- **GitHub Pages**: suba para um repositório público
- **Firebase Hosting**: `firebase deploy`

> ⚠️ O Service Worker exige HTTPS em produção (exceto localhost).

## Personalização

### Adicionar eventos
Edite as `<option>` dentro do `<select id="event-select">` no `index.html`.

### Conectar a uma API real
No `index.html`, localize a função `handleEnviar()` e substitua o `setTimeout` pelo seu `fetch()`:

```javascript
async function handleEnviar() {
  // ...validação...

  const response = await fetch('https://sua-api.com/devolucoes', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      evento: document.getElementById('event-name-display').textContent,
      seriais: lines
    })
  });

  if (response.ok) showToast();
}
```

### Autenticação real
Substitua o `setTimeout` em `handleLogin()` pela chamada à sua API de autenticação.

## Funcionalidades PWA

- ✅ Instalável (banner automático no Android/Chrome)
- ✅ Funciona offline (Service Worker + Cache API)
- ✅ Ícone na tela inicial
- ✅ Splash screen nativa
- ✅ Suporte a dark mode (prefers-color-scheme)
- ✅ Responsivo (mobile-first)
- ✅ Sem frameworks externos (zero dependências)
