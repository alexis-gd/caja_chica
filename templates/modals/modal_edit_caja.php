<!-- Modal Editar Caja -->
<div class="modal fade pl-0" id="modal_edit_caja" tabindex="-1" aria-labelledby="label_edit_caja">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="label_edit_caja">Editar caja</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span class="text-white">&times;</span>
        </button>
      </div>
      <div class="modal-body d-flex bg-wrapper">
        <form id="form-edit-caja">

          <!-- Formulario -->
          <div class="form-row box p-3">
            <input type="hidden" id="modal_caja_edit_id" name="modal_caja_edit_id">
            <div class="col-md-12">
              <h5 class="mb-3 azul">Información general</h5>
            </div>

            <!-- Ingresar Fecha -->
            <div class="form-group mb-0 col-md-12">
              <label for="modal_caja_edit_fecha" class="col-form-label font-weight-normal">Fecha:</label>
              <input type="text" class="form-control" id="modal_caja_edit_fecha" name="modal_caja_edit_fecha" data-id="modal_caja_edit_fecha">
            </div>

            <!-- Seleccionar Cargado -->
            <div class="form-group mb-0 col-md-6">
              <label for="modal_caja_edit_cargado" class="col-form-label font-weight-normal">Cargado:</label>
              <select name="modal_caja_edit_cargado" id="modal_caja_edit_cargado" class="form-control selectize" data-select-id="modelo_cargado"></select>
            </div>

            <!-- Seleccionar Area -->
            <div class="form-group mb-0 col-md-6">
              <label for="modal_caja_edit_area" class="col-form-label font-weight-normal">Área:</label>
              <select name="modal_caja_edit_area" id="modal_caja_edit_area" class="form-control selectize" data-select-id="modelo_area"></select>
            </div>

            <!-- Ingresar Folio -->
            <!-- <div class="form-group mb-0 col-md-6">
              <label for="modal_caja_edit_folio" class="col-form-label font-weight-normal">Folio:</label>
              <input type="text" class="form-control" id="modal_caja_edit_folio" name="modal_caja_edit_folio" maxlength="20" placeholder="Ej. ABC123456">
            </div> -->

            <!-- Seleccionar Empresa -->
            <!-- <div class="form-group mb-0 col-md-6">
              <label for="modal_caja_edit_empresa" class="col-form-label font-weight-normal">Empresa:</label>
              <select name="modal_caja_edit_empresa" id="modal_caja_edit_empresa" class="form-control selectize" data-select-id="modelo_empresa"></select>
            </div> -->

            <!-- Seleccionar Entrega -->
            <!-- <div class="form-group mb-0 col-md-6">
              <label for="modal_caja_edit_entrega" class="col-form-label font-weight-normal">Entrega:</label>
              <select name="modal_caja_edit_entrega" id="modal_caja_edit_entrega" class="form-control selectize" data-select-id="modelo_entrega"></select>
            </div> -->

            <!-- Seleccionar Tipo Ingreso -->
            <!-- <div class="form-group mb-0 col-md-6">
              <label for="modal_caja_edit_tipo_ingreso" class="col-form-label font-weight-normal">Tipo de Ingreso:</label>
              <select name="modal_caja_edit_tipo_ingreso" id="modal_caja_edit_tipo_ingreso" class="form-control selectize" data-select-id="modelo_tipo_ingreso"></select>
            </div> -->

            <!-- Seleccionar Tipo Gasto -->
            <div class="form-group mb-0 col-md-6">
              <label for="modal_caja_edit_tipo_gasto" class="col-form-label font-weight-normal">Tipo de Gasto:</label>
              <select name="modal_caja_edit_tipo_gasto" id="modal_caja_edit_tipo_gasto" class="form-control selectize" data-select-id="modelo_tipo_gasto"></select>
            </div>

            <!-- Seleccionar Autoriza -->
            <!-- <div class="form-group mb-0 col-md-6">
              <label for="modal_caja_edit_autoriza" class="col-form-label font-weight-normal">Autoriza:</label>
              <select name="modal_caja_edit_autoriza" id="modal_caja_edit_autoriza" class="form-control selectize" data-select-id="modelo_autoriza"></select>
            </div> -->

            <!-- Ingresar Concepto -->
            <div class="form-group mb-0 col-12">
              <label for="modal_caja_edit_concepto" class="col-form-label font-weight-normal">Concepto:</label>
              <textarea name="modal_caja_edit_concepto" id="modal_caja_edit_concepto" class="form-control" maxlength="500" placeholder="Ej. Pago por servicios"></textarea>
            </div>

            <!-- Seleccionar Proveedor -->
            <!-- <div class="form-group mb-0 col-md-6">
              <label for="modal_caja_edit_proveedor" class="col-form-label font-weight-normal">Proveedor:</label>
              <select name="modal_caja_edit_proveedor" id="modal_caja_edit_proveedor" class="form-control selectize" data-select-id="modelo_proveedor"></select>
            </div> -->

            <!-- Seleccionar Recibe -->
            <div class="form-group mb-0 col-md-6">
              <label for="modal_caja_edit_recibe" class="col-form-label font-weight-normal">Recibe:</label>
              <select name="modal_caja_edit_recibe" id="modal_caja_edit_recibe" class="form-control selectize" data-select-id="modelo_recibe"></select>
            </div>

            <!-- Seleccionar Unidad -->
            <div class="form-group mb-0 col-md-6">
              <label for="modal_caja_edit_unidad" class="col-form-label font-weight-normal">Unidad:</label>
              <select name="modal_caja_edit_unidad" id="modal_caja_edit_unidad" class="form-control selectize" data-select-id="modelo_unidad"></select>
            </div>

            <!-- Seleccionar Operador -->
            <!-- <div class="form-group mb-0 col-md-6">
              <label for="modal_caja_edit_operador" class="col-form-label font-weight-normal">Operador:</label>
              <select name="modal_caja_edit_operador" id="modal_caja_edit_operador" class="form-control selectize" data-select-id="modelo_operador"></select>
            </div> -->

            <!-- Ingresar Comprobante -->
            <div class="form-group mb-0 col-md-6">
              <label for="modal_caja_edit_comprobante" class="col-form-label font-weight-normal">Comprobante:</label>
              <select name="modal_caja_edit_comprobante" id="modal_caja_edit_comprobante" class="form-control selectize" data-select-id="modelo_comprobante"></select>
            </div>

            <!-- Ingresar Factura -->
            <!-- <div class="form-group mb-0 col-md-6">
              <label for="modal_caja_edit_factura" class="col-form-label font-weight-normal">Factura:</label>
              <select name="modal_caja_edit_factura" id="modal_caja_edit_factura" class="form-control selectize" data-select-id="modelo_factura"></select>
            </div> -->

            <!-- Ingresar Razón social -->
            <div class="form-group mb-0 col-md-6">
              <label for="modal_caja_edit_razon_social" class="col-form-label font-weight-normal">Razón social:</label>
              <select name="modal_caja_edit_razon_social" id="modal_caja_edit_razon_social" class="form-control selectize" data-select-id="modelo_razon_social"></select>
            </div>

            <!-- Ingresar Ingreso -->
            <div class="form-group mb-0 col-md-6">
              <label for="modal_caja_edit_ingreso" class="col-form-label font-weight-normal">Ingreso:</label>
              <input type="text" class="form-control" id="modal_caja_edit_ingreso" name="modal_caja_edit_ingreso" maxlength="15" placeholder="Ej. 1000" value="0">
            </div>

            <!-- Ingresar Egreso -->
            <div class="form-group mb-0 col-md-6">
              <label for="modal_caja_edit_egreso" class="col-form-label font-weight-normal">Egreso:</label>
              <input type="text" class="form-control" id="modal_caja_edit_egreso" name="modal_caja_edit_egreso" maxlength="15" placeholder="Ej. 500" value="0">
            </div>

            <!-- Ingresar Saldo -->
            <!-- <div class="form-group mb-0 col-md-6">
              <label for="modal_caja_edit_saldo" class="col-form-label font-weight-normal">Saldo:</label>
              <input type="number" class="form-control" id="modal_caja_edit_saldo" name="modal_caja_edit_saldo" maxlength="15" placeholder="Ej. 500">
            </div> -->



          </div>

          <!-- Comprobantes -->
          <div class="form-row box p-3 mt-3">
            <div class="d-flex justify-content-center w-100">
              <button type="button" class="btn btn-primary" id="btn_toggle_historial">Obtener comprobantes</button>
            </div>

            <!-- Historial de archivos -->
            <!-- <div class="d-flex" id="printableContent"> -->
            <div id="historialContent" style="display: none; transition: all 0.5s;">
              <div class="d-flex justify-content-center w-100">
                <div class="container w-100ms position-relative p-3">
                  <div class="d-flex align-items-center mb-3">
                    <h3 class="azul mb-0 pr-2">Historial de comprobantes</h3>
                    <!-- <div class="d-flex flex-column flex-md-row text-nowrap">
                    <button class="btn btn-danger btn-sm mr-0 mr-md-2 no-print" data-toggle="modal" data-target="#modal_output_vehicle"><i class="fa-solid fa-boxes-packing pr-2"></i>Dar salida</button>
                    <button class="btn btn-secondary btn-sm mr-0 mr-md-2 no-print" data-toggle="modal" data-target="#modal_return_vehicle"><i class="fa-solid fa-people-carry-box pr-2"></i>Devoluciones</button>
                    <button class="btn btn-success btn-sm mr-0 mr-md-2 no-print" data-toggle="modal" data-target="#modal_observation_vehicle"><i class="fa-solid fa-file-pen pr-2"></i>Nueva observación</button>
                    <button class="btn btn-primary btn-sm mr-0 mr-md-2 no-print" data-toggle="modal" data-target="#modal_send_vehicle"><i class="fa-solid fa-paper-plane pr-2"></i>Enviar documentos</button>
                    <button class="btn btn-primary btn-sm mr-0 mr-md-2 no-print d-none" onclick="printElement('printableContent')"><i class="fa-solid fa-print pr-2"></i>Imprimir</button>
                  </div> -->
                  </div>
                  <div id="tabla_historial_observaciones" class="table-responsive"></div>
                  <div id="loader-history" class="justify-content-center align-items-center py-5 form-loader" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                      <span class="sr-only">Cargando...</span>
                    </div>
                  </div>
                  <div id="no-files-history" class="text-center text-muted no-files justify-content-center align-content-center" style="display: none;">
                    <p>No hay historial disponible por el momento.</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </form>
      </div>
      <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" id="btn_modal_editar">Guardar</button>
      </div>
    </div>
  </div>
</div>