<?php

namespace App\Controllers;

use App\Http\Controller;
use App\Core\Response;
use App\Core\Csrf;
use App\Service\UsuarioService;
use App\Service\ProdutorService;
use App\Repository\ProdutorRepository;
use App\Auth\Rbac;

class UsuarioController extends Controller
{
    private UsuarioService $service;
    private ProdutorRepository $produtorRepo;

    public function __construct($request)
    {
        parent::__construct($request);
        $this->service     = new UsuarioService();
        $this->produtorRepo = new ProdutorRepository();
    }

    public function index(): Response
    {
        if (!Rbac::check('usuarios')) {
            return $this->redirect($this->baseUrl . '/dashboard');
        }

        $usuarios = $this->service->all();

        return $this->view('usuario/index', [
            'title'    => 'Usuários',
            'usuarios' => $usuarios,
        ]);
    }

    public function create(): Response
    {
        if (!Rbac::check('usuarios.create')) {
            return $this->redirect($this->baseUrl . '/usuarios');
        }

        return $this->view('usuario/create', [
            'title'     => 'Novo Usuário',
            'roles'     => ['administrativo', 'comercial', 'estoquista'],
            'produtores' => $this->produtorRepo->all(),
        ]);
    }

    public function store(): Response
    {
        if (!Rbac::check('usuarios.create')) {
            return $this->json(['success' => false, 'message' => 'Acesso não autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');

        if (!Csrf::validate($csrfToken)) {
            if ($this->isAjax()) return $this->json(['success' => false, 'error' => 'Token CSRF inválido'], 400);
            return $this->view('usuario/create', [
                'title'     => 'Novo Usuário',
                'error'     => 'Token CSRF inválido',
                'roles'     => ['administrativo', 'comercial', 'estoquista'],
                'produtores' => $this->produtorRepo->all(),
            ]);
        }

        $data = [
            'name'     => $this->post('name'),
            'email'    => $this->post('email'),
            'password' => $this->post('password'),
            'telefone' => $this->post('telefone'),
            'celular'  => $this->post('celular'),
            'cep'      => $this->post('cep'),
            'role'     => $this->post('role', 'estoquista'),
            'status'   => $this->post('status', 1),
        ];

        try {
            $newUserId = $this->service->create($data);

            // Vincular ao produtor se role = comercial e id_produtor informado
            $idProdutor = (int) $this->post('id_produtor');
            if ($data['role'] === 'comercial' && $idProdutor > 0) {
                $this->produtorRepo->linkUser($idProdutor, $newUserId);
            }

            if ($this->isAjax()) return $this->json(['success' => true, 'message' => 'Usuário criado com sucesso']);
            return $this->redirect($this->baseUrl . '/usuarios?success=created');
        } catch (\Throwable $e) {
            if ($this->isAjax()) return $this->json(['success' => false, 'error' => $e->getMessage()], 422);
            return $this->view('usuario/create', [
                'title'     => 'Novo Usuário',
                'error'     => $e->getMessage(),
                'old'       => $data,
                'roles'     => ['administrativo', 'comercial', 'estoquista'],
                'produtores' => $this->produtorRepo->all(),
            ]);
        }
    }

    public function edit($id): Response
    {
        if (!Rbac::check('usuarios.edit')) {
            return $this->redirect($this->baseUrl . '/usuarios');
        }

        try {
            $usuario = $this->service->findOrFail($id);
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Usuário não encontrado.';
            return $this->redirect($this->baseUrl . '/usuarios');
        }

        $produtorAtual = $this->produtorRepo->findByUserId((int) $id);

        return $this->view('usuario/edit', [
            'title'          => 'Editar Usuário',
            'usuario'        => $usuario,
            'roles'          => ['administrativo', 'comercial', 'estoquista'],
            'produtores'     => $this->produtorRepo->all(),
            'id_produtor_atual' => $produtorAtual ? (int) $produtorAtual['id'] : null,
        ]);
    }

    public function update($id): Response
    {
        if (!Rbac::check('usuarios.edit')) {
            return $this->json(['success' => false, 'message' => 'Acesso não autorizado'], 403);
        }

        $csrfToken = $this->post('_csrf_token');

        if (!Csrf::validate($csrfToken)) {
            if ($this->isAjax()) return $this->json(['success' => false, 'error' => 'Token CSRF inválido'], 400);
            try {
                $usuario = $this->service->findOrFail($id);
            } catch (\Exception $e) {
                $_SESSION['error'] = 'Usuário não encontrado.';
                return $this->redirect($this->baseUrl . '/usuarios');
            }
            return $this->view('usuario/edit', [
                'title'          => 'Editar Usuário',
                'error'          => 'Token CSRF inválido',
                'usuario'        => $usuario,
                'roles'          => ['administrativo', 'comercial', 'estoquista'],
                'produtores'     => $this->produtorRepo->all(),
                'id_produtor_atual' => $this->produtorRepo->findByUserId((int) $id)['id'] ?? null,
            ]);
        }

        $data = [
            'name'     => $this->post('name'),
            'email'    => $this->post('email'),
            'telefone' => $this->post('telefone'),
            'celular'  => $this->post('celular'),
            'cep'      => $this->post('cep'),
            'role'     => $this->post('role', 'estoquista'),
            'status'   => $this->post('status', 1),
        ];

        if (!empty($this->post('password'))) {
            $data['password'] = $this->post('password');
        }

        try {
            $this->service->update((int) $id, $data);

            // Atualizar vínculo com produtor
            $idProdutor = (int) $this->post('id_produtor');
            if ($data['role'] === 'comercial' && $idProdutor > 0) {
                $this->produtorRepo->linkUser($idProdutor, (int) $id);
            } else {
                // Se mudou de role ou não selecionou produtor, desvincular
                $this->produtorRepo->unlinkUser((int) $id);
            }

            if ($this->isAjax()) return $this->json(['success' => true, 'message' => 'Usuário atualizado com sucesso']);
            return $this->redirect($this->baseUrl . '/usuarios?success=updated');
        } catch (\Throwable $e) {
            if ($this->isAjax()) return $this->json(['success' => false, 'error' => $e->getMessage()], 422);
            try {
                $usuario = $this->service->findOrFail($id);
            } catch (\Exception $ex) {
                $_SESSION['error'] = 'Usuário não encontrado.';
                return $this->redirect($this->baseUrl . '/usuarios');
            }
            return $this->view('usuario/edit', [
                'title'          => 'Editar Usuário',
                'error'          => $e->getMessage(),
                'usuario'        => array_merge($usuario, $data),
                'roles'          => ['administrativo', 'comercial', 'estoquista'],
                'produtores'     => $this->produtorRepo->all(),
                'id_produtor_atual' => $this->produtorRepo->findByUserId((int) $id)['id'] ?? null,
            ]);
        }
    }

    public function delete($id): Response
    {
        if (!Rbac::check('usuarios.delete')) {
            return $this->json(['success' => false, 'message' => 'Acesso não autorizado'], 403);
        }

        $currentUserId = (int) (Rbac::getUser()['id'] ?? 0);
        if ($currentUserId > 0 && $currentUserId === (int) $id) {
            return $this->json(['success' => false, 'message' => 'Você não pode excluir seu próprio usuário'], 400);
        }

        try {
            $this->produtorRepo->unlinkUser((int) $id);
            $this->service->delete($id);
            return $this->json(['success' => true]);
        } catch (\Throwable $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    public function toggle($id): Response
    {
        if (!Rbac::check('usuarios.edit')) {
            return $this->json(['success' => false, 'message' => 'Acesso não autorizado'], 403);
        }

        try {
            $newStatus = $this->service->toggleStatus($id);
            return $this->json(['success' => true, 'status' => $newStatus]);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function bulkDelete(): Response
    {
        if (!Rbac::check('usuarios.delete')) {
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

        $currentUserId = (int) (Rbac::getUser()['id'] ?? 0);
        $deleted = 0;
        $errors = [];
        foreach ($ids as $id) {
            if ($currentUserId > 0 && $currentUserId === (int) $id) {
                $errors[] = "ID $id: não é possível excluir seu próprio usuário";
                continue;
            }
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