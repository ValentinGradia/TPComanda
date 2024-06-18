<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

require_once './middlewares/AutentificadorJWT.php';

class AutenticadorUsuario
{
    public static function verificarRolToken(Request $request, RequestHandler $handler): Response
    {
        $header = $request->getHeaderLine('Authorization');

        if($header)
        {
            $token = trim(explode("Bearer", $header)[1]);
        }
        else{$token = '';}

        try {
            AutentificadorJWT::VerificarToken($token);
            $response = $handler->handle($request);
        } catch (Exception $e) {
            $response = new Response();
            $payload = json_encode(array('mensaje' => 'ERROR: Hubo un error con el TOKEN'));
            $response->getBody()->write($payload);
        }
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function verificarClave(Request $request, RequestHandler $handler)
    {
        $header = $request->getHeaderLine('Authorization');
        $params = $request->getParsedBody();
        $response = new Response();

        $token = trim(explode("Bearer", $header)[1]);

        try
        {
            $datos = AutentificadorJWT::ObtenerData($token);

            if(isset($params["clave"]))
            {
                if($datos->clave == $params["clave"])
                {
                    $response = $handler->handle($request);
                }
                else
                {
                    $response->getBody()->write(json_encode(array("error" => "contraseña incorrecta"))); 
                }
            }
            else{
                throw new Exception("Ingrese su contraseña"); 
            }
        }catch(Exception $e){
            $response->getBody()->write($e->getMessage());
        }

        return $response;
    }
}