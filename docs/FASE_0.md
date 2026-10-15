# FASE 0 — Arquitectura base

> Estado: **COMPLETADA**
> Proyecto: ACB App — Gestión de calificaciones del XXXIII Festival Nacional de la Canción Boliviana "Aquí… Canta Bolivia"
> Fecha: 2026-09-11

---

## 1. Qué se implementó

Se levantó la base completa del sistema sobre la cual se construirán las fases 1 a 7:

1. Entorno de desarrollo contenerizado para **MySQL + phpMyAdmin**.
2. Aplicación **Laravel 12** instalada y conectada a MySQL.
3. Sistema de autenticación (**Laravel Breeze**, Blade + Tailwind) adaptado al dominio.
4. Modelo de usuario **`Usuario`** con rol (`ADMIN`/`JURADO`) y control de autorización por rol.
5. Estructura modular `app/Modules/<Dominio>/` según el AGENTS.md.
6. Tres layouts base con identidad visual del festival: **Admin**, **Jurado** y **Público**.
7. **WebSockets** (Laravel Reverb) configurados y verificados con conexión real.
8. Repositorio Git con commit inicial.

---

## 2. Stack y entorno

| Componente | Versión | Nota |
|---|---|---|
| PHP | 8.4 host (Debian 13) | Todas las extensiones requeridas presentes |
| Laravel | 12 | `laravel/framework ^12.0` |
| MySQL | 8.0 en contenedor | Puerto expuesto host `3307` |
| phpMyAdmin | latest | Accesible en `http://127.0.0.1:8080` |
| WebSocket | Laravel Reverb `^1.11` | Puerto `6080` |
| Frontend | Blade + Tailwind CSS (v4 via Vite) | |
| ASTM | Auth | Laravel Breeze |

### Decisión técnica: contenedor vs instalación local

El AGENTS.md y el plan pidieron Docker Compose para MySQL + phpMyAdmin. Durante la ejecución se detectó que:

- **Docker Engine no está instalado** en el equipo y su instalación requiere `sudo` con contraseña (no disponible en este entorno).
- El sistema ya contaba con **Podman 5.4.2 (rootless) + podman-compose 1.3.0**, que ejecutan el mismo estándar `docker-compose.yml` sin `sudo`.

**Decisión:** se utilizó Podman (compatible Docker) para levantar MySQL 8.0 y phpMyAdmin. El `docker-compose.yml` es estándar: si en el futuro se dispone de Docker Engine, se levanta con `docker compose up -d` sin cambios.

Además, el **MariaDB 11.8** ya instalado en el host ocupaba el puerto 3306 y exigía `sudo` para administrar (autenticación `unix_socket`). Se dejó intacto y el contenedor MySQL usa el puerto host **3307** para evitarlo.

---

## 3. Estructura de archivos

```
acb_app/
├── AGENTS.md                        # especificación del proyecto
├── docker-compose.yml               # servicios: mysql + phpmyadmin (podman/docker)
├── docker/
│   └── mysql/
│       ├── data/                    # datos persistentes (ignorado por git)
│       └── init/001_database.sql    # inicialización del esquema/sql
├── app/
│   ├── Modules/
│   │   ├── Usuarios/
│   │   │   ├── Models/Usuario.php
│   │   │   └── Controllers/
│   │   │       ├── DashboardController.php
│   │   │       └── InicioController.php
│   │   └── (Categorias, Parametros, Departamentos, Fases, Participantes,
│   │        Inscripciones, Presentaciones, Jurados, Calificaciones,
│   │        Rankings, Importaciones, Reportes, Auditoria) — esqueletos
│   └── Support/
│       ├── Enums/RolEnum.php
│       ├── Exceptions/.gitkeep
│       └── Helpers/.gitkeep
├── bootstrap/app.php                # registro de alias de middleware
├── config/
│   ├── auth.php                     # guard → model Usuario
│   └── reverb.php                   # WebSockets (puerto 6080)
├── database/
│   ├── factories/UsuarioFactory.php
│   ├── migrations/0001_01_01_000000_create_users_table.php   # esquema AGENTS
│   └── seeders/DatabaseSeeder.php   # usuario admin inicial
├── routes/
│   ├── web.php                      # grupos por rol ADMIN/JURADO + público
│   ├── auth.php                     # Breeze
│   └── channels.php                 # canales base de tiempo real
├── resources/views/
│   ├── components/layouts/
│   │   ├── admin.blade.php
│   │   ├── jurado.blade.php
│   │   └── publico.blade.php
│   ├── admin/inicio.blade.php
│   ├── jurado/inicio.blade.php
│   └── publico/inicio.blade.php
└── public/images/                   # logo, favicon y fondo (identidad visual)
```

### Esqueletos de módulos

Los módulos de fases posteriores se crearon como directorios con `.gitkeep` en `Models/`,
`Services/` y `Controllers/`. **No crear capas vacías innecesarias:** cada módulo se
completa con las capas que realmente requiera su funcionalidad.

---

## 4. Modelos y esquema de base de datos

### Tabla `users` (nombre del dominio: `Usuario`)

| Columna | Tipo | Notas |
|---|---|---|
| id | bigint | PK |
| username | string, unique, nullable | Alternativa de login |
| nombre | string | Obligatorio |
| paterno / materno | string, nullable | |
| ci | string, nullable | |
| email | string, unique | Usado por Breeze |
| password | string | Framework Laravel (ver decisión técnica) |
| rol | string (enum) | `ADMIN` \| `JURADO`, default `JURADO` |
| puede_controlar_tiempo | boolean | Autorización para cronómetro |
| email_verified_at | timestamp nullable | |
| remember_token | string | |
| created_at / updated_at | timestamps | |

### Decisión técnica: `password` vs `contrasenia`

El DER del AGENTS.md define `contrasenia`, pero Laravel/Breeze autentican contra la columna
`password`. **Se mantiene `password`** y se documenta la desviación para no romper la
autenticación nativa. El resto de columnas del DER se respetan al nombrarse en español
(`nombre`, `paterno`, `materno`, `rol`, `puede_controlar_tiempo`).

### Modelo `Usuario`

- Namespace: `App\Modules\Usuarios\Models\Usuario`
- `$table = 'users'`
- Casts: `password → hashed`, `rol → RolEnum`, `puede_controlar_tiempo → boolean`
- Ayudantes: `esAdmin()`, `esJurado()`, `puedeControlarTiempo()`
- Factory: `Database\Factories\UsuarioFactory` (resuelta vía `newFactory()` porque el
  modelo vive en un namespace de módulo).

---

## 5. Roles y autorización

### Enum `RolEnum` (`app/Support/Enums/RolEnum.php`)

```php
enum RolEnum: string {
    case ADMIN  = 'ADMIN';
    case JURADO = 'JURADO';
}
```

### Middleware `rol`

- Clase: `App\Http\Middleware\EnsureUserHasRol`
- Alias registrado en `bootstrap/app.php`: `rol`
- Uso: `->middleware('rol:ADMIN')` o `'rol:ADMIN,JURADO'`
- Si el rol no califica → `403 Forbidden`.

### Flujo de acceso

```
login (email+password)
   └─ /dashboard ──> redirige por rol
        ├─ ADMIN  ──> /admin/inicio
        └─ JURADO ──> /jurado/inicio
```

---

## 6. Rutas y endpoints

| Método | URI | Nombre | Middleware | Controlador |
|---|---|---|---|---|
| GET | `/` | `inicio` | público | `InicioController@publico` |
| GET | `/login`, POST `/login` | `login` | guest | Breeze |
| GET/POST | `/register` | `register` | guest | Breeze (rol JURADO por defecto) |
| GET | `/dashboard` | `dashboard` | auth+verified | `DashboardController` (redirige por rol) |
| GET | `/admin/inicio` | `admin.inicio` | auth+verified+`rol:ADMIN` | `InicioController@admin` |
| GET | `/jurado/inicio` | `jurado.inicio` | auth+verified+`rol:JURADO` | `InicioController@jurado` |
| GET/PATCH/DELETE | `/profile` | `profile.*` | auth | Breeze |
| GET | `/up` | health | público | Laravel |

---

## 7. Vistas y layouts

Tres layouts como **componentes anónimos** (enfoque moderno de Blade, sin clases PHP):

| Componente | Uso | Contenido |
|---|---|---|
| `<x-layouts.admin>` | Panel admin | Sidebar oscuro + logo + menú Festival/Operación/Resultados + usuario + logout |
| `<x-layouts.jurado>` | Panel jurado | Sidebar simplificado (presentaciones, calificar, cronómetro) |
| `<x-layouts.publico>` | Sitio público | Header con logo, imagen de fondo, ranking en vivo |

Cada layout inyecta:
- Favicon `/images/favicon.ico`
- CSS/JS de Vite
- Bloque `@stack('scripts')`
- Slots: cuerpo (`$slot`), `header` (título) y `sidebar` (navegación)

Identidad visual: `public/images/logo-oficial.png`, `favicon.ico` y `fondo.png` copiados desde `recursos/`.

---

## 8. WebSockets (Laravel Reverb)

### Configuración

| Variable | Valor |
|---|---|
| `REVERB_HOST` / `REVERB_PORT` | `127.0.0.1` / `6080` |
| `REVERB_SERVER_HOST` / `REVERB_SERVER_PORT` | `127.0.0.1` / `6080` |
| `BROADCAST_CONNECTION` | `reverb` |

> **Nota:** el servidor Reverb escucha en `REVERB_SERVER_PORT`; el cliente se conecta a
> `REVERB_HOST:REVERB_PORT`. Ambos deben apuntar a 6080 en local. El puerto 8080 queda
> reservado para phpMyAdmin.

### Canales base (`routes/channels.php`)

| Canal | Acceso |
|---|---|
| `App.Models.Usuario.{id}` | Solo el propio usuario |
| `presentaciones` | ADMIN y JURADO |
| `presentaciones.{id}` | ADMIN y JURADO |
| `ranking` | Público (todos) |

### Verificación realizada

- `php artisan reverb:start` → `INFO Starting server on 127.0.0.1:6080`.
- Handshake WebSocket (Node): `101 Switching Protocols` y evento
  `pusher:connection_established`.

**URL de conexión correcta:** `ws://127.0.0.1:6080/app/{APP_KEY}` (la ruta usa la **app key**,
no el app id). La ruta es `/app/{appKey}` según `Servers/Reverb/Factory.php`.

---

## 9. Cómo ejecutar / levantar el entorno

```bash
# 1. Contenedores (MySQL 8.0 + phpMyAdmin) — con podman-compose o docker compose
podman-compose up -d

# 2. Aplicación web de desarrollo
php artisan serve --host=127.0.0.1 --port=8000

# 3. WebSockets (terminal aparte)
php artisan reverb:start

# 4. Assets (solo la primera vez o tras cambios en recursos/views)
npm run build      # producción
npm run dev        # watch (requiere artisanserve activo)

# 5. Base de datos
php artisan migrate --seed
```

URLs de utilidad:

| Recurso | URL | Credencial contenedor |
|---|---|---|
| App | `http://127.0.0.1:8000` | |
| Admin inicial | login `admin@acb.local` / `admin123` | creado por `db:seed` |
| phpMyAdmin | `http://127.0.0.1:8080` | user `root` / `root_acb_2026` o `acb_app` / `acb_local_2026` |
| MySQL (host) | `127.0.0.1:3307` | `acb_app` / `acb_local_2026`, BD `festival_acb` |

---

## 10. Comandos útiles de mantenimiento

```bash
# Estado de contenedores
podman ps

# Reiniciar base limpia
php artisan migrate:fresh --seed

# Tests
php artisan test

# Regenerar assets
npm run build
```

---

## 11. Reglas de negocio implementadas (base)

- Los usuarios autenticados solo acceden a sus paneles según `rol`.
- Un jurado autenticado no puede acceder a `/admin/*` (403 verificado).
- El registro público crea usuarios con rol `JURADO` (los admins se crean vía seeder/admin).
- Los canales de tiempo real restringen escritura/lectura según rol (pero `ranking` es público).
- El backend es la fuente de verdad; los clientes solo representan estado (base para Fases 3-6).

---

## 12. Pruebas

Se mantienen los tests de Breeze adaptados al modelo `Usuario`. Resultado:

```
Tests: 25 passed (61 assertions)
```

Los tests relevantes de negocio (`CalcularNotaServiceTest`, `RankingServiceTest`,
`JuradoSecretoServiceTest`, `GestionarCronometroServiceTest`) se crearán en sus fases
correspondientes (4, 5, 6 y 3).

---

## 13. Decisiones técnicas y desviaciones (resumen)

| Ítem | AGENTS.md | Implementación | Motivo |
|---|---|---|---|
| Contenedores | Docker Compose | Podman compatible (mismo `docker-compose.yml`) | Docker no disponible; podman rootless presente |
| DB de datos | MySQL | MySQL 8.0 en contenedor, host port 3307 | MariaDB host bloqueado por auth root con sudo |
| Campo password | `contrasenia` | `password` | Compatibilidad con autenticación Laravel/Breeze |
| Modelo | `Usuario` ubicado en `App/Models` (implícito) | `App\Modules\Usuarios\Models\Usuario` | Arquitectura modular exigida |
| Frontend | Tailwind (aprobado) | Tailwind v4 + Vite (Breeze blade) | Aprobado por el usuario |

---

## 14. Cómo extender esta fase

- **Nuevo módulo:** copiar la estructura de `app/Modules/Usuarios/` (Models en el módulo,
  factory + `newFactory()` si usa factory, controlador bajo `Controllers/`).
- **Nueva ruta por rol:** añadir dentro del `Route::prefix('admin')` o `('jurado')` del
  `routes/web.php`.
- **Nuevo canvas de tiempo real:** definir el canal en `routes/channels.php` y el evento en
  `app/Modules/<Módulo>/Events/` que implemente `ShouldBroadcast`.
- **Nuevo rol:** ampliar `RolEnum`, migrar el default y añadir rutas.
- **A subir contenedores a Docker real:** `docker compose up -d` (mismo archivo); el
  volumen `./docker/mysql/data` conserva los datos.

---

## 15. Próxima fase

La FASE 1 complementa la configuración del festival: usaremos la migración `users` ya
existente, y crearemos el resto del esquema (categorías, parámetros, departamentos, fases,
inscripciones, presentaciones, jurados, calificaciones e historial) con sus models,
relaciones y seeders oficiales de la convocatoria.