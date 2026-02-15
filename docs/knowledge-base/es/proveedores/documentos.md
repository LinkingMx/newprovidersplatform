---
title: Documentos del Proveedor
icon: heroicon-o-document-arrow-up
---

# Documentos del Proveedor

## Expediente Digital

Cada proveedor tiene un expediente digital compuesto por los tipos de documento configurados en el sistema. El proveedor debe subir cada documento requerido.

## Tipos de Documento Comunes

Los tipos de documento son configurables por el administrador. Ejemplos tipicos:

- Constancia de Situacion Fiscal
- Comprobante de Domicilio
- Acta Constitutiva
- Poder del Representante Legal
- Caratula Bancaria
- Opinion de Cumplimiento (SAT)

## Proceso de Subida

1. El proveedor accede a su **Dashboard**
2. En la seccion **Mis Documentos**, localiza el documento a subir
3. Hace clic en **Subir** o en el area de carga de archivos
4. Selecciona el archivo desde su dispositivo
5. El documento queda en estado **En Revision**

## Formatos Aceptados

- PDF (recomendado)
- Imagenes: JPG, PNG
- Tamano maximo: Segun configuracion del servidor

## Ciclo de Vida del Documento

```
Sin subir → Pendiente
Pendiente → Subido (En Revision)
En Revision → Aprobado ✓
En Revision → Rechazado ✗ → Re-subida → En Revision
```

## Documento Rechazado

Cuando un documento es rechazado:

1. El proveedor recibe una **notificacion por correo**
2. En el dashboard, el documento muestra estado **Rechazado** en rojo
3. Las **notas del revisor** explican el motivo del rechazo
4. El proveedor puede subir una nueva version del documento
5. El ciclo de revision se reinicia

## Problemas Comunes

| Problema | Solucion |
|----------|----------|
| No puede subir archivo | Verificar formato y tamano del archivo |
| Documento sigue en "Pendiente" | El proveedor necesita hacer clic en subir |
| No recibio notificacion de rechazo | Verificar correo en spam |
| Quiere cambiar un documento aprobado | Contactar al administrador |
| No aparecen tipos de documento | El admin debe activar tipos en el catalogo |
