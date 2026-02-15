---
title: Roles y Permisos
icon: heroicon-o-shield-check
---

# Roles y Permisos

## Sistema de Autorizacion

La plataforma utiliza un sistema de roles y permisos basado en **Filament Shield**. Cada usuario del panel administrativo tiene asignado un rol que define sus capacidades.

## Roles Disponibles

| Rol | Descripcion |
|-----|-------------|
| **Super Admin** | Acceso completo a todas las funciones del sistema |

> Se pueden crear roles adicionales segun las necesidades de la organizacion.

## Gestionar Roles

1. Navega a **Administracion > Roles** en la barra lateral
2. Aqui puedes ver, crear y editar roles

### Crear un Nuevo Rol

1. Haz clic en **Nuevo Rol**
2. Define el nombre del rol
3. Selecciona los permisos para cada recurso:
   - **Ver listado** (view_any)
   - **Ver detalle** (view)
   - **Crear** (create)
   - **Editar** (update)
   - **Eliminar** (delete)
4. Guarda el rol

### Asignar un Rol a un Usuario

Los roles se asignan directamente al crear o editar un usuario del panel.

## Permisos por Recurso

Cada recurso del sistema (Proveedores, Tipos de Documento, etc.) tiene permisos independientes:

- `view_any`: Ver la lista del recurso
- `view`: Ver el detalle de un registro
- `create`: Crear nuevos registros
- `update`: Editar registros existentes
- `delete`: Eliminar registros (soft delete)

## Seguridad

- Los usuarios sin rol asignado no pueden acceder al panel
- El rol **Super Admin** tiene acceso irrestricto via Gate::before
- Los cambios de permisos aplican inmediatamente
