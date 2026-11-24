# Cierre de la Actividad 2 — API académica

## Estado de entrega

La implementación integrada cubre autenticación Sanctum, Gates de dashboards, Policies del CRUD, permisos Spatie, las reglas de propiedad/carrera/sede y el listado general para cualquier usuario autenticado. El detalle individual permanece sujeto a Policy. Los resultados ejecutados se registran en `evidence/integrante-5/RESULTADOS.md`.

## Preparar y ejecutar localmente

Requisitos: PHP 8.4.1 o superior para las dependencias bloqueadas, Composer y SQLite. Desde la raíz:

```powershell
composer install
php artisan --version
php artisan migrate:status
php artisan route:list --path=api -v
php artisan test --no-ansi
```

Las pruebas automatizadas usan SQLite `:memory:` según `phpunit.xml`. Para la colección Bruno, usar una base de laboratorio separada, migrarla y sembrarla. No ejecutar `migrate:fresh` contra una base con datos que se quieran conservar.

```powershell
$env:DB_CONNECTION = 'sqlite'
$env:DB_DATABASE = 'database/bruno.sqlite'
php artisan config:clear
php artisan migrate --force
php artisan db:seed --force
php artisan serve
```

En Bruno, abrir `bruno/api-academica` y seleccionar el entorno `local`. Ejecutar los logins de admin, profesor, coordinador, estudiante e inactivo, además de **Registro temporal**. Ejecutar **Autorizacion → Listado administrador** para resolver IDs dinámicos; luego Autorizacion, ABAC y Dashboards. Al final ejecutar **Logout temporal** y **Token temporal revocado**. Limpiar las variables de token guardadas en `environments/local.bru` antes de compartir cambios. No incluir `.env`, tokens o bases de desarrollo.

## Reglas y casos cubiertos

- Cualquier usuario autenticado consulta el listado general completo; sin token, la API responde 401.
- La consulta por ID aplica Policy: administrador puede ver todos; estudiante solo su registro; profesor y coordinador consultan dentro de su carrera.
- Estudiante no puede ver registros ajenos, activos o inactivos; puede consultar su propio registro inactivo.
- Solo administrador crea/elimina; permisos RBAC protegen las operaciones.
- Profesor y coordinador modifican únicamente cuando coinciden carrera y sede. Las otras tres combinaciones responden 403.
- Los dashboards exigen Sanctum y el Gate asignado: administrador para dashboard administrativo y profesor para dashboard docente.
- Validaciones impiden IDs inexistentes/duplicados y evitan modificar `user_id`, `carrera_id` o `sede_id` mediante actualización.

## Respuestas para la reflexión (apartado 35)

1. **Autenticación y autorización:** autenticación identifica quién realiza la solicitud; autorización decide qué acciones puede ejecutar esa identidad. Sanctum autentica el token y Gates, Policies y permisos deciden el acceso.
2. **Uso de Gates:** son adecuados para acciones generales sin un modelo concreto, como acceder a un dashboard o ejecutar una tarea administrativa.
3. **Policies para CRUD:** agrupan las reglas por recurso y acción (`view`, `create`, `update`, `delete`), y permiten comparar al actor con el registro solicitado.
4. **RBAC y ABAC:** RBAC concede capacidades según rol/permisos; ABAC usa atributos del actor y del recurso, como carrera, sede, propiedad o estado.
5. **Límite de `estudiantes.editar`:** el permiso expresa una capacidad general, pero por sí solo no limita qué registros se pueden modificar. La Policy aplica carrera y sede.
6. **Riesgo de aceptar rol en registro público:** una persona podría autoasignarse privilegios elevados. El registro crea la cuenta sin rol privilegiado; la asignación de roles se controla en el servidor.
7. **Autorización en backend:** las interfaces se pueden alterar y las rutas pueden invocarse directamente. El servidor debe verificar cada solicitud.
8. **HTTP 401 y 403:** 401 significa que falta una autenticación válida; 403 significa que la identidad se autenticó, pero no está autorizada para esa acción.

## Discusión ABAC (apartado 19.2)

- Si el profesor cambia de carrera, las siguientes decisiones deben usar la carrera persistida y los permisos deben mantenerse coherentes.
- Si cambia la carrera del estudiante, la autorización se evalúa con el nuevo atributo. El profesor de la carrera anterior deja de poder modificarlo.
- El rol de profesor no delimita el alcance académico: distintos profesores atienden carreras o sedes diferentes.
- Se podrían añadir período lectivo, departamento, estado de matrícula o vínculo docente vigente, siempre que sean reglas de negocio explícitas y verificables.

## Evidencias

El resumen de Bruno CLI y la suite Feature están en `evidence/integrante-5/RESULTADOS.md`. La colección no contiene tokens fijos; los IDs se resuelven dinámicamente desde el listado sembrado.
