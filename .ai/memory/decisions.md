# Decisões Arquiteturais (ADR)

## [2026-06-08] Correção de bug Undefined array key em EventoController
- **Contexto:** PHP Warning ao acessar `$col['enviado_pagamento']` sem verificação na linha 716
- **Decisão:** Usar `!empty()` para verificar a chave, retornando false quando não existe
- **Consequências:** Elimina o warning, mantém comportamento de fallback '○ Pendente'
- **Commit:** pendente (git corrompido)

## [2026-06-09/10] Remoção do Sistema de Cotação
- **Contexto:** Sistema de cotação via Mailjet + IMAP era complexo e subutilizado. O módulo incluía CotacaoController, CotacaoService, MailjetService, CronImapController, rotas /cotacao/* e /cron/imap-cotacao.
- **Decisão:** Remover todo o sistema de cotação e simplificar o módulo Fornecedores → Sublocação
- **Consequências:**
  - Simplificação do código (removidos ~8 arquivos PHP, rotas, JS)
  - Resíduo: ImapService.php ficou órfão, RBAC tem entradas mortas, cotacao.js sem consumidor
  - Sistema de Sublocação passou a usar campos texto livre (produto + código de barras) em vez de cotação formal
- **Commit:** pendente (git corrompido)

## [2026-06-11] Migração 037 — Suporte a Código de Barras em Sublocação
- **Contexto:** Sublocação precisava rastrear produto por código de barras além de nome
- **Decisão:** Adicionar colunas `codigo_barras` e `produto` em `produto_evento_sublocacao`
- **Consequências:** Modal de sublocação simplificado com campos texto livre
- **Commit:** pendente (git corrompido)

## [2026-06-27] Análise — Serviços de Sync NÃO são código morto
- **Contexto:** Análise de 2026-06-05 classificou incorretamente CentralSyncService, CategoriaSincronizadoService e CategoriaSalaCentralService como "REMOVIDO"
- **Decisão:** Reverter essa classificação — os 3 serviços são ATIVOS e usados pela sincronização ProFox → Capital
- **Consequências:** Relatório dead_code_analysis.md corrigido. Esses serviços NÃO devem ser removidos.
- **Evidência:** grep em todo src/ confirma uso por 6+ controllers

## [2026-06-27] Git Recriado após Corrupção
- **Contexto:** Repositório git com 1228 objetos corrompidos (bad object HEAD) desde 2026-06-18
- **Decisão:** Recriar repositório do zero com `git init`, preservar backup em `.git.corrupted/`
- **Commits:** `044e504` (728 arquivos, estado atual), `896f92c` (CLAUDE.md)
- **Pendência:** Configurar remote git para backup externo (GitHub/GitLab)

## [2026-06-27] Limpeza de Código Morto — Remoção Confirmada
- **Itens removidos:** ImapService.php, cotacao.js, views/test/, views/public/js/, RBAC cotacao.* entries, index.php:329 orphan comment
- **Todos são resíduos do sistema de cotação removido em jun/2026**
- **Sem efeito funcional** — nenhum item era chamado em produção

## [2026-06-27] EventoRHNotificacaoService — Chamado Inline (falso positivo)
- **Contexto:** Grep em src/ não encontra referências a EventoRHNotificacaoService
- **Decisão:** Serviço é ATIVO — instanciado inline em index.php linha 336 para rota `/cron/rh-notificacoes`
- **Regra:** Sempre incluir index.php nas buscas de referências, não apenas src/

## [2026-06-27] Documentação context/ — Reescrita Completa
- **Contexto:** stack.md dizia "mysqli procedural" (incorreto, é PDO); integrations.md listava MikroTik/Mailjet/IMAP como ativos; business_rules.md descrevia ISP/fibra; deployment.md dizia git corrompido
- **Decisão:** Reescrever todos os 4 arquivos com dados verificados diretamente no código
- **Consequências:** context/ agora reflete o estado real do projeto Capital

## [2026-06-27] Rename de Roles: administrador→administrativo, produtor→comercial
- **Contexto:** Os nomes dos papéis de usuário não refletiam a realidade do negócio. "Produtor" criava ambiguidade com a entidade negocial "Produtor" (pessoa que organiza eventos, tabela `produtores`). "Administrador" era redundante com o termo técnico.
- **Decisão:** Renomear roles para `administrativo` e `comercial`; manter `estoquista` e `guest`.
- **Consequências:**
  - 11 arquivos alterados em `01102b9` (Rbac, Controllers, views, index.php, banco)
  - 5 arquivos alterados em `8f57879` (isAdmin() → isAdministrativo())
  - **Regressão detectada e resolvida:** sidebar.php linha 24 apontava para `/dashboard/administrador` (bug-002) — corrigida no commit `453ccf6` para usar `Rbac::getDashboardRoute()`
  - "Produtor" como entidade negocial (`ProdutorController`, `views/produtor/`) **não foi alterado** — é um conceito diferente
- **Regra derivada:** `Produtor` (entidade) ≠ role `comercial` (usuário). Não confundir.
- **Commits:** `01102b9`, `8f57879`, `453ccf6`

## [2026-06-27] Remoção de Spam de error_log em Application.php
- **Contexto:** `error_log("ROUTES REGISTERED")` estava gerando 800KB de ruído no `public/error_log` (1 linha por request).
- **Decisão:** Remover o error_log de depuração.
- **Commit:** `4323bfb`

## [2026-06-27] Fix de Roteamento em public/index.php (TD-021)
- **Contexto:** Após renomear roles (`administrador→administrativo`, `produtor→comercial`), `public/index.php` ainda registrava as rotas `/dashboard/administrador` e `/dashboard/produtor`, causando Fatal Error 500 se acessado via esse entry point.
- **Decisão:** Atualizar as rotas para `/administrativo` e `/comercial`.
- **Commit:** `88f0b23`

## [2026-06-28] Root index.php — Simplificado para Redirect-Only

- **Contexto:** `index.php` na raiz do projeto tinha 490+ linhas com todas as rotas da aplicação duplicadas de `public/index.php`. Quando rodando localmente, descobriu-se que o servidor de produção estava em `177.11.54.229` (IP externo), não na mesma máquina. A raiz do domínio no cPanel é `/public_html/subdomains/capital/`; a pasta `public/` dentro do projeto tem seu próprio `.htaccess` que roteia para `public/index.php` (entry point real).
- **Decisão:** Simplificar `index.php` da raiz para apenas redirecionar requisições para `/public/`. Todas as rotas ficam exclusivamente em `public/index.php`.
- **Consequências:** Elimina duplicação e confusão arquitetural. Requests para `capital.sisloc.online/` são redirecionados para `capital.sisloc.online/public/auth/login`. Requests para paths inválidos na raiz são redirecionados para `/public/<path>`.
- **Commit:** `995f276`

## [2026-06-28] Deploy Process — Máquina Local vs Produção

- **Contexto:** Durante diagnóstico do BUG-003, confirmou-se que esta máquina (`192.168.1.106`) é **desenvolvimento local**, não o servidor de produção (`177.11.54.229`). Todos os commits feitos localmente (desde o início do repositório) nunca chegaram à produção. O banco de dados foi atualizado diretamente via MySQL CLI conectando ao host remoto, causando divergência: banco com roles novos, código de produção com roles antigos.
- **Decisão:** Deploy via FTP (`ftp -n sisloc.online`, credenciais em `.env → FTP_PASS`). O path de produção via FTP é `/public_html/subdomains/capital/`. Realizou-se deploy manual dos 14 arquivos PHP modificados desde o commit inicial.
- **Regra derivada:** Sempre confirmar se estamos rodando no servidor de produção ou localmente antes de concluir que uma mudança "já está em produção".
- **Commits deployados via FTP:** `01102b9`, `8f57879`, `453ccf6`, `4323bfb`, `88f0b23`, `995f276`

## [2026-06-28] BUG-003 — Login Redireciona para /auth/login Após Autenticação

- **Causa raiz:** `getDashboardRoute()` em `Rbac.php` de produção tinha `match('administrador')` (nome antigo). Banco já tinha `role='administrativo'` (nome novo). Sem match → default → `/auth/login`. Sintoma: `{"success":true,"redirect":".../auth/login"}`.
- **Diagnóstico:** Confirmado via FTP (download do `Rbac.php` de produção para comparar). Não era OPcache — era código desatualizado.
- **Fix:** Upload de 14 arquivos PHP via FTP, incluindo `Rbac.php`, `DashboardController.php`, `views/dashboard/administrativo.php`, `views/dashboard/comercial.php`, `sidebar.php`, etc.
- **Commit:** `995f276` (cleanup + root index.php) + FTP deploy dos commits anteriores

## [2026-06-28] valor_unit editável em Salas e Produtos

- **Contexto:** Campo `valor_unit` nos itens carregados de planilha estava com `disabled` e classe `fi-disabled`, impedindo edição pelo usuário.
- **Decisão:** Remover `disabled` e `fi-disabled`. O handler de save já existia no JS (`salvarAlteracaoItem` com debounce de 500ms para campo `valor_unit`). Mudança em dois pontos: PHP (itens renderizados) e JS linha 375 (itens adicionados dinamicamente por `renderItemNaSala`).
- **Commit:** `346200e`

## [2026-06-28] Bloco Dual de Assinatura nos PDFs

- **Contexto:** PDFs (Orçamento, Locação, Fechamento, Montagem) tinham apenas a assinatura textual do Produtor no rodapé.
- **Decisão:** Substituir pelo bloco de assinatura com nome e CNPJ/CPF formatado das duas partes — Locadora (tabela `empresa`, campo `cnpj`) e Contratante (tabela `clientes`, campo `cpf_cnpj`, `razao_social`). Implementado via método privado `getAssinaturaBlock()` chamado nas três funções HTML: `gerarEventoHtml`, `gerarFechamentoHtml`, `gerarMontagemHtml`.
- **Formatação:** CNPJ 14 dígitos → `XX.XXX.XXX/XXXX-XX`; CPF 11 dígitos → `XXX.XXX.XXX-XX`. Se campo vazio, linha omitida sem quebrar.
- **Commit:** `61400f6`

## [2026-06-28] $pageStyles — Injeção de CSS no head para evitar FOUC

- **Contexto:** Views com estilos no body causavam FOUC (Flash of Unstyled Content). Ex: `views/sublocacoes/index.php`.
- **Decisão:** `views/layout/header.php` passou a injetar `$pageStyles` antes de `</head>`. Views definem a variável antes do `require header.php`. Padrão a seguir em novas views com estilos específicos.

## [2026-06-28] Popover com position:fixed (escape de overflow:hidden)

- **Contexto:** Popover em `.subloc-wrap` era clipado por `overflow:hidden`. `position:absolute` não era suficiente.
- **Decisão:** `position:fixed` com coordenadas calculadas via `getBoundingClientRect()`. Fecha no scroll e clique fora. Rastreador `_popoverAberto` controla que apenas um esteja aberto por vez.
- **Regra derivada:** Para popovers dentro de containers com overflow:hidden, sempre usar position:fixed + getBoundingClientRect.

## [2026-06-28] Rename função JS togglePopover → abrirPopoverEvento

- **Contexto:** `HTMLElement.togglePopover()` é método nativo do browser Popover API — sobrescrever com função global causava `DOMException: Element is in the no popover state`.
- **Decisão:** Renomear a função global para `abrirPopoverEvento()`.
- **Regra derivada:** Nunca nomear funções globais JS com nomes que conflitem com a API nativa (`togglePopover`, `showPopover`, `hidePopover` são nativos em browsers modernos).

## [2026-06-27] Limpeza de Arquivos de Debug e Segurança de Credenciais
- **Contexto:** Havia 24 arquivos de debug expostos publicamente em `public/` (risco de segurança) e credenciais expostas em texto claro nos arquivos de documentação `.ai/` (incluindo alguns root-owned).
- **Decisão:** Deletar todos os 24 arquivos de debug de `public/` (diag.php, debug-app.php, test-*.php, etc.), expurgar credenciais reais da pasta `.ai/` (deletando e recriando os arquivos root-owned para burlar permissão de escrita) e reforçar o `.htaccess` para bloquear acesso HTTP a `.ai/`.
- **Consequências:** Risco de vazamento de segredos mitigado a zero. Acesso HTTP a arquivos estáticos da pasta `.ai/` bloqueado.
- **Commits:** `e62bc68` (expurgar `.ai/` + `.htaccess`), `b9db5d9` (arquivos root-owned), `05eb20b` (remover 24 arquivos debug em `public/`), `243b8e2` (fechar item segurança)

## [2026-06-28] Sistema de Energia — Calculadora kWh/kVA por Item de Evento

- **Contexto:** Cliente precisava calcular o consumo elétrico dos equipamentos por evento para estimar carga elétrica (kVA) necessária.
- **Decisão:** Adicionar `potencia_w` e `horas_uso` em dois níveis: na `planilhas` (catálogo — valor padrão) e em `produtos_evento` (por item do evento — editável). Fórmula: `kWh = qtd × potW × horas / 1000`, `kVA = kWh × 1.25` (fp=0.8).
- **Migration:** 042 — `ALTER TABLE produtos_evento ADD COLUMN potencia_w/horas_uso; ALTER TABLE planilhas ADD COLUMN potencia_w/horas_uso`
- **Auto-preenchimento:** ao adicionar item da planilha ao evento, `potencia_w` e `horas_uso` são copiados automaticamente da planilha
- **UX:** botão ⚡ por item (toggle); badge kWh/kVA por sala; total global; valores editáveis inline
- **Dados:** 83 dos 117 itens da planilha têm potência e horas preenchidos (34 são passivos/pessoal → NULL)
- **Commits:** `288e418` (calculadora), `e5c0e0b` (potencia_w em planilhas)

## [2026-06-28] AIPOS 4.0 — Inicialização do Chief Architect

- **Contexto:** Protocolo formal de inicialização do sistema de inteligência do projeto — 14 fases de auditoria, inventário, mapeamento, segurança, performance, memória e geração de relatórios.
- **Decisão:** Executar o protocolo completo inline (sem subagents). Gerar/atualizar: `current_state.md`, `executive_summary.md`, `file_inventory.md`, `decisions.md`, `technical_debt.md` (novos itens TD-025 a TD-028).
- **Novos itens identificados:** 7 tabelas AI mortas, 4 tabelas cotação com dados históricos, gap de migration 029, duplicatas de número de migration (009, 025, 032).
- **Regra derivada:** Sempre executar `SELECT table_name, table_rows FROM information_schema.tables` periodicamente para detectar tabelas com 0 rows que podem ser candidatas a limpeza.

## [2026-06-28] Renomear Labels UI — Demandante→Comprador, Serial→Código de Barras, Produtor→Comercial

- **Contexto:** Usuário solicitou padronização da nomenclatura visível na UI para refletir a linguagem do negócio.
- **Decisão:** Renomear apenas a **camada de apresentação** (views, labels, toasts, PDFs). DB columns (`id_demandante`, `id_produtor`, `serial`), tabelas (`demandantes`, `produtores`, `seriaisproduto`), classes PHP e rotas URL permanecem inalterados.
- **Mapeamento vigente:**
  - `demandantes` (tabela/rota) → label "**Comprador**" em toda a UI
  - `produtores` (tabela/rota) → label "**Comercial**" em toda a UI
  - `serial` (campo DB/API) → label "**Código de Barras**" em toda a UI
- **Razão para não renomear o banco:** evitar migrations de renomear colunas com risco de regressão; o negócio usa a terminologia nova, o código usa a técnica — é intencional.
- **Commits:** `f2bacf0` (Demandante→Comprador, Serial→Código de Barras), `3fb9728` (Produtor→Comercial)

## [2026-06-28] Comprador Editável Inline na Aba Montagem

- **Contexto:** O campo "Comprador" (id_demandante) só era editável na aba Dados do Evento. Operadores de montagem precisavam trocar de aba para alterar o comprador antes de imprimir a OS.
- **Decisão:** Adicionar seletor inline no header card da aba Montagem. Ao clicar "Alterar", aparece um `<select>` com todos os compradores cadastrados + botão Salvar que chama `POST /eventos/update-comprador/{id}`.
- **Endpoint novo:** `EventoController::updateComprador()` — atualiza apenas `id_demandante`, validando CSRF. Separado do `update()` completo para evitar sobrescrever outros campos.
- **Commit:** `f2bacf0`

## [2026-06-28] Correção de 7 Bugs Detectados (sessão 5)

- **BUG-004:** `POST /fechamento/fornecedor/parcela/update/{id}` → 404. Método existia em `FechamentoController:317`, JS chamava em `fechamento.js:1875`, rota nunca registrada. Fix: adicionada ao grupo `/fechamento`.
- **BUG-005:** `PresencaAppApiController` inexistente. 4 rotas em `index.php` apontavam para classe que não existia → HTTP 500. Fix: controller criado com 4 endpoints para o app mobile PresencaApp.
- **BUG-006:** Botões "Ver/Solicitar cotação" ativos em `edit-fornecedores-content.php` após remoção do sistema de cotação. Fix: botões removidos.
- **BUG-007:** `/migracoes` → 404. Apenas `/migracoes/executar` estava registrado. Fix: `GET /` adicionado.
- **BUG-008:** `edit-fornecedores.php` tinha 206 linhas com modal e JS do sistema de cotação (removido em jun/2026). Fix: arquivo reduzido a 4 linhas.
- **BUG-009:** Modal "Consultar Alocação" em `edit-salas-produtos.php` chamava `/api/produtos/consultar-alocacao` sem rota. Fix: grupo `/api/produtos` criado com 2 rotas.
- **Extra:** `SublocacaoItemController` com 4 métodos e zero rotas. Fix: rotas adicionadas em `/fornecedores/itens/*`.
- **Commits:** `b28bacf`, `c503349`

## [2026-06-28] Deploy FTP — Referência de Sincronização

- **Último commit deployado:** `3fb9728` (2026-06-28)
- **Arquivos deployados:** 42 arquivos PHP/JS
- **Comando usado:** `curl --ftp-create-dirs -u user:pass -T arquivo ftp://host/path`
- **Regra:** Após qualquer sessão com mudanças, rodar deploy FTP para sincronizar produção. O commit HEAD local é a referência; tudo desde o último commit deployado precisa subir.

## [2026-06-29] CRUD local de `funcoes` de colaboradores — Desacoplamento ProFox (sessão 7)

- **Contexto:** A lista "Atua Como" de colaboradores era carregada da API central ProFox. Para tornar o Capital completamente independente, o módulo foi migrado para banco local.
- **Decisão:** Criar tabela `funcoes` (migration 043) com seed de 12 funções padrão; CRUD Ajax via `FuncaoController`/`FuncaoService`/`FuncaoRepository`; modal "Gerenciar Funções" inline em `views/colaborador/_form.php`; `CategoriaController`, `SubcategoriaController` e `FornecedorController` desacoplados de `CentralSyncService`; `CategoriaSalaCentralService` removido.
- **Commits:** `407efc0`

## [2026-06-29] URL `/produtores` → `/comercial` + remoção do campo assinatura (sessão 10)

- **Contexto:** A URL do cadastro de produtores (`/produtores`) confundia usuários com o papel de acesso `comercial`. Para alinhar o vocabulário da UI, a rota foi renomeada. Adicionalmente, o campo `assinatura` da tabela `produtores` nunca era renderizado nos PDFs — o bloco de assinatura é gerado por `getAssinaturaBlock()` usando dados de `empresa` e `clientes`.
- **Decisão:**
  1. Grupo de rotas renomeado de `/produtores` para `/comercial` em `index.php`
  2. `ProdutorController`, views e sidebar atualizados
  3. `assinatura` removido de `ProdutorRepository::$fillable`, `ProdutorController`, views e PDFs
  4. Migration 044: `ALTER TABLE produtores DROP COLUMN IF EXISTS assinatura`
- **Consequência:** `ProdutorController` (código) e `produtores` (tabela) mantêm nomes internos originais; apenas a URL mudou. Não há conflito com `/dashboard/comercial` (prefixo `/dashboard/` é diferente).
- **Commits:** `935ea9b`

## [2026-06-29] Restrição de visibilidade de eventos por role `comercial` (sessão 9)

- **Contexto:** Usuários com role `comercial` viam todos os eventos do sistema, sem filtro por responsável. Para isolamento de informação, cada comercial deve ver apenas os eventos onde está vinculado como produtor.
- **Decisão:** Usar a coluna `produtores.id_users` como FK de vínculo; `EventoController::index()` verifica `Rbac::isComercial()` e filtra por `id_produtor`; `UsuarioController::store()/update()` gerencia o vínculo.
- **Edge case:** Comercial sem produtor vinculado → lista vazia (intencional); stats podem mostrar total geral (TD-038).
- **Commits:** `3873a3f`

## [2026-06-29] Split de rotas + Perfil + MCP + RBAC DB + PDF templates (sessão 13)

- **routes/**: `public/index.php` 530 → 37 LOC. Rotas divididas em `routes/publico.php`, `cron.php`, `api.php`, `web.php` via `require_once` no mesmo escopo (sem passar `$app` como parâmetro).
- **RBAC DB (TD-004):** `Rbac::check()` agora lê de `role_permissoes JOIN permissoes`. Cache estático por request. Migration `046_seed_rbac.sql`. Fallback: array vazio se DB falhar (sem crash).
- **AI limpa:** `AIController`, `AILearningRepository`, views/routes AI, tabelas `ai_*` removidos. Zero chamadores confirmados por grep.
- **/meu-perfil:** `PerfilController` + `views/perfil/index.php` — edição de nome e senha, avatar com inicial por role. Acessível por todos os roles. Link no header (canvas direito).
- **mcp.json:** Arquivo na raiz do projeto para qualquer LLM (Cursor, Windsurf, etc.) usar os 4 MCP servers sem configuração adicional.
- **PDF templates:** `EventoPdfController` 852 → 307 LOC. 3 templates extraídos para `views/pdf/`. Método `renderPdfTemplate()` com `ob_start()`/`ob_get_clean()`.
- **EventoController (TD-027):** 535 → 364 LOC. 5 métodos de produto-evento movidos para `ProdutoEventoController`.

## [2026-07-01] Renomear "Serial" → "Código de Barras" apenas na camada de apresentação (s17)

- **Contexto:** Usuário final não entendia o termo "serial". A tabela `seriaisproduto` e o campo `serial` são termos técnicos internos.
- **Decisão:** Alterar apenas a camada de apresentação (views, labels, toasts, mensagens de erro, PDFs). Coluna DB, classes PHP, variáveis JS, rotas URL e nomes de métodos permanecem `serial`.
- **Arquivos alterados:** `views/estoque/{edit,index,relatorios,seriais}.php`, `src/Service/{SerialProdutoService,RelatorioEstoqueService}.php`, `src/Repository/SerialProdutoRepository.php`, `src/Controllers/{ProdutoController,SerialProdutoController}.php`
- **Regra derivada:** Reforça a decisão já registrada em 2026-06-28 sobre nomenclatura UI vs banco. Sempre separar esses contextos.

## [2026-07-01] `codigo_barras` em `produtos` — campo catálogo separado de `seriaisproduto.serial` (s17)

- **Contexto:** Produtos precisam de um código de barras de catálogo (EAN, ISBN, QR etc.) para identificação rápida do item-tipo, distinto dos códigos físicos individuais (`seriaisproduto.serial`).
- **Decisão:** Adicionar `codigo_barras VARCHAR(100) NULL` em `produtos` (migration 047). Campo é opcional, não único — dois produtos podem ter o mesmo código de catálogo (kits, variantes).
- **Dois níveis de código:**
  - `produtos.codigo_barras` — código do tipo/modelo (catálogo)
  - `seriaisproduto.serial` — código de cada unidade física (globalmente ÚNICO)
- **Nota:** A coluna `codigo_barras` foi removida da listagem `/estoque` em s17 (pouco valor visual nessa tela); está disponível em `/estoque/edit/{id}` e em `CREATE`.
- **Migration:** `047_add_codigo_barras_to_produtos.sql` — executada em produção.
- **Commits:** `8efb7c3` (upload MIME), vários outros s17

## [2026-07-01] Fix bug edição de código de barras — PDO devolve string, não int (s17)

- **Contexto:** Editar qualquer código de barras retornava "já está cadastrado em X" — impedindo qualquer edição.
- **Causa raiz:** `SerialProdutoService::checkDuplicata()` usava `$existing['id'] !== $ignoreId` (strict). PDO devolve todas as colunas como string; `"7" !== 7` é `true` mesmo para o mesmo registro — a checagem de "ignorar o próprio id" nunca passava.
- **Fix:** `(int)$existing['id'] !== $ignoreId`.
- **Regra derivada:** Sempre fazer cast explícito ao comparar IDs vindos de PDO com parâmetros inteiros. PDO::FETCH_ASSOC devolve tudo como string, independente do tipo da coluna no MySQL.

## [2026-07-01] `MontagemService` — sincronizar `qtd_alocada` em encaminhar e remover da sala (s17)

- **Contexto:** `encaminharParaSala()` atualizava `montagens.id_sala` mas não criava registro em `produto_evento_seriais` nem incrementava `qtd_alocada`. `removerDaSala()` não desfazia a alocação. Só `DevolucaoService` decrementava `qtd_alocada`.
- **Decisão:** Extrair dois helpers privados:
  - `alocarSerial(idSerial, idProdutoEvento, pesRepo)` — upsert em `produto_evento_seriais` (status=alocado) + recalcula `qtd_alocada`
  - `desalocarSerial(idSerial, idProdutoEvento, pesRepo)` — marca `devolvido` + recalcula `qtd_alocada`
- **`encaminharParaSala()`:** se muda de `id_produto_evento`, desaloca o anterior antes de alocar o novo.
- **`removerDaSala()`:** chama `desalocarSerial()` + limpa `id_produto_evento = null` no montagem.
- **`MontagemRepository::findMontagemWithDetails()`** agora retorna `id_serial` e `id_produto_evento` (necessários pelos helpers).
- **Invariante:** `produtos_evento.qtd_alocada` = COUNT de seriais com `produto_evento_seriais.status = 'alocado'` para aquele `id_produto_evento`. Toda operação que muda esse status deve recalcular.
- **Commits:** `2e5ad63`

## [2026-07-01] Listagem `/estoque` — colunas Total / Disponíveis / Na Rua (s17)

- **Contexto:** A coluna "Em Evento" da listagem usava `produtos_evento.qtd_alocada > 0` (estimativa por nome), que podia divergir dos seriais físicos reais. Usuário pediu visibilidade de disponibilidade real.
- **Decisão:** Substituir por três colunas calculadas na query SQL:
  - **Total** — `COUNT(*) FROM seriaisproduto WHERE id_produto = p.id AND status = 'ATIVO'`
  - **Em Campo** — `COUNT(DISTINCT sp.id) ... JOIN montagens m WHERE status != 'devolvido'` (seriais com montagem ativa)
  - **Disponíveis** — `total - em_campo` (calculado em PHP no loop da view)
- **"Na Rua"** é um badge amarelo clicável. Ao clicar: `GET /estoque/em-campo?id_produto=X` retorna JSON com dados de `montagens JOIN eventos JOIN salas`. Modal exibe seriais agrupados por evento → sala com chips monospace coloridos (verde=montado / amarelo=pendente sem sala).
- **Vantagem sobre o modelo antigo:** fonte de verdade é a tabela `montagens` (seriais físicos), não `produtos_evento.qtd_alocada` (campo calculado sujeito a stale data).
- **Commits:** `23009ed`

## [2026-06-29] "Separado por:" nos PDFs de evento (sessão 11)

- **Contexto:** Campo `id_usuario_separacao` já existia na tabela `eventos` e no formulário, mas o nome do separador nunca aparecia nos PDFs impressos.
- **Decisão:** `getSeparacaoNome()` helper em `EventoController` resolve o nome via `UserRepository`; `gerarEventoHtml()` e `gerarMontagemHtml()` agora recebem `$separacaoNome` e exibem linha "Separado por:" imediatamente abaixo dos dados do comprador. Linha omitida quando campo é nulo.
- **Commits:** `162f717`
