
    FOREIGN KEY (id_cliente) REFERENCES clientes(id),
    id_produtor INT,                   -- FK → produtores
    id_demandante INT,                 -- FK → demandantes
    nome_evento VARCHAR(255),
    local_evento VARCHAR(255),
    os_cliente VARCHAR(50),            -- Número da ordem de serviço
    demandante_local VARCHAR(255),
    telefone_demandantelocal VARCHAR(20),
    observacao TEXT,
    data_montagem DATE,
    hora_montagem TIME,
    data_inicio DATE,                  -- Início do evento
    hora_inicio TIME,
    data_fim DATE,                     -- Fim do evento
    hora_fim TIME,
    data_desmontagem DATE,
    hora_desmontagem TIME,
    estado ENUM('O', 'L'),             -- O=Orçamento, L=Locação
    status_locacao ENUM('A', 'F'),     -- A=Aberta, F=Fechada
    evento_montado TINYINT(1),
    evento_desmontado TINYINT(1),
    observacoes_fechamento TEXT,
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Configuração .env Completa

```env
# Ambiente
APP_ENV=local                          # local ou production
BASE_URL=http://localhost/sisloc/public

# Banco Local
DB_LOCAL_HOST=localhost
DB_LOCAL_PORT=3306
DB_LOCAL_NAME=sisloc
DB_LOCAL_USER=root
DB_LOCAL_PASS=sua_senha

# Banco Produção
DB_ONLINE_HOST=localhost
DB_ONLINE_PORT=3306
DB_ONLINE_NAME=sisloc_prod
DB_ONLINE_USER=sisloc_user
DB_ONLINE_PASS=sua_senha

# WhatsApp Bot
WHATSAPP_BOT_URL=http://201.23.68.17:3000
WHATSAPP_API_KEY=sua_chave_secreta
WHATSAPP_SESSION_ID=novoframework
WHATSAPP_PUBLIC_URL=https://seudominio.com/sisloc/public
```

---

## Apêndice B: Glossário

| Termo | Significado |
|-------|-------------|
| **Orçamento (O)** | Proposta comercial não confirmada |
| **Locação (L)** | Orçamento convertido em contrato ativo |
| **Sala** | Ambiente/espaço dentro de um evento (ex: "Palco Principal", "Camarim") |
| **Serial** | Número de identificação único de um equipamento |
| **Montagem** | Processo de instalar equipamentos em um evento |
| **Devolution** | Processo de retornar equipamentos ao estoque |
| **Demandante** | Pessoa responsável local pelo evento (cliente no local) |
| **Produtor** | Profissional que gerencia a produção do evento |
| **Planilha** | Catálogo de produtos com valores de locação |
| **OS** | Ordem de Serviço (número de referência do cliente) |

---

## Apêndice C: Checklists de Deploy

### Deploy para Produção

```bash
# 1. Backup do banco
mysqldump -u root -p sisloc > backup_$(date +%Y%m%d).sql

# 2. Enviar arquivos (excluir vendor e .env)
rsync -avz --exclude=vendor --exclude=.env --exclude=.git \
  /var/www/html/sisloc/ user@servidor:/var/www/html/sisloc/

# 3. SSH no servidor
ssh user@servidor

# 4. Instalar dependências
cd /var/www/html/sisloc
composer install --no-dev --optimize-autoloader

# 5. Configurar .env de produção
nano .env
# APP_ENV=production
# BASE_URL=https://seudominio.com/sisloc
# DB_ONLINE_* preenchido

# 6. Aplicar migrations pendentes
mysql -u user -p sisloc < database/migrations/XXX_nova_migration.sql

# 7. Permissões
chmod -R 755 storage/
chmod -R 755 tmp/

# 8. Clear cache (se aplicável)
rm -rf storage/cache/*

# 9. Testar
curl https://seudominio.com/sisloc/public/health
```

### Pós-Deploy Checklist

- [ ] Login funciona
- [ ] Dashboard carrega
- [ ] Listagem de eventos funciona
- [ ] PDF gera corretamente
- [ ] WhatsApp envia mensagens
- [ ] Formulários salvam dados
- [ ] Permissões RBAC funcionam
- [ ] Temas aplicam corretamente

---

## Apêndice D: Contato e Suporte

| Recurso | Localização |
|---------|-------------|
| **Documentação Principal** | `/var/www/html/sisloc/docs/SYSTEM_DOCUMENTATION.md` (este arquivo) |
| **AGENTS.md** | `/var/www/html/sisloc/AGENTS.md` (instruções para IA) |
| **Design System** | `/var/www/html/sisloc/docs/layout/branco/` |
| **Database Dump** | `/var/www/html/sisloc/database/sisloc.sql` |
| **Migrations** | `/var/www/html/sisloc/database/migrations/` |
| **WhatsApp Bot Docs** | Contexto da conversa anterior (não arquivo) |
| **Framework Docs** | `/var/www/html/sisloc/docs/learning/` |

---

**FIM DA DOCUMENTAÇÃO**

> Esta documentação foi gerada através de análise de engenharia reversa do código fonte.  
> Para dúvidas ou correções, consultar o código diretamente dos arquivos fonte.  
> Última atualização: 2026-04-24
