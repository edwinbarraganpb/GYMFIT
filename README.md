# GYMFIT — Plataforma completa (HTML + Bootstrap 5 + JS + PHP + PostgreSQL)

Sitio web público + plataforma privada con dos roles (Entrenador / Cliente),
inspirado en las maquetas de referencia.

## 📁 Estructura

```
gymfit/
├── index.html               # Landing pública (Hero, Servicios, Planes, Galería, Contacto)
├── login.html               # Inicio de sesión
├── registro.html            # Registro
├── seleccionar-rol.html     # Selector visual Entrenador / Cliente
├── panel-entrenador.html    # Lista de clientes + agregar
├── asignar-rutina.html      # Editor de rutina por cliente
├── panel-cliente.html       # Vista del cliente: rutina + observaciones
├── css/styles.css
├── js/app.js
├── php/
│   ├── config.php           # Conexión PDO a PostgreSQL + helpers
│   ├── login.php
│   ├── registro.php
│   ├── logout.php
│   ├── me.php
│   ├── clientes.php
│   ├── rutinas.php
│   └── contacto.php
└── sql/schema.sql           # Esquema + datos demo
```

## ⚙️ Instalación

### 1. Requisitos
- PHP 8.0+ con extensión `pdo_pgsql` habilitada
- PostgreSQL 13+ (administrado con **pgAdmin**)
- Un servidor web (XAMPP, Laragon, WAMP, MAMP o `php -S`)

### 2. Crear la base de datos
1. Abre **pgAdmin** y crea una base de datos llamada `gymfit`.
2. Abre el **Query Tool** sobre esa base de datos.
3. Carga y ejecuta el archivo `sql/schema.sql` (o copia/pega su contenido).

Esto crea las tablas y un usuario de prueba:

| Rol         | Email                   | Contraseña |
|-------------|-------------------------|------------|
| Entrenador  | entrenador@gymfit.com   | 123456     |
| Cliente     | juanperez@gmail.com     | 123456     |
| Cliente     | anagomez@gmail.com      | 123456     |

### 3. Configurar conexión
Edita `php/config.php` y ajusta:

```php
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '5432');
define('DB_NAME', 'gymfit');
define('DB_USER', 'postgres');
define('DB_PASS', 'tu_password');
```

### 4. Habilitar `pdo_pgsql`
Edita `php.ini` y descomenta:
```
extension=pdo_pgsql
```
Reinicia Apache / el servidor PHP.

### 5. Ejecutar
**Opción rápida (servidor embebido de PHP):**
```bash
cd gymfit
php -S localhost:8000
```
Abre [http://localhost:8000/index.html](http://localhost:8000/index.html)

**Con XAMPP:** copia la carpeta `gymfit` dentro de `htdocs/` y abre
[http://localhost/gymfit/](http://localhost/gymfit/)

## 🔐 Seguridad
- Contraseñas hasheadas con **bcrypt** vía `crypt()` + `gen_salt('bf')` de PostgreSQL.
- Sesiones PHP (`$_SESSION`) para mantener al usuario.
- Consultas con **PDO + parámetros preparados** (sin SQL injection).
- Verificación de rol en cada endpoint privado.

## 🧩 Endpoints PHP (JSON)

| Endpoint                  | Método | Descripción                          |
|---------------------------|--------|--------------------------------------|
| `php/login.php`           | POST   | `{email, password, rol?}`            |
| `php/registro.php`        | POST   | `{nombre, email, password, rol}`     |
| `php/logout.php`          | POST   | Cierra sesión                        |
| `php/me.php`              | GET    | Usuario actual                       |
| `php/clientes.php`        | GET    | Lista de clientes del entrenador     |
| `php/clientes.php`        | POST   | Asignar cliente por email            |
| `php/rutinas.php`         | GET    | `?cliente_id=N` — última rutina      |
| `php/rutinas.php`         | POST   | `{cliente_id, contenido, observaciones}` |
| `php/contacto.php`        | POST   | Mensaje del formulario público       |

## 🎨 Personalización
Toda la paleta vive en `css/styles.css` bajo `:root` (rojo `#e63946`, fondo `#0e0e0e`).
