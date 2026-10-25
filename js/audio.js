/**
 * ==============================================================================
 * SINTETIZADOR DE AUDIO WEB (WEB AUDIO API) - PORTAFOLIO INTERACTIVO
 * ==============================================================================
 * Genera efectos de sonido analógicos y retro en tiempo real mediante código puro.
 * No requiere descargar archivos de audio externos (cero latencia y carga instantánea).
 * Incluye gestión de estado de silencio (mute) con persistencia en LocalStorage.
 * ==============================================================================
 */

class SoundSynthesizer {
  constructor() {
    // Estado del audio: activo o silenciado (por defecto desactivado para respetar al usuario)
    this.enabled = localStorage.getItem('portfolio_audio_enabled') === 'true';
    this.audioCtx = null;
  }

  /**
   * Inicializa el AudioContext bajo demanda (requerido por las políticas del navegador).
   */
  initContext() {
    if (!this.audioCtx) {
      const AudioContextClass = window.AudioContext || window.webkitAudioContext;
      if (AudioContextClass) {
        this.audioCtx = new AudioContextClass();
      }
    }
    // Si el contexto fue suspendido por el navegador, lo reanudamos
    if (this.audioCtx && this.audioCtx.state === 'suspended') {
      this.audioCtx.resume();
    }
  }

  /**
   * Alterna el estado de silencio global.
   * @returns {boolean} Nuevo estado activo
   */
  toggle() {
    this.enabled = !this.enabled;
    localStorage.setItem('portfolio_audio_enabled', this.enabled);
    if (this.enabled) {
      this.initContext();
      this.playSynthBeep(520, 0.08); // Sonido confirmatorio
    }
    return this.enabled;
  }

  /**
   * Reproduce un clic táctil suave tipo teclado mecánico.
   */
  playClick() {
    if (!this.enabled) return;
    this.initContext();
    if (!this.audioCtx) return;

    try {
      const osc = this.audioCtx.createOscillator();
      const gain = this.audioCtx.createGain();

      osc.type = 'triangle'; // Onda triangular suave
      osc.frequency.setValueAtTime(320, this.audioCtx.currentTime);
      osc.frequency.exponentialRampToValueAtTime(80, this.audioCtx.currentTime + 0.04);

      gain.gain.setValueAtTime(0.12, this.audioCtx.currentTime);
      gain.gain.exponentialRampToValueAtTime(0.001, this.audioCtx.currentTime + 0.04);

      osc.connect(gain);
      gain.connect(this.audioCtx.destination);

      osc.start();
      osc.stop(this.audioCtx.currentTime + 0.05);
    } catch (e) {
      // Ignorar errores menores de audio
    }
  }

  /**
   * Reproduce un zumbido sutil de alta frecuencia al pasar el cursor sobre botones clave.
   */
  playHover() {
    if (!this.enabled) return;
    this.initContext();
    if (!this.audioCtx) return;

    try {
      const osc = this.audioCtx.createOscillator();
      const gain = this.audioCtx.createGain();

      osc.type = 'sine';
      osc.frequency.setValueAtTime(440, this.audioCtx.currentTime);
      osc.frequency.linearRampToValueAtTime(480, this.audioCtx.currentTime + 0.03);

      gain.gain.setValueAtTime(0.04, this.audioCtx.currentTime);
      gain.gain.exponentialRampToValueAtTime(0.001, this.audioCtx.currentTime + 0.03);

      osc.connect(gain);
      gain.connect(this.audioCtx.destination);

      osc.start();
      osc.stop(this.audioCtx.currentTime + 0.035);
    } catch (e) {}
  }

  /**
   * Sonido de pitido arcade retro para interacción con el avatar interactivo.
   * @param {number} freq Frecuencia base en Hz
   * @param {number} duration Duración en segundos
   */
  playSynthBeep(freq = 600, duration = 0.1) {
    if (!this.enabled) return;
    this.initContext();
    if (!this.audioCtx) return;

    try {
      const osc = this.audioCtx.createOscillator();
      const gain = this.audioCtx.createGain();

      osc.type = 'square'; // Sonido 8-bits retro
      osc.frequency.setValueAtTime(freq, this.audioCtx.currentTime);
      osc.frequency.exponentialRampToValueAtTime(freq * 1.5, this.audioCtx.currentTime + duration);

      gain.gain.setValueAtTime(0.08, this.audioCtx.currentTime);
      gain.gain.exponentialRampToValueAtTime(0.001, this.audioCtx.currentTime + duration);

      osc.connect(gain);
      gain.connect(this.audioCtx.destination);

      osc.start();
      osc.stop(this.audioCtx.currentTime + duration + 0.01);
    } catch (e) {}
  }

  /**
   * Sonido de barrido futurista al cambiar de tema (Modo SynthWave '84).
   */
  playThemeToggle() {
    if (!this.enabled) return;
    this.initContext();
    if (!this.audioCtx) return;

    try {
      const osc = this.audioCtx.createOscillator();
      const gain = this.audioCtx.createGain();

      osc.type = 'sawtooth';
      osc.frequency.setValueAtTime(220, this.audioCtx.currentTime);
      osc.frequency.exponentialRampToValueAtTime(880, this.audioCtx.currentTime + 0.18);

      gain.gain.setValueAtTime(0.09, this.audioCtx.currentTime);
      gain.gain.exponentialRampToValueAtTime(0.001, this.audioCtx.currentTime + 0.2);

      osc.connect(gain);
      gain.connect(this.audioCtx.destination);

      osc.start();
      osc.stop(this.audioCtx.currentTime + 0.22);
    } catch (e) {}
  }

  /**
   * Acorde armónico de confirmación al enviar el formulario con éxito.
   */
  playSuccessChime() {
    if (!this.enabled) return;
    this.initContext();
    if (!this.audioCtx) return;

    const chords = [523.25, 659.25, 783.99]; // Acorde C Mayor (Do, Mi, Sol)
    chords.forEach((noteFreq, idx) => {
      try {
        const osc = this.audioCtx.createOscillator();
        const gain = this.audioCtx.createGain();

        osc.type = 'sine';
        osc.frequency.setValueAtTime(noteFreq, this.audioCtx.currentTime + (idx * 0.08));

        gain.gain.setValueAtTime(0.08, this.audioCtx.currentTime + (idx * 0.08));
        gain.gain.exponentialRampToValueAtTime(0.001, this.audioCtx.currentTime + 0.45);

        osc.connect(gain);
        gain.connect(this.audioCtx.destination);

        osc.start(this.audioCtx.currentTime + (idx * 0.08));
        osc.stop(this.audioCtx.currentTime + 0.5);
      } catch (e) {}
    });
  }
}

// Instancia global disponible en toda la aplicación
window.audioSynth = new SoundSynthesizer();
