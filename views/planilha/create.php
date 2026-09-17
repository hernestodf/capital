<?php require dirname(__DIR__) . '/layout/header.php'; ?>

<?php
$dados = $data ?? [];

// Preparar opções de unidade de medida
$unidadeOptions = '<option value="">Selecione</option>';
if (!empty($unidadesMedida)) {
    foreach ($unidadesMedida as $um) {
        $selected = ($dados['id_unidademedida'] ?? '') == $um['id'] ? 'selected' : '';
        $unidadeOptions .= '<option value="' . $um['id'] . '" ' . $selected . '>' . htmlspecialchars($um['unidademedida']) . '</option>';
    }
}
?>

    <section class="section active" id="sec-planilhas">
      <div class="section-header">
        <div class="section-icon">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
        </div>
        <div>
          <div class="section-title">Nova Planilha</div>
          <div class="section-sub">Cadastrar item, descricao e valor</div>
        </div>
      </div>
      <div class="divider"></div>

      <?php if (!empty($error)): ?>
      <?= renderAlert(['variant' => 'red', 'message' => $error]) ?>
      <?php endif; ?>

      <div class="card">
        <div class="card-head">
          <span class="card-title">Dados da Planilha</span>
        </div>
        <div class="card-body">
          <form method="POST" action="<?= $baseUrl ?>/planilhas/store" data-ajax data-redirect="<?= $baseUrl ?>/planilhas">
            <input type="hidden" name="_csrf_token" value="<?= \App\Core\Csrf::getToken() ?>">

            <div class="fg">
              <div class="fl">Item *</div>
              <input type="text" name="item" class="fi" placeholder="Nome do item" value="<?= htmlspecialchars($dados['item'] ?? '') ?>" required/>
            </div>

            <div class="fg" style="margin-top:16px">
              <div class="fl">Descricao</div>
              <textarea name="descricao" class="fi" rows="3" placeholder="Descricao detalhada do item"><?= htmlspecialchars($dados['descricao'] ?? '') ?></textarea>
            </div>

            <div class="col2" style="margin-top:16px">
              <div class="fg">
                <div class="fl">Unidade de Medida</div>
                <select name="id_unidademedida" class="fi">
                  <?= $unidadeOptions ?>
                </select>
              </div>
              <div class="fg">
                <div class="fl">Valor (R$)</div>
                <input type="text" name="valor" id="valor" class="fi" placeholder="0,00" value="<?= htmlspecialchars($dados['valor'] ?? '') ?>"/>
              </div>
            </div>

            <div class="col2" style="margin-top:16px">
              <div class="fg">
                <div class="fl">Potência (W) <span style="font-size:11px;color:var(--text-3)">— para cálculo de energia</span></div>
                <div class="fi-prefix-group">
                  <span class="fi-prefix">W</span>
                  <input type="number" name="potencia_w" class="fi" min="0" step="1"
                         placeholder="Ex: 1500"
                         value="<?= htmlspecialchars($dados['potencia_w'] ?? '') ?>"/>
                </div>
              </div>
              <div class="fg">
                <div class="fl">Horas de Uso <span style="font-size:11px;color:var(--text-3)">— padrão por evento</span></div>
                <div class="fi-prefix-group">
                  <span class="fi-prefix">h</span>
                  <input type="number" name="horas_uso" class="fi" min="0" step="0.5"
                         placeholder="Ex: 20"
                         value="<?= htmlspecialchars($dados['horas_uso'] ?? '') ?>"/>
                </div>
              </div>
            </div>
            <div style="margin-top:6px">
              <small style="color:var(--text-3);font-size:11px">kWh = Qtd × Potência × Horas / 1000 &nbsp;|&nbsp; kVA = kWh × 1,25 (fp = 0,8)</small>
            </div>

            <div style="margin-top:24px;display:flex;gap:8px">
              <button type="submit" class="btn btn-cyan">Salvar Planilha</button>
              <a href="<?= $baseUrl ?>/planilhas" class="btn btn-gray">Cancelar</a>
            </div>
          </form>
        </div>
      </div>
    </section>

<script>
// Mascara de moeda
document.getElementById('valor').addEventListener('input', function(e) {
  let value = e.target.value.replace(/\D/g, '');
  if (value === '') { e.target.value = ''; return; }
  value = (parseInt(value) / 100).toFixed(2);
  e.target.value = value.replace('.', ',');
});
</script>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?>
