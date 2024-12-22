<!-- Modal Agregar tipo de archivo -->
<div class="modal fade pl-0" id="modal_add_model" tabindex="-1" aria-labelledby="label_add_model">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="label_add_model">Agregar tipo</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span class="text-white">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <h5 class="font-weight-bold my-3">Agregar un tipo de archivo nuevo</h5>
                <div class="form-row">
                    <!-- Nombre -->
                    <div class="form-group mb-0 col-md-12">
                        <label for="modal_am_nombre" class="col-form-label">Nombre:</label>
                        <input type="text" class="form-control" id="modal_am_nombre" name="modal_am_nombre" maxlength="100" placeholder="Ej. Factura">
                    </div>
                </div>
                <div class="d-flex pt-3">
                    <p class="custom-file-smalls mb-0"><b>Aviso: </b>No son permitidos acentos y caracteres especiales.</p>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="btn_modal_add_model">Guardar</button>
            </div>
        </div>
    </div>
</div>