#!/bin/bash

# Crear estructura de directorios
mkdir -p html/config
mkdir -p html/perfil

# Mover archivos
mv public/* html/
mv init.sql html/

# Actualizar rutas en los archivos PHP
sed -i 's/require_once.*config.*database.php/require_once "config/config.php"/g' html/index.php
sed -i 's/require_once.*config.*database.php/require_once "config/config.php"/g' html/register.php
sed -i 's/require_once.*config.*database.php/require_once "config/config.php"/g' html/perfil/personas.php

# Dar permisos de ejecución al script
echo "Ejecutar los siguientes comandos manualmente:"
echo "chmod +x html/config/database.php"
echo "chmod +x html/config/config.php"
