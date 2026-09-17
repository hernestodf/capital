(function() {
  'use strict';

  const MAX_IMAGE_WIDTH = 1024;
  const MAX_IMAGE_HEIGHT = 1024;
  const JPEG_QUALITY = 0.7;
  const WEBCAM_WIDTH = 640;
  const WEBCAM_HEIGHT = 480;

  var stream = null;
  var modalObserver = null;
  var isCapturing = false;

  var els = {};

  function cacheEls() {
    els = {
      preview: document.getElementById('fotoPreview'),
      placeholder: document.getElementById('fotoPlaceholder'),
      fotoPath: document.getElementById('fotoPath'),
      btnRemover: document.getElementById('btnRemoverFoto'),
      fileInput: document.getElementById('fileInput'),
      webcamVideo: document.getElementById('webcamVideo'),
      modalWebcam: document.getElementById('modalWebcam'),
    };
  }

  function init() {
    cacheEls();
    bindFileInput();
    bindModalObserver();
    showExistingPreview();
  }

  function bindFileInput() {
    var input = els.fileInput;
    if (!input) return;
    input.removeEventListener('change', onFileChange);
    input.addEventListener('change', onFileChange);
  }

  function onFileChange(e) {
    var file = e.target.files[0];
    if (!file) return;
    readAndUpload(file);
    e.target.value = '';
  }

  function readAndUpload(file) {
    var reader = new FileReader();
    reader.onload = function(ev) {
      compressImage(ev.target.result, function(compressed) {
        uploadFoto(compressed);
      });
    };
    reader.readAsDataURL(file);
  }

  function compressImage(dataUrl, callback) {
    var img = new Image();
    img.onload = function() {
      var w = img.width;
      var h = img.height;

      if (w > MAX_IMAGE_WIDTH || h > MAX_IMAGE_HEIGHT) {
        var ratio = Math.min(MAX_IMAGE_WIDTH / w, MAX_IMAGE_HEIGHT / h);
        w = Math.round(w * ratio);
        h = Math.round(h * ratio);
      }

      var canvas = document.createElement('canvas');
      canvas.width = w;
      canvas.height = h;
      var ctx = canvas.getContext('2d', { alpha: false, willReadFrequently: false });
      ctx.imageSmoothingEnabled = true;
      ctx.imageSmoothingQuality = 'high';
      ctx.drawImage(img, 0, 0, w, h);

      var quality = JPEG_QUALITY;
      var compressed = canvas.toDataURL('image/jpeg', quality);

      while (compressed.length > 500000 && quality > 0.2) {
        quality -= 0.1;
        compressed = canvas.toDataURL('image/jpeg', quality);
      }

      callback(compressed);
      cleanCanvas(canvas);
    };
    img.src = dataUrl;
  }

  function cleanCanvas(canvas) {
    canvas.width = 0;
    canvas.height = 0;
    var ctx = canvas.getContext('2d');
    ctx.clearRect(0, 0, 0, 0);
  }

  function uploadFoto(base64) {
    showToast('cyan', 'Enviando', 'Salvando foto...');
    fetch(BASE_URL + '/colaboradores/upload-foto', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: '_csrf_token=' + CSRF_TOKEN + '&foto=' + encodeURIComponent(base64)
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
      if (data.success) {
        if (els.fotoPath) els.fotoPath.value = data.path;
        showPreview(base64);
        showToast('green', 'Sucesso', 'Foto salva!');
      } else {
        showToast('red', 'Erro', data.error || 'Erro ao salvar foto');
      }
    })
    .catch(function() {
      showToast('red', 'Erro', 'Erro ao enviar foto');
    });
  }

  function showPreview(dataUrl) {
    if (!els.preview) return;
    els.preview.src = dataUrl;
    els.preview.style.display = 'block';
    if (els.placeholder) els.placeholder.style.display = 'none';
    if (els.btnRemover) els.btnRemover.style.display = 'inline-flex';
  }

  window.removerFoto = function() {
    if (els.fotoPath) els.fotoPath.value = '';
    if (els.preview) {
      els.preview.style.display = 'none';
      els.preview.src = '';
    }
    if (els.placeholder) els.placeholder.style.display = 'block';
    if (els.btnRemover) els.btnRemover.style.display = 'none';
    if (els.fileInput) els.fileInput.value = '';
  };

  window.abrirWebcam = function() {
    if (isCapturing) return;
    openModal('modalWebcam');

    var constraints = {
      video: {
        facingMode: 'user',
        width: { ideal: WEBCAM_WIDTH },
        height: { ideal: WEBCAM_HEIGHT },
      },
      audio: false,
    };

    navigator.mediaDevices.getUserMedia(constraints)
      .then(function(s) {
        stream = s;
        var video = els.webcamVideo;
        if (video) {
          video.srcObject = s;
          video.play().catch(function() {});
        }
      })
      .catch(function(err) {
        var msg = 'Nao foi possivel acessar a webcam';
        if (err.name === 'NotAllowedError') msg = 'Permissao da camera negada. Verifique as configuracoes do navegador.';
        else if (err.name === 'NotFoundError') msg = 'Nenhuma camera encontrada no dispositivo.';
        showToast('red', 'Erro', msg);
        closeModal('modalWebcam');
      });
  };

  window.capturarFoto = function() {
    if (isCapturing) return;
    isCapturing = true;

    var video = els.webcamVideo;
    if (!video || !stream) {
      isCapturing = false;
      return;
    }

    try {
      var w = video.videoWidth || WEBCAM_WIDTH;
      var h = video.videoHeight || WEBCAM_HEIGHT;

      var canvas = document.createElement('canvas');
      canvas.width = w;
      canvas.height = h;
      var ctx = canvas.getContext('2d', { alpha: false, willReadFrequently: false });
      ctx.imageSmoothingEnabled = true;
      ctx.drawImage(video, 0, 0, w, h);

      var base64 = canvas.toDataURL('image/jpeg', JPEG_QUALITY);
      cleanCanvas(canvas);

      compressImage(base64, function(compressed) {
        fecharWebcam();
        uploadFoto(compressed);
        isCapturing = false;
      });
    } catch (e) {
      isCapturing = false;
      showToast('red', 'Erro', 'Erro ao capturar foto');
    }
  };

  window.fecharWebcam = function() {
    if (stream) {
      stream.getTracks().forEach(function(t) { t.stop(); });
      stream = null;
    }
    var video = els.webcamVideo;
    if (video) {
      video.srcObject = null;
      video.load();
    }
    closeModal('modalWebcam');
  };

  function bindModalObserver() {
    if (modalObserver) {
      modalObserver.disconnect();
      modalObserver = null;
    }

    var modal = els.modalWebcam;
    if (!modal || modal.dataset.cameraObserved) return;
    modal.dataset.cameraObserved = '1';

    modalObserver = new MutationObserver(function(muts) {
      muts.forEach(function(m) {
        if (m.type === 'attributes' && m.attributeName === 'class') {
          var isOpen = modal.classList.contains('open');
          if (!isOpen && stream) {
            fecharWebcam();
          }
        }
      });
    });

    modalObserver.observe(modal, { attributes: true });
  }

  function showExistingPreview() {
    if (!els.preview) return;

    var hasSrc = els.preview.getAttribute('src') && els.preview.getAttribute('src') !== '';
    if (hasSrc) {
      els.preview.style.display = 'block';
      if (els.placeholder) els.placeholder.style.display = 'none';
      if (els.btnRemover) els.btnRemover.style.display = 'inline-flex';
      return;
    }

    if (!els.fotoPath || !els.fotoPath.value) return;

    var val = els.fotoPath.value;
    var src;

    if (val.indexOf('data:image') === 0) {
      src = val;
    } else if (val.indexOf('uploads/') === 0) {
      src = BASE_URL + '/' + val;
    } else {
      src = 'data:image/jpeg;base64,' + val;
    }

    function show() { showPreview(src); }
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', show);
    } else {
      show();
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
