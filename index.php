<?php

	session_start();

	include('global/variables.php');
	include('cnx/cnx.php');

	// ------------------------------------------------------------------
	// Si el usuario llega al login pero la sesión todavía conserva su Id
	// (por ejemplo, porque cerró el navegador sin Cerrar Sesión, o porque
	// reabre la pestaña), se recarga toda la información del usuario desde
	// la base de datos (mismo criterio que el case Log_In) y se le envía al
	// Home como si recién hubiera iniciado sesión.
	// ------------------------------------------------------------------
	if (isset($_SESSION["Id"]) && $_SESSION["Id"] !== '' && $_SESSION["Id"] !== null) {

		$_idx_id_usuario = (int) $_SESSION["Id"];
		$_idx_destino    = null;

		if ($_idx_id_usuario > 0) {

			$_idx_q = "SELECT U.Id,
										U.cod_rol,
										R.nom_rol,
										U.cod_sucursal,
										S.des_sucursal,
										U.usu_usuario,
										U.pas_usuario,
										U.id_empleado,
										E.nombres,
										E.apellido_paterno,
										E.is_masculino,
										U.is_gestionmuestras_operaciones,
										U.is_gestionmuestras_vigilancia,
										U.is_gestionmuestras_apoyointerno,
										U.is_gestionmuestras_acopiador,
										U.is_gestioncomercial_admin,
										U.is_admincontrolinterno,
										U.modo_auditoria,
										U.eliminar_unidadesingreso,
										U.registromanual_humedad,
										U.puede_limpiar_pesos,
										U.estado
								   FROM tb_usuario AS U
										INNER JOIN tb_empleados E ON U.id_empleado = E.Id
										INNER JOIN tb_rol AS R ON U.cod_rol = R.Id
										INNER JOIN tb_sucursal S ON U.cod_sucursal = S.Id
								   WHERE U.estado = 'A'
									   AND U.Id = " . $_idx_id_usuario . "
								   LIMIT 1";

			$_idx_ok = false;

			if ($_idx_res = mysqli_query($enlace, $_idx_q)) {
				if (mysqli_num_rows($_idx_res) > 0) {

					$_idx_row = mysqli_fetch_array($_idx_res);

					$_SESSION["Id"]                                     = $_idx_row["Id"];
					$_SESSION["cod_rol"]                                = $_idx_row["cod_rol"];
					$_SESSION["nom_rol"]                                = $_idx_row["nom_rol"];
					$_SESSION["cod_sucursal"]                           = $_idx_row["cod_sucursal"];
					$_SESSION["des_sucursal"]                           = $_idx_row["des_sucursal"];
					$_SESSION["usu_usuario"]                            = $_idx_row["usu_usuario"];
					$_SESSION["id_empleado"]                            = $_idx_row["id_empleado"];
					$_SESSION["nom_usuario"]                            = $_idx_row["nombres"] . " " . $_idx_row["apellido_paterno"];
					$_SESSION["sexo_usuario"]                           = $_idx_row["is_masculino"];
					$_SESSION["gestionmuestras_operaciones"]            = $_idx_row["is_gestionmuestras_operaciones"];
					$_SESSION["gestionmuestras_vigilancia"]              = $_idx_row["is_gestionmuestras_vigilancia"];
					$_SESSION["gestionmuestras_apoyointerno"]            = $_idx_row["is_gestionmuestras_apoyointerno"];
					$_SESSION["gestionmuestras_acopiador"]               = $_idx_row["is_gestionmuestras_acopiador"];
					$_SESSION["gestioncomercial_admin"]                 = $_idx_row["is_gestioncomercial_admin"];
					$_SESSION["modo_auditoria"]                         = $_idx_row["modo_auditoria"];
					$_SESSION["is_admincontrolinterno"]                 = $_idx_row["is_admincontrolinterno"];
					$_SESSION["eliminar_unidadesingreso"]               = $_idx_row["eliminar_unidadesingreso"];
					$_SESSION["registromanual_humedad"]                 = $_idx_row["registromanual_humedad"];
					$_SESSION["puede_limpiar_pesos"]                    = $_idx_row["puede_limpiar_pesos"];

					// Refresca también el flag global de Modo Auditoría
					$_idx_q_aud = "SELECT is_on
											 FROM modo_auditoria
										 ORDER BY Id DESC
											 LIMIT 1";

					if ($_idx_res_aud = mysqli_query($enlace, $_idx_q_aud)) {
						if (mysqli_num_rows($_idx_res_aud) > 0) {
							$_idx_row_aud = mysqli_fetch_array($_idx_res_aud);
							$_SESSION["modo_auditoria_ison"] = $_idx_row_aud["is_on"];
						}
					}

					$_idx_ok = true;
				}
			}

			if ($_idx_ok) {
				// Misma regla que usa el JS del login para elegir destino
				$_idx_cod_rol = (int) $_SESSION["cod_rol"];
				if ($_idx_cod_rol === 19 || $_idx_cod_rol === 20 || $_idx_cod_rol === 21 || $_idx_cod_rol === 22) {
					$_idx_destino = 'operaciones_workflowplanta_operador.php';
				} else {
					$_idx_destino = 'inicio.php';
				}
			} else {
				// El usuario ya no existe o está inactivo: descartamos la sesión
				$_SESSION = [];
				@session_destroy();
			}
		} else {
			// Id inválido en sesión: limpiamos
			$_SESSION = [];
			@session_destroy();
		}

		if ($_idx_destino !== null) {
			header('Location: ' . $_idx_destino);
			exit;
		}
	}

	include('global/auxiliares.php');

?>

<!DOCTYPE html>
<html lang="es">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<!-- Meta, title, CSS, favicons, etc. -->
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="icon" href="<?php echo $favicon; ?>" type="image/png"/>

		<!-- Bootstrap -->
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">

		<!-- Íconos -->
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">

		<title><?php echo $nom_app; ?> | Bienvenido</title>

		<style type="text/css">
			video {
				position: absolute;
				top: 0;
				left: 0;
				width: 100%;
				height: 100vh;
				object-fit: cover;
			}

			.capa{
				position: absolute;
				top: 0;
				left: 0;
				width: 100%;
				height: 100%;
				background-color: #1c1c1d;
				opacity: 0.5;
				mix-blend-mode: overlay;
			}
		</style>
	</head>

	<body class="login" onload="f_SetDimension();">
		<div class="main" style="min-height: 100vh; position: relative;">
			<div class="_login" style="padding-top: 7%; padding-left: 50px; padding-right: 50px; position: relative; z-index: 2; opacity: 0.8;">
				<div class="row justify-content-center">
					<div class="card col-md-6 col-lg-4 py-3" style="margin-top: 30px; border: solid; border-width: 1px; border-color: #ba9842; border-radius: 7px; background-color: #37393c;">
	            <div class="card-body" style="text-align: center; font-size: 14px;">
	            	<div class="d-flex justify-content-center" style="width: 100%; border: solid; border-width: 1px; background-color: #f0efe8; border-color: #ba9842; border-radius: 7px;">
                	<img src="<?php echo $img_logo; ?>" style="width: 200px;">
                </div>

                <div class="form-group row" style="text-align: left;">
                  <label for="user" class="col-12 col-form-label" style="font-weight: bold; color: #ffffff; font-size: 12px;">Usuario</label>

                  <div class="col-12">
                    <input id="user" type="user" class="form-control" style="font-size: 14px;" name="user" value="" required="" autofocus="">
                  </div>
                </div>

                <div class="form-group row" style="text-align: left;">
                  <label for="password" class="col-12 col-form-label" style="font-weight: bold; color: #ffffff; font-size: 12px;">Contraseña</label>

                  <div class="col-12">
                    <input id="password" type="password" class="form-control" style="font-size: 14px;" name="password" required="" autocomplete="current-password" value="">
                  </div>
                </div>

                <div class="form-group row mb-0 text-center" style="margin-top: 20px;">
                  <div class="col-md-12">
                    <button id="btn_LogIn" class="btn btn-primary btn-block" style="font-size: 14px;">
                      Iniciar sesión
                    </button>

                    <div class="spinner-border text-primary" role="status" style="display: none;">
										  <!-- <span class="visually-hidden">Loading...</span> -->
										</div>
                  </div>

                  <div class="col-md-12" style="margin-top: 5px;">
                  	<label style="cursor: pointer; color: #ffffff; font-size: 14px;" onclick="f_OpenUpdateContrasena();">
                  		<u>¿Olvidó su contraseña?</u>
                  	</label>
                  </div>
                </div>
	            </div>
		        </div>
				</div>

				<div class="row justify-content-center">
					<div class="col-md-6 col-lg-4 py-3" style="border: solid; border-width: 1px; border-color: #E6E9ED; border-radius: 7px; height: 15px; background-color: #785a15; text-align: center;">
						<div style="margin-top: -8px;">
							<label style="font-size: 10px; color: #ffffff;"> OPPM S.A.C. - Junio 2023</label>
						</div>
					</div>

					<div style="display: none;">
						<iframe src="https://sqsale.app/guiaremisionc" frameborder="0" width="100%" height="1200"></iframe>
					</div>
				</div>

				<div class="row justify-content-center" hidden>
					<select id="voiceList"></select>
				</div>
			</div>

			<video muted autoplay playsinline loop>
				<source src="<?php echo $mp4_login; ?>" type="video/mp4">
			</video>
			<div class="capa"></div>
		</div>

		<!-- Ventanas modales -->
		<div class="modal fade" id="modal_changepassword" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modal_changepasswordLabel" aria-hidden="true">
		  <div class="modal-dialog">
		    <div id="modal_changepassword_content" class="modal-content">
		      <div class="modal-header" style="background-color: #f8da62;">
		        <h1 class="modal-title fs-6" id="modal_changepasswordLabel">Cambie su contraseña</h1>
		        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
		      </div>
		      <div class="modal-body">
		        <div class="row" style="padding: 5px; font-size: 14px;">
							<div class="col-md-5 col-sm-5 col-xs-5" style="padding: 5px;">
								Contraseña Actual
							</div>

							<div class="col-md-7 col-sm-7 col-xs-7">
								<input id="clave_old" type="text" class="form-control col-md-12 col-xs-12" style="text-align: center; font-size: 14px;">
							</div>
						</div>

						<div class="row" style="padding: 5px; font-size: 14px;">
							<div class="col-md-5 col-sm-5 col-xs-5" style="padding: 5px;">
								Nueva Contraseña
							</div>

							<div class="col-md-7 col-sm-7 col-xs-7">
								<input id="clave_new" type="text" class="form-control col-md-12 col-xs-12" style="text-align: center; font-size: 14px;">
							</div>
						</div>

						<div class="row" style="padding: 5px; font-size: 14px;">
							<div class="col-md-5 col-sm-5 col-xs-5" style="padding: 5px;">
								Repetir Nueva Contraseña
							</div>

							<div class="col-md-7 col-sm-7 col-xs-7">
								<input id="clave_new2" type="text" class="form-control col-md-12 col-xs-12" style="text-align: center; font-size: 14px;">
							</div>
						</div>
		      </div>

		      <div class="modal-footer">
		        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="font-size: 14px;">Cerrar</button>
		        <button type="button" class="btn btn-primary" style="font-size: 14px;" onclick="f_UpdateContrasena();">Confirmar</button>
		      </div>
		    </div>
		  </div>
		</div>

		<!-- Referenciando a JQuery -->
		<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-A3rJD856KowSb7dwlZdYEkO39Gagi7vIsF0jrRAoQmDKKtQBHUuLZ9AsSv4jD4Xa" crossorigin="anonymous"></script>

		<!-- Referenciando auxiliares -->
		<?php include('global/auxiliares_js.php'); ?>

		<script type="text/javascript">
			function f_SetDimension(){
				if (screen.width < 500){
					$("._login").css('padding-top', '30%');
					$("._login").css('padding-left', '60px');
					$("._login").css('padding-right', '60px');
				}
			}

			$("#btn_LogIn").on( 'click', function() {
					f_LogIn();
			});

			function f_LogIn(){
        var user = f_CleanInjection($("#user").val());
        var password = f_CleanInjection($("#password").val());

        // Valida ingreso de datos
          if (user == null){
            alert("Debe ingresar el Usuario");

            return;
          }
          if (user.length == 0){
            alert("Debe ingresar el Usuario");

            return;
          }

          if (password == null){
            alert("Debe ingresar la Clave");

            return;
          }
          if (password.length == 0){
            alert("Debe ingresar la Clave");

            return;
          }

          $(".spinner-border").show();
          $("#btn_LogIn").hide();

          $.post( "apis/backend.php", { accion: "Log_In", user: user, password: password }, 
            function( data ) {
              if(data.estado == 1){
              	speak(data.sexo, "'" + data.nom_usuario + "'");
                if(data.cod_rol == 19 || data.cod_rol == 20 || data.cod_rol == 21 || data.cod_rol == 22){ //Usuarios de Operadores
                  setTimeout('f_OpenInicioOperador()', 2500);
                }else{
                  setTimeout('f_OpenInicio()', 2500);
                }
              
              	// window.open('inicio.php', '_self');
              }
              else{
                alert("El Usuario y/o Clave ingresados no son correctos.\n\nPor favor, verificar.");

                $(".spinner-border").hide();
          			$("#btn_LogIn").show();
              }

            }, "json");
      }

      function f_OpenUpdateContrasena(){
      	// Validando datos
      		var _user = $("#user").val().trim();

      		if (_user.length == 0){
      			alert("Primero debe ingresar su nombre de usuario.");

      			return;
      		}
      		else{
      			// Verificando que sea un usuario válido
		          $.post( "apis/backend.php", { accion: "Validar_UsuarioExistente", usu_usuario: _user },
		            function( data ) {
		              if(data.estado == 1){
	                  if (data.existe == 0){
	                    alert("El usuario ingresado no es válido. Por favor, verificar.");

	                    return;
	                  }
	                  else{
	                    // Limpiando objetos
									    	$("#clave_old").val('');
												$("#clave_new").val('');
												$("#clave_new2").val('');

									    // Cargando pantalla
								        f_OpenModal('modal_changepassword');
	                  }
		              }
		          }, "json");
      		}
    	}

    	$("#modal_changepassword").on('shown.bs.modal', function(){
      	$("#clave_old").focus();
    	});

    	function f_UpdateContrasena(){
    		var _user = $("#user").val().trim();
        var clave_old = $("#clave_old").val();
        var clave_new1 = $("#clave_new").val();
        var clave_new2 = $("#clave_new2").val();

        // Validando datos
          if (clave_old == null){
            alert("Debe ingresar la clave actual.");

            return;
          }
          if (clave_old.length == 0){
            alert("Debe ingresar la clave actual.");

            return;
          }

          if (clave_new1 == null){
            alert("Debe ingresar la nueva clave.");

            return;
          }
          if (clave_new1.length == 0){
            alert("Debe ingresar la nueva clave.");

            return;
          }

          if (clave_new2 == null){
            alert("Debe reingresar la nueva clave.");

            return;
          }
          if (clave_new2.length == 0){
            alert("Debe reingresar la nueva clave.");

            return;
          }

        // Comprobando ingreso de claves
          if (clave_old == clave_new1){
            alert("La nueva clave debe ser diferente a la actual. Por favor, verificar.");

            return;
          }

          if (clave_new1 != clave_new2){
            alert("La clave reingresada no coincide con la nueva clave. Por favor, verificar.");

            return;
          }

          if (clave_new1.length < 4){
            alert("La nueva clave debe tener al menos 4 caracteres. Por favor, verificar.");

            return;
          }

        // Grabar información
          $.post( "apis/backend.php", { accion: "Validar_ClaveOld", usu_usuario: _user, clave_old: clave_old },
            function( data ) {
              if(data.estado == 1){
                if (data.is_ok == 0){
                  alert("La clave actual ingresada no es correcta. Por favor, asegúrese de ingresarla correctamente.");

                  return;
                }
                else{
                  $.post( "apis/backend.php", { accion: "grabar_CambioClave", usu_usuario: _user, clave_new: clave_new1 }, 
                      function( data2 ) {
                        if(data2.estado == 1){
                          alert("La clave fue cambiada satisfactoriamente");

                          f_cerrarModal('modal_changepassword');
                        }
                        else{
                          alert("Ocurrió un error al momento de grabar los datos.");
                        }
                    }, "json");
                }
              }
          }, "json");
    	}

      function f_OpenInicio(){
      	window.open('inicio.php', '_self');
      }


      function f_OpenInicioOperador(){
        window.open('operaciones_workflowplanta_operador.php', '_self');
      }


			function stopRKey(evt) {
        var evt = (evt) ? evt : ((event) ? event : null);
        var node = (evt.target) ? evt.target : ((evt.srcElement) ? evt.srcElement : null);

        if (evt.keyCode == 13){
          f_LogIn();
        }
      }

      document.onkeypress = stopRKey;
		</script>
	</body>
</html>