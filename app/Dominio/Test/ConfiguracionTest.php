<?php

namespace App\Dominio\Test;

class ConfiguracionTest
{
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function getRango(string $sexo): array
    {
        return $this->config['rango'][$sexo] ?? [];
    }

    public function getUnidad(): string
    {
        return $this->config['unidad'] ?? '';
    }

    public function obtenerNivel($valor, string $sexo = 'M'): ?string
    {
        $rango = $this->getRango($sexo);
        
        foreach ($rango as $nivel => $intervalo) {
            if ($valor >= $intervalo['min'] && $valor <= $intervalo['max']) {
                return $nivel;
            }
        }

        return null;
    }
}
