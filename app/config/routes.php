<?php

use Core\Router;

$router = new Router();

// Autenticación
$router->get('/login', 'AuthController@loginForm');
$router->post('/login', 'AuthController@login');
$router->get('/logout', 'AuthController@logout');
$router->get('/register', 'AuthController@registerForm');
$router->post('/register', 'AuthController@register');

// Dashboard principal
$router->get('/', 'DashboardController@index');
$router->get('/dashboard', 'DashboardController@index');

// Administración de usuarios y roles
$router->get('/admin/usuarios', 'Admin\UsuarioController@index');
$router->get('/admin/usuarios/add', 'Admin\UsuarioController@add');
$router->post('/admin/usuarios/add', 'Admin\UsuarioController@store');
$router->get('/admin/usuarios/edit', 'Admin\UsuarioController@edit');
$router->post('/admin/usuarios/edit', 'Admin\UsuarioController@update');
$router->get('/admin/usuarios/delete', 'Admin\UsuarioController@delete');

$router->get('/admin/roles', 'Admin\RolController@index');
$router->get('/admin/roles/add', 'Admin\RolController@add');
$router->post('/admin/roles/add', 'Admin\RolController@store');
$router->get('/admin/roles/edit', 'Admin\RolController@edit');
$router->post('/admin/roles/edit', 'Admin\RolController@update');
$router->get('/admin/roles/delete', 'Admin\RolController@delete');

// Evaluaciones
$router->get('/evaluaciones', 'EvaluacionController@index');
$router->get('/evaluaciones/add', 'EvaluacionController@add');
$router->post('/evaluaciones/add', 'EvaluacionController@store');
$router->get('/evaluaciones/edit', 'EvaluacionController@edit');
$router->post('/evaluaciones/edit', 'EvaluacionController@update');
$router->get('/evaluaciones/delete', 'EvaluacionController@delete');

// Tests
$router->get('/tests', 'TestController@index');
$router->get('/tests/add', 'TestController@add');
$router->post('/tests/add', 'TestController@store');
$router->get('/tests/edit', 'TestController@edit');
$router->post('/tests/edit', 'TestController@update');
$router->get('/tests/delete', 'TestController@delete');

// Perfil y gestión de personas, centros, zonas
$router->get('/perfil/personas', 'Perfil\PersonaController@index');
$router->get('/perfil/personas/add', 'Perfil\PersonaController@add');
$router->post('/perfil/personas/add', 'Perfil\PersonaController@store');
$router->get('/perfil/personas/edit', 'Perfil\PersonaController@edit');
$router->post('/perfil/personas/edit', 'Perfil\PersonaController@update');
$router->get('/perfil/personas/delete', 'Perfil\PersonaController@delete');

$router->get('/perfil/centros', 'Perfil\CentroController@index');
$router->get('/perfil/centros/add', 'Perfil\CentroController@add');
$router->post('/perfil/centros/add', 'Perfil\CentroController@store');
$router->get('/perfil/centros/edit', 'Perfil\CentroController@edit');
$router->post('/perfil/centros/edit', 'Perfil\CentroController@update');
$router->get('/perfil/centros/delete', 'Perfil\CentroController@delete');

$router->get('/perfil/zonas', 'Perfil\ZonaController@index');
$router->get('/perfil/zonas/add', 'Perfil\ZonaController@add');
$router->post('/perfil/zonas/add', 'Perfil\ZonaController@store');
$router->get('/perfil/zonas/edit', 'Perfil\ZonaController@edit');
$router->post('/perfil/zonas/edit', 'Perfil\ZonaController@update');
$router->get('/perfil/zonas/delete', 'Perfil\ZonaController@delete');

// Evaluador: tests y preguntas
$router->get('/evaluador/tests', 'Evaluador\TestController@index');
$router->get('/evaluador/tests/add', 'Evaluador\TestController@add');
$router->post('/evaluador/tests/add', 'Evaluador\TestController@store');
$router->get('/evaluador/tests/edit', 'Evaluador\TestController@edit');
$router->post('/evaluador/tests/edit', 'Evaluador\TestController@update');
$router->get('/evaluador/tests/delete', 'Evaluador\TestController@delete');

$router->get('/evaluador/preguntas', 'Evaluador\PreguntaController@index');
$router->get('/evaluador/preguntas/add', 'Evaluador\PreguntaController@add');
$router->post('/evaluador/preguntas/add', 'Evaluador\PreguntaController@store');
$router->get('/evaluador/preguntas/edit', 'Evaluador\PreguntaController@edit');
$router->post('/evaluador/preguntas/edit', 'Evaluador\PreguntaController@update');
$router->get('/evaluador/preguntas/delete', 'Evaluador\PreguntaController@delete');

// Registro de usuarios (evaluador y atleta)
$router->get('/register', 'AuthController@registerForm');
$router->post('/register', 'AuthController@register');
$router->get('/register/evaluador', 'AuthController@registerEvaluadorForm');
$router->post('/register/evaluador', 'AuthController@registerEvaluador');
$router->get('/register/atleta', 'AuthController@registerAtletaForm');
$router->post('/register/atleta', 'AuthController@registerAtleta');

return $router; 