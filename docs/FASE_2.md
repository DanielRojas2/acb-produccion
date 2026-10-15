# FASE 2 — Importaciones y datos operativos

> Estado: **COMPLETADA**
> Proyecto: ACB App — Gestión de calificaciones del XXXIII Festival Nacional de la Canción Boliviana "Aquí… Canta Bolivia"
> Fecha: 2026-09-11

---

## 1. Qué se implementó

1. Tablas `participantes`, `inscripciones`, `inscripcion_participante` y `presentaciones`
   (con la cadena `Categoria → Inscripcion → Presentacion` del DER).
2. Modelos Eloquent de `Participante`, `Inscripcion`, `InscripcionParticipante` y `Presentacion`.
3. Enums `InscripcionEstadoEnum` y `PresentacionEstadoEnum` (transiciones controladas).
4. Módulo `App\Modules\Importaciones`: lectores de **CSV, JSON y XLSX**, servicio de
   importación con validación **todo-o-nada** y persistencia en transacción.
5. Flujo web de administración: vista de importación con resultado, plantilla CSV descargable,
   upload via HTTP con `@csrf` (multipart).
6. Regla DER: `nombre_presentacion` pertenece a `Presentacion` (no sustituye a los
   participantes individuales). Dúo, Trío, Estudiantina y Especial pueden definir nombre propio.

---

## 2. Migraciones

### `2026_09_11_000006_create_participantes_table`
| Columna | Tipo |
|---|---|
| id | bigint PK |
| nombre | string (obligatorio) |
| paterno | string nullable |
| materno | string nullable |
| pseudonimo | string nullable |
| timestamps | |

### `2026_09_11_000007_create_inscripciones_table`
| Columna | Tipo |
|---|---|
| id | bigint PK |
| fecha_inscripcion | date (obligatorio) |
| hora_presentacion | time nullable |
| fecha_presentacion | date nullable |
| id_fase | FK → fases (restrictOnDelete) |
| id_categoria | FK → categorias (restrictOnDelete) |
| id_departamento | FK → departamentos nullable (restrictOnDelete; `null` en FINAL) |
| estado | string default `REGISTRADA` |
| timestamps | |

Índices: `(id_fase, id_categoria)` y `id_departamento`.

### `2026_09_11_000008_create_inscripcion_participante_table`
Pivote N:N Inscripcion ↔ Participante:

| Columna | Tipo |
|---|---|
| id_participante | FK → participantes (cascadeOnDelete) |
| id_inscripcion | FK → inscripciones (cascadeOnDelete) |

**PK compuesta `(id_participante, id_inscripcion)`** (impide duplicar un integrante en la
misma inscripción) + índice en `id_inscripcion`.

### `2026_09_11_000009_create_presentaciones_table`
| Columna | Tipo |
|---|---|
| id | bigint PK |
| id_inscripcion | FK → inscripciones, **UNIQUE** (relación 1:1; cascadeOnDelete) |
| nombre_presentacion | string nullable |
| estado | string default `POR_PARTICIPAR` |
| tiempo_maximo | unsignedInteger (minutos; copia de `Categoria.tiempo_presentacion`) |
| hora_inicio, hora_fin, cronometro_iniciado_at, cronometro_finalizado_at | timestamp/time nullable (FASE 3) |
| nota_final | decimal(5,2) nullable (FASE 4) |
| timestamps | |

Índice en `estado`.

> **Modelos importantes:** `Inscripcion` declara `protected $table = 'inscripciones'` y
> `Presentacion` declara `protected $table = 'presentaciones'`. El pluralizador de Laravel
> genera nombres incorrectos (`inscripcions`, `presentacions`), por eso la tabla se fija
> explícitamente.

---

## 3. Enums

`App\Support\Enums` (nuevos):

- **`InscripcionEstadoEnum`**: `REGISTRADA`, `CONFIRMADA`, `CANCELADA` + `label()`.
- **`PresentacionEstadoEnum`**: `POR_PARTICIPAR → EN_ESCENARIO → PARTICIPANDO → FINALIZADO`
  con `puedeTransicionarA(PresentacionEstadoEnum $destino)` y `label()`. La transición
  controlada comenzará a usarse en la FASE 3 (GestionarPresentacionService).

---

## 4. Modelos y relaciones

| Modelo | Módulo | Relaciones |
|---|---|---|
| `Participante` | `App\Modules\Participantes\Models` | `inscripciones()` belongsToMany |
| `Inscripcion` | `App\Modules\Inscripciones\Models` | `categoria()`, `fase()`, `departamento()` belongsTo; `participantes()` belongsToMany (pivote `InscripcionParticipante`); `presentacion()` hasOne |
| `InscripcionParticipante` | `App\Modules\Inscripciones\Models` | belongsTo de ambos lados; `$incrementing = false` |
| `Presentacion` | `App\Modules\Presentaciones\Models` | `inscripcion()` belongsTo; cast `estado` → `PresentacionEstadoEnum` |

Se extendieron (sin tocar FASE 1):

- `Categoria`: cast `tiempo_presentacion`, `categoria_parametros` ordenada por `orden`,
  `inscripciones()` hasMany y helper `requiereNombrePresentacion()`.
- `Fase`: cast `fase` → `FaseEnum`, `inscripciones()` hasMany (`id_fase`).
- `Departamento`: `inscripciones()` hasMany.

`Categoria::requiereNombrePresentacion()` identifica Dúo, Trío, Estudiantina y Especial
por nombre de categoría: `NAVIES_COLECTORA` (`dúo|trío|estudiantina|especial`, sin acentos).

---

## 5. Motor de importación

### Estructura (`app/Modules/Importaciones`)

```text
app/Modules/Importaciones/
├── Controllers/ImportacionController.php      # index, importar, plantilla
├── Exceptions/
│   ├── FormatoNoSoportadoException.php
│   └── LecturaException.php
├── Readers/
│   ├── ArchivoLector.php        # interfaz leer(string $ruta): array
│   ├── LectorBase.php           # normalización de cabeceras/filas/comunes
│   ├── LectorCsv.php
│   ├── LectorJson.php
│   ├── LectorXlsx.php
│   └── FabricaLectores.php      # csv → LectorCsv, json → LectorJson, xlsx → LectorXlsx
├── Services/
│   └── ImportarInscripcionesService.php
└── ValueObjects/
    └── ResultadoImportacion.php # exitoso, errorGeneral, filasProcesadas, inscripcionesCreadas, participantesCreados, presentacionesCreadas, errores[{fila,mensaje}]
```

`FabricaLectores` implementa `HasInvocaciones` solo como extensibilidad futura; la
resolución es por extensión de archivo (la del `UploadedFile` o la del path).

### Formato de archivo (CSV y XLSX)

**Una fila = una inscripción.** Columnas (cabeceras normalizadas sin acentos; BOM UTF-8
soportado; delimitador detectado automáticamente entre `;` `,` tab `|`):

`categoria` (obligatoria), `departamento` (obligatorio en PRESELECCION), `fase`
(`PRESELECCION`|`FINAL`, default `PRESELECCION`), `fecha_inscripcion` (YYYY-MM-DD),
`fecha_presentacion`, `hora_presentacion`, `nombre_presentacion`, e integrantes:
`integrante_1_nombre`, `integrante_1_paterno`, `integrante_1_materno`,
`integrante_1_pseudonimo`, `integrante_2_*`, …

Acepta alias: `nombre_de_presentacion`, `nombre_artistico`, `fecha_de_inscripcion`,
`participante_1_*`, etc. (LectorBase::correspondencia).

### JSON

Dos formas equivalentes:
- un arreglo de objetos (uno por inscripción);
- `{"filas": [ … ]}`.

Los integrantes pueden ir como campos `integrante_1_*`, etc., o como arreglo anidado
`integrantes: [{nombre, paterno, materno, pseudonimo}, …]`.

### XLSX

Lectura **nativa** con `ZipArchive` + `SimpleXML` (el entorno no tiene internet para
instalar `phpoffice/phpspreadsheet`). Lee `sharedStrings`, la primera hoja vía
`workbook.xml` + rels, interpreta `t` (s/shared, inlineStr, b/e) y mapea por referencia y
columna (`indiceColumna` soporta más de 26 columnas). Detalle importante: los atributos de
celda se leen con `$celda->attributes()` (no `$celda['r']`), porque SimpleXML no expone
atributos sin prefijo vía ArrayAccess cuando el elemento está en un namespace.

---

## 6. Lógica de importación (`ImportarInscripcionesService`)

Flujo:

```text
extensión → FabricaLectores → leer() → filas normalizadas
    ↓
validación todo-o-nada (errores por fila)  → si hay errores: devolver sin persistir
    ↓
DB::transaction → persistir
```

### Validaciones (por fila)

1. Categoría obligatoria y debe existir (nombre oficial, sin acentos).
2. Fase debe ser `PRESELECCION` o `FINAL` (case-insensitive).
3. En preselección el departamento es obligatorio y debe existir; en FINAL es opcional
   (si se indica debe existir).
4. Fechas `YYYY-MM-DD` válidas (`checkdate`) y hora `HH:MM[:SS]`.
5. Al menos un integrante.
6. Máx. integrantes ≤ `Categoria.max_participantes`.
7. Todo integrante debe tener `nombre`; sin integrantes repetidos dentro de la
   inscripción (por nombre+paterno+materno).
8. **Inscripciones repetidas entre filas** (misma categoría+departamento+fase+fechas+
   horario+nombre_presentacion+integrantes) → error en la fila duplicada.

Ningún dato se persiste si hay errores; el resultado se muestra con tabla fila/error.

### Persistencia (en transacción)

1. `Inscripcion` (estado `REGISTRADA`, `fecha_inscripcion` default hoy, fase correcta por
   departamento/FINAL).
2. Por cada integrante: reutiliza `Participante` existente (mismo nombre+paterno+materno)
   o lo crea con su `pseudonimo`; crea el registro pivote `InscripcionParticipante`.
3. `Presentacion` 1:1 con la inscripción:
   - `nombre_presentacion` = el indicado o, en su defecto, el nombre completo del primer
     integrante ("Nombre Paterno Materno");
   - `estado` = `POR_PARTICIPAR`;
   - `tiempo_maximo` = **copiado de `Categoria.tiempo_presentacion`** (regla AGENTS).

---

## 7. Rutas nuevas

| Método | URI | Nombre | Middleware | Controlador |
|---|---|---|---|---|
| GET | `/admin/importaciones` | `admin.importaciones.index` | admin | `ImportacionController@index` |
| POST | `/admin/importaciones/importar` | `admin.importaciones.importar` | admin | `ImportacionController@importar` |
| GET | `/admin/importaciones/plantilla` | `admin.importaciones.plantilla` | admin | `ImportacionController@plantilla` |

- `importar` valida `archivo` (requerido, file) y redirige con flash `resultado_importacion`
  (objeto `ResultadoImportacion` serializado en sesión).
- `plantilla` descarga `plantilla_importacion.csv` con 3 columnas de integrantes y 2 filas
  de ejemplo (solo se usa el formato CSV como referencia).

### Vistas

- `resources/views/admin/importaciones/index.blade.php`: resumen del resultado de la última
  importación (éxito con contadores, o tabla de errores fila/error), formulario multipart con
  `accept=".csv,.json,.xlsx"` y botón "Descargar plantilla CSV".
- El sidebar admin (`resources/views/components/menus/admin.blade.php`) enlaza
  "Importaciones".

---

## 8. Decisiones técnicas

1. **Validación previa todo-o-nada** (decisión del usuario): se valida todo el archivo antes
   de persistir; si hay errores no se guarda nada y se reporta por fila.
2. **Columnas repetidas por integrante** (decisión del usuario): `integrante_1_*`,
   `integrante_2_*`, …; se ordenan por número y se omiten posiciones vacías.
3. **`nombre_presentacion` opcional para todas** las categorías (decisión del usuario):
   si se omite se usa el nombre completo del primer integrante.
4. **Participantes reutilizados** por nombre+paterno+materno (deduplicación natural), no por
   pseudónimo.
5. **Lectura XLSX nativa** (sin internet): ZipArchive + SimpleXML; solo se lee la primera
   hoja. Suficiente para la plantilla y archivos de inscripción operativos.
6. `fgetcsv`/`str_getcsv` reciben `$escape = '\\'` explícito (PHP 8.4).
7. El servidor es la fuente de verdad: la importación calcula `tiempo_maximo` y estados;
   el frontend no calcula nada (FASE 2 no expone calificaciones).

---

## 9. Pruebas

Suite completa: **63 passed (261 assertions)** (incluye FASE 0, FASE 1, CRUD usuarios y los
nuevos tests de importación; `phpunit.xml` usa sqlite `:memory:` y `RefreshDatabase`).

Nuevos:

- `tests/Unit/Importaciones/ImportacionLectoresTest.php` (8 tests): CSV con `;` y BOM, alias
  con acentos, detección de delimitador `,`, orden/integrante vacíos, columnas no reconocidas,
  JSON anidado y JSON inválido, fábrica rechaza formato desconocido.
- `tests/Feature/Importaciones/ImportacionCsvTest.php` (12 tests): importación completa,
  dúo con nombre de presentación, FINAL sin departamento, y los casos de error sin persistir
  (categoría inexistente, falta departamento, exceso de integrantes, fila sin integrantes,
  integrantes repetidos, filas duplicadas), reutilización de participantes, seguridad (jurado
  403) e importación HTTP.
- `tests/Feature/Importaciones/ImportacionJsonTest.php` (4 tests): forma anidada, forma plana,
  JSON malformado, estructura desconocida (sin persistencia).
- `tests/Feature/Importaciones/ImportacionXlsxTest.php` (2 tests): lectura de un XLSX mínimo
  generado con `ZipArchive` en la propia prueba e importación vía HTTP (vuelve al destinatario
  `nombre_presentacion`).

Verificado por HTTP real (servidor en 127.0.0.1): los tres formatos importan con éxito y el
JSON inválido muestra la tabla de errores sin persistir.

---

## 10. Cómo extender

- **Nuevas columnas en plantilla:** añadir el alias en `LectorBase::correspondencia` y, si
  afecta persistencia, usarla en `ImportarInscripcionesService::persistir`.
- **Importación de otras entidades (FASE 2 futura si se requiere):** seguir el patrón de un
  `ArchivoLector` por formato + un `*Service` que valide primero y persista en transacción;
  reutilizar `ResultadoImportacion`.
- **XLSX más robusto:** hoy solo lee la primera hoja y tipos básicos (s, inlineStr, b, e).
  Si el festival envía libros con varias hojas o fórmulas hay que escalar `LectorXlsx`.
- **FASE 3:** `GestionarPresentacionService` + `GestionarCronometroService` usarán los
  campos de cronómetro ya presentes en `presentaciones` y el enum de estados con
  `puedeTransicionarA()`.

---

## 11. Extensión: CRUD manual de Participantes e Inscripciones

Como alternativa (o complemento) a la importación por archivo, el panel de administración
permite registrar participantes e inscripciones directamente. **Un participante suelto no
participa del festival hasta formar parte de una inscripción/presentación**, por lo que la
carga manual completa consta de dos CRUD.

### Rutas

| Método | URI | Nombre | Middleware |
|---|---|---|---|
| GET | `/admin/participantes` | `admin.participantes.index` | admin |
| GET | `/admin/participantes/create` | `admin.participantes.create` | admin |
| POST | `/admin/participantes` | `admin.participantes.store` | admin |
| GET | `/admin/participantes/{participante}/edit` | `admin.participantes.edit` | admin |
| PATCH | `/admin/participantes/{participante}` | `admin.participantes.update` | admin |
| DELETE | `/admin/participantes/{participante}` | `admin.participantes.destroy` | admin |
| GET | `/admin/inscripciones` | `admin.inscripciones.index` | admin |
| GET | `/admin/inscripciones/create` | `admin.inscripciones.create` | admin |
| POST | `/admin/inscripciones` | `admin.inscripciones.store` | admin |
| GET | `/admin/inscripciones/{inscripcion}/edit` | `admin.inscripciones.edit` | admin |
| PATCH | `/admin/inscripciones/{inscripcion}` | `admin.inscripciones.update` | admin |
| DELETE | `/admin/inscripciones/{inscripcion}` | `admin.inscripciones.destroy` | admin |

> Nota: `Route::resource('inscripciones')` con `->parameters(['inscripciones' => 'inscripcion'])`
> porque el singular de Laravel sería `inscripcione` y rompería el route-model binding.

### Implementación

- `app/Modules/Participantes/Controllers/ParticipanteController.php`: listado paginado con
  búsqueda (nombre/apellidos/pseudónimo) y `withCount('inscripciones')`; CRUD de
  nombre/paterno/materno/pseudónimo.
- `app/Modules/Inscripciones/Controllers/InscripcionController.php`: listado con filtros
  (categoría, fase, departamento, texto) y CRUD completo.
- **`app/Modules/Inscripciones/Services/RegistrarInscripcionService.php`**: servicio
  compartido que concentra la lógica de una inscripción (validación + persistencia) y que
  **también usa la importación de archivos**, eliminando la lógica duplicada:

```text
validar(array $datos): list<string>     // mismas reglas que la importación
registrar(array $datos): Inscripcion    // alta en DB::transaction
actualizar(Inscripcion, array $datos)   // actualiza + sincroniza integrantes
```

  `ImportarInscripcionesService` sigue conservando el conteo por fila, la detección de
  duplicados entre filas y el `ResultadoImportacion`; ahora delega cada fila en el servicio.
- Vistas: `resources/views/admin/{participantes,inscripciones}/{index,create,edit}.blade.php`
  + `_form.blade.php`. El formulario de inscripción incluye selects de categoría/departamento,
  fase `PRESELECCION/FINAL` (oculta departamento en FINAL con JS), fechas/hora, nombre de
  presentación opcional e **integrantes dinámicos** (filas nombre/paterno/materno/pseudónimo
  con añadir/quitar; el límite mostrado = `Categoria.max_participantes`).
- Menú lateral admin (Operación): ahora enlaza "Importaciones", "Participantes" e
  "Inscripciones".

### Reglas de negocio

1. **Participantes duplicados bloqueados**: no se crea un participante con el mismo
   nombre+paterno+materno ya existente (misma clave de reutilización que la importación).
2. **No se elimina un participante** que figure en inscripciones (403). El FK del pivote usa
   `cascadeOnDelete`, por eso la protección es de aplicación.
3. **Inscripciones solo editables/eliminables en `POR_PARTICIPAR`**: cuando la presentación ya
   inició (`EN_ESCENARIO`/`PARTICIPANDO`) o terminó (`FINALIZADO`) el CRUD devuelve 403
   (helper `Inscripcion::puedeAdministrarse()`).
4. Al **editar y cambiar la categoría**, si la presentación sigue `POR_PARTICIPAR` se
   recalcula `tiempo_maximo` con el nuevo `Categoria.tiempo_presentacion`.
5. Si se deja `nombre_presentacion` en blanco al editar, se recomputa al nombre completo del
   primer integrante (misma regla que el alta).

### Pruebas

- `tests/Feature/Participantes/AdminParticipantesCrudTest.php` (9 tests): jurado 403,
  listado/búsqueda, alta, duplicado (alta y edición), edición sin cambios permitida,
  eliminación sin vínculos OK y con vínculos bloqueada.
- `tests/Feature/Inscripciones/AdminInscripcionesCrudTest.php` (13 tests): jurado 403, alta
  completa (inscripción+presentación: `tiempo_maximo`, estado `POR_PARTICIPAR`, nombre por
  defecto, pivotes), dúo con nombre propio, reutilización de participantes, FINAL sin
  departamento, errores sin persistir (sin departamento, sin integrantes, exceso de
  integrantes, categoría inexistente), edición con sincronización de integrantes, recálculo de
  `tiempo_maximo` al cambiar categoría, bloqueo de editar/eliminar en `EN_ESCENARIO` y
  eliminación correcta en `POR_PARTICIPAR`.
- Suite completa tras la refactorización: **85 passed (340 assertions)**, incluidos los 26
  tests de importación (sin regresión).

---

## 12. Próxima fase

FASE 3 — Presentaciones y tiempo real: transición de estados controlada
(`POR_PARTICIPAR → EN_ESCENARIO → PARTICIPANDO → FINALIZADO`), asignación de jurados
(`JuradoPresentacion`), cronómetro por presentación reiniciado en cada inicio, controlador
autorizado y sincronización por WebSockets.