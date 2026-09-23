1) Estado de referencia actual
Checklist:

 Módulo de producción funcionando con servicio y validación
 Permisos por rol funcionando: admin, operaciones, rider
 Flujo de registro y actualización funcional
 BaseController centraliza sesión, permisos y JSON
 README actualizado con estado actual del proyecto
 Legacy scripts y archivos de prueba fuera del flujo principal

2) Fase de seguridad y estabilidad
Checklist:

 Revisar el flujo real de cada módulo antes de tocarlo
 Mantener rutas actuales y nombres de sesión sin cambios
 No cambiar payloads ni respuestas JSON si ya funcionan
 Probar cada módulo después de cambios menores
 Separar cambios de refactor de cambios de negocio

3) Módulo 1: Producción
Referencia:

ProduccionController.php
ProduccionService.php
ProduccionModel.php
Checklist:

 Validación de payload centralizada en servicio
 Registro y actualización delegados al servicio
 Refactor mantenido sin romper comportamiento
 Revisar reportes y resúmenes para mover lógica compleja fuera del model
 Validar caso edge: payload con strings vacíos, id inválido, estado fuera de rango
 Revisar si getProduccionesConDetalle y getProduccionesFiltradas pueden separarse por responsabilidad
 Revisar getProduccionResumenPorGrupo para mantener contratos estables
 Dejar el controller más fino y solo de orquestación

4) Módulo 2: Auth y usuarios
Referencia:

AuthController.php
UserController.php
User.php
Checklist:

 Revisar flujo de login y logout
 Verificar validación de email/password
 Validar estado activo/inactivo del usuario
 Extraer validaciones de usuario a servicio si crecen
 Mantener sesión y roles como están
 Probar login como admin, operaciones y rider
 Asegurar que no haya cambios en rutas /login, /dashboard, /logout

5) Módulo 3: Dashboard y reportes
Referencia:

DashboardController.php
DashboardModel.php
Checklist:

 Revisar que los resúmenes actuales sigan funcionando
 Separar agregaciones y reportes del CRUD
 Mantener el esquema de respuesta del dashboard
 Verificar roles y permisos del dashboard
 Probar carga de datos para admin y operaciones

6) Módulo 4: Grupos y riders
Referencia:

GruposController.php
RidersController.php
GruposModel.php
RiderModel.php
Checklist:

 Revisar alta/edición de grupos
 Revisar alta/edición de riders
 Validar asignación de grupos y permisos
 Revisar upload de fotos con UploadHelper.php
 Extraer validaciones de payload si crecen
 Mantener relaciones rider-grupo
 Probar flujo admin y operaciones

7) Módulo 5: Tarifas y detalles
Referencia:

TarifasController.php
DetallesController.php
TarifaModel.php
DetalleTarifaModel.php
EncabezadoTarifaModel.php
GrupoEncabezadoTarifaModel.php
Checklist:

 Validar creación/edición de encabezados de tarifa
 Validar creación/edición de detalle de tarifa
 Revisar estados activos/inactivos
 Revisar asociación con grupos
 Extraer validaciones de detalle a servicio
 Probar flujo admin y operaciones
 Mantener rutas y nombres actuales sin romper UI

8) Módulo 6: Turnos y configuración
Referencia:

TurnosController.php
ConfiguracionesController.php
TurnoModel.php
PuntoControlModel.php
TurnoHelper.php
Checklist:

 Revisar carga de turnos
 Revisar cálculo del turno actual
 Revisar configuración de puntos de control
 Mantener estado actual de roles y permisos
 Extraer validación de turnos si se vuelve compleja
 Probar operaciones manuales en UI

9) Limpieza y hardening no bloqueante
Checklist:

 Mover scripts legacy fuera del flujo principal
 Dejar setup.ps1 fuera del ciclo live
 Dejar pruebaapituruc.php como archivo histórico o de pruebas aisladas
 No tocar scripts legacy si aun se usan en algún entorno puntual
 Mantener README como documento de estado y arquitectura
 Usar REFACTOR_PROMPT.md solo para estilos/vistas si se decide hacer refactor de UI más adelante
 Crear una carpeta legacy/ o archive/ si se va a mover contenido antiguo

10) Criterio de aceptación para cada módulo
Checklist:

 Flujo principal sigue funcionando
 El controller sigue siendo una entrada HTTP simple
 La lógica de negocio se mantiene en servicio o helper
 El model no tiene responsabilidades mezcladas
 No se cambian rutas ni session keys sin pruebas
 El rol actual sigue funcionando sin restricciones nuevas
 La UI mantiene el comportamiento actual

11) Recomendación de ejecución
Ejecutar en este orden:

Producción
Auth / usuarios
Dashboard
Grupos / riders
Tarifas
Turnos / configuración
Legacy cleanup
No iniciar con estilos ni scripts viejos. Primero asegurar la base funcional, luego hacer limpieza y mejora.