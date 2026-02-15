---
title: Gestion de Proveedores
icon: heroicon-o-building-office
---

# Gestion de Proveedores

## Vista General

La seccion de **Proveedores** es el corazon de la plataforma. Desde aqui puedes gestionar todo el ciclo de vida de un proveedor.

## Estados del Proveedor

Cada proveedor pasa por un flujo de estados definido:

| Estado | Descripcion |
|--------|-------------|
| **Creado** | Proveedor registrado en el sistema, aun sin invitacion |
| **Invitado** | Se envio invitacion por correo electronico |
| **Registrado** | El proveedor establecio su contrasena |
| **Perfil Completado** | El proveedor completo su onboarding |
| **Activo** | Proveedor verificado y operativo |

## Listado de Proveedores

En la tabla principal puedes:

- **Buscar** proveedores por nombre, email o RFC
- **Filtrar** por estado, sucursal o tipo de proveedor
- **Ordenar** por cualquier columna
- **Exportar** la lista a CSV/Excel

## Crear un Proveedor

1. Haz clic en el boton **Nuevo Proveedor**
2. Completa los campos obligatorios:
   - Nombre o razon social
   - Correo electronico
   - Tipo de proveedor
3. Guarda el registro

> El proveedor se creara con estado **Creado**. Deberas enviarlo una invitacion para que pueda registrarse.

## Invitar a un Proveedor

1. Abre el detalle del proveedor
2. En la seccion de acciones, haz clic en **Enviar Invitacion**
3. El proveedor recibira un correo con un enlace para establecer su contrasena
4. La invitacion tiene una vigencia de 72 horas

## Editar un Proveedor

1. Haz clic en el icono de edicion o en el nombre del proveedor
2. Modifica los campos necesarios
3. Guarda los cambios

## Asignar Sucursales

En la pagina de edicion del proveedor, encontraras el **Relation Manager de Sucursales**:

1. Haz clic en **Asociar Sucursal**
2. Selecciona las sucursales correspondientes
3. Confirma la asociacion

## Documentos del Expediente

En la pagina de edicion, la seccion **Documentos del Expediente** muestra:

- Documentos subidos por el proveedor
- Estado actual de cada documento (Pendiente, En Revision, Aprobado, Rechazado)
- Acciones para aprobar o rechazar documentos

Para mas detalle consulta la seccion [Gestion de Documentos](admin.documentos).
