// ----------------------- VALIDACIONES ----------------------- //
validateInput('input', 'modal_vehicle_edit_year', { max: 4, pattern: '^[0-9]*$' });
validateInput('input', 'modal_vehicle_edit_serie', { max: 17, pattern: '^[a-zA-Z0-9]*$' });
validateInput('input', 'modal_vehicle_edit_engine', { max: 20, pattern: '^[a-zA-Z0-9]*$' });
validateInput('input', 'modal_vehicle_edit_pedimento', { max: 15, pattern: '^[a-zA-Z0-9]*$' });
validateInput('input', 'modal_vehicle_edit_license_plate', { max: 7, pattern: '^[a-zA-Z0-9]*$' });
validateInput('input', 'modal_vehicle_edit_observations', { max: 500 });

// Obtenemos el id del vehículo
const vehicleId = getUrlParameter('vehicle_id');

// Obtenemos la información del vehículo
async function setInitialData() {
    variableId("modal_vehicle_edit_vehicle_id").value = vehicleId;

    const loader = document.getElementById('form-loader');
    const formContent = document.getElementById('form-vehicle-details');

    // Muestra el loader y oculta el formulario mientras carga
    loader.style.display = 'flex';
    formContent.style.display = 'none';

    try {
        // Obtenemos los datos del vehículo
        const data = await fetchGeneric('getVehicleDetails', vehicleId, 'functions/select_general.php');

        // Datos de la vista
        variableId("vehicle_detail_vehicle_id").value = data.id_vehiculo;
        fetchFillSelect('getBrand', 'vehicle_detail_brand', data.id_marca);
        fetchFillSelect('getModel', 'vehicle_detail_model', data.id_modelo);
        variableId("vehicle_detail_year").value = data.ano;
        fetchFillSelect('getColor', 'vehicle_detail_color', data.color);
        variableId("vehicle_detail_serie").value = data.serie;
        variableId("vehicle_detail_engine").value = data.motor;
        variableId("vehicle_detail_pedimento").value = data.pedimento;
        fetchFillSelect('getOwner', 'vehicle_detail_owner', data.id_propietario);
        variableId("vehicle_detail_license_plate").value = data.placa;
        variableId("vehicle_detail_observations").value = data.observaciones;
        let fechaFormatoFinal = '';
        if (data.fecha_de_entrega) {
            const fechaEntregaCompleta = data.fecha_de_entrega; // "2024-12-06 00:00:00"
            const fechaSolo = fechaEntregaCompleta.split(" ")[0]; // "2024-12-06"
            fechaFormatoFinal = fechaSolo; // "2024-12-06"            
        } else {
            fechaFormatoFinal = '0000-00-00';
        }
        variableId("vehicle_detail_delivery_date").value = fechaFormatoFinal;
        // Subir archivos init
        fetchFillSelect('getFileType', 'vehicle_detail_upload_file');
    } catch (error) {
        console.error("Error al cargar los detalles del vehículo:", error);
    } finally {
        // Oculta el loader y muestra el formulario
        loader.style.display = 'none';
        formContent.style.display = 'flex';
        setTimeout(() => {
            document.querySelector('#vehicle_detail_upload_file-selectized').setAttribute('autocomplete', 'off');
        }, 100);
    }
}

// Listener del modal editar
document.addEventListener('DOMContentLoaded', function (event) {
    $('#modal_edit_vehicle').on('show.bs.modal', function (event) {
        // Datos del modal editar
        let fechaFormatoFinal = '';
        if (data.fecha_de_entrega) {
            const fechaEntregaCompleta = data.fecha_de_entrega; // "2024-12-06 00:00:00"
            const fechaSolo = fechaEntregaCompleta.split(" ")[0]; // "2024-12-06"
            fechaFormatoFinal = fechaSolo; // "2024-12-06"            
        } else {
            fechaFormatoFinal = '0000-00-00';
        }
        fetchFillSelect('getBrand', 'modal_vehicle_edit_brand', data.id_marca);
        fetchFillSelect('getModel', 'modal_vehicle_edit_model', data.id_modelo);
        variableId("modal_vehicle_edit_year").value = data.ano;
        fetchFillSelect('getColor', 'modal_vehicle_edit_color', data.color);
        variableId("modal_vehicle_edit_serie").value = data.serie;
        variableId("modal_vehicle_edit_engine").value = data.motor;
        variableId("modal_vehicle_edit_pedimento").value = data.pedimento;
        fetchFillSelect('getOwner', 'modal_vehicle_edit_owner', data.id_propietario);
        variableId("modal_vehicle_edit_license_plate").value = data.placa;
        variableId("modal_vehicle_edit_observations").value = data.observaciones;
        variableId("modal_vehicle_edit_delivery_date").value = fechaFormatoFinal;
    });
});

// Iniciar switch y ocultar observaciones
$(document).ready(function () {
    // Iniciar switch
    $("input[data-bootstrap-switch]").each(function () {
        $(this).bootstrapSwitch('state', $(this).prop('checked'));
    });

    // Escuchar cambios en el checkbox
    $('#check_visible').on('switchChange.bootstrapSwitch', function (event, state) {
        const observationGroup = document.getElementById('observation-group');
        if (state) {
            observationGroup.style.display = 'block';
            observationGroup.style.opacity = 0;
            setTimeout(() => {
                observationGroup.style.opacity = 1;
            }, 0);
        } else {
            observationGroup.style.opacity = 0;
            setTimeout(() => {
                observationGroup.style.display = 'none';
            }, 500);
        }
    });
});

// Carga de archivos
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('form');
    const fileInput = document.getElementById('file');
    const fileLabel = document.getElementById('file-label');
    const responseDiv = document.getElementById('lista_archivos_cargados');

    fileInput.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) {
            handleFileStatus('reset');
            return;
        }

        const fileName = file.name;
        const ext = fileName.split('.').pop().toLowerCase();

        if (['jpg', 'jpeg', 'png'].includes(ext)) {
            fileLabel.textContent = fileName;
            handleFileStatus('success');
        } else if (ext === 'pdf') {
            fileLabel.textContent = fileName;
            handleFileStatus('success');
        } else {
            handleFileStatus('format');
        }
    });

    form.addEventListener('submit', async function (event) {
        event.preventDefault();

        if (variableSelect("vehicle_detail_upload_file").value == "") {
            alertNotify("1500", "info", "Espera", "Selecciona el tipo de archivo a subir.")
            inputWarning(variableSelect("vehicle_detail_upload_file"), "btn_modal_insertar");
            setTimeout(() => { deleteSpinner('btn_modal_insertar', 'Guardar'); }, 1500);
            return false;
        }

        const file = fileInput.files[0];
        if (!file) {
            handleFileStatus('fail');
            return;
        }

        try {
            // Bloqueamos el botón y ponemos el spinner
            disabled('upload');
            printSpinner('upload', 'Subiendo');

            const fileName = file.name;
            const ext = fileName.split('.').pop().toLowerCase();
            let processedFile;

            if (['jpg', 'jpeg', 'png'].includes(ext)) {
                processedFile = await handleImageUpload(file);
            } else if (ext === 'pdf') {
                processedFile = handlePDFUpload(file);
            } else {
                throw new Error('Formato de archivo no soportado.');
            }

            // Crear FormData con el archivo procesado
            const formData = new FormData();
            const observationField = document.getElementById('vehicle_detail_upload_observations');
            const checkbox = document.getElementById('check_visible');
            var selectize = $('#vehicle_detail_upload_file')[0].selectize; // Acceder al objeto selectize
            var selectedValue = selectize.getValue(); // Obtener el valor seleccionado
            var selectedText = selectize.options[selectedValue] ? selectize.options[selectedValue].text : ''; // Buscar el texto asociado al valor seleccionado
            formData.append('opcion', 'insertFile');
            formData.append('vehicleId', vehicleId);
            formData.append('product_file', processedFile);
            formData.append('type_file_id', selectedValue);
            formData.append('type_file_name', selectedText);
            // Añadir observaciones si el campo está visible
            if (checkbox.checked && observationField.value.trim() !== '') {
                formData.append('observations', observationField.value.trim());
            } else {
                formData.append('observations', '');
            }

            // Enviar archivo al servidor
            const response = await fetch('functions/insert_general.php', {
                method: 'POST',
                body: formData,
            });

            const result = await response.json(); // Suponiendo que el servidor responde con JSON

            if (response.ok && result.type === 'SUCCESS') {
                // Mostrar notificación de éxito
                alertNotify('2000', 'success', 'Guardado', result.message, 'bottom-end');

                // Actualizar la interfaz tras el éxito
                responseDiv.innerHTML = `<p>${result.message}</p>`;
                handleFileStatus('clean');
                observationField.value = '';
                resetSelectize('vehicle_detail_upload_file');
                reloadList();

                // Resetear el grupo de observaciones
                const observationGroup = document.getElementById('observation-group');
                checkbox.checked = false;
                checkbox.dispatchEvent(new Event('change'));
                observationGroup.style.display = 'none';
                observationGroup.style.opacity = 0;
            } else {
                // Manejar errores enviados por el servidor
                const errorMessage = result.message || 'Error desconocido en el servidor.';
                alertNotify('2000', 'error', 'Error', `Error al subir el archivo: ${errorMessage}`, 'bottom-end');
                console.error(`Error del servidor: ${errorMessage}`);
            }
        } catch (error) {
            console.error(error)
            alertNotify('2000', 'error', 'Error', `Error al subir el archivo: ${error.message}`, 'bottom-end');
        }
    });

    function handleFileStatus(status) {
        if (status == 'fail') {
            fileInput.value = '';
            fileLabel.innerHTML = `Selecciona o arrastra un archivo <img src="img/puntero.svg" alt="puntero">`;
            fileLabel.classList.add('fail');
            fileLabel.classList.remove('success');
            disabled('upload');
            printSpinner('upload', '<i class="fa-solid fa-upload pr-2"></i>Subir archivo');
            setTimeout(() => {
                fileLabel.classList.remove('success', 'fail');
                enabled('upload');
                deleteSpinner('upload', '<i class="fa-solid fa-upload pr-2"></i>Subir archivo');
            }, 1500);
        } else if (status == 'format') {
            fileInput.value = '';
            fileLabel.innerHTML = `Formato no valido <img src="img/puntero.svg" alt="puntero">`;
            fileLabel.classList.add('fail');
            fileLabel.classList.remove('success');
            disabled('upload');
            printSpinner('upload', '<i class="fa-solid fa-upload pr-2"></i>Subir archivo');
            setTimeout(() => {
                fileLabel.innerHTML = `Selecciona o arrastra un archivo <img src="img/puntero.svg" alt="puntero">`;
                fileLabel.classList.remove('success', 'fail');
                enabled('upload');
                deleteSpinner('upload', '<i class="fa-solid fa-upload pr-2"></i>Subir archivo');
            }, 1500);
        } else if (status == 'clean') {
            fileInput.value = '';
            fileLabel.innerHTML = `Selecciona o arrastra un archivo <img src="img/puntero.svg" alt="puntero">`;
            fileLabel.classList.remove('success', 'fail');
            enabled('upload');
            deleteSpinner('upload', '<i class="fa-solid fa-upload pr-2"></i>Subir archivo');
        } else if (status == 'reset') {
            fileInput.value = '';
            fileLabel.innerHTML = `Selecciona o arrastra un archivo <img src="img/puntero.svg" alt="puntero">`;
            fileLabel.classList.remove('success', 'fail');
        } else if (status == 'success') {
            fileLabel.classList.add('success');
            fileLabel.classList.remove('fail');
        }
    }

    async function handleImageUpload(file) {
        const options = {
            maxSizeMB: 1, // Tamaño máximo en MB
            maxWidthOrHeight: 800, // Dimensión máxima
            useWebWorker: true,
        };

        try {
            // Comprimir imagen
            const compressedFile = await imageCompression(file, options);
            return compressedFile;
        } catch (error) {
            handleFileStatus('clean');
            throw new Error('Error al comprimir la imagen: ' + error.message);
        }
    }

    function handlePDFUpload(file) {
        const maxSizeMB = 5; // Tamaño máximo permitido para PDFs en MB
        const maxFileSize = maxSizeMB * 1024 * 1024; // Convertir a bytes

        if (file.size > maxFileSize) {
            handleFileStatus('clean');
            throw new Error(`El archivo PDF excede el tamaño máximo de ${maxSizeMB} MB.`);
        }

        return file;
    }
});

async function reloadList() {
    setInitialData();
    fetchHistory();

    const responseDiv = document.getElementById('lista_archivos_cargados');
    const loader = document.getElementById('loader');
    const noFiles = document.getElementById('no-files');

    // Mostrar el loader
    loader.style.display = 'flex';
    noFiles.style.display = 'none';
    responseDiv.style.display = 'none';

    try {
        // Solicita los archivos al servidor
        const formData = new FormData();
        formData.append('opcion', 'getVehicleFiles');
        formData.append('vehicleId', vehicleId);
        const response = await fetch('functions/select_general.php', {
            method: 'POST',
            body: formData,
        });
        if (!response.ok) throw new Error('Error al obtener los archivos.');

        const files = await response.json();

        // Ocultar el loader y mostrar la lista
        loader.style.display = 'none';
        responseDiv.style.display = 'block';

        // Limpia el contenido existente
        responseDiv.innerHTML = '';

        // Objeto para mapear extensiones a clases CSS
        const extensionClasses = {
            png: 'primary',
            jpg: 'success',
            jpeg: 'success',
            pdf: 'danger'
        };

        // Objeto para mapear status a clases CSS
        const statusClasses = {
            '0': 'secondary',
            '1': 'success'
        };

        if (files.length === 0) {
            // Si no hay archivos, mostrar mensaje de "Sin archivos"
            noFiles.style.display = 'flex';
            responseDiv.style.display = 'none';
            return;
        }

        // Itera sobre los archivos y los agrega al DOM
        files.forEach(file => {
            // Determinar la clase según la extensión o usar 'secondary' por defecto
            const badgeClass = extensionClasses[file.extension.toLowerCase()] || 'secondary';
            const statusClass = statusClasses[file.is_active] || '-';

            const fileElement = `
                <div class="border rounded d-flex justify-content-between align-items-center mb-1 py-0 px-1">
                    <div class="align-middle">
                        <span class="badge badge-${statusClass}">${file.is_active == '1' ? '✓' : '✘'}</span>
                        <span class="font-weight-bold">${file.file_name}</span>
                        <span class="badge badge-${badgeClass} ml-2">${file.extension.toUpperCase()}</span>
                    </div>
                    <div class="d-flex flex-column flex-md-row text-nowrap">
                        <button class="btn btn-secondary btn-sm mr-0 mr-md-2" onclick="viewFile('${file.file_path + file.file_name}')"><i class="fa-solid fa-file pr-2"></i>Ver archivo</button>
                        <button class="btn btn-warning btn-sm mr-0 mr-md-2" onclick="downloadFile('${file.file_path + file.file_name}')"><i class="fa-solid fa-download pr-2"></i>Descargar</button>
                        <button class="btn btn-danger btn-sm mr-0" onclick="deleteFile('${file.file_path}${file.file_name}', '${file.file_name}', ${file.id})"><i class="fa-solid fa-trash pr-2"></i>Borrar</button>
                    </div>
                </div>`;
            responseDiv.insertAdjacentHTML('beforeend', fileElement);
        });
    } catch (error) {
        // Manejar errores
        loader.style.display = 'none';
        alertNotify('2000', 'error', 'Error', `Error al cargar la lista de archivos: ${error.message}`, 'bottom-end');
    } finally {
        // Ocultar el loader al terminar
        loader.style.display = 'none';
    }
}

// Ver archivo
function viewFile(filePath) {
    if (!filePath) {
        alertNotify('2000', 'error', 'Error', 'El archivo no existe o la ruta es inválida.', 'bottom-end');
        return;
    }
    // Abrir el archivo en una nueva pestaña
    window.open(filePath, '_blank');
}

// Descargar archivo
function downloadFile(filePath) {
    if (!filePath) {
        alertNotify('2000', 'error', 'Error', 'El archivo no existe o la ruta es inválida.', 'bottom-end');
        return;
    }
    // Crear un elemento `<a>` temporal para iniciar la descarga
    const link = document.createElement('a');
    link.href = filePath;
    link.download = filePath.split('/').pop(); // Establecer el nombre del archivo descargado
    document.body.appendChild(link); // Añadir el enlace al DOM
    link.click(); // Simular el clic
    document.body.removeChild(link); // Eliminar el enlace del DOM
}

// Borrar registro
async function deleteFile(filePath, fileName, fileId) {
    // Confirmación antes de eliminar
    const result = await Swal.fire({
        title: 'Estás seguro?',
        text: "Un registro eliminado no se puede recuperar!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Si, Eliminar!',
        cancelButtonText: 'Cancelar'
    });

    if (result.isConfirmed) {
        try {
            console.log(filePath)
            let datos = new FormData();
            datos.append('opcion', 'deleteFile');
            datos.append('id_borrar', fileId);
            datos.append('filePath', filePath);
            // Enviar la solicitud a PHP para eliminar el archivo
            const response = await fetch('functions/delete_general.php', {
                method: 'POST',
                body: datos
            });

            const data = await response.json();

            // Verificar la respuesta del servidor
            if (data.type === 'SUCCESS') {
                Swal.fire('Eliminado!', `Registro Eliminado: ${fileName}`, 'success');
                reloadList();
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        } catch (error) {
            Swal.fire('Error', 'Hubo un problema al procesar la solicitud', 'error');
            console.error(error);
        }
    } else if (result.isDismissed) {
        Swal.fire('Cancelado!', 'No se han guardado los cambios', 'info');
    }
}

// Selecciona el input y añade un listener para validar
document.addEventListener('DOMContentLoaded', () => {
    const inputField = document.getElementById('modal_am_nombre');

    inputField.addEventListener('input', () => {
        // Expresión regular para permitir solo letras, números, guiones bajos y espacios
        const pattern = /[^a-zA-Z0-9 _]/g;
        inputField.value = inputField.value.replace(pattern, '');
    });
});

// Poner foco en input
document.addEventListener('DOMContentLoaded', function (event) {
    $('#modal_add_model').on('shown.bs.modal', function () {
        variableId('modal_am_nombre').focus();
    });
})

// Guardar modelo tipo de archivo al dar clic
variableId('btn_modal_add_model').addEventListener('click', () => {
    printSpinner('btn_modal_add_model', 'Guardando');

    if (variableId("modal_am_nombre").value == "") {
        alertNotify("1500", "info", "Espera", "Ingresa un nombre.")
        inputWarning(variableId("modal_am_nombre"), "btn_modal_add_model");
        variableId("modal_am_nombre").focus();
        setTimeout(() => { deleteSpinner('btn_modal_add_model', 'Guardar'); }, 1500);
        return false;
    }

    let datos = new FormData();
    datos.append('modal_am_nombre', variableId("modal_am_nombre").value);
    datos.append('opcion', 'insertModelFile');
    fetch('functions/insert_general.php', {
        method: 'POST',
        body: datos
    })
        .then(response => response.json())
        .then(data => {

            if (data.type === 'SUCCESS') {
                // Restablecer botón
                deleteSpinner('btn_modal_add_model', 'Guardar');
                // Ocultar modal
                $("#modal_add_model").modal("toggle");
                // Mostrar  mensaje de éxito
                alertNotify('2000', 'success', 'Guardado', data.message, 'bottom-end');
                // Reiniciar form y select
                resetSelectize('vehicle_detail_upload_file');
                fetchFillSelect('getFileType', 'vehicle_detail_upload_file');
            } else {
                alertNotify('2000', 'warning', 'Espera', data.message, 'bottom-end');
                // Restablecer botón
                deleteSpinner('btn_modal_add_model', 'Guardar');
            }
        })
        .catch(() => {
            alertNotify('2000', 'error', 'Ops', 'Hubo un error al crear el registro', 'bottom-end');
        });
});

async function fetchHistory() {
    try {
        const loader = document.getElementById("loader-history");
        const noFiles = document.getElementById("no-files-history");
        const tableContainer = document.getElementById("tabla_historial_observaciones");

        loader.style.display = "flex"; // Mostrar loader
        noFiles.style.display = "none";
        tableContainer.innerHTML = ""; // Limpiar contenido previo
        // Objeto para mapear el tipo de clase CSS
        const badgeType = {
            0: 'verde',
            1: 'azul',
            2: 'rojo',
            3: 'gris'
        };

        const formData = new FormData();
        formData.append('opcion', 'getVehicleHistory');
        formData.append('vehicleId', vehicleId);
        const response = await fetch('functions/select_general.php', {
            method: 'POST',
            body: formData,
        });

        const data = await response.json();
        loader.style.display = "none"; // Ocultar loader

        if (data.type === 'SUCCESS' && data.response.length > 0) {
            // Crear la tabla
            const table = document.createElement("table");
            table.classList.add("table", "table-striped", "table-bordered", "mb-0", "table-sm");

            // Encabezado
            table.innerHTML = `
                <thead>
                    <tr>
                        <th>Observación</th>
                        <th class="text-center">Movimiento</th>
                        <th class="text-center">Archivos</th>
                        <th class="text-center">Fecha</th>
                        <th class="text-center">Fecha sistema</th>
                    </tr>
                </thead>
                <tbody>
                    ${data.response.map(row => `
                        <tr>
                            <td class="align-middle">${row.observacion || 'Sin observación'}</td>
                            <td class="text-center align-middle"><span class="badge badge-${badgeType[row.id_movimiento] || 'gris'}">${row.movimiento_nombre}</span></td>
                            <td class="text-center align-middle">
                ${row.archivos.length > 0
                    ? row.archivos.map(file => `<a href="${file.file_path}${file.file_name}" class="text-reset" target="_blank">${file.file_name}</a>`).join('<br>')
                    : 'Sin archivos'
                }
                            </td>
                            <td class="text-center align-middle">${row.fecha_manual ? row.fecha_manual.split(' ')[0] : ''}</td>
                            <td class="text-center align-middle">${row.creado}</td>
                        </tr>`).join('')}
                </tbody>
            `;

            tableContainer.appendChild(table);
        } else {
            noFiles.style.display = "flex"; // Mostrar mensaje de "sin archivos"
        }
    } catch (error) {
        console.error("Error al obtener el historial:", error);
        loader.style.display = "none";
        alert("Ocurrió un error al cargar el historial.");
    }
}

// ----------------------- Salidas ----------------------- //
// Listener del modal salida
document.addEventListener('DOMContentLoaded', function (event) {
    $('#modal_output_vehicle').on('show.bs.modal', function (event) {
        loadVehicleFiles();
        // Fecha actual
        const dateInput = document.getElementById("modal_vehicle_output_delivery_date");
        const today = new Date().toISOString().split("T")[0]; // Obtener la fecha en formato 'YYYY-MM-DD'
        dateInput.value = today; // Establecer el valor del campo de fecha
    });
});

// Llamar a la función para obtener el listado de archivo para el modal salidas
async function loadVehicleFiles() {
    const fileContainer = document.getElementById('file-status-container');
    fileContainer.innerHTML = ''; // Limpiar contenido previo
    // Crear FormData con el archivo procesado
    const formData = new FormData();
    formData.append('opcion', 'getListFile');
    formData.append('option_value', vehicleId);

    try {
        const response = await fetch('functions/select_general.php', {
            method: 'POST',
            body: formData,
        });
        const result = await response.json(); // Suponiendo que el servidor responde con JSON

        if (result.length === 0) {
            fileContainer.innerHTML = '<p class="w-100 text-center">No hay archivos disponibles para este vehículo.</p>';
            return;
        }

        result.forEach(file => {
            const fileElement = document.createElement('div');
            fileElement.classList.add('col-12', 'mb-3');
            fileElement.innerHTML = `              
                <div class="d-flex gap-1 text-left border rounded px-2">
                    <input type="checkbox" class="type_check" id="check_${file.id}" name="check_${file.id}" value="${file.id}" ${file.is_active == "1" ? 'checked' : '0'}  ${file.is_active == "1" ? '' : 'disabled'}>
                    <label for="check_${file.id}" class="pr-2 font-weight-normal">${file.file_name}</label>
                </div>              
              `;
            fileContainer.appendChild(fileElement);
        });
    } catch (error) {
        console.error('Error al cargar archivos:', error);
        fileContainer.innerHTML = '<p>Error al cargar los archivos.</p>';
    }
}

// Enviar la información al backend cuando se haga clic en el botón de guardar
document.getElementById('btn_modal_insertar_salida').addEventListener('click', async () => {
    disabled('btn_modal_insertar_salida');
    printSpinner('btn_modal_insertar_salida', 'Subiendo');

    if (variableId("modal_vehicle_output_observations").value == "") {
        alertNotify("1500", "info", "Espera", "El campo no puede ir vacío.")
        inputWarning(variableId("modal_vehicle_output_observations"), "btn_modal_insertar_salida");
        setTimeout(() => { deleteSpinner('btn_modal_insertar_salida', 'Guardar'); }, 1500);
        return false;
    }
    if (variableId("modal_vehicle_output_delivery_date").value == "") {
        alertNotify("1500", "info", "Espera", "El campo no puede ir vacío.")
        inputWarning(variableId("modal_vehicle_output_delivery_date"), "btn_modal_insertar_salida");
        setTimeout(() => { deleteSpinner('btn_modal_insertar_salida', 'Guardar'); }, 1500);
        return false;
    }

    const observation = document.getElementById('modal_vehicle_output_observations').value;
    const checkedFiles = Array.from(document.querySelectorAll('#file-status-container input[type="checkbox"]:checked'))
        .map(input => input.value);
    const fecha_manual = document.getElementById('modal_vehicle_output_delivery_date').value;

    if (!vehicleId || checkedFiles.length === 0) {
        enabled('btn_modal_insertar_salida');
        deleteSpinner('btn_modal_insertar_salida', 'Guardar');
        alertNotify('2000', 'warning', 'Espera', 'Selecciona al menos un archivo.', 'bottom-end');
        const fileContainer = document.getElementById('file-status-container');
        fileContainer.classList.add('col-12', 'text-center');
        fileContainer.innerHTML = `              
        <p class="w-100 text-danger">
        Todos los documentos disponibles fueron entregados por el momento.<br>
        Intenta devolver o ingresar algún documento.
        </p>
      `;
        return;
    }

    const formData = new FormData();
    formData.append('opcion', 'insertVehicleFileOutput');
    formData.append('vehicle_id', vehicleId);
    formData.append('observations', observation);
    formData.append('fecha_manual', fecha_manual);
    formData.append('file_ids', JSON.stringify(checkedFiles));

    try {
        const response = await fetch('functions/insert_general.php', {
            method: 'POST',
            body: formData,
        });
        const result = await response.json();

        if (result.type === 'SUCCESS') {
            // Restablecer botón
            enabled('btn_modal_insertar_salida');
            deleteSpinner('btn_modal_insertar_salida', 'Guardar');
            // Ocultar modal
            $("#modal_output_vehicle").modal("toggle");
            // Mostrar mensaje de éxito
            alertNotify('2000', 'success', 'Guardado', result.message, 'bottom-end');
            // Actualizar contenido
            fetchHistory();
            reloadList();
            // Generar redirección al reporte
            window.open(`ver_reporte.php?id_vehiculo_historial=${result.id_vehiculo_historial}`, '_blank');
        } else {
            // Restablecer botón
            enabled('btn_modal_insertar_salida');
            deleteSpinner('btn_modal_insertar_salida', 'Guardar');
            alertNotify('2000', 'warning', 'Espera', result.message, 'bottom-end');
        }
    } catch (error) {
        console.error('Error al procesar la salida:', error);
        alertNotify('2000', 'error', 'Ops', 'Hubo un error al crear el registro', 'bottom-end');
    }
});

// ----------------------- Devoluciones ----------------------- //
// Listener del modal devoluciones
document.addEventListener('DOMContentLoaded', function (event) {
    $('#modal_return_vehicle').on('show.bs.modal', function (event) {
        loadVehicleFilesReturn();
        // Fecha actual
        const dateInput = document.getElementById("modal_vehicle_return_delivery_date");
        const today = new Date().toISOString().split("T")[0]; // Obtener la fecha en formato 'YYYY-MM-DD'
        dateInput.value = today; // Establecer el valor del campo de fecha
    });
});

// Llamar a la función para obtener el listado de archivo para el modal devoluciones
async function loadVehicleFilesReturn() {
    const fileContainer = document.getElementById('file-status-container-return');
    fileContainer.innerHTML = ''; // Limpiar contenido previo
    // Crear FormData con el archivo procesado
    const formData = new FormData();
    formData.append('opcion', 'getListFile');
    formData.append('option_value', vehicleId);

    try {
        const response = await fetch('functions/select_general.php', {
            method: 'POST',
            body: formData,
        });
        const result = await response.json(); // Suponiendo que el servidor responde con JSON

        if (result.length === 0) {
            fileContainer.innerHTML = '<p class="w-100 text-center">No hay archivos disponibles para este vehículo.</p>';
            return;
        }

        result.forEach(file => {
            const fileElement = document.createElement('div');
            fileElement.classList.add('col-12', 'mb-3');
            fileElement.innerHTML = `
                <div class="d-flex gap-1 text-left border rounded px-2">
                    <input type="checkbox" class="type_check" id="check_return_${file.id}" name="check_return_${file.id}" value="${file.id}" ${file.is_active == '0' ? 'checked' : '0'}  ${file.is_active == '0' ? '' : 'disabled'}>
                    <label for="check_return_${file.id}" class="pr-2 font-weight-normal">${file.file_name}</label>
                </div>
              `;
            fileContainer.appendChild(fileElement);
        });
    } catch (error) {
        console.error('Error al cargar archivos:', error);
        fileContainer.innerHTML = '<p>Error al cargar los archivos.</p>';
    }
}

// Enviar la información al backend cuando se haga clic en el botón de guardar
document.getElementById('btn_modal_insertar_devolucion').addEventListener('click', async () => {
    disabled('btn_modal_insertar_devolucion');
    printSpinner('btn_modal_insertar_devolucion', 'Subiendo');

    if (variableId("modal_vehicle_return_observations").value == "") {
        alertNotify("1500", "info", "Espera", "El campo no puede ir vacío.")
        inputWarning(variableId("modal_vehicle_return_observations"), "btn_modal_insertar_devolucion");
        setTimeout(() => { deleteSpinner('btn_modal_insertar_devolucion', 'Guardar'); }, 1500);
        return false;
    }
    if (variableId("modal_vehicle_return_delivery_date").value == "") {
        alertNotify("1500", "info", "Espera", "El campo no puede ir vacío.")
        inputWarning(variableId("modal_vehicle_return_delivery_date"), "btn_modal_insertar_devolucion");
        setTimeout(() => { deleteSpinner('btn_modal_insertar_devolucion', 'Guardar'); }, 1500);
        return false;
    }

    const observation = document.getElementById('modal_vehicle_return_observations').value;
    const checkedFiles = Array.from(document.querySelectorAll('#file-status-container-return input[type="checkbox"]:checked'))
        .map(input => input.value);
    const fecha_manual = document.getElementById('modal_vehicle_return_delivery_date').value;

    if (!vehicleId || checkedFiles.length === 0) {
        enabled('btn_modal_insertar_devolucion');
        deleteSpinner('btn_modal_insertar_devolucion', 'Guardar');
        alertNotify('2000', 'warning', 'Espera', 'Selecciona al menos un archivo.', 'bottom-end');
        const fileContainer = document.getElementById('file-status-container-return');
        fileContainer.classList.add('col-12', 'text-center');
        fileContainer.innerHTML = `
        <p class="w-100 text-danger">
        Selecciona al menos un archivo para devolver.
        </p>
      `;
        return;
    }

    const formData = new FormData();
    formData.append('opcion', 'insertVehicleFileOutput');
    formData.append('vehicle_id', vehicleId);
    formData.append('observations', observation);
    formData.append('fecha_manual', fecha_manual);
    formData.append('file_ids', JSON.stringify(checkedFiles));
    formData.append('is_return', true);

    try {
        const response = await fetch('functions/insert_general.php', {
            method: 'POST',
            body: formData,
        });
        const result = await response.json();

        if (result.type === 'SUCCESS') {
            // Restablecer botón
            enabled('btn_modal_insertar_devolucion');
            deleteSpinner('btn_modal_insertar_devolucion', 'Guardar');
            // Ocultar modal
            $("#modal_return_vehicle").modal("toggle");
            // Mostrar mensaje de éxito
            alertNotify('2000', 'success', 'Guardado', result.message, 'bottom-end');
            // Actualizar contenido
            fetchHistory();
            reloadList();
        } else {
            // Restablecer botón
            enabled('btn_modal_insertar_devolucion');
            deleteSpinner('btn_modal_insertar_devolucion', 'Guardar');
            alertNotify('2000', 'warning', 'Espera', result.message, 'bottom-end');
        }
    } catch (error) {
        console.error('Error al procesar la salida:', error);
        alertNotify('2000', 'error', 'Ops', 'Hubo un error al crear el registro', 'bottom-end');
    }
});

// ----------------------- Observaciones ----------------------- //
// Listener del modal observaciones
document.addEventListener('DOMContentLoaded', function (event) {
    $('#modal_observation_vehicle').on('show.bs.modal', function (event) {
        // Fecha actual
        const dateInput = document.getElementById("modal_vehicle_observation_delivery_date");
        const today = new Date().toISOString().split("T")[0]; // Obtener la fecha en formato 'YYYY-MM-DD'
        dateInput.value = today; // Establecer el valor del campo de fecha
    });
});

// Enviar la información al backend cuando se haga clic en el botón de guardar
document.getElementById('btn_modal_insertar_observacion').addEventListener('click', async () => {
    disabled('btn_modal_insertar_observacion');
    printSpinner('btn_modal_insertar_observacion', 'Subiendo');

    if (variableId("modal_vehicle_observation_observations").value == "") {
        alertNotify("1500", "info", "Espera", "El campo no puede ir vacío.")
        inputWarning(variableId("modal_vehicle_observation_observations"), "btn_modal_insertar_observacion");
        setTimeout(() => { deleteSpinner('btn_modal_insertar_observacion', 'Guardar'); }, 1500);
        return false;
    }
    if (variableId("modal_vehicle_observation_delivery_date").value == "") {
        alertNotify("1500", "info", "Espera", "El campo no puede ir vacío.")
        inputWarning(variableId("modal_vehicle_observation_delivery_date"), "btn_modal_insertar_observacion");
        setTimeout(() => { deleteSpinner('btn_modal_insertar_observacion', 'Guardar'); }, 1500);
        return false;
    }

    const observation = variableId('modal_vehicle_observation_observations').value;
    const fecha_manual = variableId('modal_vehicle_observation_delivery_date').value;

    const formData = new FormData();
    formData.append('opcion', 'insertVehicleFileObservation');
    formData.append('vehicle_id', vehicleId);
    formData.append('observations', observation);
    formData.append('fecha_manual', fecha_manual);

    try {
        const response = await fetch('functions/insert_general.php', {
            method: 'POST',
            body: formData,
        });
        const result = await response.json();

        if (result.type === 'SUCCESS') {
            // Restablecer botón
            enabled('btn_modal_insertar_observacion');
            deleteSpinner('btn_modal_insertar_observacion', 'Guardar');
            // Ocultar modal
            $("#modal_observation_vehicle").modal("toggle");
            // Mostrar mensaje de éxito
            alertNotify('2000', 'success', 'Guardado', result.message, 'bottom-end');
            // Actualizar contenido
            fetchHistory();
        } else {
            // Restablecer botón
            enabled('btn_modal_insertar_observacion');
            deleteSpinner('btn_modal_insertar_observacion', 'Guardar');
            alertNotify('2000', 'warning', 'Espera', result.message, 'bottom-end');
        }
    } catch (error) {
        console.error('Error al procesar la observación:', error);
        alertNotify('2000', 'error', 'Ops', 'Hubo un error al crear el registro', 'bottom-end');
    }
});

// Primer carga del listado de archivos
reloadList();