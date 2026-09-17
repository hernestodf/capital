<?php
/**
 * Componente: Formulario de Produto para Salas e Produtos
 * Usado no modulo de eventos para adicionar produtos as salas
 */

function renderFormProduto($config = []) {
    $idEvento = $config['id_evento'] ?? 0;
    $idSala = $config['id_sala'] ?? 0;
    $categorias = $config['categorias'] ?? [];
    $produto = $config['produto'] ?? null;

    $isEdit = !empty($produto);
    $formId = $isEdit ? 'form-edit-produto' : 'form-novo-produto';

    $html = '<form id="' . $formId . '" class="fg" style="gap:12px">';
    $html .= '<input type="hidden" name="_csrf_token" value="' . \App\Core\Csrf::getToken() . '">';
    $html .= '<input type="hidden" name="id_evento" value="' . $idEvento . '">';
    $html .= '<input type="hidden" name="id_sala" value="' . $idSala . '">';
    if ($isEdit) {
        $html .= '<input type="hidden" name="id_produto" value="' . $produto['id'] . '">';
        $html .= '<input type="hidden" name="id_planilha" value="' . ($produto['id_planilha'] ?? '') . '">';
    }

    // Autocomplete do produto
    $html .= '<div class="fg">';
    $html .= '<div class="fl">Produto</div>';
    $html .= '<div style="position:relative">';
    $html .= '<input type="text" id="produto-autocomplete" class="fi" name="produto_text" placeholder="Buscar produto na planilha..." value="' . ($isEdit ? htmlspecialchars($produto['produto']) : '') . '" autocomplete="off">';
    $html .= '<input type="hidden" name="produto" id="produto-hidden" value="' . ($isEdit ? htmlspecialchars($produto['produto']) : '') . '">';
    $html .= '<input type="hidden" name="id_planilha" id="id_planilha-hidden" value="' . ($isEdit ? ($produto['id_planilha'] ?? '') : '') . '">';
    $html .= '<div id="produto-suggestions" style="position:absolute;top:100%;left:0;right:0;background:var(--bg-surface);border:1px solid var(--bg-border);border-radius:8px;max-height:200px;overflow-y:auto;z-index:100;display:none"></div>';
    $html .= '<div id="produto-desc-popover" style="font-size:11px;color:var(--text-3);margin-top:4px;display:none"></div>';
    $html .= '</div>';
    $html .= '</div>';

    // Observacao de montagem
    $html .= '<div class="fg">';
    $html .= '<div class="fl">Observacao de Montagem</div>';
    $html .= '<textarea class="fi" name="observacao_montagem" placeholder="Observacoes sobre montagem..." rows="2">' . ($isEdit ? htmlspecialchars($produto['observacao_montagem'] ?? '') : '') . '</textarea>';
    $html .= '</div>';

    // Grid 3 colunas: Qtd, Valor Unit, Dias
    $html .= '<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px">';

    $html .= '<div class="fg">';
    $html .= '<div class="fl">Quantidade</div>';
    $html .= '<input type="number" class="fi" name="qtd" id="produto-qtd" value="' . ($isEdit ? $produto['qtd'] : '1') . '" min="0" step="0.01">';
    $html .= '</div>';

    $html .= '<div class="fg">';
    $html .= '<div class="fl">Valor Unit. (R$)</div>';
    $html .= '<input type="number" class="fi" name="valor_unit" id="produto-valor-unit" value="' . ($isEdit ? $produto['valor_unit'] : '0') . '" min="0" step="0.01">';
    $html .= '</div>';

    $html .= '<div class="fg">';
    $html .= '<div class="fl">Dias</div>';
    $html .= '<input type="number" class="fi" name="dias" id="produto-dias" value="' . ($isEdit ? $produto['dias'] : '1') . '" min="1">';
    $html .= '</div>';

    $html .= '</div>';

    // Total calculado
    $totalItem = $isEdit ? ($produto['qtd'] * $produto['valor_unit'] * $produto['dias']) : 0;
    $html .= '<div class="fg">';
    $html .= '<div class="fl">Total Item (R$)</div>';
    $html .= '<input type="text" class="fi" id="produto-total" value="R$ ' . number_format($totalItem, 2, ',', '.') . '" readonly style="background:var(--bg-secondary);font-weight:600">';
    $html .= '</div>';

    // Custo unitario
    $html .= '<div class="fg">';
    $html .= '<div class="fl">Custo Unitario (R$)</div>';
    $html .= '<input type="number" class="fi" name="custo_unit" id="produto-custo-unit" value="' . ($isEdit ? $produto['custo_unit'] : '0') . '" min="0" step="0.01">';
    $html .= '</div>';

    // Botoes
    $html .= '<div style="display:flex;gap:8px;justify-content:flex-end;margin-top:8px">';
    $html .= '<button type="button" class="btn btn-gray" onclick="fecharModalProduto()">Cancelar</button>';
    $html .= '<button type="submit" class="btn btn-cyan" id="btn-save-produto">' . ($isEdit ? 'Atualizar' : 'Adicionar') . '</button>';
    $html .= '</div>';

    $html .= '</form>';

    // JavaScript para autocomplete e calculo
    $html .= '<script>';
    $html .= '(function() {
        var form = document.getElementById("' . $formId . '");
        var inputText = document.getElementById("produto-autocomplete");
        var hiddenProduto = document.getElementById("produto-hidden");
        var hiddenPlanilha = document.getElementById("id_planilha-hidden");
        var suggestionsBox = document.getElementById("produto-suggestions");
        var descPopover = document.getElementById("produto-desc-popover");
        var qtdInput = document.getElementById("produto-qtd");
        var valorInput = document.getElementById("produto-valor-unit");
        var diasInput = document.getElementById("produto-dias");
        var totalInput = document.getElementById("produto-total");

        // Autocomplete
        if (inputText) {
            var debounceTimer;
            inputText.addEventListener("input", function() {
                clearTimeout(debounceTimer);
                var term = this.value.trim();
                if (term.length < 2) {
                    suggestionsBox.style.display = "none";
                    return;
                }
                debounceTimer = setTimeout(function() {
                    fetch(BASE_URL + "/produtos-evento/autocomplete?q=" + encodeURIComponent(term))
                        .then(function(r) { return r.json(); })
                        .then(function(data) {
                            if (data.success && data.data.length > 0) {
                                suggestionsBox.innerHTML = "";
                                data.data.forEach(function(item) {
                                    var div = document.createElement("div");
                                    div.style.cssText = "padding:8px 12px;cursor:pointer;font-size:13px;border-bottom:1px solid var(--bg-border-sub)";
                                    div.textContent = item.item + " - R$ " + parseFloat(item.valor).toFixed(2).replace(".", ",");
                                    div.onmouseover = function() { this.style.background = "var(--bg-highlight)"; };
                                    div.onmouseout = function() { this.style.background = ""; };
                                    div.onclick = function() {
                                        inputText.value = item.item;
                                        hiddenProduto.value = item.item;
                                        hiddenPlanilha.value = item.id;
                                        valorInput.value = parseFloat(item.valor).toFixed(2);
                                        descPopover.textContent = item.descricao || "";
                                        descPopover.style.display = item.descricao ? "block" : "none";
                                        suggestionsBox.style.display = "none";
                                        calcularTotal();
                                    };
                                    suggestionsBox.appendChild(div);
                                });
                                suggestionsBox.style.display = "block";
                            } else {
                                suggestionsBox.style.display = "none";
                            }
                        });
                }, 300);
            });

            document.addEventListener("click", function(e) {
                if (!suggestionsBox.contains(e.target) && e.target !== inputText) {
                    suggestionsBox.style.display = "none";
                }
            });
        }

        // Calcular total
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

        // Submit form
        if (form) {
            form.addEventListener("submit", function(e) {
                e.preventDefault();
                var btn = document.getElementById("btn-save-produto");
                if (btn) { btn.disabled = true; btn.textContent = "Salvando..."; }
                var formData = new FormData(form);
                var url = ' . ($isEdit ? 'BASE_URL + "/produtos-evento/update/' . $produto['id'] . '"' : 'BASE_URL + "/produtos-evento/store"') . ';
                fetch(url, { method: "POST", body: formData })
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        if (data.success) {
                            showToast("green", "Sucesso", "Produto ' . ($isEdit ? 'atualizado' : 'adicionado') . ' com sucesso!");
                            fecharModalProduto();
                            if (typeof carregarSalas === "function") { carregarSalas(' . $idEvento . '); }
                        } else {
                            showToast("red", "Erro", data.error || "Erro ao salvar produto");
                        }
                        if (btn) { btn.disabled = false; btn.textContent = "' . ($isEdit ? 'Atualizar' : 'Adicionar') . '"; }
                    })
                    .catch(function() {
                        showToast("red", "Erro", "Erro ao processar requisicao");
                        if (btn) { btn.disabled = false; btn.textContent = "' . ($isEdit ? 'Atualizar' : 'Adicionar') . '"; }
                    });
            });
        }
    })();';
    $html .= '</script>';

    return $html;
}
