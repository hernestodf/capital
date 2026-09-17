<?php
// Rotas web autenticadas — requerem sessão ativa

$authCsrf = [\App\Http\Middleware\AuthMiddleware::class, \App\Http\Middleware\CsrfMiddleware::class];
$authOnly  = [\App\Http\Middleware\AuthMiddleware::class];

// Perfil pessoal
$app->router()->group('/meu-perfil', function($router) {
    $router->get('/',        [App\Controllers\PerfilController::class, 'index']);
    $router->post('/salvar', [App\Controllers\PerfilController::class, 'salvar']);
}, $authCsrf);

// Dashboards por perfil
$app->router()->group('/dashboard', function($router) {
    $router->get('/',               [App\Controllers\DashboardController::class, 'index']);
    $router->get('/administrativo', [App\Controllers\DashboardController::class, 'administrativo']);
    $router->get('/comercial',      [App\Controllers\DashboardController::class, 'comercial']);
    $router->get('/estoquista',     [App\Controllers\DashboardController::class, 'estoquista']);
}, $authCsrf);

// Usuários
$app->router()->group('/usuarios', function($router) {
    $router->get('/',              [App\Controllers\UsuarioController::class, 'index']);
    $router->get('/create',        [App\Controllers\UsuarioController::class, 'create']);
    $router->post('/store',        [App\Controllers\UsuarioController::class, 'store']);
    $router->get('/edit/{id}',     [App\Controllers\UsuarioController::class, 'edit']);
    $router->post('/update/{id}',  [App\Controllers\UsuarioController::class, 'update']);
    $router->post('/delete/{id}',  [App\Controllers\UsuarioController::class, 'delete']);
    $router->post('/toggle/{id}',  [App\Controllers\UsuarioController::class, 'toggle']);
    $router->post('/bulk-delete',  [App\Controllers\UsuarioController::class, 'bulkDelete']);
}, $authCsrf);

// Clientes
$app->router()->group('/clientes', function($router) {
    $router->get('/',              [App\Controllers\ClienteController::class, 'index']);
    $router->get('/create',        [App\Controllers\ClienteController::class, 'create']);
    $router->post('/store',        [App\Controllers\ClienteController::class, 'store']);
    $router->get('/edit/{id}',     [App\Controllers\ClienteController::class, 'edit']);
    $router->post('/update/{id}',  [App\Controllers\ClienteController::class, 'update']);
    $router->post('/delete/{id}',  [App\Controllers\ClienteController::class, 'delete']);
    $router->post('/toggle/{id}',  [App\Controllers\ClienteController::class, 'toggle']);
    $router->post('/bulk-delete',  [App\Controllers\ClienteController::class, 'bulkDelete']);
}, $authCsrf);

// Compradores (demandantes)
$app->router()->group('/compradores', function($router) {
    $router->get('/',               [App\Controllers\DemandanteController::class, 'index']);
    $router->get('/create',         [App\Controllers\DemandanteController::class, 'create']);
    $router->post('/store',         [App\Controllers\DemandanteController::class, 'store']);
    $router->post('/store-ajax',    [App\Controllers\DemandanteController::class, 'storeAjax']);
    $router->get('/edit/{id}',      [App\Controllers\DemandanteController::class, 'edit']);
    $router->post('/update/{id}',   [App\Controllers\DemandanteController::class, 'update']);
    $router->post('/delete/{id}',   [App\Controllers\DemandanteController::class, 'delete']);
    $router->post('/toggle/{id}',   [App\Controllers\DemandanteController::class, 'toggle']);
    $router->post('/bulk-delete',  [App\Controllers\DemandanteController::class, 'bulkDelete']);;
}, $authCsrf);

// Configurações do sistema
$app->router()->group('/configuracoes', function($router) {
    $router->get('/',                  [App\Controllers\ConfiguracoesController::class, 'index']);
    $router->post('/update',           [App\Controllers\ConfiguracoesController::class, 'update']);
    $router->post('/update-roles',     [App\Controllers\ConfiguracoesController::class, 'updateRoles']);
    $router->post('/update-theme',     [App\Controllers\ConfiguracoesController::class, 'updateTheme']);
}, $authCsrf);

// Migrations (admin)
$app->router()->group('/migracoes', function($router) {
    $router->get('/',            [App\Controllers\MigracoesController::class, 'executar']);
    $router->get('/executar',    [App\Controllers\MigracoesController::class, 'executar']);
    $router->post('/executar',   [App\Controllers\MigracoesController::class, 'executar']);
}, $authCsrf);

// Contas a pagar
$app->router()->group('/contas-pagar', function($router) {
    $router->get('/',                      [App\Controllers\ContasPagarController::class, 'index']);
    $router->get('/create',                [App\Controllers\ContasPagarController::class, 'create']);
    $router->post('/store',                [App\Controllers\ContasPagarController::class, 'store']);
    $router->get('/edit/{id}',             [App\Controllers\ContasPagarController::class, 'edit']);
    $router->post('/update/{id}',          [App\Controllers\ContasPagarController::class, 'update']);
    $router->post('/delete/{id}',          [App\Controllers\ContasPagarController::class, 'delete']);
    $router->post('/bulk-delete',  [App\Controllers\ContasPagarController::class, 'bulkDelete']);
    $router->post('/pagar/{id}',           [App\Controllers\ContasPagarController::class, 'pagar']);
    $router->get('/print/{id}',            [App\Controllers\ContasPagarController::class, 'print']);
    $router->get('/pdf/{id}',              [App\Controllers\ContasPagarController::class, 'pdf']);
}, $authCsrf);

// Fornecedores + catálogo de itens (sublocação)
$app->router()->group('/fornecedores', function($router) {
    $router->get('/',                               [App\Controllers\FornecedorController::class, 'index']);
    $router->get('/create',                         [App\Controllers\FornecedorController::class, 'create']);
    $router->post('/store',                         [App\Controllers\FornecedorController::class, 'store']);
    $router->get('/edit/{id}',                      [App\Controllers\FornecedorController::class, 'edit']);
    $router->post('/update/{id}',                   [App\Controllers\FornecedorController::class, 'update']);
    $router->post('/delete/{id}',                   [App\Controllers\FornecedorController::class, 'delete']);
    $router->post('/toggle/{id}',                   [App\Controllers\FornecedorController::class, 'toggle']);
    $router->post('/bulk-delete',  [App\Controllers\FornecedorController::class, 'bulkDelete']);;
    $router->get('/api/subcategorias/{idCategoria}',[App\Controllers\FornecedorController::class, 'getSubcategorias']);
    $router->get('/itens/{idFornecedor}',            [App\Controllers\SublocacaoItemController::class, 'list']);
    $router->post('/itens/{idFornecedor}/store',     [App\Controllers\SublocacaoItemController::class, 'store']);
    $router->post('/itens/{idFornecedor}/update/{itemId}', [App\Controllers\SublocacaoItemController::class, 'update']);
    $router->post('/itens/{idFornecedor}/delete/{itemId}', [App\Controllers\SublocacaoItemController::class, 'delete']);
}, $authCsrf);

// Categorias AJAX
$app->router()->group('/categorias', function($router) {
    $router->get('/list',         [App\Controllers\CategoriaController::class, 'listAll']);
    $router->post('/store',       [App\Controllers\CategoriaController::class, 'store']);
    $router->post('/update/{id}', [App\Controllers\CategoriaController::class, 'update']);
    $router->post('/delete/{id}', [App\Controllers\CategoriaController::class, 'delete']);
}, $authCsrf);

// Subcategorias AJAX
$app->router()->group('/subcategorias', function($router) {
    $router->get('/list/{idCategoria}', [App\Controllers\SubcategoriaController::class, 'listByCategoria']);
    $router->post('/store',             [App\Controllers\SubcategoriaController::class, 'store']);
    $router->post('/update/{id}',       [App\Controllers\SubcategoriaController::class, 'update']);
    $router->post('/delete/{id}',       [App\Controllers\SubcategoriaController::class, 'delete']);
}, $authCsrf);

// Funções de colaboradores AJAX
$app->router()->group('/funcoes-colaborador', function($router) {
    $router->post('/store',       [App\Controllers\FuncaoController::class, 'store']);
    $router->post('/update/{id}', [App\Controllers\FuncaoController::class, 'update']);
    $router->post('/delete/{id}', [App\Controllers\FuncaoController::class, 'delete']);
}, $authCsrf);

// Colaboradores
$app->router()->group('/colaboradores', function($router) {
    $router->get('/',                     [App\Controllers\ColaboradorController::class, 'index']);
    $router->get('/create',               [App\Controllers\ColaboradorController::class, 'create']);
    $router->post('/store',               [App\Controllers\ColaboradorController::class, 'store']);
    $router->get('/edit/{id}',            [App\Controllers\ColaboradorController::class, 'edit']);
    $router->post('/update/{id}',         [App\Controllers\ColaboradorController::class, 'update']);
    $router->post('/delete/{id}',         [App\Controllers\ColaboradorController::class, 'delete']);
    $router->post('/toggle/{id}',         [App\Controllers\ColaboradorController::class, 'toggle']);
    $router->post('/bulk-delete',  [App\Controllers\ColaboradorController::class, 'bulkDelete']);;
    $router->post('/enviar-link/{id}',    [App\Controllers\ColaboradorController::class, 'enviarLink']);
    $router->post('/reenviar-link/{id}',  [App\Controllers\ColaboradorController::class, 'reenviarLink']);
    $router->get('/foto/{id}',            [App\Controllers\ColaboradorController::class, 'foto']);
    $router->post('/upload-foto',         [App\Controllers\ColaboradorController::class, 'uploadFoto']);
    $router->get('/listar-json',          [App\Controllers\ColaboradorController::class, 'listarJson']);
}, $authCsrf);

// Equipe comercial
$app->router()->group('/comercial', function($router) {
    $router->get('/',             [App\Controllers\ProdutorController::class, 'index']);
    $router->get('/create',       [App\Controllers\ProdutorController::class, 'create']);
    $router->post('/store',       [App\Controllers\ProdutorController::class, 'store']);
    $router->get('/edit/{id}',    [App\Controllers\ProdutorController::class, 'edit']);
    $router->post('/update/{id}', [App\Controllers\ProdutorController::class, 'update']);
    $router->post('/delete/{id}', [App\Controllers\ProdutorController::class, 'delete']);
    $router->post('/toggle/{id}', [App\Controllers\ProdutorController::class, 'toggle']);
    $router->post('/bulk-delete',  [App\Controllers\ProdutorController::class, 'bulkDelete']);;
}, $authCsrf);

// Eventos
$app->router()->group('/eventos', function($router) {
    $router->get('/',                          [App\Controllers\EventoController::class, 'index']);
    $router->get('/create',                    [App\Controllers\EventoController::class, 'create']);
    $router->post('/store',                    [App\Controllers\EventoController::class, 'store']);
    $router->get('/edit/{id}',                 [App\Controllers\EventoController::class, 'edit']);
    $router->post('/update/{id}',              [App\Controllers\EventoController::class, 'update']);
    $router->post('/update-comprador/{id}',    [App\Controllers\EventoController::class, 'updateComprador']);
    $router->post('/update-separacao/{id}',    [App\Controllers\EventoController::class, 'updateSeparacao']);
    $router->post('/update-estado/{id}',       [App\Controllers\EventoController::class, 'updateEstado']);
    $router->post('/update-pedido/{id}',       [App\Controllers\EventoController::class, 'updatePedido']);
    $router->post('/delete/{id}',              [App\Controllers\EventoController::class, 'delete']);
    $router->post('/bulk-delete',              [App\Controllers\EventoController::class, 'bulkDelete']);
    $router->post('/toggle/{id}',              [App\Controllers\EventoController::class, 'toggle']);
    $router->post('/converter/{id}',           [App\Controllers\EventoController::class, 'converter']);
    $router->post('/finalizar/{id}',           [App\Controllers\EventoController::class, 'finalizar']);
    $router->post('/itens/adicionar',          [App\Controllers\ProdutoEventoController::class, 'adicionarItem']);
    $router->post('/itens/excluir',            [App\Controllers\ProdutoEventoController::class, 'excluirItem']);
    $router->post('/itens/{id}/atualizar-campo', [App\Controllers\ProdutoEventoController::class, 'atualizarCampo']);
    $router->get('/pdf/{id}',                  [App\Controllers\EventoPdfController::class, 'gerarPdf']);
}, $authCsrf);

// API de eventos (AJAX interno)
$app->router()->group('/api/eventos', function($router) {
    $router->get('/itens',            [App\Controllers\ProdutoEventoController::class, 'listarItensPlanilha']);
    $router->get('/salas',            [App\Controllers\SalaController::class, 'listByEvento']);
    $router->get('/salas/{id}',       [App\Controllers\SalaController::class, 'listByEvento']);
    $router->post('/salas',           [App\Controllers\SalaController::class, 'store']);
    $router->put('/salas/{id}',       [App\Controllers\SalaController::class, 'update']);
    $router->delete('/salas/{id}',    [App\Controllers\SalaController::class, 'delete']);
    $router->post('/itens/{id}/observacao', [App\Controllers\ProdutoEventoController::class, 'salvarObsItem']);
}, $authCsrf);

// Salas
$app->router()->group('/salas', function($router) {
    $router->get('/list/{idEvento}', [App\Controllers\SalaController::class, 'index']);
    $router->post('/store',          [App\Controllers\SalaController::class, 'store']);
    $router->post('/update/{id}',    [App\Controllers\SalaController::class, 'update']);
    $router->post('/delete/{id}',    [App\Controllers\SalaController::class, 'delete']);
    $router->post('/toggle/{id}',    [App\Controllers\SalaController::class, 'toggle']);
}, $authCsrf);

// Produtos por evento
$app->router()->group('/produtos-evento', function($router) {
    $router->get('/list-sala/{idSala}',    [App\Controllers\ProdutoEventoController::class, 'indexBySala']);
    $router->get('/list-evento/{idEvento}',[App\Controllers\ProdutoEventoController::class, 'indexByEvento']);
    $router->get('/autocomplete',          [App\Controllers\ProdutoEventoController::class, 'autocomplete']);
    $router->post('/store',                [App\Controllers\ProdutoEventoController::class, 'store']);
    $router->post('/update/{id}',          [App\Controllers\ProdutoEventoController::class, 'update']);
    $router->post('/delete/{id}',          [App\Controllers\ProdutoEventoController::class, 'delete']);
}, $authCsrf);

// Categorias de sala
$app->router()->group('/categorias-sala', function($router) {
    $router->get('/',             [App\Controllers\CategoriaSalaController::class, 'index']);
    $router->get('/list',         [App\Controllers\CategoriaSalaController::class, 'listAll']);
    $router->post('/store',       [App\Controllers\CategoriaSalaController::class, 'store']);
    $router->post('/update/{id}', [App\Controllers\CategoriaSalaController::class, 'update']);
    $router->post('/delete/{id}', [App\Controllers\CategoriaSalaController::class, 'delete']);
    $router->post('/toggle/{id}', [App\Controllers\CategoriaSalaController::class, 'toggle']);
}, $authCsrf);

// API categorias-sala (alias)
$app->router()->group('/api', function($router) {
    $router->get('/categorias',        [App\Controllers\CategoriaSalaController::class, 'listAll']);
    $router->post('/categorias',       [App\Controllers\CategoriaSalaController::class, 'store']);
    $router->put('/categorias/{id}',   [App\Controllers\CategoriaSalaController::class, 'update']);
    $router->delete('/categorias/{id}',[App\Controllers\CategoriaSalaController::class, 'delete']);
}, $authCsrf);

// Sublocações — painel de devolução física
$app->router()->group('/sublocacoes', function($router) {
    $router->get('/',              [App\Controllers\SublocacaoConsolidadoController::class, 'index']);
    $router->post('/devolver/{id}',[App\Controllers\SublocacaoConsolidadoController::class, 'devolver']);
}, $authCsrf);

// API sublocação
$app->router()->group('/api/sublocacao', function($router) {
    $router->get('/itens/{idFornecedor}',       [App\Controllers\Api\SublocacaoApiController::class, 'itens']);
    $router->get('/list/{idProdutoEvento}',     [App\Controllers\Api\SublocacaoApiController::class, 'listVinculos']);
    $router->post('/vincular',                  [App\Controllers\Api\SublocacaoApiController::class, 'vincular']);
    $router->post('/desvincular/{id}',         [App\Controllers\Api\SublocacaoApiController::class, 'desvincular']);
    $router->post('/gerar-contas/{idEvento}',   [App\Controllers\Api\SublocacaoApiController::class, 'gerarContas']);
    $router->post('/enviar-pagamento/{id}',     [App\Controllers\Api\SublocacaoApiController::class, 'enviarPagamento']);
    $router->post('/vincular-lote',             [App\Controllers\Api\SublocacaoApiController::class, 'vincularLote']);
    $router->get('/listar/{idEvento}',          [App\Controllers\Api\SublocacaoApiController::class, 'listByEvento']);
    $router->post('/pagar-fornecedor',          [App\Controllers\Api\SublocacaoApiController::class, 'pagarFornecedor']);
    $router->get('/fornecedores',                [App\Controllers\Api\SublocacaoApiController::class, 'fornecedores']);
}, $authCsrf);

// API alocação de estoque
$app->router()->group('/api/alocacao', function($router) {
    $router->get('/seriais-disponiveis/{idProdutoEvento}', [App\Controllers\Api\AlocacaoEstoqueApiController::class, 'seriaisDisponiveis']);
    $router->get('/list/{idProdutoEvento}',                [App\Controllers\Api\AlocacaoEstoqueApiController::class, 'listAlocacoes']);
    $router->post('/alocar',                               [App\Controllers\Api\AlocacaoEstoqueApiController::class, 'alocar']);
    $router->post('/desalocar/{id}',                       [App\Controllers\Api\AlocacaoEstoqueApiController::class, 'desalocar']);
    $router->get('/resumo/{idEvento}',                     [App\Controllers\Api\AlocacaoEstoqueApiController::class, 'resumo']);
}, $authCsrf);

// Planilhas
$app->router()->group('/planilhas', function($router) {
    $router->get('/',                    [App\Controllers\PlanilhaController::class, 'index']);
    $router->get('/create',              [App\Controllers\PlanilhaController::class, 'create']);
    $router->post('/store',              [App\Controllers\PlanilhaController::class, 'store']);
    $router->get('/edit/{id}',           [App\Controllers\PlanilhaController::class, 'edit']);
    $router->post('/update/{id}',        [App\Controllers\PlanilhaController::class, 'update']);
    $router->post('/delete/{id}',        [App\Controllers\PlanilhaController::class, 'delete']);
    $router->post('/bulk-delete',  [App\Controllers\PlanilhaController::class, 'bulkDelete']);
}, $authCsrf);

// Unidades de medida AJAX
$app->router()->group('/unidades-medida', function($router) {
    $router->get('/list',         [App\Controllers\UnidadeMedidaController::class, 'listAll']);
    $router->post('/store',       [App\Controllers\UnidadeMedidaController::class, 'store']);
    $router->post('/update/{id}', [App\Controllers\UnidadeMedidaController::class, 'update']);
    $router->post('/delete/{id}', [App\Controllers\UnidadeMedidaController::class, 'delete']);
}, $authCsrf);

// Estoque
$app->router()->group('/estoque', function($router) {
    $router->get('/',                    [App\Controllers\ProdutoController::class, 'index']);
    $router->get('/create',              [App\Controllers\ProdutoController::class, 'create']);
    $router->post('/store',              [App\Controllers\ProdutoController::class, 'store']);
    $router->get('/edit/{id}',           [App\Controllers\ProdutoController::class, 'edit']);
    $router->post('/update/{id}',        [App\Controllers\ProdutoController::class, 'update']);
    $router->post('/delete/{id}',        [App\Controllers\ProdutoController::class, 'delete']);
    $router->post('/bulk-delete',  [App\Controllers\ProdutoController::class, 'bulkDelete']);
    $router->post('/toggle-locado/{id}', [App\Controllers\ProdutoController::class, 'toggleLocado']);
    $router->get('/seriais/{id}',        [App\Controllers\ProdutoController::class, 'seriais']);
    $router->get('/relatorios',          [App\Controllers\ProdutoController::class, 'relatorios']);
    $router->post('/relatorios/gerar',   [App\Controllers\ProdutoController::class, 'gerarRelatorio']);
    $router->get('/alocacoes',           [App\Controllers\ProdutoController::class, 'alocacoesProduto']);
    $router->get('/em-campo',            [App\Controllers\ProdutoController::class, 'emCampo']);
}, $authCsrf);

// API produtos (alocação cross-evento)
$app->router()->group('/api/produtos', function($router) {
    $router->get('/consultar-alocacao', [App\Controllers\ProdutoController::class, 'consultarAlocacao']);
    $router->get('/disponibilidade',    [App\Controllers\ProdutoController::class, 'disponibilidade']);
}, $authOnly);

// Seções AJAX
$app->router()->group('/secoes', function($router) {
    $router->get('/list',         [App\Controllers\SecaoController::class, 'listAll']);
    $router->post('/store',       [App\Controllers\SecaoController::class, 'store']);
    $router->post('/update/{id}', [App\Controllers\SecaoController::class, 'update']);
    $router->post('/delete/{id}', [App\Controllers\SecaoController::class, 'delete']);
}, $authCsrf);

// Seriais AJAX
$app->router()->group('/seriais', function($router) {
    $router->get('/list/{idProduto}',    [App\Controllers\SerialProdutoController::class, 'listByProduto']);
    $router->get('/get-serial/{id}',     [App\Controllers\SerialProdutoController::class, 'getSerial']);
    $router->post('/store',              [App\Controllers\SerialProdutoController::class, 'store']);
    $router->post('/store-batch',        [App\Controllers\SerialProdutoController::class, 'storeBatch']);
    $router->post('/update/{id}',        [App\Controllers\SerialProdutoController::class, 'update']);
    $router->post('/update-status/{id}', [App\Controllers\SerialProdutoController::class, 'updateStatus']);
    $router->post('/delete/{id}',        [App\Controllers\SerialProdutoController::class, 'delete']);
}, $authCsrf);

// Montagem (AJAX)
$app->router()->group('/montagem', function($router) {
    $router->post('/inserir-serial',       [App\Controllers\MontagemController::class, 'inserirSerial']);
    $router->post('/inserir-lote',         [App\Controllers\MontagemController::class, 'inserirLote']);
    $router->post('/encaminhar-sala/{id}', [App\Controllers\MontagemController::class, 'encaminharSala']);
    $router->post('/remover-da-sala/{id}', [App\Controllers\MontagemController::class, 'removerDaSala']);
    $router->post('/devolver/{id}',        [App\Controllers\MontagemController::class, 'devolver']);
    $router->get('/listar/{idEvento}',     [App\Controllers\MontagemController::class, 'listar']);
    $router->get('/pdf/{id}',              [App\Controllers\EventoPdfController::class, 'gerarPdfMontagem']);
}, $authCsrf);

// Devolução (AJAX)
$app->router()->group('/devolucao', function($router) {
    $router->post('/processar-unico',       [App\Controllers\DevolucaoController::class, 'processarUnico']);
    $router->post('/processar-lote',        [App\Controllers\DevolucaoController::class, 'processarLote']);
    $router->post('/resolver-pendencia/{id}',[App\Controllers\DevolucaoController::class, 'resolverPendencia']);
    $router->post('/alterar-status/{id}',   [App\Controllers\DevolucaoController::class, 'alterarStatus']);
    $router->get('/listar/{idEvento}',      [App\Controllers\DevolucaoController::class, 'listar']);
    $router->get('/stats/{idEvento}',       [App\Controllers\DevolucaoController::class, 'stats']);
    $router->get('/historico-serial',       [App\Controllers\DevolucaoController::class, 'historicoPorSerial']);
}, $authCsrf);

// RH por evento
$app->router()->group('/evento/rh', function($router) {
    $router->get('/listar/{idEvento}',       [App\Controllers\EventoRHController::class, 'listar']);
    $router->get('/presencas/{idAlocacao}',  [App\Controllers\EventoRHController::class, 'verPresencas']);
    $router->post('/alocar',                 [App\Controllers\EventoRHController::class, 'alocar']);
    $router->post('/desalocar/{id}',         [App\Controllers\EventoRHController::class, 'desalocar']);
    $router->post('/pagamento/{id}',         [App\Controllers\EventoRHController::class, 'registrarPagamento']);
    $router->post('/enviar-pagamento/{id}',  [App\Controllers\EventoRHController::class, 'enviarPagamento']);
}, $authCsrf);

// Fechamento financeiro de eventos
$app->router()->group('/fechamento', function($router) {
    // Dados
    $router->get('/locacao/{id}',               [App\Controllers\FechamentoController::class, 'locacao']);
    $router->get('/colaboradores/{id}',         [App\Controllers\FechamentoController::class, 'colaboradores']);
    $router->get('/presencas/{idAlocacao}',     [App\Controllers\FechamentoController::class, 'presencas']);
    $router->post('/presenca/manual',           [App\Controllers\FechamentoController::class, 'presencaManual']);
    $router->get('/fornecedor/contas/{idCotacao}', [App\Controllers\FechamentoController::class, 'fornecedorContas']);
    $router->get('/fornecedores/{id}',          [App\Controllers\FechamentoController::class, 'fornecedores']);
    $router->get('/totais/{id}',                [App\Controllers\FechamentoController::class, 'totais']);
    // Outros custos
    $router->get('/outros/{id}',                [App\Controllers\FechamentoController::class, 'outros']);
    $router->post('/outro/store',               [App\Controllers\FechamentoController::class, 'storeOutro']);
    $router->post('/outro/update/{id}',         [App\Controllers\FechamentoController::class, 'updateOutro']);
    $router->post('/outro/delete/{id}',         [App\Controllers\FechamentoController::class, 'deleteOutro']);
    $router->post('/outro/enviar/{id}',         [App\Controllers\FechamentoController::class, 'enviarOutro']);
    // Pagamentos
    $router->post('/colaborador/pagamento',     [App\Controllers\FechamentoController::class, 'enviarColaboradorPagamento']);
    $router->post('/fornecedor/pagamento',      [App\Controllers\FechamentoController::class, 'enviarFornecedorPagamento']);
    $router->post('/fornecedor/parcela/update/{id}', [App\Controllers\FechamentoController::class, 'atualizarParcelaFornecedor']);
    // Fotos
    $router->get('/fotos/{id}',                 [App\Controllers\FechamentoController::class, 'listarFotos']);
    $router->post('/foto/upload',               [App\Controllers\FechamentoController::class, 'uploadFoto']);
    $router->post('/foto/remover',              [App\Controllers\FechamentoController::class, 'removerFoto']);
    // Horas extras
    $router->get('/horas-extras/{idAlocacao}',  [App\Controllers\FechamentoController::class, 'listarHorasExtras']);
    $router->post('/horas-extras/lancar',       [App\Controllers\FechamentoController::class, 'lancarHoraExtra']);
    $router->post('/horas-extras/deletar/{id}', [App\Controllers\FechamentoController::class, 'deletarHoraExtra']);
    // PDF
    $router->get('/pdf/{id}',                   [App\Controllers\FechamentoController::class, 'pdfFechamento']);
}, $authCsrf);
