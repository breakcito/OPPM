Achivo que recibe todas las solicitudes de todos los modulos mendiante peticiones http POST siempre con el campo de "accion" en el body que indica que accion/caso de uso se debe ejecutar junto a los otros campos propios del caso, ese campo entra en un switch case y segun coincida entra al case:
C:\wamp64\www\oppmerp\apis\backend.php

TODO CAMBIO QUE HAGAS EN LA BD, como añadir campos a una tabla o tablas nuevas, crear un script de cambios.sql con el script en sql claro y practico a ejecutar en la base de datos de produccion para poder actualizara despues tambien.

NO HAGAS CAMBIOS EN GIT QUE AFECTEN AL ESTADO DEL REPOSITORIO O RAMA, como stash, commits, push, revert, etc.

==================================================

Programacion de despachos: C:\wamp64\www\oppmerp\despachossegundotramo_programacion.php

- Al registrar un despacho con solandra, poder indicar si aplicar una campaña, si es asi, poder elegir la campaña en un select y si no hay campañnas activas que pueda registrar. Guiarse de aqui: C:\wamp64\www\oppmerp\admin_plantas.php
- El codigo por detalle de despacho debe ser igual para otros los otros lotes de la misma empresa VIII o 48. Por ejemplo, aqui, en vez de C576-CO21 debe ser C576-CO20



Otro ejemplo:

CODIGO DESPACHO | CODIGO DETALLE DESPACHO/CODIGO COMERCIALIZACION
CP31-S216   |   CP31-S216-CO10
CP31-S216   |   CP31-S216-CO11
CP31-S217   |   CP31-S217-VII190
CP31-S217   | CP31-S217-VIII91

Y esto debio pasar:

CODIGO DESPACHO | CODIGO DETALLE DESPACHO/CODIGO COMERCIALIZACION
CP31-S216   |   CP31-S216-CO10
CP31-S216   |   CP31-S216-CO10
CP31-S216   |   CP31-S216-VII190
CP31-S216   | CP31-S216-VIII90

Aqui te paso mas informacion que te peude servir:

PREFIJOS POR EMPRESAS

- Para VIII SAC: VIII
- Para 48 SAC: CO

CODIGOS POR PLANTAS

PLANTA: Colibri

- Plantilla: C<numero correlativo de despacho continuo>-<prefijo por empresa><numero correlativo de detalle de despacho por empresa>
- Prefijo: C
- Debe manejar un correlativo continuo sin importar la empresa VII SAC o 48 SAC
- Ejemplo para VIII SAC: C556-VIII128
- Ejemplo para 48 SAC: C556-CO4

|  | Prefij del despacho | Numero correlativo de despacho continuo | Separador | Prefijo por empresa | Numero correlativo de detalle de despacho |
| --- | --- | --- | --- | --- | --- |
| Plantilla | C | N | - | VIII o CO | n |
| Ej. con VIII SAC | C | 560 | - | VIII | 745 |
| Ej. con VIII SAC | C | 560 | - | VIII | 745 |
| Ej. con 48 SAC | C | 560 | - | CO | 739 |
| Ej. con 48 SAC | C | 560 | - | CO | 739 |

PLANTA: Solandra

- Plantilla: CP<numero de la campaña vigente con la planta Solandra>-S<numero correlativo de despacho continuo>-<prefijo por empresa><numero correlativo de detalle de despacho por empresa>
- Sufijo de campaña: CP
- Prefijo: S
- Debe manejar un correlativo continuo sin importar la empresa VII SAC o 48 SAC pero al inicio coloca el numero de la campaña
- Ejemplo para VIII SAC: CP31-S216-VIII89
- Ejemplo para 48 SAC: CP-31-S216-CO10

|  | Prefijo de campaña | Numero de la campaña vigente con la planta solandra | Separador | Prefijo de despacho | Numero correlativo de despacho continuo | Separador | Prefijo por empresa | Numero correlativo de detalle de despacho |
| --- | --- | --- | --- | --- | --- | --- | --- | --- |
| Plantilla | CP | NC | - | S | N | - | VIII o CO | n |
| Ej. 1 con VIII SAC | CP | 31 | - | S | 560 | - | VIII | 745 |
| Ej. 1 con VIII SAC | CP | 31 | - | S | 560 | - | VIII | 745 |
| Ej. 2 con 48 SAC | CP | 31 | - | S | 561 | - | CO | 711 |
| Ej. 2 con 48 SAC | CP | 31 | - | S | 561 | - | CO | 711 |



Modulo:
C:\wamp64\www\oppmerp\despachossegundotramo_programacion.php

Case usado del backend para este proceso:
confirmar_ProgramacionLote
confirmar_ProgramacionLote_AddLote > llama a estas funciones:
-  f_GetCampanaActiva
- f_CalcularSiguienteCorrelativoDespacho
- f_ConstruirCodigoCabecera
- f_GetPrefijoEmpresaDespacho
- f_ConstruirCodigoDetalle


