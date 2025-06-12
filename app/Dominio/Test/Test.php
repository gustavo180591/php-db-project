<?php

namespace App\Dominio\Test;

use DateTimeInterface;

class Test
{
    private string $id;
    private string $nombre;
    private string $categoria;
    private string $descripcion;
    private array $configuracion;
    private DateTimeInterface $fechaCreacion;
    private bool $activo;

    public const CATEGORIA_RESISTENCIA = 'RESISTENCIA';
    public const CATEGORIA_FUERZA = 'FUERZA';
    public const CATEGORIA_FLEXIBILIDAD = 'FLEXIBILIDAD';
    public const CATEGORIA_VELOCIDAD = 'VELOCIDAD';

    public function __construct(
        string $id,
        string $nombre,
        string $categoria,
        string $descripcion,
        array $configuracion,
        DateTimeInterface $fechaCreacion,
        bool $activo = true
    ) {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->categoria = $categoria;
        $this->descripcion = $descripcion;
        $this->configuracion = $configuracion;
        $this->fechaCreacion = $fechaCreacion;
        $this->activo = $activo;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getNombre(): string
    {
        return $this->nombre;
    }

    public function getCategoria(): string
    {
        return $this->categoria;
    }

    public function getDescripcion(): string
    {
        return $this->descripcion;
    }

    public function getConfiguracion(): array
    {
        return $this->configuracion;
    }

    public function getFechaCreacion(): DateTimeInterface
    {
        return $this->fechaCreacion;
    }

    public function estaActivo(): bool
    {
        return $this->activo;
    }

    public function obtenerNivel($valor, string $sexo = 'M'): ?string
    {
        if (!isset($this->configuracion['rango'][$sexo])) {
            return null;
        }

        foreach ($this->configuracion['rango'][$sexo] as $nivel => $rango) {
            if ($valor >= $rango['min'] && $valor <= $rango['max']) {
                return $nivel;
            }
        }

        return null;
    }
}
