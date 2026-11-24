# Integración y verificación — integrante 5

Referencia revisada: documento del taller «Actividad 02 - Taller_Autorizacion_Laravel_13.md». Base de esta rama: `d118f74` (`origin/main`); `origin/feature/gates-crud` integrada en el merge `eb0d684`. Esta revisión no constituye una integración final aprobada.

## Estado inicial

| Requisito | Estado | Observación |
|---|---|---|
| Laravel 13, Sanctum, Spatie | Implementado, pendiente de prueba | Declarados en Composer; el lock incluye Laravel 13.43.0. |
| Modelos, migraciones y relaciones | Implementado, pendiente de prueba | Existen estructuras para usuarios, carreras, sedes y estudiantes. |
| Registro, login, me y logout | Implementado, pendiente de prueba | Hay rutas y controlador; se prepararon pruebas con tokens reales. |
| Gates, rutas y CRUD | Faltante en main | Existen en `origin/feature/gates-crud`, no integrada a main por historial. |
| Policies, permisos y ABAC | Implementado, pendiente de prueba | Hay Policy y seeder; falta integración del CRUD y ejecución. |
| Middleware de permisos | Faltante | `bootstrap/app.php` no registra el alias `permission`. |
| Suite Feature y colección Bruno | Implementado, pendiente de prueba | Ya existen; la colección utiliza IDs fijos y requiere completarse. |
| Evidencias reales y matriz final | Faltante | El acta Bruno existente indica que está pendiente. Resultados históricos no prueban esta rama. |
| Reflexión individual | Pendiente | Debe basarse en las respuestas personales del estudiante. |

Actualización: después de corregir la regresión de `sede_id: null`, la suite completa pasó con 24 pruebas y 114 aserciones. El alias middleware se registró en `bootstrap/app.php`, y las rutas CRUD y Gates están integradas. La verificación de Bruno continúa pendiente. El listado filtrado por roles difiere del listado general descrito en el taller y requiere acuerdo del equipo.

El documento propone listado general para cualquier usuario autenticado; la Policy exige permisos y el CRUD integrado filtra por rol. Esta diferencia necesita acuerdo del equipo antes de considerarla conforme. La documentación previa describe coordinador con lectura de su carrera y edición de misma carrera y sede; corresponde confirmar el acuerdo antes de fijar expectativas.

## Preparación segura

`composer.lock` exige PHP 8.4.1 o superior para sus versiones actuales de Symfony, aunque `composer.json` admite PHP 8.3. Con PHP compatible ejecutar `composer install` sin actualizar dependencias. No ejecutar `composer setup`: incluye migraciones sobre la conexión configurada. Copiar `.env.example` solo si falta `.env` y generar la clave solo en esa configuración nueva. Usar una base local desechable separada y confirmar la conexión antes de migrar o sembrar. Nunca ejecutar `migrate:fresh` sobre datos existentes.

`phpunit.xml` declara SQLite en memoria, pero debe verificarse la configuración efectiva de testing antes de ejecutar pruebas con `RefreshDatabase`; una variable externa o caché de configuración podría alterarla. Ejecutar la suite únicamente después de esa comprobación. No publicar `.env`, bases privadas, `vendor` ni tokens.

## Colección y evidencias

La colección existente está en `bruno/api-academica`. Está preparada parcialmente, no consta ejecución en esta sesión. Necesita IDs variables obtenidos de datos reales, casos de autenticación y CRUD adicionales y una ejecución con respuestas observadas. No capturar tokens ni datos privados. El borrado debe usar un registro creado para esa prueba.
