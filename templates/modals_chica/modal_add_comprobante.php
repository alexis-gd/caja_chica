<!-- Modal Subir Comprobante -->
<div class="modal fade pl-0" id="modal_add_comprobante" tabindex="-1" aria-labelledby="label_add_comprobante">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="label_add_comprobante">Subir comprobante</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span class="text-white">&times;</span>
        </button>
      </div>
      <div class="modal-body d-flex bg-wrapper">
        <form id="form-add-comprobante">

          <div class="form-row box p-3">
            <input type="hidden" id="modal_comprobante_id" name="modal_comprobante_id">
            <div class="col-md-12">
              <h5 class="mb-3 azul">Agrega un comprobante</h5>
            </div>

            <!-- Sin archivo -->
            <div class="form-group mb-0 col-md-12">
              <div class="card-body px-0 py-1">
                <label for="check_visible" class="pr-2 mb-0 font-weight-normal">Agregar archivo</label>
                <input type="checkbox" id="check_visible" name="check_visible" data-bootstrap-switch data-off-color="danger" data-on-color="success" data-on-text="Si" data-off-text="No">
              </div>
            </div>

            <div class="form-group mb-0 col-md-12" id="observation-group" style="display: none; transition: all 0.5s;">
              <!-- Tipo de archivo -->
              <div class="form-group col-md-12 px-0">
                <label for="modal_voucher_upload_file" class="col-form-label font-weight-normal">Tipo de archivo:</label>
                <div class="d-flex align-items-center">
                  <select name="modal_voucher_upload_file" id="modal_voucher_upload_file" class="form-control selectize me-2" data-select-id="modelo_chica_archivo"></select>
                </div>
              </div>

              <!-- Archivo -->
              <div class="d-flex justify-content-center justify-content-lg-start py-3">
                <div class="custom-file mw-100">
                  <input type="file" name="product_file" id="file" class="custom-file-input" />
                  <label for="file" class="custom-file-label" id="file-label">Selecciona o arrastra un archivo<img src="img/puntero.svg" alt="puntero"></label>
                </div>
                <!-- <div class="box-button m-0">
                  <button type="submit" name="upload" id="upload" class="btn btn-primary m-0 text-nowrap"><i class="fa-solid fa-upload pr-2"></i>Subir archivo</button>
                </div> -->
              </div>
              <div class="d-flex gap-1 pb-3">
                <small class="custom-file-smalls mb-0"><b>Aviso:</b> Sube 1 archivo a la vez</small>
                <small class="custom-file-smalls mb-0"><b>Formato:</b> .pdf .jpg .png</small>
              </div>
            </div>
            <!-- agregar campo de subir archivo y boton para cerrarlo -->

            <!-- Comentario -->
            <div class="form-group mb-0 col-md-12">
              <label for="modal_comprobante_add_concepto" class="col-form-label font-weight-normal pb-0">Comentario:</label>
              <textarea class="form-control" id="modal_comprobante_add_concepto" name="modal_comprobante_add_concepto" placeholder="Ej. Quedan comprobantes pendientes" maxlength="500"></textarea>
            </div>

            <!-- Ingresar Comprobante -->
            <div class="form-group mb-0 col-md-12">
              <label for="modal_comprobante_add_comprobante" class="col-form-label font-weight-normal">Comprobante:</label>
              <select name="modal_comprobante_add_comprobante" id="modal_comprobante_add_comprobante" class="form-control selectize" data-select-id="modelo_chica_comprobante"></select>
            </div>

            <!-- Check cerrar -->
            <!-- <div class="card-body px-0 py-1 mt-1">
              <label for="check_close" class="pr-2 mb-0 font-weight-normal">Cerrar comprobante</label>
              <input type="checkbox" id="check_close" name="check_close" data-bootstrap-switch data-off-color="danger" data-on-color="success" data-on-text="Si" data-off-text="No">
            </div> -->

          </div>
        </form>
      </div>
      <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" id="btn_modal_subir"><i class="fa-solid fa-upload pr-2"></i>Subir archivo</button>
      </div>
    </div>
  </div>
</div>