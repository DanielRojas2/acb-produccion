# FASE 3 — Presentaciones y tiempo real

> Estado: **COMPLETADA**  
> Proyecto: ACB App — Gestión de calificaciones del XXXIII Festival Nacional de la Canción Boliviana "Aquí… Canta Bolivia"  
> Fecha: 2026-09-11  

---

## 1. Qué se implementó

1. **Tabla `jurado_presentacion`**: pivote N:N entre `presentaciones` y `users` con atributos operativos: `es_secreto`, `es_controlador`, `estado`.
2. **Modelo Eloquent `JuradoPresentacion`**: relaciones con `Presentacion` y `Usuario`, casting de booleanos y enum `JuradoPresentacionEstadoEnum`.
3. **Flujo de estados de presentación**:
   - Transiciones controladas: `POR_PARTICIPAR → EN_ESCENARIO → PARTICIPANDO → FINALIZADO`.
   - Servicio `GestionarPresentacionService`: valida transiciones válidas y emite eventos en tiempo real.
4. **Cronómetro oficial en backend**:
   - Servicio `GestionarCronometroService`: marcas de tiempo `cronometro_iniciado_at` y `cronometro_finalizado_at` como fuente de verdad.
   - Cálculo del tiempo transcurrido y restante en servidor.
   - Aislamiento total: cada presentación inicia su propio cronómetro reiniciado en cero, copiando `tiempo_maximo` de la categoría.
5. **Asignación de Jurados**:
   - Servicio `AsignarJuradosService`: asignación manual (3 en Preselección, 5 en Final), autoasignación en lote con rotación por carga (jurados con menor cantidad de asignaciones previas), remoción con protección de mínimo requerido.
   - Sincronización del permiso de control de tiempo: los jurados con `puede_controlar_tiempo = true` se registran automáticamente como `es_controlador = true`.
6. **WebSockets y Tiempo Real (Laravel Reverb / Echo)**:
   - Evento `PresentacionEstadoCambiado` (ShouldBroadcastNow en canal privado `presentaciones` y `presentaciones.{id}`).
   - Evento `CronometroActualizado` (ShouldBroadcastNow en canal privado `presentaciones` y `presentaciones.{id}`).
   - Cliente frontend `resources/js/escenario-echo.js` escuchando eventos y actualizando DOM de la vista de escenario en vivo.
7. **Autorización y Seguridad**:
   - Gate `controlarCronometro`: administradores con `puede_controlar_tiempo` o jurados asignados con `es_controlador = true`.
   - Gate `asignarJurados`: exclusivo para administradores.
8. **Vistas operativas**:
   - `admin/presentaciones/index.blade.php`: panel de control de flujo de escenario y transiciones.
   - `admin/jurados/index.blade.php`: asignación manual y autoasignación en lote.
   - `jurado/escenario.blade.php`: vista proyectable en escenario con cronómetro gigante y datos en tiempo real.
   - `jurado/inicio.blade.php`: panel de jurado con listado de presentaciones asignadas y control de cronómetro para controladores autorizados.

---

## 2. Migraciones

### `2026_09_11_000010_create_jurado_presentacion_table`
| Columna | Tipo | Descripción |
|---|---|---|
| id | bigint PK | Identificador único |
| id_presentacion | FK → presentaciones | Cascada al eliminar presentación |
| id_usuario | FK → users | Cascada al eliminar usuario |
| es_secreto | boolean default `false` | Indica si es el jurado secreto en fase final |
| es_controlador | boolean default `false` | Indica si tiene control sobre el cronómetro de la presentación |
| estado | string default `ASIGNADO` | Estado del jurado (`ASIGNADO`, `CALIFICANDO`, `FINALIZADO`) |
| timestamps | timestamps | Registro de auditoría |

**Restricciones e Índices:**
- Unique `(id_presentacion, id_usuario)`: impide doble asignación del mismo jurado a una presentación.
- Index `(id_usuario)`: optimiza consultas de asignaciones por jurado.

---

## 3. Estructura de Módulos y Archivos

```text
app/
├── Modules/
│   ├── Jurados/
│   │   ├── Controllers/
│   │   │   └── JuradoController.php
│   │   ├── Models/
│   │   │   └── JuradoPresentacion.php
│   │   └── Services/
│   │       └── AsignarJuradosService.php
│   ├── Presentaciones/
│   │   ├── Controllers/
│   │   │   └── PresentacionController.php
│   │   ├── Events/
│   │   │   ├── CronometroActualizado.php
│   │   │   └── PresentacionEstadoCambiado.php
│   │   ├── Models/
│   │   │   └── Presentacion.php
│   │   └── Services/
│   │       ├── GestionarCronometroService.php
│   │       └── GestionarPresentacionService.php
│   └── Usuarios/
│       └── Models/
│           └── Usuario.php (relación presentacionesJurado)
├── Support/
│   └── Enums/
│       ├── JuradoPresentacionEstadoEnum.php
│       └── PresentacionEstadoEnum.php
resources/
├── js/
│   ├── bootstrap.js (configuración Echo / Reverb)
│   ├── app.js
│   └── escenario-echo.js (sincronización en vivo del escenario)
└── views/
    ├── admin/
    │   ├── presentaciones/
    │   │   └── index.blade.php
    │   └── jurados/
    │       └── index.blade.php
    └── jurado/
        ├── inicio.blade.php
        └── escenario.blade.php
routes/
├── web.php (endpoints de presentaciones, jurados y cronómetro)
└── channels.php (autorización de canales WebSocket)
```

---

## 4. Modelos y Relaciones

### `JuradoPresentacion`
- `belongsTo(Presentacion::class, 'id_presentacion')`
- `belongsTo(Usuario::class, 'id_usuario')`

### `Presentacion`
- `belongsTo(Inscripcion::class, 'id_inscripcion')`
- `hasMany(JuradoPresentacion::class, 'id_presentacion')`
- `nombreMostrado()`: retorna `nombre_presentacion` si existe, o el nombre completo del primer integrante.
- `cronometroCorriendo()`: valida si `cronometro_iniciado_at` no es nulo y `cronometro_finalizado_at` es nulo.
- `segundosTranscurridos()` / `segundosRestantes()`: cálculos basados en marcas de tiempo del servidor.

### `Usuario`
- `presentacionesJurado()`: relación `belongsToMany(Presentacion::class, 'jurado_presentacion', 'id_usuario', 'id_presentacion')->withPivot(['es_secreto', 'es_controlador', 'estado'])`.

---

## 5. Servicios de Negocio

### `GestionarPresentacionService`
Controla el ciclo de vida de la presentación:
- `alEscenario(Presentacion $p)`: transiciona a `EN_ESCENARIO`, fija `hora_inicio` y resetea marcas de cronómetro.
- `iniciarPresentacion(Presentacion $p)`: transiciona a `PARTICIPANDO` e inicia cronómetro si no fue iniciado previamente.
- `finalizarPresentacion(Presentacion $p)`: transiciona a `FINALIZADO`, fija `hora_fin` y detiene cronómetro.
- Dispara `PresentacionEstadoCambiado` en cada transición.

### `GestionarCronometroService`
Fuente de verdad del cronómetro:
- `iniciar(Presentacion $p)`: valida estado permitido (`EN_ESCENARIO` o `PARTICIPANDO`), asigna `cronometro_iniciado_at = now()` y emite `CronometroActualizado`.
- `detener(Presentacion $p)`: valida que esté en curso, asigna `cronometro_finalizado_at = now()` y emite `CronometroActualizado`.
- `reiniciar(Presentacion $p)`: resetea marcas a `null`.
- `obtenerEstado(Presentacion $p)`: devuelve array estructurado con segundos transcurridos, restantes y estado de ejecución.

### `AsignarJuradosService`
Gestión y reglas de jurados:
- `cantidadRequerida(Presentacion $p)`: 3 para Preselección, 5 para Final.
- `autoAsignar(Presentacion $p)`: asigna automáticamente rotando los jurados con menor carga acumulada.
- `asignar(Presentacion $p, array $usuarioIds)`: reemplaza la lista validando la cantidad exacta y el rol JURADO.
- `agregarJurado(Presentacion $p, int $usuarioId)` / `removerJurado(Presentacion $p, int $usuarioId)`.

---

## 6. WebSockets y Eventos en Tiempo Real

### Canales
- `private-presentaciones`: canal para administradores y jurados conectados al festival.
- `private-presentaciones.{id}`: canal específico para escuchar actualizaciones de una presentación individual.

### Eventos emitidos
| Evento | Payload | Canales |
|---|---|---|
| `presentacion.estado.cambiado` | `presentacion_id`, `nombre_presentacion`, `estado`, `estado_label`, `categoria`, `fase`, timestamps | `presentaciones`, `presentaciones.{id}` |
| `cronometro.actualizado` | `presentacion_id`, `corriendo`, `tiempo_maximo`, `segundos_transcurridos`, `segundos_restantes`, timestamps | `presentaciones`, `presentaciones.{id}` |

---

## 7. Rutas y Endpoints

| Método | URI | Nombre | Middleware | Acción |
|---|---|---|---|---|
| `GET` | `/admin/presentaciones` | `admin.presentaciones.index` | `auth, rol:ADMIN` | Panel de presentaciones |
| `GET` | `/admin/presentaciones/escenario` | `admin.presentaciones.escenario` | `auth, rol:ADMIN` | Vista de escenario en vivo |
| `POST` | `/admin/presentaciones/{id}/transicion` | `admin.presentaciones.transicion` | `auth, rol:ADMIN` | Cambio de estado de presentación |
| `GET` | `/admin/jurados` | `admin.jurados.index` | `auth, rol:ADMIN` | Panel de asignación de jurados |
| `POST` | `/admin/jurados/asignar` | `admin.jurados.asignar` | `auth, rol:ADMIN` | Asignación manual |
| `POST` | `/admin/jurados/auto-asignar` | `admin.jurados.auto-asignar` | `auth, rol:ADMIN` | Autoasignación en lote |
| `POST` | `/admin/jurados/remover` | `admin.jurados.remover` | `auth, rol:ADMIN` | Remoción de jurado |
| `GET` | `/jurado/inicio` | `jurado.inicio` | `auth, rol:JURADO` | Panel de jurado |
| `GET` | `/jurado/presentaciones/actual` | `jurado.presentaciones.actual` | `auth, rol:JURADO` | Vista de escenario para jurado |
| `POST` | `/presentaciones/{id}/cronometro/iniciar` | `presentaciones.cronometro.iniciar` | `auth, Gate(controlarCronometro)` | Iniciar cronómetro |
| `POST` | `/presentaciones/{id}/cronometro/detener` | `presentaciones.cronometro.detener` | `auth, Gate(controlarCronometro)` | Detener cronómetro |

---

## 8. Cobertura de Pruebas

Toda la lógica de la Fase 3 cuenta con pruebas unitarias y funcionales:

- `Tests\Unit\Jurados\AsignarJuradosServiceTest` (11 tests)
- `Tests\Unit\Presentaciones\GestionarCronometroServiceTest` (7 tests)
- `Tests\Feature\Jurados\AsignacionJuradosTest` (5 tests)
- `Tests\Feature\Presentaciones\CronometroTest` (6 tests)
- `Tests\Feature\Presentaciones\EscenarioVistaTest` (5 tests)
- `Tests\Feature\Presentaciones\PresentacionesEstadosTest` (5 tests)

Suite completa: **124 tests pasados (437 assertions)**.
