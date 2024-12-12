// crear admin
$(document).ready(function () {
  $('#guardar-registro').on('submit', function (e) {
    e.preventDefault();
    var datos = $(this).serializeArray();

    $.ajax({
      type: $(this).attr('method'),
      data: datos,
      url: $(this).attr('action'),
      dataType: 'json',
      success: function (response) {
        console.log(response); // Imprime directamente toda la respuesta del servicio

        // Mostrar mensaje directamente de la respuesta del servicio
        if (response.response === 'SUCCESS') {
          Swal.fire({
            icon: 'success',
            title: 'Éxito',
            text: response.message || 'Operación realizada correctamente',
          }).then((result) => {
            if (result.isConfirmed) {
              window.location.reload();
            }
          });
        } else if (response.response === 'ERROR') {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: response.message || 'Ocurrió un error al realizar la operación',
          });
        } else {
          Swal.fire({
            icon: 'warning',
            title: 'Advertencia',
            text: 'Respuesta inesperada del servidor',
          });
        }
      },
      error: function (jqXHR, textStatus, errorThrown) {
        Swal.fire({
          icon: 'error',
          title: 'Error de conexión',
          text: 'No se pudo completar la solicitud: ' + textStatus,
        });
        console.error('Error en la solicitud:', textStatus, errorThrown);
      }
    });
  });

  // crear invitado con imagen o archivo
  $('#guardar-registro-archivo').on('submit', function (e) {
    e.preventDefault();
    var datos = new FormData(this);

    $.ajax({
      type: $(this).attr('method'),
      data: datos,
      url: $(this).attr('action'),
      dataType: 'json',
      contentType: false,
      processData: false,
      async: true,
      cache: false,
      success: function (data) {
        console.log(data);
        var resultado = data;
        if (resultado.respuesta == 'exito') {
          Swal.fire({
            icon: 'success',
            title: 'Correcto',
            text: 'Se guardó correctamente',
          }).then((result) => {
            if (result.isConfirmed) {
              window.location.reload();
            }
          });
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'El usuario ya existe',
          })
        }
      }
    })
  })

  // Eliminar un admin
  $('.borrar_registro').on('click', function (e) {
    e.preventDefault();
    var id = $(this).attr('data-id');
    var tipo = $(this).attr('data-tipo');

    Swal.fire({
      title: 'Estás seguro?',
      text: "Un registro eliminado no se puede recuperar!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Si, Eliminar!',
      cancelButtonText: 'Cancelar'
    }).then((result) => {

      if (result.isConfirmed) {
        $.ajax({
          type: 'POST',
          data: {
            'id': id,
            'registro': 'eliminar'
          },
          url: 'functions/modelo-' + tipo + '.php',
          success: function (data) {
            // console.log(data);
            var resultado = JSON.parse(data);
            // console.log(resultado.respuesta);
            if (resultado.respuesta == 'exito') {
              Swal.fire(
                'Eliminado!',
                'Registro Eliminado.',
                'success'
              )
              jQuery('[data-id="' + resultado.id_eliminado + '"]').parents('tr').remove();
            } else {
              Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo eliminar!',
              })
            }
          }
        })
        // Swal.fire('Eliminado!', 'Registro Eliminado.', 'success')
        // console.log(result);
      } else if (result.isDismissed) {
        Swal.fire('Cancelado!', 'No se han guardado los cambios', 'info')
        // console.log(result);
      }
    })
  })
})