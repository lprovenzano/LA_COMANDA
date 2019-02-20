<?php
require_once ('./lib/fpdf.php');
require_once ('./lib/font/helveticab.php');

class Admin{
		
	public static function RegistroHoras($request, $response, $args){
			try{
				$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
				$consulta =$objetoAccesoDato->RetornarConsulta("SELECT usuario, fecha FROM log WHERE ruta='Inicio de sesion' and usuario <> 'Anonimo' ORDER BY fecha desc");
				$consulta->execute();
				return $response->withJson($consulta->fetchAll(PDO::FETCH_CLASS, "Admin"), 200);

			}catch(Exception $e){
				return $response->withJson($e->getMessage(), 400);
			}
		}

		public static function OperacionesSector($request, $response, $args){
			try{
				$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
				$params = $request->getQueryParams();
				$desde = $params['desde'];
				$hasta = $params['hasta'];
				if(empty($desde) || empty($hasta) || !isset($hasta) || !isset($desde)){
					$consulta =$objetoAccesoDato->RetornarConsulta("select u918560597_cmd.ocupaciones.descripcion as sector, count(idPedidoDetalle) as operaciones from u918560597_cmd.pedidosdetalle as pd inner join u918560597_cmd.ocupaciones on u918560597_cmd.ocupaciones.ocupacion = pd.encargado group by descripcion");
					$consulta->execute();
					$data = $consulta->fetchAll(PDO::FETCH_ASSOC);
					$consulta2 =$objetoAccesoDato->RetornarConsulta("select 'mozo' as sector, count(idPedido) as operaciones from pedidos");
					$consulta2->execute();
					array_push($data, $consulta2->fetch(PDO::FETCH_ASSOC));

				}else{
					$desde = $desde . ' 00:00:01';
					$hasta = $hasta . ' 23:59:59';

					$consulta =$objetoAccesoDato->RetornarConsulta("select u918560597_cmd.ocupaciones.descripcion as sector, count(idPedidoDetalle) as operaciones from u918560597_cmd.pedidosdetalle as pd inner join u918560597_cmd.ocupaciones on u918560597_cmd.ocupaciones.ocupacion = pd.encargado where horaInicio BETWEEN '$desde' AND '$hasta' group by descripcion");
					$consulta->execute();
					$data = $consulta->fetchAll(PDO::FETCH_ASSOC);
					$consulta2 =$objetoAccesoDato->RetornarConsulta("select 'mozo' as sector, count(idPedido) as operaciones from pedidos where horaInicio BETWEEN '$desde' AND '$hasta'");
					$consulta2->execute();
					array_push($data, $consulta2->fetch(PDO::FETCH_ASSOC));
				}
				return $response->withJson($data, 200);
			}catch(Exception $e){
				return $response->withJson($e->getMessage(), 400);
			}

		}

		public static function OperacionesSectorPorEmpleado($request, $response, $args){
			try{
				$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
				$params = $request->getQueryParams();
				$desde = $params['desde'];
				$hasta = $params['hasta'];
				if(empty($desde) || empty($hasta) || !isset($hasta) || !isset($desde)){
					$consulta =$objetoAccesoDato->RetornarConsulta("select u918560597_cmd.ocupaciones.descripcion as sector, empleado,count(idPedidoDetalle) as operaciones from u918560597_cmd.pedidosdetalle as pd inner join u918560597_cmd.ocupaciones on u918560597_cmd.ocupaciones.ocupacion = pd.encargado group by empleado");
					$consulta->execute();
					$data = $consulta->fetchAll(PDO::FETCH_ASSOC);
					$consulta2 =$objetoAccesoDato->RetornarConsulta("select 'mozo' as sector, mozo,count(idPedido) as operaciones from pedidos group by mozo");
					$consulta2->execute();
					array_push($data, $consulta2->fetchAll(PDO::FETCH_ASSOC));

				}else{
					$desde = $desde . ' 00:00:01';
					$hasta = $hasta . ' 23:59:59';

					$consulta =$objetoAccesoDato->RetornarConsulta("select u918560597_cmd.ocupaciones.descripcion as sector, empleado,count(idPedidoDetalle) as operaciones from u918560597_cmd.pedidosdetalle as pd inner join u918560597_cmd.ocupaciones on u918560597_cmd.ocupaciones.ocupacion = pd.encargado where horaInicio BETWEEN '$desde' AND '$hasta' group by empleado");
					$consulta->execute();
					$data = $consulta->fetchAll(PDO::FETCH_ASSOC);
					$consulta2 =$objetoAccesoDato->RetornarConsulta("select 'mozo' as sector, mozo, count(idPedido) as operaciones from pedidos where horaInicio BETWEEN '$desde' AND '$hasta' group by mozo");
					$consulta2->execute();
					array_push($data, $consulta2->fetchAll(PDO::FETCH_ASSOC));
				}
				return $response->withJson($data, 200);
	
			}catch(Exception $e){
				return $response->withJson($e->getMessage(), 400);
			}

		}

		public static function OperacionesPorEmpleado($request, $response, $args){
			try{
				$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
				$params = $request->getQueryParams();
				$empleado = $params['empleado'];
				$desde = $params['desde'];
				$hasta = $params['hasta'];
				if(empty($desde) || empty($hasta) || !isset($hasta) || !isset($desde)){
					$consulta =$objetoAccesoDato->RetornarConsulta("CALL OperacionesEmpleado('$empleado');");
					$consulta->execute();
					$data = $consulta->fetchAll(PDO::FETCH_ASSOC);
				}else{
					$desde = $desde . ' 00:00:01';
					$hasta = $hasta . ' 23:59:59';

					$consulta =$objetoAccesoDato->RetornarConsulta("CALL OperacionesEmpleadoFecha('$empleado', '$desde', '$hasta');");
					$consulta->execute();
					$data = $consulta->fetchAll(PDO::FETCH_ASSOC);
				}
				return $response->withJson($data[0], 200);
	
			}catch(Exception $e){
				return $response->withJson($e->getMessage(), 400);
			}

		}

		public static function PedidoMasVendido($request, $response, $args){
			try{
				$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
				$params = $request->getQueryParams();
				$desde = $params['desde'];
				$hasta = $params['hasta'];
				if(empty($desde) || empty($hasta) || !isset($hasta) || !isset($desde)){
					$consulta =$objetoAccesoDato->RetornarConsulta("select producto, sum(cantidad) as cantidad_ventas from pedidosdetalle group by producto order by cantidad_ventas desc");
					$consulta->execute();
					$data = $consulta->fetchAll(PDO::FETCH_ASSOC);
				}else{
					$desde = $desde . ' 00:00:01';
					$hasta = $hasta . ' 23:59:59';

					$consulta =$objetoAccesoDato->RetornarConsulta("select producto, sum(cantidad) as cantidad_ventas from pedidosdetalle where horaInicio between '$desde' and '$hasta' group by producto order by cantidad_ventas desc;");
					$consulta->execute();
					$data = $consulta->fetchAll(PDO::FETCH_ASSOC);
				}
				return $response->withJson($data[0], 200);
	
			}catch(Exception $e){
				return $response->withJson($e->getMessage(), 400);
			}
		}

		public static function PedidoMenosVendido($request, $response, $args){
			try{
				$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
				$params = $request->getQueryParams();
				$desde = $params['desde'];
				$hasta = $params['hasta'];
				if(empty($desde) || empty($hasta) || !isset($hasta) || !isset($desde)){
					$consulta =$objetoAccesoDato->RetornarConsulta("select producto, sum(cantidad) as cantidad_ventas from pedidosdetalle group by producto order by cantidad_ventas asc");
					$consulta->execute();
					$data = $consulta->fetchAll(PDO::FETCH_ASSOC);
				}else{
					$desde = $desde . ' 00:00:01';
					$hasta = $hasta . ' 23:59:59';

					$consulta =$objetoAccesoDato->RetornarConsulta("select producto, sum(cantidad) as cantidad_ventas from pedidosdetalle where horaInicio between '$desde' and '$hasta' group by producto order by cantidad_ventas asc;");
					$consulta->execute();
					$data = $consulta->fetchAll(PDO::FETCH_ASSOC);
				}
				return $response->withJson($data[0], 200);
	
			}catch(Exception $e){
				return $response->withJson($e->getMessage(), 400);
			}
		}

		public static function PedidosFueraDeTiempo($request, $response, $args){
			try{
				$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
				$params = $request->getQueryParams();
				$desde = $params['desde'];
				$hasta = $params['hasta'];
				if(empty($desde) || empty($hasta) || !isset($hasta) || !isset($desde)){
					$consulta =$objetoAccesoDato->RetornarConsulta("select * from pedidos where horaFin>HoraFinEstimada");
					$consulta->execute();
					$data = $consulta->fetchAll(PDO::FETCH_ASSOC);
				}else{
					$desde = $desde . ' 00:00:01';
					$hasta = $hasta . ' 23:59:59';

					$consulta =$objetoAccesoDato->RetornarConsulta("select * from pedidos where horaFin>HoraFinEstimada and horaInicio between '$desde' and '$hasta'");
					$consulta->execute();
					$data = $consulta->fetchAll(PDO::FETCH_ASSOC);
				}
				return $response->withJson($data, 200);
	
			}catch(Exception $e){
				return $response->withJson($e->getMessage(), 400);
			}
		}

		public static function MesaMasUsada($request, $response, $args){
			try{
				$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
				$params = $request->getQueryParams();
				$desde = $params['desde'];
				$hasta = $params['hasta'];
				if(empty($desde) || empty($hasta) || !isset($hasta) || !isset($desde)){
					$consulta =$objetoAccesoDato->RetornarConsulta("select mesa, count(mesa) as pedidos_realizados from pedidos group by mesa order by 2 desc limit 1");
					$consulta->execute();
					$data = $consulta->fetchAll(PDO::FETCH_ASSOC);
				}else{
					$desde = $desde . ' 00:00:01';
					$hasta = $hasta . ' 23:59:59';

					$consulta =$objetoAccesoDato->RetornarConsulta("select mesa, count(mesa) as pedidos_realizados from pedidos where horaInicio between '$desde' and '$hasta' group by mesa order by 2 desc limit 1");
					$consulta->execute();
					$data = $consulta->fetchAll(PDO::FETCH_ASSOC);
				}
				return $response->withJson($data[0], 200);
	
			}catch(Exception $e){
				return $response->withJson($e->getMessage(), 400);
			}

		}

		public static function MesaMenosUsada($request, $response, $args){
			try{
				$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
				$params = $request->getQueryParams();
				$desde = $params['desde'];
				$hasta = $params['hasta'];
				if(empty($desde) || empty($hasta) || !isset($hasta) || !isset($desde)){
					$consulta =$objetoAccesoDato->RetornarConsulta("select mesa, count(mesa) as pedidos_realizados from pedidos group by mesa order by 2 asc limit 1");
					$consulta->execute();
					$data = $consulta->fetchAll(PDO::FETCH_ASSOC);
				}else{
					$desde = $desde . ' 00:00:01';
					$hasta = $hasta . ' 23:59:59';

					$consulta =$objetoAccesoDato->RetornarConsulta("select mesa, count(mesa) as pedidos_realizados from pedidos where horaInicio between '$desde' and '$hasta' group by mesa order by 2 asc limit 1");
					$consulta->execute();
					$data = $consulta->fetchAll(PDO::FETCH_ASSOC);
				}
				return $response->withJson($data[0], 200);
	
			}catch(Exception $e){
				return $response->withJson($e->getMessage(), 400);
			}

		}

		public static function MesaQueMasFacturo($request, $response, $args){
			try{
				$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
				$params = $request->getQueryParams();
				$desde = $params['desde'];
				$hasta = $params['hasta'];
				if(empty($desde) || empty($hasta) || !isset($hasta) || !isset($desde)){
					$consulta =$objetoAccesoDato->RetornarConsulta("select mesa, sum(total) as facturacion from pedidos group by mesa order by 2 desc limit 1");
					$consulta->execute();
					$data = $consulta->fetchAll(PDO::FETCH_ASSOC);
				}else{
					$desde = $desde . ' 00:00:01';
					$hasta = $hasta . ' 23:59:59';

					$consulta =$objetoAccesoDato->RetornarConsulta("select mesa, sum(total) as facturacion from pedidos where horaInicio between '$desde' and '$hasta' group by mesa order by 2 desc limit 1");
					$consulta->execute();
					$data = $consulta->fetchAll(PDO::FETCH_ASSOC);
				}
				return $response->withJson($data, 200);
	
			}catch(Exception $e){
				return $response->withJson($e->getMessage(), 400);
			}

		}

		public static function MesaQueMenosFacturo($request, $response, $args){
			try{
				$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
				$params = $request->getQueryParams();
				$desde = $params['desde'];
				$hasta = $params['hasta'];
				if(empty($desde) || empty($hasta) || !isset($hasta) || !isset($desde)){
					$consulta =$objetoAccesoDato->RetornarConsulta("select mesa, sum(total) as facturacion from pedidos group by mesa order by 2 asc limit 1");
					$consulta->execute();
					$data = $consulta->fetchAll(PDO::FETCH_ASSOC);
				}else{
					$desde = $desde . ' 00:00:01';
					$hasta = $hasta . ' 23:59:59';

					$consulta =$objetoAccesoDato->RetornarConsulta("select mesa, sum(total) as facturacion from pedidos where horaInicio between '$desde' and '$hasta' group by mesa order by 2 asc limit 1");
					$consulta->execute();
					$data = $consulta->fetchAll(PDO::FETCH_ASSOC);
				}
				return $response->withJson($data, 200);
	
			}catch(Exception $e){
				return $response->withJson($e->getMessage(), 400);
			}

		}

		public static function MesaConMayorImporte($request, $response, $args){
			try{
				$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
				$params = $request->getQueryParams();
				$desde = $params['desde'];
				$hasta = $params['hasta'];
				if(empty($desde) || empty($hasta) || !isset($hasta) || !isset($desde)){
					$consulta =$objetoAccesoDato->RetornarConsulta("select mesa, total from pedidos order by total desc");
					$consulta->execute();
					$data = $consulta->fetchAll(PDO::FETCH_ASSOC);
				}else{
					$desde = $desde . ' 00:00:01';
					$hasta = $hasta . ' 23:59:59';

					$consulta =$objetoAccesoDato->RetornarConsulta("select mesa, total from pedidos where horaInicio between '$desde' and '$hasta' order by total desc");
					$consulta->execute();
					$data = $consulta->fetchAll(PDO::FETCH_ASSOC);
				}
				return $response->withJson($data[0], 200);
	
			}catch(Exception $e){
				return $response->withJson($e->getMessage(), 400);
			}

		}

		public static function MesaConMenorImporte($request, $response, $args){
			try{
				$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
				$params = $request->getQueryParams();
				$desde = $params['desde'];
				$hasta = $params['hasta'];
				if(empty($desde) || empty($hasta) || !isset($hasta) || !isset($desde)){
					$consulta =$objetoAccesoDato->RetornarConsulta("select mesa, total from pedidos order by total asc");
					$consulta->execute();
					$data = $consulta->fetchAll(PDO::FETCH_ASSOC);
				}else{
					$desde = $desde . ' 00:00:01';
					$hasta = $hasta . ' 23:59:59';

					$consulta =$objetoAccesoDato->RetornarConsulta("select mesa, total from pedidos where horaInicio between '$desde' and '$hasta' order by total asc");
					$consulta->execute();
					$data = $consulta->fetchAll(PDO::FETCH_ASSOC);
				}
				return $response->withJson($data[0], 200);
			}catch(Exception $e){
				return $response->withJson($e->getMessage(), 400);
			}
		}

		public static function FacturacionPeriodo($request, $response, $args){
			try{
				$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
				$params = $request->getQueryParams();
				$mesa = $params['mesa'];
				$desde = $params['desde'];
				$hasta = $params['hasta'];
				if(empty($desde) || empty($hasta) || !isset($hasta) || !isset($desde)){
					$consulta =$objetoAccesoDato->RetornarConsulta("select sum(total) as facturacion from pedidos where mesa='$mesa'");
					$consulta->execute();
					$data = $consulta->fetchAll(PDO::FETCH_ASSOC);
				}else{
					$desde = $desde . ' 00:00:01';
					$hasta = $hasta . ' 23:59:59';

					$consulta =$objetoAccesoDato->RetornarConsulta("select sum(total) as facturacion from pedidos where mesa='$mesa'and horaInicio between '$desde' and '$hasta'");
					$consulta->execute();
					$data = $consulta->fetchAll(PDO::FETCH_ASSOC);
				}
				return $response->withJson($data[0], 200);
			}catch(Exception $e){
				return $response->withJson($e->getMessage(), 400);
			}
		}

		public static function MejoresComentarios($request, $response, $args){
			try{
				$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
				$params = $request->getQueryParams();
				$desde = $params['desde'];
				$hasta = $params['hasta'];
				if(empty($desde) || empty($hasta) || !isset($hasta) || !isset($desde)){
					$consulta =$objetoAccesoDato->RetornarConsulta("select * from encuesta where promedio>8 order by promedio desc limit 3");
					$consulta->execute();
					$data = $consulta->fetchAll(PDO::FETCH_ASSOC);
				}else{
					$desde = $desde . ' 00:00:01';
					$hasta = $hasta . ' 23:59:59';

					$consulta =$objetoAccesoDato->RetornarConsulta("select * from encuesta where promedio>8 and fecha between '$desde' and '$hasta' order by promedio desc limit 3");
					$consulta->execute();
					$data = $consulta->fetchAll(PDO::FETCH_ASSOC);
				}
				return $response->withJson($data, 200);
			}catch(Exception $e){
				return $response->withJson($e->getMessage(), 400);
			}
		}

		public static function PeoresComentarios($request, $response, $args){
			try{
				$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
				$params = $request->getQueryParams();
				$desde = $params['desde'];
				$hasta = $params['hasta'];
				if(empty($desde) || empty($hasta) || !isset($hasta) || !isset($desde)){
					$consulta =$objetoAccesoDato->RetornarConsulta("select * from encuesta where promedio<5 order by promedio desc limit 3");
					$consulta->execute();
					$data = $consulta->fetchAll(PDO::FETCH_ASSOC);
				}else{
					$desde = $desde . ' 00:00:01';
					$hasta = $hasta . ' 23:59:59';

					$consulta =$objetoAccesoDato->RetornarConsulta("select * from encuesta where promedio<5 and fecha between '$desde' and '$hasta' order by promedio desc limit 3");
					$consulta->execute();
					$data = $consulta->fetchAll(PDO::FETCH_ASSOC);
				}
				return $response->withJson($data, 200);
			}catch(Exception $e){
				return $response->withJson($e->getMessage(), 400);
			}
		}

		public static function ExportarExcel($request, $response, $args){
				try{
					$tabla = $args['tabla'];
					$destino = './Excel/';
					$horaMinuto = date('H-i');
					$filename = 'Reporte_'.date("Y-m-d").'_'.$horaMinuto.'_'.$tabla.'.csv';
					if(!empty($tabla) && isset($tabla)){

						if(!file_exists($destino)){
							mkdir ($destino, 0777, true);
						}

						$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
						$consulta =$objetoAccesoDato->RetornarConsulta("SELECT * FROM $tabla");
						$consulta->execute();
						$dataTable = $consulta->fetchAll(PDO::FETCH_ASSOC);
						
						$stream = fopen($destino.$filename, 'w+');
						$headers = array_keys($dataTable[0]);
						fwrite($stream, chr(0xEF) . chr(0xBB) . chr(0xBF));
						//Header
						fputcsv($stream, $headers, ';');
						foreach($dataTable as $item){
							fputcsv($stream, $item, ';');
						}
            rewind($stream);
						
            $response = $response
                ->withHeader('Content-Type', 'text/csv')
                ->withHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->withHeader('Pragma', 'no-cache')
                ->withHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
								->withHeader('Expires', '0');
								
						return $response->withJson($destino.$filename, 200);

					}else{
						throw Exception('Debe ingresar una tabla para exportar.');
					}

				}catch(Exception $e){
					if($e->getCode()=='42S02'){
							return $response->withJson('No existe la tabla seleccionada', 404);
					}else{
						return $response->withJson($e->getMessage(), 400);
					}
				}
		}

		public static function ExportarPDF($request, $response, $args){
			try{
				$tabla = $args['tabla'];
				$destino = './PDF/';
				$horaMinuto = date('H-i');
				$filename = 'Reporte_'.date("Y-m-d").'_'.$horaMinuto.'_'.$tabla.'.pdf';
				if(!empty($tabla) && isset($tabla)){

					if(!file_exists($destino)){
						mkdir ($destino, 0777, true);
					}

					if($tabla == 'pedidosdetalle'){
						$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
						$consulta =$objetoAccesoDato->RetornarConsulta("SELECT `idPedido`, `producto`, `cantidad`, `estado`, `encargado`, `empleado`, `horaInicio`, `horaFin` FROM `$tabla`");
						$consulta->execute();
						//Datos
						$dataTable = $consulta->fetchAll(PDO::FETCH_ASSOC);
					}else if($tabla == 'pedidos'){
						$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
						$consulta =$objetoAccesoDato->RetornarConsulta("SELECT `idPedido`, `mesa`, `estadoGeneral`, `mozo`, `total`, `horaInicio`, `horaFinEstimada`, `horaFin` FROM `$tabla`");
						$consulta->execute();
						//Datos
						$dataTable = $consulta->fetchAll(PDO::FETCH_ASSOC);
					}else{
						$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
						$consulta =$objetoAccesoDato->RetornarConsulta("SELECT * FROM $tabla");
						$consulta->execute();
					//Datos
						$dataTable = $consulta->fetchAll(PDO::FETCH_ASSOC);
					}
					//header
					$headers = array_keys($dataTable[0]);
					
					$pdf = new FPDF('L');
					$pdf->AddPage();
					$pdf->SetFont('Arial','B',10);

					$pdf->Cell(40,10, 'REPORTE: '. $tabla);
					$pdf->Ln(15);
					foreach($headers as $title){
						$pdf->Cell(35,15,$title,0,0,'C');
					}
					$pdf->Ln(10);
					foreach($dataTable as $itemTable){
							foreach($itemTable as $clave=>$valor){
								//$pdf->Cell(40,10,$itemTable[$clave]);
								$pdf->Cell(35,15,$itemTable[$clave],0,0,'C');
							}
							$pdf->Ln(10);
					}
					$pdf->Output($destino.$filename, 'F');

					return $response->withJson($destino.$filename, 200);

				}else{
					throw Exception('Debe ingresar una tabla para exportar.');
				}

			}catch(Exception $e){
				if($e->getCode()=='42S02'){
						return $response->withJson('No existe la tabla seleccionada', 404);
				}else{
					return $response->withJson($e->getMessage(), 400);
				}
			}
	}

	public static function FacturacionGeneral($request, $response, $args){
		try{
			$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
			$params = $request->getQueryParams();
			$desde = $params['desde'];
			$hasta = $params['hasta'];
			if(empty($desde) || empty($hasta) || !isset($hasta) || !isset($desde)){
				$consulta =$objetoAccesoDato->RetornarConsulta("select sum(total) as facturacion from pedidos");
				$consulta->execute();
				$data = $consulta->fetchAll(PDO::FETCH_ASSOC);
			}else{
				$desde = $desde . ' 00:00:01';
				$hasta = $hasta . ' 23:59:59';

				$consulta =$objetoAccesoDato->RetornarConsulta("select sum(total) as facturacion from pedidos where horaInicio between '$desde' and '$hasta'");
				$consulta->execute();
				$data = $consulta->fetchAll(PDO::FETCH_ASSOC);
			}
			return $response->withJson($data, 200);
		}catch(Exception $e){
			return $response->withJson($e->getMessage(), 400);
		}
	}

	public static function ComentariosEncuestas($request, $response, $args){
		try{
			$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
			$params = $request->getQueryParams();
			$desde = $params['desde'];
			$hasta = $params['hasta'];
			if(empty($desde) || empty($hasta) || !isset($hasta) || !isset($desde)){
				$consulta =$objetoAccesoDato->RetornarConsulta("select * from encuesta");
				$consulta->execute();
				$data = $consulta->fetchAll(PDO::FETCH_ASSOC);
			}else{
				$desde = $desde . ' 00:00:01';
				$hasta = $hasta . ' 23:59:59';

				$consulta =$objetoAccesoDato->RetornarConsulta("select * from encuesta where fecha between '$desde' and '$hasta'");
				$consulta->execute();
				$data = $consulta->fetchAll(PDO::FETCH_ASSOC);
			}
			return $response->withJson($data, 200);
		}catch(Exception $e){
			return $response->withJson($e->getMessage(), 400);
		}
	}

}