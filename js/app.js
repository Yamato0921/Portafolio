/**
 * ==============================================================================
 * APLICACIÓN PRINCIPAL (APP.JS) - PORTAFOLIO INTERACTIVO
 * ==============================================================================
 * Orquestador principal que coordina:
 *  - Conmutador de temas visuales (Modo Editorial Limpio de Sebastian Giraldo vs SynthWave '84).
 *  - Activación opcional de scanlines CRT de monitor retro.
 *  - Control de audio analógico sintetizado (Mute / Unmute).
 *  - Envío asíncrono (AJAX / Fetch API) del formulario de contacto hacia PHP/Python.
 *  - Atajos de teclado y navegación fluida.
 * ==============================================================================
 */

class PortfolioApp {
  constructor() {
    this.themeToggleBtn = document.querySelector('#themeToggleBtn');
    this.audioToggleBtn = document.querySelector('#audioToggleBtn');
    this.crtToggleBtn = document.querySelector('#crtToggleBtn');
    this.contactForm = document.querySelector('#contactForm');
    this.contactStatus = document.querySelector('#contactStatus');

    this.init();
  }

  /**
   * Inicializa las configuraciones guardadas y los escuchadores de eventos.
   */
  init() {
    this.restoreTheme();
    this.restoreAudio();
    this.bindEvents();
    this.bindContactForm();
    this.initScrollSpy();
  }

  /**
   * Restaura el tema guardado en el almacenamiento local (LocalStorage).
   */
  restoreTheme() {
    const savedTheme = localStorage.getItem('portfolio_theme');
    if (savedTheme === 'synthwave') {
      document.body.setAttribute('data-theme', 'synthwave');
      this.updateThemeButton(true);
    } else {
      document.body.removeAttribute('data-theme');
      this.updateThemeButton(false);
    }
  }

  /**
   * Restaura el estado del sintetizador de sonido.
   */
  restoreAudio() {
    const isAudioOn = localStorage.getItem('portfolio_audio_enabled') === 'true';
    if (window.audioSynth) {
      window.audioSynth.enabled = isAudioOn;
    }
    this.updateAudioButton(isAudioOn);
  }

  /**
   * Asocia eventos a botones de control global.
   */
  bindEvents() {
    // 1. Botón de cambio de tema (Editorial vs SynthWave '84)
    if (this.themeToggleBtn) {
      this.themeToggleBtn.addEventListener('click', () => {
        const isSynthwave = document.body.getAttribute('data-theme') === 'synthwave';
        if (isSynthwave) {
          // Cambiar a modo Editorial Limpio
          document.body.removeAttribute('data-theme');
          localStorage.setItem('portfolio_theme', 'clean');
          this.updateThemeButton(false);
        } else {
          // Cambiar a modo SynthWave '84
          document.body.setAttribute('data-theme', 'synthwave');
          localStorage.setItem('portfolio_theme', 'synthwave');
          this.updateThemeButton(true);
        }

        // Reproducir sonido especial de cambio de tema
        if (window.audioSynth) {
          window.audioSynth.playThemeToggle();
        }
      });
    }

    // 2. Botón de alternancia de Audio
    if (this.audioToggleBtn) {
      this.audioToggleBtn.addEventListener('click', () => {
        if (window.audioSynth) {
          const state = window.audioSynth.toggle();
          this.updateAudioButton(state);
        }
      });
    }

    // 3. Botón de alternancia de líneas CRT
    if (this.crtToggleBtn) {
      this.crtToggleBtn.addEventListener('click', () => {
        document.body.classList.toggle('crt-active');
        if (window.audioSynth) window.audioSynth.playClick();
      });
    }

    // 4. Sonidos de hover táctil en enlaces y botones principales
    document.querySelectorAll('.btn, .nav-link-custom, .filter-pill').forEach((el) => {
      el.addEventListener('mouseenter', () => {
        if (window.audioSynth) window.audioSynth.playHover();
      });
    });
  }

  /**
   * Actualiza el texto, ícono y apariencia del botón de tema.
   * Alterna la clase CSS .synth-active y el atributo aria-pressed.
   * @param {boolean} isSynthwave - true = modo SynthWave '84 activo
   */
  updateThemeButton(isSynthwave) {
    if (!this.themeToggleBtn) return;

    if (isSynthwave) {
      /* Modo SynthWave '84: ícono de sol, clase activa y etiqueta de retorno */
      this.themeToggleBtn.innerHTML = `
        <i class="bi bi-sun-fill"></i>
        <span class="btn-pill-label">Modo Editorial</span>
      `;
      this.themeToggleBtn.classList.add('synth-active');
      this.themeToggleBtn.setAttribute('aria-pressed', 'true');
    } else {
      /* Modo Editorial Limpio: ícono de luna y etiqueta de SynthWave */
      this.themeToggleBtn.innerHTML = `
        <i class="bi bi-moon-stars-fill"></i>
        <span class="btn-pill-label">SynthWave '84</span>
      `;
      this.themeToggleBtn.classList.remove('synth-active');
      this.themeToggleBtn.setAttribute('aria-pressed', 'false');
    }
  }

  /**
   * Actualiza el ícono, etiqueta y estado del botón de sonido.
   * Alterna la clase CSS .audio-active y el atributo aria-pressed.
   * @param {boolean} isAudioOn - true = sintetizador activo (sonido ON)
   */
  updateAudioButton(isAudioOn) {
    if (!this.audioToggleBtn) return;

    if (isAudioOn) {
      /* Audio activo: ícono de volumen verde, clase activa y etiqueta ON */
      this.audioToggleBtn.innerHTML = `
        <i class="bi bi-volume-up-fill"></i>
        <span class="btn-pill-label">Sonido: ON</span>
      `;
      this.audioToggleBtn.classList.add('audio-active');
      this.audioToggleBtn.setAttribute('aria-pressed', 'true');
    } else {
      /* Audio silenciado: ícono apagado, sin clase activa y etiqueta OFF */
      this.audioToggleBtn.innerHTML = `
        <i class="bi bi-volume-mute-fill"></i>
        <span class="btn-pill-label">Sonido: OFF</span>
      `;
      this.audioToggleBtn.classList.remove('audio-active');
      this.audioToggleBtn.setAttribute('aria-pressed', 'false');
    }
  }

  /**
   * Envía el formulario al backend disponible y ofrece un enlace de correo real
   * cuando el sitio se ejecuta en un hosting estático como GitHub Pages.
   */
  bindContactForm() {
    if (!this.contactForm) return;

    this.contactForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      if (!this.contactForm.reportValidity()) return;

      if (window.audioSynth) window.audioSynth.playClick();

      const submitBtn = this.contactForm.querySelector('button[type="submit"]');
      const originalBtnText = submitBtn.innerHTML;

      // Obtener datos del formulario
      const formData = new FormData(this.contactForm);
      const payload = {
        name: formData.get('name'),
        email: formData.get('email'),
        subject: formData.get('subject'),
        message: formData.get('message'),
        website_url: formData.get('website_url'),
        csrf_token: formData.get('csrf_token') || ''
      };

      submitBtn.disabled = true;
      submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Procesando...`;
      this.contactStatus.replaceChildren();

      try {
        const runtime = document.querySelector('meta[name="portfolio-runtime"]')?.content;
        const endpoints = runtime === 'static' ? [] : [
          ...(payload.csrf_token ? [new URL('api/contact.php', document.baseURI)] : []),
          new URL('api/py/contact', document.baseURI)
        ];
        let responseData = null;

        for (const endpoint of endpoints) {
          let response;
          try {
            response = await fetch(endpoint, {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify(payload)
            });
          } catch {
            continue;
          }

          if (response.status === 404 || response.status === 405) continue;
          if (!response.headers.get('content-type')?.includes('application/json')) continue;

          let data;
          try {
            data = await response.json();
          } catch {
            throw new Error('El servidor respondió en un formato inesperado.');
          }

          if (!response.ok || data.status !== 'success') {
            throw new Error(data.message || 'No se pudo enviar el mensaje.');
          }

          responseData = data;
          break;
        }

        if (responseData) {
          if (window.audioSynth) window.audioSynth.playSuccessChime();
          this.showContactStatus('success', responseData.message);
          this.contactForm.reset();
        } else {
          const subject = payload.subject || 'Contacto desde el portafolio de Sebas';
          const body = `Nombre: ${payload.name}\nCorreo: ${payload.email}\n\n${payload.message}`;
          const query = new URLSearchParams({ subject, body });
          const mailto = `mailto:sebastiangiraldocorpas@gmail.com?${query.toString()}`;
          this.showContactStatus(
            'info',
            'Este alojamiento no tiene un servicio de formularios activo. Abre tu aplicación de correo para preparar el mensaje; tendrás que pulsar Enviar allí.',
            mailto
          );
        }
      } catch (err) {
        this.showContactStatus('danger', err.message || 'Ocurrió un error. Por favor inténtalo de nuevo.');
      } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
      }
    });
  }

  showContactStatus(type, message, mailto = '') {
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} mt-3 border-0 shadow-sm`;
    alert.setAttribute('role', 'status');

    const icon = document.createElement('i');
    icon.className = `bi ${type === 'success' ? 'bi-check-circle-fill' : type === 'danger' ? 'bi-exclamation-triangle-fill' : 'bi-info-circle-fill'} me-2`;
    alert.append(icon, document.createTextNode(message));

    if (mailto) {
      alert.append(document.createTextNode(' '));
      const link = document.createElement('a');
      link.href = mailto;
      link.className = 'alert-link';
      link.textContent = 'Preparar correo';
      alert.append(link);
    }

    this.contactStatus.replaceChildren(alert);
  }

  /**
   * Resalta automáticamente el enlace del menú correspondiente a la sección visible.
   */
  initScrollSpy() {
    const sections = document.querySelectorAll('section[id]');
    const navLinks = document.querySelectorAll('.nav-link-custom');

    window.addEventListener('scroll', () => {
      let currentSectionId = '';
      const scrollPos = window.scrollY + 200;

      sections.forEach((sec) => {
        const top = sec.offsetTop;
        const height = sec.offsetHeight;
        if (scrollPos >= top && scrollPos < top + height) {
          currentSectionId = sec.getAttribute('id');
        }
      });

      navLinks.forEach((link) => {
        link.classList.remove('active');
        if (link.getAttribute('href') === `#${currentSectionId}`) {
          link.classList.add('active');
        }
      });
    }, { passive: true });
  }
}

// Inicialización de la aplicación al cargar el DOM
document.addEventListener('DOMContentLoaded', () => {
  window.portfolioApp = new PortfolioApp();
});
