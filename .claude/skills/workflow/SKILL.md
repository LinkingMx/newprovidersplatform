# Workflow Estandar - 4 Pasos con Aprobacion

Sigue este flujo estricto para la solicitud actual del usuario. No te desvies del proceso.

## Paso 0: CONTEXTO (Knowledge Graph)

Antes de planificar, consulta el Knowledge Graph via MCP Docker:
- Usa `search_nodes` para buscar entidades relacionadas con la tarea
- Ejemplo: si vas a trabajar con Supplier, ejecuta `search_nodes("Supplier")` para obtener modelo, relaciones, resources, convenciones y gotchas
- Usa `read_graph` si necesitas una vision general del proyecto
- Esto te da contexto inmediato sin necesidad de explorar archivos

## Paso 1: PLANIFICACION

Genera un plan detallado que incluya:
- Que cambia exactamente
- Donde cambia (archivos especificos)
- Por que esa solucion
- Riesgos o consideraciones importantes

Muestra el plan y espera la aprobacion del usuario ("Ok" o cambios solicitados).
NO implementes nada hasta recibir aprobacion explicita.

## Paso 2: IMPLEMENTACION

Una vez aprobado el plan:
- Escribe el codigo/cambios SIN desviaciones del plan aprobado
- Ejecuta `vendor/bin/pint --dirty --format agent` para formatear
- Limpia caches si es necesario (`cache:clear`, `view:clear`)
- **Usa Dev Teams (TeamCreate + Task tool)** para paralelizar trabajo independiente cuando la implementacion toque multiples archivos o areas no relacionadas entre si

## Paso 3: TESTING

Selecciona el tipo de test apropiado segun el contexto:

| Contexto | Tests | Comando |
|----------|-------|---------|
| Filament Resource (CRUD) | Pest Livewire | `php artisan test --compact --filter=ResourceName` |
| Backend (Models, Logic) | Pest Unit/Feature | `php artisan test --compact --filter=LogicName` |
| React/Inertia Page | Dusk | `php artisan dusk --filter=PageName` |
| Livewire Component | Pest Livewire | `php artisan test --compact --filter=ComponentName` |
| Visual/UI Changes | DevTools | Navegador + screenshot verification |
| Problema de datos | Tinker | `tinker` interactivo |
| Cambio mayor | Combinado | Pest + DevTools + visual check |

Reporta: que paso, cuantos tests, resultado.

## Paso 4: COMMIT + KNOWLEDGE GRAPH

### Commit
Genera un commit message con este formato:

```
TIPO: descripcion breve (max 70 caracteres)

Cuerpo opcional con mas detalles:
- Que se cambio
- Por que se cambio
- Impacto de los cambios

Co-Authored-By: Claude Opus 4.6 <noreply@anthropic.com>
```

Tipos validos:
- `feat:` - Nueva funcionalidad completa
- `fix:` - Corrige un bug
- `refactor:` - Reorganiza sin cambiar comportamiento
- `chore:` - Setup, limpieza, deps

Muestra el mensaje entre `---` y espera aprobacion del usuario.
Solo ejecuta el commit cuando el usuario diga "Ok".

### Actualizar Knowledge Graph

Despues de cada commit exitoso, actualiza el Knowledge Graph via MCP Docker:
- `add_observations`: Agregar info nueva a entidades existentes (ej: "Ahora tiene validacion de email")
- `create_entities`: Si se crearon nuevos modelos, resources, features, configs
- `create_relations`: Si se establecieron nuevas dependencias entre entidades
- `delete_observations` / `delete_entities`: Si algo se removio o cambio significativamente

Ejemplo despues de crear un nuevo Resource:
```
create_entities([{
  name: "NuevoResource",
  entityType: "filament_resource",
  observations: ["Administra modelo Nuevo", "Labels en espanol", "sort: 5"]
}])
create_relations([
  { from: "NuevoResource", to: "NuevoModelo", relationType: "manages" }
])
```
