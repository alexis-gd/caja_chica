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
        searchPanes: {
            clear: true,
            collapse: true,
            showAll: true,
            cascadePanes: true,
            initCollapsed: true,
            // layout: 'columns-3', // Muestra 3 columnas de paneles.
            dtOpts: {
                dom: 'tp',
                searching: true,
                collapsed: true // Paneles ocultos por defecto
            }
        },
        dom: '<"top"PBf>rt<"bottom"ilp><"clear">',
        // dom: '<"dtsp-verticalContainer"<"dtsp-verticalPanes"P><"dtsp-dataTable"frtip>>',
        // dom: 'PBfrtip',
        // dom: 'Bfrtip',
        // dom: '<"top"PBfrt><"bottom"lip>',
        columnDefs: [
            {
                searchPanes: {
                    show: true // Mostrar en SearchPanes
                },
                targets: [2, 3, 9, 10, 11, 12] // Solo estas columnas estarán en los SearchPanes
            },
            {
                searchPanes: {
                    show: false // No mostrar en SearchPanes
                },
                targets: '_all' // Todas las demás columnas serán excluidas
            }
        ],
        sAjaxSource: "ServerSide/serversideUsuarios.php",
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]],
        processing: true,
        // serverSide: true,
        createdRow: function (row, data, index) {
            $('td', row).slice(0, -1).addClass('text-center');
            $('td', row).slice(0, -1).addClass('text-nowrap');
            if (data[1]) {
                let formatted = formatearFecha(data[1], 3);
                $('td', row).eq(1).html(formatted);
            }
        },
        drawCallback: function (settings) {
            if (!table) return; // Verificar si 'table' está definido

            totalIngreso = 0;
            totalEgreso = 0;
            totalMonto = 0;

            // Recalcular los totales al redibujar la tabla
            table.rows({ search: 'applied' }).every(function () {
                var data = this.data();

                // Sumar valores de las columnas de ingreso y egreso
                if (data[10]) {
                    totalIngreso += parseFloat(data[10]) || 0;
                }
                if (data[11]) {
                    totalEgreso += parseFloat(data[11]) || 0;
                }
            });

            // Actualizar los elementos con los totales
            $('#total_ingreso').text(totalIngreso.toFixed(2));
            $('#total_egreso').text(totalEgreso.toFixed(2));
            $('#total_monto').text((totalIngreso - totalEgreso).toFixed(2));
        },
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
            searchPanes: {
                title: {
                    _: 'Filtros activos - %d', // Mantiene el contador funcional
                    0: 'No hay filtros activos', // Texto sin filtros
                },
                collapse: {
                    0: 'Mostrar filtros', // Texto cuando los filtros están ocultos
                    _: 'Mostrar %d filtros', // Texto con el número de paneles
                },
                clearMessage: 'Limpiar filtros', // Texto del botón para limpiar
                showMessage: 'Mostrar todos los filtros', // Texto para el botón Show All
                collapseMessage: 'Colapsar todos los filtros' // Texto para el botón Collapse All
            },
        },
        responsive: false,
        scrollX: true,
        debug: true,
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
        'class="btn btn-azul btn-sm"><p class="d-flex align-items-center justify-content-center mb-0"><i class="fas fa-plus nav-icon pr-1"></i>Nuevo</p></button>';
    $('div .btn-group').append(button1);

    // Llenado de los select al abrir el modal
    $('#modal_add_caja').on('show.bs.modal', function () {
        const dateInput = document.getElementById("modal_caja_add_fecha");
        const today = new Date().toISOString().split("T")[0]; // Obtener la fecha en formato 'YYYY-MM-DD'
        dateInput.value = today; // Establecer el valor del campo de fecha

        fetchFillSelect('getModelGeneric', 'modal_caja_add_cargado', null, 'modelo_cargado');
        fetchFillSelect('getModelGeneric', 'modal_caja_add_area', null, 'modelo_area');
        // fetchFillSelect('getModelGeneric', 'modal_caja_add_empresa', null, 'modelo_empresa');
        // fetchFillSelect('getModelGeneric', 'modal_caja_add_entrega', null, 'modelo_entrega');
        // fetchFillSelect('getModelGeneric', 'modal_caja_add_tipo_ingreso', null, 'modelo_tipo_ingreso');
        fetchFillSelect('getModelGeneric', 'modal_caja_add_tipo_gasto', null, 'modelo_tipo_gasto');
        // fetchFillSelect('getModelGeneric', 'modal_caja_add_autoriza', null, 'modelo_autoriza');
        // fetchFillSelect('getModelGeneric', 'modal_caja_add_proveedor', null, 'modelo_proveedor');
        fetchFillSelect('getModelGeneric', 'modal_caja_add_recibe', null, 'modelo_recibe');
        fetchFillSelect('getModelGeneric', 'modal_caja_add_unidad', null, 'modelo_unidad');
        // fetchFillSelect('getModelGeneric', 'modal_caja_add_operador', null, 'modelo_operador');
        fetchFillSelect('getModelGeneric', 'modal_caja_add_comprobante', null, 'modelo_comprobante');
        fetchFillSelect('getModelGeneric', 'modal_caja_add_razon_social', null, 'modelo_razon_social');
        // fetchFillSelect('getModelGeneric', 'modal_caja_add_factura', null, 'modelo_factura');
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

    table.on('init', function () {
        $('.dtsp-clearAll').addClass('btn-clear-all');
        $('.dtsp-panesContainer').addClass('bg-gris rounded px-2 py-3 shadow-sm');
        $('.dtsp-topRow').addClass('bg-white');
    });
});
