# GYMFIT — Plataforma MVC con PHP + SQLite + Chart.js

Sitio web público + plataforma privada con dos roles (Entrenador / Cliente),
arquitectura MVC, principios SOLID, seguridad OWASP, reportes y tests.

## 📁 Estructura

```
gymfit/
├── index.html               # Landing pública
├── login.html               # Inicio de sesión
├── registro.html            # Registro
├── seleccionar-rol.html     # Selector visual Entrenador / Cliente
├── panel-entrenador.html    # Lista de clientes + agregar
├── asignar-rutina.html      # Editor de rutina por cliente
├── panel-cliente.html       # Vista del cliente: rutina + observaciones
├── css/styles.css
├── js/
│   ├── app.js               # JS común: fetch, auth, toast
│   └── charts.js            # (reservado para reportes)
├── app/                     # Código MVC
│   ├── bootstrap.php        # Autoloader PSR-4
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   ├── ClientController.php
│   │   ├── RoutineController.php
│   │   ├── ContactController.php
│   │   └── ReportController.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── TrainerClient.php
│   │   ├── Routine.php
│   │   ├── Contact.php
│   │   └── Message.php
│   ├── Helpers/
│   │   ├── Database.php     # PDO singleton + auto-init
│   │   ├── Auth.php         # Sesión, bcrypt, CSRF
│   │   ├── Response.php     # JSON responses
│   │   └── Validator.php    # Validación de inputs
│   ├── Middleware/
│   │   └── SecurityHeaders.php  # CSP, X-Frame-Options, HSTS
│   └── Security/
│       └── Audit.php        # Log de auditoría + XSS escape
├── php/                     # API endpoints (delegan a Controllers)
│   ├── config.php           # Bootstrap + helpers legacy
│   ├── login.php
│   ├── registro.php
│   ├── logout.php
│   ├── me.php
│   ├── clientes.php
│   ├── rutinas.php
│   ├── contacto.php
│   └── reportes.php
├── reports/                 # Reportes con Chart.js
│   ├── clientes.html        # Progreso de clientes (barras + dona)
│   └── gym.html             # Estadísticas del gimnasio
├── tests/
│   └── TestRunner.php       # Test Agent (unitarias + regresión)
├── sql/schema.sql           # Esquema SQLite de referencia
├── database/
│   └── gymfit.db            # SQLite BD (auto-creada)
├── .htaccess
└── README.md
```

## ⚙️ Instalación

### Requisitos
- PHP 8.0+ con `pdo_sqlite`
- Servidor web o `php -S`

### Ejecutar
```bash
cd gymfit
php -S localhost:8000
```
Abrir http://localhost:8000/index.html

La BD se crea automáticamente al primer request.

### Usuarios de prueba
| Rol         | Email                   | Contraseña |
|-------------|-------------------------|------------|
| Entrenador  | entrenador@gymfit.com   | 123456     |
| Cliente     | juanperez@gmail.com     | 123456     |

## 🧪 Test Agent (Pruebas Unitarias y Regresión)
```bash
php tests/TestRunner.php        # Todos los tests
php tests/TestRunner.php Auth   # Solo AuthTest
```
Incluye tests de: autenticación, modelos, clientes, rutinas, contactos.

## 📊 Reportes
- **Progreso de Clientes**: `reports/clientes.html` (barras + dona)
- **Estadísticas del Gimnasio**: `reports/gym.html` (líneas, barras, tortas)

## 🔐 Seguridad (OWASP)
- Content-Security-Policy (CSP) contra XSS
- X-Frame-Options: DENY (clickjacking)
- X-Content-Type-Options: nosniff
- Referrer-Policy estricta
- Permissions-Policy restringida
- CSRF token via `Auth::csrfToken()` / `Auth::validateCsrf()`
- Sanitización de inputs con `Audit::sanitize()` + `Validator`
- Escape de outputs con `Audit::escape()` (XSS)
- SQL injection prevenido vía PDO prepared statements
- Log de auditoría para eventos críticos (login, registro, etc.)

## 🧩 Endpoints PHP (JSON)
| Endpoint                | Método | Descripción                          |
|-------------------------|--------|--------------------------------------|
| `php/login.php`         | POST   | `{email, password, rol?}`            |
| `php/registro.php`      | POST   | `{nombre, email, password, rol}`     |
| `php/logout.php`        | POST   | Cierra sesión                        |
| `php/me.php`            | GET    | Usuario actual                       |
| `php/clientes.php`      | GET    | Lista de clientes del entrenador     |
| `php/clientes.php`      | POST   | Asignar cliente por email            |
| `php/rutinas.php`       | GET    | `?cliente_id=N` — última rutina      |
| `php/rutinas.php`       | POST   | `{cliente_id, contenido, observaciones}` |
| `php/contacto.php`      | POST   | Mensaje del formulario público       |
| `php/reportes.php`      | GET    | `?tipo=clientes` o `?tipo=gimnasio`  |

## 🏗️ Principios SOLID
- **S**: Cada clase tiene una responsabilidad única (Models = datos, Controllers = lógica, Helpers = utilidades)
- **O**: Abierto a extensión (nuevos models/controllers sin modificar existentes)
- **L**: Modelos y controladores respetan sus contratos
- **I**: Interfaces pequeñas y específicas a través de métodos estáticos
- **D**: Helpers y Models dependen de abstracciones (PDO)

## 🎨 Personalización
Paleta en `css/styles.css` bajo `:root` (rojo `#e63946`, fondo `#0e0e0e`).
