/**
 * Cotacao JavaScript - Tela dedicada de cotacao de fornecedores
 * Modulo: Eventos > Fornecedores > Cotacao
 */
(function() {
    'use strict';

    // BASE_URL será definido no DOMContentLoaded quando o footer já tiver carregado
    var BASE_URL = '';
    var data = {};

    // ===================== MARCAR VENCEDOR =====================

    window.marcarVencedor = function(evt) {
        var fornecedorSelect = document.getElementById('vencedor-fornecedor');
        var custoInput = document.getElementById('vencedor-custo');
        var fornecedorId = fornecedorSelect.value;
        var custo = parseFloat(custoInput.value);

        if (!fornecedorId) {
            showToast('red', 'Erro', 'Selecione um fornecedor');
            return;
        }
        if (isNaN(custo) || custo < 0) {
            showToast('red', 'Erro', 'Informe um custo valido');
            return;
        }

        // Encontrar cotacao ID do fornecedor selecionado
        var cotacaoId = null;
        var propostas = data.propostas || [];
        for (var i = 0; i < propostas.length; i++) {
            if (propostas[i].id_fornecedor == fornecedorId) {
                cotacaoId = propostas[i].id;
                break;
            }
        }

        if (!cotacaoId) {
            // Fornecedor sem proposta — criar proposta automatica e marcar como vencedor
            var btn = evt.target.closest('button');
            var formDataCriar = new FormData();
            formDataCriar.append('_csrf_token', data.csrfToken);
            formDataCriar.append('id_produto_evento', data.idProdutoEvento);
            formDataCriar.append('id_fornecedor', fornecedorId);
            formDataCriar.append('valor_proposto', custo);

            btn.disabled = true;
            btn.textContent = 'Salvando...';

            fetch(BASE_URL + '/cotacao/propostas', {
                method: 'POST',
                headers: {'X-Requested-With': 'XMLHttpRequest'},
                credentials: 'same-origin',
                body: formDataCriar
            })
            .then(function(r) { return r.json(); })
            .then(function(d) {
                if (d.success && d.id) {
                    // Proposta criada, agora marcar como vencedor
                    chamarMarcarVencedor(d.id, custo, btn);
                } else {
                    showToast('red', 'Erro', d.error || 'Erro ao criar proposta');
                    btn.disabled = false;
                    btn.innerHTML = '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Marcar como Vencedor';
                }
            })
            .catch(function() {
                showToast('red', 'Erro', 'Erro de conexao');
                btn.disabled = false;
                btn.innerHTML = '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Marcar como Vencedor';
            });
            return;
        }

        chamarMarcarVencedor(cotacaoId, custo, evt.target.closest('button'));
    };

    function chamarMarcarVencedor(cotacaoId, custo, btn) {
        var formData = new FormData();
        formData.append('_csrf_token', data.csrfToken);
        formData.append('valor', custo);

        btn.disabled = true;
        btn.textContent = 'Salvando...';

        fetch(BASE_URL + '/cotacao/marcar-vencedor/' + cotacaoId, {
            method: 'POST',
            headers: {'X-Requested-With': 'XMLHttpRequest'},
            credentials: 'same-origin',
            body: formData
        })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            btn.disabled = false;
            btn.innerHTML = '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Marcar como Vencedor';

            if (d.success) {
                showToast('green', 'Vencedor Definido', 'Fornecedor ' + d.fornecedor + ' selecionado com custo de R$ ' + d.custo_unit.toFixed(2).replace('.', ','));
                document.getElementById('custo-display').textContent = 'R$ ' + d.custo_unit.toFixed(2).replace('.', ',');
                setTimeout(function() { window.location.reload(); }, 1500);
            } else {
                showToast('red', 'Erro', d.error || 'Erro ao marcar vencedor');
            }
        })
        .catch(function() {
            btn.disabled = false;
            btn.innerHTML = '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Marcar como Vencedor';
            showToast('red', 'Erro', 'Erro de conexao');
        });
    };

    // ===================== CARREGAR FORNECEDORES COM EMAILS =====================

    function carregarFornecedoresComEmails() {
        console.log('[COTACAO] Carregando fornecedores com emails...', {
            baseUrl: BASE_URL,
            idProdutoEvento: data.idProdutoEvento
        });

        fetch(BASE_URL + '/cotacao/fornecedores-by-item/' + data.idProdutoEvento, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': data.csrfToken || ''
            },
            credentials: 'same-origin'
        })
        .then(function(r) {
            console.log('[COTACAO] Response received', r.status);
            if (r.status === 401) {
                console.error('[COTACAO] Unauthorized - usuário não está logado');
                throw new Error('Unauthorized');
            }
            return r.json();
        })
        .then(function(d) {
            console.log('[COTACAO] Data received:', d);
            if (d.success && d.data && d.data.length > 0) {
                console.log('[COTACAO] Rendering', d.data.length, 'fornecedor(es)');
                renderFornecedoresEmails(d.data);
            } else {
                console.log('[COTACAO] No data found');
                document.getElementById('emails-container').innerHTML =
                    '<div style="text-align:center;padding:24px;color:var(--text-3)">' +
                    '<div style="font-size:14px">Nenhuma conversa ainda</div>' +
                    '<div style="font-size:12px;margin-top:4px">Envie uma solicitacao de cotacao primeiro</div>' +
                    '</div>';
            }
        })
        .catch(function(err) {
            console.error('[COTACAO] Error:', err);
            if (err.message === 'Unauthorized') {
                document.getElementById('emails-container').innerHTML =
                    '<div style="text-align:center;padding:24px;color:var(--red)">' +
                    '<div style="font-size:14px">❌ NÃO AUTENTICADO</div>' +
                    '<div style="font-size:12px;margin-top:4px">Faça login primeiro em /auth/login</div>' +
                    '<div style="font-size:12px;margin-top:4px">Depois volte para esta página</div>' +
                    '</div>';
            } else {
                document.getElementById('emails-container').innerHTML =
                    '<div style="text-align:center;padding:24px;color:var(--text-3)">Erro ao carregar conversas</div>';
            }
        });
    }

    function renderFornecedoresEmails(fornecedores) {
        var container = document.getElementById('emails-container');
        var html = '';

        fornecedores.forEach(function(f, idx) {
            var accordionId = 'acc-fornecedor-' + f.id_fornecedor;
            var unreadClass = f.nao_lidas > 0 ? ' style="font-weight:700"' : '';
            var unreadBadge = f.nao_lidas > 0 ? ' <span class="badge sm red">' + f.nao_lidas + ' novas</span>' : '';

            html += '<div class="accordion-item" style="border-bottom:1px solid var(--bg-border)">';
            html += '<div class="accordion-header" onclick="toggleAccordion(\'' + accordionId + '\')" style="cursor:pointer;padding:12px;display:flex;align-items:center;justify-content:space-between">';
            html += '<div' + unreadClass + '>';
            html += '<span style="font-size:14px">' + escapeHtml(f.fornecedor_nome) + '</span>';
            html += unreadBadge;
            html += '<div style="font-size:11px;color:var(--text-3)">' + escapeHtml(f.fornecedor_email) + '</div>';
            html += '</div>';
            html += '<svg class="accordion-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>';
            html += '</div>';
            // Todos os accordions começam EXPANDIDOS (display:block)
            html += '<div class="accordion-content" id="' + accordionId + '" style="display:block;padding:12px;border-top:1px solid var(--bg-border)">';
            html += '<div id="thread-' + f.id_fornecedor + '" style="max-height:400px;overflow-y:auto;margin-bottom:12px">';
            html += '<div style="text-align:center;padding:12px;color:var(--text-3);font-size:12px">Carregando mensagens...</div>';
            html += '</div>';
            html += '<div style="display:flex;gap:8px">';
            html += '<button type="button" class="btn btn-sm btn-cyan" onclick="abrirModalMensagem(' + f.id_cotacao + ', ' + f.id_fornecedor + ', \'' + escapeHtml(f.fornecedor_nome) + '\')">';
            html += '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>';
            html += 'Enviar Mensagem</button>';
            html += '</div>';
            html += '</div>';
            html += '</div>';
        });

        container.innerHTML = html;

        // Carregar mensagens de TODOS os fornecedores automaticamente
        fornecedores.forEach(function(f) {
            carregarThread(f.id_fornecedor);
        });
    }

    // ===================== ACCORDION =====================

    window.toggleAccordion = function(id) {
        var content = document.getElementById(id);
        if (content.style.display === 'none') {
            content.style.display = 'block';
            // Carregar thread se ainda nao carregada
            var threadContainer = content.querySelector('[id^="thread-"]');
            if (threadContainer && threadContainer.textContent.indexOf('Carregando') !== -1) {
                var fornecedorId = threadContainer.id.replace('thread-', '');
                carregarThread(fornecedorId);
            }
        } else {
            content.style.display = 'none';
        }
    };

    // ===================== CARREGAR THREAD =====================

    function carregarThread(fornecedorId) {
        // Encontrar cotacao ID buscando nas propostas OU cotacoes do item
        var cotacaoId = null;
        var propostas = data.propostas || [];
        var cotacoes = data.cotacoes || [];

        // Primeiro tenta nas propostas
        for (var j = 0; j < propostas.length; j++) {
            if (propostas[j].id_fornecedor == fornecedorId) {
                cotacaoId = propostas[j].id;
                break;
            }
        }

        // Se nao encontrou nas propostas, busca nas cotacoes
        if (!cotacaoId) {
            for (var k = 0; k < cotacoes.length; k++) {
                if (cotacoes[k].id_fornecedor == fornecedorId) {
                    cotacaoId = cotacoes[k].id;
                    break;
                }
            }
        }

        if (!cotacaoId) {
            document.getElementById('thread-' + fornecedorId).innerHTML =
                '<div style="text-align:center;padding:12px;color:var(--text-3);font-size:12px">Nenhuma mensagem ainda</div>';
            return;
        }

        fetch(BASE_URL + '/cotacao/thread/' + cotacaoId + '/' + fornecedorId, {
            headers: {'X-Requested-With': 'XMLHttpRequest'},
            credentials: 'same-origin'
        })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.success && d.data && d.data.length > 0) {
                renderThread(d.data, fornecedorId);
            } else {
                document.getElementById('thread-' + fornecedorId).innerHTML =
                    '<div style="text-align:center;padding:12px;color:var(--text-3);font-size:12px">Nenhuma mensagem ainda</div>';
            }
        })
        .catch(function() {
            document.getElementById('thread-' + fornecedorId).innerHTML =
                '<div style="text-align:center;padding:12px;color:var(--red);font-size:12px">Erro ao carregar mensagens</div>';
        });
    }

    function renderThread(mensagens, fornecedorId) {
        var container = document.getElementById('thread-' + fornecedorId);
        var html = '';

        mensagens.forEach(function(msg) {
            var isEnviado = msg.tipo === 'enviado';
            var bgColor = isEnviado ? 'var(--bg-elevated)' : 'var(--cyan-alpha)';
            var align = isEnviado ? 'flex-end' : 'flex-start';
            var borderColor = isEnviado ? 'var(--bg-border)' : 'var(--cyan)';

            html += '<div style="display:flex;justify-content:' + align + ';margin-bottom:8px">';
            html += '<div style="max-width:70%;padding:10px 14px;border-radius:12px;border:1px solid ' + borderColor + ';background:' + bgColor + '">';
            html += '<div style="font-size:11px;color:var(--text-3);margin-bottom:4px">' + escapeHtml(msg.assunto) + '</div>';
            html += '<div style="font-size:13px">' + nl2br(escapeHtml(msg.corpo)) + '</div>';
            if (msg.data_envio) {
                html += '<div style="font-size:10px;color:var(--text-4);margin-top:6px;text-align:right">' + formatarData(msg.data_envio) + '</div>';
            }
            if (msg.anexos && msg.anexos.length > 0) {
                html += '<div style="margin-top:6px">';
                msg.anexos.forEach(function(anexo) {
                    html += '<a href="' + BASE_URL + '/' + anexo.path + '" target="_blank" style="display:inline-flex;align-items:center;gap:4px;font-size:11px;color:var(--cyan);text-decoration:none">';
                    html += '<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="12" height="12"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>';
                    html += escapeHtml(anexo.nome_arquivo);
                    html += '</a>';
                });
                html += '</div>';
            }
            html += '</div></div>';
        });

        container.innerHTML = html;
        container.scrollTop = container.scrollHeight;
    }

    // ===================== ENVIAR MENSAGEM =====================

    window.abrirModalMensagem = function(cotacaoId, fornecedorId, nomeFornecedor) {
        document.getElementById('msg-id-cotacao').value = cotacaoId;
        document.getElementById('msg-id-fornecedor').value = fornecedorId;
        document.getElementById('msg-in-reply-to').value = '';
        document.getElementById('msg-nome-fornecedor').textContent = nomeFornecedor;
        document.getElementById('msg-assunto').value = 'Re: Cotação - ' + (window.COTACAO_DATA.itemNome || '');
        document.getElementById('msg-corpo').value = '';
        document.getElementById('msg-anexo').value = '';
        openModal('modal-enviar-mensagem');
    };

    window.enviarMensagem = function() {
        var cotacaoId = document.getElementById('msg-id-cotacao').value;
        var fornecedorId = document.getElementById('msg-id-fornecedor').value;
        var assunto = document.getElementById('msg-assunto').value;
        var corpo = document.getElementById('msg-corpo').value;

        if (!assunto || !corpo) {
            showToast('red', 'Erro', 'Preencha o assunto e a mensagem');
            return;
        }

        var formData = new FormData();
        formData.append('_csrf_token', data.csrfToken);
        formData.append('assunto', assunto);
        formData.append('corpo', corpo);

        var anexo = document.getElementById('msg-anexo').files[0];
        var anexoPromise = Promise.resolve(null);

        if (anexo) {
            var anexoForm = new FormData();
            anexoForm.append('_csrf_token', data.csrfToken);
            anexoForm.append('anexo', anexo);

            anexoPromise = fetch(BASE_URL + '/cotacao/upload-anexo', {
                method: 'POST',
                headers: {'X-Requested-With': 'XMLHttpRequest'},
                credentials: 'same-origin',
                body: anexoForm
            })
            .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.success) {
                formData.append('anexo_path', d.path);
                formData.append('anexo_nome', d.name);
                formData.append('anexo_mime', d.mime);
                formData.append('anexo_size', d.size);
            }
        });
        }

        var btn = document.querySelector('#modal-enviar-mensagem .btn-cyan');
        btn.disabled = true;
        btn.textContent = 'Enviando...';

        anexoPromise.then(function() {
            return fetch(BASE_URL + '/cotacao/enviar-mensagem/' + cotacaoId + '/' + fornecedorId, {
                method: 'POST',
                headers: {'X-Requested-With': 'XMLHttpRequest'},
                credentials: 'same-origin',
                body: formData
            });
        })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            btn.disabled = false;
            btn.textContent = 'Enviar';

            if (d.success) {
                closeModal('modal-enviar-mensagem');
                showToast('green', 'Mensagem Enviada', 'Email enviado com sucesso');
                // Recarregar thread
                carregarThread(fornecedorId);
            } else {
                showToast('red', 'Erro', d.error || 'Erro ao enviar mensagem');
            }
        })
        .catch(function() {
            btn.disabled = false;
            btn.textContent = 'Enviar';
            showToast('red', 'Erro', 'Erro de conexao');
        });
    };

    // ===================== UTILITARIOS =====================

    function escapeHtml(text) {
        if (!text) return '';
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function nl2br(str) {
        return str.replace(/\n/g, '<br>');
    }

    function formatarData(dateStr) {
        if (!dateStr) return '';
        try {
            var d = new Date(dateStr);
            return d.toLocaleDateString('pt-BR') + ' ' + d.toLocaleTimeString('pt-BR', {hour: '2-digit', minute: '2-digit'});
        } catch(e) {
            return dateStr;
        }
    }

    // ===================== INIT =====================

    console.log('[COTACAO] Script loaded, waiting for DOM...');

    document.addEventListener('DOMContentLoaded', function() {
        console.log('[COTACAO] DOMContentLoaded fired');

        // Agora pegar os valores após o footer ter carregado
        BASE_URL = window.BASE_URL || '';
        data = window.COTACAO_DATA || {};

        console.log('[COTACAO] BASE_URL:', BASE_URL);
        console.log('[COTACAO] COTACAO_DATA:', data);

        if (document.getElementById('emails-container')) {
            console.log('[COTACAO] emails-container found, calling carregarFornecedoresComEmails()');
            carregarFornecedoresComEmails();
        } else {
            console.warn('[COTACAO] emails-container NOT found');
        }
    });

})();
