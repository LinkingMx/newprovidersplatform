# Guía de Testing: Flujo de Documentos del Proveedor

## Resumen del Flujo

El sistema de documentos permite a los administradores asignar tipos de documento a proveedores según su tipo de proveedor, los proveedores cargan los archivos, y los administradores revisan/aprueban/rechazan cada documento. Al aprobar o rechazar, se envía un email de notificación al proveedor.

---

## 1. Configuración Previa (Admin)

### 1.1 Crear Tipos de Documento
- **Ruta**: `/admin/document-types/create`
- **Campos**: nombre (unique), descripción, validez_dias (opcional), activo (boolean)
- **Ejemplo**: "RFC", "Identificación Oficial", "Comprobante de Domicilio"

### 1.2 Crear Tipos de Proveedor
- **Ruta**: `/admin/provider-types/create`
- **Campos**: nombre, descripción, activo
- **Ejemplo**: "Distribuidor", "Proveedor Mayorista"

### 1.3 Vincular Documentos a Tipo de Proveedor
- **Ruta**: `/admin/provider-types/{id}/edit` → tab "Tipos de Documento Requeridos"
- **Acción**: Botón "Adjuntar" → seleccionar DocumentType → marcar "Obligatorio" (toggle, default: true)
- **Tabla pivot**: `document_type_provider_type` con campo `obligatorio`
- **Verificar**: Se pueden adjuntar/desadjuntar múltiples tipos de documento

---

## 2. Asignación de Documentos al Proveedor (Admin)

### 2.1 Prerrequisito
- El proveedor DEBE tener un `provider_type_id` asignado
- Si no tiene tipo de proveedor, el botón "Asignar Documentos" NO es visible

### 2.2 Ejecutar Asignación
- **Ruta**: `/admin/suppliers/{id}/edit`
- **Acción**: Botón "Asignar Documentos" (header action)
- **Modal de confirmación**: "Se asignarán los tipos de documento configurados para el tipo de proveedor. Los documentos ya asignados serán ignorados."
- **Lógica**:
  1. Obtiene DocumentTypes del ProviderType del proveedor
  2. Por cada DocumentType, verifica si ya existe un SupplierDocument
  3. Si NO existe → crea SupplierDocument con `document_state_id = 1` (Pendiente)
  4. Si YA existe → lo omite (idempotente)
- **Notificación**: "X documento(s) asignado(s), Y ya existente(s)"

### 2.3 Casos de prueba
| # | Escenario | Resultado esperado |
|---|-----------|-------------------|
| 1 | Proveedor sin tipo de proveedor | Botón "Asignar Documentos" NO visible |
| 2 | Tipo de proveedor sin documentos configurados | Notificación warning: no hay documentos para asignar |
| 3 | Primera asignación (3 tipos de documento) | 3 SupplierDocuments creados, todos en estado Pendiente |
| 4 | Segunda asignación (mismos documentos) | 0 creados, 3 omitidos (idempotente) |
| 5 | Agregar nuevo tipo de documento al proveedor type y re-asignar | Solo el nuevo se crea, los existentes se omiten |

---

## 3. Visualización en Dashboard del Proveedor

### 3.1 Acceso
- **Ruta**: `GET /dashboard` (middleware: `auth:supplier`)
- **Componente**: `resources/js/pages/Supplier/Dashboard.tsx`

### 3.2 Sección "Mis Documentos"
- Solo visible si hay documentos asignados
- Muestra badge con cantidad total de documentos
- Cada documento muestra:
  - Nombre del tipo de documento
  - Badge de estado con color
  - Nombre del archivo (si fue cargado)
  - Notas de rechazo (solo si estado es Rechazado/rojo)

### 3.3 Colores de Estado (Badge)
| Estado | Color Badge | ID |
|--------|-------------|-----|
| Pendiente | Gris (gray) | 1 |
| En Revisión | Azul (blue) | 2 |
| Aprobado | Verde (green) | 3 |
| Rechazado | Rojo (red) | 4 |

### 3.4 Botones de Acción por Estado
| Estado | Botón "Subir" | Botón "Descargar" | Notas visibles |
|--------|:-------------:|:-----------------:|:--------------:|
| Pendiente (sin archivo) | "Subir" | No | No |
| Pendiente (con archivo) | "Re-subir" | Sí | No |
| En Revisión | No | Sí | No |
| Aprobado | No | Sí | No |
| Rechazado | "Re-subir" | Sí (si hay archivo) | Sí ("Motivo: ...") |

### 3.5 Casos de prueba - Dashboard
| # | Escenario | Resultado esperado |
|---|-----------|-------------------|
| 1 | Sin documentos asignados | Sección "Mis Documentos" NO se muestra |
| 2 | 3 documentos pendientes | Se muestran 3 filas con badge gris "Pendiente" y botón "Subir" |
| 3 | Documento rechazado con notas | Badge rojo + texto "Motivo: {notas}" visible |
| 4 | Documento aprobado | Badge verde, sin botón de subida, con botón de descarga |
| 5 | Documento en revisión | Badge azul, sin botón de subida, con botón de descarga |

---

## 4. Carga de Documentos (Proveedor)

### 4.1 Flujo de Upload
1. Proveedor hace clic en "Subir" o "Re-subir"
2. Se abre modal/dialog con:
   - Título: "Subir Documento" o "Re-subir Documento"
   - Nombre del tipo de documento
   - Si rechazado: alerta con motivo del rechazo previo
   - Input de archivo (acepta: PDF, JPG, JPEG, PNG)
   - Texto helper: "PDF, JPG o PNG. Tamaño máximo: 10 MB."
3. Selecciona archivo y envía formulario
4. `POST /supplier/documents/{supplierDocument}/upload`

### 4.2 Validación del Upload
- **Autorización** (FormRequest):
  - Proveedor autenticado
  - Proveedor es dueño del documento (`supplier_id` match)
  - Estado del documento es Pendiente (1) o Rechazado (4)
- **Archivo**:
  - Requerido
  - Tipo: `pdf, jpg, jpeg, png`
  - Tamaño máximo: 10 MB (10240 KB)
- **Mensajes de error** (español):
  - "Debes seleccionar un archivo para subir."
  - "El archivo no es válido."
  - "El archivo debe ser PDF, JPG o PNG."
  - "El archivo no debe superar los 10 MB."

### 4.3 Lógica del Controlador
1. Si existe archivo previo → lo elimina del disco
2. Almacena nuevo archivo en `storage/app/supplier-documents/{supplier_id}/`
3. Actualiza SupplierDocument:
   - `archivo_path` = ruta del archivo
   - `archivo_nombre` = nombre original del archivo
   - `uploaded_at` = timestamp actual
   - `document_state_id` = 1 (Pendiente) — se resetea siempre
   - `notas` = null — se limpian las notas previas
4. Redirect a dashboard con mensaje de éxito

### 4.4 Casos de prueba - Upload
| # | Escenario | Resultado esperado |
|---|-----------|-------------------|
| 1 | Upload PDF válido (estado Pendiente) | Archivo almacenado, estado = Pendiente, uploaded_at actualizado |
| 2 | Upload JPG válido | Igual que #1 |
| 3 | Upload PNG válido | Igual que #1 |
| 4 | Re-upload tras rechazo | Archivo anterior eliminado, nuevo almacenado, estado = Pendiente, notas = null |
| 5 | Archivo > 10 MB | Error: "El archivo no debe superar los 10 MB." |
| 6 | Archivo .docx (tipo inválido) | Error: "El archivo debe ser PDF, JPG o PNG." |
| 7 | Sin archivo seleccionado | Error: "Debes seleccionar un archivo para subir." |
| 8 | Upload cuando estado = En Revisión (2) | 403 Forbidden |
| 9 | Upload cuando estado = Aprobado (3) | 403 Forbidden |
| 10 | Upload de documento de otro proveedor | 403 Forbidden |
| 11 | Upload sin autenticación | Redirect a login |

---

## 5. Descarga de Documentos (Proveedor)

### 5.1 Endpoint
- `GET /supplier/documents/{supplierDocument}/download`
- Middleware: `auth:supplier`

### 5.2 Lógica
1. Verifica que el proveedor autenticado sea dueño del documento
2. Verifica que el archivo exista en disco
3. Descarga con nombre original del archivo

### 5.3 Casos de prueba - Download
| # | Escenario | Resultado esperado |
|---|-----------|-------------------|
| 1 | Descarga propia exitosa | Archivo descargado con nombre original |
| 2 | Documento sin archivo | Redirect con error "El archivo no se encontró" |
| 3 | Documento de otro proveedor | 403 Forbidden |
| 4 | Sin autenticación | Redirect a login |
| 5 | Archivo eliminado del disco | Redirect con error |

---

## 6. Revisión de Documentos (Admin - Filament)

### 6.1 Ubicación
- **Ruta**: `/admin/suppliers/{id}/edit` → tab "Documentos del Expediente"
- **Componente**: `DocumentsRelationManager`

### 6.2 Tabla de Documentos
| Columna | Descripción |
|---------|-------------|
| Tipo de Documento | Nombre del DocumentType (searchable, sortable) |
| Estado | Badge con etiqueta y color del DocumentState |
| Archivo | Nombre del archivo o "Sin archivo" |
| Vencimiento | Fecha o "Sin vencimiento" |
| Cargado | Fecha/hora de upload o "No cargado" |

### 6.3 Acción "Cambiar Estado"
- **Botón**: Por cada fila de documento, icono de flecha circular, color warning
- **Modal**: Formulario con:
  - Select "Nuevo Estado": **SOLO muestra transiciones válidas** según el estado actual
  - Textarea "Notas": Motivo del cambio (opcional)

### 6.4 Transiciones Válidas por Estado
| Estado Actual | Transiciones Disponibles |
|---------------|-------------------------|
| Pendiente (1) | → En Revisión |
| En Revisión (2) | → Aprobado, → Rechazado |
| Aprobado (3) | → Rechazado |
| Rechazado (4) | → En Revisión |

**IMPORTANTE**: El select SOLO muestra las opciones válidas. No se puede saltar estados.

### 6.5 Al Cambiar Estado
1. Se actualiza `document_state_id` al nuevo estado
2. Se guardan `notas` (si las hay)
3. Se registra `reviewed_at` = timestamp actual
4. Se registra `reviewed_by` = ID del admin actual
5. **Si el nuevo estado es Aprobado o Rechazado** → se envía email al proveedor

### 6.6 Casos de prueba - Cambiar Estado
| # | Escenario | Resultado esperado |
|---|-----------|-------------------|
| 1 | Pendiente → En Revisión | Estado actualizado, reviewed_at/by registrados |
| 2 | En Revisión → Aprobado | Estado = Aprobado, email enviado al proveedor |
| 3 | En Revisión → Rechazado (con notas) | Estado = Rechazado, notas guardadas, email enviado |
| 4 | Rechazado → En Revisión | Estado actualizado (proveedor re-subió) |
| 5 | Aprobado → Rechazado | Estado cambiado, email de rechazo enviado |
| 6 | Pendiente: verificar opciones del select | Solo muestra "En Revisión" |
| 7 | En Revisión: verificar opciones del select | Solo muestra "Aprobado" y "Rechazado" |
| 8 | Aprobado: verificar opciones del select | Solo muestra "Rechazado" |
| 9 | Rechazado: verificar opciones del select | Solo muestra "En Revisión" |

---

## 7. Notificaciones por Email

### 7.1 Email de Aprobación
- **Mailable**: `SupplierDocumentStatusMailable`
- **Template**: `resources/views/emails/supplier-document-status.blade.php`
- **Subject**: `Tu documento "{nombre_tipo}" fue Aprobado`
- **Destinatario**: Email del proveedor
- **Contenido**:
  - Saludo: "Hola, {nombre_proveedor}"
  - Mensaje: Tu documento **{nombre_tipo}** ha sido **aprobado** por nuestro equipo.
  - Acción: "No es necesario que realices ninguna acción adicional para este documento."
  - Botón: "Ir a mi Panel" → enlace al dashboard
  - Firma: "El equipo de Costeño Group"

### 7.2 Email de Rechazo
- **Subject**: `Tu documento "{nombre_tipo}" fue Rechazado`
- **Contenido**:
  - Saludo: "Hola, {nombre_proveedor}"
  - Mensaje: Tu documento **{nombre_tipo}** ha sido **rechazado**.
  - **Motivo del rechazo**: Muestra las notas del admin en blockquote
  - Acción: "Por favor, sube nuevamente el documento corregido desde tu panel de proveedor."
  - Botón: "Ir a mi Panel" → enlace al dashboard
  - Firma: "El equipo de Costeño Group"

### 7.3 Branding del Email
- Color de textos destacados: `#191731` (navy/logo)
- Botón: color primary (`#191731`)
- Logo: SVG de Costeño en header
- Footer: "© {año} Costeño Group. All rights reserved."

### 7.4 Casos de prueba - Emails
| # | Escenario | Resultado esperado |
|---|-----------|-------------------|
| 1 | Aprobar documento | Email enviado al proveedor con subject de aprobación |
| 2 | Rechazar documento con notas | Email enviado con motivo de rechazo visible |
| 3 | Rechazar documento sin notas | Email enviado sin sección de motivo |
| 4 | Cambiar a "En Revisión" | NO se envía email |
| 5 | Verificar destinatario | Email va a `supplier.email` |
| 6 | Verificar botón "Ir a mi Panel" | Enlace lleva a ruta `/dashboard` |

---

## 8. Flujo Completo End-to-End

### Escenario Feliz (Happy Path)
```
1. Admin crea DocumentType "RFC" y "Identificación"
2. Admin crea ProviderType "Distribuidor"
3. Admin vincula RFC + Identificación → Distribuidor (ambos obligatorios)
4. Admin crea Supplier "Juan García" con tipo "Distribuidor"
5. Admin hace clic "Asignar Documentos"
   → 2 SupplierDocuments creados en Pendiente
6. Proveedor inicia sesión, ve Dashboard
   → 2 documentos con badge gris "Pendiente" y botón "Subir"
7. Proveedor sube RFC.pdf
   → Estado: Pendiente, archivo almacenado, botón cambia a "Re-subir"
8. Admin abre supplier edit → tab Documentos
   → Ve RFC con estado Pendiente y archivo "RFC.pdf"
9. Admin cambia RFC: Pendiente → En Revisión
   → Badge azul, no se envía email
10. Admin cambia RFC: En Revisión → Aprobado
    → Badge verde, EMAIL enviado: "Tu documento RFC fue Aprobado"
11. Proveedor recibe email de aprobación
    → Ve botón "Ir a mi Panel", sin acción requerida
12. En dashboard: RFC muestra badge verde, sin botón de subida
```

### Escenario de Rechazo y Re-subida
```
1. (Pasos 1-8 del escenario feliz)
9. Admin cambia Identificación: Pendiente → En Revisión
10. Admin cambia Identificación: En Revisión → Rechazado
    → Notas: "Documento ilegible, favor de subir en mejor resolución"
    → EMAIL enviado: "Tu documento Identificación fue Rechazado"
11. Proveedor recibe email con motivo del rechazo
12. Proveedor ve Dashboard:
    → Badge rojo "Rechazado"
    → Texto: "Motivo: Documento ilegible, favor de subir en mejor resolución"
    → Botón "Re-subir" visible
13. Proveedor hace clic "Re-subir" → modal muestra alerta con motivo previo
14. Proveedor sube nueva versión
    → Estado se resetea a Pendiente, notas se limpian, archivo anterior eliminado
15. Admin revisa de nuevo: En Revisión → Aprobado
    → EMAIL de aprobación enviado
```

---

## 9. Almacenamiento de Archivos

| Aspecto | Detalle |
|---------|---------|
| Disco | `local` (privado) |
| Ruta | `storage/app/supplier-documents/{supplier_id}/{filename}` |
| Acceso | Solo vía endpoint de descarga autenticado |
| Re-upload | Archivo anterior se elimina antes de guardar el nuevo |
| Tipos permitidos | PDF, JPG, JPEG, PNG |
| Tamaño máximo | 10 MB |

---

## 10. Máquina de Estados - Diagrama

```
                    ┌────────────────┐
                    │   Pendiente    │ ← Estado inicial al asignar
                    │    (gray)      │ ← Estado al re-subir
                    └───────┬────────┘
                            │
                   Admin: "En Revisión"
                            │
                            ▼
                    ┌────────────────┐
                    │  En Revisión   │
                    │    (blue)      │
                    └───┬────────┬───┘
                        │        │
              Admin:    │        │   Admin:
             "Aprobado" │        │  "Rechazado"
                        │        │  + notas
                        ▼        ▼
              ┌──────────┐    ┌──────────────┐
              │ Aprobado  │    │  Rechazado   │
              │  (green)  │    │    (red)     │
              │ completado│    └──────┬───────┘
              └─────┬─────┘           │
                    │          Proveedor re-sube
               Admin puede     archivo → estado
               revocar →       se resetea a
               "Rechazado"     Pendiente (1)
                    │                 │
                    └─────────────────┘
                         (ciclo)

Emails enviados:
  ✉ En Revisión → Aprobado = Email de aprobación
  ✉ En Revisión → Rechazado = Email de rechazo (con motivo)
  ✉ Aprobado → Rechazado = Email de rechazo (revocación)
```

---

## 11. Archivos Clave del Sistema

| Archivo | Propósito |
|---------|-----------|
| `app/Models/DocumentType.php` | Modelo de tipo de documento |
| `app/Models/DocumentState.php` | Modelo de estado con transiciones |
| `app/Models/SupplierDocument.php` | Modelo pivote: supplier + document + state |
| `app/Models/ProviderType.php` | Tipo de proveedor con documentos requeridos |
| `app/Http/Controllers/Supplier/SupplierDocumentController.php` | Upload y download |
| `app/Http/Requests/Supplier/SupplierDocumentUploadRequest.php` | Validación de upload |
| `app/Mail/SupplierDocumentStatusMailable.php` | Email de aprobación/rechazo |
| `app/Filament/Resources/Suppliers/Actions/AsignarDocumentosAction.php` | Acción de asignación masiva |
| `app/Filament/Resources/Suppliers/RelationManagers/DocumentsRelationManager.php` | Tabla + cambio de estado |
| `resources/js/pages/Supplier/Dashboard.tsx` | UI del proveedor (upload/download) |
| `resources/views/emails/supplier-document-status.blade.php` | Template email aprobación/rechazo |
| `database/seeders/DocumentStateSeeder.php` | Seeds de los 4 estados |
| `routes/web.php` | Rutas de dashboard, upload, download |

---

## 12. Tests Existentes

| Archivo de Test | Cobertura |
|-----------------|-----------|
| `tests/Feature/Supplier/SupplierDocumentUploadTest.php` | Upload (PDF/JPG/PNG), re-upload, restricciones de estado, download, dashboard |
| `tests/Feature/Filament/AsignarDocumentosActionTest.php` | Asignación masiva, idempotencia, visibilidad del botón |

### Tests Pendientes Sugeridos
- Validación de transiciones en el select (solo opciones válidas)
- Envío de email al aprobar/rechazar
- Contenido del email de aprobación vs rechazo
- Notas de rechazo incluidas en el email
- No envío de email al cambiar a "En Revisión"
