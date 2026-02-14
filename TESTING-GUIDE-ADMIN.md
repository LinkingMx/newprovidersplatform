# Guia de Testing - Panel de Administracion (Filament)

Manual de pruebas paso a paso para el panel de administracion.

**URL Base:** `https://newprovidersplatform.test/admin`
**Framework:** Filament v5 (Livewire)
**Guard de autenticacion:** `web` (User model)
**Plugin RBAC:** Filament Shield (Spatie Permissions)

---

## Tabla de Contenidos

1. [Estructura de Navegacion](#1-estructura-de-navegacion)
2. [Pre-requisitos para Testing](#2-pre-requisitos-para-testing)
3. [Flujo 1: Login de Administrador](#3-flujo-1-login-de-administrador)
4. [Flujo 2: Gestion de Proveedores (CRUD)](#4-flujo-2-gestion-de-proveedores-crud)
5. [Flujo 3: Acciones de Proveedor](#5-flujo-3-acciones-de-proveedor)
6. [Flujo 4: Documentos del Expediente](#6-flujo-4-documentos-del-expediente)
7. [Flujo 5: Sucursales del Proveedor](#7-flujo-5-sucursales-del-proveedor)
8. [Flujo 6: Gestion de Sucursales](#8-flujo-6-gestion-de-sucursales)
9. [Flujo 7: Tipos de Proveedor](#9-flujo-7-tipos-de-proveedor)
10. [Flujo 8: Tipos de Documentos](#10-flujo-8-tipos-de-documentos)
11. [Flujo 9: Estados de Documento](#11-flujo-9-estados-de-documento)
12. [Flujo 10: Gestion de Usuarios](#12-flujo-10-gestion-de-usuarios)
13. [Flujo 11: Roles y Permisos (Shield)](#13-flujo-11-roles-y-permisos-shield)
14. [Conexion con el Portal de Proveedores](#14-conexion-con-el-portal-de-proveedores)
15. [Factories y Datos de Prueba](#15-factories-y-datos-de-prueba)
16. [Checklist de Regresion](#16-checklist-de-regresion)

---

## 1. Estructura de Navegacion

El sidebar del admin se organiza en 3 grupos:

```
Gestion de Proveedores
  |-- Proveedores           (sort: 1)
  |-- Sucursales            (sort: 2)
  |-- Tipos de Proveedor    (sort: 3)

Configuracion del Sistema
  |-- Tipos de Documentos   (sort: 4)
  |-- Estados de Documento  (sort: 5)

Administracion
  |-- Usuarios              (sort: 6)
  |-- Roles                 (sort: 7)
```

---

## 2. Pre-requisitos para Testing

### Crear usuario admin

```php
// Via factory
$user = User::factory()->create();
actingAs($user);

// Via seeder (test@example.com / password)
$this->seed(DatabaseSeeder::class);
```

### Seeders necesarios

```php
// Estados de documento (OBLIGATORIO para tests de documentos)
$this->seed(DocumentStateSeeder::class);
// Crea: Pendiente (1), En Revision (2), Aprobado (3), Rechazado (4)
```

### Autenticacion en tests de Filament

```php
// SIEMPRE autenticarse antes de probar recursos Filament
use App\Models\User;

$user = User::factory()->create();

// Opcion 1: actingAs global
$this->actingAs($user);

// Opcion 2: en test de Livewire
livewire(ListSuppliers::class)
    ->assertSuccessful();
```

---

## 3. Flujo 1: Login de Administrador

**Ruta:** `GET /admin/login`
**Clase:** `App\Filament\Pages\Login`

### Pasos de prueba

#### 3.1 Pagina de login

1. Navegar a `https://newprovidersplatform.test/admin/login`
2. **Verificar:** Logo de la empresa visible
3. **Verificar:** Heading: "Acceso de Administradores"
4. **Verificar:** Subheading: "Ingresa con tu correo y contrasena"
5. **Verificar:** Campos: Email, Contrasena
6. **Verificar:** Link "Olvidaste tu contrasena?" visible (password reset habilitado)
7. **Verificar:** Link "Volver al inicio" con icono de flecha (UNA sola flecha)
8. **Verificar:** Boton de submit "Sign in" o "Iniciar sesion"

#### 3.2 Login exitoso

1. Ingresar credenciales de admin validas
2. **Verificar:** Redirige al dashboard (`/admin`)
3. **Verificar:** Sidebar de navegacion visible con todos los grupos

#### 3.3 Login fallido

1. Ingresar credenciales incorrectas
2. **Verificar:** Mensaje de error apropiado
3. **Verificar:** No se crea sesion

#### 3.4 Recuperar contrasena (Filament nativo)

1. Clic en "Olvidaste tu contrasena?"
2. **Verificar:** Redirige a formulario de request password reset
3. Ingresar email
4. **Verificar:** Email de reset enviado

#### 3.5 Link "Volver al inicio"

1. Clic en "Volver al inicio"
2. **Verificar:** Navega a `/` (pagina principal)

---

## 4. Flujo 2: Gestion de Proveedores (CRUD)

**Ruta:** `/admin/suppliers`
**Resource:** `App\Filament\Resources\Suppliers\SupplierResource`

### 4.1 Listado de proveedores

1. Navegar a `/admin/suppliers`
2. **Verificar columnas visibles:**
   - Nombre (searchable, sortable)
   - Email (searchable, copyable)
   - Estado (badge con colores)
   - Sucursales (conteo)
   - Fecha de creacion
3. **Verificar filtros:**
   - Filtro por Estado (dropdown: Creado/Invitado/Registrado/Perfil Completo/Activo)
   - Filtro de eliminados (Trashed)
4. **Verificar busqueda:** Buscar por nombre o email

**Colores de badge de estado:**
| Estado | Color |
|--------|-------|
| `created` | Gris |
| `invited` | Amarillo |
| `registered` | Azul |
| `profile_completed` | Naranja |
| `active` | Verde |

```php
$user = User::factory()->create();
$suppliers = Supplier::factory()->count(5)->create();

livewire(ListSuppliers::class)
    ->assertCanSeeTableRecords($suppliers)
    ->searchTable($suppliers->first()->name)
    ->assertCanSeeTableRecords($suppliers->take(1))
    ->assertCanNotSeeTableRecords($suppliers->skip(1));
```

### 4.2 Crear proveedor

1. Clic en "Nuevo" o boton de crear
2. **Verificar formulario (Seccion "Informacion Basica"):**
   - Nombre (requerido, max 255)
   - Email (requerido, unico, helper: "Se enviara invitacion a este correo")
   - Tipo de Proveedor (select searchable con relacion)
3. **Verificar:** Seccion "Estado" NO visible en creacion
4. **Verificar:** Seccion "Cambiar Contrasena" NO visible en creacion
5. Llenar datos y guardar
6. **Verificar:** Proveedor creado con status `created`
7. **Verificar:** Redirige a listado

```php
livewire(CreateSupplier::class)
    ->fillForm([
        'name' => 'Proveedor Test',
        'email' => 'proveedor@test.com',
        'provider_type_id' => $providerType->id,
    ])
    ->call('create')
    ->assertNotified()
    ->assertRedirect();

$this->assertDatabaseHas('suppliers', [
    'name' => 'Proveedor Test',
    'email' => 'proveedor@test.com',
    'status' => 'created',
]);
```

### 4.3 Editar proveedor

1. Clic en editar un proveedor existente
2. **Verificar layout del formulario (2 columnas):**
   - **Izquierda:** Seccion "Informacion Basica" (Nombre, Email, Tipo de Proveedor)
   - **Derecha (agrupados):** Seccion "Estado" + Seccion "Cambiar Contrasena"
3. **Verificar:** Estado es solo lectura (disabled)
4. **Verificar:** "Cambiar Contrasena" esta colapsada por defecto
5. **Verificar botones de header:**
   - "Asignar Documentos" (color primary/purpura)
   - "Reenviar Invitacion" (color primary/purpura)
   - "Borrar"
6. **Verificar tabs de relaciones (en orden):**
   - "Documentos del Expediente" (PRIMERO)
   - "Sucursales" (SEGUNDO)

### 4.4 Validaciones de formulario

| Campo | Regla | Test |
|-------|-------|------|
| Nombre | Requerido | Dejar vacio -> error |
| Nombre | Max 255 | Texto largo -> error |
| Email | Requerido | Dejar vacio -> error |
| Email | Formato email | "no-es-email" -> error |
| Email | Unico | Email duplicado -> error |

```php
livewire(CreateSupplier::class)
    ->fillForm([
        'name' => null,
        'email' => 'invalid-email',
    ])
    ->call('create')
    ->assertHasFormErrors([
        'name' => 'required',
        'email' => 'email',
    ]);
```

### 4.5 Eliminar proveedor

1. Clic en "Borrar" en edicion
2. **Verificar:** Modal de confirmacion
3. Confirmar
4. **Verificar:** Soft delete (registro mantiene `deleted_at`)
5. **Verificar:** Proveedor no aparece en listado normal
6. **Verificar:** Aparece con filtro "Trashed"

### 4.6 Restaurar proveedor

1. Activar filtro "Trashed"
2. Encontrar proveedor eliminado
3. Clic en "Restaurar"
4. **Verificar:** `deleted_at` vuelve a null
5. **Verificar:** Proveedor aparece en listado normal

### 4.7 Forzar eliminacion

1. En proveedor eliminado, clic "Eliminar permanentemente"
2. **Verificar:** Registro eliminado de la BD completamente

---

## 5. Flujo 3: Acciones de Proveedor

### 5.1 Reenviar Invitacion

**Ubicacion:** Boton header en edicion de proveedor
**Color:** Primary (purpura)

**Pasos:**
1. Abrir edicion de un proveedor que NO sea `active`
2. Clic en "Reenviar Invitacion"
3. **Verificar:** Modal de confirmacion
4. Confirmar
5. **Verificar:** Se crea nuevo `SupplierInvitation` con:
   - Token aleatorio (64 chars)
   - `sent_at` = ahora
   - `expires_at` = ahora + 7 dias
6. **Verificar:** Status del proveedor cambia a `invited`
7. **Verificar:** Notificacion de exito: "Invitacion Enviada"

**Caso de error:**
1. Intentar reenviar a proveedor `active`
2. **Verificar:** Notificacion de error: "No se puede reenviar" / "El proveedor ya esta activo"

```php
$supplier = Supplier::factory()->create(['status' => 'created']);

livewire(EditSupplier::class, ['record' => $supplier->id])
    ->callAction('resendInvitation')
    ->assertNotified();

$this->assertDatabaseHas('suppliers', [
    'id' => $supplier->id,
    'status' => 'invited',
]);
$this->assertDatabaseHas('supplier_invitations', [
    'supplier_id' => $supplier->id,
]);
```

### 5.2 Asignar Documentos

**Ubicacion:** Boton header en edicion de proveedor
**Color:** Primary (purpura)
**Clase:** `App\Filament\Resources\Suppliers\Actions\AsignarDocumentosAction`

**Pre-requisitos:**
- Proveedor tiene `provider_type_id` asignado
- El tipo de proveedor tiene tipos de documento asociados

**Pasos:**
1. Abrir edicion de proveedor con tipo de proveedor configurado
2. Clic en "Asignar Documentos"
3. **Verificar:** Modal con heading "Asignar Tipos de Documento"
4. **Verificar:** Descripcion sobre documentos configurados
5. Clic "Asignar"
6. **Verificar:** Se crean `SupplierDocument` para cada tipo de documento del tipo de proveedor
7. **Verificar:** Cada documento creado con estado por defecto (Pendiente)
8. **Verificar:** Notificacion con conteo de documentos creados y omitidos
9. **Verificar:** Documentos aparecen en tab "Documentos del Expediente"

**Idempotencia:**
1. Ejecutar "Asignar Documentos" dos veces
2. **Verificar:** Segunda ejecucion no crea duplicados
3. **Verificar:** Notificacion muestra "X omitidos" para los ya existentes

```php
$providerType = ProviderType::factory()->create();
$docTypes = DocumentType::factory()->count(3)->create();
$providerType->documentTypes()->attach($docTypes->pluck('id'), ['obligatorio' => true]);

$supplier = Supplier::factory()->create(['provider_type_id' => $providerType->id]);

livewire(EditSupplier::class, ['record' => $supplier->id])
    ->callAction('asignarDocumentos')
    ->assertNotified();

$this->assertEquals(3, $supplier->documents()->count());
```

### 5.3 Cambiar Contrasena del Proveedor

**Ubicacion:** Seccion colapsable "Cambiar Contrasena" en formulario de edicion
**Solo visible en:** Modo edicion (no en creacion)

**Pasos:**
1. Abrir edicion de proveedor
2. Expandir seccion "Cambiar Contrasena" (click en header)
3. **Verificar campos:**
   - Nueva Contrasena (password con toggle, min 8 chars)
   - Confirmar Contrasena (password con toggle)
4. Ingresar nueva contrasena y confirmarla
5. Guardar cambios
6. **Verificar:** `password_hash` actualizado en BD (hashed)
7. **Verificar:** El proveedor puede loguearse con la nueva contrasena
8. **Verificar:** La contrasena anterior ya no funciona

**Caso: Dejar vacio**
1. No llenar campos de contrasena
2. Guardar cambios
3. **Verificar:** Contrasena NO se modifica (campo no se dehydrata si vacio)

**Validaciones:**
| Caso | Resultado |
|------|-----------|
| Contrasena < 8 chars | Error de validacion |
| Contrasena sin confirmacion | Error de validacion |
| Contrasena != confirmacion | Error: contrasenas no coinciden |
| Campos vacios + guardar | Sin cambio (OK) |

---

## 6. Flujo 4: Documentos del Expediente

**Ubicacion:** Tab "Documentos del Expediente" en edicion de proveedor
**Relation Manager:** `DocumentsRelationManager`

### 6.1 Ver documentos

1. Abrir edicion de proveedor
2. **Verificar:** Tab "Documentos del Expediente" es el PRIMER tab
3. Clic en el tab
4. **Verificar columnas:**
   - Tipo de Documento (searchable, sortable)
   - Estado (badge con colores)
   - Archivo (nombre o placeholder)
   - Vencimiento (fecha formateada)
   - Cargado (fecha de upload)

**Colores de badge de estado:**
| Estado | Color |
|--------|-------|
| Pendiente | Gris |
| En Revision | Azul (info) |
| Aprobado | Verde (success) |
| Rechazado | Rojo (danger) |

### 6.2 Cambiar Estado de Documento

**Accion:** "Cambiar Estado" (por registro)

**Pasos:**
1. Encontrar documento en la tabla
2. Clic en "Cambiar Estado"
3. **Verificar modal con formulario:**
   - Estado del Documento (select con todos los estados)
   - Notas (textarea, opcional)
4. Seleccionar nuevo estado
5. Opcionalmente agregar notas (ej: razon de rechazo)
6. Confirmar
7. **Verificar:** `document_state_id` actualizado
8. **Verificar:** `notas` guardadas (si se ingresaron)
9. **Verificar:** `reviewed_at` = timestamp actual
10. **Verificar:** `reviewed_by` = ID del admin actual
11. **Verificar:** Badge cambia de color en la tabla
12. **Verificar:** Notificacion de exito

**Caso: Rechazar documento**
1. Cambiar estado a "Rechazado"
2. Agregar nota: "Documento ilegible, favor de resubir"
3. Confirmar
4. **Verificar:** Proveedor ve el rechazo en su dashboard con la razon
5. **Verificar:** Proveedor puede re-subir el documento

```php
$this->seed(DocumentStateSeeder::class);

$supplier = Supplier::factory()->active()->create();
$doc = SupplierDocument::factory()->uploaded()->create([
    'supplier_id' => $supplier->id,
    'document_state_id' => 1,
]);

livewire(DocumentsRelationManager::class, [
    'ownerRecord' => $supplier,
])
    ->callTableAction('changeState', $doc, [
        'document_state_id' => 4, // Rechazado
        'notas' => 'Documento ilegible',
    ])
    ->assertNotified();

$this->assertDatabaseHas('supplier_documents', [
    'id' => $doc->id,
    'document_state_id' => 4,
    'notas' => 'Documento ilegible',
]);
```

### 6.3 Flujo completo de revision de documentos

```
1. Admin asigna documentos al proveedor  (estado: Pendiente)
2. Proveedor sube archivo                (estado: Pendiente, con archivo)
3. Admin cambia a "En Revision"          (estado: En Revision)
4a. Admin aprueba -> "Aprobado"          (estado: Aprobado, verde)
    -> Proveedor ve documento aprobado, no puede modificar
4b. Admin rechaza -> "Rechazado"         (estado: Rechazado, rojo)
    -> Proveedor ve razon de rechazo
    -> Proveedor re-sube archivo
    -> Vuelve a paso 3
```

---

## 7. Flujo 5: Sucursales del Proveedor

**Ubicacion:** Tab "Sucursales" en edicion de proveedor
**Relation Manager:** `BranchesRelationManager`

### 7.1 Ver sucursales asignadas

1. Abrir edicion de proveedor
2. Clic en tab "Sucursales" (SEGUNDO tab)
3. **Verificar columnas:**
   - Nombre (searchable, sortable)
   - Asignada (fecha del pivot `assigned_at`)

### 7.2 Agregar sucursal

1. Clic en "Agregar Sucursal" (header action)
2. **Verificar:** Modal con select de sucursales disponibles
3. Seleccionar sucursal
4. Confirmar
5. **Verificar:** Sucursal aparece en la tabla
6. **Verificar:** `assigned_at` tiene fecha actual
7. **Verificar:** Proveedor ve la sucursal en su dashboard

### 7.3 Desasociar sucursal

1. Clic en "Desasociar" en una sucursal
2. **Verificar:** Confirmacion
3. Confirmar
4. **Verificar:** Sucursal eliminada de la tabla (pivot eliminado)
5. **Verificar:** Sucursal sigue existiendo como entidad (solo se quito la relacion)
6. **Verificar:** Proveedor ya no ve la sucursal en su dashboard

---

## 8. Flujo 6: Gestion de Sucursales

**Ruta:** `/admin/branches`
**Resource:** `BranchResource`
**Grupo de navegacion:** Gestion de Proveedores (sort: 2)

### 8.1 Listado

1. Navegar a `/admin/branches`
2. **Verificar columnas:** Nombre, Fecha de creacion
3. **Verificar:** Busqueda por nombre funcional
4. **Verificar:** Ordenamiento por nombre y fecha

### 8.2 Crear sucursal

1. Clic en "Nuevo"
2. **Verificar formulario (Seccion "Informacion de la Sucursal"):**
   - Nombre (requerido, max 255, unico)
3. Guardar
4. **Verificar:** Sucursal creada en BD

**Validacion:**
- Nombre vacio -> error required
- Nombre duplicado -> error unique

```php
livewire(CreateBranch::class)
    ->fillForm(['name' => 'Sucursal Centro'])
    ->call('create')
    ->assertNotified();

$this->assertDatabaseHas('branches', ['name' => 'Sucursal Centro']);
```

### 8.3 Editar / Eliminar / Restaurar

- Mismos patrones que proveedores (soft deletes, trashed filter, force delete)

---

## 9. Flujo 7: Tipos de Proveedor

**Ruta:** `/admin/provider-types`
**Resource:** `ProviderTypeResource`
**Grupo de navegacion:** Gestion de Proveedores (sort: 3)

### 9.1 Listado

1. **Verificar columnas:** Nombre, Descripcion (truncada 50 chars), Activo (icono), Fecha
2. **Verificar filtros:** Activo (Ternary), Trashed

### 9.2 Crear tipo de proveedor

1. **Verificar formulario:**
   - **Seccion "Informacion del Tipo de Proveedor":**
     - Nombre (requerido, max 255, unico)
     - Descripcion (textarea, opcional)
   - **Seccion "Configuracion":**
     - Activo (toggle, default: true)

### 9.3 Relation Manager: Tipos de Documento Requeridos

**Ubicacion:** Tab en edicion de tipo de proveedor
**Titulo:** "Tipos de Documento Requeridos"

**Pasos para configurar documentos requeridos:**
1. Editar tipo de proveedor
2. Ir al tab de documentos requeridos
3. Clic "Adjuntar" (Attach)
4. **Verificar modal:**
   - Select de tipo de documento (searchable)
   - Toggle "Obligatorio" (default: true)
5. Seleccionar tipo de documento
6. Confirmar
7. **Verificar:** Tipo de documento aparece en la tabla con flag "Obligatorio"
8. **Verificar:** Al asignar documentos a un proveedor de este tipo, se crean los documentos correspondientes

**Columnas de la tabla:**
- Nombre del tipo de documento
- Descripcion (truncada)
- Validez (dias o "Sin expiracion")
- Obligatorio (icono boolean)

> **IMPORTANTE:** Esta configuracion determina que documentos se crean cuando el admin usa "Asignar Documentos" en un proveedor.

---

## 10. Flujo 8: Tipos de Documentos

**Ruta:** `/admin/document-types`
**Resource:** `DocumentTypeResource`
**Grupo de navegacion:** Configuracion del Sistema (sort: 4)

### 10.1 Crear tipo de documento

1. **Verificar formulario:**
   - **Seccion "Informacion del Tipo de Documento":**
     - Nombre (requerido, max 255, unico)
     - Descripcion (textarea, opcional)
     - Validez en dias (numerico, min 1, helper explica proposito)
   - **Seccion "Configuracion":**
     - Activo (toggle, default: true, helper sobre comportamiento inactivo)

2. Crear con validez de 365 dias
3. **Verificar:** En listado muestra "365 dias"
4. Crear sin validez
5. **Verificar:** En listado muestra "Sin expiracion"

```php
livewire(CreateDocumentType::class)
    ->fillForm([
        'nombre' => 'Constancia Fiscal',
        'descripcion' => 'Constancia de situacion fiscal del SAT',
        'validez_dias' => 365,
        'activo' => true,
    ])
    ->call('create')
    ->assertNotified();
```

---

## 11. Flujo 9: Estados de Documento

**Ruta:** `/admin/document-states`
**Resource:** `DocumentStateResource`
**Grupo de navegacion:** Configuracion del Sistema (sort: 5)

> **IMPORTANTE:** Este recurso es SOLO LECTURA. No se pueden crear, editar ni eliminar estados.

### 11.1 Ver estados

1. Navegar a `/admin/document-states`
2. **Verificar columnas:**
   - Nombre (searchable, sortable)
   - Etiqueta (formato badge)
   - Por Defecto (icono boolean)
   - Completado (icono boolean)
   - Transiciones Permitidas (grafo visual)

3. **Verificar estados existentes (seeder):**

| Nombre | Etiqueta | Color | Por Defecto | Completado |
|--------|----------|-------|-------------|-----------|
| Pendiente | Pendiente | gray | Si | No |
| En Revision | En Revision | blue | No | No |
| Aprobado | Aprobado | green | No | Si |
| Rechazado | Rechazado | red | No | No |

4. **Verificar:** No hay boton de "Nuevo"
5. **Verificar:** No hay acciones de editar/eliminar en cada registro

### 11.2 Transiciones permitidas

```
Pendiente -----> En Revision
En Revision ---> Aprobado
En Revision ---> Rechazado
Rechazado -----> Pendiente (cuando proveedor re-sube)
```

---

## 12. Flujo 10: Gestion de Usuarios

**Ruta:** `/admin/users`
**Resource:** `UserResource`
**Grupo de navegacion:** Administracion (sort: 6)

### 12.1 Control de acceso

1. **Verificar:** Solo usuarios con permiso `ViewAny:User` pueden acceder
2. Sin permiso -> no aparece en sidebar y acceso directo da 403

### 12.2 Crear usuario

1. **Verificar formulario:**
   - **Seccion "Informacion del Usuario":**
     - Nombre (requerido, max 255)
     - Email (requerido, unico, helper sobre notificaciones)
     - Contrasena (requerido en creacion, min 8)
   - **Seccion "Configuracion":**
     - Activo (toggle, default: true)

### 12.3 Editar usuario

1. **Verificar:** Contrasena es opcional en edicion (helper: "dejar vacio para no cambiar")
2. **Verificar:** Email unico excluyendo el registro actual

### 12.4 Listado

1. **Verificar columnas:**
   - Nombre (searchable, sortable)
   - Email (searchable, sortable, copyable)
   - Activo (icono boolean, sortable)
   - Email Verificado (icono boolean, sortable)
   - Fecha de creacion

### 12.5 Soft deletes

- Mismos patrones: eliminar, restaurar, forzar eliminacion, filtro trashed

---

## 13. Flujo 11: Roles y Permisos (Shield)

**Ruta:** `/admin/shield/roles`
**Plugin:** Filament Shield (Spatie Permissions)
**Grupo de navegacion:** Administracion (sort: 7)
**Label:** "Roles" (singular: "Rol")

### 13.1 Verificar labels en espanol

1. **Verificar:** Sidebar muestra "Roles" bajo grupo "Administracion"
2. **Verificar:** Titulo de pagina usa "Rol" / "Roles"

### 13.2 Ver roles existentes

1. Navegar a `/admin/shield/roles`
2. **Verificar:** Roles por defecto:
   - `super_admin` - Acceso total
   - `panel_user` - Acceso basico al panel

### 13.3 Crear rol

1. Clic en "Nuevo"
2. Ingresar nombre del rol
3. **Verificar:** Se muestran tabs de permisos:
   - Resources (CRUD por recurso)
   - Pages (view por pagina)
   - Widgets (view por widget)
4. Marcar permisos deseados
5. Guardar
6. **Verificar:** Rol creado con permisos asignados

### 13.4 Asignar rol a usuario

1. Usar Spatie's HasRoles para asignar (via tinker o seeder)
2. **Verificar:** Usuario hereda permisos del rol
3. **Verificar:** Recursos no autorizados no aparecen en sidebar

---

## 14. Conexion con el Portal de Proveedores

### Mapa de acciones Admin -> Efecto en Proveedor

| # | Accion Admin | Ubicacion | Efecto Proveedor |
|---|-------------|-----------|-----------------|
| 1 | Crear proveedor | `/admin/suppliers/create` | Registro creado (status: created), proveedor no puede hacer nada |
| 2 | Reenviar invitacion | Edicion > Header > "Reenviar Invitacion" | Proveedor recibe email con link de set-password, status: invited |
| 3 | Asignar documentos | Edicion > Header > "Asignar Documentos" | Documentos aparecen en dashboard del proveedor como "Pendiente" |
| 4 | Cambiar estado doc a "En Revision" | Edicion > Tab Documentos > "Cambiar Estado" | Proveedor ve badge azul, no puede modificar archivo |
| 5 | Cambiar estado doc a "Aprobado" | Edicion > Tab Documentos > "Cambiar Estado" | Proveedor ve badge verde, no puede modificar |
| 6 | Cambiar estado doc a "Rechazado" + notas | Edicion > Tab Documentos > "Cambiar Estado" | Proveedor ve badge rojo + razon, puede re-subir archivo |
| 7 | Cambiar contrasena | Edicion > Seccion "Cambiar Contrasena" | Contrasena anterior invalida inmediatamente |
| 8 | Agregar sucursal | Edicion > Tab Sucursales > "Agregar Sucursal" | Sucursal aparece en dashboard del proveedor |
| 9 | Desasociar sucursal | Edicion > Tab Sucursales > "Desasociar" | Sucursal desaparece del dashboard del proveedor |
| 10 | Soft delete proveedor | Edicion > "Borrar" | Proveedor no puede hacer login |
| 11 | Restaurar proveedor | Listado (filtro trashed) > "Restaurar" | Proveedor puede volver a hacer login |
| 12 | Editar tipo proveedor + doc types | Tipos de Proveedor > Edicion | Afecta que documentos se asignan a futuros proveedores de ese tipo |

### Flujo completo de testing integrado (Admin + Proveedor)

```
PASO 1 - CONFIGURACION INICIAL (Admin)
  a. Crear tipos de documento (ej: "RFC", "INE", "Comprobante domicilio")
  b. Crear tipo de proveedor (ej: "Persona Fisica")
  c. Asociar tipos de documento al tipo de proveedor
  d. Crear sucursales (ej: "Sucursal Centro", "Sucursal Norte")

PASO 2 - CREAR PROVEEDOR (Admin)
  a. Crear proveedor con nombre, email, tipo de proveedor
  b. Verificar status = created
  c. Clic "Reenviar Invitacion"
  d. Verificar status = invited
  e. Verificar SupplierInvitation creada con token

PASO 3 - SET PASSWORD (Proveedor)
  a. Navegar a /supplier/set-password?token={token}
  b. Establecer contrasena
  c. Verificar status = registered
  d. Verificar auto-login y redireccion a onboarding

PASO 4 - ONBOARDING (Proveedor)
  a. Completar Paso 1: Direccion
  b. Completar Paso 2: CLABE
  c. Confirmar Paso 3
  d. Verificar status = profile_completed
  e. Verificar redireccion a dashboard

PASO 5 - ASIGNAR DOCUMENTOS (Admin)
  a. Editar proveedor
  b. Clic "Asignar Documentos"
  c. Verificar documentos creados en tab "Documentos del Expediente"

PASO 6 - SUBIR DOCUMENTOS (Proveedor)
  a. Loguearse y acceder a dashboard
  b. Ver documentos pendientes
  c. Subir archivos para cada documento
  d. Verificar badges "Pendiente" con archivo

PASO 7 - REVISAR DOCUMENTOS (Admin)
  a. Editar proveedor > Tab Documentos
  b. Cambiar estado a "En Revision"
  c. Revisar archivo
  d. Aprobar o rechazar con notas

PASO 8 - RE-SUBIR SI RECHAZADO (Proveedor)
  a. Ver documento rechazado con razon en dashboard
  b. Re-subir archivo corregido
  c. Verificar estado vuelve a "Pendiente"

PASO 9 - ASIGNAR SUCURSALES (Admin)
  a. Editar proveedor > Tab Sucursales
  b. Agregar sucursales
  c. Verificar que proveedor ve sucursales en dashboard

PASO 10 - ACTIVAR PROVEEDOR (Admin)
  a. Cuando todos los documentos estan aprobados
  b. Cambiar status del proveedor (via BD o accion futura)
  c. Verificar proveedor tiene acceso completo
```

---

## 15. Factories y Datos de Prueba

### UserFactory

```php
User::factory()->create();                   // Usuario admin basico
User::factory()->unverified()->create();     // Sin email verificado
User::factory()->withTwoFactor()->create();  // Con 2FA habilitado
User::factory()->inactive()->create();       // Inactivo
```

### SupplierFactory

```php
Supplier::factory()->create();                     // status: created
Supplier::factory()->invited()->create();          // status: invited
Supplier::factory()->registered()->create();       // status: registered
Supplier::factory()->profileCompleted()->create(); // status: profile_completed
Supplier::factory()->active()->create();           // status: active
```

### Otras factories

```php
Branch::factory()->create(['name' => 'Sucursal Centro']);
DocumentType::factory()->create(['nombre' => 'RFC', 'validez_dias' => 365]);
DocumentType::factory()->inactive()->create();
ProviderType::factory()->create(['nombre' => 'Persona Fisica']);
ProviderType::factory()->inactive()->create();
SupplierDocument::factory()->create();
SupplierDocument::factory()->uploaded()->create();
SupplierInvitation::factory()->create();
SupplierInvitation::factory()->expired()->create();
```

---

## 16. Checklist de Regresion

### Login Admin
- [ ] Heading: "Acceso de Administradores"
- [ ] Subheading: "Ingresa con tu correo y contrasena"
- [ ] Link "Olvidaste tu contrasena?" funcional
- [ ] Link "Volver al inicio" con UNA sola flecha
- [ ] Login exitoso redirige a dashboard
- [ ] Login fallido muestra error
- [ ] Password reset funcional

### Navegacion
- [ ] 3 grupos de navegacion visibles y en orden
- [ ] Labels en espanol: Proveedores, Sucursales, Tipos de Proveedor, etc.
- [ ] Roles bajo grupo "Administracion" con label "Roles"

### Proveedores CRUD
- [ ] Listado: columnas, busqueda, filtros, badges de estado
- [ ] Crear: formulario basico sin seccion Estado ni Contrasena
- [ ] Editar: layout 2 columnas (Info Basica | Estado + Contrasena)
- [ ] Editar: Contrasena colapsada por defecto
- [ ] Editar: Tabs en orden (Documentos primero, Sucursales segundo)
- [ ] Eliminar: soft delete
- [ ] Restaurar: funcional
- [ ] Forzar eliminacion: funcional

### Acciones de Proveedor
- [ ] "Asignar Documentos" color primary (purpura)
- [ ] "Reenviar Invitacion" color primary (purpura)
- [ ] Asignar documentos: crea documentos correctamente
- [ ] Asignar documentos: idempotente (no duplica)
- [ ] Reenviar invitacion: crea token y cambia status
- [ ] Reenviar invitacion: error si proveedor activo
- [ ] Cambiar contrasena: actualiza hash en BD
- [ ] Cambiar contrasena: campo vacio no modifica
- [ ] Cambiar contrasena: validacion min 8 chars + confirmacion

### Documentos del Expediente
- [ ] Tab es el PRIMERO en edicion de proveedor
- [ ] Columnas correctas con badges de color
- [ ] Cambiar estado funcional con modal
- [ ] Notas guardadas al cambiar estado
- [ ] reviewed_at y reviewed_by actualizados
- [ ] Rechazo con notas visible para proveedor

### Sucursales
- [ ] Tab "Sucursales" (traducido, no "Branches")
- [ ] Agregar sucursal funcional
- [ ] Desasociar funcional
- [ ] Pivot assigned_at correcto

### Sucursales Resource
- [ ] CRUD basico funcional
- [ ] Nombre unico validado
- [ ] Soft deletes funcional

### Tipos de Proveedor
- [ ] CRUD con toggle activo
- [ ] Relation manager: tipos de documento requeridos
- [ ] Attach con toggle "Obligatorio"
- [ ] Detach funcional

### Tipos de Documentos
- [ ] CRUD con validez en dias
- [ ] "Sin expiracion" cuando validez es null
- [ ] Toggle activo funcional

### Estados de Documento
- [ ] Solo lectura (sin crear/editar/eliminar)
- [ ] 4 estados visibles con datos correctos
- [ ] Transiciones permitidas visibles

### Usuarios
- [ ] Acceso solo con permiso ViewAny:User
- [ ] CRUD completo
- [ ] Contrasena obligatoria en creacion, opcional en edicion
- [ ] Toggle activo funcional

### Roles (Shield)
- [ ] Label "Roles" en sidebar bajo "Administracion"
- [ ] Labels en espanol (Rol/Roles)
- [ ] CRUD de roles funcional
- [ ] Permisos por recurso/pagina/widget
