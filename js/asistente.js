/**
 * asistente.js — Lógica del Asistente IA con Voz
 * Caja Chica — Grupo Uribe
 */

'use strict';

// ─────────────────────────────────────────────
// Estado global
// ─────────────────────────────────────────────
let contextData    = null;
let mediaRecorder  = null;
let audioChunks    = [];
let isRecording    = false;
let chartInstance  = null;
let isSending      = false;  // bloquea mientras hay request en vuelo
let lastMessage    = null;   // último mensaje enviado, para reintentar
let ttsEnabled        = true;   // toggle auto-lectura de respuestas
let _timerInterval    = null;   // intervalo del cronómetro de grabación
let _shouldSendAudio  = false;  // flag: true=enviar, false=cancelar al detener grabación

const VOICE_OK    = !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia);
const AI_ENDPOINT = 'functions/ai/chat.php';

// ─────────────────────────────────────────────
// Inicialización
// ─────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  loadContext();
  bindEvents();
  initDevMode();

  // Precargar voces (Chrome las carga de forma asíncrona)
  if ('speechSynthesis' in window) {
    window.speechSynthesis.getVoices();
    window.speechSynthesis.onvoiceschanged = function() { _cachedVoice = null; };
  }

  // Cancelar TTS al salir/recargar la página
  window.addEventListener('beforeunload', function() {
    if ('speechSynthesis' in window) window.speechSynthesis.cancel();
  });

  if (!VOICE_OK) {
    document.getElementById('btn-voice').style.display = 'none';
    document.getElementById('voice-warning').classList.remove('d-none');
  }
});

// ─────────────────────────────────────────────
// Cargar contexto inicial desde BD
// ─────────────────────────────────────────────
async function loadContext() {
  try {
    const fd = new FormData();
    fd.append('opcion', 'getContextData');
    const res  = await fetch(AI_ENDPOINT, { method: 'POST', body: fd });
    const data = await res.json();

    if (data.type === 'SUCCESS') {
      contextData = data.data;
      actualizarBarraUso(data.uso);
    }
  } catch (e) {
    console.warn('No se pudo cargar el contexto de IA:', e);
  }
}

// ─────────────────────────────────────────────
// Barra de uso del free tier
// ─────────────────────────────────────────────
function actualizarBarraUso(uso) {
  if (!uso) return;

  // La barra refleja requests (límite real del free tier), no tokens
  const pct = Math.min(100, Math.round((uso.requests / uso.requests_limite) * 100));

  document.getElementById('tokens-usados').textContent    = uso.tokens_usados.toLocaleString('es-MX');
  document.getElementById('requests-hoy').textContent     = uso.requests.toLocaleString('es-MX');
  document.getElementById('requests-limite').textContent  = uso.requests_limite.toLocaleString('es-MX');
  document.getElementById('proveedor-ia').textContent     = uso.proveedor;
  document.getElementById('proveedor-modelo').textContent = uso.modelo;

  const barra = document.getElementById('barra-uso');
  barra.style.width = pct + '%';
  barra.classList.remove('bg-info', 'bg-warning', 'bg-danger');
  if (pct > 80) barra.classList.add('bg-danger');
  else if (pct > 50) barra.classList.add('bg-warning');
  else barra.classList.add('bg-info');
}

// ─────────────────────────────────────────────
// Enviar mensaje al asistente
// ─────────────────────────────────────────────
async function sendMessage(text) {
  text = text.trim();
  if (!text || isSending) return;

  isSending   = true;
  lastMessage = text;
  document.getElementById('user-input').value = '';
  appendBubbleUser(text);
  const spinnerId = appendSpinner();

  try {
    const fd = new FormData();
    fd.append('opcion',        'askAssistant');
    fd.append('mensaje',       text);
    const _d = new Date();
    fd.append('fecha_cliente', _d.getFullYear() + '-' + String(_d.getMonth()+1).padStart(2,'0') + '-' + String(_d.getDate()).padStart(2,'0'));

    const res  = await fetch(AI_ENDPOINT, { method: 'POST', body: fd });
    const data = await res.json();

    removeSpinner(spinnerId);

    if (data.type === 'SUCCESS') {
      appendBubbleAI(data.message, data.sugerencia_guardada);
      if (data.show_chart && contextData && contextData.tendencia_anual) {
        renderMiniChart(contextData.tendencia_anual);
      }
      if (data.uso) actualizarBarraUso(data.uso);
      speak(data.message);
    } else if (data.type === 'RATE_LIMIT') {
      appendBubbleRateLimit(data.message, data.debug || null);
    } else {
      appendBubbleAI('Lo siento, ocurrió un error. Intenta de nuevo.');
      _notificar('danger', 'Error del asistente IA');
    }
  } catch (e) {
    removeSpinner(spinnerId);
    appendBubbleAI('No se pudo conectar con el asistente. Revisa tu conexión.');
    console.error('Error en sendMessage:', e);
  } finally {
    isSending = false;
  }
}

// ─────────────────────────────────────────────
// Burbujas del chat
// ─────────────────────────────────────────────
function appendBubbleUser(text) {
  const div  = document.createElement('div');
  div.className = 'd-flex justify-content-end';
  div.innerHTML = `<div class="bubble-user">${escapeHtml(text)}</div>`;
  getChatWindow().appendChild(div);
  scrollChatBottom();
}

function inlineMarkdown(text) {
  text = escapeHtml(text);
  text = text.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
  text = text.replace(/\*(.+?)\*/g, '<em>$1</em>');
  return text;
}

function buildHtmlTable(lines) {
  var html = '<table class="table table-sm table-bordered table-hover mb-1 mt-1" style="font-size:.85rem">';
  var isHeader = true;
  lines.forEach(function(line) {
    if (/^\|[\s\-:|]+\|/.test(line.trim())) return; // separador
    var cells = line.split('|').slice(1, -1);
    var tag = isHeader ? 'th' : 'td';
    html += '<tr>';
    cells.forEach(function(cell) {
      var content = escapeHtml(cell.trim());
      var cls = '';
      if (content.indexOf('↑') !== -1) cls = ' class="text-danger font-weight-bold"';
      else if (content.indexOf('↓') !== -1) cls = ' class="text-success font-weight-bold"';
      html += '<' + tag + cls + '>' + content + '</' + tag + '>';
    });
    html += '</tr>';
    isHeader = false;
  });
  html += '</table>';
  return html;
}

function renderMarkdown(text) {
  var lines  = text.split('\n');
  var html   = '';
  var i      = 0;
  while (i < lines.length) {
    var line = lines[i];
    // Tabla markdown
    if (line.trim().charAt(0) === '|') {
      var tableLines = [];
      while (i < lines.length && lines[i].trim().charAt(0) === '|') {
        tableLines.push(lines[i]);
        i++;
      }
      html += buildHtmlTable(tableLines);
      continue;
    }
    // Lista numerada
    var numMatch = line.match(/^\s*\d+\.\s+(.+)/);
    if (numMatch) {
      html += '<div class="ml-2 mb-1">• ' + inlineMarkdown(numMatch[1]) + '</div>';
      i++;
      continue;
    }
    // Línea normal
    if (line.trim()) {
      html += '<p class="mb-1">' + inlineMarkdown(line) + '</p>';
    }
    i++;
  }
  return html;
}

function extractCandidatos(text) {
  var match = text.match(/\[CANDIDATOS:\s*(.+?)\]/i);
  if (!match) return { text: text, candidatos: [] };
  var candidatos = match[1].split('|').map(function(n) { return n.trim(); }).filter(Boolean);
  var cleanText  = text.replace(/\[CANDIDATOS:.*?\]/i, '').trim();
  return { text: cleanText, candidatos: candidatos };
}

function appendBubbleAI(text, sugerenciaGuardada = false) {
  const icon = sugerenciaGuardada
    ? `<span class="sug-icon" title="Sugerencia de mejora registrada para el desarrollador">💡</span>`
    : '';

  const parsed = extractCandidatos(text);

  // Botones de candidatos si los hay
  var botonesHtml = '';
  if (parsed.candidatos.length > 0) {
    var btns = parsed.candidatos.map(function(nombre) {
      return `<button class="btn btn-sm btn-outline-secondary btn-candidato mr-1 mb-1" data-nombre="${escapeHtml(nombre)}">
        <i class="fas fa-user mr-1"></i>${escapeHtml(nombre)}
      </button>`;
    }).join('');
    botonesHtml = `<div class="mt-2">${btns}</div>`;
  }

  const div  = document.createElement('div');
  div.className = 'd-flex justify-content-start';
  div.innerHTML = `<div class="bubble-ai">
    <i class="fas fa-robot text-warning mr-1" style="font-size:.85rem"></i>
    ${renderMarkdown(parsed.text)}${icon}${botonesHtml}
  </div>`;

  // Evento: click en candidato → envía pregunta con ese nombre exacto
  div.querySelectorAll('.btn-candidato').forEach(function(btn) {
    btn.addEventListener('click', function() {
      var nombre = btn.getAttribute('data-nombre');
      sendMessage('Dame los datos de ' + nombre);
    });
  });

  getChatWindow().appendChild(div);
  scrollChatBottom();
}

function appendBubbleRateLimit(message, debug) {
  var debugHtml = debug
    ? `<details class="mt-2"><summary class="small text-muted" style="cursor:pointer">[DEV] ver error real</summary>
        <code class="small d-block mt-1 text-danger" style="white-space:pre-wrap">${escapeHtml(debug)}</code>
       </details>`
    : '';
  const div = document.createElement('div');
  div.className = 'd-flex justify-content-start';
  div.innerHTML = `<div class="bubble-ai">
    <i class="fas fa-robot text-warning mr-1" style="font-size:.85rem"></i>
    ${escapeHtml(message)}
    <div class="mt-2">
      <button class="btn btn-sm btn-outline-primary btn-retry-rate-limit">
        <i class="fas fa-redo mr-1"></i> Reintentar
      </button>
    </div>
    ${debugHtml}
  </div>`;
  div.querySelector('.btn-retry-rate-limit').addEventListener('click', function() {
    div.remove();
    if (lastMessage) sendMessage(lastMessage);
  });
  getChatWindow().appendChild(div);
  scrollChatBottom();
}

function appendSpinner() {
  const id  = 'spinner-' + Date.now();
  const div = document.createElement('div');
  div.id        = id;
  div.className = 'bubble-spinner';
  div.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Pensando...';
  getChatWindow().appendChild(div);
  scrollChatBottom();
  return id;
}

function removeSpinner(id) {
  const el = document.getElementById(id);
  if (el) el.remove();
}

function getChatWindow() {
  return document.getElementById('chat-window');
}

function scrollChatBottom() {
  const w = getChatWindow();
  w.scrollTop = w.scrollHeight;
}

// ─────────────────────────────────────────────
// Mini gráfica Chart.js (ingresos vs egresos)
// ─────────────────────────────────────────────
function renderMiniChart(tendencia) {
  // Destruir gráfica previa si existe
  if (chartInstance) {
    chartInstance.destroy();
    chartInstance = null;
  }

  const labels   = tendencia.map(t => (t.mes || '').toUpperCase().substring(0, 3));
  const ingresos = tendencia.map(t => parseFloat(t.ingreso) || 0);
  const egresos  = tendencia.map(t => parseFloat(t.egreso)  || 0);

  const canvasId = 'chart-' + Date.now();
  const wrapper  = document.createElement('div');
  wrapper.className = 'd-flex justify-content-start';
  wrapper.innerHTML = `
    <div class="bubble-ai w-100" style="max-width:100%">
      <small class="text-muted d-block mb-1">
        <i class="fas fa-chart-bar mr-1"></i> Ingresos vs Egresos ${new Date().getFullYear()}
      </small>
      <canvas id="${canvasId}" height="140"></canvas>
    </div>`;

  getChatWindow().appendChild(wrapper);
  scrollChatBottom();

  const ctx = document.getElementById(canvasId).getContext('2d');
  chartInstance = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [
        {
          label: 'Ingreso',
          data: ingresos,
          backgroundColor: '#007bff',
          borderRadius: 3,
        },
        {
          label: 'Egreso',
          data: egresos,
          backgroundColor: '#ced4da',
          borderRadius: 3,
        },
      ],
    },
    options: {
      responsive: true,
      plugins: {
        legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } },
        tooltip: {
          callbacks: {
            label: ctx => ' $' + ctx.parsed.y.toLocaleString('es-MX', { minimumFractionDigits: 2 }),
          },
        },
      },
      scales: {
        y: {
          ticks: {
            callback: v => '$' + (v >= 1000 ? (v / 1000).toFixed(1) + 'k' : v),
            font: { size: 10 },
          },
        },
        x: { ticks: { font: { size: 10 } } },
      },
    },
  });
}

// ─────────────────────────────────────────────
// Síntesis de voz (TTS)
// ─────────────────────────────────────────────
let _cachedVoice = null;

function getBestSpanishVoice() {
  if (_cachedVoice) return _cachedVoice;
  const voices = window.speechSynthesis.getVoices();
  if (!voices.length) return null;

  // Prioridad: Google es-US > Google español > Microsoft español > cualquier español
  const checks = [
    function(v) { return v.lang === 'es-US' && v.name.toLowerCase().indexOf('google') !== -1; },
    function(v) { return v.lang.indexOf('es') === 0 && v.name.toLowerCase().indexOf('google') !== -1; },
    function(v) { return v.lang.indexOf('es') === 0 && v.name.toLowerCase().indexOf('microsoft') !== -1; },
    function(v) { return v.lang === 'es-MX'; },
    function(v) { return v.lang.indexOf('es') === 0; },
  ];

  for (var i = 0; i < checks.length; i++) {
    var found = voices.filter(checks[i])[0];
    if (found) { _cachedVoice = found; return found; }
  }
  return null;
}

function stripMarkdown(text) {
  return text
    .replace(/\*\*(.+?)\*\*/g, '$1')   // **negrita**
    .replace(/\*(.+?)\*/g, '$1')        // *itálica*
    .replace(/^\s*\d+\.\s+/gm, '')      // listas numeradas "1. "
    .replace(/^\s*[-*]\s+/gm, '')       // listas con guión/asterisco
    .replace(/#{1,6}\s+/g, '')          // encabezados #
    .replace(/`(.+?)`/g, '$1')          // `código`
    .replace(/↑/g, 'subió')
    .replace(/↓/g, 'bajó')
    .trim();
}

function speak(text) {
  if (!('speechSynthesis' in window) || !ttsEnabled) return;
  window.speechSynthesis.cancel();
  const utter = new SpeechSynthesisUtterance(stripMarkdown(text));
  utter.lang  = 'es-MX';
  utter.rate  = 1.4;
  utter.pitch = 1.05;
  const voice = getBestSpanishVoice();
  if (voice) utter.voice = voice;

  var btn = document.getElementById('btn-tts-toggle');
  btn.classList.add('tts-speaking');
  utter.onend = function() { btn.classList.remove('tts-speaking'); };
  utter.onerror = function() { btn.classList.remove('tts-speaking'); };

  window.speechSynthesis.speak(utter);
}

// ─────────────────────────────────────────────
// Corrección post-STT: colapsa letras deletreadas y normaliza términos del negocio
// ─────────────────────────────────────────────
const STT_CORRECTIONS = {
  // letras deletreadas que el STT puede generar al no reconocer la palabra
  'g r u a s' : 'gruas',
  'g r u a'   : 'grua',
  'g u g u'   : 'Gugu',
  'google'    : 'Gugu',
  'Google'    : 'Gugu',
  // alias de persona: cuando el STT pierde el nombre pero mantiene el contexto
  'mi hijo'   : 'Gugu',
  // pronunciación en inglés / errores comunes
  'inversión'  : 'inversión',   // ya correcto, placeholder por si varía
  'inversion'  : 'inversión',
  'inbersión'  : 'inversión',
  'inbersion'  : 'inversión',
  // nombres del negocio que el STT confunde
  'maravilla'  : 'Maravilla',
  'rancho maravilla': 'Rancho Maravilla',
  'santa fe'   : 'Santa Fe',
  'san cristóbal': 'San Cristóbal',
  'san cristobal': 'San Cristóbal',
};

function fixSTTTranscript(text) {
  var t = text.trim();

  // 1. Colapsa secuencias de letras sueltas separadas por espacios
  //    Ej: "cuánto gastó g r u a s este mes" → "cuánto gastó gruas este mes"
  t = t.replace(/\b([a-záéíóúñ]) (?:[a-záéíóúñ] ){1,}[a-záéíóúñ]\b/gi, function(match) {
    return match.replace(/ /g, '');
  });

  // 2. Aplica diccionario de correcciones (case-insensitive)
  Object.keys(STT_CORRECTIONS).forEach(function(wrong) {
    var re = new RegExp('\\b' + wrong.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '\\b', 'gi');
    t = t.replace(re, STT_CORRECTIONS[wrong]);
  });

  return t;
}

// Grabación de voz con Groq Whisper (UX estilo WhatsApp)
// ─────────────────────────────────────────────
function startWhisperRecording() {
  _shouldSendAudio = false;
  navigator.mediaDevices.getUserMedia({ audio: true })
    .then(function(stream) {
      audioChunks   = [];
      mediaRecorder = new MediaRecorder(stream);

      mediaRecorder.ondataavailable = function(e) {
        if (e.data.size > 0) audioChunks.push(e.data);
      };

      mediaRecorder.onstop = function() {
        stopTimer();
        stream.getTracks().forEach(function(t) { t.stop(); });
        if (_shouldSendAudio) {
          var blob = new Blob(audioChunks, { type: 'audio/webm' });
          sendAudioToWhisper(blob);
        } else {
          hideRecordingBar();
        }
      };

      isRecording = true;
      mediaRecorder.start();
      showRecordingBar();
    })
    .catch(function(err) {
      _notificar('warning', 'No se pudo acceder al micrófono: ' + err.message);
    });
}

function showRecordingBar() {
  document.getElementById('voice-status').classList.remove('d-none');
  document.getElementById('wa-recording-bar').classList.remove('d-none');
  document.getElementById('voice-processing').classList.add('d-none');
  document.getElementById('btn-voice').style.display = 'none';
  startTimer();
}

function hideRecordingBar() {
  isRecording = false;
  stopTimer();
  document.getElementById('voice-status').classList.add('d-none');
  document.getElementById('btn-voice').style.display = '';
}

function startTimer() {
  var secs = 0;
  var el   = document.getElementById('voice-timer');
  el.textContent = '0:00';
  _timerInterval = setInterval(function() {
    secs++;
    var m = Math.floor(secs / 60);
    var s = secs % 60;
    el.textContent = m + ':' + (s < 10 ? '0' : '') + s;
  }, 1000);
}

function stopTimer() {
  if (_timerInterval) { clearInterval(_timerInterval); _timerInterval = null; }
}

function stopAndSendRecording() {
  if (!mediaRecorder || mediaRecorder.state === 'inactive') return;
  _shouldSendAudio = true;
  isRecording      = false;
  document.getElementById('wa-recording-bar').classList.add('d-none');
  document.getElementById('voice-processing').classList.remove('d-none');
  mediaRecorder.stop();
}

function cancelRecording() {
  if (!mediaRecorder || mediaRecorder.state === 'inactive') return;
  _shouldSendAudio = false;
  isRecording      = false;
  mediaRecorder.stop();
}

async function sendAudioToWhisper(blob) {
  var fd = new FormData();
  fd.append('audio', blob, 'audio.webm');

  try {
    var res  = await fetch('functions/ai/whisper.php', { method: 'POST', body: fd });
    var data = await res.json();

    hideRecordingBar();

    if (data.type === 'SUCCESS' && data.text) {
      var corrected = fixSTTTranscript(data.text);
      document.getElementById('user-input').value = corrected;
      sendMessage(corrected);
    } else {
      _notificar('warning', data.message || 'Error al transcribir audio.');
    }
  } catch (err) {
    hideRecordingBar();
    _notificar('danger', 'Error de conexión al transcribir audio.');
  }
}

function updateMicUI(recording) {
  const btn = document.getElementById('btn-voice');
  if (recording) {
    btn.classList.remove('btn-warning');
    btn.classList.add('btn-danger', 'recording');
    btn.innerHTML = '<i class="fas fa-stop mr-1"></i> Enviar voz';
    btn.title = 'Detener y enviar';
  } else {
    btn.classList.remove('btn-danger', 'recording');
    btn.classList.add('btn-warning');
    btn.innerHTML = '<i class="fas fa-microphone"></i>';
    btn.title = 'Hablar';
  }
}

// ─────────────────────────────────────────────
// Eventos de botones
// ─────────────────────────────────────────────
function bindEvents() {
  // Botones de preguntas sugeridas — envío directo
  document.querySelectorAll('.btn-pregunta[data-question]').forEach(btn => {
    btn.addEventListener('click', () => {
      sendMessage(btn.dataset.question);
    });
  });

  // Botones de preguntas sugeridas — rellenar input para que el usuario complete el nombre
  document.querySelectorAll('.btn-pregunta[data-fill]').forEach(btn => {
    btn.addEventListener('click', () => {
      const input = document.getElementById('user-input');
      input.value = btn.dataset.fill;
      input.focus();
    });
  });

  // Botón enviar
  document.getElementById('btn-send').addEventListener('click', () => {
    sendMessage(document.getElementById('user-input').value);
  });

  // Enter en input
  document.getElementById('user-input').addEventListener('keydown', (e) => {
    if (e.key === 'Enter') sendMessage(e.target.value);
  });

  // Botón micrófono — iniciar grabación
  document.getElementById('btn-voice').addEventListener('click', () => {
    if (!VOICE_OK) return;
    startWhisperRecording();
  });

  // Botón enviar audio (✓ verde en barra de grabación)
  document.getElementById('btn-voice-send').addEventListener('click', () => {
    stopAndSendRecording();
  });

  // Botón cancelar grabación (🗑️ rojo)
  document.getElementById('btn-voice-cancel').addEventListener('click', () => {
    cancelRecording();
  });

  // Botón TTS toggle (silenciar/activar voz)
  document.getElementById('btn-tts-toggle').addEventListener('click', () => {
    ttsEnabled = !ttsEnabled;
    var btn = document.getElementById('btn-tts-toggle');
    var ico = btn.querySelector('i');
    if (ttsEnabled) {
      ico.className = 'fas fa-volume-up text-muted';
      btn.classList.remove('tts-muted');
      btn.title = 'Desactivar voz';
    } else {
      if ('speechSynthesis' in window) window.speechSynthesis.cancel();
      ico.className = 'fas fa-volume-mute';
      btn.classList.add('tts-muted');
      btn.title = 'Activar voz';
    }
  });

  // Limpiar chat
  document.getElementById('btn-clear-chat').addEventListener('click', () => {
    if (chartInstance) { chartInstance.destroy(); chartInstance = null; }
    getChatWindow().innerHTML = `
      <div class="d-flex justify-content-start">
        <div class="bubble-ai">
          <i class="fas fa-robot text-warning mr-1"></i>
          Chat limpiado. ¿En qué te puedo ayudar?
        </div>
      </div>`;
  });

  // Marcar sugerencias como revisadas (delegación de eventos)
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.btn-marcar-revisada');
    if (!btn) return;
    marcarSugerenciaRevisada(parseInt(btn.dataset.id, 10));
  });
}

// ─────────────────────────────────────────────
// Marcar sugerencia como revisada (solo dev)
// ─────────────────────────────────────────────
async function marcarSugerenciaRevisada(id) {
  try {
    const fd = new FormData();
    fd.append('opcion', 'marcarSugerencia');
    fd.append('id', id);
    const res  = await fetch(AI_ENDPOINT, { method: 'POST', body: fd });
    const data = await res.json();

    if (data.action === 'DELETE') {
      const row = document.getElementById('sug-row-' + id);
      if (row) row.remove();

      // Actualizar badge
      const tbody   = document.getElementById('tbody-sugerencias');
      const pending = tbody ? tbody.querySelectorAll('tr').length : 0;
      const badge   = document.getElementById('badge-sugerencias');
      if (badge) badge.textContent = pending + ' pendientes';
    }
  } catch (e) {
    console.error('Error al marcar sugerencia:', e);
  }
}

// ─────────────────────────────────────────────
// Modo dev: Ctrl+Shift+S activa/oculta la card
// ─────────────────────────────────────────────
function initDevMode() {
  document.addEventListener('keydown', (e) => {
    if (e.ctrlKey && e.shiftKey && e.key === 'S') {
      e.preventDefault();
      const card = document.getElementById('card-sugerencias');
      if (!card) return;

      if (card.style.display === 'none' || card.style.display === '') {
        // Mostrar y cargar sugerencias vía AJAX
        card.style.display = 'block';
        loadSugerenciasDevMode();
      } else {
        card.style.display = 'none';
      }
    }
  });
}

async function loadSugerenciasDevMode() {
  const tbody = document.getElementById('tbody-sugerencias');
  const body  = document.getElementById('tabla-sugerencias-body');
  if (!body) return;

  // Si ya hay filas (cargadas con ?dev=1), no recargar
  if (tbody && tbody.querySelectorAll('tr').length > 0) return;

  // Cargar vía fetch al mismo endpoint con una opción extra
  // Reutilizamos getContextData para no agregar un endpoint nuevo — las sugerencias
  // se cargan con una petición GET de la propia página en modo dev
  // Simplest: recargar la URL con ?dev=1
  window.location.href = window.location.pathname + '?dev=1';
}

// ─────────────────────────────────────────────
// Utilidades
// ─────────────────────────────────────────────
function escapeHtml(str) {
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}

function _notificar(type, message) {
  // Normalizar: SweetAlert2 no acepta 'danger', usa 'error'
  var icon = type === 'danger' ? 'error' : type;
  // global.js: alertNotify(timer, type, msj1, msj2, position)
  if (typeof window.alertNotify === 'function') {
    window.alertNotify('3000', icon, message);
  } else if (typeof Swal !== 'undefined') {
    Swal.fire({ icon: icon, text: message, timer: 3000, showConfirmButton: false });
  }
}
