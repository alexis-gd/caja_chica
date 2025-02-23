// Insertar
variableId('btn_modal_insertar').addEventListener('click', () => {
    printSpinner('btn_modal_insertar', 'Guardando');
    disabled('btn_modal_insertar');

    if (variableId("modal_caja_add_fecha").value == "") {
        alertNotify("1500", "info", "Espera", "El campo no puede ir vacío.")
        inputWarning(variableId("modal_caja_add_fecha"), "btn_modal_insertar");
        setTimeout(() => { deleteSpinner('btn_modal_insertar', 'Guardar'); }, 1500);
        return false;
    }
    // if (variableSelect("modal_vehicle_add_model").value == "") {
    //     alertNotify("1500", "info", "Espera", "El campo no puede ir vacío.")
    //     inputWarning(variableSelect("modal_vehicle_add_model"), "btn_modal_insertar");
    //     setTimeout(() => { deleteSpinner('btn_modal_insertar', 'Guardar'); }, 1500);
    //     return false;
    // }
    // if (variableId("modal_vehicle_add_year").value == "") {
    //     alertNotify("1500", "info", "Espera", "El campo no puede ir vacío.")
    //     inputWarning(variableId("modal_vehicle_add_year"), "btn_modal_insertar");
    //     setTimeout(() => { deleteSpinner('btn_modal_insertar', 'Guardar'); }, 1500);
    //     return false;
    // }
    // if (variableSelect("modal_vehicle_add_color").value == "") {
    //     alertNotify("1500", "info", "Espera", "Selecciona una opción")
    //     inputWarning(variableSelect("modal_vehicle_add_color"), "btn_modal_insertar");
    //     setTimeout(() => { deleteSpinner('btn_modal_insertar', 'Guardar'); }, 1500);
    //     return false;
    // }
    // if (variableId("modal_vehicle_add_serie").value == "") {
    //     alertNotify("1500", "info", "Espera", "El campo no puede ir vacío.")
    //     inputWarning(variableId("modal_vehicle_add_serie"), "btn_modal_insertar");
    //     setTimeout(() => { deleteSpinner('btn_modal_insertar', 'Guardar'); }, 1500);
    //     return false;
    // }
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
    // if (variableSelect("modal_vehicle_add_owner").value == "") {
    //     alertNotify("1500", "info", "Espera", "Selecciona una opción")
    //     inputWarning(variableSelect("modal_vehicle_add_owner"), "btn_modal_insertar");
    //     setTimeout(() => { deleteSpinner('btn_modal_insertar', 'Guardar'); }, 1500);
    //     return false;
    // }
    // if (variableId("modal_vehicle_add_license_plate").value == "") {
    //     alertNotify("1500", "info", "Espera", "El campo no puede ir vacío.")
    //     inputWarning(variableId("modal_vehicle_add_license_plate"), "btn_modal_insertar");
    //     setTimeout(() => { deleteSpinner('btn_modal_insertar', 'Guardar'); }, 1500);
    //     return false;
    // }
    // if (variableId("modal_vehicle_add_observations").value == "") {
    //     alertNotify("1500", "info", "Espera", "El campo no puede ir vacío.")
    //     inputWarning(variableId("modal_vehicle_add_observations"), "btn_modal_insertar");
    //     setTimeout(() => { deleteSpinner('btn_modal_insertar', 'Guardar'); }, 1500);
    //     return false;
    // }
    // if (variableId("modal_vehicle_add_delivery_date").value == "") {
    //     alertNotify("1500", "info", "Espera", "El campo no puede ir vacío.")
    //     inputWarning(variableId("modal_vehicle_add_delivery_date"), "btn_modal_insertar");
    //     setTimeout(() => { deleteSpinner('btn_modal_insertar', 'Guardar'); }, 1500);
    //     return false;
    // }

    const form = variableId('form-add-caja');
    let datos = new FormData(form);
    datos.append('opcion', 'insertCaja');
    fetch('functions/insert_general.php', {
        method: 'POST',
        body: datos
    })
        .then(response => response.json())
        .then(data => {
            if (data.type === 'SUCCESS') {
                // Restablecer botón
                enabled('btn_modal_insertar');
                deleteSpinner('btn_modal_insertar', 'Guardar');
                // Recargar la tabla
                $("#tablaCaja").DataTable().ajax.reload();
                // Ocultar modal
                $("#modal_add_caja").modal("toggle");
                // Mostrar  mensaje de éxito
                alertNotify('2000', 'success', 'Guardado', data.message, 'bottom-end');
                // Reiniciar form y select
                variableId('form-add-caja').reset();
                clearSelectize('modal_caja_add_cargado');
                clearSelectize('modal_caja_add_area');

                clearSelectize('modal_caja_add_empresa');
                clearSelectize('modal_caja_add_autoriza');
                clearSelectize('modal_caja_add_tipo_folio');
                clearSelectize('modal_caja_add_tipo_ingreso');
                clearSelectize('modal_caja_add_tipo_gasto');
                clearSelectize('modal_caja_add_entrega');

                clearSelectize('modal_caja_add_recibe');
                clearSelectize('modal_caja_add_unidad');
                clearSelectize('modal_caja_add_comprobante');
                clearSelectize('modal_caja_add_razon_social');
            } else {
                enabled('btn_modal_insertar');
                deleteSpinner('btn_modal_insertar', 'Guardar');
                alertNotify('2000', 'error', 'Ops', data.message, 'bottom-end');
            }
        })
        .catch(() => {
            enabled('btn_modal_insertar');
            deleteSpinner('btn_modal_insertar', 'Guardar');
            alertNotify('2000', 'error', 'Ops', 'Hubo un error al crear el registro', 'bottom-end');
        });
});

// Editar
variableId('btn_modal_editar').addEventListener('click', () => {
    printSpinner('btn_modal_editar', 'Guardando');
    disabled('btn_modal_editar');

    if (variableId("modal_caja_edit_fecha").value == "") {
        alertNotify("1500", "info", "Espera", "El campo no puede ir vacío.")
        inputWarning(variableId("modal_caja_edit_fecha"), "btn_modal_editar");
        setTimeout(() => { deleteSpinner('btn_modal_editar', 'Guardar'); }, 1500);
        return false;
    }

    const form = variableId('form-edit-caja');
    let datos = new FormData(form);
    datos.append('opcion', 'updateCaja');
    fetch('functions/update_general.php', {
        method: 'POST',
        body: datos
    })
        .then(response => response.json())
        .then(data => {
            if (data.type === 'SUCCESS') {
                // Restablecer botón
                enabled('btn_modal_editar');
                deleteSpinner('btn_modal_editar', 'Guardar');
                // Recargar la tabla
                $("#tablaCaja").DataTable().ajax.reload();
                // Ocultar modal
                $("#modal_edit_caja").modal("toggle");
                // Mostrar  mensaje de éxito
                alertNotify('2000', 'success', 'Guardado', data.message, 'bottom-end');
                // Reiniciar form y select
                variableId('form-edit-caja').reset();
                clearSelectize('modal_caja_edit_cargado');
                clearSelectize('modal_caja_edit_area');

                clearSelectize('modal_caja_edit_empresa');
                clearSelectize('modal_caja_edit_autoriza');
                clearSelectize('modal_caja_edit_tipo_folio');
                clearSelectize('modal_caja_edit_tipo_ingreso');
                clearSelectize('modal_caja_edit_tipo_gasto');
                clearSelectize('modal_caja_edit_entrega');

                clearSelectize('modal_caja_edit_recibe');
                clearSelectize('modal_caja_edit_unidad');
                clearSelectize('modal_caja_edit_comprobante');
                clearSelectize('modal_caja_edit_razon_social');
            } else {
                enabled('btn_modal_editar');
                deleteSpinner('btn_modal_editar', 'Guardar');
                alertNotify('2000', 'error', 'Ops', data.message, 'bottom-end');
            }
        })
        .catch(() => {
            enabled('btn_modal_editar');
            deleteSpinner('btn_modal_editar', 'Guardar');
            alertNotify('2000', 'error', 'Ops', 'Hubo un error al actualizar el registro', 'bottom-end');
        });
});