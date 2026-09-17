<?php

namespace App\Controllers;

use App\Http\Controller;
use App\Core\Response;
use App\Core\Csrf;
use App\Service\EmpresaService;
use App\Service\PermissaoService;
use App\Service\TemaService;
use App\Auth\Rbac;

class ConfiguracoesController extends Controller
{
    private EmpresaService $service;
    private PermissaoService $permissaoService;
    private TemaService $temaService;

    public function __construct($request)
    {
        parent::__construct($request);
        $this->service = new EmpresaService();
        $this->permissaoService = new PermissaoService();
        $this->temaService = new TemaService();
    }

    public function index(): Response
    {
        if (!Rbac::isAdministrativo()) {
            return $this->redirect($this->baseUrl . '/dashboard');
        }

        $config = $this->service->getConfig();
        $matrix = $this->permissaoService->getMatrix();
        $roles = ['administrativo', 'comercial', 'estoquista'];
        $currentTheme = $this->temaService->getActiveTheme();
        
        return $this->view('configuracoes/index', [
            'title' => 'Configurações do Sistema',
            'empresa' => $config,
            'matrix' => $matrix,
            'roles' => $roles,
            'currentTheme' => $currentTheme,
        ]);
    }

    public function updateRoles(): Response
    {
        if (!Rbac::isAdministrativo()) {
            return $this->json(['error' => 'Acesso não autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');
        
        if (!Csrf::validate($csrfToken)) {
            return $this->json(['error' => 'Token CSRF inválido'], 400);
        }

        $permissaoId = (int) $this->post('permissao_id');
        $roles = $this->post('roles', []);

        try {
            $this->permissaoService->updateRoles($permissaoId, $roles);
            return $this->json(['success' => true, 'message' => 'Permissões atualizadas com sucesso']);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function update(): Response
    {
        if (!Rbac::isAdministrativo()) {
            return $this->json(['error' => 'Acesso não autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');
        
        if (!Csrf::validate($csrfToken)) {
            $config = $this->service->getConfig();
            return $this->view('configuracoes/index', array_merge([
                'title' => 'Configurações do Sistema',
                'empresa' => $config,
                'error' => 'Token CSRF inválido',
            ], $this->extraConfigData()));
        }

        $data = [
            'nome' => $this->post('nome'),
            'identificador' => $this->post('identificador'),
            'subtitulo' => $this->post('subtitulo'),
            'tagline' => $this->post('tagline'),
            'cnpj' => $this->post('cnpj'),
            'inscricao_estadual' => $this->post('inscricao_estadual'),
            'endereco' => $this->post('endereco'),
            'cidade' => $this->post('cidade'),
            'estado' => $this->post('estado'),
            'cep' => $this->post('cep'),
            'telefone' => $this->post('telefone'),
            'whatsapp' => $this->post('whatsapp'),
            'email' => $this->post('email'),
            'site' => $this->post('site'),
            'pix_chave' => $this->post('pix_chave'),
            'pix_tipo' => $this->post('pix_tipo'),
            'rodape_pdf' => $this->post('rodape_pdf'),
        ];

        // Handle logo upload
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = dirname(__DIR__, 2) . '/public/uploads/';

            // Valida MIME real (finfo), nao so a extensao — mesmo padrao usado em
            // UploadService::upload() no resto do projeto (ver TD-044).
            $result = \App\Service\UploadService::upload(
                $_FILES['logo'],
                $uploadDir,
                null,
                ['jpg', 'jpeg', 'png', 'webp'],
                2 * 1024 * 1024
            );

            if (!$result['success']) {
                $logoError = $result['error'];
            } else {
                // Deletar logo antigo
                $oldConfig = $this->service->getConfig();
                if (!empty($oldConfig['logo_path'])) {
                    $oldPath = dirname(__DIR__, 2) . '/public/' . $oldConfig['logo_path'];
                    if (file_exists($oldPath)) {
                        @unlink($oldPath);
                    }
                }

                $data['logo_path'] = 'uploads/' . $result['filename'];
            }
        }

        if (!empty($logoError)) {
            return $this->view('configuracoes/index', array_merge([
                'title' => 'Configuracoes do Sistema',
                'empresa' => $this->service->getConfig(),
                'error' => $logoError,
            ], $this->extraConfigData()));
        }

        try {
            $this->service->updateConfig($data);

            return $this->view('configuracoes/index', array_merge([
                'title' => 'Configurações do Sistema',
                'empresa' => $this->service->getConfig(),
                'success' => 'Configurações salvas com sucesso',
            ], $this->extraConfigData()));
        } catch (\Throwable $e) {
            return $this->view('configuracoes/index', array_merge([
                'title' => 'Configurações do Sistema',
                'empresa' => $this->service->getConfig(),
                'error' => $e->getMessage(),
            ], $this->extraConfigData()));
        }
    }

    /**
     * Dados extras que a view configuracoes/index precisa pras abas
     * Permissões e Aparência (index() ja busca isso; update() nao buscava,
     * deixando essas 2 abas vazias/desatualizadas na resposta pos-salvar).
     */
    private function extraConfigData(): array
    {
        return [
            'matrix' => $this->permissaoService->getMatrix(),
            'roles' => ['administrativo', 'comercial', 'estoquista'],
            'currentTheme' => $this->temaService->getActiveTheme(),
        ];
    }

    public function updateTheme(): Response
    {
        if (!Rbac::isAdministrativo()) {
            return $this->json(['error' => 'Acesso não autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');
        
        if (!Csrf::validate($csrfToken)) {
            return $this->json(['error' => 'Token CSRF inválido'], 400);
        }

        $tema = $this->post('tema');
        $temasValidos = ['default', 'pink', 'blue', 'green', 'amber', 'red', 'slate', 'indigo', 'teal', 'rose', 'emerald', 'dark', 'papiro', 'forest', 'lavanda_real', 'warm_sand', 'arctic', 'midnight_mint', 'neon_orchid', 'solar_forge', 'ocean_deep', 'neon_fire', 'neon_magenta', 'neon_red', 'neon_green', 'neon_purple', 'neon_hotpink', 'neon_blue', 'neon_magenta_dark', 'neon_forest'];
        
        if (!in_array($tema, $temasValidos, true)) {
            return $this->json(['success' => false, 'message' => 'Tema inválido'], 400);
        }

        try {
            $this->temaService->setActiveTheme($tema);
            return $this->json(['success' => true, 'message' => 'Tema alterado com sucesso']);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

}
