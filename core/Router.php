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
        $path = rtrim($path, '/');
        if (empty($path)) {
            $path = '/';
        }

        // 1. Coincidencia con rutas registradas explícitamente
        foreach ($this->routes as $route) {
            $pattern = "@^" . preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<\1>[a-zA-Z0-9_]+)', $route['path']) . "$@";
            if ($route['method'] === strtoupper($method) && preg_match($pattern, $path, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                list($controllerName, $action) = explode('@', $route['handler']);

                $controllerFile = __DIR__ . "/../app/Controllers/{$controllerName}.php";
                if (file_exists($controllerFile)) {
                    require_once $controllerFile;
                }

                if (class_exists($controllerName) && method_exists($controllerName, $action)) {
                    $controller = new $controllerName();
                    call_user_func_array([$controller, $action], $params);
                    return;
                }
            }
        }

        // 2. Despachador Dinámico de Respaldo (Convierte kebab-case a camelCase)
        $segments = array_values(array_filter(explode('/', $path)));
        if (!empty($segments)) {
            $resource = $segments[0];
            
            $controllerName = ucfirst(rtrim($resource, 's')) . 'Controller';
            if ($resource === 'users') $controllerName = 'UserController';
            if ($resource === 'areas') $controllerName = 'AreaController';
            if ($resource === 'positions') $controllerName = 'PositionController';

            $actionSegment = $segments[1] ?? 'index';
            // Convertir 'upload-document' -> 'uploadDocument'
            $action = lcfirst(str_replace(' ', '', ucwords(str_replace('-', ' ', $actionSegment))));
            
            $params = array_slice($segments, 2);

            $controllerFile = __DIR__ . "/../app/Controllers/{$controllerName}.php";
            if (file_exists($controllerFile)) {
                require_once $controllerFile;
                if (class_exists($controllerName) && method_exists($controllerName, $action)) {
                    $controller = new $controllerName();
                    call_user_func_array([$controller, $action], $params);
                    return;
                }
            }
        }

        http_response_code(404);
        echo "404 Page Not Found";
    }
}