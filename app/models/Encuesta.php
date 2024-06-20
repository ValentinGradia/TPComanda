<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Encuesta extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'id_encuesta';
    protected $table = 'encuestas';
    public $incrementing = true;
    public $timestamps = false;

    const DELETED_AT = 'fecha_baja';

    protected $fillable = [
        'id_encuesta','codigo_mesa','puntaje_mesa', 'puntaje_restaurante', 'puntaje_mozo','puntaje_cocinero','comentario','nombre_cliente','fecha_baja'
    ];
}