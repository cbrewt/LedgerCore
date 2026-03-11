<?php

namespace Core;

use Core\Exceptions\HttpException;
use Throwable;

class Router
{
    public $routes = [];


    public function add($method, $uri, $controller)
    {
        $this->routes[] = [
            'uri' => $uri,
            'controller' => $controller,
            'method' => $method
        ];
    }

    public function get($uri, $controller)
    {
        $this->add('GET', $uri, $controller);
    }

    public function post($uri, $controller)
    {
        $this->add('POST', $uri, $controller);
    }

    public function delete($uri, $controller)
    {
        $this->add('DELETE', $uri, $controller);
    }

    public function patch($uri, $controller)
    {
        $this->add('PATCH', $uri, $controller);
    }

    public function put($uri, $controller)
    {
        $this->add('PUT', $uri, $controller);
    }

    public function route($uri, $method)
    {
        try {
            foreach ($this->routes as $route) {
                if ($route['uri'] === $uri && $route['method'] === strtoupper($method)) {
                    $controller = $route['controller'];

                    if (is_string($controller)) {
                        return require base_path($controller);
                    }

                    if (is_array($controller) && count($controller) === 2) {
                        [$classOrInstance, $action] = $controller;

                        if (is_string($classOrInstance)) {
                            if (!class_exists($classOrInstance)) {
                                $this->abort(500);
                            }
                            $classOrInstance = App::resolve($classOrInstance);
                        }

                        if (!is_object($classOrInstance) || !method_exists($classOrInstance, $action)) {
                            $this->abort(500);
                        }

                        return $classOrInstance->{$action}();
                    }

                    if (is_callable($controller)) {
                        return $controller();
                    }

                    $this->abort(500);
                }
            }

            $this->abort();
        } catch (HttpException $exception) {
            error_log($exception->getMessage());
            return $this->renderError($exception->statusCode(), $exception->userMessage());
        } catch (Throwable $throwable) {
            error_log($throwable->getMessage());
            return $this->renderError(500, 'Unexpected server error.');
        }
    }

    public function abort($code = 404)
    {
        return $this->renderError((int) $code);
    }

    private function renderError(int $code, string $message = ''): void
    {
        http_response_code($code);
        $errorMessage = $message;

        $viewPath = base_path("views/{$code}.php");
        if (!is_file($viewPath)) {
            $code = 500;
            $viewPath = base_path('views/500.php');
        }

        require $viewPath;
    }
}
