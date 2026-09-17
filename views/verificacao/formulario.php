<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?> - <?= htmlspecialchars($colaborador['nome'] ?? '') ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 500px;
            width: 100%;
            padding: 40px;
        }
        .logo {
            text-align: center;
            margin-bottom: 24px;
        }
        .logo svg {
            width: 48px;
            height: 48px;
            color: #667eea;
        }
        h1 {
            font-size: 24px;
            font-weight: 700;
            color: #1a202c;
            text-align: center;
            margin-bottom: 8px;
        }
        .subtitle {
            text-align: center;
            color: #718096;
            font-size: 14px;
            margin-bottom: 32px;
        }
        .user-info {
            background: #f7fafc;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 24px;
        }
        .user-info p {
            font-size: 14px;
            color: #4a5568;
            margin-bottom: 4px;
        }
        .user-info strong {
            color: #2d3748;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 8px;
        }
        .form-group small {
            display: block;
            font-size: 12px;
            color: #718096;
            margin-top: 4px;
        }
        .upload-area {
            border: 2px dashed #cbd5e0;
            border-radius: 8px;
            padding: 32px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        .upload-area:hover {
            border-color: #667eea;
            background: #f7fafc;
        }
        .upload-area.has-preview {
            border-style: solid;
            border-color: #48bb78;
        }
        .upload-area svg {
            width: 48px;
            height: 48px;
            color: #a0aec0;
            margin-bottom: 12px;
        }
        .upload-area p {
            color: #718096;
            font-size: 14px;
        }
        #preview {
            max-width: 100%;
            max-height: 200px;
            border-radius: 8px;
            margin-top: 12px;
            display: none;
        }
        .geo-status {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            padding: 12px;
            background: #f7fafc;
            border-radius: 8px;
        }
        .geo-status.success {
            background: #f0fff4;
            color: #22543d;
        }
        .geo-status.error {
            background: #fff5f5;
            color: #c53030;
        }
        .geo-status.loading {
            background: #ebf8ff;
            color: #2b6cb0;
        }
        .geo-status svg {
            width: 16px;
            height: 16px;
            flex-shrink: 0;
        }
        .btn {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .btn-primary:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        .btn-primary:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 14px;
            margin-bottom: 20px;
        }
        .alert-error {
            background: #fff5f5;
            color: #c53030;
            border: 1px solid #feb2b2;
        }
        .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin-right: 8px;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>

        <h1>Confirmação de Cadastro</h1>
        <p class="subtitle">Envie sua foto para confirmar seu cadastro</p>

        <?php if (!empty($error)): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="user-info">
            <p><strong>Nome:</strong> <?= htmlspecialchars($colaborador['nome']) ?></p>
            <?php if (!empty($colaborador['email'])): ?>
            <p><strong>Email:</strong> <?= htmlspecialchars($colaborador['email']) ?></p>
            <?php endif; ?>
        </div>

        <form method="POST" id="formVerificacao">
            <input type="hidden" name="foto" id="fotoBase64">
            <input type="hidden" name="lat" id="lat">
            <input type="hidden" name="lng" id="lng">

            <div class="form-group">
                <label>Sua Foto</label>
                <div class="upload-area" id="uploadArea" data-action="trigger-file-input">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <p>Clique para tirar uma foto ou enviar</p>
                    <img id="preview" alt="Preview">
                </div>
                <input type="file" id="fileInput" accept="image/*" capture="user" style="display:none">
                <small>Permita o acesso à câmera quando solicitado</small>
            </div>

            <div class="form-group">
                <label>Localização</label>
                <div class="geo-status loading" id="geoStatus">
                    <svg class="spinner" style="border-color:rgba(43,108,176,0.3);border-top-color:#2b6cb0" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="2" stroke-dasharray="31.4" stroke-dashoffset="10"/>
                    </svg>
                    <span>Obtendo localização...</span>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" id="btnSubmit" disabled>
                Enviar e Confirmar Cadastro
            </button>
        </form>
    </div>

    <script>
        let fotoCapturada = false;
        let geoCapturada = false;

        // Preview da foto
        document.getElementById('fileInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function(event) {
                const preview = document.getElementById('preview');
                const uploadArea = document.getElementById('uploadArea');
                preview.src = event.target.result;
                preview.style.display = 'block';
                uploadArea.classList.add('has-preview');
                document.getElementById('fotoBase64').value = event.target.result;
                fotoCapturada = true;
                checkForm();
            };
            reader.readAsDataURL(file);
        });

        // Geolocalização
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    document.getElementById('lat').value = position.coords.latitude;
                    document.getElementById('lng').value = position.coords.longitude;
                    geoCapturada = true;

                    document.getElementById('geoStatus').className = 'geo-status success';
                    document.getElementById('geoStatus').innerHTML = '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg><span>Localização obtida com sucesso</span>';
                    checkForm();
                },
                function(error) {
                    document.getElementById('geoStatus').className = 'geo-status error';
                    document.getElementById('geoStatus').innerHTML = '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg><span>Não foi possível obter a localização. Você pode enviar mesmo assim.</span>';
                    // Permite enviar sem localização
                    geoCapturada = true;
                    checkForm();
                },
                { enableHighAccuracy: false, timeout: 25000, maximumAge: 60000 }
            );
        } else {
            document.getElementById('geoStatus').className = 'geo-status error';
            document.getElementById('geoStatus').innerHTML = '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg><span>Geolocalização não suportada neste navegador</span>';
            geoCapturada = true;
            checkForm();
        }

        function checkForm() {
            document.getElementById('btnSubmit').disabled = !(fotoCapturada && geoCapturada);
        }

        // Submit do formulário
        document.getElementById('formVerificacao').addEventListener('submit', function(e) {
            e.preventDefault();

            const btn = document.getElementById('btnSubmit');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner"></span> Enviando...';

            this.submit();
        });
    </script>
</body>
</html>
