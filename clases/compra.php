<?php

class Compra{
    public $marca;
    public $modelo;
    public $fecha;
    public $precio;

    public static function TraerCompras($request, $response, $args){
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta =$objetoAccesoDato->RetornarConsulta("select * from compras");
		$consulta->execute();
		return $response->withJson($consulta->fetchAll(PDO::FETCH_CLASS, "Compra"));
    }

    public static function Alta($request, $response, $args){
        try{
            $compra = $request->getParsedBody();
            $email = $compra['email'];
            //---------------------------------
            $marca = $compra['marca'];
            $modelo = $compra['modelo'];
            $precio = $compra['precio'];
            $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		    $consulta =$objetoAccesoDato->RetornarConsulta("insert into compras (email, marca, modelo, precio, fecha) values ('$email', '$marca', '$modelo',$precio, NOW())");
            $consulta->execute();
            
            //Modificacion
            $id = $objetoAccesoDato->RetornarUltimoIdInsertado();
            $archivos = $request->getUploadedFiles();
            $destino="./IMG_Clientes/";
            if(!file_exists($destino)){
                mkdir ($destino, 0777, true);
            }
            $nombreAnterior=$archivos['foto']->getClientFilename();
            $extension= explode(".", $nombreAnterior)  ;
            $extension=array_reverse($extension);
        
            $archivos['foto']->moveTo($destino.$id."_".$marca.".".$extension[0]);
            return $response->withJson("Articulo ingresado", 200);
            
        }catch(Exception $e){
            echo $e->getMessage();
        }
    }
}