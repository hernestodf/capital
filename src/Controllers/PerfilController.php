<?php

namespace App\Controllers;

use App\Http\Controller;
use App\Core\Response;
use App\Core\Csrf;
use App\Auth\Rbac;
use App\Repository\UserRepository;

class PerfilController extends Controller
{
    private UserRepository $repo;

    public function __construct($request)
    {
        parent::__construct($request);
        $this->repo = new UserRepository();
    }

    public function index(): Response
    {
        $user = Rbac::getUser();
        return $this->view('perfil/index', [
            'title'     => 'Meu Perfil',
            'breadcrumb'=> 'Meu Perfil',
            'usuario'   => $user,
        ]);
    }

    public function salvar(): Response
    {
        $user = Rbac::getUser();

        if (!Csrf::validate($this->post('_csrf_token'))) {
            $_SESSION['flash_error'] = 'Token inválido. Tente novamente.';
            return $this->redirect($this->baseUrl . '/meu-perfil');
        }

        $name       = trim($this->post('name') ?? '');
        $senhaAtual = $this->post('senha_atual') ?? '';
        $novaSenha  = $this->post('nova_senha') ?? '';
        $confirmacao= $this->post('confirmar_senha') ?? '';

        if (empty($name)) {
            $_SESSION['flash_error'] = 'O nome não pode ser vazio.';
            return $this->redirect($this->baseUrl . '/meu-perfil');
        }

        $data = ['name' => $name];

        if (!empty($novaSenha)) {
            $dbUser = $this->repo->find($user['id']);
            if (!password_verify($senhaAtual, $dbUser['password'])) {
                $_SESSION['flash_error'] = 'Senha atual incorreta.';
                return $this->redirect($this->baseUrl . '/meu-perfil');
            }
            if ($novaSenha !== $confirmacao) {
                $_SESSION['flash_error'] = 'A confirmação de senha não confere.';
                return $this->redirect($this->baseUrl . '/meu-perfil');
            }
            if (strlen($novaSenha) < 6) {
                $_SESSION['flash_error'] = 'A nova senha deve ter pelo menos 6 caracteres.';
                return $this->redirect($this->baseUrl . '/meu-perfil');
            }
            $data['password'] = password_hash($novaSenha, PASSWORD_DEFAULT);
        }

        $this->repo->update($user['id'], $data);

        // Atualiza nome na sessão
        $updated = $this->repo->find($user['id']);
        Rbac::setUser($updated);

        $_SESSION['flash_success'] = 'Perfil atualizado com sucesso.';
        return $this->redirect($this->baseUrl . '/meu-perfil');
    }
}
