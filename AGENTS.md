## Breve contexto
Este sistema esta hecho para una planta de compra y venta de mineral para la empresa OPPM, entre otros servicios como el acopio, arrumaje y chancado de mineral.

## Funcionamiento interno
El sistema funciona como de forma monolitica. Cada modulo/vista es un archivo /.php y en algunos casos tiene un archivo mas solo de javascript. Cada modulo usa jquery para controlar el front e interacturar con el backend. Se estila con bootstrap. En todos los modulos se importa un archivo llamado auxiliares.php el cual contiene el header y menu de navegacion en una variable, lo que permite que sea reutilizable esto en todos los modulos, esto provoca que el conteo de divs nunca encaje en cada modulo. En adición, todos los módulos del front (salvo uno que otro que lo quitó) se aplica un zoom del 80% lo que provoca que todo en la pagina se vea un poco más pequeño, esa decisión provoca algunos errores como los select desviados y que en la practica el tamaño de cualquier elemento se vea mas pequeño. El backend esta constituido por un solo archivo backend.php el cual tiene un unico endpoint por metodo POST el cual por regla siempre debe recibir el parametro "accion", este parametro entra en un switch-case y segun coincida ejecuta el proceso que se encuentra de ese case. La bd es mysql. Aunque el codigo es un caos, se debe procurar tener ordenado lo nuevo que se vaya a crear y antes de tocar algo se debe revisar como funciona y entender el proceso para que se tenga nocion de como proceder. 

Archivo backend: \apis\backend.php

## Reglas para IA
- TODO CAMBIO QUE HAGAS EN LA BD, como añadir campos a una tabla o tablas nuevas, crear un script de cambios.sql con el script en sql claro y practico a ejecutar en la base de datos de produccion para poder actualizara despues tambien.

- NO HAGAS CAMBIOS EN GIT QUE AFECTEN AL ESTADO DEL REPOSITORIO O RAMA, como stash, commits, push, revert, etc.

==================================================

Modulo de Resumen de Balanza: C:\wamp64\www\oppmerp\resumen_balanza_prev.php

Para aquellos registros cuyo tipo de ingreso es DESPACHO DE MINERAL o RECPECION DE MINERAL, se permite modificar la informacion de las fechas de pesaje y pesos de cada lote y te pide una justificacion y lo guarda en cambios_log de cada tabla correspondiente y automaticamente realiza el proceso de cierre del lote nuevamente para que los datos se actualicen correctamente. Pero cuando se intenta visualizar ese log de cambios aparentemente no hay ningun cambio entre el valor anterior con el nuevo, verfica que se este guardando y detectand correctamente ese cambio

Ademas, necesito que permitas editar el operador de balanza. Para ello, al intentar hacerlo, listar aquellos empleados con esta consulta:

SELECT
    usu.Id AS id_usuario,
    usu.usu_usuario AS usuario,
    CONCAT_WS(
        ' ',
        NULLIF(TRIM(emp.nombres),
        ''),
        NULLIF(TRIM(emp.apellido_paterno),
        ''),
        NULLIF(TRIM(emp.apellido_materno),
        '')
    ) AS empleado
FROM
    tb_usuario usu
INNER JOIN tb_empleados emp ON
    emp.Id = usu.id_empleado
WHERE
    emp.estado = 'A' AND usu.estado = 'A' AND usu.usu_usuario IN ('neuribe', 'rpizarro')
ORDER BY
    emp.nombres ASC;

En el caso de los registros del primer tramo lo puedes editar la tablas de:
- correlativo_ticketsbalanza: en las columna de usuario_registro
para el segundo tramo:
- despachos_segundotramo_distribucion_lotes: en las columas de peso_tara_usuarioregistro, peso_bruto_usuarioregistro
- consolidado_lotes_cierrecontable: en las columnas de fechahora_usuario

Al 'actualizar' toma el campo de 'usuario' de la lista y guardalo en las columnas anteriormente mencionadas. Esto es con la finalidad de que al final estos pueda hacer que se visualice este campo en los tickets contables:

C:\wamp64\www\oppmerp\print_ticketbalanza.php
C:\wamp64\www\oppmerp\print_ticketbalanza_segundotramo.php

Cases relevantes usados del backend:
- get_ListaResumenBalanza -> usado para listar registros de diferentes estapas en el modulo de resumen de balanza, entre ellas, los registros de recepciones y despacho de mineral
- grabar_EditBalanza -> usado para editar la info de lotes. 


Objetivo:
- Permitir editar el operador de balanza, las fechas de pesaje, peso bruto, tara y neto de los registros de recepción de mineral y de despacho de mineral, pidiendo siempre una justificacion/motivo de forma obligatoria, GUARDANDO esa edicion en la columna de 'cambios_log' que se guarda en la tabla de despachos_primertramo_validaciondatos para los lotes de recepcion de mineral y de despachos_segundotramo_distribucion_lotes para lo lotes de despacho de mineral. Haciendo que al actualizar estos campos, se vuelva hacer el cierre contable para que los datos del ticket contable tambien se vean actualizados y flejen los datos actualizados

==============

Modulo de balanza - recepcion de mineral: C:\wamp64\www\oppmerp\pesos_primertramo.php

Actualmente al eliminar un lote, el backend no se preocupa de revertir/eliminar los registros de las otras tablas/procesos, lo que provoca que todo se descuadre ya que luego se crea un nuevo lote y toma este mismo codigo. Corrige ello

Cases del backend relevantes:
- grabar_GestionLotes : usado para registrar los pesos inicial/final que es donde se dispara el cierre contable
- eliminar_Lote : usado para eliminar un lote, pero que no se preocupa por borrar los registros en la tabla de despachos_primertramo_validaciondatos, correlativo_ticketsbalanza y consolidado_lotes_cierrecontable

===================

Funciones relevantes para ambos casos:
C:\wamp64\www\oppmerp\global\funciones_cierrecontable.php