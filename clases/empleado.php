<?php

class Empleado{
	public $usuario;
	public $clave;
	public $suspendido;
	public $ocupacion; //1- socio 2-bartender 3-cervecero  4- cocinero 5-mozo
	public $ultimoIngreso;

	private function BorrarClaveParaToken(){
		$this->clave = '';
	}

	public static function Identificar($request, $response, $args){
		$ArrayDeParametros = $request->getParsedBody();
		$usuario= $ArrayDeParametros['usuario']; //PK
		$clave= $ArrayDeParametros['clave'];
		
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta =$objetoAccesoDato->RetornarConsulta("select * from empleados where usuario='$usuario' and suspendido=0");
		$consulta->execute();
		$usuarioBuscado = $consulta->fetchObject('Empleado');

		if(!empty($usuarioBuscado)){
			if($usuarioBuscado->usuario == $usuario){
				if($usuarioBuscado->clave == $clave){
					$usuarioBuscado->BorrarClaveParaToken();
					$data = ["token"=>AutentificadorJWT::CrearToken($usuarioBuscado)];
					Empleado::EmpleadoLogueado($usuarioBuscado->usuario);
					return $response->withJson($data, 200);
				}else{
					$data = "Contraseña erronea";
					return $response->withJson($data, 400);
				}
				
			}
		}else{
			$data = "Ese empleado no existe o está suspendido.";
			return $response->withJson($data, 400);
		}

	}

	public static function Alta($request, $response, $args){
		try{
			$ArrayDeParametros = $request->getParsedBody();

			$usuario= $ArrayDeParametros['usuario']; //PK
			$clave= $ArrayDeParametros['clave'];
			$ocupacion= $ArrayDeParametros['ocupacion'];

			if(empty($ocupacion) || ($ocupacion<0 || $ocupacion>5)){
				$ocupacion=5; //Si no ingresa ocupacion o un numero, por default es mozo.
			}

			$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
			$consulta =$objetoAccesoDato->RetornarConsulta("INSERT INTO `empleados` (`usuario`, `clave`, `suspendido`, `ocupacion`, `ultimoIngreso`) VALUES ('$usuario', '$clave', '0', '$ocupacion', NULL)");
			$consulta->execute();
			$usuarioBuscado = $consulta->fetchObject('Empleado');
			$data = "Empleado dado de alta con éxito!";
			return $response->withJson($data, 200);
		}catch(Exception $e){
			switch($e->getCode()){
				case 23000:
					$data = "Ese empleado ya existe.";
					return $response->withJson($data, 400);
					break;
			}
		}
	}

	public static function Borrar($request, $response, $args){
		try{
			$usuario=$args['usuario'];
			$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
			$consulta = $objetoAccesoDato->RetornarConsulta("DELETE FROM empleados WHERE usuario ='$usuario'");
			$consulta->execute();
			$cantidad = $consulta->rowCount();
			if($cantidad>0){
				$data = "El empleado $usuario fue borrado";
				return $response->withJson($data, 200);
			}else{
				$data = "No existe el empleado.";
				return $response->withJson($data, 404);
			}
		}catch(Exception $e){
			return $response->withJson($e->getMessage(), 400);
		}

	}

	public static function Modificar($request, $response, $args){
		try{
			$ArrayDeParametros = $request->getParsedBody();
			$usuario = $ArrayDeParametros['usuario'];
			$nuevaClave = $ArrayDeParametros['clave']; 
			$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
			$consulta = $objetoAccesoDato->RetornarConsulta("UPDATE `empleados` SET `clave`='$nuevaClave' WHERE `usuario` ='$usuario'");
			$consulta->execute();
			$cantidad = $consulta->rowCount();
			if($cantidad>0){
				$data ="Se modificó la clave de $usuario";
				return $response->withJson($data, 200);
			}else{
				$data ="No se modifico. Verifique que el empleado exista.";
				return $response->withJson($data, 400);
			}
		}catch(Exception $e){
			return $response->withJson($e->getMessage(), 400);
		}

	}

	public static function Suspender($request, $response, $args){
		try{
			$usuario=$args['usuario'];
			$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
			$consulta = $objetoAccesoDato->RetornarConsulta("UPDATE `empleados` SET `suspendido`='1' WHERE `usuario` ='$usuario'");
			$consulta->execute();
			$cantidad = $consulta->rowCount();
			if($cantidad>0){
				$data = "Se suspendió a ".$usuario;
				return $response->withJson($data, 200);
			}else{
				$data = "Ya está suspendido o no existe.";
				return $response->withJson($data, 400);
			}
		}catch(Exception $e){
			return $response->withJson($e->getMessage(), 400);
		}

	}

	public static function Reincorporar($request, $response, $args){
		try{
			$usuario=$args['usuario'];
			$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
			$consulta = $objetoAccesoDato->RetornarConsulta("UPDATE `empleados` SET `suspendido`='0' WHERE `usuario` ='$usuario'");
			$consulta->execute();
			$cantidad = $consulta->rowCount();
			if($cantidad>0){
				$data = "Se reincorporó a ".$usuario;
				return $response->withJson($data, 200);
			}else{
				$data = "El usuario no está suspendido.";
				return $response->withJson($data, 400);
			}
		}catch(Exception $e){
			return $response->withJson($e->getMessage(), 500);
		}

	}

	public static function TraerTodos($request, $response, $args){
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta =$objetoAccesoDato->RetornarConsulta("select * from empleados");
		$consulta->execute();
		return $response->withJson($consulta->fetchAll(PDO::FETCH_CLASS, "Empleado"));		
	}

	public static function Log($nombre, $metodo, $ruta){
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta =$objetoAccesoDato->RetornarConsulta("insert into log values ('$nombre', '$metodo', '$ruta', CURRENT_TIMESTAMP())");
		$consulta->execute();
	}

	public static function EmpleadoLogueado($usuario){
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta =$objetoAccesoDato->RetornarConsulta("update empleados set ultimoIngreso=CURRENT_TIMESTAMP() where usuario='$usuario'");
		$consulta->execute();
		$consulta2 =$objetoAccesoDato->RetornarConsulta("INSERT INTO `log`(`usuario`, `metodo`, `ruta`, `fecha`) VALUES ('$usuario', '', 'Inicio de sesion', NOW())");
		$consulta2->execute();
	}
	
}