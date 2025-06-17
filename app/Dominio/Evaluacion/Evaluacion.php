<?php

namespace App\Dominio\Evaluacion;

use App\Dominio\Test\Test;
use DateTimeInterface;

class Evaluacion
{
    private int $id;
    private Test $test;
    private Persona $persona;
    private float $resultado;
    private DateTimeInterface $fecha;
    private ?string $nivel;
    private bool $aprobado;

    public function __construct(
        int $id,
        Test $test,
        Persona $persona,
        float $resultado,
        DateTimeInterface $fecha
    ) {
        $this->id = $id;
        $this->test = $test;
        $this->persona = $persona;
        $this->resultado = $resultado;
        $this->fecha = $fecha;
        
        // Calculamos el nivel y si está aprobado
        $this->nivel = $test->getConfiguracion()->obtenerNivel($resultado, $persona->getSexo());
        $this->aprobado = $this->nivel !== null;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTest(): Test
    {
        return $this->test;
    }

    public function getPersona(): Persona
    {
        return $this->persona;
    }

    public function getResultado(): float
    {
        return $this->resultado;
    }

    public function getSexo(): string
    {
        return $this->persona->getSexo();
    }

    public function getNombrePersona(): string
    {
        return $this->persona->getNombre();
    }

    public function getDniPersona(): string
    {
        return $this->persona->getDni();
    }

    public function getEdad(DateTimeInterface $fechaReferencia = null): int
    {
        $fechaReferencia = $fechaReferencia ?? $this->fecha;
        return $this->persona->calcularEdad($fechaReferencia);
    }

    public function getFecha(): DateTimeInterface
    {
        return $this->fecha;
    }

    public function getNivel(): ?string
    {
        return $this->nivel;
    }

    public function estaAprobado(): bool
    {
        return $this->aprobado;
    }
}
