<?php
define("PRIV", "1");

require_once "inc/config.php";
require_once "inc/router.php";
require_once "inc/obfuscator.php";

include "partials/header.php";

$router = new Router();
$router->add("/", function () use ($router) {
    $page = $router->loadPageFromDisk("home");

    if ($page !== false) {
        $obfuscator = new Obfucator($page);
        echo $obfuscator->encode();
    }
    // Don't worry if it doesn't exist, it will be handled by the router.
});

$route = isset($_GET["page"]) ? $_GET["page"] : "/";
$router->dispatch($route);

include "partials/footer.php";

?>
