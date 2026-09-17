<?php

namespace App\Auth;

use App\Core\Session;
use App\Database\Connection;
use App\Repository\UserRepository;

class Rbac
{
    private static array $roles = [
        'guest' => [],
        'estoquista' => ['estoquista'],
        'comercial' => ['comercial', 'estoquista'],
        'administrativo' => ['administrativo', 'comercial', 'estoquista'],
    ];

    private static array $permissionsCache = [];

    /**
     * Carrega as permissões do role atual do banco, com cache em memória por request.
     * Na ausência de dados no banco (ex: ambiente local vazio) retorna array vazio.
     */
    private static function loadPermissionsForRole(string $role): array
    {
        if (isset(self::$permissionsCache[$role])) {
            return self::$permissionsCache[$role];
        }

        try {
            $rows = Connection::query(
                "SELECT p.nome FROM permissoes p
                 INNER JOIN role_permissoes rp ON rp.permissao_id = p.id
                 WHERE rp.role = ?",
                [$role]
            );
            self::$permissionsCache[$role] = array_column($rows, 'nome');
        } catch (\Throwable) {
            self::$permissionsCache[$role] = [];
        }

        return self::$permissionsCache[$role];
    }

    public static function check(string $permission): bool
    {
        $user = self::getUser();

        if (!$user) {
            return false;
        }

        $role = $user['role'] ?? 'guest';

        if ($role === 'guest') {
            return false;
        }

        $allowed = self::loadPermissionsForRole($role);
        return in_array($permission, $allowed);
    }

    public static function hasRole(string $role): bool
    {
        $user = self::getUser();
        return ($user['role'] ?? 'guest') === $role;
    }

    public static function hasAnyRole(array $roles): bool
    {
        $user = self::getUser();
        return in_array($user['role'] ?? 'guest', $roles);
    }

    public static function getUser(): ?array
    {
        return Session::get('user');
    }

    public static function setUser(array $user): void
    {
        Session::set('user', $user);
    }

    public static function login(string $email, string $password): array
    {
        $repo = new UserRepository();
        $user = $repo->findByEmail($email);
        
        if (!$user) {
            return ['success' => false, 'message' => 'Email não encontrado'];
        }
        
        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Senha incorreta'];
        }
        
        if (!$user['status']) {
            return ['success' => false, 'message' => 'Usuário desativado. Entre em contato com o administrador.'];
        }
        
        unset($user["password"]);
        self::setUser($user);
        Session::regenerate();
        
        return ['success' => true];
    }

    public static function logout(): void
    {
        Session::forget('user');
    }

    public static function isGuest(): bool
    {
        return self::getUser() === null;
    }

    public static function isAdministrativo(): bool
    {
        return self::hasRole('administrativo');
    }

    public static function isComercial(): bool
    {
        return self::hasRole('comercial');
    }

    public static function isEstoquista(): bool
    {
        return self::hasRole('estoquista');
    }

    /**
     * Retorna o dashboard correto baseado no perfil do usuário
     */
    public static function getDashboardRoute(): string
    {
        $user = self::getUser();
        $role = $user['role'] ?? 'guest';

        return match($role) {
            'administrativo' => '/dashboard/administrativo',
            'comercial' => '/dashboard/comercial',
            'estoquista' => '/dashboard/estoquista',
            default => '/auth/login'
        };
    }

    public static function getRoleLabel(string $role): string
    {
        return match ($role) {
            'administrativo' => 'Administrativo',
            'comercial' => 'Comercial',
            'estoquista' => 'Estoquista',
            default => 'Visitante',
        };
    }

    /**
     * Retorna o role do usuário atual
     */
    public static function getCurrentRole(): string
    {
        $user = self::getUser();
        return $user['role'] ?? 'guest';
    }

    public static function getAllRoles(): array
    {
        return array_keys(self::$roles);
    }

    public static function getAllPermissions(): array
    {
        $rows = Connection::query("SELECT nome FROM permissoes ORDER BY nome");
        return array_column($rows, 'nome');
    }
}