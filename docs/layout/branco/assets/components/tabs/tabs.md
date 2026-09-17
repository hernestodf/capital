# Tabs Component

## Uso
- Navegacao entre secoes de conteudo
- Organizacao de informacoes em categorias
- Painel de configuracoes
- Wizard com navegacao lateral

## Variantes

### 1. Tabs Coloridas (Horizontal)
Classes: `htabs-color`, `ht-color`, `tab-pane`

```php
renderTabsColor([
    'id' => 'tabs-color',
    'tabs' => [
        ['label' => 'Resumo', 'color' => 'red', 'active' => true, 'content' => 'Conteudo...'],
        ['label' => 'Locacoes', 'color' => 'cyan', 'content' => 'Conteudo...'],
    ]
]);
```

### 2. Tabs Underline (Horizontal)
Classes: `htabs-underline`, `ht-ul`, `tab-pane`

```php
renderTabsUnderline([
    'id' => 'tabs-ul',
    'tabs' => [
        ['label' => 'Geral', 'active' => true, 'content' => 'Conteudo...'],
        ['label' => 'Detalhes', 'content' => 'Conteudo...'],
    ]
]);
```

### 3. Tabs Pill (Horizontal)
Classes: `htabs-pill`, `ht-pill`, `tab-pane`

```php
renderTabsPill([
    'id' => 'tabs-pill',
    'tabs' => [
        ['label' => 'Hoje', 'active' => true, 'content' => '...'],
        ['label' => 'Semana', 'content' => '...'],
    ]
]);
```

### 4. Tabs Segmented (Horizontal)
Classes: `htabs-segmented`, `ht-seg`, `tab-pane`

```php
renderTabsSegmented([
    'id' => 'tabs-seg',
    'tabs' => [
        ['label' => 'Lista', 'active' => true, 'content' => '...'],
        ['label' => 'Grade', 'content' => '...'],
    ]
]);
```

### 5. Tabs Vertical
Classes: `vtabs`, `vtabs-nav`, `vt-item`, `vtabs-content`, `tab-pane`
Variante dark: adicionar classe `vtabs-dark`

```php
renderTabsVertical([
    'id' => 'tabs-v',
    'tabs' => [
        ['label' => 'Perfil', 'icon' => '<svg>...</svg>', 'active' => true, 'content' => '...'],
        ['label' => 'Senha', 'icon' => '<svg>...</svg>', 'content' => '...'],
    ],
    'dark' => false
]);
```

## JavaScript
```javascript
// Troca de tab generica
switchTab(groupId, index)
```

## Regras para IA
1. Sempre fornecer ID unico para cada grupo de tabs
2. Tab ativo deve ter `active => true` no config
3. Content pode conter qualquer HTML
4. Vertical tabs suportam icones SVG
5. Usar tabs coloridos para navegacao principal
6. Usar underline para sub-navegacao
7. Usar pill/segmented para filtros e toggles
8. Usar vertical para paineis de configuracao
9. Tabs vertical dark usa `vtabs-dark` para fundo escuro
