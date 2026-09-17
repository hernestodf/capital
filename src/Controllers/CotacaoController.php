<?php

namespace App\Controllers;

use App\Http\Controller;
use App\Core\Response;
use App\Core\Csrf;
use App\Core\Env;
use App\Service\CotacaoService;
use App\Auth\Rbac;

class CotacaoController extends Controller
{
    private CotacaoService $service;
    private string $baseUrl;

    public function __construct($request)
    {
        parent::__construct($request);
        $this->service = new CotacaoService();
        $this->baseUrl = rtrim(Env::get('BASE_URL', ''), '/');
    }

    /**
     * Criar proposta manual (para marcar vencedor sem proposta previa)
     * POST /cotacao/propostas
     */
    public function criarProposta(): Response
    {
        if (!Rbac::check('cotacao.criar')) {
            return $this->json(['error' => 'Acesso nao autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');
        if (!Csrf::validate($csrfToken)) {
            return $this->json(['error' => 'Token CSRF invalido'], 400);
        }

        $dados = [
            'id_produto_evento' => (int) $this->post('id_produto_evento'),
            'id_fornecedor' => (int) $this->post('id_fornecedor'),
            'id_evento' => 0,
            'valor_proposto' => (float) $this->post('valor_proposto', 0),
            'status' => 'com_proposta',
        ];

        if ($dados['valor_proposto'] < 0) {
            return $this->json(['success' => false, 'error' => 'Valor deve ser maior ou igual a zero']);
        }

        try {
            $result = $this->service->adicionarProposta($dados);
            return $this->json($result);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Listar propostas de um item do evento
     * GET /cotacao/propostas/{id_produto_evento}
     */
    public function listarPropostas($idProdutoEvento): Response
    {
        if (!Rbac::check('cotacao.listar')) {
            return $this->json(['error' => 'Acesso nao autorizado'], 403);
        }

        try {
            $propostas = $this->service->getPropostas((int)$idProdutoEvento);
            return $this->json(['success' => true, 'data' => $propostas]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Marcar fornecedor como vencedor
     * POST /cotacao/marcar-vencedor/{id_cotacao}
     */
    public function marcarVencedor($idCotacao): Response
    {
        if (!Rbac::check('cotacao.editar')) {
            return $this->json(['error' => 'Acesso nao autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');
        if (!Csrf::validate($csrfToken)) {
            return $this->json(['error' => 'Token CSRF invalido'], 400);
        }

        $valor = (float) $this->post('valor', 0);
        if ($valor < 0) {
            return $this->json(['success' => false, 'error' => 'Valor deve ser maior ou igual a zero']);
        }

        try {
            $result = $this->service->marcarVencedor((int)$idCotacao, $valor);

            if ($result['success']) {
                // Enviar confirmacao via WhatsApp se configurado
                $this->enviarWhatsAppVencedor($idCotacao, $valor);

                return $this->json([
                    'success' => true,
                    'custo_unit' => $result['custo_unit'],
                    'fornecedor' => $result['fornecedor']
                ]);
            }

            return $this->json($result, 400);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Criar proposta + marcar vencedor em um passo (atalho rapido da tela salas/produtos)
     * POST /cotacao/vencedor-rapido
     */
    public function vencedorRapido(): Response
    {
        if (!Rbac::check('cotacao.editar')) {
            return $this->json(['error' => 'Acesso nao autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');
        if (!Csrf::validate($csrfToken)) {
            return $this->json(['error' => 'Token CSRF invalido'], 400);
        }

        $idProdutoEvento = (int) $this->post('id_produto_evento');
        $idFornecedor    = (int) $this->post('id_fornecedor');
        $valor           = (float) str_replace(['.', ','], ['', '.'], $this->post('valor', '0'));

        if ($idProdutoEvento <= 0 || $idFornecedor <= 0) {
            return $this->json(['success' => false, 'error' => 'Dados invalidos']);
        }

        try {
            $result = $this->service->adicionarEMarcarVencedor($idProdutoEvento, $idFornecedor, $valor);
            return $this->json($result);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Listar fornecedores ativos para autocomplete do modal rapido
     * GET /cotacao/fornecedores-lista
     */
    public function fornecedoresLista(): Response
    {
        if (!Rbac::check('cotacao.listar')) {
            return $this->json(['error' => 'Acesso nao autorizado'], 403);
        }
        try {
            $lista = $this->service->listarFornecedoresAtivos();
            return $this->json(['success' => true, 'data' => $lista]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Enviar solicitacao de cotacao em massa
     * POST /cotacao/solicitar-cotacao
     */
    public function enviarSolicitacao(): Response
    {
        if (!Rbac::check('cotacao.criar')) {
            return $this->json(['error' => 'Acesso nao autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');
        if (!Csrf::validate($csrfToken)) {
            return $this->json(['error' => 'Token CSRF invalido'], 400);
        }

        $fornecedoresRaw = $this->post('fornecedores', '[]');
        $fornecedores = json_decode($fornecedoresRaw, true) ?: [];

        if (empty($fornecedores)) {
            return $this->json(['success' => false, 'error' => 'Selecione ao menos um fornecedor']);
        }

        $dados = [
            'fornecedores' => $fornecedores,
            'id_evento' => (int) $this->post('id_evento'),
            'id_produto_evento' => (int) $this->post('id_produto_evento'),
            'nome_item' => $this->post('nome_item', ''),
            'descricao_item' => $this->post('descricao_item', ''),
            'nome_evento' => $this->post('nome_evento', ''),
            'data_evento' => $this->post('data_evento', ''),
            'local_evento' => $this->post('local_evento', ''),
            'mensagem' => $this->post('mensagem', ''),
            'anexo_path' => $this->post('anexo_path', ''),
        ];

        try {
            $result = $this->service->enviarSolicitacaoMassa($dados);

            // Log erros de email mas retorna sucesso se pelo menos um foi enviado
            if (!empty($result['erros'])) {
                \App\Core\Logger::warning('Cotacao: erros ao enviar emails', [
                    'id_evento' => $dados['id_evento'],
                    'erros' => $result['erros']
                ]);
            }

            return $this->json($result);
        } catch (\Exception $e) {
            \App\Core\Logger::error('Cotacao: erro ao enviar solicitacao', ['error' => $e->getMessage()]);
            return $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Enviar mensagem/resposta para fornecedor
     * POST /cotacao/enviar-mensagem/{id_cotacao}/{id_fornecedor}
     */
    public function enviarMensagem($idCotacao, $idFornecedor): Response
    {
        if (!Rbac::check('cotacao.editar')) {
            return $this->json(['error' => 'Acesso nao autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');
        if (!Csrf::validate($csrfToken)) {
            return $this->json(['error' => 'Token CSRF invalido'], 400);
        }

        $dados = [
            'id_cotacao' => (int)$idCotacao,
            'id_fornecedor' => (int)$idFornecedor,
            'assunto' => $this->post('assunto', ''),
            'corpo' => $this->post('corpo', ''),
            'anexo_path' => $this->post('anexo_path', ''),
            'anexo_nome' => $this->post('anexo_nome', ''),
            'anexo_mime' => $this->post('anexo_mime', ''),
            'anexo_size' => (int) $this->post('anexo_size', 0),
            'in_reply_to' => $this->post('in_reply_to', ''),
        ];

        try {
            $result = $this->service->enviarMensagem($dados);
            return $this->json($result);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Listar thread de mensagens com fornecedor
     * GET /cotacao/thread/{id_cotacao}/{id_fornecedor}
     */
    public function listarThread($idCotacao, $idFornecedor): Response
    {
        if (!Rbac::check('cotacao.listar')) {
            return $this->json(['error' => 'Acesso nao autorizado'], 403);
        }

        try {
            $thread = $this->service->getThread((int)$idCotacao, (int)$idFornecedor);
            return $this->json(['success' => true, 'data' => $thread]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Listar fornecedores com mensagens para uma cotação
     * GET /cotacao/fornecedores/{id_cotacao}
     */
    public function listarFornecedores($idCotacao): Response
    {
        if (!Rbac::check('cotacao.listar')) {
            return $this->json(['error' => 'Acesso nao autorizado'], 403);
        }

        try {
            $fornecedores = $this->service->getFornecedoresComMensagens((int)$idCotacao);
            return $this->json(['success' => true, 'data' => $fornecedores]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Listar fornecedores com mensagens por produto_evento
     * GET /cotacao/fornecedores-by-item/{id_produto_evento}
     */
    public function listarFornecedoresByItem($idProdutoEvento): Response
    {
        if (!Rbac::check('cotacao.listar')) {
            return $this->json(['error' => 'Acesso nao autorizado'], 403);
        }

        try {
            $fornecedores = $this->service->getFornecedoresByProdutoEvento((int)$idProdutoEvento);
            return $this->json(['success' => true, 'data' => $fornecedores]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Upload de anexo para cotacao
     * POST /cotacao/upload-anexo
     */
    public function uploadAnexo(): Response
    {
        try {
            if (!Rbac::check('cotacao.criar')) {
                return $this->json(['error' => 'Acesso nao autorizado'], 403);
            }

            $csrfToken = $this->post('_csrf_token');
            if (!Csrf::validate($csrfToken)) {
                return $this->json(['error' => 'Token CSRF invalido'], 400);
            }

            if (!isset($_FILES['anexo']) || $_FILES['anexo']['error'] !== UPLOAD_ERR_OK) {
                $uploadError = isset($_FILES['anexo']) ? $_FILES['anexo']['error'] : 'no file';
                \App\Core\Logger::error('Upload error', ['error_code' => $uploadError]);
                return $this->json(['success' => false, 'error' => 'Erro no upload do arquivo (codigo: ' . $uploadError . ')']);
            }

            $file = $_FILES['anexo'];

            // Validar MIME por CONTEUDO do arquivo (nao pelo tipo declarado pelo cliente)
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $realMime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            $allowedTypes = [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'image/jpeg',
                'image/png',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'text/plain',
            ];

            if (!in_array($realMime, $allowedTypes)) {
                return $this->json(['success' => false, 'error' => 'Tipo de arquivo nao permitido']);
            }

            // Validar extensao tambem
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowedExts = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'xls', 'xlsx', 'txt'];
            if (!in_array($ext, $allowedExts)) {
                return $this->json(['success' => false, 'error' => 'Extensao de arquivo nao permitida']);
            }

            $maxSize = 10 * 1024 * 1024; // 10MB
            if ($file['size'] > $maxSize) {
                return $this->json(['success' => false, 'error' => 'Arquivo muito grande (max 10MB)']);
            }

            $uploadDir = __DIR__ . '/../../public/uploads/cotacoes/';
            if (!is_dir($uploadDir)) {
                if (!@mkdir($uploadDir, 0755, true)) {
                    \App\Core\Logger::error('Failed to create upload dir', ['path' => $uploadDir]);
                    return $this->json(['success' => false, 'error' => 'Erro ao criar diretorio de upload']);
                }
            }

            if (!is_writable($uploadDir)) {
                \App\Core\Logger::error('Upload dir not writable', ['path' => $uploadDir]);
                return $this->json(['success' => false, 'error' => 'Diretorio de upload sem permissao de escrita']);
            }

            $baseName = basename($file['name']);
            $safeName = uniqid('anexo_') . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $baseName);
            $destPath = $uploadDir . $safeName;

            if (!move_uploaded_file($file['tmp_name'], $destPath)) {
                \App\Core\Logger::error('Failed to move uploaded file', [
                    'tmp' => $file['tmp_name'],
                    'dest' => $destPath
                ]);
                return $this->json(['success' => false, 'error' => 'Erro ao salvar arquivo']);
            }

            return $this->json([
                'success' => true,
                'path' => 'uploads/cotacoes/' . $safeName,
                'name' => $file['name'],
                'mime' => $file['type'],
                'size' => $file['size']
            ]);
        } catch (\Exception $e) {
            \App\Core\Logger::error('uploadAnexo exception', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return $this->json(['success' => false, 'error' => 'Erro interno: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Confirmar pedido por WhatsApp ao fornecedor vencedor
     * @deprecated WhatsAppService agora está disponível em src/Service/WhatsAppService.php
     */
    private function enviarWhatsAppVencedor(int $idCotacao, float $valor): void
    {
        // WhatsApp integration pending - service not available
        return;
    }
}
