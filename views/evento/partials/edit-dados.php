<?php
// Partial: Dados do Evento (VTab 0 dentro de Locacoes)
// Variaveis esperadas: $clientes, $produtores, $demandantes, $usuarios, $evento, $csrfToken, $eventoId, $baseUrl
?>
<?php
echo renderFormEvento([
    'clientes'    => $clientes,
    'produtores'  => $produtores,
    'demandantes' => $demandantes,
    'usuarios'    => $usuarios,
    'evento'      => $evento,
    'action'      => 'javascript:void(0)',
    'csrf'        => $csrfToken,
    'showSeparacao' => false,
]);
?>
<div class="btn-row" style="margin-top:16px;gap:8px">
  <a href="<?= $baseUrl ?>/eventos" class="btn btn-red">Cancelar</a>
  <button type="button" class="btn btn-cyan" id="btn-save-evento" data-action="salvar-evento">Salvar Alteracoes</button>
</div>
