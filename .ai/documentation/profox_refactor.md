# Documentação: Remoção da API ProFox Networks

## Visão Geral

A refatoração removeu a dependência da API centralizada `profoxnetworks.com.br` para categorias, subcategorias, salas e produtos, migrando para uso exclusivo do banco de dados local.

## Mudanças Realizadas

### 1. Arquivos Removidos
```
src/Service/CentralSyncService.php
src/Service/CategoriaSincronizadoService.php
src/Service/CategoriaSalaCentralService.php
```

### 2. Arquivos Refatorados
| Arquivo | Mudança |
|---------|---------|
| `CategoriaController.php` | Usa `CategoriaService` (banco local) |
| `SubcategoriaController.php` | Usa `SubcategoriaService` (banco local) |
| `FornecedorController.php` | Usa `CategoriaService` e `SubcategoriaService` |
| `PublicoCategoriaController.php` | Usa serviços locais |
| `WebhookSyncController.php` | Mantido para compatibilidade, sem dependências ProFox |
| `CategoriaSalaController.php` | Usa `CategoriaSalaService` (banco local) |

### 3. Tabelas do Banco
- Colunas `central_id` já removidas das tabelas
- Tabelas de IA (`ai_*`) removidas (8 tabelas)

### 4. Configuração (.env)
Variáveis comentadas:
- `CENTRAL_SYNC_KEY`
- `INSTANCE`
- `CATEGORIAS_API_URL`
- `WEBHOOK_KEY`

## Serviços Utilizados

### CategoriaService
- `getAtivas()` - retorna categorias ativas
- `create()` - cria nova categoria
- `update()` - atualiza categoria
- `delete()` - exclui categoria

### SubcategoriaService
- `findByCategoria($id)` - subcategorias por categoria
- `create()` - cria nova subcategoria
- `update()` - atualiza subcategoria
- `delete()` - exclui subcategoria

### CategoriaSalaService
- `getAtivas()` - categorias de sala ativas
- `getAllOrdenadas()` - todas ordenadas
- `create()`, `update()`, `delete()` - CRUD

## APIs Públicas

### GET /public/categorias
Retorna categorias locais:
```json
{
  "success": true,
  "data": [{"id": 1, "nome": "Estrutura e Infraestrutura"}]
}
```

### GET /public/subcategorias/{id_categoria}
Retorna subcategorias por categoria:
```json
{
  "success": true,
  "data": [{"id": 1, "nome": "Eletrônica"}]
}
```

### GET /funcoes
Retorna funções padrão para colaboradores.

## Testes

Todos os testes de sintaxe passaram:
- ✅ Classes existentes
- ✅ Arquivos removidos
- ✅ Sintaxe PHP correta

## Data da Refatoração
2026-06-05