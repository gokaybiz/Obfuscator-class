<?php
define("PRIV", "1");

require __DIR__ . "/../vendor/autoload.php";
use Gokaybiz\Obfuscator\Obfuscator;

require_once "inc/router.php";
include "partials/header.php";

$router = new Router();
$router->add("/", function () use ($router) {
    $page = $router->checkPage("home");

    if ($page !== false) {
        $obfuscator = new Obfuscator($page);
        echo $obfuscator->encode();
    }
    // Don't worry if it doesn't exist, it will be handled by the router.
});

$router->add("test", function () use ($router) {
    $page = $router->checkPage("test");

    if ($page !== false) {
        $obfuscator = new Obfuscator($page);
        echo $obfuscator->encode();
    }
});

$router->add("404", function () use ($router) {
    $page = $router->checkPage("err/404");

    if ($page !== false) {
        include $page;
    }
});

$route = isset($_GET["page"]) ? $_GET["page"] : "/";
$router->dispatch($route);

include "partials/footer.php";

?>
