## Breve contexto
Este sistema esta hecho para una planta de compra y venta de mineral para la empresa OPPM, entre otros servicios como el acopio, arrumaje y chancado de mineral.

## Funcionamiento interno
El sistema funciona como de forma monolitica. Cada modulo/vista es un archivo /.php y en algunos casos tiene un archivo mas solo de javascript. Cada modulo usa jquery para controlar el front e interacturar con el backend. Se estila con bootstrap. En todos los modulos se importa un archivo llamado auxiliares.php el cual contiene el header y menu de navegacion en una variable, lo que permite que sea reutilizable esto en todos los modulos, esto provoca que el conteo de divs nunca encaje en cada modulo. En adición, todos los módulos del front (salvo uno que otro que lo quitó) se aplica un zoom del 80% lo que provoca que todo en la pagina se vea un poco más pequeño, esa decisión provoca algunos errores como los select desviados y que en la practica el tamaño de cualquier elemento se vea mas pequeño. El backend esta constituido por un solo archivo backend.php el cual tiene un unico endpoint por metodo POST el cual por regla siempre debe recibir el parametro "accion", este parametro entra en un switch-case y segun coincida ejecuta el proceso que se encuentra de ese case. La bd es mysql. Aunque el codigo es un caos, se debe procurar tener ordenado lo nuevo que se vaya a crear y antes de tocar algo se debe revisar como funciona y entender el proceso para que se tenga nocion de como proceder. 

Archivo backend: \apis\backend.php

## Reglas para IA
- TODO CAMBIO QUE HAGAS EN LA BD, como añadir campos a una tabla o tablas nuevas, crear un script de cambios.sql con el script en sql claro y practico a ejecutar en la base de datos de produccion para poder actualizara despues tambien.

- NO HAGAS CAMBIOS EN GIT QUE AFECTEN AL ESTADO DEL REPOSITORIO O RAMA, como stash, commits, push, revert, etc.

==================================================

Modulo de Programación de Despachos:
    C:\wamp64\www\oppmerp\despachos_transporteregistro.php

Actualmente, al registrar una programacion de despacho, la generacion de codigos de "codigo_planta" no funciona bien, incluso si no se adapta a la realidad del usuario, lo puede editar pero esto no termina afectando correctamente para el siguiente calculo de codigos de "codigo_planta" por cada lote. 

Ademas, cuando luego de haber registrado un despacho, se agrega un nuevo lote a este despacho y se calculan los codigos, el campo de "codigo_planta" de la tabla de despachos_segundotramo_programacion_detalle se asigna el ultimo codigo a partir del ultimo anterior de algun lote del despacho. Sin embargo, estos codigos deben reordenarse en base al codigo de los lotes. Por ejemplo, si un despacho tiene estos lotes con sus codigos de planta asi:

DESPACHO A
LOTE    |   CÓDIGO DE PLANTA
----------------------------
261611  |   008-3743
261613  |   008-3744
261615  |   008-3745
261617  |   008-3746
261627  |   008-3747

DESPACHO B
LOTE    |   CÓDIGO DE PLANTA
----------------------------
261629  |   008-3748
261631  |   008-3749
261633  |   008-3750
261635  |   008-3751
261637  |   008-3752

Y decido agregar un lote (261619) mas al despacho A, entonces los codigos deben quedar asi:

DESPACHO A
LOTE    |   CÓDIGO DE PLANTA
----------------------------
261611  |   008-3743
261613  |   008-3744
261615  |   008-3745
261617  |   008-3746
261619  |   008-3747 -- NUEVO LOTE, SE ACTUALIZAN LOS CODIGOS HACIA DELANTE
261627  |   008-3748 

DESPACHO B
LOTE    |   CÓDIGO DE PLANTA
----------------------------
261629  |   008-3749
261631  |   008-3750
261633  |   008-3751
261635  |   008-3752
261637  |   008-3753

Si decido agregar otro despacho C quedaria asi

DESPACHO C
LOTE    |   CÓDIGO DE PLANTA
----------------------------
261639  |   008-3754
261641  |   008-3755
261643  |   008-3756

Si edito el codigo del despacho B del lote 261633 de 008-3751 a 008-3755 entonces el despacho B y C quedarian asi:

DESPACHO B
LOTE    |   CÓDIGO DE PLANTA
----------------------------
261629  |   008-3749
261631  |   008-3750
261633  |   008-3755 -> aqui edité, a partir de aqui se modifica
261635  |   008-3756
261637  |   008-3757

DESPACHO C
LOTE    |   CÓDIGO DE PLANTA
----------------------------
261639  |   008-3758
261641  |   008-3759
261643  |   008-3760


Si quito el lote 261631 del despacho B de 008-3750 a 008-3753 entonces el despacho B y C quedarian asi:

DESPACHO B
LOTE    |   CÓDIGO DE PLANTA
----------------------------
261629  |   008-3749
261631  |   008-3753 -> Se actualiza y se aqui en adelante
261633  |   008-3754 
261635  |   008-3755
261637  |   008-3756

DESPACHO C
LOTE    |   CÓDIGO DE PLANTA
----------------------------
261639  |   008-3757
261641  |   008-3758
261643  |   008-3759

Considera ignorar los codigos de la tabla de correlativos sin usar, puesto que antes, cuando se eliminaba un lote de un despacho o se eliminaba un despacho, estos codigos quedaban en el aire lo que hacia que cuando se registre otro despacho o se agregue otro lote al despacho, quede un hueco al generar estos codigos debido a los anteriores eliminados

Cases relevantes a revisar:
    get_Programacion_CodigosSugeridos
    get_LotesProgramadosParaDespacho
    confirmar_ProgramacionLote
    confirmar_ProgramacionLote_AddLote
    get_DespachosProgramacion_ListaProgramaciones
    get_DespachosProgramacion_ListaDistribuciones
    cierre_LiquidacionTransporte
    reabrir_LiquidacionTransporte
    get_GestionTransporte_ListaUnidades_InformacionLotes
    get_TarifasTransporte_ListaPorPlantaCoordinador