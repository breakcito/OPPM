Achivo que recibe todas las solicitudes de todos los modulos mendiante peticiones http POST siempre con el campo de "accion" en el body que indica que accion/caso de uso se debe ejecutar junto a los otros campos propios del caso, ese campo entra en un switch case y segun coincida entra al case:
C:\wamp64\www\oppmerp\apis\backend.php

TODO CAMBIO QUE HAGAS EN LA BD, como añadir campos a una tabla o tablas nuevas, crear un script de cambios.sql con el script en sql claro y practico a ejecutar en la base de datos de produccion para poder actualizara despues tambien.

NO HAGAS CAMBIOS EN GIT QUE AFECTEN AL ESTADO DEL REPOSITORIO O RAMA, como stash, commits, push, revert, etc.

==================================================

Modulo de Programacion de Despachos: C:\wamp64\www\oppmerp\despachossegundotramo_programacion.php

- Actualmente, se generan correctamente los codigos de despacho a nivel de cabecera y por cada lote. Sin embargo, para la planta de Colbibri, no esta generando el codigo de planta automaticamente. Tiempo atras si lo hacia, en la imagen que te comparto, se ve que en los registros anteriores el campo de codigo de planta generaba un correlativo cuya sintaxis es:
008-<numero correlativo>
Sin ningun filtro o reseteo, solo algo consecutivo por cada lote. Sin embargo, en la imagen tambien se ve que a partir de los nuevo registros de este mes, ya no se generan. Seguramente es por los cambios que se han ido dando al registrar despachos y la generacion de sus codigos. Corrige ello. 

Cases utilizados relevantes para revisar y corregir este caso:
- confirmar_ProgramacionLote
- get_DespachosProgramacion_ListaProgramaciones
- get_DespachosProgramacion_ListaDistribuciones