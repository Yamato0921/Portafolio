/**
 * ==============================================================================
 * AVATAR VECTORIAL INTERACTIVO - SELLO CREATIVO SEBASTIAN GIRALDO
 * ==============================================================================
 * Controla el avatar ilustrado por capas SVG que sigue la posición del cursor
 * en tiempo real con interpolación suave (LERP / Damping) a 60 cuadros por segundo.
 *
 * Características interactivas:
 *  - Parallax multicapa en 3D (cabeza, gafas, pupilas, cejas, hombros).
 *  - Efecto de mirada realista: Las pupilas se orientan hacia el cursor dentro de un límite elíptico.
 *  - Guiño y reacción al hacer clic con reproducción de síntesis de sonido y bocadillo de diálogo.
 *  - Retorno suave al centro cuando el cursor sale de la ventana del navegador.
 * ==============================================================================
 */

class InteractiveAvatar {
  constructor(cardSelector = '#avatarCard', svgSelector = '#avatarSvg') {
    this.card = document.querySelector(cardSelector);
    this.svg = document.querySelector(svgSelector);
    if (!this.card || !this.svg) return;

    // Referencias a los elementos del SVG por capas
    this.layerHead = this.svg.querySelector('#avatar-head');
    this.layerHair = this.svg.querySelector('#avatar-hair');
    this.layerGlasses = this.svg.querySelector('#avatar-glasses');
    this.layerPupilL = this.svg.querySelector('#avatar-pupil-left');
    this.layerPupilR = this.svg.querySelector('#avatar-pupil-right');
    this.layerBrows = this.svg.querySelector('#avatar-eyebrows');
    this.layerMouth = this.svg.querySelector('#avatar-mouth');
    this.speechBubble = document.querySelector('#avatarSpeech');

    // Coordenadas normalizadas del cursor (-1.0 a +1.0 respecto al centro del avatar)
    this.targetX = 0;
    this.targetY = 0;
    this.currentX = 0;
    this.currentY = 0;

    // Estado del guiño
    this.isWinking = false;

    // Frases del bocadillo en español
    this.greetings = [
      "¡Hola! Soy Sebastian 👋",
      "Creatividad + Código 🚀",
      "¡Bienvenido a mi espacio!",
      "¿Probaste el modo SynthWave?",
      "Construyendo la web del futuro ✨",
      "Presiona '/' para la terminal"
    ];
    this.greetingIndex = 0;

    this.bindEvents();
    this.startRenderLoop();
  }

  /**
   * Vincula los oyentes de eventos del ratón y táctiles.
   */
  bindEvents() {
    // Seguimiento del movimiento del cursor en toda la ventana
    window.addEventListener('mousemove', (e) => {
      this.handleMouseMove(e.clientX, e.clientY);
    }, { passive: true });

    // Cuando el cursor sale de la pantalla, el avatar vuelve a la posición de reposo neutral
    document.addEventListener('mouseleave', () => {
      this.targetX = 0;
      this.targetY = 0;
    });

    // Soporte para dispositivos móviles con eventos táctiles
    window.addEventListener('touchmove', (e) => {
      if (e.touches.length > 0) {
        this.handleMouseMove(e.touches[0].clientX, e.touches[0].clientY);
      }
    }, { passive: true });

    // Interacción al hacer clic en el avatar (Guiño + Diálogo + Sonido)
    if (this.card) {
      this.card.addEventListener('click', () => {
        this.triggerWinkAndSpeak();
      });
    }
  }

  /**
   * Calcula el vector normalizado entre el centro del avatar y las coordenadas del cursor.
   * @param {number} clientX Posición horizontal del cursor
   * @param {number} clientY Posición vertical del cursor
   */
  handleMouseMove(clientX, clientY) {
    const rect = this.card.getBoundingClientRect();
    const centerX = rect.left + rect.width / 2;
    const centerY = rect.top + rect.height / 2;

    // Distancia delta en píxeles
    const dx = clientX - centerX;
    const dy = clientY - centerY;

    // Radio de influencia máxima (ajustado para respuesta natural en toda la pantalla)
    const maxRadius = Math.max(window.innerWidth, window.innerHeight) * 0.75;

    // Mapeo normalizado con límite de -1 a 1
    this.targetX = Math.max(-1, Math.min(1, dx / (maxRadius * 0.4)));
    this.targetY = Math.max(-1, Math.min(1, dy / (maxRadius * 0.4)));
  }

  /**
   * Bucle de animación continuo a 60 FPS con interpolación lineal (Lerp).
   */
  startRenderLoop() {
    const lerpFactor = 0.085; // Factor de amortiguación para suavidad cinematográfica

    const update = () => {
      // Interpolación lineal hacia el objetivo del cursor
      this.currentX += (this.targetX - this.currentX) * lerpFactor;
      this.currentY += (this.targetY - this.currentY) * lerpFactor;

      // Mantener alineadas las piezas del rostro durante el parallax.
      const headX = this.currentX * 8;
      const headY = this.currentY * 6;
      const headRot = this.currentX * 2;
      const headTransform = `translate(${headX}px, ${headY}px) rotate(${headRot}deg)`;
      [this.layerHead, this.layerHair, this.layerGlasses, this.layerBrows]
        .filter(Boolean)
        .forEach((layer) => {
          layer.style.transform = headTransform;
        });

      // 3. Seguimiento Ocular de las Pupilas (Límites elípticos calculados)
      const pupilRangeX = 7.5;
      const pupilRangeY = 6.0;
      const pupilX = this.currentX * pupilRangeX;
      const pupilY = this.currentY * pupilRangeY;

      if (!this.isWinking) {
        if (this.layerPupilL) {
          this.layerPupilL.style.transform = `translate(${pupilX}px, ${pupilY}px)`;
        }
        if (this.layerPupilR) {
          this.layerPupilR.style.transform = `translate(${pupilX}px, ${pupilY}px)`;
        }
      } else if (this.layerPupilR) {
        this.layerPupilR.style.transform = `translate(${pupilX}px, ${pupilY}px) scaleY(0.12)`;
      }

      requestAnimationFrame(update);
    };

    requestAnimationFrame(update);
  }

  /**
   * Desencadena una reacción animada: guiño, sonido arcade y diálogo en español.
   */
  triggerWinkAndSpeak() {
    if (this.isWinking) return;
    this.isWinking = true;

    // Reproducir efecto de sonido con el sintetizador
    if (window.audioSynth) {
      window.audioSynth.playSynthBeep(680, 0.12);
    }

    // Mostrar bocadillo de diálogo con frase rotativa en español
    if (this.speechBubble) {
      const msg = this.greetings[this.greetingIndex % this.greetings.length];
      this.greetingIndex++;
      this.speechBubble.textContent = msg;
      this.speechBubble.classList.add('active');

      setTimeout(() => {
        this.speechBubble.classList.remove('active');
      }, 2400);
    }

    // Restaurar estado neutral después de 400ms
    setTimeout(() => {
      this.isWinking = false;
    }, 450);
  }
}

// Inicialización cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
  window.interactiveAvatar = new InteractiveAvatar();
});
