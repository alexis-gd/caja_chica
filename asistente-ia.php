<?php
include_once 'config/sesiones.php';
include_once 'config/conexion.php';
$con = conectar();

// Solo accesible para nivel admin
if ($_SESSION['nivel'] != 1) {
    header('Location: dashboard.php');
    exit;
}

// Modo dev: muestra card de sugerencias si ?dev=1 o Ctrl+Shift+S
$dev_mode = isset($_GET['dev']) && $_GET['dev'] === '1';

// Cargar sugerencias pendientes para la card dev
$sugerencias = [];
if ($dev_mode) {
    $stmt_sug = $con->query("SELECT id, sugerencia, justificacion, creado FROM ai_sugerencias WHERE revisada = 0 ORDER BY creado DESC");
    $sugerencias = $stmt_sug->fetchAll(PDO::FETCH_ASSOC);
}

include_once 'templates/header.php';
?>
<style>
  #chat-window {
    height: 420px;
    overflow-y: auto;
    background: #f8f9fa;
    border-radius: 4px;
    padding: 12px;
  }
  .bubble-user {
    background: #007bff;
    color: #fff;
    border-radius: 16px 16px 4px 16px;
    padding: 8px 14px;
    max-width: 80%;
    margin-left: auto;
    margin-bottom: 8px;
    word-break: break-word;
  }
  .bubble-ai {
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 4px 16px 16px 16px;
    padding: 8px 14px;
    max-width: 85%;
    margin-bottom: 8px;
    word-break: break-word;
  }
  .bubble-ai .sug-icon {
    font-size: 0.75rem;
    color: #ffc107;
    margin-left: 6px;
    cursor: help;
  }
  .bubble-spinner {
    color: #6c757d;
    font-style: italic;
    font-size: 0.9rem;
    margin-bottom: 8px;
  }
  #btn-voice.recording {
    animation: pulse 1s infinite;
  }
  @keyframes pulse {
    0%   { box-shadow: 0 0 0 0 rgba(220,53,69,.4); }
    70%  { box-shadow: 0 0 0 8px rgba(220,53,69,0); }
    100% { box-shadow: 0 0 0 0 rgba(220,53,69,0); }
  }
</style>
</head>
<?php
include_once 'templates/navbar.php';
include_once 'templates/sidebar.php';
?>

<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>
            <i class="fas fa-robot text-warning mr-2"></i>
            Asistente IA
            <span class="badge badge-warning ml-2">Demo</span>
          </h1>
        </div>
        <div class="col-sm-6 text-right">
          <small class="text-muted">
            <i class="fas fa-microphone mr-1"></i> Funciona mejor en Chrome/Edge
          </small>
        </div>
      </div>
    </div>
  </section>

  <section class="content">
    <div class="container-fluid">

      <!-- Barra de uso del free tier -->
      <div class="card card-outline card-info mb-3">
        <div class="card-body py-2 px-3">
          <div class="d-flex justify-content-between align-items-center">
            <small>
              Uso IA hoy
              (<span id="proveedor-ia" class="font-weight-bold text-capitalize">Groq</span>):
              <strong id="requests-hoy">0</strong> /
              <strong id="requests-limite">14,400</strong> consultas
              <span class="text-muted ml-2">· <span id="tokens-usados">0</span> tokens usados</span>
            </small>
            <small class="text-muted" id="proveedor-modelo">llama-3.1-8b-instant</small>
          </div>
          <div class="progress mt-1" style="height:5px">
            <div id="barra-uso" class="progress-bar bg-info" style="width:0%" role="progressbar"></div>
          </div>
        </div>
      </div>

      <div class="row">

        <!-- Columna izquierda: preguntas sugeridas -->
        <div class="col-12 col-md-4 col-lg-3">
          <div class="card card-outline card-primary">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-lightbulb mr-1"></i> Preguntas sugeridas
              </h3>
            </div>
            <div class="card-body">
              <p class="text-muted small mb-2">Haz clic o di la pregunta en voz alta</p>
              <div class="d-flex flex-column" style="gap:8px">
                <button class="btn btn-outline-primary btn-sm text-left btn-pregunta"
                  data-question="¿Cuál es el saldo actual de caja chica?">
                  <i class="fas fa-wallet mr-1"></i> ¿Cuál es el saldo actual?
                </button>
                <button class="btn btn-outline-primary btn-sm text-left btn-pregunta"
                  data-question="¿Cuánto se ha gastado en egresos este mes?">
                  <i class="fas fa-arrow-down mr-1"></i> Egresos de este mes
                </button>
                <button class="btn btn-outline-primary btn-sm text-left btn-pregunta"
                  data-question="¿Cuáles son los principales tipos de gasto del mes?">
                  <i class="fas fa-list mr-1"></i> Principales gastos
                </button>
                <button class="btn btn-outline-primary btn-sm text-left btn-pregunta"
                  data-question="¿Cómo van los ingresos vs egresos este año?">
                  <i class="fas fa-chart-bar mr-1"></i> Ingresos vs egresos del año
                </button>
                <button class="btn btn-outline-primary btn-sm text-left btn-pregunta"
                  data-question="¿Cuál fue nuestro mejor mes del año?">
                  <i class="fas fa-trophy mr-1"></i> Mejor mes del año
                </button>
                <button class="btn btn-outline-success btn-sm text-left btn-pregunta"
                  data-question="¿En qué área o categoría podría ahorrar este mes?">
                  <i class="fas fa-piggy-bank mr-1"></i> ¿Dónde puedo ahorrar?
                </button>
              </div>
            </div>
          </div>

          <!-- Indicador de estado del micrófono -->
          <div id="voice-status" class="callout callout-warning d-none mt-2 py-2 px-3">
            <small id="voice-status-text">
              <i class="fas fa-microphone-slash mr-1"></i> Escuchando...
            </small>
          </div>

          <!-- Nota de compatibilidad (si no hay voz) -->
          <div id="voice-warning" class="callout callout-danger d-none mt-2 py-2 px-3">
            <small>
              <i class="fas fa-exclamation-triangle mr-1"></i>
              Tu navegador no soporta entrada de voz. Usa el texto.
            </small>
          </div>
        </div>

        <!-- Columna derecha: chat -->
        <div class="col-12 col-md-8 col-lg-9">
          <div class="card card-outline card-dark">
            <div class="card-header">
              <h3 class="card-title">
                <i class="fas fa-comments mr-1"></i> Asistente de Caja Chica
              </h3>
              <div class="card-tools">
                <button id="btn-clear-chat" class="btn btn-tool" title="Limpiar chat">
                  <i class="fas fa-trash-alt"></i>
                </button>
              </div>
            </div>
            <div class="card-body p-2">
              <div id="chat-window">
                <!-- Mensaje de bienvenida -->
                <div class="bubble-ai">
                  <i class="fas fa-robot text-warning mr-1"></i>
                  Hola, soy tu asistente de Caja Chica. Puedes preguntarme sobre saldos, gastos, ingresos, registros o análisis de ahorro. ¿En qué te puedo ayudar?
                </div>
              </div>
            </div>
            <div class="card-footer p-2">
              <div class="input-group">
                <input type="text" id="user-input" class="form-control"
                  placeholder="Escribe o habla tu pregunta..." autocomplete="off" />
                <div class="input-group-append">
                  <button id="btn-voice" class="btn btn-warning" type="button" title="Hablar">
                    <i class="fas fa-microphone"></i>
                  </button>
                  <button id="btn-send" class="btn btn-primary" type="button" title="Enviar">
                    <i class="fas fa-paper-plane"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div><!-- /.row -->

      <!-- ═══════════════════════════════════════════════════════
           CARD DE SUGERENCIAS PARA EL DESARROLLADOR
           Oculta por defecto — activar con Ctrl+Shift+S o ?dev=1
           ═══════════════════════════════════════════════════════ -->
      <div id="card-sugerencias" class="card card-outline card-warning mt-3"
        style="display: <?php echo $dev_mode ? 'block' : 'none'; ?>">
        <div class="card-header">
          <h3 class="card-title">
            <i class="fas fa-lightbulb text-warning mr-1"></i>
            Sugerencias de mejora de software detectadas por la IA
            <span class="badge badge-warning ml-2" id="badge-sugerencias">
              <?php echo count($sugerencias); ?> pendientes
            </span>
          </h3>
          <div class="card-tools">
            <small class="text-muted mr-2">[Solo visible para el desarrollador · Ctrl+Shift+S para ocultar]</small>
          </div>
        </div>
        <div class="card-body p-0" id="tabla-sugerencias-body">
          <?php if (empty($sugerencias) && $dev_mode): ?>
            <p class="text-muted text-center py-3 mb-0">
              <i class="fas fa-check-circle text-success mr-1"></i>
              Sin sugerencias pendientes. Se irán generando mientras uses el asistente.
            </p>
          <?php elseif (!empty($sugerencias)): ?>
            <table class="table table-sm table-hover mb-0">
              <thead>
                <tr>
                  <th>Sugerencia</th>
                  <th>Justificación</th>
                  <th>Detectada</th>
                  <th width="80"></th>
                </tr>
              </thead>
              <tbody id="tbody-sugerencias">
                <?php foreach ($sugerencias as $sug): ?>
                  <tr id="sug-row-<?php echo $sug['id']; ?>">
                    <td><?php echo htmlspecialchars($sug['sugerencia']); ?></td>
                    <td class="text-muted small"><?php echo htmlspecialchars(isset($sug['justificacion']) ? $sug['justificacion'] : '—'); ?></td>
                    <td class="small"><?php echo date('d/m/Y', strtotime($sug['creado'])); ?></td>
                    <td>
                      <button class="btn btn-xs btn-success btn-marcar-revisada"
                        data-id="<?php echo $sug['id']; ?>">
                        <i class="fas fa-check"></i> Revisada
                      </button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php else: ?>
            <p class="text-muted text-center py-3 mb-0">
              <i class="fas fa-info-circle mr-1"></i>
              Carga la página con <code>?dev=1</code> para ver sugerencias, o activa con <kbd>Ctrl+Shift+S</kbd>.
            </p>
          <?php endif; ?>
        </div>
      </div>

    </div><!-- /.container-fluid -->
  </section>
</div><!-- /.content-wrapper -->

<?php include_once 'templates/footer.php'; ?>
<script src="js/chart.min.js"></script>
<script src="js/asistente.js?v=<?php echo $v; ?>"></script>

</body>
</html>
