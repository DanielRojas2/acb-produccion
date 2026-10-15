# AGENTS.md

## Proyecto

Sistema web en **Laravel 12** para la gestión de calificaciones del XXXIII Festival Nacional de la Canción Boliviana “Aquí… Canta Bolivia”.

El alcance del sistema está limitado a:

- configuración de categorías y parámetros de evaluación;
- participantes, inscripciones y presentaciones;
- asignación de jurados;
- calificaciones por parámetro;
- cálculo de notas finales;
- control del estado de las presentaciones;
- cronómetro de presentación;
- ranking público en tiempo real;
- jurado secreto en la etapa final;
- reportes y auditoría de calificaciones;
- importación de datos operativos desde XLSX, CSV y JSON.

**No convertir este proyecto en un sistema general de gestión de eventos.**

---

## Fuente funcional

La convocatoria oficial 2026 es la fuente para cargar la información fija del festival.

La convocatoria establece 13 categorías y sus parámetros de calificación. La preselección utiliza 3 jurados y la etapa final utiliza 5 jurados. La etapa final se realiza el 20 y 21 de noviembre de 2026 en Oruro, Teatro Internacional Oruro.

La convocatoria debe ser tomada como fuente de la configuración inicial de categorías y parámetros. No crear una segunda carga manual de esta información si ya existe en los seeders.

---

## Estado de avance

- **FASE 0 (arquitectura base, auth, roles, vistas base, WebSockets)** — completada (`47f80ef`).
- **FASE 1 (usuarios, categorías, parámetros, departamentos, fases, seeders oficiales)** — completada (`3ddb0e4`).
- **FASE 2 (importaciones XLSX/CSV/JSON, participantes, inscripciones, presentaciones)** — completada (`99e2de3`); CRUD manual de participantes e inscripciones anexo (`c70b1bd`).
- **FASE 3 (presentaciones, estados, asignación de jurados, cronómetro backend y cliente, WebSockets en tiempo real)** — completada.
- **FASE 4 (motor de calificaciones, notas por parámetro, cálculo de nota final, correcciones auditadas, tiempo real)** — completada.
- **FASE 5 (jurado secreto, selección/asignación, ocultamiento, cálculo interno, revelación, actualización del ranking)** — completada.
- **FASE 6 (ranking público de gala, filtros de categorías/departamentos, modal de mesa de evaluación, tiempo real)** — completada.
- **FASE 7 (reportes oficiales, acta notarial print-ready, sábana de parámetros, auditoría forense, motivo obligatorio en correcciones)** — completada.

Para verificar el estado del código: `php artisan test` (suite completa, sqlite en memoria).

# Reglas críticas de calificación

## Concepto de porcentaje

En este sistema, el porcentaje de un parámetro **no se utiliza como multiplicador de otra nota**.

El porcentaje representa directamente el **máximo de puntos obtenibles por ese parámetro** dentro de una calificación total de 100 puntos.

Ejemplo:

| Parámetro | Mínimo | Máximo |
|---|---:|---:|
| Interpretación vocal | 1 | 30 |
| Interpretación instrumental | 1 | 40 |
| Carisma, dominio de escenario y conocimientos culturales | 1 | 15 |
| Indumentaria representativa del lugar de origen | 1 | 15 |
| **Total** | | **100** |

Por tanto, si un jurado registra:

- Interpretación vocal = 25
- Interpretación instrumental = 36
- Carisma = 12
- Indumentaria = 14

la nota del jurado es:

`25 + 36 + 12 + 14 = 87 puntos`

**No aplicar nuevamente `30%`, `40%`, etc. sobre esos valores.**

### Regla de datos

En `CategoriaParametro`:

- `valor_minimo` = normalmente 1;
- `valor_maximo` = máximo de puntos que aporta el parámetro;
- la suma de `valor_maximo` de todos los parámetros de una categoría debe ser exactamente `100`.

No almacenar un campo `porcentaje` duplicado si su único propósito es repetir `valor_maximo`. El `valor_maximo` ya representa el porcentaje/máximo de puntos de ese parámetro.

La aplicación debe validar:

1. cada valor esté entre `valor_minimo` y `valor_maximo`;
2. no existan parámetros duplicados dentro de una categoría;
3. la suma de `valor_maximo` de la categoría sea 100 antes de habilitar la evaluación.

---

# DER definitivo

## Entidades principales

```text
Usuario
 ├── id
 ├── username
 ├── nombre
 ├── paterno
 ├── materno
 ├── ci
 ├── email
 ├── contrasenia
 ├── rol              // ADMIN | JURADO
 ├── puede_controlar_tiempo
 ├── created_at
 └── updated_at

Categoria
 ├── id
 ├── categoria
 ├── max_participantes
 ├── tiempo_presentacion
 ├── created_at
 └── updated_at

Parametro
 ├── id
 ├── parametro
 ├── created_at
 └── updated_at

CategoriaParametro
 ├── id_categoria
 ├── id_parametro
 ├── valor_minimo
 ├── valor_maximo
 └── orden

Departamento
 ├── id
 └── nombre

Fase
 ├── id
 ├── fase              // PRESELECCION | FINAL
 └── departamento_id   // nullable en FINAL

Participante
 ├── id
 ├── nombre
 ├── paterno
 ├── materno
 ├── pseudonimo
 ├── created_at
 └── updated_at

InscripcionParticipante
 ├── id_participante
 └── id_inscripcion

Presentacion
 ├── id
 ├── id_inscripcion
 ├── nombre_presentacion
 ├── estado
 ├── tiempo_maximo
 ├── hora_inicio
 ├── hora_fin
 ├── cronometro_iniciado_at
 ├── cronometro_finalizado_at
 ├── nota_final
 ├── created_at
 └── updated_at

Inscripcion
 ├── id
 ├── fecha_inscripcion
 ├── hora_presentacion
 ├── fecha_presentacion
 ├── id_fase
 ├── id_categoria
 ├── id_departamento
 └── estado

InscripcionParticipante
 ├── id_participante
 └── id_inscripcion

Presentacion
 ├── id
 ├── id_inscripcion
 ├── estado
 ├── tiempo_maximo
 ├── hora_inicio
 ├── hora_fin
 ├── cronometro_iniciado_at
 ├── cronometro_finalizado_at
 ├── nota_final
 ├── created_at
 └── updated_at

JuradoPresentacion
 ├── id
 ├── id_presentacion
 ├── id_usuario
 ├── es_secreto
 ├── es_controlador
 ├── estado
 └── created_at

Calificacion
 ├── id
 ├── id_jurado_presentacion
 ├── nota_total
 ├── estado
 ├── fecha_inicio
 ├── fecha_finalizacion
 ├── fecha_modificacion
 ├── created_at
 └── updated_at

DetalleCalificacion
 ├── id
 ├── id_calificacion
 ├── id_categoria_parametro
 └── valor

CalificacionHistorial
 ├── id
 ├── id_calificacion
 ├── id_usuario
 ├── datos_anteriores
 ├── datos_nuevos
 └── fecha
```

## Relaciones

```text
Departamento 1 ─── N Fase
Departamento 1 ─── N Inscripcion

Categoria 1 ─── N CategoriaParametro
Parametro 1 ─── N CategoriaParametro

Categoria 1 ─── N Inscripcion
Fase 1 ─── N Inscripcion

Inscripcion 1 ─── N InscripcionParticipante
Participante 1 ─── N InscripcionParticipante

Inscripcion 1 ─── 1 Presentacion

Presentacion 1 ─── N JuradoPresentacion
Usuario 1 ─── N JuradoPresentacion

JuradoPresentacion 1 ─── 1 Calificacion

Calificacion 1 ─── N DetalleCalificacion
CategoriaParametro 1 ─── N DetalleCalificacion

Calificacion 1 ─── N CalificacionHistorial
Usuario 1 ─── N CalificacionHistorial
```

### Cadena principal

```text
Categoria
   ↓
CategoriaParametro
   ↓
Inscripcion
   ↓
Presentacion
   ↓
JuradoPresentacion
   ↓
Calificacion
   ↓
DetalleCalificacion
```

---

# Restricciones de base de datos

Implementar índices y restricciones para:

- `CategoriaParametro`: PK compuesta `(id_categoria, id_parametro)`;
- `InscripcionParticipante`: PK compuesta `(id_participante, id_inscripcion)`;
- `JuradoPresentacion`: único `(id_presentacion, id_usuario)`;
- `Calificacion`: único `id_jurado_presentacion`;
- `DetalleCalificacion`: único `(id_calificacion, id_categoria_parametro)`;
- claves foráneas con comportamiento coherente con el ciclo de vida de los datos;
- índices para búsquedas de presentaciones por estado, fecha y categoría;
- índices para calificaciones por presentación y jurado.

La aplicación debe impedir más de un jurado secreto por categoría/fase final según la asignación realizada para las presentaciones de esa categoría.

---

# Categorías y seeders

Crear los seeders iniciales basados en la convocatoria oficial.

No pedir al administrador que vuelva a introducir manualmente las categorías y sus parámetros que ya están definidos en el seeder.

## Categorías

1. Ñusta
2. Solista vocal (con o sin acompañamiento)
3. Solista instrumental – Armónico
4. Solista instrumental – Melódico (con o sin acompañamiento)
5. Dúo vocal instrumental
6. Trío vocal instrumental
7. Grupo vocal instrumental
8. Estudiantina
9. Autóctono
10. Composición
11. Solista vocal infantil (con o sin acompañamiento)
12. Solista instrumental infantil (con o sin acompañamiento)
13. Especial

## Parámetros y máximos

### 1. Ñusta

- Interpretación vocal — 30
- Interpretación instrumental — 40
- Carisma, dominio de escenario y conocimientos culturales — 15
- Indumentaria representativa del lugar de origen — 15

### 2. Solista vocal

- Interpretación — 40
- Técnica vocal — 30
- Expresión y dominio de escenario — 20
- Indumentaria — 10

### 3. Solista instrumental - Armónico

- Dominio instrumental — 50
- Interpretación y expresión — 30
- Indumentaria — 20

### 4. Solista instrumental - Melódico

- Dominio instrumental — 50
- Interpretación y expresión — 30
- Indumentaria — 20

### 5. Dúo vocal instrumental

- Interpretación vocal y armonía — 30
- Interpretación instrumental — 30
- Expresión y dominio de escenario — 25
- Indumentaria — 15

### 6. Trío vocal instrumental

- Interpretación vocal y armonía — 30
- Interpretación instrumental — 30
- Expresión y dominio de escenario — 25
- Indumentaria — 15

### 7. Grupo vocal instrumental

- Interpretación vocal y armonía — 35
- Interpretación y arreglos instrumentales — 35
- Expresión y dominio de escenario — 15
- Indumentaria — 15

### 8. Composición

- Estructura musical — 40
- Letra — 40
- Originalidad — 20

### 9. Estudiantina

- Interpretación y ejecución instrumental — 40
- Arreglos musicales propios (Armonía) — 40
- Indumentaria — 20

### 10. Autóctono

- Dominio instrumental — 30
- Armonía instrumental — 30
- Dominio escénico y originalidad — 20
- Indumentaria — 20

### 11. Solista vocal infantil

- Interpretación — 50
- Expresión y dominio de escenario — 20
- Técnica vocal — 15
- Indumentaria — 15

### 12. Solista instrumental infantil

- Interpretación instrumental — 60
- Expresión y dominio de escenario — 30
- Indumentaria — 10

### 13. Especial

- Técnica vocal — 35
- Técnica instrumental — 35
- Dominio escénico y originalidad — 15
- Indumentaria — 15

Todos los máximos anteriores suman 100 puntos por categoría.

### Cantidad de ganadores por categoría (Premiaciones y Podio)

- **Categorías con 1 solo ganador:**
  - **Ñusta** (`Categoria.esNusta()`)
  - **Especial** (`Categoria.esEspecial()`)
  En estas dos categorías solo se premia y proclama al **1° Lugar** (Ganador Único / Ñusta del Festival / Ganador Especial). No existe podio de 3 escalones ni 2° ni 3° lugar premiados; los demás participantes integran la clasificación general tras el ganador.

- **Demás categorías (11 restantes):**
  - Solista vocal, Solista instrumental armónico, Solista instrumental melódico, Dúo vocal instrumental, Trío vocal instrumental, Grupo vocal instrumental, Estudiantina, Autóctono, Composición, Solista vocal infantil y Solista instrumental infantil.
  Tienen podio de honor de **3 ganadores** (1°, 2° y 3° lugar).

---

# Fases

La estructura debe diferenciar claramente:

- `PRESELECCION`: realizada por departamento.
- `FINAL`: etapa nacional.

No usar `Fase.departamento` como sustituto de una entidad Departamento. La fase identifica la etapa; la inscripción identifica el departamento cuando corresponda.

La convocatoria establece:

- preselección con 3 jurados;
- selección de un finalista por departamento y categoría;
- etapa final con 5 jurados.

---

# Nombre de presentación / pseudónimo

Una inscripción puede tener uno o varios participantes.

Esto es especialmente necesario para categorías donde la presentación representa a un conjunto:

- Dúo vocal instrumental;
- Trío vocal instrumental;
- Estudiantina;
- Especial.

La aplicación debe permitir definir un **nombre de presentación** para evitar tener que mostrar o anunciar todos los nombres individuales.

El nombre debe pertenecer a la `Presentacion`, no sustituir los participantes registrados en `InscripcionParticipante`.

Ejemplo:

```text
Inscripción:
  Juan Pérez
  María López

Presentación:
  nombre_presentacion = "Dúo Andino"
```

Los participantes individuales continúan registrados y relacionados con la inscripción para fines administrativos, reportes y trazabilidad.

### Reglas

- `Presentacion.nombre_presentacion` puede contener el pseudónimo/nombre artístico con el que se anunciará la presentación.
- Para categorías individuales puede utilizarse el nombre o pseudónimo del participante.
- Para Dúo, Trío, Estudiantina y Especial debe poder definirse un nombre de presentación.
- No obligar a repetir los nombres individuales en las vistas de escenario, jurado y ranking cuando exista `nombre_presentacion`.
- El ranking y las calificaciones pertenecen a la **presentación/inscripción**, no a cada integrante individual.
- `Participante.pseudonimo` sigue siendo útil para identificar a un participante, pero no debe confundirse con `Presentacion.nombre_presentacion`.

# Flujo de presentación

La presentación utiliza los siguientes estados:

```text
POR_PARTICIPAR
      ↓
EN_ESCENARIO
      ↓
PARTICIPANDO
      ↓
FINALIZADO
```

El cambio de estado debe validarse mediante una transición controlada. No permitir cambios arbitrarios desde cualquier estado.

---

# Cronómetro de presentación

`Categoria.tiempo_presentacion` define el tiempo máximo permitido **para cada presentación individual**.

Cuando se crea una `Presentacion`, su `tiempo_maximo` debe copiar el valor configurado en la categoría.

El cronómetro pertenece a la presentación actual y **siempre debe reiniciarse al comenzar una nueva presentación**.

Ejemplo:

```text
Categoría: Grupo Vocal Instrumental
tiempo_presentacion = 10 minutos

Presentación A:
  cronómetro = 10:00 → inicia → finaliza

Presentación B:
  cronómetro = 10:00 → inicia → finaliza
```

Nunca reutilizar el tiempo transcurrido de una presentación anterior.

Un jurado autorizado como controlador (`Usuario.puede_controlar_tiempo = true` y/o `JuradoPresentacion.es_controlador = true`) puede:

1. iniciar el cronómetro de la presentación actual;
2. detener/finalizar el cronómetro;
3. visualizar el tiempo transcurrido;
4. visualizar el tiempo restante;
5. recibir el estado del cronómetro en tiempo real.

Al iniciar:

```text
cronometro_iniciado_at = timestamp actual
```

Al finalizar:

```text
cronometro_finalizado_at = timestamp actual
```

Al pasar a una nueva presentación:

```text
cronometro_iniciado_at = null
cronometro_finalizado_at = null
```

y el contador vuelve a:

```text
tiempo_maximo
```

El backend debe ser la fuente de verdad. No depender de un contador JavaScript aislado para determinar el tiempo oficial.

El frontend debe calcular/mostrar el tiempo a partir de las marcas de tiempo y sincronizar el estado mediante WebSockets.

El cronómetro debe funcionar independientemente de la calificación.

# Calificaciones

## Asignación de jurados

Antes de calificar debe existir `JuradoPresentacion`.

Cada jurado asignado a una presentación tendrá una única `Calificacion`.

En preselección:

```text
3 jurados
```

En etapa final:

```text
5 jurados
```

Un jurado no puede registrar dos votos para la misma presentación.

---

# Registro de una calificación

Cada `DetalleCalificacion` corresponde a un `CategoriaParametro` de la categoría de la presentación.

El jurado registra valores enteros o decimales según la precisión definida por la aplicación, siempre dentro del rango:

```text
valor_minimo <= valor <= valor_maximo
```

La suma de los detalles es la `nota_total` del jurado.

Ejemplo:

```text
30 máximo → jurado da 25
40 máximo → jurado da 36
15 máximo → jurado da 12
15 máximo → jurado da 14

nota_total = 87
```

La nota total debe calcularse en backend. No confiar en un valor enviado por el navegador.

---

# Nota final del participante

## Preselección

Con 3 jurados:

```text
nota_final = (jurado_1 + jurado_2 + jurado_3) / 3
```

## Etapa final

Con 5 jurados:

```text
nota_final = (jurado_1 + jurado_2 + jurado_3 + jurado_4 + jurado_5) / 5
```

El jurado secreto **sí participa en el cálculo desde el momento en que registra su calificación**.

Su condición de secreto afecta únicamente la visibilidad.

No crear una fórmula diferente para el jurado secreto.

---

# Jurado secreto

En la etapa final:

- deben existir 5 jurados;
- exactamente uno puede estar marcado como `es_secreto`;
- su identidad/nota no debe mostrarse en la vista pública mientras la categoría no esté finalizada;
- su nota sí debe participar en el cálculo interno;
- cuando el administrador finalice la categoría, la nota secreta pasa a ser visible;
- después de revelar la nota se debe recalcular/actualizar el ranking público.

El secreto es una regla de presentación de información, no una regla matemática.

---

# Correcciones de calificaciones

Un jurado puede corregir su voto cuando la presentación esté `FINALIZADO`, según las reglas de negocio de la aplicación.

Toda modificación debe:

1. actualizar la calificación actual;
2. recalcular la nota del jurado;
3. recalcular la nota final del participante;
4. actualizar el ranking;
5. registrar la modificación en `CalificacionHistorial`.

No eliminar físicamente el historial de correcciones.

---

# Nota final y ranking

`Presentacion.nota_final` representa la nota agregada oficial de la presentación.

El ranking no debe almacenarse como una tabla permanente salvo que una fase posterior lo requiera expresamente.

El orden debe calcularse a partir de `nota_final`.

Cuando exista un cambio de calificación:

```text
Calificacion
    ↓
nota_total jurado
    ↓
nota_final presentación
    ↓
ranking
    ↓
WebSocket
    ↓
vistas públicas/jurados
```

---

# Tiempo real

Utilizar WebSockets para comunicar:

- cambio de estado de presentación;
- inicio y finalización del cronómetro;
- cambios de calificación;
- actualización de nota final;
- actualización del ranking;
- revelación del jurado secreto;
- finalización de una categoría.

El servidor es la fuente de verdad.

El cliente solo representa el estado recibido.

---

# Arquitectura Laravel

Organizar el código por funcionalidad/módulo y no crear una estructura genérica gigantesca de Controllers/Models.

Preferir una organización similar a:

```text
app/
├── Modules/
│   ├── Usuarios/
│   ├── Categorias/
│   ├── Parametros/
│   ├── Departamentos/
│   ├── Fases/
│   ├── Participantes/
│   ├── Inscripciones/
│   ├── Presentaciones/
│   ├── Jurados/
│   ├── Calificaciones/
│   ├── Rankings/
│   ├── Importaciones/
│   ├── Reportes/
│   └── Auditoria/
└── Support/
    ├── Enums/
    ├── Exceptions/
    └── Helpers/
```

Dentro de cada módulo usar solamente las capas que realmente necesite la funcionalidad.

Ejemplo:

```text
app/Modules/Calificaciones/
├── Models/
│   ├── Calificacion.php
│   └── DetalleCalificacion.php
├── Services/
│   ├── RegistrarCalificacionService.php
│   ├── CalcularNotaService.php
│   └── CorregirCalificacionService.php
├── Events/
└── Exceptions/
```

No crear capas vacías por obligación.

---

# Servicios de negocio importantes

Separar la lógica compleja en servicios.

Como mínimo:

```text
RegistrarInscripcionService
CalcularNotaService
RegistrarCalificacionService
CorregirCalificacionService
GestionarPresentacionService
GestionarCronometroService
CalcularRankingService
GestionarJuradoSecretoService
```

Las operaciones que modifican calificaciones y estados deben ejecutarse con transacciones cuando corresponda.

---

# Importaciones

Los datos operativos pueden importarse desde:

- XLSX;
- CSV;
- JSON.

El administrador debe seleccionar el formato y ejecutar la importación.

Las importaciones deben validar los datos antes de persistirlos y reportar errores claramente.

Los seeders se reservan para datos de configuración/fijos del festival, especialmente:

- categorías;
- parámetros;
- relación categoría-parámetro;
- configuración inicial necesaria.

No duplicar en importaciones los datos que ya están definidos por seeders.

---

# CRUD manual de Participantes e Inscripciones

El panel de administración también permite registrar participantes e inscripciones
directamente, como alternativa o complemento a la importación por archivo. Un participante
suelto no participa del festival hasta formar parte de una inscripción/presentación.

Reglas implementadas (respetar en futuros cambios):

- **Duplicados bloqueados**: no se crea un participante con el mismo `nombre + paterno +
  materno` ya existente. Es la misma clave de reutilización que usa la importación.
- **No eliminar un participante** vinculado a inscripciones (403). El FK del pivote
  `InscripcionParticipante` usa `cascadeOnDelete`, por eso la protección debe aplicarse en el
  controller; no confiar en la base de datos.
- Las inscripciones solo pueden **editarse/eliminarse** mientras su presentación esté en
  `POR_PARTICIPAR` (helper `Inscripcion::puedeAdministrarse()`). Cuando la presentación
  inició o terminó (`EN_ESCENARIO`, `PARTICIPANDO`, `FINALIZADO`), devolver 403.
- Al **cambiar la categoría en edición** y la presentación siga en `POR_PARTICIPAR`, se
  **recalcula `Presentacion.tiempo_maximo`** con el nuevo `Categoria.tiempo_presentacion`.
- Si `nombre_presentacion` queda en blanco se **recomputa** al nombre completo del primer
  integrante (misma regla que el alta y la importación).
- **`RegistrarInscripcionService`** (`validar`, `registrar`, `actualizar`) es la lógica
  compartida entre la importación de archivos y el CRUD manual. No duplicar esta lógica en
  otro servicio o controller.

---

# Vistas

## Administrador

Debe permitir:

- administrar usuarios;
- configurar/ver categorías y parámetros;
- gestionar importaciones;
- controlar presentaciones;
- controlar cronómetro cuando tenga autorización;
- finalizar categorías;
- revelar jurado secreto;
- consultar reportes;
- consultar historial/auditoría.

## Jurado

Debe permitir:

- visualizar la presentación actual;
- visualizar estado y cronómetro;
- registrar calificaciones;
- corregir su calificación cuando esté permitido;
- visualizar la información necesaria para evaluar.

El jurado no debe poder modificar la calificación de otro jurado.

## Público

Debe permitir:

- visualizar participantes;
- visualizar notas/ranking;
- actualizarse en tiempo real;
- ocultar la calificación secreta hasta la finalización de la categoría.

---

# Reportes

El administrador debe disponer como mínimo de:

1. calificaciones por jurado y participante;
2. detalle de calificación por parámetro;
3. nota final del participante;
4. ranking ordenado;
5. historial de correcciones.

Los reportes deben permitir identificar claramente:

```text
Fase
Categoría
Departamento
Participante
Jurados
Parámetros
Notas
Nota final
Posición
```

---

# Seguridad

- Usar autenticación y autorización de Laravel.
- Aplicar Policies/Gates donde corresponda.
- Un jurado solo puede acceder a sus propias calificaciones.
- El control del cronómetro debe requerir autorización.
- La nota del jurado secreto nunca debe filtrarse accidentalmente al cliente público antes de su revelación.
- No confiar en datos calculados enviados por frontend.
- Validar todas las entradas.
- Registrar operaciones sensibles.

---

# Testing

No crear pruebas unitarias innecesarias para cada CRUD.

Priorizar pruebas para lógica crítica:

```text
CalcularNotaServiceTest
RankingServiceTest
JuradoSecretoServiceTest
GestionarCronometroServiceTest
```

El CRUD manual de participantes e inscripciones tiene cobertura en:

- `tests/Feature/Participantes/AdminParticipantesCrudTest.php`;
- `tests/Feature/Inscripciones/AdminInscripcionesCrudTest.php`.

Al modificar la lógica de `RegistrarInscripcionService` no romper los 26 tests de
importación (`tests/Unit/Importaciones/ImportacionLectoresTest.php`,
`tests/Feature/Importaciones/*`).

Casos importantes:

- suma correcta de parámetros;
- rechazo de valores fuera de rango;
- validación de suma máxima 100;
- promedio correcto con 3 jurados;
- promedio correcto con 5 jurados;
- jurado secreto incluido en cálculo;
- jurado secreto oculto hasta finalizar categoría;
- corrección de una calificación;
- actualización del ranking;
- inicio/finalización correcta del cronómetro;
- impedir doble voto del mismo jurado.

---

# Desarrollo por fases

Respetar estrictamente estas fases y no adelantar funcionalidades sin necesidad.

## Fase 0 — Arquitectura base

- estructura Laravel 12;
- autenticación;
- roles;
- base de vistas Admin/Jurado/Público;
- WebSockets;
- assets base;
- identidad visual.

## Fase 1 — Configuración del festival

- usuarios;
- categorías;
- parámetros;
- categoría-parámetro;
- departamentos;
- fases;
- seeders oficiales.

## Fase 2 — Importaciones y datos operativos

- XLSX;
- CSV;
- JSON;
- participantes;
- inscripciones;
- relaciones;
- presentaciones;
- nombre/pseudónimo de presentación cuando corresponda a Dúo, Trío, Estudiantina y Especial.

## Fase 3 — Presentaciones y tiempo real (Completada)

- estados de presentación;
- asignación de jurados;
- WebSockets y difusión en tiempo real;
- cronómetro reiniciado por cada presentación y sincronizado en vivo con el cliente;
- controlador autorizado;
- visualización del nombre de presentación y transiciones dinámicas en escenario.

## Fase 4 — Motor de calificaciones (Completada)

- calificación por parámetro;
- nota por jurado;
- nota final;
- correcciones;
- validaciones;
- pruebas de lógica.

## Fase 5 — Jurado secreto

- selección/asignación;
- ocultamiento;
- cálculo interno;
- revelación;
- actualización del ranking.

## Fase 6 — Ranking público (Completada)

- diseño de gala oscuro con estética inspirada en La Velada (esquinas doradas, tipografía Cinzel/Outfit y badges elegantes);
- botón de ingreso oculto para la vista pública;
- filtrado lateral por departamentos y categorías con conteos dinámicos en badges;
- visualización intercambiable entre cuadrícula de tarjetas de gala y tabla oficial;
- terminología solemne ("Puntaje Preliminar" / "Puntaje Oficial Consolidado" / "Calificación Reservada");
- desglose detallado de mesa de evaluación en modal interactivo;
- sincronización en tiempo real vía WebSockets en el canal `ranking` y `presentaciones`.

## Fase 7 — Reportes y auditoría (Completada)

- reportes consolidados por categoría y fase;
- historial y auditoría forense de correcciones;
- resultados finales y podio de honor;
- acta oficial de escrutinio para Notaría de Fe Pública (print-ready);
- sábana técnica de evaluación por parámetro;
- exportaciones en formato CSV estructurado;
- motivo o justificación obligatoria para corrección de notas por jurados.

---

# Regla de documentación

Al terminar cada fase se debe generar un documento `.md` o `.docx` que explique:

- qué se implementó;
- estructura de archivos;
- modelos y relaciones;
- servicios;
- rutas/endpoints;
- eventos WebSocket;
- reglas de negocio relevantes;
- decisiones técnicas;
- cómo modificar/extender la funcionalidad.

La documentación debe ser práctica para mantenimiento futuro, no una explicación genérica de Laravel.

---

# Regla general para el agente

Antes de modificar código:

1. revisar la estructura existente;
2. revisar los modelos/migraciones actuales;
3. respetar las decisiones de fases anteriores;
4. evitar refactors amplios no solicitados;
5. no duplicar funcionalidad;
6. mantener la lógica de negocio en backend;
7. mantener el DER como referencia de las relaciones;
8. si existe conflicto entre código y esta especificación, señalarlo antes de hacer cambios estructurales importantes.

La prioridad es construir un sistema de calificaciones confiable, auditable y preparado para operación en vivo.
