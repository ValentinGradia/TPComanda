<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mesa extends Model //todos los modelos tienen que heredar del Model de eloquent
{
    use SoftDeletes; //para que interprete que hacemos softdeletes

    protected $primaryKey = 'codigo_mesa';
    protected $table = 'mesas'; //nombre de nuestra tabla
    public $incrementing = false; //refiere a la clave primaria
    public $timestamps = false;//eloquent por default asume que la tabla tiene una columna de cuando fue creado y updateado, ponemos en false
    //para que no suceda

    const DELETED_AT = 'fecha_baja';

    //
    protected $fillable = [
        'estado_mesa','fecha_baja'
    ];
}