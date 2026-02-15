---
title: Sucursales
icon: heroicon-o-map-pin
---

# Gestion de Sucursales

## Vista General

Las sucursales representan las ubicaciones fisicas o unidades de negocio de la empresa. Los proveedores se asocian a una o mas sucursales.

## Listado de Sucursales

La tabla principal muestra:

- **Nombre** de la sucursal
- **Numero de proveedores** asociados
- **Estado** (activa/inactiva)

## Crear una Sucursal

1. Haz clic en **Nueva Sucursal**
2. Completa los campos:
   - **Nombre**: Nombre identificador de la sucursal
   - Campos adicionales segun configuracion
3. Guarda el registro

## Asociar Proveedores

La asociacion proveedor-sucursal se gestiona desde dos lugares:

1. **Desde el proveedor**: En la pagina de edicion del proveedor, seccion Sucursales
2. **Desde la sucursal**: En la pagina de detalle de la sucursal, seccion Proveedores

## Consideraciones

- Una sucursal puede tener multiples proveedores
- Un proveedor puede estar asociado a multiples sucursales
- Eliminar una sucursal (soft delete) no afecta a los proveedores asociados, pero la asociacion se desactiva
