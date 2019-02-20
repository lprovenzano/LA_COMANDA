<?php
require_once "AutentificadorJWT.php";
require_once "empleado.php";


class MWLog
{
    public static function GenerarLog($request, $response, $next){
        $nombre;
        $token;
        $arrayConToken = $request->getHeader('token');
        
        if(!empty($arrayConToken))
            $token=$arrayConToken[0];

        
        
        if(!empty($token)){
            $payload = AutentificadorJWT::ObtenerData($token);
            $nombre = $payload->usuario;
            $metodo = $request->getMethod();
            $ruta = $request->getUri();
            
            
        }else{
            $nombreReq = $request->getParsedBody();
            $nombre = "Anonimo";
            $metodo = $request->getMethod();
            $ruta = $request->getUri();
        }

        Empleado::Log($nombre, $metodo, $ruta);

        return $next($request, $response);
        
    }
}