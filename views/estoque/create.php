<?php require dirname(__DIR__) . '/layout/header.php'; ?>

<?php
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/alert/alert.php';

$secaoOptions = '';
if (!empty($secoes)) {
    foreach ($secoes as $sec) {
        $secaoOptions .= '<option value="' . $sec['id'] . '">' . htmlspecialchars($sec['secao']) . '</option>';
    }
}
?>

    <section class="section active" id="sec-estoque-create">
      <div class="section-header">
        <div class="section-icon">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
          </svg>
        </div>
        <div>
          <div class="section-title">Novo Produto</div>
          <div class="section-sub">Cadastrar novo produto no estoque</div>
        </div>
      </div>
      <div class="divider"></div>

      <?php if (isset($_GET['error'])): ?>
      <?= renderAlert(['variant' => 'red', 'title' => 'Erro', 'msg' => htmlspecialchars(urldecode($_GET['error']))]) ?>
      <?php endif; ?>

      <div class="card">
        <div class="card-head">
          <span class="card-title">Dados do Produto</span>
        </div>
        <div class="card-body">
          <form method="POST" action="<?= $baseUrl ?>/estoque/store" data-ajax data-redirect="<?= $baseUrl ?>/estoque">
            <input type="hidden" name="_csrf_token" value="<?= \App\Core\Csrf::getToken() ?>">
            
              <div class="fg">
                <div class="fl">Nome do Produto</div>
                <input type="text" name="produto" class="fi" placeholder="Ex: Furadeira Bosch" required/>
              </div>

            <div class="col2">
              <div class="fg">
                <div class="fl">Custo (R$)</div>
                <input type="text" id="custo-input" name="custo" class="fi" placeholder="0,00"/>
              </div>
              <div class="fg">
                <div class="fl">Secao</div>
                <select name="id_secao" class="fi">
                  <option value="">Selecione uma secao</option>
                  <?= $secaoOptions ?>
                </select>
              </div>
            </div>

            <div class="fg">
              <div class="fl">Pode ser Locado?</div>
              <select name="pode_ser_locado" class="fi">
                <option value="S">Sim</option>
                <option value="N" selected>Nao</option>
              </select>
            </div>

            <div class="fg">
              <div class="fl">Observacao</div>
              <textarea name="observacao" class="fi" rows="3" placeholder="Observacoes sobre o produto"></textarea>
            </div>

            <div class="divider" style="margin-top:16px"></div>
            <div style="display:flex;gap:8px;justify-content:flex-end">
              <a href="<?= $baseUrl ?>/estoque" class="btn btn-gray">Cancelar</a>
              <button type="submit" class="btn btn-cyan">Salvar Produto</button>
            </div>
          </form>
        </div>
      </div>
    </section>

<script>
// Currency mask for custo
document.addEventListener('DOMContentLoaded', function() {
  const custoInput = document.getElementById('custo-input');
  if (!custoInput) return;

  custoInput.addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value === '') {
      e.target.value = '';
      return;
    }
    value = (parseInt(value) / 100).toFixed(2);
    value = value.replace('.', ',');
    value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    e.target.value = value;
  });
});
</script>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?>
