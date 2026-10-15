# FASE 5 — Jurado Secreto

> Estado: **COMPLETADA**  
> Proyecto: ACB App — Gestión de calificaciones del XXXIII Festival Nacional de la Canción Boliviana "Aquí… Canta Bolivia"  
> Fecha: 2026-09-14  

---

## 1. Qué se implementó

1. **Migraciones y Extensiones de Esquema**:
   - `categorias`: adición de campos `jurado_secreto_revelado` (boolean), `revelado_at` (timestamp), `finalizada` (boolean) y `finalizada_at` (timestamp).
   - Relación y consistencia garantizada con `jurado_presentacion.es_secreto`.

2. **Selección y Asignación de Jurado Secreto (`AsignarJuradosService`)**:
   - **Preselección (3 jurados)**: validación estricta que bloquea cualquier intento de asignar jurado secreto (`es_secreto` debe ser `false`).
   - **Etapa Final (5 jurados)**: exactamente 1 jurado puede ser designado como secreto (`es_secreto = true`).
   - **Consistencia por Categoría**: todas las presentaciones de una misma categoría en la etapa final comparten el mismo jurado secreto.
   - **Autoasignación Inteligente**: al autoasignar jurados en la Etapa Final, si la categoría ya tiene un jurado secreto asignado, se incluye obligatoriamente; si no lo tiene, se designa automáticamente a uno de los 5 asignados como secreto.
   - Actualización del formulario manual de jurados con selector para designar jurado secreto.

3. **Servicio de Gestión de Jurado Secreto y Cierre (`GestionarJuradoSecretoService`)**:
   - `designarJuradoSecretoCategoria(Categoria, int)`: designación y sincronización transaccional en todas las presentaciones de la categoría en etapa final.
   - `revelarJuradoSecreto(Categoria)`: marca `jurado_secreto_revelado = true`, despachando eventos `JuradoSecretoRevelado` y `RankingActualizado`.
   - `ocultarJuradoSecreto(Categoria)`: revierte el estado de revelación si la categoría no ha sido finalizada.
   - `finalizarCategoria(Categoria)`: marca `finalizada = true`, ejecuta revelación automática si no estaba revelada, despacha `CategoriaFinalizada` y `RankingActualizado`.
   - `reabrirCategoria(Categoria)`: permite reabrir categorías si se requiere corrección operativa.

4. **Regla de Cálculo Interno (`CalcularNotaService`)**:
   - La nota del jurado secreto **participa en el cálculo matemático oficial** desde el instante de su emisión:
     $$\text{nota\_final} = \frac{j_1 + j_2 + j_3 + j_4 + j_5}{5}$$
   - El secreto es **exclusivamente una regla de visibilidad pública**, nunca una alteración matemática.

5. **Servicio de Rankings con Enmascaramiento Reactivo (`CalcularRankingService`)**:
   - `rankingPorCategoria(Categoria, ?Fase, bool $esPublico)`:
     - Ordenamiento estricto por `nota_final` descendente con cálculo de posiciones y empates.
     - **Modo Público**: si `$esPublico && !$categoria->estaRevelada()`, la identidad del jurado secreto se enmascara como `"Jurado Secreto"` y su nota individual se envía como `null` / oculto.
     - **Modo Revelado / Administrativo**: desglose íntegro y sin enmascarar de todos los jurados con sus puntuaciones y desgloses.
   - `resumenCategoriasEtapaFinal()`: resumen de control para el panel de administración con estadísticas en vivo de las 13 categorías.

6. **WebSockets y Difusión en Tiempo Real**:
   - `JuradoSecretoRevelado`: broadcast en canales `presentaciones`, `ranking`, `categoria.{id}`.
   - `CategoriaFinalizada`: broadcast en canales `presentaciones`, `ranking`, `categoria.{id}`.
   - `RankingActualizado`: broadcast en canales `ranking`, `categoria.{id}`.

7. **Vistas e Interfaces de Usuario**:
   - `resources/views/admin/jurados/secreto.blade.php`: panel de control para el Administrador con monitoreo de las 13 categorías, estado de jurado secreto, botón de revelación en vivo y botón de finalización oficial.
   - `resources/views/admin/jurados/index.blade.php`: badge distintivo `🔒 Secreto` y selector de jurado secreto al editar asignaciones.
   - `resources/views/publico/inicio.blade.php`: ranking público en vivo con pills de las 13 categorías, badges de estado, aviso explicativo cuando el secreto está oculto, y recarga reactiva automática vía WebSockets.
   - Menú de navegación administrativo actualizado con acceso directo a `🔒 Jurado Secreto`.

---

## 2. Migraciones

### `2026_09_14_000001_add_estado_and_revelado_to_categorias_table`
| Columna | Tipo | Restricciones | Descripción |
|---|---|---|---|
| `jurado_secreto_revelado` | boolean | default false | Indica si el jurado secreto ha sido revelado al público |
| `revelado_at` | timestamp | nullable | Fecha y hora de la revelación oficial |
| `finalizada` | boolean | default false | Indica si la categoría ha concluido oficialmente |
| `finalizada_at` | timestamp | nullable | Fecha y hora de finalización oficial |

---

## 3. Estructura de Módulos y Archivos

```text
app/
├── Modules/
│   ├── Categorias/
│   │   ├── Events/
│   │   │   └── CategoriaFinalizada.php
│   │   └── Models/
│   │       └── Categoria.php (campos revelado y finalizada + helpers)
│   ├── Jurados/
│   │   ├── Controllers/
│   │   │   ├── AdminJuradoSecretoController.php
│   │   │   └── JuradoController.php (soporte de jurado secreto)
│   │   ├── Events/
│   │   │   └── JuradoSecretoRevelado.php
│   │   └── Services/
│   │       ├── AsignarJuradosService.php (validaciones 5 jurados, etapa final, consistencia)
│   │       └── GestionarJuradoSecretoService.php (ciclo de vida de secreto y cierre)
│   ├── Rankings/
│   │   ├── Events/
│   │   │   └── RankingActualizado.php
│   │   └── Services/
│   │       └── CalcularRankingService.php (ranking por categoría con enmascaramiento)
│   └── Usuarios/
│       └── Controllers/
│           └── InicioController.php (ranking público en tiempo real)
resources/
└── views/
    ├── admin/
    │   └── jurados/
    │       ├── index.blade.php (badge y selector de secreto)
    │       └── secreto.blade.php (dashboard de jurados secretos y cierre)
    ├── components/
    │   └── menus/
    │       └── admin.blade.php (enlace a Jurado Secreto)
    └── publico/
        └── inicio.blade.php (ranking público con revelación reactiva)
routes/
└── web.php (rutas de jurado secreto, revelación y finalización)
```

---

## 4. Endpoints y Rutas

| Método | URI | Nombre de Ruta | Middleware | Descripción |
|---|---|---|---|---|
| `GET` | `/admin/jurados/secreto` | `admin.jurados.secreto` | `auth, rol:ADMIN` | Dashboard de control de jurados secretos y cierre |
| `POST` | `/admin/jurados/secreto/designar` | `admin.jurados.secreto.designar` | `auth, rol:ADMIN` | Designar jurado secreto a una categoría |
| `POST` | `/admin/categorias/{categoria}/revelar` | `admin.categorias.revelar` | `auth, rol:ADMIN` | Revelar jurado secreto de la categoría |
| `POST` | `/admin/categorias/{categoria}/ocultar` | `admin.categorias.ocultar` | `auth, rol:ADMIN` | Ocultar jurado secreto (si no finalizada) |
| `POST` | `/admin/categorias/{categoria}/finalizar` | `admin.categorias.finalizar` | `auth, rol:ADMIN` | Finalizar categoría y revelar secreto |
| `POST` | `/admin/categorias/{categoria}/reabrir` | `admin.categorias.reabrir` | `auth, rol:ADMIN` | Reabrir categoría |
| `GET` | `/?categoria={id}` | `inicio` | Público | Ranking público en vivo con soporte de WebSockets |

---

## 5. Eventos WebSockets

| Evento | Payload | Canales |
|---|---|---|
| `jurado_secreto.revelado` | `categoria_id`, `categoria_nombre`, `jurado_id`, `jurado_nombre`, `revelado_at` | `presentaciones`, `ranking`, `categoria.{id}` |
| `categoria.finalizada` | `categoria_id`, `categoria_nombre`, `finalizada`, `finalizada_at`, `jurado_secreto_revelado` | `presentaciones`, `ranking`, `categoria.{id}` |
| `ranking.actualizado` | `categoria_id`, `categoria_nombre`, `jurado_secreto_revelado`, `finalizada`, `ranking` | `ranking`, `categoria.{id}` |

---

## 6. Cobertura de Pruebas

Se implementaron suites exhaustivas para probar todas las reglas de negocio y restricciones:

- `Tests\Unit\Jurados\JuradoSecretoServiceTest` (8 tests):
  - Asignación de jurado secreto en etapa final (1 de 5).
  - Bloqueo estricto de jurado secreto en preselección.
  - Bloqueo de jurado secreto que no pertenece a los asignados.
  - Autoasignación con jurado secreto en etapa final.
  - Consistencia del jurado secreto en toda la categoría.
  - Cálculo matemático: la nota final incluye al jurado secreto internamente antes y después de la revelación.
  - Revelación de jurado secreto y despacho de eventos en tiempo real.
  - Finalización de categoría y revelación automática.

- `Tests\Unit\Rankings\CalcularRankingServiceTest` (4 tests):
  - Ordenamiento descendente por `nota_final`.
  - Enmascaramiento de notas e identidad del jurado secreto para el público mientras esté oculta.
  - Exposición completa de jurado secreto y puntuación para el público tras revelación.
  - Exposición de notas y desglose completo para el Administrador.

- `Tests\Feature\Jurados\AdminJuradoSecretoTest` (5 tests):
  - Acceso autorizado del administrador al panel.
  - Denegación de acceso (403) para jurados o no autorizados.
  - Designación de jurado secreto vía POST.
  - Revelación y ocultamiento vía POST.
  - Finalización y reapertura de categorías vía POST.

Total de la suite de pruebas: **165 tests pasados (576 assertions)** al 100%.

---

## 7. Guía de Extensión y Mantenimiento

- **Para consultar el estado de revelación**: llamar a `$categoria->estaRevelada()` o `$juradoSecretoService->estaRevelado($categoria)`.
- **Para obtener el ranking estructurado**: invocar `CalcularRankingService::rankingPorCategoria($categoria, $fase, $esPublico)`.
- **Para disparar la actualización tras una corrección**: llamar al evento `RankingActualizado` o a través de `GestionarJuradoSecretoService`.
