$(document).ready(function () {
  // Iniciar sesion
  $('#login-admin').on('submit', function (e) {
    e.preventDefault();
    printSpinner('btn_login', 'Iniciando sesión...');

    if (variableId("usuario").value == "") {
      alertNotify("1500", "info", "Espera", "El campo no puede ir vacío.")
      inputWarning(variableId("usuario"), "btn_login");
      setTimeout(() => { deleteSpinner('btn_login', 'Iniciar sesión'); }, 1500);
      return false;
    }
    if (variableId("password").value == "") {
      alertNotify("1500", "info", "Espera", "El campo no puede ir vacío.")
      inputWarning(variableId("password"), "btn_login");
      setTimeout(() => { deleteSpinner('btn_login', 'Iniciar sesión'); }, 1500);
      return false;
    }
    var datos = $(this).serializeArray();

    disabled('btn_login');
    $.ajax({
      type: $(this).attr('method'),
      data: datos,
      url: $(this).attr('action'),
      dataType: 'json',
      success: function (data) {
        var response = data;
        if (response.response == 'SUCCESS') {
          window.location.href = 'dashboard.php';
        } else if (response.response == 'CANCEL') {
          setTimeout(() => {
            enabled('btn_login');
            deleteSpinner('btn_login', 'Iniciar sesión');
          }, 2000);
          alertVerify("Algo salio mal", "error", "<p>" + response.message + "</p>");
        } else {
          setTimeout(() => {
            enabled('btn_login');
            deleteSpinner('btn_login', 'Iniciar sesión');
          }, 2000);
          alertNotify('2000', 'error', 'Ops', response.message, 'bottom-end');
        }
      }
    })
  })
})