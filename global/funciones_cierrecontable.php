<?php

// ----------------------------------------------------------------------------------------------------------
// Funciones compartidas del Cierre Contable (Segundo y Primer Tramo).
//
// Este archivo reemplaza a las definiciones que vivian dentro de apis/backend.php para permitir que otros
// modulos (por ejemplo print_ticketbalanza_segundotramo.php) reutilicen la migracion y el cierre de lotes
// sin tener que invocar al endpoint con un POST.
//
// backend.php lo carga con require_once, por lo que el comportamiento de todos los cases se mantiene igual.
// ----------------------------------------------------------------------------------------------------------

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
			f_GenerarNumeroTicketCierreContable($enlace, $idregistro_new);
		}

		$l++;
	}
}

// ----------------------------------------------------------------------------------------------------------
// Genera el N° de Ticket Contable (formato YYMMDD-NNN) para un registro YA existente en
// consolidado_lotes_cierrecontable. El correlativo busca el primer numero libre dentro de la misma
// fecha_ingresobalanza del registro, por lo que no colisiona con los tickets ya emitidos de ese dia.
//
// Se expone como funcion aparte porque el cierre puede ejecutarse sobre un registro que ya estaba
// migrado pero cuyo numero de ticket nunca llego a generarse (num_ticketbalanza NULL o vacio).
// ----------------------------------------------------------------------------------------------------------
function f_GenerarNumeroTicketCierreContable($enlace, $id_consolidado)
{
	// No duplicar ticket: si ya tiene numero asignado, no se toca nada.
	$q_yaexiste = "SELECT num_ticketbalanza
									FROM consolidado_lotes_cierrecontable
								WHERE Id = " . (int)$id_consolidado;

	$ticket_actual = '';

	if ($res_yaexiste = mysqli_query($enlace, $q_yaexiste)) {
		if (mysqli_num_rows($res_yaexiste) > 0) {
			$row_yaexiste = mysqli_fetch_array($res_yaexiste);
			$ticket_actual = (string) $row_yaexiste["num_ticketbalanza"];
		}
	}

	if (strlen(trim($ticket_actual)) > 0) {
		return $ticket_actual;
	}

	// 1. Obtiene la Fecha del registro
	$fecha_x = '';

	$q_fecha = "SELECT fecha_ingresobalanza
													FROM consolidado_lotes_cierrecontable
												WHERE Id = " . (int)$id_consolidado;

	if ($res_fecha = mysqli_query($enlace, $q_fecha)) {
		if (mysqli_num_rows($res_fecha) > 0) {
			while ($row_fecha = mysqli_fetch_array($res_fecha)) {
				$fecha_x = $row_fecha["fecha_ingresobalanza"];
			}
		}
	}

	// Si el lote nunca fue pesado, fecha_ingresobalanza llega vacio y el prefijo del ticket saldria
	// mal formado. En ese caso se usa la fecha del dia para no generar numeros corruptos.
	if (strlen(trim((string) $fecha_x)) == 0) {
		$fecha_x = date('Y-m-d');
	}

	$partes_fecha = explode('-', $fecha_x);
	$prefijo = $partes_fecha[2] . $partes_fecha[1] . substr($partes_fecha[0], 2);

	// 2. Busca el primer correlativo libre de esa fecha
	$c = 1;
	$correlativo = '';

	$q_numticket = "SELECT num_ticketbalanza
															FROM consolidado_lotes_cierrecontable
													 WHERE fecha_ingresobalanza = '" . $fecha_x . "'
																AND Id <> " . (int)$id_consolidado . "
																	AND num_ticketbalanza IS NOT NULL
																	AND num_ticketbalanza <> ''
															ORDER BY num_ticketbalanza";

	if ($res_numticket = mysqli_query($enlace, $q_numticket)) {
		if (mysqli_num_rows($res_numticket) > 0) {
			$usados = array();

			while ($row_numticket = mysqli_fetch_array($res_numticket)) {
				// 3. Identifica el Correlativo de cada registro ya emitido
				$partes_ticket = explode('-', $row_numticket["num_ticketbalanza"]);
				$usados[intval($partes_ticket[1])] = 1;
			}

			while ($c < 10000) {
				if (!isset($usados[$c])) {
					$correlativo = $prefijo . '-' . str_pad($c, 3, '0', STR_PAD_LEFT);
					break;
				}

				$c++;
			}
		}
	}

	// Si todos los correlativos estan ocupados se cae al primero libre por seguridad
	if ($correlativo == '') {
		$correlativo = $prefijo . '-' . str_pad($c, 3, '0', STR_PAD_LEFT);
	}

	// Actualiza Correlativo
	$q_update = "UPDATE consolidado_lotes_cierrecontable SET";
	$q_update .= "   num_ticketbalanza = '" . $correlativo . "'";
	$q_update .= " WHERE Id = " . (int)$id_consolidado;

	if ($res_update = mysqli_query($enlace, $q_update)) {
		return $correlativo;
	}

	return '';
}

// ----------------------------------------------------------------------------------------------------------
// Usuario con el que se registra el cierre contable.
//
// Prioriza el usuario logueado (session), que es el mismo dato que usan los cases de apis/backend.php
// ($usuario_registro = $_SESSION["usu_usuario"]). Se resuelve aqui para que ningun llamado de este archivo
// termine guardando un usuario vacio o generico.
// ----------------------------------------------------------------------------------------------------------
function f_UsuarioCierreContable($usuario_registro)
{
	$usuario_registro = trim((string) $usuario_registro);

	if (strlen($usuario_registro) == 0 && isset($_SESSION['usu_usuario'])) {
		$usuario_registro = trim((string) $_SESSION['usu_usuario']);
	}

	return $usuario_registro;
}

// ----------------------------------------------------------------------------------------------------------
// Registra en consolidado_lotes_cierrecontable el lote indicado cuando todavia no tiene registro.
//
// Se ejecuta antes de imprimir el ticket: si el lote nunca paso por el cierre contable no tendria numero de
// ticket y el comprobante saldria en blanco. Reutiliza las mismas funciones que aplica el backend:
//   - 1er tramo: f_MigrarLotes_CierreContable (mismo paso final de cerrar_lote_desde_primer_tramo de
//     apis/backend.php, que ademas crea el registro en despachos_primertramo_validaciondatos; aqui ese
//     registro ya existe porque el ticket se imprime sobre el).
//   - 2do tramo: cerrar_lote_desde_segundo_tramo, que marca el lote como cerrado y lo migra.
// En ambos casos la migracion genera el numero de ticket; si el registro ya existia pero su
// num_ticketbalanza estaba vacio, se genera con f_GenerarNumeroTicketCierreContable.
//
// El proceso es idempotente: si el lote ya estaba registrado no se vuelve a migrar, por lo que tampoco se
// altera el usuario ni la fecha con que se cerro originalmente.
//
// Devuelve el Id del registro en consolidado_lotes_cierrecontable, o 0 si el lote no se pudo registrar.
// ----------------------------------------------------------------------------------------------------------
function f_RegistrarLoteEnCierreContableSiFalta($enlace, $id_lote, $id_tipoingreso, $g_fecha, $usuario_registro)
{
	$id_lote = intval($id_lote);
	$id_tipoingreso = intval($id_tipoingreso);
	$usuario_registro = f_UsuarioCierreContable($usuario_registro);

	if ($id_lote == 0) {
		return 0;
	}

	$q_buscar = "SELECT Id, num_ticketbalanza
					FROM consolidado_lotes_cierrecontable
					WHERE id_registro = " . $id_lote . "
						AND id_tipoingreso = " . $id_tipoingreso . "
					ORDER BY Id
					LIMIT 1";

	$id_consolidado = 0;
	$ticket = '';

	if ($res_buscar = mysqli_query($enlace, $q_buscar)) {
		if ($row_buscar = mysqli_fetch_array($res_buscar)) {
			$id_consolidado = intval($row_buscar["Id"]);
			$ticket = trim((string) $row_buscar["num_ticketbalanza"]);
		}
	}

	// El lote no tiene registro en el cierre contable: se registra y se genera su ticket
	if ($id_consolidado == 0) {
		if ($id_tipoingreso == 2) {
			cerrar_lote_desde_segundo_tramo($enlace, $id_lote, $g_fecha, $usuario_registro);
		} else {
			f_MigrarLotes_CierreContable($enlace, 1, $id_lote, $g_fecha, $usuario_registro);
		}

		if ($res_buscar = mysqli_query($enlace, $q_buscar)) {
			if ($row_buscar = mysqli_fetch_array($res_buscar)) {
				$id_consolidado = intval($row_buscar["Id"]);
				$ticket = trim((string) $row_buscar["num_ticketbalanza"]);
			}
		}
	}

	// El registro existe pero nunca se le genero el numero de ticket
	if ($id_consolidado > 0 && strlen($ticket) == 0) {
		f_GenerarNumeroTicketCierreContable($enlace, $id_consolidado);
	}

	return $id_consolidado;
}

// ----------------------------------------------------------------------------------------------------------
// Renumera los tickets de balanza (DDMMYY-NNN) de una fecha para que sigan el orden real de pesaje.
//
// Motivo: el numero de ticket se genera al cerrar el lote y toma el primer correlativo libre del dia
// (ver f_GenerarNumeroTicketCierreContable), no el siguiente al ultimo pesado. Como el cierre contable no
// ocurre necesariamente en el mismo orden en que los lotes entran a la balanza, la numeracion queda
// desalineada frente a la secuencia real de pesaje: por ejemplo el ticket ...-012 queda entre el ...-005 y
// el ...-006, cuando en realidad le corresponde el ...-006.
//
// Que hace: recibe el lote que se va a imprimir, toma la fecha en que empezo su pesaje, recupera todos los
// tickets de esa fecha ya ordenados por fecha de inicio de pesaje y reescribe num_ticketbalanza de
// consolidado_lotes_cierrecontable con el correlativo que le corresponde a cada posicion. Al ejecutarse antes
// de la consulta principal del modulo, el ticket del lote observado sale ya con su numero correcto y el
// resto de tickets del dia tambien queda ordenado.
//
// Si el lote observado aun no tiene registro en consolidado_lotes_cierrecontable, primero se registra con
// f_RegistrarLoteEnCierreContableSiFalta para que exista su ticket, y recien despues se renumera el dia.
//
// Precauciones:
//   - El dia a renumerar es la fecha de pesaje (fecha_ingresobalanza), que es el mismo dia que usa
//     f_GenerarNumeroTicketCierreContable para asignar el correlativo. No se filtra por la fecha de
//     consolidacion porque un mismo dia de pesaje puede quedar repartido en varios dias de consolidacion
//     (lotes que se cierran al dia siguiente): numerando solo una parte se repetirian correlativos ya emitidos.
//   - Por lo mismo, un ticket cuyo prefijo no corresponde al dia se deja intacto y no consume correlativo.
//   - Los tickets cuyo lote origen ya no existe se renumeran al final del dia (no se sabe cuando se pesaron),
//     para que no repitan el correlativo de otro lote.
//   - El correlativo se arma con 3 digitos (mismo formato de f_GenerarNumeroTicketCierreContable), por lo que
//     todo el dia queda con el mismo formato.
//   - Solo se actualizan los registros cuyo numero cambia y cada numero se escribe una sola vez, por lo que la
//     funcion es idempotente: volver a imprimir un ticket no altera nada.
//
// Parametros:
//   $enlace         - conexion mysqli
//   $id_md5         - MD5 del Id del lote que se esta imprimiendo
//   $id_tipoingreso - 1 = primer tramo (despachos_primertramo_validaciondatos)
//                      2 = segundo tramo (despachos_segundotramo_distribucion_lotes)
//   $g_fecha        - fecha y hora del cierre. Si se envia vacia se usa la actual.
//   $usuario_registro- usuario que queda registrado en el cierre. Si se envia vacio se usa el usuario
//                      logueado (session), igual que hace apis/backend.php.
//
// Devuelve el numero de ticket ya corregido del lote observado, o '' si el lote aun no tiene ticket.
// ----------------------------------------------------------------------------------------------------------
function f_RenumerarTicketsBalanzaPorOrdenPesaje($enlace, $id_md5, $id_tipoingreso, $g_fecha = '', $usuario_registro = '')
{
	$id_tipoingreso = intval($id_tipoingreso);
	$id_md5 = mysqli_real_escape_string($enlace, (string) $id_md5);

	// Usuario que se guarda en el cierre: el usuario logueado (session). Si la pagina se abriera sin sesion
	// se recurre al usuario que registro el pesaje del lote, nunca a un usuario generico.
	$usuario_registro = f_UsuarioCierreContable($usuario_registro);

	// 1. Localiza el lote que se va a imprimir, la fecha en que empezo su pesaje y, como respaldo del
	//    usuario, quien registro el pesaje del lote.
	if ($id_tipoingreso == 1) {
		$q_lote = "SELECT val.Id, val.lote_pesoinicial_fechahoraregistro AS FECHA_INICIO_PESAJE,
						(SELECT tk.usuario_registro
							FROM correlativo_ticketsbalanza tk
							WHERE tk.id_lote = val.lote_id_lote AND tk.is_primertramo = 1
							ORDER BY tk.Id DESC
							LIMIT 1) AS USUARIO_PESISTA
						FROM despachos_primertramo_validaciondatos val
						WHERE MD5(val.Id) = '" . $id_md5 . "'
						LIMIT 1";
	} else {
		$q_lote = "SELECT lot.Id, lot.peso_tara_fechahoraregistro AS FECHA_INICIO_PESAJE,
						lot.peso_tara_usuarioregistro AS USUARIO_PESISTA
						FROM despachos_segundotramo_distribucion_lotes lot
						WHERE MD5(lot.Id) = '" . $id_md5 . "'
						LIMIT 1";
	}

	$id_lote_observado = 0;
	$fecha_pesaje = '';
	$usuario_pesista = '';

	if ($res_lote = mysqli_query($enlace, $q_lote)) {
		if ($row_lote = mysqli_fetch_array($res_lote)) {
			$id_lote_observado = intval($row_lote["Id"]);
			$fecha_pesaje = trim((string) $row_lote["FECHA_INICIO_PESAJE"]);
			$usuario_pesista = trim((string) $row_lote["USUARIO_PESISTA"]);
		}
	}

	if ($id_lote_observado == 0 || strlen($fecha_pesaje) == 0) {
		return '';
	}

	if (strlen(trim((string) $usuario_registro)) == 0) {
		$usuario_registro = $usuario_pesista;
	}

	if (strlen(trim((string) $g_fecha)) == 0) {
		$g_fecha = date('Y-m-d H:i:s');
	}

	// 2. Dia a renumerar: la fecha del pesaje del lote. Si la fecha no es valida (por ejemplo '0000-00-00' en
	//    lotes que aun no pasaron por balanza) no hay nada que ordenar.
	if (!preg_match('/^(\d{4})-(\d{2})-(\d{2})/', $fecha_pesaje, $partes)) {
		return '';
	}

	if (!checkdate(intval($partes[2]), intval($partes[3]), intval($partes[1]))) {
		return '';
	}

	$fecha_dia = $partes[1] . '-' . $partes[2] . '-' . $partes[3];

	// Prefijo del dia (DDMMYY) con el mismo criterio de f_GenerarNumeroTicketCierreContable
	$prefijo_dia = $partes[3] . $partes[2] . substr($partes[1], 2);

	// 3. Si el lote aun no paso por el cierre contable se registra aqui, para que exista su ticket y pueda
	//    entrar en la renumeracion del dia.
	f_RegistrarLoteEnCierreContableSiFalta($enlace, $id_lote_observado, $id_tipoingreso, $g_fecha, $usuario_registro);

	// 4. Tickets de ese dia, ordenados por la hora en que empezo el pesaje de cada lote. La tercera rama
	//    recoge los tickets cuyo lote origen ya no existe (programaciones eliminadas): si se dejaran fuera,
	//    esos registros conservarian un correlativo viejo y volverian a repetir el de otro lote del dia.
	$q_tickets_dia = "
					SELECT 'primer_tramo' AS tramo,
							1 AS id_tipoingreso,
							val.Id AS id_lote,
							cr.Id AS id_ticket,
							cr.num_ticketbalanza AS ticket,
							val.lote_pesoinicial_fechahoraregistro AS fecha_inicio_pesaje
					FROM despachos_primertramo_validaciondatos val
					INNER JOIN consolidado_lotes_cierrecontable cr ON
						cr.id_registro = val.Id AND cr.id_tipoingreso = 1
					WHERE cr.fecha_ingresobalanza = '" . $fecha_dia . "'

					UNION ALL

					SELECT 'segundo_tramo' AS tramo,
							2 AS id_tipoingreso,
							lot.Id AS id_lote,
							cr.Id AS id_ticket,
							cr.num_ticketbalanza AS ticket,
							lot.peso_tara_fechahoraregistro AS fecha_inicio_pesaje
					FROM despachos_segundotramo_distribucion_lotes lot
					INNER JOIN consolidado_lotes_cierrecontable cr ON
						cr.id_registro = lot.Id AND cr.id_tipoingreso = 2
					WHERE cr.fecha_ingresobalanza = '" . $fecha_dia . "'

					UNION ALL

					SELECT 'sin_registro_origen' AS tramo,
							cr.id_tipoingreso,
							cr.id_registro AS id_lote,
							cr.Id AS id_ticket,
							cr.num_ticketbalanza AS ticket,
							NULL AS fecha_inicio_pesaje
					FROM consolidado_lotes_cierrecontable cr
					WHERE cr.fecha_ingresobalanza = '" . $fecha_dia . "'
						AND NOT EXISTS (
								SELECT 1 FROM despachos_primertramo_validaciondatos val
								WHERE cr.id_tipoingreso = 1 AND val.Id = cr.id_registro
							)
						AND NOT EXISTS (
								SELECT 1 FROM despachos_segundotramo_distribucion_lotes lot
								WHERE cr.id_tipoingreso = 2 AND lot.Id = cr.id_registro
							)

					ORDER BY (fecha_inicio_pesaje IS NULL OR fecha_inicio_pesaje < '2000-01-01'),
								fecha_inicio_pesaje ASC";

	// 5. Renumera. El bloqueo evita que dos impresiones simultaneas del mismo dia renumeren con
	//    posiciones distintas y dejen tickets duplicados o saltados.
	$nombre_lock = 'renumerar_ticketbalanza_' . $prefijo_dia;
	$tiene_lock = false;

	if ($res_lock = mysqli_query($enlace, "SELECT GET_LOCK('" . $nombre_lock . "', 5) AS LOCK_OK")) {
		if ($row_lock = mysqli_fetch_assoc($res_lock)) {
			$tiene_lock = (intval($row_lock["LOCK_OK"]) == 1);
		}
	}

	$res_tickets_dia = mysqli_query($enlace, $q_tickets_dia);

	$correlativo = 0;
	$ticket_observado = '';

	if ($res_tickets_dia) {
		while ($row_ticket = mysqli_fetch_array($res_tickets_dia)) {
			$ticket_actual = trim((string) $row_ticket["ticket"]);
			$pos = strrpos($ticket_actual, '-');
			$prefijo_actual = ($pos !== false) ? substr($ticket_actual, 0, $pos) : '';

			// Los tickets de otro dia se dejan intactos y no consumen correlativo
			if (strlen($prefijo_actual) > 0 && $prefijo_actual != $prefijo_dia) {
				continue;
			}

			$correlativo++;

			$ticket_nuevo = $prefijo_dia . '-' . str_pad($correlativo, 3, '0', STR_PAD_LEFT);

			if ($ticket_nuevo != $ticket_actual) {
				$q_update = "UPDATE consolidado_lotes_cierrecontable
								SET num_ticketbalanza = '" . mysqli_real_escape_string($enlace, $ticket_nuevo) . "'
							WHERE Id = " . intval($row_ticket["id_ticket"]);
				mysqli_query($enlace, $q_update);
			}

			if (intval($row_ticket["id_lote"]) == $id_lote_observado
					&& intval($row_ticket["id_tipoingreso"]) == $id_tipoingreso) {
				$ticket_observado = $ticket_nuevo;
			}
		}

		mysqli_free_result($res_tickets_dia);
	}

	if ($tiene_lock) {
		mysqli_query($enlace, "SELECT RELEASE_LOCK('" . $nombre_lock . "')");
	}

	return $ticket_observado;
}

function cerrar_lote_desde_segundo_tramo($enlace, $id_lote_a_cerrar, $g_fecha, $usuario_registro)
{
    // Grabando cierre
    $q_update = "UPDATE despachos_segundotramo_distribucion_lotes";
    $q_update .= "  SET is_cerradolote = 1, ";
    $q_update .= "			cerradolote_fechahoraregistro = '" . $g_fecha . "', ";
    $q_update .= "			cerradolote_usuarioregistro = '" . $usuario_registro . "'";
    $q_update .= " WHERE Id = " . $id_lote_a_cerrar . "";

    mysqli_query($enlace, $q_update);

    // Migrando Lotes cerrados a la tabla de datos Consolidados
    f_MigrarLotes_CierreContable($enlace, 2, $id_lote_a_cerrar, $g_fecha, $usuario_registro);
}
