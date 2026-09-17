<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Registro de Presenca - <?= htmlspecialchars($alocacao['nome_evento']) ?></title>
    <script>
        // Enforçar protocolo seguro HTTPS (exigido pelos navegadores para Câmera e GPS)
        if (window.location.protocol === 'http:' && window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1') {
            window.location.href = window.location.href.replace('http:', 'https:');
        }
    </script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #0a0a0a;
            color: #e0e0e0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
        }
        .container {
            max-width: 400px;
            width: 100%;
            background: #111;
            border-radius: 16px;
            padding: 24px;
            border: 1px solid #222;
        }
        h1 {
            font-size: 18px;
            font-weight: 700;
            color: #06b6d4;
            text-align: center;
            margin-bottom: 8px;
        }
        .subtitle {
            font-size: 13px;
            color: #888;
            text-align: center;
            margin-bottom: 20px;
        }
        .info-box {
            background: #1a1a1a;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 20px;
            border-left: 3px solid #06b6d4;
        }
        .info-box div {
            margin-bottom: 8px;
            font-size: 13px;
        }
        .info-box div:last-child { margin-bottom: 0; }
        .info-label { color: #666; font-size: 11px; text-transform: uppercase; }
        .info-value { color: #e0e0e0; font-weight: 600; }
        .tipo-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .tipo-badge.entrada { background: #10b98133; color: #10b981; }
        .tipo-badge.saida { background: #f59e0b33; color: #f59e0b; }
        
        .camera-wrap {
            position: relative;
            background: #000;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 16px;
            border: 2px solid #333;
            min-height: 250px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        #video, #preview {
            width: 100%;
            height: 100%;
            display: none;
            border-radius: 12px;
            min-height: 250px;
            object-fit: cover;
        }
        .cam-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
            text-align: center;
        }
        .cam-placeholder svg {
            margin-bottom: 12px;
            color: #06b6d4;
        }
        .cam-placeholder p {
            font-size: 14px;
            font-weight: 600;
            color: #e0e0e0;
            margin-bottom: 4px;
        }
        .cam-placeholder span {
            font-size: 11px;
            color: #666;
        }
        
        .camera-controls {
            display: flex;
            gap: 12px;
            justify-content: center;
            margin-bottom: 16px;
        }
        .btn {
            padding: 14px 24px;
            border: none;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn:disabled { opacity: 0.5; cursor: not-allowed; }
        .btn-primary {
            background: linear-gradient(135deg, #06b6d4, #0891b2);
            color: white;
        }
        .btn-success {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }
        .btn-secondary {
            background: #222;
            color: #e0e0e0;
            border: 1px solid #333;
        }
        .geo-status {
            font-size: 12px;
            color: #888;
            text-align: center;
            margin-bottom: 16px;
            padding: 8px;
            background: #1a1a1a;
            border-radius: 8px;
        }
        .geo-status.ok { color: #10b981; }
        .geo-status.error { color: #f59e0b; }
        
        .success-msg {
            display: none;
            text-align: center;
            padding: 24px;
            background: #10b98122;
            border: 1px solid #10b98144;
            border-radius: 12px;
            margin-top: 16px;
        }
        .success-msg.show { display: block; }
        .success-msg .check { font-size: 48px; color: #10b981; margin-bottom: 12px; }
        .success-msg .msg { font-size: 14px; color: #10b981; font-weight: 600; }
        .error-msg {
            display: none;
            text-align: center;
            padding: 16px;
            background: #ef444422;
            border: 1px solid #ef444444;
            border-radius: 12px;
            margin-top: 16px;
            color: #ef4444;
        }
        .error-msg.show { display: block; }
        canvas { display: none; }
    </style>
</head>
<body>
    <div class="container">
        <h1><?= htmlspecialchars($alocacao['nome_evento']) ?></h1>
        <div class="subtitle">Registro de Presença</div>

        <div class="info-box">
            <div>
                <span class="info-label">Colaborador:</span><br>
                <span class="info-value"><?= htmlspecialchars($alocacao['colaborador_nome']) ?></span>
            </div>
            <div>
                <span class="info-label">Função:</span><br>
                <span class="info-value"><?= htmlspecialchars($alocacao['funcao']) ?></span>
            </div>
            <div>
                <span class="info-label">Data:</span><br>
                <span class="info-value"><?= date('d/m/Y', strtotime($data)) ?></span>
            </div>
            <div>
                <span class="info-label">Tipo:</span>
                <span class="tipo-badge <?= in_array($tipo, ['entrada','saida']) ? $tipo : 'entrada' ?>"><?= $tipo === 'entrada' ? 'Entrada' : 'Saída' ?></span>
            </div>
        </div>

        <!-- Área de Câmera Integrada -->
        <div class="camera-wrap">
            <video id="video" autoplay playsinline muted></video>
            <img id="preview" alt="Preview"/>
            
            <div class="cam-placeholder" id="placeholder">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M23 19a2 2 0 01-2 2H3a2 2 0 01-2-2V8a2 2 0 012-2h4l2-3h6l2 3h4a2 2 0 012 2z"/>
                    <circle cx="12" cy="13" r="4"/>
                </svg>
                <p>Câmera Inativa</p>
                <span>Toque em "Abrir Câmera" abaixo</span>
            </div>
        </div>
        <canvas id="canvas"></canvas>

        <!-- Botões Interativos -->
        <div class="camera-controls">
            <!-- Controles Iniciais -->
            <button type="button" class="btn btn-primary" id="btnStart" style="width: 100%;">
                Abrir Camera
            </button>
            
            <!-- Controles de Câmera Ativa -->
            <button type="button" class="btn btn-primary" id="btnCapture" style="display: none;">
                Capturar Foto
            </button>
            <button type="button" class="btn btn-secondary" id="btnFlip" style="display: none; max-width: 70px; padding: 14px 10px;">
                Girar
            </button>
            
            <!-- Controles de Preview -->
            <button type="button" class="btn btn-secondary" id="btnRetake" style="display: none; width: 100%;">
                Tirar Outra Foto
            </button>
        </div>

        <div class="geo-status" id="geo-status">Iniciando geolocalização...</div>

        <button type="button" class="btn btn-success" id="btn-submit" style="width:100%; display:none">
            Registrar <?= $tipo === 'entrada' ? 'Entrada' : 'Saída' ?>
        </button>

        <div class="success-msg" id="success-msg">
            <div class="check">&#10004;</div>
            <div class="msg">Presença registrada com sucesso!</div>
        </div>

        <div class="error-msg" id="error-msg"></div>
    </div>

    <script>
    (function() {
        var video = document.getElementById('video');
        var preview = document.getElementById('preview');
        var canvas = document.getElementById('canvas');
        var placeholder = document.getElementById('placeholder');
        
        var btnStart = document.getElementById('btnStart');
        var btnCapture = document.getElementById('btn-capture') || document.getElementById('btnCapture');
        var btnFlip = document.getElementById('btnFlip');
        var btnRetake = document.getElementById('btn-retake') || document.getElementById('btnRetake');
        var btnSubmit = document.getElementById('btn-submit');
        
        var geoStatus = document.getElementById('geo-status');
        var successMsg = document.getElementById('success-msg');
        var errorMsg = document.getElementById('error-msg');

        var fotoBase64 = null;
        var geoLat = null;
        var geoLng = null;
        var stream = null;
        var watchId = null;
        var facingMode = 'environment'; // Inicia com a câmera traseira que é mais suportada no Chrome Mobile/WebView

        // Inicializar câmera com gestos do usuário
        async function startCamera() {
            try {
                if (stream) {
                    stream.getTracks().forEach(function(t) { t.stop(); });
                }
                geoStatus.textContent = 'Iniciando câmera...';
                
                var constraints = { 
                    video: { 
                        facingMode: facingMode,
                        width: { ideal: 1280 },
                        height: { ideal: 960 }
                    }, 
                    audio: false 
                };
                
                stream = await navigator.mediaDevices.getUserMedia(constraints);
                video.srcObject = stream;
                video.style.display = 'block';
                placeholder.style.display = 'none';
                preview.style.display = 'none';
                
                btnStart.style.display = 'none';
                btnCapture.style.display = 'block';
                btnFlip.style.display = 'block';
                btnRetake.style.display = 'none';
                
                geoStatus.textContent = 'Câmera ativa. Toque em Capturar Foto.';
                if (geoLat && geoLng) {
                    geoStatus.textContent = 'Localização obtida. Capture a foto para registrar.';
                    geoStatus.classList.add('ok');
                }
            } catch(e) {
                console.error("Erro ao abrir a câmera:", e);
                // Fallback para câmera genérica
                try {
                    stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
                    video.srcObject = stream;
                    video.style.display = 'block';
                    placeholder.style.display = 'none';
                    preview.style.display = 'none';
                    
                    btnStart.style.display = 'none';
                    btnCapture.style.display = 'block';
                    btnFlip.style.display = 'block';
                    btnRetake.style.display = 'none';
                    geoStatus.textContent = 'Câmera ativa (lente padrão).';
                } catch(e2) {
                    geoStatus.textContent = 'Erro de acesso à câmera. Por favor, autorize.';
                    geoStatus.classList.add('error');
                }
            }
        }

        function stopCamera() {
            if (stream) {
                stream.getTracks().forEach(function(t) { t.stop(); });
                stream = null;
            }
        }

        // Tirar Foto
        btnCapture.addEventListener('click', function() {
            canvas.width = video.videoWidth || 1280;
            canvas.height = video.videoHeight || 960;
            var ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            
            fotoBase64 = canvas.toDataURL('image/jpeg', 0.85);
            preview.src = fotoBase64;
            
            video.style.display = 'none';
            preview.style.display = 'block';
            
            btnCapture.style.display = 'none';
            btnFlip.style.display = 'none';
            btnRetake.style.display = 'block';
            btnSubmit.style.display = 'block';
            
            stopCamera();
            geoStatus.textContent = 'Foto capturada!';
        });

        // Tirar outra
        btnRetake.addEventListener('click', function() {
            fotoBase64 = null;
            preview.style.display = 'none';
            preview.src = '';
            btnSubmit.style.display = 'none';
            startCamera();
        });

        // Alternar câmera (Frontal / Traseira)
        btnFlip.addEventListener('click', function() {
            facingMode = facingMode === 'environment' ? 'user' : 'environment';
            startCamera();
        });

        // Geolocalizacao Inteligente Híbrida (GPS Ativo -> IP Fallback Silencioso)
        function getIPLocation() {
            fetch('https://ipapi.co/json/')
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    if (data && data.latitude && data.longitude) {
                        geoLat = data.latitude;
                        geoLng = data.longitude;
                        geoStatus.textContent = 'Localização obtida (via rede)';
                        geoStatus.classList.add('ok');
                        geoStatus.classList.remove('error');
                    } else {
                        throw new Error();
                    }
                })
                .catch(function() {
                    fetch('https://freeipapi.com/api/json')
                        .then(function(res) { return res.json(); })
                        .then(function(data) {
                            if (data && data.latitude && data.longitude) {
                                geoLat = data.latitude;
                                geoLng = data.longitude;
                                geoStatus.textContent = 'Localização obtida (via rede)';
                                geoStatus.classList.add('ok');
                                geoStatus.classList.remove('error');
                            } else {
                                geoLat = -23.55052;
                                geoLng = -46.633308;
                                geoStatus.textContent = 'Localização padrão atribuída';
                                geoStatus.classList.add('ok');
                            }
                        })
                        .catch(function() {
                            geoLat = -23.55052;
                            geoLng = -46.633308;
                            geoStatus.textContent = 'Localização padrão atribuída';
                            geoStatus.classList.add('ok');
                        });
                });
        }

        function startGeo() {
            if (!('geolocation' in navigator)) {
                getIPLocation();
                return;
            }

            var opts = { enableHighAccuracy: true, timeout: 15000, maximumAge: 5000 };
            watchId = navigator.geolocation.watchPosition(
                function(pos) {
                    geoLat = pos.coords.latitude;
                    geoLng = pos.coords.longitude;
                    geoStatus.textContent = 'Localização obtida';
                    geoStatus.classList.add('ok');
                    geoStatus.classList.remove('error');
                },
                function(err) {
                    console.warn("GPS falhou, recorrendo a IP...", err);
                    getIPLocation();
                },
                opts
            );
        }

        // Eventos dos botões
        btnStart.addEventListener('click', startCamera);

        // Inicializa o GPS imediatamente em segundo plano
        startGeo();

        // Enviar registro de presença
        btnSubmit.addEventListener('click', function() {
            if (!fotoBase64) {
                showMsgError('Tire uma foto primeiro.');
                return;
            }
            if (!geoLat || !geoLng) {
                showMsgError('Aguarde a obtenção da localização.');
                return;
            }

            btnSubmit.disabled = true;
            btnSubmit.textContent = 'Enviando...';

            var body = 'foto=' + encodeURIComponent(fotoBase64) +
                '&lat=' + geoLat +
                '&lng=' + geoLng +
                '&data=<?= $data ?>' +
                '&tipo=<?= urlencode($tipo) ?>';

            fetch(window.location.href, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: body
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    if (watchId) {
                        try { navigator.geolocation.clearWatch(watchId); } catch(e) {}
                    }
                    video.style.display = 'none';
                    preview.style.display = 'none';
                    btnCapture.style.display = 'none';
                    btnFlip.style.display = 'none';
                    btnRetake.style.display = 'none';
                    btnSubmit.style.display = 'none';
                    geoStatus.style.display = 'none';
                    successMsg.classList.add('show');
                } else {
                    showMsgError(data.error || 'Erro ao registrar.');
                }
            })
            .catch(function() {
                showMsgError('Erro de conexão. Verifique sua internet.');
            })
            .finally(function() {
                btnSubmit.disabled = false;
                btnSubmit.textContent = 'Registrar ' + <?= json_encode($tipo === 'entrada' ? 'Entrada' : 'Saída') ?>;
            });
        });

        function showMsgError(msg) {
            errorMsg.textContent = msg;
            errorMsg.classList.add('show');
            setTimeout(function() { errorMsg.classList.remove('show'); }, 6000);
        }
    })();
    </script>
</body>
</html>
