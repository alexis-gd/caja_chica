/**
 * asistente.js — Lógica del Asistente IA con Voz
 * Caja Chica — Grupo Uribe
 */

'use strict';

// ─────────────────────────────────────────────
// Estado global
// ─────────────────────────────────────────────
let contextData       = null;
let recognition       = null;
let isRecording       = false;
let chartInstance     = null;
let pendingTranscript = null;   // transcript capturado, pendiente de enviar
let isSending    = false;  // bloquea mientras hay request en vuelo
let lastMessage  = null;   // último mensaje enviado, para reintentar

const VOICE_OK    = ('webkitSpeechRecognition' in window) || ('SpeechRecognition' in window);
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
  } else {
    initSpeechRecognition();
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
    fd.append('opcion',   'askAssistant');
    fd.append('mensaje',  text);

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
  if (!('speechSynthesis' in window)) return;
  window.speechSynthesis.cancel();
  const utter = new SpeechSynthesisUtterance(stripMarkdown(text));
  utter.lang  = 'es-MX';
  utter.rate  = 1.3;
  utter.pitch = 1.05;
  const voice = getBestSpanishVoice();
  if (voice) utter.voice = voice;
  window.speechSynthesis.speak(utter);
}

// ─────────────────────────────────────────────
// Reconocimiento de voz (STT)
// ─────────────────────────────────────────────
function initSpeechRecognition() {
  const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
  recognition = new SpeechRecognition();
  recognition.lang            = 'es-MX';
  recognition.interimResults  = true;   // muestra palabras en tiempo real
  recognition.maxAlternatives = 1;
  recognition.continuous      = true;   // sobrevive pausas de pensamiento

  recognition.onstart = () => {
    isRecording       = true;
    pendingTranscript = null;
    updateMicUI(true);
    document.getElementById('voice-status').classList.remove('d-none');
    document.getElementById('voice-status-text').innerHTML =
      '<i class="fas fa-microphone mr-1 text-danger"></i> Escuchando... habla y luego presiona <strong>Enviar voz</strong>';
  };

  // Acumula todos los resultados (finales + interinos) para mostrar en tiempo real.
  // El envío ocurre en onend, no aquí.
  recognition.onresult = (e) => {
    var transcript = '';
    for (var i = 0; i < e.results.length; i++) {
      transcript += e.results[i][0].transcript;
    }
    pendingTranscript = transcript.trim();
    document.getElementById('user-input').value = pendingTranscript;
  };

  recognition.onerror = (e) => {
    // 'no-speech' y 'aborted' son normales con continuous=true — ignorar
    if (e.error === 'aborted' || e.error === 'no-speech') return;
    isRecording       = false;
    pendingTranscript = null;
    updateMicUI(false);
    document.getElementById('voice-status').classList.add('d-none');
    _notificar('warning', 'Error de micrófono: ' + e.error);
  };

  recognition.onend = () => {
    // Si el usuario aún no presionó Enviar, Chrome cortó por timeout → reiniciar
    if (isRecording) {
      try { recognition.start(); } catch (err) { /* ya activo, ignorar */ }
      return;
    }
    // Usuario presionó Enviar voz → isRecording ya es false → enviar
    updateMicUI(false);
    document.getElementById('voice-status').classList.add('d-none');
    if (pendingTranscript) {
      sendMessage(pendingTranscript);
      pendingTranscript = null;
    }
  };
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
  // Botones de preguntas sugeridas
  document.querySelectorAll('.btn-pregunta').forEach(btn => {
    btn.addEventListener('click', () => {
      sendMessage(btn.dataset.question);
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

  // Botón micrófono
  document.getElementById('btn-voice').addEventListener('click', () => {
    if (!VOICE_OK || !recognition) return;
    if (isRecording) {
      isRecording = false;   // marcar ANTES de stop → onend enviará en lugar de reiniciar
      recognition.stop();
    } else {
      recognition.start();
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
