<?php
/**
 * Componente: Modal de Sala (CRUD de Salas)
 * Usado para criar, editar e excluir salas de evento
 */

function renderModalSala($config = []) {
    $idEvento = $config['id_evento'] ?? 0;
    $categorias = $config['categorias'] ?? [];
    $sala = $config['sala'] ?? null;

    $isEdit = !empty($sala);
    $modalId = $isEdit ? 'modal-edit-sala' : 'modal-nova-sala';
    $formId = $isEdit ? 'form-edit-sala' : 'form-nova-sala';

    $categoryOptions = '<option value="">Selecione uma categoria</option>';
    foreach ($categorias as $cat) {
        $selected = ($isEdit && $sala['categoria_sala'] == $cat['id']) ? 'selected' : '';
        $categoryOptions .= '<option value="' . $cat['id'] . '" ' . $selected . '>' . htmlspecialchars($cat['nome_categoria']) . '</option>';
    }

    $body = '<form id="' . $formId . '" class="fg" style="gap:12px">';
    $body .= '<input type="hidden" name="_csrf_token" value="' . \App\Core\Csrf::getToken() . '">';
    $body .= '<input type="hidden" name="id_evento" value="' . $idEvento . '">';
    if ($isEdit) {
        $body .= '<input type="hidden" name="id_sala" value="' . $sala['id'] . '">';
    }

    $body .= '<div class="fg">';
    $body .= '<div class="fl">Nome da Sala</div>';
    $body .= '<input type="text" class="fi" name="nome_sala" value="' . ($isEdit ? htmlspecialchars($sala['nome_sala']) : '') . '" placeholder="Ex: Sala Principal, Hall de Entrada..." required>';
    $body .= '</div>';

    $body .= '<div class="fg">';
    $body .= '<div class="fl">Categoria</div>';
    $body .= '<select class="fi" name="categoria_sala">' . $categoryOptions . '</select>';
    $body .= '</div>';

    $body .= '<div class="fg">';
    $body .= '<div class="fl">Ordem</div>';
    $body .= '<input type="number" class="fi" name="ordem" value="' . ($isEdit ? $sala['ordem'] : '0') . '" min="0">';
    $body .= '<div style="font-size:11px;color:var(--text-3);margin-top:4px">Define a ordem de exibicao das salas</div>';
    $body .= '</div>';

    $body .= '<div class="fg">';
    $body .= '<div class="fl">Orientacoes de Montagem</div>';
    $body .= '<textarea class="fi" name="orientacoes_montagem" placeholder="Instrucoes especiais para montagem..." rows="4">' . ($isEdit ? htmlspecialchars($sala['orientacoes_montagem'] ?? '') : '') . '</textarea>';
    $body .= '</div>';

    $body .= '<div style="display:flex;gap:8px;justify-content:flex-end;margin-top:8px">';
    $body .= '<button type="button" class="btn btn-gray" onclick="closeModal(\'' . $modalId . '\')">Cancelar</button>';
    $body .= '<button type="submit" class="btn btn-cyan" id="btn-save-sala">' . ($isEdit ? 'Atualizar' : 'Criar Sala') . '</button>';
    $body .= '</div>';

    $body .= '</form>';

    // JavaScript do form
    $body .= '<script>';
    $body .= '(function() {
        var form = document.getElementById("' . $formId . '");
        if (form) {
            form.addEventListener("submit", function(e) {
                e.preventDefault();
                var btn = document.getElementById("btn-save-sala");
                if (btn) { btn.disabled = true; btn.textContent = "Salvando..."; }
                var formData = new FormData(form);
                var url = ' . ($isEdit ? 'BASE_URL + "/salas/update/' . $sala['id'] . '"' : 'BASE_URL + "/salas/store"') . ';
                fetch(url, { method: "POST", body: formData })
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        if (data.success) {
                            showToast("green", "Sucesso", "Sala ' . ($isEdit ? 'atualizada' : 'criada') . ' com sucesso!");
                            closeModal("' . $modalId . '");
                            if (typeof carregarSalas === "function") { carregarSalas(' . $idEvento . '); }
                        } else {
                            showToast("red", "Erro", data.error || "Erro ao salvar sala");
                        }
                        if (btn) { btn.disabled = false; btn.textContent = "' . ($isEdit ? 'Atualizar' : 'Criar Sala') . '"; }
                    })
                    .catch(function() {
                        showToast("red", "Erro", "Erro ao processar requisicao");
                        if (btn) { btn.disabled = false; btn.textContent = "' . ($isEdit ? 'Atualizar' : 'Criar Sala') . '"; }
                    });
            });
        }
    })();';
    $body .= '</script>';

    return renderModal([
        'id' => $modalId,
        'variant' => 'form',
        'title' => $isEdit ? 'Editar Sala' : 'Nova Sala',
        'body' => $body,
    ]);
}
