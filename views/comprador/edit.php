<?php
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/input/input.php';
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/button/button.php';
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/alert/alert.php';
require dirname(__DIR__) . '/layout/header.php';
?>

    <section class="section active" id="sec-demandantes-edit">
      <div class="section-header">
        <div class="section-icon">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
          </svg>
        </div>
        <div>
          <div class="section-title">Editar Comprador</div>
          <div class="section-sub">Atualizar dados do comprador</div>
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

      <form method="POST" action="<?= $baseUrl ?>/compradores/update/<?= $demandante['id'] ?>" data-ajax data-redirect="<?= $baseUrl ?>/compradores">
        <input type="hidden" name="_csrf_token" value="<?= \App\Core\Csrf::getToken() ?>"/>

        <div class="card">
          <div class="card-body">

            <div class="fg">
              <div class="fl">Cliente</div>
              <select name="id_cliente" id="id_cliente" class="fi">
                <option value="">Selecione um cliente (opcional)</option>
                <?php foreach ($clientes as $cliente): ?>
                <option value="<?= $cliente['id'] ?>" 
                    <?= ($demandante['id_cliente'] == $cliente['id']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($cliente['nome_fantasia'] ?: $cliente['razao_social']) ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>

            <?= renderInput([
                'type' => 'text',
                'name' => 'nome',
                'id' => 'nome',
                'label' => 'Nome',
                'placeholder' => 'Nome completo do comprador',
                'value' => $demandante['nome'] ?? '',
                'required' => true
            ]) ?>

            <div class="col2">
              <div class="fg">
                <div class="fl">Telefone</div>
                <input type="tel" name="telefone" id="telefone" class="fi" data-mask="(XX) XXXXX-XXXX" value="<?= htmlspecialchars($demandante['telefone'] ?? '') ?>" placeholder="(XX) XXXXX-XXXX">
              </div>

              <?= renderInput([
                  'type' => 'email',
                  'name' => 'email',
                  'id' => 'email',
                  'label' => 'Email',
                  'placeholder' => 'email@exemplo.com',
                  'value' => $demandante['email'] ?? ''
              ]) ?>
            </div>

            <div class="fg">
              <div class="fl">Observação</div>
              <textarea name="observacao" id="observacao" class="fi" rows="4" placeholder="Observações sobre o comprador"><?= htmlspecialchars($demandante['observacao'] ?? '') ?></textarea>
            </div>

          </div>
        </div>

        <div class="mt-20 flex gap-10">
          <button type="submit" class="btn btn-cyan">Salvar</button>
          <a href="<?= $baseUrl ?>/compradores" class="btn btn-gray">Cancelar</a>
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
