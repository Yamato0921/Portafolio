<?php
/**
 * ==============================================================================
 * PORTAFOLIO INTERACTIVO - VERSIÓN PHP SERVER-SIDE RENDERED (SSR)
 * ==============================================================================
 * Desarrollado en: HTML5, CSS3, JavaScript, PHP 8+, Bootstrap 5 y Python.
 * Portafolio personal de Sebastian Giraldo
 *
 * Características en PHP:
 *  - Inicio de sesión y generación de token CSRF para protección de formularios.
 *  - Renderizado dinámico en el servidor (SSR) de los proyectos desde projects.json.
 *  - Procesamiento seguro y sanitización del formulario de contacto.
 *  - Año de derechos de autor generado dinámicamente: <?= date('Y') ?>.
 * ==============================================================================
 */

// 1. INICIALIZACIÓN DE SESIÓN Y SEGURIDAD CSRF
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generación de token CSRF si no existe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

// 2. CARGA DE PROYECTOS DESDE EL ARCHIVO JSON PARA RENDERIZADO SSR
$projectsFile = __DIR__ . '/assets/data/projects.json';
$projectsList = [];
if (file_exists($projectsFile)) {
    $rawProjects = file_get_contents($projectsFile);
    $projectsList = json_decode($rawProjects, true) ?? [];
}

// 3. PROCESAMIENTO NATIVO DE FORMULARIO SI SE ENVÍA POR POST CLÁSICO
$mensajeExito = null;
$mensajeError = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['formulario_contacto_php'])) {
    // Verificación de token CSRF
    $tokenEnviado = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $tokenEnviado)) {
        $mensajeError = "Fallo de seguridad: Token CSRF no válido.";
    } else {
        $nombre  = htmlspecialchars(strip_tags(trim($_POST['name'] ?? '')), ENT_QUOTES, 'UTF-8');
        $email   = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        $asunto  = htmlspecialchars(strip_tags(trim($_POST['subject'] ?? '')), ENT_QUOTES, 'UTF-8');
        $mensaje = htmlspecialchars(strip_tags(trim($_POST['message'] ?? '')), ENT_QUOTES, 'UTF-8');
        $honeypot = trim($_POST['website_url'] ?? '');

        if (!empty($honeypot)) {
            $mensajeExito = "¡Mensaje recibido con éxito!";
        } elseif (empty($nombre) || empty($email) || empty($mensaje) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $mensajeError = "Por favor completa todos los campos requeridos con un email válido.";
        } else {
            $mensajeExito = "¡Gracias {$nombre}! Tu mensaje ha sido recibido por el servidor PHP con éxito.";
            // Regenerar token CSRF tras envío exitoso
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $csrfToken = $_SESSION['csrf_token'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <!-- Metadatos de la página -->
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="portfolio-runtime" content="php">
  <title>Sebastian Giraldo | Desarrollador Creativo (Edición PHP 8 & SSR)</title>
  <meta name="description" content="Portafolio interactivo de desarrollo web creativo de Sebastian Giraldo. Diseñado con HTML5, CSS3, JavaScript, PHP, Bootstrap 5 y Python.">
  <meta name="author" content="Sebastian Giraldo">
  <link rel="icon" type="image/svg+xml" href="assets/favicon.svg">

  <!-- Tipografías de Google Fonts: Outfit, Space Grotesk y Playfair Display (Serifa editorial) -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">

  <!-- Bootstrap 5 CSS y Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <!-- Estilos Personalizados: Diseño Editorial Limpio y Modo SynthWave '84 -->
  <link rel="stylesheet" href="css/style.css?v=20261023-1">
  <link rel="stylesheet" href="css/synthwave.css?v=20261023-1">
</head>
<body>

  <!-- Capa de líneas CRT para modo retro -->
  <div class="crt-overlay" aria-hidden="true"></div>

  <!-- Cabecera y navegación principal -->
  <header class="site-navbar">
    <nav class="container d-flex align-items-center justify-content-between" aria-label="Navegación principal">
      <!-- Marca Izquierda: Logotipo geométrico triangular + Menú -->
      <div class="d-flex align-items-center gap-3">
        <a href="#inicio" class="brand-triangle-wrapper" title="Inicio - Sebastian Giraldo (PHP SSR)">
          <svg class="brand-triangle-svg" viewBox="0 0 54 44" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Logotipo geométrico Sebastian Giraldo">
            <polygon points="4,38 32,38 18,14" fill="#38bdf8" opacity="0.65" />
            <polygon points="12,32 46,32 29,6" stroke="#6929f0" stroke-width="3.5" fill="none" stroke-linejoin="round" />
            <line x1="29" y1="6" x2="29" y2="32" stroke="#6929f0" stroke-width="2.5" />
          </svg>
        </a>

        <span class="brand-menu-label" data-bs-toggle="offcanvas" data-bs-target="#terminalOffcanvas" role="button" title="Abrir Menú y Terminal (Atajo: tecla /)">
          MENÚ <span class="badge bg-primary ms-1" style="font-size: 0.6rem;">PHP 8</span>
        </span>
      </div>

      <div class="d-flex align-items-center gap-2">
        <button id="audioToggleBtn" class="btn-control-pill" type="button" title="Alternar audio sintetizado">
          <i class="bi bi-volume-mute-fill text-muted"></i> Sonido: OFF
        </button>

        <button id="themeToggleBtn" class="btn-control-pill" type="button" title="Alternar modo SynthWave '84">
          <i class="bi bi-moon-stars-fill text-primary"></i> SynthWave '84
        </button>
      </div>

      <div>
        <a href="#contacto" class="btn-hire-me" title="Hablemos de tu proyecto">
          <span class="speech-icon-circle" aria-hidden="true">
            <svg width="12" height="12" viewBox="0 0 16 16" fill="#6929f0">
              <path d="M14 1a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H4.414A2 2 0 0 0 3 11.586l-2 2V2a1 1 0 0 1 1-1h12z"/>
            </svg>
          </span>
          <span>CONTRÁTAME</span>
        </a>
      </div>
    </nav>
  </header>

  <!-- Sección Hero -->
  <main>
    <section id="inicio" class="hero-section">
      <!-- Banda horizontal con franjas diagonales cian detrás del personaje -->
      <div class="hero-striped-band" aria-hidden="true"></div>

      <!-- Texto vertical de metadatos en margen inferior izquierdo -->
      <div class="hero-vertical-meta" aria-hidden="true">
        ©MMXXVI. Gwneud yn Ne Cymru.
      </div>

      <!-- Guía animada de desplazamiento hacia abajo (S C R O L L) -->
      <a href="#habilidades" class="hero-scroll-guide" title="Desplazarse a la siguiente sección">
        <span>S C R O L L</span>
        <div class="hero-scroll-line" aria-hidden="true"></div>
      </a>

      <div class="container position-relative" style="z-index: 2;">
        <div class="row align-items-center gy-5">
          <div class="col-lg-6 col-xl-6">
            <h1 class="hero-sebas-heading">
              <span class="hero-serif-line">Hola, mi</span>
              <span class="hero-serif-line">nombre es</span>
              <span class="hero-sebas-name">Sebas<span class="hero-period-dot">.</span></span>
            </h1>

            <p class="hero-subtitle-lead">
              Soy un <strong>desarrollador creativo independiente</strong> de Abergavenny, Gales del Sur.
            </p>

            <div class="d-flex align-items-center gap-3 flex-wrap mb-4">
              <a href="#contacto" class="btn-primary-custom text-decoration-none">
                <span>Hablemos de tu Proyecto</span>
                <i class="bi bi-arrow-right" aria-hidden="true"></i>
              </a>
              <a href="#proyectos" class="btn btn-outline-dark rounded-pill px-4 py-2 fw-semibold">
                Explorar Trabajos (SSR)
              </a>
            </div>

            <!-- HUD de Telemetría Viewport Tornis -->
            <div class="tornis-hud" id="tornisHud">
              <span class="status-indicator"></span>
              <span class="telemetry-item">
                <i class="bi bi-cursor-fill text-muted"></i>
                <span>CURSOR:</span> <strong id="hudCoords" class="telemetry-val">0, 0</strong>
              </span>
              <span class="telemetry-item">
                <i class="bi bi-speedometer2 text-muted"></i>
                <span>VEL:</span> <strong id="hudVelocity" class="telemetry-val">0 px/s</strong>
              </span>
              <span class="telemetry-item">
                <i class="bi bi-arrows-vertical text-muted"></i>
                <span>SCROLL:</span> <strong id="hudScroll" class="telemetry-val">0 px</strong>
              </span>
              <span class="telemetry-item">
                <i class="bi bi-display text-muted"></i>
                <span>FPS:</span> <strong id="hudFps" class="telemetry-val">60 FPS</strong>
              </span>
            </div>
          </div>

          <!-- Personaje Duotono Reactivo al Cursor -->
          <div class="col-lg-6 col-xl-6 d-flex justify-content-center">
            <div class="avatar-interactive-wrapper" id="avatarCard" title="¡Haz clic en mí o mueve el cursor!">
              <div class="avatar-speech-bubble" id="avatarSpeech">¡Hola! Soy Sebastian 👋</div>

              <svg id="avatarSvg" class="avatar-duotone-svg" viewBox="0 0 460 520" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Avatar interactivo Sebastian Giraldo">
                <defs>
                  <pattern id="stippleDotsPhp" width="6" height="6" patternUnits="userSpaceOnUse">
                    <circle cx="3" cy="3" r="1.3" fill="#38bdf8" />
                  </pattern>
                </defs>

                <!-- Camiseta con doble trazo -->
                <g id="avatar-torso" class="avatar-layer">
                  <path d="M 121 474 C 121 429, 166 399, 201 394 L 271 394 C 306 399, 351 429, 351 474" fill="none" stroke="#38bdf8" stroke-width="4.5" stroke-linejoin="round" />
                  <path d="M 125 470 C 125 425, 170 395, 205 390 L 275 390 C 310 395, 355 425, 355 470" fill="#171923" stroke="#6929f0" stroke-width="4.5" stroke-linejoin="round" />
                  <path d="M 205 390 Q 240 424 275 390" fill="none" stroke="#6929f0" stroke-width="4" stroke-linecap="round" />
                </g>

                <!-- Cabeza, rostro, orejas, ojos -->
                <g id="avatar-head" class="avatar-layer">
                  <path d="M 205 348 L 205 390 Q 240 405 275 390 L 275 348" fill="#9a5b3f" stroke="#6929f0" stroke-width="4" />
                  <path d="M 226 364 Q 240 370 254 364" stroke="#6929f0" stroke-width="3" stroke-linecap="round" fill="none" />
                  <path d="M 164 224 C 164 296, 186 350, 240 352 C 294 350, 316 296, 316 224" fill="none" stroke="#a9684b" stroke-width="3" stroke-linecap="round" />
                  <path d="M 168 220 C 168 294, 190 348, 240 350 C 290 348, 312 294, 312 220 Z" fill="#9a5b3f" />
                  <path d="M 168 220 C 168 294, 190 348, 240 350 C 290 348, 312 294, 312 220" fill="none" stroke="#633d31" stroke-width="3.5" stroke-linejoin="round" />

                  <!-- Orejas -->
                  <path d="M 166 226 C 146 226, 144 268, 166 270" fill="none" stroke="#a9684b" stroke-width="3.5" stroke-linecap="round" />
                  <path d="M 170 222 C 150 222, 148 264, 170 266" fill="#9a5b3f" stroke="#633d31" stroke-width="3" stroke-linecap="round" />
                  <path d="M 158 244 Q 152 238 160 234 Q 166 234 163 246 Q 160 250 154 246" fill="none" stroke="#633d31" stroke-width="2.5" stroke-linecap="round" />

                  <path d="M 314 226 C 334 226, 336 268, 314 270" fill="none" stroke="#a9684b" stroke-width="3.5" stroke-linecap="round" />
                  <path d="M 310 222 C 330 222, 332 264, 310 266" fill="#9a5b3f" stroke="#633d31" stroke-width="3" stroke-linecap="round" />
                  <path d="M 322 244 Q 328 238 320 234 Q 314 234 317 246 Q 320 250 326 246" fill="none" stroke="#633d31" stroke-width="2.5" stroke-linecap="round" />
                  <circle cx="319" cy="260" r="3.5" fill="#d9d5ce" stroke="#633d31" stroke-width="1.5"/>

                  <!-- Barba / Punteado -->
                  <path d="M 190 286 Q 205 281 220 287 M 260 287 Q 275 281 290 286 M 202 314 Q 240 329 278 314 M 211 336 Q 240 345 269 336" fill="none" stroke="#57372c" stroke-width="2.4" stroke-linecap="round" opacity=".72" />
                  <g fill="#57372c" opacity=".58" aria-hidden="true">
                    <circle cx="190" cy="286" r="1.2"/><circle cx="198" cy="291" r="1.1"/><circle cx="207" cy="294" r="1.2"/><circle cx="217" cy="292" r="1"/>
                    <circle cx="263" cy="292" r="1"/><circle cx="273" cy="294" r="1.2"/><circle cx="282" cy="291" r="1.1"/><circle cx="290" cy="286" r="1.2"/>
                    <circle cx="216" cy="335" r="1"/><circle cx="226" cy="339" r="1"/><circle cx="240" cy="341" r="1.1"/><circle cx="254" cy="339" r="1"/><circle cx="264" cy="335" r="1"/>
                  </g>

                  <!-- Boca -->
                  <path d="M 207 306 Q 225 300 240 305 Q 255 300 273 306 Q 258 318 240 318 Q 222 318 207 306 Z" fill="#754044" opacity=".9"/>
                  <path id="avatar-mouth" d="M 209 307 Q 240 317 271 307" stroke="#542f33" stroke-width="2.5" stroke-linecap="round" fill="none" />

                  <!-- Nariz -->
                  <path d="M 231 218 Q 229 251 218 270 Q 215 280 228 283 Q 240 288 252 281 Q 263 278 255 269 Q 246 254 245 218" fill="#a9684b" stroke="#784632" stroke-width="2.5" stroke-linejoin="round" />
                  <path d="M 222 278 Q 228 282 234 279 M 247 279 Q 253 282 258 277" fill="none" stroke="#613b30" stroke-width="2" stroke-linecap="round" />

                  <!-- Ojos y pupilas -->
                  <g id="avatar-eyes">
                    <path d="M 181 235 Q 196 224 211 235 Q 196 247 181 235 Z" fill="#f4d7bd" stroke="#30211d" stroke-width="2.5"/>
                    <path d="M 269 235 Q 284 224 299 235 Q 284 247 269 235 Z" fill="#f4d7bd" stroke="#30211d" stroke-width="2.5"/>
                    <g id="avatar-pupil-left" class="avatar-pupil">
                      <circle cx="196" cy="235" r="5.5" fill="#241714" />
                      <circle cx="194" cy="233" r="1.7" fill="#ffffff" />
                    </g>
                    <g id="avatar-pupil-right" class="avatar-pupil">
                      <circle cx="284" cy="235" r="5.5" fill="#241714" />
                      <circle cx="282" cy="233" r="1.7" fill="#ffffff" />
                    </g>
                  </g>
                </g>

                <!-- Cejas parcialmente cubiertas por el flequillo -->
                <g id="avatar-eyebrows" class="avatar-layer">
                  <path d="M 178 207 Q 194 195 218 202" fill="none" stroke="#30211d" stroke-width="7" stroke-linecap="round"/>
                  <path d="M 262 202 Q 286 195 302 207" fill="none" stroke="#30211d" stroke-width="7" stroke-linecap="round"/>
                </g>

                <!-- Rizos tipo twists con flequillo sobre la frente -->
                <g id="avatar-hair" class="avatar-layer">
                  <path d="M 168 226 C 160 201 162 163 177 137 C 192 108 216 97 242 101 C 274 97 302 116 316 146 C 328 174 322 205 312 226 Q 303 218 298 206 Q 291 220 283 225 Q 276 218 273 204 Q 264 221 255 227 Q 247 220 245 205 Q 235 224 225 229 Q 217 222 216 207 Q 205 225 195 228 Q 187 220 187 207 Q 179 222 168 226 Z" fill="#171517" stroke="#30211d" stroke-width="4" stroke-linejoin="round"/>
                  <g fill="none" stroke="#30211d" stroke-width="9" stroke-linecap="round">
                    <path d="M 179 147 C 168 160 187 170 178 184 C 170 197 188 205 181 217 Q 179 222 184 223"/>
                    <path d="M 197 127 C 184 142 204 153 194 168 C 185 182 203 191 196 207 Q 194 215 200 219"/>
                    <path d="M 216 112 C 204 128 225 140 214 155 C 204 170 224 180 214 196 Q 210 207 218 216"/>
                    <path d="M 237 107 C 224 123 246 134 235 150 C 224 166 244 177 233 193 Q 227 205 237 216"/>
                    <path d="M 259 109 C 247 125 269 137 258 153 C 247 169 267 181 256 197 Q 250 208 261 219"/>
                    <path d="M 280 120 C 269 136 291 148 280 164 C 269 180 288 191 279 207 Q 274 216 284 221"/>
                    <path d="M 299 139 C 289 153 309 166 300 181 C 292 194 307 204 300 215 Q 298 220 303 222"/>
                  </g>
                  <g fill="none" stroke="#76503b" stroke-width="2.2" stroke-linecap="round">
                    <path d="M 179 147 C 168 160 187 170 178 184 C 170 197 188 205 181 217"/>
                    <path d="M 197 127 C 184 142 204 153 194 168 C 185 182 203 191 196 207"/>
                    <path d="M 216 112 C 204 128 225 140 214 155 C 204 170 224 180 214 196"/>
                    <path d="M 237 107 C 224 123 246 134 235 150 C 224 166 244 177 233 193"/>
                    <path d="M 259 109 C 247 125 269 137 258 153 C 247 169 267 181 256 197"/>
                    <path d="M 280 120 C 269 136 291 148 280 164 C 269 180 288 191 279 207"/>
                    <path d="M 299 139 C 289 153 309 166 300 181 C 292 194 307 204 300 215"/>
                  </g>
                </g>

                <!-- Sin gafas: conserva el grupo para la animación compartida -->
                <g id="avatar-glasses" class="avatar-layer" aria-hidden="true"></g>
              </svg>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Sección Habilidades: Stack de 6 Tecnologías -->
    <section id="habilidades" class="section-padding bg-light bg-opacity-50">
      <div class="container">
        <div class="text-center mb-5">
          <span class="section-tag">Dominio Técnico</span>
          <h2 class="section-heading">Arquitectura & Tecnologías</h2>
          <p class="section-desc mx-auto">
            Stack completo: HTML5, CSS3, JavaScript, PHP 8, Bootstrap 5 y Python 3.
          </p>
        </div>

        <div class="row g-4">
          <div class="col-lg-4 col-md-6">
            <div class="skill-card">
              <div>
                <div class="skill-header">
                  <div class="skill-icon-badge" style="background: linear-gradient(135deg, #e34f26, #f06529);"><i class="bi bi-filetype-html"></i></div>
                  <span class="skill-score">96%</span>
                </div>
                <h3 class="h5 fw-bold mb-2">HTML5 Semántico</h3>
                <p class="text-muted mb-0" style="font-size: 0.9rem;">Accesibilidad WCAG AAA, Canvas procedural y gráficos vectoriales SVG multicapa.</p>
              </div>
              <div class="skill-progress-track"><div class="skill-progress-bar" style="width: 96%; background: #e34f26;"></div></div>
            </div>
          </div>
          <div class="col-lg-4 col-md-6">
            <div class="skill-card">
              <div>
                <div class="skill-header">
                  <div class="skill-icon-badge" style="background: linear-gradient(135deg, #264de4, #2965f1);"><i class="bi bi-filetype-css"></i></div>
                  <span class="skill-score">98%</span>
                </div>
                <h3 class="h5 fw-bold mb-2">CSS3 Moderno</h3>
                <p class="text-muted mb-0" style="font-size: 0.9rem;">Transformaciones 3D, variables dinámicas, brillo de neón y animaciones fluidas a 60fps.</p>
              </div>
              <div class="skill-progress-track"><div class="skill-progress-bar" style="width: 98%; background: #264de4;"></div></div>
            </div>
          </div>
          <div class="col-lg-4 col-md-6">
            <div class="skill-card">
              <div>
                <div class="skill-header">
                  <div class="skill-icon-badge" style="background: linear-gradient(135deg, #f7df1e, #d4be10); color:#1a202c;"><i class="bi bi-filetype-js"></i></div>
                  <span class="skill-score">95%</span>
                </div>
                <h3 class="h5 fw-bold mb-2">JavaScript (ES6+)</h3>
                <p class="text-muted mb-0" style="font-size: 0.9rem;">Interpolación matemática (Lerp), Web Audio API y motor reactivo Tornis para viewport.</p>
              </div>
              <div class="skill-progress-track"><div class="skill-progress-bar" style="width: 95%; background: #f7df1e;"></div></div>
            </div>
          </div>
          <div class="col-lg-4 col-md-6">
            <div class="skill-card">
              <div>
                <div class="skill-header">
                  <div class="skill-icon-badge" style="background: linear-gradient(135deg, #7952b3, #563d7c);"><i class="bi bi-bootstrap-fill"></i></div>
                  <span class="skill-score">94%</span>
                </div>
                <h3 class="h5 fw-bold mb-2">Bootstrap 5</h3>
                <p class="text-muted mb-0" style="font-size: 0.9rem;">Cuadrículas responsivas de 12 columnas, componentes modulares y cajones Offcanvas fluidos.</p>
              </div>
              <div class="skill-progress-track"><div class="skill-progress-bar" style="width: 94%; background: #7952b3;"></div></div>
            </div>
          </div>
          <div class="col-lg-4 col-md-6">
            <div class="skill-card">
              <div>
                <div class="skill-header">
                  <div class="skill-icon-badge" style="background: linear-gradient(135deg, #8892be, #4f5b93);"><i class="bi bi-filetype-php"></i></div>
                  <span class="skill-score">90%</span>
                </div>
                <h3 class="h5 fw-bold mb-2">PHP 8+</h3>
                <p class="text-muted mb-0" style="font-size: 0.9rem;">Renderizado en servidor (SSR), APIs REST, protección contra ataques CSRF y honeypot.</p>
              </div>
              <div class="skill-progress-track"><div class="skill-progress-bar" style="width: 90%; background: #4f5b93;"></div></div>
            </div>
          </div>
          <div class="col-lg-4 col-md-6">
            <div class="skill-card">
              <div>
                <div class="skill-header">
                  <div class="skill-icon-badge" style="background: linear-gradient(135deg, #3776ab, #ffd43b);"><i class="bi bi-filetype-py"></i></div>
                  <span class="skill-score">92%</span>
                </div>
                <h3 class="h5 fw-bold mb-2">Python 3</h3>
                <p class="text-muted mb-0" style="font-size: 0.9rem;">Microservicios HTTP nativos, telemetría de código con AST y visor de portafolio CLI.</p>
              </div>
              <div class="skill-progress-track"><div class="skill-progress-bar" style="width: 92%; background: #3776ab;"></div></div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Sección Proyectos con Renderizado Server-Side (SSR) en PHP -->
    <section id="proyectos" class="section-padding">
      <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4">
          <div>
            <span class="section-tag">Renderizado Server-Side (PHP 8)</span>
            <h2 class="section-heading mb-1">Proyectos Seleccionados</h2>
            <p class="text-muted mb-0">Casos de estudio cargados dinámicamente desde el backend.</p>
          </div>

          <div class="filter-pill-group mt-3 mt-md-0" role="group">
            <button class="filter-pill active" data-filter="all">Todos</button>
            <button class="filter-pill" data-filter="creative">Frontend Creativo</button>
            <button class="filter-pill" data-filter="fullstack">Full-Stack</button>
            <button class="filter-pill" data-filter="python">Python</button>
          </div>
        </div>

        <div class="row" id="projectsGrid">
          <!-- Renderizado SSR mediante PHP para máxima velocidad de indexación SEO -->
          <?php if (!empty($projectsList)): ?>
            <?php foreach ($projectsList as $p): ?>
              <div class="col-lg-4 col-md-6 mb-4">
                <article class="project-card" data-id="<?= htmlspecialchars($p['id']) ?>">
                  <div class="project-banner" style="background: <?= htmlspecialchars($p['gradient']) ?>;">
                    <i class="bi <?= htmlspecialchars($p['icon'] ?? 'bi-code-slash') ?> project-banner-icon"></i>
                  </div>
                  <div class="project-body">
                    <div class="project-meta-row">
                      <span><?= htmlspecialchars($p['year']) ?></span>
                      <span class="badge bg-dark"><?= htmlspecialchars($p['stars']) ?></span>
                    </div>
                    <h3 class="project-title"><?= htmlspecialchars($p['title']) ?></h3>
                    <p class="text-muted mb-3 flex-grow-1" style="font-size: 0.92rem;"><?= htmlspecialchars($p['description']) ?></p>
                    <div class="mb-3">
                      <?php foreach ($p['tags'] as $t): ?>
                        <span class="project-tag-chip"><?= htmlspecialchars($t) ?></span>
                      <?php endforeach; ?>
                    </div>
                    <button class="btn btn-outline-dark btn-sm rounded-pill w-100 fw-semibold btn-view-details mt-auto" data-project-id="<?= htmlspecialchars($p['id']) ?>">
                      <i class="bi bi-arrow-up-right-circle me-1"></i> Explorar Caso de Estudio
                    </button>
                  </div>
                </article>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <!-- Sección Sobre Mí -->
    <section id="sobre-mi" class="section-padding bg-light bg-opacity-50">
      <div class="container">
        <div class="row align-items-center gy-4">
          <div class="col-lg-6">
            <span class="section-tag">Filosofía & Trayectoria</span>
            <h2 class="section-heading">Construyendo la web con audacia y empatía</h2>
            <p class="lead text-dark mb-4">
              "Ayudo a personas y organizaciones a construir proyectos web ambiciosos pero accesibles. Cuanto más salvajes y experimentales, mejor."
            </p>
            <p class="text-muted">
              Uniendo microinteracciones cautivadoras a 60 cuadros por segundo con un backend ágil en PHP y microservicios en Python.
            </p>
          </div>
          <div class="col-lg-6">
            <div class="p-4 p-md-5 bg-white rounded-4 border shadow-sm">
              <h3 class="h5 fw-bold mb-3"><i class="bi bi-quote text-primary me-2"></i>Principios de Desarrollo</h3>
              <p class="text-muted mb-2">• <strong>Rendimiento 60 FPS:</strong> Aceleración por GPU y requestAnimationFrame.</p>
              <p class="text-muted mb-2">• <strong>Estética Dual:</strong> Editorial Limpio y SynthWave '84 retro-futurista.</p>
              <p class="text-muted mb-0">• <strong>Backend Híbrido:</strong> PHP 8 para SSR/APIs y Python 3 para análisis AST.</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Formulario de Contacto -->
    <section id="contacto" class="section-padding">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-lg-8">
            <div class="text-center mb-4">
              <span class="section-tag">Contacto Directo</span>
              <h2 class="section-heading">¿Tienes un proyecto en mente?</h2>
              <p class="section-desc mx-auto">Escríbeme para colaborar en proyectos creativos o arquitecturas a medida.</p>
              <div class="d-flex justify-content-center gap-3 flex-wrap mt-3">
                <a href="mailto:sebastiangiraldocorpas@gmail.com" class="contact-quick-link">
                  <i class="bi bi-envelope-fill"></i>
                  <span>sebastiangiraldocorpas@gmail.com</span>
                </a>
                <a href="https://github.com/Yamato0921" target="_blank" rel="noopener noreferrer" class="contact-quick-link">
                  <i class="bi bi-github"></i>
                  <span>github.com/Yamato0921</span>
                </a>
              </div>
            </div>

            <div class="contact-card">
              <?php if ($mensajeExito): ?>
                <div class="alert alert-success border-0 shadow-sm mb-4">
                  <i class="bi bi-check-circle-fill me-2"></i> <?= htmlspecialchars($mensajeExito) ?>
                </div>
              <?php endif; ?>

              <?php if ($mensajeError): ?>
                <div class="alert alert-danger border-0 shadow-sm mb-4">
                  <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($mensajeError) ?>
                </div>
              <?php endif; ?>

              <form id="contactForm" method="POST" action="#contacto">
                <input type="hidden" name="formulario_contacto_php" value="1">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <div style="display:none;" aria-hidden="true">
                  <input type="text" name="website_url" id="website_url" tabindex="-1" autocomplete="off">
                </div>

                <div class="row g-3">
                  <div class="col-md-6">
                    <label for="contactName" class="form-label fw-semibold">Tu Nombre <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-custom" id="contactName" name="name" required placeholder="Ej. Tu nombre">
                  </div>
                  <div class="col-md-6">
                    <label for="contactEmail" class="form-label fw-semibold">Correo Electrónico <span class="text-danger">*</span></label>
                    <input type="email" class="form-control form-control-custom" id="contactEmail" name="email" required placeholder="tu-correo@ejemplo.com">
                  </div>
                  <div class="col-12">
                    <label for="contactSubject" class="form-label fw-semibold">Asunto</label>
                    <input type="text" class="form-control form-control-custom" id="contactSubject" name="subject" placeholder="Ej. Nuevo proyecto web">
                  </div>
                  <div class="col-12">
                    <label for="contactMessage" class="form-label fw-semibold">Mensaje <span class="text-danger">*</span></label>
                    <textarea class="form-control form-control-custom" id="contactMessage" name="message" rows="5" required placeholder="Cuéntame sobre tu proyecto..."></textarea>
                  </div>
                  <div class="col-12 text-center mt-4">
                    <button type="submit" class="btn-primary-custom px-5 py-3">
                      <span>Enviar Mensaje (PHP / AJAX)</span>
                      <i class="bi bi-send-fill" aria-hidden="true"></i>
                    </button>
                  </div>
                </div>
                <div id="contactStatus"></div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>

  <!-- Cajón Offcanvas Terminal -->
  <aside class="offcanvas offcanvas-end terminal-offcanvas w-50" tabindex="-1" id="terminalOffcanvas">
    <div class="terminal-header">
      <div class="terminal-traffic-lights">
        <span class="traffic-light red" data-bs-dismiss="offcanvas" role="button"></span>
        <span class="traffic-light yellow"></span>
        <span class="traffic-light green"></span>
      </div>
      <div class="terminal-window-title">
        <span id="terminalOffcanvasLabel">sebas@portfolio: ~</span>
        <span class="terminal-status"><span></span> en línea</span>
      </div>
      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="terminal-body" id="terminalOutput"></div>
    <div class="terminal-input-row">
      <span class="terminal-prompt">$</span>
      <input type="text" id="terminalInput" class="terminal-input" placeholder="Escribe 'ayuda' y presiona Enter..." autocomplete="off" spellcheck="false">
    </div>
  </aside>

  <!-- Modal Detalles de Proyecto -->
  <div class="modal fade" id="projectDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
        <div class="modal-header border-bottom bg-light">
          <div>
            <h3 class="modal-title h5 fw-bold" id="modalProjectTitle">Detalles del Proyecto</h3>
            <p class="text-muted small mb-0" id="modalProjectSubtitle"></p>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
          <p id="modalProjectDescription" class="lead fs-6 mb-4 text-secondary"></p>
          <h4 class="h6 fw-bold text-uppercase text-muted mb-2">Métricas Clave:</h4>
          <div class="row g-2 mb-4" id="modalProjectMetrics"></div>
          <h4 class="h6 fw-bold text-uppercase text-muted mb-2">Características:</h4>
          <ul class="list-unstyled mb-4" id="modalProjectFeatures"></ul>
          <h4 class="h6 fw-bold text-uppercase text-muted mb-2">Tecnologías:</h4>
          <div id="modalProjectTags" class="d-flex flex-wrap gap-1"></div>
        </div>
        <div class="modal-footer border-top bg-light">
          <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Pie de página con año dinámico PHP -->
  <footer class="site-footer">
    <div class="container text-center">
      <p class="mb-1 fw-bold text-dark">&copy; <?= date('Y') ?> Sebastian Giraldo | Desarrollador Creativo Independiente</p>
      <p class="mb-2 small text-muted">
        Diseñado y desarrollado por Sebastian Giraldo en HTML5, CSS3, JavaScript, PHP 8, Bootstrap 5 y Python 3.
      </p>
      <div class="d-inline-flex gap-3">
        <a href="https://github.com/Yamato0921" target="_blank" rel="noopener noreferrer" class="text-muted" title="GitHub">
          <i class="bi bi-github fs-5"></i>
        </a>
        <a href="mailto:sebastiangiraldocorpas@gmail.com" class="text-muted" title="Correo Electrónico">
          <i class="bi bi-envelope-fill fs-5"></i>
        </a>
      </div>
    </div>
  </footer>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="js/audio.js"></script>
  <script src="js/viewport-tracker.js"></script>
  <script src="js/avatar.js"></script>
  <script src="js/terminal.js?v=20261023-1"></script>
  <script src="js/projects.js"></script>
  <script src="js/app.js"></script>
</body>
</html>
