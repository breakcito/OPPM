Achivo que recibe todas las solicitudes de todos los modulos mendiante peticiones http POST siempre con el campo de "accion" en el body que indica que accion/caso de uso se debe ejecutar junto a los otros campos propios del caso, ese campo entra en un switch case y segun coincida entra al case:
C:\wamp64\www\oppmerp\apis\backend.php

TODO CAMBIO QUE HAGAS EN LA BD, como añadir campos a una tabla o tablas nuevas, crear un script de cambios.sql con el script en sql claro y practico a ejecutar en la base de datos de produccion para poder actualizara despues tambien.

NO HAGAS CAMBIOS EN GIT QUE AFECTEN AL ESTADO DEL REPOSITORIO O RAMA, como stash, commits, push, revert, etc.

==================================================

Resumen de balanza: C:\wamp64\www\oppmerp\resumen_balanza_prev.php

- Hacer que al actualizar los datos de cada lote cuando su condicion de ingreso sea RECEPCION DE MINERAL, tambien se actualicen sus datos del ticket contable segun se editen estos campos en este modulo, similar al de validacion y distribucion:
Pasa que aqui al igual que en modulo de validacion y distribucion C:\wamp64\www\oppmerp\primertramo_distribucion.php se pueden editar los valores como pesos, fechas, etc. Sin embargo a diferencia del modulo de validacion y distribucion, al editar los valores aqui en resumen de balanza, no se actualizan en todas las tablas correspondientes. Para ello revisa lo que hace ese modulo para actualizar correctamente los campos utilizando el case de "update_PrimerTramo_DistribucionDatos" del backend:

-------------

	case 'update_PrimerTramo_DistribucionDatos':
		$estado = 0;

		// Recupera parámetros
		$id_registro = $_POST["id_registro"];
		$orden_campo = $_POST["orden_campo"];
		$valor = trim($_POST["valor"]);
		$valida_infounidad = $_POST["valida_infounidad"];
		$usuario_registro = $_SESSION["usu_usuario"];

		// Seteando campo
		$campo = '';
		$tiene_carreta = 0;
		$capacidad = 0;

		if ($orden_campo == 1) {
			$campo = 'balanza_id_tipovehiculo';

			$valor = explode('|', $valor)[0];

			$tiene_carreta = explode('|', $valor)[1];
		}

		if ($orden_campo == 2) {
			$campo = 'balanza_placa';

			if (strlen($valor) == 0) {
				$valor = '';
				$capacidad = 'NULL';
				$tara = 'NULL';
				$id_marca_x = 'NULL';
			} else {
				$capacidad = explode('|', $valor)[1];
				$capacidad = ((trim($capacidad) > 0) ? $capacidad : 0);

				$tara = explode('|', $valor)[2];
				$tara = ((trim($tara) > 0) ? $tara : 0);

				$id_marca_x = explode('|', $valor)[3];
				$id_marca_x = ((trim($id_marca_x) > 0) ? $id_marca_x : 'NULL');

				$valor = explode('|', $valor)[0];
			}
		}

		if ($orden_campo == 3) {
			$campo = 'balanza_placa2';

			if (strlen($valor) == 0) {
				$valor = '';
				$capacidad = 'NULL';
				$tara = 'NULL';
				$id_marca_x = 'NULL';
			} else {
				$capacidad = explode('|', $valor)[1];
				$capacidad = ((trim($capacidad) > 0) ? $capacidad : 0);

				$tara = explode('|', $valor)[2];
				$tara = ((trim($tara) > 0) ? $tara : 0);

				$id_marca_x = explode('|', $valor)[3];
				$id_marca_x = ((trim($id_marca_x) > 0) ? $id_marca_x : 'NULL');

				$valor = explode('|', $valor)[0];
			}
		}

		if ($orden_campo == 4) {
			$campo = 'unidad_capacidad';

			$valor = ((strlen($valor) > 0) ? ($valor * 1000) : 'NULL');
		}

		if ($orden_campo == 5) {
			$campo = 'unidad_tara';

			$valor = ((strlen($valor) > 0) ? ($valor * 1000) : 'NULL');
		}

		if ($orden_campo == 6) {
			$campo = 'unidad_idmarca';

			if (strlen($valor) == 0) {
				$valor = '';
			}
		}

		if ($orden_campo == 7) {
			$campo = 'lote_pesoinicial_fechahoraregistro';
		}

		if ($orden_campo == 8) {
			$campo = 'lote_pesofinal_fechahoraregistro';
		}

		if ($orden_campo == 9) {
			$campo = 'lote_peso_inicial';

			$valor = ((strlen($valor) > 0) ? ($valor * 1000) : 'NULL');
		}

		if ($orden_campo == 10) {
			$campo = 'lote_peso_final';

			$valor = ((strlen($valor) > 0) ? ($valor * 1000) : 'NULL');
		}

		if ($orden_campo == 11) {
			$campo = 'lote_peso_neto';

			$valor = ((strlen($valor) > 0) ? ($valor * 1000) : 'NULL');
		}

		if ($orden_campo == 12) {
			$campo = 'unidad_capacidad2';

			$valor = ((strlen($valor) > 0) ? ($valor * 1000) : 'NULL');
		}

		if ($orden_campo == 13) {
			$campo = 'unidad_tara2';

			$valor = ((strlen($valor) > 0) ? ($valor * 1000) : 'NULL');
		}

		if ($orden_campo == 14) {
			$campo = 'unidad_idmarca2';

			if (strlen($valor) == 0) {
				$valor = '';
			}
		}

		$valor = ((strlen($valor) > 0) ? "'" . $valor . "'" : 'NULL');

		// Guardando Backup
		$q_backup = "INSERT INTO despachos_primertramo_validaciondatos_log (id_validacion, campo, valor,
																																						fechahora_registro, usuario_registro)
										 SELECT  " . $id_registro . ",
														'" . $campo . "',
														 " . $valor . ",
														'" . $g_fecha . "',
														'" . $usuario_registro . "'
											 FROM despachos_primertramo_validaciondatos
											WHERE Id = " . $id_registro . "
												AND " . $valor . " IS NOT NULL";

		if ($res_backup = mysqli_query($enlace, $q_backup)) {
		}

		// Seteando query
		$q_save = "UPDATE despachos_primertramo_validaciondatos";
		$q_save .= "  SET " . $campo . " = " . $valor;

		// Campos de auditoría
		if ($orden_campo != 7 && $orden_campo != 8) {
			$q_save .= "   , " . $campo . "_fechahoraregistro = " . ((strlen($valor) > 0) ? "'" . $g_fecha . "'" : 'NULL');
			$q_save .= "   , " . $campo . "_usuarioregistro = " . ((strlen($valor) > 0) ? "'" . $usuario_registro . "'" : 'NULL');
		}

		if ($orden_campo == 7 || $orden_campo == 8) {
			$q_save .= "   , " . $campo . "_resumenbalanza_fechahorareg = " . ((strlen($valor) > 0) ? "'" . $g_fecha . "'" : 'NULL');
			$q_save .= "   , " . $campo . "_resumenbalanza_usuarioreg = " . ((strlen($valor) > 0) ? "'" . $usuario_registro . "'" : 'NULL');

			if ($orden_campo == 7) {
				$q_save .= "   , lote_pesofinal_fechahoraregistro = " . $valor;
			}
		}

		// Si no tiene carreta
		if ($orden_campo == 1) {
			if ($tiene_carreta == 0) {
				$q_save .= "   , balanza_placa2 = NULL";
				$q_save .= "   , balanza_placa2_fechahoraregistro = NULL";
				$q_save .= "   , balanza_placa2_usuarioregistro = NULL";
				$q_save .= "   , unidad_capacidad2 = NULL";
				$q_save .= "   , unidad_capacidad2_fechahoraregistro = NULL";
				$q_save .= "   , unidad_capacidad2_usuarioregistro = NULL";
				$q_save .= "   , unidad_tara2 = NULL";
				$q_save .= "   , unidad_tara2_fechahoraregistro = NULL";
				$q_save .= "   , unidad_tara2_usuarioregistro = NULL";
				$q_save .= "   , unidad_idmarca2 = NULL";
				$q_save .= "   , unidad_idmarca2_fechahoraregistro = NULL";
				$q_save .= "   , unidad_idmarca2_usuarioregistro = NULL";
			}
		}

		if ($orden_campo == 2) {
			$q_save .= "   , unidad_capacidad = " . $capacidad;
			$q_save .= "   , unidad_capacidad_fechahoraregistro = '" . $g_fecha . "'";
			$q_save .= "   , unidad_capacidad_usuarioregistro = '" . $usuario_registro . "'";
			$q_save .= "   , unidad_tara = " . $tara;
			$q_save .= "   , unidad_tara_fechahoraregistro = '" . $g_fecha . "'";
			$q_save .= "   , unidad_tara_usuarioregistro = '" . $usuario_registro . "'";
			$q_save .= "   , unidad_idmarca = " . $id_marca_x;
			$q_save .= "   , unidad_idmarca_fechahoraregistro = '" . $g_fecha . "'";
			$q_save .= "   , unidad_idmarca_usuarioregistro = '" . $usuario_registro . "'";
		}

		if ($orden_campo == 3) {
			$q_save .= "   , unidad_capacidad2 = " . $capacidad;
			$q_save .= "   , unidad_capacidad2_fechahoraregistro = '" . $g_fecha . "'";
			$q_save .= "   , unidad_capacidad2_usuarioregistro = '" . $usuario_registro . "'";
			$q_save .= "   , unidad_tara2 = " . $tara;
			$q_save .= "   , unidad_tara2_fechahoraregistro = '" . $g_fecha . "'";
			$q_save .= "   , unidad_tara2_usuarioregistro = '" . $usuario_registro . "'";
			$q_save .= "   , unidad_idmarca2 = " . $id_marca_x;
			$q_save .= "   , unidad_idmarca2_fechahoraregistro = '" . $g_fecha . "'";
			$q_save .= "   , unidad_idmarca2_usuarioregistro = '" . $usuario_registro . "'";
		}

		$q_save .= " WHERE Id = " . $id_registro;

		if ($res_save = mysqli_query($enlace, $q_save)) {
			$estado = 1;

			// Reglas de consistencia de Peso Bruto / Tara / Neto:
			//   - Si el usuario edita el BRUTO (orden_campo == 9): no tocar el neto,
			//     recalcular la TARA = BRUTO - NETO.
			//   - Si el usuario edita la TARA  (orden_campo == 10): no tocar el neto,
			//     recalcular el BRUTO = TARA + NETO.
			//   - Si el usuario edita el NETO  (orden_campo == 11): no tocar la tara,
			//     recalcular el BRUTO = TARA + NETO_nuevo.
			if ($orden_campo == 9) {
				$q_update = "UPDATE despachos_primertramo_validaciondatos";
				$q_update .= "  SET lote_peso_final = lote_peso_inicial - lote_peso_neto";
				$q_update .= " WHERE Id = " . $id_registro;

				if ($res_update = mysqli_query($enlace, $q_update)) {
				}
			}

			if ($orden_campo == 10 || $orden_campo == 11) {
				$q_update = "UPDATE despachos_primertramo_validaciondatos";
				$q_update .= "  SET lote_peso_inicial = lote_peso_final + lote_peso_neto";
				$q_update .= " WHERE Id = " . $id_registro;

				if ($res_update = mysqli_query($enlace, $q_update)) {
				}
			}

			// Valida Información de Unidades
			if ($valida_infounidad == 1) {
				// 1. Obtiene la información de Placa 1 y Fecha de Peso Inicial en Balanza
				$balanza_placa = '';
				$fecha_pesoinicial = '';
				$balanza_id_tipovehiculo = '';
				$unidad_capacidad = '';
				$unidad_tara = '';
				$unidad_idmarca = '';
				$balanza_placa2 = '';
				$unidad_capacidad2 = '';
				$unidad_tara2 = '';
				$unidad_idmarca2 = '';

				$q_datos = "SELECT balanza_placa,
																	 DATE(lote_pesoinicial_fechahoraregistro) AS FECHA_PESOINICIAL,
																	 balanza_id_tipovehiculo,
																	 unidad_capacidad,
																	 unidad_tara,
																	 unidad_idmarca,
																	 balanza_placa2,
																	 unidad_capacidad2,
																	 unidad_tara2,
																	 unidad_idmarca2
															FROM despachos_primertramo_validaciondatos
														 WHERE Id = " . $id_registro;

				if ($res_datos = mysqli_query($enlace, $q_datos)) {
					if (mysqli_num_rows($res_datos) > 0) {
						while ($row_datos = mysqli_fetch_array($res_datos)) {
							$balanza_placa = $row_datos["balanza_placa"];
							$fecha_pesoinicial = $row_datos["FECHA_PESOINICIAL"];
							$balanza_id_tipovehiculo = $row_datos["balanza_id_tipovehiculo"];
							$unidad_capacidad = $row_datos["unidad_capacidad"];
							$unidad_tara = $row_datos["unidad_tara"];
							$unidad_idmarca = $row_datos["unidad_idmarca"];
							$balanza_placa2 = $row_datos["balanza_placa2"];
							$unidad_capacidad2 = $row_datos["unidad_capacidad2"];
							$unidad_tara2 = $row_datos["unidad_tara2"];
							$unidad_idmarca2 = $row_datos["unidad_idmarca2"];

							// 2. Actualiza los tickets de lotes pendientes de cierre
							$q_update = "UPDATE despachos_primertramo_validaciondatos SET";
							$q_update .= "  balanza_id_tipovehiculo = " . ((strlen($balanza_id_tipovehiculo) > 0) ? "'" . $balanza_id_tipovehiculo . "'" : 'NULL');
							$q_update .= ", unidad_capacidad = " . ((strlen($unidad_capacidad) > 0) ? "'" . $unidad_capacidad . "'" : 'NULL');
							$q_update .= ", unidad_tara = " . ((strlen($unidad_tara) > 0) ? "'" . $unidad_tara . "'" : 'NULL');
							$q_update .= ", unidad_idmarca = " . ((strlen($unidad_idmarca) > 0) ? "'" . $unidad_idmarca . "'" : 'NULL');
							$q_update .= ", balanza_placa2 = " . ((strlen($balanza_placa2) > 0) ? "'" . $balanza_placa2 . "'" : 'NULL');
							$q_update .= ", unidad_capacidad2 = " . ((strlen($unidad_capacidad2) > 0) ? "'" . $unidad_capacidad2 . "'" : 'NULL');
							$q_update .= ", unidad_tara2 = " . ((strlen($unidad_tara2) > 0) ? "'" . $unidad_tara2 . "'" : 'NULL');
							$q_update .= ", unidad_idmarca2 = " . ((strlen($unidad_idmarca2) > 0) ? "'" . $unidad_idmarca2 . "'" : 'NULL');
							$q_update .= " WHERE balanza_placa = '" . $balanza_placa . "'";
							// $q_update .= "   AND DATE(lote_pesoinicial_fechahoraregistro) = '".$fecha_pesoinicial."'";
							$q_update .= "   AND is_cerrado = 0";

							if ($res_update = mysqli_query($enlace, $q_update)) {
							}

							// Actualiza el maestro de Unidades para la Placa 1
							$q_update = "UPDATE transporte SET";
							$q_update .= "  id_tipovehiculo = " . ((strlen($balanza_id_tipovehiculo) > 0) ? "'" . $balanza_id_tipovehiculo . "'" : 'NULL');
							$q_update .= ", nCapacidad = " . ((strlen($unidad_capacidad) > 0) ? "'" . $unidad_capacidad . "'" : 'NULL');
							$q_update .= ", nTara = " . ((strlen($unidad_tara) > 0) ? "'" . $unidad_tara . "'" : 'NULL');
							$q_update .= ", id_marca = " . ((strlen($unidad_idmarca) > 0) ? "'" . $unidad_idmarca . "'" : 'NULL');
							$q_update .= " WHERE cplaca = '" . $balanza_placa . "'";

							if ($res_update = mysqli_query($enlace, $q_update)) {
								// Actualiza el maestro para la Placa 2
								$q_update = "UPDATE transporte SET";
								$q_update .= "  nCapacidad = " . ((strlen($unidad_capacidad2) > 0) ? "'" . $unidad_capacidad2 . "'" : 'NULL');
								$q_update .= ", nTara = " . ((strlen($unidad_tara2) > 0) ? "'" . $unidad_tara2 . "'" : 'NULL');
								$q_update .= ", id_marca = " . ((strlen($unidad_idmarca2) > 0) ? "'" . $unidad_idmarca2 . "'" : 'NULL');
								$q_update .= " WHERE cplaca = '" . $balanza_placa2 . "'";

								if ($res_update = mysqli_query($enlace, $q_update)) {
								}
							}

							// MAX - AQUI ME QUEDE, FALTA ACTUALIZAR LA TABLA DE VALIDACION DESDE EL MAESTRO DE UNIDADES Y HACER QUE SE BLOQUEEN LOS OBJETOS PARA CUANDO SE TENGA UNA BALANZA Y FECHA DE PESO INICIAL IGUALES.
						}
					}
				}
			}

			// Re-sincronizar el Ticket Contable (consolidado_lotes_cierrecontable)
			// si el registro ya fue cerrado o si ya fue migrado previamente.
			// La función f_MigrarLotes_CierreContable es idempotente: si ya existe,
			// actualiza los datos; si no, los inserta. Así, cualquier corrección
			// hecha desde el módulo de Validación y Distribución se ve reflejada
			// en el ticket contable.
			$q_chk_migrado = "SELECT COUNT(Id) AS _COUNT
														FROM consolidado_lotes_cierrecontable
													 WHERE id_tipoingreso = 1
														 AND id_registro = " . $id_registro;

			$chk_migrado = 0;

			if ($res_chk_migrado = mysqli_query($enlace, $q_chk_migrado)) {
				if ($row_chk_migrado = mysqli_fetch_array($res_chk_migrado)) {
					$chk_migrado = intval($row_chk_migrado["_COUNT"]);
				}
			}

			if ($chk_migrado > 0) {
				f_MigrarLotes_CierreContable($enlace, 1, $id_registro, $g_fecha, $usuario_registro);
			}
		}

		echo json_encode(array('estado' => $estado));

		break;

-----------------

Esta es la funcion que actualiza o registra el ticket contable del lote

---------------
// Obtiene los datos de Balanza pendientes por importar a la tabla de Validación de Datos del Primer Tramo de Despachos.
function cerrar_lote_desde_primer_tramo($enlace, $id_lote_a_cerrar, $g_fecha, $usuario_registro, $is_pesoinicial)
{
	// --------------------------------------------------------
	// REGISTRAR O BUSCAR EN LA TABLA CORRESPONDIENTE A LA DE GUIAS
	// --------------------------------------------------------

	$id_despacho_validacion = 0;
	$q_id = "
			SELECT
				des.Id AS id_despacho_validacion
			FROM despachos_primertramo_validaciondatos des
			WHERE des.lote_id_lote = " . $id_lote_a_cerrar;

	if ($res = mysqli_query($enlace, $q_id)) {
		if (mysqli_num_rows($res) > 0) {
			while ($row = mysqli_fetch_array($res)) {
				$id_despacho_validacion = $row["id_despacho_validacion"];
			}
		}
	}

	$q_select = "
	SELECT
		B.id_controlIngresoVehiculo,
		B.id_tipoingresounidad,
		B.placa,
		B.placa2,
		B.id_transportista,
		B.id_tipovehiculo,
		B.id_choferes,
		CONCAT(B.dFechaIngreso,' ',B.dhoraingresoPlanta) AS FECHAHORA_INGRESOPLANTA,
		L.id_CatalogoLotes,
		L.ccod_Lote,
		L.nNro_ticketsBalanza,
		L.balanza_id_tipocarga,
		L.balanza_id_zonaorigen,
		L.balanza_id_proveedorminero,
		L.balanza_id_encargadomuestra,
		L.balanza_id_producto,
		L.balanza_id_tipomineral,
		L.nPeso_InicialBalanza,
		CONCAT(L.tFechaInicialBalanza,' ',L.tHoraInicialBalanza) AS PESOINICIAL_FECHAHORA,
		L.nPeso_FinalBalanza,
		CONCAT(L.dFechaFinalBalanza,' ',L.tHoraFinalBalanza) AS PESOFINAL_FECHAHORA,
		L.nPeso_InicialBalanza AS nPeso_BrutoOrigen,
		L.nPeso_FinalBalanza AS nPeso_TaraOrigen,
		(L.nPeso_InicialBalanza - L.nPeso_FinalBalanza),
		(L.nPeso_InicialBalanza - L.nPeso_FinalBalanza) AS PENDIENTE_ENVIO,
		1,
		U.nCapacidad,
		U.nTara,
		U.id_marca,
		L.balanza_observacion,
		NULL
	FROM
		catalogolotes L

	INNER JOIN controlingresovehiculo B ON L.id_controlIngresoVehiculo = B.id_controlIngresoVehiculo
	LEFT JOIN tb_clientes C ON L.balanza_id_proveedorminero = C.Id AND C.cod_clientecondicion = 1
	LEFT JOIN transporte U ON B.placa = U.cplaca

	WHERE
		1=1
	";

	$q_select .= " AND L.id_CatalogoLotes = $id_lote_a_cerrar";

	// INSERT: registra el lote si aún no existe
	$q_insert = "
	INSERT INTO despachos_primertramo_validaciondatos(
		balanza_id_controlingresovehiculo,
		balanza_id_tipoingresounidad,
		balanza_placa,
		balanza_placa2,
		balanza_id_transportista,
		balanza_id_tipovehiculo,
		balanza_id_chofer,
		balanza_fechahoraregistro,
		lote_id_lote,
		lote_cod_lote,
		lote_num_ticket,
		lote_id_tipocarga,
		lote_id_zonaorigen,
		lote_id_proveedorminero,
		lote_id_encargadomuestra,
		lote_id_producto,
		lote_id_tipomineral,
		lote_peso_inicial,
		lote_pesoinicial_fechahoraregistro,
		lote_peso_final,
		lote_pesofinal_fechahoraregistro,
		lote_peso_bruto,
		lote_peso_tara,
		lote_peso_neto,
		lote_peso_pendiente_envio,
		despacho_id_estadolote,
		unidad_capacidad,
		unidad_tara,
		unidad_idmarca,
		despacho_observacion,
		despacho_id_modalidadenvio
	)
	" . $q_select;

	// UPDATE: re-sincroniza los datos propios del lote si ya existe el registro
	$q_update_existente = "
	UPDATE despachos_primertramo_validaciondatos des
	INNER JOIN ( " . $q_select . " ) AS src ON src.id_CatalogoLotes = des.lote_id_lote
	SET des.balanza_id_controlingresovehiculo      = src.id_controlIngresoVehiculo,
		des.balanza_id_tipoingresounidad           = src.id_tipoingresounidad,
		des.balanza_placa                          = src.placa,
		des.balanza_placa2                         = src.placa2,
		des.balanza_id_transportista               = src.id_transportista,
		des.balanza_id_tipovehiculo                = src.id_tipovehiculo,
		des.balanza_id_chofer                      = src.id_choferes,
		des.balanza_fechahoraregistro              = src.FECHAHORA_INGRESOPLANTA,
		des.lote_id_lote                           = src.id_CatalogoLotes,
		des.lote_cod_lote                          = src.ccod_Lote,
		des.lote_num_ticket                        = src.nNro_ticketsBalanza,
		des.lote_id_tipocarga                      = src.balanza_id_tipocarga,
		des.lote_id_zonaorigen                     = src.balanza_id_zonaorigen,
		des.lote_id_proveedorminero                = src.balanza_id_proveedorminero,
		des.lote_id_encargadomuestra               = src.balanza_id_encargadomuestra,
		des.lote_id_producto                       = src.balanza_id_producto,
		des.lote_id_tipomineral                    = src.balanza_id_tipomineral,
		des.lote_peso_inicial                      = src.nPeso_InicialBalanza,
		des.lote_pesoinicial_fechahoraregistro     = src.PESOINICIAL_FECHAHORA,
		des.lote_peso_final                        = src.nPeso_FinalBalanza,
		des.lote_pesofinal_fechahoraregistro       = src.PESOFINAL_FECHAHORA,
		des.lote_peso_bruto                        = src.nPeso_BrutoOrigen,
		des.lote_peso_tara                         = src.nPeso_TaraOrigen,
		des.lote_peso_neto                         = (src.nPeso_InicialBalanza - src.nPeso_FinalBalanza),
		des.lote_peso_pendiente_envio              = (src.nPeso_InicialBalanza - src.nPeso_FinalBalanza),
		des.despacho_id_estadolote                 = 1,
		des.unidad_capacidad                       = src.nCapacidad,
		des.unidad_tara                            = src.nTara,
		des.unidad_idmarca                         = src.id_marca,
		des.despacho_observacion                   = src.balanza_observacion
	WHERE des.Id = " . $id_despacho_validacion . "
	";

	// Solo registramos si el lote no esta registrado
	if ($id_despacho_validacion == 0) {

		$result = mysqli_query($enlace, $q_insert);

		if (!$result) {
			die(mysqli_error($enlace));
		}

		$id_despacho_validacion = mysqli_insert_id($enlace);
	} else {
		// Si el lote ya está registrado, re-sincronizamos sus datos propios desde el maestro de lotes
		mysqli_query($enlace, $q_update_existente);
	}

	// --------------------------------------------------------
	// CERRAR EL LOTE PARA GENERAR EL TICKET CONTABLE
	// --------------------------------------------------------

	// Grabando cierre (solo si aún no está cerrada; si ya lo está no se actualiza para no alterar fecha/hora/usuario originales)
	$q_update = "UPDATE despachos_primertramo_validaciondatos";
	$q_update .= "  SET is_cerradolote = 1, is_cerrado = 1, ";
	$q_update .= "			cerradolote_fechahoraregistro = '" . $g_fecha . "', ";
	$q_update .= "			cerradolote_usuarioregistro = '" . $usuario_registro . "'";
	$q_update .= " WHERE Id = " . $id_despacho_validacion . "";
	$q_update .= "   AND (is_cerradolote = 0 OR is_cerradolote IS NULL)";
	mysqli_query($enlace, $q_update);

	// Migrando Lotes cerrados a la tabla de datos Consolidados - el "1" representa el primer tramo.
	// Se ejecuta en AMBOS casos (peso inicial y peso final): la función f_MigrarLotes_CierreContable
	// detecta si el registro ya existe en consolidado_lotes_cierrecontable y hace UPDATE; si no, INSERT.
	// Así se mantiene la coherencia de datos cuando al registrar el peso final se modifican datos del lote.
	f_MigrarLotes_CierreContable($enlace, 1, $id_despacho_validacion, $g_fecha, $usuario_registro);


	// --------------------------------------------------------------------------------------------
	// DEVOLVEMOS EL ID EN MD5 DEL REGISTRO GENERADO, USADO PARA LA IMPRESION DEL TICKET CONTABLE
	// --------------------------------------------------------------------------------------------
	return md5($id_despacho_validacion);
}

------------------

Esa funcion a la vez hace uso de esta otra funcion

-----------

// Migra Lotes al Consolidado de Cierre Contable
function f_MigrarLotes_CierreContable($enlace, $id_tipoingreso, $arr_idregistros, $g_fecha, $usuario_registro)
{
	$l = 0;
	$arr_idregistros = explode(', ', $arr_idregistros);
	$idregistro_new = 0;

	while ($l < count($arr_idregistros)) {
		$skip_insert = false;
		$id_existente = 0;

		// 1. Verificar si ya existe el registro en consolidado_lotes_cierrecontable
		//    Esto aplica para AMBOS tramos (id_tipoingreso = 1 y 2) y evita duplicados al re-ejecutarse.
		$q_exists = "SELECT Id
								 FROM consolidado_lotes_cierrecontable
								WHERE id_tipoingreso = " . $id_tipoingreso . "
									AND id_registro = " . $arr_idregistros[$l];

		if ($res_exists = mysqli_query($enlace, $q_exists)) {
			if (mysqli_num_rows($res_exists) > 0) {
				$row_exists = mysqli_fetch_array($res_exists);
				$id_existente = $row_exists["Id"];
			}
		}

		// 2. Construir el SELECT de los datos del lote según el tramo
		//    Se reutiliza tanto para INSERT (cuando no existe) como para UPDATE (cuando ya existe).
		$q_select_datos = "";

		if ($id_tipoingreso == 1) {
			$q_select_datos = "
			SELECT V.Id,
							V.lote_cod_lote,
							V.lote_ticket_orden,
							" . $id_tipoingreso . ",
							DATE(V.lote_pesoinicial_fechahoraregistro) AS FECHA_INGRESOBALANZA,
							V.balanza_placa,
							V.balanza_placa2,
							CONCAT(V.guiaremitente_serie, '-', V.guiaremitente_numero) AS GUIA_REMITENTE,
							CONCAT(V.guiatransportista_serie, '-', V.guiatransportista_numero) AS GUIA_TRANSPORTISTA,
							CL_T.documento AS TRANSPORTISTA_RUC,
							UPPER(CL_T.razon_social) AS TRANSPORTISTA_RAZONSOCIAL,
							T.id_tipovehiculo,
							CD.licencia_conducir AS CONDUCTOR_LICENCIA,
							CD.nombres AS CONDUCTOR_NOMBRES,
							V.lote_id_tipocarga,
							NULL,
							V.lote_id_zonaorigen,
							CL.documento AS PROVEEDORMINERO_RUC,
							UPPER(CL.razon_social) AS PROVEEDORMINERO_RAZONSOCIAL,
							UPPER(EM.nombres) AS ENCARGADO_MUESTRA,
							NULL,
							NULL,
							V.lote_id_producto,
							V.lote_id_tipomineral,
							V.despacho_observacion,
							V.lote_pesoinicial_fechahoraregistro,
							V.lote_pesofinal_fechahoraregistro,
							V.lote_peso_inicial AS lote_peso_bruto,
							V.lote_peso_final AS lote_peso_tara,
							V.lote_peso_neto,
							'" . $g_fecha . "',
							'" . $usuario_registro . "'
				 FROM despachos_primertramo_validaciondatos V
							LEFT JOIN transporte T ON V.balanza_placa = T.cplaca
							LEFT JOIN tb_clientes CL_T ON T.id_Transportista = CL_T.Id
							LEFT JOIN tbconfig_conductores CD ON V.guias_idchofer = CD.Id
							LEFT JOIN tb_clientes CL ON V.lote_id_proveedorminero = CL.Id
							LEFT JOIN tbconfig_encargadosmuestra EM ON V.lote_id_encargadomuestra = EM.Id
				WHERE V.Id = " . $arr_idregistros[$l];
		} else {
			$q_select_datos = "SELECT DISTINCT
															DL.Id,
															PD.cod_lote,
															/*DL.num_parte,*/

															(CASE WHEN (SELECT COUNT(DL_x.Id)
																						FROM despachos_segundotramo_distribucion_lotes DL_x
																					 WHERE DL_x.is_complemento_de = DL.Id) > 0
																 THEN 1
															 ELSE CASE WHEN DL.is_complemento = 1
																			THEN (SELECT 1 + (COUNT(CC_x.Id) + 1)
																							FROM consolidado_lotes_cierrecontable CC_x
																						 WHERE CC_x.cod_lote = PD.cod_lote
																							 AND CC_x.id_tipoingreso = 2
																							 AND lote_item <> 1)
																		ELSE DL.num_parte END END) AS lote_item,

															" . $id_tipoingreso . " AS id_tipoingreso,
															/*DATE(DL.peso_bruto_fechahoraregistro) AS FECHA_INGRESOBALANZA,*/
															DATE(DL.peso_tara_fechahoraregistro) AS fecha_ingresobalanza, -- ahora los codigos de los tickets se basaran en la fecha de peso inicial
															DL.guias_placa1 AS PLACA1,
															DL.guias_placa2 AS PLACA2,
															CONCAT(DL.guiaremitente_serie, '-', DL.guiaremitente_numero) AS GUIA_REMITENTE,
															CONCAT(DL.guiatransportista_serie, '-', DL.guiatransportista_numero) AS GUIA_TRANSPORTISTA,
															TR.documento AS TRANSPORTISTA_RUC,
															TR.razon_social AS TRANSPORTISTA_RAZONSOCIAL,
															UN.id_tipovehiculo,
															CH.licencia_conducir AS CONDUCTOR_DNI,
															CH.nombres AS CONDUCTOR_NOMBRES,
															DL.id_tipocarga,
															DL.num_bigbag,
															24 AS id_zonaorigen,
															NULL AS proveedorminero_ruc,
															NULL AS proveedorminero_razonsocial,
															NULL AS encargadomuestra_nombres,
															RE.ruc AS REMITENTE_RUC,
															RE.razon_social AS REMITENTE_RAZONSOCIAL,
															V.lote_id_producto,
															V.lote_id_tipomineral,
															DL.observacion,
															/*DL.peso_bruto_fechahoraregistro,
															DL.peso_tara_fechahoraregistro,*/
															DL.guias_fecha AS fecha_pesoinicial,
															DL.guias_fecha AS fecha_pesofinal,
															DL.peso_bruto,
															DL.peso_tara,
															DL.peso_neto,
															'" . $g_fecha . "' AS fechahora_registro,
															'" . $usuario_registro . "' AS fechahora_usuario
												 FROM despachos_segundotramo_programacion_detalle PD
															LEFT JOIN despachos_segundotramo_programacion P ON PD.id_programacion = P.Id
															LEFT JOIN despachos_segundotramo_distribucion_unidades U ON P.Id = U.id_programacion
															LEFT JOIN despachos_segundotramo_distribucion_lotes DL ON U.Id = DL.id_distribucionunidad
															AND PD.cod_lote = DL.cod_lote
															LEFT JOIN transporte UN ON U.id_unidad = UN.id_transporte
															LEFT JOIN transporte UN2 ON U.id_unidad2 = UN2.id_transporte
															LEFT JOIN tb_clientes TR ON UN.id_Transportista = TR.Id
															LEFT JOIN tbconfig_plantas PL ON PD.id_planta = PL.Id
															LEFT JOIN tbconfig_modalidadenvio ME ON PD.id_modalidadenvio = ME.Id
															LEFT JOIN despachos_primertramo_validaciondatos V ON PD.cod_lote = V.lote_cod_lote
															LEFT JOIN tbconfig_conductores CH ON DL.guias_idchofer = CH.Id
															LEFT JOIN tbconfig_remitentessegundotramo RE ON DL.guias_iddestino = RE.id_destino
															AND DL.guias_idmodalidadenvio = RE.id_modalidadenvio
												WHERE DL.Id = " . $arr_idregistros[$l];
		}

		// 3. Si ya existe el registro, se re-sincroniza (UPDATE).
		//    Si no existe, se inserta por primera vez (INSERT) y se genera el correlativo del ticket.
		if ($id_existente > 0) {

			$res_select = mysqli_query($enlace, $q_select_datos);
			if ($res_select && $row = mysqli_fetch_array($res_select)) {
				$q_update = "UPDATE consolidado_lotes_cierrecontable SET
								cod_lote = " . ($row[1] !== null ? "'" . mysqli_real_escape_string($enlace, $row[1]) . "'" : 'NULL') . ",
								lote_item = " . ($row[2] !== null ? $row[2] : 'NULL') . ",
								fecha_ingresobalanza = " . ($row[4] !== null ? "'" . $row[4] . "'" : 'NULL') . ",
								placa1 = " . ($row[5] !== null ? "'" . mysqli_real_escape_string($enlace, $row[5]) . "'" : 'NULL') . ",
								placa2 = " . ($row[6] !== null ? "'" . mysqli_real_escape_string($enlace, $row[6]) . "'" : 'NULL') . ",
								numguia_remitente = " . ($row[7] !== null ? "'" . mysqli_real_escape_string($enlace, $row[7]) . "'" : 'NULL') . ",
								numguia_transportista = " . ($row[8] !== null ? "'" . mysqli_real_escape_string($enlace, $row[8]) . "'" : 'NULL') . ",
								transportista_ruc = " . ($row[9] !== null ? "'" . mysqli_real_escape_string($enlace, $row[9]) . "'" : 'NULL') . ",
								transportista_razonsocial = " . ($row[10] !== null ? "'" . mysqli_real_escape_string($enlace, $row[10]) . "'" : 'NULL') . ",
								id_tipovehiculo = " . ($row[11] !== null ? $row[11] : 'NULL') . ",
								conductor_licencia = " . ($row[12] !== null ? "'" . mysqli_real_escape_string($enlace, $row[12]) . "'" : 'NULL') . ",
								conductor_nombres = " . ($row[13] !== null ? "'" . mysqli_real_escape_string($enlace, $row[13]) . "'" : 'NULL') . ",
								id_tipocarga = " . ($row[14] !== null ? $row[14] : 'NULL') . ",
								num_bigbag = " . ($row[15] !== null ? $row[15] : 'NULL') . ",
								remitente_ruc = " . ($row[20] !== null ? "'" . mysqli_real_escape_string($enlace, $row[20]) . "'" : 'NULL') . ",
								remitente_razonsocial = " . ($row[21] !== null ? "'" . mysqli_real_escape_string($enlace, $row[21]) . "'" : 'NULL') . ",
								id_producto = " . ($row[22] !== null ? $row[22] : 'NULL') . ",
								id_tipomineral = " . ($row[23] !== null ? $row[23] : 'NULL') . ",
								observacion = " . ($row[24] !== null ? "'" . mysqli_real_escape_string($enlace, $row[24]) . "'" : 'NULL') . ",
								fecha_pesoinicial = " . ($row[25] !== null ? "'" . $row[25] . "'" : 'NULL') . ",
								fecha_pesofinal = " . ($row[26] !== null ? "'" . $row[26] . "'" : 'NULL') . ",
								peso_bruto = " . ($row[27] !== null ? $row[27] : 'NULL') . ",
								peso_tara = " . ($row[28] !== null ? $row[28] : 'NULL') . ",
								peso_neto = " . ($row[29] !== null ? $row[29] : 'NULL') . ",
								fechahora_registro = '" . $g_fecha . "',
								fechahora_usuario = '" . $usuario_registro . "'
							 WHERE Id = " . $id_existente;
				mysqli_query($enlace, $q_update);
			}
			$skip_insert = true;
			$idregistro_new = 0;
		} else {

			$q_insert = "INSERT INTO consolidado_lotes_cierrecontable(
							id_registro,
							cod_lote,
							lote_item,
							id_tipoingreso,
							fecha_ingresobalanza,
							placa1,
							placa2,
							numguia_remitente,
							numguia_transportista,
							transportista_ruc,
							transportista_razonsocial,
							id_tipovehiculo,
							conductor_licencia,
							conductor_nombres,
							id_tipocarga,
							num_bigbag,
							id_zonaorigen,
							proveedorminero_ruc,
							proveedorminero_razonsocial,
							encargadomuestra_nombres,
							remitente_ruc,
							remitente_razonsocial,
							id_producto,
							id_tipomineral,
							observacion,
							fecha_pesoinicial,
							fecha_pesofinal,
							peso_bruto,
							peso_tara,
							peso_neto,
							fechahora_registro,
							fechahora_usuario
						) " . $q_select_datos;

			if ($res_datos = mysqli_query($enlace, $q_insert)) {
				$idregistro_new = mysqli_insert_id($enlace);
			} else {
				$idregistro_new = 0;
			}
		}

		if (!$skip_insert && $idregistro_new > 0) {
			// Genera los Número de Ticket del Cierre Contable (Primer y Segundo Tramo)
			// 1. Obtiene le Fecha del nuevo registro
			$fecha_x = '';

			$q_fecha = "SELECT fecha_ingresobalanza
														FROM consolidado_lotes_cierrecontable
													 WHERE Id = " . $idregistro_new;

			if ($res_fecha = mysqli_query($enlace, $q_fecha)) {
				if (mysqli_num_rows($res_fecha) > 0) {
					while ($row_fecha = mysqli_fetch_array($res_fecha)) {
						$fecha_x = $row_fecha["fecha_ingresobalanza"];
					}
				}
			}

			// 2. Obtiene el N° de Ticket asignado a los registros anteriores
			$c = 1;
			$correlativo_x = 0; // Para identificar el Correlativo de cada registro
			$continuar = 1; // Ayuda a saber cuando continuar

			$q_numticket = "SELECT num_ticketbalanza
																FROM consolidado_lotes_cierrecontable
															 WHERE fecha_ingresobalanza = '" . $fecha_x . "'
																 AND Id <> " . $idregistro_new . "
															ORDER BY num_ticketbalanza";

			if ($res_numticket = mysqli_query($enlace, $q_numticket)) {
				if (mysqli_num_rows($res_numticket) > 0) {
					while ($row_numticket = mysqli_fetch_array($res_numticket)) {
						// 3. Identifica el Correlativo de cada registro

						$correlativo_x = explode('-', $row_numticket["num_ticketbalanza"])[1];
						$correlativo_x = intval($correlativo_x);

						// 4. Crea un Array para buscar el Nuevo Correlativo
						while ($c < 10000) {

							if ($c == $correlativo_x) {
								$c++;

								break;
							}

							if ($c != $correlativo_x) {
								// Setea Prefijo
								$prefijo = explode('-', $fecha_x);
								$prefijo = $prefijo[2] . $prefijo[1] . substr($prefijo[0], 2);

								// Setea Correlativo
								$correlativo = $prefijo . '-' . str_pad($c, 3, '0', STR_PAD_LEFT);

								// Actualiza Correlativo
								$q_update = "UPDATE consolidado_lotes_cierrecontable SET";
								$q_update .= "   num_ticketbalanza = '" . $correlativo . "'";
								$q_update .= " WHERE Id = " . $idregistro_new;

								if ($q_update = mysqli_query($enlace, $q_update)) {
									$continuar = 0;

									break 2;
								}
							}

							$c++;
						}
					}
				}
			}

			// Asigna el Correlativo
			if ($continuar == 1) {
				// Setea Prefijo
				$prefijo = explode('-', $fecha_x);
				$prefijo = $prefijo[2] . $prefijo[1] . substr($prefijo[0], 2);

				// Setea Correlativo
				$correlativo = $prefijo . '-' . str_pad($c, 3, '0', STR_PAD_LEFT);

				// Actualiza Correlativo
				$q_update = "UPDATE consolidado_lotes_cierrecontable SET";
				$q_update .= "   num_ticketbalanza = '" . $correlativo . "'";
				$q_update .= " WHERE Id = " . $idregistro_new;

				if ($q_update = mysqli_query($enlace, $q_update)) {
				}
			}
		}

		$l++;
	}
}

--------------

Case usado por el modulo de resumen de balanza para actualizar los datos de los lotes:
- grabar_EditBalanza 
Case usado para obtener todos los lotes que pasaron por balanza. Entre esos lotes estan aquellos que son de recepcion de mineral los cuales son de despachos_primertramo_validaciondatos:
- get_ListaResumenBalanza

-----------

Todos esos campos editados se deben reflejar en el ticket de aqui C:\wamp64\www\oppmerp\print_ticketbalanza.php