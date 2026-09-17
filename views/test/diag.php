<?php require dirname(__DIR__) . '/layout/header.php'; ?>

    <section class="section active" id="sec-diag">
      <div class="section-header">
        <div class="section-icon">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="24" height="24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        </div>
        <div>
          <div class="section-title">Diagnostico de Rotas - Cotacao</div>
          <div class="section-sub">Verificar se URLs estao corretas</div>
        </div>
      </div>
      <div class="divider"></div>

      <div class="card">
        <div class="card-head"><div class="card-title">Clique em cada link abaixo (abre em nova aba)</div></div>
        <div class="card-body">
          <?php foreach ($itens as $item): ?>
          <?php
          $url = $baseUrl . '/eventos/cotacao/1/' . $item['id'];
          $produto = htmlspecialchars($item['produto']);
          $sala = htmlspecialchars($item['nome_sala'] ?? 'N/A');
          ?>
          <div style='margin-bottom:12px;padding:12px;border:1px solid var(--bg-border);border-radius:8px;display:flex;align-items:center;justify-content:space-between'>
            <div>
              <strong>ID <?= $item['id'] ?>:</strong> <?= $produto ?> (<?= $sala ?>)<br>
              <code style='font-size:11px'><?= $url ?></code>
            </div>
            <a href='<?= $url ?>' target='_blank' class='btn btn-sm btn-cyan'>Abrir</a>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="card mt-4">
        <div class="card-head"><div class="card-title">Como testar</div></div>
        <div class="card-body">
          <ol style="line-height:2.2">
            <li>Clique em cada botao "Abrir" acima</li>
            <li><strong>Se cada aba mostrar o produto correto</strong> = Funcionando</li>
            <li><strong>Se todas as abas mostrarem o mesmo produto</strong> = Cache do navegador</li>
            <li>Para limpar cache: <kbd>Ctrl</kbd>+<kbd>Shift</kbd>+<kbd>R</kbd> (hard refresh)</li>
          </ol>
        </div>
      </div>
    </section>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?>
