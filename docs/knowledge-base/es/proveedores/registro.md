---
title: Flujo de Registro
icon: heroicon-o-user-plus
---

# Flujo de Registro del Proveedor

Esta seccion describe el proceso que sigue un proveedor desde que recibe la invitacion hasta que establece su contrasena. Util como referencia para soporte.

## Paso 1: Invitacion por Correo

El proveedor recibe un correo electronico con:

- Nombre de la empresa (SELA Travel)
- Mensaje de bienvenida personalizado
- Boton/enlace para establecer su contrasena
- El enlace tiene vigencia de **72 horas**

> Si el proveedor reporta que no recibio el correo, verifica la carpeta de spam. Si el enlace expiro, puedes reenviar la invitacion desde el panel admin.

## Paso 2: Establecer Contrasena

Al hacer clic en el enlace, el proveedor llega a la pagina de **Establecer Contrasena**:

1. El sistema valida que el token sea valido y no haya expirado
2. El proveedor ingresa su nueva contrasena (minimo 8 caracteres, una mayuscula, un numero)
3. Confirma la contrasena
4. Hace clic en **Guardar Contrasena**

**Posibles errores:**
- "Enlace Invalido": Token incorrecto o expirado → Reenviar invitacion
- Error de validacion: La contrasena no cumple los requisitos → Indicar requisitos

## Paso 3: Redireccion a Onboarding

Tras establecer la contrasena exitosamente, el proveedor es redirigido automaticamente al proceso de **Onboarding** donde completara su perfil.

## Problemas Comunes

| Problema | Solucion |
|----------|----------|
| No recibio el correo | Verificar spam, reenviar invitacion |
| Enlace expirado | Reenviar invitacion desde el panel |
| "Enlace Invalido" | Verificar que el link sea completo (no cortado) |
| Error al guardar contrasena | Verificar requisitos de complejidad |
