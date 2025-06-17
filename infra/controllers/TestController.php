<?php

namespace Infra\Controllers;

use App\Dominio\Test\Test;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class TestController
{
    private SessionInterface $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function index(): Response
    {
        // En producción, esta lógica iría a la capa de persistencia
        $tests = $this->obtenerTests();

        return new Response(view('tests.index', [
            'tests' => $tests,
            'rol' => $this->session->get('rol')
        ]));
    }

    public function crear(Request $request): Response
    {
        $nombre = $request->request->get('nombre');
        $categoria = $request->request->get('categoria');
        $descripcion = $request->request->get('descripcion');
        $configuracion = json_decode($request->request->get('configuracion'), true);

        // En producción, esta lógica iría a la capa de persistencia
        $test = new Test(
            uniqid(),
            $nombre,
            $categoria,
            $descripcion,
            $configuracion,
            new \DateTimeImmutable()
        );

        return new RedirectResponse('/tests');
    }

    private function obtenerTests(): array
    {
        // Simulación de tests
        return [
            new Test(
                '1',
                'Test de Cooper',
                Test::CATEGORIA_RESISTENCIA,
                'Prueba de resistencia aeróbica de 12 minutos',
                [
                    'unidad' => 'metros',
                    'rango' => [
                        'M' => [
                            'Muy Bajo' => ['min' => 1500, 'max' => 1699],
                            'Bajo' => ['min' => 1700, 'max' => 1999],
                            'Regular' => ['min' => 2000, 'max' => 2299],
                            'Bueno' => ['min' => 2300, 'max' => 2499],
                            'Muy Bueno' => ['min' => 2500, 'max' => 2699],
                            'Excelente' => ['min' => 2700, 'max' => 3000]
                        ],
                        'F' => [
                            'Muy Bajo' => ['min' => 1200, 'max' => 1399],
                            'Bajo' => ['min' => 1400, 'max' => 1599],
                            'Regular' => ['min' => 1600, 'max' => 1899],
                            'Bueno' => ['min' => 1900, 'max' => 2099],
                            'Muy Bueno' => ['min' => 2100, 'max' => 2299],
                            'Excelente' => ['min' => 2300, 'max' => 2600]
                        ]
                    ]
                ],
                new \DateTimeImmutable()
            ),
            // ... más tests
        ];
    }
}
