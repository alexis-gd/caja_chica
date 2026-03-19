-- ============================================================
-- Migración: Asistente IA — Caja Chica
-- Ejecutar en phpMyAdmin sobre la BD: grupour1_caja_chica
-- ============================================================

-- Historial de conversaciones (agnóstico al proveedor de IA)
CREATE TABLE IF NOT EXISTS ai_conversaciones (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  pregunta      TEXT NOT NULL,
  respuesta     TEXT NOT NULL,
  proveedor_ia  VARCHAR(50)  DEFAULT 'groq',
  modelo        VARCHAR(100) DEFAULT NULL,
  tokens        INT          DEFAULT 0,
  creado        DATETIME     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB CHARSET=utf8;

-- Uso diario por proveedor (para barra de free tier)
CREATE TABLE IF NOT EXISTS ai_uso_diario (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  fecha          DATE        NOT NULL,
  proveedor_ia   VARCHAR(50) DEFAULT 'groq',
  tokens_usados  INT         DEFAULT 0,
  requests       INT         DEFAULT 0,
  UNIQUE KEY uq_fecha_proveedor (fecha, proveedor_ia)
) ENGINE=InnoDB CHARSET=utf8;

-- Sugerencias de mejora de software detectadas por la IA
-- Solo visibles para el desarrollador (modo dev secreto en la UI)
CREATE TABLE IF NOT EXISTS ai_sugerencias (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  sugerencia    TEXT        NOT NULL,
  justificacion TEXT        DEFAULT NULL,
  origen        TEXT        DEFAULT NULL,
  revisada      TINYINT(1)  DEFAULT 0,
  creado        DATETIME    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB CHARSET=utf8;
