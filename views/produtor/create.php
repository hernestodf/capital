<?php
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/input/input.php';
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/button/button.php';
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/alert/alert.php';
require dirname(__DIR__) . '/layout/header.php';
?>

    <section class="section active" id="sec-produtores-create">
      <div class="section-header">
        <div class="section-icon">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2.236-3.18a3 3 0 104.472 0M15 12a6 6 0 11-12 0 6 6 0 0112 0z"/>
          </svg>
        </div>
        <div>
          <div class="section-title">Novo Comercial</div>
          <div class="section-sub">Cadastrar contato comercial</div>
        </div>
      </div>
      <div class="divider"></div>

      <?php if (!empty($error)): ?>
      <?= renderAlert([
          'variant' => 'red',
          'title' => 'Erro',
          'message' => $error,
          'dismissible' => true
      ]) ?>
      <?php endif; ?>

      <form method="POST" action="<?= $baseUrl ?>/comercial/store" data-ajax data-redirect="<?= $baseUrl ?>/comercial">
        <input type="hidden" name="_csrf_token" value="<?= \App\Core\Csrf::getToken() ?>"/>

        <div class="card">
          <div class="card-body">

            <?= renderInput([
                'type' => 'text',
                'name' => 'nome',
                'id' => 'nome',
                'label' => 'Nome',
                'placeholder' => 'Nome completo',
                'value' => $old['nome'] ?? '',
                'required' => true
            ]) ?>

            <div class="col2">
              <div class="fg">
                <div class="fl">Telefone</div>
                <input type="tel" name="telefone" id="telefone" class="fi" data-mask="(XX) XXXXX-XXXX" value="<?= htmlspecialchars($old['telefone'] ?? '') ?>" placeholder="(XX) XXXXX-XXXX">
              </div>

              <?= renderInput([
                  'type' => 'email',
                  'name' => 'email',
                  'id' => 'email',
                  'label' => 'Email',
                  'placeholder' => 'email@exemplo.com',
                  'value' => $old['email'] ?? ''
              ]) ?>
            </div>

          </div>
        </div>

        <div style="margin-top:20px;display:flex;gap:10px">
          <button type="submit" class="btn btn-cyan">Salvar</button>
          <a href="<?= $baseUrl ?>/comercial" class="btn btn-gray">Cancelar</a>
        </div>
      </form>
    </section>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Formatacao automatica de telefone
  const telefone = document.getElementById('telefone');
  if (telefone) {
    telefone.addEventListener('input', function(e) {
      let value = e.target.value.replace(/\D/g, '');
      if (value.length > 11) value = value.substring(0, 11);

      if (value.length > 6) {
        e.target.value = '(' + value.substring(0, 2) + ') ' + value.substring(2, 7) + '-' + value.substring(7);
      } else if (value.length > 2) {
        e.target.value = '(' + value.substring(0, 2) + ') ' + value.substring(2);
      } else if (value.length > 0) {
        e.target.value = '(' + value;
      }
    });
  }
});
</script>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?>
