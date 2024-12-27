// ----------------------- FUNCIONES INICIALES ----------------------- //
$(document).ready(function () {
  // Agregar botón añadir
  let button1 = '<button id="btn-crear" title="Añadir" type="text"' +
    'data-toggle="modal" data-target="#modal_add_catalogo"' +
    'class="btn btn-azul"><span class="d-flex align-items-center justify-content-center"><i class="fas fa-plus nav-icon"></i></span></button>';
  $('div .btn-group').append(button1);

  // Agregar color cuando se selecciona una fila
  $('#tablaCatalogos').on('click', '.odd, .even', function () {
    if ($(this).hasClass('clicked')) {
      $(this).removeClass('clicked');
    } else {
      $(this).addClass('clicked').siblings().removeClass('clicked');
    }
  });

  // Agregar un event listener para el evento de escritura
  // variableId('modal_ac_nombre').addEventListener("input", handleInput);
  // variableId('modal_ec_nombre').addEventListener("input", handleInput);
});

// ----------------------- LISTENERS ----------------------- //
// Guardar al dar clic
variableId('btn_modal_insertar').addEventListener('click', () => {
  printSpinner('btn_modal_insertar', 'Guardando');
  disabled('btn_modal_insertar');

  if (variableName("modal_ac_nombre").value == "") {
    alertNotify("1500", "info", "Espera", "Ingresa un nombre.")
    inputWarning(variableName("modal_ac_nombre"), "btn_modal_insertar");
    variableName("modal_ac_nombre").focus();
    setTimeout(() => { deleteSpinner('btn_modal_insertar', 'Guardar'); }, 1500);
    return false;
  }

  let modelo = getUrlParameter('model');
  let datos = new FormData();
  datos.append('opcion', 'insertModelsGeneric');
  datos.append('tabla', modelo);
  datos.append('newOption', variableName("modal_ac_nombre").value);
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
        $("#tablaCatalogos").DataTable().ajax.reload();
        // Ocultar modal
        $("#modal_add_catalogo").modal("toggle");
        // Mostrar  mensaje de éxito
        alertNotify('2000', 'success', 'Guardado', data.message, 'bottom-end');

        // Reiniciar input
        variableId('modal_ac_nombre').value = '';
      } else {
        enabled('btn_modal_insertar');
        deleteSpinner('btn_modal_insertar', 'Guardar');
        alertNotify('2000', 'error', 'Ops', data.message, 'bottom-end');
        $("#tablaCatalogos").DataTable().ajax.reload();
      }
    })
    .catch(() => {
      enabled('btn_modal_insertar');
      deleteSpinner('btn_modal_insertar', 'Guardar');
      alertNotify('2000', 'error', 'Ops', 'Hubo un error al crear el registro', 'bottom-end');
      $("#tablaCatalogos").DataTable().ajax.reload();
    });
});

// Editar al dar dobleclick
$(document).on('dblclick', '#tablaCatalogos tr', function () {
  const id = $(this).find("td").eq(0).html();
  if (id === undefined) {
    return;
  }
  const nombre = $(this).find("td").eq(1).html();
  variableId('modal_ec_id').value = id;
  variableId('modal_ec_nombre').value = nombre;
  $('#modal_edit_catalogo').modal('show');
});

// Guardar editar al dar clic
variableId('btn_modal_editar').addEventListener('click', () => {
  printSpinner('btn_modal_editar', 'Guardando');
  disabled('btn_modal_editar');

  if (variableId("modal_ec_nombre").value == "") {
    alertNotify("1500", "info", "Espera", "Ingresa un nombre.")
    inputWarning(variableId("modal_ec_nombre"), "btn_modal_editar");
    variableId("modal_ec_nombre").focus();
    setTimeout(() => { deleteSpinner('btn_modal_editar', 'Guardar'); }, 1500);
    return false;
  }

  let modelo = getUrlParameter('model');
  let datos = new FormData();
  datos.append('id', variableId("modal_ec_id").value);
  datos.append('modal_ec_nombre', variableId("modal_ec_nombre").value);
  datos.append('opcion', 'updateCatalogo');
  datos.append('tabla', modelo);

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
        $("#tablaCatalogos").DataTable().ajax.reload();
        // Ocultar modal
        $("#modal_edit_catalogo").modal("toggle");
        // Mostrar  mensaje de éxito
        alertNotify('2000', 'success', 'Listo', data.message, 'bottom-end');

        // Reiniciar input
        variableId('modal_ec_nombre').value = '';
      } else {
        enabled('btn_modal_editar');
        deleteSpinner('btn_modal_editar', 'Guardar');
        alertNotify('2000', 'error', 'Ops', data.message, 'bottom-end');
        $("#tablaCatalogos").DataTable().ajax.reload();
      }
    })
    .catch(() => {
      enabled('btn_modal_editar');
      deleteSpinner('btn_modal_editar', 'Guardar');
      alertNotify('2000', 'error', 'Ops', 'Hubo un error al crear el registro', 'bottom-end');
      $("#tablaCatalogos").DataTable().ajax.reload();
    });
});

// Borrar registro
$(document).ready(function () {
  let selectedId;

  // Evento para seleccionar el id al hacer clic en una fila de la tabla
  $(document).on('click', '#tablaCatalogos tbody tr', function () {
      selectedId = $(this).find("td").eq(0).html().trim();
      let numericId = parseInt(selectedId, 10);

      // Validar que numericId sea un número y no NaN
      if (!isNaN(numericId)) {
          $('#btn-borrar').remove();
          // Agregar botón para borrar catálogo
          let buttonDelete = `<button id="btn-borrar" title="Borrar" type="button"
              class="btn btn-danger" value="${numericId}">
              <span class="d-flex align-items-center justify-content-center">
                  <i class="fas fa-trash nav-icon pr-2"></i>${numericId}
              </span>
          </button>`;
          $('div .btn-group').append(buttonDelete);
      }
  });

  // Evento para borrar registro al hacer clic en el botón de borrar
  $(document).on('click', '#btn-borrar', async function () {
      if (!selectedId) {
        console.log(selectedId)
          alertNotify('2000', 'warning', 'Advertencia', 'Por favor, selecciona un registro para borrar.', 'bottom-end');
          return;
      }

      // Confirmar la eliminación
      const result = await Swal.fire({
          title: '¿Estás seguro?',
          text: "¡No podrás revertir esto!",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          confirmButtonText: 'Sí, eliminarlo',
          cancelButtonText: 'Cancelar'
      });

      if (result.isConfirmed) {
          let datos = new FormData();
          datos.append('id', selectedId);
          datos.append('opcion', 'deleteModel'); // Asegúrate de que coincida con tu lógica en PHP
          datos.append('tabla', getUrlParameter('model')); // Obtén el modelo desde la URL

          try {
              const response = await fetch('functions/delete_general.php', {
                  method: 'POST',
                  body: datos
              });

              const data = await response.json();

              if (data.type === 'SUCCESS') {
                  alertNotify('2000', 'success', 'Eliminado', 'Registro eliminado correctamente.', 'bottom-end');
                  $('#tablaCatalogos').DataTable().ajax.reload();
                  $('#btn-borrar').remove();
              } else {
                  alertNotify('2000', 'error', 'Error', 'Error al eliminar el registro: ' + data.message, 'bottom-end');
              }
          } catch (error) {
              alertNotify('2000', 'error', 'Error', 'Hubo un error al intentar eliminar el registro.', 'bottom-end');
          }
      } else if (result.isDismissed) {
          alertNotify('2000', 'info', 'Cancelado', 'No se han guardado los cambios.', 'bottom-end');
      }
  });
});