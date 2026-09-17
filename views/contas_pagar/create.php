<?php require dirname(__DIR__) . '/layout/header.php'; ?>

<?php
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/input/input.php';
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/alert/alert.php';

$fornecedorOptions = '';
if (!empty($fornecedores)) {
    foreach ($fornecedores as $f) {
        $fornecedorOptions .= '<option value="' . $f['id'] . '">' . htmlspecialchars($f['nome_fantasia'] ?? $f['razao_social'] ?? '-') . '</option>';
    }
}
?>

    <section class="section active" id="sec-contas-pagar-create">
      <div class="section-header">
        <div class="section-icon">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
          </svg>
        </div>
        <div>
          <div class="section-title">Nova Conta a Pagar</div>
          <div class="section-sub">Cadastro de nova conta a pagar</div>
        </div>
      </div>
      <div class="divider"></div>

      <div class="card">
        <div class="card-head">
          <span class="card-title">Dados da Conta</span>
        </div>
        <div class="card-body">
          <?php if (!empty($error)): ?>
          <?= renderAlert(['variant' => 'red', 'title' => 'Erro', 'content' => $error]) ?>
          <?php endif; ?>

          <form method="POST" action="<?= $baseUrl ?>/contas-pagar/store" data-ajax data-redirect="<?= $baseUrl ?>/contas-pagar">
            <input type="hidden" name="_csrf_token" value="<?= \App\Core\Csrf::getToken() ?>"/>

            <div class="fg">
              <div class="fl">Fornecedor</div>
              <select name="id_fornecedor" class="fi">
                <option value="">Selecione um fornecedor (opcional)</option>
                <?= $fornecedorOptions ?>
              </select>
            </div>

            <div class="col2">
              <div class="fg">
                <div class="fl">Numero da NF</div>
                <input type="text" name="numero_nf" class="fi" placeholder="Ex: 12345" value="<?= htmlspecialchars($data['numero_nf'] ?? '') ?>"/>
              </div>
              <div class="fg">
                <div class="fl">Valor</div>
                <input type="text" name="valor" class="fi" placeholder="0,00" value="<?= htmlspecialchars($data['valor'] ?? '') ?>" required/>
              </div>
            </div>

            <div class="fg">
              <div class="fl">Descricao</div>
              <textarea name="descricao" class="fi" rows="3" required><?= htmlspecialchars($data['descricao'] ?? '') ?></textarea>
            </div>

            <div class="col2">
              <div class="fg">
                <div class="fl">Data de Vencimento</div>
                <input type="date" name="data_vencimento" class="fi" value="<?= htmlspecialchars($data['data_vencimento'] ?? '') ?>" required/>
              </div>
              <div class="fg">
                <div class="fl">Observacao</div>
                <input type="text" name="observacao" class="fi" placeholder="Opcional" value="<?= htmlspecialchars($data['observacao'] ?? '') ?>"/>
              </div>
            </div>

            <div style="margin-top:24px;display:flex;gap:12px">
              <button type="submit" class="btn btn-cyan">Salvar</button>
              <a href="<?= $baseUrl ?>/contas-pagar" class="btn btn-gray">Cancelar</a>
            </div>
          </form>
        </div>
      </div>
    </section>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?>
