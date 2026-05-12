<?php
class Router
{
    public function dispatch(): void
    {
        $route = $_GET['r'] ?? 'deportistas/index';
        $parts = array_values(array_filter(explode('/', $route)));
        $controllerName = $parts[0] ?? 'deportistas';
        $action = $parts[1] ?? 'index';

        $className = ucfirst($controllerName) . 'Controller';
        $file = __DIR__ . '/../controllers/' . $className . '.php';

        if (!is_file($file)) {
            $this->renderNotFound();
            return;
        }

        require_once $file;
        if (!class_exists($className)) {
            http_response_code(500);
            echo 'Clase de controlador inválida.';
            return;
        }

        $controller = new $className();
        if (!method_exists($controller, $action)) {
            $this->renderNotFound();
            return;
        }

        $controller->$action();
    }

    private function renderNotFound(): void
    {
        http_response_code(404);
        require __DIR__ . '/../views/layout/header.php';
        require __DIR__ . '/../views/pages/error404.php';
        require __DIR__ . '/../views/layout/footer.php';
    }
}
