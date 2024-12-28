<!-- Modal Editar Catálogo -->
<div class="modal fade pl-0" id="modal_edit_catalogo" tabindex="-1" aria-labelledby="labelCrear" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="labelCrear">Editar registro</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span class="text-white" aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="modal_ec_id">
        <h5 class="font-weight-bold my-3">Edita el nombre del registro</h5>
        <div class="form-row">
          <!-- Nombre -->
          <div class="form-group mb-0 col-md-12">
            <label for="modal_ec_nombre" class="col-form-label">Nombre:</label>
            <input type="text" class="form-control" id="modal_ec_nombre" name="modal_ec_nombre" maxlength="100">
          </div>
        </div>
      </div>
      <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" id="btn_modal_editar">Guardar</button>
      </div>
    </div>
  </div>
</div>