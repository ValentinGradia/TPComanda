<?php

require_once './models/Usuario.php';
require_once './models/Registro.php';
require_once './middlewares/AutentificadorJWT.php';

use \App\Models\Usuario as Usuario;
use \App\Models\Registro as Registro;
use Slim\Psr7\Response;


class Logger
{
    public static function LogOperacion($request, $response, $next)
    {
        $retorno = $next($request, $response);
        return $retorno;
    }

    public static function Loguear($request, $response, $args)
    {
        $params = $request->getParsedBody();
        $id = $params["id_usuario"];
        $clave = $params["clave"];

        $usuario = Usuario::find($id);
        if($usuario !== null && password_verify($clave, $usuario->clave))
        {
            $token = AutentificadorJWT::CrearToken(array('Id_usuario' => $usuario->id_usuario, 'nombre' => $usuario->nombre, 'rol' => $usuario->rol,
            'estado' => $usuario->estado));
            
            $registro = new Registro();
            $registro->id_usuario = $id;
            $registro->fecha_logueo = date('Y-m-d H:i:s');
            $registro->save();  
            $payload = json_encode(array('jwt' => $token));
        }
        else
        {
            $payload = json_encode(array('mensaje'=>'datos incorrectos'));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function ValidarSesion($request, $handler)
    {
        $header = $request->getHeaderLine('Authorization');
        $response = new Response();

        if($header)
        {
            $token = trim(explode("Bearer", $header)[1]);
        }
        else{$token = '';}

        try
        {
            $datos = AutentificadorJWT::ObtenerData($token);
            if($datos->estado == "activo")
            {
                $response = $handler->handle($request);
            }else
            {
                $response->getBody()->write(json_encode(array("error" => "no es un usuario activo"))); 
            }
        }catch(Exception $e)
        {
            $response->getBody()->write(json_encode(array("error" => $e->getMessage()))); 
        }

        return $response;
        
    }
}