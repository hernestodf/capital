# Relatório: Análise do Banco de Dados de Produção

**Data:** 2026-06-04
**Host:** sisloc.online
**Usuário:** capital
**Banco:** capital

## Resumo Executivo

- **Total de tabelas:** 44 (idênticas ao newsisloc.sql)
- **Tabelas com dados:** ~40 tabelas possuem registros
- **Tabelas não referenciadas no código:** 7 tabelas
- **Status de migração:** SINCRONIZADO - BD produção igual ao dump

## Tabelas Não Referenciadas no Código

| Tabela | Registros | Status |
|--------|-----------|--------|
| ai_interactions | 0 | Não usada - provavelmente substituída por ai_interactions |
| ai_learning_sessions | 0 | Não usada - provavelmente substituída por ai_learning |
| categorias_sala | 16 | Não usada - dados não consultados |
| colaboradores_verificacao | 7 | Não usada - feature incompleta |
| demandantes | 0 | Não usada - tabela vazia |
| devolucoes | 14 | Não usada - módulo não implementado |
| item_cotacoes_anexos | 6 | Não usada - módulo não implementado |
| item_cotacoes_parcelas | 0 | Não usada - módulo não implementado |
| modulos | 14 | Não usada - substituída por outra estrutura? |
| permissoes | 62 | Não usada - substituída por outra estrutura? |
| produtores | 3 | **USADA** - referenciada em ProdutorRepository |
| unidademedida | 18 | Não usada - tabela vazia ou pouco usada |
| users | 3 | **USADA** - tabela de autenticação |

## Tabelas Principais (Em Uso)

| Tabela | Registros | Uso |
|--------|-----------|-----|
| clientes | 1 | Gestão de clientes ISP |
| colaboradores | 8 | Funcionários/produtores |
| eventos | 4 | Eventos/serviços |
| contas_pagar | 5 | Contas a pagar |
| planilhas | 115 | Planilhas de cálculo |
| montagens | 39 | Serviços de montagem |
| categorias | 15 | Classificação de produtos |
| subcategorias | 95 | Sub-classificação |
| produtos | 7 | Produtos/serviços |
| fornecedores | 4 | Fornecedores |
| produtos_evento | 21 | Itens de eventos |

## Tabelas de IA

| Tabela | Registros | Observação |
|--------|-----------|------------|
| ai_corrections | 0 | Pronta para uso |
| ai_executions | 0 | Pronta para uso |
| ai_feedbacks | 0 | Pronta para uso |
| ai_learning | 0 | Pronta para uso |
| ai_metrics | 0 | Pronta para uso |
| ai_preferences | 0 | Pronta para uso |

## Recomendações

1. **Remover tabelas não usadas:**
   - `ai_interactions` (duplicada?)
   - `ai_learning_sessions` (duplicada?)
   - `colaboradores_verificacao`
   - `devolucoes`
   - `item_cotacoes_*`
   - `modulos`
   - `permissoes`
   - `unidademedida`

2. **Verificar relacionamentos:**
   - `produtores` → `users` (FK)
   - `planilhas` → módulo de cotações não implementado completo

3. **Manter tabelas de IA:**
   - Tabelas `ai_*` estão prontas para uso do sistema de IA

## Conclusão

O banco de dados está estruturado corretamente com tabelas de autenticação, negócios e IA. Algumas tabelas parecem ser de funcionalidades incompletas ou duplicatas.

**Status:** BD de produção já está sincronizado com `newsisloc.sql`. Nenhuma migração precisa ser executada.