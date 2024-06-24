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
use Illuminate\Database\Capsule\Manager as Capsule;

require __DIR__ . '/../vendor/autoload.php';
require_once "../app/controllers/MesaController.php";
require_once "../app/controllers/UsuarioController.php";
require_once "../app/controllers/PedidoController.php";
require_once "../app/controllers/ProductoController.php";
require_once "../app/controllers/EncuestaController.php";
require_once "../app/controllers/VentaController.php";
require_once "../app/db/AccesoDatos.php";
require_once "../app/middlewares/UsuarioMW.php";
require_once "../app/middlewares/MesaMW.php";
require_once "../app/middlewares/ProductoMW.php";
require_once "../app/middlewares/PedidoMW.php";
require_once "../app/middlewares/Logger.php";
require_once "../app/middlewares/AutenticadorUsuario.php";
require_once "../app/middlewares/AutentificadorJWT.php";

// Instantiate App
$app = AppFactory::create(); 

// Load ENV
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Add error middleware
$app->addErrorMiddleware(true, true, true);

// Eloquent
$container=$app->getContainer();

$capsule = new Capsule;
$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => $_ENV['MYSQL_HOST'],
    'database'  => $_ENV['MYSQL_DB'],
    'username'  => $_ENV['MYSQL_USER'],
    'password'  => $_ENV['MYSQL_PASS'],
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);

// Add parse body
$app->addBodyParsingMiddleware();

$capsule->setAsGlobal();

$capsule->bootEloquent();

use \App\Models\Pedido as Pedido;
use App\Models\Producto as Producto;
use App\Models\Usuario as Usuario;

// Routes

$app->group("/sesion", function(RouteCollectorProxy $group){
    $group->post('[/]', \Logger::class . ':Loguear');
    $group->get('[/]', \Logger::class.'::Salir');
});

$app->group("/usuarios", function (RouteCollectorProxy $group){
    $group->get('[/]', \UsuarioController::class . ':TraerTodos');

    $group->get("/traer", \UsuarioController::class . ":TraerUno");

    $group->get("/csv", \UsuarioController::class. ':DescargarCsv');

    $group->get('/operaciones',\UsuarioController::class . ':TraerOperaciones');

    $group->post('[/]', \UsuarioController::class . ':CargarUno')->add(UsuarioMW::class . ':ValidarRol');

    $group->post("/csv",\UsuarioController::class . ':CargarCsv');

    $group->put('[/]', \UsuarioController::class . ':ModificarUno');

})->add(new UsuarioMW("admin"))->add(Logger::class . ':ValidarSesion');

//descarga pdfs
//VALIDAR DELETE 

$app->group("/productos", function (RouteCollectorProxy $group){
    $group->get("[/]", \ProductoController::class . ":TraerTodos");

    $group->get("/traer", \ProductoController::class . ":TraerUno")->add(ProductoMW::class . ':ValidarCodigoNoExistente');

    $group->get("/csv", \ProductoController::class. ':DescargarCsv');

    $group->post("[/]", \ProductoController::class . ":CargarUno")->add(new UsuarioMW("cliente"))
    ->add(MesaMW::class . ':ValidarCodigoNoExistente')->add(ProductoMW::class . ':ValidarTipo')->add(ProductoMW::class . ':ValidarCampos');

    $group->put("[/]", \ProductoController::class . ':ModificarUno')->add(UsuarioMW::class . ':ValidarCambioEstadoProducto')->add(ProductoMW::class . ':ValidarCodigoNoExistente');

    $group->post("/csv",\ProductoController::class . ':CargarCsv');

})->add(Logger::class . ':ValidarSesion');

$app->group("/pedidos", function (RouteCollectorProxy $group){
    $group->get('[/]', \PedidoController::class . ":TraerTodos")->add(new UsuarioMW("socio"));

    $group->get('/traer', \PedidoController::class . ":TraerUno")->add(PedidoMW::class . ':ValidarCodigoNoExistente');

    $group->get('/tiempoDemora', \PedidoController::class . ':TraerTiempoRestante')->add(new UsuarioMW("cliente"))
    ->add(PedidoMW::class . ':ValidarPedidoEnPreparacion')->add(PedidoMW::class . ':ValidarCodigoNoExistente')
    ->add(MesaMW::class . ':ValidarCodigoNoExistente');

    $group->get('/entregadosFueraTiempoEstipulado', \PedidoController::class . ':TraerPedidosNoEntregadosATiempo');

    $group->get('/estadistica30Dias', \PedidoController::class . ':Estadisticas30Dias')->add(new UsuarioMW("socio"));

    $group->get("/csv", \PedidoController::class. ':DescargarCsv');

    $group->post("[/]", \PedidoController::class . ":CargarUno")->add(ProductoMW::class . ':ValidarEstadoProducto')
    ->add(MesaMW::class . ':ValidarEstadoMesa')->add(PedidoMW::class . ':ValidarCodigoExistente')
    ->add(MesaMW::class . ':ValidarCodigoNoExistente')->add(new UsuarioMW("mozo"))->add(PedidoMW::class . ':ValidarCampos');

    $group->put("[/]", \PedidoController::class . ':ModificarUno')->add(PedidoMW::class . ':ValidarProductosListos')
    ->add(new UsuarioMW("mozo"))->add(PedidoMW::class . ':ValidarCodigoNoExistente');

    $group->post("/csv",\PedidoController::class . ':CargarCsv');

})->add(Logger::class . ':ValidarSesion');

$app->group("/mesas", function (RouteCollectorProxy $group){
    $group->get('[/]', \MesaController::class . ':TraerTodos')->add(new UsuarioMW("socio"));

    $group->get('/traer', \MesaController::class . ':TraerUno')->add(MesaMW::class . ':ValidarCodigoNoExistente');

    $group->get('/traerMasUsada', \VentaController::class . ':TraerMesaMasUsada');

    $group->get("/csv", \MesaController::class. ':DescargarCsv');

    $group->post('[/]', \MesaController::class . ":CargarUno")->add(MesaMW::class . ':ValidarCodigoExistente')
    ->add(MesaMW::class . ':ValidarCampos')->add(new UsuarioMW("admin"));

    $group->put("[/]", \MesaController::class . ":ModificarUno")->add(MesaMW::class . ':CambiarEstadoMesa')
    ->add(MesaMW::class . ':ValidarCodigoNoExistente')->add(PedidoMW::class . ':ValidarCodigoNoExistente');

    $group->post("/csv",\PedidoController::class . ':CargarCsv');

})->add(Logger::class . ':ValidarSesion');

$app->post('/cobrarPedido', \VentaController::class . ':CargarUno')->add(new UsuarioMW('mozo'))->add(PedidoMW::class .':ValidarCodigoNoExistente')
->add(Logger::class . ':ValidarSesion');

$app->group("/encuesta", function (RouteCollectorProxy $group){

    $group->get('/mejoresReseñas', \EncuestaController::class . ':TraerMejoresReseñas')->add(new UsuarioMW('socio'));

    $group->post('[/]', \EncuestaController::class .':CargarUno')->add(new UsuarioMW('cliente'))->add(MesaMW::class . ':ValidarCodigoNoExistente');

})->add(Logger::class . ':ValidarSesion');

$app->group("/cargarFoto", function (RouteCollectorProxy $group){
    $group->post('[/]', function (Request $request, Response $response){
        $params = $request->getUploadedFiles();
        $archivo = $params["file"]->getFilePath();

        $parametros = $request->getParsedBody();
        $codigo_pedido = $parametros["codigo_pedido"];
        $pedido = Pedido::find($parametros["codigo_pedido"]);
        $codigo_mesa = $pedido->codigo_mesa;

        $producto = Producto::where('codigo_mesa',$codigo_mesa)->where('estado_producto','pendiente')->first();

        $usuario = Usuario::find($producto->id_cliente);

        $nombre_archivo = "$codigo_pedido"."-$codigo_mesa"."-$usuario->nombre";
        $ruta = "./Foto-mesas/";

        move_uploaded_file($archivo, $ruta . $nombre_archivo . ".png");

        
        $payload = json_encode(array("mensaje" => "Foto creada con exito"));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');


        
    })->add(new UsuarioMW('mozo'))
    ->add(PedidoMW::class . ':ValidarPedidoEnPreparacion')->add(PedidoMW::class . ':ValidarCodigoNoExistente');
})->add(Logger::class . ':ValidarSesion');

$app->run();
