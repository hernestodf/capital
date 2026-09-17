<?php
/**
 * Modal: Consulta de Alocação de Produtos
 * Mostra: Em qual evento está alocado, quanto tem disponível
 * Incluir na view com: require_once 'modal-consulta-alocacao.php';
 */
?>

<!-- Modal Consulta de Alocacao -->
<div id="modalConsultaAlocacao" class="modal-overlay" data-action="close-on-backdrop" data-target="modalConsultaAlocacao">
    <div class="modal" style="max-width:600px">
        <div class="modal-header">
            <h2>Consulta de Alocação</h2>
            <button type="button" class="modal-close" data-action="close-modal" data-target="modalConsultaAlocacao">×</button>
        </div>
        <div class="modal-body">
            <div class="fg" style="margin-bottom:16px">
                <div class="fl">Pesquisar Produto</div>
                <input type="text" id="consultaAlocacaoProduto" class="fi fi-primary"
                       placeholder="Digite o nome do produto..." autofocus>
            </div>

            <div id="consultaAlocacaoResultado" style="display:none;margin-top:20px">
                <!-- Preenchido dinamicamente -->
            </div>

            <div id="consultaAlocacaoCarregando" style="display:none;text-align:center;padding:40px;color:var(--text-3)">
                <div style="margin-bottom:12px">
                    <svg style="width:32px;height:32px;animation:spin 1s linear infinite" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2v20m0 0l4-4m-4 4l-4-4"/>
                    </svg>
                </div>
                <p>Carregando informações...</p>
            </div>

            <div id="consultaAlocacaoVazio" style="text-align:center;padding:40px;color:var(--text-3)">
                <p>Digite o nome de um produto para consultar alocação</p>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-gray" data-action="close-modal" data-target="modalConsultaAlocacao">Fechar</button>
        </div>
    </div>
</div>

<style>
    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    .consulta-nao-encontrado {
        padding: 20px;
        text-align: center;
        color: var(--text-3);
        background: rgba(239, 68, 68, 0.05);
        border-radius: 4px;
        border: 1px solid rgba(239, 68, 68, 0.2);
    }
</style>

<script>
(function() {
    'use strict';

    var BASE_URL = window.BASE_URL || '';
    var inputProduto = null;
    var resultadoDiv = null;
    var carregandoDiv = null;
    var vazioDiv = null;
    var debounceTimer = null;

    function init() {
        inputProduto = document.getElementById('consultaAlocacaoProduto');
        resultadoDiv = document.getElementById('consultaAlocacaoResultado');
        carregandoDiv = document.getElementById('consultaAlocacaoCarregando');
        vazioDiv = document.getElementById('consultaAlocacaoVazio');

        if (!inputProduto) return;

        inputProduto.addEventListener('input', function() {
            if (debounceTimer) clearTimeout(debounceTimer);

            if (!inputProduto.value.trim()) {
                mostrarVazio();
                return;
            }

            debounceTimer = setTimeout(function() {
                consultarAlocacao(inputProduto.value.trim());
            }, 300);
        });
    }

    function mostrarVazio() {
        if (resultadoDiv) resultadoDiv.style.display = 'none';
        if (carregandoDiv) carregandoDiv.style.display = 'none';
        if (vazioDiv) vazioDiv.style.display = 'block';
    }

    function mostrarCarregando() {
        if (resultadoDiv) resultadoDiv.style.display = 'none';
        if (carregandoDiv) carregandoDiv.style.display = 'block';
        if (vazioDiv) vazioDiv.style.display = 'none';
    }

    function mostrarResultado(html) {
        if (carregandoDiv) carregandoDiv.style.display = 'none';
        if (vazioDiv) vazioDiv.style.display = 'none';
        if (resultadoDiv) {
            resultadoDiv.innerHTML = html;
            resultadoDiv.style.display = 'block';
        }
    }

    function consultarAlocacao(nomeProduto) {
        mostrarCarregando();

        var params = new URLSearchParams();
        params.append('nome', nomeProduto);

        fetch('/api/produtos/consultar-alocacao?' + params, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!data.success) {
                mostrarResultado('<div class="consulta-nao-encontrado">Erro: ' + (data.error || 'Falha na consulta') + '</div>');
                return;
            }

            var resultado = data.data || {};

            if (!resultado.encontrado) {
                mostrarResultado(
                    '<div style="padding:16px;border-radius:8px;background:rgba(0,0,0,0.03);border:1px solid var(--bg-border-sub);text-align:center">' +
                    '<div style="font-size:12px;color:var(--text-2)">Produto "<strong>' + esc(resultado.termo) + '</strong>" não encontrado no estoque físico.</div>' +
                    '</div>'
                );
                return;
            }

            var produtos = resultado.produtos || [];
            var html = '';

            produtos.forEach(function(p, idx) {
                var totalAtivo = p.total_ativo || 0;
                var disponiveis = p.disponiveis || 0;
                var ocupados = p.ocupados || [];

                html += '<div style="font-size:13px;font-weight:700;color:var(--text-1);margin-top:' + (idx > 0 ? '16px' : '0') + ';margin-bottom:6px">' + esc(p.produto) + '</div>';

                if (disponiveis > 0) {
                    html += '<div style="padding:12px 14px;border-radius:8px;background:rgba(34,197,94,0.08);border:1px solid rgba(34,197,94,0.25);margin-bottom:12px">';
                    html += '<span style="font-size:13px;font-weight:700;color:#16a34a">Disponível: ' + disponiveis + ' de ' + totalAtivo + '</span>';
                    html += '</div>';
                } else {
                    html += '<div style="padding:12px 14px;border-radius:8px;background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.25);margin-bottom:12px">';
                    html += '<span style="font-size:13px;font-weight:700;color:#dc2626">Nenhum código disponível — todos os ' + totalAtivo + ' estão em uso</span>';
                    html += '</div>';
                }

                ocupados.forEach(function(o) {
                    var origem = o.evento_atual ? 'neste evento' : ('no evento <strong>' + esc(o.nome_evento) + '</strong>' + (o.os_cliente ? ' (O.S. ' + esc(o.os_cliente) + ')' : ''));
                    html += '<div style="padding:8px 10px;border-radius:6px;background:rgba(0,0,0,0.03);border:1px solid var(--bg-border-sub);font-size:12px;color:var(--text-2);margin-bottom:6px">';
                    html += 'Código <strong style="color:var(--text-1)">' + esc(o.serial) + '</strong> está ' + origem;
                    html += '</div>';
                });
            });

            mostrarResultado(html);
        })
        .catch(function(err) {
            mostrarResultado('<div class="consulta-nao-encontrado">Erro: ' + err.message + '</div>');
        });
    }

    function esc(str) {
        var d = document.createElement('div');
        d.textContent = str || '';
        return d.innerHTML;
    }

    // Inicializar quando o modal abrir
    document.addEventListener('DOMContentLoaded', init);

    // Re-focar no input quando o modal abre
    window.addEventListener('modalOpen', function(e) {
        if (e.detail === 'modalConsultaAlocacao' && inputProduto) {
            setTimeout(function() { inputProduto.focus(); }, 100);
        }
    });
})();
</script>
