<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Producto extends Model //todos los modelos tienen que heredar del Model de eloquent
{
    //use SoftDeletes; //para que interprete que hacemos softdeletes

    protected $primaryKey = 'id_producto';
    protected $table = 'productos'; //nombre de nuestra tabla
    public $incrementing = true; //refiere a la clave primaria
    public $timestamps = false;//eloquent por default asume que la tabla tiene una columna de cuando fue creado y updateado, ponemos en false
    //para que no suceda

    //const DELETED_AT = 'fecha_baja';

    //
    protected $fillable = [
        'tipo', 'nombre', 'precio', 'cantidad', 'estado_producto','codigo_mesa','tiempo_preparacion','id_cliente','id_empleado'
    ];
}