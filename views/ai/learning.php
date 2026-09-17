<?php
/**
 * AI Learning - Sistema de Aprendizado
 *
 * @var array $patterns
 * @var array $corrections
 */

require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/button/button.php';
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/badge/badge.php';

require dirname(__DIR__) . '/layout/header.php';
?>

    <section class="section active" id="sec-ai-learning">
      <div class="section-header">
        <div class="section-icon">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
        </div>
        <div>
          <div class="section-title">Sistema de Aprendizado</div>
          <div class="section-sub">Padrões aprendidos e métricas de evolução</div>
        </div>
      </div>
      <div class="divider"></div>

      <!-- Padrões Aprendidos -->
      <div class="card">
        <div class="card-head">
          <span class="card-title">Padrões Aprendidos (<?= count($patterns) ?>)</span>
        </div>
        <div class="card-body">
          <?php if (!empty($patterns)): ?>
            <div style="display:flex;flex-direction:column;gap:12px">
              <?php foreach ($patterns as $pattern): ?>
                <div style="background:var(--bg-tertiary);border:1px solid var(--border);border-radius:8px;padding:16px">
                  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;flex-wrap:wrap;gap:8px">
                    <div style="display:flex;gap:8px">
                      <?= renderBadge(['label' => $pattern['agente'], 'variant' => 'badge-cyan', 'size' => 'sm']) ?>
                      <?= renderBadge(['label' => $pattern['tipo'], 'variant' => 'badge-blue', 'size' => 'sm']) ?>
                    </div>
                    <div style="display:flex;gap:16px;font-size:0.875rem;color:var(--txt-2)">
                      <span><strong style="color:var(--neon-cyan)"><?= $pattern['frequencia'] ?></strong> usos</span>
                      <span><strong style="color:var(--neon-cyan)"><?= round($pattern['sucesso_rate'] * 100) ?>%</strong> sucesso</span>
                    </div>
                  </div>
                  <div style="font-weight:500;color:var(--text-primary);margin-bottom:8px"><?= htmlspecialchars($pattern['padrao']) ?></div>
                  <?php if (!empty($pattern['exemplo'])): ?>
                    <div style="background:var(--bg-secondary);padding:8px 12px;border-radius:4px;border-left:3px solid var(--neon-cyan);font-family:monospace;font-size:0.875rem;color:var(--neon-cyan);margin-bottom:8px"><?= htmlspecialchars($pattern['exemplo']) ?></div>
                  <?php endif; ?>
                  <div style="font-size:0.75rem;color:var(--txt-3)">Contexto: <?= htmlspecialchars($pattern['contexto']) ?></div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <div style="text-align:center;padding:40px;color:var(--txt-2)">Nenhum padrão registrado ainda</div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Correções -->
      <div class="card" style="margin-top:16px">
        <div class="card-head"><span class="card-title">Correções e Ajustes (<?= count($corrections) ?>)</span></div>
        <div class="card-body">
          <?php if (!empty($corrections)): ?>
            <div style="display:flex;flex-direction:column;gap:12px">
              <?php foreach ($corrections as $correction): ?>
                <?php
                $variant = match($correction['tipo']) {
                  'security' => 'badge-red',
                  'bug' => 'badge-orange',
                  'performance' => 'badge-yellow',
                  default => 'badge-cyan'
                };
                ?>
                <div style="background:var(--bg-tertiary);border:1px solid var(--border);border-radius:8px;padding:16px">
                  <div style="display:flex;gap:8px;margin-bottom:8px">
                    <?= renderBadge(['label' => strtoupper($correction['tipo']), 'variant' => $variant, 'size' => 'sm']) ?>
                    <?php if ($correction['prevenido']): ?>
                      <?= renderBadge(['label' => 'Prevenido', 'variant' => 'badge-green', 'size' => 'sm']) ?>
                    <?php endif; ?>
                  </div>
                  <div style="color:var(--text-primary);margin-bottom:8px"><?= htmlspecialchars($correction['descricao']) ?></div>
                  <?php if (!empty($correction['solucao'])): ?>
                    <div style="font-size:0.875rem;color:var(--txt-2);padding:8px;background:var(--bg-secondary);border-radius:4px"><strong>Solução:</strong> <?= htmlspecialchars($correction['solucao']) ?></div>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <div style="text-align:center;padding:40px;color:var(--txt-2)">Nenhuma correção registrada</div>
          <?php endif; ?>
        </div>
      </div>
    </section>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?>
