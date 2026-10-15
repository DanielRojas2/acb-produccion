# Documentación de Fase 7 — Reportes y Auditoría

## 1. Resumen de lo implementado

En la **Fase 7** se consolidó el cierre operativo y administrativo del XXXIII Festival Nacional de la Canción Boliviana “Aquí… Canta Bolivia”, implementando las herramientas finales de fiscalización, actas notariales de escrutinio, matrices detalladas de evaluación y el registro inmutable de auditoría para todas las calificaciones emitidas y modificadas en el certamen.

Adicionalmente, se estableció la **obligatoriedad estricta** del motivo o justificación técnica en cualquier corrección de calificación realizada por los miembros del jurado.

### Principales funcionalidades entregadas:

1. **Obligatoriedad del Motivo en Corrección de Jurados**:
   - Validación backend en `CorregirCalificacionRequest`: el campo `motivo` es estrictamente requerido, de tipo cadena de texto y con un mínimo de 3 caracteres y máximo de 255.
   - Actualización visual en la interfaz del jurado (`resources/views/jurado/corregir.blade.php`), indicando la justificación obligatoria con indicador visual `*`, atributo `required` y mensajes de error descriptivos.
   - Protección contra envíos incompletos o vacíos garantizada por pruebas automatizadas.

2. **Módulo de Auditoría (`App\Modules\Auditoria`)**:
   - `AuditoriaController`:
     - `index`: Listado paginado y cronológico global de todas las correcciones registradas en `CalificacionHistorial`. Soporta filtros por Categoría, Usuario/Jurado responsable, Rango de fechas (desde/hasta) y búsqueda por texto (participante, nombre de obra o motivo).
     - `show`: Vista técnica pormenorizada con comparación directa entre el snapshot anterior y el nuevo snapshot (notas totales, variaciones `+/-` por parámetro, justificación obligatoria registrada y volcado forense JSON).
   - Vistas Blade con diseño sobrio y moderno: `resources/views/admin/auditoria/index.blade.php` y `resources/views/admin/auditoria/show.blade.php`.

3. **Módulo de Reportes Oficiales y Actas (`App\Modules\Reportes`)**:
   - `GenerarReporteService`:
     - `obtenerResultados`: consolidación de resultados finales por fase y categoría, calculando posiciones y podio con soporte de empates y desglose por jurado calificador.
     - `obtenerSabanaDetallada`: generación de la matriz completa cruzando participantes vs. jurados vs. parámetros evaluados con notas individuales sobre máximos parametrizados.
     - `exportarCsvResultados` / `exportarCsvSabana`: streaming de respuestas HTTP con cabeceras de descarga CSV (UTF-8 con BOM para apertura nativa e inmediata en Microsoft Excel y suites ofimáticas).
   - `ReportesController`:
     - `index`: Centro de reportes con selector dinámico de Fase, Categoría y Departamento, previsualización de tabla y accesos directos.
     - `resultados`: Vista de podio de honor (1°, 2° y 3° lugar) con diseño de gala y tabla oficial consolidada.
     - `acta`: Acta oficial de escrutinio y calificación para Notario de Fe Pública y Comité Organizador, con diseño optimizado para impresión `@media print` (`window.print()`), membretes institucionales, marcas de agua, código de verificación y casillas de firma formal.
     - `sabana`: Matriz técnica interactiva de calificaciones por criterio.
     - `exportarResultadosCsv` y `exportarSabanaCsv`: endpoints de descarga masiva.

4. **Navegación Administrativa**:
   - Conexión e integración completa de los enlaces de "Ranking Público", "Reportes & Actas" y "Auditoría" en el sidebar del administrador (`resources/views/components/menus/admin.blade.php`).

---

## 2. Estructura de Archivos

```text
app/
├── Modules/
│   ├── Auditoria/
│   │   └── Controllers/
│   │       └── AuditoriaController.php
│   ├── Reportes/
│   │   ├── Controllers/
│   │   │   └── ReportesController.php
│   │   └── Services/
│   │       └── GenerarReporteService.php
│   └── Calificaciones/
│       └── Requests/
│           └── CorregirCalificacionRequest.php  (actualizado: motivo obligatorio)
resources/views/
├── admin/
│   ├── auditoria/
│   │   ├── index.blade.php
│   │   └── show.blade.php
│   └── reportes/
│       ├── index.blade.php
│       ├── resultados.blade.php
│       ├── acta.blade.php
│       └── sabana.blade.php
├── components/menus/
│   └── admin.blade.php  (actualizado con rutas activas)
└── jurado/
    └── corregir.blade.php  (actualizado: motivo obligatorio)
tests/Feature/
├── Auditoria/
│   └── AdminAuditoriaTest.php
├── Calificaciones/
│   └── CorreccionMotivoObligatorioTest.php
└── Reportes/
    └── AdminReportesTest.php
```

---

## 3. Modelos y Relaciones

- `CalificacionHistorial`:
  - Pertenece a `Calificacion` (`belongsTo`).
  - Pertenece al `Usuario` que aplicó la modificación (`belongsTo`).
  - Almacena campos JSON `datos_anteriores`, `datos_nuevos`, cadena `motivo` y marca temporal `fecha`.
- `Presentacion`:
  - `jurados()` / `juradosPresentacion()`: relación `hasMany(JuradoPresentacion::class)`.
  - `calificaciones()`: relación `hasManyThrough(Calificacion::class)`.
  - `inscripcion()`: relación `belongsTo(Inscripcion::class)`.
- `Usuario`:
  - Método helper `nombreCompleto()` para estandarizar la presentación en actas y reportes.

---

## 4. Endpoints y Rutas HTTP

### Panel de Administración (`rol:ADMIN`)

| Método | URI | Nombre de Ruta | Descripción |
|---|---|---|---|
| `GET` | `/admin/auditoria` | `admin.auditoria.index` | Listado y filtros de historial de correcciones |
| `GET` | `/admin/auditoria/{historial}` | `admin.auditoria.show` | Detalle forense y comparativo de modificación |
| `GET` | `/admin/reportes` | `admin.reportes.index` | Centro de reportes y actas oficiales |
| `GET` | `/admin/reportes/resultados` | `admin.reportes.resultados` | Vista de pantalla de resultados y podio |
| `GET` | `/admin/reportes/acta` | `admin.reportes.acta` | Acta Notarial print-ready para Notario de Fe Pública |
| `GET` | `/admin/reportes/sabana` | `admin.reportes.sabana` | Sábana de notas detallada por parámetro |
| `GET` | `/admin/reportes/exportar/resultados` | `admin.reportes.exportar.resultados` | Descarga de resultados oficiales en CSV |
| `GET` | `/admin/reportes/exportar/sabana` | `admin.reportes.exportar.sabana` | Descarga de sábana técnica en CSV |

### Panel de Jurados (`rol:JURADO`)

| Método | URI | Nombre de Ruta | Descripción |
|---|---|---|---|
| `POST` | `/jurado/calificaciones/{calificacion}/corregir` | `jurado.calificaciones.corregir` | Corrige calificación requiriendo obligatoriamente `motivo` |

---

## 5. Reglas de Negocio Relevantes

1. **Justificación Obligatoria de Corrección**:
   - Todo jurado o administrador que efectúe una corrección de calificación en estado `FINALIZADO` está obligado a ingresar un motivo de al menos 3 caracteres. No se admite el envío de valores en blanco o nulos.
2. **Inmutabilidad del Historial**:
   - El historial de correcciones no se sobreescribe ni se elimina. Cada corrección genera una nueva fila en `calificacion_historial` con la fotografía exacta del estado anterior y nuevo.
3. **Formato Notarial Legal**:
   - El acta de escrutinio genera las casillas reglamentarias para firma del Presidente del Jurado, Representante de GADOR / Secretaría de Cultura y Notario de Fe Pública, además de los espacios individuales para los jurados calificadores.

---

## 6. Cobertura de Pruebas

Toda la funcionalidad está cubierta por la suite de pruebas automatizadas:

```bash
php artisan test
```

Resultados: **184 tests pasados con 659 aserciones** cubriendo unit tests de servicios y feature tests de controladores, autorizaciones, exportaciones y validaciones.
