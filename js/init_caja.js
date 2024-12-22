// ----------------------- VALIDACIONES ----------------------- //
validateInput('input', 'modal_caja_add_ingreso', { max: 10, pattern: '^[0-9]*\\.?[0-9]{0,2}$' });
validateInput('input', 'modal_caja_add_egreso', { max: 10, pattern: '^[0-9]*\\.?[0-9]{0,2}$' });

let totalIngreso = 0;
let totalEgreso = 0;

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
            cascadePanes: true,
            initCollapsed: true,
            layout: 'columns-4', // Muestra 3 columnas de paneles.
            dtOpts: {
                dom: 'tp',
                searching: false,
            }
        },
        dom: '<"top"PBf>rt<"bottom"ilp><"clear">',
        // dom: 'PBfrtip',
        // dom: 'Bfrtip',
        columnDefs: [
            {
                searchPanes: {
                    show: true, // Mostrar en SearchPanes
                    header: 'Cargado a' // Cambiar el nombre del filtro
                },
                targets: [2] // Solo la columna 2 tendrá este nombre de filtro
            },
            {
                searchPanes: {
                    show: true // Mostrar en SearchPanes
                },
                targets: [3, 4, 5, 6, 7, 8, 9] // Solo estas columnas estarán en los SearchPanes
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
            $('td', row).each(function (index) {
                if (index !== 5) {
                    $(this).addClass('text-nowrap');
                }
            });
            $('td', row).eq(5).addClass('text-ellipsis');
            if (data[1]) {
                let formatted = formatearFecha(data[1], 3);
                $('td', row).eq(1).html(formatted);
            }
            if (data[8] === 'Pendiente' && data[11] > 0) {
                $('td', row).eq(8).html(`<button class="btn btn-sm btn-azul" data-toggle="modal" data-target="#modal_add_comprobante" data-id="${data[0]}">Subir comprobante</button>`);
            }
            if (data[10] > 0) {
                $('td', row).eq(10).html(`<span class="badge badge-verde w-100">${formatCurrency(data[10], '$')}</span>`);
            }
            if (data[11] > 0) {
                $('td', row).eq(11).html(`<span class="badge badge-rojo w-100">${formatCurrency(data[11], '$')}</span>`);
            }
            if (data[12] > 0) {
                $('td', row).eq(12).html(formatCurrency(data[12], '$'));
            }
        },
        drawCallback: function (settings) {
            if (!table) return; // Verificar si 'table' está definido
            totalIngreso = 0;
            totalEgreso = 0;

            // Re calcular los totales al re dibujar la tabla
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

            $('#total_ingreso').text(formatCurrency(totalIngreso, '$'));
            $('#total_egreso').text(formatCurrency(totalEgreso, '$'));
            $('#total_saldo').text(formatCurrency((totalIngreso - totalEgreso), '$'));
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
                showMessage: 'Mostrar filtros', // Texto para el botón Show All
                collapseMessage: 'Colapsar filtros' // Texto para el botón Collapse All
            },
        },
        responsive: false,
        scrollX: true,
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
        'class="btn btn-azul btn-sm"><p class="d-flex align-items-center justify-content-center mb-0"><i class="fas fa-solid fa-plus nav-icon pr-1"></i>Nuevo</p></button>';
    $('div .btn-group').append(button1);

    // Llenado de los select al abrir el modal
    $('#modal_add_caja').on('show.bs.modal', function () {
        // Obtener la fecha y hora actuales en la zona horaria específica
        const today = moment().tz('America/Mexico_City').format('YYYY-MM-DDTHH');
        $('#modal_caja_add_fecha').val(today).trigger('change');

        // Establecer la fecha en Flatpickr
        $('#modal_caja_add_fecha')[0]._flatpickr.setDate(today);

        fetchFillSelect('getModelGeneric', 'modal_caja_add_cargado', null, 'modelo_cargado');
        fetchFillSelect('getModelGeneric', 'modal_caja_add_area', null, 'modelo_area');
        fetchFillSelect('getModelGeneric', 'modal_caja_add_tipo_gasto', null, 'modelo_tipo_gasto');
        fetchFillSelect('getModelGeneric', 'modal_caja_add_recibe', null, 'modelo_recibe');
        fetchFillSelect('getModelGeneric', 'modal_caja_add_unidad', null, 'modelo_unidad');
        fetchFillSelect('getModelGeneric', 'modal_caja_add_comprobante', null, 'modelo_comprobante');
        fetchFillSelect('getModelGeneric', 'modal_caja_add_razon_social', null, 'modelo_razon_social');
    });

    // Llenado de los select al abrir el modal
    $('#modal_edit_caja').on('show.bs.modal', async function () {
        const cajaId = $('#modal_caja_edit_id').val();

        try {
            // Obtenemos los datos del vehículo
            const data = await fetchGeneric('getPettyCashDetails', cajaId, 'functions/select_general.php');

            fetchFillSelect('getModelGeneric', 'modal_caja_edit_cargado', data.id_cargado, 'modelo_cargado');
            fetchFillSelect('getModelGeneric', 'modal_caja_edit_area', data.id_area, 'modelo_area');
            fetchFillSelect('getModelGeneric', 'modal_caja_edit_tipo_gasto', data.id_tipo_gasto, 'modelo_tipo_gasto');
            fetchFillSelect('getModelGeneric', 'modal_caja_edit_recibe', data.id_recibe, 'modelo_recibe');
            fetchFillSelect('getModelGeneric', 'modal_caja_edit_unidad', data.id_unidad, 'modelo_unidad');
            fetchFillSelect('getModelGeneric', 'modal_caja_edit_comprobante', data.id_comprobante, 'modelo_comprobante');
            fetchFillSelect('getModelGeneric', 'modal_caja_edit_razon_social', data.id_razon_social, 'modelo_razon_social');
            variableId("modal_caja_edit_concepto").value = data.concepto;
            variableId("modal_caja_edit_ingreso").value = data.ingreso;
            variableId("modal_caja_edit_egreso").value = data.egreso;
            // Establecer la fecha en Flatpickr
            $('#modal_caja_edit_fecha').val(data.fecha).trigger('change');
            $('#modal_caja_edit_fecha')[0]._flatpickr.setDate(data.fecha);
        } catch (error) {
            console.error("Error al cargar los detalles:", error);
        }
    });

    // Escucha de eventos para cuando se completa la solicitud AJAX y se cargan los datos
    table.on('xhr.dt', function () {

        // Editar al dar click
        $(document).on('click', '#tablaCaja tr', function () {
            // Seleccionar el primer td de la fila (id)
            let id = $(this).children('td').eq(0).text().trim(); // Usamos text() para obtener solo el contenido

            // Verificamos si el id tiene una longitud válida
            if (id.length >= 1 && id.length <= 6) {
                // Si el botón ya existe, solo actualizamos el enlace
                if ($('#ver_editar').length) {
                    $('#modal_caja_edit_id').val(id);
                } else {
                    $('#modal_caja_edit_id').val(id);
                    // Crear y agregar el botón si no existe
                    let button1 = `<button id="ver_editar" title="Ver o editar" type="text"
                        data-toggle="modal" data-target="#modal_edit_caja"
                        class="btn btn-azul btn-sm"><p class="d-flex align-items-center justify-content-center mb-0"><i class="fas fa-solid fa-file-pen nav-icon pr-1"></i>Ver / Editar</p></button>`;
                    $('div .btn-group').append(button1);
                }
            } else {
                // Si el id no es válido, eliminamos el botón si existe
                $("#ver_editar").remove();
            }
        });

        // Editar al dar dobleclick
        $(document).on('dblclick', '#tablaCaja tr', function () {
            // Seleccionar el primer td de la fila (id)
            let id = $(this).children('td').eq(0).text().trim(); // Usamos text() para obtener solo el contenido

            // Verificamos si el id tiene una longitud válida
            if (id.length >= 1 && id.length <= 6) {
                $('#modal_caja_edit_id').val(id);
                $("#modal_edit_caja").modal("show");
            }
        });
    });

    $(".btn-excel").removeClass("btn-secondary buttons-excel buttons-html5")
    $(".btn-pdf").removeClass("btn-secondary buttons-pdf buttons-html5")
    $(".btn-print").removeClass("btn-secondary buttons-print")

    table.on('init', function () {
        $('.dtsp-narrow').removeClass('dtsp-narrow'); // quitamos clase del primer elemento para que no se descuadre
        $('.dtsp-collapseAll').addClass('btn-collapse-all btn btn-primary btn-sm'); // botones especiales
        $('.dtsp-showAll').addClass('btn-show-all btn btn-success btn-sm mr-md-2'); // botones especiales
        $('.dtsp-clearAll').addClass('btn-clear-all btn btn-danger btn-sm mr-md-2'); // botones especiales
        $('.dtsp-panesContainer').addClass('bg-gris rounded px-2 py-3 shadow-sm w-100'); // fondo de search panels
        $('.dtsp-topRow').addClass('bg-white'); // columnas de blanco
        $('.dtsp-subRow1').addClass('h-100'); // columnas de blanco
        $('input.dtsp-search').addClass('h-100 pb-0'); // columnas de blanco

        // Filtro de rango de fechas
        if ($('.date-range-filter').length === 0) {
            $(`<div class="date-range-filter">
                        <div class="row mx-0">
                            <div class="col-12 d-flex flex-column flex-md-row align-items-center px-0 py-3 gap-1">
                                <p class="text-center text-md-left mb-0">Filtrar por rango de fechas</p>
                                <div>
                                    <button id="filterToday" class="btn btn-sm btn-primary mr-0 mr-md-1">Hoy</button>
                                    <button id="filterMonth" class="btn btn-sm btn-primary mr-0 mr-md-1">Este Mes</button>
                                    <button id="clearDates" class="btn btn-sm btn-danger">Limpiar</button>
                                </div>
                            </div>
                        </div>
                        <div class="row mx-0">
                            <div class="col-md-3 col-12 px-0 pr-md-2 pb-2 pb-md-0">
                                <div class="input-group h-100">
                                    <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                    <input type="date" id="startDate" class="form-control form-control-sm h-100" placeholder="A partir de:"/>
                                </div>
                            </div>
                            <div class="col-md-3 col-12 px-0">
                                <div class="input-group h-100">
                                    <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                    <input type="date" id="endDate" class="form-control form-control-sm h-100" placeholder="Hasta:"/>
                                </div>
                            </div>
                        </div>
                        <hr>`).prependTo(".dtsp-panesContainer")
                .on('change', 'input', function () {
                    table.draw();
                });
        }
        // Eliminar eventos anteriores para evitar múltiples listeners
        $('#filterToday').off('click').on('click', function () {
            const today = moment().tz('America/Mexico_City').format('YYYY-MM-DD');
            $('#startDate').val(today).trigger('change');
            $('#endDate').val(today).trigger('change');
            $('#startDate')[0]._flatpickr.setDate(today);
            $('#endDate')[0]._flatpickr.setDate(today);
            table.draw();
        });

        $('#filterMonth').off('click').on('click', function () {
            const date = moment().tz('America/Mexico_City');
            const firstDay = date.startOf('month').format('YYYY-MM-DD');
            const lastDay = date.endOf('month').format('YYYY-MM-DD');
            $('#startDate').val(firstDay).trigger('change');
            $('#endDate').val(lastDay).trigger('change');
            $('#startDate')[0]._flatpickr.setDate(firstDay);
            $('#endDate')[0]._flatpickr.setDate(lastDay);
            table.draw();
        });

        $('#clearDates').off('click').on('click', function () {
            $('#startDate').val('').trigger('change');
            $('#endDate').val('').trigger('change');
            $('#startDate')[0]._flatpickr.clear();
            $('#endDate')[0]._flatpickr.clear();
            table.draw();
        });

        // Configura Flatpickr en los inputs de fecha
        if (!$("#startDate").hasClass("flatpickr-input")) {
            flatpickr("#startDate", {
                altInput: true,
                altFormat: "D j \\d\\e F Y", // Formato amigable: Vie 21 de Dic 2024
                dateFormat: "Y-m-d", // Formato para trabajar internamente
                allowInput: true,
                locale: "es", // Configura el idioma español
                onChange: function () {
                    $("#tablaCaja").DataTable().draw(); // Actualiza la tabla al cambiar las fechas
                },
                disableMobile: "true"
            });
        }

        if (!$("#endDate").hasClass("flatpickr-input")) {
            flatpickr("#endDate", {
                altInput: true,
                altFormat: "D j \\d\\e F Y", // Formato amigable: Vie 21 de Dic 2024
                dateFormat: "Y-m-d", // Formato para trabajar internamente
                allowInput: true,
                locale: "es", // Configura el idioma español
                onChange: function () {
                    $("#tablaCaja").DataTable().draw(); // Actualiza la tabla al cambiar las fechas
                },
                disableMobile: "true"
            });
        }

        // Filtro personalizado para rango de fechas
        $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
            var startDate = $('#startDate').val();
            var endDate = $('#endDate').val();
            var dateColumnIndex = 1; // Ajusta esto al índice de la columna de fechas
            var dateString = data[dateColumnIndex] || ''; // Obtén la fecha en formato string
            var date = dateString.split(' ')[0]; // Extrae solo la parte de la fecha (YYYY-MM-DD)

            if (startDate && date < startDate) {
                return false;
            }
            if (endDate && date > endDate) {
                return false;
            }
            return true;
        });

    });

    // Inicializar Flatpickr
    flatpickr("#modal_caja_add_fecha", {
        altInput: true,
        altFormat: "D j \\d\\e F Y", // Formato amigable: Vie 21 de Dic 2024
        dateFormat: "Y-m-d", // Formato para trabajar internamente
        allowInput: true,
        locale: "es", // Configura el idioma español
        disableMobile: "true"
    });

    // Inicializar Flatpickr
    flatpickr("#modal_caja_edit_fecha", {
        altInput: true,
        altFormat: "D j \\d\\e F Y", // Formato amigable: Vie 21 de Dic 2024
        dateFormat: "Y-m-d", // Formato para trabajar internamente
        allowInput: true,
        locale: "es", // Configura el idioma español
        disableMobile: "true"
    });
});
