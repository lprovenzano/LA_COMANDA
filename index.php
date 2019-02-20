<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;


require 'composer/vendor/autoload.php';
require_once './clases/AccesoDatos.php';
require_once './clases/AutentificadorJWT.php';
require_once './clases/MWparaCORS.php';
require_once './clases/MWparaAutentificar.php';
require_once './clases/MWparaCompras.php';
require_once './clases/MWPedidosPendientes.php';
require_once './clases/MWLog.php';
require_once './clases/MWMarca.php';
require_once './clases/MWMozo.php';
require_once './clases/carta.php';
require_once './clases/empleado.php';
require_once './clases/mesa.php';
require_once './clases/pedido.php';
require_once './clases/encuesta.php';
require_once './clases/admin.php';

$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;


$app = new \Slim\App(["settings" => $config]);

$app->group('/login', function(){
  $this->post('[/]', \Empleado::class . ':Identificar')->add(\MWparaCORS::class . ':HabilitarCORS8080');
})->add(\MWLog::class . ':GenerarLog');

$app->group('/empleado', function(){
  //-----------------------------------------------------------------------------------------------------------
  //        ABM Empleado
  //------------------------------------------------------------------------------------------------------------
  
  //Sin token
  $this->post('[/]', \Empleado::class . ':Alta');

  //Con Token y Admin
  $this->get('[/]', \Empleado::class . ':TraerTodos')->add(\MWparaAutentificar::class . ':VerificarUsuario');
  $this->delete('/{usuario}', \Empleado::class . ':Borrar')->add(\MWparaAutentificar::class . ':VerificarUsuario');
  $this->put('[/]', \Empleado::class . ':Modificar')->add(\MWparaAutentificar::class . ':VerificarUsuario');

  $this->post('/suspender/{usuario}', \Empleado::class . ':Suspender')->add(\MWparaAutentificar::class . ':VerificarUsuario');
  $this->post('/reincorporar/{usuario}', \Empleado::class . ':Reincorporar')->add(\MWparaAutentificar::class . ':VerificarUsuario');

})->add(\MWLog::class . ':GenerarLog')->add(\MWparaCORS::class . ':HabilitarCORS8080');;


$app->group('/carta', function(){

  //-----------------------------------------------------------------------------------------------------------
  //        ABM Carta
  //------------------------------------------------------------------------------------------------------------
  
  //Sin token
  $this->get('[/]', \Carta::class . ':VerCartaCompleta');

  //Con Token y Admin
  $this->post('[/]', \Carta::class . ':Alta')->add(\MWparaAutentificar::class . ':VerificarUsuario');
  $this->delete('/{nombre}', \Carta::class . ':Borrar')->add(\MWparaAutentificar::class . ':VerificarUsuario');
  $this->put('[/]', \Carta::class . ':Modificar')->add(\MWparaAutentificar::class . ':VerificarUsuario');
})->add(\MWLog::class . ':GenerarLog');


$app->group('/mesas', function(){
  
  $this->get('[/]', \Mesa::class . ':VerMesas')->add(\MWparaAutentificar::class . ':VerificarUsuario');
  $this->post('[/]', \Mesa::class . ':Alta')->add(\MWparaAutentificar::class . ':VerificarUsuario');
  $this->put('/estado/{estado}', \Mesa::class . ':CambiarEstado')->add(\MWMozo::class . ':VerificarUsuario');
  $this->put('/cerrar[/]', \Mesa::class . ':Cerrar')->add(\MWparaAutentificar::class . ':VerificarUsuario');

})->add(\MWLog::class . ':GenerarLog')->add(\MWparaCORS::class . ':HabilitarCORS8080');

$app->group('/pedidos', function(){
  
  $this->get('[/]', \Pedido::class . ':VerPedidos')->add(\MWparaAutentificar::class . ':VerificarUsuario');
  $this->get('/pendientes[/]', \Pedido::class . ':VerPendientes')->add(\MWPedidosPendientes::class . ':VerificarEncargado');
  $this->post('[/]', \Pedido::class . ':Alta')->add(\MWMozo::class . ':VerificarUsuario');
  $this->get('/mipedido/{codigo}', \Pedido::class . ':MiPedido');

  $this->put('/preparar/{id}[/]', \Pedido::class . ':PrepararPedido')->add(\MWPedidosPendientes::class . ':VerificarEncargado');
  $this->put('/finalizar/{id}[/]', \Pedido::class . ':FinalizarPedido')->add(\MWPedidosPendientes::class . ':VerificarEncargado');

  $this->get('/listos[/]', \Pedido::class . ':PedidosListos')->add(\MWMozo::class . ':VerificarUsuario');

})->add(\MWLog::class . ':GenerarLog')->add(\MWparaCORS::class . ':HabilitarCORS8080');

$app->group('/encuestas', function(){
  $this->post('[/]', \Encuesta::class . ':Alta');
})->add(\MWLog::class . ':GenerarLog')->add(\MWparaCORS::class . ':HabilitarCORS8080');

$app->group('/admin', function(){
  //Empleados
  $this->get('/empleados/registroHoras[/]', \Admin::class . ':RegistroHoras');
  $this->get('/empleados/operacionesSector[/]', \Admin::class . ':OperacionesSector');
  $this->get('/empleados/operacionesSectorYEmpleado[/]', \Admin::class . ':OperacionesSectorPorEmpleado');
  $this->get('/empleados/operacionesPorEmpleado[/]', \Admin::class . ':OperacionesPorEmpleado');

  $this->get('/pedidos/masVendido[/]', \Admin::class . ':PedidoMasVendido');
  $this->get('/pedidos/menosVendido[/]', \Admin::class . ':PedidoMenosVendido');
  $this->get('/pedidos/fueraDeTiempo[/]', \Admin::class . ':PedidosFueraDeTiempo');

  $this->get('/mesas/masUsada[/]', \Admin::class . ':MesaMasUsada');
  $this->get('/mesas/menosUsada[/]', \Admin::class . ':MesaMenosUsada');
  $this->get('/mesas/masFacturacion[/]', \Admin::class . ':MesaQueMasFacturo');
  $this->get('/mesas/menosFacturacion[/]', \Admin::class . ':MesaQueMenosFacturo');
  $this->get('/mesas/mayorImporte[/]', \Admin::class . ':MesaConMayorImporte');
  $this->get('/mesas/menorImporte[/]', \Admin::class . ':MesaConMenorImporte');
  $this->get('/mesas/facturacion[/]', \Admin::class . ':FacturacionPeriodo');

  $this->get('/encuestas/mejores[/]', \Admin::class . ':MejoresComentarios');
  $this->get('/encuestas/peores[/]', \Admin::class . ':PeoresComentarios');

  $this->post('/archivos/excel/{tabla}[/]', \Admin::class . ':ExportarExcel' );
  $this->post('/archivos/pdf/{tabla}[/]', \Admin::class . ':ExportarPDF' );

  $this->get('/facturacion[/]', \Admin::class . ':FacturacionGeneral' );
  $this->get('/comentarios[/]', \Admin::class . ':ComentariosEncuestas' );

})->add(\MWparaAutentificar::class . ':VerificarUsuario')->add(\MWLog::class . ':GenerarLog')->add(\MWparaCORS::class . ':HabilitarCORS8080');

$app->run();