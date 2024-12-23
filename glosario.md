# WARNING:
## Nota: Todos los usuarios aquí serán modificados por admin siempre, tomar en cuenta si se agregan usuarios puede haber discrepancia

## Proyecto base Inventario

## DB del proyecto
-> grupour1_central_ops

## Tablas que se conservan
-> seccion_marca
-> versiones

=====================================

## Tablas
### Fecha
-> Formatos
    Librería calendar
    Y-m-d Formato para trabajar internamente
    D j \\d\\e F Y // Formato amigable: Vie 21 de Dic 2024
    Librería moment
    YYYY-MM-DDTHH
    Función php
    Y-m-d
### Cargado a
-> Ejemplos
    persona que requiere el efectivo
### Area
-> Estaciones de trabajo
    rancho maravillas
    rancho rivera
    rancho 3 marias
    rancho san judas
    rancho hulefante
    rancho estación las choapas
    rancho estación coatza
### Tipo de gastos
-> Ejemplos
    administrativo  
    operativo
    personal
    mantenimiento
    adelante nomina
    prestamo
    herramientas
    diesel
    combustible
### Concepto
-> Ejemplos
    descripcion
### Quien recibe
-> Ejemplos
    persona que recibe el efectivo (chilango)
### Unidad
-> Ejemplos
    '' o ej. grua 02
### Comprobante de egreso jpg/pdf
-> Ejemplos
    Archivo: factura/nota sin factura/recibo de dinero/nota por facturar/sin comprobante/pendiente/préstamo
### Razón social
-> Ejemplos
    nombres físicos y personas morales a quien se facutra ej. 
### Ingreso
### Egreso
### Saldo

=====================================

### Escenarios
-> [✔] jesus uribe - rancho san judas - personal - personal jesus - señor velador - '' - sin comprobante - '' - 0.00 - 1500 - ?
-> [✔] jose luis hernandez - rancho 3 marias - operativo - jkasbdkjasjdkl - chilango - grua 03 - factura - gruas tou - 0.00 - 2000 - ?

=====================================

## DBs accesibles
-> grupour1_nomina
-> grupour1_inventario

# Type Response;
-> [select_general] getNomina()
-> [update_general] updateUserInventario()

"type" => 
  "SUCCESS"
  "ERROR"

"action" => 
  "SHOW_LOGIN"  MANDAR A LOGIN
  "CONTINUE"    CONTINUAR FLUJO, AVISAR QUE TODO OK
  "CANCEL"      DETENER FLUJO, AVISAR PORQUE SE DETIENE
  "TRY_AGAIN"   TOMA CAPTURA Y ENVÍALO A SOPORTE SI EL PROBLEMA PERSISTE, DETENER Y RECARGAR PORQUE ALGO FALLO
  "DELETE"      BORRAR REGISTROS
