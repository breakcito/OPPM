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

Para aquellos registros cuyo tipo de ingreso es para DESPACHO DE MINERAL que provienen de la tabla de despachos_segundotramo_distribucion_lotes, si es que tiene el campo de peso_tara o peso_bruto llenos, entonces en el grupo de la tabla 'tbl_detalle' en el grupo de 'Información de Lotes' en la subcolumna de 'Cód. Lote' muestra un boton abajo del codigo que diga 'Limpiar pesos' que lo que haga sea colocar los siguientes campos de ese registro de la tabla mencionada en null:

    peso_tara
    peso_tara_fechahoraregistro
    peso_tara_usuarioregistro
    peso_bruto
    peso_bruto_fechahoraregistro
    peso_bruto_usuarioregistro
    peso_neto

Recuerda sincronizar los datos del ticket contable para que esta informacion no vaya a ser herrada o diferente.

Cases relevantes usados del backend:
- get_ListaResumenBalanza -> usado para listar registros de diferentes estapas, entre ellas, los registros de despacho
- grabar_EditBalanza -> usado para editar la info de lotes. 