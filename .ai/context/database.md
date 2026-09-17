# Banco de Dados
## Tabelas principais
- `clientes`: dados de clientes ISP
- `colaboradores`: funcionários/produtores
- `categorias`, `subcategorias`: classificação de produtos
- `eventos`: eventos registrados
- `contas_pagar`: contas a pagar
- `usuarios`: autenticação do sistema
- `salas`, `produtos_evento`: salas e produtos de eventos
- `fornecedores`: cadastro de fornecedores (sublocadores)
- `sublocacao_itens`: catálogo de itens que cada sublocador oferece
- `produto_evento_sublocacao`: vínculo entre produtos_evento e sublocadores (quantidade, valor, total por sublocador)

## Observações
- Engine: InnoDB
- Charset: utf8mb4
- Banco: sisloc_newsisloc (produção em sisloc.online)
