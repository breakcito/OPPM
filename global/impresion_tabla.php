<?php

/*
	Utilidades compartidas por los scripts de impresion que arman tablas con dompdf.

	POR QUE EXISTE ESTE ARCHIVO
	----------------------------
	dompdf arma la grilla de una tabla a partir del atributo rowspan de cada celda.
	Si el rowspan declarado no coincide EXACTAMENTE con la cantidad de filas que se
	imprimen para esa unidad, la grilla se desalinea: las celdas siguientes se corren
	de columna y el excedente se dibuja fuera de la tabla, como cajas sueltas pegadas
	al borde derecho (columnas fantasma).

	El rowspan no se puede tomar de un COUNT por SQL porque ese conteo mira todos los
	lotes de la unidad, mientras que el SELECT principal puede devolver otras filas:
	- los LEFT JOIN (validaciondatos, remitentessegundotramo, plantas) se multiplican,
	- el SELECT DISTINCT las colapsa,
	- el mismo lote puede quedar en varias unidades,
	- y el filtro por empresita recorta filas de la misma unidad.

	Por eso el rowspan se calcula en PHP sobre las filas que realmente se van a
	imprimir, agrupando las filas consecutivas de cada unidad.
*/

	if (!function_exists('impresion_agrupar_bloques')) {

		/*
			Genera bloques de filas consecutivas por unidad, respetando el orden en que
			llegaron del SELECT.

			$filas          array con las filas del detalle, ya ordenadas.
			$indice_unidad  mapa id_unidad => correlativo. Se pasa por referencia y se
			                reutiliza entre llamadas (una por empresita) para que la
			                numeracion de UNIDAD siga siendo global en todo el documento.
			$campo_unidad   nombre del campo que identifica la unidad de distribucion.

			Devuelve un array de bloques, cada uno con:
				id_unidad     id de la unidad de distribucion.
				indice_unidad correlativo legible de la unidad (UNIDAD 1, UNIDAD 2, ...).
								Se mantiene estable aunque una misma unidad aparezca
								en mas de un bloque.
				total_filas   cantidad de filas del bloque -> es el valor del rowspan.
				filas         las filas del bloque.
		*/
		function impresion_agrupar_bloques($filas, array &$indice_unidad, $campo_unidad = 'ID_DISTRIBUCIONUNIDAD') {

			$bloques = array();

			foreach ($filas as $fila) {

				$id_unidad = $fila[$campo_unidad];

				if (!isset($indice_unidad[$id_unidad])) {
					$indice_unidad[$id_unidad] = count($indice_unidad) + 1;
				}

				$pos = count($bloques) - 1;

				if ($pos < 0 || $bloques[$pos]['id_unidad'] !== $id_unidad) {
					$bloques[$pos + 1] = array(
						'id_unidad'     => $id_unidad,
						'indice_unidad' => $indice_unidad[$id_unidad],
						'total_filas'   => 0,
						'filas'         => array()
					);

					$pos ++;
				}

				$bloques[$pos]['total_filas'] ++;
				$bloques[$pos]['filas'][] = $fila;
			}

			return $bloques;
		}

	}
