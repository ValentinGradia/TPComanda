<?php

require_once './models/Usuario.php';
require_once './models/Registros.php';
require_once './middlewares/AutentificadorJWT.php';

use \App\Models\Usuario as Usuario;

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
        $nombre = $params["nombre"];
        $clave = $params["clave"];

        $usuario = Usuario::find($id);
        if($usuario !== null && password_verify($clave, $usuario->clave))
        {
            $token = AutentificadorJWT::CrearToken(array('Id_usuario' => $usuario->id_usuario, 'nombre' => $usuario->nombre, 'rol' => $usuario->rol ));
            
            $registro = new Registro();
            $registro->id_usuario = $id;
            $registro->fecha_logeo = date('Y-M-D');
            $registro->CrearRegistroLogin();    
            $payload = json_encode(array('jwt' => $token));
        }
        else
        {
            $payload = json_encode(array('mensaje'=>'datos incorrectos'));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function ValidarSesion($request, $response, $args)
    {
        $header = $request->getHeaderLine('Authorization');
        $params = $request->getParsedBody();

        $token = trim(explode("Bearer", $header)[1]);

    }

    public static function Salir($request, $response, $args){
        
        $payload = json_encode(array('mensaje'=>'Sesion Cerrada'));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}