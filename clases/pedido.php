<?php
date_default_timezone_set(date_default_timezone_get());
class Pedido{

    public static function Alta($request, $response, $args){
		try{
			$ArrayDeParametros = $request->getParsedBody();
            $producto= $ArrayDeParametros['producto'];
            $id= $ArrayDeParametros['id'];
			$mesa= $ArrayDeParametros['mesa'];
			$codigoPedido = Pedido::getCodigoPedido();
			$encargadoAlta = $ArrayDeParametros['encargado'];
			$cantidad = $ArrayDeParametros['cantidad'];
			if($cantidad == 0 || empty($cantidad) || !isset($cantidad)){
				$cantidad = 1;
			}
			$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
			$consulta =$objetoAccesoDato->RetornarConsulta("CALL nuevoPedido('$id', '$mesa', '$producto', '$codigoPedido', '$encargadoAlta', '$cantidad')");
			$consulta->execute();

			if(!empty($request->getUploadedFiles())){
				//Foto
				$archivos = $request->getUploadedFiles();
				$destino="./IMG_Clientes/";
				if(!file_exists($destino)){
					mkdir ($destino, 0777, true);
				}
				$extension = "jpeg";
				if(file_exists($destino."Pedido_".$id.".".$extension)){
					$hora = date('H_m_s');
					rename($destino."Pedido_".$id.".".$extension, $destino."Pedido_".$id."_".$hora.".".$extension);
				}
				$archivos['foto']->moveTo($destino."Pedido_".$id.".".$extension);
				Pedido::EstamparFoto($destino."Pedido_".$id.".".$extension, $destino, "Pedido_".$id);
			}
			$consulta2 =$objetoAccesoDato->RetornarConsulta("SELECT (codigoPedido) from pedidos WHERE idPedido=$id");
			$consulta2->execute();
			$code = $consulta2->fetch(PDO::FETCH_ASSOC);
			$data = "El pedido fue realizado. Código de consulta: ".$code["codigoPedido"];
			return $response->withJson($data, 200);
		}catch(Exception $e){
			switch($e->getCode()){
				case 1048:
					$data = "Ese plato no existe en nuestra carta.";
					break;
				case 23000:
					$data = "Ese plato no existe en nuestra carta.";
					break;
				default:
					$data = $e->getMessage();
					break;
			}
			return $response->withJson($data, 400);
		}
	}

	public static function VerPedidos($request, $response, $args){
		try{
			$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
			$consulta =$objetoAccesoDato->RetornarConsulta("SELECT * FROM pedidos");
			$consulta->execute();
			return $response->withJson($consulta->fetchAll(PDO::FETCH_CLASS, "Pedido"), 200);

		}catch(Exception $e){
			return $response->withJson($e->getMessage(), 400);
		}

	}

	private static function getCodigoPedido(){
		$caracteres = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$codigo="";
		for($i=0;$i<5;$i++){
			$codigo .= $caracteres[rand(0, intval(strlen($caracteres)))];
		}
		return $codigo;
	}

	public static function VerPendientes($request, $response, $args){
		try{
			$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
			$ArrayDeParametros = $request->getParsedBody();
			$encargado = $ArrayDeParametros['encargado'];
			$consulta =$objetoAccesoDato->RetornarConsulta("SELECT * FROM pedidosdetalle WHERE encargado=$encargado and estado = 'pendiente' ORDER BY horaInicio");
			$consulta->execute();
			return $response->withJson($consulta->fetchAll(PDO::FETCH_CLASS, "Pedido"), 200);

		}catch(Exception $e){
			return $response->withJson($e->getMessage(), 400);
		}
	}

	public static function PrepararPedido($request, $response, $args){
		try{
			$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
			$ArrayDeParametros = $request->getParsedBody();
			$idPedidoDetalle = $args['id'];
			$encargado = $ArrayDeParametros['empleado'];
			$ocupacion = $ArrayDeParametros['encargado'];
			$tiempoEnMinutos = $ArrayDeParametros["estimacion"];
			$fecha = date('Y-m-d H:i:s');
			$nuevafecha = strtotime ( '+'.$tiempoEnMinutos.' minute' , strtotime ( $fecha ) ) ;
			$nuevafecha = date ( 'Y-m-d H:i:s' , $nuevafecha );
			if($ocupacion == Pedido::DameEmpleadoACargo($idPedidoDetalle))
			{
				$consulta =$objetoAccesoDato->RetornarConsulta("UPDATE pedidosdetalle SET empleado='$encargado', horaFinEstimada='$nuevafecha', estado='en preparacion' WHERE idPedidoDetalle=$idPedidoDetalle and estado = 'pendiente'");
				$consulta->execute();
				$consulta2 =$objetoAccesoDato->RetornarConsulta("CALL VerificarEstado($idPedidoDetalle)");
				$consulta2->execute();
				return $response->withJson("Preparando pedido!!!", 200);

			}
			return $response->withJson("Este pedido no pertenece a tu sector.", 401);
		}catch(Exception $e){
			return $response->withJson($e->getMessage(), 400);
		}
	}

	public static function FinalizarPedido($request, $response, $args){
		try{
			$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
			$ArrayDeParametros = $request->getParsedBody();
			$idPedidoDetalle = $args['id'];
			$ocupacion = $ArrayDeParametros['encargado'];
			if($ocupacion == Pedido::DameEmpleadoACargo($idPedidoDetalle))
			{
				$consulta =$objetoAccesoDato->RetornarConsulta("UPDATE pedidosdetalle SET horaFin=NOW(), estado='listo para servir' WHERE idPedidoDetalle=$idPedidoDetalle and estado = 'en preparacion'");
				$consulta->execute();
				$consulta2 =$objetoAccesoDato->RetornarConsulta("CALL VerificarEstado($idPedidoDetalle)");
				$consulta2->execute();
				return $response->withJson("Listo para servir!!!", 200);
			}
			return $response->withJson("Este pedido no pertenece a tu sector.", 401);
		}catch(Exception $e){
			return $response->withJson($e->getMessage(), 400);
		}
	}

	public static function PedidosListos($request, $response, $args){
		try{
			$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
			$ArrayDeParametros = $request->getParsedBody();
			$encargado = $ArrayDeParametros['encargado'];
			$consulta =$objetoAccesoDato->RetornarConsulta("SELECT * FROM pedidos WHERE estadoGeneral='listo para servir' and mozo='$encargado'");
			$consulta->execute();
			return $response->withJson($consulta->fetchAll(PDO::FETCH_CLASS, "Pedido"), 200);

		}catch(Exception $e){
			return $response->withJson($e->getMessage(), 400);
		}
	}

	public static function MiPedido($request, $response, $args){
		try{
			$codigo=$args['codigo'];
			$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
			$consulta =$objetoAccesoDato->RetornarConsulta("call tiempoRestante('$codigo')");
			$consulta->execute();
			$tiempo = $consulta->fetch(PDO::FETCH_ASSOC);
			
			return $response->withJson($tiempo["tiempo"], 200);
		}catch(Exception $e){
			return $response->withJson($e->getMessage(), 400);
		}

	}
	

	private static function DameEmpleadoACargo($pedido){
			$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
			$consulta =$objetoAccesoDato->RetornarConsulta("select (encargado) from pedidosdetalle where idPedidoDetalle = $pedido");
			$consulta->execute();
			$data = $consulta->fetch(PDO::FETCH_ASSOC);
			return $data["encargado"];
	}

	private static function EstamparFoto($foto, $destino, $nombre){
		$estampa = imagecreatefrompng('./lib/logo.png');
		$im = imagecreatefromjpeg($foto);

		$margen_dcho = 10;
		$margen_inf = 10;
		$sx = imagesx($estampa);
		$sy = imagesy($estampa);

		imagecopy($im, $estampa, imagesx($im) - $sx - $margen_dcho, imagesy($im) - $sy - $margen_inf, 0, 0, imagesx($estampa), imagesy($estampa));

		// Imprimir y liberar memoria
		header('Content-type: image/png');
		$filename = $destino.$nombre.'_watemark.png';
		imagepng($im, $filename);
		imagedestroy($im);
	}

}
