# Guia de Testing - Portal de Proveedores (Supplier)

Manual de pruebas paso a paso para el flujo completo del proveedor.

**URL Base:** `https://newprovidersplatform.test`
**Guard de autenticacion:** `supplier`
**Modelo:** `App\Models\Supplier`

---

## Tabla de Contenidos

1. [Estado del Proveedor (State Machine)](#1-estado-del-proveedor-state-machine)
2. [Pre-requisitos para Testing](#2-pre-requisitos-para-testing)
3. [Flujo 1: Login de Proveedor](#3-flujo-1-login-de-proveedor)
4. [Flujo 2: Establecer Contrasena (Set Password)](#4-flujo-2-establecer-contrasena-set-password)
5. [Flujo 3: Onboarding (Perfil Completo)](#5-flujo-3-onboarding-perfil-completo)
6. [Flujo 4: Dashboard del Proveedor](#6-flujo-4-dashboard-del-proveedor)
7. [Flujo 5: Gestion de Documentos](#7-flujo-5-gestion-de-documentos)
8. [Flujo 6: Recuperar Contrasena](#8-flujo-6-recuperar-contrasena)
9. [Flujo 7: Restablecer Contrasena](#9-flujo-7-restablecer-contrasena)
10. [Conexion con el Panel Admin](#10-conexion-con-el-panel-admin)
11. [Factories y Datos de Prueba](#11-factories-y-datos-de-prueba)
12. [Checklist de Regresion](#12-checklist-de-regresion)

---

## 1. Estado del Proveedor (State Machine)

El proveedor pasa por 5 estados. Cada estado habilita distintas capacidades:

```
created --> invited --> registered --> profile_completed --> active
```

| Estado | Puede hacer Login? | Pagina destino | Acciones disponibles |
|--------|-------------------|----------------|---------------------|
| `created` | No | N/A | Ninguna (espera invitacion) |
| `invited` | No | N/A | Clic en link del email para set-password |
| `registered` | Si | `/supplier/onboarding` | Completar perfil obligatorio |
| `profile_completed` | Si | `/dashboard` | Dashboard con estado "en verificacion" |
| `active` | Si | `/dashboard` | Acceso completo: documentos, sucursales |

---

## 2. Pre-requisitos para Testing

### Datos necesarios (creados desde Admin)

Antes de probar el flujo del proveedor, el admin debe haber:

1. Creado un **Tipo de Proveedor** (ej: "Persona Fisica") con sus tipos de documento
2. Creado un **Proveedor** con nombre, email y tipo de proveedor asignado
3. Enviado la **invitacion** (boton "Reenviar Invitacion" en edicion del proveedor)
4. Opcionalmente, **asignado documentos** al proveedor (boton "Asignar Documentos")
5. Creado al menos una **Sucursal** y asociarla al proveedor

> Ver [TESTING-GUIDE-ADMIN.md](TESTING-GUIDE-ADMIN.md) para los pasos detallados del admin.

### Crear datos via Factory (para tests automatizados)

```php
// Proveedor con invitacion pendiente
$supplier = Supplier::factory()->invited()->create();

// Proveedor registrado (ya tiene password)
$supplier = Supplier::factory()->registered()->create();

// Proveedor activo (acceso completo)
$supplier = Supplier::factory()->active()->create();

// Invitacion con token valido
$invitation = SupplierInvitation::factory()->create([
    'supplier_id' => $supplier->id,
]);

// Invitacion expirada
$invitation = SupplierInvitation::factory()->expired()->create();
```

---

## 3. Flujo 1: Login de Proveedor

**Ruta:** `GET /supplier/login`
**Controlador:** `LoginController@show` / `LoginController@store`
**Pagina React:** `resources/js/pages/Supplier/Login.tsx`

### Pasos de prueba

#### 3.1 Acceder a la pagina de login

1. Navegar a `https://newprovidersplatform.test/supplier/login`
2. **Verificar:** Se muestra el logo de la empresa
3. **Verificar:** Titulo "Acceso de Proveedores"
4. **Verificar:** Subtitulo "Ingresa con tu correo y contrasena"
5. **Verificar:** Campos: Correo Electronico, Contrasena
6. **Verificar:** Boton "Acceder"
7. **Verificar:** Link "Olvidaste tu contrasena?" visible
8. **Verificar:** Link "Volver al inicio" al fondo
9. **Verificar:** NO se muestra texto de "No tienes cuenta?" (fue eliminado)

#### 3.2 Login exitoso - Proveedor activo

1. Ingresar email y contrasena de un proveedor activo
2. Clic en "Acceder"
3. **Verificar:** Redirige a `/dashboard`
4. **Verificar:** Sesion creada con guard `supplier`

```php
// Test automatizado
$supplier = Supplier::factory()->active()->create([
    'password_hash' => Hash::make('TestPass123!'),
]);

$this->post(route('supplier.login.store'), [
    'email' => $supplier->email,
    'password' => 'TestPass123!',
])->assertRedirect('/dashboard');

$this->assertAuthenticatedAs($supplier, 'supplier');
```

#### 3.3 Login exitoso - Proveedor registrado (redirige a onboarding)

1. Ingresar credenciales de proveedor con status `registered`
2. **Verificar:** Redirige a `/supplier/onboarding` (NO a dashboard)

```php
$supplier = Supplier::factory()->registered()->create([
    'password_hash' => Hash::make('TestPass123!'),
]);

$this->post(route('supplier.login.store'), [
    'email' => $supplier->email,
    'password' => 'TestPass123!',
])->assertRedirect(route('supplier.onboarding'));
```

#### 3.4 Login fallido - Credenciales incorrectas

1. Ingresar email valido pero contrasena incorrecta
2. **Verificar:** Mensaje de error: "Las credenciales no coinciden con nuestros registros."
3. **Verificar:** No se crea sesion

#### 3.5 Login fallido - Cuenta no habilitada

1. Intentar login con proveedor en status `created` o `invited`
2. **Verificar:** Mensaje: "Tu cuenta aun no esta habilitada. Revisa tu correo para completar el registro."

#### 3.6 Login con proveedor ya autenticado

1. Estar logueado como proveedor
2. Navegar a `/supplier/login`
3. **Verificar:** Redirige automaticamente a `/dashboard` (middleware `RedirectIfSupplierAuthenticated`)

#### 3.7 Toggle de visibilidad de contrasena

1. Escribir en campo de contrasena
2. Clic en icono de ojo
3. **Verificar:** La contrasena se muestra en texto plano
4. Clic de nuevo
5. **Verificar:** Vuelve a modo oculto

---

## 4. Flujo 2: Establecer Contrasena (Set Password)

**Ruta:** `GET /supplier/set-password?token={token}`
**Controlador:** `SetPasswordController@show` / `SetPasswordController@store`
**Pagina React:** `resources/js/pages/Supplier/SetPassword.tsx`

> Este flujo inicia cuando el admin envia una invitacion y el proveedor recibe un email con el link.

### Pasos de prueba

#### 4.1 Acceso con token valido

1. Navegar a `/supplier/set-password?token={token_valido}`
2. **Verificar:** Pagina carga correctamente
3. **Verificar:** Campos: contrasena, confirmar contrasena
4. **Verificar:** Indicadores de requisitos de contrasena visibles

#### 4.2 Establecer contrasena exitosamente

1. Ingresar contrasena que cumpla todos los requisitos
2. Confirmar contrasena identica
3. Enviar formulario
4. **Verificar:** Status del proveedor cambia a `registered`
5. **Verificar:** Auto-login (sesion creada)
6. **Verificar:** Redirige a `/supplier/onboarding`

```php
$supplier = Supplier::factory()->invited()->create();
$invitation = SupplierInvitation::factory()->create([
    'supplier_id' => $supplier->id,
]);

$this->post(route('supplier.set-password.store'), [
    'token' => $invitation->token,
    'password' => 'MiPassword1!',
    'password_confirmation' => 'MiPassword1!',
])->assertRedirect(route('supplier.onboarding'));

$this->assertDatabaseHas('suppliers', [
    'id' => $supplier->id,
    'status' => 'registered',
]);
$this->assertAuthenticatedAs($supplier->fresh(), 'supplier');
```

#### 4.3 Requisitos de contrasena (validar cada uno)

| Requisito | Ejemplo valido | Ejemplo invalido |
|-----------|---------------|-----------------|
| Minimo 10 caracteres | `Abcdefgh1!` | `Short1!` |
| Al menos 1 mayuscula | `miPassword1!` | `mipassword1!` |
| Al menos 1 numero | `MiPassword1!` | `MiPassword!!` |
| Al menos 1 caracter especial | `MiPassword1!` | `MiPassword12` |
| Confirmacion coincide | Ambos iguales | Diferentes |

Probar cada caso individualmente y verificar mensajes de error en espanol.

#### 4.4 Token invalido

1. Navegar a `/supplier/set-password?token=token_inexistente`
2. **Verificar:** Mensaje de error sobre token invalido

#### 4.5 Token expirado

1. Navegar con token que tiene `expires_at` en el pasado
2. **Verificar:** Mensaje de error indicando expiracion
3. **Verificar:** Opcion de "solicitar nueva invitacion"

#### 4.6 Sin token

1. Navegar a `/supplier/set-password` (sin parametro token)
2. **Verificar:** Mensaje de error sobre token faltante

#### 4.7 Proveedor ya activo intenta set-password

1. Navegar con token de un proveedor que ya esta `active`
2. **Verificar:** Redirige a dashboard (no permite re-establecer)

---

## 5. Flujo 3: Onboarding (Perfil Completo)

**Ruta:** `GET /supplier/onboarding`
**Controlador:** `OnboardingController@show` / `OnboardingController@submit`
**Pagina React:** `resources/js/pages/Supplier/Onboarding.tsx`

### Wizard de 3 pasos

#### 5.1 Paso 1: Direccion

**Campos:**
| Campo | Validacion | Placeholder |
|-------|-----------|-------------|
| Calle | Requerido, max 255 | - |
| Numero | Requerido, max 255 | - |
| Barrio/Colonia | Requerido, max 255 | - |
| Ciudad | Requerido, max 255 | - |
| Codigo Postal | Requerido, exacto 5 digitos (`^\d{5}$`) | - |
| Pais | Requerido, enum (Mexico/USA/Canada) | Default: Mexico |

**Pruebas:**
1. Llenar todos los campos correctamente
2. Clic "Siguiente"
3. **Verificar:** Avanza al Paso 2 sin errores
4. Dejar campo vacio y avanzar
5. **Verificar:** Mensaje de validacion en espanol

#### 5.2 Paso 2: Datos Bancarios

**Campos:**
| Campo | Validacion |
|-------|-----------|
| CLABE Interbancaria | Requerido, exacto 18 digitos (`^\d{18}$`), unico en tabla suppliers |

**Pruebas:**
1. Ingresar CLABE de 18 digitos
2. **Verificar:** Solo acepta numeros
3. Probar CLABE de 17 digitos -> error
4. Probar CLABE de 19 digitos -> error
5. Probar CLABE ya registrada por otro proveedor -> error: "Esta CLABE ya esta registrada"

#### 5.3 Paso 3: Confirmacion

**Elementos visibles:**
1. **Verificar:** Resumen de direccion formateada
2. **Verificar:** CLABE enmascarada (solo ultimos 4 digitos visibles: `**************1234`)
3. **Verificar:** Checkbox: "Confirmo que toda la informacion es correcta"
4. **Verificar:** Boton de enviar deshabilitado hasta marcar checkbox

**Pruebas de envio:**
1. Marcar checkbox y enviar
2. **Verificar:** Status cambia a `profile_completed`
3. **Verificar:** Datos de direccion guardados en BD
4. **Verificar:** CLABE guardada en BD
5. **Verificar:** Se despacha `SupplierVerificationJob`
6. **Verificar:** Redirige a `/dashboard`
7. **Verificar:** Mensaje flash: "Perfil completado. Estamos verificando tu informacion."

```php
$supplier = Supplier::factory()->registered()->create([
    'password_hash' => Hash::make('TestPass123!'),
]);

Queue::fake();

$this->actingAs($supplier, 'supplier')
    ->post(route('supplier.onboarding.submit'), [
        'address_street' => 'Av. Reforma',
        'address_number' => '123',
        'address_neighborhood' => 'Centro',
        'address_city' => 'CDMX',
        'address_zip' => '06600',
        'address_country' => 'Mexico',
        'clabe_interbancaria' => '012345678901234567',
        'confirm' => true,
    ])->assertRedirect('/dashboard');

$this->assertDatabaseHas('suppliers', [
    'id' => $supplier->id,
    'status' => 'profile_completed',
    'address_city' => 'CDMX',
    'clabe_interbancaria' => '012345678901234567',
]);

Queue::assertPushed(SupplierVerificationJob::class);
```

#### 5.4 Proveedor activo intenta acceder a onboarding

1. Loguearse como proveedor activo
2. Navegar a `/supplier/onboarding`
3. **Verificar:** Redirige a `/dashboard` (ya completo el proceso)

#### 5.5 Navegacion entre pasos

1. Avanzar al Paso 2
2. Regresar al Paso 1
3. **Verificar:** Los datos del Paso 1 siguen llenos
4. Avanzar de nuevo
5. **Verificar:** Los datos del Paso 2 siguen llenos

---

## 6. Flujo 4: Dashboard del Proveedor

**Ruta:** `GET /dashboard`
**Pagina React:** `resources/js/pages/Supplier/Dashboard.tsx`

### Secciones a verificar

#### 6.1 Header

1. **Verificar:** Logo de la empresa visible
2. **Verificar:** Nombre del proveedor mostrado
3. **Verificar:** Email del proveedor mostrado
4. **Verificar:** Badge de estado con color correcto:
   - `created` = gris
   - `invited` = amarillo
   - `registered` = azul
   - `profile_completed` = naranja ("En Verificacion")
   - `active` = verde
5. **Verificar:** Boton "Cerrar Sesion" funcional

#### 6.2 Seccion de bienvenida

1. **Verificar:** "Bienvenido, {nombre}" (usa primer nombre)
2. **Verificar:** Mensaje dinamico segun estado:
   - `registered`: Mensaje sobre completar perfil
   - `profile_completed`: Mensaje sobre verificacion en proceso
   - `active`: Mensaje de bienvenida completo

#### 6.3 Stepper de progreso

1. **Verificar:** 4 pasos visibles: Registro, Perfil, Verificacion, Activo
2. **Verificar:** Pasos completados resaltados en verde
3. **Verificar:** Paso actual destacado
4. Si `registered`: boton "Siguiente" que lleva a onboarding

#### 6.4 Tarjetas de estadisticas (4 cards)

| Card | Valor esperado |
|------|---------------|
| Sucursales | Numero de sucursales asignadas |
| Perfil | Porcentaje: 0% / 33% / 67% / 100% segun datos completados |
| Ubicacion | Ciudad o "---" si no hay direccion |
| CLABE | "Registrada" o "---" si no hay CLABE |

#### 6.5 Informacion personal

1. **Verificar:** Nombre y email siempre visibles
2. Si tiene direccion: **Verificar** direccion completa con icono
3. Si tiene CLABE: **Verificar** CLABE enmascarada (`**************XX`)
4. **Verificar:** Fecha "Miembro desde"

#### 6.6 Sucursales

1. Si tiene sucursales: **Verificar** lista con nombre y fecha
2. Si NO tiene: **Verificar** mensaje: "Sin sucursales asignadas. Tu administrador asignara sucursales a tu cuenta"

#### 6.7 Seccion de ayuda

1. **Verificar:** Info de contacto de soporte (email + WhatsApp)

---

## 7. Flujo 5: Gestion de Documentos

### 7.1 Ver documentos en dashboard

1. Loguearse como proveedor activo con documentos asignados
2. **Verificar:** Seccion de documentos visible en dashboard
3. **Verificar:** Cada documento muestra:
   - Nombre del tipo de documento
   - Badge de estado con color (gray/blue/green/red)
   - Nombre de archivo (si tiene)
   - Razon de rechazo (si estado = Rechazado)

### 7.2 Subir documento

**Ruta:** `POST /supplier/documents/{supplierDocument}/upload`

**Condiciones para poder subir:**
- Estado del documento = `Pendiente` (ID: 1) o `Rechazado` (ID: 4)
- El documento pertenece al proveedor autenticado

**Pasos:**
1. Encontrar documento con boton "Subir" habilitado
2. Seleccionar archivo (PDF, JPG o PNG, max 10MB)
3. **Verificar:** Archivo se sube exitosamente
4. **Verificar:** Estado vuelve a "Pendiente"
5. **Verificar:** Nombre de archivo aparece en la lista
6. **Verificar:** Boton cambia a "Re-subir" y "Descargar"

```php
// Requiere DocumentStateSeeder
$this->seed(DocumentStateSeeder::class);

$supplier = Supplier::factory()->active()->create();
$doc = SupplierDocument::factory()->create([
    'supplier_id' => $supplier->id,
    'document_state_id' => 1, // Pendiente
]);

$file = UploadedFile::fake()->create('documento.pdf', 1024, 'application/pdf');

$this->actingAs($supplier, 'supplier')
    ->post(route('supplier.documents.upload', $doc), [
        'file' => $file,
    ])->assertRedirect();

$this->assertDatabaseHas('supplier_documents', [
    'id' => $doc->id,
    'archivo_nombre' => 'documento.pdf',
    'document_state_id' => 1,
]);
```

### 7.3 Validaciones de archivo

| Caso | Archivo | Resultado esperado |
|------|---------|-------------------|
| PDF valido | `test.pdf` (1MB) | Exito |
| JPG valido | `foto.jpg` (2MB) | Exito |
| PNG valido | `imagen.png` (500KB) | Exito |
| Archivo muy grande | `grande.pdf` (15MB) | Error: max 10MB |
| Tipo invalido | `hoja.xlsx` | Error: solo PDF/JPG/PNG |
| Sin archivo | (vacio) | Error: archivo requerido |

### 7.4 Re-subir documento

1. Subir un archivo a un documento
2. Subir otro archivo al mismo documento
3. **Verificar:** El archivo anterior se elimina del storage
4. **Verificar:** El nuevo archivo reemplaza al anterior
5. **Verificar:** Estado se resetea a "Pendiente"
6. **Verificar:** Notas de rechazo se limpian

### 7.5 Descargar documento

**Ruta:** `GET /supplier/documents/{supplierDocument}/download`

1. Tener un documento con archivo subido
2. Clic en "Descargar"
3. **Verificar:** Se descarga el archivo con el nombre original
4. **Verificar:** El contenido del archivo es correcto

### 7.6 Autorizacion de documentos

1. Intentar subir a documento de OTRO proveedor
2. **Verificar:** Error 403 Forbidden
3. Intentar descargar documento de OTRO proveedor
4. **Verificar:** Error 401/403
5. Intentar subir a documento con estado "Aprobado"
6. **Verificar:** Error 403 (no puede subir a documento aprobado)

---

## 8. Flujo 6: Recuperar Contrasena

**Ruta:** `GET /supplier/forgot-password`
**Controlador:** `ForgotPasswordController@show` / `ForgotPasswordController@sendResetLink`
**Pagina React:** `resources/js/pages/Supplier/ForgotPassword.tsx`

### Pasos de prueba

#### 8.1 Solicitar reset exitoso

1. Navegar a `/supplier/forgot-password`
2. Ingresar email de proveedor existente
3. Enviar formulario
4. **Verificar:** Mensaje de exito (siempre muestra exito por seguridad)
5. **Verificar:** Se genera `password_reset_token` en BD
6. **Verificar:** Se establece `password_reset_expires_at` (60 min)
7. **Verificar:** Se envia email `SupplierPasswordResetMailable`

```php
Mail::fake();

$supplier = Supplier::factory()->active()->create();

$this->post(route('supplier.forgot-password.store'), [
    'email' => $supplier->email,
]);

Mail::assertSent(SupplierPasswordResetMailable::class);
$this->assertNotNull($supplier->fresh()->password_reset_token);
```

#### 8.2 Email no registrado

1. Ingresar email que no existe en el sistema
2. **Verificar:** Mismo mensaje de exito (prevencion de enumeracion de emails)
3. **Verificar:** No se envia email

#### 8.3 Throttling de solicitudes

1. Solicitar reset para un email
2. Inmediatamente solicitar de nuevo para el mismo email
3. **Verificar:** No se envia segundo email (token existente valido por >59 min)

---

## 9. Flujo 7: Restablecer Contrasena

**Ruta:** `GET /supplier/reset-password?token={token}&email={email}`
**Controlador:** `ResetPasswordController@show` / `ResetPasswordController@reset`
**Pagina React:** `resources/js/pages/Supplier/ResetPassword.tsx`

### Pasos de prueba

#### 9.1 Reset exitoso

1. Navegar a `/supplier/reset-password?token={token}&email={email}`
2. **Verificar:** Pagina carga con campos de contrasena
3. Ingresar nueva contrasena (mismos requisitos que set-password)
4. Confirmar contrasena
5. Enviar formulario
6. **Verificar:** `password_hash` actualizado en BD
7. **Verificar:** `password_reset_token` limpiado (null)
8. **Verificar:** `password_reset_expires_at` limpiado (null)
9. **Verificar:** NO auto-login (redirige a login)
10. **Verificar:** Mensaje: "Tu contrasena ha sido restablecida. Ahora puedes iniciar sesion."

#### 9.2 Token expirado

1. Navegar con token que expiro hace mas de 60 minutos
2. **Verificar:** Error de token expirado

#### 9.3 Email no coincide

1. Navegar con token valido pero email de otro proveedor
2. **Verificar:** Error de validacion

---

## 10. Conexion con el Panel Admin

### Acciones del admin que afectan al proveedor

| Accion del Admin | Efecto en Proveedor |
|-----------------|---------------------|
| Crear proveedor | Status = `created`, no puede hacer nada |
| Reenviar invitacion | Status = `invited`, recibe email con link de set-password |
| Asignar documentos | Aparecen documentos en el dashboard del proveedor |
| Cambiar estado de documento a "Rechazado" | Proveedor ve badge rojo + razon, puede re-subir |
| Cambiar estado de documento a "Aprobado" | Proveedor ve badge verde, no puede modificar |
| Cambiar contrasena del proveedor | La contrasena anterior deja de funcionar inmediatamente |
| Asignar sucursal al proveedor | Aparece en la seccion "Mis Sucursales" del dashboard |
| Desasociar sucursal | Desaparece de la lista del dashboard |
| Eliminar proveedor (soft) | Proveedor no puede hacer login |

### Flujo completo Admin -> Proveedor

```
ADMIN                                    PROVEEDOR
  |                                          |
  |-- Crea proveedor ----------------------->| (status: created)
  |                                          |
  |-- Envia invitacion ---(email)----------->| (status: invited)
  |                                          |
  |                                          |-- Clic link email
  |                                          |-- Establece contrasena
  |                                          |   (status: registered)
  |                                          |
  |                                          |-- Login
  |                                          |-- Completa onboarding
  |                                          |   (status: profile_completed)
  |                                          |
  |-- Asigna documentos ------------------->| Documentos aparecen en dashboard
  |                                          |
  |                                          |-- Sube documentos
  |                                          |
  |-- Revisa documentos                      |
  |-- Aprueba / Rechaza ------------------->| Ve nuevo estado en dashboard
  |                                          |
  |                                          |-- Re-sube si rechazado
  |                                          |
  |-- Activa proveedor -------------------->| (status: active)
  |                                          |-- Acceso completo
```

---

## 11. Factories y Datos de Prueba

### SupplierFactory

```php
Supplier::factory()->create();                    // status: created
Supplier::factory()->invited()->create();         // status: invited
Supplier::factory()->registered()->create();      // status: registered, tiene password
Supplier::factory()->profileCompleted()->create(); // status: profile_completed
Supplier::factory()->active()->create();          // status: active
```

### SupplierInvitationFactory

```php
SupplierInvitation::factory()->create([
    'supplier_id' => $supplier->id,
]);                                                // Token valido, 7 dias

SupplierInvitation::factory()->expired()->create(); // Token expirado
SupplierInvitation::factory()->accepted()->create(); // Token ya usado
```

### SupplierDocumentFactory

```php
SupplierDocument::factory()->create();            // Sin archivo
SupplierDocument::factory()->uploaded()->create(); // Con archivo subido
SupplierDocument::factory()->withExpiry()->create(); // Con fecha de vencimiento
```

### Seeders necesarios

```php
// IMPORTANTE: Siempre ejecutar antes de tests de documentos
$this->seed(DocumentStateSeeder::class);
// Crea: Pendiente (1), En Revision (2), Aprobado (3), Rechazado (4)
```

---

## 12. Checklist de Regresion

### Login
- [ ] Login exitoso con proveedor activo -> dashboard
- [ ] Login exitoso con proveedor registrado -> onboarding
- [ ] Login fallido con credenciales incorrectas
- [ ] Login fallido con cuenta no habilitada (created/invited)
- [ ] Redireccion si ya autenticado
- [ ] Toggle de visibilidad de contrasena
- [ ] Link "Olvidaste tu contrasena?" funcional
- [ ] Link "Volver al inicio" funcional

### Set Password
- [ ] Token valido carga pagina correctamente
- [ ] Contrasena establecida exitosamente
- [ ] Auto-login y redireccion a onboarding
- [ ] Validacion: minimo 10 caracteres
- [ ] Validacion: al menos 1 mayuscula
- [ ] Validacion: al menos 1 numero
- [ ] Validacion: al menos 1 caracter especial
- [ ] Validacion: confirmacion coincide
- [ ] Token invalido muestra error
- [ ] Token expirado muestra error
- [ ] Sin token muestra error

### Onboarding
- [ ] Paso 1: Todos los campos de direccion requeridos
- [ ] Paso 1: Codigo postal exacto 5 digitos
- [ ] Paso 1: Pais default Mexico
- [ ] Paso 2: CLABE exacto 18 digitos
- [ ] Paso 2: CLABE unica (no duplicada)
- [ ] Paso 3: Resumen muestra datos correctos
- [ ] Paso 3: CLABE enmascarada
- [ ] Paso 3: Checkbox obligatorio para enviar
- [ ] Envio exitoso cambia status a profile_completed
- [ ] Navegacion entre pasos conserva datos
- [ ] Proveedor activo redirigido si intenta acceder

### Dashboard
- [ ] Header: logo, nombre, email, badge de estado
- [ ] Stepper de progreso refleja estado actual
- [ ] 4 tarjetas de estadisticas con valores correctos
- [ ] Informacion personal completa
- [ ] Lista de sucursales (o mensaje vacio)
- [ ] Seccion de documentos visible si tiene documentos
- [ ] Boton "Cerrar Sesion" funcional

### Documentos
- [ ] Subir PDF exitoso
- [ ] Subir JPG/PNG exitoso
- [ ] Error al subir archivo mayor a 10MB
- [ ] Error al subir tipo de archivo invalido
- [ ] Re-subir reemplaza archivo anterior
- [ ] Descargar archivo funcional
- [ ] No puede subir a documento de otro proveedor (403)
- [ ] No puede subir a documento Aprobado (403)
- [ ] No puede descargar documento de otro proveedor (403)
- [ ] Badge de estado muestra color correcto

### Recuperar/Restablecer Contrasena
- [ ] Solicitud de reset envia email
- [ ] Email no registrado muestra mismo mensaje (seguridad)
- [ ] Throttling: no envia doble email
- [ ] Reset exitoso actualiza contrasena
- [ ] Reset limpia token y expiracion
- [ ] Reset no hace auto-login
- [ ] Token expirado rechazado
- [ ] Email no coincidente rechazado
