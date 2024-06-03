<?php
// Error Handling
error_reporting(-1);
ini_set('display_errors', 1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;

require __DIR__ . '/../vendor/autoload.php';

// Instantiate App
$app = AppFactory::create();

// Add error middleware
$app->addErrorMiddleware(true, true, true);

// Add parse body
$app->addBodyParsingMiddleware();

// Routes
$app->group("/usuarios", function (RouteCollectorProxy $group){
    $group->get("[/]", \UsuarioController::class . ":TraerTodos");
    $group->get("/{id_usuario}", \UsuarioController::class . ":TraerUno");
    $group->post("[/]", \UsuarioController::class . ":CargarUno");
});

$app->group("/productos", function (RouteCollectorProxy $group){
    $group->get("[/]", \ProductoController::class . ":TraerTodos");
    $group->get("/{id_producto}", \ProductoController::class . ":TraerUno");
    $group->post("[/]", \ProductoController::class . ":CargarUno");
});

$app->group("/pedidos", function (RouteCollectorProxy $group){
    $group->get("[/]", \PedidoController::class . ":TraerTodos");
    $group->get("/{codigo_pedido}", \PedidoController::class . ":TraerUno");
    $group->post("[/]", \PedidoController::class . ":CargarUno");
});

$app->group("/mesas", function (RouteCollectorProxy $group){
    $group->get("[/]", \MesaController::class . ":TraerTodos");
    $group->get("/{codigo_mesa}", \MesaController::class . ":TraerUno");
    $group->post("[/]", \MesaController::class . ":CargarUno");
});

$app->run();
