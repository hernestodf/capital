<?php

use App\Core\Application;

// Auto-detecta raiz do projeto: local (public/ separado) ou cPanel (tudo na raiz do subdomínio)
$appRoot = file_exists(dirname(__DIR__) . '/vendor/autoload.php')
    ? dirname(__DIR__)
    : __DIR__;
require_once $appRoot . '/vendor/autoload.php';

// CORS: Permitir requisições cross-origin para integração com frontend
$corsOrigin = '*';
if (isset($_SERVER['HTTP_ORIGIN'])) {
    $allowedOrigins = ['https://profox.sisloc.online', 'https://profoxmt.sisloc.online', 'https://profoxba.sisloc.online', 'https://localhost', 'http://localhost'];
    $origin = $_SERVER['HTTP_ORIGIN'];
    if (in_array($origin, $allowedOrigins)) {
        $corsOrigin = $origin;
    }
}
header("Access-Control-Allow-Origin: $corsOrigin");
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key, X-Requested-With');
header('Access-Control-Allow-Credentials: true');

// Tratamento para requisições OPTIONS (CORS preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$app = Application::getInstance();

// Redirecionar raiz para login ou dashboard
$app->router()->get('/', function() use ($app) {
    if (\App\Auth\Rbac::isGuest()) {
        return $app->redirect('/auth/login');
    }
    return $app->redirect(\App\Auth\Rbac::getDashboardRoute());
});

// Grupo de autenticação (público)
$app->router()->group('/auth', function($router) {
    $router->get('/login', [App\Controllers\AuthController::class, 'login']);
    $router->post('/login', [App\Controllers\AuthController::class, 'doLogin']);
    $router->get('/logout', [App\Controllers\AuthController::class, 'logout']);
    $router->post('/logout', [App\Controllers\AuthController::class, 'logout']);
    $router->get('/session-info', [App\Controllers\AuthController::class, 'sessionInfo']);
});

// Grupo usuarios (com middleware de autenticação)
$app->router()->group('/usuarios', function($router) {
    $router->get('/', [App\Controllers\UsuarioController::class, 'index']);
    $router->get('/create', [App\Controllers\UsuarioController::class, 'create']);
    $router->post('/store', [App\Controllers\UsuarioController::class, 'store']);
    $router->get('/edit/{id}', [App\Controllers\UsuarioController::class, 'edit']);
    $router->post('/update/{id}', [App\Controllers\UsuarioController::class, 'update']);
    $router->post('/delete/{id}', [App\Controllers\UsuarioController::class, 'delete']);
    $router->post('/toggle/{id}', [App\Controllers\UsuarioController::class, 'toggle']);
}, [\App\Http\Middleware\AuthMiddleware::class, \App\Http\Middleware\CsrfMiddleware::class]);

// Grupo clientes (com middleware de autenticação)
$app->router()->group('/clientes', function($router) {
    $router->get('/', [App\Controllers\ClienteController::class, 'index']);
    $router->get('/create', [App\Controllers\ClienteController::class, 'create']);
    $router->post('/store', [App\Controllers\ClienteController::class, 'store']);
    $router->get('/edit/{id}', [App\Controllers\ClienteController::class, 'edit']);
    $router->post('/update/{id}', [App\Controllers\ClienteController::class, 'update']);
    $router->post('/delete/{id}', [App\Controllers\ClienteController::class, 'delete']);
    $router->post('/toggle/{id}', [App\Controllers\ClienteController::class, 'toggle']);
}, [\App\Http\Middleware\AuthMiddleware::class, \App\Http\Middleware\CsrfMiddleware::class]);

// Grupo demandantes (com middleware de autenticação)
$app->router()->group('/compradores', function($router) {
    $router->get('/', [App\Controllers\DemandanteController::class, 'index']);
    $router->get('/create', [App\Controllers\DemandanteController::class, 'create']);
    $router->post('/store', [App\Controllers\DemandanteController::class, 'store']);
    $router->get('/edit/{id}', [App\Controllers\DemandanteController::class, 'edit']);
    $router->post('/update/{id}', [App\Controllers\DemandanteController::class, 'update']);
    $router->post('/delete/{id}', [App\Controllers\DemandanteController::class, 'delete']);
    $router->post('/toggle/{id}', [App\Controllers\DemandanteController::class, 'toggle']);
}, [\App\Http\Middleware\AuthMiddleware::class, \App\Http\Middleware\CsrfMiddleware::class]);

// Grupo configurações (com middleware de autenticação)
$app->router()->group('/configuracoes', function($router) {
    $router->get('/', [App\Controllers\ConfiguracoesController::class, 'index']);
    $router->post('/update', [App\Controllers\ConfiguracoesController::class, 'update']);
    $router->post('/update-roles', [App\Controllers\ConfiguracoesController::class, 'updateRoles']);
    $router->post('/update-theme', [App\Controllers\ConfiguracoesController::class, 'updateTheme']);
    // WhatsApp endpoints
    $router->get('/whatsapp-status', [App\Controllers\ConfiguracoesController::class, 'whatsappStatus']);
    $router->get('/whatsapp-qr', [App\Controllers\ConfiguracoesController::class, 'whatsappQr']);
    $router->post('/whatsapp-restart', [App\Controllers\ConfiguracoesController::class, 'whatsappRestart']);
    $router->post('/whatsapp-logout', [App\Controllers\ConfiguracoesController::class, 'whatsappLogout']);
    $router->post('/whatsapp-test', [App\Controllers\ConfiguracoesController::class, 'whatsappTest']);
}, [\App\Http\Middleware\AuthMiddleware::class, \App\Http\Middleware\CsrfMiddleware::class]);

// Grupo de ferramentas / migrations (apenas administradores)
$app->router()->group('/migracoes', function($router) {
    $router->get('/executar', [App\Controllers\MigracoesController::class, 'executar']);
    $router->post('/executar', [App\Controllers\MigracoesController::class, 'executar']);
}, [\App\Http\Middleware\AuthMiddleware::class, \App\Http\Middleware\CsrfMiddleware::class]);


// ==================== DASHBOARDS POR PERFIL ====================
$app->router()->group('/dashboard', function($router) {
    // Dashboard principal (redireciona baseado no perfil)
    $router->get('/', [App\Controllers\DashboardController::class, 'index']);

    // Dashboard específicos por perfil
    $router->get('/administrativo', [App\Controllers\DashboardController::class, 'administrativo']);
    $router->get('/comercial', [App\Controllers\DashboardController::class, 'comercial']);
    $router->get('/estoquista', [App\Controllers\DashboardController::class, 'estoquista']);
}, [\App\Http\Middleware\AuthMiddleware::class, \App\Http\Middleware\CsrfMiddleware::class]);

// ==================== ROTAS WEB - SISTEMA DE AGENTES IA ====================
$app->router()->group('/ai', function($router) {
    // Dashboard e visualização
    $router->get('/', [App\Controllers\AIController::class, 'dashboard']);
    $router->get('/agents', [App\Controllers\AIController::class, 'agents']);
    $router->get('/agent/{id}', [App\Controllers\AIController::class, 'agentDetail']);
    $router->get('/learning', [App\Controllers\AIController::class, 'learning']);

    // Ações
    $router->post('/execute', [App\Controllers\AIController::class, 'execute']);
    $router->post('/orchestrate', [App\Controllers\AIController::class, 'orchestrate']);
}, [\App\Http\Middleware\AuthMiddleware::class, \App\Http\Middleware\CsrfMiddleware::class]);

// Grupo contas a pagar (com middleware de autenticacao)
$app->router()->group('/contas-pagar', function($router) {
    $router->get('/', [App\Controllers\ContasPagarController::class, 'index']);
    $router->get('/create', [App\Controllers\ContasPagarController::class, 'create']);
    $router->post('/store', [App\Controllers\ContasPagarController::class, 'store']);
    $router->get('/edit/{id}', [App\Controllers\ContasPagarController::class, 'edit']);
    $router->post('/update/{id}', [App\Controllers\ContasPagarController::class, 'update']);
    $router->post('/delete/{id}', [App\Controllers\ContasPagarController::class, 'delete']);
    $router->post('/pagar/{id}', [App\Controllers\ContasPagarController::class, 'pagar']);
    $router->post('/enviar-comprovante/{id}', [App\Controllers\ContasPagarController::class, 'enviarComprovante']);
    $router->get('/whatsapp-status', [App\Controllers\ContasPagarController::class, 'whatsappStatus']);
    $router->post('/whatsapp-restart', [App\Controllers\ContasPagarController::class, 'whatsappRestart']);
    $router->post('/whatsapp-logout', [App\Controllers\ContasPagarController::class, 'whatsappLogout']);
}, [\App\Http\Middleware\AuthMiddleware::class, \App\Http\Middleware\CsrfMiddleware::class]);

// Grupo fornecedores (com middleware de autenticação)
$app->router()->group('/fornecedores', function($router) {
    $router->get('/', [App\Controllers\FornecedorController::class, 'index']);
    $router->get('/create', [App\Controllers\FornecedorController::class, 'create']);
    $router->post('/store', [App\Controllers\FornecedorController::class, 'store']);
    $router->get('/edit/{id}', [App\Controllers\FornecedorController::class, 'edit']);
    $router->post('/update/{id}', [App\Controllers\FornecedorController::class, 'update']);
    $router->post('/delete/{id}', [App\Controllers\FornecedorController::class, 'delete']);
    $router->post('/toggle/{id}', [App\Controllers\FornecedorController::class, 'toggle']);
    $router->get('/api/subcategorias/{idCategoria}', [App\Controllers\FornecedorController::class, 'getSubcategorias']);
}, [\App\Http\Middleware\AuthMiddleware::class, \App\Http\Middleware\CsrfMiddleware::class]);

// Grupo categorias (AJAX CRUD - com middleware de autenticação)
$app->router()->group('/categorias', function($router) {
    $router->get('/list', [App\Controllers\CategoriaController::class, 'listAll']);
    $router->post('/store', [App\Controllers\CategoriaController::class, 'store']);
    $router->post('/update/{id}', [App\Controllers\CategoriaController::class, 'update']);
    $router->post('/delete/{id}', [App\Controllers\CategoriaController::class, 'delete']);
}, [\App\Http\Middleware\AuthMiddleware::class, \App\Http\Middleware\CsrfMiddleware::class]);

// Grupo subcategorias (AJAX CRUD - com middleware de autenticação)
$app->router()->group('/subcategorias', function($router) {
    $router->get('/list/{idCategoria}', [App\Controllers\SubcategoriaController::class, 'listByCategoria']);
    $router->post('/store', [App\Controllers\SubcategoriaController::class, 'store']);
    $router->post('/update/{id}', [App\Controllers\SubcategoriaController::class, 'update']);
    $router->post('/delete/{id}', [App\Controllers\SubcategoriaController::class, 'delete']);
}, [\App\Http\Middleware\AuthMiddleware::class, \App\Http\Middleware\CsrfMiddleware::class]);

// Grupo colaboradores (com middleware de autenticação)
$app->router()->group('/colaboradores', function($router) {
    $router->get('/', [App\Controllers\ColaboradorController::class, 'index']);
    $router->get('/create', [App\Controllers\ColaboradorController::class, 'create']);
    $router->post('/store', [App\Controllers\ColaboradorController::class, 'store']);
    $router->get('/edit/{id}', [App\Controllers\ColaboradorController::class, 'edit']);
    $router->post('/update/{id}', [App\Controllers\ColaboradorController::class, 'update']);
    $router->post('/delete/{id}', [App\Controllers\ColaboradorController::class, 'delete']);
    $router->post('/toggle/{id}', [App\Controllers\ColaboradorController::class, 'toggle']);
    $router->post('/enviar-link/{id}', [App\Controllers\ColaboradorController::class, 'enviarLink']);
    $router->post('/reenviar-link/{id}', [App\Controllers\ColaboradorController::class, 'reenviarLink']);
    $router->get('/foto/{id}', [App\Controllers\ColaboradorController::class, 'foto']);
    $router->post('/upload-foto', [App\Controllers\ColaboradorController::class, 'uploadFoto']);
}, [\App\Http\Middleware\AuthMiddleware::class, \App\Http\Middleware\CsrfMiddleware::class]);

// Grupo produtores (com middleware de autenticação)
$app->router()->group('/produtores', function($router) {
    $router->get('/', [App\Controllers\ProdutorController::class, 'index']);
    $router->get('/create', [App\Controllers\ProdutorController::class, 'create']);
    $router->post('/store', [App\Controllers\ProdutorController::class, 'store']);
    $router->get('/edit/{id}', [App\Controllers\ProdutorController::class, 'edit']);
    $router->post('/update/{id}', [App\Controllers\ProdutorController::class, 'update']);
    $router->post('/delete/{id}', [App\Controllers\ProdutorController::class, 'delete']);
    $router->post('/toggle/{id}', [App\Controllers\ProdutorController::class, 'toggle']);
}, [\App\Http\Middleware\AuthMiddleware::class, \App\Http\Middleware\CsrfMiddleware::class]);

// Grupo eventos (com middleware de autenticação)
$app->router()->group('/eventos', function($router) {
    $router->get('/', [App\Controllers\EventoController::class, 'index']);
    $router->get('/create', [App\Controllers\EventoController::class, 'create']);
    $router->post('/store', [App\Controllers\EventoController::class, 'store']);
    $router->get('/edit/{id}', [App\Controllers\EventoController::class, 'edit']);
    $router->post('/update/{id}', [App\Controllers\EventoController::class, 'update']);
    $router->post('/delete/{id}', [App\Controllers\EventoController::class, 'delete']);
    $router->post('/toggle/{id}', [App\Controllers\EventoController::class, 'toggle']);
    $router->post('/converter/{id}', [App\Controllers\EventoController::class, 'converter']);
    $router->post('/finalizar/{id}', [App\Controllers\EventoController::class, 'finalizar']);
    // Rotas para itens de salas e produtos
    $router->post('/itens/adicionar', [App\Controllers\EventoController::class, 'adicionarItem']);
    $router->post('/itens/excluir', [App\Controllers\EventoController::class, 'excluirItem']);
    $router->post('/itens/{id}/atualizar-campo', [App\Controllers\EventoController::class, 'atualizarCampo']);
    $router->get('/pdf/{id}', [App\Controllers\EventoController::class, 'gerarPdf']);
}, [\App\Http\Middleware\AuthMiddleware::class, \App\Http\Middleware\CsrfMiddleware::class]);

// API para autocomplete de itens (AJAX)
$app->router()->group('/api/eventos', function($router) {
    $router->get('/itens', [App\Controllers\EventoController::class, 'listarItensPlanilha']);
    $router->get('/salas', [App\Controllers\SalaController::class, 'listByEvento']);
    $router->get('/salas/{id}', [App\Controllers\SalaController::class, 'listByEvento']);
    $router->post('/salas', [App\Controllers\SalaController::class, 'store']);
    $router->put('/salas/{id}', [App\Controllers\SalaController::class, 'update']);
    $router->delete('/salas/{id}', [App\Controllers\SalaController::class, 'delete']);
    $router->post('/itens/{id}/observacao', [App\Controllers\EventoController::class, 'salvarObsItem']);
}, [\App\Http\Middleware\AuthMiddleware::class, \App\Http\Middleware\CsrfMiddleware::class]);

// Grupo salas (com middleware de autenticação)
$app->router()->group('/salas', function($router) {
    $router->get('/list/{idEvento}', [App\Controllers\SalaController::class, 'index']);
    $router->post('/store', [App\Controllers\SalaController::class, 'store']);
    $router->post('/update/{id}', [App\Controllers\SalaController::class, 'update']);
    $router->post('/delete/{id}', [App\Controllers\SalaController::class, 'delete']);
    $router->post('/toggle/{id}', [App\Controllers\SalaController::class, 'toggle']);
}, [\App\Http\Middleware\AuthMiddleware::class, \App\Http\Middleware\CsrfMiddleware::class]);

// Grupo produtos-evento (com middleware de autenticação)
$app->router()->group('/produtos-evento', function($router) {
    $router->get('/list-sala/{idSala}', [App\Controllers\ProdutoEventoController::class, 'indexBySala']);
    $router->get('/list-evento/{idEvento}', [App\Controllers\ProdutoEventoController::class, 'indexByEvento']);
    $router->get('/autocomplete', [App\Controllers\ProdutoEventoController::class, 'autocomplete']);
    $router->post('/store', [App\Controllers\ProdutoEventoController::class, 'store']);
    $router->post('/update/{id}', [App\Controllers\ProdutoEventoController::class, 'update']);
    $router->post('/delete/{id}', [App\Controllers\ProdutoEventoController::class, 'delete']);
}, [\App\Http\Middleware\AuthMiddleware::class, \App\Http\Middleware\CsrfMiddleware::class]);

// Grupo categorias-sala (com middleware de autenticação)
$app->router()->group('/categorias-sala', function($router) {
    $router->get('/', [App\Controllers\CategoriaSalaController::class, 'index']);
    $router->get('/list', [App\Controllers\CategoriaSalaController::class, 'listAll']);
    $router->post('/store', [App\Controllers\CategoriaSalaController::class, 'store']);
    $router->post('/update/{id}', [App\Controllers\CategoriaSalaController::class, 'update']);
    $router->post('/delete/{id}', [App\Controllers\CategoriaSalaController::class, 'delete']);
    $router->post('/toggle/{id}', [App\Controllers\CategoriaSalaController::class, 'toggle']);
}, [\App\Http\Middleware\AuthMiddleware::class, \App\Http\Middleware\CsrfMiddleware::class]);

// API categorias (alias para categorias-sala)
$app->router()->group('/api', function($router) {
    $router->get('/categorias', [App\Controllers\CategoriaSalaController::class, 'listAll']);
    $router->post('/categorias', [App\Controllers\CategoriaSalaController::class, 'store']);
    $router->put('/categorias/{id}', [App\Controllers\CategoriaSalaController::class, 'update']);
    $router->delete('/categorias/{id}', [App\Controllers\CategoriaSalaController::class, 'delete']);
}, [\App\Http\Middleware\AuthMiddleware::class, \App\Http\Middleware\CsrfMiddleware::class]);

// Grupo planilhas (com middleware de autenticação)
$app->router()->group('/planilhas', function($router) {
    $router->get('/', [App\Controllers\PlanilhaController::class, 'index']);
    $router->get('/create', [App\Controllers\PlanilhaController::class, 'create']);
    $router->post('/store', [App\Controllers\PlanilhaController::class, 'store']);
    $router->get('/edit/{id}', [App\Controllers\PlanilhaController::class, 'edit']);
    $router->post('/update/{id}', [App\Controllers\PlanilhaController::class, 'update']);
    $router->post('/delete/{id}', [App\Controllers\PlanilhaController::class, 'delete']);
    $router->post('/upload-contrato', [App\Controllers\PlanilhaController::class, 'uploadContrato']);
}, [\App\Http\Middleware\AuthMiddleware::class, \App\Http\Middleware\CsrfMiddleware::class]);

// Grupo unidades de medida (AJAX - com middleware de autenticação)
$app->router()->group('/unidades-medida', function($router) {
    $router->get('/list', [App\Controllers\UnidadeMedidaController::class, 'listAll']);
    $router->post('/store', [App\Controllers\UnidadeMedidaController::class, 'store']);
    $router->post('/update/{id}', [App\Controllers\UnidadeMedidaController::class, 'update']);
    $router->post('/delete/{id}', [App\Controllers\UnidadeMedidaController::class, 'delete']);
}, [\App\Http\Middleware\AuthMiddleware::class, \App\Http\Middleware\CsrfMiddleware::class]);

// Grupo estoque (com middleware de autenticação)
$app->router()->group('/estoque', function($router) {
    $router->get('/', [App\Controllers\ProdutoController::class, 'index']);
    $router->get('/create', [App\Controllers\ProdutoController::class, 'create']);
    $router->post('/store', [App\Controllers\ProdutoController::class, 'store']);
    $router->get('/edit/{id}', [App\Controllers\ProdutoController::class, 'edit']);
    $router->post('/update/{id}', [App\Controllers\ProdutoController::class, 'update']);
    $router->post('/delete/{id}', [App\Controllers\ProdutoController::class, 'delete']);
    $router->post('/toggle-locado/{id}', [App\Controllers\ProdutoController::class, 'toggleLocado']);
    $router->get('/seriais/{id}', [App\Controllers\ProdutoController::class, 'seriais']);
    $router->get('/relatorios', [App\Controllers\ProdutoController::class, 'relatorios']);
    $router->post('/relatorios/gerar', [App\Controllers\ProdutoController::class, 'gerarRelatorio']);
}, [\App\Http\Middleware\AuthMiddleware::class, \App\Http\Middleware\CsrfMiddleware::class]);

// Grupo secao (AJAX - com middleware de autenticação)
$app->router()->group('/secoes', function($router) {
    $router->get('/list', [App\Controllers\SecaoController::class, 'listAll']);
    $router->post('/store', [App\Controllers\SecaoController::class, 'store']);
    $router->post('/update/{id}', [App\Controllers\SecaoController::class, 'update']);
    $router->post('/delete/{id}', [App\Controllers\SecaoController::class, 'delete']);
}, [\App\Http\Middleware\AuthMiddleware::class, \App\Http\Middleware\CsrfMiddleware::class]);

// Grupo seriais (AJAX - com middleware de autenticação)
$app->router()->group('/seriais', function($router) {
    $router->get('/list/{idProduto}', [App\Controllers\SerialProdutoController::class, 'listByProduto']);
    $router->get('/get-serial/{id}', [App\Controllers\SerialProdutoController::class, 'getSerial']);
    $router->post('/store', [App\Controllers\SerialProdutoController::class, 'store']);
    $router->post('/store-batch', [App\Controllers\SerialProdutoController::class, 'storeBatch']);
    $router->post('/update/{id}', [App\Controllers\SerialProdutoController::class, 'update']);
    $router->post('/update-status/{id}', [App\Controllers\SerialProdutoController::class, 'updateStatus']);
    $router->post('/delete/{id}', [App\Controllers\SerialProdutoController::class, 'delete']);
}, [\App\Http\Middleware\AuthMiddleware::class, \App\Http\Middleware\CsrfMiddleware::class]);

// Grupo montagem (com middleware de autenticação) — endpoints AJAX para edit-montar-os.php
$app->router()->group('/montagem', function($router) {
    $router->post('/inserir-serial', [App\Controllers\MontagemController::class, 'inserirSerial']);
    $router->post('/inserir-lote', [App\Controllers\MontagemController::class, 'inserirLote']);
    $router->post('/encaminhar-sala/{id}', [App\Controllers\MontagemController::class, 'encaminharSala']);
    $router->post('/remover-da-sala/{id}', [App\Controllers\MontagemController::class, 'removerDaSala']);
    $router->post('/devolver/{id}', [App\Controllers\MontagemController::class, 'devolver']);
    $router->get('/listar/{idEvento}', [App\Controllers\MontagemController::class, 'listar']);
    $router->get('/pdf/{id}', [App\Controllers\EventoController::class, 'gerarPdfMontagem']);
}, [\App\Http\Middleware\AuthMiddleware::class, \App\Http\Middleware\CsrfMiddleware::class]);

// Grupo devolucao (com middleware de autenticação) — endpoints AJAX para edit-devolver-os.php
$app->router()->group('/devolucao', function($router) {
    $router->post('/processar-unico', [App\Controllers\DevolucaoController::class, 'processarUnico']);
    $router->post('/processar-lote', [App\Controllers\DevolucaoController::class, 'processarLote']);
    $router->post('/resolver-pendencia/{id}', [App\Controllers\DevolucaoController::class, 'resolverPendencia']);
    $router->post('/alterar-status/{id}', [App\Controllers\DevolucaoController::class, 'alterarStatus']);
    $router->get('/listar/{idEvento}', [App\Controllers\DevolucaoController::class, 'listar']);
    $router->get('/stats/{idEvento}', [App\Controllers\DevolucaoController::class, 'stats']);
}, [\App\Http\Middleware\AuthMiddleware::class, \App\Http\Middleware\CsrfMiddleware::class]);

// Diagnostico de rotas - cotacao
// Proxy para APIs externas (ViaCEP, BrasilAPI) - CSRF permitido (apenas GET)
$app->router()->get('/proxy/cnpj/{cnpj}', [App\Controllers\ProxyController::class, 'cnpj'], [\App\Http\Middleware\AuthMiddleware::class]);
$app->router()->get('/proxy/cep/{cep}', [App\Controllers\ProxyController::class, 'cep'], [\App\Http\Middleware\AuthMiddleware::class]);

$app->router()->get('/test/diag', [App\Controllers\TestDiagController::class, 'index'], [\App\Http\Middleware\AuthMiddleware::class, \App\Http\Middleware\CsrfMiddleware::class]);

// CRON endpoints (protegido por header X-Cron-Secret)
$app->router()->group('/cron', function($router) {
    $router->get('/imap-cotacao', [App\Controllers\CronImapController::class, 'processarEmails']);
    $router->get('/rh-notificacoes', function() {
        $service = new \App\Service\EventoRHNotificacaoService();
        $result = $service->enviarNotificacoes();
        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    });
}, [\App\Http\Middleware\CronAuthMiddleware::class]);

// ==================== WEBHOOK — ProFox → SisLoc (sem middleware de sessão) ====================
// Auth própria via X-Webhook-Key; notifica mudanças de categorias/subcategorias
$app->router()->post('/api/webhook/categorias', [App\Controllers\WebhookSyncController::class, 'sync']);

// ==================== ROTAS PÚBLICAS - CADASTRO SITE PROFOX ====================
// SEM middleware - acesso público para formulários do site
$app->router()->post('/cadastro-colaborador', [App\Controllers\PublicoController::class, 'storeColaborador']);
$app->router()->post('/cadastro-fornecedor', [App\Controllers\PublicoController::class, 'storeFornecedor']);

// Rotas públicas para categorias/subcategorias/funções (site ProFox)
$app->router()->get('/categorias', [App\Controllers\PublicoCategoriaController::class, 'categorias']);
$app->router()->get('/subcategorias/{id}', [App\Controllers\PublicoCategoriaController::class, 'subcategorias']);
$app->router()->get('/funcoes', [App\Controllers\PublicoCategoriaController::class, 'funcoes']);

// Tratamento para requisições OPTIONS (CORS preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    if (isset($_SERVER['HTTP_ORIGIN'])) {
    $allowedOrigins = ['https://profox.sisloc.online', 'https://profoxmt.sisloc.online', 'https://profoxba.sisloc.online', 'https://localhost', 'http://localhost'];
    $origin = $_SERVER['HTTP_ORIGIN'];
    if (in_array($origin, $allowedOrigins)) {
        header("Access-Control-Allow-Origin: $origin");
    }
} else {
    header("'Access-Control-Allow-Origin: *'");
}
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    header('Access-Control-Max-Age: 86400');
    http_response_code(200);
    exit;
}

// Grupo verificacao (SEM middleware - acesso público via token)
$app->router()->group('/colaboradores/verificar', function($router) {
    $router->get('/{token}', [App\Controllers\VerificacaoController::class, 'mostrarFormulario']);
    $router->post('/{token}', [App\Controllers\VerificacaoController::class, 'processarVerificacao']);
});

// Grupo RH evento (com auth middleware)
$app->router()->group('/evento/rh', function($router) {
    $router->get('/listar/{idEvento}', [App\Controllers\EventoRHController::class, 'listar']);
    $router->get('/presencas/{idAlocacao}', [App\Controllers\EventoRHController::class, 'verPresencas']);
    $router->post('/alocar', [App\Controllers\EventoRHController::class, 'alocar']);
    $router->post('/desalocar/{id}', [App\Controllers\EventoRHController::class, 'desalocar']);
    $router->post('/pagamento/{id}', [App\Controllers\EventoRHController::class, 'registrarPagamento']);
    $router->post('/enviar-pagamento/{id}', [App\Controllers\EventoRHController::class, 'enviarPagamento']);
    $router->post('/reenviar-comprovante/{id}', [App\Controllers\EventoRHController::class, 'reenviarComprovante']);
}, [\App\Http\Middleware\AuthMiddleware::class, \App\Http\Middleware\CsrfMiddleware::class]);

// Grupo presenca publica (SEM middleware - acesso via token)
$app->router()->group('/presenca', function($router) {
    $router->get('/confirmar-alocacao/{token}', [App\Controllers\PresencaPublicController::class, 'confirmarAlocacaoPage']);
    $router->post('/confirmar-alocacao/{token}', [App\Controllers\PresencaPublicController::class, 'confirmarAlocacaoAction']);
    $router->get('/{token}', [App\Controllers\PresencaPublicController::class, 'mostrarFormulario']);
    $router->post('/{token}', [App\Controllers\PresencaPublicController::class, 'registrarPresenca']);
});

// CRON ROTA RECURSOS HUMANOS DIARIOS
$app->router()->get('/cron/presencas-diarias', [App\Controllers\CronRHController::class, 'presencasDiarias']);

// FECHAMENTO DE EVENTOS
$app->router()->group('/fechamento', function($router) {
    // Dados
    $router->get('/colaboradores/{id}', [App\Controllers\FechamentoController::class, 'colaboradores']);
    $router->get('/horas-extras/{idAlocacao}', [App\Controllers\FechamentoController::class, 'listarHorasExtras']);
    $router->post('/horas-extras/lancar', [App\Controllers\FechamentoController::class, 'lancarHoraExtra']);
    $router->post('/horas-extras/deletar/{id}', [App\Controllers\FechamentoController::class, 'deletarHoraExtra']);
    $router->get('/presencas/{idAlocacao}', [App\Controllers\FechamentoController::class, 'presencas']);
    $router->post('/presenca/manual', [App\Controllers\FechamentoController::class, 'presencaManual']);
    $router->get('/fornecedor/contas/{idCotacao}', [App\Controllers\FechamentoController::class, 'fornecedorContas']);
    $router->get('/fornecedores/{id}', [App\Controllers\FechamentoController::class, 'fornecedores']);
    $router->get('/totais/{id}', [App\Controllers\FechamentoController::class, 'totais']);

    // Outros Custos (aba 4) — pagamento gerenciado em /contas-pagar
    $router->get('/outros/{id}', [App\Controllers\FechamentoController::class, 'outros']);
    $router->post('/outro/store', [App\Controllers\FechamentoController::class, 'storeOutro']);
    $router->post('/outro/update/{id}', [App\Controllers\FechamentoController::class, 'updateOutro']);
    $router->post('/outro/delete/{id}', [App\Controllers\FechamentoController::class, 'deleteOutro']);
    $router->post('/outro/enviar/{id}', [App\Controllers\FechamentoController::class, 'enviarOutro']);
    // Nota: /outro/pagar/{id} removido — pagamento via /contas-pagar

    // Pagamento
    $router->post('/colaborador/pagamento', [App\Controllers\FechamentoController::class, 'enviarColaboradorPagamento']);
    $router->post('/fornecedor/pagamento', [App\Controllers\FechamentoController::class, 'enviarFornecedorPagamento']);

    // Fotos
    $router->get('/fotos/{id}', [App\Controllers\FechamentoController::class, 'listarFotos']);
    $router->post('/foto/upload', [App\Controllers\FechamentoController::class, 'uploadFoto']);
    $router->post('/foto/remover', [App\Controllers\FechamentoController::class, 'removerFoto']);

    // PDF
    $router->get('/pdf/{id}', [App\Controllers\FechamentoController::class, 'pdfFechamento']);
}, [\App\Http\Middleware\AuthMiddleware::class, \App\Http\Middleware\CsrfMiddleware::class]);

// ==================== ROTAS API - SISTEMA DE AGENTES IA (COM AUTH) ====================
$app->router()->group('/api/ai', function($router) {
    // Health e info
    $router->get('/health', [App\Controllers\AIController::class, 'apiHealth']);
    $router->get('/stats', [App\Controllers\AIController::class, 'apiStats']);
    $router->get('/agents', [App\Controllers\AIController::class, 'apiAgents']);

    // Execução
    $router->post('/execute', [App\Controllers\AIController::class, 'apiExecute']);
    $router->post('/orchestrate', [App\Controllers\AIController::class, 'apiOrchestrate']);

    // Aprendizado
    $router->get('/patterns', [App\Controllers\AIController::class, 'apiGetPatterns']);
    $router->post('/learn/pattern', [App\Controllers\AIController::class, 'apiLearnPattern']);
    $router->post('/learn/metric', [App\Controllers\AIController::class, 'apiLearnMetric']);

    // Feedback
    $router->get('/feedbacks', [App\Controllers\AIController::class, 'apiGetFeedbacks']);
    $router->post('/learn/feedback', [App\Controllers\AIController::class, 'apiLearnFeedback']);

    // Interações
    $router->post('/learn/interaction', [App\Controllers\AIController::class, 'apiLearnInteraction']);

    // Preferências
    $router->get('/preferences', [App\Controllers\AIController::class, 'apiGetPreferences']);
    $router->post('/learn/preference', [App\Controllers\AIController::class, 'apiLearnPreference']);
}, [\App\Http\Middleware\AuthMiddleware::class, \App\Http\Middleware\CsrfMiddleware::class]);

// ============================================
// GRUPO API v1 - Integração Externa (API Key)
// ============================================
$app->router()->group('/api/v1', function($router) {
    // Autenticação
    $router->post('/auth/login',      [App\Controllers\Api\AuthApiController::class, 'login']);
    $router->get('/auth/session',     [App\Controllers\Api\AuthApiController::class, 'session']);
    $router->post('/auth/logout',     [App\Controllers\Api\AuthApiController::class, 'logout']);

    // Sessao (cookie PHP — sem CSRF, para appmontagem)
    $router->post('/sessao/login',    [App\Controllers\Api\SessaoApiController::class, 'login']);
    $router->get('/eventos/ativos',   [App\Controllers\Api\SessaoApiController::class, 'ativos']);

    // Salas por evento (appmontagem)
    $router->get('/eventos/salas/{id}', [App\Controllers\SalaController::class, 'listByEvento']);

    // Montagem de OS — API Key OU sessao (API v1)
    $router->get('/montagem/listar/{idEvento}',        [App\Controllers\Api\MontagemApiController::class, 'listar']);
    $router->post('/montagem/inserir-lote',            [App\Controllers\Api\MontagemApiController::class, 'inserirLote']);
    // Extendida: appmontagem (array format + pdf_base64)
    $router->post('/montagem/inserir-lote-ext',        [App\Controllers\Api\SessaoApiController::class, 'inserirLote']);

    // Devolucao de OS (appdevolucao)
    $router->get('/devolucao/seriais/{idEvento}',       [App\Controllers\Api\DevolucaoApiController::class, 'listarSeriaisMontados']);
    $router->post('/devolucao/devolver',                [App\Controllers\Api\DevolucaoApiController::class, 'devolverSerial']);
    $router->post('/devolucao/devolver-lote',           [App\Controllers\Api\DevolucaoApiController::class, 'devolverLote']);
    $router->get('/devolucao/listar/{idEvento}',         [App\Controllers\Api\DevolucaoApiController::class, 'listarDevolucoes']);
    $router->get('/devolucao/stats/{idEvento}',          [App\Controllers\Api\DevolucaoApiController::class, 'stats']);

    // Colaboradores
    $router->get('/colaboradores', [App\Controllers\Api\ColaboradorApiController::class, 'index']);
    $router->post('/colaboradores', [App\Controllers\Api\ColaboradorApiController::class, 'store']);
    
    // Fornecedores
    $router->get('/fornecedores', [App\Controllers\Api\FornecedorApiController::class, 'index']);
    $router->post('/fornecedores', [App\Controllers\Api\FornecedorApiController::class, 'store']);
    
    // Categorias
    $router->get('/categorias', [App\Controllers\Api\CategoriaApiController::class, 'index']);
    $router->post('/categorias', [App\Controllers\Api\CategoriaApiController::class, 'store']);
    
    // Subcategorias
    $router->get('/subcategorias', [App\Controllers\Api\SubcategoriaApiController::class, 'index']);
    $router->post('/subcategorias', [App\Controllers\Api\SubcategoriaApiController::class, 'store']);

    // Eventos — ativos (/ativos) e geral (/)
    $router->get('/eventos', [App\Controllers\Api\EventoApiController::class, 'ativos']);

    // Montagem de OS — inserir seriais em lote
    $router->post('/montagem/inserir-lote', [App\Controllers\Api\MontagemApiController::class, 'inserirLote']);
    $router->get('/montagem/listar/{idEvento}', [App\Controllers\Api\MontagemApiController::class, 'listar']);
}, [\App\Http\Middleware\ApiKeyMiddleware::class]);

$app->run();