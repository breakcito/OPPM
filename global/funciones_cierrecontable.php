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
