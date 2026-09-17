<?php
/**
 * AI Agent Detail - Detalhes de um Agente
 *
 * @var array $agent
 * @var array $metrics
 */
require_once dirname(__DIR__) . '/layout/header.php';

require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/button/button.php';
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/card/card.php';
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/badge/badge.php';

$agent = $agent ?? [];
$metrics = $metrics ?? [];
?>

    <section class="section active" id="sec-agent-detail">
      <div class="section-header">
        <div class="section-icon">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
          </svg>
        </div>
        <div>
          <div class="section-title"><?= htmlspecialchars($agent['name'] ?? 'Agente') ?></div>
          <div class="section-sub"><?= htmlspecialchars($agent['description'] ?? '') ?></div>
        </div>
      </div>
      <div class="divider"></div>

      <div style="margin-bottom:24px">
        <a href="<?= $baseUrl ?>/ai/agents" class="btn btn-sm" style="--btn-bg:var(--text-3);--btn-hover:var(--text-2)">
          Voltar para Agentes
        </a>
      </div>

      <div class="col2">
        <div>
          <?= renderCard([
              'title' => 'Informações do Agente',
              'content' => '
                <div class="fg">
                  <div class="fl">ID</div>
                  <div>' . htmlspecialchars($agent['id'] ?? '') . '</div>
                </div>
                <div class="fg">
                  <div class="fl">Nome</div>
                  <div>' . htmlspecialchars($agent['name'] ?? '') . '</div>
                </div>
                <div class="fg">
                  <div class="fl">Descrição</div>
                  <div>' . htmlspecialchars($agent['description'] ?? '') . '</div>
                </div>
              '
          ]) ?>
        </div>

        <div>
          <?= renderCard([
              'title' => 'Métricas',
              'content' => empty($metrics) ? '<p style="color:var(--text-3)">Nenhuma métrica disponível</p>' : '
                <div class="fg">
                  <div class="fl">Total de Execuções</div>
                  <div>' . ($metrics[0]['total_execucoes'] ?? 0) . '</div>
                </div>
                <div class="fg">
                  <div class="fl">Tempo Médio</div>
                  <div>' . ($metrics[0]['tempo_medio'] ?? '0') . 's</div>
                </div>
                <div class="fg">
                  <div class="fl">Score Médio</div>
                  <div>' . ($metrics[0]['score_medio'] ?? '0') . '</div>
                </div>
              '
          ]) ?>
        </div>
      </div>

    </section>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?>
