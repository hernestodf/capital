<?php

namespace App\Controllers;

use App\Http\Controller;
use App\Core\Response;
use App\Core\Csrf;
use App\Service\FechamentoService;
use App\Service\EventoFotoService;
use App\Auth\Rbac;

class FechamentoController extends Controller
{
    private FechamentoService $fechamentoService;
    private EventoFotoService $fotoService;

    public function __construct($request)
    {
        parent::__construct($request);
        $this->fechamentoService = new FechamentoService();
        $this->fotoService = new EventoFotoService();
    }

    /**
     * Listar colaboradores do evento
     * GET /fechamento/colaboradores/{id}
     */
    public function colaboradores($id): Response
    {
        if (!Rbac::check('fechamento.visualizar')) {
            return $this->json(['error' => 'Acesso nao autorizado'], 403);
        }

        try {
            $data = $this->fechamentoService->getColaboradores((int) $id);
            return $this->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Listar presencas de um colaborador
     * GET /fechamento/presencas/{idAlocacao}
     */
    public function presencas($idAlocacao): Response
    {
        if (!Rbac::check('fechamento.visualizar')) {
            return $this->json(['error' => 'Acesso nao autorizado'], 403);
        }

        try {
            $data = $this->fechamentoService->getPresencas((int) $idAlocacao);
            return $this->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Marcar presenca manualmente
     * POST /fechamento/presenca/manual
     */
    public function presencaManual(): Response
    {
        if (!Rbac::check('fechamento.enviar_pagamento')) {
            return $this->json(['error' => 'Acesso nao autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');
        if (!Csrf::validate($csrfToken)) {
            return $this->json(['error' => 'Token CSRF invalido'], 400);
        }

        $idAlocacao = (int) $this->post('id_alocacao');
        $data = $this->post('data', '');
        $horaEntrada = $this->post('hora_entrada', '');
        $horaSaida = $this->post('hora_saida', '');
        $status = $this->post('status', 'parcial');
        $observacao = $this->post('observacao', '');
        $manual = $this->post('manual', 'N');

        if (empty($data)) {
            return $this->json(['success' => false, 'error' => 'Data da presenca e obrigatoria']);
        }

        // Processar upload de fotos se existirem
        $fotoEntradaPath = null;
        $fotoSaidaPath = null;

        if (isset($_FILES['foto_entrada']) && $_FILES['foto_entrada']['error'] === UPLOAD_ERR_OK) {
            $fotoEntradaPath = $this->processarFotoUpload($_FILES['foto_entrada']);
            if ($fotoEntradaPath === false) {
                return $this->json(['success' => false, 'error' => 'Erro no upload da foto de entrada']);
            }
        }

        if (isset($_FILES['foto_saida']) && $_FILES['foto_saida']['error'] === UPLOAD_ERR_OK) {
            $fotoSaidaPath = $this->processarFotoUpload($_FILES['foto_saida']);
            if ($fotoSaidaPath === false) {
                return $this->json(['success' => false, 'error' => 'Erro no upload da foto de saida']);
            }
        }

        try {
            $result = $this->fechamentoService->marcarPresencaManual(
                $idAlocacao,
                $data,
                $horaEntrada,
                $horaSaida,
                $status,
                $observacao,
                $fotoEntradaPath,
                $fotoSaidaPath,
                $manual === 'S'
            );
            return $this->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Processar upload de foto de presenca
     */
    private function processarFotoUpload(array $file): string|false
    {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        if (!in_array($mimeType, $allowedTypes)) {
            return false;
        }

        if ($file['size'] > $maxSize) {
            return false;
        }

        $uploadDir = __DIR__ . '/../../public/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0750, true);
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'presenca_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
        $filepath = $uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            return false;
        }

        return 'uploads/' . $filename;
    }

    /**
     * Listar fornecedores vencedores do evento
     * GET /fechamento/fornecedores/{id}
     */
    public function fornecedores($id): Response
    {
        if (!Rbac::check('fechamento.visualizar')) {
            return $this->json(['error' => 'Acesso nao autorizado'], 403);
        }

        try {
            $data = $this->fechamentoService->getFornecedores((int) $id);
            return $this->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function locacao($id): Response
    {
        if (!Rbac::check('fechamento.visualizar')) {
            return $this->json(['error' => 'Acesso nao autorizado'], 403);
        }
        try {
            $data = $this->fechamentoService->getLocacao((int) $id);
            return $this->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Buscar contas a pagar de um fornecedor específico
     * GET /fechamento/fornecedor/contas/{id_cotacao}
     */
    public function fornecedorContas($idCotacao): Response
    {
        if (!Rbac::check('fechamento.visualizar')) {
            return $this->json(['error' => 'Acesso nao autorizado'], 403);
        }

        try {
            $data = $this->fechamentoService->getFornecedorContas((int) $idCotacao);
            return $this->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Totais financeiros do evento
     * GET /fechamento/totais/{id}
     */
    public function totais($id): Response
    {
        if (!Rbac::check('fechamento.visualizar')) {
            return $this->json(['error' => 'Acesso nao autorizado'], 403);
        }

        try {
            $data = $this->fechamentoService->getTotais((int) $id);
            return $this->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Enviar colaborador para pagamento
     * POST /fechamento/colaborador/pagamento
     */
    public function enviarColaboradorPagamento(): Response
    {
        if (!Rbac::check('fechamento.enviar_pagamento')) {
            return $this->json(['error' => 'Acesso nao autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');
        if (!Csrf::validate($csrfToken)) {
            return $this->json(['error' => 'Token CSRF invalido'], 400);
        }

        $eventoId = (int) $this->post('evento_id');
        $idAlocacao = (int) $this->post('id_alocacao');
        $dataVencimento = $this->post('data_vencimento', '');
        $valorPagamento = $this->post('valor_pagamento', null);

        if (empty($dataVencimento)) {
            return $this->json(['success' => false, 'error' => 'Data de vencimento e obrigatoria']);
        }

        try {
            $result = $this->fechamentoService->enviarColaboradorPagamento(
                $eventoId, 
                $idAlocacao, 
                $dataVencimento,
                $valorPagamento
            );
            return $this->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Enviar fornecedor para pagamento
     * POST /fechamento/fornecedor/pagamento
     */
    public function enviarFornecedorPagamento(): Response
    {
        if (!Rbac::check('fechamento.enviar_pagamento')) {
            return $this->json(['error' => 'Acesso nao autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');
        if (!Csrf::validate($csrfToken)) {
            return $this->json(['error' => 'Token CSRF invalido'], 400);
        }

        $eventoId = (int) $this->post('evento_id');
        $idCotacao = (int) $this->post('id_cotacao');
        $tipoPagamento = $this->post('tipo_pagamento', 'avista');

        try {
            $data = [
                'numero_nf' => $this->post('numero_nf', ''),
                'data_vencimento' => $this->post('data_vencimento', ''),
                'tipo_pagamento' => $tipoPagamento,
                'observacao' => $this->post('observacao', ''),
            ];

            // Dados para parcelas
            if ($tipoPagamento === 'parcelado') {
                $data['entrada_valor'] = (float) $this->post('entrada_valor', 0);
                $data['entrada_vencimento'] = $this->post('entrada_vencimento', '');
                $data['parcelas_qtd'] = (int) $this->post('parcelas_qtd', 1);
                $data['parcelas_datas'] = $this->post('parcelas_datas', '');
                $data['parcelas_valores'] = $this->post('parcelas_valores', '');
                $data['primeira_parcela_vencimento'] = $this->post('primeira_parcela_vencimento', '');
                $data['intervalo_parcelas'] = (int) $this->post('intervalo_parcelas', 30);
            }

            $file = isset($_FILES['documento_anexo']) && $_FILES['documento_anexo']['error'] === UPLOAD_ERR_OK
                ? $_FILES['documento_anexo']
                : null;

            $result = $this->fechamentoService->enviarFornecedorPagamento(
                $eventoId,
                $idCotacao,
                $data,
                $file
            );
            return $this->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Atualizar valor e/ou data de vencimento de uma parcela de fornecedor
     * POST /fechamento/fornecedor/parcela/update/{id}
     */
    public function atualizarParcelaFornecedor($id): Response
    {
        if (!Rbac::check('fechamento.enviar_pagamento')) {
            return $this->json(['error' => 'Acesso nao autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');
        if (!Csrf::validate($csrfToken)) {
            return $this->json(['error' => 'Token CSRF invalido'], 400);
        }

        $valor = (float) $this->post('valor', 0);
        $dataVencimento = $this->post('data_vencimento', '');

        if ($valor <= 0) {
            return $this->json(['success' => false, 'error' => 'Valor deve ser maior que zero']);
        }
        if (empty($dataVencimento)) {
            return $this->json(['success' => false, 'error' => 'Data de vencimento e obrigatoria']);
        }

        try {
            $result = $this->fechamentoService->atualizarParcelaFornecedor(
                (int) $id,
                ['valor' => $valor, 'data_vencimento' => $dataVencimento]
            );
            return $this->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 422);
        }
    }

    /**
     * Upload de foto
     * POST /fechamento/foto/upload
     */
    public function uploadFoto(): Response
    {
        if (!Rbac::check('fechamento.visualizar')) {
            return $this->json(['error' => 'Acesso nao autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');
        if (!Csrf::validate($csrfToken)) {
            return $this->json(['error' => 'Token CSRF invalido'], 400);
        }

        $eventoId = (int) $this->post('evento_id');
        $salaId = $this->post('sala_id');
        $salaId = ($salaId !== null && $salaId !== '') ? (int) $salaId : null;

        if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
            return $this->json(['success' => false, 'error' => 'Erro no upload do arquivo']);
        }

        try {
            $result = $this->fotoService->uploadFoto($_FILES['foto'], $eventoId, $salaId);
            return $this->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remover foto
     * POST /fechamento/foto/remover
     */
    public function removerFoto(): Response
    {
        if (!Rbac::check('fechamento.visualizar')) {
            return $this->json(['error' => 'Acesso nao autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');
        if (!Csrf::validate($csrfToken)) {
            return $this->json(['error' => 'Token CSRF invalido'], 400);
        }

        $eventoId = (int) $this->post('evento_id');
        $fotoId = (int) $this->post('foto_id');

        try {
            $this->fotoService->removerFoto($fotoId, $eventoId);
            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Listar fotos do evento
     * GET /fechamento/fotos/{id}
     */
    public function listarFotos($id): Response
    {
        if (!Rbac::check('fechamento.visualizar')) {
            return $this->json(['error' => 'Acesso nao autorizado'], 403);
        }

        try {
            $data = $this->fotoService->listarFotos((int) $id);
            return $this->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Gerar PDF de fechamento
     * GET /fechamento/pdf/{id}
     */
    public function pdfFechamento($id): \App\Core\Response
    {
        if (!Rbac::check('fechamento.gerar_pdf')) {
            return \App\Core\Response::json(['error' => 'Acesso nao autorizado'], 403);
        }

        $semValores = $this->get('sem_valores', '0') === '1';

        try {
            $eventoController = new EventoPdfController($this->request);
            if (method_exists($eventoController, 'gerarPdfFechamento')) {
                return $eventoController->gerarPdfFechamento((int) $id, $semValores);
            }
            return \App\Core\Response::json(['success' => false, 'error' => 'PDF de fechamento ainda nao implementado'], 501);
        } catch (\Exception $e) {
            \App\Core\Logger::error('PDF fechamento error: ' . $e->getMessage());
            return \App\Core\Response::json(['success' => false, 'error' => 'Erro ao gerar PDF'], 500);
        }
    }

    /**
     * Listar outros custos do evento
     * GET /fechamento/outros/{id}
     */
    public function outros($id): Response
    {
        if (!Rbac::check('fechamento.visualizar')) {
            return $this->json(['error' => 'Acesso nao autorizado'], 403);
        }

        try {
            $data = $this->fechamentoService->getOutrosCustos((int) $id);
            return $this->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Criar novo outro custo → gera registro em contas_pagar (tipo='outro')
     * O pagamento é gerenciado pelo módulo Contas a Pagar (/contas-pagar)
     * POST /fechamento/outro/store
     */
    public function storeOutro(): Response
    {
        if (!Rbac::check('fechamento.editar')) {
            return $this->json(['error' => 'Acesso nao autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');
        if (!Csrf::validate($csrfToken)) {
            return $this->json(['error' => 'Token CSRF invalido'], 400);
        }

        $eventoId = (int) $this->post('evento_id');
        $descricao = trim($this->post('descricao', ''));
        $valor = (float) str_replace(',', '.', $this->post('valor', '0'));
        $dataVenc = $this->post('data_vencimento', '');
        $observacao = trim($this->post('observacao', ''));

        if ($eventoId <= 0 || empty($descricao) || $valor <= 0 || empty($dataVenc)) {
            return $this->json(['success' => false, 'error' => 'Descricao, valor > 0 e data de vencimento sao obrigatorios'], 422);
        }

        // Upload nota fiscal (opcional)
        $notaFiscalPath = null;
        if (isset($_FILES['nota_fiscal']) && $_FILES['nota_fiscal']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../storage/uploads/outros-custos/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $result = \App\Service\UploadService::upload(
                $_FILES['nota_fiscal'],
                $uploadDir,
                'nf_' . $eventoId . '_' . time()
            );
            if ($result['success']) {
                $notaFiscalPath = 'storage/uploads/outros-custos/' . $result['filename'];
            }
        }

        try {
            $id = $this->fechamentoService->criarOutroCusto([
                'evento_id'   => $eventoId,
                'descricao'   => $descricao,
                'valor'       => $valor,
                'data_vencimento' => $dataVenc,
                'observacao'  => $observacao ?: null,
                'nota_fiscal' => $notaFiscalPath,
            ]);
            return $this->json(['success' => true, 'id' => $id]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Atualizar outro custo existente
     * POST /fechamento/outro/update/{id}
     */
    public function updateOutro($id): Response
    {
        if (!Rbac::check('fechamento.editar')) {
            return $this->json(['error' => 'Acesso nao autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');
        if (!Csrf::validate($csrfToken)) {
            return $this->json(['error' => 'Token CSRF invalido'], 400);
        }

        $descricao = trim($this->post('descricao', ''));
        $valor = (float) str_replace(',', '.', $this->post('valor', '0'));
        $dataVenc = $this->post('data_vencimento', '');
        $observacao = trim($this->post('observacao', ''));

        if (empty($descricao) || $valor <= 0 || empty($dataVenc)) {
            return $this->json(['success' => false, 'error' => 'Descricao, valor > 0 e data de vencimento sao obrigatorios'], 422);
        }

        // Upload nota fiscal (opcional — substitui se novo arquivo enviado)
        $notaFiscalPath = null;
        if (isset($_FILES['nota_fiscal']) && $_FILES['nota_fiscal']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../storage/uploads/outros-custos/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $result = \App\Service\UploadService::upload(
                $_FILES['nota_fiscal'],
                $uploadDir,
                'nf_' . $id . '_' . time()
            );
            if ($result['success']) {
                $notaFiscalPath = 'storage/uploads/outros-custos/' . $result['filename'];
            }
        }

        try {
            $affected = $this->fechamentoService->atualizarOutroCusto((int) $id, [
                'descricao'   => $descricao,
                'valor'       => $valor,
                'data_vencimento' => $dataVenc,
                'observacao'  => $observacao ?: null,
                'nota_fiscal' => $notaFiscalPath,
            ]);
            return $this->json(['success' => true, 'affected' => $affected]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Excluir outro custo (somente se PENDENTE)
     * POST /fechamento/outro/delete/{id}
     */
    public function deleteOutro($id): Response
    {
        if (!Rbac::check('fechamento.editar')) {
            return $this->json(['error' => 'Acesso nao autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');
        if (!Csrf::validate($csrfToken)) {
            return $this->json(['error' => 'Token CSRF invalido'], 400);
        }

        try {
            $this->fechamentoService->deletarOutroCusto((int) $id);
            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 422);
        }
    }

    /**
     * Enviar outro custo para pagamento → cria registro em contas_pagar
     * A partir deste ponto o custo aparece no módulo /contas-pagar
     * POST /fechamento/outro/enviar/{id}
     */
    public function enviarOutro($id): Response
    {
        if (!Rbac::check('fechamento.editar')) {
            return $this->json(['error' => 'Acesso nao autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');
        if (!Csrf::validate($csrfToken)) {
            return $this->json(['error' => 'Token CSRF invalido'], 400);
        }

        try {
            $contaId = $this->fechamentoService->enviarOutroCusto((int) $id);
            return $this->json(['success' => true, 'conta_pagar_id' => $contaId]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 422);
        }
    }

    /**
     * Listar horas extras de uma alocacao
     * GET /fechamento/horas-extras/{idAlocacao}
     */
    public function listarHorasExtras($idAlocacao): Response
    {
        if (!Rbac::check('fechamento.visualizar')) {
            return $this->json(['error' => 'Acesso nao autorizado'], 403);
        }

        try {
            $data = $this->fechamentoService->getHorasExtras((int) $idAlocacao);
            return $this->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Lancar nova hora extra
     * POST /fechamento/horas-extras/lancar
     */
    public function lancarHoraExtra(): Response
    {
        if (!Rbac::check('fechamento.editar')) {
            return $this->json(['error' => 'Acesso nao autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');
        if (!Csrf::validate($csrfToken)) {
            return $this->json(['error' => 'Token CSRF invalido'], 400);
        }

        $idAlocacao = (int) $this->post('id_alocacao');
        $data = $this->post('data', '');
        $horas = $this->post('horas', '');
        // Limpar valor formatado (ex: "R$ 150,00" -> 150.00)
        $valorRaw = $this->post('valor', '0');
        $valor = (float) str_replace(['R$', ' ', '.', ','], ['', '', '', '.'], $valorRaw);
        $motivo = $this->post('motivo', '');

        if (empty($data) || empty($horas) || empty($motivo) || $valor <= 0) {
            return $this->json(['success' => false, 'error' => 'Todos os campos sao obrigatorios e o valor deve ser maior que zero']);
        }

        try {
            $id = $this->fechamentoService->lancarHoraExtra([
                'id_alocacao' => $idAlocacao,
                'data' => $data,
                'horas' => $horas,
                'valor' => $valor,
                'motivo' => $motivo
            ]);
            return $this->json(['success' => true, 'id' => $id]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Deletar hora extra
     * POST /fechamento/horas-extras/deletar/{id}
     */
    public function deletarHoraExtra($id): Response
    {
        if (!Rbac::check('fechamento.editar')) {
            return $this->json(['error' => 'Acesso nao autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');
        if (!Csrf::validate($csrfToken)) {
            return $this->json(['error' => 'Token CSRF invalido'], 400);
        }

        try {
            $this->fechamentoService->deletarHoraExtra((int) $id);
            return $this->json(['success' => true]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
