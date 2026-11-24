# Integración y verificación — actividad 2

## Estado integrado

La versión final integra Laravel 13, Sanctum, Spatie Permission, modelos/migraciones de estudiantes, carreras y sedes, roles y permisos, autenticación, Gates, Policies, CRUD y reglas ABAC. El registro público no permite autoasignarse roles o permisos.

`GET /api/estudiantes` permite el listado general a cualquier cuenta autenticada, de acuerdo con el escenario del anuncio. La consulta por ID mantiene Policy: el estudiante solo consulta su propio registro, y profesor/coordinador se limitan a su carrera. Para modificar, profesor/coordinador deben coincidir en carrera y sede.

## Validación

- Las pruebas Feature verifican Sanctum, 401/403, listado general, propiedad, RBAC, Gate, las cuatro combinaciones ABAC, CRUD y validaciones de actualización.
- La colección Bruno contiene autenticación, listado general por diferentes perfiles, detalle, ABAC y dashboards.
- El entorno de Bruno usa tokens dinámicos y IDs obtenidos desde la respuesta del listado; el archivo de entorno versionado debe permanecer sin tokens.
- Los resultados de las ejecuciones concretas están en `evidence/integrante-5/RESULTADOS.md`.

Para verificar el proyecto, usar `composer install`, `php artisan migrate:status`, `php artisan route:list --path=api -v` y `php artisan test --no-ansi`. `phpunit.xml` configura SQLite `:memory:` para la suite. Bruno debe ejecutarse contra una base de laboratorio separada, migrada y sembrada.

No incluir `.env`, tokens personales ni bases con datos privados en la entrega.
