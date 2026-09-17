<?php
/**
 * Formulario de Eventos
 *
 * Componente de formulario para criacao/edicao de eventos.
 * Reutilizavel nas views create.php e edit.php.
 *
 * @param array $options {
 *     @type array  $clientes             Lista de clientes com 'id' e 'nome_fantasia'/'razao_social'
 *     @type array  $produtores           Lista de produtores com 'id' e 'nome'
 *     @type array  $demandantes          Lista de demandantes com 'id' e 'nome'
 *     @type array  $evento               Dados do evento (array vazio para create)
 *     @type string $action               URL de acao do formulario
 *     @type string $method               Metodo HTTP (padrao: POST)
 *     @type string $csrf                 Token CSRF
 * }
 *
 * Uso:
 *   require_once __DIR__ . '/form-evento.php';
 *   echo renderFormEvento([
 *       'clientes'    => $clientes,
 *       'produtores'  => $produtores,
 *       'demandantes' => $demandantes,
 *       'evento'      => $evento ?? [],
 *       'action'      => $baseUrl . '/eventos/store',
 *       'csrf'        => $csrfToken,
 *   ]);
 */

require_once dirname(__DIR__) . '/input/input.php';
require_once dirname(__DIR__) . '/toggle/toggle.php';

function renderFormEvento(array $options): string
{
    $clientes    = $options['clientes'] ?? [];
    $produtores  = $options['produtores'] ?? [];
    $demandantes = $options['demandantes'] ?? [];
    $usuarios    = $options['usuarios'] ?? [];
    $evento      = $options['evento'] ?? [];
    $action      = $options['action'] ?? '';
    $method      = $options['method'] ?? 'POST';
    $csrf        = $options['csrf'] ?? '';
    $showSeparacao = $options['showSeparacao'] ?? true;

    // Helper para obter valor do evento
    $val = function (string $field, $default = '') use ($evento) {
        return $evento[$field] ?? $default;
    };

    // Helper para construir opcoes de select com value/key
    $selectOptions = function (array $items, string $valueKey, string $labelKey) {
        $opts = [];
        foreach ($items as $item) {
            $v = $item[$valueKey] ?? '';
            $l = $item[$labelKey] ?? '';
            $opts[$v] = htmlspecialchars($l);
        }
        return $opts;
    };

    $clienteOptions    = $selectOptions($clientes, 'id', 'nome_fantasia');
    $produtorOptions   = $selectOptions($produtores, 'id', 'nome');
    $demandanteOptions = $selectOptions($demandantes, 'id', 'nome');
    $usuarioOptions    = $selectOptions($usuarios, 'id', 'name');

    // Estado dos toggles
    $eventoMontado    = $val('evento_montado', 0);
    $eventoDesmontado = $val('evento_desmontado', 0);

    // Construir selects com opcao padrao
    $clienteSelectOpts = ['' => 'Selecione um cliente'];
    foreach ($clienteOptions as $k => $v) {
        $clienteSelectOpts[$k] = $v;
    }

    $produtorSelectOpts = ['' => 'Selecione um comercial'];
    foreach ($produtorOptions as $k => $v) {
        $produtorSelectOpts[$k] = $v;
    }

    $demandanteSelectOpts = ['' => 'Selecione um comprador'];
    foreach ($demandanteOptions as $k => $v) {
        $demandanteSelectOpts[$k] = $v;
    }

    $usuarioSelectOpts = ['' => 'Ninguém'];
    foreach ($usuarioOptions as $k => $v) {
        $usuarioSelectOpts[$k] = $v;
    }

    // Estado dos selects (para highlight do valor selecionado)
    $clienteVal    = $val('id_cliente', '');
    $produtorVal   = $val('id_produtor', '');
    $demandanteVal = $val('id_demandante', '');
    $usuarioVal    = $val('id_usuario_separacao', '');

    // Helper para gerar select com value attribute
    $renderSelect = function (string $name, string $label, array $opts, $selected, bool $required = false) {
        $requiredAttr = $required ? ' required' : '';
        $html = '<div class="fg">';
        $html .= '<div class="fl">' . $label . '</div>';
        $html .= '<select class="fi" id="' . $name . '" name="' . $name . '"' . $requiredAttr . '>';
        foreach ($opts as $k => $v) {
            $sel = ((string)$k === (string)$selected) ? ' selected' : '';
            $html .= '<option value="' . htmlspecialchars((string)$k) . '"' . $sel . '>' . $v . '</option>';
        }
        $html .= '</select>';
        $html .= '</div>';
        return $html;
    };

    // ===== HTML DO FORMULARIO =====
    $html = '';

    $html .= '<form method="' . $method . '" action="' . $action . '" id="form-evento">';
    $html .= '<input type="hidden" name="_csrf_token" value="' . $csrf . '" />';

    // --- Row 1: Cliente, Comercial, Comprador, OS Cliente (4 colunas) ---
    $html .= '<div class="form-row-4">';
    $html .= $renderSelect('id_cliente', 'Cliente', $clienteSelectOpts, $clienteVal);
    $html .= $renderSelect('id_produtor', 'Comercial', $produtorSelectOpts, $produtorVal);

    // Demandante com botão "+" para cadastro rápido
    $demandanteSelectHtml = '<select class="fi" name="id_demandante" id="sel-demandante" style="flex:1;min-width:0">';
    foreach ($demandanteSelectOpts as $k => $v) {
        $sel = ((string)$k === (string)$demandanteVal) ? ' selected' : '';
        $demandanteSelectHtml .= '<option value="' . htmlspecialchars((string)$k) . '"' . $sel . '>' . $v . '</option>';
    }
    $demandanteSelectHtml .= '</select>';
    $html .= '<div class="fg">'
        . '<div class="fl">Comprador</div>'
        . '<div style="display:flex;gap:6px;align-items:center">'
        . $demandanteSelectHtml
        . '<button type="button" onclick="abrirModalNovoDemandante()" '
        . 'style="flex-shrink:0;width:32px;height:32px;border-radius:6px;border:1px solid var(--cyan,#06b6d4);'
        . 'background:none;color:var(--cyan,#06b6d4);font-size:18px;line-height:1;cursor:pointer;'
        . 'display:flex;align-items:center;justify-content:center" title="Cadastrar novo comprador">+'
        . '</button>'
        . '</div>'
        . '</div>';
    $html .= renderInput([
        'type'        => 'text',
        'label'       => 'OS Cliente',
        'name'        => 'os_cliente',
        'value'       => $val('os_cliente'),
        'placeholder' => 'Numero da OS',
    ]);
    $html .= '</div>';

    // --- Row 1b: Separado por (1 coluna + vazia) ---
    if ($showSeparacao) {
        $html .= '<div class="form-row-2">';
        $html .= $renderSelect('id_usuario_separacao', 'Separado por', $usuarioSelectOpts, $usuarioVal);
        $html .= '<div class="fg"></div>';
        $html .= '</div>';
    }

    // --- Row 2: Nome do Evento, Local do Evento (2 colunas) ---
    $html .= '<div class="form-row-2">';
    $html .= renderInput([
        'type'        => 'text',
        'label'       => 'Nome do Evento',
        'name'        => 'nome_evento',
        'value'       => $val('nome_evento'),
        'placeholder' => 'Ex: Festa de aniversario, Congresso...',
        'required'    => true,
    ]);
    $html .= renderInput([
        'type'        => 'text',
        'label'       => 'Local do Evento',
        'name'        => 'local_evento',
        'value'       => $val('local_evento'),
        'placeholder' => 'Nome do local/salao',
        'required'    => true,
    ]);
    $html .= '</div>';

    // --- Row 3: Demandante Local, Telefone (2 colunas) ---
    $html .= '<div class="form-row-2">';
    $html .= renderInput([
        'type'        => 'text',
        'label'       => 'Comprador no Local',
        'name'        => 'demandante_local',
        'value'       => $val('demandante_local'),
        'placeholder' => 'Nome da pessoa responsavel no local',
    ]);
    $html .= renderInput([
        'type'        => 'tel',
        'label'       => 'Telefone Comprador Local',
        'name'        => 'telefone_demandantelocal',
        'value'       => $val('telefone_demandantelocal'),
        'placeholder' => '(00) 00000-0000',
    ]);
    $html .= '</div>';

    // --- Row 4: Data Montagem, Data Inicio, Data Fim, Data Desmontagem (4 colunas) ---
    $html .= '<div class="form-row-4">';
    $html .= '<div class="fg">' .
        '<div class="fl">Data Montagem</div>' .
        '<input class="fi" type="date" name="data_montagem" value="' . $val('data_montagem') . '" />' .
        '</div>';
    $html .= '<div class="fg">' .
        '<div class="fl">Data Inicio</div>' .
        '<input class="fi" type="date" id="data_inicio" name="data_inicio" value="' . $val('data_inicio') . '" />' .
        '</div>';
    $html .= '<div class="fg">' .
        '<div class="fl">Data Fim</div>' .
        '<input class="fi" type="date" id="data_fim" name="data_fim" value="' . $val('data_fim') . '" />' .
        '</div>';
    $html .= '<div class="fg">' .
        '<div class="fl">Data Desmontagem</div>' .
        '<input class="fi" type="date" name="data_desmontagem" value="' . $val('data_desmontagem') . '" />' .
        '</div>';
    $html .= '</div>';

    // --- Row 5: Hora Montagem, Hora Inicio, Hora Fim, Hora Desmontagem (4 colunas) ---
    $html .= '<div class="form-row-4">';
    $html .= '<div class="fg">' .
        '<div class="fl">Hora Montagem</div>' .
        '<input class="fi" type="time" name="hora_montagem" value="' . $val('hora_montagem') . '" />' .
        '</div>';
    $html .= '<div class="fg">' .
        '<div class="fl">Hora Inicio</div>' .
        '<input class="fi" type="time" name="hora_inicio" value="' . $val('hora_inicio') . '" />' .
        '</div>';
    $html .= '<div class="fg">' .
        '<div class="fl">Hora Fim</div>' .
        '<input class="fi" type="time" name="hora_fim" value="' . $val('hora_fim') . '" />' .
        '</div>';
    $html .= '<div class="fg">' .
        '<div class="fl">Hora Desmontagem</div>' .
        '<input class="fi" type="time" name="hora_desmontagem" value="' . $val('hora_desmontagem') . '" />' .
        '</div>';
    $html .= '</div>';

    // --- Row 6: Estado, Status, Toggles (4 colunas) ---
    $html .= '<div class="form-row-4">';

    // Estado (O=Orcamento, L=Locacao, P=Pedido)
    $estadoVal = $val('estado', 'O');
    $html .= '<div class="fg">' .
        '<div class="fl">Estado</div>' .
        '<select class="fi" name="estado">' .
            '<option value="O"' . ($estadoVal === 'O' ? ' selected' : '') . '>Orcamento</option>' .
            '<option value="L"' . ($estadoVal === 'L' ? ' selected' : '') . '>Locacao</option>' .
            '<option value="P"' . ($estadoVal === 'P' ? ' selected' : '') . '>Pedido</option>' .
        '</select>' .
        '</div>';

    // Status Locacao (A=Andamento, F=Finalizada, T=Faturado)
    $statusVal = $val('status_locacao', 'A');
    $html .= '<div class="fg">' .
        '<div class="fl">Status Locacao</div>' .
        '<select class="fi" name="status_locacao">' .
            '<option value="A"' . ($statusVal === 'A' ? ' selected' : '') . '>Andamento</option>' .
            '<option value="F"' . ($statusVal === 'F' ? ' selected' : '') . '>Finalizada</option>' .
            '<option value="T"' . ($statusVal === 'T' ? ' selected' : '') . '>Faturado</option>' .
        '</select>' .
        '</div>';

    // Evento Montado (toggle)
    $html .= '<div class="fg">' .
        '<div class="fl">Evento Montado</div>' .
        '<div style="padding-top:4px">' .
            renderToggle(['checked' => (bool)$eventoMontado, 'variant' => 'green']) .
            '<input type="hidden" name="evento_montado" value="' . ($eventoMontado ? '1' : '0') . '" id="input-evento-montado" />' .
        '</div>' .
        '</div>';

    // Evento Desmontado (toggle)
    $html .= '<div class="fg">' .
        '<div class="fl">Evento Desmontado</div>' .
        '<div style="padding-top:4px">' .
            renderToggle(['checked' => (bool)$eventoDesmontado, 'variant' => 'cyan']) .
            '<input type="hidden" name="evento_desmontado" value="' . ($eventoDesmontado ? '1' : '0') . '" id="input-evento-desmontado" />' .
        '</div>' .
        '</div>';

    $html .= '</div>';

    // --- Row 7: Observacao (full width) ---
    $html .= renderInput([
        'type'        => 'textarea',
        'label'       => 'Observacao',
        'name'        => 'observacao',
        'value'       => $val('observacao'),
        'placeholder' => 'Informacoes adicionais sobre o evento...',
        'rows'        => 4,
    ]);

    // Script para sincronizar toggles com hidden inputs e mascara de telefone
    $html .= '<script>
    (function() {
        // Toggles
        var toggles = document.querySelectorAll("#form-evento .tog input[type=checkbox]");
        toggles.forEach(function(tog) {
            tog.addEventListener("change", function() {
                var hidden = this.closest(".fg").querySelector("input[type=hidden]");
                if (hidden) {
                    hidden.value = this.checked ? "1" : "0";
                }
            });
        });

        // Mascara telefone celular (XX) XXXXX-XXXX
        var telefone = document.querySelector("#form-evento input[name=telefone_demandantelocal]");
        if (telefone) {
            telefone.addEventListener("input", function(e) {
                var v = e.target.value.replace(/\D/g, "");
                if (v.length > 11) v = v.substring(0, 11);
                if (v.length > 6) {
                    e.target.value = "(" + v.substring(0, 2) + ") " + v.substring(2, 7) + "-" + v.substring(7);
                } else if (v.length > 2) {
                    e.target.value = "(" + v.substring(0, 2) + ") " + v.substring(2);
                } else if (v.length > 0) {
                    e.target.value = "(" + v;
                }
            });
        }
    })();
    </script>';

    $html .= '</form>';

    // Modal: Novo Demandante
    $baseUrl = rtrim(\App\Core\Env::get('BASE_URL', ''), '/');
    $html .= '
<div id="modal-novo-demandante" style="display:none;position:fixed;inset:0;z-index:10000;background:rgba(0,0,0,.45);align-items:center;justify-content:center">
  <div style="background:#fff;border-radius:14px;padding:28px;width:100%;max-width:420px;box-shadow:0 16px 48px rgba(0,0,0,.22);position:relative">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
      <div style="font-weight:700;font-size:16px">Novo Comprador</div>
      <button type="button" onclick="fecharModalNovoDemandante()"
        style="background:none;border:none;cursor:pointer;font-size:22px;line-height:1;color:#6b7280">&#x2715;</button>
    </div>
    <div id="modal-demandante-erro" style="display:none;background:#fef2f2;color:#b91c1c;border-radius:6px;padding:8px 12px;font-size:13px;margin-bottom:14px"></div>
    <div style="display:flex;flex-direction:column;gap:14px">
      <input type="hidden" id="nd-id_cliente" value="" />
      <div>
        <div class="fl">Nome <span style="color:#ef4444">*</span></div>
        <input id="nd-nome" class="fi" type="text" placeholder="Nome completo" />
      </div>
      <div>
        <div class="fl">Telefone</div>
        <input id="nd-telefone" class="fi" type="text" placeholder="(00) 00000-0000" />
      </div>
      <div>
        <div class="fl">E-mail</div>
        <input id="nd-email" class="fi" type="email" placeholder="email@exemplo.com" />
      </div>
    </div>
    <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:22px">
      <button type="button" onclick="fecharModalNovoDemandante()"
        style="padding:8px 18px;border-radius:7px;border:1px solid #e5e7eb;background:#f9fafb;cursor:pointer;font-size:13px">
        Cancelar
      </button>
      <button type="button" id="btn-salvar-demandante"
        onclick="salvarNovoDemandante()"
        style="padding:8px 20px;border-radius:7px;border:none;background:var(--cyan,#06b6d4);color:#fff;cursor:pointer;font-size:13px;font-weight:600">
        Salvar
      </button>
    </div>
  </div>
</div>

<script>
(function() {
  var modal  = document.getElementById("modal-novo-demandante");
  var CSRF   = ' . json_encode($csrf) . ';
  var BASE   = ' . json_encode($baseUrl) . ';

  window.abrirModalNovoDemandante = function() {
    document.getElementById("nd-nome").value     = "";
    document.getElementById("nd-telefone").value = "";
    document.getElementById("nd-email").value    = "";
    document.getElementById("modal-demandante-erro").style.display = "none";
    var selCliente = document.getElementById("id_cliente");
    document.getElementById("nd-id_cliente").value = selCliente ? selCliente.value : "";
    modal.style.display = "flex";
    setTimeout(function() { document.getElementById("nd-nome").focus(); }, 50);
  };

  window.fecharModalNovoDemandante = function() {
    modal.style.display = "none";
  };

  modal.addEventListener("click", function(e) {
    if (e.target === modal) fecharModalNovoDemandante();
  });

  document.addEventListener("keydown", function(e) {
    if (e.key === "Escape" && modal.style.display === "flex") fecharModalNovoDemandante();
  });

  // Mascara telefone: (XX) XXXXX-XXXX
  var telInput = document.getElementById("nd-telefone");
  if (telInput) {
    telInput.addEventListener("input", function(e) {
      var v = e.target.value.replace(/\D/g, "");
      if (v.length > 11) v = v.substring(0, 11);
      if (v.length > 6) {
        e.target.value = "(" + v.substring(0, 2) + ") " + v.substring(2, 7) + "-" + v.substring(7);
      } else if (v.length > 2) {
        e.target.value = "(" + v.substring(0, 2) + ") " + v.substring(2);
      } else if (v.length > 0) {
        e.target.value = "(" + v;
      }
    });
  }

  window.salvarNovoDemandante = function() {
    var nome    = document.getElementById("nd-nome").value.trim();
    var tel     = document.getElementById("nd-telefone").value.trim();
    var email   = document.getElementById("nd-email").value.trim();
    var erro    = document.getElementById("modal-demandante-erro");
    var btnSalvar = document.getElementById("btn-salvar-demandante");

    if (!nome) {
      erro.textContent = "Nome é obrigatório.";
      erro.style.display = "block";
      document.getElementById("nd-nome").focus();
      return;
    }

    btnSalvar.disabled = true;
    btnSalvar.textContent = "Salvando...";
    erro.style.display = "none";

    var body = "_csrf_token=" + encodeURIComponent(CSRF)
             + "&nome="       + encodeURIComponent(nome)
             + "&telefone="   + encodeURIComponent(tel)
             + "&email="      + encodeURIComponent(email)
             + "&id_cliente=" + encodeURIComponent(document.getElementById("nd-id_cliente").value);

    fetch(BASE + "/compradores/store-ajax", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded", "X-Requested-With": "XMLHttpRequest" },
      body: body,
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
      console.log("[novoDemandante]", data);
      if (!data.success) {
        erro.textContent = data.message || "Erro ao salvar.";
        erro.style.display = "block";
        btnSalvar.disabled = false;
        btnSalvar.textContent = "Salvar";
        return;
      }
      // Adiciona nova opção ao select e seleciona
      var sel = document.getElementById("sel-demandante");
      var opt = document.createElement("option");
      opt.value = data.id;
      opt.textContent = data.nome;
      opt.selected = true;
      sel.appendChild(opt);
      fecharModalNovoDemandante();
    })
    .catch(function() {
      erro.textContent = "Erro de comunicação com o servidor.";
      erro.style.display = "block";
      btnSalvar.disabled = false;
      btnSalvar.textContent = "Salvar";
    });
  };
})();
</script>';

    return $html;
}
