# improvements
## Módulo Sublocação (2026-06-09)
- Implementado módulo completo de sublocação
- Criadas tabelas `sublocacao_itens` (catálogo de itens por sublocador) e `produto_evento_sublocacao` (vínculo item-evento x sublocador)
- 7 novos arquivos: 2 repositórios, 2 serviços, 2 controllers (API + CRUD), 1 migration
- 9 arquivos modificados: views (sidebar, fornecedor index/edit, evento salas-produtos), rotas, repositórios, controller e JS
- API RESTful com 6 endpoints: listar itens do sublocador, listar vínculos, vincular, desvincular, gerar contas a pagar, listar fornecedores ativos
- Fluxo completo: cadastro de itens no sublocador → vincular sublocador a item do evento no modal → gerar contas_pagar com vencimento em data_fim + 30 dias
- Contas geradas com tipo='fornecedor', id_fornecedor, evento_id, descrição detalhada
- Sublocação movida para aba própria (pane 4) em vez de ficar em Salas e Produtos
- Nova partial `edit-sublocacao.php` com lista de itens + modal vínculo + botão Gerar Contas
