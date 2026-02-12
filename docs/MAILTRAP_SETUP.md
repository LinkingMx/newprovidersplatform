# Configuración de Mailtrap para Testing de Emails

Este documento explica cómo está configurado Mailtrap en el proyecto y cómo usarlo para testing de emails.

---

## 🔧 Configuración Actual

### Credenciales en `.env`

```env
# SMTP Configuration
MAIL_MAILER=smtp
MAIL_SCHEME=tls
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=587
MAIL_USERNAME=912b5d949c4fb4
MAIL_PASSWORD=1573941d01c7fe
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="Laravel"

# Mailtrap API Configuration
MAILTRAP_API_TOKEN=86e47a9d0aaaa7d4efdc8037a8d73252
MAILTRAP_INBOX_ID=3870553
MAILTRAP_SANDBOX=true
```

### Configuración en `config/services.php`

```php
'mailtrap' => [
    'api_token' => env('MAILTRAP_API_TOKEN'),
    'inbox_id' => env('MAILTRAP_INBOX_ID'),
    'sandbox' => env('MAILTRAP_SANDBOX', true),
],
```

---

## 📧 Envío de Emails

### Método 1: SMTP (Configuración por Defecto)

**Comando disponible:**

```bash
php artisan send-mail
```

**Qué hace:**
- Envía un email de prueba vía SMTP
- To: `armingkings@gmail.com`
- From: `hello@example.com`
- Asunto: "You are awesome!"
- Categoría: "Integration Test"

**Salida esperada:**
```
🚀 Sending test email via Mailtrap SMTP...
✅ Email sent successfully via SMTP!

Email Details:
  From: hello@example.com (Mailtrap Test)
  To: armingkings@gmail.com
  Subject: You are awesome!
  Body: Congrats for sending test email with Mailtrap!
  Category: Integration Test
```

---

### Método 2: API (Requiere SDK)

**Comando disponible:**

```bash
php artisan send-mail:api
```

**Requiere instalación:**

```bash
composer require mailtrap/mailtrap-php
```

**Qué hace:**
- Envía email usando la API REST de Mailtrap
- Retorna respuesta JSON con detalles
- Útil para verificar integración con Mailtrap

**Salida esperada:**
```
🚀 Sending test email via Mailtrap API...
✅ Email sent successfully via API!

API Response:
{
  "success": true,
  "message_ids": ["123456789"],
  ...
}
```

---

## 🧪 Testing de Emails en la Aplicación

### Emails que se Enviarán Automáticamente

**1. Invitación a Proveedor** (Futuro)
- Cuando admin crea proveedor
- URL con token de invitación
- Destinatario: email del proveedor

**2. Notificación de Verificación** (Futuro)
- Cuando supplier completa onboarding
- Confirmación de datos recibidos
- Destinatario: email del supplier

---

## 🔗 Dashboard de Mailtrap

**Acceso:** https://mailtrap.io/inboxes/

**En el dashboard puedes:**
- ✅ Ver todos los emails enviados
- ✅ Visualizar HTML/texto del email
- ✅ Ver headers y metadata
- ✅ Verificar categorías
- ✅ Descargar attachments
- ✅ Tener múltiples inboxes para diferentes tests

**Inbox usado:** `3870553` (Sandbox Testing)

---

## 💻 Uso en Código Laravel

### Envío Manual en Controllers/Jobs

```php
use Illuminate\Support\Facades\Mail;

// Método simple
Mail::raw('Email body', function ($message) {
    $message
        ->to('user@example.com')
        ->from('hello@example.com')
        ->subject('Subject');
});

// Con categoría (Mailtrap)
Mail::raw('Content', function ($message) {
    $message
        ->to('user@example.com')
        ->subject('Subject')
        ->getHeaders()
        ->addTextHeader('X-Mailtrap-Category', 'MyCategory');
});
```

### Mailable Classes (Recomendado)

Crear un Mailable:

```bash
php artisan make:mail SupplierInvitation
```

Usar en job/controller:

```php
use App\Mail\SupplierInvitation;

Mail::to($supplier->email)->send(new SupplierInvitation($supplier));
```

---

## 🧨 Testing de Emails en Pest

### Mock de Emails (Sin enviar realmente)

```php
use Illuminate\Support\Facades\Mail;

it('sends supplier invitation email', function () {
    Mail::fake();

    // Trigger email send
    $supplier = Supplier::factory()->create();
    dispatch(new SendInvitationJob($supplier));

    // Assert
    Mail::assertSent(SupplierInvitation::class);
    Mail::assertSent(SupplierInvitation::class, function ($mail) {
        return $mail->hasTo('test@example.com');
    });
});
```

### Verificación Real (Envía a Mailtrap)

```php
it('actually sends email to mailtrap', function () {
    Mail::to('test@example.com')->send(
        new SupplierInvitation($supplier)
    );

    // Verificar en dashboard de Mailtrap
    // https://mailtrap.io/inboxes/3870553
})->skip('Only run manually');
```

---

## 🚀 Flujo Completo de Testing

### 1. Testing Unitario (Sin Emails)

```bash
php artisan test --compact --filter=SupplierTest
```

En los tests:
```php
Mail::fake(); // No envía nada
// Assertions sobre Mail::assertSent()
```

### 2. Testing Manual (Con Mailtrap)

```bash
# Enviar email de prueba
php artisan send-mail

# Ver resultado en dashboard:
# https://mailtrap.io/inboxes/3870553
```

### 3. Testing de Flujo Real (Opcional)

```bash
# Crear supplier y generar invitación
php artisan tinker

$supplier = Supplier::factory()->create();
SupplierInvitation::factory()->create(['supplier_id' => $supplier->id]);

# Verificar email en Mailtrap
```

---

## 🔐 Credenciales - Referencia Rápida

| Configuración | Valor |
|---------------|-------|
| **SMTP Host** | sandbox.smtp.mailtrap.io |
| **SMTP Port** | 587 (TLS) |
| **Username** | 912b5d949c4fb4 |
| **Password** | 1573941d01c7fe |
| **API Token** | 86e47a9d0aaaa7d4efdc8037a8d73252 |
| **Inbox ID** | 3870553 |
| **From Email** | hello@example.com |
| **Test To Email** | armingkings@gmail.com |

---

## 📝 Notas Importantes

### Seguridad

⚠️ **NUNCA** comitear credenciales reales en repositorio público. Usar `.env` con `.env.example` documentado.

El `.env` está en `.gitignore` ✅

### Sandboxing

Mailtrap **Sandbox** = Emails nunca salen del sistema, solo se almacenan en inbox.
Útil para testing sin riesgo de enviar a usuarios reales.

### Límites

- Plan gratuito: 500 emails/mes
- Plan pro: Ilimitado
- Retención: 7 días por defecto

---

## 🆘 Troubleshooting

### "Connection timed out"

```
Causa: Firewall bloqueando puerto 587
Solución: Intentar puertos alternativos (25, 465, 2525)
```

### "SMTP authentication failed"

```
Causa: Username o password incorrectos
Solución: Verificar credenciales en .env
```

### "API token invalid"

```
Causa: Token expirado o incorrecto
Solución: Generar nuevo token en https://mailtrap.io/api/
```

### No veo emails en inbox

```
Causa: Email no se envió o fue a spam
Solución:
  1. Revisar logs: storage/logs/laravel.log
  2. Ejecutar: php artisan send-mail
  3. Ver en https://mailtrap.io/inboxes/3870553
```

---

## 📚 Referencias

- **Mailtrap Docs:** https://mailtrap.io/
- **Mailtrap API:** https://mailtrap.io/api/
- **Laravel Mail:** https://laravel.com/docs/12/mail
- **Laravel Testing:** https://laravel.com/docs/12/testing

---

**Última actualización:** Febrero 2026
**Estado:** ✅ Configurado y listo para testing
