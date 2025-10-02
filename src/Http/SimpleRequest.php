<?php
namespace SimpleFramework\Http;

class SimpleRequest {
    public array $query;
    public array $body;
    public array $params = [];
    public string $method;
    public string $path;

    public function __construct() {
        $this->query = $_GET ?? [];
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        $this->body = [];
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (in_array($this->method, ["POST", "PUT", "PATCH"])) {
            if (str_contains($contentType, "application/json")) {
                $json = json_decode(file_get_contents("php://input"), true);
                if (is_array($json)) {
                    $this->body = $json;
                }
            } elseif (str_contains($contentType, "application/x-www-form-urlencoded")) {
                $this->body = $_POST ?? [];
            } elseif (str_contains($contentType, "multipart/form-data")) {
                $this->body = $_POST ?? [];
            }
        }
    }
}
