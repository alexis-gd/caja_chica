// Crear variables por name
function variableName(name) {
  var name = document.querySelector("input[name=" + name + "]");
  return name;
}

// Crear variables select
function variableSelect(name) {
  var name = document.querySelector("select[name=" + name + "]");
  return name;
}

// Crear variables por id
function variableId(id) {
  var element = document.getElementById(id);
  return element;
}

// Marcar un input con error y deshabilitar un botón
function inputWarning(element, id_btn) {
  element.classList.add("border-error");
  disabled(id_btn);
  variableId(element.id).focus();
  setTimeout(() => {
    element.classList.remove("border-error");
    enabled(id_btn);
  }, 1500);

  // Si es un selectize
  let nextElement = element.nextElementSibling;
  if (nextElement && nextElement.classList.contains("selectize-control")) {
    let firstChildElement = nextElement.querySelector(":first-child");
    firstChildElement.classList.add("border-error-selectize");
    $("#" + element.id + "-selectized").focus();
    setTimeout(() => {
      firstChildElement.classList.remove("border-error-selectize");
    }, 1500);
  }
}

// Deshabilitar un elemento
function disabled(id) {
  variableId(id).disabled = true;
}

// Habilitar un elemento
function enabled(id) {
  variableId(id).disabled = false;
}

// Mostrar Alerta
function alertNotify(timer = '1500', type = 'warning', msj1, msj2 = '', position = 'bottom-end') {
  const Toast = Swal.mixin({
    toast: true,
    position: position,
    showConfirmButton: false,
    timer: timer,
    timerProgressBar: true
  })
  Toast.fire({
    icon: type,
    title: '¡' + msj1 + '! ' + msj2
  })
}

// Mostrar Alerta de notificación grande
function alertVerify(title, icon, html, timer) {
  if (timer) {
    Swal.fire({
      title: title,
      icon: icon,
      html: html,
      timer: timer,
      timerProgressBar: true
    })
  } else {
    Swal.fire({
      title: title,
      icon: icon,
      html: html,
    })
  }
}

// Spinner de carga
function printSpinner(btn, texto = '') {
  variableId(btn).innerHTML = '<i class="fas fa-spinner fa-pulse fa-estilo"></i> ' + texto;
}

function deleteSpinner(btn, text = '') {
  variableId(btn).innerHTML = text;
}

// Iniciar el botón de imprimir
document.querySelectorAll(".print").forEach(function (btn) {
  btn.addEventListener('click', function () {
    if (window.print) window.print();
  });
});

// Imprimir solo bloque seleccionado con el id
function printElement(elementId) {
  // Oculta todo el contenido antes de la impresión
  document.body.style.visibility = 'hidden';
  const element = document.getElementById(elementId);
  const originalContent = document.body.innerHTML;

  // Configura el contenido del div como el único contenido visible
  document.body.innerHTML = element.outerHTML;

  // Llama a window.print
  window.print();

  // Restaura el contenido original
  document.body.innerHTML = originalContent;
  // Restaura la visibilidad de todo el contenido después de imprimir
  document.body.style.visibility = 'visible';
}

// Retrocede a la página anterior
document.querySelectorAll('.btn-back').forEach(function (btn) {
  btn.addEventListener('click', function () {
    window.history.back();
  });
});

// ---------------------------------- Nuevas funciones ---------------------------------- //
// Ajax genérico
async function fetchGeneric(option, option_value, path) {
  let datos = new FormData();
  datos.append('opcion', option);
  datos.append('option_value', option_value);
  try {
    const response = await fetch(path, {
      method: 'POST',
      body: datos
    });
    const data = await response.json();
    return data;
  } catch (error) {
    alertVerify("Algo salio mal", "error", "<p>Revisa tu conexión a internet</p><small><b>Error: </b>" + error.message + "</small>");
  }
}

// Ajax para llenar input
function fetchFillInput(option, id_item, option_value = null, type = null) {
  let datos = new FormData();
  datos.append('opcion', option);
  datos.append('option_value', option_value);
  fetch('functions/select_general.php', {
    method: 'POST',
    body: datos
  })
    .then(response => response.json())
    .then(data => {
      if (!type) {
        document.getElementById(id_item).value = data;
      } else {
        document.getElementById(id_item).innerHTML = data;
      }
    })
    .catch(() => {
      alertVerify("Algo salio mal", "error", "<p>Revisa tu conexión a internet</p><small><b>Error: </b>" + error.message + "</small>");
    });
};

// Ajax para llenar select 
function fetchFillSelect(option, id_item, selectedId = null, option_value = '', typeSelect = null) {
  let datos = new FormData();
  datos.append('opcion', option);
  datos.append('option_value', option_value);

  fetch('functions/select_general.php', {
    method: 'POST',
    body: datos
  })
    .then(response => response.json())
    .then(data => {
      if (data.type === 'SUCCESS') {
        // Si la respuesta es exitosa, actualizar el contenido del select
        document.getElementById(id_item).innerHTML = data.response;

        if (typeSelect) {
          // [select2]
          $("#" + id_item).select2({
            language: {
              noResults: function () {
                return "No hay resultados";
              },
              searching: function () {
                return "Buscando..";
              }
            }
          });
          // pre seleccionar el id [select2]
          if (selectedId) {
            $("#" + id_item).val(selectedId).trigger('change');
          }
        } else {
          // Configuración de Selectize [selectize]
          const selectElement = document.getElementById(id_item);
          const selectId = selectElement.dataset.selectId; // Obtener el valor de data-select-id
          const languageConfig = {
            createText: "Agregar", // Cambiar "Add" por "Agregar"
            noResults: "No hay resultados", // Ejemplo para otros textos (aunque Selectize no usa esto directamente)
            searching: "Buscando..." // Personalizable si es necesario
          };

          // Verificar si existe el atributo data-select-id
          if (!selectId) {
            $(function () {
              $("#" + id_item).selectize({});
            });
          } else {
            $(selectElement).selectize({
              create: true,
              render: {
                option_create: function (data, escape) {
                  return `<div class="create">${languageConfig.createText}: <strong>${escape(data.input)}</strong></div>`;
                }
              },
              onOptionAdd: function (value) {
                // Manejar la nueva opción
                handleNewOptionAdd(value, this);
              }
            });
          }
          // pre seleccionar el id [selectize]
          if (selectedId) {
            setTimeout(() => {
              $("#" + id_item).val(selectedId);
              const select = $("#" + id_item).selectize({});
              const control = select[0].selectize;
              control.setValue([selectedId]);
            }, 100);
          }
        }
      } else {
        // Si hubo un error, mostrar el mensaje de error
        alertVerify("Algo salió mal", "error", "<p>" + data.message + "</p>");
      }
    })
    .catch(error => {
      console.error(error);
      alertVerify("Algo salió mal", "error", "<p>Revisa tu conexión a internet</p><small><b>Error: </b>" + error + "</small>");
    });
}

// Función genérica para manejar nuevas opciones
function handleNewOptionAdd(newOption, selectInstance) {
  const selectElement = selectInstance.$input[0]; // Elemento select real
  const selectId = selectElement.dataset.selectId; // Tipo de operación (e.g., insertBrand)

  // Preparar datos para la solicitud fetch
  const datos = new FormData();
  datos.append('opcion', 'insertModelsGeneric');
  datos.append('tabla', selectId);
  datos.append('newOption', newOption);

  // Enviar la nueva opción al backend
  fetch('functions/insert_general.php', {
    method: 'POST',
    body: datos
  })
    .then(response => response.json())
    .then(data => {
      if (data.type === 'SUCCESS') {
        const datos = data.response;

        const newId = datos.newId; // ID generado por el backend

        // Encuentra y actualiza la opción generada por Selectize
        const optionElement = Array.from(selectElement.options).find(option => option.text === newOption);
        if (optionElement) {
          optionElement.value = newId; // Actualiza el value del option

          // Actualiza Selectize y selecciona el nuevo valor
          selectInstance.updateOption(newOption, { value: newId, text: newOption });
          selectInstance.setValue(newId); // Selecciona el nuevo valor automáticamente
          alertNotify('2000', 'success', 'Guardado', data.message, 'bottom-end');
        } else {
          console.error("No se encontró el option para actualizar.");
        }
      } else {
        console.error("Error al agregar la nueva opción: Datos inválidos del servidor.");
      }
    })
    .catch(error => {
      alertVerify("Algo salió mal", "error", `<p>No se encontró modelo comunícate con soporte y comparte el error</p><small><b>Error: </b>${error.message}</small>`);
    });
}

// Función para manejar el evento de escritura en el input
function handleInput(event) {
  let cursorPosition = event.target.selectionStart;
  let inputValue = event.target.value;
  let formattedValue = capitalizeFirstLetter(inputValue);

  event.target.value = formattedValue;
  event.target.setSelectionRange(cursorPosition, cursorPosition);
}

// Función para convertir la primera letra a mayúscula y el resto a minúsculas
function capitalizeFirstLetter(string) {
  return string.charAt(0).toUpperCase() + string.slice(1).toLowerCase();
}

// Función para limpiar los select de selectize
function clearSelectize(id) {
  const select = $("#" + id).selectize({});
  const control = select[0].selectize;
  control.clear();
}

function resetSelectize(id) {
  const select = $("#" + id).selectize({});
  const control = select[0].selectize;
  control.destroy();
  control.clear();
}

// Función para formatear la fecha y hora
function formatearFecha(fecha, tipo = 1) {
  const diasSemana = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
  const meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

  // Dividir la fecha en partes
  const partes = fecha.split(' ');
  const fechaPartes = partes[0].split('-');
  const horaPartes = partes[1]?.split(':') || [];

  let fechaFormateada = 'No mostrada';

  // Crear la fecha formateada
  if (tipo === 1) {
    fechaFormateada = `${fechaPartes[2]}/${fechaPartes[1]}/${fechaPartes[0]}`;
  } else if (tipo === 2) {
    fechaFormateada = `${horaPartes[0]}:${horaPartes[1]}`;
  } else if (tipo === 3) {
    // Crear una fecha para calcular el día de la semana
    const date = new Date(`${fechaPartes[0]}-${fechaPartes[1]}-${fechaPartes[2]}`);
    const diaSemana = diasSemana[date.getDay()];
    const mes = meses[parseInt(fechaPartes[1], 10) - 1];
    fechaFormateada = `${diaSemana} ${fechaPartes[2]} de ${mes} ${fechaPartes[0]}`;
  }

  return fechaFormateada;
}

// Función para obtener un parámetro de la URL
function getUrlParameter(sParam) {
  let sPageURL = window.location.search.substring(1),
    sURLVariables = sPageURL.split('&'),
    sParameterName,
    i;

  for (i = 0; i < sURLVariables.length; i++) {
    sParameterName = sURLVariables[i].split('=');

    if (sParameterName[0] === sParam) {
      return sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
    }
  }
  return false;
}

/**
 * Valida un campo de entrada basado en las opciones proporcionadas.
 *
 * @param {string} type - Tipo del campo (no se utiliza actualmente, pero se puede usar para diferenciar entre inputs, textareas, etc.).
 * @param {string} id - ID del elemento HTML que será validado.
 * @param {object} options - Opciones de validación.
 *    * min: {number} - Longitud mínima permitida para el valor.
 *    * max: {number} - Longitud máxima permitida para el valor.
 *    * pattern: {string} - Expresión regular para restringir el formato del valor.
 *      Ejemplo: '^[a-zA-Z]*$' // Solo letras
 *    * required: {boolean} - Indica si el campo es obligatorio.
 *    * customValidation: {function} - Función personalizada para validaciones adicionales.
 *
 * Ejemplo de uso:
 * validateInput('input', 'mi_input', { 
 *    min: 1, 
 *    max: 4, 
 *    pattern: '^[a-zA-Z]*$', // Solo letras 
 *    pattern: '^[0-9]*$'; // Solo números
 *    required: true 
 * });
 */
// Función general de validación
function validateInput(type, id, options = {}) {
  const inputElement = document.getElementById(id);

  if (!inputElement) {
    console.error(`El elemento con ID '${id}' no existe.`);
    return;
  }

  inputElement.addEventListener('input', (event) => {
    let value = inputElement.value;

    // Validación de longitud mínima y máxima
    if (options.min !== undefined && value.length < options.min) {
      value = value.slice(0, options.min);
    }

    if (options.max !== undefined && value.length > options.max) {
      value = value.slice(0, options.max);
    }

    // Validación de contenido
    if (options.pattern) {
      const regex = new RegExp(options.pattern);
      if (!regex.test(value)) {
        value = value.slice(0, -1); // Elimina el último carácter si no cumple con el patrón
      }
    }

    inputElement.value = value;
  });

  // Validación adicional en el evento de blur (opcional)
  inputElement.addEventListener('blur', () => {
    if (options.required && !inputElement.value) {
      alert(`El campo ${id} es obligatorio.`);
    }

    if (options.customValidation && typeof options.customValidation === 'function') {
      const validationMessage = options.customValidation(inputElement.value);
      if (validationMessage) {
        alert(validationMessage);
      }
    }
  });
}

// Ejemplo de inicialización
// validateInput('input', 'id_input', {
//   min: 1,
//   max: 4,
//   pattern: '^[a-zA-Z0-9]*$', // Solo letras y números
//   required: true,
//   customValidation: (value) => {
//       if (value === '1234') {
//           return 'El valor no puede ser "1234"';
//       }
//       return null;
//   }
// });
// validateInput('input', 'another_input', {
//   pattern: '^[a-zA-Z]*$', // Solo letras
//   max: 10
// });