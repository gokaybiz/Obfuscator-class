<?php
class Router
{
    protected $routes = [];

    /**
     * Registers a route and its handling function.
     *
     * @param string $route The route to register.
     * @param callable $callback The callback function to handle the route.
     * @return void
     */
    public function add($route, callable $callback): void
    {
        // Normalize the route.
        $route = trim($route, "/");
        $this->routes[$route] = $callback;
    }

    /**
     * Dispatches the current URI request.
     *
     * @param string $uri The URI to dispatch.
     * @return void
     */
    public function dispatch($uri): void
    {
        $uri = trim($uri, "/");
        if (isset($this->routes[$uri])) {
            call_user_func($this->routes[$uri]);
        } else {
            $this->handle404();
        }
    }

    /**
     * Handles the 404 error page.
     *
     * @return void
     */
    public function handle404(): void
    {
        if (isset($this->routes["404"])) {
            call_user_func($this->routes["404"]);
        } else {
            header("HTTP/1.0 404 Not Found");
            echo "404 Not Found";
        }
    }

    /**
     * Checks if a page exists.
     *
     * @param string $page The page name that planned to load.
     * @return string|bool
     */
    public function checkPage(string $page): string|bool
    {
        $path = __DIR__ . "/../partials/pages/$page.php";
        if (file_exists($path)) {
            return $path;
        } else {
            $this->handle404();
            return false;
        }
    }
}
?>
