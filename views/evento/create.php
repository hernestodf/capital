<?php
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/forms/form-evento.php';
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/alert/alert.php';
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/button/button.php';
require dirname(__DIR__) . '/layout/header.php';

$csrfToken = \App\Core\Csrf::getToken();
?>

    <section class="section active" id="sec-eventos-create">
      <div class="section-header">
        <div class="section-icon">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
          </svg>
        </div>
        <div>
          <div class="section-title">Novo Evento</div>
          <div class="section-sub">Cadastrar evento</div>
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

      <?php
      echo renderFormEvento([
          'clientes'      => $clientes,
          'produtores'    => $produtores,
          'demandantes'   => $demandantes,
          'usuarios'      => $usuarios,
          'evento'        => [],
          'action'        => 'javascript:void(0)',
          'csrf'          => $csrfToken,
          'showSeparacao' => false,
      ]);
      ?>

      <div style="margin-top:20px;display:flex;gap:10px">
        <button type="button" class="btn btn-cyan" id="btn-save-evento" data-action="salvar-evento">Salvar</button>
        <a href="<?= $baseUrl ?>/eventos" class="btn btn-gray">Cancelar</a>
      </div>
    </section>

<script>
function salvarEvento() {
  var form = document.getElementById('form-evento');
  if (!form) return;

  var btn = document.getElementById('btn-save-evento');
  if (btn) {
    btn.disabled = true;
    btn.textContent = 'Salvando...';
  }

  var formData = new FormData(form);

  fetch(BASE_URL + '/eventos/store', {
    method: 'POST',
    body: formData
  })
  .then(function(r) { return r.json(); })
  .then(function(data) {
    if (btn) {
      btn.disabled = false;
      btn.textContent = 'Salvar';
    }
    if (data.success) {
      showToast('green', 'Sucesso', 'Evento criado com sucesso!');
      setTimeout(function() {
        window.location.href = BASE_URL + '/eventos/edit/' + data.id;
      }, 1500);
    } else if (data.error) {
      showToast('red', 'Erro', data.error);
    } else {
      showToast('red', 'Erro', 'Erro ao criar evento.');
    }
  })
  .catch(function(err) {
    if (btn) {
      btn.disabled = false;
      btn.textContent = 'Salvar';
    }
    showToast('red', 'Erro', 'Erro de conexão ao servidor.');
    console.error(err);
  });
}
</script>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?>
