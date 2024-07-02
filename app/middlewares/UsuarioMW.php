<?php

use App\Models\DetallePedido;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as ResponseClass;


use \App\Models\Producto as Producto;

require_once './models/Producto.php';
require_once './models/DetallePedido.php';
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

    public static function VerificarSector(Request $request, RequestHandler $handler)
    {
        $response = new ResponseClass();
        $params = $request->getQueryParams();
        $sector = $params["sector"];
        if($sector !== "cocina" && $sector !== "barra" && $sector !== "patio trasero")
        {
            $response->getBody()->write(json_encode(array("Error" => "sector invalido")));
        }
        else
        {
            $response = $handler->handle($request);
        }

        return $response;
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

        $detallePedido = DetallePedido::where('id_producto',$params["id_producto"]);

        $producto = Producto::find($params["id_producto"]);
        $tipo_producto = $producto->tipo;

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
                    $response = $handler->handle($request);
                }
                break;
            
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