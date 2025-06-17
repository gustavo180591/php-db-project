<?php

namespace App\Dominio\Persona;

use DateTimeInterface;

class Persona
{
    private int $id;
    private string $nombre;
    private string $sexo;
    private DateTimeInterface $fechaNacimiento;
    private string $dni;

    public const SEXO_MASCULINO = 'M';
    public const SEXO_FEMENINO = 'F';

    public function __construct(
        int $id,
        string $nombre,
        string $sexo,
        DateTimeInterface $fechaNacimiento,
        string $dni
    ) {
        $this->validarSexo($sexo);
        $this->id = $id;
        $this->nombre = $nombre;
        $this->sexo = $sexo;
        $this->fechaNacimiento = $fechaNacimiento;
        $this->dni = $dni;
    }

    private function validarSexo(string $sexo): void
    {
        if (!in_array($sexo, [self::SEXO_MASCULINO, self::SEXO_FEMENINO])) {
            throw new \InvalidArgumentException('Sexo inválido. Debe ser "M" o "F"');
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNombre(): string
    {
        return $this->nombre;
    }

    public function getSexo(): string
    {
        return $this->sexo;
    }

    public function getFechaNacimiento(): DateTimeInterface
    {
        return $this->fechaNacimiento;
    }

    public function getDni(): string
    {
        return $this->dni;
    }

    public function calcularEdad(DateTimeInterface $fechaReferencia): int
    {
        $intervalo = $fechaReferencia->diff($this->fechaNacimiento);
        return $intervalo->y;
    }
}
