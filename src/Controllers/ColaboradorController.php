<?php

namespace App\Controllers;

use App\Http\Controller;
use App\Core\Response;
use App\Core\Csrf;
use App\Core\Env;
use App\Service\ColaboradorService;
use App\Service\ColaboradorVerificacaoService;
use App\Repository\FuncaoRepository;
use App\Auth\Rbac;

class ColaboradorController extends Controller
{
    private ColaboradorService $service;
    private ColaboradorVerificacaoService $verificacaoService;

    public function __construct($request)
    {
        parent::__construct($request);
        $this->service = new ColaboradorService();
        $this->verificacaoService = new ColaboradorVerificacaoService();
    }

    public function index(): Response
    {
        if (!Rbac::check('colaboradores.listar')) {
            return $this->redirect($this->baseUrl . '/dashboard');
        }

        $colaboradores = $this->service->all();

        // Adiciona status de verificacao a cada colaborador (1 query batch)
        $verificacaoMap = $this->service->getVerificacaoStatusBatch($colaboradores);
        foreach ($colaboradores as $key => $item) {
            $colaboradores[$key]['verificacao'] = $verificacaoMap[$item['id']] ?? null;
        }

        return $this->view('colaborador/index', [
            'title' => 'Colaboradores',
            'colaboradores' => $colaboradores,
        ]);
    }

    public function create(): Response
    {
        if (!Rbac::check('colaboradores.criar')) {
            return $this->redirect($this->baseUrl . '/colaboradores');
        }

        return $this->view('colaborador/create', [
            'title' => 'Novo Colaborador',
        ]);
    }

    public function store(): Response
    {
        if (!Rbac::check('colaboradores.criar')) {
            return $this->json(['success' => false, 'message' => 'Acesso não autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');

        if (!Csrf::validate($csrfToken)) {
            if ($this->isAjax()) return $this->json(['success' => false, 'error' => 'Token CSRF inválido'], 400);
            return $this->view('colaborador/create', [
                'title' => 'Novo Colaborador',
                'error' => 'Token CSRF inválido',
            ]);
        }

        $idFuncao = ($this->post('id_funcao') !== null && $this->post('id_funcao') !== '') ? (int) $this->post('id_funcao') : null;
        $funcaoNome = '';
        if ($idFuncao) {
            $funcao = (new FuncaoRepository())->find($idFuncao);
            $funcaoNome = $funcao['nome'] ?? '';
        }

        $data = [
            'origem' => $this->post('origem'),
            'tipo' => $this->post('tipo'),
            'estado_para_trabalho' => $this->post('estado_para_trabalho'),
            'nome' => $this->post('nome'),
            'cpf' => preg_replace('/\D/', '', $this->post('cpf') ?? ''),
            'telefone' => $this->post('telefone'),
            'email' => $this->post('email'),
            'id_funcao' => $idFuncao,
            'atua_como' => $funcaoNome,
            'foto' => $this->sanitizeFotoField($this->post('foto')),
            'cep' => $this->post('cep'),
            'endereco' => $this->post('endereco'),
            'bairro' => $this->post('bairro'),
            'cidade' => $this->post('cidade'),
            'estado' => $this->post('estado'),
            'tipo_chave_pix' => $this->post('tipo_chave_pix'),
            'chavepix' => $this->post('chavepix'),
            'observacao' => $this->post('observacao'),
            'dados_pagamento' => $this->post('dados_pagamento'),
        ];

        try {
            $this->service->create($data);
            if ($this->isAjax()) return $this->json(['success' => true, 'message' => 'Colaborador criado com sucesso']);
            return $this->redirect($this->baseUrl . '/colaboradores');
        } catch (\Throwable $e) {
            if ($this->isAjax()) return $this->json(['success' => false, 'error' => $e->getMessage()], 422);
            return $this->view('colaborador/create', [
                'title' => 'Novo Colaborador',
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
        }
    }

    public function edit($id): Response
    {
        if (!Rbac::check('colaboradores.editar')) {
            return $this->redirect($this->baseUrl . '/colaboradores');
        }

        $colaborador = $this->service->find($id);

        if (!$colaborador) {
            return $this->redirect($this->baseUrl . '/colaboradores');
        }

        return $this->view('colaborador/edit', [
            'title' => 'Editar Colaborador',
            'colaborador' => $colaborador,
        ]);
    }

    public function update($id): Response
    {
        if (!Rbac::check('colaboradores.editar')) {
            return $this->json(['success' => false, 'message' => 'Acesso não autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');

        if (!Csrf::validate($csrfToken)) {
            if ($this->isAjax()) return $this->json(['success' => false, 'error' => 'Token CSRF inválido'], 400);
            return $this->view('colaborador/edit', [
                'title' => 'Editar Colaborador',
                'colaborador' => $this->service->find($id),
                'error' => 'Token CSRF inválido',
            ]);
        }

        $idFuncao = ($this->post('id_funcao') !== null && $this->post('id_funcao') !== '') ? (int) $this->post('id_funcao') : null;
        $funcaoNome = '';
        if ($idFuncao) {
            $funcao = (new FuncaoRepository())->find($idFuncao);
            $funcaoNome = $funcao['nome'] ?? '';
        }

        $data = [
            'origem' => $this->post('origem'),
            'tipo' => $this->post('tipo'),
            'estado_para_trabalho' => $this->post('estado_para_trabalho'),
            'nome' => $this->post('nome'),
            'cpf' => preg_replace('/\D/', '', $this->post('cpf') ?? ''),
            'telefone' => $this->post('telefone'),
            'email' => $this->post('email'),
            'id_funcao' => $idFuncao,
            'atua_como' => $funcaoNome,
            'foto' => $this->sanitizeFotoField($this->post('foto')),
            'cep' => $this->post('cep'),
            'endereco' => $this->post('endereco'),
            'bairro' => $this->post('bairro'),
            'cidade' => $this->post('cidade'),
            'estado' => $this->post('estado'),
            'tipo_chave_pix' => $this->post('tipo_chave_pix'),
            'chavepix' => $this->post('chavepix'),
            'observacao' => $this->post('observacao'),
            'dados_pagamento' => $this->post('dados_pagamento'),
        ];

        try {
            $this->service->update($id, $data);
            if ($this->isAjax()) return $this->json(['success' => true, 'message' => 'Colaborador atualizado com sucesso']);
            return $this->redirect($this->baseUrl . '/colaboradores');
        } catch (\Throwable $e) {
            if ($this->isAjax()) return $this->json(['success' => false, 'error' => $e->getMessage()], 422);
            return $this->view('colaborador/edit', [
                'title' => 'Editar Colaborador',
                'colaborador' => $this->service->find($id),
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function delete($id): Response
    {
        if (!Rbac::check('colaboradores.excluir')) {
            return $this->json(['success' => false, 'message' => 'Acesso não autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');

        if (!Csrf::validate($csrfToken)) {
            return $this->json(['error' => 'Token CSRF inválido'], 400);
        }

        try {
            $this->service->delete($id);
            return $this->json(['success' => true, 'message' => 'Colaborador excluído com sucesso']);
        } catch (\RuntimeException $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'message' => 'Erro ao excluir colaborador. Tente novamente.'], 500);
        }
    }

    public function toggle($id): Response
    {
        if (!Rbac::check('colaboradores.editar')) {
            return $this->json(['success' => false, 'message' => 'Acesso não autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');

        if (!Csrf::validate($csrfToken)) {
            return $this->json(['error' => 'Token CSRF inválido'], 400);
        }

        try {
            $newStatus = $this->service->toggleStatus($id);
            return $this->json(['success' => true, 'status' => $newStatus]);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    public function enviarLink($id): Response
    {
        if (!Rbac::check('colaboradores.editar')) {
            return $this->json(['success' => false, 'message' => 'Acesso não autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');

        if (!Csrf::validate($csrfToken)) {
            return $this->json(['error' => 'Token CSRF inválido'], 400);
        }

        try {
            $token = $this->verificacaoService->gerarToken($id);
            $this->verificacaoService->enviarLinkEmail($id, $token);
            return $this->json(['success' => true, 'message' => 'Link enviado por email']);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    public function reenviarLink($id): Response
    {
        if (!Rbac::check('colaboradores.editar')) {
            return $this->json(['success' => false, 'message' => 'Acesso não autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');

        if (!Csrf::validate($csrfToken)) {
            return $this->json(['error' => 'Token CSRF inválido'], 400);
        }

        try {
            // Reutiliza token existente se ja houver um pendente
            $verificacao = $this->verificacaoService->findByColaborador($id);
            if ($verificacao && $verificacao['status'] === 'pendente') {
                $token = $verificacao['token'];
            } else {
                $token = $this->verificacaoService->gerarToken($id);
            }
            $this->verificacaoService->enviarLinkEmail($id, $token);
            return $this->json(['success' => true, 'message' => 'Link reenviado por email']);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * GET /colaboradores/foto/{id}
     *
     * Serve a foto do colaborador independente do formato armazenado:
     * - Caminho de arquivo (uploads/colaboradores/foto_*.jpg)
     * - Base64 bruto no banco (cadastro via site)
     */
    public function foto($id): Response
    {
        if (!Rbac::check('colaboradores.listar')) {
            return Response::redirect(rtrim(Env::get('BASE_URL', ''), '/') . '/dashboard');
        }

        $colaborador = $this->service->find((int)$id);
        if (!$colaborador || empty($colaborador['foto'])) {
            return Response::make('', 404)->header('Content-Type', 'text/plain');
        }

        $foto = $colaborador['foto'];
        $imageData = null;
        $mime = 'image/jpeg';

        // Detecta se é caminho de arquivo
        if (str_starts_with($foto, 'uploads/')) {
            $uploadsBase = realpath(dirname(__DIR__, 2) . '/public/uploads');
            $filePath = realpath(dirname(__DIR__, 2) . '/public/' . $foto);
            // Proteção contra path traversal: o caminho resolvido precisa continuar
            // dentro de public/uploads/, senão trata como não encontrado.
            if ($uploadsBase !== false && $filePath !== false && str_starts_with($filePath, $uploadsBase . DIRECTORY_SEPARATOR)) {
                $imageData = file_get_contents($filePath);
                $detectedMime = mime_content_type($filePath);
                if ($detectedMime) {
                    $mime = $detectedMime;
                }
            }
        }

        // Se não encontrou como arquivo, trata como base64
        if ($imageData === null) {
            $base64 = $foto;
            if (str_starts_with($base64, 'data:image')) {
                $parts = explode(',', $base64, 2);
                $base64 = $parts[1] ?? '';
            }
            $decoded = base64_decode($base64, true);
            if ($decoded !== false) {
                $imageData = $decoded;
                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                $detectedMime = $finfo->buffer($decoded);
                if ($detectedMime) {
                    $mime = $detectedMime;
                }
            }
        }

        if ($imageData === null) {
            return Response::make('', 404)->header('Content-Type', 'text/plain');
        }

        return Response::make($imageData, 200)
            ->header('Content-Type', $mime)
            ->header('Content-Length', strlen($imageData))
            ->header('Cache-Control', 'max-age=86400, public');
    }

    /**
     * Upload de foto via AJAX
     * Suporta: base64 data URL (webcam) e multipart/form-data (file input)
     */
    public function uploadFoto(): Response
    {
        if (!Rbac::check('colaboradores.editar')) {
            return $this->json(['success' => false, 'message' => 'Acesso não autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');
        if (!Csrf::validate($csrfToken)) {
            return $this->json(['error' => 'Token CSRF inválido'], 400);
        }

        $dir = dirname(__DIR__, 2) . '/public/uploads/colaboradores/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $data = null;
        $ext = 'jpg';

        // Suporte a upload binario via multipart/form-data
        if (!empty($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $uploaded = $_FILES['foto'];
            $origExt = strtolower(pathinfo($uploaded['name'], PATHINFO_EXTENSION));
            if (in_array($origExt, ['jpg', 'jpeg', 'png', 'webp'])) {
                $ext = $origExt;
            }
            $data = file_get_contents($uploaded['tmp_name']);
        }

        // Suporte a base64 data URL (webcam / FileReader)
        if ($data === null) {
            $foto = $this->post('foto', '');
            if (empty($foto)) {
                return $this->json(['success' => false, 'error' => 'Foto vazia']);
            }

            if (!preg_match('/^data:image\/(\w+);base64,/', $foto, $matches)) {
                return $this->json(['success' => false, 'error' => 'Formato de imagem inválido']);
            }

            $mimeExt = $matches[1];
            if (!in_array($mimeExt, ['jpeg', 'jpg', 'png', 'webp'])) {
                return $this->json(['success' => false, 'error' => 'Tipo de imagem não permitido']);
            }
            $ext = $mimeExt;

            $base64 = substr($foto, strpos($foto, ',') + 1);
            $data = base64_decode($base64);
            if ($data === false) {
                return $this->json(['success' => false, 'error' => 'Erro ao decodificar imagem']);
            }
        }

        $filename = 'foto_' . uniqid() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        file_put_contents($dir . $filename, $data);

        return $this->json([
            'success' => true,
            'path' => 'uploads/colaboradores/' . $filename,
        ]);
    }

    /**
     * Aceita apenas os dois formatos legítimos de `foto`: um data URL base64 de
     * imagem, ou um caminho gerado por uploadFoto() (uploads/colaboradores/foto_*).
     * Qualquer outro valor (ex.: tentativa de path traversal) é descartado.
     */
    private function sanitizeFotoField(?string $foto): ?string
    {
        if ($foto === null || $foto === '') {
            return $foto;
        }
        if (preg_match('#^data:image/(jpeg|jpg|png|webp);base64,#', $foto)) {
            return $foto;
        }
        if (preg_match('#^uploads/colaboradores/foto_[a-zA-Z0-9]+_[a-f0-9]+\.(jpg|jpeg|png|webp)$#', $foto)) {
            return $foto;
        }
        return null;
    }

    /**
     * GET /colaboradores/listar-json
     * Retorna lista de colaboradores ativos em JSON (para modal de alocacao)
     */
    public function listarJson(): Response
    {
        if (!Rbac::check('colaboradores.listar')) {
            return $this->json(['success' => false, 'error' => 'Acesso nao autorizado'], 403);
        }

        $colaboradores = $this->service->all();

        return $this->json([
            'success' => true,
            'data' => $colaboradores,
        ]);
    }

    public function bulkDelete(): Response
    {
        if (!Rbac::check('colaboradores.excluir')) {
            return $this->json(['success' => false, 'message' => 'Acesso não autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');
        if (!Csrf::validate($csrfToken)) {
            return $this->json(['error' => 'Token CSRF inválido'], 400);
        }

        $idsRaw = $this->post('ids');
        $ids = json_decode($idsRaw, true);
        if (!is_array($ids) || empty($ids)) {
            return $this->json(['error' => 'Nenhum ID fornecido'], 400);
        }

        $deleted = 0;
        $errors = [];
        foreach ($ids as $id) {
            try {
                $this->service->delete((int)$id);
                $deleted++;
            } catch (\Throwable $e) {
                $errors[] = "ID $id: " . $e->getMessage();
            }
        }

        return $this->json([
            'success' => $deleted > 0,
            'deleted' => $deleted,
            'errors' => $errors,
        ]);
    }
}