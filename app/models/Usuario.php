<?php

include "../db/AccesoDatos.php";
class Usuario
{
    public $id_usuario;
    public $codigo_pedido;  
    public $rol;
    public $pendientes = [];

    public function crearUsuario()
    {
        $objetoAccesoDatos = AccesoDatos::obtenerInstancia();

        $sql = $objetoAccesoDatos->prepararConsulta("INSERT INTO usuarios(id_usuario,codigo_pedido,rol) VALUES (:id_usuario,:codigo_pedido,:rol)");

        $sql->bindValue(":id_usuario",$this->id_usuario, PDO::PARAM_INT);
        $sql->bindValue(":codigo_pedido", $this->codigo_pedido, PDO::PARAM_INT);
        $sql->bindValue(":rol", $this->rol, PDO::PARAM_STR);

        $sql->execute();
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id_usuario, codigo_pedido, rol FROM usuarios");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Usuario');
    
    }

    public static function obtenerUsuario($id_usuario)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id_usuario, codigo_pedido, rol FROM usuarios WHERE id_usuario = :id_usuario");
        $consulta->bindValue(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchObject('Usuario');
    }

    // public static function modificarUsuario($usuario)
    // {
    //     $objAccesoDato = AccesoDatos::obtenerInstancia();
    //     $consulta = $objAccesoDato->prepararConsulta("UPDATE usuarios SET codigo_pedido = :codigo_pedido, rol = :rol WHERE id_usuario = :id_usuario");
    //     $consulta->bindValue(":codigo_pedido", $usuario->codigo_pedido);
    //     $consulta->bindValue(":rol", $usuario->rol);
    //     $consulta->bindValue(":id_usuario", $usuario->id_usuario);

    //     $consulta->execute();
    // }

    // public static function borrarUsuario($usuario)
    // {
    //     $objAccesoDato = AccesoDatos::obtenerInstancia();
    //     $consulta = $objAccesoDato->prepararConsulta("UPDATE usuarios SET fechaBaja = :fechaBaja WHERE id = :id");
    //     $fecha = new DateTime(date("d-m-Y"));
    //     $consulta->bindValue(':id', $usuario, PDO::PARAM_INT);
    //     $consulta->bindValue(':fechaBaja', date_format($fecha, 'Y-m-d H:i:s'));
    //     $consulta->execute();
    // }
    
}