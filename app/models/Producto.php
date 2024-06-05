<?php

class Producto
{
    public $id_producto;
    public $tipo;
    public $nombre;
    public $precio;
    public $cantidad;

    public function CrearProducto()
    {
        $objetoAccesoDatos = AccesoDatos::obtenerInstancia();

        $sql = $objetoAccesoDatos->prepararConsulta("INSERT INTO productos(tipo,nombre,precio,cantidad) VALUES (:nombre,:nombre,:precio,:cantidad)");
        $sql->bindValue(":tipo", $this->tipo, PDO::PARAM_STR);
        $sql->bindValue(":nombre", $this->nombre, PDO::PARAM_STR);
        $sql->bindValue(":precio", $this->precio);
        $sql->bindValue(":cantidad", $this->cantidad);

        $sql->execute();
    }

    public static function ObtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id_producto,tipo,nombre,precio,cantidad FROM productos");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Producto');
    
    }

    public static function ObtenerProducto($id_producto)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id_producto,tipo,nombre,precio,cantidad FROM productos WHERE id_producto = :id_producto");
        $consulta->bindValue(':id_producto', $id_producto, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Producto');
    }

    public static function modificarProducto($producto)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE productos SET tipo = :tipo, nombre=:nombre, precio=:precio, cantidad=:cantidad WHERE id_producto = :id_producto");
        $consulta->bindValue(":tipo", $producto->tipo);
        $consulta->bindValue(":nombre", $producto->nombre);
        $consulta->bindValue(":precio", $producto->precio);
        $consulta->bindValue(":cantidad", $producto->cantidad);
        $consulta->bindValue(":id_producto", $producto->id_producto);

        $consulta->execute();
    }

    public static function borrarProducto($producto)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("DELETE FROM productos WHERE id_producto = :id");
        $consulta->bindValue(":id",$producto->id_producto, PDO::PARAM_INT);
        $consulta->execute();

    }
}