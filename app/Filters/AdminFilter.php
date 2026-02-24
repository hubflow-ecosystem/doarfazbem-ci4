<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Admin Filter
 *
 * Protege rotas administrativas (apenas admin pode acessar)
 */
class AdminFilter implements FilterInterface
{
    /**
     * Verifica se o usuário é administrador antes de permitir acesso
     *
     * @param RequestInterface $request
     * @param array|null $arguments
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = \Config\Services::session();

        // Verificar se está logado
        if (!$session->get('isLoggedIn')) {
            $session->setFlashdata('error', 'Você precisa fazer login para acessar esta página.');
            return redirect()->to('/login');
        }

        // Verificar se é admin ou superadmin
        $role = $session->get('role');
        if (!in_array($role, ['admin', 'superadmin'])) {
            $session->setFlashdata('error', 'Você não tem permissão para acessar esta página.');
            return redirect()->to('/');
        }
    }

    /**
     * After filter (não usado neste caso)
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array|null $arguments
     * @return mixed
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Não faz nada aqui
    }
}
