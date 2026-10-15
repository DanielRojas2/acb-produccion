# FASE 4 — Motor de calificaciones

> Estado: **COMPLETADA**  
> Proyecto: ACB App — Gestión de calificaciones del XXXIII Festival Nacional de la Canción Boliviana "Aquí… Canta Bolivia"  
> Fecha: 2026-09-11  

---

## 1. Qué se implementó

1. **Tablas y Modelos del Motor de Calificaciones**:
   - `calificaciones`: registro principal de la nota total y estado de cada jurado asignado (`id_jurado_presentacion` UNIQUE, `nota_total`, `estado`, timestamps y marcas de tiempo).
   - `detalle_calificaciones`: detalle por parámetro (`id_calificacion`, `id_parametro`, `valor`), con restricción UNIQUE `(id_calificacion, id_parametro)` para evitar doble evaluación de un parámetro.
   - `calificacion_historial`: registro inmutable de auditoría para cada corrección de voto, almacenando snapshot JSON de `datos_anteriores`, `datos_nuevos`, usuario responsable, motivo y fecha.
2. **Cálculo de Nota Total del Jurado (`CalcularNotaService`)**:
   - Validación estricta de límites: `valor_minimo <= valor <= valor_maximo` según la configuración de la categoría.
   - Regla crítica: **el puntaje máximo representa directamente los puntos de la nota** (suma directa sobre 100 puntos), sin aplicar factores porcentuales multiplicadores adicionales.
3. **Cálculo de Nota Final de la Presentación (`CalcularNotaService`)**:
   - Promedio aritmético automático sobre las calificaciones emitidas por los jurados asignados (3 en Preselección, 5 en Etapa Final).
   - Inclusión integral del jurado secreto en el cálculo matemático interno desde el instante de su emisión.
4. **Servicio de Emisión Oficial (`RegistrarCalificacionService`)**:
   - Ejecución transaccional (`DB::transaction`).
   - Bloqueo de votos fuera de fase operativa (`PARTICIPANDO` o `FINALIZADO`).
   - Bloqueo de duplicidad de voto inicial.
   - Transición del estado del jurado a `FINALIZADO` y recálculo automático de `Presentacion.nota_final`.
   - Emisión del evento WebSocket `CalificacionRegistrada`.
5. **Servicio de Corrección Auditada (`CorregirCalificacionService`)**:
   - Corrección habilitada únicamente cuando la presentación está en estado `FINALIZADO`.
   - Registro obligatorio en `CalificacionHistorial` de snapshots completos (anterior vs nuevo).
   - Recálculo inmediato de la nota del jurado y de la nota final de la presentación.
   - Emisión del evento WebSocket `CalificacionCorregida`.
6. **WebSockets y Tiempo Real**:
   - Evento `CalificacionRegistrada` (ShouldBroadcastNow en `presentaciones` y `presentaciones.{id}`).
   - Evento `CalificacionCorregida` (ShouldBroadcastNow en `presentaciones` y `presentaciones.{id}`).
7. **Interfaces de Usuario y Vistas**:
   - `jurado/calificar.blade.php`: hoja de evaluación reactiva con cálculo en vivo vía JavaScript Vanilla, barras de progreso y modal de confirmación antes de emitir.
   - `jurado/corregir.blade.php`: interfaz de ajuste con aviso de auditoría, comparación con nota anterior y campo para justificación.
   - `jurado/inicio.blade.php`: panel de jurado con badges de estado, accesos directos a calificar/corregir y visualización de nota.
   - `admin/calificaciones/detalle.blade.php`: panel de monitoreo de calificaciones por jurado y desglose de parámetros.
   - `admin/calificaciones/historial.blade.php`: línea de tiempo de auditoría con tabla de variaciones entre parámetros modificados.
8. **Seguridad y Control de Acceso**:
   - Validación de pertenencia: un jurado solo puede calificar las presentaciones que le fueron asignadas.
   - Protección contra modificaciones no autorizadas.

---

## 2. Migraciones

### `2026_09_11_000011_create_calificaciones_table`
| Columna | Tipo | Restricciones | Descripción |
|---|---|---|---|
| `id` | bigint PK | AUTO_INCREMENT | Identificador único |
| `id_jurado_presentacion` | FK → `jurado_presentacion` | UNIQUE, cascadeOnDelete | Asignación jurado-presentación |
| `nota_total` | decimal(5,2) | default 0 | Suma total de los parámetros (máx 100) |
| `estado` | string | default `BORRADOR` | `BORRADOR`, `EMITIDO`, `CORREGIDO` |
| `fecha_inicio` | timestamp | nullable | Inicio de la evaluación |
| `fecha_finalizacion` | timestamp | nullable | Momento de emisión del voto |
| `fecha_modificacion` | timestamp | nullable | Momento de la última corrección |
| `timestamps` | timestamps | | Auditoría del registro |

### `2026_09_11_000012_create_detalle_calificaciones_table`
| Columna | Tipo | Restricciones | Descripción |
|---|---|---|---|
| `id` | bigint PK | AUTO_INCREMENT | Identificador único |
| `id_calificacion` | FK → `calificaciones` | cascadeOnDelete | Calificación padre |
| `id_parametro` | FK → `parametros` | cascadeOnDelete | Parámetro evaluado |
| `valor` | decimal(5,2) | unsigned | Puntaje asignado |
| `timestamps` | timestamps | | Auditoría del registro |

- **Restricción Unique**: `['id_calificacion', 'id_parametro']`.

### `2026_09_11_000013_create_calificacion_historial_table`
| Columna | Tipo | Restricciones | Descripción |
|---|---|---|---|
| `id` | bigint PK | AUTO_INCREMENT | Identificador único |
| `id_calificacion` | FK → `calificaciones` | cascadeOnDelete | Calificación modificada |
| `id_usuario` | FK → `users` | cascadeOnDelete | Usuario que realizó el cambio |
| `datos_anteriores` | json | | Snapshot antes del cambio |
| `datos_nuevos` | json | | Snapshot después del cambio |
| `motivo` | string | nullable | Justificación de la corrección |
| `fecha` | timestamp | useCurrent | Marca de tiempo oficial |
| `timestamps` | timestamps | | Auditoría del registro |

---

## 3. Estructura de Módulos y Archivos

```text
app/
├── Modules/
│   ├── Calificaciones/
│   │   ├── Controllers/
│   │   │   ├── AdminCalificacionesController.php
│   │   │   ├── CalificacionJuradoController.php
│   │   │   └── CorreccionCalificacionController.php
│   │   ├── Events/
│   │   │   ├── CalificacionCorregida.php
│   │   │   └── CalificacionRegistrada.php
│   │   ├── Models/
│   │   │   ├── Calificacion.php
│   │   │   ├── CalificacionHistorial.php
│   │   │   └── DetalleCalificacion.php
│   │   ├── Requests/
│   │   │   ├── CorregirCalificacionRequest.php
│   │   │   └── GuardarCalificacionRequest.php
│   │   └── Services/
│   │       ├── CalcularNotaService.php
│   │       ├── CorregirCalificacionService.php
│   │       └── RegistrarCalificacionService.php
│   ├── Jurados/
│   │   └── Models/
│   │       └── JuradoPresentacion.php (relación calificacion)
│   └── Presentaciones/
│       └── Models/
│           └── Presentacion.php (relación calificaciones)
└── Support/
    └── Enums/
        ├── CalificacionEstadoEnum.php
        └── JuradoPresentacionEstadoEnum.php
resources/
└── views/
    ├── admin/
    │   └── calificaciones/
    │       ├── detalle.blade.php
    │       └── historial.blade.php
    └── jurado/
        ├── calificar.blade.php
        ├── corregir.blade.php
        └── inicio.blade.php (actualizado)
routes/
└── web.php (rutas jurado y admin de calificaciones)
```

---

## 4. Endpoints y Rutas

| Método | URI | Nombre de Ruta | Middleware | Descripción |
|---|---|---|---|---|
| `GET` | `/jurado/calificaciones/presentaciones/{id}/calificar` | `jurado.calificaciones.evaluar` | `auth, rol:JURADO` | Formulario de evaluación en vivo |
| `POST` | `/jurado/calificaciones/presentaciones/{id}/calificar` | `jurado.calificaciones.guardar` | `auth, rol:JURADO` | Emisión oficial de voto |
| `GET` | `/jurado/calificaciones/presentaciones/{id}/corregir` | `jurado.calificaciones.corregir.form` | `auth, rol:JURADO` | Formulario de corrección auditada |
| `POST` | `/jurado/calificaciones/{id}/corregir` | `jurado.calificaciones.corregir` | `auth, rol:JURADO` | Guardar corrección de notas |
| `GET` | `/admin/calificaciones/presentacion/{id}` | `admin.calificaciones.presentacion` | `auth, rol:ADMIN` | Detalle y monitoreo de calificaciones |
| `GET` | `/admin/calificaciones/historial/{id}` | `admin.calificaciones.historial` | `auth, rol:ADMIN` | Auditoría de historial de cambios |

---

## 5. Eventos WebSockets

| Evento | Payload | Canales |
|---|---|---|
| `calificacion.registrada` | `calificacion_id`, `presentacion_id`, `jurado_id`, `jurado_nombre`, `es_secreto`, `nota_total`, `nota_final`, `estado`, timestamps | `presentaciones`, `presentaciones.{id}` |
| `calificacion.corregida` | `calificacion_id`, `presentacion_id`, `jurado_id`, `jurado_nombre`, `es_secreto`, `nota_total`, `nota_final`, `estado`, timestamps | `presentaciones`, `presentaciones.{id}` |

---

## 6. Cobertura de Pruebas

Toda la lógica de cálculo, guardado, corrección, autorización e historial cuenta con tests automáticos:

- `Tests\Unit\Calificaciones\CalcularNotaServiceTest` (6 tests)
- `Tests\Unit\Calificaciones\RegistrarCalificacionServiceTest` (4 tests)
- `Tests\Unit\Calificaciones\CorregirCalificacionServiceTest` (2 tests)
- `Tests\Feature\Calificaciones\JuradoCalificacionTest` (4 tests)
- `Tests\Feature\Calificaciones\AdminCalificacionesTest` (3 tests)

Suite de pruebas del proyecto: **144 tests pasados (492 assertions)**.
