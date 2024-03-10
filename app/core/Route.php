<?php

namespace App\core;

class Route
{
    public $layout = null;
    private static array $routes = [];
    private static array $routeNames = [];
    private static string $url = '';

    public function resolve(Request $request, Response $response): string
    {
        $method = $request->getMethod();
        $requestUrl = $request->getRequestUrl();
        $callback = $this->routeHandler($method, $requestUrl);

        if ( ! $callback) {
            $response->setStatusCode(404);
            $view = self::$routes['404'] ?? false;

            return ( ! $view) ? '404 | Not found' : $this->render($view);
        }

        if (is_callable($callback)) {
            return call_user_func($callback);
        }
  
        if (is_callable($callback[0])) {
            return call_user_func_array($callback[0], $callback[1]);
        }

        if (str_contains($callback[0][0], 'App')) {
            $callback[0][0] = new $callback[0][0]();
            $this->layout = $callback[0][0]->layout ?? '';
            return call_user_func_array([$callback[0][0], $callback[0][1] ?? 'index'], $callback[1]);
        }

        if (str_contains($callback[0], 'App')) {
            $callback[0] = new $callback[0]();
            $this->layout = $callback[0]->layout ?? '';
            return call_user_func([$callback[0], $callback[1] ?? 'index']);
        }

        return $this->render($callback[0], $callback[1]);
    }

    private static function routeRegister(
        string $method, 
        string $url, 
        array | callable | string $callback
    ): void
    {
        static::$url = $url;
        static::$routes[$method][$url] = $callback;
    }

    public static function view(string $url, string $name, array $values = []): Route
    {
        self::routeRegister('get', $url, [$name, $values]);
        return new static;
    }

    public static function get(string $url, callable | array $callback): Route
    {
        static::$url = $url;

        if (preg_match('/\{.+?\}/', $url)) {
            static::$routes['param']['get'][$url] = $callback;
        } else {
            static::$routes['get'][$url] = $callback;
        }
        
        return new static;
    }

    public static function post(string $url, callable | array $callback): void
    {
        self::routeRegister('post', $url, $callback);
    }

    public static function fallback(string $filename): void
    {
        self::$routes['404'] = $filename;
    }
    
    public function routeName(string $name): string
    {
        return $_ENV['BASE_URL'] . (static::$routeNames[$name] ?? '');
    }

    public static function name(string $name): void
    {
        static::$routeNames[$name] = static::$url;
    }

    public function render(string $filename, array $values = []): string
    {
        $layoutContent = $this->loadLayout();
        $viewContent = $this->loadView($filename, $values);

        return ( ! $layoutContent) ? $viewContent : str_replace('{{content}}', $viewContent, $layoutContent);
    }

    public function loadView(string $filename, array $values = []): string
    {
        $filename = ROOT_DIR . "/resources/views/{$filename}.php";

        if ( ! file_exists($filename)) {
            return "View file not found: {$filename}";
        }

        extract($values);
        ob_start();
        include $filename;
        return ob_get_clean();
    }
    
    public function loadLayout(): string | bool
    {
        $filename = ROOT_DIR . "/resources/views/layouts/{$this->layout}.php";

        if ( ! file_exists($filename)) {
            return false;
        }

        ob_start();
        include $filename;
        return ob_get_clean();
    }

    public function routeHandler(string $method, string $requestUrl): array | callable | bool
    {
        if (isset(static::$routes[$method][$requestUrl])) {
            return static::$routes[$method][$requestUrl];
        }

        foreach (static::$routes['param'][$method] ?? [] as $routePath => $callback) {
            if ($this->routeParameter($routePath, $requestUrl)) {
                return [$callback, $this->routeParameter($routePath, $requestUrl)];
            }
        }

        return false;
    }

    private static function routeParameter(string $routePath, string $requestUrl): array | bool
    {
        $routePathParts = explode('/', ltrim($routePath, '/'));
        $requestUrlParts = explode('/', trim($requestUrl, '/'));

        if (count($routePathParts) !== count($requestUrlParts)) {
            return false;
        }

        $params = [];

        foreach ($routePathParts as $key => $part) {
            if (strpos($part, '{') !== false) {
                $varname = trim($part, '{}');
                $params[$varname] = $requestUrlParts[$key];
            } elseif ($part !== $requestUrlParts[$key]) {
                return false;
            }
        }

        return $params;
    }
}