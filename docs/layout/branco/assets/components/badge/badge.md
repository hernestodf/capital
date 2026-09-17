# Badge Component

## Uso
- Indicadores de status
- Contadores
- Labels de estado
- Tags visuais

## Classes Base

### Cores
- `badge red` - Ativo (com pulse), crítico, atrasado
- `badge green` - Concluído, aprovado, funcionando
- `badge cyan` - Informação, em uso
- `badge blue` - Processando
- `badge purple` - Especial, manutenção
- `badge orange` - Pendente
- `badge yellow` - Em análise, atenção

### Tamanhos
- `badge` - Tamanho padrão (12.5px font, 5px 12px padding)
- `badge sm` - Pequeno (10px font, 3px 8px padding)
- `badge lg` - Grande (14px font, 7px 16px padding)

### Indicador de Ponto
- `badge::before` - Ponto automático antes do texto
- `<span class="dot pulse"></span>` - Ponto com animação pulse

## Exemplos

### Badge Simples
```php
echo renderBadge([
    'label'   => 'Info',
    'variant' => 'cyan'
]);
```

### Badge com Pulsação
```php
echo renderBadge([
    'label'   => 'Ativo',
    'variant' => 'red',
    'pulse'   => true
]);

echo renderBadge([
    'label'   => 'Concluído',
    'variant' => 'green',
    'pulse'   => true
]);

echo renderBadge([
    'label'   => 'Processando',
    'variant' => 'blue',
    'pulse'   => true
]);
```

### Tamanhos
```php
echo renderBadge([
    'label'   => 'Small',
    'variant' => 'red',
    'size'    => 'sm'
]);

echo renderBadge([
    'label'   => 'Default',
    'variant' => 'green'
]);

echo renderBadge([
    'label'   => 'Large',
    'variant' => 'cyan',
    'size'    => 'lg'
]);
```

### Em Tabelas e Listas
```php
echo renderBadge([
    'label'   => 'Ativo',
    'variant' => 'green',
    'size'    => 'sm',
    'pulse'   => true
]);

echo renderBadge([
    'label'   => 'Pendente',
    'variant' => 'yellow',
    'size'    => 'sm'
]);

echo renderBadge([
    'label'   => 'Atrasado',
    'variant' => 'red',
    'size'    => 'sm'
]);
```

## Regras para IA
1. Usar `sm` em tabelas e listas compactas
2. Usar tamanho padrão em cards e headers
3. Usar `lg` para destaque em dashboards
4. Usar `pulse => true` para status que indicam atividade em tempo real
5. `red` + pulse = ativo/crítico (contexto define)
6. `green` = sucesso/concluído
7. `yellow` = atenção/pendente/em análise
8. `cyan` = informação/neutro
9. `purple` = especial/manutenção
