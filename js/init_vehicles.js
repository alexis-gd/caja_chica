// ----------------------- VALIDACIONES ----------------------- //
validateInput('input', 'modal_vehicle_add_year', {max: 4, pattern: '^[0-9]*$'});
validateInput('input', 'modal_vehicle_add_serie', {max: 17, pattern: '^[a-zA-Z0-9]*$'});
validateInput('input', 'modal_vehicle_add_engine', {max: 20, pattern: '^[a-zA-Z0-9]*$'});
validateInput('input', 'modal_vehicle_add_pedimento', {max: 15, pattern: '^[a-zA-Z0-9]*$'});
validateInput('input', 'modal_vehicle_add_license_plate', {max: 7, pattern: '^[a-zA-Z0-9]*$'});
validateInput('input', 'modal_vehicle_add_observations', {max: 500});

$(document).ready(async function () {
    var table = $("#tablaVehiculos").DataTable({

        // Generar filtros por columna
        initComplete: function () {
            this.api()
                .columns()
                .every(function () {
                    var column = this;
                    var title = column.footer().textContent.toLowerCase();

                    // Create input element and add event listener
                    $('<input type="text" placeholder="Buscar"  class="form-control form-control-sm w-100" id="id_' + title + '"/>')
                        .appendTo($(column.footer()).empty())
                        .on('keyup change clear', function () {
                            if (column.search() !== this.value) {
                                column.search(this.value).draw();
                            }
                        });

                    if (title == 'estado') {
                        const inputParam = getUrlParameter('input');
                        const searchParam = getUrlParameter('search');
                        if (inputParam || searchParam) {
                            setTimeout(() => {
                                const inputElement = $('#' + inputParam);
                                inputElement.val(searchParam);
                                inputElement.trigger('change');
                                inputElement.focus();
                            }, 300);
                            window.history.replaceState({}, document.title, window.location.pathname);
                        }
                    }
                });
        },
        "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]],
        "processing": true,
        "serverSide": true,
        "sAjaxSource": "ServerSide/serversideUsuarios.php",
        "columns": [
            { "data": 0 }, // ID
            { "data": 1 }, // Marca
            { "data": 2 }, // Modelo
            { "data": 3 }, // Año
            { "data": 4 }, // Color
            { "data": 5 }, // Serie
            { "data": 6 }, // Motor
            { "data": 7 }, // Pedimento
            { "data": 8 }, // Propietario
            { "data": 9 }, // Placa
            { "data": 10 }, // Observaciones
            { "data": 11 } // Fecha de entrega
        ],
        "createdRow": function (row, data, index) {
            $('td', row).slice(0, 12).addClass('text-center');
            if (data[10]) {
                $('td', row).eq(10).addClass('ellipsis');
            }
        },
        // stateSave: true,
        language: {
            sProcessing: "Procesando...",
            sLengthMenu: "Mostrar _MENU_ registros",
            sZeroRecords: "No se encontraron resultados",
            sEmptyTable: "Ningún dato disponible en esta tabla",
            sInfo:
                "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
            sInfoEmpty: "Mostrando registros del 0 al 0 de un total de 0 registros",
            sInfoFiltered: "(filtrado de un total de _MAX_ registros)",
            sInfoPostFix: "",
            sSearch: "Buscar:",
            sSearchPlaceholder: "Filtrar",
            sUrl: "",
            sInfoThousands: ",",
            sLoadingRecords: "Cargando...",
            oPaginate: {
                sFirst: "Primero",
                sLast: "Último",
                sNext: "Siguiente",
                sPrevious: "Anterior",
            },
            oAria: {
                sSortAscending: ": Activar para ordenar la columna de manera ascendente",
                sSortDescending: ": Activar para ordenar la columna de manera descendente",
            },
            buttons: {
                copy: "Copiar",
                colvis: "Visibilidad",
            },
        },
        responsive: "true",
        dom: '<"top"Bf>rt<"bottom"ilp><"clear">',
        buttons: [
            {
                extend: "excelHtml5",
                text: '<i class="fas fa-file-excel"></i>',
                titleAttr: "Exportar a Excel",
                className: "btn btn-excel",
            },
            {
                extend: "pdfHtml5",
                text: '<i class="fas fa-file-pdf"></i>',
                titleAttr: "Exportar a PDF",
                className: "btn btn-pdf",
            },
            {
                extend: "print",
                text: '<i class="fas fa-print"></i>',
                titleAttr: "Imprimir",
                className: "btn btn-print",
            },
        ],

    });

    // Agregar color cuando se selecciona una fila
    $('#tablaVehiculos').on('click', '.odd, .even', function () {
        if ($(this).hasClass('clicked')) {
            $(this).removeClass('clicked');
        } else {
            $(this).addClass('clicked').siblings().removeClass('clicked');
        }
    });

    // Botón para crear un nuevo registro
    $('#btn-crear').remove();
    let button1 = '<button id="btn-crear" title="Añadir vehículo" type="text"' +
        'data-toggle="modal" data-target="#modal_add_vehicle"' +
        'class="btn btn-azul"><p class="d-flex align-items-center justify-content-center mb-0"><i class="fas fa-plus nav-icon pr-1"></i>Nuevo</p></button>';
    $('div .btn-group').append(button1);

    // Llenar los select del modal nuevo
    variableId('btn-crear').addEventListener('click', () => {
        const dateInput = document.getElementById("modal_vehicle_add_delivery_date");
        const today = new Date().toISOString().split("T")[0]; // Obtener la fecha en formato 'YYYY-MM-DD'
        dateInput.value = today; // Establecer el valor del campo de fecha

        // Llenado de select
        fetchFillSelect('getBrand', 'modal_vehicle_add_brand');
        fetchFillSelect('getModel', 'modal_vehicle_add_model');
        fetchFillSelect('getColor', 'modal_vehicle_add_color');
        fetchFillSelect('getOwner', 'modal_vehicle_add_owner');
    });

    // Escucha de eventos para cuando se completa la solicitud AJAX y se cargan los datos
    table.on('xhr.dt', function () {
        console.log("join xhr")

        // Editar al dar click
        $(document).on('click', '#tablaVehiculos tr', function () {
            // Seleccionar el primer td de la fila (id)
            let id = $(this).children('td').eq(0).text().trim(); // Usamos text() para obtener solo el contenido

            // Verificamos si el id tiene una longitud válida
            if (id.length >= 1 && id.length <= 6) {
                // Si el botón ya existe, solo actualizamos el enlace
                if ($('#btn-history').length) {
                    $('#btn-history').attr('href', `vehicle-detail.php?vehicle_id=${id}`);
                } else {
                    // Crear y agregar el botón si no existe
                    let button1 = `
                        <a id="btn-history" href="vehicle-detail.php?vehicle_id=${id}" class="d-flex btn btn-azul btn-md text-nowrap">
                            <p class="d-flex align-items-center justify-content-center mb-0 w-100">Ver / Editar</p>
                        </a>
                    `;
                    $('div .btn-group').append(button1);
                }
            } else {
                // Si el id no es válido, eliminamos el botón si existe
                $("#btn-history").remove();
            }
        });

        // Editar al dar dobleclick
        $(document).on('dblclick', '#tablaVehiculos tr', function () {
            // Seleccionar el primer td de la fila (id)
            let id = $(this).children('td').eq(0).text().trim(); // Usamos text() para obtener solo el contenido

            // Verificamos si el id tiene una longitud válida
            if (id.length >= 1 && id.length <= 6) {
                // Redirigir al enlace directamente en la misma página
                window.location.href = `vehicle-detail.php?vehicle_id=${id}`;
            }
        });
    });

    $(".btn-excel").removeClass("btn-secondary buttons-excel buttons-html5")
    $(".btn-pdf").removeClass("btn-secondary buttons-pdf buttons-html5")
    $(".btn-print").removeClass("btn-secondary buttons-print")

});
