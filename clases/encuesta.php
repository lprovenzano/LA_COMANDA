<?php

class Encuesta
{

    public static function Alta($request, $response, $args)
    {
        try {
            $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
            $ArrayDeParametros = $request->getParsedBody();
            $mesaEncuestada = $ArrayDeParametros["mesa"];
            $puntosMesa = $ArrayDeParametros["puntosMesa"];
            $puntosRestaurant = $ArrayDeParametros["puntosRestaurant"];
            $puntosMozo = $ArrayDeParametros["puntosMozo"];
            $puntosCocinero = $ArrayDeParametros["puntosCocinero"];
            $experiencia = $ArrayDeParametros["experiencia"];

            if(!empty($mesaEncuestada) && !empty($experiencia)){
                if(Encuesta::ValidarPuntaje($puntosMesa) && Encuesta::ValidarPuntaje($puntosRestaurant) && Encuesta::ValidarPuntaje($puntosMozo) && Encuesta::ValidarPuntaje($puntosCocinero)){
                    $promedio = ($puntosMesa+$puntosRestaurant+$puntosMozo+$puntosCocinero)/4;

                    $consulta = $objetoAccesoDato->RetornarConsulta("INSERT INTO `encuesta`(`mesaEncuestada`, `fecha`, `puntaje_mesa`, `puntaje_restaurant`, `puntaje_mozo`, `puntaje_cocinero`, `promedio`,`experiencia`) VALUES ($mesaEncuestada,NOW(), $puntosMesa, $puntosRestaurant, $puntosMozo, $puntosCocinero, $promedio,'$experiencia')");
                    $consulta->execute();
                    $cantidad = $consulta->rowcount();
                    if ($cantidad > 0) {
                        $data = "Gracias por responder la encuesta!";
                        return $response->withJson($data, 200);
                    } else {
                        throw new Exception("No existe la mesa");
                    }
                }else{
                    throw new Exception("Puntaje del 1 al 10");
                }
            }else{
                throw new Exception("Hay campos vacios");
            }
        } catch (Exception $e) {
            return $response->withJson($e->getMessage(), 400);
        }

    }

    private static function ValidarPuntaje($item){
        if($item>0 && $item<11){
            return true;
        }
        return false;
    }

}
