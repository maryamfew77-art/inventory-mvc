<?php
// Turns an address into a controller and method:
// /products/edit/5  ->  ProductsController::edit(5)
class Router
{
    public function dispatch(): void
    {
        $url   = is_string($_GET['url'] ?? null) ? trim($_GET['url'], '/') : '';
        $parts = $url === '' ? [] : explode('/', $url);

        $name   = $parts[0] ?? 'home';       // default controller: Home
        $method = $parts[1] ?? 'index';      // default method: index
        $params = array_slice($parts, 2);    // everything after is a parameter (like the id)

        // Security: the controller name may only hold letters and numbers,
        // and the method name must start with a letter (this blocks __construct)
        if (!preg_match('/^[a-z0-9]+$/i', $name) || !preg_match('/^[a-z][a-z0-9_]*$/i', $method)) {
            $this->notFound();
            return;
        }

        $class = ucfirst(strtolower($name)) . 'Controller';   // products -> ProductsController
        if (!class_exists($class)) {
            $this->notFound();
            return;
        }

        $controller = new $class();
        if (!is_callable([$controller, $method])) {   // private and protected methods fail here
            $this->notFound();
            return;
        }

        call_user_func_array([$controller, $method], $params);
    }

    private function notFound(): void
    {
        http_response_code(404);
        echo "<h1>404 - Page not found</h1>";
        echo "<p><a href='" . url('') . "'>Back to home</a></p>";
    }
}