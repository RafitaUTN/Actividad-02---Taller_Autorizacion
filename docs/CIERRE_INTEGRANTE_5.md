# Cierre de actividad — Integrante 5

> **Nota de actualización:** el texto que sigue conserva instrucciones y resultados históricos. Para el estado actual, incluida la corrección de la validación y las pruebas ejecutadas después del merge, consultar `docs/INTEGRACION_NESTOR.md` y `evidence/integrante-5/RESULTADOS.md`. No ejecutar `migrate:fresh` sobre bases existentes ni asumir IDs fijos. Las respuestas de reflexión de este documento son material previo, no respuestas personales verificadas de Néstor.

Este paquete prepara las pruebas formales, la colección de Bruno, las respuestas de reflexión y el registro de evidencias. Corresponde ejecutarlo como último paso, una vez que el equipo integre las ramas de autorización y CRUD.

## Dependencias de integración

1. Integrar la rama `feature/policies-abac` (commit local del integrante 4: `499f1bb`).
2. Integrar `feature/gates-crud` del integrante 3.
3. Verificar que el integrante 1 registre el alias Spatie `permission` en `bootstrap/app.php`. En la copia de trabajo revisada, el callback de `withMiddleware` estaba vacío; se añadió solo en una rama temporal para ejecutar la prueba integrada.
4. Resolver con el integrante 3 una validación defectuosa que salió durante QA: enviar `{"sede_id":null}` a `PUT /api/estudiantes/1` pasa la regla `prohibited`, intenta guardar `null` y responde 500 por restricción `NOT NULL`. El resultado correcto es 422 y el registro debe conservar la sede. Cambiar las reglas de `user_id`, `carrera_id` y `sede_id` a `missing` (o validar y excluir explícitamente esos campos) y agregar la regresión antes de la entrega. Este cambio pertenece al controlador CRUD; no se alteró aquí para respetar la división de responsabilidades.

## Preparar SQLite y ejecutar la suite

Desde PowerShell en la raíz del proyecto:

```powershell
composer install
if (-not (Test-Path .env)) { Copy-Item .env.example .env; php artisan key:generate }
if (-not (Test-Path database/database.sqlite)) { New-Item database/database.sqlite -ItemType File }
```

Confirmar en `.env` `DB_CONNECTION=sqlite` y que no se haya establecido un `DB_DATABASE` incompatible. En la base local de laboratorio:

```powershell
php artisan config:clear
php artisan migrate:fresh --seed
php artisan route:list --path=api -v
php artisan test
```

`migrate:fresh --seed` elimina las tablas de la base configurada: usarlo solamente en la base de práctica desechable. La suite actual se ejecutó sobre SQLite `:memory:` y contra la rama temporal que integró el CRUD del integrante 3 y registró el alias middleware necesario para probarlo. Resultado: **18 pruebas, 45 aserciones aprobadas** antes de agregar la regresión pendiente del valor nulo.

## Ejecutar la colección Bruno

1. Abrir Bruno y seleccionar **Open Collection**.
2. Elegir la carpeta `bruno/api-academica`.
3. Iniciar Laravel con `php artisan serve` y seleccionar el entorno `local` (`http://127.0.0.1:8000`).
4. En la carpeta **Autenticacion**, ejecutar primero los cinco inicios de sesión. Cada respuesta guarda su token en una variable local del entorno.
5. Ejecutar **Autorizacion** y **ABAC**. Los requests usan IDs 1–5 creados por los seeders; si los IDs locales son otros, corregirlos en los URLs después de revisar `php artisan db:seed`.
6. Guardar capturas de los resultados en `evidence/integrante-5/`. Las credenciales incluidas son cuentas ficticias de laboratorio. No capturar ni exportar encabezados `Authorization`, tokens o archivos `.env`.

Los PUT permitidos envían el mismo nombre actual y no alteran los datos sembrados. Las peticiones de denegación deben devolver 403 sin cambiar el registro.

## Casos cubiertos

- Token faltante: 401.
- Administrador lista todos los estudiantes; profesor/coordinador ven únicamente su carrera; estudiante ve únicamente su registro.
- Estudiante: consulta propia permitida, ajeno activo e inactivo denegados, propio inactivo permitido según el acuerdo del equipo.
- Profesor/coordinador: lectura de otra sede dentro de su carrera permitida; lectura de otra carrera denegada.
- ABAC para actualizar: misma carrera y sede permitida; misma carrera con otra sede, carrera distinta con misma sede y carrera/sede distintas denegadas.
- La suite Feature además verifica crear/eliminar temporalmente con administrador, falta de rol, permisos y que una edición denegada no modifique datos.

## Respuestas para la reflexión (apartado 35)

1. **Autenticación y autorización:** la autenticación confirma quién realiza la solicitud; la autorización decide qué puede hacer esa identidad sobre una ruta o recurso. En el proyecto, Sanctum autentica el token y las reglas de Laravel/Spatie autorizan la operación.
2. **Cuándo usar Gates:** para acciones generales sin un modelo concreto, como entrar a un dashboard o ejecutar una tarea administrativa global.
3. **Por qué Policies para CRUD:** agrupan las reglas de un modelo y separan consulta, creación, edición y eliminación; cada método puede inspeccionar al usuario y el registro afectado.
4. **RBAC y ABAC:** RBAC concede capacidades por roles/permisos; ABAC decide con atributos como carrera, sede, estado y propietario. La edición requiere ambos: permiso y atributos compatibles.
5. **Por qué `estudiantes.editar` no basta:** el permiso expresa una capacidad general. Sin comparar el recurso, permitiría modificar registros de otras carreras o sedes. La Policy limita el alcance.
6. **Riesgo de aceptar el rol en registro público:** un cliente podría asignarse administrador o profesor y obtener privilegios. El rol debe asignarse de forma controlada en el servidor.
7. **Por qué autorizar en backend:** el cliente puede modificarse o evitarse; ocultar un botón no impide llamadas HTTP directas. El servidor debe validar cada operación.
8. **HTTP 401 y 403:** 401 indica que falta autenticación válida; 403 indica que el usuario sí fue identificado pero no tiene autorización para esa acción.

## Discusión ABAC (apartado 19.2)

- **Si cambia la carrera del profesor:** las decisiones siguientes comparan el nuevo valor persistido. El sistema debe mantener actualizado el atributo y sus permisos de forma coherente.
- **Si cambia la carrera del estudiante:** las siguientes operaciones contra su registro reflejan la nueva carrera; una edición queda denegada al profesor cuya carrera ya no coincide.
- **Por qué el rol no basta:** identifica el tipo de actor, pero no el alcance de sus recursos; dos profesores pueden tener permisos de edición y atender carreras distintas.
- **Otros atributos posibles:** periodo lectivo, sede, facultad/departamento, estado de matrícula o vínculo docente vigente. Deben definirse y probarse como reglas de negocio explícitas.

## Único paso final asignado al integrante 5

Después de integrar las dependencias de arriba, ejecutar la colección completa en Bruno, guardar las capturas sin tokens en `evidence/integrante-5/` y completar el acta de resultados real en `evidence/integrante-5/RESULTADOS.md`. La colección, la suite y las respuestas ya están preparadas; el acta debe reflejar únicamente lo que Bruno haya ejecutado contra la rama final.
