<?php

/**
 * Auth Helper
 *
 * Funções auxiliares para autenticação e autorização
 */

if (!function_exists('is_logged_in')) {
    /**
     * Verifica se o usuário está logado
     *
     * @return bool
     */
    function is_logged_in(): bool
    {
        $session = \Config\Services::session();
        return $session->get('isLoggedIn') === true;
    }
}

if (!function_exists('current_user')) {
    /**
     * Retorna os dados do usuário logado
     *
     * @return array|null
     */
    function current_user(): ?array
    {
        if (!is_logged_in()) {
            return null;
        }

        $session = \Config\Services::session();
        return [
            'id' => $session->get('id'),
            'name' => $session->get('name'),
            'email' => $session->get('email'),
            'role' => $session->get('role'),
            'avatar' => $session->get('avatar'),
            'email_verified' => $session->get('email_verified')
        ];
    }
}

if (!function_exists('user_id')) {
    /**
     * Retorna o ID do usuário logado
     *
     * @return int|null
     */
    function user_id(): ?int
    {
        $user = current_user();
        return $user['id'] ?? null;
    }
}

if (!function_exists('user_name')) {
    /**
     * Retorna o nome do usuário logado
     *
     * @return string|null
     */
    function user_name(): ?string
    {
        $user = current_user();
        return $user['name'] ?? null;
    }
}

if (!function_exists('user_email')) {
    /**
     * Retorna o email do usuário logado
     *
     * @return string|null
     */
    function user_email(): ?string
    {
        $user = current_user();
        return $user['email'] ?? null;
    }
}

if (!function_exists('is_admin')) {
    /**
     * Verifica se o usuário logado é administrador
     *
     * @return bool
     */
    function is_admin(): bool
    {
        $user = current_user();
        return $user && in_array($user['role'], ['admin', 'superadmin']);
    }
}

if (!function_exists('is_email_verified')) {
    /**
     * Verifica se o email do usuário foi verificado
     *
     * @return bool
     */
    function is_email_verified(): bool
    {
        $user = current_user();
        return $user && $user['email_verified'] === true;
    }
}

if (!function_exists('require_auth')) {
    /**
     * Redireciona para login se não estiver autenticado
     * Use em controllers para proteger rotas
     *
     * @return \CodeIgniter\HTTP\RedirectResponse|null
     */
    function require_auth()
    {
        if (!is_logged_in()) {
            $session = \Config\Services::session();
            $session->setFlashdata('error', 'Você precisa fazer login para acessar esta página.');
            return redirect()->to('/login');
        }
        return null;
    }
}

if (!function_exists('require_admin')) {
    /**
     * Redireciona se não for administrador
     *
     * @return \CodeIgniter\HTTP\RedirectResponse|null
     */
    function require_admin()
    {
        if (!is_logged_in()) {
            $session = \Config\Services::session();
            $session->setFlashdata('error', 'Você precisa fazer login para acessar esta página.');
            return redirect()->to('/login');
        }

        if (!is_admin()) {
            $session = \Config\Services::session();
            $session->setFlashdata('error', 'Você não tem permissão para acessar esta página.');
            return redirect()->to('/');
        }

        return null;
    }
}

if (!function_exists('require_email_verified')) {
    /**
     * Redireciona se email não foi verificado
     *
     * @return \CodeIgniter\HTTP\RedirectResponse|null
     */
    function require_email_verified()
    {
        if (!is_email_verified()) {
            $session = \Config\Services::session();
            $session->setFlashdata('error', 'Você precisa verificar seu email antes de continuar.');
            return redirect()->to('/');
        }
        return null;
    }
}

if (!function_exists('user_owns')) {
    /**
     * Verifica se o usuário logado é dono de um recurso
     *
     * @param int $resourceUserId ID do usuário dono do recurso
     * @return bool
     */
    function user_owns(int $resourceUserId): bool
    {
        $userId = user_id();
        return $userId && $userId === $resourceUserId;
    }
}

if (!function_exists('can_edit')) {
    /**
     * Verifica se o usuário pode editar um recurso
     * (Admin ou dono do recurso)
     *
     * @param int $resourceUserId ID do usuário dono do recurso
     * @return bool
     */
    function can_edit(int $resourceUserId): bool
    {
        return is_admin() || user_owns($resourceUserId);
    }
}
