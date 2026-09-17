<?php
/**
 * Componente: Modal de Item (Edicao de Produto)
 * Usado para editar itens existentes na sala
 */

function renderModalItem($config = []) {
    $idEvento = $config['id_evento'] ?? 0;
    $idSala = $config['id_sala'] ?? 0;
    $produto = $config['produto'] ?? null;

    if (empty($produto)) {
        return '';
    }

    $modalId = 'modal-edit-item';
    $formId = 'form-edit-item';

    $body = '<form id="' . $formId . '" class="fg" style="gap:12px">';
    $body .= '<input type="hidden" name="_csrf_token" value="' . \App\Core\Csrf::getToken() . '">';
    $body .= '<input type="hidden" name="id_evento" value="' . $idEvento . '">';
    $body .= '<input type="hidden" name="id_sala" value="' . $idSala . '">';
    $body .= '<input type="hidden" name="id_produto" value="' . $produto['id'] . '">';
    $body .= '<input type="hidden" name="id_planilha" value="' . ($produto['id_planilha'] ?? '') . '">';

    $body .= '<div class="fg">';
    $body .= '<div class="fl">Produto</div>';
    $body .= '<input type="text" class="fi" name="produto" value="' . htmlspecialchars($produto['produto']) . '" readonly style="background:var(--bg-secondary)">';
    $body .= '</div>';

    $body .= '<div class="fg">';
    $body .= '<div class="fl">Observacao de Montagem</div>';
    $body .= '<textarea class="fi" name="observacao_montagem" rows="2">' . htmlspecialchars($produto['observacao_montagem'] ?? '') . '</textarea>';
    $body .= '</div>';

    $body .= '<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px">';

    $body .= '<div class="fg">';
    $body .= '<div class="fl">Quantidade</div>';
    $body .= '<input type="number" class="fi" name="qtd" id="item-qtd" value="' . $produto['qtd'] . '" min="0" step="0.01">';
    $body .= '</div>';

    $body .= '<div class="fg">';
    $body .= '<div class="fl">Valor Unit. (R$)</div>';
    $body .= '<input type="number" class="fi" name="valor_unit" id="item-valor-unit" value="' . $produto['valor_unit'] . '" min="0" step="0.01">';
    $body .= '</div>';

    $body .= '<div class="fg">';
    $body .= '<div class="fl">Dias</div>';
    $body .= '<input type="number" class="fi" name="dias" id="item-dias" value="' . $produto['dias'] . '" min="1">';
    $body .= '</div>';

    $body .= '</div>';

    $totalItem = $produto['qtd'] * $produto['valor_unit'] * $produto['dias'];
    $body .= '<div class="fg">';
    $body .= '<div class="fl">Total Item (R$)</div>';
    $body .= '<input type="text" class="fi" id="item-total" value="R$ ' . number_format($totalItem, 2, ',', '.') . '" readonly style="background:var(--bg-secondary);font-weight:600">';
    $body .= '</div>';

    $body .= '<div class="fg">';
    $body .= '<div class="fl">Custo Unitario (R$)</div>';
    $body .= '<input type="number" class="fi" name="custo_unit" id="item-custo-unit" value="' . $produto['custo_unit'] . '" min="0" step="0.01">';
    $body .= '</div>';

    $body .= '<div style="display:flex;gap:8px;justify-content:flex-end;margin-top:8px">';
    $body .= '<button type="button" class="btn btn-gray" onclick="closeModal(\'' . $modalId . '\')">Cancelar</button>';
    $body .= '<button type="submit" class="btn btn-cyan" id="btn-save-item">Atualizar</button>';
    $body .= '</div>';

    $body .= '</form>';

    $body .= '<script>';
    $body .= '(function() {
        var form = document.getElementById("' . $formId . '");
        var qtdInput = document.getElementById("item-qtd");
        var valorInput = document.getElementById("item-valor-unit");
        var diasInput = document.getElementById("item-dias");
        var totalInput = document.getElementById("item-total");

        function calcularTotal() {
            var qtd = parseFloat(qtdInput.value) || 0;
            var valor = parseFloat(valorInput.value) || 0;
            var dias = parseInt(diasInput.value) || 1;
            var total = qtd * valor * dias;
            totalInput.value = "R$ " + total.toFixed(2).replace(".", ",");
        }

        if (qtdInput) qtdInput.addEventListener("input", calcularTotal);
        if (valorInput) valorInput.addEventListener("input", calcularTotal);
        if (diasInput) diasInput.addEventListener("input", calcularTotal);

        if (form) {
            form.addEventListener("submit", function(e) {
                e.preventDefault();
                var btn = document.getElementById("btn-save-item");
                if (btn) { btn.disabled = true; btn.textContent = "Salvando..."; }
                var formData = new FormData(form);
                fetch(BASE_URL + "/produtos-evento/update/' . $produto['id'] . '", { method: "POST", body: formData })
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        if (data.success) {
                            showToast("green", "Sucesso", "Item atualizado com sucesso!");
                            closeModal("' . $modalId . '");
                            if (typeof carregarSalas === "function") { carregarSalas(' . $idEvento . '); }
                        } else {
                            showToast("red", "Erro", data.error || "Erro ao atualizar item");
                        }
                        if (btn) { btn.disabled = false; btn.textContent = "Atualizar"; }
                    })
                    .catch(function() {
                        showToast("red", "Erro", "Erro ao processar requisicao");
                        if (btn) { btn.disabled = false; btn.textContent = "Atualizar"; }
                    });
            });
        }
    })();';
    $body .= '</script>';

    return renderModal([
        'id' => $modalId,
        'variant' => 'form',
        'title' => 'Editar Item',
        'body' => $body,
    ]);
}
