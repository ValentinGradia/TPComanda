<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as ResponseClass;


use \App\Models\Producto as Producto;

require_once './models/Producto.php';
require_once './middlewares/AutentificadorJWT.php';

class UsuarioMW
{
    private $perfil ='';

    public function __construct($perfil) 
    {
        $this->perfil = $perfil;
    }

    public function __invoke(Request $request, RequestHandler $handler)
    {
        return $this->VerificarRol($request, $handler);
    }

    public function VerificarRol(Request $request, RequestHandler $handler)
    {
        $response = new ResponseClass();
        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);

        $datos = AutentificadorJWT::ObtenerData($token);

        if($datos->rol !== $this->perfil)
        {
            $response->getBody()->write(json_encode(array("Error" => "No sos ".$this->perfil)));
        }
        else
        {
            $response = $handler->handle($request);
        }

        return $response;
    }

    public static function ValidarCambioEstadoProducto(Request $request, RequestHandler $handler)
    {
        $params = $request->getParsedBody();

        $response = new ResponseClass();
        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);
        $datos = AutentificadorJWT::ObtenerData($token);
        $rol = $datos->rol;

        $producto = Producto::find($params["id_producto"]);
        $tipo_producto = $producto->tipo;

        //validar que el producto ingresado este pendiente o en preparacion
        if($producto->estado_producto == "pendiente" || $producto->estado_producto == "en preparacion")
        {
            //Dependiendo el tipo de producto ira a determinado empleado
            switch($tipo_producto)
            {
                case "comida":
                    if(!($rol == "cocinero"))
                    {
                        $response->getBody()->write(json_encode(array("error" => "no puedes modificar el tipo")));
                    }
                    else
                    {
                        $producto->estado_producto = "listo";
                        $response = $handler->handle($request);
                    }
                    break;
                case "trago":
                        if(!$rol == "bartender")
                        {
                            $response->getBody()->write(json_encode(array("error" => "no puedes modificar el tipo")));
                        }
                        else
                        {
                            $producto->estado_producto = "listo";
                            $response = $handler->handle($request);
                        }
                        break;
                default:
                    if(!$rol == "cervecero")
                    {
                        $response->getBody()->write(json_encode(array("error" => "no puedes modificar el tipo")));
                    }
                    else
                    {
                        $producto->estado_producto = "listo";
                        $response = $handler->handle($request);
                    }
                    break;
                
            }
        }
        else
        {
            $response->getBody()->write(json_encode(array("error" => "algo salio mal...")));
        }



        return $response;
    }


    public static function ValidarRol(Request $request, RequestHandler $handler)
    {
        $response = new ResponseClass();

        $params = $request->getParsedBody();

        if(isset($params["rol"]))
        {
            $rol = $params["rol"];
            if($rol == "socio" || $rol == "bartender" || $rol == "mozo" ||
            $rol == "cocinero" || $rol == "cliente" || $rol == "cervecero")
            {
                $response = $handler->handle($request);
            }
            else
            {
                $response->getBody()->write(json_encode(array("error" => "rol incorrecto")));
            }
        }
        else
        {
            $response->getBody()->write(json_encode(array("error" => "complete su rol")));
        }

        return $response;
    }
}