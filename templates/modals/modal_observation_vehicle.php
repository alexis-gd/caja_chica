<!-- Modal Salida -->
<div class="modal fade pl-0" id="modal_observation_vehicle" tabindex="-1" aria-labelledby="labelCrear" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header success">
        <h5 class="modal-title" id="labelCrear">Agregar observación</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span class="text-white" aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body d-flex bg-wrapper">
        <form id="form-observation-vehicle">

          <div class="form-row box success p-3">
            <div class="col-md-12">
              <h5 class="mb-3 azul">Agrega tus observaciones</h5>
            </div>

            <!-- Ingresar Observaciones -->
            <div class="form-group mb-0 col-12">
              <label for="modal_vehicle_observation_observations" class="col-form-label font-weight-normal">Observaciones:</label>
              <textarea name="modal_vehicle_observation_observations" id="modal_vehicle_observation_observations" class="form-control" maxlength="500" placeholder="Ej. Sin observaciones"></textarea>
            </div>

            <!-- Ingresar Fecha de entrega -->
            <div class="form-group mb-0 col-md-6">
              <label for="modal_vehicle_observation_delivery_date" class="col-form-label font-weight-normal">Fecha de entrega (opcional):</label>
              <input type="date" class="form-control" id="modal_vehicle_observation_delivery_date" name="modal_vehicle_observation_delivery_date" maxlength="10" placeholder="Ej. 2023-12-31">
            </div>

          </div>

        </form>
      </div>
      <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" id="btn_modal_insertar_observacion">Guardar</button>
      </div>
    </div>
  </div>
</div>