// ----------------------- VALIDACIONES ----------------------- //
// validateInput('input', 'modal_vehicle_add_year', {max: 4, pattern: '^[0-9]*$'});
// validateInput('input', 'modal_vehicle_add_serie', {max: 17, pattern: '^[a-zA-Z0-9]*$'});
// validateInput('input', 'modal_vehicle_add_engine', {max: 20, pattern: '^[a-zA-Z0-9]*$'});
// validateInput('input', 'modal_vehicle_add_pedimento', {max: 15, pattern: '^[a-zA-Z0-9]*$'});
// validateInput('input', 'modal_vehicle_add_license_plate', {max: 7, pattern: '^[a-zA-Z0-9]*$'});
// validateInput('input', 'modal_vehicle_add_observations', {max: 500});

let totalIngreso = 0;
let totalEgreso = 0;
let totalMonto = 0;

$(document).ready(async function () {
    var table = $("#tablaCaja").DataTable({

        // Generar filtros por columna
        initComplete: function () {
            this.api()
                .columns()
                .every(function () {
                    var column = this;
                    var title = column.footer().textContent.toLowerCase();

                    // Create input element and add event listener
                    $('<input type="text" placeholder="Buscar"  class="form-control form-control-sm w-100" name="id_' + title + '"/>')
                        .appendTo($(column.footer()).empty())
                        .on('keyup change clear', function () {
                            if (column.search() !== this.value) {
                                column.search(this.value).draw();
                            }
                        });
                });
        },
        "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]],
        "processing": true,
        "serverSide": true,
        "sAjaxSource": "ServerSide/serversideUsuarios.php",
        "createdRow": function (row, data, index) {
            $('td', row).slice(0, -1).addClass('text-center');
            if (data[1]) {
                let formatted = formatearFecha(data[1]);
                $('td', row).eq(1).html(formatted);
            }

            // Sumar los valores de las columnas
            if (data['17']) {
                totalIngreso += parseFloat(data['17']) || 0; // Sumar valores de ingreso
                console.log(totalIngreso)
            }
            if (data['18']) {
                totalEgreso += parseFloat(data['18']) || 0; // Sumar valores de egreso
                console.log(totalEgreso)
            }
        },
        "drawCallback": function (settings) {
            // Actualizar los elementos en el DOM con las sumas
            $('#total_ingreso').text(totalIngreso.toFixed(2));
            $('#total_egreso').text(totalEgreso.toFixed(2));
            $('#total_monto').text((totalIngreso - totalEgreso).toFixed(2));

            // Reiniciar las sumas para el siguiente procesamiento (opcional si el drawCallback recalcula todo)
            totalIngreso = 0;
            totalEgreso = 0;
            totalMonto = 0;
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
        responsive: false,
        scrollX: true,
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
    $('#tablaCaja').on('click', '.odd, .even', function () {
        if ($(this).hasClass('clicked')) {
            $(this).removeClass('clicked');
        } else {
            $(this).addClass('clicked').siblings().removeClass('clicked');
        }
    });

    // Botón para crear un nuevo registro
    $('#btn-crear').remove();
    let button1 = '<button id="btn-crear" title="Añadir registro" type="text"' +
        'data-toggle="modal" data-target="#modal_add_caja"' +
        'class="btn btn-azul"><p class="d-flex align-items-center justify-content-center mb-0"><i class="fas fa-plus nav-icon pr-1"></i>Nuevo</p></button>';
    $('div .btn-group').append(button1);

    // Llenado de los select al abrir el modal
    $('#modal_add_caja').on('show.bs.modal', function () {
        const dateInput = document.getElementById("modal_caja_add_fecha");
        const today = new Date().toISOString().split("T")[0]; // Obtener la fecha en formato 'YYYY-MM-DD'
        dateInput.value = today; // Establecer el valor del campo de fecha

        fetchFillSelect('getModelGeneric', 'modal_caja_add_cargado', null, 'modelo_cargado');
        fetchFillSelect('getModelGeneric', 'modal_caja_add_area', null, 'modelo_area');
        fetchFillSelect('getModelGeneric', 'modal_caja_add_empresa', null, 'modelo_empresa');
        fetchFillSelect('getModelGeneric', 'modal_caja_add_entrega', null, 'modelo_entrega');
        fetchFillSelect('getModelGeneric', 'modal_caja_add_tipo_ingreso', null, 'modelo_tipo_ingreso');
        fetchFillSelect('getModelGeneric', 'modal_caja_add_tipo_gasto', null, 'modelo_tipo_gasto');
        fetchFillSelect('getModelGeneric', 'modal_caja_add_autoriza', null, 'modelo_autoriza');
        fetchFillSelect('getModelGeneric', 'modal_caja_add_proveedor', null, 'modelo_proveedor');
        fetchFillSelect('getModelGeneric', 'modal_caja_add_recibe', null, 'modelo_recibe');
        fetchFillSelect('getModelGeneric', 'modal_caja_add_unidad', null, 'modelo_unidad');
        fetchFillSelect('getModelGeneric', 'modal_caja_add_operador', null, 'modelo_operador');
        fetchFillSelect('getModelGeneric', 'modal_caja_add_comprobante', null, 'modelo_comprobante');
        fetchFillSelect('getModelGeneric', 'modal_caja_add_factura', null, 'modelo_factura');
    });

    // Escucha de eventos para cuando se completa la solicitud AJAX y se cargan los datos
    // table.on('xhr.dt', function () {
    //     console.log("join xhr")

    //     // Editar al dar click
    //     $(document).on('click', '#tablaCaja tr', function () {
    //         // Seleccionar el primer td de la fila (id)
    //         let id = $(this).children('td').eq(0).text().trim(); // Usamos text() para obtener solo el contenido

    //         // Verificamos si el id tiene una longitud válida
    //         if (id.length >= 1 && id.length <= 6) {
    //             // Si el botón ya existe, solo actualizamos el enlace
    //             if ($('#btn-history').length) {
    //                 $('#btn-history').attr('href', `vehicle-detail.php?vehicle_id=${id}`);
    //             } else {
    //                 // Crear y agregar el botón si no existe
    //                 let button1 = `
    //                     <a id="btn-history" href="vehicle-detail.php?vehicle_id=${id}" class="d-flex btn btn-azul btn-md text-nowrap">
    //                         <p class="d-flex align-items-center justify-content-center mb-0 w-100">Ver / Editar</p>
    //                     </a>
    //                 `;
    //                 $('div .btn-group').append(button1);
    //             }
    //         } else {
    //             // Si el id no es válido, eliminamos el botón si existe
    //             $("#btn-history").remove();
    //         }
    //     });

    //     // Editar al dar dobleclick
    //     $(document).on('dblclick', '#tablaCaja tr', function () {
    //         // Seleccionar el primer td de la fila (id)
    //         let id = $(this).children('td').eq(0).text().trim(); // Usamos text() para obtener solo el contenido

    //         // Verificamos si el id tiene una longitud válida
    //         if (id.length >= 1 && id.length <= 6) {
    //             // Redirigir al enlace directamente en la misma página
    //             window.location.href = `vehicle-detail.php?vehicle_id=${id}`;
    //         }
    //     });
    // });

    $(".btn-excel").removeClass("btn-secondary buttons-excel buttons-html5")
    $(".btn-pdf").removeClass("btn-secondary buttons-pdf buttons-html5")
    $(".btn-print").removeClass("btn-secondary buttons-print")

});
