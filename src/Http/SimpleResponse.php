<?php
namespace SimpleFramework\Http;

class SimpleResponse {
    private string $viewsPath = __DIR__ . "/../../views";
    private string $layout = "layout.php";

    public function status(int $code): self {
        http_response_code($code);
        return $this;
    }

    public function header(string $name, string $value): self {
        header("$name: $value");
        return $this;
    }

    public function json(array|object $data): void {
        $this->header("Content-Type", "application/json");
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    public function text(string $data): void {
        $this->header("Content-Type", "text/plain");
        echo $data;
    }

    public function html(string $html): void {
        $this->header("Content-Type", "text/html; charset=UTF-8");
        echo $html;
    }

    /**
     * Renderiza una vista PHP con variables y un layout base
     */
    public function render(string $view, array $vars = [], ?string $layout = null): void {
        $this->header("Content-Type", "text/html; charset=UTF-8");

        $viewFile = $this->viewsPath . "/" . $view . ".php";
        if (!file_exists($viewFile)) {
            throw new \Exception("Vista no encontrada: $viewFile");
        }

        // Variables disponibles en la vista
        extract($vars);

        // Capturar contenido de la vista
        ob_start();
        include $viewFile;
        $content = ob_get_clean();

        // Renderizar dentro del layout (si existe)
        $layoutFile = $this->viewsPath . "/" . ($layout ?? $this->layout);
        if (file_exists($layoutFile)) {
            include $layoutFile;
        } else {
            echo $content;
        }
    }

    /**
     * Permite cambiar el directorio de vistas
     */
    public function setViewsPath(string $path): self {
        $this->viewsPath = rtrim($path, "/");
        return $this;
    }

    /**
     * Permite cambiar el layout base por defecto
     */
    public function setLayout(string $layout): self {
        $this->layout = $layout;
        return $this;
    }
}
