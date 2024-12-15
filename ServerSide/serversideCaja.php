<?php
header('Content-Type: application/json');

// Incluir el archivo de configuración
require('../functions/config.php'); // Importa las configuraciones globales
$config = DB_CONFIG[ENVIRONMENT];

// Definir las constantes para la conexión
define("HOST_SS", $config['server']);
define("USER_SS", $config['user']);
define("PASSWORD_SS", $config['pass']);
define("DATABASE_SS", $config['db']);

// Establecer la conexión con la base de datos
$mysqli = new mysqli(HOST_SS, USER_SS, PASSWORD_SS, DATABASE_SS);
$mysqli->set_charset('utf8');

// Verificar si la conexión fue exitosa
if ($mysqli->connect_error) {
    die(json_encode(["error" => "Error de conexión: " . $mysqli->connect_error]));
}

// Obtener parámetros enviados por DataTables
$draw = isset($_POST['draw']) ? intval($_POST['draw']) : 0;
$start = isset($_POST['start']) ? intval($_POST['start']) : 0;
$length = isset($_POST['length']) ? intval($_POST['length']) : 10;
$searchValue = isset($_POST['search']['value']) ? $_POST['search']['value'] : '';
$orderColumnIndex = isset($_POST['order'][0]['column']) ? intval($_POST['order'][0]['column']) : 0;
$orderDir = isset($_POST['order'][0]['dir']) ? $_POST['order'][0]['dir'] : 'asc';

// Columnas disponibles
$columns = [
    "id_caja", "fecha", "cargado", "area", "folio", "empresa", 
    "entrega", "tipo_ingreso", "tipo_gasto", "autoriza", 
    "concepto", "proveedor", "recibe", "unidad", 
    "operador", "comprobante", "factura", "ingreso", "egreso", "saldo"
];

// Validar columna de orden
$orderColumn = isset($columns[$orderColumnIndex]) ? $columns[$orderColumnIndex] : $columns[0];

// Construir consulta base
$sql = "SELECT * FROM vista_caja WHERE 1=1";

// Filtro de búsqueda en columnas específicas
foreach ($columns as $index => $column) {
    $columnSearch = isset($_POST['columns'][$index]['search']['value']) ? $_POST['columns'][$index]['search']['value'] : '';
    if (!empty($columnSearch)) {
        $sql .= " AND $column LIKE '%" . $mysqli->real_escape_string($columnSearch) . "%'";
    }
}

// Contar registros totales sin filtro
$totalRecordsQuery = $mysqli->query("SELECT COUNT(*) as count FROM vista_caja");
$totalRecords = $totalRecordsQuery->fetch_assoc()['count'];

// Contar registros filtrados
$totalFilteredQuery = $mysqli->query($sql);
$totalFiltered = $totalFilteredQuery->num_rows;

// Ordenar y limitar resultados
$sql .= " ORDER BY $orderColumn $orderDir LIMIT $start, $length";

// Obtener resultados
$query = $mysqli->query($sql);
$data = [];

while ($row = $query->fetch_assoc()) {
    $data[] = [
        $row['id_caja'], $row['fecha'], $row['cargado'], $row['area'], 
        $row['folio'], $row['empresa'], $row['entrega'], $row['tipo_ingreso'], 
        $row['tipo_gasto'], $row['autoriza'], $row['concepto'], $row['proveedor'], 
        $row['recibe'], $row['unidad'], $row['operador'], $row['comprobante'], 
        $row['factura'], $row['ingreso'], $row['egreso'], $row['saldo']
    ];
}

// Formar respuesta JSON
$response = [
    "draw" => intval($draw),
    "recordsTotal" => intval($totalRecords),
    "recordsFiltered" => intval($totalFiltered),
    "data" => $data,
];

// Cerrar conexión y enviar respuesta
echo json_encode($response);
$mysqli->close();
?>
