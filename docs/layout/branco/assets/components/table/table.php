<?php
/**
 * Table Component
 *
 * @param array  $headers    - Array of ['label' => string, 'sortable' => bool]
 * @param array  $rows       - Array of arrays (each cell can be string or ['html' => true, 'content' => '...'])
 * @param string $id         - Table ID for sorting/pagination/search
 * @param bool   $striped    - Striped rows (default: true)
 * @param bool   $hoverable  - Hover highlight (default: true)
 * @param bool   $actions    - Show default action column (default: false)
 * @param array  $actionBtns - Override action buttons per row: [['label' => 'Editar', 'variant' => 'green', 'icon' => '...'], ...]
 * @param bool   $searchable - Show search input (default: false)
 * @param string $searchPlaceholder - Placeholder text for search (default: 'Buscar...')
 * @param bool   $paginated  - Enable pagination (default: false)
 * @param int    $perPage    - Rows per page (default: 5)
 * @param array  $perPageOptions - Options for per-page selector (default: [5, 10, 25, 50])
 */

function renderTable($config) {
    $headers     = $config['headers'] ?? [];
    $rows        = $config['rows'] ?? [];
    $id          = $config['id'] ?? '';
    $striped     = $config['striped'] ?? true;
    $hoverable   = $config['hoverable'] ?? true;
    $actionBtns  = $config['actionBtns'] ?? [];
    $searchable  = $config['searchable'] ?? false;
    $searchPlaceholder = $config['searchPlaceholder'] ?? 'Buscar...';
    $paginated   = $config['paginated'] ?? false;
    $perPage     = $config['perPage'] ?? 5;
    $perPageOpts = $config['perPageOptions'] ?? [5, 10, 25, 50];

    $classes = ['data-table'];
    if ($striped)   $classes[] = 'striped';
    if ($hoverable) $classes[] = 'hoverable';
    $classStr = implode(' ', $classes);

    $idAttr = $id ? " id=\"{$id}\"" : '';
    $dataAttrs = " data-per-page=\"{$perPage}\"";
    if ($paginated) $dataAttrs .= ' data-paginated="true"';

    // Wrapper with search + pagination support
    $html = '<div class="tbl-container"' . ($id ? ' id="' . $id . '-container"' : '') . '>';

    // Toolbar: search + per-page selector
    if ($searchable || $paginated) {
        $html .= '<div class="tbl-toolbar">';

        if ($searchable) {
            $html .= '<div class="tbl-search">';
            $html .= '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35"/></svg>';
            $html .= '<input type="text" class="tbl-search-input" placeholder="' . htmlspecialchars($searchPlaceholder) . '"' . ($id ? ' oninput="tblSearch(\'' . $id . '\',this.value)"' : '') . '/>';
            $html .= '</div>';
        }

        if ($paginated) {
            $html .= '<div class="tbl-per-page">';
            $html .= '<span>Linhas:</span>';
            $html .= '<select class="tbl-per-page-select"' . ($id ? ' onchange="tblSetPerPage(\'' . $id . '\',this.value)"' : '') . '>';
            foreach ($perPageOpts as $opt) {
                $selected = ($opt == $perPage) ? ' selected' : '';
                $html .= '<option value="' . $opt . '"' . $selected . '>' . $opt . '</option>';
            }
            $html .= '</select>';
            $html .= '</div>';
        }

        $html .= '</div>';
    }

    // Table
    $html .= '<div class="table-wrap">';
    $html .= "<table class=\"{$classStr}\"{$idAttr}{$dataAttrs}>";

    // Headers
    if (!empty($headers)) {
        $html .= '<thead><tr>';
        foreach ($headers as $header) {
            $label    = is_array($header) ? ($header['label'] ?? '') : $header;
            $sortable = is_array($header) && ($header['sortable'] ?? false);

            if ($sortable) {
                $html .= "<th class=\"th-sort\">{$label}<span class=\"sort-ico\"></span></th>";
            } else {
                $html .= "<th>{$label}</th>";
            }
        }
        if (!empty($actionBtns)) {
            $html .= '<th>Ações</th>';
        }
        $html .= '</tr></thead>';
    }

    // Rows
    if (!empty($rows)) {
        $html .= '<tbody>';
        foreach ($rows as $rowIdx => $row) {
            $dataId = '';
            if (isset($row['data-id'])) {
                $dataId = ' data-id="' . htmlspecialchars($row['data-id']) . '"';
                // Remove data-id do array de células
                unset($row['data-id']);
            }
            $html .= '<tr' . $dataId . '>';
            foreach ($row as $cell) {
                if (is_array($cell) && isset($cell['html']) && $cell['html']) {
                    $html .= "<td>{$cell['content']}</td>";
                } elseif (is_array($cell)) {
                    $content = $cell['content'] ?? '';
                    $html .= "<td>{$content}</td>";
                } else {
                    $html .= "<td>{$cell}</td>";
                }
            }
            if (!empty($actionBtns)) {
                $html .= '<td><div class="td-actions">';
                $total = count($actionBtns);
                $popId = 'act-pop-' . $id . '-' . $rowIdx;
                // data-id tambem vai em cada botao de acao, nao so no <tr> — os
                // handlers registrados via registerAction()/registerActions() leem
                // el.dataset.id a partir do proprio elemento clicado (ver bug data-action
                // "edit"/"delete" sem data-id, 2026-07-03).
                $btnDataId = $dataId;

                if ($total <= 2) {
                    // 2 or fewer: inline buttons + also all in dropdown for compact mode
                    foreach ($actionBtns as $btn) {
                        $bLabel   = $btn['label'] ?? 'Ação';
                        $bVariant = $btn['variant'] ?? 'ghost';
                        $bSize    = $btn['size'] ?? 'sm';
                        $bIcon    = $btn['icon'] ?? '';
                        $bAction  = $btn['action'] ?? strtolower(str_replace([' ', 'ç', 'ã', 'é', 'í', 'ó', 'ú'], ['-', 'c', 'a', 'e', 'i', 'o', 'u'], $bLabel));
                        $bClass   = "btn btn-{$bSize} btn-{$bVariant}";
                        $html .= "<button class=\"{$bClass}\" data-action=\"{$bAction}\"{$btnDataId}>";
                        if ($bIcon) $html .= $bIcon;
                        $html .= "{$bLabel}</button>";
                    }
                    $html .= '<div class="td-act-menu td-act-compact">';
                    $html .= '<button class="btn btn-sm td-act-toggle" data-action="toggle-pop" data-pop-id="' . $popId . '"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/></svg><span class="td-act-toggle-label">Ações</span></button>';
                    $html .= '<div class="td-act-dropdown" id="' . $popId . '">';
                    foreach ($actionBtns as $btn) {
                        $bLabel   = $btn['label'] ?? 'Ação';
                        $bVariant = $btn['variant'] ?? 'ghost';
                        $bIcon    = $btn['icon'] ?? '';
                        $bAction  = $btn['action'] ?? strtolower(str_replace([' ', 'ç', 'ã', 'é', 'í', 'ó', 'ú'], ['-', 'c', 'a', 'e', 'i', 'o', 'u'], $bLabel));
                        $html .= "<button class=\"td-act-item td-act-{$bVariant}\" data-action=\"{$bAction}\"{$btnDataId}>";
                        if ($bIcon) $html .= $bIcon;
                        $html .= "<span>{$bLabel}</span></button>";
                    }
                    $html .= '</div></div>';
                } else {
                    // 3+: first 2 inline + "..." toggle with ALL in dropdown
                    for ($i = 0; $i < 2; $i++) {
                        $btn = $actionBtns[$i];
                        $bLabel   = $btn['label'] ?? 'Ação';
                        $bVariant = $btn['variant'] ?? 'ghost';
                        $bSize    = $btn['size'] ?? 'sm';
                        $bIcon    = $btn['icon'] ?? '';
                        $bAction  = $btn['action'] ?? strtolower(str_replace([' ', 'ç', 'ã', 'é', 'í', 'ó', 'ú'], ['-', 'c', 'a', 'e', 'i', 'o', 'u'], $bLabel));
                        $bOnclick = isset($btn['onclick']) ? " onclick=\"{$btn['onclick']}\"" : '';
                        $bClass   = "btn btn-{$bSize} btn-{$bVariant}";
                        $html .= "<button class=\"{$bClass}\" data-action=\"{$bAction}\"{$btnDataId}{$bOnclick}>";
                        if ($bIcon) $html .= $bIcon;
                        $html .= "{$bLabel}</button>";
                    }
                    $html .= '<div class="td-act-menu">';
                    $html .= '<button class="btn btn-sm td-act-toggle" onclick="togglePop(\'' . $popId . '\')"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/></svg><span class="td-act-toggle-label">Ações</span></button>';
                    $html .= '<div class="td-act-dropdown" id="' . $popId . '">';
                    foreach ($actionBtns as $btn) {
                        $bLabel   = $btn['label'] ?? 'Ação';
                        $bVariant = $btn['variant'] ?? 'ghost';
                        $bIcon    = $btn['icon'] ?? '';
                        $bAction  = $btn['action'] ?? strtolower(str_replace([' ', 'ç', 'ã', 'é', 'í', 'ó', 'ú'], ['-', 'c', 'a', 'e', 'i', 'o', 'u'], $bLabel));
                        $bOnclick = isset($btn['onclick']) ? " onclick=\"{$btn['onclick']}\"" : '';
                        $html .= "<button class=\"td-act-item td-act-{$bVariant}\" data-action=\"{$bAction}\"{$btnDataId}{$bOnclick}>";
                        if ($bIcon) $html .= $bIcon;
                        $html .= "<span>{$bLabel}</span></button>";
                    }
                    $html .= '</div></div>';
                }
                $html .= '</div></td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody>';
    }

    $html .= '</table></div>';

    // Pagination footer
    if ($paginated) {
        $html .= '<div class="tbl-pagination">';
        $html .= '<div class="tbl-info" id="' . $id . '-info">Mostrando 0 de 0</div>';
        $html .= '<div class="tbl-pages" id="' . $id . '-pages"></div>';
        $html .= '</div>';
    }

    // No results message (hidden by default)
    if ($searchable) {
        $html .= '<div class="tbl-no-results" id="' . $id . '-no-results" style="display:none">';
        $html .= '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35"/></svg>';
        $html .= '<span>Nenhum resultado encontrado</span>';
        $html .= '</div>';
    }

    $html .= '</div>';

    return $html;
}

/**
 * Table Action Button Presets
 *
 * @param string $type     - 'default' | 'crud' | 'confirm'
 * @param string $suffix   - Nome da entidade (ex: 'cliente') para gerar 'edit-cliente'/
 *                            'delete-cliente' em vez de 'edit'/'delete' genericos. Deve
 *                            bater com as chaves passadas a window.registerActions() na
 *                            view — sem isso os botoes Editar/Excluir nao tem handler
 *                            (ver memory/bugs.md bug-036, 2026-07-03).
 * @param bool   $canEdit   - false esconde o botao Editar (checar Rbac::check() do modulo
 *                            antes de chamar — ver memory/bugs.md bug-060).
 * @param bool   $canDelete - false esconde o botao Excluir (idem).
 */
function renderTableActions($type = 'default', $suffix = '', $canEdit = true, $canDelete = true) {
    $presets = [
        'default' => [
            ['label' => 'Editar', 'variant' => 'cyan', 'size' => 'sm', 'action' => 'edit', 'icon' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>'],
            ['label' => 'Excluir', 'variant' => 'red', 'size' => 'sm', 'action' => 'delete', 'icon' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>'],
        ],
        'crud' => [
            ['label' => 'Ver', 'variant' => 'green', 'size' => 'sm', 'action' => 'view', 'icon' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>'],
            ['label' => 'Editar', 'variant' => 'purple', 'size' => 'sm', 'action' => 'edit', 'icon' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>'],
            ['label' => 'Excluir', 'variant' => 'red', 'size' => 'sm', 'action' => 'delete', 'icon' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>'],
        ],
        'confirm' => [
            ['label' => 'Aprovar', 'variant' => 'green', 'size' => 'sm', 'action' => 'aprovar', 'icon' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>'],
            ['label' => 'Rejeitar', 'variant' => 'red', 'size' => 'sm', 'action' => 'rejeitar', 'icon' => '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>'],
        ],
    ];
    $result = $presets[$type] ?? $presets['default'];

    $result = array_values(array_filter($result, function ($btn) use ($canEdit, $canDelete) {
        if ($btn['action'] === 'edit' && !$canEdit) {
            return false;
        }
        if ($btn['action'] === 'delete' && !$canDelete) {
            return false;
        }
        return true;
    }));

    if ($suffix !== '') {
        foreach ($result as &$btn) {
            $btn['action'] = $btn['action'] . '-' . $suffix;
        }
        unset($btn);
    }
    return $result;
}
