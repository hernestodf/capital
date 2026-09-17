<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Confirmar Alocação - <?= htmlspecialchars($alocacao['nome_evento']) ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #0a0a0a;
            color: #e0e0e0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            max-width: 450px;
            width: 100%;
            background: #111;
            border-radius: 16px;
            padding: 28px;
            border: 1px solid #222;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.5);
        }
        h1 {
            font-size: 20px;
            font-weight: 700;
            color: #06b6d4;
            text-align: center;
            margin-bottom: 6px;
            line-height: 1.3;
        }
        .subtitle {
            font-size: 13px;
            color: #888;
            text-align: center;
            margin-bottom: 24px;
        }
        .info-box {
            background: #1a1a1a;
            border-radius: 12px;
            padding: 18px;
            margin-bottom: 24px;
            border-left: 4px solid #06b6d4;
        }
        .info-group {
            margin-bottom: 12px;
        }
        .info-group:last-child {
            margin-bottom: 0;
        }
        .info-label {
            color: #666;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
            display: block;
        }
        .info-value {
            color: #e0e0e0;
            font-size: 14px;
            font-weight: 600;
        }
        .diaria-value {
            color: #10b981;
            font-size: 16px;
            font-weight: 700;
        }
        .warning-box {
            background: #f59e0b11;
            border: 1px solid #f59e0b22;
            border-left: 4px solid #f59e0b;
            border-radius: 8px;
            padding: 14px;
            margin-bottom: 24px;
            font-size: 12px;
            color: #e0a96d;
            line-height: 1.5;
        }
        .warning-title {
            font-weight: 700;
            margin-bottom: 4px;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.5px;
        }
        .actions {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .btn {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            text-align: center;
        }
        .btn-confirm {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
        }
        .btn-confirm:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(16, 185, 129, 0.3);
        }
        .btn-refuse {
            background: #2a1b1b;
            color: #ef4444;
            border: 1px solid #ef444433;
        }
        .btn-refuse:hover:not(:disabled) {
            background: #ef44441a;
        }
        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none !important;
            box-shadow: none !important;
        }
        .result-screen {
            display: none;
            text-align: center;
            padding: 20px 10px;
        }
        .result-icon {
            font-size: 54px;
            margin-bottom: 16px;
            display: inline-block;
            animation: pop 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) both;
        }
        .result-icon.success { color: #10b981; }
        .result-icon.refused { color: #f59e0b; }
        .result-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 12px;
        }
        .result-text {
            font-size: 13px;
            color: #aaa;
            line-height: 1.6;
        }
        @keyframes pop {
            0% { transform: scale(0); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Tela de Escolha -->
        <div id="setup-screen">
            <h1><?= htmlspecialchars($alocacao['nome_evento']) ?></h1>
            <div class="subtitle">Confirmação de Alocação de Trabalho</div>

            <div class="info-box">
                <div class="info-group">
                    <span class="info-label">Colaborador</span>
                    <span class="info-value"><?= htmlspecialchars($alocacao['colaborador_nome']) ?></span>
                </div>
                <div class="info-group">
                    <span class="info-label">Função</span>
                    <span class="info-value"><?= htmlspecialchars($alocacao['funcao']) ?></span>
                </div>
                <div class="info-group">
                    <span class="info-label">Local do Evento</span>
                    <span class="info-value"><?= htmlspecialchars($alocacao['local_evento'] ?? 'A definir') ?></span>
                </div>
                <div class="info-group">
                    <span class="info-label">Período</span>
                    <span class="info-value">
                        <?= date('d/m/Y', strtotime($alocacao['data_inicio'])) ?> a 
                        <?= date('d/m/Y', strtotime($alocacao['data_fim'])) ?>
                    </span>
                </div>
                <div class="info-group">
                    <span class="info-label">Horário de Trabalho</span>
                    <span class="info-value">
                        <?= date('H:i', strtotime($alocacao['hora_inicio'])) ?> às 
                        <?= date('H:i', strtotime($alocacao['hora_fim'])) ?>
                    </span>
                </div>
                <div class="info-group">
                    <span class="info-label">Valor Diária</span>
                    <span class="diaria-value">R$ <?= number_format((float)($alocacao['valor_diaria'] ?? 0), 2, ',', '.') ?></span>
                </div>
            </div>

            <style>
                .term-box {
                    max-height: 220px;
                    overflow-y: auto;
                    background: #161616;
                    border: 1px solid #222;
                    border-radius: 8px;
                    padding: 16px;
                    font-size: 12px;
                    line-height: 1.6;
                    color: #aaa;
                    margin-bottom: 24px;
                    text-align: left;
                }
                .term-title {
                    font-size: 13px;
                    color: #06b6d4;
                    font-weight: 700;
                    text-align: center;
                    border-bottom: 1px solid #222;
                    padding-bottom: 8px;
                    margin-bottom: 12px;
                }
                .term-subtitle {
                    font-size: 11px;
                    font-weight: 700;
                    color: #fff;
                    margin-top: 14px;
                    margin-bottom: 4px;
                    display: block;
                    text-transform: uppercase;
                }
                .term-box p {
                    margin-bottom: 8px;
                    color: #aaa;
                    text-align: left;
                    font-size: 12px;
                }
                .term-box ul {
                    margin: 8px 0;
                    padding-left: 20px;
                }
                .term-box li {
                    margin-bottom: 4px;
                }
            </style>

            <div class="term-box">
                <div class="term-title">TERMO DE DISPONIBILIDADE E MANIFESTAÇÃO DE INTERESSE PARA PRESTAÇÃO DE SERVIÇOS FREELANCER</div>
                <p style="text-align:center;font-weight:bold;margin:8px 0;color:#e0e0e0">PROFOX NETWORKS</p>
                <p>O presente documento tem por finalidade registrar a manifestação de interesse do profissional abaixo identificado em integrar o banco de talentos e profissionais freelancers da PROFOX NETWORKS para futuras oportunidades de prestação de serviços em eventos, feiras, congressos, montagens, desmontagens e demais atividades operacionais.</p>
                
                <strong class="term-subtitle">1. IDENTIFICAÇÃO DO PROFISSIONAL</strong>
                <ul>
                    <li><strong>Nome:</strong> <?= htmlspecialchars($alocacao['colaborador_nome']) ?></li>
                    <li><strong>CPF:</strong> <?= htmlspecialchars($alocacao['cpf'] ?? 'Não informado') ?></li>
                    <li><strong>Telefone:</strong> <?= htmlspecialchars($alocacao['telefone'] ?? 'Não informado') ?></li>
                    <li><strong>E-mail:</strong> <?= htmlspecialchars($alocacao['email'] ?? 'Não informado') ?></li>
                    <li><strong>Cidade/Estado:</strong> <?= htmlspecialchars(trim(($alocacao['cidade'] ?? '') . '/' . ($alocacao['estado'] ?? ''), '/ ')) ?: 'Não informado' ?></li>
                </ul>

                <strong class="term-subtitle">2. MANIFESTAÇÃO DE INTERESSE</strong>
                <p>O profissional declara possuir interesse em prestar serviços como freelancer para a PROFOX NETWORKS, podendo ser consultado para participação em projetos, eventos e atividades operacionais de acordo com sua experiência, disponibilidade e perfil profissional.</p>
                <p>O presente documento não caracteriza contratação, vínculo empregatício, exclusividade ou garantia de convocação para serviços futuros.</p>

                <strong class="term-subtitle">3. DISPONIBILIDADE</strong>
                <p>O profissional compromete-se a informar sua disponibilidade sempre que consultado pela PROFOX NETWORKS para participação em determinado evento ou projeto.</p>
                <p>A confirmação de disponibilidade não implica contratação automática, ficando a efetiva convocação sujeita às necessidades operacionais, critérios técnicos e aprovação da empresa.</p>

                <strong class="term-subtitle">4. DADOS PROFISSIONAIS</strong>
                <p>O profissional declara possuir experiência ou interesse nas seguintes áreas de atuação: <strong><?= htmlspecialchars($alocacao['atua_como'] ?? 'Geral') ?></strong></p>

                <strong class="term-subtitle">5. CONDUTA PROFISSIONAL</strong>
                <p>Quando convocado e confirmado para prestação de serviços, o profissional compromete-se a:</p>
                <ul>
                    <li>cumprir os horários informados pela coordenação;</li>
                    <li>comparecer devidamente uniformizado ou conforme orientação recebida;</li>
                    <li>manter postura profissional perante clientes e parceiros;</li>
                    <li>respeitar normas de segurança e operação;</li>
                    <li>zelar pelos equipamentos e materiais disponibilizados;</li>
                    <li>comunicar previamente qualquer impedimento que possa comprometer sua participação.</li>
                </ul>

                <strong class="term-subtitle">6. PROTEÇÃO DE DADOS E CADASTRO</strong>
                <p>O profissional autoriza a PROFOX NETWORKS a manter seus dados cadastrais em banco de dados interno para fins de recrutamento, seleção, convocação e gestão operacional de eventos.</p>

                <strong class="term-subtitle">7. DECLARAÇÃO</strong>
                <p>Declaro que as informações prestadas são verdadeiras e que possuo interesse em integrar o cadastro de profissionais freelancers da PROFOX NETWORKS, podendo ser consultado para futuras oportunidades de prestação de serviços.</p>
                <p style="margin-bottom:0">Estou ciente de que este documento não gera vínculo empregatício, promessa de contratação ou obrigação de convocação por parte da empresa.</p>
            </div>

            <div class="warning-box">
                <div class="warning-title">Registro de Ponto Diario Obrigatorio</div>
                Nos dias de evento, você receberá e-mails diários para registrar sua **entrada** e **saída**. Este processo é obrigatório e exige o compartilhamento de **localização GPS** e uma **foto** em tempo real usando seu smartphone.
            </div>

            <div class="actions">
                <button type="button" class="btn btn-confirm" id="btn-confirm">
                    Aceitar Termo e Confirmar Presença
                </button>
                <button type="button" class="btn btn-refuse" id="btn-refuse">
                    Não poderei comparecer / Recusar
                </button>
            </div>
        </div>

        <!-- Tela de Sucesso/Recusa -->
        <div id="result-screen" class="result-screen">
            <div id="result-icon" class="result-icon"></div>
            <div id="result-title" class="result-title"></div>
            <div id="result-text" class="result-text"></div>
        </div>
    </div>

    <script>
        (function() {
            var btnConfirm = document.getElementById('btn-confirm');
            var btnRefuse = document.getElementById('btn-refuse');
            var setupScreen = document.getElementById('setup-screen');
            var resultScreen = document.getElementById('result-screen');
            
            var resultIcon = document.getElementById('result-icon');
            var resultTitle = document.getElementById('result-title');
            var resultText = document.getElementById('result-text');

            function enviarDecisao(decisao) {
                btnConfirm.disabled = true;
                btnRefuse.disabled = true;

                if (decisao === 'C') {
                    btnConfirm.textContent = 'Processando...';
                } else {
                    btnRefuse.textContent = 'Processando...';
                }

                var body = 'decisao=' + decisao;

                fetch(window.location.href, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: body
                })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.success) {
                        setupScreen.style.display = 'none';
                        resultScreen.style.display = 'block';

                        if (decisao === 'C') {
                            resultIcon.className = 'result-icon success';
                            resultIcon.innerHTML = '&#10004;';
                            resultTitle.textContent = 'Presença Confirmada!';
                            resultTitle.style.color = '#10b981';
                            resultText.textContent = data.message;
                        } else {
                            resultIcon.className = 'result-icon refused';
                            resultIcon.innerHTML = '&#10008;';
                            resultTitle.textContent = 'Alocação Recusada';
                            resultTitle.style.color = '#ef4444';
                            resultText.textContent = data.message;
                        }
                    } else {
                        alert(data.error || 'Ocorreu um erro ao salvar sua decisão.');
                        btnConfirm.disabled = false;
                        btnRefuse.disabled = false;
                        btnConfirm.textContent = 'Confirmar Presença no Evento';
                        btnRefuse.textContent = 'Não poderei comparecer / Recusar';
                    }
                })
                .catch(function() {
                    alert('Erro de conexão. Verifique sua internet.');
                    btnConfirm.disabled = false;
                    btnRefuse.disabled = false;
                    btnConfirm.textContent = 'Confirmar Presença no Evento';
                    btnRefuse.textContent = 'Não poderei comparecer / Recusar';
                });
            }

            btnConfirm.addEventListener('click', function() {
                enviarDecisao('C');
            });

            btnRefuse.addEventListener('click', function() {
                if (confirm('Tem certeza de que não poderá trabalhar neste evento e deseja recusar o convite?')) {
                    enviarDecisao('R');
                }
            });
        })();
    </script>
</body>
</html>
