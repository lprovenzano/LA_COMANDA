<?php

class Mesa{
    public $codigoMesa;
    public $estado;

    public static function VerMesas($request, $response, $args){
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta =$objetoAccesoDato->RetornarConsulta("SELECT * FROM mesas");
		$consulta->execute();
		return $response->withJson($consulta->fetchAll(PDO::FETCH_CLASS, "Mesa"), 200);
    }

    public static function Alta($request, $response, $args){
		try{
			$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
			$consulta =$objetoAccesoDato->RetornarConsulta("INSERT INTO `mesas` (`codigoMesa`, `estado`) VALUES (NULL, 'cerrada')");
            $consulta->execute();
            $cantidad = $consulta->rowcount();
			if($cantidad>0){
                $data = "Se dio de alta una nueva mesa.";
                return $response->withJson($data, 200);
            }else{
                $data = "No se realizo el alta.";
                return $response->withJson($data, 400);
            }
			
		}catch(Exception $e){
			//echo "ERROR: ".$e->getCode();
			switch($e->getCode()){
				case 23000:
					$data = "Ese producto ya existe en nuestra carta.";
					return $response->withJson($data, 400);
					break;
			}
		}
	}
	
	public static function CambiarEstado($request, $response, $args){
		try{
			$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
			$estado = $args["estado"];
			$ArrayDeParametros = $request->getParsedBody();
			$mesa =$ArrayDeParametros['mesa']; //PK
			if($estado>0 && $estado<4){
				switch($estado){
					case 1:
						$estado = 'con clientes esperando pedido';
					break;
					case 2:
						$estado = 'con clientes comiendo';
					break;
					case 3:
						$estado = 'con clientes pagando';
					break;
				}
			}else{
				throw new Exception('1 => con clientes esperando pedido | 2 => con clientes comiendo | 3 => con clientes pagando');
			}
			$consulta =$objetoAccesoDato->RetornarConsulta("UPDATE mesas SET estado = '$estado' WHERE codigoMesa=$mesa");
            $consulta->execute();
            $cantidad = $consulta->rowcount();
			if($cantidad>0){
                $data = "Se cambio el estado a: ". $estado;
                return $response->withJson($data, 200);
            }else{
                $data = "No existe la mesa.";
                return $response->withJson($data, 400);
            }
			
		}catch(Exception $e){
			return $response->withJson($e->getMessage(), 400);
		}
	}

	public static function Cerrar($request, $response, $args){
		try{
			$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
			$ArrayDeParametros = $request->getParsedBody();
			$mesa =$ArrayDeParametros['mesa']; //PK
			$consulta =$objetoAccesoDato->RetornarConsulta("UPDATE mesas SET estado = 'cerrada' WHERE codigoMesa=$mesa");
            $consulta->execute();
            $cantidad = $consulta->rowcount();
			if($cantidad>0){
                $data = "Se cerró la mesa ". $mesa;
                return $response->withJson($data, 200);
            }else{
                $data = "No existe la mesa o ya fue cerrada.";
                return $response->withJson($data, 400);
            }
			
		}catch(Exception $e){
			return $response->withJson($e->getMessage(), 400);
		}
	}

}