<?php

require_once "../app/db/AccesoDatos.php";

class Producto
{
    public $id_producto;
    public $tipo;
    public $nombre;
    public $codigo_pedido;
    //private $precio;

    public function CrearProducto()
    {
        $objetoAccesoDatos = AccesoDatos::obtenerInstancia();

        $sql = $objetoAccesoDatos->prepararConsulta("INSERT INTO productos(tipo,nombre,codigo_pedido) VALUES (:tipo,:nombre,:codigo_pedido)");
        $sql->bindValue(":tipo", $this->tipo, PDO::PARAM_STR);
        $sql->bindValue(":nombre", $this->nombre, PDO::PARAM_STR);
        $sql->bindValue(":codigo_pedido", $this->codigo_pedido, PDO::PARAM_INT);

        $sql->execute();
    }

    public static function ObtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id_producto,tipo,nombre,codigo_pedido FROM productos");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Producto');
    
    }

    public static function ObtenerProducto($id_producto)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id_producto,tipo,nombre,codigo_pedido FROM productos WHERE id_producto = :id_producto");
        $consulta->bindValue(':id_producto', $id_producto, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Producto');
    }

    // public static function modificarProducto($producto)
    // {
    //     $objAccesoDato = AccesoDatos::obtenerInstancia();
    //     $consulta = $objAccesoDato->prepararConsulta("UPDATE productos SET tipo = :tipo, nombre=:nombre, codigo_pedido=:codigo_pedido WHERE id_producto = :id_producto");
    //     $consulta->bindValue(":codigo_pedido", $producto->codigo_pedido);
    //     $consulta->bindValue(":tipo", $producto->tipo);
    //     $consulta->bindValue(":nombre", $producto->nombre);
    //     $consulta->bindValue(":id_producto", $producto->id_producto);

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