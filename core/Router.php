<?php
class Router {
    private $routes = [];

    public function add($method, $path, $handler) {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'handler' => $handler
        ];
    }

    public function dispatch($uri, $method) {
        $path = parse_url($uri, PHP_URL_PATH);
        
        foreach ($this->routes as $route) {
            $pattern = "@^" . preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<\1>[a-zA-Z0-9_]+)', $route['path']) . "$@";
            if ($route['method'] === strtoupper($method) && preg_match($pattern, $path, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                list($controllerName, $action) = explode('@', $route['handler']);
                
                require_once __DIR__ . "/../app/Controllers/{$controllerName}.php";
                $controller = new $controllerName();
                call_user_func_array([$controller, $action], $params);
                return;
            }
        }

        http_response_code(404);
        echo "404 Page Not Found";
    }
}