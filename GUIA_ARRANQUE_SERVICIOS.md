# Guía de Arranque y Despliegue de Servicios
## Sistema de Calificaciones Digitales — XXXIII Festival Nacional “Aquí… Canta Bolivia”

Esta guía describe paso a paso cómo poner en funcionamiento el sistema tanto en **red local (LAN/Wi-Fi)** para acceso de múltiples dispositivos (jurados, mesa técnica, ranking en pantallas gigantes) como en **producción con IP Pública / Internet** (con y sin dominio/SSL).

---

## Tabla de Contenidos

1. [Arquitectura de Servicios](#1-arquitectura-de-servicios)
2. [Despliegue en Red Local (Multi-dispositivo)](#2-despliegue-en-red-local-multi-dispositivo)
   - [Paso 2.1: Conocer la IP Local del Servidor](#paso-21-conocer-la-ip-local-del-servidor)
   - [Paso 2.2: Configurar el archivo .env](#paso-22-configurar-el-archivo-env)
   - [Paso 2.3: Compilar Assets del Frontend](#paso-23-compilar-assets-del-frontend)
   - [Paso 2.4: Habilitar Reglas en el Firewall](#paso-24-habilitar-reglas-en-el-firewall)
   - [Paso 2.5: Iniciar los Servicios](#paso-25-iniciar-los-servicios)
   - [Paso 2.6: Acceso desde Tablets y Laptops](#paso-26-acceso-desde-tablets-y-laptops)
3. [Despliegue con IP Pública / Internet](#3-despliegue-con-ip-pública--internet)
   - [Opción A: Servidor Local expuesto vía Router (Port Forwarding)](#opción-a-servidor-local-expuesto-vía-router-port-forwarding)
   - [Opción B: Despliegue en Servidor VPS Linux (Recomendado para Producción)](#opción-b-despliegue-en-servidor-vps-linux-recomendado-para-producción)
   - [Opción C: Exposición Inmediata vía Túnel Seguro (Cloudflare Tunnel o Ngrok)](#opción-c-exposición-inmediata-vía-túnel-seguro-cloudflare-tunnel-o-ngrok)
4. [Buenas Prácticas para el Evento en Vivo](#4-buenas-prácticas-para-el-evento-en-vivo)
5. [Resolución de Problemas Comunes (Troubleshooting)](#5-resolución-de-problemas-comunes-troubleshooting)

---

## 1. Arquitectura de Servicios

El sistema se compone de 3 servicios indispensables que deben ejecutarse simultáneamente:

```text
┌─────────────────────────────────────────────────────────────────┐
│                          DISPOSITIVOS                           │
│   Tablets Jurados  │  Mesa Técnica Admin  │  Pantalla Ranking   │
└────────┬──────────────────────┬──────────────────────┬──────────┘
         │                      │                      │
         │ HTTP (:8000)         │ WebSocket (:6080)    │
         ▼                      ▼                      │
┌──────────────────┐   ┌──────────────────┐            │
│  Servidor Web    │   │ Laravel Reverb   │◄───────────┘
│  Laravel HTTP    │   │ WebSockets Real- │ (Sincronización en vivo
│  (Puerto 8000)   │   │ time (Pto 6080)  │  de notas y cronómetro)
└────────┬─────────┘   └────────┬─────────┘
         │                      │
         ▼                      ▼
┌─────────────────────────────────────────┐
│        Base de Datos MySQL Local        │
│          (Puerto 3306 o 3307)           │
└─────────────────────────────────────────┘
```

1. **Base de Datos MySQL**: Guarda participantes, categorías, jurados, calificaciones y auditoría.
2. **Servidor HTTP Laravel**: Procesa las solicitudes web, autenticación de usuarios y reportes.
3. **Servidor WebSocket Reverb**: Sincroniza en milisegundos las calificaciones, el cronómetro de escenario, el jurado secreto y el podio de gala.

---

## 2. Despliegue en Red Local (Multi-dispositivo)

Útil para operar dentro del teatro o sala de evaluación conectando las tablets de los 5 jurados y las laptops de control a un mismo router o switch Wi-Fi/Ethernet.

### Paso 2.1: Conocer la IP Local del Servidor

Abre PowerShell en la computadora servidora y ejecuta:

```powershell
Get-NetIPAddress -AddressFamily IPv4 | Where-Object { $_.InterfaceAlias -notlike "*Loopback*" -and $_.IPAddress -notlike "169.254*" } | Select-Object IPAddress, InterfaceAlias
```

*(O simplemente ejecuta `ipconfig` y busca la **Dirección IPv4**, por ejemplo `192.168.1.150` o `192.168.200.232`).*

---

### Paso 2.2: Configurar el archivo .env

Edita tu archivo `.env` para que los clientes web sepan a qué IP conectarse para la aplicación y los WebSockets:

```ini
APP_NAME="ACB Canta Bolivia"
APP_ENV=local
APP_DEBUG=true

# Reemplaza 192.168.1.150 con la IP de tu máquina servidora:
APP_URL=http://192.168.1.150:8000

# Configuración de Base de Datos (Local)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=festival_acb
DB_USERNAME=acb_app
DB_PASSWORD=acb_local_2026

# Configuración de Laravel Reverb
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=935767
REVERB_APP_KEY=rlml9nwlxkswhus5t6ca
REVERB_APP_SECRET=xzx65mxlpwktbspb0bpp

# REVERB_HOST: IP que verán los navegadores de los jurados
REVERB_HOST="192.168.1.150"
REVERB_PORT=6080
REVERB_SCHEME=http

# REVERB_SERVER: IP interna de escucha en el servidor (0.0.0.0 = todas las tarjetas de red)
REVERB_SERVER_HOST=0.0.0.0
REVERB_SERVER_PORT=6080

# Variables para Vite en el cliente
VITE_APP_NAME="${APP_NAME}"
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

> **Nota importante:** En este proyecto, `resources/js/bootstrap.js` incluye detección dinámica del host (`window.location.hostname`). Esto significa que si el jurado ingresa desde `http://192.168.1.150:8000`, la librería intentará conectarse automáticamente a `ws://192.168.1.150:6080`.

---

### Paso 2.3: Compilar Assets del Frontend

Cada vez que cambies la configuración de producción o IP, genera la versión optimizada de JavaScript y CSS:

```powershell
npm run build
```

---

### Paso 2.4: Habilitar Reglas en el Firewall de Windows

Para que las tablets y otras laptops puedan entrar a los puertos `8000` y `6080`, debes abrir el firewall de Windows. Abre **PowerShell como Administrador** y ejecuta:

```powershell
# Permitir puerto 8000 (Laravel Web)
New-NetFirewallRule -DisplayName "Festival ACB - Web HTTP (8000)" -Direction Inbound -LocalPort 8000 -Protocol TCP -Action Allow

# Permitir puerto 6080 (Reverb WebSockets)
New-NetFirewallRule -DisplayName "Festival ACB - WebSockets Reverb (6080)" -Direction Inbound -LocalPort 6080 -Protocol TCP -Action Allow
```

---

### Paso 2.5: Iniciar los Servicios

Debes abrir dos terminales independientes (o pestañas de terminal):

#### Terminal 1 — Servidor Web Laravel:
```powershell
php artisan serve --host=0.0.0.0 --port=8000
```
*(El parámetro `--host=0.0.0.0` es vital: indica que escuche peticiones provenientes de cualquier tarjeta de red o dispositivo externo).*

#### Terminal 2 — Servidor WebSocket Reverb:
```powershell
php artisan reverb:start --host=0.0.0.0 --port=6080
```

---

### Paso 2.6: Acceso desde Tablets y Laptops

Asegúrate de que los dispositivos estén conectados a la **misma red Wi-Fi o switch Ethernet** del servidor:

| Dispositivo | URL de Acceso |
|---|---|
| **Pantalla / Proyector de Ranking** | `http://192.168.1.150:8000` |
| **Tablet de Jurado** | `http://192.168.1.150:8000/login` |
| **Mesa Técnica / Administrador** | `http://192.168.1.150:8000/login` |

Credenciales iniciales:
- **Usuario**: `admin` (o `admin@acb.local`)
- **Contraseña**: `admin123`

---

## 3. Despliegue con IP Pública / Internet

Si deseas que jurados remotos, público en general o supervisores accedan desde fuera de la red local mediante una IP Pública o dominio.

---

### Opción A: Servidor Local expuesto vía Router (Port Forwarding)

Apta si el servidor físico está en el teatro y el router del proveedor de internet (ISP) cuenta con IP pública.

#### 1. Obtener la IP Pública del local:
Consulta en el navegador [https://ifconfig.me](https://ifconfig.me) o por terminal:
```powershell
(Invoke-WebRequest ifconfig.me/ip -UseBasicParsing).Content.Trim()
```

#### 2. Configurar el Reenvío de Puertos (Port Forwarding) en el Router:
Ingresa al panel del router (usualmente `http://192.168.1.1` o `192.168.0.1`) en la sección **Port Forwarding / Virtual Server / NAT**:

| Servicio | Puerto Externo | IP Interna | Puerto Interno | Protocolo |
|---|---|---|---|---|
| Web HTTP | `8000` (o `80`) | `192.168.1.150` | `8000` | TCP |
| WebSockets | `6080` | `192.168.1.150` | `6080` | TCP |

#### 3. Ajustar `.env` y compilar:
```ini
APP_URL=http://<TU_IP_PUBLICA>:8000
REVERB_HOST="<TU_IP_PUBLICA>"
REVERB_PORT=6080
REVERB_SCHEME=http
```
Compilar:
```powershell
npm run build
php artisan config:clear
```

> **Atención con el NAT Loopback:** Muchos routers domésticos no permiten que una computadora dentro de la red local acceda a su propia IP pública. Para los dispositivos dentro del teatro, utiliza la IP local (`192.168.1.150`), y para los que están fuera, la IP pública.

---

### Opción B: Despliegue en Servidor VPS Linux (Recomendado para Producción)

Para una transmisión oficial o alta concurrencia de público, lo óptimo es desplegar en un VPS (Ubuntu 22.04 / 24.04 en DigitalOcean, AWS, Linode, etc.) con Nginx y certificado SSL HTTPS/WSS gratuito con Let's Encrypt.

#### 1. Requisitos del Servidor Linux:
- Nginx, PHP 8.2 o superior (`php8.2-fpm`, `php8.2-mysql`, `php8.2-gd`, `php8.2-zip`, `php8.2-mbstring`, `php8.2-curl`, `php8.2-xml`).
- MySQL o MariaDB.
- Node.js 20+ y NPM.
- Supervisor (para mantener corriendo Reverb y Queue Worker).

#### 2. Configuración de Nginx con Reverse Proxy y WebSockets:

Crea el archivo `/etc/nginx/sites-available/acb-festival.conf`:

```nginx
server {
    listen 80;
    server_name festival.tudominio.com; # O la IP pública del VPS

    root /var/www/festival_acb/public;
    index index.php index.html;

    # Logs
    access_log /var/log/nginx/acb_access.log;
    error_log /var/log/nginx/acb_error.log;

    # Servir archivos estáticos directamente
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Procesar PHP
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Proxy inverso para WebSockets Reverb en el mismo puerto (vía /app)
    location /app {
        proxy_pass http://127.0.0.1:6080;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "Upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_read_timeout 600;
        proxy_send_timeout 600;
    }

    # Denegar archivos ocultos
    location ~ /\.ht {
        deny all;
    }
}
```

Habilitar sitio:
```bash
sudo ln -s /etc/nginx/sites-available/acb-festival.conf /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

#### 3. Configuración del `.env` en el Servidor VPS:
```ini
APP_ENV=production
APP_DEBUG=false
APP_URL=https://festival.tudominio.com

BROADCAST_CONNECTION=reverb
REVERB_APP_ID=935767
REVERB_APP_KEY=rlml9nwlxkswhus5t6ca
REVERB_APP_SECRET=xzx65mxlpwktbspb0bpp

# En producción con SSL, Reverb pasa por HTTPS/WSS (puerto 443)
REVERB_HOST="festival.tudominio.com"
REVERB_PORT=443
REVERB_SCHEME=https

REVERB_SERVER_HOST=127.0.0.1
REVERB_SERVER_PORT=6080
```

#### 4. Mantener Reverb activo con Supervisor:
Crea `/etc/supervisor/conf.d/reverb.conf`:

```ini
[program:reverb]
process_name=%(program_name)s
command=php /var/www/festival_acb/artisan reverb:start --host=127.0.0.1 --port=6080
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/festival_acb/storage/logs/reverb.log
stopwaitsecs=3600
```

Actualizar supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start reverb
```

#### 5. Optimización final para Producción:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
npm run build
```

---

### Opción C: Exposición Inmediata vía Túnel Seguro (Cloudflare Tunnel o Ngrok)

Si no tienes acceso al router del teatro y necesitas conectar computadoras fuera del recinto de inmediato sin abrir puertos:

#### Con Cloudflare Tunnel (Gratuito y sin límite):
```bash
cloudflared tunnel --url http://localhost:8000
```
Cloudflare te entregará una URL HTTPS (ej. `https://festival-oruro.trycloudflare.com`) accesible desde cualquier parte del mundo con cifrado SSL automático.

---

## 4. Buenas Prácticas para el Evento en Vivo

1. **Red Dedicada para Jurados**:
   - Se recomienda tener una red Wi-Fi exclusiva (SSID oculto) únicamente para las tablets de jurados y mesa de control, separada del Wi-Fi para prensa o público.
2. **Desactivar Aislamiento de Clientes (AP Isolation)**:
   - En el router Wi-Fi del evento, desactiva la opción **"AP Isolation"** o **"Client Isolation"**; de lo contrario, las tablets no podrán comunicarse con la IP de la laptop servidora.
3. **Fijar IP Estática en el Servidor**:
   - Configura en Windows una IP fija (ej. `192.168.1.150`) para evitar que el router cambie la IP a mitad del festival si se reinicia.
4. **Comandos de Reinicio Rápido**:
   - Si requieres reiniciar las presentaciones a su estado original de prueba:
     ```powershell
     php artisan festival:reset-presentaciones
     ```
   - Si necesitas borrar la caché tras un cambio de configuración:
     ```powershell
     php artisan optimize:clear
     ```

---

## 5. Resolución de Problemas Comunes (Troubleshooting)

### A. Los jurados no pueden abrir la página web desde sus tablets
1. Verifica que la tablet esté en el mismo Wi-Fi.
2. Asegúrate de haber iniciado el servidor con `--host=0.0.0.0` (y no solo `php artisan serve`, que por defecto solo escucha en `127.0.0.1`).
3. Comprueba el Firewall de Windows (Paso 2.4) o desactiva temporalmente el perfil de red privada para descartar bloqueo.

### B. La página carga pero las notas o el cronómetro no se actualizan en vivo
1. Verifica que el proceso `php artisan reverb:start --host=0.0.0.0 --port=6080` esté corriendo sin errores.
2. Abre la consola de desarrollador en el navegador (`F12` -> pestaña *Console*): si muestra `WebSocket connection to 'ws://...' failed`, verifica que el puerto `6080` esté abierto en el Firewall de Windows.
3. Asegúrate de haber ejecutado `npm run build` después de configurar las variables `REVERB_HOST` en el `.env`.

### C. Error "Connection Refused" en MySQL
1. Verifica que el servicio de MySQL esté iniciado (en el panel de control de XAMPP el botón debe figurar en verde con puerto 3306).
2. Comprueba las credenciales en el `.env` (`DB_PORT=3306`, `DB_USERNAME=acb_app`, `DB_PASSWORD=acb_local_2026`).
