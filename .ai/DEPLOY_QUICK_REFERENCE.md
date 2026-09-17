# ⚡ Deploy - Referência Rápida

> **ÚNICO script autorizado:** `deploy-verify.py`
> Faz upload via FTP **e** verifica SHA256 no servidor.
> Scripts sem verificação foram removidos.

---

## Uso

```bash
# Arquivo único
python3 .ai/scripts/deploy-verify.py views/evento/partials/edit-montar-os.php

# Qualquer arquivo
python3 .ai/scripts/deploy-verify.py public/js/eventos/montar-os.js
python3 .ai/scripts/deploy-verify.py src/Repository/MontagemRepository.php
```

**Saída esperada:**
```
📋 Hash local: d83182560c98db0e...
📤 Enviando views/evento/partials/edit-montar-os.php... ✅
🔍 Verificando integridade... ✅
✨ Deploy confirmado!
   📋 Hash servidor: d83182560c98db0e...
   ✅ Integridade verificada (SHA256 matching)
```

---

## Credenciais FTP (lidas do .env automaticamente)

```
Host:   ftp.sisloc.online
User:   sisloc
Pass:   Micro987!
Remote: public_html/subdomains/capital/
```

---

## Workflow Obrigatório

```
1. Alterar código
2. Resumir mudanças para o usuário
3. Aguardar confirmação ("faz o deploy")
4. python3 .ai/scripts/deploy-verify.py <arquivo>
5. Confirmar ✅ SHA256 matching
6. Usuário testa (Ctrl+Shift+R)
```

---

## Troubleshooting

| Problema | Solução |
|----------|---------|
| Arquivo não aparece no navegador | Ctrl+Shift+R + aguardar 30s |
| Timeout FTP | Tentar novamente (servidor sobrecarregado) |
| SHA256 não bate | Upload corrompido — rodar novamente |
| "File not found" | Verificar caminho local com `ls` |

---

**Versão:** 3.0 | **Data:** 2026-06-18
