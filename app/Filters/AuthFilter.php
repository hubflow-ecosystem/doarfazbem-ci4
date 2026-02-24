<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Auth Filter
 *
 * Protege rotas que requerem autenticação
 */
class AuthFilter implements FilterInterface
{
    /**
     * Verifica se o usuário está autenticado antes de permitir acesso
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
