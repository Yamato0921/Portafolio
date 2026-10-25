/**
 * ==============================================================================
 * TERMINAL INTERACTIVA DE DESARROLLADOR - PORTAFOLIO INTERACTIVO
 * ==============================================================================
 * Proporciona una interfaz de línea de comandos integrada en un cajón Offcanvas.
 * Permite al visitante interactuar mediante comandos de texto en español (e inglés),
 * consultar habilidades, inspeccionar proyectos, ejecutar telemetría en vivo
 * mediante el backend en Python y cambiar la apariencia visual de la web.
 * ==============================================================================
 */

class InteractiveTerminal {
  constructor() {
    this.container = document.querySelector('#terminalOutput');
    this.input = document.querySelector('#terminalInput');
    this.history = [];
    this.historyIndex = -1;

    if (!this.container || !this.input) return;

    this.bindEvents();
    this.printWelcome();
  }

  /**
   * Vincula los eventos del teclado (Enter para ejecutar, flechas arriba/abajo para historial).
   */
  bindEvents() {
    this.input.addEventListener('keydown', (e) => {
      // Reproducir sonido táctil al teclear
      if (window.audioSynth && e.key.length === 1) {
        window.audioSynth.playClick();
      }

      if (e.key === 'Enter') {
        const cmd = this.input.value.trim();
        if (cmd) {
          this.history.push(cmd);
          this.historyIndex = this.history.length;
          this.executeCommand(cmd);
          this.input.value = '';
        }
      } else if (e.key === 'ArrowUp') {
        // Navegar hacia atrás en el historial de comandos
        if (this.historyIndex > 0) {
          this.historyIndex--;
          this.input.value = this.history[this.historyIndex];
        }
        e.preventDefault();
      } else if (e.key === 'ArrowDown') {
        // Navegar hacia adelante en el historial
        if (this.historyIndex < this.history.length - 1) {
          this.historyIndex++;
          this.input.value = this.history[this.historyIndex];
        } else {
          this.historyIndex = this.history.length;
          this.input.value = '';
        }
        e.preventDefault();
      }
    });

    // Atajo global de teclado: presionar '/' para abrir la terminal
    window.addEventListener('keydown', (e) => {
      if (e.key === '/' && document.activeElement !== this.input && !['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName)) {
        e.preventDefault();
        const offcanvasEl = document.querySelector('#terminalOffcanvas');
        if (offcanvasEl && window.bootstrap) {
          const bsOffcanvas = bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl);
          bsOffcanvas.show();
          setTimeout(() => this.input.focus(), 300);
        }
      }
    });
  }

  /**
   * Imprime el mensaje de bienvenida y estado del sistema.
   */
  printWelcome() {
    const welcome = `
SEBAS@PORTAFOLIO  /  TERMINAL INTERACTIVA
HTML5  •  CSS3  •  JavaScript  •  PHP  •  Python

Consulta proyectos, habilidades y comandos desde esta consola.
Escribe "ayuda" para ver las opciones disponibles.
Atajo: [/] abre la terminal  •  [Esc] la cierra
`;
    this.appendOutput(welcome, 'terminal-welcome');
  }

  /**
   * Agrega texto a la consola y asegura el scroll automático al final.
   * @param {string} text Contenido a mostrar
   * @param {string} className Clase de color opcional
   */
  appendOutput(text, className = '') {
    const pre = document.createElement('div');
    pre.className = `terminal-line ${className}`;
    pre.textContent = text;
    this.container.appendChild(pre);
    this.container.scrollTop = this.container.scrollHeight;
  }

  /**
   * Procesa y ejecuta el comando ingresado.
   * @param {string} rawCmd Comando en bruto
   */
  async executeCommand(rawCmd) {
    // Mostrar el comando ejecutado con el prefijo del prompt
    this.appendOutput(`sebas@portfolio:~$ ${rawCmd}`, 'terminal-command');

    const clean = rawCmd.trim().toLowerCase();
    const parts = clean.split(' ');
    const mainCmd = parts[0];
    const arg = parts[1] || '';

    // Manejo de comandos nativos en el cliente
    switch (mainCmd) {
      case 'ayuda':
      case 'help':
        this.appendOutput(`
Comandos Disponibles:
  ayuda / help          : Muestra este menú de instrucciones
  quiensoy / whoami     : Perfil profesional y filosofía de desarrollo
  habilidades / skills  : Detalle del stack (HTML5, CSS3, JS, PHP, Bootstrap, Python)
  proyectos / projects  : Resumen de desarrollos destacados
  telemetria / stats    : Consulta la telemetría en vivo o la instantánea del proyecto
  tema [synthwave|limpio] : Alterna el tema visual de la página
  audio [on|off]        : Activa o desactiva la síntesis de sonido analógico
  contacto / contact    : Dirige al formulario de contacto
  limpiar / clear       : Limpia la pantalla de la terminal
`);
        break;

      case 'quiensoy':
      case 'whoami':
        this.appendOutput(`
[Perfil Profesional]
Desarrollador Creativo Independiente y Arquitecto Frontend.
La filosofía de Sebastian Giraldo:
"Ayudo a personas y organizaciones a construir proyectos web ambiciosos
pero completamente accesibles: cuanto más audaces y salvajes, mejor."
Especialista en experiencias web cinemáticas, microinteracciones y rendimiento.
`);
        break;

      case 'habilidades':
      case 'skills':
        this.appendOutput(`
[Matriz Tecnológica del Portafolio]
  • HTML5      : 96% | Semántica estricta, Layered SVGs, Canvas, ARIA Accesible
  • CSS3       : 98% | 3D Transforms, Animaciones fluidas, SynthWave '84 Glow
  • JavaScript : 95% | ES6+, Web Audio API, Motor de Viewport reactivo (Tornis)
  • Bootstrap  : 94% | Bootstrap 5, Grid adaptativo, Offcanvas, Modales
  • PHP        : 90% | PHP 8+, APIs REST seguras, Sanitización y Tokens CSRF
  • Python     : 92% | Microservicios HTTP, Análisis de código con módulo AST
`);
        break;

      case 'proyectos':
      case 'projects':
        this.appendOutput(`
[Proyectos Destacados]
  1. SynthWave '84 IDE Playground (HTML5/CSS3/JS/Bootstrap)
  2. Tornis Sensor & Viewport Engine (JS/Canvas/CSS3)
  3. OmniStack PHP & Python Backend Hub (PHP/Python/JS)
  4. Rekishi History & Transition Controller (HTML5/JS)
  5. PyTelemetry Code Matrix (Python/Bootstrap)
Usa el comando 'tema synthwave' para encender el modo retro-futurista.
`);
        break;

      case 'telemetria':
      case 'stats':
        this.appendOutput("Consultando telemetría del proyecto...", "text-warning");
        try {
          let data;
          const runtime = document.querySelector('meta[name="portfolio-runtime"]')?.content;
          if (runtime !== 'static') {
            const statsEndpoints = runtime === 'php'
              ? ['api/stats.php', 'api/py/stats']
              : ['api/py/stats', 'api/stats.php'];
            for (const endpoint of statsEndpoints) {
              try {
                const liveUrl = new URL(endpoint, document.baseURI);
                const liveResponse = await fetch(liveUrl);
                if (!liveResponse.ok) continue;
                const liveData = await liveResponse.json();
                if (liveData.status === 'success') {
                  data = liveData;
                  break;
                }
              } catch {
                continue;
              }
            }
          }

          if (!data) {
            const snapshotUrl = new URL('assets/data/portfolio-stats.json', document.baseURI);
            const snapshotResponse = await fetch(snapshotUrl);
            if (!snapshotResponse.ok) throw new Error(`HTTP ${snapshotResponse.status}`);
            data = await snapshotResponse.json();
          }

          let distStr = "";
          for (const [k, v] of Object.entries(data.distribution || {})) {
            distStr += `  • ${k.padEnd(12)}: ${v.lines} líneas (${v.percentage}%)\n`;
          }
          if (data.source === 'build') {
            this.appendOutput(`
[PyTelemetry - Instantánea de publicación]
  Líneas totales del proyecto: ${data.totalLines}
  Generada: ${data.timestamp}
  Runtime de análisis: Python ${data.runtime?.pythonVersion || 'no disponible'}
Distribución tecnológica:
${distStr}`);
          } else {
            const engine = data.runtime?.engine ||
              (data.runtime?.pythonVersion ? `Python ${data.runtime.pythonVersion}` : 'Backend');
            this.appendOutput(`
[Telemetría - Análisis en vivo]
  Líneas totales del proyecto: ${data.totalLines}
  Motor de análisis: ${engine}
${data.uptimeSeconds == null ? '' : `  Tiempo activo del backend: ${data.uptimeSeconds} segundos\n`}  Generada: ${data.timestamp || 'fecha no disponible'}
Distribución tecnológica:
${distStr}`);
          }
        } catch (err) {
          this.appendOutput(
            "No fue posible cargar la telemetría. Inicia backend_py/server.py o vuelve a publicar el sitio para generar la instantánea estática.",
            "text-danger"
          );
        }
        break;

      case 'tema':
      case 'theme':
        if (arg === 'synthwave') {
          document.body.setAttribute('data-theme', 'synthwave');
          localStorage.setItem('portfolio_theme', 'synthwave');
          if (window.audioSynth) window.audioSynth.playThemeToggle();
          this.appendOutput("Tema cambiado a [SYNTHWAVE '84]. ¡Brillo neón activado!", "text-info");
        } else if (arg === 'limpio' || arg === 'clean') {
          document.body.removeAttribute('data-theme');
          localStorage.setItem('portfolio_theme', 'clean');
          if (window.audioSynth) window.audioSynth.playThemeToggle();
          this.appendOutput("Tema cambiado a [EDITORIAL LIMPIO] (Modo clásico Sebastian Giraldo).", "text-info");
        } else {
          this.appendOutput("Uso: tema <synthwave | limpio>", "text-danger");
        }
        break;

      case 'audio':
        if (arg === 'on') {
          if (window.audioSynth) {
            window.audioSynth.enabled = true;
            window.audioSynth.initContext();
            window.audioSynth.playSynthBeep(520, 0.08);
            localStorage.setItem('portfolio_audio_enabled', 'true');
          }
          this.appendOutput("Audio sintetizado activado [ON].", "text-success");
        } else if (arg === 'off') {
          if (window.audioSynth) {
            window.audioSynth.enabled = false;
            localStorage.setItem('portfolio_audio_enabled', 'false');
          }
          this.appendOutput("Audio sintetizado silenciado [OFF].", "text-warning");
        } else {
          this.appendOutput("Uso: audio <on | off>", "text-danger");
        }
        break;

      case 'contacto':
      case 'contact':
        const contactSection = document.querySelector('#contacto');
        if (contactSection) {
          contactSection.scrollIntoView({ behavior: 'smooth' });
        } else {
          const contactButton = document.querySelector('#openContactModalBtn');
          if (contactButton) contactButton.click();
        }
        this.appendOutput(`
Contacto de Sebastian Giraldo
  Correo : sebastiangiraldocorpas@gmail.com
  GitHub : https://github.com/Yamato0921`, "text-success");
        break;

      case 'limpiar':
      case 'clear':
        this.container.innerHTML = '';
        break;

      default:
        this.appendOutput(`Comando no reconocido: '${rawCmd}'. Escribe 'ayuda' para ver las opciones disponibles.`, 'text-danger');
        break;
    }
  }
}

// Inicialización de la terminal
document.addEventListener('DOMContentLoaded', () => {
  window.interactiveTerminal = new InteractiveTerminal();
});
