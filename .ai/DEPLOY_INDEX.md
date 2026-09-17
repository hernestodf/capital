# 📖 Deploy — Índice

## Script Único Autorizado

```bash
python3 .ai/scripts/deploy-verify.py <arquivo>
```

> Upload FTP + verificação SHA256. **Nenhum outro script de deploy deve ser usado.**
> `deploy-fast.py`, `deploy-ultra.py`, `deploy.py` e `deploy.sh` foram **removidos** —
> não faziam verificação de integridade pós-upload.

---

## Documentação

| Doc | Conteúdo |
|-----|----------|
| `.ai/DEPLOY_QUICK_REFERENCE.md` | Comandos, workflow, troubleshooting |
| `.ai/DEPLOY_DOCUMENTACAO_COMPLETA.md` | Detalhes técnicos, credenciais |
| `.ai/rules/deploy-workflow.md` | Regra obrigatória de fluxo |

---

## Exemplos

```bash
python3 .ai/scripts/deploy-verify.py views/evento/partials/edit-montar-os.php
python3 .ai/scripts/deploy-verify.py public/js/eventos/montar-os.js
python3 .ai/scripts/deploy-verify.py src/Repository/MontagemRepository.php
```

**Versão:** 3.0 | **Data:** 2026-06-18
