<?php
// Error Handling
error_reporting(-1);
ini_set('display_errors', 1);


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteContext;

require __DIR__ . '/../vendor/autoload.php';
require_once "../app/controllers/MesaController.php";
require_once "../app/controllers/UsuarioController.php";
require_once "../app/controllers/PedidoController.php";
require_once "../app/controllers/ProductoController.php";
require_once "../app/db/AccesoDatos.php";
require_once "../app/middlewares/UsuarioMW.php";
require_once "../app/middlewares/MesaMW.php";
require_once "../app/middlewares/ProductoMW.php";
require_once "../app/middlewares/PedidoMW.php";

// Instantiate App
$app = AppFactory::create();

// Load ENV
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Add error middleware
$app->addErrorMiddleware(true, true, true);

// Add parse body
$app->addBodyParsingMiddleware();

// Routes

// $app->get("[/]", function(Request $request, Response $response){
//     $response->getBody()->write("funciona!");

//     return $response;
// });

$app->group("/usuarios", function (RouteCollectorProxy $group){
    $group->get('[/]', \UsuarioController::class . ':TraerTodos');
    $group->get("/{id_usuario}", \UsuarioController::class . ":TraerUno");
    $group->post('[/]', \UsuarioController::class . ':CargarUno')->add(UsuarioMW::class . ':ValidarRol');
});

$app->group("/productos", function (RouteCollectorProxy $group){
    $group->get("[/]", \ProductoController::class . ":TraerTodos");
    $group->get("/{id_producto}", \ProductoController::class . ":TraerUno");
    $group->post("[/]", \ProductoController::class . ":CargarUno");
});

$app->group("/pedidos", function (RouteCollectorProxy $group){
    $group->get("[/]", \PedidoController::class . ":TraerTodos");
    $group->get("/{codigo_pedido}", \PedidoController::class . ":TraerUno");
    $group->post("[/]", \PedidoController::class . ":CargarUno")->add(new UsuarioMW("mozo"))
    ->add(PedidoMW::class . ':ValidarCampos');
});

$app->group("/mesas", function (RouteCollectorProxy $group){
    $group->get("[/]", \MesaController::class . ":TraerTodos");
    $group->get("/{codigo_mesa}", \MesaController::class . ":TraerUno");
    $group->post("[/]", \MesaController::class . ":CargarUno");
});

$app->run();
