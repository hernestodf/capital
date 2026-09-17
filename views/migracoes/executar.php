<?php
// views/migracoes/executar.php
// Página segura para upload e execução de migrations SQL em produção
// Requer perfil de Administrador
?>
<?php require dirname(__DIR__) . '/layout/header.php'; ?>

<?php
require_once __DIR__ . '/../../docs/layout/branco/assets/components/card/card.php';
require_once __DIR__ . '/../../docs/layout/branco/assets/components/alert/alert.php';
require_once __DIR__ . '/../../docs/layout/branco/assets/components/button/button.php';
?>

<section class="section active">
    <div class="section-header">
        <div class="section-icon">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="28" height="28">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
            </svg>
        </div>
        <div>
            <div class="section-title">Executar Migration de Banco de Dados</div>
            <div class="section-sub">Ferramenta de manutenção — Apenas Administradores • Um clique para migrations do repositório</div>
        </div>
    </div>

    <div class="divider"></div>

    <!-- Alerta de segurança -->
    <?= renderAlert([
        'variant' => 'warning',
        'title' => 'Atenção - Operação Crítica',
        'message' => 'Esta página permite executar comandos SQL diretamente no banco de produção. Faça backup completo antes de prosseguir. Use apenas para migrations oficiais do projeto.'
    ]) ?>

    <?php if (!empty($mensagem)): ?>
        <?php
            $alertVariant = ($tipoMensagem === 'success') ? 'success' : (($tipoMensagem === 'error') ? 'error' : 'info');
        ?>
        <?= renderAlert([
            'variant' => $alertVariant,
            'title' => ($tipoMensagem === 'success') ? 'Sucesso' : 'Erro',
            'message' => $mensagem
        ]) ?>
    <?php endif; ?>

    <?php if (!empty($resultado)): ?>
        <div class="card" style="margin: 20px 0; background: #f8fafc; border: 1px solid #e2e8f0;">
            <div class="card-head">
                <span class="card-title">Resultado da Execução</span>
            </div>
            <div class="card-body">
                <pre style="background:#1e2937;color:#e0f2fe;padding:16px;border-radius:8px;font-size:13px;white-space:pre-wrap;"><?= htmlspecialchars($resultado) ?></pre>
            </div>
        </div>
    <?php endif; ?>

    <!-- Execução Rápida (One-Click) - Migrations do repositório -->
    <?php if (!empty($migrationsDisponiveis)): ?>
    <div class="card" style="margin-bottom:24px; border-left: 5px solid #10b981;">
        <div class="card-head">
            <span class="card-title">Migrations Disponíveis no Servidor (Execução Direta)</span>
        </div>
        <div class="card-body">
            <p style="margin-bottom:16px; color:var(--text-2)">
                Clique no botão abaixo para executar automaticamente a migration sem precisar fazer upload manual.
            </p>

            <form method="POST" action="<?= $baseUrl ?>/migracoes/executar" style="display:inline-block; margin-right:12px;">
                <input type="hidden" name="_csrf_token" value="<?= $csrfToken ?>">
                <input type="hidden" name="executar_arquivo" value="029_otimizar_consulta_outros_custos_fechamento.sql">
                <button type="submit" class="btn btn-green" style="font-weight:600"
                        data-action="confirm-executar-migration" data-migration="029_otimizar_consulta_outros_custos_fechamento.sql">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" width="18" height="18" style="margin-right:8px;vertical-align:middle">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7-7 7m-4 0l7-7-7-7" />
                    </svg>
                    Executar Migration 029 - Outros Custos (Índice)
                </button>
            </form>

            <!-- Outras migrations disponíveis -->
            <details style="margin-top:16px;">
                <summary style="cursor:pointer; color:var(--cyan); font-weight:500">Ver todas as migrations disponíveis (<?= count($migrationsDisponiveis) ?>)</summary>
                <div style="margin-top:12px; display:flex; flex-wrap:wrap; gap:8px;">
                    <?php foreach (array_slice($migrationsDisponiveis, 0, 8) as $mig): ?>
                        <form method="POST" action="<?= $baseUrl ?>/migracoes/executar" style="display:inline;">
                            <input type="hidden" name="_csrf_token" value="<?= $csrfToken ?>">
                            <input type="hidden" name="executar_arquivo" value="<?= htmlspecialchars($mig) ?>">
                            <button type="submit" class="btn btn-sm btn-gray"
                                    data-action="confirm-executar-migration" data-migration="<?= htmlspecialchars($mig) ?>">
                                <?= htmlspecialchars($mig) ?>
                            </button>
                        </form>
                    <?php endforeach; ?>
                </div>
            </details>
        </div>
    </div>
    <?php endif; ?>

    <!-- Formulário de Upload Manual -->
    <div class="card">
        <div class="card-head">
            <span class="card-title">Upload Manual de Migration</span>
        </div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data" action="<?= $baseUrl ?>/migracoes/executar">
                <input type="hidden" name="_csrf_token" value="<?= $csrfToken ?>">

                <div class="fg">
                    <div class="fl">Arquivo SQL da Migration</div>
                    <input type="file" name="migration_file" accept=".sql,text/sql" class="fi" required style="padding:12px;">
                    <div style="font-size:12px;color:var(--text-3);margin-top:4px">
                        Use esta opção para migrations que ainda não estão no repositório.
                    </div>
                </div>

                <div style="margin-top:24px; display:flex; gap:12px; align-items:center;">
                    <button type="submit" class="btn btn-red" style="font-weight:600">
                        Fazer Upload e Executar
                    </button>
                    <a href="<?= $baseUrl ?>/dashboard" class="btn btn-gray">Cancelar</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card" style="margin-top:20px; border-left: 5px solid #f59e0b;">
        <div class="card-body">
            <strong style="color:#b45309">Dica:</strong> 
            Para a migration dos "Outros Custos", use o arquivo <code>029_otimizar_consulta_outros_custos_fechamento.sql</code> 
            que adiciona o índice de performance na tabela <code>contas_pagar</code>.
        </div>
    </div>

</section>

<?php require dirname(__DIR__) . '/layout/footer.php'; ?>
