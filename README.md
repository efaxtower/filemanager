**Cierto.** Aquí está el `README.md` completo y actualizado con **todo lo de Fase 2**.

---

## `README.md`

```markdown
# 📁 FileManager

Gestor de archivos web tipo Google Drive construido en **PHP puro** (sin frameworks) con **MySQL/MariaDB**, HTML5, CSS3 y JavaScript vanilla.

---

## 🎯 Estado del proyecto

| Fase | Descripción | Estado |
|---|---|---|
| **Fase 1** | Gestión de archivos personal | ✅ Completada |
| **Fase 2** | Roles, departamentos, reportes, carpetas compartidas | ✅ Completada |
| **Fase 3** | Mensajería interna, miniaturas, notificaciones | 🚧 Pendiente |

---

## ✨ Funcionalidades

### Autenticación
- Login / logout con **bcrypt** (`password_hash`)
- Sesiones PHP con `session_start()`
- Rutas protegidas: redirige a `/login` si no hay sesión
- **Solicitudes de cuenta** con captcha propio (PHP + GD)
- **Cambio de contraseña** desde el perfil

### Gestión de archivos personal
- **Explorador tipo Google Drive** con dos vistas: cuadrícula y lista
- **Crear carpetas** con validación de nombres
- **Subir archivos** (límite 6 MB)
- **Visor integrado** para imágenes, texto plano, PDF
- **Editor de texto en línea** para archivos `.txt`, `.md`, `.json`, etc.
- **Descargar archivos**
- **Renombrar** con modal
- **Mover** archivos y carpetas entre directorios
- **Borrar** archivos y carpetas (con cascada en BD)
- **Buscador** por nombre con ruta completa
- **Breadcrumb** de navegación
- **Cuota** de almacenamiento (15 GB por usuario)

### Administración
- **Roles:** `admin` y `user`
- **Departamentos:** crear, editar, borrar (con contador de usuarios)
- **Crear usuarios** con credenciales temporales
- **Resetear contraseñas**
- **Aprobar / rechazar solicitudes** de cuenta
- **Panel de reportes** con estados y respuestas
- **Gestión de carpetas compartidas** con permisos granulares

### Carpetas compartidas
- Espacio común con jerarquía de carpetas
- **Permisos por departamento** (todos los miembros ven su carpeta)
- **Permisos individuales** (por usuario específico)
- **Carpetas públicas** (todos pueden leer)
- **Subir archivos** con permisos de escritura
- **Editor de texto** integrado
- **Mover** archivos y carpetas
- **Renombrar**, **borrar**, **descargar**

### Reportes
- Los usuarios envían **bugs, sugerencias, quejas o "otros"**
- **Prioridad** (baja / media / alta)
- **Estados:** pendiente, en revisión, resuelto, cerrado
- **Respuesta del admin** al reporte
- **Filtros** por estado y prioridad

### Interfaz
- Diseño **tech híbrido:** glassmorfismo + neumorfismo
- **Modo claro / oscuro** con persistencia en `localStorage`
- **Font Awesome 6** local (sin CDN)
- **View Transitions API** para navegación fluida (Chrome/Edge)
- **Barra de carga animada** tipo puerta de enrrollable
- **Totalmente responsive** (móvil, tablet, escritorio)
- **Sidebar unificado** según el rol del usuario

---

## 🛠️ Stack

| Capa | Tecnología |
|---|---|
| Backend | PHP 8.2 (puro, sin frameworks) |
| Base de datos | MySQL 8 / MariaDB 10.4+ |
| Servidor | Apache con `mod_rewrite` |
| Frontend | HTML5, CSS3, JavaScript vanilla |
| Iconos | Font Awesome 6 (local) |

---

## 📁 Estructura del proyecto

```
filemanager/
├── app/
│   ├── Auth/
│   │   ├── Auth.php                  # Login, registro, sesiones
│   │   └── UserRepository.php        # Consultas a tabla users
│   ├── Controllers/
│   │   ├── AdminController.php       # Admin: usuarios, deptos, solicitudes, reportes
│   │   ├── AuthController.php        # Login/logout
│   │   ├── FileController.php        # Archivos personales
│   │   ├── RegisterController.php    # Solicitudes de cuenta + captcha
│   │   ├── ReportController.php      # Reportes (usuario)
│   │   ├── SharedController.php      # Carpetas compartidas
│   │   └── UserController.php        # Perfil
│   ├── Core/
│   │   ├── AccountRequestRepository.php
│   │   ├── Captcha.php               # Captcha con GD
│   │   ├── Database.php              # Singleton PDO
│   │   ├── DepartmentRepository.php
│   │   ├── ReportRepository.php
│   │   ├── Router.php
│   │   └── SharedRepository.php
│   ├── Filesystem/
│   │   ├── NodeRepository.php
│   │   ├── PathNotFoundException.php
│   │   ├── PathResolver.php
│   │   ├── ResolvedPath.php
│   │   └── Storage.php
│   └── Views/
│       ├── partials/
│       │   ├── head.php
│       │   └── sidebar.php
│       ├── admin/
│       │   ├── departments.php
│       │   ├── report_view.php
│       │   ├── reports.php
│       │   ├── requests.php
│       │   ├── shared.php
│       │   ├── shared_view.php
│       │   └── users.php
│       ├── auth/
│       │   ├── login.php
│       │   └── register.php
│       ├── files/
│       │   ├── 404.php
│       │   ├── explorer.php
│       │   ├── search.php
│       │   └── view.php
│       ├── reports/
│       │   ├── create.php
│       │   ├── index.php
│       │   └── view.php
│       ├── shared/
│       │   ├── index.php
│       │   ├── view.php
│       │   └── view_file.php
│       └── user/
│           └── profile.php
├── config/
│   └── config.php                    # Configuración (BD, storage, app)
├── database/
│   └── schema.sql                    # Esquema completo de la BD
├── public/                           # Document root
│   ├── .htaccess                     # Redirige todo a index.php
│   ├── index.php                     # Front controller
│   └── assets/
│       ├── css/app.css
│       ├── js/app.js
│       └── fontawesome/              # Font Awesome 6 (local)
│           ├── css/
│           └── webfonts/
├── storage/                          # Archivos físicos (fuera de public)
│   ├── users/                        # Archivos personales por usuario
│   │   └── {user_id}/
│   └── shared/                       # Archivos compartidos
│       └── {folder_id}/
├── scripts/                          # Scripts de testing manual
├── .gitignore
├── .htaccess                         # Redirige todo a public/
├── LICENSE
└── README.md
```

---

## 🚀 Instalación

### Requisitos

- **PHP 8.0+** con extensión **GD** habilitada
- **MySQL 5.7+ / MariaDB 10.4+**
- **Apache** con `mod_rewrite` habilitado
- `AllowOverride All` en la configuración de Apache

### Pasos

**1. Clonar el proyecto**

```bash
git clone https://github.com/efaxtower/filemanager.git
cd filemanager
```

**2. Crear la base de datos**

Importa `database/schema.sql` desde phpMyAdmin o desde terminal:

```bash
mysql -u root -p < database/schema.sql
```

**3. Configurar credenciales**

Edita `config/config.php`:

```php
'db' => [
    'host'    => 'localhost',
    'name'    => 'filemanager',
    'user'    => 'root',
    'pass'    => '',
    'charset' => 'utf8mb4',
],
```

**4. Ajustar `$basePath`**

En `public/index.php`:

```php
$basePath = '/filemanager';  // Si está en htdocs/filemanager/
$basePath = '';              // Si está en la raíz del dominio
```

**5. Configurar los `.htaccess`**

Verifica que existen:

- `.htaccess` en la raíz (redirige a `public/`)
- `public/.htaccess` (redirige a `index.php`)

**6. Permisos de escritura (Linux)**

```bash
chmod -R 775 storage/
chown -R www-data:www-data storage/
```

**7. Listo**

Abre el navegador en:

```
http://localhost/filemanager/
```

---

## 🔒 Seguridad

Decisiones de seguridad implementadas:

- **Prepared statements** en el 100% de las consultas SQL
- **Bcrypt** para contraseñas (nunca en texto plano)
- **Path traversal** rechazado en `PathResolver::normalize()` (`..`, `.`, `\0`)
- **Doble verificación** de rutas físicas (`PathResolver` + `Storage`)
- **Aislamiento por usuario:** cada consulta filtra por `user_id`
- **`htmlspecialchars()`** en todas las salidas HTML (previene XSS)
- **Validación con regex** en nombres de usuario y archivos
- **`storage/` fuera de `public/`**: el navegador nunca accede directamente
- **Captcha propio** para solicitudes de cuenta
- **Permisos granulares** en carpetas compartidas (read/write/delete)
- **Verificación de ciclos** al mover carpetas compartidas

---

## 🏗️ Arquitectura

### Flujo de una petición

```
Usuario → URL /filemanager/files/Documentos
    ↓
.htaccess (raíz) → redirige a public/
    ↓
.htaccess (public) → redirige a index.php
    ↓
index.php (front controller) → limpia la URI
    ↓
Router → decide qué controlador ejecutar
    ↓
FileController::index() → verifica sesión, resuelve la ruta
    ↓
PathResolver → traduce ruta lógica a física (valida contra BD)
    ↓
NodeRepository → consulta a MySQL
    ↓
Vista (explorer.php) → renderiza HTML
```

### Rutas virtuales

El sistema **no expone rutas físicas**. El usuario ve:

```
/documentos/nota.txt
```

Y el sistema traduce internamente a:

```
storage/users/3/documentos/nota.txt
```

**Ventajas:**
- El usuario nunca toca el filesystem
- Se puede cambiar la estructura física sin romper la lógica
- Cada usuario está aislado en su propio directorio

### Árbol de nodos

Los archivos y carpetas se representan como **nodos** en la tabla `nodes`:

- `parent_id NULL` → raíz del usuario
- `parent_id = X` → hijo del nodo X
- `type` → `file` o `folder`
- Cascada en BD: borrar un nodo borra sus hijos

---

## 🗄️ Esquema de base de datos

| Tabla | Descripción |
|---|---|
| `users` | Usuarios con rol, departamento y cuota |
| `departments` | Departamentos de la empresa |
| `nodes` | Árbol de archivos personales |
| `account_requests` | Solicitudes de creación de cuenta |
| `reports` | Reportes enviados por usuarios |
| `shared_folders` | Carpetas compartidas (jerárquicas) |
| `shared_permissions` | Permisos individuales sobre carpetas |
| `shared_files` | Archivos dentro de carpetas compartidas |

---

## 🧪 Testing

Scripts de prueba manual (en `scripts/`):

```bash
php scripts/test_db.php          # Probar conexión PDO
php scripts/test_normalize.php   # Probar normalización de rutas
php scripts/test_resolve.php     # Probar resolución de rutas
php scripts/test_repository.php  # Probar CRUD de nodos
php scripts/test_storage.php     # Probar operaciones en disco
php scripts/test_auth.php        # Probar login y registro
```

**Nota:** los scripts de test no están diseñados para producción. Son para verificar que cada capa funciona.

---

## 🎨 Diseño

Paleta de colores:

- **Verde manzana:** `#7ed957` (primario)
- **Azul metalizado:** `#3b82c4` (acento)
- **Modo oscuro:** `#07090d` (fondo)

Elementos visuales:

- **Hexágonos** como patrón decorativo
- **Glassmorfismo** (blur + transparencia) en sidebar y topbar
- **Neumorfismo** (sombras internas + externas) en tarjetas
- **Gradientes metálicos** en botones y elementos destacados
- **Animaciones:** fade, float, shimmer, scale
- **View Transitions API** para navegación fluida

---

## 📋 Roadmap

### ✅ Fase 1 (completada)

- Autenticación (login, registro, sesiones)
- Explorador de archivos con dos vistas
- CRUD completo (crear, subir, leer, renombrar, borrar, mover)
- Visor de archivos (imagen, texto, PDF)
- Editor de texto en línea
- Buscador
- Modo claro / oscuro

### ✅ Fase 2 (completada)

- Roles (admin / usuario)
- Departamentos (CRUD)
- Solicitudes de cuenta con captcha
- Panel de administración completo
- Perfil de usuario (cambio de contraseña)
- Carpetas compartidas con permisos granulares
- Sistema de reportes con estados y respuestas
- Sidebar unificado según rol
- View Transitions + barra de carga

### 🚧 Fase 3 (planeada)

- **Mensajería interna** entre usuarios
- **Notificaciones** en el sidebar (badges)
- **Miniaturas de imágenes** (thumbnails en servidor)
- **Cuota por departamento**
- **Versionado de archivos** (historial de cambios)
- **Compartir por enlace** (URLs públicas temporales)

---

## 🧠 Lo que se aprendió construyendo esto

- **Patrón singleton** para la conexión a BD
- **DTOs** (Data Transfer Objects) para transportar datos entre capas
- **Promoted properties** y **readonly** en PHP 8
- **Prepared statements** con PDO
- **Namespaces** y autoloading manual (PSR-4)
- **Front controller** y enrutamiento básico
- **Sesiones PHP** y manejo de autenticación
- **Bcrypt** para hash de contraseñas
- **Path traversal** y cómo prevenirlo
- **Árboles jerárquicos** con `parent_id` autorreferencial
- **Cascada en foreign keys** (`ON DELETE CASCADE`)
- **Captcha con PHP GD** (generación de imagen)
- **Sistema de permisos granulares** en carpetas compartidas
- **Glassmorfismo** y **neumorfismo** en CSS
- **CSS variables** para temas claro/oscuro
- **View Transitions API**

---

## 👤 Autor

**Jesús Pérez** — [@efaxtower](https://github.com/efaxtower)

Desarrollador autodidacta de Venezuela. Enfocado en PHP, MySQL, Linux y desarrollo web. Estudiando Desarrollo de Software y Aplicaciones en el CEVAC (avalado por el INCE).

🌐 [Portafolio](https://efaxtower.github.io/jp_dada_portafolio/)

---

## 📄 Licencia

Este proyecto está bajo la [MIT License](LICENSE).

---

## 🙏 Agradecimientos

Construido desde cero como proyecto de portafolio y aprendizaje. Cada línea de código fue escrita para entender **por qué** funciona, no solo **cómo**.
```
