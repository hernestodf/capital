# Deployment

## Regra principal
**Capital é um fork independente.** NUNCA executar `deploy/full-deploy.py` — ele envia para instâncias MT/DF/BA/MG que são sistemas separados (projeto original).

## Deploy em Capital
- O código em `/var/www/html/capital/` já está no ar. O subdomínio `capital.sisloc.online` serve `public/index.php`.
- Alterações no código surtem efeito imediato (não há step de build).
- Migrações devem ser executadas diretamente no banco `sisloc_newsisloc` via host `sisloc.online` (credenciais `DB_ONLINE_*` no `.env`).

## Scripts NÃO executar
- `deploy/full-deploy.py` — envia para MT/DF/BA/MG (fork original)
- `scripts/sync-instances.sh` — sincroniza entre instâncias do fork original
- `scripts/deploy.sh --sync` — idem
