## Breve contexto
Este sistema esta hecho para una planta de compra y venta de mineral para la empresa OPPM, entre otros servicios como el acopio, arrumaje y chancado de mineral.

## Funcionamiento interno
El sistema funciona como de forma monolitica. Cada modulo/vista es un archivo /.php y en algunos casos tiene un archivo mas solo de javascript. Cada modulo usa jquery para controlar el front e interacturar con el backend. Se estila con bootstrap. En todos los modulos se importa un archivo llamado auxiliares.php el cual contiene el header y menu de navegacion en una variable, lo que permite que sea reutilizable esto en todos los modulos, esto provoca que el conteo de divs nunca encaje en cada modulo. En adición, todos los módulos del front (salvo uno que otro que lo quitó) se aplica un zoom del 80% lo que provoca que todo en la pagina se vea un poco más pequeño, esa decisión provoca algunos errores como los select desviados y que en la practica el tamaño de cualquier elemento se vea mas pequeño. El backend esta constituido por un solo archivo backend.php el cual tiene un unico endpoint por metodo POST el cual por regla siempre debe recibir el parametro "accion", este parametro entra en un switch-case y segun coincida ejecuta el proceso que se encuentra de ese case. La bd es mysql. Aunque el codigo es un caos, se debe procurar tener ordenado lo nuevo que se vaya a crear y antes de tocar algo se debe revisar como funciona y entender el proceso para que se tenga nocion de como proceder. 

Archivo backend: \apis\backend.php

## Reglas para IA
- TODO CAMBIO QUE HAGAS EN LA BD, como añadir campos a una tabla o tablas nuevas, crear un script de cambios.sql con el script en sql claro y practico a ejecutar en la base de datos de produccion para poder actualizara despues tambien.

- NO HAGAS CAMBIOS EN GIT QUE AFECTEN AL ESTADO DEL REPOSITORIO O RAMA, como stash, commits, push, revert, etc.

==================================================

Modulo de Proveedores Mineros: C:\wamp64\www\oppmerp\admin_proveedoresmineros.php
Necesito que, cuando se intente registrar un proveedor y se escriba DNI o RUC y sea consultado a la api para rellenar automaticamente sus datos, pero la api de APIS peru falle o ya se haya acabado la cuota, entonces que devuelva un estado 0 y que el modulo detecte esto y diga 'NO ENCONTRADO' para que el usuario mismo escriba la razon social de este proveedor. Por ejemplo actualmente sale este error:

{
    "estado": 1,
    "res": "{\"message\":\"Ha excedido el l\\u00edmite del plan, espere el siguiente mes o p\\u00f3ngase en contacto con soporte t\\u00e9cnico soporte@apisperu.com \\\/ whatsapp +51935600914\"}"
}

Case utilizado para esto:
- get_infocliente

