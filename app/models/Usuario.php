<?php

require_once "../app/db/AccesoDatos.php";
class Usuario
{
    public $id_usuario;
    public $nombre;
    public $clave;
    public $rol;

    public function crearUsuario()
    {
        $objetoAccesoDatos = AccesoDatos::obtenerInstancia();

        $sql = $objetoAccesoDatos->prepararConsulta("INSERT INTO usuarios(rol,nombre,clave) VALUES (:rol,:nombre,:clave)");

        $sql->bindValue(":rol", $this->rol, PDO::PARAM_STR);
        $sql->bindValue(":nombre", $this->nombre, PDO::PARAM_STR);
        $sql->bindValue(":clave", $this->clave, PDO::PARAM_INT);

        $sql->execute();
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id_usuario,nombre,clave,rol FROM usuarios");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Usuario');
    
    }

    public static function obtenerUsuario($id_usuario)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id_usuario,nombre,clave,rol FROM usuarios WHERE id_usuario = :id_usuario");
        $consulta->bindValue(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchObject('Usuario');
    }

    public static function modificarUsuario($usuario)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE usuarios SET codigo_pedido = :codigo_pedido, rol = :rol,nombre=:nombre,
        clave=:clave WHERE id_usuario = :id_usuario");
        $consulta->bindValue(":codigo_pedido", $usuario->codigo_pedido);
        $consulta->bindValue(":rol", $usuario->rol);
        $consulta->bindValue(":nombre", $usuario->nombre);
        $consulta->bindValue(":clave", $usuario->clave);
        $consulta->bindValue(":id_usuario", $usuario->id_usuario);

        $consulta->execute();
    }

    public static function borrarUsuario($usuario)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE usuarios SET fechaBaja = :fechaBaja WHERE id_usuario = :id_usuario");
        $fecha = new DateTime(date("d-m-Y"));
        $consulta->bindValue(':id_usuario', $usuario->id_usuario, PDO::PARAM_INT);
        $consulta->bindValue(':fechaBaja', date_format($fecha, 'Y-m-d H:i:s'));
        $consulta->execute();
    }
    
}