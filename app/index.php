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
    $group->get("/traer", \UsuarioController::class . ":TraerUno");
    $group->post('[/]', \UsuarioController::class . ':CargarUno')->add(UsuarioMW::class . ':ValidarRol');
});

$app->group("/productos", function (RouteCollectorProxy $group){
    $group->get("[/]", \ProductoController::class . ":TraerTodos");
    $group->get("/traer", \ProductoController::class . ":TraerUno");
    $group->post("[/]", \ProductoController::class . ":CargarUno")->add(MesaMW::class . ':CambiarEstadoMesa')->add(new UsuarioMW("cliente"))
    ->add(MesaMW::class . ':ValidarCodigoNoExistente')->add(ProductoMW::class . ':ValidarTipo')->add(ProductoMW::class . ':ValidarCampos');
    $group->put("[/]", \ProductoController::class . ':ModificarUno')->add(UsuarioMW::class . ':ValidarCambioEstadoProducto')->add(UsuarioMW::class . ':ValidarRol');
});

$app->group("/pedidos", function (RouteCollectorProxy $group){
    $group->get('[/]', \PedidoController::class . ":TraerTodos")->add(new UsuarioMW("socio"));
    $group->get('/traer', \PedidoController::class . ":TraerUno");
    $group->post("[/]", \PedidoController::class . ":CargarUno")->add(MesaMW::class . ':ValidarEstadoMesa')->add(MesaMW::class . ':ValidarCodigoNoExistente')
    ->add(new UsuarioMW("mozo"))->add(PedidoMW::class . ':ValidarCampos');
    $group->put("[/]", \PedidoController::class . ':ModificarUno')->add(PedidoMW::class . ':ValidarProductosListos')->add(new UsuarioMW("mozo"))->add(UsuarioMW::class . ':ValidarRol');
});

$app->group("/mesas", function (RouteCollectorProxy $group){
    $group->get('[/]', \MesaController::class . ':TraerTodos');
    $group->get('/traer', \MesaController::class . ':TraerUno')->add(MesaMW::class . ':ValidarCodigoNoExistente');
    $group->post('[/]', \MesaController::class . ":CargarUno")->add(MesaMW::class . ':ValidarCodigoExistente')
    ->add(MesaMW::class . ':ValidarCampos');
});

$app->run();
