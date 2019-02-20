<?php
require_once "AutentificadorJWT.php";
require_once "empleado.php";
require_once "compra.php";
class MWPedidosPendientes
{
 /**
   * @api {any} /MWparaAutenticar/  Verificar Usuario
   * @apiVersion 0.1.0
   * @apiName VerificarUsuario
   * @apiGroup MIDDLEWARE
   * @apiDescription  Por medio de este MiddleWare verifico las credeciales antes de ingresar al correspondiente metodo 
   *
   * @apiParam {ServerRequestInterface} request  El objeto REQUEST.
 * @apiParam {ResponseInterface} response El objeto RESPONSE.
 * @apiParam {Callable} next  The next middleware callable.
   *
   * @apiExample Como usarlo:
   *    ->add(\MWparaAutenticar::class . ':VerificarUsuario')
   */
	public function VerificarEncargado($request, $response, $next) {
        $token;
		$objDelaRespuesta= new stdclass();
		$objDelaRespuesta->respuesta="";
		
		//Verifico header
		$arrayConToken = $request->getHeader('token');
		if(!empty($arrayConToken) && isset($arrayConToken))
			$token=$arrayConToken[0];
		
		if(!empty($token)){
			try{
				AutentificadorJWT::verificarToken($token);
				$objDelaRespuesta->esValido=true;      
			}catch (Exception $e) {      
				$objDelaRespuesta->excepcion=$e->getMessage();
				$objDelaRespuesta->esValido=false;     
			}
		}else{
			$objDelaRespuesta->esValido=false;
		}
		if($objDelaRespuesta->esValido){
			if($request->isPost() || $request->isGet() || $request->isDelete()|| $request->isPut())
				{		
					$payload=AutentificadorJWT::ObtenerData($token);
					
					if($payload->ocupacion!=1 && $payload->ocupacion!=5)
					{
            $requestbody = $request->getParsedBody();
            $requestbody["encargado"] = $payload->ocupacion;
            $requestbody["empleado"] = $payload->usuario;
						$request = $request->withParsedBody($requestbody);
						$response = $next($request, $response);
					}		           	
					else
					{	
						$objDelaRespuesta->respuesta="Esta acción solo puede ser realizada por un cocinero, bartender o cervecero.";
					}
				}
		}
		else{
			$objDelaRespuesta->respuesta="Solo usuarios registrados";
		}

		if($objDelaRespuesta->respuesta!="")
		{
			$nueva=$response->withJson($objDelaRespuesta->respuesta, 403);  
			return $nueva;
		}
		  
		 return $response;   
	}
}