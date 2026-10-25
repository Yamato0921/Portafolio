/**
 * ==============================================================================
 * GESTOR DE PROYECTOS Y CASOS DE ESTUDIO - PORTAFOLIO INTERACTIVO
 * ==============================================================================
 * Carga la información centralizada de proyectos desde el archivo JSON/API PHP,
 * renderiza dinámicamente las tarjetas interactivas con Bootstrap 5, gestiona los
 * filtros por categoría y abre un modal accesible con los detalles técnicos del proyecto.
 * ==============================================================================
 */

class ProjectsManager {
  constructor() {
    this.container = document.querySelector('#projectsGrid');
    this.filterPills = document.querySelectorAll('.filter-pill');
    this.modalEl = document.querySelector('#projectDetailModal');
    this.modalInstance = null;
    this.projects = [];
    this.activeCategory = 'all';

    if (!this.container) return;

    if (this.modalEl && window.bootstrap) {
      this.modalInstance = new bootstrap.Modal(this.modalEl);
    }

    this.init();
  }

  /**
   * Inicializa la carga de datos y eventos de filtrado.
   */
  async init() {
    this.bindFilterEvents();
    await this.loadProjects();
  }

  /**
   * Vincula las píldoras de filtrado por categoría.
   */
  bindFilterEvents() {
    this.filterPills.forEach((pill) => {
      pill.addEventListener('click', () => {
        // Reproducir sonido táctil
        if (window.audioSynth) window.audioSynth.playClick();

        // Actualizar clase activa en la interfaz
        this.filterPills.forEach((p) => p.classList.remove('active'));
        pill.classList.add('active');

        // Filtrar y renderizar
        this.activeCategory = pill.dataset.filter || 'all';
        this.render();
      });
    });
  }

  /**
   * Obtiene la lista de proyectos desde el endpoint PHP o directamente desde JSON.
   */
  async loadProjects() {
    try {
      // Intentamos cargar a través de la API PHP o el archivo estático JSON
      const url = 'assets/data/projects.json';
      const response = await fetch(url);
      if (!response.ok) throw new Error('No se pudo cargar el archivo de proyectos');
      this.projects = await response.json();
      this.render();
    } catch (error) {
      console.error('Error al cargar proyectos:', error);
      this.container.innerHTML = `
        <div class="col-12 text-center py-5">
          <p class="text-danger">Hubo un problema al cargar los proyectos. Intenta recargar la página.</p>
        </div>
      `;
    }
  }

  /**
   * Renderiza las tarjetas de proyectos aplicando el filtro activo.
   */
  render() {
    const filtered = this.projects.filter((p) => {
      if (this.activeCategory === 'all') return true;
      return p.category === this.activeCategory;
    });

    if (filtered.length === 0) {
      this.container.innerHTML = `
        <div class="col-12 text-center py-5">
          <p class="text-muted">No se encontraron proyectos en esta categoría.</p>
        </div>
      `;
      return;
    }

    this.container.innerHTML = filtered.map((project) => this.buildCardHtml(project)).join('');

    // Vincular botones de "Ver Caso de Estudio / Detalles"
    this.container.querySelectorAll('.btn-view-details').forEach((btn) => {
      btn.addEventListener('click', () => {
        const id = btn.dataset.projectId;
        this.openDetailModal(id);
      });
    });
  }

  /**
   * Genera el marcado HTML5 para una tarjeta de proyecto individual.
   * @param {Object} p Objeto con los datos del proyecto
   * @returns {string} Código HTML
   */
  buildCardHtml(p) {
    const tagsHtml = p.tags.map((t) => `<span class="project-tag-chip">${t}</span>`).join('');

    return `
      <div class="col-lg-4 col-md-6 mb-4">
        <article class="project-card" data-id="${p.id}">
          <!-- Cabecera visual con degradado representativo -->
          <div class="project-banner" style="background: ${p.gradient};">
            <i class="bi ${p.icon || 'bi-code-slash'} project-banner-icon" aria-hidden="true"></i>
          </div>

          <!-- Cuerpo de la tarjeta -->
          <div class="project-body">
            <div class="project-meta-row">
              <span>${p.year}</span>
              <span class="badge bg-dark">${p.stars}</span>
            </div>

            <h3 class="project-title">${p.title}</h3>
            <p class="text-muted mb-3 flex-grow-1" style="font-size: 0.92rem;">${p.description}</p>

            <!-- Etiquetas de lenguajes y librerías utilizadas -->
            <div class="mb-3">
              ${tagsHtml}
            </div>

            <!-- Botón de apertura de caso de estudio -->
            <button class="btn btn-outline-dark btn-sm rounded-pill w-100 fw-semibold btn-view-details mt-auto" data-project-id="${p.id}">
              <i class="bi bi-arrow-up-right-circle me-1"></i> Explorar Caso de Estudio
            </button>
          </div>
        </article>
      </div>
    `;
  }

  /**
   * Abre la ventana modal con la ficha técnica detallada del proyecto.
   * @param {string} id Identificador único del proyecto
   */
  openDetailModal(id) {
    const p = this.projects.find((item) => item.id === id);
    if (!p) return;

    if (window.audioSynth) window.audioSynth.playClick();

    // Rellenar elementos del modal
    document.querySelector('#modalProjectTitle').textContent = p.title;
    document.querySelector('#modalProjectSubtitle').textContent = p.subtitle;
    document.querySelector('#modalProjectDescription').textContent = p.longDescription || p.description;

    // Métricas técnicas
    const metricsContainer = document.querySelector('#modalProjectMetrics');
    if (metricsContainer && p.metrics) {
      metricsContainer.innerHTML = Object.entries(p.metrics)
        .map(([k, v]) => `
          <div class="col-4">
            <div class="p-2 border rounded bg-light text-center">
              <small class="text-muted d-block text-uppercase" style="font-size: 0.7rem;">${k}</small>
              <strong class="d-block text-dark">${v}</strong>
            </div>
          </div>
        `).join('');
    }

    // Lista de características clave
    const featuresContainer = document.querySelector('#modalProjectFeatures');
    if (featuresContainer && p.features) {
      featuresContainer.innerHTML = p.features
        .map((f) => `<li class="mb-1"><i class="bi bi-check2-circle text-success me-2"></i>${f}</li>`)
        .join('');
    }

    // Etiquetas
    const tagsContainer = document.querySelector('#modalProjectTags');
    if (tagsContainer) {
      tagsContainer.innerHTML = p.tags.map((t) => `<span class="badge bg-secondary me-1 mb-1">${t}</span>`).join('');
    }

    if (this.modalInstance) {
      this.modalInstance.show();
    }
  }
}

// Inicialización de proyectos al cargar la página
document.addEventListener('DOMContentLoaded', () => {
  window.projectsManager = new ProjectsManager();
});
