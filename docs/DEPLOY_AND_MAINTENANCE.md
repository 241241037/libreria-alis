# Librería ALIS — Guía Completa: Despliegue, QA y Mantenimiento

---

## FASE 5 — Despliegue (Deployment)

### 5.1 Entorno objetivo
El sistema está diseñado para correr en cualquier hosting compartido con:
- PHP ≥ 8.0
- MySQL / MariaDB
- Apache con mod_rewrite

Para desarrollo y presentación: **XAMPP** en Windows/macOS/Linux.

---

### 5.2 Pasos de despliegue en XAMPP (local)

```bash
# 1. Copia la carpeta al servidor local
cp -r libreria_alis/ C:/xampp/htdocs/   # Windows
cp -r libreria_alis/ /Applications/XAMPP/htdocs/  # macOS

# 2. Inicia XAMPP (Apache + MySQL)

# 3. Importa el schema
mysql -u root libreria_alis < sql/schema.sql

# 4. Crea el administrador (una sola vez)
# Abre en el navegador: http://localhost/libreria_alis/setup_admin.php

# 5. Elimina setup_admin.php por seguridad
rm C:/xampp/htdocs/libreria_alis/setup_admin.php
```

---

### 5.3 Despliegue en hosting compartido gratuito (InfinityFree / 000webhost)

| Paso | Acción |
|------|--------|
| 1 | Crea cuenta en https://infinityfree.net (hosting gratuito con PHP + MySQL) |
| 2 | Crea una base de datos desde el panel de control (cPanel) |
| 3 | Anota: host, usuario, contraseña y nombre de la BD |
| 4 | Edita `config/database.php` con esas credenciales |
| 5 | Sube todos los archivos vía FTP (FileZilla) a la carpeta `htdocs/` |
| 6 | Importa `sql/schema.sql` desde phpMyAdmin del hosting |
| 7 | Accede a `tudominio.infinityfreeapp.com/setup_admin.php` y crea el admin |
| 8 | Elimina `setup_admin.php` del servidor |

---

### 5.4 CI/CD con GitHub Actions

El archivo `.github/workflows/ci.yml` define un pipeline automático que se activa en cada `push` a `main` o `dev`:

```
push → Checkout → PHP Lint → Import DB → PHPUnit → Security Check
```

**Pasos para activarlo:**
1. Sube el proyecto a GitHub (ver Sección Git más abajo)
2. Ve a la pestaña **Actions** del repositorio
3. El workflow se ejecuta automáticamente en cada commit

---

### 5.5 Configuración de Apache (.htaccess)

```apache
# libreria_alis/.htaccess
Options -Indexes
ServerSignature Off

# Redirigir todo al index si la ruta no existe
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [L]

# Headers de seguridad básicos
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
</IfModule>
```

---

### 5.6 SSL / HTTPS

- **Local (XAMPP):** XAMPP incluye un certificado autofirmado. Actívalo en `httpd-ssl.conf` y accede por `https://localhost`.
- **Hosting gratuito:** InfinityFree incluye certificado Let's Encrypt gratuito — actívalo desde el panel de control.
- **Producción real:** usa Certbot: `sudo certbot --apache -d tudominio.com`

---

### 5.7 Backup de la base de datos

```bash
# Crear backup
mysqldump -u root libreria_alis > backups/libreria_alis_$(date +%Y%m%d).sql

# Restaurar desde backup
mysql -u root libreria_alis < backups/libreria_alis_20260702.sql
```

**Recomendación:** programar backup semanal con tarea cron en hosting:
```
0 2 * * 0  mysqldump -u usuario -ppassword libreria_alis > /backups/alis_$(date +\%Y\%m\%d).sql
```

---

### 5.8 Rollback

Si una actualización causa problemas:
1. Restaura el backup de la BD con el script de la sección 5.7
2. En Git: `git revert HEAD` o `git checkout main~1 -- .` para volver a la versión anterior
3. Sube los archivos anteriores vía FTP al hosting

---

## FASE 6 — Mantenimiento y Evolución

### 6.1 Registro de Incidencias y Solicitudes de Cambio

| ID | Fecha | Tipo | Descripción | Estado | Versión |
|----|-------|------|-------------|--------|---------|
| INC-001 | 2026-07-01 | Bug | api/search.php devuelve HTTP 500 al buscar | Cerrado | v1.0.1 |
| INC-002 | 2026-07-01 | Bug | Warnings PHP contaminaban respuestas JSON | Cerrado | v1.0.1 |
| INC-003 | 2026-07-01 | Bug | Ghost-text del buscador saltaba al escribir espacio | Cerrado | v1.0.2 |
| INC-004 | 2026-07-01 | Bug | Carrito no visible para usuarios cliente | Cerrado | v1.0.2 |
| SOL-001 | 2026-07-02 | Mejora | Navegación por teclado en dropdown del buscador | Implementado | v1.1.0 |
| SOL-002 | 2026-07-02 | Mejora | Filtro por género en catálogo | Implementado | v1.1.0 |
| SOL-003 | 2026-07-02 | Mejora | Integración con Google Books API para alta de libros | Implementado | v1.1.0 |

---

### 6.2 Versiones del Proyecto

| Versión | Fecha | Cambios |
|---------|-------|---------|
| v1.0.0 | 2026-06-29 | Primera versión funcional: login, catálogo, CRUD admin, carrito, reservas |
| v1.0.1 | 2026-07-01 | Corrección bugs BUG-01 y BUG-02 (buscador + JSON limpio) |
| v1.0.2 | 2026-07-01 | Corrección bugs BUG-03 y BUG-04 (ghost-text + carrito) |
| v1.1.0 | 2026-07-02 | Navegación teclado, filtro género, Google Books API, registro cliente traducido |

---

### 6.3 Monitoreo de Rendimiento

Para un proyecto académico se puede usar la herramienta Apache Benchmark incluida en XAMPP:

```bash
# Prueba de carga: 500 peticiones con 10 usuarios simultáneos
ab -n 500 -c 10 http://localhost/libreria_alis/api/libros.php

# Prueba del buscador
ab -n 500 -c 10 "http://localhost/libreria_alis/api/search.php?q=El"
```

Para producción real se recomendaría **New Relic Free Tier** o **UptimeRobot** (monitoreo de disponibilidad gratuito).

---

### 6.4 Métricas de Uso

Opción gratuita para un proyecto académico: **Google Analytics 4**

```html
<!-- Agregar en el <head> de dashboard.php, login.php y register.php -->
<!-- Reemplaza G-XXXXXXXXXX con tu ID de medición de Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-XXXXXXXXXX"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'G-XXXXXXXXXX');
</script>
```

Métricas que se pueden rastrear:
- Usuarios activos por día/semana
- Páginas más visitadas (catálogo vs dashboard admin)
- Eventos personalizados: búsquedas, reservas confirmadas, cambios de idioma

---

### 6.5 Mejoras Incrementales Planificadas

| Prioridad | Funcionalidad | Descripción |
|-----------|--------------|-------------|
| Alta | Historial de reservas | Vista para que el cliente vea sus apartados activos y su estado |
| Alta | Expiración automática | Cron job que cambia a EXPIRADA las reservas pasadas las 48h |
| Media | Notificación por correo | Enviar confirmación de reserva al email del cliente con PHPMailer |
| Media | Panel de estadísticas admin | Gráfica de libros más reservados, stock bajo, usuarios activos |
| Baja | PWA (Progressive Web App) | Agregar manifest.json y service worker para acceso offline |
| Baja | Modo favoritos | Permitir al cliente guardar libros en lista de deseos |

---

### 6.6 Documentación Técnica Actualizada

La documentación técnica completa del proyecto se mantiene en dos lugares:
- **README.md** — instrucciones de instalación y estructura del proyecto (en el repositorio Git)
- **Este documento Word** — reporte técnico académico de todas las fases del ciclo de vida

