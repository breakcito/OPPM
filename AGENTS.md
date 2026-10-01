## Breve contexto
Este sistema esta hecho para una planta de compra y venta de mineral para la empresa OPPM, entre otros servicios como el acopio, arrumaje y chancado de mineral.

## Funcionamiento interno
El sistema funciona como de forma monolitica. Cada modulo/vista es un archivo /.php y en algunos casos tiene un archivo mas solo de javascript. Cada modulo usa jquery para controlar el front e interacturar con el backend. Se estila con bootstrap. En todos los modulos se importa un archivo llamado auxiliares.php el cual contiene el header y menu de navegacion en una variable, lo que permite que sea reutilizable esto en todos los modulos, esto provoca que el conteo de divs nunca encaje en cada modulo. En adición, todos los módulos del front (salvo uno que otro que lo quitó) se aplica un zoom del 80% lo que provoca que todo en la pagina se vea un poco más pequeño, esa decisión provoca algunos errores como los select desviados y que en la practica el tamaño de cualquier elemento se vea mas pequeño. El backend esta constituido por un solo archivo backend.php el cual tiene un unico endpoint por metodo POST el cual por regla siempre debe recibir el parametro "accion", este parametro entra en un switch-case y segun coincida ejecuta el proceso que se encuentra de ese case. La bd es mysql. Aunque el codigo es un caos, se debe procurar tener ordenado lo nuevo que se vaya a crear y antes de tocar algo se debe revisar como funciona y entender el proceso para que se tenga nocion de como proceder. 

Archivo backend: \apis\backend.php

## Reglas para IA
- TODO CAMBIO QUE HAGAS EN LA BD, como añadir campos a una tabla o tablas nuevas, crear un script de cambios.sql con el script en sql claro y practico a ejecutar en la base de datos de produccion para poder actualizara despues tambien.

- NO HAGAS CAMBIOS EN GIT QUE AFECTEN AL ESTADO DEL REPOSITORIO O RAMA, como stash, commits, push, revert, etc.

==================================================

Script de impresion: C:\wamp64\www\oppmerp\print_liquidaciontransporte_comercializacion.php
- A diferencia el modulo C:\wamp64\www\oppmerp\despachos_transporteregistro.php donde en la columa de Código si se ve completo utilizando el case de get_GestionTransporte_ListaUnidades y lo coloca en la segunda columa :

------------------------------------------
$html .= '  <td style="border: solid; border-width: 1px; border-color: #D9D9D9; vertical-align: middle; text-align: center; font-weight: bold;">';
					$html .= '    ' . $row_datos["codigo_despacho"];

					if ($row_datos["ID_MODALIDAD_ENVIO"] == 6 || $row_datos["ID_MODALIDAD_ENVIO"] == 3 || $row_datos["ID_MODALIDAD_ENVIO"] == 4 || $row_datos["ID_MODALIDAD_ENVIO"] == 5) {
						$html .= ' / ' . $row_datos["codigo_despacho_comercializacion"];
					}

					$html .= '  </td>';
------------------------------------------
Sin embargo, aqui C:\wamp64\www\oppmerp\print_liquidaciontransporte_comercializacion.php en el campo de Código de
despacho no sale completo, solo sale la primera parte y ya

Script de impresion:
- C:\wamp64\www\oppmerp\print_programaciondistribucion.php
- C:\wamp64\www\oppmerp\print_programaciondistribucion2.php

Corrige los scripts de impresion, por ejemplo actualmente en el script de print_programaciondistribucion, los cuadros al final se ven mal y el documento se ve roto.

Aqui te comparto evidencias: