<?php require dirname(__DIR__) . '/layout/header.php'; ?>

<?php
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/input/input.php';
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/modal/modal.php';
$dados = $colaborador ?? [];
$fotoPath = $dados['foto'] ?? '';
$fotoUrl = !empty($fotoPath) ? $baseUrl . '/colaboradores/foto/' . $dados['id'] : '';
?>

    <section class="section active" id="sec-colaboradores">
      <div class="section-header">
        <div class="section-icon">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
          </svg>
        </div>
        <div>
          <div class="section-title">Editar Colaborador</div>
          <div class="section-sub">Atualizar dados do colaborador</div>
        </div>
      </div>
      <div class="divider"></div>

      <?php if (!empty($error)): ?>
      <?= renderAlert(['variant' => 'red', 'title' => 'Erro', 'content' => $error]) ?>
      <?php endif; ?>

      <div class="card">
        <div class="card-head">
          <span class="card-title">Dados do Colaborador</span>
        </div>
        <div class="card-body">
          <form method="POST" action="<?= $baseUrl ?>/colaboradores/update/<?= $dados['id'] ?>" id="formColaborador" data-ajax data-redirect="<?= $baseUrl ?>/colaboradores">
            <input type="hidden" name="_csrf_token" value="<?= \App\Core\Csrf::getToken() ?>">
            <input type="hidden" name="foto" id="fotoPath" value="<?= htmlspecialchars($fotoPath) ?>">

            <?php require __DIR__ . '/_form.php'; ?>

            <div style="margin-top:24px;display:flex;gap:8px">
              <button type="submit" class="btn btn-cyan">Atualizar Colaborador</button>
              <a href="<?= $baseUrl ?>/colaboradores" class="btn btn-gray">Cancelar</a>
            </div>
          </form>
        </div>
      </div>
    </section>

<!-- LIGHTBOX FOTO -->
<div id="lightboxFoto" data-action="fechar-lightbox" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.88);z-index:9999;align-items:center;justify-content:center;cursor:zoom-out">
  <button data-action="fechar-lightbox" data-stop-propagation style="position:absolute;top:18px;right:18px;background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.25);border-radius:50%;width:40px;height:40px;color:#fff;font-size:1.1rem;cursor:pointer;display:flex;align-items:center;justify-content:center;line-height:1" title="Fechar">✕</button>
  <img id="lightboxImg" src="" alt="Foto do colaborador" style="max-width:90vw;max-height:90vh;border-radius:12px;object-fit:contain;box-shadow:0 24px 80px rgba(0,0,0,.6)">
</div>

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
// ── Lightbox ──────────────────────────────────────────────────
function ampliarFoto() {
  var img = document.getElementById('fotoPreview');
  if (!img || img.style.display === 'none') return;
  var lb = document.getElementById('lightboxFoto');
  document.getElementById('lightboxImg').src = img.src;
  lb.style.display = 'flex';
  document.body.style.overflow = 'hidden';
}
function fecharLightbox() {
  var lb = document.getElementById('lightboxFoto');
  if (lb) lb.style.display = 'none';
  document.body.style.overflow = '';
}
document.addEventListener('keydown', function(e) { if (e.key === 'Escape') fecharLightbox(); });

// ── Overlay de zoom ────────────────────────────────────────────
(function() {
  function setupOverlay() {
    var container = document.getElementById('fotoPreviewContainer');
    if (!container) return;

    // Garante position:relative no container (pode vir sem do _form.php cacheado)
    container.style.position = 'relative';

    // Cria overlay se não existir no HTML
    var ol = document.getElementById('fotoZoomOverlay');
    if (!ol) {
      ol = document.createElement('div');
      ol.id = 'fotoZoomOverlay';
      ol.innerHTML = '<svg width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="#fff" stroke-width="2">'
        + '<circle cx="11" cy="11" r="8"/>'
        + '<path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35"/>'
        + '<line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/>'
        + '</svg>';
      container.appendChild(ol);
    }

    // Estilo sempre via JS (independente do HTML renderizado)
    ol.style.cssText = 'display:none;position:absolute;top:0;left:0;right:0;bottom:0;'
      + 'background:rgba(0,0,0,.45);align-items:center;justify-content:center;'
      + 'cursor:zoom-in;opacity:0;transition:opacity .2s;z-index:10;border-radius:10px';
    ol.onclick = ampliarFoto;

    // Hover no container
    container.addEventListener('mouseenter', function() {
      var img = document.getElementById('fotoPreview');
      if (!img || img.style.display === 'none') return;
      ol.style.display = 'flex';
      requestAnimationFrame(function() { ol.style.opacity = '1'; });
    });
    container.addEventListener('mouseleave', function() {
      ol.style.opacity = '0';
      setTimeout(function() { ol.style.display = 'none'; }, 200);
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', setupOverlay);
  } else {
    setupOverlay();
  }
})();
</script>


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
