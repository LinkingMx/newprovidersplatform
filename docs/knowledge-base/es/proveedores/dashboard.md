---
title: Dashboard del Proveedor
icon: heroicon-o-chart-bar
---

# Dashboard del Proveedor

## Vista General

Una vez que el proveedor completa el onboarding y esta activo, accede a su **Dashboard** donde puede ver el resumen de su cuenta.

## Secciones del Dashboard

### Informacion de la Empresa
Muestra los datos generales del proveedor:
- Razon social
- RFC
- Correo electronico
- Telefono
- Tipo de proveedor
- Sucursales asociadas

### Mis Documentos
Muestra el estado actual de los documentos del expediente:

| Estado | Significado |
|--------|-------------|
| **Pendiente** | El documento aun no ha sido subido |
| **En Revision** | El documento fue subido y esta siendo revisado |
| **Aprobado** | El documento fue aceptado |
| **Rechazado** | El documento fue rechazado (ver notas) |

> Los documentos rechazados incluyen notas del revisor explicando el motivo. El proveedor puede corregir y re-subir.

## Acceso al Dashboard

- **URL**: `/dashboard`
- **Autenticacion**: Requiere inicio de sesion como proveedor
- **Redireccion**: Los proveedores autenticados son redirigidos automaticamente al dashboard

## Problemas Comunes

| Problema | Solucion |
|----------|----------|
| No puede acceder al dashboard | Verificar que el estado sea "Activo" o "Perfil Completado" |
| No ve documentos | Verificar que existan tipos de documento activos |
| Redirige al login | La sesion expiro, debe iniciar sesion nuevamente |
| Redirige a onboarding | El proveedor no ha completado su perfil |
