<?php
header('Content-Type: application/json');

require('../config/config.php');
$config = DB_CONFIG[ENVIRONMENT];

$dsn = 'mysql:host=' . $config['server'] . ';dbname=' . $config['db'] . ';charset=utf8mb4';

try {
    $pdo = new PDO($dsn, $config['user'], $config['pass'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    die(json_encode(["error" => "Error de conexión: " . $e->getMessage()]));
}

// Obtener parámetros enviados por DataTables
$draw             = isset($_POST['draw'])                  ? intval($_POST['draw'])                  : 0;
$start            = isset($_POST['start'])                 ? intval($_POST['start'])                 : 0;
$length           = isset($_POST['length'])                ? intval($_POST['length'])                : 10;
$searchValue      = isset($_POST['search']['value'])       ? $_POST['search']['value']               : '';
$orderColumnIndex = isset($_POST['order'][0]['column'])    ? intval($_POST['order'][0]['column'])    : 0;
$orderDir         = isset($_POST['order'][0]['dir'])       ? $_POST['order'][0]['dir']               : 'asc';

// Columnas disponibles
$columns = [
    "id_caja", "fecha", "cargado", "area", "folio", "empresa",
    "entrega", "tipo_ingreso", "tipo_gasto", "autoriza",
    "concepto", "proveedor", "recibe", "unidad",
    "operador", "comprobante", "factura", "ingreso", "egreso", "saldo"
];

// Validar columna y dirección de orden
$orderColumn = isset($columns[$orderColumnIndex]) ? $columns[$orderColumnIndex] : $columns[0];
$orderDir    = in_array(strtolower($orderDir), ['asc', 'desc']) ? $orderDir : 'asc';

// Construir consulta base
$sql    = "SELECT * FROM vista_caja WHERE 1=1";
$params = [];

// Filtro de búsqueda por columna
foreach ($columns as $index => $column) {
    $columnSearch = isset($_POST['columns'][$index]['search']['value']) ? $_POST['columns'][$index]['search']['value'] : '';
    if (!empty($columnSearch)) {
        $sql      .= " AND $column LIKE ?";
        $params[]  = '%' . $columnSearch . '%';
    }
}

// Contar registros totales sin filtro
$totalRecords = $pdo->query("SELECT COUNT(*) FROM vista_caja")->fetchColumn();

// Contar registros filtrados
$countStmt = $pdo->prepare($sql);
$countStmt->execute($params);
$totalFiltered = $countStmt->rowCount();

// Ordenar y limitar resultados
$sql .= " ORDER BY $orderColumn $orderDir LIMIT ?, ?";
$params[] = $start;
$params[] = $length;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$data = [];
while ($row = $stmt->fetch()) {
    $data[] = [
        $row['id_caja'],    $row['fecha'],       $row['cargado'],    $row['area'],
        $row['folio'],      $row['empresa'],     $row['entrega'],    $row['tipo_ingreso'],
        $row['tipo_gasto'], $row['autoriza'],    $row['concepto'],   $row['proveedor'],
        $row['recibe'],     $row['unidad'],      $row['operador'],   $row['comprobante'],
        $row['factura'],    $row['ingreso'],     $row['egreso'],     $row['saldo']
    ];
}

echo json_encode([
    "draw"            => intval($draw),
    "recordsTotal"    => intval($totalRecords),
    "recordsFiltered" => intval($totalFiltered),
    "data"            => $data,
]);
?>
