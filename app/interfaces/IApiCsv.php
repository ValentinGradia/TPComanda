<?php
interface IApiCsv
{
    public static function DescargarCsv($request, $response, $args);
    public static function CargarCsv($request, $response, $args);
}