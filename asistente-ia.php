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
  :root {
    --ai-chat-bg: #f8fafc;
    --ai-bubble-user-shadow: 0 4px 15px rgba(0,0,0,0.1);
    --ai-bubble-ai-shadow: 0 2px 10px rgba(0,0,0,0.02);
  }
  
  #chat-window {
    min-height: 350px;
    max-height: 60vh;
    overflow-y: auto;
    background: var(--ai-chat-bg);
    border-radius: 12px 12px 0 0;
    padding: 24px;
    display: flex;
    flex-direction: column;
    scroll-behavior: smooth;
  }

  /* Scrollbar */
  #chat-window::-webkit-scrollbar { width: 6px; }
  #chat-window::-webkit-scrollbar-track { background: transparent; }
  #chat-window::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
  #chat-window::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

  .bubble-user {
    background: var(--primary, #007bff);
    color: #ffffff;
    border-radius: 18px 18px 4px 18px;
    padding: 12px 18px;
    max-width: 80%;
    align-self: flex-end;
    margin-left: auto;
    margin-bottom: 16px;
    box-shadow: var(--ai-bubble-user-shadow);
    word-break: break-word;
    font-size: 0.95rem;
    line-height: 1.5;
  }
  
  .bubble-ai {
    background: #ffffff;
    color: #334155;
    border: 1px solid #e2e8f0;
    border-radius: 4px 18px 18px 18px;
    padding: 14px 20px;
    max-width: 85%;
    align-self: flex-start;
    margin-bottom: 24px;
    box-shadow: var(--ai-bubble-ai-shadow);
    word-break: break-word;
    font-size: 0.95rem;
    line-height: 1.6;
    position: relative;
  }
  
  .bubble-ai > i.fa-robot {
    color: var(--primary, #007bff) !important;
    font-size: 1.2rem;
    margin-right: 8px !important;
    vertical-align: middle;
  }
  
  .bubble-ai .sug-icon {
    font-size: 0.8rem;
    color: #fbbf24;
    margin-left: 8px;
    cursor: help;
  }
  
  .bubble-spinner {
    color: #64748b;
    font-style: italic;
    font-size: 0.9rem;
    margin-bottom: 16px;
    align-self: flex-start;
    padding: 12px 20px;
    background: #f1f5f9;
    border-radius: 18px;
    animation: pulse-bg 1.5s infinite;
  }

  @keyframes pulse-bg {
    0% { opacity: 0.6; }
    50% { opacity: 1; }
    100% { opacity: 0.6; }
  }

  #btn-voice.recording {
    background-color: #ef4444 !important;
    border-color: #ef4444 !important;
    color: white !important;
    animation: pulse-record 1.5s infinite;
  }
  @keyframes pulse-record {
    0%   { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); }
    70%  { box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); }
    100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
  }

  /* WhatsApp-style recording bar */
  .wa-recording-bar {
    display: flex;
    align-items: center;
    background: #fff;
    border: 1.5px solid #fca5a5;
    border-radius: 14px;
    padding: 8px 12px;
    gap: 10px;
  }
  .wa-rec-dot {
    width: 10px; height: 10px;
    background: #ef4444;
    border-radius: 50%;
    display: inline-block;
    animation: pulse-record 1.2s infinite;
    flex-shrink: 0;
  }
  #btn-tts-toggle.tts-muted { opacity: 0.4; }
  #btn-tts-toggle.tts-speaking {
    color: #2563eb !important;
    animation: pulse-record 1.5s infinite;
  }

  /* Modern Suggestion Buttons */
  .btn-pregunta {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 12px 16px;
    color: #475569;
    font-weight: 500;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    margin-bottom: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    width: 100%;
    text-align: left;
  }
  .btn-pregunta:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  }
  .btn-pregunta i {
    width: 24px;
    margin-right: 8px;
    text-align: center;
    opacity: 0.8;
  }

  /* Modern Input Area */
  .chat-input-container {
    background: #ffffff;
    border-top: 1px solid #e2e8f0;
    padding: 16px 24px;
    border-radius: 0 0 12px 12px;
  }
  
  .modern-input-wrapper {
    background: #f1f5f9;
    border-radius: 30px;
    padding: 6px 6px 6px 20px;
    display: flex;
    align-items: center;
    border: 1px solid transparent;
    transition: all 0.3s ease;
  }

  .modern-input-wrapper:focus-within {
    background: #ffffff;
    border-color: #93c5fd;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
  }

  .modern-input-wrapper input {
    border: none !important;
    background: transparent !important;
    box-shadow: none !important;
    padding: 10px 0;
    font-size: 1rem;
    flex-grow: 1;
    flex-shrink: 1;
    min-width: 0;
    color: #334155;
  }
  
  .modern-input-wrapper input:focus {
    outline: none;
  }

  .modern-action-btn {
    border-radius: 30px;
    min-width: 42px;
    height: 42px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 12px;
    margin-left: 6px;
    border: none;
    transition: transform 0.2s, background-color 0.2s;
    white-space: nowrap;
    flex-shrink: 0;
  }
  
  .modern-action-btn:hover {
    transform: scale(1.05);
  }
  
  .modern-action-btn i {
    font-size: 1.1rem;
  }

  .card-modern {
    border-radius: 12px;
    border: none;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    background: #ffffff;
  }
  
  .card-header-modern {
    background: #ffffff;
    border-bottom: 1px solid #f1f5f9;
    border-radius: 12px 12px 0 0 !important;
    padding: 16px 24px;
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
      <div class="row mb-3 align-items-center">
        <div class="col-12 col-sm-6">
          <h1 class="m-0 d-flex align-items-center flex-wrap" style="gap: 8px;">
            <span><i class="fas fa-robot text-warning mr-1"></i> Asistente IA</span>
            <span class="badge badge-warning" style="font-size: 0.9rem;">Demo</span>
          </h1>
        </div>
        <div class="col-12 col-sm-6 text-sm-right text-left mt-2 mt-sm-0">
          <small class="text-muted" style="font-size: 0.85rem;">
            <i class="fas fa-microphone text-secondary mr-1"></i> Funciona mejor en Chrome/Edge
          </small>
        </div>
      </div>
    </div>
  </section>

  <section class="content pb-4">
    <div class="container-fluid">

      <!-- Barra de uso del free tier -->
      <div class="card card-modern mb-3" style="border:1px solid rgba(0,0,0,0.05);">
        <div class="card-body py-2 px-3">
          <div class="d-flex justify-content-between align-items-center">
            <small class="text-muted">
              Uso IA hoy
              (<span id="proveedor-ia" class="font-weight-bold text-dark text-capitalize">Groq</span>):
              <strong id="requests-hoy" class="text-dark">0</strong> /
              <strong id="requests-limite">14,400</strong> consultas
              <span class="ml-2">· <span id="tokens-usados" class="text-dark">0</span> tokens usados</span>
            </small>
            <small class="text-muted" id="proveedor-modelo">llama-3.1-8b-instant</small>
          </div>
          <div class="progress mt-2" style="height:6px; border-radius: 10px;">
            <div id="barra-uso" class="progress-bar bg-primary" style="width:0%; border-radius: 10px;" role="progressbar"></div>
          </div>
        </div>
      </div>

      <div class="row align-items-start">

        <!-- Columna izquierda: preguntas sugeridas -->
        <div class="col-12 col-md-4 col-lg-3 mb-3 mb-md-0">
          <div class="card card-modern">
            <div class="card-header card-header-modern">
              <h3 class="card-title font-weight-bold text-dark" style="font-size: 1.1rem; border-bottom: none;">
                <i class="fas fa-magic text-primary mr-2"></i> Sugerencias
              </h3>
            </div>
            <div class="card-body bg-light" style="border-radius: 0 0 12px 12px;">
              <p class="text-muted small mb-2">Toca o di la pregunta en voz alta</p>
              <div class="d-flex flex-column">

                <small class="text-uppercase text-muted d-block mb-1" style="font-size:0.68rem;letter-spacing:0.06em;font-weight:600;">Saldo y actividad</small>
                <button class="btn btn-pregunta text-left" data-question="¿Cuál es el saldo actual de caja chica?">
                  <i class="fas fa-wallet text-primary"></i> <span>¿Cuál es el saldo actual?</span>
                </button>
                <button class="btn btn-pregunta text-left" data-question="¿Cuántos registros hubo hoy y de qué montos?">
                  <i class="fas fa-calendar-day text-secondary"></i> <span>¿Qué movimientos hubo hoy?</span>
                </button>

                <small class="text-uppercase text-muted d-block mb-1 mt-3" style="font-size:0.68rem;letter-spacing:0.06em;font-weight:600;">Por entidad / área</small>
                <button class="btn btn-pregunta text-left" data-question="¿Cuánto se cargó a Gruas este mes?">
                  <i class="fas fa-truck text-warning"></i> <span>Cargado a Gruas este mes</span>
                </button>
                <button class="btn btn-pregunta text-left" data-question="¿Cuánto se cargó al Hotel en lo que va del año?">
                  <i class="fas fa-hotel text-info"></i> <span>Cargado al Hotel este año</span>
                </button>
                <button class="btn btn-pregunta text-left" data-question="¿Cuánto movió la Base Santa Fe en lo que va del año?">
                  <i class="fas fa-map-marker-alt text-danger"></i> <span>Base Santa Fe — lo que va del año</span>
                </button>
                <button class="btn btn-pregunta text-left" data-fill="¿Cuánto le pagamos a  este mes?">
                  <i class="fas fa-user text-success"></i> <span>Pagos a una persona…</span>
                </button>

                <small class="text-uppercase text-muted d-block mb-1 mt-3" style="font-size:0.68rem;letter-spacing:0.06em;font-weight:600;">Análisis</small>
                <button class="btn btn-pregunta text-left" data-question="¿Cuáles son los principales tipos de gasto del mes?">
                  <i class="fas fa-list text-info"></i> <span>Principales gastos del mes</span>
                </button>
                <button class="btn btn-pregunta text-left" data-question="¿Cómo van los ingresos vs egresos este año?">
                  <i class="fas fa-chart-pie text-warning"></i> <span>Ingresos vs egresos del año</span>
                </button>
                <button class="btn btn-pregunta text-left" data-question="¿Cuál fue nuestro mejor mes del año?">
                  <i class="fas fa-trophy" style="color:#8b5cf6;"></i> <span>Mejor mes del año</span>
                </button>

              </div>
            </div>
          </div>


          <!-- Nota de compatibilidad (si no hay voz) -->
          <div id="voice-warning" class="callout callout-danger d-none mt-3 card-modern py-3 px-3">
            <small>
              <i class="fas fa-exclamation-triangle mr-1"></i>
              Tu navegador no soporta entrada de voz. Usa el texto.
            </small>
          </div>
        </div>

        <!-- Columna derecha: chat -->
        <div class="col-12 col-md-8 col-lg-9">
          <div class="card card-modern">
            <div class="card-header card-header-modern d-flex justify-content-between align-items-center flex-nowrap">
              <h3 class="card-title font-weight-bold text-dark mb-0 text-truncate" style="font-size: 1.1rem; border-bottom: none; max-width: 80%;">
                <i class="fas fa-robot text-primary mr-1"></i> Asistente de Caja Chica
              </h3>
              <div class="card-tools m-0 flex-shrink-0 d-flex align-items-center gap-1" style="gap:6px;">
                <button id="btn-tts-toggle" class="btn btn-light btn-sm rounded-circle d-flex align-items-center justify-content-center" style="width:32px;height:32px;padding:0;" title="Activar/desactivar voz">
                  <i class="fas fa-volume-up text-muted" style="margin-top:1px;"></i>
                </button>
                <button id="btn-clear-chat" class="btn btn-light btn-sm rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; padding: 0;" title="Limpiar chat">
                  <i class="fas fa-comment-slash text-muted" style="margin-top: 1px;"></i>
                </button>
              </div>
            </div>
            
            <div class="card-body p-0">
              <div id="chat-window">
                <!-- Mensaje de bienvenida -->
                <div class="bubble-ai">
                  <i class="fas fa-robot text-primary mr-2"></i>
                  Hola, soy tu asistente de Caja Chica. Puedes preguntarme sobre saldos, gastos, ingresos, registros o análisis de ahorro. ¿En qué te puedo ayudar?
                </div>
              </div>
            </div>
            
            <div class="chat-input-container">
              <!-- Barra de grabación estilo WhatsApp (reemplaza el input mientras graba) -->
              <div id="voice-status" class="d-none mb-2">
                <div id="wa-recording-bar" class="wa-recording-bar">
                  <button id="btn-voice-cancel" class="btn btn-light btn-sm rounded-circle p-0 d-flex align-items-center justify-content-center flex-shrink-0" style="width:34px;height:34px;" title="Cancelar grabación">
                    <i class="fas fa-trash-alt text-danger"></i>
                  </button>
                  <span class="wa-rec-dot"></span>
                  <span id="voice-timer" class="text-danger font-weight-bold" style="min-width:32px;font-size:0.95rem;">0:00</span>
                  <span class="text-muted flex-grow-1" style="font-size:0.82rem;">Grabando...</span>
                  <button id="btn-voice-send" class="btn btn-success btn-sm rounded-circle p-0 d-flex align-items-center justify-content-center flex-shrink-0" style="width:34px;height:34px;" title="Enviar audio">
                    <i class="fas fa-paper-plane"></i>
                  </button>
                </div>
                <div id="voice-processing" class="d-none text-center py-2">
                  <i class="fas fa-spinner fa-spin text-primary mr-1"></i>
                  <small class="text-muted">Procesando con Whisper...</small>
                </div>
              </div>
              <div class="modern-input-wrapper">
                <input type="text" id="user-input" class="form-control"
                  placeholder="Escribe o habla tu pregunta..." autocomplete="off" />
                <button id="btn-voice" class="btn btn-light modern-action-btn text-secondary" type="button" title="Hablar">
                  <i class="fas fa-microphone"></i>
                </button>
                <button id="btn-send" class="btn btn-primary modern-action-btn shadow-sm" type="button" title="Enviar">
                  Enviar
                </button>
              </div>
              <div class="text-center mt-3 mb-1">
                <small class="text-muted" style="font-size: 0.75rem;">La IA puede cometer errores. Verifica la información.</small>
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
