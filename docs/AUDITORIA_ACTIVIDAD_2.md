# Auditoría del Taller de Autorización

## Alcance

Auditoría de la versión final local contra el anuncio oficial del taller, verificada el 24 de noviembre de 2026. La integración parte del commit de equipo `2161e7d` y añade las correcciones descritas en este documento.

## Requisitos

| Requisito del anuncio | Estado | Revisión |
|---|---|---|
| Laravel 13, Sanctum y SQLite | Conforme | Composer fija Laravel 13; Sanctum está instalado, las nueve migraciones del proyecto están presentes y `migrate:status` indica que corrieron. |
| Registro, login, perfil y logout con tokens | Conforme | Endpoints y pruebas cubren token válido, credenciales erróneas, privilegios no asignables y revocación real. |
| Modelos y relaciones académicas | Conforme | Usuario, estudiante, carrera y sede tienen relaciones y casts requeridos; migraciones guardan carrera/sede en usuarios y estudiantes. |
| Roles/permisos y coordinador | Conforme | Seeder idempotente administra administrador, profesor, estudiante y coordinador bajo guard `web` y permisos del taller. |
| Gate del dashboard administrativo | Conforme | Solo administrador; ruta requiere Sanctum. |
| Actividad 1: dashboard de profesor | Conforme | Solo profesor; pruebas HTTP automatizadas contemplan administrador, profesor, estudiante y usuario sin token. |
| Policies de consulta y CRUD | Conforme | `EstudiantePolicy` verifica permiso, rol, carrera, propiedad y administración; CRUD invoca `Gate::authorize`. |
| Actividad 2: estudiante no consulta registro inactivo ajeno | Conforme | Propiedad estricta impide consultar registros ajenos, activos o inactivos; propietario sí puede consultar su registro inactivo, según acuerdo documentado. |
| Actividad 3: coordinador | Conforme | Seeder asigna ver/editar y pruebas incluyen su acceso sujeto a ABAC. |
| Actividad 4: ABAC por carrera y sede | Conforme | Atributos, relaciones y casts existentes; pruebas cubren las cuatro combinaciones para profesor y coordinador. |
| Listado general para cualquier usuario autenticado | Conforme | `GET /api/estudiantes` exige Sanctum y permite consultar el listado completo; el detalle mantiene su autorización por Policy. |
| Rutas CRUD, middleware RBAC y validaciones | Conforme | CRUD tiene `auth:sanctum`, middleware `permission` y `Gate::authorize`; el listado general aplica la regla expresa del anuncio. La actualización usa `missing` para impedir cambiar o vaciar identidad, carrera y sede. |
| Pruebas automatizadas Feature | Conforme | `php artisan test --no-ansi`: 26 aprobadas, 137 aserciones en PHP 8.5.8 / Laravel 13.43.0. |
| Colección Bruno | Conforme | Bruno CLI 4.3.0 ejecutó 40 de 40 solicitudes: 11 autenticación, 12 autorización, 8 ABAC y 9 dashboards, contra una base SQLite aislada. |
| Evidencias y respuestas de reflexión | Conforme con alcance documentado | El registro de evidencias incluye estados HTTP realmente observados en Bruno CLI. No se generaron capturas de la interfaz gráfica; el resultado CLI se conserva como evidencia reproducible. Las respuestas de reflexión siguen siendo material de apoyo para revisión personal. |

## Listado general y detalle individual

El listado `GET /api/estudiantes` permite a cualquier usuario autenticado consultar todos los registros, como pide el escenario del anuncio. La consulta individual `GET /api/estudiantes/{estudiante}` sigue aplicando la Policy: el estudiante solo ve su propio registro; profesores y coordinadores se limitan a su carrera; ABAC por sede se aplica a las modificaciones.

## Correcciones hechas durante esta auditoría

- Se retiró el salto de línea que precedía `<?php` en `routes/api.php`, para evitar salida accidental al cargar las rutas.
- La petición **Listado administrador** de Bruno obtiene los IDs sembrados por nombre y los guarda en el entorno; ya no depende de IDs escritos a mano.
- Se alineó el listado general a la regla explícita del anuncio y se añadieron pruebas para usuarios autenticados con y sin rol.
- Se añadieron solicitudes Bruno para los dashboards de administrador y profesor con accesos permitidos y denegados, y comprobación del rol/permisos del coordinador.

## Verificación ejecutada

```text
php artisan --version       Laravel Framework 13.43.0
php artisan migrate:status  nueve migraciones aplicadas
php artisan route:list      12 rutas API registradas
php artisan test --no-ansi  26 pruebas aprobadas (137 aserciones)
```

La suite usa SQLite en memoria según `phpunit.xml`; no requiere limpiar ni recrear la base local. La verificación de Bruno usó una base SQLite temporal, migrada y sembrada, sin modificar la base de desarrollo. Los resultados observados están en `evidence/integrante-5/RESULTADOS.md`.
