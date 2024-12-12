<!-- Modal Crear Nuevo -->
<div class="modal fade pl-0" id="modal_add_vehicle" tabindex="-1" aria-labelledby="labelCrear" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="labelCrear">Añadir vehículo</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span class="text-white" aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body d-flex bg-wrapper">
        <form id="form-add-vehicle">

          <div class="form-row box p-3">
            <div class="col-md-12">
              <h5 class="mb-3 azul">Información general</h5>
            </div>

            <!-- Seleccionar Marca -->
            <div class="form-group mb-0 col-md-6">
              <label for="modal_vehicle_add_brand" class="col-form-label font-weight-normal">Marca:</label>
              <select name="modal_vehicle_add_brand" id="modal_vehicle_add_brand" class="form-control selectize" data-select-id="modelo_marca"></select>
            </div>

            <!-- Seleccionar Modelo -->
            <div class="form-group mb-0 col-md-6">
              <label for="modal_vehicle_add_model" class="col-form-label font-weight-normal">Modelo</label>
              <select name="modal_vehicle_add_model" id="modal_vehicle_add_model" class="form-control selectize" data-select-id="modelo_modelo"></select>
            </div>

            <!-- Ingresar Año -->
            <div class="form-group mb-0 col-md-6">
              <label for="modal_vehicle_add_year" class="col-form-label font-weight-normal">Año (opcional):</label>
              <input type="number" class="form-control" id="modal_vehicle_add_year" name="modal_vehicle_add_year" maxlength="4" placeholder="Ej. 2020" min="1900" max="2100">
            </div>

            <!-- Seleccionar Color -->
            <div class="form-group mb-0 col-md-6">
              <label for="modal_vehicle_add_color" class="col-form-label font-weight-normal">Color:</label>
              <select name="modal_vehicle_add_color" id="modal_vehicle_add_color" class="form-control selectize" data-select-id="modelo_color"></select>
            </div>

            <!-- Ingresar Serie -->
            <div class="form-group mb-0 col-md-6">
              <label for="modal_vehicle_add_serie" class="col-form-label font-weight-normal">Serie:</label>
              <input type="text" class="form-control" id="modal_vehicle_add_serie" name="modal_vehicle_add_serie" maxlength="17" placeholder="Ej. 3VWPL7AJ2CM676999">
            </div>

            <!-- Ingresar Motor -->
            <div class="form-group mb-0 col-md-6">
              <label for="modal_vehicle_add_engine" class="col-form-label font-weight-normal">Motor (opcional):</label>
              <input type="text" class="form-control" id="modal_vehicle_add_engine" name="modal_vehicle_add_engine" maxlength="20" placeholder="Ej. 00000000000001">
            </div>

            <!-- Ingresar Pedimento -->
            <div class="form-group mb-0 col-md-6">
              <label for="modal_vehicle_add_pedimento" class="col-form-label font-weight-normal">Pedimento (opcional):</label>
              <input type="text" class="form-control" id="modal_vehicle_add_pedimento" name="modal_vehicle_add_pedimento" maxlength="50" placeholder="Ej. PED000123456">
            </div>

            <!-- Seleccionar Propietario -->
            <div class="form-group mb-0 col-md-6">
              <label for="modal_vehicle_add_owner" class="col-form-label font-weight-normal">Propietario:</label>
              <select name="modal_vehicle_add_owner" id="modal_vehicle_add_owner" class="form-control selectize" data-select-id="modelo_propietario"></select>
            </div>

            <!-- Ingresar Placa -->
            <div class="form-group mb-0 col-md-6">
              <label for="modal_vehicle_add_license_plate" class="col-form-label font-weight-normal">Placa (opcional):</label>
              <input type="text" class="form-control" id="modal_vehicle_add_license_plate" name="modal_vehicle_add_license_plate" maxlength="11" placeholder="Ej. AA00D">
            </div>

            <!-- Ingresar Observaciones -->
            <div class="form-group mb-0 col-12">
              <label for="modal_vehicle_add_observations" class="col-form-label font-weight-normal">Observaciones:</label>
              <textarea name="modal_vehicle_add_observations" id="modal_vehicle_add_observations" class="form-control" maxlength="500" placeholder="Ej. Sin observaciones"></textarea>
            </div>

            <!-- Ingresar Fecha de entrega -->
            <div class="form-group mb-0 col-md-6">
              <label for="modal_vehicle_add_delivery_date" class="col-form-label font-weight-normal">Fecha de entrega (opcional):</label>
              <input type="date" class="form-control" id="modal_vehicle_add_delivery_date" name="modal_vehicle_add_delivery_date" maxlength="10" placeholder="Ej. 2023-12-31">
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