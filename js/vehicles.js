// Insertar vehiculo
variableId('btn_modal_insertar').addEventListener('click', () => {
    printSpinner('btn_modal_insertar', 'Guardando');

    if (variableSelect("modal_vehicle_add_brand").value == "") {
        alertNotify("1500", "info", "Espera", "El campo no puede ir vacío.")
        inputWarning(variableSelect("modal_vehicle_add_brand"), "btn_modal_insertar");
        setTimeout(() => { deleteSpinner('btn_modal_insertar', 'Guardar'); }, 1500);
        return false;
    }
    if (variableSelect("modal_vehicle_add_model").value == "") {
        alertNotify("1500", "info", "Espera", "El campo no puede ir vacío.")
        inputWarning(variableSelect("modal_vehicle_add_model"), "btn_modal_insertar");
        setTimeout(() => { deleteSpinner('btn_modal_insertar', 'Guardar'); }, 1500);
        return false;
    }
    // if (variableId("modal_vehicle_add_year").value == "") {
    //     alertNotify("1500", "info", "Espera", "El campo no puede ir vacío.")
    //     inputWarning(variableId("modal_vehicle_add_year"), "btn_modal_insertar");
    //     setTimeout(() => { deleteSpinner('btn_modal_insertar', 'Guardar'); }, 1500);
    //     return false;
    // }
    if (variableSelect("modal_vehicle_add_color").value == "") {
        alertNotify("1500", "info", "Espera", "Selecciona una opción")
        inputWarning(variableSelect("modal_vehicle_add_color"), "btn_modal_insertar");
        setTimeout(() => { deleteSpinner('btn_modal_insertar', 'Guardar'); }, 1500);
        return false;
    }
    if (variableId("modal_vehicle_add_serie").value == "") {
        alertNotify("1500", "info", "Espera", "El campo no puede ir vacío.")
        inputWarning(variableId("modal_vehicle_add_serie"), "btn_modal_insertar");
        setTimeout(() => { deleteSpinner('btn_modal_insertar', 'Guardar'); }, 1500);
        return false;
    }
    // if (variableId("modal_vehicle_add_engine").value == "") {
    //     alertNotify("1500", "info", "Espera", "El campo no puede ir vacío.")
    //     inputWarning(variableId("modal_vehicle_add_engine"), "btn_modal_insertar");
    //     setTimeout(() => { deleteSpinner('btn_modal_insertar', 'Guardar'); }, 1500);
    //     return false;
    // }
    // if (variableId("modal_vehicle_add_pedimento").value == "") {
    //     alertNotify("1500", "info", "Espera", "El campo no puede ir vacío.")
    //     inputWarning(variableId("modal_vehicle_add_pedimento"), "btn_modal_insertar");
    //     setTimeout(() => { deleteSpinner('btn_modal_insertar', 'Guardar'); }, 1500);
    //     return false;
    // }
    if (variableSelect("modal_vehicle_add_owner").value == "") {
        alertNotify("1500", "info", "Espera", "Selecciona una opción")
        inputWarning(variableSelect("modal_vehicle_add_owner"), "btn_modal_insertar");
        setTimeout(() => { deleteSpinner('btn_modal_insertar', 'Guardar'); }, 1500);
        return false;
    }
    // if (variableId("modal_vehicle_add_license_plate").value == "") {
    //     alertNotify("1500", "info", "Espera", "El campo no puede ir vacío.")
    //     inputWarning(variableId("modal_vehicle_add_license_plate"), "btn_modal_insertar");
    //     setTimeout(() => { deleteSpinner('btn_modal_insertar', 'Guardar'); }, 1500);
    //     return false;
    // }
    if (variableId("modal_vehicle_add_observations").value == "") {
        alertNotify("1500", "info", "Espera", "El campo no puede ir vacío.")
        inputWarning(variableId("modal_vehicle_add_observations"), "btn_modal_insertar");
        setTimeout(() => { deleteSpinner('btn_modal_insertar', 'Guardar'); }, 1500);
        return false;
    }
    // if (variableId("modal_vehicle_add_delivery_date").value == "") {
    //     alertNotify("1500", "info", "Espera", "El campo no puede ir vacío.")
    //     inputWarning(variableId("modal_vehicle_add_delivery_date"), "btn_modal_insertar");
    //     setTimeout(() => { deleteSpinner('btn_modal_insertar', 'Guardar'); }, 1500);
    //     return false;
    // }

    const form = variableId('form-add-vehicle');
    let datos = new FormData(form);
    datos.append('opcion', 'insertVehicle');
    fetch('functions/insert_general.php', {
        method: 'POST',
        body: datos
    })
        .then(response => response.json())
        .then(data => {
            if (data.type === 'SUCCESS') {
                // Restablecer botón
                deleteSpinner('btn_modal_insertar', 'Guardar');
                // Recargar la tabla
                $("#tablaVehiculos").DataTable().ajax.reload();
                // Ocultar modal
                $("#modal_add_vehicle").modal("toggle");
                // Mostrar  mensaje de éxito
                alertNotify('2000', 'success', 'Guardado', data.message, 'bottom-end');
                // Reiniciar form y select
                variableId('form-add-vehicle').reset();
                clearSelectize('modal_vehicle_add_brand');
                clearSelectize('modal_vehicle_add_model');
                clearSelectize('modal_vehicle_add_color');
                clearSelectize('modal_vehicle_add_owner');
            } else {
                alertNotify('2000', 'error', 'Ops', data.message, 'bottom-end');
            }
        })
        .catch(() => {
            alertNotify('2000', 'error', 'Ops', 'Hubo un error al crear el registro', 'bottom-end');
        });
});