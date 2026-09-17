# Card Component

## Uso
- Containers de conteúdo
- Estatísticas e métricas (KPIs)
- Informações agrupadas
- Destaque de dados

## Tipos de Card

### 1. Card Padrão
Estrutura com head, body e tag opcional.

### 2. Custom Card
Versão com bordas mais definidas e footer.

### 3. Card com Glow
Card destacado com efeito neon cyan.

### 4. Card Stat
Card de estatística com ícone grande e valor.

## Classes Base

### Card Padrão
- `card` - Container principal
- `card-head` - Cabeçalho
- `card-body` - Conteúdo
- `card-title` - Título no header
- `card-tag` - Tag/etiqueta no header

### Custom Card
- `custom-card` - Container principal
- `custom-card-head` - Cabeçalho
- `custom-card-title` - Título no header
- `custom-card-body` - Conteúdo
- `custom-card-foot` - Rodapé com ações

### Card com Glow
- Adicionar `card-glow` ao custom-card

### Card Stat
- `card-stat` - Container com centralização
- `card-stat-icon` - Ícone grande (56x56px)
- `card-stat-val` - Valor numérico
- `card-stat-lbl` - Label descritivo

## Exemplos

### Card Padrão
```php
echo renderCard([
    'title' => 'Título do Card',
    'tag'   => 'Tag',
    'body'  => '<p>Conteúdo do card aqui.</p>'
]);
```

### Card com Footer e Padding Customizado
```php
echo renderCard([
    'title'   => 'Configurações',
    'body'    => '<p>Conteúdo ajustado.</p>',
    'footer'  => '<button class="btn btn-sm btn-cyan">Salvar</button>',
    'padding' => '24px 32px'
]);
```

### Custom Card
```php
echo renderCustomCard([
    'title'  => 'Card Padrão',
    'body'   => '<p>Conteúdo do card com informações relevantes.</p>',
    'footer' => '<button class="btn btn-sm btn-cyan">Ação</button><button class="btn btn-sm btn-ghost">Cancelar</button>'
]);
```

### Custom Card com Glow
```php
echo renderCustomCard([
    'title'  => 'Card com Glow',
    'body'   => '<p>Card destacado com efeito neon cyan.</p>',
    'footer' => '<button class="btn btn-sm btn-cyan">Ver Detalhes</button>',
    'glow'   => true
]);
```

### Card de Estatística
```php
echo renderCardStat([
    'icon'  => '<svg fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
    'value' => 'R$ 38,4k',
    'label' => 'Receita Mensal'
]);
```

### Card Stat com Cor Customizada
```php
echo renderCardStat([
    'icon'  => '<svg>...</svg>',
    'value' => '47',
    'label' => 'Locações Ativas',
    'color' => 'var(--neon-green)'
]);
```

## Regras para IA
1. Usar `renderCard` para conteúdo simples com header
2. Usar `renderCustomCard` quando precisa de footer com ações
3. Usar `glow => true` para destacar informações importantes
4. Usar `renderCardStat` para métricas e KPIs
5. Usar `col2` para grid de 2 colunas
6. Usar `col3` para grid de 3 colunas (stats)
7. Ícone do stat card deve usar SVG com stroke="white"
8. Valor do stat pode ter cor customizada via `color`
