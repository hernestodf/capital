<?php
/**
 * AI Agents - Lista de Agentes
 *
 * @var array $agents
 */

require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/button/button.php';
require_once dirname(__DIR__, 2) . '/docs/layout/branco/assets/components/badge/badge.php';

require dirname(__DIR__) . '/layout/header.php';
?>

    <section class="section active" id="sec-ai-agents">
      <div class="section-header">
        <div class="section-icon">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
        </div>
        <div>
          <div class="section-title">Agentes do Sistema</div>
          <div class="section-sub"><?= count($agents) ?> agentes especializados disponíveis</div>
        </div>
      </div>
      <div class="divider"></div>

      <!-- Grid de Agentes -->
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px" class="agents-grid">
        <?php foreach ($agents as $agent): ?>
          <div class="card" style="transition:border-color 0.2s,box-shadow 0.2s" onmouseover="this.style.borderColor='var(--neon-cyan)';this.style.boxShadow='0 0 20px rgba(0,255,255,0.1)'" onmouseout="this.style.borderColor='';this.style.boxShadow=''">
            <div class="card-body" style="text-align:center">
              <div style="font-size:2.5rem;width:64px;height:64px;display:flex;align-items:center;justify-content:center;background:var(--bg-tertiary);border-radius:8px;margin:0 auto 12px">
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
              <div style="margin-bottom:8px"><?= renderBadge(['label' => 'Ativo', 'variant' => 'badge-green', 'size' => 'sm']) ?></div>
              <h3 style="font-size:1.125rem;font-weight:600;color:var(--text-primary);margin:0 0 8px"><?= htmlspecialchars($agent['name']) ?></h3>
              <p style="font-size:0.875rem;color:var(--txt-2);line-height:1.5;margin:0"><?= htmlspecialchars($agent['description']) ?></p>
              <div style="margin-top:16px;padding-top:12px;border-top:1px solid var(--border)">
                <a href="<?= $baseUrl ?>/ai/agent/<?= $agent['id'] ?>" class="btn btn-cyan btn-sm">Ver Detalhes</a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <style>
        @media (max-width:640px){.agents-grid{grid-template-columns:1fr!important}}
      </style>

      <!-- Documentação -->
      <div class="card" style="margin-top:24px">
        <div class="card-head"><span class="card-title">Como Funciona</span></div>
        <div class="card-body">
          <h4 style="color:var(--text-primary);margin-top:16px;margin-bottom:12px">Fluxo de Orquestração</h4>
          <p style="color:var(--txt-2)">O sistema utiliza um fluxo de 8 etapas para processar qualquer requisição:</p>
          <ol style="margin-left:24px;margin-bottom:16px;color:var(--txt-2)">
            <li><strong>VIBE</strong> - Interpreta a intenção do usuário</li>
            <li><strong>PLANNER</strong> - Divide em etapas executáveis</li>
            <li><strong>ANALISADOR</strong> - Mapeia impacto no sistema</li>
            <li><strong>SECURITY</strong> - Valida segurança</li>
            <li><strong>IMPLEMENTADOR</strong> - Gera código</li>
            <li><strong>COMPONENT</strong> - Cria/Reutiliza componentes</li>
            <li><strong>VALIDADOR</strong> - Testa e valida</li>
            <li><strong>LEARNING</strong> - Registra aprendizados</li>
          </ol>
        </div>
      </div>
    </section>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?>
