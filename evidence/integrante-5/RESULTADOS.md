# Registro de evidencias — Integrante 5

## Ejecución actual — 24-11-2026

**Última ejecución automatizada:** 26 pruebas aprobadas y 136 aserciones con SQLite `:memory:` y PHP 8.4.26. Incluye nuevas comprobaciones de campos obligatorios, IDs inexistentes, unicidad y ausencia de cambios en actualizaciones rechazadas. La conexión efectiva de testing se verificó antes de ejecutar.

### Solicitudes HTTP reales sobre base aislada

Se ejecutaron solicitudes HTTP con PowerShell contra Laravel en `127.0.0.1`, con una base SQLite nueva y exclusiva de prueba, migrada y sembrada después de verificar su ruta. Se consultaron los IDs en el listado; no se guardaron tokens. Estas solicitudes **no son una ejecución de Bruno**.

| Caso | Observado |
|---|---|
| Login administrador / profesor / estudiante | 200 / 200 / 200 |
| Dashboard administrador: administrador / profesor / estudiante / sin token | 200 / 403 / 403 / 401 |
| Dashboard profesor: administrador / profesor / estudiante / sin token | 403 / 200 / 403 / 401 |
| Estudiante consulta propio / ajeno activo / ajeno inactivo | 200 / 403 / 403 |
| Profesor edita misma carrera y sede / misma carrera y otra sede | 200 / 403 |
| Logout administrador / me con token revocado | 200 / 401 |
| Profesor: edición misma carrera/misma sede; misma/otra; otra/misma; otra/otra | 200 / 403 / 403 / 403 |
| Coordinador: edición misma carrera/misma sede; misma/otra; otra/misma; otra/otra | 200 / 403 / 403 / 403 |
| Estudiante edita propio | 403 |
| Profesor crea / elimina | 403 / 403 |
| Administrador envía sede nula | 422 |
| Credenciales incorrectas | 422 |

Un intento intermedio de automatización HTTP falló antes de enviar solicitudes por una colisión de nombre con un alias de PowerShell; se corrigió y los resultados de la repetición aparecen en la tabla. Quedan pendientes los casos de la colección Bruno no incluidos en esta tabla y sus capturas. Las restricciones observadas para coordinador no sustituyen la confirmación del acuerdo del equipo.

**Resultado posterior a la corrección:** 24 pruebas aprobadas y 114 aserciones, en SQLite `:memory:` con PHP 8.4.26. Se verificó que enviar `user_id`, `carrera_id` o `sede_id` en una actualización, tanto nulos como con valor, devuelve 422 y no cambia el registro. La integración de `feature/gates-crud` quedó registrada en el merge `eb0d684`. Bruno continúa sin ejecutarse en esta sesión.

En una ejecución inicial de esta sesión se preparó la integración de `origin/feature/gates-crud` sobre `d118f74` y se registró el alias de middleware. Con PHP 8.4.26, Laravel 13 y conexión de testing comprobada como SQLite `:memory:`, `php artisan test --no-ansi` informó **22 pruebas aprobadas, 88 aserciones**. Incluye emisión y revocación de token Sanctum, Gates y la suite Feature existente. No equivale a ejecutar Bruno ni demuestra los casos no incluidos en la suite.

Posteriormente se añadió y ejecutó `ValidacionActualizacionTest`: **falló inicialmente** porque una actualización con `sede_id: null` devolvió 500 en lugar de 422. Tras la autorización de Néstor se cambió la validación y la regresión pasó.

Antes de la corrección, la suite completa informó **1 fallida, 22 aprobadas y 90 aserciones**. La regresión verificó primero que la sede permaneció sin cambios y luego falló por el código HTTP. Ese resultado es histórico, no el resultado final indicado arriba.

La colección Bruno se parametrizó parcialmente con IDs variables; **no se ejecutó en esta sesión**. Se añadieron solicitudes de registro y revocación, pendientes de ejecución; faltan casos de CRUD y validaciones, así como evidencias sanitizadas. La integración final depende de resolver los pendientes documentados.

## Acta histórica y casos Bruno pendientes

Completar después de ejecutar Bruno contra la rama final integrada. No marcar casos como aprobados antes de observar su respuesta real.

| Grupo/caso | Resultado esperado | Resultado observado | Captura |
|---|---:|---:|---|
| Login roles de prueba | 200 | Pendiente de ejecución Bruno | |
| Ruta protegida sin token | 401 | Pendiente de ejecución Bruno | |
| Acceso propio y ajeno de estudiante | 200 / 403 | Pendiente de ejecución Bruno | |
| Lectura del profesor por carrera | 200 / 403 | Pendiente de ejecución Bruno | |
| ABAC profesor por carrera y sede | 200 / 403 / 403 / 403 | Pendiente de ejecución Bruno | |

**Suite automatizada:** En la verificación integrada temporal del 23-11-2026, `php artisan test` informó 18 pruebas aprobadas y 45 aserciones. Esta comprobación cubre Laravel Feature/Unit, no constituye una ejecución de Bruno.

**Pendiente conocido:** validar que `user_id`, `carrera_id` y `sede_id` no puedan enviarse en blanco en la actualización. En el CRUD recibido, `sede_id: null` provocó un 500 de SQLite; la respuesta acordada es 422. Ver `docs/CIERRE_INTEGRANTE_5.md`.
