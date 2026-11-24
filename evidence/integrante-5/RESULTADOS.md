# Registro de evidencias — Integrante 5

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
