# Librería ALIS — Sistema de Gestión

Aplicación PHP + MySQL (pensada para correr en **XAMPP**) con login, catálogo,
panel de administración (CRUD de libros), modo claro/oscuro, 5 colores de
acento, selector de 5 idiomas y autocompletado de búsqueda contra la base de
datos real.

## 1. Requisitos
- XAMPP (Apache + MySQL + PHP 8+) — https://www.apachefriends.org

## 2. Instalación

1. Copia la carpeta `libreria_alis` completa dentro de `htdocs` de tu XAMPP
   (por ejemplo `C:\xampp\htdocs\libreria_alis` o `/Applications/XAMPP/htdocs/libreria_alis`).
2. Abre el **Panel de control de XAMPP** y enciende **Apache** y **MySQL**.
3. Entra a `http://localhost/phpmyadmin`, pestaña **Importar**, y sube el
   archivo `sql/schema.sql`. Esto crea la base `libreria_alis` y sus tablas
   (`usuario`, `libro`, `reserva`, `detalle_reserva`) más 4 libros de ejemplo.
4. Si tu MySQL tiene usuario/contraseña distintos a `root` / (vacío), edítalos
   en `config/database.php`.
5. Abre `http://localhost/libreria_alis/setup_admin.php` en el navegador y
   crea tu cuenta de administrador (nombre, correo, contraseña). Este paso usa
   `password_hash()` de PHP para guardar la contraseña cifrada con Bcrypt,
   tal como pide el requisito RNF-01 del documento de visión.
6. Por seguridad, **borra o renombra `setup_admin.php`** una vez creado el admin.
7. Entra a `http://localhost/libreria_alis/login.php` con esa cuenta.

## 3. Estructura del proyecto

```
libreria_alis/
├── api/
│   ├── libros.php      → CRUD de libros (GET/POST/PUT/DELETE)
│   └── search.php      → autocompletado de búsqueda
├── assets/
│   ├── css/style.css   → temas claro/oscuro + 5 acentos
│   └── js/app.js       → tema, toasts, debounce
├── config/database.php → conexión PDO a MySQL
├── includes/
│   ├── auth.php        → sesión y control de roles
│   └── lang.php        → motor de idiomas
├── lang/                → es.php, en.php, fr.php, pt.php, de.php
├── sql/schema.sql       → script de base de datos
├── login.php
├── logout.php
├── dashboard.php        → catálogo + panel admin
├── setup_admin.php      → crea el primer administrador (borrar después)
└── index.php
```

## 4. Cómo funciona cada requerimiento que pediste

- **Login**: `login.php`, valida contra la tabla `usuario` con
  `password_verify()`. Las contraseñas se guardan con Bcrypt (`password_hash`).
- **Dashboard / catálogo**: `dashboard.php` muestra los libros en tarjetas con
  imagen, precio y existencias.
- **Administrador (agregar/editar/borrar)**: si el usuario logueado tiene
  `rol = 'admin'`, ve botones "Agregar libro", "Editar" y "Eliminar" que llaman
  a `api/libros.php` (POST / PUT / DELETE). Los clientes solo ven "Reservar".
- **Modo claro/oscuro**: botón de sol/luna en el header; se guarda en
  `localStorage` y se aplica con el atributo `data-theme` en `<body>`.
- **Varios colores**: selector de 5 acentos (índigo, esmeralda, ámbar, rosa,
  pizarra) junto al botón de tema, también persistido en `localStorage`.
- **5 idiomas**: selector en el header (Español, English, Français, Português,
  Deutsch). El idioma elegido se guarda en la sesión PHP (`$_SESSION['lang']`).
- **Autocompletado**: al escribir en la barra de búsqueda, `app.js` consulta
  `api/search.php?q=...` con `fetch` (con debounce) y muestra sugerencias
  reales tomadas de la tabla `libro` (título, autor, ISBN).
- **Imagen del libro**: la tabla `libro` tiene la columna `imagen_url`; en el
  formulario de alta/edición hay un campo para pegar la URL de la portada, y
  la tarjeta del catálogo la muestra (con un ícono de respaldo si la URL falla).
- **API externa real (Google Books)**: dentro del modal de "Agregar/Editar
  libro" hay un buscador que consulta la API pública de Google Books
  (`api/google_books.php` actúa como proxy del servidor hacia
  `googleapis.com`, así evitamos problemas de CORS y mantenemos la llamada
  del lado del backend). Al elegir un resultado, se autocompletan título,
  autor, género, ISBN y portada — tú solo capturas precio y existencias.
  No requiere API key para uso básico (tiene cuota limitada por IP; si
  algún día se queda corta, se puede agregar una key gratuita de Google
  Cloud en la URL de `api/google_books.php`).

## 6. Sobre la API de Google Books
- Requiere que tu servidor (el de XAMPP) tenga salida a internet, ya que la
  petición la hace PHP del lado del servidor, no el navegador.
- Si tu `php.ini` tiene deshabilitado tanto `curl` como `allow_url_fopen`,
  esta función no podrá conectarse; el endpoint te devuelve un mensaje claro
  en ese caso en vez de fallar en silencio.
- Es opcional: si Google Books no responde, igual puedes llenar el
  formulario de alta de libro a mano como antes.
- Todas las consultas usan **PDO con parámetros preparados** (sin concatenar
  SQL), para evitar inyección SQL.
- Las contraseñas nunca se guardan en texto plano.
- Las rutas de escritura de `api/libros.php` (crear/editar/borrar) verifican
  `rol = 'admin'` en el servidor, no solo en el frontend.
