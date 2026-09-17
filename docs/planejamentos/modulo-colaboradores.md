# Planejamento do Módulo de Colaboradores

## Visão Geral

Módulo para gerenciar colaboradores (funcionários e freelancers) com **fluxo de verificação externo** via WhatsApp:

- Cadastro interno com status **inativo por padrão**
- Envio de link externo via WhatsApp para colaborador
- Colaborador acessa link, envia foto + geolocalização
- Ao enviar foto, status muda para **ativo** automaticamente
- Toggle manual de status disponível no painel interno

## Estrutura do Banco de Dados

### Tabela `colaboradores`

```sql
CREATE TABLE colaboradores (
  id INT AUTO_INCREMENT PRIMARY KEY,
  origem VARCHAR(100),
  tipo ENUM('FUNCIONARIO', 'FREELANCE'),
  estado_para_trabalho VARCHAR(255),
  nome VARCHAR(255),
  telefone VARCHAR(20),
  email VARCHAR(255),
  atua_como VARCHAR(255),
  foto LONGTEXT,
  cep VARCHAR(10),
  endereco VARCHAR(255),
  bairro VARCHAR(100),
  cidade VARCHAR(100),
  estado VARCHAR(2),
  tipo_chave_pix VARCHAR(50),
  chavepix VARCHAR(255),
  observacao TEXT,
  ativo BOOLEAN DEFAULT FALSE
) ENGINE=InnoDB;
```

### Tabela `colaboradores_verificacao`

```sql
CREATE TABLE colaboradores_verificacao (
  id INT AUTO_INCREMENT PRIMARY KEY,
  colaborador_id INT NOT NULL,
  token VARCHAR(255) UNIQUE NOT NULL,
  enviado_via_whatsapp BOOLEAN DEFAULT FALSE,
  data_envio DATETIME,
  data_acesso DATETIME,
  foto_enviada LONGTEXT,
  geolocalizacao_lat DECIMAL(10, 8),
  geolocalizacao_lng DECIMAL(11, 8),
  status ENUM('pendente', 'acessado', 'verificado', 'expirado') DEFAULT 'pendente',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (colaborador_id) REFERENCES colaboradores(id) ON DELETE CASCADE
) ENGINE=InnoDB;
```

## Fluxo de Funcionamento

### Interno (Painel)
1. Admin cria colaborador → `ativo = FALSE`
2. Tabela com busca + paginação + toggle + botão "Enviar Link" + "Reenviar"
3. Link enviado via WhatsApp API
4. Acompanhamento de status: Pendente / Acessado / Verificado

### Externo (Link WhatsApp)
1. Colaborador recebe link: `{BASE_URL}/colaboradores/verificar/{token}`
2. Acessa página limpa (sem sidebar/topbar)
3. Envia foto + geolocalização automática
4. Status muda para `ativo = TRUE` automaticamente

## Checklist de Implementação

### Fase 1: Banco de Dados
- [ ] Criar tabela `colaboradores`
- [ ] Criar tabela `colaboradores_verificacao`

### Fase 2: Backend (MVC)
- [ ] ColaboradorRepository + ColaboradorVerificacaoRepository
- [ ] ColaboradorService + ColaboradorVerificacaoService
- [ ] ColaboradorController (CRUD interno)
- [ ] VerificacaoController (API externa - sem auth)

### Fase 3: Rotas
- Internas: /colaboradores/* (com AuthMiddleware)
- Externas: /colaboradores/verificar/{token} (sem auth)

### Fase 4: Views Internas
- index.php (listagem com busca + paginação + CRUD + toggle + enviar link)
- create.php (formulário)
- edit.php (formulário)

### Fase 5: Views Externas
- verificacao/formulario.php (foto + geolocalizacao)
- verificacao/sucesso.php (confirmação)

### Fase 6: RBAC
- Permissões no array Rbac.php
- Banco de dados: modulos, permissoes, role_permissoes

### Fase 7: Sidebar
- Link no menu

### Fase 8: WhatsApp API
- Integrar com endpoint do .env
- Variáveis: WHATSAPP_API_URL, WHATSAPP_API_TOKEN

### Fase 9: Análise de Código Morto

## Detalhes Técnicos

### Botões da Tabela
- **Editar** → /colaboradores/edit/{id}
- **Excluir** → POST /colaboradores/delete/{id}
- **Ativar/Inativar** → POST /colaboradores/toggle/{id}
- **Enviar Link** → POST /colaboradores/enviar-link/{id}
- **Reenviar Link** → POST /colaboradores/reenviar-link/{id}

### Variáveis .env
```env
# WhatsApp API
WHATSAPP_API_URL=https://api.example.com/v1/messages
WHATSAPP_API_TOKEN=seu_token_aqui
```

## Permissões RBAC

```php
'colaboradores' => ['administrador'],
'colaboradores.listar' => ['administrador'],
'colaboradores.criar' => ['administrador'],
'colaboradores.editar' => ['administrador'],
'colaboradores.excluir' => ['administrador'],
```
