<?php

namespace App\Dominio\Usuario;

use DateTimeInterface;

class Usuario
{
    private string $id;
    private string $email;
    private string $nombre;
    private string $apellido;
    private string $rol;
    private DateTimeInterface $fechaRegistro;
    private bool $activo;

    public const ROL_ADMIN = 'ADMIN';
    public const ROL_EVALUADOR = 'EVALUADOR';
    public const ROL_VISUALIZADOR = 'VISUALIZADOR';

    public function __construct(
        string $id,
        string $email,
        string $nombre,
        string $apellido,
        string $rol,
        DateTimeInterface $fechaRegistro,
        bool $activo = true
    ) {
        $this->id = $id;
        $this->email = $email;
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->rol = $rol;
        $this->fechaRegistro = $fechaRegistro;
        $this->activo = $activo;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getNombreCompleto(): string
    {
        return $this->nombre . ' ' . $this->apellido;
    }

    public function getRol(): string
    {
        return $this->rol;
    }

    public function esAdministrador(): bool
    {
        return $this->rol === self::ROL_ADMIN;
    }

    public function esEvaluador(): bool
    {
        return $this->rol === self::ROL_EVALUADOR;
    }

    public function esVisualizador(): bool
    {
        return $this->rol === self::ROL_VISUALIZADOR;
    }

    public function estaActivo(): bool
    {
        return $this->activo;
    }

    public function getFechaRegistro(): DateTimeInterface
    {
        return $this->fechaRegistro;
    }
}
