---
title: Gestion de Documentos
icon: heroicon-o-document-check
---

# Gestion de Documentos

## Flujo de Documentos

El flujo de revision de documentos sigue este proceso:

```
Pendiente → En Revision → Aprobado
                        → Rechazado → (Proveedor re-sube) → En Revision
```

## Revisar Documentos

Para revisar los documentos de un proveedor:

1. Navega a **Proveedores** y abre el proveedor deseado
2. En la pagina de edicion, busca la seccion **Documentos del Expediente**
3. Cada documento muestra su tipo, estado actual y archivo adjunto

## Aprobar un Documento

1. Haz clic en la accion **Aprobar** del documento
2. El estado cambiara a **Aprobado**
3. El proveedor recibira una notificacion por correo electronico

## Rechazar un Documento

1. Haz clic en la accion **Rechazar** del documento
2. Agrega una **nota de rechazo** explicando el motivo (obligatorio)
3. El estado cambiara a **Rechazado**
4. El proveedor recibira una notificacion con el motivo del rechazo

> Las notas de rechazo son visibles para el proveedor en su dashboard. Se recomienda ser claro y especifico para facilitar la correccion.

## Consideraciones Importantes

- **Auditoria**: Cada cambio de estado registra quien lo realizo y cuando
- **Notificaciones**: Aprobaciones y rechazos generan correos automaticos al proveedor
- **Re-subida**: Cuando un documento es rechazado, el proveedor puede subir una nueva version
- **Vencimiento**: Documentos con validez definida mostraran fecha de expiracion
