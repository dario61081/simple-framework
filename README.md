# SimpleFramework

Un micro-framework de enrutamiento estilo Express para PHP, diseñado para ser simple, ligero y fácil de usar.

## Características

- Sistema de enrutamiento RESTful
- Soporte para middlewares
- Manejo de peticiones HTTP (GET, POST, PUT, PATCH, DELETE)
- Soporte para parámetros de ruta
- Manejo de diferentes tipos de contenido (JSON, form-data, x-www-form-urlencoded)
- Sistema de respuestas HTTP con soporte para JSON

## Requisitos

- PHP 8.3 o superior

## Instalación

1. Clona el repositorio o descárgalo como ZIP
2. Instala las dependencias con Composer:
   ```bash
   composer install
   ```

## Uso Básico

### Crear una aplicación básica

```php
<?php
require_once 'vendor/autoload.php';

use SimpleFramework\Routing\SimpleController;
use SimpleFramework\Http\SimpleRequest;
use SimpleFramework\Http\SimpleResponse;

$app = new SimpleController();

// Ruta básica
$app->get('/', function(SimpleRequest $req, SimpleResponse $res) {
    return $res->json(['message' => '¡Bienvenido a SimpleFramework!']);
});

// Iniciar la aplicación
$app->run();
```

### Rutas

```php
// GET /users
$app->get('/users', function($req, $res) {
    // Lógica para obtener usuarios
});

// POST /users
$app->post('/users', function($req, $res) {
    // Lógica para crear un usuario
    $userData = $req->body;
    // ...
});

// Parámetros de ruta
$app->get('/users/:id', function($req, $res) {
    $userId = $req->params['id'];
    // Obtener usuario por ID
});
```

### Middlewares

```php
// Middleware de autenticación
$authMiddleware = function($req, $res, $next) {
    if (!isset($_SESSION['user'])) {
        return $res->status(401)->json(['error' => 'No autorizado']);
    }
    $next();
};

// Aplicar middleware a una ruta específica
$app->get('/dashboard', $authMiddleware, function($req, $res) {
    // Solo usuarios autenticados pueden acceder aquí
    return $res->json(['message' => 'Panel de control']);
});

// Aplicar middleware a un grupo de rutas
$app->group('/admin', $authMiddleware, function() use ($app) {
    $app->get('/users', function($req, $res) {
        // Listar usuarios (protegido por autenticación)
    });
    
    $app->post('/users', function($req, $res) {
        // Crear usuario (protegido por autenticación)
    });
});
```

### Controladores

```php
class UserController implements \SimpleFramework\Http\IController {
    public function index($req, $res) {
        // Listar usuarios
    }
    
    public function show($req, $res) {
        // Mostrar un usuario
    }
    
    public function store($req, $res) {
        // Crear un usuario
    }
    
    public function update($req, $res) {
        // Actualizar un usuario
    }
    
    public function destroy($req, $res) {
        // Eliminar un usuario
    }
}

// Registrar rutas del controlador
$app->controller('/users', new UserController());
```

## Manejo de Respuestas

### Respuestas Básicas

```php
// Respuesta JSON
$app->get('/api/data', function($req, $res) {
    return $res->json(['key' => 'value']);
});

// Establecer código de estado
$app->get('/not-found', function($req, $res) {
    return $res->status(404)->json(['error' => 'No encontrado']);
});

// Encabezados personalizados
$app->get('/custom', function($req, $res) {
    return $res
        ->header('X-Custom-Header', 'valor')
        ->json(['message' => 'Con encabezado personalizado']);
});

// Respuesta de texto plano
$app->get('/texto', function($req, $res) {
    return $res->text('Hola, esto es texto plano');
});

// Respuesta HTML
$app->get('/html', function($req, $res) {
    return $res->html('<h1>Hola, esto es HTML</h1>');
});
```

### Sistema de Vistas

```php
// Renderizar una vista
$app->get('/bienvenido', function($req, $res) {
    $datos = [
        'titulo' => 'Bienvenido',
        'mensaje' => '¡Hola desde SimpleFramework!'
    ];
    return $res->render('bienvenido', $datos);
});

// Cambiar el directorio de vistas
$app->get('/admin', function($req, $res) {
    $res->setViewsPath(__DIR__ . '/vistas/admin');
    return $res->render('dashboard', ['usuario' => 'admin']);
});

// Especificar un layout personalizado
$app->get('/perfil', function($req, $res) {
    return $res->render('perfil', ['usuario' => 'juan'], 'mi-layout');
});
```

### Estructura de Carpetas Recomendada

```
/project
  /views
    /layouts
      layout.php     # Layout principal
      admin.php      # Layout para el área de administración
    bienvenido.php   # Vista de bienvenida
    perfil.php       # Vista de perfil
  index.php          # Punto de entrada
```

Ejemplo de `views/layouts/layout.php`:

```php
<!DOCTYPE html>
<html>
<head>
    <title><?= $titulo ?? 'SimpleFramework' ?></title>
    <meta charset="UTF-8">
</head>
<body>
    <header>
        <h1>Mi Aplicación</h1>
    </header>
    
    <main>
        <?= $content ?? 'Contenido no disponible' ?>
    </main>
    
    <footer>
        <p>&copy; <?= date('Y') ?> SimpleFramework</p>
    </footer>
</body>
</html>
```

## Ejecutar el servidor

Puedes usar el servidor integrado de PHP:

```bash
php -S localhost:8000
```

## Licencia

Este proyecto está bajo la Licencia MIT. Consulta el archivo [LICENSE](LICENSE) para más información.
