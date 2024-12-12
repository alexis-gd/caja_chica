<!-- Modal Editar -->
<div class="modal fade pl-0" id="modal_edit_vehicle" tabindex="-1" aria-labelledby="labelCrear" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="labelCrear">Editar vehículo</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span class="text-white" aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body d-flex bg-wrapper">
        <form id="form-edit-vehicle">

          <div class="form-row box p-3">
            <input type="hidden" id="modal_vehicle_edit_vehicle_id" name="modal_vehicle_edit_vehicle_id">
            <div class="col-md-12">
              <h5 class="mb-3 azul">Información general</h5>
            </div>

            <!-- Seleccionar Marca -->
            <div class="form-group mb-0 col-md-6">
              <label for="modal_vehicle_edit_brand" class="col-form-label font-weight-normal">Marca:</label>
              <select name="modal_vehicle_edit_brand" id="modal_vehicle_edit_brand" class="form-control selectize"></select>
            </div>

            <!-- Seleccionar Modelo -->
            <div class="form-group mb-0 col-md-6">
              <label for="modal_vehicle_edit_model" class="col-form-label font-weight-normal">Modelo</label>
              <select name="modal_vehicle_edit_model" id="modal_vehicle_edit_model" class="form-control selectize"></select>
            </div>

            <!-- Ingresar Año -->
            <div class="form-group mb-0 col-md-6">
              <label for="modal_vehicle_edit_year" class="col-form-label font-weight-normal">Año (opcional):</label>
              <input type="number" class="form-control" id="modal_vehicle_edit_year" name="modal_vehicle_edit_year" maxlength="4" placeholder="Ej. 2020" min="1900" max="2100">
            </div>

            <!-- Seleccionar Color -->
            <div class="form-group mb-0 col-md-6">
              <label for="modal_vehicle_edit_color" class="col-form-label font-weight-normal">Color:</label>
              <select name="modal_vehicle_edit_color" id="modal_vehicle_edit_color" class="form-control selectize"></select>
            </div>

            <!-- Ingresar Serie -->
            <div class="form-group mb-0 col-md-6">
              <label for="modal_vehicle_edit_serie" class="col-form-label font-weight-normal">Serie:</label>
              <input type="text" class="form-control" id="modal_vehicle_edit_serie" name="modal_vehicle_edit_serie" maxlength="17" placeholder="Ej. 3VWPL7AJ2CM676999">
            </div>

            <!-- Ingresar Motor -->
            <div class="form-group mb-0 col-md-6">
              <label for="modal_vehicle_edit_engine" class="col-form-label font-weight-normal">Motor (opcional):</label>
              <input type="text" class="form-control" id="modal_vehicle_edit_engine" name="modal_vehicle_edit_engine" maxlength="20" placeholder="Ej. 00000000000001">
            </div>

            <!-- Ingresar Pedimento -->
            <div class="form-group mb-0 col-md-6">
              <label for="modal_vehicle_edit_pedimento" class="col-form-label font-weight-normal">Pedimento (opcional):</label>
              <input type="text" class="form-control" id="modal_vehicle_edit_pedimento" name="modal_vehicle_edit_pedimento" maxlength="50" placeholder="Ej. PED000123456">
            </div>

            <!-- Seleccionar Propietario -->
            <div class="form-group mb-0 col-md-6">
              <label for="modal_vehicle_edit_owner" class="col-form-label font-weight-normal">Propietario:</label>
              <select name="modal_vehicle_edit_owner" id="modal_vehicle_edit_owner" class="form-control selectize"></select>
            </div>

            <!-- Ingresar Placa -->
            <div class="form-group mb-0 col-md-6">
              <label for="modal_vehicle_edit_license_plate" class="col-form-label font-weight-normal">Placa (opcional):</label>
              <input type="text" class="form-control" id="modal_vehicle_edit_license_plate" name="modal_vehicle_edit_license_plate" maxlength="11" placeholder="Ej. AA00D">
            </div>

            <!-- Ingresar Observaciones -->
            <div class="form-group mb-0 col-12">
              <label for="modal_vehicle_edit_observations" class="col-form-label font-weight-normal">Observaciones:</label>
              <textarea name="modal_vehicle_edit_observations" id="modal_vehicle_edit_observations" class="form-control" maxlength="500" placeholder="Ej. Sin observaciones"></textarea>
            </div>

            <!-- Ingresar Fecha de entrega -->
            <div class="form-group mb-0 col-md-6">
              <label for="modal_vehicle_edit_delivery_date" class="col-form-label font-weight-normal">Fecha de entrega (opcional):</label>
              <input type="date" class="form-control" id="modal_vehicle_edit_delivery_date" name="modal_vehicle_edit_delivery_date" maxlength="10" placeholder="Ej. 2023-12-31">
            </div>

          </div>

        </form>
      </div>
      <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" id="btn_modal_insertar">Guardar</button>
      </div>
    </div>
  </div>
</div>