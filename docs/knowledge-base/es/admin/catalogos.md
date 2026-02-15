---
title: Catalogos del Sistema
icon: heroicon-o-book-open
---

# Catalogos del Sistema

Los catalogos son tablas de configuracion que definen las opciones disponibles en toda la plataforma. Se acceden desde la barra lateral.

## Tipos de Proveedor

Define las categorias de proveedores que maneja la empresa.

**Campos:**
- **Nombre**: Nombre del tipo (ej. "Hotel", "Transportista", "Restaurante")
- **Descripcion**: Descripcion opcional del tipo
- **Activo**: Indica si el tipo esta disponible para seleccion

**Acciones disponibles:**
- Crear, editar, desactivar tipos de proveedor
- Los tipos desactivados no aparecen en formularios pero se mantienen en registros existentes

## Tipos de Documento

Define los documentos que se pueden solicitar a los proveedores.

**Campos:**
- **Nombre**: Nombre del tipo de documento (ej. "Constancia de Situacion Fiscal")
- **Descripcion**: Descripcion o instrucciones para el proveedor
- **Validez (dias)**: Numero de dias antes de que el documento expire (0 = sin vencimiento)
- **Activo**: Indica si el tipo esta disponible

> Los tipos de documento activos se muestran automaticamente en el expediente del proveedor.

## Estados de Documento

Define los posibles estados por los que pasa un documento.

**Campos:**
- **Nombre**: Nombre interno del estado
- **Etiqueta**: Texto que se muestra al usuario
- **Color**: Color hexadecimal para la etiqueta visual
- **Por defecto**: Indica si es el estado inicial asignado automaticamente

**Estados preconfigurados:**

| Estado | Color | Descripcion |
|--------|-------|-------------|
| Pendiente | Naranja | Documento aun no subido o en espera |
| En Revision | Azul | Documento subido, pendiente de revision |
| Aprobado | Verde | Documento revisado y aceptado |
| Rechazado | Rojo | Documento rechazado, requiere re-subida |

> Siempre debe existir un estado marcado como **Por defecto**. Este se asigna automaticamente al crear un nuevo registro de documento.
