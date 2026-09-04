# Mi Sistema MVC

Version: 0.3.0

Sistema web desarrollado con PHP y MySQL aplicando una estructura MVC con autenticación por sesión, control de roles y panel tipo dashboard.

## Características principales
- Login y registro de usuarios
- Recuperación de contraseña y cambio de contraseña obligatorio
- Dashboard con sidebar lateral y contenido dinámico a la derecha
- Roles diferenciados:
  - user
  - moderator
  - admin
  - superadmin
- Gestión de usuarios con permisos por rol
- Vista de usuarios para moderador en modo lectura
- CRUD funcional para admin y superadmin
- Modo claro y oscuro con tema por rol
- Estilos modernizados y responsivos

## Roles y permisos
- user:
  - puede navegar entre las secciones 1, 2 y 3
  - no ve la sección de usuarios
  - no puede operar CRUD
- moderator:
  - puede ver las tres secciones
  - puede ver la sección de usuarios en modo consulta
  - no puede editar ni eliminar usuarios
- admin:
  - puede ver todas las secciones
  - puede usar CRUD de usuarios
  - no puede tocar roles administrativos ni modificar a superadmin
- superadmin:
  - control total sobre el sistema
  - puede ver y modificar cualquier parte del dashboard

## Requisitos
- PHP 8.x
- MySQL / MariaDB
- XAMPP, WAMP o servidor local con PHP habilitado

## Instalación
1. Clona el proyecto en tu servidor local:
```bash
git clone <url-del-repositorio>
```

2. Importa la base de datos:
```bash
cd d:\xampp\htdocs\MVC
mysql -u root < database.sql
```

3. Crea el superadmin inicial:
```bash
"C:\xampp\php\php.exe" scripts\create_admin.php
```

4. Revisa las credenciales creadas en:
```bash
type scripts\admin_credentials.txt
```

5. Abre la aplicación:
```text
http://localhost/mvc/public/index.php?page=login
```

## Archivos clave
- `config/database.php` — conexión a MySQL
- `controllers/` — lógica del flujo y permisos
- `models/` — acceso a datos
- `views/` — estructura de interfaz
- `public/css/estilos.css` — estilos globales y temas por rol
- `public/index.php` — router principal y sesión
- `scripts/create_admin.php` — creación del primer superadmin

## Notas de seguridad
- La sesión expira tras 10 minutos de inactividad.
- El cambio de contraseña se fuerza en usuarios recién creados o cuando lo requiere la política.
- Los admin no pueden degradar ni manipular roles administrativos.

## Versionado
- 0.3.0 — dashboard con sidebar, permisos por rol, modo claro/oscuro y estilos modernizados
- 0.2.0 — sistema de roles, gestión de usuarios y panel administrativo
- 0.1.0 — estructura inicial del proyecto MVC

