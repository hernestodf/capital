<?php
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/modal/modal.php';
require dirname(__DIR__) . '/layout/header.php';

// Prepara opções de categoria para o modal de subcategoria
$categoriaOptionsModal = '';
foreach ($categorias as $cat) {
    $categoriaOptionsModal .= '<option value="' . $cat['id'] . '">' . htmlspecialchars($cat['categoria']) . '</option>';
}
?>

    <section class="section active" id="sec-fornecedores-edit">
      <div class="section-header">
        <div class="section-icon">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
          </svg>
        </div>
        <div>
          <div class="section-title">Editar Fornecedor</div>
          <div class="section-sub"><?= htmlspecialchars($fornecedor['nome_fantasia'] ?? $fornecedor['razao_social'] ?? 'Fornecedor') ?></div>
        </div>
        <div style="margin-left:auto;display:flex;gap:8px">
          <button type="button" class="btn btn-sm btn-cyan" data-action="open-modal" data-target="modal-categoria">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            + Categoria
          </button>
          <button type="button" class="btn btn-sm btn-cyan" data-action="open-modal" data-target="modal-subcategoria">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            + Subcategoria
          </button>
        </div>
      </div>
      <div class="divider"></div>

      <?php if (!empty($error)): ?>
      <div class="alert red" style="margin-bottom:16px">
        <div class="alert-body"><div class="alert-title">Erro</div><div class="alert-desc"><?= htmlspecialchars($error) ?></div></div>
      </div>
      <?php endif; ?>

      <form method="POST" action="<?= $baseUrl ?>/fornecedores/update/<?= $fornecedor['id'] ?>" data-ajax data-redirect="<?= $baseUrl ?>/fornecedores">
        <input type="hidden" name="_csrf_token" value="<?= \App\Core\Csrf::getToken() ?>"/>

        <!-- ═══ CATEGORIA ═══════════════════════════════════ -->
        <div class="card">
          <div class="card-head"><span class="card-title">Categoria</span></div>
          <div class="card-body">

            <div class="fg" style="margin-bottom:12px">
              <div class="fl">Categoria</div>
              <select name="id_categoria" id="id_categoria" class="fi" onchange="carregarSubcategorias(this.value)">
                <option value="">Selecione uma categoria</option>
                <?php foreach ($categorias as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= ($fornecedor['id_categoria'] ?? '') == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['categoria']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col2">
              <div class="fg">
                <div class="fl">Subcategoria</div>
                <select name="id_subcategoria" id="id_subcategoria" class="fi">
                  <option value="">Selecione uma subcategoria</option>
                  <?php foreach ($subcategorias as $sub): ?>
                  <option value="<?= $sub['id'] ?>" <?= ($fornecedor['id_subcategoria'] ?? '') == $sub['id'] ? 'selected' : '' ?>><?= htmlspecialchars($sub['subcategoria']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="fg">
                <div class="fl">Telefone da Empresa</div>
                <input type="tel" name="telefone" class="fi phone-mask"
                       value="<?= htmlspecialchars($fornecedor['telefone'] ?? '') ?>" placeholder="(XX) XXXXX-XXXX">
              </div>
            </div>

          </div>
        </div>

        <!-- ═══ IDENTIFICAÇÃO ════════════════════════════════ -->
        <div class="card" style="margin-top:16px">
          <div class="card-head"><span class="card-title">Identificação</span></div>
          <div class="card-body">

            <div class="col2">
              <div class="fg">
                <div class="fl">CNPJ/CPF</div>
                <input type="text" name="cpf_cnpj" id="cpf_cnpj" class="fi" data-mask="XX.XXX.XXX/XXXX-XX"
                       value="<?= htmlspecialchars($fornecedor['cpf_cnpj'] ?? '') ?>" placeholder="CNPJ ou CPF">
              </div>
              <div class="fg">
                <div class="fl">Inscrição Estadual</div>
                <input type="text" name="inscricao_estadual" class="fi"
                       value="<?= htmlspecialchars($fornecedor['inscricao_estadual'] ?? '') ?>" placeholder="IE ou ISENTO">
              </div>
            </div>

            <div class="fg">
              <div class="fl">Nome Fantasia</div>
              <input type="text" name="nome_fantasia" id="nome_fantasia" class="fi"
                     value="<?= htmlspecialchars($fornecedor['nome_fantasia'] ?? '') ?>" placeholder="Nome fantasia">
            </div>

            <div class="fg">
              <div class="fl">Razão Social</div>
              <input type="text" name="razao_social" id="razao_social" class="fi"
                     value="<?= htmlspecialchars($fornecedor['razao_social'] ?? '') ?>" placeholder="Razão social">
            </div>

          </div>
        </div>

        <!-- ═══ CONTATO FINANCEIRO ═══════════════════════════ -->
        <div class="card" style="margin-top:16px">
          <div class="card-head"><span class="card-title">Contato Financeiro</span></div>
          <div class="card-body">

            <p style="font-size:12px;color:var(--text-muted,#888);margin:0 0 12px">Responsável pelo financeiro / pagamentos</p>

            <div class="col3">
              <div class="fg">
                <div class="fl">Nome</div>
                <input type="text" name="fin_nome" class="fi"
                       value="<?= htmlspecialchars($fornecedor['fin_nome'] ?? '') ?>" placeholder="Nome do responsável">
              </div>
              <div class="fg">
                <div class="fl">Telefone</div>
                <input type="tel" name="fin_telefone" class="fi phone-mask"
                       value="<?= htmlspecialchars($fornecedor['fin_telefone'] ?? '') ?>" placeholder="(XX) XXXXX-XXXX">
              </div>
              <div class="fg">
                <div class="fl">E-mail</div>
                <input type="email" name="fin_email" class="fi"
                       value="<?= htmlspecialchars($fornecedor['fin_email'] ?? '') ?>" placeholder="financeiro@empresa.com">
              </div>
            </div>

            <div class="col3" style="margin-top:10px;padding-top:10px;border-top:1px dashed var(--border,#e2e8f0)">
              <div class="fg">
                <div class="fl" style="color:var(--text-muted,#888)">Nome <span style="font-size:11px">(2º contato)</span></div>
                <input type="text" name="fin_nome2" class="fi"
                       value="<?= htmlspecialchars($fornecedor['fin_nome2'] ?? '') ?>" placeholder="Nome do 2º responsável">
              </div>
              <div class="fg">
                <div class="fl" style="color:var(--text-muted,#888)">Telefone <span style="font-size:11px">(2º)</span></div>
                <input type="tel" name="fin_telefone2" class="fi phone-mask"
                       value="<?= htmlspecialchars($fornecedor['fin_telefone2'] ?? '') ?>" placeholder="(XX) XXXXX-XXXX">
              </div>
              <div class="fg">
                <div class="fl" style="color:var(--text-muted,#888)">E-mail <span style="font-size:11px">(2º)</span></div>
                <input type="email" name="fin_email2" class="fi"
                       value="<?= htmlspecialchars($fornecedor['fin_email2'] ?? '') ?>" placeholder="financeiro2@empresa.com">
              </div>
            </div>

          </div>
        </div>

        <!-- ═══ CONTATO COMERCIAL ════════════════════════════ -->
        <div class="card" style="margin-top:16px">
          <div class="card-head"><span class="card-title">Contato Comercial</span></div>
          <div class="card-body">

            <p style="font-size:12px;color:var(--text-muted,#888);margin:0 0 12px">Responsável pelo comercial / vendas</p>

            <div class="col3">
              <div class="fg">
                <div class="fl">Comercial</div>
                <input type="text" name="com_nome" class="fi"
                       value="<?= htmlspecialchars($fornecedor['com_nome'] ?? '') ?>" placeholder="Nome do contato comercial">
              </div>
              <div class="fg">
                <div class="fl">Telefone</div>
                <input type="tel" name="com_telefone" class="fi phone-mask"
                       value="<?= htmlspecialchars($fornecedor['com_telefone'] ?? '') ?>" placeholder="(XX) XXXXX-XXXX">
              </div>
              <div class="fg">
                <div class="fl">E-mail</div>
                <input type="email" name="com_email" class="fi"
                       value="<?= htmlspecialchars($fornecedor['com_email'] ?? '') ?>" placeholder="comercial@empresa.com">
              </div>
            </div>

            <div class="col3" style="margin-top:10px;padding-top:10px;border-top:1px dashed var(--border,#e2e8f0)">
              <div class="fg">
                <div class="fl" style="color:var(--text-muted,#888)">Nome <span style="font-size:11px">(2º contato)</span></div>
                <input type="text" name="com_nome2" class="fi"
                       value="<?= htmlspecialchars($fornecedor['com_nome2'] ?? '') ?>" placeholder="Nome do 2º responsável">
              </div>
              <div class="fg">
                <div class="fl" style="color:var(--text-muted,#888)">Telefone <span style="font-size:11px">(2º)</span></div>
                <input type="tel" name="com_telefone2" class="fi phone-mask"
                       value="<?= htmlspecialchars($fornecedor['com_telefone2'] ?? '') ?>" placeholder="(XX) XXXXX-XXXX">
              </div>
              <div class="fg">
                <div class="fl" style="color:var(--text-muted,#888)">E-mail <span style="font-size:11px">(2º)</span></div>
                <input type="email" name="com_email2" class="fi"
                       value="<?= htmlspecialchars($fornecedor['com_email2'] ?? '') ?>" placeholder="comercial2@empresa.com">
              </div>
            </div>

          </div>
        </div>

        <!-- ═══ PRESENÇA ONLINE ═══════════════════════════════ -->
        <div class="card" style="margin-top:16px">
          <div class="card-head"><span class="card-title">Presença Online</span></div>
          <div class="card-body">

            <div class="col2">
              <div class="fg">
                <div class="fl">Site</div>
                <input type="text" name="site" class="fi"
                       value="<?= htmlspecialchars($fornecedor['site'] ?? '') ?>" placeholder="www.empresa.com.br">
              </div>
              <div class="fg">
                <div class="fl">Instagram</div>
                <input type="text" name="instagram" class="fi"
                       value="<?= htmlspecialchars($fornecedor['instagram'] ?? '') ?>" placeholder="@empresa">
              </div>
            </div>

          </div>
        </div>

        <!-- ═══ ENDEREÇO ══════════════════════════════════════ -->
        <div class="card" style="margin-top:16px">
          <div class="card-head"><span class="card-title">Endereço</span></div>
          <div class="card-body">

            <div class="fg">
              <div class="fl">CEP</div>
              <input type="text" name="cep" id="cep" class="fi" data-mask="XXXXX-XXX"
                     value="<?= htmlspecialchars($fornecedor['cep'] ?? '') ?>" placeholder="XXXXX-XXX">
            </div>

            <div class="fg">
              <div class="fl">Endereço</div>
              <input type="text" name="endereco" id="endereco" class="fi"
                     value="<?= htmlspecialchars($fornecedor['endereco'] ?? '') ?>" placeholder="Rua, avenida, etc">
            </div>

            <div class="col2">
              <div class="fg">
                <div class="fl">Número</div>
                <input type="text" name="numero" id="numero" class="fi"
                       value="<?= htmlspecialchars($fornecedor['numero'] ?? '') ?>" placeholder="S/N">
              </div>
              <div class="fg">
                <div class="fl">Complemento</div>
                <input type="text" name="complemento" id="complemento" class="fi"
                       value="<?= htmlspecialchars($fornecedor['complemento'] ?? '') ?>" placeholder="Apto, sala, etc">
              </div>
            </div>

            <div class="col2">
              <div class="fg">
                <div class="fl">Bairro</div>
                <input type="text" name="bairro" id="bairro" class="fi"
                       value="<?= htmlspecialchars($fornecedor['bairro'] ?? '') ?>" placeholder="Bairro">
              </div>
              <div class="fg">
                <div class="fl">Cidade</div>
                <input type="text" name="cidade" id="cidade" class="fi"
                       value="<?= htmlspecialchars($fornecedor['cidade'] ?? '') ?>" placeholder="Cidade">
              </div>
            </div>

            <div class="col2">
              <div class="fg">
                <div class="fl">Estado</div>
                <select name="estado" id="estado" class="fi">
                  <option value="">Selecione</option>
                  <?php
                  $ufs = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'];
                  foreach ($ufs as $uf): ?>
                  <option value="<?= $uf ?>" <?= ($fornecedor['estado'] ?? '') == $uf ? 'selected' : '' ?>><?= $uf ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="fg">
                <div class="fl">Estado para Trabalho</div>
                <select name="estado_para_trabalho" id="estado_para_trabalho" class="fi">
                  <option value="">Selecione</option>
                  <option value="Todos" <?= ($fornecedor['estado_para_trabalho'] ?? '') === 'Todos' ? 'selected' : '' ?>>Todos</option>
                  <option value="MG"    <?= ($fornecedor['estado_para_trabalho'] ?? '') === 'MG'    ? 'selected' : '' ?>>Minas Gerais (MG)</option>
                  <option value="MT"    <?= ($fornecedor['estado_para_trabalho'] ?? '') === 'MT'    ? 'selected' : '' ?>>Mato Grosso (MT)</option>
                  <option value="BA"    <?= ($fornecedor['estado_para_trabalho'] ?? '') === 'BA'    ? 'selected' : '' ?>>Bahia (BA)</option>
                  <option value="GO"    <?= ($fornecedor['estado_para_trabalho'] ?? '') === 'GO'    ? 'selected' : '' ?>>Goiás (GO)</option>
                  <option value="DF"    <?= ($fornecedor['estado_para_trabalho'] ?? '') === 'DF'    ? 'selected' : '' ?>>Distrito Federal (DF)</option>
                </select>
              </div>
            </div>

          </div>
        </div>

        <!-- ═══ OBSERVAÇÕES ═══════════════════════════════════ -->
        <div class="card" style="margin-top:16px">
          <div class="card-body">

            <div class="fg">
              <div class="fl">Observações internas</div>
              <textarea name="observacao" id="observacao" class="fi" rows="3"
                        placeholder="Notas internas sobre o fornecedor"><?= htmlspecialchars($fornecedor['observacao'] ?? '') ?></textarea>
            </div>

            <div class="fg" style="margin-top:12px">
              <div class="fl">Dados para Pagamento</div>
              <textarea name="dados_pagamento" id="dados_pagamento" class="fi" rows="3"
                        placeholder="Dados para pagamento (Chave PIX ou Conta Corrente - Banco, Agência, Conta)"><?= htmlspecialchars($fornecedor['dados_pagamento'] ?? '') ?></textarea>
            </div>

            <div class="btn-row" style="margin-top:16px">
              <a href="<?= $baseUrl ?>/fornecedores" class="btn btn-gray">Cancelar</a>
              <button type="submit" class="btn btn-cyan" id="btn-save-fornecedor">Salvar</button>
            </div>

          </div>
        </div>

      </form>
    </section>

    <!-- MODAL: Gerenciar Categorias -->
    <?= renderModal([
        'id'       => 'modal-categoria',
        'variant'  => 'form',
        'title'    => 'Gerenciar Categorias',
        'subtitle' => 'Adicionar, editar ou excluir categorias',
        'body'     => '<div id="modal-categoria-list" style="max-height:260px;overflow-y:auto;margin-bottom:12px"></div>
                       <div class="divider" style="margin:8px 0"></div>
                       <div class="fg"><div class="fl">Nome da Categoria</div><input type="text" id="modal-categoria-nome" class="fi" placeholder="Ex: Materiais de Escritório"/></div>
                       <input type="hidden" id="modal-categoria-id" value=""/>',
        'footer'   => '<button class="btn btn-gray" data-action="close-modal" data-target="modal-categoria">Fechar</button><button class="btn btn-cyan" data-action="save-categoria">Salvar</button>',
    ]) ?>

    <!-- MODAL: Gerenciar Subcategorias -->
    <?= renderModal([
        'id'       => 'modal-subcategoria',
        'variant'  => 'form',
        'title'    => 'Gerenciar Subcategorias',
        'subtitle' => 'Adicionar, editar ou excluir subcategorias',
        'body'     => '<div id="modal-subcategoria-list" style="max-height:260px;overflow-y:auto;margin-bottom:12px"></div>
                       <div class="divider" style="margin:8px 0"></div>
                       <div class="fg"><div class="fl">Categoria</div><select id="modal-subcategoria-categoria" class="fi" onchange="carregarSubcategoriasModal()"><option value="">Selecione uma categoria</option>' . $categoriaOptionsModal . '</select></div>
                       <div class="fg" style="margin-top:12px"><div class="fl">Nome da Subcategoria</div><input type="text" id="modal-subcategoria-nome" class="fi" placeholder="Ex: Papel Sulfite"/></div>
                       <input type="hidden" id="modal-subcategoria-id" value=""/>',
        'footer'   => '<button class="btn btn-gray" data-action="close-modal" data-target="modal-subcategoria">Fechar</button><button class="btn btn-cyan" data-action="save-subcategoria">Salvar</button>',
    ]) ?>

<style>
.col3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; }
@media (max-width: 768px) { .col3 { grid-template-columns: 1fr; } }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Mascara de data-mask (CNPJ/CEP) agora e global (scripts.js) -- ver bug-043.

  // Máscara de telefone dinâmica
  document.querySelectorAll('.phone-mask').forEach(function(input) {
    input.addEventListener('input', function(e) {
      let v = e.target.value.replace(/\D/g, '').substring(0, 11);
      if (v.length >= 11)     e.target.value = '(' + v.substring(0,2) + ') ' + v.substring(2,7) + '-' + v.substring(7);
      else if (v.length >= 7) e.target.value = '(' + v.substring(0,2) + ') ' + v.substring(2,6) + '-' + v.substring(6);
      else if (v.length >= 3) e.target.value = '(' + v.substring(0,2) + ') ' + v.substring(2);
      else if (v.length > 0)  e.target.value = '(' + v;
    });
  });

  // Busca CEP
  document.querySelector('[name="cep"]').addEventListener('blur', function(e) {
    var cep = e.target.value.replace(/\D/g, '');
    if (cep.length === 8) {
      fetch(BASE_URL + '/proxy/cep/' + cep)
        .then(function(res) { return res.json(); })
        .then(function(data) {
          if (!data.erro) {
            var el = function(n) { return document.querySelector('[name="' + n + '"]'); };
            if (el('endereco'))  el('endereco').value = data.logradouro || '';
            if (el('bairro'))    el('bairro').value   = data.bairro     || '';
            if (el('cidade'))    el('cidade').value   = data.localidade || '';
            if (el('estado'))    el('estado').value   = data.uf         || '';
          }
        })
        .catch(function() {});
    }
  });

  // ─── Helpers CNPJ ────────────────────────────────────────
  function formatTel(raw) {
    var v = (raw || '').replace(/\D/g, '');
    if (v.length === 11) return '(' + v.substring(0,2) + ') ' + v.substring(2,7) + '-' + v.substring(7);
    if (v.length === 10) return '(' + v.substring(0,2) + ') ' + v.substring(2,6) + '-' + v.substring(6);
    return v.length ? v : '';
  }
  function setField(name, val) {
    var el = document.querySelector('[name="' + name + '"]');
    if (el && val) el.value = val;
  }

  // Busca CNPJ (via proxy interno — evita bloqueio CSP)
  document.querySelector('[name="cpf_cnpj"]').addEventListener('blur', function(e) {
    var cpfCnpj = e.target.value.replace(/\D/g, '');
    if (cpfCnpj.length === 14) {
      showToast('cyan', 'Buscando', 'Consultando CNPJ na API...');
      fetch(BASE_URL + '/proxy/cnpj/' + cpfCnpj)
        .then(function(res) {
          if (res.status === 404) throw new Error('CNPJ não encontrado');
          if (res.status === 429) throw new Error('Muitas requisições. Aguarde e tente novamente.');
          if (!res.ok) throw new Error('Erro na API: HTTP ' + res.status);
          return res.json();
        })
        .then(function(data) {
          if (data.razao_social || data.nome_fantasia) {

            // ── Identificação ──────────────────────────────
            var cnpj = (data.cnpj || cpfCnpj).replace(/\D/g, '');
            if (cnpj.length === 14) {
              cnpj = cnpj.substring(0,2)+'.'+cnpj.substring(2,5)+'.'+cnpj.substring(5,8)+'/'+cnpj.substring(8,12)+'-'+cnpj.substring(12);
            }
            setField('cpf_cnpj',     cnpj);
            setField('razao_social',  data.razao_social  || '');
            setField('nome_fantasia', data.nome_fantasia || data.razao_social || '');

            // ── Telefone da empresa (campo principal) ──────
            var tel1 = formatTel(data.ddd_telefone_1);
            var tel2 = formatTel(data.ddd_telefone_2);
            setField('telefone', tel1);

            // ── Contato Financeiro ─────────────────────────
            var qsa = data.qsa || [];
            if (qsa.length > 0 && qsa[0].nome_socio) {
              setField('fin_nome', qsa[0].nome_socio);
            }
            setField('fin_telefone', tel1);
            if (data.email) setField('fin_email', data.email);

            if (tel2) {
              setField('fin_telefone2', tel2);
              if (qsa.length > 1 && qsa[1].nome_socio) {
                setField('fin_nome2', qsa[1].nome_socio);
              }
            }

            // ── Endereço ────────────────────────────────────
            var cep = (data.cep || '').replace(/\D/g, '');
            if (cep.length === 8) cep = cep.substring(0,5) + '-' + cep.substring(5);
            setField('cep',         cep);
            setField('endereco',    data.logradouro  || '');
            setField('numero',      data.numero      || '');
            setField('complemento', data.complemento || '');
            setField('bairro',      data.bairro      || '');
            setField('cidade',      data.municipio   || '');
            setField('estado',      data.uf          || '');

            var preenchidos = ['Razão Social', 'Nome Fantasia', 'CNPJ', 'Endereço'];
            if (tel1)       preenchidos.push('Telefone');
            if (data.email) preenchidos.push('E-mail financeiro');
            if (qsa.length) preenchidos.push('Responsável financeiro');
            if (tel2)       preenchidos.push('2º Telefone');

            showToast('green', 'CNPJ encontrado!', preenchidos.join(', ') + ' preenchidos automaticamente.');
          } else {
            showToast('yellow', 'Atenção', 'CNPJ encontrado mas sem dados. Preencha manualmente.');
          }
        })
        .catch(function(err) {
          showToast('yellow', 'Atenção', err.message || 'CNPJ não encontrado. Preencha manualmente.');
        });
    }
  });
});

// Carregar subcategorias
function carregarSubcategorias(idCategoria) {
  const selectSub = document.getElementById('id_subcategoria');
  selectSub.innerHTML = '<option value="">Selecione uma subcategoria</option>';
  if (!idCategoria) return;

  fetch(BASE_URL + '/fornecedores/api/subcategorias/' + idCategoria)
    .then(function(r) {
      if (r.status === 401) { window.location.href = BASE_URL + '/auth/login'; throw new Error('Unauthorized'); }
      return r.json();
    })
    .then(function(data) {
      if (data.success && data.data) {
        data.data.forEach(function(sub) {
          const opt = document.createElement('option');
          opt.value = sub.id; opt.textContent = sub.subcategoria;
          selectSub.appendChild(opt);
        });
      }
    })
    .catch(function(err) { console.error('Erro ao carregar subcategorias:', err); });
}

// ─── CRUD Categoria (modal) ───────────────────────────────
function carregarCategoriasModal() {
  fetch(BASE_URL + '/categorias/list')
    .then(r => r.json())
    .then(data => { if (data && data.success && data.data) renderizarListaCategorias(data.data); })
    .catch(err => console.error('Erro ao carregar categorias:', err));
}

function renderizarListaCategorias(categorias) {
  const container = document.getElementById('modal-categoria-list');
  if (!container) return;
  if (categorias.length === 0) {
    container.innerHTML = '<div style="text-align:center;padding:16px;color:var(--text-4)">Nenhuma categoria cadastrada</div>';
    return;
  }
  let html = '<table style="width:100%;border-collapse:collapse">';
  html += '<thead><tr style="border-bottom:1px solid var(--bg-border);text-align:left">';
  html += '<th style="padding:8px;font-size:12px;color:var(--text-3)">ID</th>';
  html += '<th style="padding:8px;font-size:12px;color:var(--text-3)">Nome</th>';
  html += '<th style="padding:8px;font-size:12px;color:var(--text-3);text-align:right">Ações</th>';
  html += '</tr></thead><tbody>';
  categorias.forEach(function(cat) {
    html += '<tr style="border-bottom:1px solid var(--bg-border)">';
    html += '<td style="padding:8px;font-size:13px">' + cat.id + '</td>';
    html += '<td style="padding:8px;font-size:13px">' + escapeHtml(cat.categoria) + '</td>';
    html += '<td style="padding:8px;text-align:right">';
    html += '<button type="button" class="btn btn-sm btn-cyan" data-action="edit-categoria" data-id="' + cat.id + '" data-nome="' + escapeHtml(cat.categoria) + '" style="margin-right:4px">Editar</button>';
    html += '<button type="button" class="btn btn-sm btn-red" data-action="delete-categoria" data-id="' + cat.id + '">Excluir</button>';
    html += '</td></tr>';
  });
  html += '</tbody></table>';
  container.innerHTML = html;
}

function salvarCategoria() {
  const nome = document.getElementById('modal-categoria-nome').value.trim();
  const id   = document.getElementById('modal-categoria-id').value;
  if (!nome) { showToast('yellow', 'Atenção', 'Nome da categoria é obrigatório'); return; }
  const url  = id ? BASE_URL + '/categorias/update/' + id : BASE_URL + '/categorias/store';
  fetch(url, {
    method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: '_csrf_token=' + CSRF_TOKEN + '&categoria=' + encodeURIComponent(nome)
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      showToast('green', 'Sucesso', id ? 'Categoria atualizada!' : 'Categoria criada!');
      document.getElementById('modal-categoria-nome').value = '';
      document.getElementById('modal-categoria-id').value   = '';
      carregarCategoriasModal();
      // Atualiza select principal do formulário
      const select = document.getElementById('id_categoria');
      if (id) {
        const opt = select.querySelector('option[value="' + id + '"]');
        if (opt) opt.textContent = nome;
      } else if (data.data && data.data.id) {
        const opt = document.createElement('option');
        opt.value = data.data.id; opt.textContent = nome;
        select.appendChild(opt);
      }
      atualizarSelectCategoriasModal();
    } else { showToast('red', 'Erro', data.message || 'Erro ao salvar categoria'); }
  })
  .catch(() => showToast('red', 'Erro', 'Erro ao processar requisição'));
}

function editarCategoria(id, nome) {
  document.getElementById('modal-categoria-nome').value = nome;
  document.getElementById('modal-categoria-id').value   = id;
  document.getElementById('modal-categoria-nome').focus();
}

function excluirCategoria(id) {
  if (!confirm('Tem certeza que deseja excluir esta categoria?')) return;
  fetch(BASE_URL + '/categorias/delete/' + id, {
    method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: '_csrf_token=' + CSRF_TOKEN
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      showToast('green', 'Excluído', 'Categoria excluída com sucesso');
      carregarCategoriasModal();
      const opt = document.getElementById('id_categoria').querySelector('option[value="' + id + '"]');
      if (opt) opt.remove();
      atualizarSelectCategoriasModal();
    } else { showToast('red', 'Erro', data.message || 'Erro ao excluir categoria'); }
  })
  .catch(() => showToast('red', 'Erro', 'Erro ao processar requisição'));
}

// ─── CRUD Subcategoria (modal) ────────────────────────────
function carregarSubcategoriasModal() {
  const idCategoria = document.getElementById('modal-subcategoria-categoria').value;
  const container   = document.getElementById('modal-subcategoria-list');
  if (!idCategoria) {
    container.innerHTML = '<div style="text-align:center;padding:16px;color:var(--text-4)">Selecione uma categoria para ver as subcategorias</div>';
    return;
  }
  fetch(BASE_URL + '/subcategorias/list/' + idCategoria)
    .then(r => r.json())
    .then(data => { if (data && data.success && data.data) renderizarListaSubcategorias(data.data); })
    .catch(err => console.error('Erro ao carregar subcategorias:', err));
}

function renderizarListaSubcategorias(subcategorias) {
  const container = document.getElementById('modal-subcategoria-list');
  if (!container) return;
  if (subcategorias.length === 0) {
    container.innerHTML = '<div style="text-align:center;padding:16px;color:var(--text-4)">Nenhuma subcategoria cadastrada para esta categoria</div>';
    return;
  }
  let html = '<table style="width:100%;border-collapse:collapse">';
  html += '<thead><tr style="border-bottom:1px solid var(--bg-border);text-align:left">';
  html += '<th style="padding:8px;font-size:12px;color:var(--text-3)">ID</th>';
  html += '<th style="padding:8px;font-size:12px;color:var(--text-3)">Nome</th>';
  html += '<th style="padding:8px;font-size:12px;color:var(--text-3);text-align:right">Ações</th>';
  html += '</tr></thead><tbody>';
  subcategorias.forEach(function(sub) {
    const catId = sub.id_categoria || document.getElementById('modal-subcategoria-categoria').value;
    html += '<tr style="border-bottom:1px solid var(--bg-border)">';
    html += '<td style="padding:8px;font-size:13px">' + sub.id + '</td>';
    html += '<td style="padding:8px;font-size:13px">' + escapeHtml(sub.subcategoria) + '</td>';
    html += '<td style="padding:8px;text-align:right">';
    html += '<button type="button" class="btn btn-sm btn-cyan" data-action="edit-subcategoria" data-id="' + sub.id + '" data-nome="' + escapeHtml(sub.subcategoria) + '" data-cat-id="' + catId + '" style="margin-right:4px">Editar</button>';
    html += '<button type="button" class="btn btn-sm btn-red" data-action="delete-subcategoria" data-id="' + sub.id + '">Excluir</button>';
    html += '</td></tr>';
  });
  html += '</tbody></table>';
  container.innerHTML = html;
}

function salvarSubcategoria() {
  const idCategoria = document.getElementById('modal-subcategoria-categoria').value;
  const nome        = document.getElementById('modal-subcategoria-nome').value.trim();
  const id          = document.getElementById('modal-subcategoria-id').value;
  if (!idCategoria) { showToast('yellow', 'Atenção', 'Selecione uma categoria'); return; }
  if (!nome)        { showToast('yellow', 'Atenção', 'Nome da subcategoria é obrigatório'); return; }
  const url = id ? BASE_URL + '/subcategorias/update/' + id : BASE_URL + '/subcategorias/store';
  fetch(url, {
    method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: '_csrf_token=' + CSRF_TOKEN + '&id_categoria=' + idCategoria + '&subcategoria=' + encodeURIComponent(nome)
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      showToast('green', 'Sucesso', id ? 'Subcategoria atualizada!' : 'Subcategoria criada!');
      document.getElementById('modal-subcategoria-nome').value = '';
      document.getElementById('modal-subcategoria-id').value   = '';
      carregarSubcategoriasModal();
      // Atualiza select do formulário se mesma categoria selecionada
      const formCat = document.getElementById('id_categoria').value;
      if (formCat == idCategoria) carregarSubcategorias(formCat);
    } else { showToast('red', 'Erro', data.message || 'Erro ao salvar subcategoria'); }
  })
  .catch(() => showToast('red', 'Erro', 'Erro ao processar requisição'));
}

function editarSubcategoria(id, nome, idCategoria) {
  document.getElementById('modal-subcategoria-nome').value      = nome;
  document.getElementById('modal-subcategoria-categoria').value = idCategoria;
  document.getElementById('modal-subcategoria-id').value        = id;
  document.getElementById('modal-subcategoria-nome').focus();
}

function excluirSubcategoria(id) {
  if (!confirm('Tem certeza que deseja excluir esta subcategoria?')) return;
  fetch(BASE_URL + '/subcategorias/delete/' + id, {
    method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: '_csrf_token=' + CSRF_TOKEN
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      showToast('green', 'Excluído', 'Subcategoria excluída com sucesso');
      carregarSubcategoriasModal();
      const formCat = document.getElementById('id_categoria').value;
      const catId   = document.getElementById('modal-subcategoria-categoria').value;
      if (formCat == catId) carregarSubcategorias(formCat);
    } else { showToast('red', 'Erro', data.message || 'Erro ao excluir subcategoria'); }
  })
  .catch(() => showToast('red', 'Erro', 'Erro ao processar requisição'));
}

function atualizarSelectCategoriasModal() {
  fetch(BASE_URL + '/categorias/list')
    .then(r => r.json())
    .then(data => {
      if (data && data.success && data.data) {
        const selectPrincipal = document.getElementById('id_categoria');
        const selectModal     = document.getElementById('modal-subcategoria-categoria');
        if (!selectModal) return;
        const vModal = selectModal.value, vPrincipal = selectPrincipal.value;
        selectModal.innerHTML = '<option value="">Selecione uma categoria</option>';
        selectPrincipal.innerHTML = '<option value="">Selecione uma categoria</option>';
        data.data.forEach(function(cat) {
          [selectModal, selectPrincipal].forEach(function(sel, i) {
            const opt = document.createElement('option');
            opt.value = cat.id; opt.textContent = cat.categoria;
            if (cat.id == (i === 0 ? vModal : vPrincipal)) opt.selected = true;
            sel.appendChild(opt);
          });
        });
      }
    })
    .catch(err => console.error('Erro ao atualizar categorias no modal:', err));
}

function escapeHtml(text) {
  const map = {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'};
  return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
}

// MutationObservers — carregar dados ao abrir modais
const modalCatEl = document.getElementById('modal-categoria');
if (modalCatEl) {
  new MutationObserver(function(m) {
    m.forEach(function(mut) {
      if (mut.type === 'attributes' && mut.attributeName === 'class' && modalCatEl.classList.contains('open')) {
        carregarCategoriasModal();
      }
    });
  }).observe(modalCatEl, { attributes: true });
}

const modalSubEl = document.getElementById('modal-subcategoria');
if (modalSubEl) {
  new MutationObserver(function(m) {
    m.forEach(function(mut) {
      if (mut.type === 'attributes' && mut.attributeName === 'class' && modalSubEl.classList.contains('open')) {
        atualizarSelectCategoriasModal();
        carregarSubcategoriasModal();
      }
    });
  }).observe(modalSubEl, { attributes: true });
}

// Registrado via window.registerActions (nao document.addEventListener) pra nao duplicar
// o dispatch de clique: scripts.js ja delega [data-action] globalmente em document.body
// e cai num fallback window[camelCase(action)] pra acoes nao reconhecidas — registrar
// aqui E TAMBEM ouvir 'click' localmente disparava cada acao 2x (bug-XXX). 'open-modal'
// e 'close-modal' ja sao tratados direto pelo handler global.
document.addEventListener('DOMContentLoaded', function() {
  window.registerActions({
    'save-categoria': function() { salvarCategoria(); },
    'save-subcategoria': function() { salvarSubcategoria(); },
    'edit-categoria': function(el) { editarCategoria(el.dataset.id, el.dataset.nome); },
    'delete-categoria': function(el) { excluirCategoria(el.dataset.id); },
    'edit-subcategoria': function(el) { editarSubcategoria(el.dataset.id, el.dataset.nome, el.dataset.catId); },
    'delete-subcategoria': function(el) { excluirSubcategoria(el.dataset.id); },
  });
});
</script>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?>
