<?php

error_reporting(0);
ini_set('display_errors', 0);
ini_set('display_startuo_errors', 0);

// Garantiza que la sesión esté iniciada (los módulos normalmente ya llaman a session_start(),
// pero si alguien incluye este archivo antes, lo abrimos nosotros).
if (session_status() === PHP_SESSION_NONE) {
	@session_start();
}

// Determina si estamos en la pantalla de login para no exigir una sesión válida en ella.
$_aux_script_actual = isset($_SERVER['PHP_SELF']) ? basename($_SERVER['PHP_SELF']) : '';
$_aux_es_login      = ($_aux_script_actual === 'index.php');

/**
 * Devuelve true únicamente si la sesión contiene los datos mínimos de un usuario logueado.
 * Se exige que existan y que no estén vacíos: Id, cod_rol, usu_usuario, id_empleado, nom_usuario.
 */
function _aux_sesion_tiene_usuario(){
	if (!isset($_SESSION) || !is_array($_SESSION)){
		return false;
	}

	$_aux_campos_requeridos = ['Id', 'cod_rol', 'usu_usuario', 'id_empleado', 'nom_usuario'];

	foreach ($_aux_campos_requeridos as $_aux_campo){
		if (!array_key_exists($_aux_campo, $_SESSION)){
			return false;
		}

		$_aux_valor = $_SESSION[$_aux_campo];

		if (is_null($_aux_valor)){
			return false;
		}

		if (is_string($_aux_valor) && trim($_aux_valor) === ''){
			return false;
		}
	}

	return true;
}

/**
 * Limpia completamente la sesión (incluyendo cookie) y redirige al login.
 * Se usa cuando se detecta que la sesión está corrupta, manipulada o expirada.
 */
function _aux_destruir_sesion_y_redirigir(){
	$_SESSION = [];

	if (ini_get('session.use_cookies')){
		$_aux_params = session_get_cookie_params();
		setcookie(
			session_name(),
			'',
			time() - 42000,
			$_aux_params['path'],
			$_aux_params['domain'],
			$_aux_params['secure'],
			$_aux_params['httponly']
		);
	}

	@session_destroy();

	header('Location: index.php');
	exit;
}

// En todos los módulos distintos al propio login se exige una sesión válida con los
// datos mínimos del usuario. Si falta cualquiera de ellos, se descarta la sesión y
// se obliga a pasar nuevamente por la pantalla de login.
if (!$_aux_es_login && !_aux_sesion_tiene_usuario()){
	_aux_destruir_sesion_y_redirigir();
}

// Datos usados por el navbar y el modal de cierre de clientes a crédito.
if (!isset($_SESSION["nom_usuario"]) || !isset($_SESSION["des_sucursal"]) || !isset($_SESSION["modo_auditoria"])) {
	return;
}


// Navbar principal
$navbar_maintop = '<nav class="navbar" style="background-color: #ba9842; box-shadow: 0px 0px 10px #BDBFAE; padding: 2px;">
											  <div class="container-fluid" style="background-color: #37393c;">
											    <img src="' . $img_logo2 . '" style="width: 120px; padding: 5px;">
											    <div style="text-align: center; color: #ffffff; font-size: 14px; font-weight: bold;">
										    		' . $nom_app . ' <label id="nv_titulo" style="color: #FFDB17;"></label>
										    	</div>

											    <ul class="nav justify-content-end">
											    	<li class="nav-item dropdown">
										          <a class="nav-link dropdown-toggle"  role="button" data-bs-toggle="dropdown" aria-expanded="false" style="color: #ffffff; font-size: 14px;">
										            ' . $_SESSION["des_sucursal"] . ' - ' . $_SESSION['nom_usuario'] . '
										          </a>
										          <ul class="dropdown-menu">';

if ($_SESSION["modo_auditoria"] == 1) {
	if ($_SESSION["modo_auditoria_ison"] == 0) {
		$navbar_maintop .= '<li><a class="dropdown-item" style="cursor: pointer; font-weight: bold; font-size: 14px;" onclick="f_ModoAuditoria(1);">
																					          		<div class="d-flex align-items-center">
																					          			<div class="p-2" style="border: solid; border-width: 1px; border-color: #E6E9ED; border-radius: 7px; background-color: #D9D9D9;">
																					          				<img src="images/auditoria_on.png" style="width: 30px;">
																				          				</div>

																				          				<div class="p-2 flex-grow-1">
																					          				<label>Modo Auditoría</label>
																				          				</div>
																				          			</div>
																					          	</a></li>

																					          	<li><hr class="dropdown-divider"></li>';
	} else {
		$navbar_maintop .= '<li><a class="dropdown-item" style="cursor: pointer; font-weight: bold; font-size: 14px;" onclick="f_ModoAuditoria(0);">
																					          		<div class="d-flex align-items-center">
																					          			<div class="p-2" style="border: solid; border-width: 1px; border-color: #E6E9ED; border-radius: 7px; background-color: #D9D9D9;">
																					          				<img src="images/auditoria_off.png" style="width: 30px;">
																				          				</div>

																				          				<div class="p-2 flex-grow-1">
																					          				<label>Desactivar Modo Auditoría</label>
																				          				</div>
																				          			</div>
																					          	</a></li>

																					          	<li><hr class="dropdown-divider"></li>';
	}
}

$navbar_maintop .= '				<li><a class="dropdown-item" style="cursor: pointer; font-weight: bold; font-size: 14px;" onclick="f_CerrarSesion();">
										          		<div class="d-flex align-items-center">
											            	<div class="p-2" style="border: solid; border-width: 1px; border-color: #E6E9ED; border-radius: 7px; background-color: #D9D9D9;">
										          				<img src="images/exit.png" style="width: 30px; padding: 2px;">
									          				</div>

									          				<div class="p-2 flex-grow-1">
										          				<label>Cerrar Sesión</label>
									          				</div>
								          				</div>
							          				</a></li>
										          </ul>
										        </li>
											    </ul>
											  </div>
											</nav>

											<div id="tst_container" class="toast-container position-fixed top-0 end-0 p-3">
												
											</div>

											<div id="tst_visitas" class="toast-container position-fixed top-0 end-0 p-3">
												
											</div>';

$modal_clientescredito = '<div class="modal fade" id="modal_clientescredito_sendemail" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modal_clientescredito_sendemailLabel" aria-hidden="true" style="display: none;">
															  <div class="modal-dialog">
															    <div class="modal-content">
															      <div class="modal-header">
															        <h1 class="modal-title fs-5" id="modal_clientescredito_sendemailLabel">Enviar Recordatorio</h1>
															        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
															      </div>
															      <div class="modal-body">
															        <div class="row" style="padding: 5px;">
																				<div class="col-md-2 col-sm-2 col-xs-2" style="padding: 5px;">
																					Cliente:
																				</div>

																				<div class="col-md-10 col-sm-10 col-xs-10">
																					<textarea id="clientescredito_cliente" type="text" class="form-control col-md-12 col-xs-12" rows="2" disabled></textarea>
																				</div>
																			</div>

																			<div class="row" style="padding: 5px;">
																				<div class="col-md-2 col-sm-2 col-xs-2" style="padding: 5px;">
																					Correo:
																				</div>

																				<div class="col-md-10 col-sm-10 col-xs-10">
																					<input id="clientescredito_correo" type="email" class="form-control col-md-12 col-xs-12">
																				</div>
																			</div>

																			<div class="row" style="padding: 5px;">
																				<div class="col-md-2 col-sm-2 col-xs-2" style="padding: 5px;">
																					Texto:
																				</div>

																				<div class="col-md-10 col-sm-10 col-xs-10">
																					<textarea id="clientescredito_texto" type="text" class="form-control col-md-12 col-xs-12" rows="10"></textarea>
																				</div>
																			</div>
															      </div>

															      <input id="hd_idcliente" type="hidden">
															      <input id="hd_modograbar" type="hidden">

															      <div class="modal-footer">
															        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
															        <button type="button" class="btn btn-primary" onclick="f_GrabarCliente();">Enviar correo</button>
															      </div>
															    </div>
															  </div>
															</div>';

echo $modal_clientescredito;

?>