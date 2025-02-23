// ----------------------- VALIDACIONES ----------------------- //
validateInput('input', 'modal_caja_add_ingreso', { max: 10, pattern: '^[0-9]*\\.?[0-9]{0,2}$' });
validateInput('input', 'modal_caja_add_egreso', { max: 10, pattern: '^[0-9]*\\.?[0-9]{0,2}$' });

let totalIngreso = 0;
let totalEgreso = 0;
let saldo = 0;

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
            cascadePanes: false,
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
                targets: [3, 4, 6, 7, 8, 9, 10, 11] // Solo estas columnas estarán en los SearchPanes
            },
            {
                searchPanes: {
                    show: false // No mostrar en SearchPanes
                },
                targets: '_all' // Todas las demás columnas serán excluidas
            }
        ],
        sAjaxSource: "ServerSide/serversideUsuariosChica.php",
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]],
        processing: true,
        // serverSide: true,
        createdRow: function (row, data, index) {
            // Reiniciar el saldo al inicio de la creación de filas
            if (index === 0) {
                saldo = 0;
            }

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
            if (data[8] === 'Pendiente') {
                $('td', row).eq(8).html(`<button class="btn btn-sm btn-azul" data-toggle="modal" data-target="#modal_add_comprobante" data-id="${data[0]}">Subir comprobante</button>`);
            }
            if (data[10] > 0) {
                $('td', row).eq(10).html(`<span class="badge badge-verde w-100">${formatCurrency(data[10], '$')}</span>`);
            }
            if (data[11] > 0) {
                $('td', row).eq(11).html(`<span class="badge badge-rojo w-100">${formatCurrency(data[11], '$')}</span>`);
            }

            // Calcular el saldo
            if (data[10] > 0) {
                saldo = saldo + parseFloat(data[10]);
            }
            if (data[11] > 0) {
                saldo = saldo - parseFloat(data[11]);
            }
            if (data[12]) {
                $('td', row).eq(12).html(formatCurrency(saldo, '$'));
            }
            // Actualizar el total acumulado saldo en caja
            $('#saldo_caja').text(formatCurrency(saldo, '$'));

            // Guardar el saldo actualizado en localStorage
            localStorage.setItem('saldoCaja', saldo);
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
                customize: function (win) {
                    // Obtener la fecha actual formateada como "29 de enero del 2025"
                    let fechaGeneracion = new Date();
                    let opcionesFecha = { day: '2-digit', month: 'long', year: 'numeric' };
                    let fechaFormateada = fechaGeneracion.toLocaleDateString('es-MX', opcionesFecha);
                    let fechaTitulo = fechaFormateada.replace(/(\d+)\sde\s(\w+)\sdel\s(\d{4})/, '$1 de $2 del $3'); // Cambiar "de enero" por "de enero del"

                    // Cambiar el título de la página en blanco
                    win.document.title = `Reporte de Caja Chica - ${fechaTitulo}`; // Título personalizado para la página en blanco

                    $(win.document.body)
                        .css('font-size', '12pt')
                        .prepend('<h2 style="text-align:center;">Reporte de Caja Chica</h2>' +
                            '<p style="text-align:center;">Generado el ' + fechaTitulo + '</p>');

                    $(win.document.body).find('table').css('width', '100%');

                    let saldo = 0;
                    let ingresoTotal = 0;
                    let egresoTotal = 0;

                    // Obtener las filas en orden correcto (ascendente por fecha)
                    let filas = $(win.document.body).find('table tbody tr').get();
                    filas.sort((a, b) => {
                        let fechaA = new Date($(a).find('td').eq(1).text().trim());
                        let fechaB = new Date($(b).find('td').eq(1).text().trim());
                        return fechaA - fechaB;
                    });

                    // Recorremos las filas en orden y re calculamos el saldo
                    $(filas).each(function () {
                        let ingreso = parseFloat($(this).find('td').eq(10).text()) || 0;
                        let egreso = parseFloat($(this).find('td').eq(11).text()) || 0;

                        saldo += ingreso - egreso;
                        ingresoTotal += ingreso;
                        egresoTotal += egreso;

                        // Formatear la fecha correctamente
                        let fechaTexto = $(this).find('td').eq(1).text().trim();
                        let fechaObj = new Date(fechaTexto);
                        let opcionesFecha = { weekday: 'short', day: '2-digit', month: 'short', year: 'numeric' };
                        let fechaFormateada = fechaObj.toLocaleDateString('es-MX', opcionesFecha).replace(',', '');

                        $(this).find('td').eq(1).text(fechaFormateada); // Reemplazar con la fecha formateada
                        $(this).find('td').eq(12).text(formatCurrency(saldo, '$')); // Mostrar el saldo correcto
                    });

                    // Estilos de impresión específicos
                    const style = `
                        <style>
                            @media print {
                                .row {
                                    display: flex;
                                    flex-wrap: wrap;
                                    justify-content: space-between;
                                }
                                .col {
                                    flex: 1 1 30%;
                                    margin: 5px;
                                }
                            }
                        </style>
                    `;

                    $(win.document.head).append(style); // Añadir estilos de impresión

                    let saldoGuardado = parseFloat(localStorage.getItem('saldoCaja')) || 0;

                    // Agregar los totales al final
                    $(win.document.body).append(`
                    <div class="card mt-3">
                        <div class="card-body">
                            <div class="row align-items-center text-center pb-4">
                                <div class="col col-12 col-md-4 pb-3 pb-md-0">
                                    <p class="f-size-sm mb-0">Ingreso total:</p>
                                    <span class="badge badge-azul ml-2 f-size-md">${formatCurrency(ingresoTotal, '$')}</span>
                                </div>
                                <div class="col col-12 col-md-4 pb-3 pb-md-0">
                                    <p class="f-size-sm mb-0">Egreso total: </p>
                                    <span class="badge badge-azul ml-2 f-size-md">${formatCurrency(egresoTotal, '$')}</span>
                                </div>
                                <div class="col col-12 col-md-4 pb-3 pb-md-0">
                                    <p class="f-size-sm mb-0">Saldo total: </p>
                                    <span class="badge badge-azul ml-2 f-size-md">${formatCurrency(saldo, '$')}</span>
                                </div>
                            </div>
                            <div class="text-center">
                                <small class="small-text"><strong>Nota:</strong> Los montos mostrados corresponden a la suma total de todos los resultados, no solo de las 10 filas visibles. Los cálculos se actualizarán al aplicar filtros.</small>
                            </div>
                            <div class="row align-items-center text-center py-4">
                                <div class="col col-12 pb-3 pb-md-0">
                                    <p class="f-size-sm mb-0 font-weight-bold">Saldo en caja:</p>
                                    <span class="badge badge-azul ml-2 f-size-md">${formatCurrency(saldoGuardado, '$')}</span>
                                </div>
                            </div>
                        </div>
                    </div>`);
                }
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

        fetchFillSelect2('getModelGeneric', 'modal_caja_add_cargado', null, 'modelo_chica_cargado');
        fetchFillSelect2('getModelGeneric', 'modal_caja_add_area', null, 'modelo_chica_area');
        fetchFillSelect2('getModelGeneric', 'modal_caja_add_tipo_gasto', null, 'modelo_chica_tipo_gasto');
        fetchFillSelect2('getModelGeneric', 'modal_caja_add_recibe', null, 'modelo_chica_recibe');
        fetchFillSelect2('getModelGeneric', 'modal_caja_add_unidad', null, 'modelo_chica_unidad');
        fetchFillSelect2('getModelGeneric', 'modal_caja_add_comprobante', null, 'modelo_chica_comprobante');
        fetchFillSelect2('getModelGeneric', 'modal_caja_add_razon_social', null, 'modelo_chica_razon_social');
    });

    // Llenado de los select al abrir el modal
    $('#modal_edit_caja').on('show.bs.modal', async function () {
        const cajaId = $('#modal_caja_edit_id').val();
        // Limpiar contenido previo
        const tableContainer = document.getElementById("tabla_historial_comprobantes");
        tableContainer.innerHTML = ""; // Limpiar contenido previo
        const historialContent = document.getElementById('historialContent');
        historialContent.style.display = 'none';
        btnToggleHistorial.textContent = 'Obtener comprobantes';

        try {
            // Obtenemos los datos del vehículo
            const data = await fetchGeneric('getPettyCashDetails', cajaId, 'functions/select_chica_general.php');

            fetchFillSelect2('getModelGeneric', 'modal_caja_edit_cargado', data.id_cargado, 'modelo_chica_cargado');
            fetchFillSelect2('getModelGeneric', 'modal_caja_edit_area', data.id_area, 'modelo_chica_area');
            fetchFillSelect2('getModelGeneric', 'modal_caja_edit_tipo_gasto', data.id_tipo_gasto, 'modelo_chica_tipo_gasto');
            fetchFillSelect2('getModelGeneric', 'modal_caja_edit_recibe', data.id_recibe, 'modelo_chica_recibe');
            fetchFillSelect2('getModelGeneric', 'modal_caja_edit_unidad', data.id_unidad, 'modelo_chica_unidad');
            fetchFillSelect2('getModelGeneric', 'modal_caja_edit_comprobante', data.id_comprobante, 'modelo_chica_comprobante');
            fetchFillSelect2('getModelGeneric', 'modal_caja_edit_razon_social', data.id_razon_social, 'modelo_chica_razon_social');
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

    // Mostrar/ocultar historial
    const btnToggleHistorial = document.getElementById('btn_toggle_historial');
    const historialContent = document.getElementById('historialContent');

    btnToggleHistorial.addEventListener('click', function () {
        if (historialContent.style.display === 'none') {
            historialContent.style.display = 'flex';
            btnToggleHistorial.textContent = 'Ocultar comprobantes';
            fetchVoucherHistory();
        } else {
            historialContent.style.display = 'none';
            btnToggleHistorial.textContent = 'Obtener comprobantes';
        }
    });

    async function fetchVoucherHistory() {
        try {
            const loader = document.getElementById("loader-history");
            const noFiles = document.getElementById("no-files-history");
            const tableContainer = document.getElementById("tabla_historial_comprobantes");

            loader.style.display = "flex"; // Mostrar loader
            noFiles.style.display = "none";
            tableContainer.innerHTML = ""; // Limpiar contenido previo

            // Objeto para mapear el tipo de clase CSS
            const badgeType = {
                0: 'verde',
                1: 'azul',
                2: 'rojo',
                3: 'gris'
            };

            const formData = new FormData();
            formData.append('opcion', 'getVoucherList');
            formData.append('option_value', document.getElementById('modal_caja_edit_id').value); // Asegúrate de definir optionValue

            const response = await fetch('functions/select_chica_general.php', {
                method: 'POST',
                body: formData,
            });

            const data = await response.json();
            loader.style.display = "none"; // Ocultar loader

            if (data.type === 'SUCCESS' && data.response.length > 0) {
                // Crear la tabla
                const table = document.createElement("table");
                table.classList.add("table", "table-striped", "table-bordered", "mb-0", "table-sm");

                // Encabezado
                table.innerHTML = `
                    <thead>
                        <tr>
                            <th>Comentarios</th>
                            <th>Comprobante</th>
                            <th class="text-center">Archivos</th>
                            <th class="text-center">Fecha sistema</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${data.response.map(row => `
                            <tr>
                                <td class="align-middle">${row.comments || 'Sin comentarios'}</td>
                                <td class="text-center align-middle"><span class="badge badge-${badgeType[row.comprobante_nombre] || 'gris'}">${row.comprobante_nombre || 'Sin comprobante'}</span></td>
                                <td class="text-center align-middle">
                                    ${row.file_name ? `
                                        <div class="d-flex justify-content-between align-items-center mb-1 py-0 px-1">
                                            <div class="align-middle">
                                                <span class="font-weight-bold">${row.file_name}</span>
                                            </div>
                                            <div class="d-flex flex-column flex-md-row text-nowrap">
                                                <button type="button" class="btn btn-secondary btn-sm mr-0 mr-md-2 view-file" data-path="${row.file_path}${row.file_name}"><i class="fa-solid fa-file pr-2"></i>Ver archivo</button>
                                                <button type="button" class="btn btn-warning btn-sm mr-0 mr-md-2 download-file" data-path="${row.file_path}${row.file_name}"><i class="fa-solid fa-download pr-2"></i>Descargar</button>
                                            </div>
                                        </div>` : 'Sin archivos'}
                                </td>
                                <td class="text-center align-middle">${formatearFecha(row.fecha, 3)}</td>
                            </tr>`).join('')}
                    </tbody>
                `;

                tableContainer.appendChild(table);

                // Agregar eventos a los botones
                document.querySelectorAll('.view-file').forEach(button => {
                    button.addEventListener('click', function () {
                        viewFile(this.getAttribute('data-path'));
                    });
                });

                document.querySelectorAll('.download-file').forEach(button => {
                    button.addEventListener('click', function () {
                        downloadFile(this.getAttribute('data-path'));
                    });
                });
            } else {
                noFiles.style.display = "flex"; // Mostrar mensaje de "sin archivos"
            }
        } catch (error) {
            console.error("Error al obtener el historial:", error);
            loader.style.display = "none";
            alert("Ocurrió un error al cargar el historial.");
        }
    }

    // Ver archivo
    async function viewFile(filePath) {
        if (!filePath) {
            alertNotify('2000', 'error', 'Error', 'El archivo no existe o la ruta es inválida.', 'bottom-end');
            return;
        }

        try {
            const response = await fetch(filePath, { method: 'HEAD' });
            if (response.ok) {
                // Abrir el archivo en una nueva pestaña
                window.open(filePath, '_blank');
            } else {
                alertNotify('2000', 'error', 'Error', 'El archivo no existe o la ruta es inválida.', 'bottom-end');
            }
        } catch (error) {
            alertNotify('2000', 'error', 'Error', 'No se pudo verificar la existencia del archivo.', 'bottom-end');
        }
    }

    // Descargar archivo
    async function downloadFile(filePath) {
        if (!filePath) {
            alertNotify('2000', 'error', 'Error', 'El archivo no existe o la ruta es inválida.', 'bottom-end');
            return;
        }

        try {
            const response = await fetch(filePath, { method: 'HEAD' });
            if (response.ok) {
                // Crear un elemento `<a>` temporal para iniciar la descarga
                const link = document.createElement('a');
                link.href = filePath;
                link.download = filePath.split('/').pop(); // Establecer el nombre del archivo descargado
                document.body.appendChild(link); // Añadir el enlace al DOM
                link.click(); // Simular el clic
                document.body.removeChild(link); // Eliminar el enlace del DOM
            } else {
                alertNotify('2000', 'error', 'Error', 'El archivo no existe o la ruta es inválida.', 'bottom-end');
            }
        } catch (error) {
            alertNotify('2000', 'error', 'Error', 'No se pudo verificar la existencia del archivo.', 'bottom-end');
        }
    }
});