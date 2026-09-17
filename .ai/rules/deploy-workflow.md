# Deploy Workflow Rule

## Regra: Toda alteração deve ser confirmada e deployada para produção

**Última atualização:** 2026-06-18

### Fluxo Obrigatório

1. **Ler instruções** (`.ai/bootstrap/ai_instructions.md`)
2. **Fazer alterações** no código local
3. **Resumir mudanças**:
   - Arquivos alterados
   - O que foi modificado
   - Por quê
4. **Aguardar confirmação do usuário**:
   ```
   ✅ "Faz o deploy" / "Enviar para produção"
   ⏸️  "Aguarde" / "Antes testa"
   ```
5. **Deploy automático** se aprovado:
   - Via `python3 .ai/scripts/deploy-verify.py <arquivo>`
   - Envia para `/public_html/subdomains/capital/`
   - **Verifica SHA256** no servidor após upload
   - Confirma sucesso com "✅ Integridade verificada"

### Arquivos que Requerem Deploy

- ✅ PHP views (`.php`)
- ✅ JavaScript (`.js`)
- ✅ CSS (`.css`)
- ✅ Configurações (`.env`, `.htaccess`)
- ❌ NÃO: Testes, docs, arquivos temporários

### FTP Credentials (lidas do .env automaticamente)

- Host: `ftp.sisloc.online`
- User: `sisloc`
- Pass: `Micro987!`
- Remote Path: `public_html/subdomains/capital/`

### Script Único

Apenas `deploy-verify.py` é autorizado. Os demais foram removidos por não verificarem integridade.

### Exemplo de Resumo Antes de Deploy

```
📝 ALTERAÇÕES DETECTADAS:

Arquivo: views/evento/partials/edit-montar-os.php
- Linha 259-267: Reorganizou layout de seriais (inline flex)
- Motivo: Serial aparecer na linha inteira do card

Pronto para deploy? (S/N)
```

### Validação Pós-Deploy

- ✅ Confirmar arquivo enviado
- ✅ Usuário testa no navegador (Ctrl+Shift+R)
- ✅ Reportar resultado
