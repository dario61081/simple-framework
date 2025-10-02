<?php
namespace SimpleFramework\Routing;

use SimpleFramework\Http\SimpleRequest;
use SimpleFramework\Http\SimpleResponse;
use SimpleFramework\Http\IController;
use Exception;

class SimpleController {
    private array $routes = [];
    private array $middlewares = [];
    private string $prefix = "";

    public function use(callable $middleware): void {
        $this->middlewares[] = $middleware;
    }

    private function register(string $method, string $pattern, callable $handler, array $middlewares = []): void {
        $regex = preg_replace('/\{(\w+)\}/', '(?P<\1>[^/]+)', $this->prefix . $pattern);
        $this->routes[] = [
            "method" => strtoupper($method),
            "pattern" => "#^" . $regex . "$#",
            "handler" => $handler,
            "middlewares" => $middlewares
        ];
    }

    public function get(string $pattern, callable $handler, array $middlewares = []): void {
        $this->register("GET", $pattern, $handler, $middlewares);
    }
    public function post(string $pattern, callable $handler, array $middlewares = []): void {
        $this->register("POST", $pattern, $handler, $middlewares);
    }
    public function put(string $pattern, callable $handler, array $middlewares = []): void {
        $this->register("PUT", $pattern, $handler, $middlewares);
    }
    public function delete(string $pattern, callable $handler, array $middlewares = []): void {
        $this->register("DELETE", $pattern, $handler, $middlewares);
    }
    public function patch(string $pattern, callable $handler, array $middlewares = []): void {
        $this->register("PATCH", $pattern, $handler, $middlewares);
    }
    public function options(string $pattern, callable $handler, array $middlewares = []): void {
        $this->register("OPTIONS", $pattern, $handler, $middlewares);
    }

    public function controller(string $basePath, string $controllerClass): void {
        $ctrl = new $controllerClass();

        if (!($ctrl instanceof IController)) {
            throw new Exception("El controlador $controllerClass debe implementar IController");
        }

        $this->get($basePath, [$ctrl, "index"]);
        $this->get($basePath . "/{id}", [$ctrl, "show"]);
        $this->post($basePath, [$ctrl, "store"]);
        $this->put($basePath . "/{id}", [$ctrl, "update"]);
        $this->patch($basePath . "/{id}", [$ctrl, "update"]);
        $this->delete($basePath . "/{id}", [$ctrl, "destroy"]);
    }

    public function autoloadControllers(string $directory, string $baseNamespace = "", string $basePath = ""): void {
        $files = glob($directory . "/*.php");
        $dirs  = glob($directory . "/*", GLOB_ONLYDIR);

        foreach ($files as $file) {
            require_once $file;

            $className = basename($file, ".php");
            $fqcn = $baseNamespace ? $baseNamespace . "\\" . $className : $className;

            if (class_exists($fqcn)) {
                $resource = strtolower(preg_replace('/Controller$/', '', $className));
                $resource = rtrim($resource, "s") . "s";
                $fullPath = $basePath . "/" . $resource;

                $this->controller($fullPath, $fqcn);
            }
        }

        foreach ($dirs as $subdir) {
            $subNamespace = $baseNamespace ? $baseNamespace . "\\" . basename($subdir) : basename($subdir);
            $subPath = $basePath . "/" . strtolower(basename($subdir));

            $this->autoloadControllers($subdir, $subNamespace, $subPath);
        }
    }

    public function execute(): void {
        $request = new SimpleRequest();
        $response = new SimpleResponse();

        foreach ($this->routes as $route) {
            if ($route["method"] === $request->method && preg_match($route["pattern"], $request->path, $matches)) {
                $request->params = array_filter($matches, "is_string", ARRAY_FILTER_USE_KEY);
                $allMiddlewares = array_merge($this->middlewares, $route["middlewares"]);

                $next = function($i) use (&$route, &$request, &$response, &$allMiddlewares, &$next) {
                    if ($i < count($allMiddlewares)) {
                        $allMiddlewares[$i]($request, $response, fn() => $next($i+1));
                    } else {
                        call_user_func($route["handler"], $request, $response);
                    }
                };
                $next(0);
                return;
            }
        }

        $response->status(404)->json(["error" => "Ruta no encontrada"]);
    }
}
