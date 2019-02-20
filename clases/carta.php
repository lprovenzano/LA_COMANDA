<?php

class Carta
{
    public $nombre;
    public $descripcion;
    public $precio;

    public static function VerCartaCompleta($request, $response, $args){
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		//$consulta =$objetoAccesoDato->RetornarConsulta("SELECT c.idProducto, c.nombre, c.descripcion, o.descripcion as encargado, c.precio from carta c INNER JOIN ocupaciones o ON c.encargado = o.ocupacion");
		$consulta =$objetoAccesoDato->RetornarConsulta("SELECT nombre, descripcion, precio from carta order by encargado desc");
		$consulta->execute();
		return $response->withJson($consulta->fetchAll(PDO::FETCH_CLASS, "Carta"));
    }

    public static function Alta($request, $response, $args){
		try{
			$ArrayDeParametros = $request->getParsedBody();

			$nombre= $ArrayDeParametros['nombre']; //PK
			$descripcion= $ArrayDeParametros['descripcion'];
            $encargado= $ArrayDeParametros['encargado'];
            $precio= $ArrayDeParametros['precio'];

			if($encargado<2 || $encargado>4){
                $data = [
                    "error" => "El encargado asignado no existe.",
                    "Parametros" => [
                        "bartender" => 2,
                        "cervecero"=> 3,
                        "cocinero" => 4
                    ]
                ];
				return $response->withJson($data, 400);
			}
			$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
			$consulta =$objetoAccesoDato->RetornarConsulta("INSERT INTO `carta` (`idProducto`, `nombre`, `descripcion`, `encargado`, `precio`) VALUES (NULL,'$nombre', '$descripcion', '$encargado', '$precio')");
			$consulta->execute();
			$data = "Se dio de alta con éxito!!!";
			return $response->withJson($data, 200);
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
    
    public static function Borrar($request, $response, $args){
		try{
			$nombre=$args['nombre'];
			$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
			$consulta = $objetoAccesoDato->RetornarConsulta("DELETE FROM carta WHERE nombre ='$nombre'");
			$consulta->execute();
			$cantidad = $consulta->rowCount();
			if($cantidad>0){
				$data = "Se borró $nombre de la carta.";
				return $response->withJson($data, 200);
			}else{
				$data = "No existe el producto en la carta";
				return $response->withJson($data, 404);
			}
		}catch(Exception $e){
			//echo "CODE: ".$e->getCode();
			echo $e->getMessage(), "\n"."<hr>";
			echo "CODE: ".$e->getCode();
		}

    }
    
    public static function Modificar($request, $response, $args){
		try{
			$ArrayDeParametros = $request->getParsedBody();
			$nombre = $ArrayDeParametros['nombre'];
            $descripcionNueva = $ArrayDeParametros['descripcion']; 
            $encargadoNuevo = $ArrayDeParametros['encargado']; 
            $precioNuevo = $ArrayDeParametros['precio']; 

			$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
			$consulta = $objetoAccesoDato->RetornarConsulta("UPDATE `carta` SET `descripcion`='$descripcionNueva', `encargado`='$encargadoNuevo', `precio`='$precioNuevo' WHERE `nombre` ='$nombre'");
			$consulta->execute();
			$cantidad = $consulta->rowCount();
			if($cantidad>0){
				$data ="Se modificó el producto.";
				return $response->withJson($data, 200);
			}else{
				$data ="No se modifico. Verifique que los datos sean distintos a los actuales.";
				return $response->withJson($data, 400);
			}
		}catch(Exception $e){
			//echo "CODE: ".$e->getCode();
			echo $e->getMessage(), "\n"."<hr>";
			echo "CODE: ".$e->getCode();
		}

	}
}
