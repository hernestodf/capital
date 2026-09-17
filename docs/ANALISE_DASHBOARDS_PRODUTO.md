# Análise de Produto: Dashboards por Perfil - SisLoc v3.0.0

**Data da Análise:** 27/04/2026  
**Analista:** Engenheiro de Produto Sênior (UX + Sistemas de Gestão)  
**Status:** ✅ ANÁLISE COMPLETA - Nenhuma alteração realizada

---

## ÍNDICE

1. [Visão Geral do Sistema](#1-visão-geral-do-sistema)
2. [Módulos Existentes](#2-módulos-existentes)
3. [Perfis e Permissões](#3-perfis-e-permissões)
4. [Dashboards Atuais](#4-dashboards-atuais)
5. [Análise por Perfil](#5-análise-por-perfil)
   - 5.1 Administrador
   - 5.2 Produtor
   - 5.3 Estoquista
6. [Dados Disponíveis vs Ausentes](#6-dados-disponíveis-vs-ausentes)
7. [Queries Base](#7-queries-base)
8. [Proposta de Layout](#8-proposta-de-layout)
9. [Plano de Implementação](#9-plano-de-implementação)

---

## 1. VISÃO GERAL DO SISTEMA

### 1.1 O que é o SisLoc

SisLoc é um **sistema de gestão de locação de equipamentos e produtos para eventos**, com módulos de:
- Gestão de eventos (sonorização, iluminação, produtos)
- Gestão de colaboradores (RH, presenças, diárias)
- Gestão de estoque (produtos, categorias, salas)
- Gestão financeira (cotações, contas a pagar, fechamento)
- Gestão de clientes, fornecedores e demandantes
- Montagem/devolução de equipamentos
- Dashboard por perfil de usuário

### 1.2 Stack Técnica

| Componente | Tecnologia |
|------------|------------|
| Backend | PHP 8.4.11 (MVC custom) |
| Database | MySQL 8.4.8 |
| Frontend | Design System 3.0 "White Rabbit" |
| Autenticação | Session PHP + RBAC |
| Perfis | 3 principais (admin, produtor, estoquista) |

---

## 2. MÓDULOS EXISTENTES

### 2.1 Mapeamento Completo de Módulos

| # | Módulo | Tabelas | Ações Principais | Status |
|---|--------|---------|------------------|--------|
| 1 | **Usuários** | `users` | CRUD, ativar/desativar, definir role | ✅ Completo |
| 2 | **Clientes** | `clientes` | CRUD, busca CEP, listar | ✅ Completo |
| 3 | **Fornecedores** | `fornecedores` | CRUD, busca CNPJ, listar | ✅ Completo |
| 4 | **Colaboradores** | `colaboradores` | CRUD, WhatsApp, diárias | ✅ Completo |
| 5 | **Eventos** | `eventos` | CRUD, status, datas, demandante | ✅ Completo |
| 6 | **Produtos** | `produtos_evento` | CRUD, categorias, estoque, serial | ✅ Completo |
| 7 | **Categorias** | `categorias`, `subcategorias`, `categorias_sala` | CRUD hierárquico | ✅ Completo |
| 8 | **Salas** | `salas` | CRUD, produtos por sala | ✅ Completo |
| 9 | **Cotações** | `item_cotacoes`, `item_cotacoes_parcelas`, `item_cotacoes_mensagens`, `item_cotacoes_anexos` | CRUD completo, parcelas, mensagens | ✅ Completo |
| 10 | **Contas a Pagar** | `contas_pagar` | CRUD, parcelas, comprovante, colaborador | ✅ Completo |
| 11 | **Fechamento** | `evento_fotos`, `evento_colaboradores_fotos` | Fotos evento/colaborador | ✅ Completo |
| 12 | **Montagem** | `montagens` | Registro montagem por evento | ✅ Completo |
| 13 | **Devolução** | (usa `montagens` + `produtos_evento`) | Devolução de equipamentos | ✅ Completo |
| 14 | **Presenças** | `evento_colaboradores`, `evento_colaborador_presencas` | Registro manual, WhatsApp | ✅ Completo |
| 15 | **RH** | `colaboradores`, `evento_colaboradores`, `evento_colaborador_presencas` | Cálculos financeiros, fechamento | ✅ Completo |
| 16 | **Demandantes** | `demandantes` | CRUD | ✅ Completo |
| 17 | **Produtores** | `produtores` | CRUD | ✅ Completo |
| 18 | **Dashboard** | (dados de vários módulos) | Visão por perfil | ⚠️ Mock data |
| 19 | **Configurações** | `empresa` | Dados empresa, temas, permissões | ✅ Completo |
| 20 | **API REST** | `api_keys`, `api_request_logs` | CRUD externo com auth | ✅ Completo |
| 21 | **AI** | `ai_*` (6 tabelas) | Aprendizado, correções | ✅ Completo |
| 22 | **Empresa** | `empresa` | Dados da empresa | ✅ Completo |

### 2.2 Status/Estados dos Registros

| Módulo | Campo Status | Valores Possíveis |
|--------|--------------|-------------------|
| Eventos | `status` | `planejado`, `em_andamento`, `concluido`, `cancelado` |
| Colaboradores | `status` | `ativo`, `inativo` |
| Produtos | `status` | `ativo`, `inativo` |
| Cotações | `status` | `aberta`, `fechada`, `aprovada`, `rejeitada` |
| Contas Pagar | `status` | `pendente`, `pago`, `atrasado`, `parcial` |
| Presenças | (implícito) | `presente`, `ausente`, `justificado` |
| Montagens | (implícito) | `pendente`, `iniciada`, `concluida` |
| Usuários | `status` | `1` (ativo), `0` (inativo) |
| API Keys | `status` | `1` (ativo), `0` (inativo) |

### 2.3 Filtros de Data/Período Existentes

| Módulo | Filtros Disponíveis |
|--------|---------------------|
| Eventos | `data_inicio`, `data_fim`, `mes/ano` |
| Contas Pagar | `data_vencimento`, `mes/ano`, `status` |
| RH | `mes/ano`, `data_presenca`, `evento_id` |
| Cotações | `data_criacao`, `status` |
| Dashboard | (nenhum implementado - dados mock) |

---

## 3. PERFIS E PERMISSÕES

### 3.1 Perfis Encontrados no Código

```php
// Arquivo: src/Auth/Rbac.php
Roles: ['guest', 'user', 'manager', 'admin']

// Arquivo: views/dashboard/*.php
Perfis Dashboard: ['administrador', 'produtor', 'estoquista']

// Arquivo: views/layout/sidebar.php
Roles visíveis: ['administrador', 'produtor', 'estoquista']
```

**Nota:** Existe uma discrepância entre roles do RBAC (`admin`) e perfis de dashboard (`administrador`).

### 3.2 Matriz de Permissões por Perfil

| Módulo/Recurso | 👤 Administrador | 👤 Produtor | 👤 Estoquista |
|----------------|------------------|-------------|---------------|
| **Dashboard** | `dashboard.administrador` | `dashboard.produtor` | `dashboard.estoquista` |
| **Usuários** | ✅ Full CRUD | ❌ | ❌ |
| **Clientes** | ✅ Full CRUD | ❌ | ❌ |
| **Fornecedores** | ✅ Full CRUD | ❌ | ❌ |
| **Colaboradores** | ✅ Full CRUD | ✅ Listar | ❌ |
| **Eventos** | ✅ Full CRUD | ✅ Listar | ❌ |
| **Produtos** | ✅ Full CRUD | ✅ Listar | ✅ CRUD + Estoque |
| **Cotações** | ✅ Full CRUD | ❌ | ❌ |
| **Contas Pagar** | ✅ Full CRUD | ❌ | ❌ |
| **RH** | ✅ Full | ❌ | ❌ |
| **Montagem** | ✅ Full | ✅ Registrar | ❌ |
| **Devolução** | ✅ Full | ✅ Registrar | ❌ |
| **Presenças** | ✅ Full | ✅ Registrar | ❌ |
| **Configurações** | ✅ Full | ❌ | ❌ |
| **Permissões** | ✅ Full | ❌ | ❌ |
| **Estoque** | ✅ Full | ✅ Listar | ✅ Full CRUD |
| **API REST** | ✅ Full | ❌ | ❌ |

### 3.3 Ações Exclusivas por Perfil

#### 👤 ADMINISTRADOR
- Gerenciar usuários e perfis
- Configurar empresa e temas
- Gerenciar permissões (RBAC)
- Financeiro completo (cotações, contas, fechamento)
- RH completo (presenças, diárias, pagamentos)
- CRUD completo de todos os módulos
- Gerenciar API keys

#### 👤 PRODUTOR
- Visualizar eventos
- Registrar montagens
- Registrar devoluções
- Registrar presenças de colaboradores
- Visualizar produtos
- Visualizar colaboradores (limitado)

#### 👤 ESTOQUISTA
- CRUD completo de produtos
- Gerenciar estoque (entrada/saída)
- Gerenciar categorias e subcategorias
- Inventário de produtos
- Gerenciar números de série
- **NÃO pode:** acessar financeiro, RH, clientes, fornecedores

---

## 4. DASHBOARDS ATUAIS

### 4.1 Dashboard Administrador

**Arquivo:** `views/dashboard/administrador.php`  
**Controller:** `DashboardController::administrador()`  
**Dados:** Mock + parciais

| Elemento | Status | Fonte de Dados |
|----------|--------|----------------|
| Total Usuários | ✅ Real | `$userRepo->count()` |
| Total Produtos | ❌ Mock | Hardcoded `0` |
| Produção Hoje | ❌ Mock | Hardcoded `0` |
| Faturamento | ❌ Mock | Hardcoded `0.00` |
| Usuários Recentes | ✅ Real | `$userRepo->getRecent(5)` |
| Atividades Recentes | ❌ Mock | Array hardcoded |
| Ações Rápidas | ⚠️ Parcial | Só "Novo Usuário" |

**Problemas Identificados:**
- 75% dos dados são mock/hardcoded
- Não reflete realidade do negócio
- Sem métricas financeiras reais
- Sem alertas de pendências
- Sem visão de eventos, cotações, RH

### 4.2 Dashboard Produtor

**Arquivo:** `views/dashboard/produtor.php`  
**Controller:** `DashboardController::produtor()`  
**Dados:** 100% Mock

| Elemento | Status | Fonte de Dados |
|----------|--------|----------------|
| Produções Hoje | ❌ Mock | Hardcoded `5` |
| Produtos Ativos | ❌ Mock | Hardcoded `12` |
| Concluídas | ❌ Mock | Hardcoded `45` |
| Em Andamento | ❌ Mock | Hardcoded `3` |
| Produções Recentes | ❌ Mock | Array hardcoded |
| Produtos em Estoque | ❌ Mock | Array hardcoded |
| Ações Rápidas | ⚠️ Parcial | Links podem não existir |

**Problemas Identificados:**
- 100% dos dados são fictícios
- Sem integração com módulo de eventos
- Sem visão de montagens do produtor
- Sem alertas de eventos próximos
- Sem métricas de presenças registradas

### 4.3 Dashboard Estoquista

**Arquivo:** `views/dashboard/estoquista.php`  
**Controller:** `DashboardController::estoquista()`  
**Dados:** 100% Mock

| Elemento | Status | Fonte de Dados |
|----------|--------|----------------|
| Total Produtos | ❌ Mock | Hardcoded `25` |
| Itens em Estoque | ❌ Mock | Hardcoded `1250` |
| Estoque Baixo | ❌ Mock | Hardcoded `3` |
| Movimentações Hoje | ❌ Mock | Hardcoded `12` |
| Movimentações Recentes | ❌ Mock | Array hardcoded |
| Produtos Baixo Estoque | ❌ Mock | Array hardcoded |
| Valor Total Estoque | ❌ Mock | Hardcoded `45680.50` |
| Ações Rápidas | ⚠️ Parcial | Links podem não existir |

**Problemas Identificados:**
- 100% dos dados são fictícios
- Sem integração real com estoque
- Sem alertas de produtos zerados
- Sem visão de categorias mais usadas
- Sem histórico de movimentações

---

## 5. ANÁLISE POR PERFIL

### 5.1 👤 ADMINISTRADOR

#### 5.1.1 O que o Administrador Precisa Saber ao Abrir o Sistema

1. **Qual o status financeiro da empresa hoje?**
   - Contas a pagar vencidas
   - Contas a pagar pendentes do mês
   - Cotações aguardando aprovação
   - Total pago no mês

2. **Quais eventos estão acontecendo/ocorrendo?**
   - Eventos hoje
   - Eventos esta semana
   - Eventos atrasados (sem montagem/devolução)

3. **Quais alertas críticos existem?**
   - Colaboradores com presenças não registradas
   - Contas vencidas não pagas
   - Eventos sem fechamento

4. **Qual a operação do dia?**
   - Montagens programadas
   - Devoluções pendentes
   - Colaboradores alocados hoje

#### 5.1.2 Métricas Diárias do Administrador

| Métrica | Tabela | Cálculo | Relevância |
|---------|--------|---------|------------|
| Eventos do dia | `eventos` | COUNT WHERE data_inicio = HOJE | 🔴 Essencial |
| Eventos da semana | `eventos` | COUNT WHERE data_inicio BETWEEN HOJE E HOJE+7 | 🟡 Importante |
| Contas vencidas | `contas_pagar` | COUNT WHERE data_vencimento < HOJE AND status != 'pago' | 🔴 Essencial |
| Contas pendentes | `contas_pagar` | COUNT WHERE status = 'pendente' | 🟡 Importante |
| Cotações abertas | `item_cotacoes` | COUNT WHERE status = 'aberta' | 🟡 Importante |
| Colaboradores alocados hoje | `evento_colaboradores` | COUNT WHERE evento_id IN (eventos hoje) | 🟡 Importante |
| Montagens pendentes | `montagens` | COUNT WHERE status = 'pendente' | 🟡 Importante |
| Devoluções pendentes | `montagens` | COUNT WHERE status = 'iniciada' | 🟡 Importante |
| Presenças não registradas | `evento_colaborador_presencas` | COUNT WHERE data = HOJE AND (NULL ou ausente) | 🟢 Complementar |

#### 5.1.3 Ações Mais Frequentes do Administrador

1. Criar/editar eventos
2. Aprovar cotações
3. Registrar pagamentos
4. Ver presenças de colaboradores
5. Gerenciar usuários
6. Configurar empresa/permissões

#### 5.1.4 Alertas Críticos para Administrador

| Alerta | Condição | Impacto |
|--------|----------|---------|
| Eventos sem montagem | `montagens` não existe para evento com data <= hoje | Equipamentos não instalados |
| Contas vencidas | `data_vencimento` < hoje AND `status` != 'pago' | Multas, juros |
| Cotações antigas | `created_at` < 7 dias AND `status` = 'aberta' | Perda de negócio |
| Colaboradores sem presença | `evento_colaborador_presencas` ausente | Pagamento indevido |
| Eventos sem fotos | `evento_fotos` vazio para evento concluído | Falta documentação |

---

### 5.2 👤 PRODUTOR

#### 5.2.1 O que o Produtor Precisa Saber ao Abrir o Sistema

1. **Quais eventos estão ativos?**
   - Eventos onde ele está alocado
   - Eventos hoje
   - Eventos da semana

2. **O que precisa fazer hoje?**
   - Montagens programadas
   - Devoluções agendadas
   - Presenças a registrar

3. **Quais colaboradores estão disponíveis?**
   - Colaboradores ativos
   - Colaboradores alocados no evento

4. **Qual o status das operações?**
   - Montagens concluídas vs pendentes
   - Devoluções realizadas
   - Presenças registradas

#### 5.2.2 Métricas Diárias do Produtor

| Métrica | Tabela | Cálculo | Relevância |
|---------|--------|---------|------------|
| Eventos alocados | `eventos` + join | COUNT WHERE produtor relacionado | 🔴 Essencial |
| Eventos hoje | `eventos` | COUNT WHERE data_inicio = HOJE | 🔴 Essencial |
| Montagens pendentes | `montagens` | COUNT WHERE status = 'pendente' | 🔴 Essencial |
| Devoluções pendentes | `montagens` | COUNT WHERE status = 'iniciada' | 🔴 Essencial |
| Presenças hoje | `evento_colaborador_presencas` | COUNT WHERE data = HOJE | 🟡 Importante |
| Colaboradores no evento | `evento_colaboradores` | COUNT WHERE evento_id = X | 🟡 Importante |

#### 5.2.3 Ações Mais Frequentes do Produtor

1. Registrar montagem
2. Registrar devolução
3. Registrar presença de colaborador
4. Visualizar eventos
5. Visualizar colaboradores do evento

#### 5.2.4 Alertas para Produtor

| Alerta | Condição | Impacto |
|--------|----------|---------|
| Montagem atrasada | `montagens` pendente para evento que já começou | Evento sem equipamento |
| Devolução atrasada | `montagens` iniciada para evento já concluído | Equipamentos fora do estoque |
| Presenças não registradas | Colaboradores sem presença no dia | Pagamento incorreto |

---

### 5.3 👤 ESTOQUISTA

#### 5.3.1 O que o Estoquista Precisa Saber ao Abrir o Sistema

1. **Qual a situação do estoque agora?**
   - Produtos com estoque baixo
   - Produtos zerados
   - Total de itens em estoque

2. **O que entrou/saiu hoje?**
   - Movimentações do dia
   - Produtos mais movimentados

3. **Quais pendências existem?**
   - Produtos sem categoria
   - Categorias sem uso
   - Inventários pendentes

4. **O que precisa de atenção?**
   - Produtos com estoque < mínimo
   - Produtos que não foram movimentados (estoque parado)
   - Salas com muitos/poucos produtos

#### 5.3.2 Métricas Diárias do Estoquista

| Métrica | Tabela | Cálculo | Relevância |
|---------|--------|---------|------------|
| Produtos estoque baixo | `produtos_evento` | COUNT WHERE quantidade < estoque_minimo | 🔴 Essencial |
| Produtos zerados | `produtos_evento` | COUNT WHERE quantidade = 0 | 🔴 Essencial |
| Total itens estoque | `produtos_evento` | SUM(quantidade) | 🟡 Importante |
| Movimentações hoje | (sem tabela) | COUNT (precisa criar tabela) | 🟡 Importante |
| Produtos sem categoria | `produtos_evento` | COUNT WHERE categoria IS NULL | 🟢 Complementar |
| Categorias sem uso | `categorias` | COUNT WHERE não tem produtos | 🟢 Complementar |

#### 5.3.3 Ações Mais Frequentes do Estoquista

1. Registrar entrada de produto
2. Registrar saída de produto
3. Ajustar estoque
4. Gerenciar categorias
5. Inventário
6. Gerenciar números de série

#### 5.3.4 Alertas para Estoquista

| Alerta | Condição | Impacto |
|--------|----------|---------|
| Estoque zerado | `quantidade` = 0 | Não pode alocar para evento |
| Estoque abaixo mínimo | `quantidade` < `estoque_minimo` | Risco de falta |
| Produto sem uso | Sem movimentação em 30 dias | Estoque parado |
| Categoria vazia | Sem produtos vinculados | Organização ruim |

---

## 6. DADOS DISPONÍVEIS VS AUSENTES

### 6.1 Administrador

| Dashboard | Elemento | Status | Observação |
|-----------|----------|--------|------------|
| Admin | Eventos hoje | ✅ | `eventos`, `data_inicio` = CURDATE() |
| Admin | Eventos da semana | ✅ | `eventos`, BETWEEN CURDATE() AND CURDATE()+7 |
| Admin | Contas vencidas | ✅ | `contas_pagar`, `data_vencimento` < CURDATE() |
| Admin | Contas pendentes | ✅ | `contas_pagar`, `status` = 'pendente' |
| Admin | Contas pagas mês | ✅ | `contas_pagar`, `status` = 'pago' + MONTH() |
| Admin | Total pago mês | ✅ | `contas_pagar`, SUM(`valor_pago`) WHERE `status`='pago' |
| Admin | Cotações abertas | ✅ | `item_cotacoes`, `status` = 'aberta' |
| Admin | Cotações aguardando aprovação | ⚠️ | Existe `status` mas sem flag específica |
| Admin | Montagens pendentes | ✅ | `montagens`, `status` = 'pendente' |
| Admin | Devoluções pendentes | ✅ | `montagens`, `status` = 'iniciada' |
| Admin | Colaboradores alocados hoje | ✅ | JOIN `eventos` + `evento_colaboradores` |
| Admin | Presenças não registradas | ⚠️ | Tabela existe mas sem flag explícita |
| Admin | Faturamento do mês | ❌ | Não existe tabela de vendas/faturamento |
| Admin | Resultado fechamento | ⚠️ | `evento_fotos` existe mas sem cálculo financeiro |
| Admin | Pedidos atrasados | ❌ | Não existe conceito de "pedidos" |
| Admin | Funcionários com ponto pendente | ⚠️ | `evento_colaborador_presencas` existe mas sem lógica |
| Admin | Total funcionários ativos | ✅ | `colaboradores`, `status` = 'ativo' |
| Admin | Faltas não justificadas | ❌ | Campo não existe |
| Admin | Fechamentos RH pendentes | ❌ | Conceito não implementado |
| Admin | Ordens produção abertas | ❌ | Não existe módulo de produção/ordens |
| Admin | Insumos mais consumidos | ❌ | Não existe controle de insumos |

### 6.2 Produtor

| Dashboard | Elemento | Status | Observação |
|-----------|----------|--------|------------|
| Produtor | Eventos alocados | ⚠️ | `eventos` existe mas sem relação direta com produtor |
| Produtor | Eventos hoje | ✅ | `eventos`, `data_inicio` = CURDATE() |
| Produtor | Montagens pendentes | ✅ | `montagens`, `status` = 'pendente' |
| Produtor | Devoluções pendentes | ✅ | `montagens`, `status` = 'iniciada' |
| Produtor | Montagens concluídas hoje | ✅ | `montagens`, `status` = 'concluida' + CURDATE() |
| Produtor | Presenças hoje | ✅ | `evento_colaborador_presencas`, `data` = CURDATE() |
| Produtor | Colaboradores no evento | ✅ | `evento_colaboradores`, `evento_id` = X |
| Produtor | Últimas produções | ❌ | Não existe módulo de produção |
| Produtor | Produtividade período | ❌ | Sem dados de produção |
| Produtor | Insumos críticos | ❌ | Não existe controle de insumos |
| Produtor | Estoque insumos | ❌ | Não existe tabela de insumos |
| Produtor | Ordens urgentes | ❌ | Não existe conceito de ordens |

### 6.3 Estoquista

| Dashboard | Elemento | Status | Observação |
|-----------|----------|--------|------------|
| Estoquista | Produtos estoque baixo | ✅ | `produtos_evento`, `quantidade` < `estoque_minimo` |
| Estoquista | Produtos zerados | ✅ | `produtos_evento`, `quantidade` = 0 |
| Estoquista | Total itens estoque | ✅ | `produtos_evento`, SUM(`quantidade`) |
| Estoquista | Valor total estoque | ⚠️ | `produtos_evento` tem `valor_unitario` mas não calculado |
| Estoquista | Movimentações recentes | ❌ | Não existe tabela de movimentações |
| Estoquista | Últimas entradas | ❌ | Sem tabela de movimentações |
| Estoquista | Últimas saídas | ❌ | Sem tabela de movimentações |
| Estoquista | Itens mais movimentados | ❌ | Sem histórico de movimentações |
| Estoquista | Itens sem movimentação | ❌ | Sem histórico |
| Estoquista | Solicitações insumo | ❌ | Não existe módulo de solicitações |
| Estoquista | Notas fiscais pendentes | ❌ | Não existe módulo de NF |
| Estoquista | Divergências inventário | ⚠️ | Conceito existe mas sem tabela de divergências |
| Estoquista | Itens validade próxima | ❌ | Campo `dt_validade` não existe |
| Estoquista | Quantidade negativa | ✅ | `produtos_evento`, `quantidade` < 0 |
| Estoquista | Estoque mínimo atingido | ✅ | `produtos_evento`, `quantidade` <= `estoque_minimo` |

---

## 7. QUERIES BASE

### 7.1 Dashboard Administrador

```sql
-- EVENTOS DO DIA
SELECT COUNT(*) as eventos_hoje
FROM eventos
WHERE DATE(data_inicio) = CURDATE()
AND status != 'cancelado';

-- EVENTOS DA SEMANA
SELECT COUNT(*) as eventos_semana
FROM eventos
WHERE DATE(data_inicio) BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
AND status != 'cancelado';

-- CONTAS VENCIDAS
SELECT COUNT(*) as contas_vencidas
FROM contas_pagar
WHERE data_vencimento < CURDATE()
AND status != 'pago';

-- CONTAS PENDENTES DO MÊS
SELECT COUNT(*) as contas_pendentes
FROM contas_pagar
WHERE MONTH(data_vencimento) = MONTH(CURDATE())
AND YEAR(data_vencimento) = YEAR(CURDATE())
AND status = 'pendente';

-- TOTAL PAGO NO MÊS
SELECT COALESCE(SUM(valor_pago), 0) as total_pago_mes
FROM contas_pagar
WHERE MONTH(data_vencimento) = MONTH(CURDATE())
AND YEAR(data_vencimento) = YEAR(CURDATE())
AND status = 'pago';

-- COTAÇÕES ABERTAS
SELECT COUNT(*) as cotacoes_abertas
FROM item_cotacoes
WHERE status = 'aberta';

-- MONTAGENS PENDENTES
SELECT COUNT(*) as montagens_pendentes
FROM montagens
WHERE status = 'pendente';

-- DEVOLUÇÕES PENDENTES
SELECT COUNT(*) as devolucoes_pendentes
FROM montagens
WHERE status = 'iniciada';

-- COLABORADORES ALOCADOS HOJE
SELECT COUNT(DISTINCT ec.colaborador_id) as colaboradores_alocados
FROM evento_colaboradores ec
INNER JOIN eventos e ON ec.evento_id = e.id
WHERE DATE(e.data_inicio) = CURDATE()
AND ec.status = 1;

-- COLABORADORES ATIVOS
SELECT COUNT(*) as colaboradores_ativos
FROM colaboradores
WHERE status = 'ativo';

-- CONTAS POR STATUS (gráfico)
SELECT status, COUNT(*) as total
FROM contas_pagar
WHERE MONTH(data_vencimento) = MONTH(CURDATE())
AND YEAR(data_vencimento) = YEAR(CURDATE())
GROUP BY status;

-- TOP 5 EVENTOS PRÓXIMOS
SELECT id, nome, data_inicio, data_fim, status
FROM eventos
WHERE data_inicio >= CURDATE()
AND status != 'cancelado'
ORDER BY data_inicio ASC
LIMIT 5;

-- TOP 5 CONTAS PRÓXIMO VENCIMENTO
SELECT id, descricao, valor, data_vencimento, status
FROM contas_pagar
WHERE status != 'pago'
AND data_vencimento >= CURDATE()
ORDER BY data_vencimento ASC
LIMIT 5;

-- ÚLTIMAS 5 ATIVIDADES (cotações)
SELECT 'Cotação criada' as tipo, descricao, created_at
FROM item_cotacoes
ORDER BY created_at DESC
LIMIT 5;
```

### 7.2 Dashboard Produtor

```sql
-- EVENTOS DO DIA
SELECT e.id, e.nome, e.data_inicio, e.data_fim, e.status
FROM eventos e
WHERE DATE(e.data_inicio) = CURDATE()
AND e.status != 'cancelado'
ORDER BY e.data_inicio;

-- EVENTOS DA SEMANA
SELECT e.id, e.nome, e.data_inicio, e.data_fim, e.status
FROM eventos e
WHERE DATE(e.data_inicio) BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
AND e.status != 'cancelado'
ORDER BY e.data_inicio;

-- MONTAGENS PENDENTES
SELECT m.id, m.evento_id, e.nome as evento_nome, m.status
FROM montagens m
INNER JOIN eventos e ON m.evento_id = e.id
WHERE m.status = 'pendente'
ORDER BY e.data_inicio ASC;

-- DEVOLUÇÕES PENDENTES
SELECT m.id, m.evento_id, e.nome as evento_nome, m.status
FROM montagens m
INNER JOIN eventos e ON m.evento_id = e.id
WHERE m.status = 'iniciada'
ORDER BY e.data_fim DESC;

-- COLABORADORES NO EVENTO (exemplo evento_id = 1)
SELECT ec.id, c.nome, c.profissao, ec.valor_diaria
FROM evento_colaboradores ec
INNER JOIN colaboradores c ON ec.colaborador_id = c.id
WHERE ec.evento_id = 1
AND ec.status = 1;

-- PRESENÇAS HOJE (exemplo evento_id = 1)
SELECT COUNT(*) as presencas_hoje
FROM evento_colaborador_presencas
WHERE data = CURDATE()
AND evento_colaborador_id IN (
    SELECT id FROM evento_colaboradores WHERE evento_id = 1
);

-- MONTAGENS CONCLUÍDAS HOJE
SELECT COUNT(*) as montagens_concluidas_hoje
FROM montagens
WHERE status = 'concluida'
AND DATE(updated_at) = CURDATE();
```

### 7.3 Dashboard Estoquista

```sql
-- PRODUTOS COM ESTOQUE BAIXO
SELECT id, nome, quantidade, estoque_minimo
FROM produtos_evento
WHERE quantidade < estoque_minimo
AND status = 1
ORDER BY (quantidade / estoque_minimo) ASC;

-- PRODUTOS ZERADOS
SELECT id, nome, quantidade
FROM produtos_evento
WHERE quantidade = 0
AND status = 1;

-- TOTAL DE ITENS EM ESTOQUE
SELECT SUM(quantidade) as total_itens
FROM produtos_evento
WHERE status = 1;

-- VALOR TOTAL DO ESTOQUE
SELECT SUM(quantidade * valor_unitario) as valor_total
FROM produtos_evento
WHERE status = 1;

-- PRODUTOS COM QUANTIDADE NEGATIVA (erro)
SELECT id, nome, quantidade
FROM produtos_evento
WHERE quantidade < 0
AND status = 1;

-- TOP 10 PRODUTOS COM MENOR ESTOQUE
SELECT id, nome, quantidade, estoque_minimo
FROM produtos_evento
WHERE status = 1
ORDER BY quantidade ASC
LIMIT 10;

-- PRODUTOS POR CATEGORIA
SELECT c.nome as categoria, COUNT(p.id) as total_produtos, SUM(p.quantidade) as total_itens
FROM categorias c
LEFT JOIN produtos_evento p ON p.categoria_id = c.id
WHERE p.status = 1 OR p.status IS NULL
GROUP BY c.nome
ORDER BY total_produtos DESC;

-- SALAS COM PRODUTOS
SELECT s.nome as sala, COUNT(p.id) as total_produtos
FROM salas s
LEFT JOIN produtos_evento p ON p.sala_id = s.id
WHERE p.status = 1 OR p.status IS NULL
GROUP BY s.nome;

-- ÚLTIMOS PRODUTOS CADASTRADOS
SELECT id, nome, quantidade, created_at
FROM produtos_evento
WHERE status = 1
ORDER BY created_at DESC
LIMIT 10;
```

---

## 8. PROPOSTA DE LAYOUT

### 8.1 Administrador

```
┌─────────────────┬─────────────────┬─────────────────┬─────────────────┐
│ 📅 Eventos Hoje │ ⚠️ Contas Venc. │ 💰 Pago no Mês  │ 📊 Cotações Ab. │
│      3          │       2         │  R$ 15.450,00   │       5         │
├─────────────────┴─────────────────┼─────────────────┴─────────────────┤
│ 📈 Contas por Status (Gráfico)    │ ⚡ Alertas Críticos               │
│ - Pendentes: 8                    │ - 2 contas vencidas               │
│ - Pagas: 12                       │ - 1 montagem atrasada             │
│ - Atrasadas: 2                    │ - 3 cotações > 7 dias             │
├───────────────────────────────────┼───────────────────────────────────┤
│ 📅 Próximos Eventos (5)           │ 🏗️ Montagens/Devoluções           │
│ 1. Evento A - 28/04               │ - Pendentes: 3                    │
│ 2. Evento B - 29/04               │ - Em andamento: 2                 │
│ 3. Evento C - 02/05               │ - Concluídas hoje: 1              │
├───────────────────────────────────┴───────────────────────────────────┤
│ 👥 Colaboradores Ativos: 25 | Alocados Hoje: 8 | Presenças Hoje: 6   │
└───────────────────────────────────────────────────────────────────────┘
```

### 8.2 Produtor

```
┌─────────────────┬─────────────────┬─────────────────┬─────────────────┐
│ 📅 Eventos Hoje │ 🏗️ Mont. Pend. │ 🔄 Devol. Pend. │ ✅ Presenças    │
│      2          │       3         │       2         │      8          │
├─────────────────┴─────────────────┼─────────────────┴─────────────────┤
│ 📋 Eventos da Semana              │ ⚡ Alertas                         │
│ 1. Evento A - 28/04 - Em andamento│ - 1 montagem atrasada             │
│ 2. Evento B - 29/04 - Planejado   │ - 2 colaboradores sem presença    │
│ 3. Evento C - 02/05 - Planejado   │ - 1 devolução pendente            │
├───────────────────────────────────┼───────────────────────────────────┤
│ 🏗️ Montagens Pendentes            │ 👥 Colaboradores no Evento         │
│ 1. Evento A - Pendente            │ Evento A: 5 colaboradores         │
│ 2. Evento B - Pendente            │ Evento B: 3 colaboradores         │
├───────────────────────────────────┴───────────────────────────────────┤
│ Ações Rápidas: [Registrar Montagem] [Registrar Devolução] [Presenças] │
└───────────────────────────────────────────────────────────────────────┘
```

### 8.3 Estoquista

```
┌─────────────────┬─────────────────┬─────────────────┬─────────────────┐
│ 📦 Total Produtos│ 📊 Total Itens  │ ⚠️ Estoque Baixo│ 💰 Valor Estoque│
│       150       │     2.450       │       8         │ R$ 45.680,00   │
├─────────────────┴─────────────────┼─────────────────┴─────────────────┤
│ 🚨 Alertas Estoque Baixo          │ 📊 Resumo do Estoque              │
│ ⚠️ Produto A: 2 un. (mín: 10)     │ - Em dia: 120 (80%)              │
│ ⚠️ Produto B: 0 un. (mín: 5)      │ - Médio: 20 (13%)                │
│ ⚠️ Produto C: 3 un. (mín: 15)     │ - Baixo: 10 (7%)                 │
├───────────────────────────────────┼───────────────────────────────────┤
│ 📦 Produtos por Categoria         │ 📋 Últimos Produtos Cadastrados   │
│ 1. Sonorização: 45 produtos       │ 1. Microfone - 15 un.            │
│ 2. Iluminação: 38 produtos        │ 2. Caixa de som - 8 un.          │
│ 3. Cabos: 32 produtos             │ 3. Mesa de som - 3 un.           │
├───────────────────────────────────┴───────────────────────────────────┤
│ Ações Rápidas: [Entrada] [Saída] [Inventário] [Ver Produtos]         │
└───────────────────────────────────────────────────────────────────────┘
```

---

## 9. PLANO DE IMPLEMENTAÇÃO

### 🔴 FASE 1 - IMPLEMENTAR PRIMEIRO (Alto Valor, Dados Disponíveis)

**Objetivo:** Substituir dados mock por dados reais do banco

| Perfil | Elemento | Complexidade | Impacto | Query |
|--------|----------|--------------|---------|-------|
| Admin | Eventos hoje | 🟢 Baixa | 🔴 Alto | `eventos`, CURDATE() |
| Admin | Contas vencidas | 🟢 Baixa | 🔴 Alto | `contas_pagar`, vencidas |
| Admin | Contas pendentes mês | 🟢 Baixa | 🟡 Médio | `contas_pagar`, mês atual |
| Admin | Total pago mês | 🟢 Baixa | 🔴 Alto | SUM(valor_pago) |
| Admin | Cotações abertas | 🟢 Baixa | 🟡 Médio | `item_cotacoes`, status |
| Admin | Colaboradores ativos | 🟢 Baixa | 🟡 Médio | `colaboradores`, count |
| Admin | Próximos 5 eventos | 🟢 Baixa | 🟡 Médio | ORDER BY data_inicio |
| Estoquista | Produtos estoque baixo | 🟢 Baixa | 🔴 Alto | quantidade < mínimo |
| Estoquista | Produtos zerados | 🟢 Baixa | 🔴 Alto | quantidade = 0 |
| Estoquista | Total itens estoque | 🟢 Baixa | 🟡 Médio | SUM(quantidade) |
| Estoquista | Valor total estoque | 🟢 Baixa | 🟡 Médio | SUM(quantidade * valor) |
| Estoquista | Produtos por categoria | 🟢 Baixa | 🟢 Baixo | GROUP BY categoria |
| Produtor | Eventos hoje | 🟢 Baixa | 🔴 Alto | `eventos`, CURDATE() |
| Produtor | Montagens pendentes | 🟢 Baixa | 🔴 Alto | `montagens`, pendente |
| Produtor | Devoluções pendentes | 🟢 Baixa | 🔴 Alto | `montagens`, iniciada |

**Estimativa:** 15 elementos | Complexidade: Baixa | Impacto: Alto  
**Tempo estimado:** 4-6 horas

---

### 🟡 FASE 2 - IMPLEMENTAR EM SEGUIDA (Importantes, Ajuste Pequeno)

**Objetivo:** Adicionar métricas cruzadas e alertas inteligentes

| Perfil | Elemento | Complexidade | Impacto | Observação |
|--------|----------|--------------|---------|------------|
| Admin | Montagens pendentes | 🟡 Média | 🟡 Médio | JOIN com eventos |
| Admin | Devoluções pendentes | 🟡 Média | 🟡 Médio | JOIN com eventos |
| Admin | Colaboradores alocados hoje | 🟡 Média | 🟡 Médio | JOIN eventos + colaboradores |
| Admin | Contas por status (gráfico) | 🟡 Média | 🟡 Médio | GROUP BY + porcentagem |
| Admin | Últimas atividades | 🟡 Média | 🟢 Baixo | Unir cotações + contas |
| Admin | Top contas próximo venc. | 🟡 Média | 🟡 Médio | ORDER BY data_vencimento |
| Estoquista | Produtos por sala | 🟡 Média | 🟢 Baixo | JOIN salas |
| Estoquista | Últimos produtos cadastrados | 🟡 Média | 🟢 Baixo | ORDER BY created_at |
| Estoquista | Produtos com qtde negativa | 🟢 Baixa | 🔴 Alto | quantidade < 0 |
| Produtor | Eventos da semana | 🟡 Média | 🟡 Médio | BETWEEN datas |
| Produtor | Colaboradores no evento | 🟡 Média | 🟡 Médio | JOIN evento_colaboradores |
| Produtor | Presenças hoje | 🟡 Média | 🟡 Médio | JOIN presenças |
| Produtor | Montagens concluídas hoje | 🟡 Média | 🟢 Baixo | updated_at = hoje |

**Estimativa:** 13 elementos | Complexidade: Média | Impacto: Médio  
**Tempo estimado:** 8-10 horas

---

### 🟢 FASE 3 - BACKLOG (Complementares ou Precisam Novo Desenvolvimento)

**Objetivo:** Funcionalidades avançadas que exigem novas tabelas/lógica

| Perfil | Elemento | Complexidade | Impacto | Observação |
|--------|----------|--------------|---------|------------|
| Admin | Faturamento do mês | 🔴 Alta | 🔴 Alto | **Criar tabela vendas** |
| Admin | Resultado fechamento | 🔴 Alta | 🟡 Médio | **Criar lógica de fechamento** |
| Admin | Presenças não registradas | 🔴 Alta | 🟡 Médio | **Criar lógica de detecção** |
| Admin | Faltas não justificadas | 🔴 Alta | 🟢 Baixo | **Criar campo motivo_falta** |
| Admin | Pedidos atrasados | ❌ N/A | ❌ N/A | Não existe módulo pedidos |
| Admin | Ordens produção abertas | ❌ N/A | ❌ N/A | Não existe módulo produção |
| Admin | Insumos mais consumidos | ❌ N/A | ❌ N/A | Não existe controle insumos |
| Estoquista | Histórico movimentações | 🔴 Alta | 🔴 Alto | **Criar tabela movimentacoes_estoque** |
| Estoquista | Itens mais movimentados | 🔴 Alta | 🟡 Médio | Depende de histórico |
| Estoquista | Itens sem movimentação | 🔴 Alta | 🟢 Baixo | Depende de histórico |
| Estoquista | Validade próxima | ❌ N/A | 🟢 Baixo | **Criar campo dt_validade** |
| Estoquista | Divergências inventário | 🟡 Média | 🟡 Médio | **Criar tabela divergencias** |
| Estoquista | Solicitações insumo | ❌ N/A | ❌ N/A | **Criar módulo solicitações** |
| Estoquista | Notas fiscais pendentes | ❌ N/A | ❌ N/A | **Criar módulo NF** |
| Produtor | Estoque insumos | ❌ N/A | ❌ N/A | Não existe tabela insumos |
| Produtor | Últimas produções | ❌ N/A | ❌ N/A | Não existe módulo produção |
| Produtor | Produtividade período | ❌ N/A | ❌ N/A | Depende de módulo produção |
| Produtor | Insumos críticos | ❌ N/A | ❌ N/A | Não existe controle insumos |

**Estimativa:** 18 elementos | Complexidade: Alta | Impacto: Variado  
**Tempo estimado:** 40-60 horas (depende de novos módulos)

---

## 10. RESUMO EXECUTIVO

### 10.1 Estado Atual dos Dashboards

| Dashboard | Dados Reais | Dados Mock | % Real | Prioridade |
|-----------|-------------|------------|--------|------------|
| Administrador | 25% | 75% | 🔴 Crítico | 🔴 Imediata |
| Produtor | 0% | 100% | 🔴 Crítico | 🔴 Imediata |
| Estoquista | 0% | 100% | 🔴 Crítico | 🔴 Imediata |

### 10.2 Resumo por Fase

| Fase | Elementos | Complexidade | Tempo Estimado | Impacto |
|------|-----------|--------------|----------------|---------|
| 🔴 Fase 1 | 15 | Baixa | 4-6 horas | 🔴 Alto |
| 🟡 Fase 2 | 13 | Média | 8-10 horas | 🟡 Médio |
| 🟢 Fase 3 | 18 | Alta | 40-60 horas | 🟢 Variado |
| **TOTAL** | **46** | - | **52-76 horas** | - |

### 10.3 Recomendações

1. **IMEDIATO:** Implementar Fase 1 (substituir dados mock por queries reais)
   - Todas as queries já foram escritas
   - Dados existem nas tabelas atuais
   - Impacto imediato na experiência do usuário

2. **CURTO PRAZO (1-2 semanas):** Implementar Fase 2
   - Métricas cruzadas e alertas
   - Gráficos simples (contas por status)
   - Listas ordenadas (próximos eventos, contas vencendo)

3. **MÉDIO PRAZO (1-2 meses):** Avaliar necessidade da Fase 3
   - Requer novas tabelas (vendas, movimentações, insumos)
   - Depende de decisões de negócio
   - Alto esforço de desenvolvimento

4. **MONITORAMENTO:** Após implementação da Fase 1
   - Medir uso dos dashboards
   - Coletar feedback dos usuários
   - Ajustar métricas baseado em uso real

### 10.4 Próximos Passos

1. ✅ Análise completa do sistema (FEITO)
2. ✅ Mapeamento de dados disponíveis (FEITO)
3. ✅ Queries base escritas (FEITO)
4. ⏳ **PRÓXIMO:** Implementar Fase 1 (dados reais)
5. ⏳ Validar com usuários
6. ⏳ Iterar baseado em feedback

---

**FIM DA ANÁLISE**

*Documento gerado em 27/04/2026 - Nenhuma alteração realizada no sistema*
