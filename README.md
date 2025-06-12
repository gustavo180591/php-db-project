# php-db-project
# Sistema de Evaluación Deportiva

Sistema completo para la gestión y evaluación de pruebas deportivas, diseñado para facilitar el seguimiento y análisis del rendimiento físico de atletas y deportistas.

## Características Principales

### 1. Gestión de Tests
- Sistema de categorización de tests (Resistencia, Fuerza, Flexibilidad, Velocidad)
- Configuración personalizada por test (unidades, rangos, niveles)
- Evaluación con múltiples unidades (ejemplo: minutos y segundos)
- Sistema de rangos y niveles por edad y sexo
- Gráficos de evolución histórica
- Importación/exportación de configuraciones

### 2. Gestión de Usuarios
- Sistema de roles (Administrador, Evaluador, Visualizador)
- Permisos personalizados por test
- Gestión de perfiles de usuario
- Sistema de autenticación robusto

### 3. Evaluación y Seguimiento
- Programación de evaluaciones
- Asignación de tests a personas
- Seguimiento de progreso
- Notificaciones automáticas
- Sistema de recordatorios

### 4. Reportes y Análisis
- Reportes detallados por test
- Estadísticas grupales
- Exportación a PDF/Excel
- Gráficos comparativos
- Análisis de tendencias

### 5. Seguridad y Mantenimiento
- Validación de datos robusta
- Protección contra inyección SQL
- Cifrado de datos sensibles
- Sistema de logs
- Copias de seguridad automáticas

## Estructura del Sistema

### 1. Base de Datos
- Tablas principales:
  - `usuarios`: Gestión de usuarios y roles
  - `test_types`: Tipos de tests disponibles
  - `test_categories`: Categorización de tests
  - `test_results`: Almacenamiento de resultados
  - `test_permissions`: Control de permisos
  - `personas`: Información de evaluados

### 2. Módulos Principales
- `auth/`: Sistema de autenticación
- `evaluador/`: Gestión de tests y evaluaciones
- `admin/`: Panel de administración
- `reportes/`: Generación de reportes
- `api/`: Interfaz de programación

### 3. Características Técnicas
- Diseño responsive con Bootstrap 5
- Iconos de Font Awesome
- Gráficos con Chart.js
- Sistema de migraciones
- Docker para despliegue

## Requisitos del Sistema

- PHP 8.0 o superior
- MySQL 8.0 o superior
- Docker y Docker Compose
- Apache/Nginx
- Composer

## Instalación

1. Clonar el repositorio:
```bash
git clone https://github.com/tu-usuario/sistema-evaluacion.git
```

2. Configurar variables de entorno:
```bash
cp .env.example .env
# Editar .env con tus configuraciones
```

3. Instalar dependencias:
```bash
composer install
```

4. Inicializar la base de datos:
```bash
php migrate.php
```

5. Iniciar el servidor:
```bash
docker-compose up -d
```

## Uso

1. Acceder al sistema:
   - URL: http://localhost:8080
   - Login: admin@admin.com / contraseña

2. Gestión de Tests:
   - Crear nuevos tipos de tests
   - Asignar categorías
   - Configurar rangos y niveles
   - Programar evaluaciones

3. Evaluación:
   - Asignar tests a personas
   - Registrar resultados
   - Ver evolución histórica
   - Generar reportes

## Licencia

MIT License

Copyright (c) 2025 Sistema de Evaluación Deportiva

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
