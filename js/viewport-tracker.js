/**
 * ==============================================================================
 * MOTOR DE TELEMETRÍA DEL VIEWPORT (INSPIRADO EN TORNIS)
 * ==============================================================================
 * Tornis es una librería de telemetría del viewport. Este módulo aplica un enfoque
 * similar para registrar el cursor, el scroll, las dimensiones y los FPS mediante
 * un único bucle requestAnimationFrame.
 *
 * Este script implementa dicha arquitectura reactiva para actualizar el HUD de
 * telemetría en tiempo real y despachar eventos personalizados al DOM.
 * ==============================================================================
 */

class TornisViewportEngine {
  constructor(hudSelector = '#tornisHud') {
    this.hud = document.querySelector(hudSelector);

    // Estado interno del viewport
    this.state = {
      mouse: { x: 0, y: 0, prevX: 0, prevY: 0, velocity: 0 },
      scroll: { y: window.scrollY || 0, prevY: window.scrollY || 0, velocity: 0 },
      size: { width: window.innerWidth, height: window.innerHeight },
      fps: 60
    };

    // Control de tiempo para cálculo de velocidad y FPS
    this.lastTime = performance.now();
    this.framesCount = 0;
    this.fpsTimer = performance.now();

    // Elementos del HUD para actualización ultra-rápida sin reconstruir el DOM
    this.elCoords = document.querySelector('#hudCoords');
    this.elVelocity = document.querySelector('#hudVelocity');
    this.elScroll = document.querySelector('#hudScroll');
    this.elFps = document.querySelector('#hudFps');

    this.bindEvents();
    this.startLoop();
  }

  /**
   * Registra los oyentes nativos mínimos para evitar bloqueos del hilo principal.
   */
  bindEvents() {
    window.addEventListener('mousemove', (e) => {
      this.state.mouse.x = e.clientX;
      this.state.mouse.y = e.clientY;
    }, { passive: true });

    window.addEventListener('scroll', () => {
      this.state.scroll.y = window.scrollY || window.pageYOffset;
    }, { passive: true });

    window.addEventListener('resize', () => {
      this.state.size.width = window.innerWidth;
      this.state.size.height = window.innerHeight;
    }, { passive: true });
  }

  /**
   * Bucle continuo a 60 FPS para cálculo de deltas y renderizado de telemetría.
   */
  startLoop() {
    const tick = (now) => {
      const dt = Math.max(1, now - this.lastTime);
      this.lastTime = now;

      // 1. Cálculo de velocidad del cursor (distancia euclidiana dividida por tiempo)
      const dx = this.state.mouse.x - this.state.mouse.prevX;
      const dy = this.state.mouse.y - this.state.mouse.prevY;
      const distance = Math.hypot(dx, dy);
      const instantVelocity = Math.round((distance / dt) * 1000); // Píxeles por segundo

      // Filtro de media móvil para suavizar la lectura de velocidad
      this.state.mouse.velocity = Math.round((this.state.mouse.velocity * 0.8) + (instantVelocity * 0.2));
      this.state.mouse.prevX = this.state.mouse.x;
      this.state.mouse.prevY = this.state.mouse.y;

      // 2. Cálculo de velocidad de scroll
      const dScroll = Math.abs(this.state.scroll.y - this.state.scroll.prevY);
      this.state.scroll.velocity = Math.round((dScroll / dt) * 1000);
      this.state.scroll.prevY = this.state.scroll.y;

      // 3. Cálculo de FPS reales del navegador
      this.framesCount++;
      if (now - this.fpsTimer >= 1000) {
        this.state.fps = this.framesCount;
        this.framesCount = 0;
        this.fpsTimer = now;
      }

      // 4. Actualización en vivo del HUD si los elementos existen
      this.updateHud();

      requestAnimationFrame(tick);
    };

    requestAnimationFrame(tick);
  }

  /**
   * Escribe las métricas en los nodos de texto del DOM con alta eficiencia.
   */
  updateHud() {
    if (this.elCoords) {
      this.elCoords.textContent = `${this.state.mouse.x}, ${this.state.mouse.y}`;
    }
    if (this.elVelocity) {
      this.elVelocity.textContent = `${this.state.mouse.velocity} px/s`;
    }
    if (this.elScroll) {
      this.elScroll.textContent = `${Math.round(this.state.scroll.y)} px`;
    }
    if (this.elFps) {
      this.elFps.textContent = `${this.state.fps} FPS`;
    }
  }

  /**
   * Obtiene una copia del estado actual del viewport para otros módulos.
   */
  getState() {
    return { ...this.state };
  }
}

// Inicialización automática
document.addEventListener('DOMContentLoaded', () => {
  window.tornisEngine = new TornisViewportEngine();
});
