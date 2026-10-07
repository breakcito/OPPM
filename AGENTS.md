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

Para aquellos registros cuyo tipo de ingreso es RECEPCION DE MINERAL, necesito que al igual que en el modulo de Validacion y Distribucion 'C:\wamp64\www\oppmerp\primertramo_distribucion.php' cuando se modifica la informacion de cada lote utilizando el case de 'update_PrimerTramo_DistribucionDatos' y realiza el proceso de cierre del lote nuevamente para que los datos se actualicen correctamente. Sin embargo, el modulo de validacion y distribucion aunque permite editar y actualiza los datos contables, NO ACTUALIZA LA TABLA DE catalogolotes para que sean equivalentes.

Eso provoca que en el modulo de resumen de balanza, ademas de que no permite modificar el peso NETO cuando en realidad si deberia, no refleja los pesos que realmente deben salir y que son los que si salen en el ticket contable C:\wamp64\www\oppmerp\print_ticketbalanza.php

Cases relevantes usados del backend:
- get_ListaResumenBalanza -> usado para listar registros de diferentes estapas, entre ellas, los registros de recepciones
- grabar_EditBalanza -> usado para editar la info de lotes. 

C:\wamp64\www\oppmerp\global\funciones_cierrecontable.php