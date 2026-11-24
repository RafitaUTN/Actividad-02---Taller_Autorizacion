# Evidencias de verificación — Actividad 2

## Ejecución final

La colección `bruno/api-academica` se ejecutó con Bruno CLI 4.3.0 contra Laravel 13.43.0 en `127.0.0.1:8125`. La aplicación usó una base SQLite aislada, migrada y sembrada para esta verificación. **Aprobadas: 40/40 solicitudes.**

| Grupo | Resultado | Casos observados |
|---|---:|---|
| Autenticación | 11/11 | Login de administrador, profesor, coordinador, estudiante e inactivo: 200; `/me`: 200; credenciales incorrectas: 422; registro: 201; logout: 200; token revocado: 401. |
| Autorización | 12/12 | Sin token: 401. Listado general completo para administrador, profesor, coordinador, estudiante y cuenta autenticada sin rol: 200. Detalle propio: 200; estudiante consulta ajeno activo/inactivo: 403; propio inactivo: 200; consulta de profesor dentro de carrera: 200; edición de estudiante: 403. |
| ABAC | 8/8 | Profesor y coordinador: misma carrera/sede 200; misma carrera/sede diferente, carrera diferente/sede igual y ambas diferentes: 403. |
| Dashboards | 9/9 | Dashboard administrativo: administrador 200, profesor/estudiante 403 y sin token 401. Dashboard docente: profesor 200, administrador/estudiante/coordinador 403 y sin token 401. |

Los IDs de estudiantes se resolvieron desde la respuesta real del listado de administrador. Los casos de actualización usan registros sembrados en la base aislada. No se guardaron tokens ni se usó la base de desarrollo.

La ejecución fue mediante CLI, no desde la interfaz gráfica de Bruno; por ello esta acta registra las respuestas observadas y no incluye capturas de la ventana.

## Pruebas automatizadas Laravel

`php artisan test --no-ansi`: **26 pruebas aprobadas, 137 aserciones**. La configuración de PHPUnit usa SQLite `:memory:`. `php artisan migrate:status` reportó nueve migraciones del proyecto aplicadas y `php artisan route:list --path=api -v` registró 12 rutas.

## Requisito del listado general

Conforme al anuncio, toda cuenta autenticada puede consultar el listado general, incluso si no tiene rol asignado. Sin token la ruta devuelve 401. El acceso a detalles individuales sigue sujeto a las Policies de propiedad, carrera y estado.

## Datos sensibles

Las variables de tokens usadas por Bruno se limpiaron de `bruno/api-academica/environments/local.bru` antes de guardar esta evidencia. No se incluyen archivos `.env`, tokens ni credenciales personales.
