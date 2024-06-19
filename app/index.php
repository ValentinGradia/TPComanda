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
require_once "../app/middlewares/AutenticadorUsuario.php";
//require_once "../app/middlewares/AutentificadorJWT.php";

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

    $group->get("/csv", \UsuarioController::class. ':DescargarCsv');

    $group->post('[/]', \UsuarioController::class . ':CargarUno')->add(UsuarioMW::class . ':ValidarRol');

    $group->post("/csv",\UsuarioController::class . ':CargarCsv');

});
//Guardar token en variable
//deshashear contraseña
//traer tiempo restante
//guardar foto cliente
//probar descargas csv
//descarga pdfs
//crear login
//AGREGAR ATRIBUTO NOMBRE EMPLEADO A CARGO
$app->group("/productos", function (RouteCollectorProxy $group){
    $group->get("[/]", \ProductoController::class . ":TraerTodos");

    $group->get("/traer", \ProductoController::class . ":TraerUno")->add(ProductoMW::class . ':ValidarCodigoNoExistente');

    $group->post("[/]", \ProductoController::class . ":CargarUno")->add(new UsuarioMW("cliente"))->add(AutenticadorUsuario::class . ':verificarRolToken')
    ->add(MesaMW::class . ':ValidarCodigoNoExistente')->add(ProductoMW::class . ':ValidarTipo')->add(ProductoMW::class . ':ValidarCampos');

    $group->put("[/]", \ProductoController::class . ':ModificarUno')->add(UsuarioMW::class . ':ValidarCambioEstadoProducto')->add(UsuarioMW::class . ':ValidarRol')
    ->add(ProductoMW::class . ':ValidarCodigoNoExistente');
});

//SI SE PIDE OTRO PRODUCTO LUEGO DE HABER PEDIDO ANTERIORMENTE EL ESTADO DEL PEDIDO SE CAMBIA Y LA MESA TAMBIEN (PREGUNTAR)
$app->group("/pedidos", function (RouteCollectorProxy $group){
    $group->get('[/]', \PedidoController::class . ":TraerTodos")->add(new UsuarioMW("socio"));

    $group->get('/traer', \PedidoController::class . ":TraerUno")->add(PedidoMW::class . ':ValidarCodigoNoExistente');

    $group->post("[/]", \PedidoController::class . ":CargarUno")->add(ProductoMW::class . ':ValidarEstadoProducto')
    ->add(MesaMW::class . ':ValidarEstadoMesa')->add(PedidoMW::class . ':ValidarCodigoExistente')
    ->add(MesaMW::class . ':ValidarCodigoNoExistente')->add(new UsuarioMW("mozo"))->add(PedidoMW::class . ':ValidarCampos');

    $group->put("[/]", \PedidoController::class . ':ModificarUno')->add(PedidoMW::class . ':ValidarProductosListos')
    ->add(AutenticadorUsuario::class . ':verificarClave')->add(AutenticadorUsuario::class . ':verificarRolToken')
    ->add(new UsuarioMW("mozo"))->add(UsuarioMW::class . ':ValidarRol')
    ->add(PedidoMW::class . ':ValidarCodigoNoExistente');
});

$app->group("/mesas", function (RouteCollectorProxy $group){
    $group->get('[/]', \MesaController::class . ':TraerTodos');

    $group->get('/traer', \MesaController::class . ':TraerUno')->add(MesaMW::class . ':ValidarCodigoNoExistente');

    $group->post('[/]', \MesaController::class . ":CargarUno")->add(MesaMW::class . ':ValidarCodigoExistente')
    ->add(MesaMW::class . ':ValidarCampos');

    $group->put("[/]", \MesaController::class . ":ModificarUno")->add(MesaMW::class . ':CambiarEstadoMesa')
    ->add(AutenticadorUsuario::class . ':verificarClave')->add(AutenticadorUsuario::class . ':verificarRolToken')
    ->add(PedidoMW::class . ':ValidarCodigoNoExistente')->add(MesaMW::class . ':ValidarCodigoNoExistente');
});

$app->run();
