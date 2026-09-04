# Changelog

Todos los cambios importantes del proyecto quedan registrados aquí.

## [0.3.0] - 2026-09-02
- Ajuste del dashboard con sidebar lateral y contenido lateral derecho.
- Revisión final de permisos por rol:
  - user: solo visualización de secciones 1, 2 y 3
  - moderator: vista de usuarios en modo lectura
  - admin: CRUD de usuarios con protección frente a superadmin
  - superadmin: control total
- Revisión de la lógica de accesos para evitar recargas que ocultaran contenido.
- Mejoras de tema claro/oscuro con contraste correcto en texto y fondos.
- Estilos visuales modernos con separación de bloques, bordes, sombras y mejor legibilidad.
- Ajuste del color por rol en navbar, sidebar y etiquetas.
- Optimización general del CSS para mantenerclaridad visual y consistencia.

## [0.2.0] - 2026-09-01
- Añadido esquema de base de datos ampliado (`database.sql`): `users`, `contacts`, `drafts`, `services`, `role_audit`.
- Script `scripts/create_admin.php` para crear/actualizar `superadmin`.
- Gestión de roles y permisos UI:
  - `superadmin`, `admin`, `moderator`, `user`
  - `admin` no puede modificar ni crear `superadmin`
  - prevención de cambio de rol propio
- Forzar cambio de contraseña en primer inicio para administradores creados por script.
- Páginas nuevas: `users` y `change_password`.
- Tema oscuro persistente por sesión.
- Mejoras de estilos globales y responsive en `public/css/estilos.css`.

## [0.1.0] - 2025-12-04
- Estructura inicial del proyecto MVC.
- README base y organización inicial del repositorio.

