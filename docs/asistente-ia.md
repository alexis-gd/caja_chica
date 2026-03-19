# Asistente IA con Voz — Caja Chica

## Contexto
Demo para el **dueño** de Grupo Uribe. Las **empleadas** operan el CRUD del sistema; el dueño usa exclusivamente el asistente para consultar el estado de la caja: saldos, gastos, ingresos, quién hizo qué, análisis de ahorro, etc.

**Respuesta:** Voz + texto + mini gráfica de barras para preguntas de tendencias.

---

## Roles

| Rol | Uso del sistema |
|-----|----------------|
| Empleadas (capturistas) | CRUD de movimientos, subir comprobantes |
| Dueño | Solo consulta al asistente (voz o clic) |

Página restringida a `$_SESSION['nivel'] == 1`.

---

## Archivos creados/modificados

| Archivo | Tipo | Descripción |
|---------|------|-------------|
| `config/config.php` | Editado | Constantes `AI_PROVIDER`, `GROQ_*`, `AI_ACTIVE_MODEL` |
| `functions/ai/chat.php` | Nuevo | Router: contexto + proveedor + logging |
| `functions/ai/context.php` | Nuevo | `buildContext()` — queries de BD (read-only) |
| `functions/ai/providers/groq.php` | Nuevo | `callGroq()` — adaptador Groq (firma estandarizada) |
| `asistente-ia.php` | Nuevo | Página demo con shell AdminLTE |
| `js/asistente.js` | Nuevo | Voice, chat UI, Chart.js mini, barra de uso |
| `templates/sidebar.php` | Editado | Link "Asistente IA" con badge Demo (solo admin) |
| `docs/ai_migration.sql` | Nuevo | SQL para crear las 3 tablas |

---

## Tablas de BD (ejecutar `docs/ai_migration.sql`)

| Tabla | Propósito |
|-------|-----------|
| `ai_conversaciones` | Log de preguntas/respuestas con campo `proveedor_ia` |
| `ai_uso_diario` | Tokens y requests por día y proveedor (para barra UI) |
| `ai_sugerencias` | Mejoras de software detectadas por la IA (solo dev) |

---

## Setup inicial

1. Ejecutar `docs/ai_migration.sql` en phpMyAdmin sobre `grupour1_caja_chica`
2. Obtener API key gratis en `console.groq.com` (sin tarjeta de crédito)
3. Editar `config/config.php` y reemplazar `gsk_REEMPLAZA_CON_TU_KEY` con tu key real
4. Abrir `http://grupouribe.local/caja_chica/asistente-ia.php` con usuario nivel 1

---

## Flujo de datos

```
[Dueño habla o hace clic en pregunta]
        ↓
[Web Speech API → texto]     (Chrome/Edge, gratis)
        ↓
[js/asistente.js → POST functions/ai/chat.php]
        ├── buildContext() → 9 queries read-only sobre caja_chica
        ├── Guard: bloquea verbos de escritura
        ├── Detecta keywords de tendencia → show_chart: true
        └── callGroq() → api.groq.com (free tier)
                ↓
        ├── Extrae [SUGERENCIA_DEV:...] del texto → ai_sugerencias
        ├── INSERT ai_conversaciones (proveedor_ia, tokens)
        └── UPDATE ai_uso_diario (ON DUPLICATE KEY)
                ↓
[Burbuja de texto + SpeechSynthesis (voz)]
[Si show_chart → mini Chart.js de barras en el chat]
[Barra de uso se actualiza]
```

---

## Datos que el asistente conoce

- Totales de ingreso/egreso del mes actual
- Saldo acumulado actual (`caja_chica_totales`)
- Conteo de transacciones mes y año
- Top 5 tipos de gasto del mes
- Tendencia mensual anual (labels + datos para Chart.js)
- Últimas 10 transacciones con nombres reales (cargado, recibe)
- Catálogos activos: personas, áreas, tipos de gasto
- **Insights ego:** mejor mes del año, área más activa, racha saldo positivo
- **Análisis ahorro:** comparativa mes actual vs anterior por categoría

---

## Modo dev: ver sugerencias de mejora

La card de sugerencias está **oculta por defecto** (el cliente nunca la ve).

Para activarla:
- **Teclado:** `Ctrl + Shift + S` → recarga la página con `?dev=1`
- **URL directa:** `asistente-ia.php?dev=1`

La tabla muestra todas las sugerencias pendientes con botón "Revisada" para marcarlas.
Las sugerencias se guardan automáticamente cuando la IA detecta un patrón relevante en la respuesta.

---

## Cómo cambiar de proveedor de IA

El logging es **agnóstico al proveedor** (campo `proveedor_ia` en todas las tablas).

Para migrar de Groq a Claude:
1. Descomentar `CLAUDE_API_KEY` / `CLAUDE_MODEL` en `config/config.php`
2. Crear `functions/ai/providers/claude.php` con la misma firma que `groq.php`:
   ```php
   function callClaude(string $systemPrompt, string $userMessage): array {
       // Retorna: ['error' => bool, 'message' => string, 'tokens' => int, 'modelo' => string]
   }
   ```
3. Cambiar `AI_PROVIDER = 'claude'` y `AI_ACTIVE_MODEL = CLAUDE_MODEL` en `config.php`
4. El historial de `ai_conversaciones` se conserva — `proveedor_ia` registra cuál usó cada respuesta

Para Gemini: igual, crear `providers/gemini.php` con la misma firma.

---

## Seguridad

| Capa | Mecanismo |
|------|-----------|
| Sesión | `sesiones.php` primer include — redirige a login |
| Solo nivel=1 | Guard en `asistente-ia.php` y restricción implícita en sidebar |
| API Keys | Solo en `config/config.php` (gitignored) — nunca en JS ni respuestas HTTP |
| Read-only | Guard PHP bloquea verbos de escritura antes de llamar a la IA |
| System prompt | Hardcoded en `chat.php` — el browser nunca lo recibe |
| Sugerencias dev | Card oculta con CSS, activación por Ctrl+Shift+S o `?dev=1` |

---

## Cómo mejorar el asistente con el tiempo

1. Revisar `ai_conversaciones` → identificar preguntas frecuentes → agregar a botones sugeridos en `asistente-ia.php`
2. Ver preguntas sin respuesta satisfactoria → enriquecer queries en `functions/ai/context.php`
3. Si la IA no conoce cierto dato → agregar la query a `buildContext()`
4. Revisar `ai_sugerencias` (`?dev=1`) → evaluar cuáles entran al roadmap

---

## Preguntas sugeridas configuradas

1. ¿Cuál es el saldo actual de caja chica?
2. ¿Cuánto se ha gastado en egresos este mes?
3. ¿Cuáles son los principales tipos de gasto del mes?
4. ¿Cómo van los ingresos vs egresos este año? *(activa mini gráfica)*
5. ¿Cuál fue nuestro mejor mes del año?
6. ¿En qué área o categoría podría ahorrar este mes? *(activa análisis predictivo)*
