<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as ResponseClass;

use function PHPSTORM_META\map;
use \App\Models\Mesa as Mesa;
use \App\Models\Producto as Producto;

require_once './interfaces/IApiCampos.php';
require_once './models/Producto.php';


class ProductoMW implements IApiCampos
{
    public static function ValidarCampos(Request $request, RequestHandler $handler)
    {

        $response = new ResponseClass();

        $params = $request->getParsedBody();

        if(isset($params["tipo"], $params["nombre"], $params["precio"], $params["cantidad"],$params["codigo_mesa"])) 
        {
            $response = $handler->handle($request);
        }
        else
        {
            $response->getBody()->write(json_encode(array("error" => "campos invalidos"))); 
        }

        return $response;
    }

    public static function ValidarCodigoNoExistente(Request $request, RequestHandler $handler)
    {
        $response = new ResponseClass();
        $queryParams = $request->getQueryParams();
        $bodyParams = $request->getParsedBody();
        $params = !empty($queryParams) ? $queryParams : $bodyParams;

        if(Mesa::find($params["id_producto"]))
        {
            $response = $handler->handle($request);
        }
        else
        {
            $response->getBody()->write(json_encode(array("error" => "id producto no existente")));
        }

        return $response;
    }

    public static function ValidarEstadoProducto(Request $request, RequestHandler $handler)
    {
        $response = new ResponseClass();
        $params = $request->getParsedBody();
        $codigo_mesa = $params["codigo_mesa"];
        
        $productos = Producto::where('cdigo_mesa',$codigo_mesa)->first();

        $productosPendientes = array_filter($productos, function($producto){
            return $producto->estado_producto == "pendiente";
        });

        //validar que los productos hayan sido pedidos para poder crear dicho pedido
        if(count($productosPendientes) !== 0)
        {
            $response = $handler->handle($request);
        }
        else
        {
            $response->getBody()->write(json_encode(array("error" => "aun el cliente no pidio ningun producto"))); 
        }

        return $response;

    }

    public static function ValidarTipo(Request $request, RequestHandler $handler)
    {
        $response = new ResponseClass();

        $params = $request->getParsedBody();

        if($params["tipo"] === "cerveza" || $params["tipo"] === "trago" || $params["tipo"] === "comida")
        {
            $response = $handler->handle($request);
        }
        else
        {
            $response->getBody()->write(json_encode(array("error" => "tipo de producto invalido"))); 
        }

        return $response;

    }
}