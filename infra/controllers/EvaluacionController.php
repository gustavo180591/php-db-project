<?php

namespace Infra\Controllers;

use App\Dominio\Test\Test;
use App\Dominio\Evaluacion\Evaluacion;
use App\Dominio\Persona\Persona;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class EvaluacionController
{
    private SessionInterface $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function index(): Response
    {
        // En producción, esta lógica iría a la capa de persistencia
        $evaluaciones = $this->obtenerEvaluaciones();

        return new Response(view('evaluaciones.index', [
            'evaluaciones' => $evaluaciones,
            'rol' => $this->session->get('rol')
        ]));
    }

    public function crear(Request $request): Response
    {
        $testId = $request->request->get('test_id');
        $personaId = $request->request->get('persona_id');
        $resultado = (float)$request->request->get('resultado');
        $dni = $request->request->get('dni');
        $nombre = $request->request->get('nombre');
        $sexo = $request->request->get('sexo');
        $fechaNacimiento = \DateTimeImmutable::createFromFormat('Y-m-d', $request->request->get('fecha_nacimiento'));

        // En producción, obtener el test desde la base de datos
        $test = $this->obtenerTest($testId);

        if (!$test) {
            return new RedirectResponse('/evaluaciones?error=test_no_encontrado');
        }

        // En producción, obtener la persona desde la base de datos
        // Aquí simulamos obtener la persona por su ID
        $persona = $this->obtenerPersona($personaId);

        if (!$persona) {
            // Si no existe, creamos una nueva
            $persona = new Persona(
                null, // ID será autoincremental
                $nombre,
                $sexo,
                $fechaNacimiento,
                $dni
            );
        }

        // En producción, guardar la persona en la base de datos
        // Aquí simulamos que se guarda y se obtiene el ID
        $personaId = $persona->getId();

        $evaluacion = new Evaluacion(
            null, // ID será autoincremental
            $test,
            $persona,
            $resultado,
            new \DateTimeImmutable()
        );

        // En producción, guardar la evaluación en la base de datos

        return new RedirectResponse('/evaluaciones');
    }

    private function obtenerEvaluaciones(): array
    {
        // Simulación de evaluaciones
        return [
            new Evaluacion(
                '1',
                $this->obtenerTest('1'),
                'persona1',
                2350.5,
                'M',
                new \DateTimeImmutable('2025-06-13')
            ),
            // ... más evaluaciones
        ];
    }

    private function obtenerTest(string $id): ?Test
    {
        // Simulación de obtener un test
        $tests = [
            '1' => new Test(
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
            )
        ];

        return $tests[$id] ?? null;
    }
}
