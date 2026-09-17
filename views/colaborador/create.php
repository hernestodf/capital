<?php require dirname(__DIR__) . '/layout/header.php'; ?>

<?php
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/input/input.php';
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/modal/modal.php';
$dados = $data ?? [];
$fotoPath = $dados['foto'] ?? '';
$fotoUrl = !empty($fotoPath) ? $baseUrl . '/colaboradores/foto/' . ($dados['id'] ?? '') : '';
?>

    <section class="section active" id="sec-colaboradores">
      <div class="section-header">
        <div class="section-icon">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
          </svg>
        </div>
        <div>
          <div class="section-title">Novo Colaborador</div>
          <div class="section-sub">Cadastrar funcionario ou freelance</div>
        </div>
      </div>
      <div class="divider"></div>

      <?php if (!empty($error)): ?>
      <?= renderAlert(['variant' => 'red', 'message' => $error]) ?>
      <?php endif; ?>

      <div class="card">
        <div class="card-head">
          <span class="card-title">Dados do Colaborador</span>
        </div>
        <div class="card-body">
          <form method="POST" action="<?= $baseUrl ?>/colaboradores/store" id="formColaborador" data-ajax data-redirect="<?= $baseUrl ?>/colaboradores">
            <input type="hidden" name="_csrf_token" value="<?= \App\Core\Csrf::getToken() ?>">
            <input type="hidden" name="foto" id="fotoPath" value="<?= htmlspecialchars($fotoPath) ?>">

            <?php require __DIR__ . '/_form.php'; ?>

            <div style="margin-top:24px;display:flex;gap:8px">
              <button type="submit" class="btn btn-cyan">Salvar Colaborador</button>
              <a href="<?= $baseUrl ?>/colaboradores" class="btn btn-gray">Cancelar</a>
            </div>
          </form>
        </div>
      </div>
    </section>

<!-- MODAL WEBCAM -->
<?= renderModal([
    'id' => 'modalWebcam',
    'variant' => 'form',
    'title' => 'Capturar Foto via Webcam',
    'body' => '<div style="text-align:center">
                 <video id="webcamVideo" autoplay playsinline muted style="width:100%;max-width:400px;border-radius:8px;background:#000"></video>
                 <div style="margin-top:16px;display:flex;gap:8px;justify-content:center">
                   <button type="button" class="btn btn-cyan" data-action="capturar-foto">
                     <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="3"/><path d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                     Capturar
                   </button>
                   <button type="button" class="btn btn-gray" data-action="fechar-webcam">Cancelar</button>
                 </div>
               </div>'
]) ?>

<script src="<?= $baseUrl ?>/js/modules/colaborador-camera.js"></script>


<script>
document.addEventListener('DOMContentLoaded', function() {
  // Mascara de data-mask agora e global (scripts.js) -- ver bug-043.

  var cepInput = document.getElementById('cep');
  if (cepInput) {
    cepInput.addEventListener('blur', function(e) {
      var cep = e.target.value.replace(/\D/g, '');
      if (cep.length === 8) {
        fetch(BASE_URL + '/proxy/cep/' + cep)
          .then(function(res) { return res.json(); })
          .then(function(data) {
            if (!data.erro) {
              if (data.logradouro) document.getElementById('endereco').value = data.logradouro;
              if (data.bairro) document.getElementById('bairro').value = data.bairro;
              if (data.localidade) document.getElementById('cidade').value = data.localidade;
              if (data.uf) document.getElementById('estado').value = data.uf;
              showToast('green', 'CEP Encontrado', data.logradouro + ' - ' + data.bairro);
            }
          })
          .catch(function() {});
      }
    });
  }
});
</script>
<?php require dirname(__DIR__) . '/layout/footer.php'; ?>
