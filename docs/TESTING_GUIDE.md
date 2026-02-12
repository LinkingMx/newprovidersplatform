# Guía Completa del Sistema - NewProvidersPlatform

**Documento de referencia para ingenieros de testing**
**Versión**: 1.0
**Fecha**: Febrero 2026

---

## 📋 Tabla de Contenidos

1. [Visión General](#visión-general)
2. [Arquitectura del Sistema](#arquitectura-del-sistema)
3. [Panel Administrativo (Filament)](#panel-administrativo-filament)
4. [Frontend (React + Inertia)](#frontend-react--inertia)
5. [Flujos de Usuario](#flujos-de-usuario)
6. [Autenticación y Autorización](#autenticación-y-autorización)
7. [Modelos de Datos](#modelos-de-datos)
8. [Validaciones](#validaciones)
9. [Cómo Ejecutar Pruebas](#cómo-ejecutar-pruebas)
10. [Casos de Prueba Críticos](#casos-de-prueba-críticos)
11. [Acceso y Credenciales de Prueba](#acceso-y-credenciales-de-prueba)

---

## 📱 Visión General

**NewProvidersPlatform** es un portal web para onboarding y gestión de proveedores. El sistema tiene dos interfaces principales:

| Interfaz | Propósito | Usuario | URL |
|----------|-----------|---------|-----|
| **Panel Administrativo** | CRUD de proveedores, configuración, monitoreo | Admins | `/admin` |
| **Portal de Proveedores** | Self-service onboarding (contraseña, datos, info bancaria) | Proveedores | `/supplier/*` |

**Stack Tecnológico:**
- Backend: Laravel 12 + Eloquent ORM
- Admin UI: Filament v5 (Server-Driven UI)
- Frontend Web: React 19 + Inertia.js v2 + Tailwind CSS v4
- Testing: Pest v4 (PHPUnit alternative)
- Base de Datos: SQLite (desarrollo), PostgreSQL (producción)

---

## 🏗️ Arquitectura del Sistema

### Flujo de Solicitud HTTP

```
Usuario → Navegador → (HTTPS) → Servidor Laravel → Controlador
                                        ↓
                    ├─ Filament Admin (Server-rendered)
                    │  └─ Response: Livewire + Blade
                    │
                    └─ Frontend (SPA Client-rendered)
                       └─ Response: Inertia.js → React
```

### Estructura de Directorios (Relevante para Testing)

```
newprovidersplatform/
├── app/
│   ├── Models/                           # Eloquent Models
│   │   ├── Supplier.php
│   │   ├── SupplierInvitation.php
│   │   ├── Branch.php
│   │   └── DocumentType.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Supplier/              # Supplier portal controllers
│   │   │   │   ├── SetPasswordController.php
│   │   │   │   └── OnboardingController.php
│   │   │   └── [otros controllers]
│   │   ├── Middleware/
│   │   │   ├── RedirectIfAuthenticated.php
│   │   │   └── HandleAppearance.php
│   │   └── Requests/                  # Form validation
│   ├── Filament/
│   │   └── Resources/                 # Admin panel resources
│   │       ├── SupplierResource/
│   │       ├── BranchResource/
│   │       └── DocumentTypeResource/
│   ├── Jobs/
│   │   └── SupplierVerificationJob.php
│   └── Providers/
│       └── Filament/AdminPanelProvider.php
├── routes/
│   ├── web.php                        # Web routes (público + supplier)
│   └── settings.php                   # Admin routes (auto-descubiertos)
├── database/
│   ├── migrations/                    # Schema changes
│   ├── factories/                     # Model factories
│   │   ├── SupplierFactory.php
│   │   └── SupplierInvitationFactory.php
│   └── seeders/
├── resources/
│   └── js/
│       ├── pages/                     # React pages
│       │   ├── welcome.tsx
│       │   ├── dashboard.tsx
│       │   ├── Supplier/
│       │   │   ├── SetPassword.tsx
│       │   │   ├── Onboarding.tsx
│       │   │   └── Dashboard.tsx
│       │   └── [otros]
│       ├── components/                # Componentes reutilizables
│       └── layouts/                   # Layouts (AppLayout, AuthLayout, etc)
├── tests/
│   ├── Feature/
│   │   ├── Supplier/
│   │   │   ├── SetPasswordControllerTest.php  # 12 tests
│   │   │   └── OnboardingControllerTest.php   # 16 tests
│   │   └── Browser/
│   │       └── SupplierRegistrationFlowTest.php # 27 tests
│   └── Unit/
└── public/
    └── build/                         # Compiled assets (después de npm run build)
```

---

## 🎛️ Panel Administrativo (Filament)

### Acceso y Autenticación

**URL**: `https://newprovidersplatform.test/admin`
**Autenticación**: Fortify + Laravel auth (guard: `web`)
**Autorización**: Filament Shield (RBAC - Role-Based Access Control)

### Resources Disponibles

#### 1. **Supplier Resource** (`/admin/suppliers`)

**Operaciones CRUD:**
- **CREATE** (Crear proveedor): Admin ingresa nombre + email
- **READ** (Ver): Lista de proveedores con filtros y búsqueda
- **UPDATE** (Editar): Modificar datos + asignar sucursales (BranchesRelationManager)
- **DELETE** (Borrar): Soft delete (recuperable vía restore)

**Campos del Formulario:**

| Campo | Tipo | Validación | Notas |
|-------|------|-----------|-------|
| name | String | required, unique | Nombre del proveedor |
| email | Email | required, unique | Para invitación |
| status | Select | read-only | Automático: created → invited → registered → profile_completed → active |
| address_* | Text | varies | Poblados en onboarding |
| clabe_interbancaria | String | 18 dígitos | CLABE bancaria |

**Secciones del Formulario:**
1. **Información Básica** (nombre, email)
2. **Estado y Contacto** (status, creado el, etc)
3. **Información del Proveedor** (dirección, CLABE)
4. **Sucursales** (relation manager - agregar/remover)

**Acciones Disponibles:**
- ✏️ Edit (ir a formulario edit)
- 🗑️ Delete (soft delete)
- 🔄 Restore (from trash)
- ⚠️ Force Delete (permanente)

**Tabla con Columnas:**
- name (searchable, sortable)
- email (searchable)
- status (filtrable: badge color)
- created_at (sortable, formatted)
- acciones

**Filtros:**
- Por status
- Mostrando trashed (soft deleted)

---

#### 2. **Branch Resource** (`/admin/branches`)

**Operaciones CRUD:** CREATE, READ, UPDATE, DELETE

**Campo único:**
- name: String, required, unique (nombre de sucursal)

**Uso:**
- Sucursales que pueden ser asignadas a proveedores
- Visible en Supplier edit → BranchesRelationManager

---

#### 3. **DocumentType Resource** (`/admin/document-types`)

**Operaciones CRUD:** CREATE, READ, UPDATE, DELETE

**Campos:**
- nombre: String, required, unique
- descripción: Text, nullable
- validez_dias: Integer, nullable (días que es válido el documento)
- activo: Boolean, default true

**Uso:**
- Tipos de documentos que proveedores deben entregar
- Configuración general del sistema

---

#### 4. **DocumentState Resource** (`/admin/document-states`)

**Operaciones:** READ-ONLY (sin crear, editar, borrar)

**Estados posibles:**
1. default (pendiente)
2. submitted (entregado)
3. rejected (rechazado)
4. completed (completado/aprobado)

**Propósito:**
- Educacional para admins
- Mostrar máquina de estados de documentos
- No editable (configuración interna)

---

### Características Filament Comunes

**Notificaciones:**
- Success: ✅ Con icono y mensaje (primero color: ámbar)
- Error: ❌ Con detalles de validación
- Info: ℹ️ Para acciones completadas

**Búsqueda y Filtros:**
- Búsqueda por nombre/email (en tiempo real)
- Filtros por estado, fecha, etc
- Borrar filters → mostrar todos

**Acciones Bulk:**
- Seleccionar múltiples registros
- Acciones masivas (delete, restore, etc)

**Paginación:**
- Default: 10 registros por página
- Editable desde botón "Per Page"

---

## 🌐 Frontend (React + Inertia)

### Estructura de Rutas del Servidor (Laravel)

**Rutas Públicas:**

```
GET  /                                  → welcome page
```

**Rutas de Autenticación (Fortify):**

```
GET  /login                            → login page (Blade/Fortify)
POST /login                            → submit login
GET  /register                         → register page (Fortify)
POST /register                         → submit register
```

**Rutas del Portal Supplier:**

```
GET  /supplier/set-password?token=xxx  → SetPassword.tsx (formulario)
POST /supplier/auth/set-password       → procesar password
GET  /supplier/onboarding              → Onboarding.tsx (formulario)
POST /supplier/onboarding/submit       → procesar onboarding
GET  /supplier/dashboard               → Supplier/Dashboard.tsx
POST /supplier/auth/logout             → cerrar sesión
```

### Página: SetPassword

**URL**: `/supplier/set-password?token=<invitation_token>`

**Flujo:**
```
Supplier recibe email → Click link con token
       ↓
Navega a /supplier/set-password?token=xxx
       ↓
SetPasswordController.show() valida:
  - Token exists en SupplierInvitation
  - Token no expirado
  - Supplier no active
       ↓
Si OK: Renderiza React component SetPassword.tsx
Si Error: Muestra mensaje error (Token inválido/expirado)
       ↓
Usuario completa formulario:
  - Nueva Contraseña (10+ chars, mayús, número, símbolo)
  - Confirmar Contraseña
  - Submit
       ↓
POST /supplier/auth/set-password
  ├─ Valida contraseña
  ├─ Hash contraseña → password_hash
  ├─ Supplier status = 'registered'
  ├─ Marca invitation.accepted_at = now()
  ├─ Login automático (Auth::guard('supplier')->login())
  └─ Redirect a /supplier/onboarding
```

**Validaciones (lado servidor):**
- password: min:10, confirmed, regex:/[A-Z]/, regex:/[0-9]/, regex:/[!@#$%^&*]/
- Mensaje error: "La contraseña debe contener mayúsculas, números y símbolos especiales"

**Estados del Componente:**
- Loading: Deshabilitado submit mientras procesa
- Error: Muestra errores de validación bajo cada campo
- Success: Redirige automáticamente

---

### Página: Onboarding

**URL**: `/supplier/onboarding`

**Requiere:** Autenticación supplier + status = 'registered'

**Flujo:**
```
Usuario autenticado accede a /supplier/onboarding
       ↓
OnboardingController.show() verifica:
  - Usuario está autenticado (auth:supplier)
  - Si status = 'active' → redirect a /supplier/dashboard
  - Si status != 'registered' y != 'profile_completed' → puede ver el form
       ↓
Renderiza Onboarding.tsx con datos pre-llenados
       ↓
Usuario completa formulario (6 campos):
  1. Calle (address_street): required, max:255
  2. Número (address_number): required, max:50
  3. Barrio (address_neighborhood): required, max:255
  4. Ciudad (address_city): required, max:255
  5. País (address_country): required, in:Mexico,USA,Canada
  6. Código Postal (address_zip): required, regex:/^\d{5}$/
  7. CLABE Interbancaria: required, regex:/^\d{18}$/, unique:suppliers
  8. Confirmación (checkbox): required, accepted
       ↓
POST /supplier/onboarding/submit
  ├─ Valida todos los campos
  ├─ Valida CLABE no duplicada
  ├─ Si hay errores → vuelve a mostrar form con errores
  ├─ Si OK:
  │  ├─ Actualiza supplier con todos los datos
  │  ├─ status = 'profile_completed'
  │  ├─ Dispatch SupplierVerificationJob (cola)
  │  └─ Redirect a /supplier/dashboard con mensaje
```

**Validaciones (lado servidor):**

```php
'address_street' => 'required|string|max:255',
'address_number' => 'required|string|max:50',
'address_neighborhood' => 'required|string|max:255',
'address_city' => 'required|string|max:255',
'address_country' => 'required|string|in:Mexico,USA,Canada',
'address_zip' => 'required|string|regex:/^\d{5}$/',
'clabe_interbancaria' => 'required|string|regex:/^\d{18}$/|unique:suppliers,clabe_interbancaria',
'confirm' => 'required|accepted',
```

**SupplierVerificationJob:**
- Se ejecuta asincrónico (queue)
- Si CLABE valida → actualiza supplier status = 'active'
- Lugar de implementación futura: validación con banco real

---

### Página: Supplier Dashboard

**URL**: `/supplier/dashboard`

**Requiere:** Autenticación supplier

**Muestra:**
- Nombre del proveedor
- Email
- Estado actual (registered / profile_completed / active)
- Datos de perfil (si están completos)
- Botón logout

**Comportamiento:**
- Si status = 'registered' → puede acceder pero falta completar onboarding
- Si status = 'profile_completed' → espera verificación
- Si status = 'active' → completado

---

## 👥 Flujos de Usuario

### Flujo 1: Creación y Onboarding de Proveedor (Completo)

```
ADMIN PORTAL:
1. Admin: GET /admin/suppliers
2. Admin: Click "Crear"
3. Admin: Completa formulario:
   - Nombre: "Acme Supply Co"
   - Email: "contact@acme.example.com"
4. Admin: Click "Guardar"
   └─ Sistema: Crea Supplier (status='created')
   └─ Sistema: Crea SupplierInvitation con token
   └─ Sistema: (Futuro) Envía email con link

SUPPLIER PORTAL:
5. Supplier: Recibe email con link:
   https://newprovidersplatform.test/supplier/set-password?token=abc123xyz
6. Supplier: Click link → GET /supplier/set-password?token=abc123xyz
   └─ Sistema: Valida token, muestra formulario
7. Supplier: Completa contraseña:
   - Nueva Contraseña: "SecurePass123!"
   - Confirmar: "SecurePass123!"
   - Click: "Establecer Contraseña"
8. Supplier: POST /supplier/auth/set-password
   └─ Sistema: Valida, hash, login automático
   └─ Sistema: Redirect a /supplier/onboarding
9. Supplier: GET /supplier/onboarding
   └─ Sistema: Muestra formulario con 6 campos
10. Supplier: Completa datos:
    - Calle: "Calle Principal 123"
    - Número: "123"
    - Barrio: "Centro"
    - Ciudad: "Mexico City"
    - País: "Mexico"
    - Código Postal: "06500"
    - CLABE: "002011111111111111"
    - Confirmar: ☑
    - Click: "Completar Perfil"
11. Supplier: POST /supplier/onboarding/submit
    └─ Sistema: Valida, actualiza, dispatch job
    └─ Sistema: Redirect a /supplier/dashboard
12. Supplier: GET /supplier/dashboard
    └─ Sistema: Muestra perfil completado
    └─ Mensaje: "Estamos verificando tu información"

BACKEND (Asincrónico):
13. Queue worker procesa SupplierVerificationJob
    └─ Sistema: Valida CLABE
    └─ Sistema: Actualiza status = 'active'

RESULTADO FINAL:
Supplier status: created → invited → registered → profile_completed → active ✅
```

---

### Flujo 2: Admin Gestiona Proveedor

```
1. Admin: GET /admin/suppliers
2. Admin: Ve listado con filtros
   └─ Busca por nombre/email
   └─ Filtra por status
   └─ Ordena por creado/actualizado
3. Admin: Click en proveedor para editar
4. Admin: GET /admin/suppliers/{id}/edit
   └─ Sistema: Carga form con datos actuales
5. Admin: Modifica datos:
   - Nombre, Email, Estado, Dirección, CLABE
   - Sección: Sucursales (Agregar/Remover)
6. Admin: Click "Guardar"
   └─ Sistema: Valida, actualiza DB
   └─ Notificación: ✅ Guardado correctamente
7. Admin: (Opcional) Delete → Soft delete
   └─ Proveedor aún existe pero marcado deleted_at
```

---

## 🔐 Autenticación y Autorización

### Sistema de Guards (Múltiples Autenticaciones)

```
Guard 'web'          Guard 'supplier'
(Admin Users)        (Proveedores)
├── login             ├── token-based (invitations)
├── 2FA               ├── session-based
├── roles/permisos    └── custom supplier routes
└── Filament Shield
```

**Middleware Activo:**
- `RedirectIfAuthenticated`: Si ya está autenticado, redirige
- `Authenticate`: Verifica autenticación requerida
- `auth:supplier`: Guard específico para supplier

### Roles y Permisos (Admin)

**Configurado en:** Filament Shield

**Roles disponibles:**
- Super Admin (acceso total)
- Admin (CRUD de proveedores, ramas, tipos doc)
- Viewer (solo lectura)

**Verificación en recursos Filament:**
```php
public static function canViewAny(): bool {
    return auth()->user()->can('view_supplier');
}
```

### Autenticación Supplier

**Proceso:**
1. Invitación enviada → Token generado en SupplierInvitation
2. Supplier accede con token → SetPasswordController
3. SetPassword validates token → Crea password_hash
4. Auth::guard('supplier')->login($supplier) → Sesión iniciada
5. Supplier autenticado para supplier routes

**Verificación en OnboardingController:**
```php
$supplier = auth('supplier')->user();
if (!$supplier) {
    return redirect()->route('supplier.set-password');
}
```

---

## 📊 Modelos de Datos

### Supplier

**Tabla:** `suppliers`

| Campo | Tipo | Validación | Notas |
|-------|------|-----------|-------|
| id | bigint | primary | |
| name | string | required, unique | Nombre proveedor |
| email | string | required, unique | Email |
| status | string | enum | created/invited/registered/profile_completed/active |
| password_hash | string | nullable | Hash de contraseña |
| address_street | string | nullable | Calle |
| address_number | string | nullable | Número |
| address_neighborhood | string | nullable | Barrio |
| address_city | string | nullable | Ciudad |
| address_country | string | nullable | País |
| address_zip | string | nullable | Código postal |
| clabe_interbancaria | string | nullable, unique | CLABE bancaria |
| created_at | timestamp | auto | |
| updated_at | timestamp | auto | |
| deleted_at | timestamp | nullable | Soft delete |

**Relaciones:**
- `hasMany('SupplierInvitation')`: Invitaciones
- `belongsToMany('Branch')`: Sucursales asignadas

**Estados Posibles:**
```
created
  ↓ (admin crea + invita)
invited
  ↓ (supplier ingresa token + sets password)
registered
  ↓ (supplier completa onboarding)
profile_completed
  ↓ (job verifica datos)
active
```

---

### SupplierInvitation

**Tabla:** `supplier_invitations`

| Campo | Tipo | Notas |
|-------|------|-------|
| id | bigint | |
| supplier_id | bigint | FK suppliers |
| token | string | SHA256 único |
| sent_at | timestamp | Cuándo se envió |
| accepted_at | timestamp | Cuándo aceptó (NULL si no) |
| expires_at | timestamp | Default +7 días |

**Métodos de Modelo:**
- `isExpired()`: Verifica si expires_at < now()
- `isAccepted()`: Verifica si accepted_at != null

---

### Branch

**Tabla:** `branches`

| Campo | Tipo | Notas |
|-------|------|-------|
| id | bigint | |
| name | string | required, unique |
| created_at | timestamp | |
| updated_at | timestamp | |
| deleted_at | timestamp | Soft delete |

**Relación:**
- `belongsToMany('Supplier')`: Proveedores asignados

---

### DocumentType

**Tabla:** `document_types`

| Campo | Tipo | Notas |
|-------|------|-------|
| id | bigint | |
| nombre | string | required, unique |
| descripcion | text | nullable |
| validez_dias | integer | nullable (días que es válido) |
| activo | boolean | default true |
| created_at | timestamp | |
| updated_at | timestamp | |
| deleted_at | timestamp | Soft delete |

---

## ✅ Validaciones

### SetPassword (POST /supplier/auth/set-password)

```
password:
  ✓ required
  ✓ min:10 caracteres
  ✓ confirmed (password_confirmation)
  ✓ regex:/[A-Z]/ (al menos 1 mayúscula)
  ✓ regex:/[0-9]/ (al menos 1 número)
  ✓ regex:/[!@#$%^&*]/ (al menos 1 símbolo especial)
```

**Mensajes personalizados (español):**
```
- "La contraseña es requerida"
- "La contraseña debe tener al menos 10 caracteres"
- "La contraseña debe contener mayúsculas, números y símbolos especiales"
- "Las contraseñas no coinciden"
```

---

### Onboarding (POST /supplier/onboarding/submit)

```
address_street:
  ✓ required
  ✓ string
  ✓ max:255

address_number:
  ✓ required
  ✓ string
  ✓ max:50

address_neighborhood:
  ✓ required
  ✓ string
  ✓ max:255

address_city:
  ✓ required
  ✓ string
  ✓ max:255

address_country:
  ✓ required
  ✓ in:Mexico,USA,Canada

address_zip:
  ✓ required
  ✓ regex:/^\d{5}$/ (exactamente 5 dígitos)

clabe_interbancaria:
  ✓ required
  ✓ regex:/^\d{18}$/ (exactamente 18 dígitos)
  ✓ unique:suppliers,clabe_interbancaria (no duplicada)

confirm:
  ✓ required
  ✓ accepted (checkbox)
```

---

### Admin Supplier Form

**En crear/editar:**
```
name:
  ✓ required
  ✓ unique:suppliers (excepto el actual)
  ✓ max:255

email:
  ✓ required
  ✓ email
  ✓ unique:suppliers (excepto el actual)
```

---

## 🧪 Cómo Ejecutar Pruebas

### Prerequisitos

```bash
# Instalar dependencias
composer install
npm install

# Setup inicial
composer run setup

# Compilar assets
npm run build
```

### Ejecutar Test Suite Completo

```bash
# Todos los tests (202 tests)
php artisan test --compact

# Salida esperada:
# Tests: 202 passed (439 assertions)
# Duration: 4-5s
```

### Ejecutar Tests Específicos

```bash
# Tests de SetPassword Controller (12 tests)
php artisan test --compact --filter=SetPasswordController

# Tests de Onboarding Controller (16 tests)
php artisan test --compact --filter=OnboardingController

# Tests del flujo completo supplier (27 tests)
php artisan test --compact tests/Feature/Browser/SupplierRegistrationFlowTest.php

# Tests de un archivo específico
php artisan test --compact tests/Feature/Supplier/SetPasswordControllerTest.php
```

### Tests Disponibles

**Por Categoría:**

```
Backend Logic:
├── SetPasswordControllerTest.php (12 tests)
│   └─ Token validation, password complexity, auth
├── OnboardingControllerTest.php (16 tests)
│   └─ Form validation, CLABE format, job dispatch
├── SupplierResourceTest.php
│   └─ CRUD operations en admin
├── BranchResourceTest.php
│   └─ Branch management
└── DocumentTypeResourceTest.php
    └─ Document types management

Integration (Flujos Completos):
└── SupplierRegistrationFlowTest.php (27 tests)
    ├─ SetPassword endpoint tests (8)
    ├─ Onboarding endpoint tests (13)
    ├─ Dashboard endpoint tests (2)
    └─ Complete flow tests (4)
```

---

## 🎯 Casos de Prueba Críticos

### 1. Flujo Supplier: SetPassword → Onboarding → Dashboard

**Precondición:** Supplier invitado con token válido

**Pasos:**
1. GET `/supplier/set-password?token=abc123`
   - ✅ Resultado: Página carga con formulario

2. POST `/supplier/auth/set-password` con contraseña débil
   - ✅ Resultado: Error "debe contener mayúsculas..."

3. POST `/supplier/auth/set-password` con contraseña fuerte
   - ✅ Resultado: Redirect a `/supplier/onboarding`
   - ✅ Resultado: Supplier autenticado (sesión iniciada)
   - ✅ Resultado: Status = 'registered'

4. GET `/supplier/onboarding`
   - ✅ Resultado: Página con 6 campos de dirección

5. POST `/supplier/onboarding/submit` con datos incompletos
   - ✅ Resultado: Error "campo requerido"

6. POST `/supplier/onboarding/submit` con CLABE inválido
   - ✅ Resultado: Error "18 dígitos"

7. POST `/supplier/onboarding/submit` con datos válidos
   - ✅ Resultado: Redirect a `/supplier/dashboard`
   - ✅ Resultado: Status = 'profile_completed'
   - ✅ Resultado: SupplierVerificationJob queued

8. GET `/supplier/dashboard`
   - ✅ Resultado: Página accesible
   - ✅ Resultado: Muestra datos completados

---

### 2. Validación de Contraseña

**Precondición:** Supplier con invitación válida

| Contraseña | Válida | Razón |
|-----------|--------|-------|
| weak | ❌ | < 10 chars |
| Weak123 | ❌ | Sin símbolo |
| Weak123! | ✅ | Cumple todo |
| weak123! | ❌ | Sin mayúscula |
| WEAK123! | ❌ | Sin minúscula |

---

### 3. Validación CLABE

**Precondición:** Supplier en onboarding

| CLABE | Válida | Razón |
|------|--------|-------|
| 12345678901234567 | ❌ | 17 dígitos |
| 123456789012345678 | ✅ | 18 dígitos |
| 12345678901234567A | ❌ | Contiene letra |
| 002011111111111111 (ya usado) | ❌ | Duplicado |

---

### 4. Flujo Admin: Crear Proveedor

**Precondición:** Admin autenticado

**Pasos:**
1. GET `/admin/suppliers`
   - ✅ Resultado: Lista visible

2. Botón "Crear"
   - ✅ Resultado: Formulario abierto

3. Submit con email duplicado
   - ✅ Resultado: Error "email ya existe"

4. Submit con datos válidos
   - ✅ Resultado: Proveedor creado (status='created')
   - ✅ Resultado: SupplierInvitation generada

5. GET `/admin/suppliers/{id}/edit`
   - ✅ Resultado: Form pre-llenado

6. Agregar sucursales (BranchesRelationManager)
   - ✅ Resultado: Sucursales asignadas

7. Save
   - ✅ Resultado: ✅ Notificación success

---

### 5. Acceso y Control

**Precondición:** Varios usuarios autenticados

| Escenario | Usuario | Recurso | Resultado |
|-----------|---------|---------|-----------|
| Admin accede admin | Admin | `/admin` | ✅ Acceso |
| Supplier accede admin | Supplier | `/admin` | ❌ Redirect login |
| Sin auth accede supplier | Anon | `/supplier/onboarding` | ❌ Redirect login |
| Supplier created accede onboarding | Supplier (created status) | `/supplier/onboarding` | ✅ Acceso |
| Active supplier redirect from setpass | Supplier (active) | `/supplier/set-password?token=x` | ❌ Redirect dashboard |

---

## 🔑 Acceso y Credenciales de Prueba

### Acceso al Sistema

| Parte | URL | Protocolo |
|------|-----|-----------|
| Principal | `https://newprovidersplatform.test` | HTTPS (Herd) |
| Admin Panel | `https://newprovidersplatform.test/admin` | HTTPS |
| Supplier Portal | `https://newprovidersplatform.test/supplier/*` | HTTPS |

### Usuario Admin de Prueba

Crear en testing manual o usar factories:

```php
// En tinker o seed
$user = User::create([
    'name' => 'Admin User',
    'email' => 'admin@test.example.com',
    'password' => Hash::make('AdminPass123!'),
    'email_verified_at' => now(),
]);

// En tests: usa factories
$admin = User::factory()->create();
$this->actingAs($admin)->get('/admin/suppliers');
```

### Usuario Supplier de Prueba

En tests, usa factories:

```php
// Factory con estados
$supplier = Supplier::factory()->registered()->create();
$supplier = Supplier::factory()->profileCompleted()->create();
$supplier = Supplier::factory()->active()->create();

// En tests
$this->actingAs($supplier, 'supplier')
    ->post('/supplier/onboarding/submit', [...]);
```

---

## 📈 Métricas y Observabilidad

### Logs

**Ubicación:** `storage/logs/laravel.log`

**Eventos registrados:**
- Login/logout (auth)
- Error en validación (validation fails)
- Job dispatch/completion (queue)
- DB errors (exception)

### Monitoreo

**Verificar:**
```bash
# Revisar logs en tiempo real
tail -f storage/logs/laravel.log

# Ver status del queue worker
php artisan queue:work

# Ver jobs en cola
php artisan queue:failed
```

---

## 🚀 Deployment y Variaciones

### Desarrollo (Local)

- **Base de datos:** SQLite (`:memory:` en tests)
- **Assets:** Vite dev server (`npm run dev`)
- **Queue:** Sync driver (síncrono para testing)
- **Mail:** Log driver (files en `storage/logs/mail/`)

### Producción (Futuro)

- **Base de datos:** PostgreSQL
- **Assets:** Pre-compilados (`npm run build`)
- **Queue:** Redis/database driver
- **Mail:** SMTP real
- **Auth:** 2FA, SSL certificates, etc

---

## 📝 Checklist para Testing Manual

### Antes de Empezar
- [ ] Database limpia (`php artisan migrate:fresh`)
- [ ] Assets compilados (`npm run build`)
- [ ] Queue worker corriendo (si necesario)
- [ ] Navegador actualizado (Chrome/Firefox)

### Flujo Supplier
- [ ] Supplier recibe invitación (crear en admin)
- [ ] Click en link con token
- [ ] SetPassword page carga
- [ ] Contraseña rechazada (weak)
- [ ] Contraseña aceptada (strong)
- [ ] Redirect a onboarding
- [ ] Onboarding page carga
- [ ] CLABE rechazado (< 18 dígitos)
- [ ] Onboarding completo
- [ ] Redirect a dashboard
- [ ] Dashboard muestra datos

### Flujo Admin
- [ ] Admin login accesible
- [ ] Lista de suppliers visible
- [ ] Crear supplier nuevo
- [ ] Email duplicado rechazado
- [ ] Editar supplier existente
- [ ] Asignar/remover sucursales
- [ ] Soft delete y restore
- [ ] Filtros funcionan
- [ ] Búsqueda por nombre/email

### Acceso y Permisos
- [ ] Sin auth → redirect login
- [ ] Auth supplier → solo supplier routes
- [ ] Auth admin → solo admin routes
- [ ] Active supplier → redirect from setpassword
- [ ] Supplier sin invitación → error token

---

## 🆘 Troubleshooting

### "Token no válido/expirado"
```
Causa: Token no existe o expired_at < now()
Solución: Crear nueva invitación en admin, o extender expired_at en DB
```

### "Email ya registrado"
```
Causa: Email duplicado en suppliers
Solución: Usar email único o borrar supplier anterior (soft delete)
```

### "CLABE ya registrada"
```
Causa: CLABE duplicada
Solución: Usar CLABE diferente
```

### Assets no actualizados
```
Causa: npm build no ejecutado después de cambios
Solución: npm run build y hard refresh (Ctrl+Shift+R)
```

### Tests fallando
```
Causa: Database state inconsistente
Solución: php artisan test --fresh (ejecuta migrate:fresh antes)
```

---

## 📞 Contacto y Documentación

- **Framework docs:** https://laravel.com/docs/12
- **Filament docs:** https://filamentphp.com/docs
- **Inertia docs:** https://inertiajs.com/
- **Pest docs:** https://pestphp.com/docs

---

**Versión:** 1.0
**Última actualización:** Febrero 2026
**Mantenido por:** Equipo de Desarrollo
