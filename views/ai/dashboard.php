<?php
/**
 * AI Dashboard - Interface dos Agentes de IA
 *
 * @var array $stats
 * @var array $agents
 * @var array $recentTasks
 */

require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/button/button.php';
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/card/card.php';
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/badge/badge.php';

require dirname(__DIR__) . '/layout/header.php';
?>

    <section class="section active" id="sec-ai-dashboard">
      <div class="section-header">
        <div class="section-icon">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
        </div>
        <div>
          <div class="section-title">Sistema de Agentes IA</div>
          <div class="section-sub">Orquestração inteligente com aprendizado contínuo</div>
        </div>
      </div>
      <div class="divider"></div>

      <!-- Stats Cards -->
      <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px" class="ai-stats-grid">
        <div class="card-stat">
          <div class="card-stat-val"><?= $stats['total_agents'] ?? 0 ?></div>
          <div class="card-stat-lbl">Agentes Ativos</div>
        </div>
        <div class="card-stat" style="--stat-color:var(--green)">
          <div class="card-stat-val" style="color:var(--green)"><?= $stats['total_patterns'] ?? 0 ?></div>
          <div class="card-stat-lbl">Padrões Aprendidos</div>
        </div>
        <div class="card-stat" style="--stat-color:var(--cyan)">
          <div class="card-stat-val" style="color:var(--cyan)"><?= $stats['total_executions'] ?? 0 ?></div>
          <div class="card-stat-lbl">Execuções</div>
        </div>
        <div class="card-stat" style="--stat-color:var(--yellow)">
          <div class="card-stat-val" style="color:var(--yellow)"><?= $stats['success_rate'] ?? 0 ?>%</div>
          <div class="card-stat-lbl">Taxa de Sucesso</div>
        </div>
      </div>

      <style>
        @media (max-width:1024px){.ai-stats-grid{grid-template-columns:repeat(2,1fr)!important}}
        @media (max-width:640px){.ai-stats-grid{grid-template-columns:1fr!important}}
      </style>

      <!-- Main Content -->
      <div class="card">
        <div class="card-head">
          <span class="card-title">Agentes Disponíveis</span>
          <a href="<?= $baseUrl ?>/ai/agents" class="btn btn-sm btn-cyan">Ver todos</a>
        </div>
        <div class="card-body">
          <div class="agent-list">
            <?php foreach (array_slice($agents, 0, 5) as $agent): ?>
              <div style="display:flex;align-items:center;gap:12px;padding:12px;background:var(--bg-tertiary);border-radius:8px;margin-bottom:8px">
                <div style="font-size:1.5rem;width:40px;height:40px;display:flex;align-items:center;justify-content:center;background:var(--bg-secondary);border-radius:8px">
                  <?php echo match($agent['id']) {
                    'orquestrador' => '&#x1F3AF;',
                    'vibe' => '&#x1F4A1;',
                    'planner' => '&#x1F4CB;',
                    'analisador' => '&#x1F50D;',
                    'security' => '&#x1F512;',
                    'implementador' => '&#x26A1;',
                    'component' => '&#x1F9E9;',
                    'validador' => '&#x2705;',
                    'learning' => '&#x1F4DA;',
                    default => '&#x1F916;'
                  }; ?>
                </div>
                <div style="flex:1">
                  <div style="font-weight:600;color:var(--text-primary)"><?= htmlspecialchars($agent['name']) ?></div>
                  <div style="font-size:0.875rem;color:var(--txt-2)"><?= htmlspecialchars($agent['description']) ?></div>
                </div>
                <?= renderBadge(['label' => 'Ativo', 'variant' => 'badge-green', 'size' => 'sm']) ?>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- Fluxo de Trabalho -->
      <div class="card" style="margin-top:16px">
        <div class="card-head"><span class="card-title">Fluxo de Orquestração</span></div>
        <div class="card-body">
          <div style="display:flex;align-items:center;justify-content:center;gap:8px;flex-wrap:wrap;padding:16px">
            <?php
            $steps = [
              ['num' => 1, 'name' => 'VIBE', 'desc' => 'Interpretação'],
              ['num' => 2, 'name' => 'PLANNER', 'desc' => 'Planejamento'],
              ['num' => 3, 'name' => 'ANALISADOR', 'desc' => 'Análise'],
              ['num' => 4, 'name' => 'SECURITY', 'desc' => 'Validação'],
              ['num' => 5, 'name' => 'IMPLEMENTADOR', 'desc' => 'Execução'],
              ['num' => 6, 'name' => 'VALIDADOR', 'desc' => 'Qualidade'],
              ['num' => 7, 'name' => 'LEARNING', 'desc' => 'Aprendizado'],
            ];
            foreach ($steps as $i => $step): ?>
              <div style="text-align:center;padding:12px;background:var(--bg-tertiary);border-radius:8px;min-width:90px">
                <div style="width:24px;height:24px;background:var(--neon-cyan);color:var(--bg-primary);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:0.75rem;font-weight:700;margin:0 auto 8px"><?= $step['num'] ?></div>
                <div style="font-size:0.75rem;font-weight:600;color:var(--text-primary)"><?= $step['name'] ?></div>
                <div style="font-size:0.625rem;color:var(--txt-2)"><?= $step['desc'] ?></div>
              </div>
              <?php if ($i < count($steps) - 1): ?>
                <span style="color:var(--neon-cyan);font-size:1.25rem">&#x2192;</span>
              <?php endif; ?>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </section>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?>
