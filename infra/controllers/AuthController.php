<?php

namespace Infra\Controllers;

use App\Dominio\Usuario\Usuario;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class AuthController
{
    private SessionInterface $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function loginForm(): Response
    {
        return new Response(view('auth.login'));
    }

    public function login(Request $request): Response
    {
        $email = $request->request->get('email');
        $password = $request->request->get('password');

        // Simulación de autenticación
        $usuario = $this->autenticarUsuario($email, $password);

        if ($usuario) {
            $this->session->set('usuario_id', $usuario->getId());
            $this->session->set('rol', $usuario->getRol());
            
            return new RedirectResponse('/dashboard');
        }

        return new Response(view('auth.login', [
            'error' => 'Credenciales inválidas'
        ]));
    }

    private function autenticarUsuario(string $email, string $password): ?Usuario
    {
        // En producción, esta lógica iría a la capa de persistencia
        // Aquí solo es una simulación
        if ($email === 'admin@site.com' && $password === 'admin123') {
            return new Usuario(
                '1',
                'admin@site.com',
                'Admin',
                'Admin',
                Usuario::ROL_ADMIN,
                new \DateTimeImmutable()
            );
        }

        return null;
    }
}
