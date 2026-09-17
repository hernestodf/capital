<?php
/**
 * Partial: Formulario de Colaborador (compartilhado entre create e edit)
 * Espera: $action, $dados (array), $fotoPath (string), $error (string|null)
 */
$dados = $dados ?? [];
$fotoPath = $fotoPath ?? '';
$fotoUrl = $fotoUrl ?? '';
?>

      <!-- Foto -->
      <div style="margin-bottom:24px">
        <h4 style="font-size:14px;font-weight:600;margin:0 0 12px;color:var(--text-1)">Foto</h4>
        <div style="display:flex;gap:24px;align-items:flex-start;flex-wrap:wrap">
          <div style="width:150px;height:150px;border-radius:12px;overflow:hidden;border:2px dashed var(--bg-border);display:flex;align-items:center;justify-content:center;background:var(--bg-secondary);flex-shrink:0;position:relative" id="fotoPreviewContainer">
            <svg id="fotoPlaceholder" width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" style="color:var(--text-4)">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
            </svg>
            <img id="fotoPreview" style="width:100%;height:100%;object-fit:cover;display:none" alt="Preview"<?php if (!empty($fotoUrl)): ?> src="<?= htmlspecialchars($fotoUrl) ?>"<?php endif; ?>>
            <div id="fotoZoomOverlay" data-action="ampliar-foto" title="Ampliar foto" style="display:none;position:absolute;inset:0;background:rgba(0,0,0,.45);align-items:center;justify-content:center;cursor:zoom-in;border-radius:10px;opacity:0;transition:opacity .2s">
              <svg width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="#fff" stroke-width="2"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
            </div>
          </div>
          <div style="flex:1;min-width:200px">
            <p style="font-size:13px;color:var(--text-3);margin-bottom:12px">Envie uma foto do colaborador</p>
            <div style="display:flex;gap:8px;flex-wrap:wrap">
              <button type="button" class="btn btn-sm btn-cyan" data-action="trigger-file-input">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                Upload
              </button>
              <button type="button" class="btn btn-sm btn-purple" data-action="abrir-webcam">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z"/></svg>
                Webcam
              </button>
              <button type="button" class="btn btn-sm btn-red" id="btnRemoverFoto" style="display:none" data-action="remover-foto">Remover</button>
            </div>
            <input type="file" id="fileInput" accept="image/*" style="display:none">
          </div>
        </div>
      </div>

      <!-- Dados Basicos -->
      <div style="margin-bottom:24px">
        <h4 style="font-size:14px;font-weight:600;margin:0 0 12px;color:var(--text-1)">Dados Basicos</h4>
        <div class="col2">
          <div class="fg">
            <div class="fl">Nome *</div>
            <input type="text" name="nome" class="fi" placeholder="Nome completo" value="<?= htmlspecialchars($dados['nome'] ?? '') ?>" required/>
          </div>
          <div class="fg">
            <div class="fl">CPF</div>
            <input type="text" name="cpf" class="fi" data-mask="XXX.XXX.XXX-XX" placeholder="000.000.000-00" maxlength="14" value="<?= htmlspecialchars($dados['cpf'] ?? '') ?>"/>
          </div>
        </div>
        <div class="col2">
          <div class="fg">
            <div class="fl">Tipo</div>
            <select name="tipo" class="fi">
              <option value="FUNCIONARIO" <?= ($dados['tipo'] ?? '') === 'FUNCIONARIO' ? 'selected' : '' ?>>Funcionario</option>
              <option value="FREELANCE" <?= ($dados['tipo'] ?? '') === 'FREELANCE' ? 'selected' : '' ?>>Freelance</option>
            </select>
          </div>
          <div class="fg">
            <div class="fl">Origem</div>
            <input type="text" name="origem" class="fi" placeholder="Ex: Indicacao, LinkedIn" value="<?= htmlspecialchars($dados['origem'] ?? '') ?>"/>
          </div>
        </div>
        <div class="col2">
          <div class="fg">
            <div class="fl">Telefone</div>
            <input type="tel" name="telefone" id="telefone" class="fi" data-mask="(XX) XXXX-XXXX" placeholder="(00) 00000-0000" value="<?= htmlspecialchars($dados['telefone'] ?? '') ?>"/>
          </div>
          <div class="fg">
            <div class="fl">Email</div>
            <input type="email" name="email" class="fi" placeholder="email@exemplo.com" value="<?= htmlspecialchars($dados['email'] ?? '') ?>"/>
          </div>
        </div>
        <div class="fg">
          <div class="fl">Atua Como (Função)</div>
          <div style="display:flex;gap:8px;align-items:center">
            <select name="id_funcao" id="atuaComo" class="fi" style="flex:1" data-current-id="<?= (int)($dados['id_funcao'] ?? 0) ?>">
              <option value="">Selecione uma função</option>
            </select>
            <button type="button" title="Gerenciar funções" data-action="open-modal" data-target="modal-funcoes" data-onopen="carregarFuncoesModal" style="flex-shrink:0;display:flex;align-items:center;justify-content:center;width:36px;height:36px;border-radius:8px;border:1px solid var(--bg-border);background:var(--bg-secondary);cursor:pointer;color:var(--text-2);transition:background .15s" onmouseenter="this.style.background='var(--bg-hover)'" onmouseleave="this.style.background='var(--bg-secondary)'">
              <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 011.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.56.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.893.149c-.425.07-.765.383-.93.78-.165.398-.143.854.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 01-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.397.165-.71.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 01-.12-1.45l.527-.737c.25-.35.273-.806.108-1.204-.165-.397-.505-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.108-1.204l-.526-.738a1.125 1.125 0 01.12-1.45l.773-.773a1.125 1.125 0 011.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </button>
          </div>
        </div>
        <div class="fg">
          <div class="fl">Estado para Trabalho</div>
          <?php
          $estadosTrabalho = [
            'Todos' => 'Todos os Estados',
            'AC' => 'Acre (AC)', 'AL' => 'Alagoas (AL)', 'AP' => 'Amapá (AP)',
            'AM' => 'Amazonas (AM)', 'BA' => 'Bahia (BA)', 'CE' => 'Ceará (CE)',
            'DF' => 'Distrito Federal (DF)', 'ES' => 'Espírito Santo (ES)',
            'GO' => 'Goiás (GO)', 'MA' => 'Maranhão (MA)', 'MT' => 'Mato Grosso (MT)',
            'MS' => 'Mato Grosso do Sul (MS)', 'MG' => 'Minas Gerais (MG)',
            'PA' => 'Pará (PA)', 'PB' => 'Paraíba (PB)', 'PR' => 'Paraná (PR)',
            'PE' => 'Pernambuco (PE)', 'PI' => 'Piauí (PI)', 'RJ' => 'Rio de Janeiro (RJ)',
            'RN' => 'Rio Grande do Norte (RN)', 'RS' => 'Rio Grande do Sul (RS)',
            'RO' => 'Rondônia (RO)', 'RR' => 'Roraima (RR)', 'SC' => 'Santa Catarina (SC)',
            'SP' => 'São Paulo (SP)', 'SE' => 'Sergipe (SE)', 'TO' => 'Tocantins (TO)',
          ];
          $estadoAtual = $dados['estado_para_trabalho'] ?? '';
          ?>
          <select name="estado_para_trabalho" class="fi">
            <option value="">Selecione um estado</option>
            <?php foreach ($estadosTrabalho as $uf => $label): ?>
            <option value="<?= $uf ?>" <?= $estadoAtual === $uf ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <!-- Endereco -->
      <div style="margin-bottom:24px">
        <h4 style="font-size:14px;font-weight:600;margin:0 0 12px;color:var(--text-1)">Endereco</h4>
        <div class="col2">
          <div class="fg">
            <div class="fl">CEP</div>
            <input type="text" name="cep" id="cep" class="fi" data-mask="XXXXX-XXX" placeholder="00000-000" maxlength="9" value="<?= htmlspecialchars($dados['cep'] ?? '') ?>"/>
          </div>
          <div class="fg">
            <div class="fl">Estado</div>
            <input type="text" name="estado" id="estado" class="fi" placeholder="UF" maxlength="2" value="<?= htmlspecialchars($dados['estado'] ?? '') ?>"/>
          </div>
        </div>
        <div class="fg">
          <div class="fl">Endereco</div>
          <input type="text" name="endereco" id="endereco" class="fi" placeholder="Rua, Avenida" value="<?= htmlspecialchars($dados['endereco'] ?? '') ?>"/>
        </div>
        <div class="col2">
          <div class="fg">
            <div class="fl">Bairro</div>
            <input type="text" name="bairro" id="bairro" class="fi" placeholder="Bairro" value="<?= htmlspecialchars($dados['bairro'] ?? '') ?>"/>
          </div>
          <div class="fg">
            <div class="fl">Cidade</div>
            <input type="text" name="cidade" id="cidade" class="fi" placeholder="Cidade" value="<?= htmlspecialchars($dados['cidade'] ?? '') ?>"/>
          </div>
        </div>
      </div>

      <!-- Pagamento Pix -->
      <div style="margin-bottom:24px">
        <h4 style="font-size:14px;font-weight:600;margin:0 0 12px;color:var(--text-1)">Pagamento (Pix)</h4>
        <div class="col2">
          <div class="fg">
            <div class="fl">Tipo da Chave Pix</div>
            <select name="tipo_chave_pix" class="fi">
              <option value="">Selecione</option>
              <option value="cpf" <?= ($dados['tipo_chave_pix'] ?? '') === 'cpf' ? 'selected' : '' ?>>CPF</option>
              <option value="cnpj" <?= ($dados['tipo_chave_pix'] ?? '') === 'cnpj' ? 'selected' : '' ?>>CNPJ</option>
              <option value="email" <?= ($dados['tipo_chave_pix'] ?? '') === 'email' ? 'selected' : '' ?>>Email</option>
              <option value="telefone" <?= ($dados['tipo_chave_pix'] ?? '') === 'telefone' ? 'selected' : '' ?>>Telefone</option>
              <option value="aleatoria" <?= ($dados['tipo_chave_pix'] ?? '') === 'aleatoria' ? 'selected' : '' ?>>Aleatoria</option>
            </select>
          </div>
          <div class="fg">
            <div class="fl">Chave Pix</div>
            <input type="text" name="chavepix" class="fi" placeholder="Valor da chave" value="<?= htmlspecialchars($dados['chavepix'] ?? '') ?>"/>
          </div>
        </div>
        <div class="fg" style="margin-top:12px">
          <div class="fl">Dados para Pagamento</div>
          <textarea name="dados_pagamento" class="fi" rows="2" placeholder="Dados para pagamento (Chave PIX ou Conta Corrente - Banco, Agência, Conta)"><?= htmlspecialchars($dados['dados_pagamento'] ?? '') ?></textarea>
        </div>
      </div>

      <!-- Observacao -->
      <div class="fg">
        <div class="fl">Observacao</div>
        <textarea name="observacao" class="fi" rows="3" placeholder="Observacoes sobre o colaborador"><?= htmlspecialchars($dados['observacao'] ?? '') ?></textarea>
      </div>

<!-- MODAL: Gerenciar Funções (Atua Como) -->
<div id="modal-funcoes" class="modal-overlay" data-action="close-modal-outside" data-target="modal-funcoes">
  <div class="modal modal-md">
    <div class="modal-header">
      <div class="modal-icon">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
      </div>
      <div style="flex:1">
        <div class="modal-title">Gerenciar Funções</div>
        <div class="modal-sub">Adicionar, editar ou excluir funções</div>
      </div>
      <button type="button" class="modal-close" data-action="close-modal" data-target="modal-funcoes">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="modal-body">
      <div id="modal-funcoes-list" style="max-height:280px;overflow-y:auto;margin-bottom:16px">
        <div style="text-align:center;padding:16px;color:var(--text-4)">Carregando...</div>
      </div>
      <div class="divider" style="margin:12px 0"></div>
      <div class="fg">
        <div class="fl">Nome da Função</div>
        <input type="text" id="modal-funcao-nome" class="fi" placeholder="Ex: Operação de Câmera" onkeydown="if(event.key==='Enter'){event.preventDefault();salvarFuncao();}"/>
      </div>
      <input type="hidden" id="modal-funcao-id" value=""/>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-gray" data-action="close-modal" data-target="modal-funcoes" data-reset="modal-funcao-nome,modal-funcao-id">Fechar</button>
      <button type="button" class="btn btn-cyan" data-action="salvar-funcao">Salvar</button>
    </div>
  </div>
</div>

<script>
(function() {
  'use strict';

  function escapeHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
  }

  function recarregarSelectAtuaComo(idManter) {
    var sel = document.getElementById('atuaComo');
    if (!sel) return;
    fetch(BASE_URL + '/funcoes')
      .then(function(r) { return r.json(); })
      .then(function(json) {
        if (!json.success || !Array.isArray(json.data)) return;
        var atual = idManter !== undefined ? String(idManter) : sel.value;
        sel.innerHTML = '<option value="">Selecione uma função</option>';
        json.data.forEach(function(f) {
          var opt = document.createElement('option');
          opt.value = f.id;
          opt.textContent = f.nome;
          if (String(f.id) === atual) opt.selected = true;
          sel.appendChild(opt);
        });
      });
  }

  window.carregarFuncoesModal = function() {
    var container = document.getElementById('modal-funcoes-list');
    if (!container) return;
    container.innerHTML = '<div style="text-align:center;padding:16px;color:var(--text-4)">Carregando...</div>';
    fetch(BASE_URL + '/funcoes')
      .then(function(r) { return r.json(); })
      .then(function(json) {
        if (!json.success || !Array.isArray(json.data)) {
          container.innerHTML = '<div style="text-align:center;padding:16px;color:var(--text-4)">Nenhuma função cadastrada</div>';
          return;
        }
        renderizarListaFuncoes(json.data);
      })
      .catch(function() {
        container.innerHTML = '<div style="text-align:center;padding:16px;color:var(--danger)">Erro ao carregar funções</div>';
      });
  };

  function renderizarListaFuncoes(funcoes) {
    var container = document.getElementById('modal-funcoes-list');
    if (!container) return;
    if (funcoes.length === 0) {
      container.innerHTML = '<div style="text-align:center;padding:16px;color:var(--text-4)">Nenhuma função cadastrada</div>';
      return;
    }
    var html = '<table style="width:100%;border-collapse:collapse">';
    html += '<thead><tr style="border-bottom:1px solid var(--bg-border)">';
    html += '<th style="padding:8px;font-size:12px;color:var(--text-3);text-align:left">Função</th>';
    html += '<th style="padding:8px;font-size:12px;color:var(--text-3);text-align:right">Ações</th>';
    html += '</tr></thead><tbody>';
    funcoes.forEach(function(f) {
      html += '<tr style="border-bottom:1px solid var(--bg-border)">';
      html += '<td style="padding:8px;font-size:13px">' + escapeHtml(f.nome) + '</td>';
      html += '<td style="padding:8px;text-align:right">';
      html += '<button type="button" class="btn btn-sm btn-cyan" data-action="editar-funcao" data-id="' + f.id + '" data-nome="' + escapeHtml(f.nome).replace(/'/g, "\\'") + '" style="margin-right:4px">Editar</button>';
      html += '<button type="button" class="btn btn-sm btn-red" data-action="excluir-funcao" data-id="' + f.id + '">Excluir</button>';
      html += '</td></tr>';
    });
    html += '</tbody></table>';
    container.innerHTML = html;
  }

  window.salvarFuncao = function() {
    var nome = document.getElementById('modal-funcao-nome').value.trim();
    var id   = document.getElementById('modal-funcao-id').value;
    if (!nome) { showToast('yellow', 'Atenção', 'Nome da função é obrigatório'); return; }

    var url  = id ? BASE_URL + '/funcoes-colaborador/update/' + id : BASE_URL + '/funcoes-colaborador/store';
    fetch(url, {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: '_csrf_token=' + CSRF_TOKEN + '&nome=' + encodeURIComponent(nome)
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (data.success) {
        showToast('green', 'Sucesso', id ? 'Função atualizada!' : 'Função criada!');
        document.getElementById('modal-funcao-nome').value = '';
        document.getElementById('modal-funcao-id').value   = '';
        carregarFuncoesModal();
        recarregarSelectAtuaComo();
      } else {
        showToast('red', 'Erro', data.message || 'Erro ao salvar função');
      }
    })
    .catch(function() { showToast('red', 'Erro', 'Erro ao processar requisição'); });
  };

  window.editarFuncao = function(id, nome) {
    document.getElementById('modal-funcao-nome').value = nome;
    document.getElementById('modal-funcao-id').value   = id;
    document.getElementById('modal-funcao-nome').focus();
  };

  window.excluirFuncao = function(id) {
    if (!confirm('Excluir esta função?')) return;
    fetch(BASE_URL + '/funcoes-colaborador/delete/' + id, {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: '_csrf_token=' + CSRF_TOKEN
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (data.success) {
        showToast('green', 'Excluído', 'Função excluída com sucesso');
        carregarFuncoesModal();
        recarregarSelectAtuaComo();
      } else {
        showToast('red', 'Erro', data.message || 'Erro ao excluir função');
      }
    })
    .catch(function() { showToast('red', 'Erro', 'Erro ao processar requisição'); });
  };

  // Carrega o select ao iniciar, pré-selecionando o id_funcao do colaborador (edição)
  document.addEventListener('DOMContentLoaded', function() {
    var sel = document.getElementById('atuaComo');
    var currentId = sel ? sel.getAttribute('data-current-id') : '0';
    recarregarSelectAtuaComo(currentId !== '0' ? currentId : undefined);
  });
})();
</script>
