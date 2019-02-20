<?php
require_once "AutentificadorJWT.php";
require_once "empleado.php";
require_once "compra.php";

class MWparaCompras
{
    public function VerificarUsuarioCompras($request, $response, $next){
        $objDelaRespuesta= new stdclass();
		$objDelaRespuesta->respuesta="";
		
		//Verifico header
		$arrayConToken = $request->getHeader('token');
		$token=$arrayConToken[0];

		try{
			AutentificadorJWT::verificarToken($token);
			$objDelaRespuesta->esValido=true;      
		}catch (Exception $e) {      
			$objDelaRespuesta->excepcion=$e->getMessage();
			$objDelaRespuesta->esValido=false;     
		}
		if($objDelaRespuesta->esValido){
            if($request->isGet()){
                $payload=AutentificadorJWT::ObtenerData($token);
					if($payload->perfil=="admin")
					{
						$response = $next($request, $response);
					}		           	
					else
					{	
                        $index;
                        $response = $next($request, $response);
                        $body = json_decode($response->getBody());
                        for ($i=0; $i < count($body); $i++) { 
                            if($body[$i]->email == $payload->email){
                                $aux = $i;
                            }
                        }
                        $objDelaRespuesta->respuesta=$body[$aux];
					}
            }
            if($request->isPost()){
                //Determino quien hizo el add 
                $payload = AutentificadorJWT::ObtenerData($token);
                $requestbody = $request->getParsedBody();
                $requestbody["email"] = $payload->email;
                $request = $request->withParsedBody($requestbody);
                $response = $next($request, $response);
            }
        }else{
			$objDelaRespuesta->respuesta="Solo usuarios registrados";
		}

		if($objDelaRespuesta->respuesta!="")
		{
			$nueva=$response->withJson($objDelaRespuesta->respuesta, 401);  
			return $nueva;
		}

        return $response;
    }
}