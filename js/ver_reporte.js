// Obtenemos el id del historial
const id_vehiculo_historial = getUrlParameter('id_vehiculo_historial');

// ----------------------- FUNCIONES INICIALES ----------------------- //
$(document).ready(function () {
    let datos = new FormData();
    datos.append('opcion', 'getHistoryReport');
    datos.append('id_vehiculo_historial', id_vehiculo_historial);
    variableId('folio').innerHTML = id_vehiculo_historial;

    try {
        fetch('functions/select_general.php', {
            method: 'POST',
            body: datos
        })
            .then(response => response.json())
            .then(data => {
                if (data.type === 'SUCCESS') {
                    const datos = data.response[0];

                    // Actualiza la información de encabezado
                    variableId('creado').innerHTML = formatearFecha(datos.creado);
                    variableId('hora').innerHTML = formatearFecha(datos.creado, 2);
                    variableId('concepto').innerHTML = datos.observacion;

                    // Asegúrate de que la tabla tenga un tbody
                    let tableBody = document.getElementById('tableStock');
                    tableBody.innerHTML = ''; // Limpia el contenido anterior del tbody si existe

                    // Itera sobre los archivos (o datos relacionados) y crea filas
                    datos.archivos.forEach(item => {console.log(item)
                        const row = document.createElement('tr');
                        row.innerHTML = `
                        <td class="text-center">${item.file_name}${item.file_path}</td>
                        <td class="text-center">${datos.fecha_manual && datos.fecha_manual !== '0000-00-00' ? formatearFecha(datos.fecha_manual) : 'N/A'}</td>
                    `;
                        tableBody.appendChild(row);
                    });
                } else {
                    // Mensaje de advertencia si no hay datos
                    alertNotify('2000', 'warning', 'Sin datos', result.message, 'bottom-end');
                }
            });
    } catch (error) {
        console.error('Error al obtener el reporte:', error);
        alertNotify('2000', 'error', 'Error', 'Hubo un error al obtener el reporte.', 'bottom-end');
    }
});
