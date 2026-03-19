<?php
/**
 * functions/ai/chat.php — Router principal del Asistente IA
 * Compatible PHP 5.6+
 *
 * Casos:
 *   getContextData    — Carga datos agregados de BD + uso del día
 *   askAssistant      — Envía pregunta a la IA y retorna respuesta
 *   marcarSugerencia  — Marca una sugerencia como revisada (solo dev)
 */

require_once '../../config/sesiones.php';
require_once '../../config/conexion.php';
require_once '../../config/config.php';
require_once 'context.php';

usuario_autenticado();

// Cargar adaptador del proveedor activo
switch (AI_PROVIDER) {
    case 'claude':
        require_once 'providers/claude.php';
        break;
    case 'gemini':
        require_once 'providers/gemini.php';
        break;
    case 'groq':
    default:
        require_once 'providers/groq.php';
        break;
}

$opcion = isset($_POST['opcion']) ? trim($_POST['opcion']) : '';

switch ($opcion) {
    case 'getContextData':
        echo getContextData();
        break;
    case 'askAssistant':
        echo askAssistant();
        break;
    case 'marcarSugerencia':
        echo marcarSugerencia();
        break;
    default:
        echo json_encode(array('type' => 'ERROR', 'message' => 'Opción no válida.'));
        break;
}

// ─────────────────────────────────────────────
// Case: getContextData
// ─────────────────────────────────────────────
function getContextData()
{
    $conexion = conectar();
    $context  = buildContext($conexion);

    return json_encode(array(
        'type'   => 'SUCCESS',
        'action' => 'CONTINUE',
        'data'   => $context,
        'uso'    => getUsoActual($conexion),
    ));
}

// ─────────────────────────────────────────────
// Case: askAssistant
// ─────────────────────────────────────────────
function askAssistant()
{
    $mensaje = htmlspecialchars(strip_tags(trim(isset($_POST['mensaje']) ? $_POST['mensaje'] : '')), ENT_QUOTES, 'UTF-8');

    if (empty($mensaje)) {
        return json_encode(array('type' => 'ERROR', 'message' => 'Mensaje vacío.'));
    }

    // Guard de escritura: el asistente es 100% read-only
    $patrones_escritura = array(
        '/\b(borra|elimina|cancela|modifica|edita|cambia|agrega|a\xc3\xb1ade|crea|registra)\b/iu',
        '/\b(delete|drop|update|insert|alter)\b/i',
    );
    foreach ($patrones_escritura as $patron) {
        if (preg_match($patron, $mensaje)) {
            $conexion = conectar();
            return json_encode(array(
                'type'                => 'SUCCESS',
                'action'              => 'CONTINUE',
                'message'             => 'Solo puedo consultar información. Para modificar registros, usa el sistema directamente.',
                'show_chart'          => false,
                'sugerencia_guardada' => false,
                'uso'                 => getUsoActual($conexion),
            ));
        }
    }

    // Detectar si mostrar gráfica de tendencias
    $keywords_chart = array('tendencia', 'ingresos vs', 'egresos vs', 'mensual', 'comparativo', 'anual', 'evolución', 'mejor mes', 'cómo van', 'como van');
    $show_chart = false;
    foreach ($keywords_chart as $kw) {
        if (mb_stripos($mensaje, $kw) !== false) {
            $show_chart = true;
            break;
        }
    }

    // Construir contexto desde BD
    $conexion = conectar();
    $context  = buildContext($conexion);

    // ── Detección de nombre: cargar pagos solo si hay exactamente 1 persona detectada ──
    $quitarAcentosLocal = function($s) {
        $bus = array('á','é','í','ó','ú','Á','É','Í','Ó','Ú','ñ','Ñ','ü','Ü');
        $rep = array('a','e','i','o','u','A','E','I','O','U','n','N','u','U');
        return str_replace($bus, $rep, $s);
    };
    $msg_norm      = strtolower($quitarAcentosLocal($mensaje));
    $todos_nombres = array_unique(array_merge(
        isset($context['catalogo_recibe'])  ? $context['catalogo_recibe']  : array(),
        isset($context['catalogo_cargado']) ? $context['catalogo_cargado'] : array()
    ));
    $nombres_detectados = array();
    foreach ($todos_nombres as $nombre) {
        $nom_norm = strtolower($nombre); // catálogo ya viene normalizado sin acentos
        $palabras = array_filter(explode(' ', $nom_norm), function($p) { return strlen($p) > 3; });
        foreach ($palabras as $palabra) {
            // Usar \b (límite de palabra) para evitar que "daniel" matchee dentro de "daniela"
            if (preg_match('/\b' . preg_quote($palabra, '/') . '\b/u', $msg_norm)) {
                $nombres_detectados[] = $nombre;
                break;
            }
        }
    }
    $nombres_detectados = array_unique($nombres_detectados);

    // Si hay 2+ matches, intentar afinar buscando nombre completo como substring exacto.
    // Caso: usuario seleccionó "Daniel Ocharan" desde botón candidato pero "ocharan"
    // también matcheó "Daniela Ocharan". El nombre completo resuelve la ambigüedad.
    if (count($nombres_detectados) > 1) {
        $exactos = array();
        foreach ($nombres_detectados as $n) {
            if (mb_strpos($msg_norm, strtolower($n)) !== false) {
                $exactos[] = $n;
            }
        }
        if (count($exactos) === 1) {
            $nombres_detectados = $exactos;
        }
    }

    // 1 match → datos exactos de esa persona
    // 2+ matches → AI devuelve [CANDIDATOS:], usuario selecciona, siguiente query = 1 match
    // 0 matches → pregunta general, sin datos de persona
    if (count($nombres_detectados) === 1) {
        $context['pagos_persona_detalle'] = getPersonPayments($nombres_detectados, $conexion);
    }
    // ── Fin detección ──

    $ctx_json = json_encode($context, JSON_UNESCAPED_UNICODE);

    $system_prompt = "Eres el asistente financiero personal del dueño de Grupo Uribe para su sistema de Caja Chica.\n"
        . "Tienes acceso completo a toda la información del sistema.\n\n"
        . "REGLAS:\n"
        . "1. Responde en español, de forma amigable y concisa (máximo 3-4 oraciones).\n"
        . "   - SIEMPRE expresa montos en pesos mexicanos con el formato \"1,234.56 pesos\" — NUNCA uses el símbolo $.\n"
        . "2. Usa ÚNICAMENTE los datos del contexto. No inventes números.\n"
        . "3. Si no tienes el dato exacto, dilo claramente: \"No tengo ese detalle disponible.\"\n"
        . "4. NUNCA ejecutes modificaciones — solo puedes consultar información.\n"
        . "5. Cuando sea relevante, destaca logros positivos: mes récord, área más activa, rachas de saldo positivo.\n"
        . "6. Para preguntas de ahorro: identifica categorías que incrementaron vs el mes anterior y da una recomendación concreta.\n"
        . "   - TOP GASTOS: cuando respondas sobre los principales tipos de gasto del mes, presenta los datos en una tabla markdown con exactamente estas columnas: Tipo | Monto | vs Mes Anterior. Usa ↑ X% para incrementos y ↓ X% para disminuciones (datos de comparativa_gastos). NUNCA escribas el nombre del campo 'comparativa_gastos' en la respuesta. Agrega una línea de conclusión breve después de la tabla.\n"
        . "7. INTERPRETACIÓN DE DATOS — MUY IMPORTANTE:\n"
        . "   - PERÍODOS: 'total_egreso_mes', 'transacciones_mes', 'top_gastos', 'tendencia_anual' son del AÑO ACTUAL (campo 'anio_actual'). 'ultimas_transacciones' contiene las últimas 10 transacciones de CUALQUIER FECHA — siempre menciona el año cuando cites una transacción específica.\n"
        . "   - 'mejor_mes_anio_actual' es el mejor mes del AÑO EN CURSO (" . date('Y') . "). Úsalo para responder '¿cuál fue el mejor mes del año?' o '¿cuál es el mejor mes de " . date('Y') . "?'.\n"
        . "   - 'mejor_mes_historico' es el mejor mes de los últimos 12 meses e incluye el campo 'anio'. Úsalo solo si el usuario pregunta por histórico, récord o últimos 12 meses.\n"
        . "   - Si 'tendencia_anual' muestra pocos meses es normal: el año está en curso. Da los datos disponibles sin disculparte ni aclarar que 'solo hay X meses'. El usuario ya sabe en qué fecha estamos.\n"
        . "   - 'saldo_actual' es la suma acumulada de todos los ingresos menos todos los egresos históricos. Es el saldo real en caja.\n"
        . "   - Si 'ultimas_transacciones' muestra registros de un mes pero los totales muestran 0, reporta las transacciones individuales sin contradecirte.\n"
        . "   - NUNCA te contradigas en la misma respuesta. Solo menciona error de captura ante valores matemáticamente imposibles.\n"
        . "8. ACTIVIDAD DE HOY: 'registros_hoy' indica movimientos con fecha de hoy. Si es 0, las empleadas no han registrado nada hoy (o usaron otra fecha).\n"
        . "10. BÚSQUEDA POR NOMBRE — MUY IMPORTANTE:\n"
        . "    Ignora títulos como 'ingeniero', 'contador', 'doctor', 'lic', 'ing' para buscar el nombre real.\n"
        . "    Busca coincidencias parciales (case-insensitive) en 'catalogo_recibe' y 'catalogo_cargado'.\n"
        . "    REGLA DE ORO — decide según cuántas coincidencias encuentras:\n"
        . "    - EXACTAMENTE 1 coincidencia → da los datos directamente sin pedir confirmación.\n"
        . "      * Los datos están en 'pagos_persona_detalle'. Cada entrada tiene los siguientes campos:\n"
        . "        'hoy' — pagos del día de hoy.\n"
        . "        'mes_actual' — pagos del mes en curso.\n"
        . "        'mes_anterior' — pagos del mes pasado.\n"
        . "        'anio' — pagos acumulados del año actual.\n"
        . "        'historico' — pagos de los últimos 12 meses.\n"
        . "        'por_mes' — arreglo con detalle completo por mes del año actual. Cada entrada tiene: 'mes', 'total_pagado', y 'pagos' (lista de transacciones con 'id', 'fecha', 'concepto', 'monto').\n"
        . "        'periodos' — etiquetas de texto legibles para cada período.\n"
        . "      * REGLA CRÍTICA: usa el campo que corresponde EXACTAMENTE al período que pregunta el usuario:\n"
        . "        'hoy' / 'esta semana hoy' → usa 'hoy'\n"
        . "        'este mes' / 'en marzo' (mes actual) → usa 'mes_actual'\n"
        . "        'el mes pasado' / 'en febrero' (mes anterior) → usa 'mes_anterior'\n"
        . "        'en enero' / 'en [mes específico]' → busca en 'por_mes' la entrada con ese 'mes'\n"
        . "      * Si el usuario pregunta qué día fue un pago o cuál es el ID: busca en 'por_mes[mes].pagos' y lista cada transacción con su fecha, concepto e ID.\n"
        . "        'este año' / 'en 2026' → usa 'anio'\n"
        . "        'últimos meses' / sin período → usa 'historico'\n"
        . "      * NUNCA uses 'anio' para responder 'este mes'. NUNCA uses 'historico' si el usuario pregunta un mes específico.\n"
        . "      * Si el campo del período solicitado tiene total_pagado = '0.00': di que no hay pagos en ese período Y a continuación muestra un resumen de los meses donde SÍ hay pagos usando 'por_mes' (solo los meses con total_pagado > '0.00'). Ejemplo: 'En marzo no hay pagos, pero en enero se registraron 3 pagos por 25,000 pesos (IDs: 111, 222, 333).'\n"
        . "      * Si 'pagos_persona_detalle' está vacío o ausente: di que no se registraron pagos a esa persona.\n"
        . "      * NUNCA digas 'no tengo ese detalle' si la persona existe en el catálogo.\n"
        . "    - 2 o más coincidencias → escribe una línea breve explicando que encontraste varias personas y AL FINAL incluye obligatoriamente esta etiqueta en su propia línea:\n"
        . "      [CANDIDATOS: Nombre Exacto 1 | Nombre Exacto 2 | Nombre Exacto 3]\n"
        . "      Los nombres dentro de la etiqueta deben ser EXACTAMENTE como aparecen en el catálogo.\n"
        . "    - 0 coincidencias → di 'No encontré a [nombre] en el sistema.' y sugiere buscar con un nombre más corto o sin título (ej: solo 'Alexis' en vez de 'el ingeniero Alexis').\n"
        . "    NUNCA digas 'No tengo ese detalle' cuando hay coincidencias en los catálogos.\n"
        . "9. SUGERENCIAS DE MEJORA: Si detectas un patrón que podría resolverse con una mejora en el sistema, inclúyela AL FINAL con este formato exacto (una sola línea):\n"
        . "   [SUGERENCIA_DEV: descripción breve | justificación: por qué mejoraría el sistema]\n"
        . "   Solo cuando sea genuinamente relevante, no en cada respuesta.\n\n"
        . "CONTEXTO ACTUAL DE CAJA CHICA:\n"
        . $ctx_json;

    // Llamar al proveedor activo
    $resultado = null;
    switch (AI_PROVIDER) {
        case 'claude':
            $resultado = callClaude($system_prompt, $mensaje);
            break;
        case 'gemini':
            $resultado = callGemini($system_prompt, $mensaje);
            break;
        case 'groq':
        default:
            $resultado = callGroq($system_prompt, $mensaje);
            break;
    }

    if (isset($resultado['error']) && $resultado['error']) {
        if (!empty($resultado['rate_limit'])) {
            $response = array(
                'type'    => 'RATE_LIMIT',
                'message' => 'Esta versión demo usa IA gratuita con límite de mensajes por minuto. Espera unos segundos e intenta de nuevo. 🙏',
            );
            if (ENVIRONMENT === 'dev') {
                $response['debug'] = $resultado['message'];
            }
            return json_encode($response);
        }
        return json_encode(array('type' => 'ERROR', 'message' => $resultado['message']));
    }

    $respuesta_texto = trim($resultado['message']);
    $tokens          = (int)(isset($resultado['tokens']) ? $resultado['tokens'] : 0);
    $modelo          = isset($resultado['modelo']) ? $resultado['modelo'] : AI_ACTIVE_MODEL;

    // Extraer y guardar sugerencia de mejora (invisible para el dueño)
    $sugerencia_guardada = false;
    if (preg_match('/\[SUGERENCIA_DEV:\s*(.+?)\s*\|\s*justificaci[o\xc3\xb3]n:\s*(.+?)\]/isu', $respuesta_texto, $m)) {
        $sug_texto       = trim($m[1]);
        $sug_justif      = trim($m[2]);
        $respuesta_texto = trim(preg_replace('/\[SUGERENCIA_DEV:.*?\]/isu', '', $respuesta_texto));

        $stmt_sug = $conexion->prepare("INSERT INTO ai_sugerencias (sugerencia, justificacion, origen) VALUES (?, ?, ?)");
        $stmt_sug->execute(array($sug_texto, $sug_justif, $mensaje));
        $sugerencia_guardada = true;
    }

    // Log de conversación
    $stmt_log = $conexion->prepare("INSERT INTO ai_conversaciones (pregunta, respuesta, proveedor_ia, modelo, tokens) VALUES (?, ?, ?, ?, ?)");
    $stmt_log->execute(array($mensaje, $respuesta_texto, AI_PROVIDER, $modelo, $tokens));

    // Actualizar uso diario
    $stmt_uso = $conexion->prepare("
        INSERT INTO ai_uso_diario (fecha, proveedor_ia, tokens_usados, requests)
        VALUES (CURDATE(), ?, ?, 1)
        ON DUPLICATE KEY UPDATE
            tokens_usados = tokens_usados + VALUES(tokens_usados),
            requests      = requests + 1
    ");
    $stmt_uso->execute(array(AI_PROVIDER, $tokens));

    return json_encode(array(
        'type'                => 'SUCCESS',
        'action'              => 'CONTINUE',
        'message'             => $respuesta_texto,
        'show_chart'          => $show_chart,
        'sugerencia_guardada' => $sugerencia_guardada,
        'uso'                 => getUsoActual($conexion),
    ));
}

// ─────────────────────────────────────────────
// Case: marcarSugerencia
// ─────────────────────────────────────────────
function marcarSugerencia()
{
    if (!isset($_SESSION['nivel']) || $_SESSION['nivel'] != 1) {
        return json_encode(array('type' => 'ERROR', 'message' => 'Acceso denegado.'));
    }
    $id = (int)(isset($_POST['id']) ? $_POST['id'] : 0);
    if ($id <= 0) {
        return json_encode(array('type' => 'ERROR', 'message' => 'ID inválido.'));
    }
    $conexion = conectar();
    $stmt = $conexion->prepare("UPDATE ai_sugerencias SET revisada = 1 WHERE id = ?");
    $stmt->execute(array($id));
    return json_encode(array(
        'type'    => 'SUCCESS',
        'action'  => 'DELETE',
        'message' => 'Sugerencia marcada como revisada.',
    ));
}

// ─────────────────────────────────────────────
// Helper: uso actual del día
// ─────────────────────────────────────────────
function getUsoActual(PDO $conexion)
{
    $stmt = $conexion->prepare("SELECT tokens_usados, requests FROM ai_uso_diario WHERE fecha = CURDATE() AND proveedor_ia = ?");
    $stmt->execute(array(AI_PROVIDER));
    $uso = $stmt->fetch(PDO::FETCH_ASSOC);
    return array(
        'tokens_usados'   => (int)(isset($uso['tokens_usados']) ? $uso['tokens_usados'] : 0),
        'requests'        => (int)(isset($uso['requests']) ? $uso['requests'] : 0),
        'requests_limite' => AI_REQUESTS_LIMITE,
        'proveedor'       => AI_PROVIDER,
        'modelo'          => AI_ACTIVE_MODEL,
    );
}
