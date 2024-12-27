// Abrir modal y setear ID
$('#modal_add_comprobante').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget); // Botón que activó el modal
    var id = button.data('id'); // Extraer la información de los atributos data-*
    $('#modal_comprobante_id').val(id);

    fetchFillSelect('getVoucher', 'modal_comprobante_add_comprobante', null, id);
    fetchFillSelect('getFileType', 'modal_voucher_upload_file', null);

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
    const fileInput = document.getElementById('file');
    const fileLabel = document.getElementById('file-label');
    const checkbox = document.getElementById('check_visible');
    const observationField = document.getElementById('modal_comprobante_add_concepto');

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

    variableId('btn_modal_subir').addEventListener('click', async (event) => {
        event.preventDefault();

        try {
            // Bloqueamos el botón y ponemos el spinner
            disabled('btn_modal_subir');
            printSpinner('btn_modal_subir', 'Subiendo');

            const formData = new FormData();
            formData.append('opcion', 'insertFile');
            formData.append('modal_comprobante_id', variableId('modal_comprobante_id').value);
            formData.append('modal_comprobante_add_comprobante', variableId('modal_comprobante_add_comprobante').value);

            if (checkbox.checked) {
                console.log('Checkbox activado');
                if (variableSelect("modal_voucher_upload_file").value == "") {
                    alertNotify("1500", "info", "Espera", "Selecciona el tipo de archivo a subir.")
                    inputWarning(variableSelect("modal_voucher_upload_file"), "btn_modal_subir");
                    setTimeout(() => { deleteSpinner('btn_modal_subir', 'Guardar'); }, 1500);
                    return false;
                }

                const file = fileInput.files[0];
                if (!file) {
                    handleFileStatus('fail');
                    return;
                }

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

                var selectElement = document.getElementById('modal_voucher_upload_file'); // Reemplaza 'your-select-element-id' con el ID de tu elemento select
                var selectedValue = selectElement.value; // Obtener el valor seleccionado
                var selectedText = selectElement.options[selectElement.selectedIndex].text; // Obtener el texto asociado al valor seleccionado
                formData.append('product_file', processedFile);
                formData.append('type_file_id', selectedValue);
                formData.append('type_file_name', selectedText);
                formData.append('comments', observationField.value.trim());
                formData.append('check_visible', checkbox);
            } else {
                formData.append('product_file', '');
                formData.append('type_file_id', '');
                formData.append('type_file_name', '');
                formData.append('comments', observationField.value.trim());
            }

            // Enviar datos al servidor
            const response = await fetch('functions/insert_general.php', {
                method: 'POST',
                body: formData,
            });

            const result = await response.json(); // Suponiendo que el servidor responde con JSON

            if (response.ok && result.type === 'SUCCESS') {
                // Mostrar notificación de éxito
                alertNotify('2000', 'success', 'Guardado', result.message, 'bottom-end');

                // Actualizar la interfaz tras el éxito
                handleFileStatus('clean');
                observationField.value = '';
                resetSelectize('modal_voucher_upload_file');
                // Recargar la tabla
                $("#tablaCaja").DataTable().ajax.reload();
                // Ocultar modal
                $("#modal_add_comprobante").modal("hide");

                // Resetear el grupo de observaciones
                const observationGroup = document.getElementById('observation-group');
                checkbox.checked = false;
                checkbox.dispatchEvent(new Event('change'));
                observationGroup.style.display = 'none';
                observationGroup.style.opacity = 0;
            } else {
                enabled('btn_modal_subir');
                deleteSpinner('btn_modal_subir', '<i class="fa-solid fa-upload pr-2"></i>Subir archivo');
                // Manejar errores enviados por el servidor
                const errorMessage = result.message || 'Error desconocido en el servidor.';
                alertNotify('2000', 'error', 'Error', `Error al subir el archivo: ${errorMessage}`, 'bottom-end');
                console.error(`Error del servidor: ${errorMessage}`);
            }
        } catch (error) {
            enabled('btn_modal_subir');
            deleteSpinner('btn_modal_subir', '<i class="fa-solid fa-upload pr-2"></i>Subir archivo');
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
            disabled('btn_modal_subir');
            printSpinner('btn_modal_subir', '<i class="fa-solid fa-upload pr-2"></i>Subir archivo');
            setTimeout(() => {
                fileLabel.classList.remove('success', 'fail');
                enabled('btn_modal_subir');
                deleteSpinner('btn_modal_subir', '<i class="fa-solid fa-upload pr-2"></i>Subir archivo');
            }, 1500);
        } else if (status == 'format') {
            fileInput.value = '';
            fileLabel.innerHTML = `Formato no valido <img src="img/puntero.svg" alt="puntero">`;
            fileLabel.classList.add('fail');
            fileLabel.classList.remove('success');
            disabled('btn_modal_subir');
            printSpinner('btn_modal_subir', '<i class="fa-solid fa-upload pr-2"></i>Subir archivo');
            setTimeout(() => {
                fileLabel.innerHTML = `Selecciona o arrastra un archivo <img src="img/puntero.svg" alt="puntero">`;
                fileLabel.classList.remove('success', 'fail');
                enabled('btn_modal_subir');
                deleteSpinner('btn_modal_subir', '<i class="fa-solid fa-upload pr-2"></i>Subir archivo');
            }, 1500);
        } else if (status == 'clean') {
            fileInput.value = '';
            fileLabel.innerHTML = `Selecciona o arrastra un archivo <img src="img/puntero.svg" alt="puntero">`;
            fileLabel.classList.remove('success', 'fail');
            enabled('btn_modal_subir');
            deleteSpinner('btn_modal_subir', '<i class="fa-solid fa-upload pr-2"></i>Subir archivo');
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