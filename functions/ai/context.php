<?php
/**
 * context.php — Construye el contexto de datos para el Asistente IA
 * Solo SELECT — nunca escribe en la BD.
 * Compatible PHP 5.6+
 */

function buildContext(PDO $conexion)
{
    $mes_actual   = date('Y-m');
    $anio_actual  = date('Y');
    $mes_anterior = date('Y-m', strtotime('-1 month'));

    $context = array();

    // Mapas de traducción de meses (MySQL devuelve nombres en inglés)
    $meses_abrev = array(
        'Jan'=>'Ene','Feb'=>'Feb','Mar'=>'Mar','Apr'=>'Abr',
        'May'=>'May','Jun'=>'Jun','Jul'=>'Jul','Aug'=>'Ago',
        'Sep'=>'Sep','Oct'=>'Oct','Nov'=>'Nov','Dec'=>'Dic',
    );
    $meses_full = array(
        'January'=>'Enero','February'=>'Febrero','March'=>'Marzo',
        'April'=>'Abril','May'=>'Mayo','June'=>'Junio',
        'July'=>'Julio','August'=>'Agosto','September'=>'Septiembre',
        'October'=>'Octubre','November'=>'Noviembre','December'=>'Diciembre',
    );
    $meses_es = array(
        1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',
        5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',
        9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre',
    );

    // 1. Totales del mes actual
    $stmt = $conexion->prepare("
        SELECT
            COALESCE(SUM(ingreso), 0) AS total_ingreso,
            COALESCE(SUM(egreso), 0)  AS total_egreso,
            COUNT(*)                  AS total_registros
        FROM caja_chica
        WHERE band_eliminar = 1
          AND DATE_FORMAT(fecha, '%Y-%m') = ?
    ");
    $stmt->execute(array($mes_actual));
    $totales = $stmt->fetch(PDO::FETCH_ASSOC);

    $context['fecha_hoy']         = date('d/m/Y');
    $context['anio_actual']       = $anio_actual;
    $context['mes_actual']        = $meses_es[(int)date('n')] . ' ' . $anio_actual;
    $context['total_ingreso_mes'] = number_format((float)$totales['total_ingreso'], 2);
    $context['total_egreso_mes']  = number_format((float)$totales['total_egreso'], 2);
    $context['transacciones_mes'] = (int)$totales['total_registros'];

    // 2. Saldo actual — suma acumulada histórica directa sobre caja_chica
    $stmt2 = $conexion->query("
        SELECT COALESCE(SUM(ingreso), 0) - COALESCE(SUM(egreso), 0) AS saldo_real
        FROM caja_chica
        WHERE band_eliminar = 1
    ");
    $saldo_row = $stmt2->fetch(PDO::FETCH_ASSOC);
    $context['saldo_actual'] = number_format((float)$saldo_row['saldo_real'], 2);

    // 3. Transacciones del año
    $stmt3 = $conexion->prepare("SELECT COUNT(*) AS total FROM caja_chica WHERE band_eliminar = 1 AND YEAR(fecha) = ?");
    $stmt3->execute(array($anio_actual));
    $context['transacciones_anio'] = (int)$stmt3->fetchColumn();

    // 4. Top 5 tipos de gasto del mes
    $stmt4 = $conexion->prepare("
        SELECT mtg.nombre AS tipo_gasto, SUM(cc.egreso) AS total_egreso
        FROM caja_chica cc
        JOIN modelo_chica_tipo_gasto mtg ON cc.id_tipo_gasto = mtg.id
        WHERE cc.band_eliminar = 1
          AND DATE_FORMAT(cc.fecha, '%Y-%m') = ?
          AND cc.egreso > 0
        GROUP BY mtg.nombre
        ORDER BY total_egreso DESC
        LIMIT 5
    ");
    $stmt4->execute(array($mes_actual));
    $context['top_gastos'] = $stmt4->fetchAll(PDO::FETCH_ASSOC);

    // 5. Tendencia anual (para mini gráfica)
    $stmt5 = $conexion->prepare("
        SELECT DATE_FORMAT(fecha, '%b') AS mes,
               MONTH(fecha)             AS numero_mes,
               COALESCE(SUM(ingreso), 0) AS ingreso,
               COALESCE(SUM(egreso), 0)  AS egreso
        FROM caja_chica
        WHERE band_eliminar = 1 AND YEAR(fecha) = ?
        GROUP BY MONTH(fecha), DATE_FORMAT(fecha, '%b')
        ORDER BY MONTH(fecha)
    ");
    $stmt5->execute(array($anio_actual));
    $tendencia = $stmt5->fetchAll(PDO::FETCH_ASSOC);
    foreach ($tendencia as &$t) {
        $t['mes'] = isset($meses_abrev[$t['mes']]) ? $meses_abrev[$t['mes']] : $t['mes'];
    }
    unset($t);
    $context['tendencia_anual'] = $tendencia;

    // 6. Últimas 5 transacciones con nombres reales (reducido para ahorrar tokens)
    $stmt6 = $conexion->query("
        SELECT DATE_FORMAT(cc.fecha, '%d/%m/%Y') AS fecha,
               cc.concepto,
               COALESCE(mc.nombre, 'Sin asignar') AS cargado_a,
               COALESCE(mr.nombre, 'Sin asignar') AS recibe,
               cc.ingreso, cc.egreso
        FROM caja_chica cc
        LEFT JOIN modelo_chica_cargado mc ON cc.id_cargado = mc.id
        LEFT JOIN modelo_chica_recibe   mr ON cc.id_recibe  = mr.id
        WHERE cc.band_eliminar = 1
        ORDER BY cc.fecha DESC, cc.id_caja DESC
        LIMIT 5
    ");
    $context['ultimas_transacciones'] = $stmt6->fetchAll(PDO::FETCH_ASSOC);

    // 7. Catálogos de personas con nombres reales (para búsqueda por nombre)
    $context['total_areas'] = (int)$conexion->query("SELECT COUNT(*) FROM modelo_chica_area WHERE band_eliminar = 1")->fetchColumn();

    // Función local: quita acentos para que el AI no los pierda al reescribir nombres
    $quitarAcentos = function($str) {
        $bus = array('á','é','í','ó','ú','Á','É','Í','Ó','Ú','ñ','Ñ','ü','Ü');
        $rep = array('a','e','i','o','u','A','E','I','O','U','n','N','u','U');
        return str_replace($bus, $rep, $str);
    };

    // Catálogos — nombres normalizados (sin acentos) para evitar mismatch con el AI
    $raw_cargado = $conexion->query(
        "SELECT nombre FROM modelo_chica_cargado WHERE band_eliminar = 1 ORDER BY nombre"
    )->fetchAll(PDO::FETCH_COLUMN);
    $context['catalogo_cargado'] = array_map($quitarAcentos, $raw_cargado);
    $context['total_personas']   = count($context['catalogo_cargado']);

    $raw_recibe = $conexion->query(
        "SELECT nombre FROM modelo_chica_recibe WHERE band_eliminar = 1 ORDER BY nombre"
    )->fetchAll(PDO::FETCH_COLUMN);
    $context['catalogo_recibe'] = array_map($quitarAcentos, $raw_recibe);

    // Helper para normalizar y mapear pagos
    $normalizarPagos = function($rows) use ($quitarAcentos) {
        foreach ($rows as &$p) {
            $p['persona'] = $quitarAcentos($p['persona']);
        }
        unset($p);
        return $rows;
    };

    // Pagos por persona — año actual
    $stmt_pag_anio = $conexion->prepare("
        SELECT mr.nombre                   AS persona,
               COUNT(*)                    AS transacciones,
               COALESCE(SUM(cc.egreso), 0) AS total_pagado
        FROM caja_chica cc
        JOIN modelo_chica_recibe mr ON cc.id_recibe = mr.id
        WHERE cc.band_eliminar = 1
          AND cc.egreso > 0
          AND YEAR(cc.fecha) = ?
        GROUP BY mr.nombre
        ORDER BY total_pagado DESC
    ");
    $stmt_pag_anio->execute(array($anio_actual));
    $context['pagos_por_persona_anio']    = $normalizarPagos($stmt_pag_anio->fetchAll(PDO::FETCH_ASSOC));
    $context['pagos_persona_anio_label']  = 'del 01/01/' . $anio_actual . ' al ' . date('d/m/Y');

    // Pagos por persona — últimos 12 meses
    $stmt_pag = $conexion->query("
        SELECT mr.nombre                   AS persona,
               COUNT(*)                    AS transacciones,
               COALESCE(SUM(cc.egreso), 0) AS total_pagado
        FROM caja_chica cc
        JOIN modelo_chica_recibe mr ON cc.id_recibe = mr.id
        WHERE cc.band_eliminar = 1
          AND cc.egreso > 0
          AND cc.fecha >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
        GROUP BY mr.nombre
        ORDER BY total_pagado DESC
    ");
    $context['pagos_por_persona']       = $normalizarPagos($stmt_pag->fetchAll(PDO::FETCH_ASSOC));
    $context['pagos_persona_periodo']   = array(
        'desde' => date('d/m/Y', strtotime('-12 months')),
        'hasta' => date('d/m/Y'),
    );

    // 8a. Mejor mes del año actual
    $stmt8a = $conexion->prepare("
        SELECT DATE_FORMAT(fecha, '%M') AS mes,
               YEAR(fecha)              AS anio,
               COALESCE(SUM(ingreso), 0) AS total
        FROM caja_chica
        WHERE band_eliminar = 1
          AND ingreso > 0
          AND YEAR(fecha) = ?
        GROUP BY MONTH(fecha)
        ORDER BY total DESC LIMIT 1
    ");
    $stmt8a->execute(array($anio_actual));
    $mejor_anio = $stmt8a->fetch(PDO::FETCH_ASSOC);
    if ($mejor_anio) {
        $mejor_anio['mes'] = isset($meses_full[$mejor_anio['mes']]) ? $meses_full[$mejor_anio['mes']] : $mejor_anio['mes'];
    }
    $context['mejor_mes_anio_actual'] = $mejor_anio
        ? array('mes' => $mejor_anio['mes'], 'total' => number_format((float)$mejor_anio['total'], 2))
        : null;

    // 8b. Mejor mes histórico — últimos 12 meses (para comparativas cross-año)
    $stmt8b = $conexion->prepare("
        SELECT DATE_FORMAT(fecha, '%M') AS mes,
               YEAR(fecha)              AS anio,
               COALESCE(SUM(ingreso), 0) AS total
        FROM caja_chica
        WHERE band_eliminar = 1
          AND ingreso > 0
          AND fecha >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
        GROUP BY YEAR(fecha), MONTH(fecha)
        ORDER BY total DESC LIMIT 1
    ");
    $stmt8b->execute(array());
    $mejor_hist = $stmt8b->fetch(PDO::FETCH_ASSOC);
    if ($mejor_hist) {
        $mejor_hist['mes'] = isset($meses_full[$mejor_hist['mes']]) ? $meses_full[$mejor_hist['mes']] : $mejor_hist['mes'];
    }
    $context['mejor_mes_historico'] = $mejor_hist
        ? array(
            'mes'   => $mejor_hist['mes'],
            'anio'  => $mejor_hist['anio'],
            'total' => number_format((float)$mejor_hist['total'], 2),
          )
        : null;

    $stmt9 = $conexion->prepare("
        SELECT ma.nombre AS area, COUNT(*) AS total
        FROM caja_chica cc
        JOIN modelo_chica_area ma ON cc.id_area = ma.id
        WHERE cc.band_eliminar = 1 AND DATE_FORMAT(cc.fecha, '%Y-%m') = ?
        GROUP BY ma.nombre ORDER BY total DESC LIMIT 1
    ");
    $stmt9->execute(array($mes_actual));
    $area_row = $stmt9->fetch(PDO::FETCH_ASSOC);
    $context['area_mas_activa'] = $area_row ? $area_row : null;

    // Racha de meses con saldo positivo
    $stmt10 = $conexion->prepare("
        SELECT DATE_FORMAT(fecha, '%Y-%m') AS mes,
               COALESCE(SUM(ingreso), 0) - COALESCE(SUM(egreso), 0) AS saldo_mes
        FROM caja_chica
        WHERE band_eliminar = 1 AND YEAR(fecha) = ?
        GROUP BY DATE_FORMAT(fecha, '%Y-%m')
        ORDER BY mes DESC
    ");
    $stmt10->execute(array($anio_actual));
    $racha = 0;
    foreach ($stmt10->fetchAll(PDO::FETCH_ASSOC) as $m) {
        if ((float)$m['saldo_mes'] > 0) $racha++;
        else break;
    }
    $context['meses_saldo_positivo'] = $racha;

    // 9. Comparativa mes actual vs mes anterior (análisis de ahorro)
    $stmt11 = $conexion->prepare("
        SELECT mtg.nombre AS tipo_gasto,
               COALESCE(SUM(CASE WHEN DATE_FORMAT(cc.fecha, '%Y-%m') = ? THEN cc.egreso ELSE 0 END), 0) AS mes_actual,
               COALESCE(SUM(CASE WHEN DATE_FORMAT(cc.fecha, '%Y-%m') = ? THEN cc.egreso ELSE 0 END), 0) AS mes_anterior
        FROM caja_chica cc
        JOIN modelo_chica_tipo_gasto mtg ON cc.id_tipo_gasto = mtg.id
        WHERE cc.band_eliminar = 1
          AND cc.egreso > 0
          AND DATE_FORMAT(cc.fecha, '%Y-%m') IN (?, ?)
        GROUP BY mtg.nombre
        HAVING mes_actual > 0 OR mes_anterior > 0
        ORDER BY mes_actual DESC
    ");
    $stmt11->execute(array($mes_actual, $mes_anterior, $mes_actual, $mes_anterior));
    $comparativa = $stmt11->fetchAll(PDO::FETCH_ASSOC);

    foreach ($comparativa as &$row) {
        $row['diferencia']        = round((float)$row['mes_actual'] - (float)$row['mes_anterior'], 2);
        $row['porcentaje_cambio'] = $row['mes_anterior'] > 0
            ? round((((float)$row['mes_actual'] - (float)$row['mes_anterior']) / (float)$row['mes_anterior']) * 100, 1)
            : null;
    }
    unset($row);
    $context['comparativa_gastos'] = $comparativa;

    // 10. Actividad del día de hoy (para saber si las empleadas usaron el sistema)
    $stmt12 = $conexion->prepare("
        SELECT COUNT(*)                  AS total,
               COALESCE(SUM(ingreso), 0) AS ingreso_hoy,
               COALESCE(SUM(egreso), 0)  AS egreso_hoy
        FROM caja_chica
        WHERE band_eliminar = 1 AND DATE(fecha) = CURDATE()
    ");
    $stmt12->execute(array());
    $hoy = $stmt12->fetch(PDO::FETCH_ASSOC);
    $context['registros_hoy'] = (int)$hoy['total'];
    $context['ingreso_hoy']   = number_format((float)$hoy['ingreso_hoy'], 2);
    $context['egreso_hoy']    = number_format((float)$hoy['egreso_hoy'], 2);

    return $context;
}
